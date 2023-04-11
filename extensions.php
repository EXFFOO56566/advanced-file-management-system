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
$users_table = "";
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
    $rowsPerPage = 30;
    // by default we show first page
    $pageNum = 1;
    // if $_GET['page'] defined, use it as page number
    if (isset($_GET['page'])) {
        $pageNum = $_GET['page'];
    }
    $offset = ($pageNum - 1) * $rowsPerPage;
    //CREATE PAGING LINKS
    // how many rows we have in database
    $query = "SELECT COUNT(id) AS numrows FROM {$db_pr}extensions WHERE '1' ";
    $result = mysqli_query($mysqli,$query) or die('Error, query failed');
    $row = mysqli_fetch_assoc($result);
    $numrows = $row['numrows'];
    // how many pages we have when using paging?
    $maxPage = ceil($numrows / $rowsPerPage);
    // print the link to access each page
    $self = $_SERVER['PHP_SELF'];
    $nav = '';
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
        $page = $pageNum - 1;
        $prev = " <a href=\"$self?page=$page\">Prev</a> ";
        $first = " <a href=\"$self?page=1\">1st Page</a> ";
    } else {
        $prev = '&nbsp;'; // we're on page one, don't print previous link
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
        $name = (!empty($_REQUEST["name"])) ? strip_tags(str_replace("'", "`", $_REQUEST["name"])) : '';
        $assign = (!empty($_REQUEST["assign"])) ? strip_tags(str_replace("'", "`", $_REQUEST["assign"])) : '';
        //"create new extension" action processing.
        if (!empty($_REQUEST["add_extension"]) && $_REQUEST["add_extension"] == "yes" && !empty($name)) {
        if($demo){
            $msg = "<div class='loginMessage loginError'>Operation not permitted in preview version of this product.</div>";
        } else {
            //check for existing user in DB.
            $name = strtoupper(trim(str_replace(".", "", $name)));
            if ($name != '' && preg_match('/[^\w\d_-]/si', $name)) {
                $name = str_replace(' ', '-', $name);
                if (preg_match('/[^\w\d_-]/si', $name)) {
                    $name = preg_replace('/[^\w\d_-]/si', '', $name);
                }
            }
            if (!empty($name)) {
                $sql = "SELECT * FROM {$db_pr}extensions WHERE name='" . $name . "'";
                $result = mysqli_query($mysqli,$sql) or die("oopsy, error selecting extension from database for comparison");
                if (mysqli_num_rows($result) > 0) {
                    //user exists, throw error.
                    $msg = "<div class='loginMessage loginError'>Extension already exists in database. Try another one.</div>";
                } else {
                    //ok, let's insert new user to database
                    if (!empty($name)) {
                        if (($name == "EXE" || $name == "COM" || $name == "BAT") && $demo) {
                            $msg = "<div class='loginMessage loginError'>That extension is not allowed in demo!</div>";
                        } else {
                            $sql = "INSERT INTO {$db_pr}extensions (dateCreated,name) VALUES (NOW(),'" . $name . "')";
                            $result = mysqli_query($mysqli,$sql) or die("Error occurred - tried  to insert new extension.");
                            $msg = "<div class='loginMessage loginSuccess'>Extension was successfully added!</div>";
                            addLog($_SESSION["idUser"], "Added new extension $name");
                            if (isset($assign) && !empty($assign)) {
                                $extname = "," . $name;
                                $qry = mysqli_query($mysqli,"SELECT extensions, id FROM {$db_pr}users ORDER BY id");
                                while ($row = mysqli_fetch_row($qry)) {
                                    $x = $row[0] . $extname;
                                    $x2 = explode(",", ltrim($x, ','));
                                    sort($x2);
                                    $assignExt = implode(",", $x2);
                                    $qry2 = mysqli_query($mysqli,"UPDATE {$db_pr}users SET extensions = '" . $assignExt . "' WHERE id={$row[1]}");
                                }
                            }
                        }
                        $name = "";
                    }
                }
            } else {
                $msg = "<div class='loginMessage loginError'>Empty extension supplied.</div>";
            }
        }
        }
        //"delete selected extensions" action processing.
        if (!empty($_REQUEST["extensions_delete"]) && $_REQUEST["extensions_delete"] == "yes") {
        if($demo){
            $msg = "<div class='loginMessage loginError'>Operation not permitted in preview version of this product.</div>";
        } else {
            if (is_array($_POST['extToDel'])) {
                if (join(",", $_POST['extToDel']) != '') {
                    $sql = "DELETE  FROM {$db_pr}extensions WHERE id IN ('" . join("','", $_POST['extToDel']) . "')";
                    $result = mysqli_query($mysqli,$sql) or die("oopsy, error when tryin to delete extensions");
                    $msg = "<div class='loginMessage loginError'>Extensions with id " . join(",", $_POST['extToDel']) . " were deleted.</div>";
                    addLog($_SESSION["idUser"], "Deleted following extensions: " . join(",", $_POST['extToDel']));
                    foreach ($_POST['extToDel'] AS $val) {
                        $ext = getExtensionById($val);
                        $sql = "SELECT * FROM {$db_pr}users WHERE extensions LIKE '%{$ext['name']}%' ";
                        $res = mysqli_query($mysqli,$sql);
                        while ($row = mysqli_fetch_assoc($res)) {
                            $usrExt = explode(',', $row['extensions']);
                            unset($usrExt[$ext['name']]);
                            mysqli_query($mysqli,"UPDATE {$db_pr}users SET extensions ='" . implode(',', $usrExt) . "' WHERE id='{$row['id']}'");
                        }
                    }
                }
            }
           }
        }
        include "includes/header.php";
        ?>

        <script type="text/javascript" charset="utf-8">
            $(document).ready(function () {

//////////
                var oTable = $('#table').dataTable({

                    "bProcessing": true,
                    "bServerSide": true,
                    "sAjaxSource": "includes/datasource/extensions.php",
                    "fnRowCallback": function (nRow, aData, iDisplayIndex, iDisplayIndexFull) {
                        $('td:eq(0)', nRow).html('<input type="checkbox" value="' + aData[0] + '" name="extensions[]" />');
                        $('td:eq(1)', nRow).html('<div class="cell">' + aData[1] + '</div>');
                    }, "aaSorting": []

                });
/////////////////
                $('#btndeleteselected').click(function () {
                    var countSelected = $("input[name='extensions[]']:checked").length;
                    if (countSelected != 0) {
                        if (confirmDelete()) {
                            $("#msgCont").html('');
                            var values = new Array();
                            $.each($("input[name='extensions[]']:checked"), function () {
                                values.push($(this).val());
                            });
                            $.getJSON('ajax/delete_extension.php', {ids: values}, function (data) {
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
                return confirm("Are you sure to delete selected extension?");
            }
            $(document).ready(function () {

                //Examples of how to assign the Colorbox event to elements
                $("#btnAdd").colorbox({inline: true, innerWidth: "620px", overlayClose: false});
            });
            $(document).bind('cbox_closed', function (e) {
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

            <h2>All Extensions</h2>
            <button id="btnAdd" class='btn btn-success rFloat ' id="btnAdd" href="#inline_content">Add New</button>
            <div id="dynamic">
                <table cellpadding="0" cellspacing="0" border="0" class="display" id="table">
                    <thead>
                    <tr>
                        <th width="5%">&nbsp;</th>
                        <th width="95%">Name</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr>
                        <td colspan="2" class="dataTables_empty">Loading data from server</td>
                    </tr>
                    </tbody>
                    <tfoot>
                    <tr>
                        <th>&nbsp;</th>
                        <th>Name</th>
                    </tr>
                    </tfoot>
                </table>
            </div>
            <div class="clear"></div>
            <button id="btndeleteselected" class="btn btn-danger">Delete Selected</button>
        </div><br/><br/>

        </div>
        <div style='display:none'>
            <div id='inline_content'>
                <h2>Add new extensions</h2>
                <br/><br/><br/><br/>
                    <form action="extensions.php" enctype="multipart/form-data" method="post" name="ff1"
                          class="form-horizontal popup-form" >
                        <input value="yes" name="add_extension" type="hidden"/>

                        <div class="form-group">
                            <label for="name" class="col-sm-4 control-label">Extension Name:</label>

                            <div class="col-sm-8">
                                <input class="form-control" name="name" type="text" placeholder="Extension (example: JPG)">
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="col-sm-offset-4 col-sm-8">
                                <div class="checkbox">
                                    <label>
                                        <input type="checkbox" name="assign" value="yes">Assign this new extension
                                        to all users
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