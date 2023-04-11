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
//RETRIEVE VARIABLES
$username = (!empty($_REQUEST['username'])) ? strip_tags(str_replace("'", "`", $_REQUEST['username'])) : '';
$email = (!empty($_REQUEST['email'])) ? strip_tags(str_replace("'", "`", $_REQUEST['email'])) : '';
// RETRIEVE
if (!empty($_REQUEST["restore"]) && $_REQUEST['restore'] == "yes") {
    if ($email == "") {
        $msg = "<div class='loginMessage loginError'><span>Empty email.</span></div>";
    } else {
        $sSQL = "SELECT * FROM `{$db_pr}users` WHERE `email`='" . $email . "'";
        $result = mysqli_query($mysqli,$sSQL) or die("Invalid query: " . mysqli_error($mysqli));
        if (mysqli_num_rows($result) > 0) {
            $row = mysqli_fetch_assoc($result);
            if ($email != $row["email"]) {
                $msg = "<div class='loginMessage loginError'><strong>Error!</strong> <span>User with such email doesn't exist in our system.</span></div>";
                addLog($row["id"], "Error during password retrieving. Wrong email.");
            } else {
                $pass = $row["password"];
                $newPass = randomPassword();
                if ($row["id"] != "1" && !$demo) {
                    $sSQL = "UPDATE `{$db_pr}users` SET `password`='" . md5($newPass) . "' WHERE `id`='" . $row["id"] . "'";
                    mysqli_query($mysqli,$sSQL) or die("Invalid query: " . mysqli_error($mysqli));
                    //creating message for sending

                    $subject = "Password Reset";
                    $mailData = array(
                        "{%username%}"=>$row["username"],
                        "{%linkToScript%}"=>"http://{$_SERVER['SERVER_NAME']}{$script_dir}",
                        "{%password%}"=>$newPass
                    );

                    sendMail($row["email"], $subject, "forgot_password.php", $mailData);
                    //-----> send notification end
                    $msg = "<div class='loginMessage loginSuccess' ><strong>Success!</strong> <span>New password was set and sent to your email.</span></div>";
                    addLog($row["id"], "Successfully reset password.");
                } else {
                    $msg = "<div class='loginMessage loginError'><span>For demo purposes you can't change admins password.</span></div>";
                }
            }
        } else {
            $msg = "<div class='loginMessage loginError'><strong>Error!</strong> <span>User with such email doesn't exist in our system.</span></div>";
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
            <form class="form-horizontal" role="form" name="ff1">
                <input type="hidden" value="yes" name="restore"/>

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
                        <button type="submit" class="btn btn-primary btn-block">Reset Password</button>
                    </div>
                </div>
            </form>
        </div>
        <?php include "includes/footer.php"; } ?>