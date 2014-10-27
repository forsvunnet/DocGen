<?php
require_once 'docgen.php';
// JSON Doc
header('Content-Type: application/json');

$path = '../path/to/code';
$ext = 'js,php';

// Files and folders to ignore (regex):
$ignore = array(
  'node_modules',
  '^\.'
);

// Create the docs
$code_dir = new CodeDir( $ext, $ignore );
$docs = $code_dir->traverse_dir( $path )->export();

echo json_encode( $docs );
