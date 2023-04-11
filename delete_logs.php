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
    if ($level == "admin") {
        if (!empty($_REQUEST["edit_settings"]) && $_REQUEST["edit_settings"] == "yes") {
            if (!empty($_POST["delete"]) && $_POST["delete"] == "1") {
                //delete logs
                $query = "DELETE FROM {$db_pr}activitylogs";
                if (mysqli_query($mysqli,$query)) {
                    $msg = "<div class='loginMessage loginSuccess'>Success! All logs deleted.</div>";
                } else {
                    $msg = "<div class='loginMessage loginError'>Something went terribly wrong! Logs were not deleted.</div>";
                }
            } else {
                header("Location: logs.php");
            }
        }
        include "includes/header.php";
        ?>

        <div id="content-main">
            <h2>Activity Logs Management</h2>
            <div class="clear"></div>
            <strong><?php echo $msg; ?></strong>

            <form  class="form-horizontal inner-form" name="ff1" method="post" enctype="multipart/form-data"
                  action="">
                <input value="yes" name="edit_settings" type="hidden"/>
                <input value="<?php echo $id; ?>" name="id" type="hidden"/>

                <div class="form-group">
                    <label class="col-sm-7 control-label" for="notify_delete">Are you sure you want to empty activity
                        logs data?</label>
                    <label class="checkbox-inline">
                        <input name="delete" type="radio" value="1"/>
                        Yes&nbsp;&nbsp;
                    </label>
                    <label class="checkbox-inline">
                        <input name="delete" type="radio" value="2"/>
                        No
                    </label>
                </div>
                <div class="form-group">
                    <div class="col-sm-offset-6 col-sm-3">
                        <button class="btn btn-primary btn-block" type="submit">Submit</button>
                    </div>
                </div>
                <div class="clear"></div>
            </form>
        </div>

        </div>
        <?php include "includes/footer.php"; ?>
    <?php } else {
        header("Location: main.php");
    }
} ?>