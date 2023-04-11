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
session_start();
require_once("includes/dbconnect.php"); //Load the settings
require_once("includes/functions.php"); //Load the functions
$msg = "";
if (isset($_SESSION["logged_in"]) && $_SESSION["logged_in"] != true) {
    header("Location: index.php");
    exit();
} else {
    //get access level
    if (isset($_SESSION["accesslevel"])) {
        $access = $_SESSION["accesslevel"];
        //determin admin or not.
        if (stristr($access, "abcdef")) {
            $level = "admin";
        } else {
            $level = "user";
        }
    } else {
        header("Location: index.php");
    }
    //request file ID
    $id = (!empty($_REQUEST["id"])) ? strip_tags(str_replace("'", "`", $_REQUEST["id"])) : '';
    //get file info
    $fileInfo = getFileInfo($id);
    //public directory path
    $publicDirUrl = getPublicFolderURL($fileInfo[4],$md5_salt);
    $showPublicDirLink = getSettings('require_login_download')==2 && getSettings("public_directory")==1?true:false;
    $filepath = $_SERVER['HTTP_HOST'] . $script_dir . "download_file.php?idFile=" . $id;
    $filename = $fileInfo[0];
    //get user info
    $userInfo = getUserInfo($_SESSION["idUser"]);
    if ($fileInfo[3] != $_SESSION["idUser"] && stristr($access, "m") != true) {
        echo "you don't have permissions to view this page";
    } else {
        //if sending email
        $sendTo = (!empty($_REQUEST["sendTo"])) ? strip_tags(str_replace("'", "`", $_REQUEST["sendTo"])) : '';
        $msg    = (!empty($_REQUEST["msg"])) ? str_replace("'", "`", $_REQUEST["msg"]) : '';
        if (!empty($_POST["sendEmail"]) && $_POST["sendEmail"] == "yes" && !empty($sendTo)) {
            //validate recipients email
            if (!preg_match("(^[-\w\.]+@([-a-z0-9]+\.)+[a-z]{2,4}$)i", $sendTo)) {
                $msg = "<div class='loginMessage loginError'>Recipients email address in invalid!</div>";
                addLog($_SESSION["idUser"], "Tried to share a link to \"" . $fileInfo[0] . "." . $fileInfo[1] . "\" with $sendTo");
            } else {
                //everything is ok, send email
                $headers = "MIME-Version: 1.0\n";
                $headers .= "Content-type: text/html; charset=utf-8\n";
                $headers .= "From: '" . $userInfo[0] . "' <" . $userInfo[1] . "> \n";
                $subject = $userInfo[0] . " shared a link with you!";
                $msg = strtr($msg,array(
                "[link_to_file]"=>"<a href='http://{$filepath}' target='_blank'>{$filename}</a>",
                "[link_to_folder]"=>"<a href='http://{$publicDirUrl}' target='_blank'>"._getFolderName($fileInfo[4])."</a>"));
                sendMail($sendTo, $subject, stripslashes(nl2br($msg)));
                $msg = "<div class='loginMessage loginSuccess'>Thank you, your link was sent to $sendTo</div>";
                addLog($_SESSION["idUser"], "Shared a link to \"" . $fileInfo[0] . "." . $fileInfo[1] . "\"  with $sendTo ");
            }
        }
    include "includes/header.php";
        ?>

        <div id="content-main">
            <h2>Share Link</h2>
            <a id="btnAdd" class="btn btn-primary rFloat " href="files.php">Back to list</a>
            <div class="clear"></div>
            <form action="" enctype="multipart/form-data" method="post" name="sendMail"
                  class="form-horizontal ">
                <div class="form-group">
                    <label class="col-sm-4 control-label">Selected file:</label>
                    <div class="col-sm-5">
                        <p class="form-control-static"><?php echo $fileInfo[0] ?></p>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-sm-4 control-label">Copy/Paste Link:</label>
                    <div class="col-sm-5">
                         <input type="text" value="http://<?php echo $filepath; ?>"  class="form-control" onclick="this.select()">
                    </div>
                </div>
                <?php if($showPublicDirLink){?>
                <div class="form-group">
                    <label class="col-sm-4 control-label">Directory Link:</label>
                    <div class="col-sm-5">
                        <input type="text" value="http://<?php echo $publicDirUrl; ?>" class="form-control" onclick="this.select()">
                    </div>
                </div>
                <?php }?>
            </form>

            <h2>Email Link</h2>
            <div class="clear"></div>
            <?php echo $msg ?>
            <p>You can send an email to your friend using following form.</p>

            <form action="" enctype="multipart/form-data" method="post" name="sendMail" class="form-horizontal ">
                <input type="hidden" value="yes" name="sendEmail"/>

                <div class="form-group">
                    <label for="inputEmail3" class="col-sm-4 control-label">From:</label>
                    <div class="col-sm-5">
                        <p class="form-control-static"><?php echo $userInfo[1]; ?></p>
                    </div>
                </div>
                <div class="form-group">
                    <label for="sendTo" class="col-sm-4 control-label">To:</label>

                    <div class="col-sm-5">
                        <input class="form-control" name="sendTo" type="text" placeholder="Email">
                    </div>
                </div>
                <div class="form-group">
                    <label for="inputEmail3" class="col-sm-4 control-label">Message:
                    <div class="subLabel"><small>Shortcodes:<br/><b>[link_to_file]</b> = file link
                            <?php if($showPublicDirLink){ ?>
                                <br>
                                <b>[link_to_folder]</b> = folder link
                            <?php }?>
                    </small></div>
                    </label>

                    <div class="col-sm-5">
                        <textarea name="msg" rows="8" class="form-control" >Hey, sending you the link to check out:
[link_to_file]

Kind Regards,

<?php echo $userInfo[0]; ?>
                        </textarea>
                    </div>
                </div>
                <div class="form-group">
                    <div class="col-sm-offset-4 col-sm-3">
                        <button type="submit" class="btn btn-primary btn-block">Submit</button>
                    </div>
                </div>
            </form>
        </div>
        </div>
        <?php
        include "includes/footer.php";
    }
}?>