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
    if (stristr($access, "abcdef")) {
        //show page only if user has rights to edit his or all files.
        $id   = (!empty($_REQUEST["id"])) ? strip_tags(str_replace("'", "`", $_REQUEST["id"])) : '';
        $name = (!empty($_REQUEST["name"])) ? strip_tags(str_replace("'", "`", $_REQUEST["name"])) : '';
        //edit file block.
        if (!empty($_REQUEST["edit_file"]) && $_REQUEST["edit_file"] == "yes" && !empty($name) && !empty($id)) {
        if($demo){
            $msg = "<div class='loginMessage loginError'>Operation not permitted in preview version of this product.</div>";
        } else {
            //check if user has proper access + file creator
            $sql = "SELECT * FROM {$db_pr}folders WHERE id='" . $id . "'";
            $result = mysqli_query($mysqli,$sql) or die("Error occurred when trying to query the database.");
            $rrr = mysqli_fetch_assoc($result);
            $name = str_replace(" ", "-", strtolower(trim($name)));
            if ($name != '' && preg_match('/[^\w\d_-]/si', $name)) {
                $name = str_replace(' ', '-', $name);
                if (preg_match('/[^\w\d_-]/si', $name)) {
                    $name = preg_replace('/[^\w\d_-]/si', '', $name);
                }
            }
            if (!empty($rrr['parentID']) && $rrr['parentID'] != 0) {
                /*$qq             = "SELECT name FROM {$db_pr}folders WHERE id='" . $rrr['parentID'] . "'";
                $ress           = mysqli_query($mysqli,$qq);
                $rr             = mysqli_fetch_assoc($ress);
                $mainFolderName = $rr["name"];*/
                $mainFolderName = getFolderPathById($rrr['parentID']);
                //also create mkdir folder in script base, and make it writable
                $thisdir  = getcwd();
                $oldName  = $thisdir . "/" . $mainFolderName . "/" . $rrr['name'];
                $newName  = $thisdir . "/" . $mainFolderName . "/" . $name;
                $isParent = false;
                //chmod($script_dir.$name, 777);
            } else {
                //also create mkdir folder in script base, and make it writable
                $thisdir  = getcwd();
                $oldName  = $thisdir . "/" . $rrr['name'];
                $newName  = $thisdir . "/" . $name;
                $isParent = true;
                //chmod($script_dir.$name, 777);
            }
            if (rename($oldName, $newName)) {
                $sql = "UPDATE {$db_pr}folders SET name='{$name}' WHERE id='{$id}'";
                $res = mysqli_query($mysqli,$sql) or die("Error occurred - tried to update folder info.");
                if ($isParent) {
                    $sql = "SELECT * FROM {$db_pr}files WHERE catID='{$id}' OR catID IN (select id from {$db_pr}folders WHERE parentID='{$id}')";
                    $res = mysqli_query($mysqli,$sql) or die("error get associated files");
                    while ($row = mysqli_fetch_assoc($res)) {
                        $newPathInfo    = explode("/", $row['path']);
                        $newPathInfo[0] = $name;
                        $sql            = "UPDATE {$db_pr}files SET path='" . join("/", $newPathInfo) . "' WHERE id ='{$row['id']}'";
                        $r              = mysqli_query($mysqli,$sql);
                    }
                } else {
                    $sql = "SELECT * FROM {$db_pr}files WHERE catID='{$id}'";
                    $res = mysqli_query($mysqli,$sql) or die("error get associated files");
                    while ($row = mysqli_fetch_assoc($res)) {
                        $newPathInfo    = explode("/", $row['path']);
                        $newPathInfo[1] = $name;
                        //print_r($newPathInfo);
                        $sql = "UPDATE {$db_pr}files SET path='" . join("/", $newPathInfo) . "' WHERE id ='{$row['id']}'";
                        $r   = mysqli_query($mysqli,$sql);
                    }
                }
                $msg = "<div class='loginMessage loginSuccess'>Folder was successfully updated!</div>";
            }
           }
        }
        $sSQL = "SELECT * FROM {$db_pr}folders WHERE id='" . $id . "'";
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
            <h2>Edit Folder</h2>
            <a id="btnAdd" class="btn btn-primary rFloat " href="folders.php">Back to list</a>

            <div class="clear"></div>
            <strong><?php echo $msg; ?></strong>

            <form action="edit_folder.php" enctype="multipart/form-data" method="post" name="ff1"
                  class="form-horizontal inner-form" >
                <input value="yes" name="edit_file" type="hidden"/>
                <input value="<?php echo $id; ?>" name="id" type="hidden"/>

                <div class="form-group">
                    <label for="title" class="col-sm-6 control-label">Folder Name:</label>

                    <div class="col-sm-6">
                        <input class="form-control" name="name" type="text" placeholder="File Title"
                               value="<?php echo $name ?>">
                    </div>
                </div>
                <div class="form-group">
                    <div class="col-sm-offset-6 col-sm-3">
                        <button type="submit" class="btn btn-primary btn-block">Rename Folder</button>
                    </div>
                </div>
                <br/>
            </form>
        </div>
        </div>
        <?php include "includes/footer.php";
    }
} ?>