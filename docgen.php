<?php
class CodeUtility {
  static $scripts = array();
  function register( $slug, $callback ) {
    self::$scripts[$slug] = $callback;
  }

  function apply_at_rule( $slug, $line ) {
    if ( array_key_exists( $slug, self::$scripts ) ) {
      if ( is_array( self::$scripts[$slug] ) ) {
        return self::$scripts[$slug][0][0]->{self::$scripts[$slug][0]}($line);
      }
      else {
        return self::$scripts[$slug]($line);
      }
    }
    else {
      return $line;
    }
  }
}
// A class for code documentation
class CodeDoc {
  var $valid = FALSE;
  var $file_name;
  var $line;
  var $title;
  var $definition;
  var $parameters;
  var $data;
  public function __construct( $content, $definition, $file_name='', $pos='' ) {
    $lines = explode( "\n", $content );
    array_shift( $lines );
    array_pop( $lines );
    foreach ( $lines as &$line ) {
      if ( !preg_match( '/\s+\*/', $line ) ) {
        return FALSE;
      }
      $line = preg_replace( '/\s+\*\s*/', '', $line );
    }
    $this->valid = TRUE;
    $this->file_name = $file_name;
    $this->line = $pos;
    if ( $definition ) {
      $this->definition = $definition['name'];

      $this->parameters = explode( ',', str_replace(' ', '', $definition['params'] ) );
    }
    $this->title = array_shift( $lines );

    $description = array();
    foreach ($lines as $line) {
      if ( strlen($line) && '@' === $line[0] ) {
        $this->process_special( $line );
      }
      else {
        $description[] = $line;
      }
    }
    $this->description = implode( "\n", $description );
  }

  public function process_special( $line ) {
    $matches = array();
    if ( preg_match( '/^@(\S+)(.*)$/', $line, $matches ) ){
      if ( !isset( $this->data[$matches[1]] ) ) {
        $this->data[$matches[1]] = array();
      }
      $this->data[$matches[1]][] = CodeUtility::apply_at_rule( $matches[1], $matches[2] );
    }
  }

  public function to_array() {
    return array(
      'title' => $this->title,
      'description' => $this->description,
      'definition' => $this->definition,
      'parameters' => $this->parameters,
      'file' => $this->file_name,
      'line' => $this->line,
      'data' => $this->data,
    );
  }

}
// A factory class for CodeDoc
class CodeDir {
  var $ext = array();
  var $ignore;
  var $code_docs = array();
  var $errors = array();

  public function __construct( $ext, $ignore = array() ) {
    $this->ignore = $ignore;
    if ( is_array( $ext ) ) {
      $this->ext = $ext;
    }
    elseif ( is_string( $ext ) ) {
      $this->ext = explode(',', str_replace( ' ', '', $ext ) );
    }
    else {
      throw new Exception("Extension must be array or string", 1);
    }
  }

  public function traverse_dir( $dir ) {
    // Scan the dir
    $scan = scandir( $dir );
    // Iterate through the scan
    foreach ( $scan as $path ) {
      if ( '.' === $path || '..' === $path ) {
        continue;
      }

      // Ignore ignored files
      foreach ($this->ignore as $ignore) {
        $cont = false;
        if ( preg_match( "/{$ignore}/", $path ) ) {
          $cont = true;
          break;
        }
        if ( $cont ) {
          continue;
        }
      } // - ignore
      // Recursive search for files
      if ( is_dir( $dir .'/'.$path ) ) {
        $this->traverse_dir( $dir .'/'. $path );
      }
      // Check file for extension
      else {
        $ext = pathinfo( $dir .'/'.$path, PATHINFO_EXTENSION );
        if ( in_array( $ext, $this->ext ) ) {
          // Process the file
          $this->process_file( $dir .'/'. $path );
        }
      }
    }
    return $this;
  }

  public function process_file( $file ) {
    $content = file_get_contents( $file );
    $pos = -1;
    do {
      $pos = strpos( $content, "/**\n", $pos + 1 );
      if ( FALSE !== $pos ) {
        $end_pos  = strpos( $content, '*/', $pos );
        if ( FALSE !== $end_pos ) {
          // Get the code header
          $header = substr( $content, $pos, $end_pos - $pos + 2 );
          // Get the source code
          $def = $this->get_def( $content, $end_pos );
          $doc = new CodeDoc( $header, $def, $file, $pos );
          if ( $doc->valid ) {
            $id = count( $this->code_docs );
            $this->code_docs[] = $doc;
          }
          else {
            $this->errors[] = $file .':'. $pos;
          }
        }
      }
    } while ( FALSE !== $pos );

    return $this;
  }

  function get_def( $content, $pos ) {
    $new_line = strpos( $content, "\n", $pos );
    // Sanity check position
    if ( FALSE === $new_line ) { return ''; }
    // Sanity check position
    $new_line_end = strpos( $content, "\n", $new_line + 1 );
    if ( FALSE === $new_line_end ) { return ''; }

    // Get the line
    $line = substr( $content, $new_line,  $new_line_end - $new_line );

    $matches = array();

    // PHP style functions
    if ( preg_match( '/^\s*function\s+([^\(]+)\(([^\)]+)\)\s*\{/', $line, $matches ) ) {
      return array(
        'name'   => $matches[1],
        'params' => $matches[2],
      );
    }
    // JavaScript style functions
    if ( preg_match( '/^\s*(?:var\s+)?(\S+)\s*=\s*function\s*\(([^\)]+)\)\s*\{/', $line, $matches ) ) {
      return array(
        'name'   => $matches[1],
        'params' => $matches[2],
      );
    }
    return FALSE;
  }

  public function export() {
    $docs = array();
    foreach ( $this->code_docs as $code_doc ) {
      $docs[] = $code_doc->to_array();
    }
    return $docs;
  }

}
