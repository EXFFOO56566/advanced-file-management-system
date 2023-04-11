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
    $file = (!empty($_REQUEST["file"])) ? strip_tags(str_replace("'", "`", $_REQUEST["file"])) : '';
    $id   = (!empty($_REQUEST["id"])) ? strip_tags(str_replace("'", "`", $_REQUEST["id"])) : '';
    //show page only if user has rights to edit his or all files.
    $text = (!empty($_REQUEST["text"])) ? strip_tags(str_replace("'", "`", $_REQUEST["text"])) : '';
    $fileInfo = getFileInfo($file);


    if (!empty($_REQUEST["add_msg"]) && $_REQUEST["add_msg"] == "yes" && !empty($text) && !empty($id)) {
        //check if user has proper access + file creator
        $sql = "SELECT id,userID FROM {$db_pr}files WHERE id='" . $id . "'";
        $result = mysqli_query($mysqli,$sql) or die("Error occurred - tried  to get file info.");
        $rrr = mysqli_fetch_assoc($result);
        //if user has permissions to edit file
        $ui  = getUserInfo($_SESSION["idUser"]);
        $sql = "INSERT INTO {$db_pr}messages (fileID, dateCreated, name, email, text) VALUES ('" . $id . "',NOW(),'" . $ui[0] . "','" . $ui[1] . "','" . $text . "')";
        $result = mysqli_query($mysqli,$sql) or die("Error occurred - tried  to insert message.");
        $msg = "<div class='loginMessage loginSuccess'>Message was successfully added.</div>";
        addLog($_SESSION["idUser"], "Added message for file " . getFileTitle($id));

        $tempUID = getFileInfo($id);
        $userID = $tempUID[3];
        $userInfo = getUserInfo($userID);
        $mailData = array(
            "{%user%}"=>getUser($_SESSION["idUser"]),
            "{%file%}"=>getFileTitle($fileID),
            "{%message%}"=>nl2br($text)
        );
        sendMail($userInfo[1],"New message for file " . getFileTitle($id) . "!","new_message.php",$mailData);
        sendMail(getSettings('notify_email'),"New message for file " . getFileTitle($id) . "!","new_message.php",$mailData);
    }

include "includes/header.php";
    ?>
    <div id="content-main">
        <?php echo $msg; ?>
        <h2>Add Message</h2>
        <a id="btnAdd" class="btn btn-primary rFloat " href="files.php">Back to list</a>

        <div class="clear"></div>
        <form action="messages.php?file=<?php echo $file ?>" enctype="multipart/form-data" method="post" name="ff1"
              class="form-horizontal inner-form" >
            <input value="yes" name="add_msg" type="hidden"/>
            <input value="<?php echo $file; ?>" name="id" type="hidden"/>
            <input value="<?php echo $file; ?>" name="file" type="hidden"/>

            <div class="form-group">
                <label for="title" class="col-sm-3 control-label">File:</label>

                <div class="col-sm-9">

                    <label class="control-label" style="text-align: left"><?php echo($fileInfo[2])?></label>

                </div>
            </div>

            <div class="form-group">
                <label for="title" class="col-sm-3 control-label">Message:</label>

                <div class="col-sm-9">
                    <textarea name="text" id="text" class="form-control" rows="3"></textarea>
                </div>
            </div>
            <div class="form-group">
                <div class="col-sm-offset-3 col-sm-2">
                    <button type="submit" class="btn btn-primary btn-block">Submit</button>
                </div>
            </div>
        </form>
        <h2>File Messages</h2>
        <div class="clear"></div>
        <div id="blogComments">

            <?php
            $sql = "SELECT * FROM {$db_pr}messages WHERE fileID='" . $file . "' ORDER BY dateCreated ASC";
            $result = mysqli_query($mysqli,$sql) or die("oopsy, error encountered");
            if (mysqli_num_rows($result) > 0) {
                while ($rr = mysqli_fetch_assoc($result)) {
                    $email = $rr["email"]; //"someone@somewhere.com";
                    $size     = 100;
                    $grav_url = "http://www.gravatar.com/avatar/" . md5(strtolower(trim($email))) . "?d=" . urlencode($default) . "&s=" . $size;
                    $mname   = $rr["name"];
                    ?>
                    <div class="fmCommentWrap">
                        <div class="fmGravatar"><img src="<?php echo $grav_url; ?>" alt="avatar" />
                        </div>
                        <div class="fmMessage">
                            <div class="fmMessageHeader"><?php echo $mname ?>
                                - <?php echo date("F jS H:i", strtotime($rr["dateCreated"])) ?></div>
                            <div class="fmMessageText"><?php echo nl2br($rr["text"]) ?></div>
                        </div>
                    </div>

                <?php
                }
            } else {
                echo "0 messages found for this file";
            }
            ?>
        </div>
    </div>
    </div>
    <?php include "includes/footer.php"; }// }?>