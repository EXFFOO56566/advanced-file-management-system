<?php
/********************************************************************************
 * #                      Advanced File Manager v3.0
 * #******************************************************************************
 * #      Author:     Convergine.com
 * #      Email:      info@convergine.com
 * #      Website:    http://www.convergine.com
 * #
 * #
 * #      Version:    3.0
 * #      Copyright:  (c) 2009 - 2015 - Convergine.com
 * #
 * #*******************************************************************************/
######################### DO NOT MODIFY (UNLESS SURE) ########################
session_start();
require_once("includes/dbconnect.php"); //Load the settings
require_once("includes/functions.php"); //Load the functions
$msg = "";
$id = (!empty($_REQUEST["id"])) ? strip_tags(str_replace("'", "`", $_REQUEST["id"])) : '';
$folderInfo = getPublicFolder($id,$md5_salt);
$showFiles = true;
if(getSettings("public_directory")==2){
    $showFiles = false;
    $folderInfo['name']="";
    $msg = '<div class="loginMessage loginError">Public view disabled by administrator.</div>';
}else{
    $showFiles = true;
}
if($folderInfo===false){
    $folderInfo['name']="";
    $msg = '<div class="loginMessage loginError">Sorry, folder not found</div>';
    $showFiles = false;
}
    ?>
    <!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8"/>
        <title>Advanced File Manager<?php echo !empty($_SESSION["username"])?" - Welcome {$_SESSION["username"]}":""?>!</title>
        <link rel="stylesheet" type="text/css" href="../css/bootstrap.css">
        <link rel="stylesheet" type="text/css" href="../css/stylesheet.css">
        <link rel="stylesheet" type="text/css" href="../css/tipTip.css">
        <link href='//fonts.googleapis.com/css?family=Lobster' rel='stylesheet' type='text/css'>
        <script src="//ajax.googleapis.com/ajax/libs/jquery/1.8.2/jquery.min.js"></script>
        <script src="//code.jquery.com/ui/1.8.24/jquery-ui.js"></script>
        <link  href="//code.jquery.com/ui/1.8.24/themes/base/jquery-ui.css" rel="stylesheet">
        <script src="../js/main.js"></script>
        <link rel="stylesheet" href="../includes/viewimage/colorbox.css" />
        <script src="../includes/viewimage/colorbox.js"></script>
        <script src="../includes/viewimage/displayimage.js"></script>
        <style type="text/css" title="currentStyle">
            @import "../includes/media/css/demo_page.css";
            @import "../includes/media/css/demo_table.css";
        </style>
        <script type="text/javascript" language="javascript" src="../includes/media/js/jquery.dataTables.js"></script>

    </head>

<body>
<div id="header">
    <h1><a href="../main.php">Advanced File Management 3</a></h1>

</div>
<div class="clear"></div>
<div id="content">
    <link href="../includes/jplayer/skin/blue.monday/jplayer.blue.monday.css" rel="stylesheet" type="text/css"/>
    <script type="text/javascript" src="../includes/jplayer/jquery.jplayer.min.js"></script>
<?php if($showFiles){?>
    <script type="text/javascript" charset="utf-8">
        $(document).ready(function () {
            $("#btnAdd").colorbox({inline: true, width: "700px", height: "513px"});
            $(".iframe").colorbox({iframe: true, width: "480px", height: "200px"});
            $(".callbacks").colorbox({
                onOpen: function () {
                    alert('onOpen: colorbox is about to open');
                },
                onLoad: function () {
                    alert('onLoad: colorbox has started to load the targeted content');
                },
                onComplete: function () {
                    alert('onComplete: colorbox has displayed the loaded content');
                },
                onCleanup: function () {
                    alert('onCleanup: colorbox has begun the close process');
                },
                onClosed: function () {
                    alert('onClosed: colorbox has completely closed');
                }
            });
            $("#jquery_jplayer_1").jPlayer({
                ready: function () {
                    $(this).jPlayer("setMedia", {
                        mp3: "folder1/when_i_was_your_man.mp3"
                    });
                },
                swfPath: "js",
                supplied: "mp3",
                wmode: "window",
                smoothPlayBar: true,
                keyEnabled: true
            });
            var oTable = $('#table').dataTable({

                "bProcessing": true,
                "bServerSide": true,
                "sAjaxSource": "../includes/datasource/files_public.php?idF=<?php echo($folderInfo['id'])?>",
                "oLanguage": {
                    "sInfoFiltered": ""
                }


            });
/////////////////

        });
        function getStringImg(str) {
            return alert(str);
        }
        function confirmDelete() {
            return confirm("Are you sure to delete selected files?");
        }
        function viewImage(imgTitle, path) {
            $("#imgValue").html($.colorbox({href: '../'+path, initialHeight: 'auto', initialWidth: 'auto', maxWidth: '850px', title: imgTitle}));
        }
        function viewAudio(id) {
            $("#audioValue").html($.colorbox({href: "../includes/mp3.php?id=" + id, className: "inline", iframe: true, width: "480px", height: "200px"}));
        }
        function viewVideo(id) {
            $("#audioValue").html($.colorbox({href: "../includes/video.php?id=" + id, className: "inline", iframe: true, width: "80%", height: "72%"}));
        }
        function viewPdf(path) {
            $("#pdfValue").html($.colorbox({href: "../includes/pdf/web/viewer.php?pdf=" + path, className: "inline", iframe: true, width: "850px", height: "100%"}));
        }
        function viewCode(code) {
            $("#codeValue").html($.colorbox({href: "../includes/google-code-prettify/view.php?file=" + code, className: "inline", iframe: true, width: "950px", height: "100%", maxWidth: '1100px'}));
        }
        function viewDoc(path) {
            $("#docValue").html($.colorbox({href: "https://docs.google.com/viewer?url=" + path + "&embedded=true", className: "inline", iframe: true, width: "850px", height: "100%", maxWidth: '1100px'}));
        }
    </script>
<?php }?>
    <div id="content-main">
<br>
        <h2><?php echo($folderInfo['name']) ?></h2>
        <div class="clear"></div>
        <div id='msg'><?php echo $msg; ?></div>
        <br/>
        <?php if($showFiles){?>
        <div id="dynamic">
            <form action="#" id="formTable">
                <table cellpadding="0" cellspacing="0" border="0" class="display lra" id="table">
                    <thead>
                    <tr>
                        <th>&nbsp;</th>
                        <th width="35%">Title</th>
                        <th width="15%">Size</th>
                        <th width="8%">Extension</th>
                        <th width="25%">Date Uploaded</th>
                        <th width="18%">Link</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr>
                        <td colspan="6" class="dataTables_empty">Loading data from server</td>
                    </tr>
                    </tbody>
                    <tfoot>
                    <tr>
                        <th>&nbsp;</th>
                        <th>Title</th>
                        <th>Size</th>
                        <th>Extension</th>
                        <th>Date Uploaded</th>
                        <th>Link</th>
                    </tr>
                    </tfoot>
                </table>
            </form>
        </div>
        <?php }?>
        <div class="spacer"></div>
        <br/><br/>
    </div>
    <br/><br/>
    <span id="folder"></span>
    <div id="imgValue"></div>
    <div id="audioValue"></div>
    <div id="videoValue"></div>
    <div id="pdfValue"></div>
    <div id="codeValue"></div>
    <div id="docValue"></div>
    </div>
<div class="footer">
    <a target="_blank" href="http://www.convergine.com"><img border="0" src="../images/madebycircle.png"></a>
</div>
</body>
</html>