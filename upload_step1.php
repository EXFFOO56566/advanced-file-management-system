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
$disable_button = false;
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
    //new in version 1.3, upload directories
    if ($level != "user") {
        $upload_dirs = "";
        $query       = "SELECT * FROM {$db_pr}folders ORDER BY name ASC";
        $result = mysqli_query($mysqli,$query) or die("error getting folders from database");
        while ($rr = mysqli_fetch_assoc($result)) {
            $upload_dirs .= "<option value='" . $rr["name"] . "' " . ($upload_dir == $rr["name"] ? "selected" : "") . ">" . $rr["name"] . "</option>";
        }
    } else {
        if (!stristr($access, "i")) { //if user cant see all files
            $q   = "SELECT upload_dirs FROM {$db_pr}users WHERE id='" . $_SESSION["idUser"] . "'";
            $res = mysqli_query($mysqli,$q);
            $rr  = mysqli_fetch_assoc($res);
            if (!empty($rr["upload_dirs"])) {
                $folders = str_replace(',', "','", $rr["upload_dirs"]);
                $query   = "SELECT * FROM {$db_pr}folders WHERE id IN ('" . $folders . "') ORDER BY name ASC";
                $result = mysqli_query($mysqli,$query) or die("error getting folders from database");
                while ($rr = mysqli_fetch_assoc($result)) {
                    $upload_dirs .= "<option value='" . $rr["name"] . "' " . ($upload_dir == $rr["id"] ? "selected" : "") . ">" . $rr["name"] . "</option>";
                }
            } else {
                $disable_button = true;
            }
        } else {
            $query = "SELECT * FROM {$db_pr}folders ORDER BY name ASC";
            $result = mysqli_query($mysqli,$query) or die("error getting folders from database");
            while ($rr = mysqli_fetch_assoc($result)) {
                $upload_dirs .= "<option value='" . $rr["name"] . "' " . ($upload_dir == $rr["id"] ? "selected" : "") . ">" . $rr["name"] . "</option>";
            }
        }
    }
    $sSQL = "SELECT id,username,filesize,quota,extensions FROM {$db_pr}users WHERE id='" . $_SESSION["idUser"] . "'";
    $result = mysqli_query($mysqli,$sSQL) or die("err: " . mysqli_error($mysqli) . $sSQL);
    if ($row = mysqli_fetch_assoc($result)) {
        foreach ($row as $key => $value) {
            $$key = $value;
        }
    }
    mysqli_free_result($result);
    include "includes/header.php";
    ?>

    <div id="content-main">
        <div class="contentPage">
            <h2>Select Folder</h2>
            <div class="clear"></div>
            <div class="clear"></div>
                <form action="upload_step2.php" enctype="multipart/form-data" method="get" name="ff1"
                      class="form-horizontal center-form">
                    <h4>Please select folder to upload files.</h4>

                    <div class="form-group">
                        <?php if (!$disable_button) { ?>


                        <select name="upload_dir" id="upload_dir" class="form-control">
                            <?php getFoldersDrop()?>
                        </select>

                        <?php } ?>
                    </div>
                    <div class="clear"></div>
                    <div class="form-group">
                        <div class="col-sm-offset-3 col-sm-5">
                            <button type="submit" class="btn btn-primary btn-block">Submit</button>
                        </div>
                    </div>
                </form>

        </div>
    </div>
    </div>
    <?php include "includes/footer.php";
} ?>