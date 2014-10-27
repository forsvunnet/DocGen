<!DOCTYPE html>
<html>
<head>
  <title>Voidjs Docs</title>
  <script src="//ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js"></script>
  <link href="//maxcdn.bootstrapcdn.com/bootstrap/3.2.0/css/bootstrap.min.css" rel="stylesheet">
  <script src="http://cdnjs.cloudflare.com/ajax/libs/prettify/r298/run_prettify.js" type="text/javascript"></script>
  <link href="http://cdnjs.cloudflare.com/ajax/libs/prettify/r298/prettify.css" type="text/css">
</head>
<body>
<div class="container">
  <div class="row">
    <div class="col-md-4">
      <h1>Menu</h1>
      <nav id="menu"></nav>
    </div>
    <div class="col-md-8">
      <div class="page-header">
        <h1>Documentation</h1>
      </div>
      <div id="content"></div>
    </div>
  </div>
</div>
<script type="text/javascript">
var files = {};
var build_menu = function() {
  var menu = $('#menu');
  for ( var file in files ) {
    menu.append(
      $('<strong>').text( file )
    );
    file_docs = $('<ul>');

    for ( var title in files[file] ) {
      var doc = files[file][title];
      console.log(doc);
      var list_item = $('<li>').append(
        $('<a>').text( title )
          .attr({
            'href': '#'+ doc.id
          })
      );

      file_docs.append( list_item );
    }
    menu.append(
      file_docs
    );
  }
};
var content, id_index = 0;
var process_doc = function( doc ) {
  var def = doc.definition || doc.title;
  var title = doc.title || doc.definition;
  if ( !files[doc.file] ) { files[doc.file] = {}; }
  var object = {
    doc:doc,
    elem: {},
    id: ++id_index,
  };
  var box = $('<article>');
  box.appendTo( content );
  if ( title ) {
    object.elem.title = $('<h2>');
    object.elem.title
      .text( title )
      .attr( 'id', id_index )
      .appendTo( box );
  }
  if ( doc.file ) {
    object.elem.file = $('<small>');
    object.elem.file
      .addClass( 'cf' )
      .text( doc.file + ( doc.line ? ':' + doc.line : '' ) )
      .appendTo( box );
  }
  // Progress bar
  var progress = doc.progress;
  if ( !progress ) { progress = 0; }
  object.elem.progress = $('<div>');
  object.elem.progress
    .addClass('progress')
    .append( $('<div>')
      .addClass('progress-bar')
      .attr({
        role: 'progress',
        'aria-valuenow': progress,
        'aria-valuenowmin': 0,
        'aria-valuenowmax': 100,
      })
    )
    .appendTo( box );

  if ( 15 > progress ) {
    object.elem.progress
      .addClass('progress-bar-danger');
  }
  else if ( 30 > progress ) {
    object.elem.progress
      .addClass('progress-bar-warning');
  }
  else if ( 60 < progress ) {
    object.elem.progress
      .addClass('progress-bar-success');
  }

  if ( doc.definition ) {
    object.elem.definition = $('<code>');
    object.elem.definition
      .text( doc.definition )
      .appendTo( box );
  }
  if ( doc.parameters ) {
    // @TODO: Recursive <dl>
    object.elem.parameters = $('<div>');
    object.elem.parameters
      .text( doc.parameters )
      .appendTo( box );
  }
  if ( doc.description ) {
    object.elem.description = $('<p>');
    object.elem.description
      .html( doc.description.replace("\n", '<br>') )
      .appendTo( box );
  }
  object.elem.box = box;
  files[doc.file][def] = object;
};

var fill_data = function( json ) {
  console.log( json );
  for (var i = 0; i < json.length; i++) {
    process_doc( json[i] );
  }

  build_menu();
};

$(document).ready(function() {
  content = $('#content');
  $.getJSON( '/json.php', fill_data );
});
</script>
</body>
</html>
