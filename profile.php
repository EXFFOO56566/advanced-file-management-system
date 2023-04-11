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
    ######################### DO NOT MODIFY (UNLESS SURE) END ########################
    //show page only if admin access level
    //request all neccessary variables for user update action.
    $id          = $_SESSION['idUser'];
    $username    = (!empty($_REQUEST["username"])) ? strip_tags(str_replace("'", "`", $_REQUEST["username"])) : '';
    $password    = (!empty($_REQUEST["password"])) ? strip_tags(str_replace("'", "`", $_REQUEST["password"])) : '';
    $email       = (!empty($_REQUEST["email"])) ? strip_tags(str_replace("'", "`", $_REQUEST["email"])) : '';
    $quota       = (!empty($_REQUEST["quota"])) ? strip_tags(str_replace("'", "`", $_REQUEST["quota"])) : '1';
    $filesize    = (!empty($_REQUEST["filesize"])) ? strip_tags(str_replace("'", "`", $_REQUEST["filesize"])) : '1';
    $active      = (!empty($_REQUEST["active"])) ? strip_tags(str_replace("'", "`", $_REQUEST["active"])) : '';
    $upload_dir  = (!empty($_REQUEST["upload_dir"])) ? strip_tags(str_replace("'", "`", $_REQUEST["upload_dir"])) : 'uploads';
    $upload_dirs = (!empty($_REQUEST["upload_dirs"])) ? $_REQUEST["upload_dirs"] : '';
    $admin       = (isset($_REQUEST["admin"])) ? 1 : 0;
    $sendpass    = (!empty($_REQUEST["sendpass"])) ? $_REQUEST["sendpass"] : 0;
    //"edit user" action processing.
    if (!empty($_REQUEST["edit_user"]) && $_REQUEST["edit_user"] == "yes" && !empty($username) && !empty($id)) {
        if (!empty($username) && !empty($email)) {
            if ($id == "1" && $demo) {
                //$msg .= "<div class='loginMessage loginError'>Can't update admin information in live demo version of this script.</div>";
            } else {
                $sql = "UPDATE {$db_pr}users SET email='" . $email . "' WHERE id='" . $id . "'";
                $result = mysqli_query($mysqli,$sql) or die("Error occurred - tried  to update user.");
            }
            if (!empty($password)) {
                if ($demo) {
                    $msg .= "<div class='loginMessage loginError'>Can't update password in preview version of this script.</div>";
                } else {
                    $sql = "UPDATE {$db_pr}users SET password='" . md5($password) . "' WHERE id='" . $id . "'";
                    $result = mysqli_query($mysqli,$sql) or die("Error occurred - tried  to update users password.");
                    if ($sendpass) {
                        //creating message for sending
                        $subject = "Password changed";
                        $mailData = array(
                            "{%username%}"=>$username,
                            "{%linkToScript%}"=>"http://{$_SERVER['SERVER_NAME']}{$script_dir}",
                            "{%password%}"=>$password,
                            "{%email%}"=>$email
                        );
                        sendMail(getSettings('notify_email'), $subject, "password_changed.php", $mailData);
                    }
                }
            }
            $msg .= "<div class='loginMessage loginSuccess'>User was successfully updated!</div>";
            addLog($_SESSION["idUser"], "User $username update profile");
        }
    }
    //select editable user's info and show it for editor.
    $sSQL = "SELECT id,username,email,filesize,quota,extensions,accesslevel as accesslevel2,upload_dir,upload_dirs,active FROM {$db_pr}users WHERE id='" . $id . "'";
    $result = mysqli_query($mysqli,$sSQL) or die("err: " . mysqli_error($mysqli) . $sSQL);
    if ($row = mysqli_fetch_assoc($result)) {
        foreach ($row as $key => $value) {
            $$key = $value;
        }
    }
    mysqli_free_result($result);
    $extensionsArr = explode(",", $extensions);
    $upload_dirs   = explode(",", $upload_dirs);
    //new in version 1.2
    $extStr         = "";
    $new_extensions = getExtensions();
    $cc             = 1;
    for ($i = 0; $i < count($new_extensions); $i++) {
        $extStr .= "<label class=\"checkbox-inline fixed\"><input type='checkbox' name='extensions[]' class='chkExtension' value=\"" . $new_extensions[$i] . "\" ";
        if (is_array($extensionsArr)) {
            if (in_array($new_extensions[$i], $extensionsArr)) {
                $extStr .= " checked ";
            }
        } else if ($extensions == $new_extensions[$i]) {
            $extStr .= " checked ";
        }
        $extStr .= " />." . $new_extensions[$i] . "</label>";
        $cc++;
        if ($cc == 9) {
            $extStr .= "";
            $cc = 1;
        }
    }
    //new in version 1.4, user status
    $user_stat = "";
    $user_stat .= "<option value='2' " . ($active == "2" ? "selected" : "") . ">Not Active</option>";
    $user_stat .= "<option value='1' " . ($active == "1" ? "selected" : "") . ">Active</option>";
    include "includes/header.php";
    ?>

    <script type="text/javascript">
        function randomPassword() {
            chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890";
            pass = " ";
            for (x = 0; x < 10; x++) {
                i = Math.floor(Math.random() * 62);
                pass += chars.charAt(i);
            }
            ff1.password.value = pass;
            $(".top_testresult").remove();
        }
    </script>
    </head>
    <div id="content-main">
        <h2>Edit Profile</h2>
        <div class="clear"></div>
       <?php echo $msg; ?>
        <form action="" enctype="multipart/form-data" method="post" name="ff1" class="form-horizontal inner-form">
            <input value="yes" name="edit_user" type="hidden"/>
            <input value="<?php echo $id; ?>" name="id" type="hidden"/>

            <div class="form-group">
                <label for="username" class="col-sm-4 control-label">Username:</label>

                <div class="col-sm-8">
                    <input class="form-control user_id_adv" name="username" type="text" id="username"
                           value="<?php echo $username ?>" placeholder="Username" readonly>
                </div>
            </div>
            <div class="form-group">
                <label for="password" class="col-sm-4 control-label">Password:</label>

                <div class="col-sm-7">
                    <input class="form-control password_adv" name="password" type="text" id="password"
                           placeholder="Password">
                </div>
                    <span onclick="randomPassword();"  class="updatePass">&nbsp&nbsp<img src="images/icon_refresh.png"/></span>
            </div>
            <div class="clear"></div>
            <div class="form-group">
                <div class="col-sm-offset-4 col-sm-8">
                    <div class="checkbox">
                        <label>
                            <input type="checkbox" name="sendpass"/>Send password to your email
                        </label>
                    </div>
                </div>
            </div>
            <div class="clear"></div>
            <div class="form-group">
                <label for="email" class="col-sm-4 control-label">Email:</label>

                <div class="col-sm-8">
                    <input class="form-control" name="email" type="email" id="email" placeholder="Email"
                           value="<?php echo $email ?>">
                </div>
            </div>
            <div class="clear"></div>
            <div class="form-group inner-form" >
                <div class="col-sm-offset-4 col-sm-4">
                    <button type="submit" class="btn btn-primary btn-block">Save</button>
                </div>
            </div>
        </form>
    </div>
    <div class="clear"></div>
    </div>
    <?php include "includes/footer.php";
} ?>