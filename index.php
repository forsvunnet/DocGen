<!DOCTYPE html>
<html>
<head>
  <title>Voidjs Docs</title>
  <script src="//ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js"></script>
  <link href="//maxcdn.bootstrapcdn.com/bootstrap/3.2.0/css/bootstrap.min.css" rel="stylesheet">
  <script src="http://cdnjs.cloudflare.com/ajax/libs/prettify/r298/run_prettify.js" type="text/javascript"></script>
  <link href="http://cdnjs.cloudflare.com/ajax/libs/prettify/r298/prettify.css" type="text/css">
  <style type="text/css">
  .code-01 { color: blue; }
  .code-02 { color: blue; }
  .code-03 { color: blue; }
  .function-definition {
    padding: 0.6em 1em;
    margin-bottom: 1em;
    display: inline-block;
    background-color: #fff;
    border: 1px solid #e9e9e9;
  }
  .fr { float: right; }
  </style>
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
      if (doc.id == 72)console.log(doc);
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
  var i;
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
  if ( doc.file ) {
    object.elem.file = $('<small>');
    object.elem.file
      .addClass( 'fr' )
      .text( doc.file + ( doc.line ? ':' + doc.line : '' ) )
      .appendTo( box );
  }
  if ( title ) {
    object.elem.title = $('<h2>');
    object.elem.title
      .text( title )
      .attr( 'id', id_index )
      .appendTo( box );
  }
  // Progress bar
  var progress = doc.data ? (doc.data.progress ? doc.data.progress[0] : 0 ) : 0;
  if ( 'string' === typeof progress ) {
    progress = parseInt( progress.replace( '%','' ), 10 );
  }
  if ( !progress ) { progress = 0; }
  object.elem.progress = $('<div>');
  object.elem.progress_bar = $('<div>');
  object.elem.progress_bar.addClass('progress-bar')
    .attr({
      role: 'progress',
      'aria-valuenow': progress,
      'aria-valuemin': 0,
      'aria-valuemax': 100,
    })
    .css( 'width', progress + '%' );
  object.elem.progress
    .addClass('progress')
    .append( object.elem.progress_bar )
    .appendTo( box );

  if ( 15 > progress ) {
    object.elem.progress_bar
      .addClass('progress-bar-danger');
  }
  else if ( 30 > progress ) {
    object.elem.progress_bar
      .addClass('progress-bar-warning');
  }
  else if ( 60 < progress ) {
    object.elem.progress_bar
      .addClass('progress-bar-success');
  }

  if ( doc.definition ) {
    object.elem.definition = $('<code>');
    object.elem.definition
      .addClass( 'function-definition' )
      .text( doc.definition )
      .appendTo( box );
  }

  // @PARAM
  var parameters = doc.parameters;
  if ( doc.data && doc.data.param ){
    parameters = [];
    for ( i in doc.data.param ) {
      var param = doc.data.param[i];
      parameters.push( param[0] + ' ' + param[1] );
    }
  }
  if ( parameters ) {
    object.elem.parameters = $('<span>');
    object.elem.parameters
      .html( '( <span class="code-03">'+ parameters.join(', ') +'</span> )' )
      .appendTo( object.elem.definition );
  }

  // @RETURN
  var ret = false;
  if ( doc.data && doc.data.return && doc.data.return.length ){
    ret = doc.data.return[0][0];
  }
  if ( ret ) {
    object.elem.ret = $('<span>');
    object.elem.ret
      .html( '<span class="code-02">'+ ret +'</span> ' )
      .prependTo( object.elem.definition );
  }

  if ( doc.description ) {
    object.elem.description = $('<p>');
    object.elem.description
      .html( doc.description.replace(/\n/g, '<br>') )
      .appendTo( box );
  }
  object.elem.box = box;
  files[doc.file][def] = object;
};

var fill_data = function( json ) {
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
