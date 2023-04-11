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
//LOGIN VARIABLES
$username = (!empty($_REQUEST['username'])) ? strip_tags(str_replace("'", "`", $_REQUEST['username'])) : '';
$password = (!empty($_REQUEST['password'])) ? strip_tags(str_replace("'", "`", $_REQUEST['password'])) : '';
// LOGIN
if (!empty($_REQUEST["login"]) && $_REQUEST['login'] == "yes") {
    // Validate recaptcha
    if (isset($_SESSION['captcha'])) {
        if (isset($_REQUEST['captcha']) && trim(strtolower($_REQUEST['captcha'])) != $_SESSION['captcha']) {
            $captcha_message        = "";
            $captcha_valid          = false;
            $_SESSION['reqCaptcha'] = true;
        } else {
            $captcha_valid = true;
        }
    }
    if ($_SESSION['reqCaptcha'] == true and $captcha_valid == false) {
        $msg .= " Invalid captcha";
    } else {
        if ($username == "" || $password == "") {
            $_SESSION['reqCaptcha'] = true;
            $msg                    = $msg . "Empty username and/or password";
        } else {
            $sSQL = "SELECT * FROM `{$db_pr}users` WHERE `username`='" . $username . "' AND active='1'";
            $result = mysqli_query($mysqli,$sSQL) or die("Invalid query: " . mysqli_error($mysqli));
            if (mysqli_num_rows($result) > 0) {
                $row = mysqli_fetch_assoc($result);
                if (md5($password) != $row["password"]) {
                    $_SESSION['reqCaptcha'] = true;
                    $msg                    = $msg . "Wrong username and/or password";
                    addLog($row["id"], "Error during login. Wrong password.");
                } else {
                    if ((isset($_SESSION['captcha']) and $captcha_valid == true) or (!isset($_SESSION['captcha']))) {
                        $_SESSION['idUser']      = $row["id"];
                        $_SESSION['username']    = $row["username"];
                        $_SESSION['accesslevel'] = $row["accesslevel"];
                        $_SESSION['logged_in']   = true;
                        $sSQL                    = "UPDATE `{$db_pr}users` SET `last_login`=NOW() WHERE `id`='" . $row["id"] . "'";
                        mysqli_query($mysqli,$sSQL) or die("Invalid query: " . mysqli_error($mysqli));
                        addLog($row["id"], "Successfully logged in.");
                    }
                }
            } else {
                $_SESSION['reqCaptcha'] = true;
                $msg                    = $msg . " Wrong username (username) and/or password.";
            }
        }
    }
}
if (isset($_SESSION["logged_in"]) && $_SESSION["logged_in"] == true) {
    header("Location: main.php");
    exit();
} else {
include "includes/header_static.php";
    ?>
<div class="contentPage">
    <h1 class="header">Advanced File Management 3</h1>

    <div class="loginForm">
        <?php if ($msg != '') { ?>
            <div class="loginMessage loginError"> <?php echo $msg; ?></div>
        <?php } ?>

        <form class="form-horizontal" role="form" name="ff1" method="post">
            <input type="hidden" name="login" value="yes"/>
            <div class="form-group">
                <label for="inputEmail3" class="sr-only">Username</label>
                <div class="col-sm-12">
                    <input type="text" class="form-control" id="inputEmail3" placeholder="Username" name="username" value="<?php echo $username ?>" tabindex="1">
                </div>
            </div>
            <div class="form-group">
                <label for="inputPassword3" class="sr-only">Password</label>
                <div class="col-sm-12">
                    <input type="password" class="form-control" id="inputPassword3" placeholder="Password" name="password" tabindex="2">
                </div>
            </div>
            <?php if (isset($_SESSION['reqCaptcha'])) { ?>
                <div class="captchaCont">
                    <div class="captchaLeft">
                        <img src="includes/captcha.php" id="captcha" width="250" height="40"/>
                    </div>
                    <div class="captchaRight">
                        <a href="#" onclick="document.getElementById('captcha').src='includes/captcha.php?'+Math.random();document.getElementById('captcha-form').focus();" id="change-image"><img src="images/icon_refresh.png"/></a>
                    </div>
                </div>
                <div class="form-group">
                    <label for="inputPassword3" class="sr-only">Enter captcha</label>
                    <div class="col-sm-12">
                        <input type="text" name="captcha" id="captcha-form" autocomplete="off" placeholder="Enter captcha" class="form-control" tabindex="3"/>
                    </div>
                </div>
            <?php } ?>
            <div class="form-group">
                <div class="col-sm-6">
                    <?php
                    $allow_reg = getReg();
                    if ($allow_reg) { ?>
                        <a href="user_registration.php">create account</a>
                    <?php } ?>
                </div>
                <div class="col-sm-6 text-right">
                    <a href="forgot_password.php">forgot password?</a>
                </div>
            </div>
            <div class="form-group">
                <div class="col-sm-12">
                    <button type="submit" class="btn btn-primary btn-lg btn-block">Sign in</button>
                </div>
            </div>
        </form>
    </div>
    <?php include "includes/footer.php";
} ?>