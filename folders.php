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
    //paging settings
    // how many rows to show per page
    $rowsPerPage = 14;
    // by default we show first page
    $pageNum = 1;
    // if $_GET['page'] defined, use it as page number
    if (isset($_GET['page'])) {
        $pageNum = $_GET['page'];
    }
    $offset = ($pageNum - 1) * $rowsPerPage;
    //CREATE PAGING LINKS
    // how many rows we have in database
    $query = "SELECT COUNT(id) AS numrows FROM {$db_pr}folders WHERE '1' ";
    $result = mysqli_query($mysqli,$query) or die('Error, query failed');
    $row     = mysqli_fetch_assoc($result);
    $numrows = $row['numrows'];
    // how many pages we have when using paging?
    $maxPage = ceil($numrows / $rowsPerPage);
    // print the link to access each page
    $self = $_SERVER['PHP_SELF'];
    $nav  = '';
    for ($page = 1; $page <= $maxPage; $page++) {
        if ($page == $pageNum) {
            $nav .= " $page "; // no need to create a link to current page
        } else {
            $nav .= " <a href=\"$self?page=$page\">$page</a> ";
        }
    }
    // creating previous and next link
    // plus the link to go straight to
    // the first and last page
    if ($pageNum > 1) {
        $page  = $pageNum - 1;
        $prev  = " <a href=\"$self?page=$page\">Prev</a> ";
        $first = " <a href=\"$self?page=1\">1st Page</a> ";
    } else {
        $prev  = '&nbsp;'; // we're on page one, don't print previous link
        $first = '&nbsp;'; // nor the first page link
    }
    if ($pageNum < $maxPage) {
        $page = $pageNum + 1;
        $next = " <a href=\"$self?page=$page\">Next</a> ";
        $last = " <a href=\"$self?page=$maxPage\">Last</a> ";
    } else {
        $next = '&nbsp;'; // we're on the last page, don't print next link
        $last = '&nbsp;'; // nor the last page link
    }
    //show page only if admin access level
    if ($level == "admin") {
        //request all neccessary variables for extensions action.
        $name     = (!empty($_REQUEST["name"])) ? strip_tags(str_replace("'", "`", $_REQUEST["name"])) : '';
        $parentID = (!empty($_REQUEST["parentID"])) ? strip_tags(str_replace("'", "`", $_REQUEST["parentID"])) : '0';
        $assign   = (!empty($_REQUEST["assign"])) ? strip_tags(str_replace("'", "`", $_REQUEST["assign"])) : '';
        //"create new folder" action processing.
        if (!empty($_REQUEST["add_folder"]) && $_REQUEST["add_folder"] == "yes" && !empty($name)) {
        if($demo){
            $msg = "<div class='loginMessage loginError'>Operation not permitted in preview version of this product.</div>";
        } else {
            //check for existing user in DB.
            $name = str_replace(" ", "-", strtolower(trim($name)));
            if ($name != '' && preg_match('/[^\w\d_-]/si', $name)) {
                $name = str_replace(' ', '-', $name);
                if (preg_match('/[^\w\d_-]/si', $name)) {
                    $name = preg_replace('/[^\w\d_-]/si', '', $name);
                }
            }
            if (!empty($name)) {
                $sql = "SELECT * FROM {$db_pr}folders WHERE name='" . $name . "' AND parentID='" . $parentID . "'";
                $result = mysqli_query($mysqli,$sql) or die("oopsy, error selecting folder from database for comparison");
                if (mysqli_num_rows($result) > 0) {
                    $msg = "<div class='loginMessage loginError'>Folder already exists in database. Try another one.</div>";
                } else {
                    if (!empty($name)) {
                        if (!empty($parentID) && $parentID != 0) {
                            $mainFolderName = getFolderPathById($parentID);
                            //also create mkdir folder in script base, and make it writable
                            $thisdir = getcwd();
                            $newFolderPath = $thisdir . "/" . $mainFolderName . "/" . $name;
                            mkdir($newFolderPath, 0777);
                            //chmod($script_dir.$name, 777);
                        } else {
                            //also create mkdir folder in script base, and make it writable
                            $thisdir = getcwd();
                            mkdir($thisdir . "/" . $name, 0777);
                            //chmod($script_dir.$name, 777);
                        }
                        $sql = "INSERT INTO {$db_pr}folders (dateCreated,name,parentID) VALUES (NOW(),'" . $name . "','" . $parentID . "')";
                        $result = mysqli_query($mysqli,$sql) or die("Error occurred - tried  to insert new folder.");
                        $msg = "<div class='loginMessage loginSuccess'>Folder was successfully added!</div>";
                        addLog($_SESSION["idUser"], "Added new folder $name");
                        if (isset($assign) && !empty($assign)) {
                            $maxid       = mysqli_fetch_row(mysqli_query($mysqli,"SELECT MAX(id) FROM {$db_pr}folders"));
                            $folder_id   = $maxid[0];
                            $folder_name = "," . $folder_id;
                            $qry         = mysqli_query($mysqli,"SELECT upload_dirs, id FROM {$db_pr}users ORDER BY id");
                            while ($row = mysqli_fetch_row($qry)) {
                                $x         = $row[0] . $folder_name;
                                $x2        = explode(",", $x);
                                $assignDir = implode(",", $x2);
                                $qry2      = mysqli_query($mysqli,"UPDATE {$db_pr}users SET upload_dirs = '" . $assignDir . "' WHERE id={$row[1]}");
                            }
                        }
                        //}
                        $name = "";
                    }
                }
            } else {
                $msg = "<div class='loginMessage loginError'>Empty folder supplied.</div>";
            }
           }
        }
        $foldersList = getFoldersList();
        include "includes/header.php";
        ?>

        <script type="text/javascript" charset="utf-8">
            $(document).ready(function () {
//////////
                var oTable = $('#table').dataTable({

                    "bProcessing": true,
                    "bServerSide": true,
                    "sAjaxSource": "includes/datasource/folders.php",
                    "fnRowCallback": function (nRow, aData, iDisplayIndex, iDisplayIndexFull) {
                        if (aData[1] == 'uploads') {
                            $('td:eq(0)', nRow).html('<input type="checkbox" value="" disabled name="folders[]" />');
                            $('td:eq(1)', nRow).html(aData[1]);
                            $('td:eq(2)', nRow).html('<div class="cel">' + aData[2] + '</div>');
                            $('td:eq(3)', nRow).html('<div class="cell"><a href="edit_folder.php?id=' + aData[0] + '"><span class=" glyphicon glyphicon-pencil"></span></a></div>');
                        } else {
                            $('td:eq(0)', nRow).html('<input type="checkbox" value="' + aData[0] + '" name="folders[]" />');
                            $('td:eq(1)', nRow).html(aData[1]);
                            $('td:eq(2)', nRow).html(aData[2]);
                            $('td:eq(3)', nRow).html('<?php if($level=='admin'){?><a href="edit_folder.php?id=' + aData[0] + '"><span class=" glyphicon glyphicon-pencil"></span></a><?php }else{?><?php echo("&nbsp;");}?>');
                        }
                    }, "aaSorting": []

                });
/////////////////
                $('#btndeleteselected').click(function () {
                    var countSelected = $('input[type=checkbox]:checked').length;
                    if (countSelected != 0) {
                        if (confirmDelete()) {
                            $("#msgCont").html('');
                            var values = new Array();
                            $.each($("input[name='folders[]']:checked"), function () {
                                values.push($(this).val());
                            });
                            $.getJSON('ajax/delete_folder.php', {ids: values}, function (data) {
                                $("#msgCont").append(data.mes);
                                $("#msgCont").append(data.mesSuc);
                            })
                            oTable.fnDraw()
                        }
                    } else {
                        alert('Nothing selected.');
                    }
                });
            });
            function confirmDelete() {
                return confirm("Are you sure to delete selected folder?");
            }
            $(document).ready(function () {
                //Examples of how to assign the Colorbox event to elements
                $("#btnAdd").colorbox({inline: true, innerWidth: "620px", overlayClose: false});
            });
            $(document).bind('cbox_closed', function (e) {
                $(".top_badPass").hide();
                var form = $(e.currentTarget).find("form");
                form.find("input[type='text']").each(function () {
                    $(this).val("");
                })
                form.find("select").each(function () {
                    $(this).val("");
                    $(this).parent().find(".text").html($(this).find('option').eq(0).text())
                })
                form.find("input[type='checkbox']").each(function () {
                    $(this).removeAttr("checked");
                    $(this).parent().removeClass("active");
                })
            });
        </script>

        <div id="content-main">
            <div id="msgCont">
                <?php echo $msg; ?>
            </div>
            <h2>Manage Folders</h2>
            <button id="btnAdd" class="btn btn-success rFloat " href="#inline_content">Add New</button>
            <div class="clear"></div>
            <div id="dynamic">
                <table cellpadding="0" cellspacing="0" border="0" class="display" id="table">
                    <thead>
                    <tr>
                        <th width="5%">&nbsp;</th>
                        <th width="50%">Name</th>
                        <th width="40%">Date Created</th>
                        <th width="5%">&nbsp;</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr>
                        <td colspan="4" class="dataTables_empty">Loading data from server</td>
                    </tr>
                    </tbody>
                    <tfoot>
                    <tr>
                        <th width="5%">&nbsp;</th>
                        <th width="50%">Name</th>
                        <th width="40%">Date Created</th>
                        <th width="5%">&nbsp;</th>
                    </tr>
                    </tfoot>
                </table>
                <br/><br/>
            </div>
            <div class="clear"></div>
            <button id="btndeleteselected" class="btn btn-danger ">Delete Selected</button>
            <br/><br/><br/><br/>
        </div>
        </div>
        <div style='display:none'>
            <div id='inline_content'>
                <h2>Create Folder</h2>
                <br/><br/>

                <form action="folders.php" enctype="multipart/form-data" method="post" name="ff1"
                      class="form-horizontal popup-form">
                    <input value="yes" name="add_folder" type="hidden"/>

                    <div class="form-group">
                        <label for="inputEmail3" class="col-sm-4 control-label">Folder Name:</label>

                        <div class="col-sm-8">
                            <input class="form-control" name="name" type="text" placeholder="Folder Name">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="inputPassword3" class="col-sm-4 control-label">Parent Folder:</label>

                        <div class="col-sm-8">
                            <select name="parentID" id="parentID" class="form-control">
                                <option value="Please Select"> No Parent Folder</option>
                                <?php foreach($foldersList as $id=>$info){?>
                                    <option value="<?php echo($id)?>"><?php echo($info['name'])?></option>
                                    <?php if(isset($info['children'])){?>

                                        <?php foreach($info['children'] as $sid=>$sinfo){?>
                                            <option value="<?php echo($sid)?>">- <?php echo($sinfo['name'])?></option>
                                        <?php }?>

                                    <?}?>
                                <?}?>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="col-sm-offset-4 col-sm-8">
                            <div class="checkbox">
                                <label>
                                    <input type="checkbox" name="assign" value="yes"> Assign this new folder to all
                                    users
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="col-sm-offset-4 col-sm-4">
                            <button type="submit" class="btn btn-primary btn-block">Create</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        <?php include "includes/footer.php";
    }
} ?>