<?php
require_once("dbconnect.php"); //Load the settings
require_once("functions.php"); //Load the functions
$video= explode( ',', $_GET['id']);
?>
<!doctype html>
<head>
   <!-- player skin -->
   <link rel="stylesheet" type="text/css" href="flowplayer/skin/minimalist.css" />
  <!-- flowplayer depends on jQuery 1.7.1+ (for now) -->
   <script src="http://ajax.googleapis.com/ajax/libs/jquery/1/jquery.min.js"></script>
   <!-- include flowplayer -->
   <script src="flowplayer/flowplayer.min.js"></script>
</head>

<body>
   <!-- the player -->
   <div class="flowplayer" data-swf="flowplayer/flowplayer.swf" data-ratio="0.5" style="width:100%;height:100%;max-height:auto;max-width:auto;">
      <video>
         <source type="video/<?php echo $video[2]; ?>" src="../<?php echo $video[1]; ?>"/>
      </video>
   </div>
   <div style="color:#333;padding-top:4px;"><center><strong><?php echo $video[0]; ?></strong></center></div>
</body>
