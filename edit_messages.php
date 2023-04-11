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
    //request all neccessary variables for extensions action.
    $name = (!empty($_REQUEST["name"])) ? strip_tags(str_replace("'", "`", $_REQUEST["name"])) : '';
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
    $query = "SELECT COUNT(id) AS numrows FROM {$db_pr}messages WHERE '1' ";
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
        //"delete selected messages" action processing.
        if (!empty($_REQUEST["messages_delete"]) && $_REQUEST["messages_delete"] == "yes") {
            if (is_array($_POST['extToDel'])) {
                if (join(",", $_POST['extToDel']) != '') {
                    $sql = "DELETE  FROM {$db_pr}messages WHERE id IN ('" . join("','", $_POST['extToDel']) . "')";
                    $result = mysqli_query($mysqli,$sql) or die("oopsy, error when tryin to delete messages");
                    $msg = "Messages with id " . join(",", $_POST['extToDel']) . " were deleted.";
                    addLog($_SESSION["idUser"], "Deleted following messages: " . join(",", $_POST['extToDel']));
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
                    "sAjaxSource": "includes/datasource/messages.php",
                    "fnRowCallback": function (nRow, aData, iDisplayIndex, iDisplayIndexFull) {
                        $('td:eq(0)', nRow).html('<input type="checkbox" value="' + aData[0] + '" name="folders[]" />');
                        $('td:eq(1)', nRow).html(aData[1]);
                        $('td:eq(2)', nRow).html('<div class="cell" >' + aData[2] + '</div>');
                        $('td:eq(3)', nRow).html('<div class="cell">' + aData[3] + '</div>');
                        $('td:eq(4)', nRow).html('<a href="messages.php?file=' + aData[4] + '"><span class="glyphicon glyphicon-fullscreen"></span></a>');
                    }

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
                            $.getJSON('ajax/delete_messages.php', {ids: values}, function (data) {
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
        </script>
        <div id="content-main">
            <div id="msgCont"></div>
            <div class="content_block">
                <h2>File Messages</h2>
                <div class="clear"></div>
                <div id="dynamic">
                    <table cellpadding="0" cellspacing="0" border="0" class="display" id="table">
                        <thead>
                        <tr>
                            <th width="5%">&nbsp;</th>
                            <th width="15%">Name</th>
                            <th width="25%">Date Posted</th>
                            <th width="60%">Message</th>
                            <th width="5%">&nbsp;</th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr>
                            <td colspan="5" class="dataTables_empty">Loading data from server</td>
                        </tr>
                        </tbody>
                        <tfoot>
                        <tr>
                            <th width="5%">&nbsp;</th>
                            <th width="15%">Name</th>
                            <th width="25%">Date Posted</th>
                            <th width="60%">Message</th>
                            <th width="5%">&nbsp;</th>
                        </tr>
                        </tfoot>
                    </table>
                    <br/><br/>
                </div>
                <button id="btndeleteselected" class="btn btn-danger ">Delete Selected</button>
                <br/><br/><br/><br/>
                <strong><?php echo $msg; ?></strong>
            </div>
        </div>
        </div>
        <?php include "includes/footer.php";
    }
} ?>