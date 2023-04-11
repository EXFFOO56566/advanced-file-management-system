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
    if ($level == "admin") {
        //request all neccessary variables for user update action.
        $id          = (!empty($_REQUEST["id"])) ? strip_tags(str_replace("'", "`", $_REQUEST["id"])) : '';
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
        $_accesslevel = (!empty($_REQUEST["_accesslevel"])) ? $_REQUEST["_accesslevel"] : "";
        //"edit user" action processing.
        if (!empty($_REQUEST["edit_user"]) && $_REQUEST["edit_user"] == "yes" && !empty($username) && !empty($id)) {
        if($demo){
            $msg = "<div class='loginMessage loginError'>Operation not permitted in preview version of this product.</div>";
        } else {
            if (!empty($username) && !empty($email)) {
                if (is_array($_POST["extensions"]) == true) {
                    $extensionsStr = implode(",", $_POST["extensions"]);
                } else {
                    $extensionsStr = $_POST["extensions"];
                }
                if (is_array($_POST["upload_dirs"]) == true) {
                    $foldStr = implode(",", $_POST["upload_dirs"]);
                } else {
                    $foldStr = $_POST["upload_dirs"];
                }
                if (is_array($_POST["accesslevel2"]) == true) {
                    $accesslevelStr = implode("", $_POST["accesslevel2"]);
                } else {
                    $accesslevelStr = $_POST["accesslevel2"];
                }
                if ($demo) {
                    $msg .= "<div class='loginMessage loginError'>Can't update information in preview version of this script.</div>";
                } else {
                    if($_accesslevel=="abcdefghijklmnopqrstuvwxyz" && $accesslevelStr!="tgihjklm" && $id!="1"){ /*case when user was set as admin and now admin rights are revoked*/ }
                    else if ($id == "1"){$_accesslevel=="abcdefghijklmnopqrstuvwxyz";/*can't change permissions of main admin*/}
                    $sql = "UPDATE {$db_pr}users SET username='" . $username . "',email='" . $email . "',accesslevel='" . $accesslevelStr . "',extensions='" . $extensionsStr . "',quota='" . $quota . "',filesize='" . $filesize . "',upload_dir='" . $upload_dir . "',upload_dirs='" . $foldStr . "',active='" . $active . "' WHERE id='" . $id . "'";
                    $result = mysqli_query($mysqli,$sql) or die("Error occurred - tried  to update user.");
                }
                if ($id == "1") {
                    $sql = "UPDATE {$db_pr}users SET accesslevel='abcdefghijklmnopqrstuvwxyz',active='1' WHERE id='1'";
                    $result = mysqli_query($mysqli,$sql) or die("Error occurred - tried  to update user.");
                }
                if (!empty($password)) {
                    if ($demo) {
                        $msg .= "<div class='loginMessage loginError'>Can't update admin password in preview version of this script.</div>";
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
                addLog($_SESSION["idUser"], "Updated info for user $username");
                if ($admin) {
                    $sql = "UPDATE {$db_pr}users SET accesslevel='abcdefghijklmnopqrstuvwxyz' WHERE id='" . $id . "'";
                    $result = mysqli_query($mysqli,$sql) or die("Error occurred - tried  to update user.");
                    addLog($_SESSION["idUser"], "User \'$username\' has been set as admin.");
                    $msg .= "<div class='loginMessage loginSuccess'>User '$username' has been set as admin.!</div>";
                }
            }
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
            function chkExtensions(formname, checktoggle) {
                var checkboxes = new Array();
                checkboxes = document[formname].getElementsByTagName('input');
                for (var i = 0; i < checkboxes.length; i++) {
                    if (checkboxes[i].type == 'checkbox') {
                        if (checkboxes[i].className == 'chkExtension') {
                            checkboxes[i].checked = checktoggle;
                            if (checktoggle) {
                                $(checkboxes[i]).attr("checked", "checked").parent().addClass('active');
                            } else {
                                $(checkboxes[i]).removeAttr("checked").parent().removeClass('active');
                            }
                        }
                    }
                }
            }
            function chkFolders(checktoggle) {
                $(".folder").find("input[type='checkbox']").each(function () {
                    if (checktoggle) {
                        $(this).attr("checked", "checked").parent().addClass("active");
                    } else {
                        $(this).removeAttr("checked").parent().removeClass("active");
                    }
                })
            }
            function chkUploadDirectory(formname, checktoggle) {
                var checkboxes = new Array();
                checkboxes = document[formname].getElementsByTagName('input');
                for (var i = 0; i < checkboxes.length; i++) {
                    if (checkboxes[i].type == 'checkbox') {
                        if (checkboxes[i].className == 'chkUploadDir') {
                            checkboxes[i].checked = checktoggle;
                        }
                    }
                }
            }
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
        <script type="text/javascript" src='js/password_strength_plugin.js'></script>
        <link rel="stylesheet" type="text/css" href="css/passchecker.css">
        <script>
            $(document).ready(function () {
                $(".password_adv").passStrength({
                    shortPass: "top_shortPass",
                    badPass: "top_badPass",
                    goodPass: "top_goodPass",
                    strongPass: "top_strongPass",
                    baseStyle: "top_testresult",
                    userid: ".user_id_adv",
                    messageloc: 1
                });
            });
        </script>


        <div id="content-main">
        <h2>Edit User</h2>
            <div class="clear"></div>
        <?php echo $msg; ?>

        <form action="" enctype="multipart/form-data" method="post" name="ff1" class="form-horizontal inner-form">
        <input value="yes" name="edit_user" type="hidden"/>
        <input value="<?php echo $id; ?>" name="id" type="hidden"/>
        <input value="<?php echo $accesslevel2; ?>" name="_accesslevel" type="hidden"/>

        <div class="form-group">
            <label for="username" class="col-sm-4 control-label">Username:</label>

            <div class="col-sm-8">
                <input class="form-control user_id_adv" name="username" type="text" id="username"
                       value="<?php echo $username ?>"
                       placeholder="Username" <?php echo ($id != $_SESSION['idUser']) ? "" : "readonly" ?>>
            </div>
        </div>
        <?php if ($id != $_SESSION['idUser']) { ?>
            <div class="form-group">
                <label for="active" class="col-sm-4 control-label">Status:</label>

                <div class="col-sm-8">
                    <select name="active" id="active" class="form-control">
                        <option value="">Please Select</option>
                        <?php echo $user_stat; ?>
                    </select>
                </div>
            </div>
        <?php }else{ ?>
        <input type="hidden" name="active" value="<?php echo($active)?>">
        <?}?>

        <div class="form-group">
            <label for="password" class="col-sm-4 control-label">Password:</label>

            <div class="col-sm-7">
                <input class="form-control password_adv" name="password" type="text" id="password"
                       placeholder="Password">
            </div>
                    <span onclick="randomPassword();" class="updatePass">&nbsp&nbsp<img src="images/icon_refresh.png"/></span>
        </div>
        <?php if ($id != $_SESSION['idUser']) { ?>
            <div class="clear"></div>
            <div class="form-group">
                <div class="col-sm-offset-4 col-sm-8">
                    <div class="checkbox">
                        <label>
                            <input type="checkbox" name="sendpass"/>Send password to user upon save
                        </label>
                    </div>
                </div>
            </div>
        <?php } ?>
        <div class="clear"></div>
        <div class="form-group">
            <label for="email" class="col-sm-4 control-label">Email:</label>

            <div class="col-sm-8">
                <input class="form-control" name="email" type="email" id="email" placeholder="Email"
                       value="<?php echo $email ?>">
            </div>
        </div>
        <div class="clear"></div>
        <div class="form-group">
            <label for="email" class="col-sm-4 control-label">Permissions:</label>

            <div class=" col-sm-8">
                <div class="checkbox">
                    <label><input type="checkbox" name="accesslevel2[]"
                                  value="t" <?php if (stristr($accesslevel2, "t")) {
                            echo "checked";
                        } ?>/>Can view only own files. <img src="images/info.png" height="18" width="18" class="tipTip" title="If selected, user will see only files uploaded by him/her"></label>
                </div>
                <div class="checkbox">
                    <label> <input type="checkbox" name="accesslevel2[]"
                                   value="g" <?php if (stristr($accesslevel2, "g")) {
                            echo "checked";
                        } ?>/> Can upload files. <img src="images/info.png" height="18" width="18" class="tipTip" title="If selected, user will be able to upload files in assigned directories"></label>
                </div>
                <div class="checkbox">
                    <label> <input type="checkbox" name="accesslevel2[]"
                                   value="i" <?php if (stristr($accesslevel2, "i")) {
                            echo "checked";
                        } ?>/> Can view everyone's files. <img src="images/info.png" height="18" width="18" class="tipTip" title="If selected, user will see all files in directories he/she is assigned to."></label>
                </div>
                <div class="checkbox">
                    <label> <input type="checkbox" name="accesslevel2[]"
                                   value="h" <?php if (stristr($accesslevel2, "h")) {
                            echo "checked";
                        } ?>/> Can delete own files. <img src="images/info.png" height="18" width="18" class="tipTip" title="If selected, user will be able to delete files which were uploaded by him/her"></label>
                </div>
                <div class="checkbox">
                    <label> <input type="checkbox" name="accesslevel2[]"
                                   value="j" <?php if (stristr($accesslevel2, "j")) {
                            echo "checked";
                        } ?>/> Can delete any file. <img src="images/info.png" height="18" width="18" class="tipTip" title="If selected, user will be able to delete any files in assigned directories"></label>
                </div>
                <div class="checkbox">
                    <label> <input type="checkbox" name="accesslevel2[]"
                                   value="k" <?php if (stristr($accesslevel2, "k")) {
                            echo "checked";
                        } ?>/> Can edit own files. <img src="images/info.png" height="18" width="18" class="tipTip" title="If selected, user can rename own files"></label>
                </div>
                <div class="checkbox">
                    <label> <input type="checkbox" name="accesslevel2[]"
                                   value="l" <?php if (stristr($accesslevel2, "l")) {
                            echo "checked";
                        } ?>/> Can edit all files. <img src="images/info.png" height="18" width="18" class="tipTip" title="If selected, user can rename any file in assigned directories"></label>
                </div>
                <div class="checkbox">
                    <label> <input type="checkbox" name="accesslevel2[]"
                                   value="m" <?php if (stristr($accesslevel2, "m")) {
                            echo "checked";
                        } ?>/> Can share any file. <img src="images/info.png" height="18" width="18" class="tipTip" title="If selected, sharing files will be enabled for this user"></label>
                </div>
            </div>
        </div>
        <div class="form-group">
            <label for="email" class="col-sm-4 control-label">Extensions:
                <div class="subLabel">
                    <a href="javascript:;" onclick="javascript:chkExtensions('ff1', true);">check all</a>
                    | <a href="javascript:;" onclick="javascript:chkExtensions('ff1', false);">uncheck all</a>
                </div>
            </label>

            <div class="col-sm-8">
                <?php echo $extStr; ?>
            </div>
        </div>
        <div class="clear"></div>
        <div class="form-group">
            <label for="quota" class="col-sm-4 control-label">Disk Size Quota:</label>

            <div class="col-sm-3">
                <input class="form-control" name="quota" type="text" id="quota" placeholder="MB"
                       value="<?php echo $quota ?>"/>
            </div>
            <div class="icon-info">MB <img src="images/info.png" height="18" width="18" class="tipTip" title="Maximum allocated storage space for this user."></div>
        </div>
        <div class="clear"></div>
        <div class="clear"></div>
        <div class="form-group">
            <label for="quota" class="col-sm-4 control-label">Maximum File Size:</label>

            <div class="col-sm-3">
                <input class="form-control" name="filesize" type="text" id="filesize" placeholder="MB"
                       value="<?php echo $filesize ?>"/>
            </div>
            <div class="icon-info">MB
                <img src="images/info.png" height="18" width="18" class="tipTip" title="Maximum file size per each file uploaded."></div>
        </div>
        <div class="clear"></div>
        <div class="form-group folder">
            <label for="email" class="col-sm-4 control-label">Directories:
                <div class="subLabel">
                    <a href="javascript:;" onclick="javascript:chkFolders( true);">check all</a>
                    | <a href="javascript:;" onclick="javascript:chkFolders( false);">uncheck all</a>
                </div>
            </label>

            <div class="col-sm-8">

                <?php getFoldersCheckForUser($upload_dirs)?>
            </div>
        </div>
        <div class="clear"></div>
        <div class="form-group inline-form" >
            <div class="col-sm-offset-4 col-sm-3">
                <button type="submit" class="btn btn-primary btn-block">Submit</button>
            </div>
            <?php if ($accesslevel2 != 'abcdefghijklmnopqrstuvwxyz') { ?>
                <div class="col-sm-offset-1 col-sm-4">
                    <button class="btn btn-danger btn-block" name="admin" type="submit">Set As admin
                    </button>
                </div>
            <?php } ?>
        </div>
        </form>
        </div>

        <div class="clear"></div>
        </div>
        <?php include "includes/footer.php";
    }
} ?>