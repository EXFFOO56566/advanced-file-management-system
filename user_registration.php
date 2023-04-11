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
//if new user registrations allowed
$allow_reg = getReg();
if ($allow_reg) {
    //REGISTER VARIABLES
    $username = (!empty($_REQUEST['username'])) ? strip_tags(str_replace("'", "`", $_REQUEST['username'])) : '';
    $password = (!empty($_REQUEST['password'])) ? strip_tags(str_replace("'", "`", $_REQUEST['password'])) : '';
    $email    = (!empty($_REQUEST['email'])) ? strip_tags(str_replace("'", "`", $_REQUEST['email'])) : '';
    // REGISTER
    if (!empty($_REQUEST["register"]) && $_REQUEST['register'] == "yes") {
        if ($username == "" || $password == "" || $email == "") {
            $msg = '<div class="loginMessage loginError">Empty username, password or email.</div>';
        } else {
            $sSQL = "SELECT * FROM `{$db_pr}users` WHERE `username`='" . $username . "' OR email='" . $email . "'";
            $result = mysqli_query($mysqli,$sSQL) or die("Invalid query: " . mysqli_error($mysqli));
            if (mysqli_num_rows($result) > 0) {
                $msg = '<div class="loginMessage loginError">User with such username/email already exists.</div>';
            } else {
                $auto_approve            = autoApprove();
                $auto_create_user_folder = getSettings('auto_create_user_folder');
                if ($auto_approve) {
                    $active     = "1";
                    $ap         = "Yes";
                    $extensions = getSettings('extensions');
                    $filesize   = getSettings('filesize');
                    $upload_dir = getSettings('upload_dirs');
                    $quota      = getSettings('quota');
                    $query      = "INSERT INTO {$db_pr}users (dateCreated,username,password,email,extensions,quota,filesize,active,accesslevel,upload_dirs)
                    VALUES ('" . date("Y-m-d H:i:s") . "','" . $username . "','" . md5($password) . "','" . $email . "','{$extensions}','{$quota}','{$filesize}','" . $active . "','ghk','{$upload_dir}')";
                } else {
                    $active = "2";
                    $ap     = "No";
                    $query  = "INSERT INTO {$db_pr}users (dateCreated,username,password,email,extensions,quota,filesize,active,accesslevel,upload_dir)
                    VALUES ('" . date("Y-m-d H:i:s") . "','" . $username . "','" . md5($password) . "','" . $email . "','JPG','1','1','" . $active . "','ghk','uploads')";
                }
                if (mysqli_query($mysqli,$query)) {
                    $newID = mysqli_insert_id($mysqli);
                    //send email to admin
                    //creating message for sending

                    $mailData = array(
                        "{%username%}"=>$username,
                        "{%linkToScript%}"=>"http://{$_SERVER['SERVER_NAME']}{$script_dir}",
                        "{%password%}"=>$password,
                        "{%email%}"=>$email,
                        "_auto_approve"=>$auto_approve
                    );

                    $subject = "New User Registration";

                    sendMail(getSettings('notify_email'), $subject, "registration_admin.php", $mailData);
                    //-----> send notification end
                    $msg = '<div class="loginMessage loginSuccess">Registration was successful! Please wait for your application to be reviewed by administrator.</div>';
                    //send email to user
                    //creating message for sending

                    $subject = "Registration Confirmation";

                    sendMail($email, $subject, "registration_user.php", $mailData);
                    //-----> send notification end
                    if ($auto_approve) {
                        $_SESSION['idUser']      = $newID;
                        $_SESSION['username']    = $username;
                        $_SESSION['accesslevel'] = "ghk";
                        $_SESSION['logged_in']   = true;
                        $sSQL                    = "UPDATE `{$db_pr}users` SET `last_login`=NOW() WHERE `id`='" . $newID . "'";
                        mysqli_query($mysqli,$sSQL) or die("Invalid query: " . mysqli_error($mysqli));
                        addLog($newID, "Successfully created new account with email " . $email . ". Auto-approve:" . $ap);
                        header("Location: index.php");
                    }
                    if ($auto_create_user_folder) {
                        $name = str_replace(" ", "-", strtolower(trim($username)));
                        if ($name != '' && preg_match('/[^\w\d_-]/si', $name)) {
                            $name = str_replace(' ', '-', $name);
                            if (preg_match('/[^\w\d_-]/si', $name)) {
                                $name = preg_replace('/[^\w\d_-]/si', '', $name);
                            }
                        }
                        $sql = "SELECT * FROM {$db_pr}folders WHERE name='" . $name . "'";
                        $result = mysqli_query($mysqli,$sql) or die("oopsy, error selecting folder from database for comparison");
                        if (mysqli_num_rows($result) > 0) {
                            $folderData = mysqli_fetch_assoc($result);
                            $folderID   = $folderData['id'];
                        }
                        if (!empty($name)) {
                            //also create mkdir folder in script base, and make it writable
                            $thisdir = getcwd();
                            @mkdir($thisdir . "/" . $name, 0777);
                            if (empty($folderID)) {
                                $sql = "INSERT INTO {$db_pr}folders (dateCreated,name,parentID) VALUES (NOW(),'" . $name . "','0')";
                                $result = mysqli_query($mysqli,$sql) or die("Error occurred - tried  to insert new folder.");
                                $folderID = mysqli_insert_id($mysqli);
                            }
                            $sql = "UPDATE {$db_pr}users SET upload_dirs='{$folderID}' WHERE id='$newID'";
                            $result = mysqli_query($mysqli,$sql) or die("Error occurred - tried  to insert new folder.");
                        }
                    }
                }
            }
        }
    }
    if ($_SESSION["logged_in"] == true) {
        header("Location: main.php");
    } else {
        include "includes/header_static.php";
        ?>

        <div class="contentPage">
            <h1 class="header">Advanced File Management 3</h1>

            <div class="loginForm">
                <?php if ($msg != '') { ?>
                    <?php echo $msg; ?>
                <?php } ?>

                <form class="form-horizontal" role="form" name="ff1" method="post">
                    <input type="hidden" value="yes" name="register"/>

                    <div class="form-group">
                        <label for="username" class="sr-only">Username</label>

                        <div class="col-sm-12">
                            <input type="text" class="form-control" id="username" placeholder="Username" name="username"
                                   value="<?php echo $username ?>">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="password" class="sr-only">Password</label>

                        <div class="col-sm-12">
                            <input type="password" class="form-control" id="password" placeholder="Password"
                                   name="password">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="email" class="sr-only">Email</label>

                        <div class="col-sm-12">
                            <input type="text" class="form-control" id="email" placeholder="Email" name="email"
                                   value="<?php echo $email ?>">
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="col-sm-12 text-right">
                            <a href="index.php">back to login</a>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="col-sm-12">
                            <button type="submit" class="btn btn-primary btn-lg btn-block">Create Account</button>
                        </div>
                    </div>
                </form>
            </div>

            <?php include "includes/footer.php";
    }
} else {
    header("Location: index.php");
} ?>