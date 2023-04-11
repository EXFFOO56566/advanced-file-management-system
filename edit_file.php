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
    //show page only if user has rights to edit his or all files.
    if (stristr($access, "k") || stristr($access, "l")) {
        $id    = (!empty($_REQUEST["id"])) ? strip_tags(str_replace("'", "`", $_REQUEST["id"])) : '';
        $title = (!empty($_REQUEST["title"])) ? strip_tags(str_replace("'", "`", $_REQUEST["title"])) : '';
        //edit file block.
        if (!empty($_REQUEST["edit_file"]) && $_REQUEST["edit_file"] == "yes" && !empty($title) && !empty($id)) {

        if($demo){
            $msg = "<div class='loginMessage loginError'>Operation not permitted in preview version of this product.</div>";
        } else {
            //check if user has proper access + file creator
            $sql = "SELECT id,userID FROM {$db_pr}files WHERE id='" . $id . "'";
            $result = mysqli_query($mysqli,$sql) or die("Error occurred - tried  to get file info.");
            $rrr = mysqli_fetch_assoc($result);
            if ($_SESSION["idUser"] != $rrr["userID"] && stristr($access, "l") != true) {
                //if user accessed this page directly from URL and he doesn't have right to edit any file
                $msg = "<div class='loginMessage loginError'>You don't have permissions to edit this file</div>";
                addLog($_SESSION["idUser"], "Tried to update info for file $title");
            } else if ($_SESSION["idUser"] == $rrr["userID"] && stristr($access, "k") != true) {
                //if user accessed page by typing URL and it is his file, but he doesnt have permissions to edit his files
                $msg = "<div class='loginMessage loginError'>You don't have permissions to edit this file</div>";
                addLog($_SESSION["idUser"], "Tried to update info for file $title");
            } else {
                //if user has permissions to edit file
                $sql = "UPDATE {$db_pr}files SET title='" . $title . "' WHERE id='" . $id . "'";
                $result = mysqli_query($mysqli,$sql) or die("Error occurred - tried  to update file.");
                $msg = "<div class='loginMessage loginSuccess'>File was successfully updated!</div>";
                addLog($_SESSION["idUser"], "Updated info for file $title");
                $notice = sendNotice('notify_edit');
                if ($notice) {
                    //send notice to system mail
                    $mailData = array(
                        "{%user%}"=>getUser($_SESSION["idUser"]),
                        "{%fileTitle%}"=>$title
                    );
                    $subject = "File was updated!";
                    sendMail(getSettings('notify_email'), $subject ,"update_file.php",$mailData);
                }
            }
           }
        }
        $sSQL = "SELECT id,title FROM {$db_pr}files WHERE id='" . $id . "'";
        $result = mysqli_query($mysqli,$sSQL) or die("err: " . mysqli_error($mysqli) . $sSQL);
        if ($row = mysqli_fetch_assoc($result)) {
            foreach ($row as $key => $value) {
                $$key = $value;
            }
        }
        mysqli_free_result($result);
        include "includes/header.php"
        ?>
        <div id="content-main">
            <h2>Edit File</h2>
            <a id="btnAdd" class="btn btn-primary rFloat " href="files.php">Back to list</a>

            <div class="clear"></div>
            <strong><?php echo $msg; ?></strong>

            <form action="edit_file.php" enctype="multipart/form-data" method="post" name="ff1" class="form-horizontal inner-form">
                <input value="yes" name="edit_file" type="hidden"/>
                <input value="<?php echo $id; ?>" name="id" type="hidden"/>

                <div class="form-group">
                    <label for="title" class="col-sm-6 control-label">File Title:</label>

                    <div class="col-sm-6">
                        <input class="form-control" name="title" type="text" placeholder="File Title"
                               value="<?php echo $title ?>">
                    </div>
                </div>
                <div class="form-group">
                    <div class="col-sm-offset-6 col-sm-4">
                        <button type="submit" class="btn btn-primary  btn-block">Rename File</button>
                    </div>
                </div>
                <br/>
            </form>
        </div>
        </div>
        <?php include "includes/footer.php";
    }
} ?>