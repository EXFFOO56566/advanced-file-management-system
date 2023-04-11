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
$prefix1 = "KB";
$total_uploaded = 0;
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
    if ($level == "user") {
        $filter = "AND userID='" . $_SESSION["idUser"] . "'";
    } else {
        $filter = "";
    }
    if ($level == "user") {
        $filter2 = "AND idUser='" . $_SESSION["idUser"] . "'";
    } else {
        $filter2 = "";
    }
    $userCount = 0;
    $from  = date("Y-m-01");
    $today = date("Y-m-d");
    if ($level == "admin") {
        $sql = "SELECT COUNT(id) userCount FROM {$db_pr}users WHERE id<>'1'";
        $result = mysqli_query($mysqli,$sql) or die("error getting user count");
        $rr        = mysqli_fetch_assoc($result);
        $userCount = $rr["userCount"];
        $sql = "SELECT COUNT(id) userCount FROM {$db_pr}users WHERE id<>'1' AND dateCreated >= '{$from} 00:00:00' ";
        $result = mysqli_query($mysqli,$sql) or die("error getting user count1");
        $rr             = mysqli_fetch_assoc($result);
        $userCountMonth = $rr["userCount"];
        $sql = "SELECT COUNT(id) userCount FROM {$db_pr}users WHERE id<>'1' AND dateCreated >= '{$today} 00:00:00' ";
        $result = mysqli_query($mysqli,$sql) or die("error getting user count2");
        $rr             = mysqli_fetch_assoc($result);
        $userCountToday = $rr["userCount"];
        $sql = "SELECT SUM(quota) userQuota FROM {$db_pr}users WHERE id<>'1'";
        $result = mysqli_query($mysqli,$sql) or die("error getting user count3");
        $rr         = mysqli_fetch_assoc($result);
        $totalQuota = $rr["userQuota"];
    }
    $totalDownload      = 0;
    $totalDownloadMonth = 0;
    $totalDownloadToday = 0;
    $sql = "SELECT SUM(size) total  FROM {$db_pr}downloads WHERE 1 " . $filter . "";
    $result = mysqli_query($mysqli,$sql) or die("error getting files from db");
    if (mysqli_num_rows($result) > 0) {
        $res           = mysqli_fetch_assoc($result);
        $totalDownload = getSizeStr($res['total']);
    }
    $sql = "SELECT SUM(size) total FROM {$db_pr}downloads WHERE 1 " . $filter . "  AND date >= '{$from} 00:00:00' ";
    $result = mysqli_query($mysqli,$sql) or die("error getting files from db");
    if (mysqli_num_rows($result) > 0) {
        $res                = mysqli_fetch_assoc($result);
        $totalDownloadMonth = getSizeStr($res['total']);
    }
    $sql = "SELECT SUM(size) total FROM {$db_pr}downloads WHERE 1 " . $filter . "  AND date >= '{$today} 00:00:00' ";
    $result = mysqli_query($mysqli,$sql) or die("error getting files from db");
    if (mysqli_num_rows($result) > 0) {
        $res                = mysqli_fetch_assoc($result);
        $totalDownloadToday = getSizeStr($res['total']);
    }
    $totalSize1      = 0;
    $totalFiles      = 0;
    $totalFilesMonth = 0;
    $totalFilesToday = 0;
    $sql             = "SELECT * FROM {$db_pr}files WHERE 1 " . $filter . "";
    $result = mysqli_query($mysqli,$sql) or die("error getting files from db");
    if (mysqli_num_rows($result) > 0) {
        while ($rr = mysqli_fetch_assoc($result)) {
            $totalSize1 += $rr["size"];
            $totalFiles += 1;
        }
        if ($totalSize1 < 1048576) { //if less than 1 MB
            $prefix1        = "KB";
            $total_uploaded = $totalSize1 / 1024;
        } else {
            $prefix1        = "MB";
            $total_uploaded = $totalSize1 / 1024 / 1024;
        }
    }
    $totalSize   = 0;
    $daysinmonth = date("t");
    $first_day   = mktime(0, 0, 0, date("m"), 1, date("Y"));
    $last_day    = mktime(0, 0, 0, date("m"), date("t"), date("Y"));
    $sql         = "SELECT * FROM {$db_pr}files WHERE 1 " . $filter . " AND (dateUploaded BETWEEN '" . date("Y-m-d H:i:s", $first_day) . "' AND '" . date("Y-m-d H:i:s", $last_day) . "')";
    $result = mysqli_query($mysqli,$sql) or die("error getting files from db 2");
    if (mysqli_num_rows($result) > 0) {
        while ($rr = mysqli_fetch_assoc($result)) {
            $totalSize += $rr["size"];
            $totalFilesMonth += 1;
        }
        if ($totalSize < 1048576) { //if less than 1 MB
            $prefix2          = "KB";
            $total_this_month = $totalSize / 1024;
        } else {
            $prefix2          = "MB";
            $total_this_month = $totalSize / 1024 / 1024;
        }
    }
    $totalSize = 0;
    $today     = mktime(0, 0, 0, date("m"), date("t"), date("Y"));
    $sql       = "SELECT * FROM {$db_pr}files WHERE 1 " . $filter . " AND dateUploaded > '" . date("Y-m-d 00:00:00", strtotime("now")) . "' ";
    $result = mysqli_query($mysqli,$sql) or die("error getting files from db 2");
    if (mysqli_num_rows($result) > 0) {
        while ($rr = mysqli_fetch_assoc($result)) {
            $totalSize += $rr["size"];
            $totalFilesToday += 1;
        }
        if ($totalSize < 1048576) { //if less than 1 MB
            $prefix           = "KB";
            $total_this_today = $totalSize / 1024;
        } else {
            $prefix           = "MB";
            $total_this_today = $totalSize / 1024 / 1024;
        }
    }
    include "includes/header.php";
    ?>

    <div id="content-main">
        <div id="wrapper">
            <div class="col">
                <h2>Today</h2>
                <?php // echo $_SERVER['PHP_SELF']; ?>
                Total uploaded:
                <strong><?php echo number_format($total_this_today, 2, ".", ",") . " " . $prefix ?></strong><br/>
                Total downloaded: <strong><?php echo($totalDownloadToday) ?></strong><br/>
                Total files uploaded: <strong><?php echo($totalFilesToday) ?></strong><br/>
                Total new users: <strong><?php echo($userCountToday) ?></strong><br/>
            </div>
            <div class="col">
                <h2>This Month</h2>
                Total uploaded:
                <strong><?php echo number_format($total_this_month, 2, ".", ",") . " " . $prefix2 ?></strong><br/>
                Total downloaded: <strong><?php echo($totalDownloadMonth) ?></strong><br/>
                Total files uploaded: <strong><?php echo($totalFilesMonth) ?></strong><br/>
                Total new users: <strong><?php echo($userCountMonth) ?></strong><br/>
            </div>
            <div class="col">
                <h2>All Time Stats</h2>
                Total uploaded:
                <strong><?php echo number_format($total_uploaded, 2, ".", ",") . " " . $prefix1 ?></strong><br/>
                Total downloaded: <strong><?php echo($totalDownload) ?></strong><br/>
                Total files uploaded: <strong><?php echo($totalFiles) ?></strong><br/>
                Total new users: <strong><?php echo($userCount) ?></strong><br/>
                Total assigned storage quota: <strong><?php echo $totalQuota ?> MB</strong><br/>
            </div>
            <div class="clear"></div>
            <br>

            <div class="col">
                <h3>Today's Top 10</h3>
                <table class="stat">
                    <tr>
                        <th>&nbsp;</th>
                        <th>user</th>
                        <th>downloads</th>
                        <th>size</th>
                    </tr>
                    <?php
                    $sql = "SELECT d.*,u.username,(SELECT COUNT(id) FROM {$db_pr}downloads WHERE idUser=d.idUser AND date > '" . date("Y-m-d 00:00:00") . "') as total,
                                        (SELECT SUM(size) FROM {$db_pr}downloads WHERE idUser=d.idUser AND date > '" . date("Y-m-d 00:00:00") . "') as sizes FROM {$db_pr}downloads d
                                        INNER JOIN {$db_pr}users u ON u.id=d.idUser
                            WHERE d.date > '" . date("Y-m-d 00:00:00") . "'

                    group BY idUser ORDER BY total DESC LIMIT 10";
                    $res = mysqli_query($mysqli,$sql);
                    if (mysqli_num_rows($res)) {
                        $i = 1;
                        while ($row = mysqli_fetch_assoc($res)) {
                            ?>
                            <tr class="<?php echo $i % 2 ? "odd" : "" ?>">
                                <td><?php echo $i ?></td>
                                <td><?php echo($row['username']) ?></td>
                                <td style="text-align: center"><?php echo($row['total']) ?></td>
                                <td><?php echo(getSizeStr($row['sizes'])) ?></td>
                            </tr>
                            <?php $i++;
                        }
                    } else {
                        ?>
                        <td colspan="4">No data</td>
                    <?php } ?>
                </table>
            </div>
            <div class="col">
                <h3>This Month's Top 10</h3>
                <table class="stat">
                    <tr>
                        <th>&nbsp;</th>
                        <th>user</th>
                        <th>downloads</th>
                        <th>size</th>
                    </tr>
                    <?php
                    $sql = "SELECT d.*,u.username,(SELECT COUNT(id) FROM {$db_pr}downloads WHERE idUser=d.idUser AND date>'" . date("Y-m-01 00:00:00") . "') as total,
                                        (SELECT SUM(size) FROM {$db_pr}downloads WHERE idUser=d.idUser AND date>'" . date("Y-m-01 00:00:00") . "') as sizes FROM {$db_pr}downloads d
                                        INNER JOIN {$db_pr}users u ON u.id=d.idUser
                            WHERE d.date>'" . date("Y-m-01 00:00:00") . "'

                    group BY idUser ORDER BY total DESC LIMIT 10";
                    $res = mysqli_query($mysqli,$sql);
                    if (mysqli_num_rows($res)) {
                        $i = 1;
                        while ($row = mysqli_fetch_assoc($res)) {
                            ?>
                            <tr class="<?php echo $i % 2 ? "odd" : "" ?>">
                                <td><?php echo $i ?></td>
                                <td><?php echo($row['username']) ?></td>
                                <td style="text-align: center"><?php echo($row['total']) ?></td>
                                <td><?php echo(getSizeStr($row['sizes'])) ?></td>
                            </tr>
                            <?php $i++;
                        }
                    } else {
                        ?>
                        <td colspan="4">No data</td>
                    <?php } ?>
                </table>
            </div>
            <div class="col">
                <h3>All Time Top 10</h3>
                <table class="stat">
                    <tr>
                        <th>&nbsp;</th>
                        <th>user</th>
                        <th>downloads</th>
                        <th>size</th>
                    </tr>
                    <?php
                    $sql = "SELECT d.*,u.username,(SELECT COUNT(id) FROM {$db_pr}downloads WHERE idUser=d.idUser) as total,
                                        (SELECT SUM(size) FROM {$db_pr}downloads WHERE idUser=d.idUser) as sizes FROM {$db_pr}downloads d
                                        INNER JOIN {$db_pr}users u ON u.id=d.idUser

                    group BY idUser ORDER BY total DESC LIMIT 10";
                    $res = mysqli_query($mysqli,$sql);
                    if (mysqli_num_rows($res)) {
                        $i = 1;
                        while ($row = mysqli_fetch_assoc($res)) {
                            ?>
                            <tr class="<?php echo $i % 2 ? "odd" : "" ?>">
                                <td><?php echo $i ?></td>
                                <td><?php echo($row['username']) ?></td>
                                <td style="text-align: center"><?php echo($row['total']) ?></td>
                                <td><?php echo(getSizeStr($row['sizes'])) ?></td>
                            </tr>
                            <?php $i++;
                        }
                    } else {
                        ?>
                        <td colspan="4">No data</td>
                    <?php } ?>
                </table>
            </div>
            <div class="clear"></div>
            <br>

            <div class="topFiles">
                <h2>Most Popular Files</h2>
                <div class="clear"></div>
                <?php
                $sql = "SELECT d.*,f.*,(SELECT COUNT(id) FROM  {$db_pr}downloads WHERE idFile=d.idFile) as total FROM {$db_pr}downloads d
                        INNER JOIN {$db_pr}files f ON f.id=d.idFile
                        group BY idFile ORDER BY total DESC LIMIT 10";
                $res = mysqli_query($mysqli,$sql);
                if (mysqli_num_rows($res)) {
                    ?>
                    <table class="stat">
                        <tr>
                            <th>&nbsp;</th>
                            <th>File</th>
                            <th style="text-align: center">Downloads Count</th>
                        </tr>
                        <?php    $i = 1;
                        while ($row = mysqli_fetch_assoc($res)) {
                            ?>
                            <tr class="<?php echo ($i % 2) ? "odd" : "" ?>">
                                <td><?php echo $i ?>.</td>
                                <td><?php echo($row['title']) ?></td>
                                <td style="text-align: center"><?php echo($row['total']) ?> Downloads</td>
                            </tr>
                            <?php $i++;
                        } ?>
                    </table>
                <?php } else { ?>
                    <td colspan="3">No data</td>
                <?php } ?>
            </div>
        </div>
    </div>
    </div>
    </div>
    <?php include "includes/footer.php";
} ?>