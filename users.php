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
        exit('Permissions denied');
    }
    ######################### DO NOT MODIFY (UNLESS SURE) END ########################

    //show page only if admin
    if ($level == "admin") {
        $username     = (!empty($_REQUEST["username"])) ? strip_tags(str_replace("'", "`", $_REQUEST["username"])) : '';
        $password     = (!empty($_REQUEST["password"])) ? strip_tags(str_replace("'", "`", $_REQUEST["password"])) : '';
        $email        = (!empty($_REQUEST["email"])) ? strip_tags(str_replace("'", "`", $_REQUEST["email"])) : '';
        $quota        = (!empty($_REQUEST["quota"])) ? strip_tags(str_replace("'", "`", $_REQUEST["quota"])) : '1';
        $filesize     = (!empty($_REQUEST["filesize"])) ? strip_tags(str_replace("'", "`", $_REQUEST["filesize"])) : '1';
        $upload_dir   = (!empty($_REQUEST["upload_dir"])) ? strip_tags(str_replace("'", "`", $_REQUEST["upload_dir"])) : 'uploads';
        $upload_dirs  = (!empty($_REQUEST["upload_dirs"])) ? $_REQUEST["upload_dirs"] : '';
        $active       = (!empty($_REQUEST["active"])) ? strip_tags(str_replace("'", "`", $_REQUEST["active"])) : '';
        $extensions   = (!empty($_REQUEST["extensions"])) ? $_REQUEST["extensions"] : '';
        $accesslevel2 = (!empty($_REQUEST["accesslevel2"])) ? $_REQUEST["accesslevel2"] : '';
        $sendpass     = (!empty($_REQUEST["sendpass"])) ? $_REQUEST["sendpass"] : 0;
        $admin        = (isset($_REQUEST["admin"])) ? 1 : 0;
        //"create new user" action processing.
        if (!empty($_REQUEST["create_user"]) && $_REQUEST["create_user"] == "yes" && !empty($username)) {
        if($demo){
            $msg = "<div class='loginMessage loginError'>Operation not permitted in preview version of this product.</div>";
        } else {
            //check for existing user in DB.
            $sql = "SELECT * FROM {$db_pr}users WHERE username='" . $username . "'";
            $result = mysqli_query($mysqli,$sql) or die("oopsy, error selecting user from database for comparison");
            if (mysqli_num_rows($result) > 0) {
                //user exists, throw error.
                $msg = "<div class='loginMessage loginError'>User already exists in database. Try another username.</div>";
            } else {
                //ok, let's insert new user to database
                if (!empty($username) && !empty($password) && !empty($email)) {
                    if (is_array($_POST["extensions"]) == true) {
                        $extensionsStr = implode(",", $_POST["extensions"]);
                    } else {
                        $extensionsStr = $_POST["extensions"];
                    }
                    if (is_array($_POST["upload_dirs"]) == true) {
                        $foldStr = implode(",", $_POST["upload_dirs"]);
                    } else {
                        $foldStr = $_POST["upload_dirs"];
                    }
                    if (is_array($_POST["accesslevel2"]) == true) {
                        $accesslevelStr = implode("", $_POST["accesslevel2"]);
                    } else {
                        $accesslevelStr = $_POST["accesslevel2"];
                    }
                    if ($admin) {
                        $accesslevelStr = "abcdefghijklmnopqrstuvwxyz";
                    }
                    $sql = "INSERT INTO {$db_pr}users (dateCreated,username,password,email,accesslevel,extensions,quota,active,filesize,upload_dir,upload_dirs)
                VALUES ('" . date("Y-m-d H:i:s") . "','" . $username . "','" . md5($password) . "','" . $email . "','" . $accesslevelStr . "','" . $extensionsStr . "','" . $quota . "','" . $active . "','" . $filesize . "','" . $upload_dir . "','" . $foldStr . "')";
                    $result = mysqli_query($mysqli,$sql) or die("Error occurred - tried  to insert new user.");
                    $msg = "<div class='loginMessage loginSuccess'>User was successfully added!</div>";
                    addLog($_SESSION["idUser"], "Added new user $username");
                    if ($sendpass) {
                        if(empty($password)){
                            //generate new password if no password was supplied and send to user was selected.
                            $password = randomPassword();
                        }
                        //creating message for sending
                        $subject = "Your account details.";
                        $mailData = array(
                            "{%username%}"=>$username,
                            "{%linkToScript%}"=>"http://{$_SERVER['SERVER_NAME']}{$script_dir}",
                            "{%password%}"=>$password,
                            "{%email%}"=>$email
                        );
                        sendMail($email, $subject, "create_user.php", $mailData);
                    }
                    $username     = "";
                    $password     = "";
                    $email        = "";
                    $accesslevel2 = "";
                    $active       = "";
                    $extensions   = "";
                    $quota        = "1";
                    $filesize     = "1";
                }
            }
           }
        }
        //"delete selected users" action processing.
        if (!empty($_REQUEST["users_delete"]) && $_REQUEST["users_delete"] == "yes") {
        if($demo){
            $msg = "<div class='loginMessage loginError'>Operation not permitted in preview version of this product.</div>";
        } else {
            if (is_array($_POST['usersToDel'])) {
                if (join(",", $_POST['usersToDel']) != '') {
                    $sql = "DELETE  FROM {$db_pr}users WHERE id IN ('" . join("','", $_POST['usersToDel']) . "')";
                    $result = mysqli_query($mysqli,$sql) or die("oopsy, error when tryin to delete users");
                    $msg = "<div class='loginMessage loginSuccess'>User(s) with id " . join(",", $_POST['usersToDel']) . " were deleted.</div>";
                    addLog($_SESSION["idUser"], "Deleted following users: " . join(",", $_POST['usersToDel']));
                }
            } else {
                $sql = "DELETE  FROM {$db_pr}users WHERE id='" . $usersToDel . "'";
                $result = mysqli_query($mysqli,$sql) or die("oopsy, error when tryin to delete user");
                $msg = "<div class='loginMessage loginSuccess'>User with id $usersToDel was deleted.</div>";
                addLog($_SESSION["idUser"], "Deleted following user: " . $usersToDel);
            }
            }
        }
        //new in version 1.2
        $extStr         = "";
        $new_extensions = getExtensions();
        $cc             = 1;
        for ($i = 0; $i < count($new_extensions); $i++) {
            $extStr .= "<label class=\"checkbox-inline fixed\"><input type='checkbox' name='extensions[]' class='chkExtension' value=\"" . $new_extensions[$i] . "\" ";
            if (is_array($extensions)) {
                if (in_array($new_extensions[$i],$extensions)) {
                    $extStr .= " checked ";
                }
            } else if ($extensions == $new_extensions[$i]) {
                $extStr .= " checked ";
            }
            $extStr .= " />." . $new_extensions[$i] . "</label>";
            $cc++;
            if ($cc == 9) {
                $extStr .= "";
                $cc = 1;
            }
        }
        //new in version 1.4, user status
        $user_stat = "";
        $user_stat .= "<option value='2' " . ($active == "2" ? "selected" : "") . ">Not Active</option>";
        $user_stat .= "<option value='1' " . ($active == "1" ? "selected" : "") . ">Active</option>";
        include "includes/header.php";
        ?>


        <script type="text/javascript" charset="utf-8">
            $(document).ready(function () {

                var oTable = $('#table').dataTable({
                    "bProcessing": true,
                    "bServerSide": true,
                    "sAjaxSource": "includes/datasource/users.php",
                    "fnRowCallback": function (nRow, aData, iDisplayIndex, iDisplayIndexFull) {
                        if (aData[0] != 1) {
                            $('td:eq(0)', nRow).html('<input type="checkbox" value="' + aData[0] + '" name="users[]" /></div>');
                        } else {
                            $('td:eq(0)', nRow).html('<input type="checkbox" disabled /></div>');
                        }
                        $('td:eq(1)', nRow).html( aData[1] );
                        $('td:eq(2)', nRow).html( aData[2] );
                        $('td:eq(3)', nRow).html( aData[3] );
                        $('td:eq(4)', nRow).html( aData[4] );
                        $('td:eq(5)', nRow).html( aData[5] );
                        $('td:eq(6)', nRow).html( "<div style='max-width: 276px;overflow: hidden'>"+aData[6]+"</div>" );
                        $('td:eq(7)', nRow).html( aData[7] );
                        $('td:eq(8)', nRow).html('<div style="width: 90px"><span style="float:left;width: 70px">' + aData[8] + '</span>' +
                            '<a style="float:right;"  href="edit_user.php?id=' + aData[0] + '"><span class=" glyphicon glyphicon-pencil"></span></a></div>');
                    }
                });

                $('#btndeleteselected').click(function () {
                    var countSelected = $('input[type=checkbox]:checked').length;
                    if (countSelected != 0) {
                        if (confirmDelete()) {

                            $("#msgCont").html('');
                            var values = new Array();
                            $.each($("input[name='users[]']:checked"), function () {
                                values.push($(this).val());
                            });
                            $.getJSON('ajax/delete_user.php', {ids: values}, function (data) {
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
                return confirm("Are you sure to delete selected user?");
            }
            function chkExtensions(formname, checktoggle) {
                var checkboxes = new Array();
                checkboxes = document[formname].getElementsByTagName('input');
                for (var i = 0; i < checkboxes.length; i++) {
                    if (checkboxes[i].type == 'checkbox') {
                        if (checkboxes[i].className == 'chkExtension') {
                            checkboxes[i].checked = checktoggle;
                            if (checktoggle) {
                                $(checkboxes[i]).attr("checked", "checked").parent().addClass('active');
                            } else {
                                $(checkboxes[i]).removeAttr("checked").parent().removeClass('active');
                            }
                        }
                    }
                }
            }
            function chkFolders(checktoggle) {
                $(".folder").find("input[type='checkbox']").each(function () {
                    if (checktoggle) {
                        $(this).attr("checked", "checked").parent().addClass("active");
                    } else {
                        $(this).removeAttr("checked").parent().removeClass("active");
                    }
                })
            }
            function chkUploadDirectory(formname, checktoggle) {
                var checkboxes = new Array();
                checkboxes = document[formname].getElementsByTagName('input');
                for (var i = 0; i < checkboxes.length; i++) {
                    if (checkboxes[i].type == 'checkbox') {
                        if (checkboxes[i].className == 'chkUploadDir') {
                            checkboxes[i].checked = checktoggle;
                        }
                    }
                }
            }
        </script>

        <script type="text/javascript" src='js/password_strength_plugin.js'></script>
        <link rel="stylesheet" type="text/css" href="css/passchecker.css">
        <script>
            $(document).ready(function () {
                $(".password_adv").passStrength({
                    shortPass: "top_shortPass",
                    badPass: "top_badPass",
                    goodPass: "top_goodPass",
                    strongPass: "top_strongPass",
                    baseStyle: "top_testresult",
                    userid: ".user_id_adv",
                    messageloc: 1
                });
            });
        </script>


        <link rel="stylesheet" href="includes/viewimage/colorbox.css"/>
        <script src="includes/viewimage/colorbox.js"></script>


        <script>
            $(document).ready(function () {
                //Examples of how to assign the Colorbox event to elements
                $("#btnAdd").colorbox({inline: true, width: "735px", scrolling: false, overlayClose: false});
                $("#chkAllExtension").click(function () {
                    $('input[type=checkbox].chkExtension').each(function () {
                        $(this).wrap('<span class="typeCheck active"></span>').css({display: 'none'});
                    });
                });
                $("#unchkAllExtension").click(function () {
                    $('input[type=checkbox].chkExtension').each(function () {
                        $(this).wrap('<span class="typeCheck"></span>').css({display: 'none'});
                    });
                });
                $("#chkAllUploadDirectory").click(function () {
                    $('input[type=checkbox].chkUploadDir').each(function () {
                        $(this).wrap('<span class="typeCheck active"></span>').css({display: 'none'});
                    });
                });
                $("#unchkAllUploadDirectory").click(function () {
                    $('input[type=checkbox].chkUploadDir').each(function () {
                        $(this).wrap('<span class="typeCheck"></span>').css({display: 'none'});
                    });
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
            });
            function randomPassword() {
                chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890";
                pass = " ";
                for (x = 0; x < 10; x++) {
                    i = Math.floor(Math.random() * 62);
                    pass += chars.charAt(i);
                }
                ff1.password.value = pass;
                $(".top_testresult").remove();
            }
        </script>


        <div id="content-main">
        <div id="msgCont">
            <?php echo $msg; ?>
        </div>
        <!-- <div class="content_block"> -->

        <h2>All Users</h2>
        <button id="btnAdd" class="btn btn-success rFloat " href="#inline_content">Add New</button>
        <div class="clear"></div>
        <div id="dynamic">
            <table cellpadding="0" cellspacing="0" border="0" class="display" id="table" style="width:100%">
                <thead>
                <tr>
                    <th width="5%">&nbsp;</th>
                    <th width="10%">Username</th>
                    <th width="6%">User Type</th>
                    <th width="10%">Email</th>
                    <th width="7%">Quota</th>
                    <th width="7%">File Size</th>
                    <th width="25%">Extensions</th>
                    <th width="10%">Upload</th>
                    <th width="20%">Last Login</th>
                </tr>
                </thead>
                <tbody>
                <tr>
                    <td colspan="9" class="dataTables_empty">Loading data from server</td>
                </tr>
                </tbody>
                <tfoot>
                <tr>
                    <th>&nbsp;</th>
                    <th>Username</th>
                    <th>User Type</th>
                    <th>Email</th>
                    <th>Quota</th>
                    <th>File Size</th>
                    <th>Extensions</th>
                    <th>Upload</th>
                    <th>Last Login</th>
                </tr>
                </tfoot>
            </table>
        </div>
        <div class="clear"></div>
        <button id="btndeleteselected" class="btn btn-danger ">Delete Selected</button>
        </div><br/><br/>

        </div>
        </div>

        </div>
        <div style='display:none'>
            <div id='inline_content' >
                <h2>Add New User</h2>
                <form action="users.php" enctype="multipart/form-data" method="post" name="ff1" class="form-horizontal popup-form">
                    <input value="yes" name="create_user" type="hidden"/>

                    <div class="form-group">
                        <label for="username" class="col-sm-4 control-label">Username:</label>

                        <div class="col-sm-8">
                            <input class="form-control user_id_adv" name="username" type="text" id="username"
                                   placeholder="Username">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="active" class="col-sm-4 control-label">Status:</label>

                        <div class="col-sm-8">
                            <select name="active" id="active" class="form-control">
                                <option value="">Please Select</option>
                                <?php echo $user_stat; ?>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="username" class="col-sm-4 control-label">Password:</label>

                        <div class="col-sm-7">
                            <input class="form-control password_adv" name="password" type="text" id="password"
                                   placeholder="Password">
                        </div>
                    <span onclick="randomPassword();"  class="updatePass">&nbsp&nbsp<img src="images/icon_refresh.png"/></span>
                    </div>
                    <div class="clear"></div>
                    <div class="form-group">
                        <div class="col-sm-offset-4 col-sm-8">
                            <div class="checkbox">
                                <label>
                                    <input type="checkbox" name="sendpass" value="1"/>Send password to user upon save
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="clear"></div>
                    <div class="form-group">
                        <label for="email" class="col-sm-4 control-label">Email:</label>

                        <div class="col-sm-8">
                            <input class="form-control" name="email" type="email" id="email" placeholder="Email">
                        </div>
                    </div>
                    <div class="clear"></div>
                    <div class="form-group">
                        <label for="email" class="col-sm-4 control-label">Permissions:</label>

                        <div class=" col-sm-8">
                            <div class="checkbox">
                                <label><input type="checkbox" name="accesslevel2[]"
                                              value="t" <?php if (is_array($accesslevel2) && in_array("t",$accesslevel2)) {
                                        echo "checked";
                                    } ?> />Can view only own files. <img src="images/info.png" height="18" width="18" class="tipTip" title="If selected, user will see only files uploaded by him/her to assigned directories."></label>
                            </div>
                            <div class="checkbox">
                                <label> <input type="checkbox" name="accesslevel2[]"
                                               value="g" <?php if (is_array($accesslevel2) && in_array("g",$accesslevel2)) {
                                        echo "checked";
                                    } ?> /> Can upload files. <img src="images/info.png" height="18" width="18" class="tipTip" title="If selected, user will be able to upload files in assigned directories"></label>
                            </div>
                            <div class="checkbox">
                                <label> <input type="checkbox" name="accesslevel2[]"
                                               value="i" <?php if (is_array($accesslevel2) && in_array("i",$accesslevel2)) {
                                        echo "checked";
                                    } ?> /> Can view everyone's files.  <img src="images/info.png" height="18" width="18" class="tipTip" title="If selected, user will see all files in directories he/she is assigned to."></label>
                            </div>
                            <div class="checkbox">
                                <label> <input type="checkbox" name="accesslevel2[]"
                                               value="h" <?php if (is_array($accesslevel2) && in_array("h",$accesslevel2)) {
                                        echo "checked";
                                    } ?> /> Can delete own files. <img src="images/info.png" height="18" width="18" class="tipTip" title="If selected, user will be able to delete files which were uploaded by him/her"></label>
                            </div>
                            <div class="checkbox">
                                <label> <input type="checkbox" name="accesslevel2[]"
                                               value="j" <?php if (is_array($accesslevel2) && in_array("j",$accesslevel2)) {
                                        echo "checked";
                                    } ?> /> Can delete any file. <img src="images/info.png" height="18" width="18" class="tipTip" title="If selected, user will be able to delete any files in assigned directories"></label>
                            </div>
                            <div class="checkbox">
                                <label> <input type="checkbox" name="accesslevel2[]"
                                               value="k" <?php if (is_array($accesslevel2) && in_array("k",$accesslevel2)) {
                                        echo "checked";
                                    } ?> /> Can edit own files. <img src="images/info.png" height="18" width="18" class="tipTip" title="If selected, user can rename own files"></label>
                            </div>
                            <div class="checkbox">
                                <label> <input type="checkbox" name="accesslevel2[]"
                                               value="l" <?php if (is_array($accesslevel2) && in_array("l",$accesslevel2)) {
                                        echo "checked";
                                    } ?> /> Can edit all files. <img src="images/info.png" height="18" width="18" class="tipTip" title="If selected, user can rename any file in assigned directories"></label>
                            </div>
                            <div class="checkbox">
                                <label> <input type="checkbox" name="accesslevel2[]"
                                               value="m" <?php if (is_array($accesslevel2) && in_array("m",$accesslevel2)) {
                                        echo "checked";
                                    } ?> /> Can share any files. <img src="images/info.png" height="18" width="18" class="tipTip" title="If selected, sharing files will be enabled for this user"></label>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="email" class="col-sm-4 control-label">Extensions:
                            <div class="subLabel">
                                <a href="javascript:;" onclick="javascript:chkExtensions('ff1', true);">check all</a>
                                | <a href="javascript:;" onclick="javascript:chkExtensions('ff1', false);">uncheck all</a>
                            </div>
                        </label>

                        <div class="col-sm-8">
                            <?php echo $extStr; ?>
                        </div>
                    </div>
                    <div class="clear"></div>
                    <div class="form-group">
                        <label for="quota" class="col-sm-4 control-label">Disk Size Quota:</label>

                        <div class="col-sm-3">
                            <input class="form-control" name="quota" type="text" id="quota" placeholder="MB"
                                   value="<?php echo $quota ?>"/>
                        </div>
                        <div class="icon-info">MB
                            <img src="images/info.png" height="18" width="18" class="tipTip" title="Maximum allocated storage space for this user."></div>
                    </div>
                    <div class="clear"></div>
                    <div class="clear"></div>
                    <div class="form-group">
                        <label for="quota" class="col-sm-4 control-label">Maximum File Size:</label>

                        <div class="col-sm-3">
                            <input class="form-control" name="filesize" type="text" id="filesize" placeholder="MB"
                                   value="<?php echo $filesize ?>"/>
                        </div>
                        <div class="icon-info">MB
                            <img src="images/info.png" height="18" width="18" class="tipTip" title="Maximum file size per each file uploaded."></div>
                    </div>
                    <div class="clear"></div>
                    <div class="form-group folder">
                        <label for="email" class="col-sm-4 control-label">Directories:
                            <div class="subLabel">
                                <a href="javascript:;" onclick="javascript:chkFolders( true);">check all</a>
                                | <a href="javascript:;" onclick="javascript:chkFolders( false);">uncheck all</a>
                            </div>
                        </label>

                        <div class="col-sm-8">

                            <?php getFoldersCheckForUser();?>
                        </div>
                    </div>
                    <div class="clear"></div>
                    <div class="form-group popup-form" >
                        <div class="col-sm-offset-4 col-sm-3">
                            <button type="submit" class="btn btn-primary btn-block">Save</button>
                        </div>
                        <div class="col-sm-offset-1 col-sm-4">
                            <button class="btn btn-danger btn-block" name="admin" type="submit">Set As admin</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        <?php include "includes/footer.php";
    }
}?>