<html>
<head>
<script type="text/javascript" src="http://code.jquery.com/jquery-1.7.2.min.js"></script>
<link rel="stylesheet" type="text/css" href="bootstrap.css">
    <link href="prettify.css" rel="stylesheet">
<script type="text/javascript" src="prettify.js"></script>
<?php
$code = explode(',', $_GET['file']);
$ext= $code[2];
$src=array('apollo','basic','clj','css','dart','erlang','go','hs','lisp','llvm','lua','matlab','ml','mumps','n','pascal','proto','r','rd','scala','sql','tcl','tex','vb','vhdl','wiki','xq','yaml');
$implode= implode(',', $src);
$explode= explode(',',$implode);
if(in_array($ext, $explode)){
echo "<script type='text/javascript' src='src/lang-".$ext."'></script>
";
}
?>
<script type="text/javascript">
(function(jQuery){
  jQuery( document ).ready( function() {
    prettyPrint();
  } );
}(jQuery))
</script>
</head>
<body>
<?php 
$filename= "../../".$code[1];
if($code[2]=='css'){
  echo "<figure class='code'>
	<div class='navbar'>
		<div class='navbar-inner' style='text-align:center;'>
			<span class='brand2' style='color:#333;'>".$code[0].".".$code[2]."</span>
		</div>
		
	</div>
</figure>
<figure class='code' style='position:relative;margin-top:-20px;'>
<pre class='prettyprint linenums lang-".$code[2]."'>";
highlight_file($filename);
echo "</pre>
</figure>"; 

}else{

function highlightArray($filename)
{
   $content=highlight_file($filename, true);
   $content=explode("<br />", $content);
   return $content;
}

$content=highlightArray($filename);
$content_count=count($content);

  echo "<figure class='code'>
	<div class='navbar'>
		<div class='navbar-inner' style='text-align:center;'>
			<span class='brand2' style='color:#333;'>".$code[0].".".$code[2]."</span>
		</div>
		
	</div>
</figure>
<figure class='code' style='position:relative;margin-top:-20px;'>
<pre class='prettyprint linenums lang-".$code[2]."'>";
for($i=1;$i<=$content_count;++$i)
{
   echo  $content[$i-1];
}
echo "</pre>
</figure>";
}?>
</body>
</html>