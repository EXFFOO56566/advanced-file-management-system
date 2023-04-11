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
$showTab = 1;

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
        $notify_delete           = (!empty($_REQUEST["notify_delete"])) ? strip_tags(str_replace("'", "`", $_REQUEST["notify_delete"])) : '';
        $notify_edit             = (!empty($_REQUEST["notify_edit"])) ? strip_tags(str_replace("'", "`", $_REQUEST["notify_edit"])) : '';
        $notify_upload           = (!empty($_REQUEST["notify_upload"])) ? strip_tags(str_replace("'", "`", $_REQUEST["notify_upload"])) : '';
        $notify_email            = (!empty($_REQUEST["notify_email"])) ? strip_tags(str_replace("'", "`", $_REQUEST["notify_email"])) : '';
        $auto_approve            = (!empty($_REQUEST["auto_approve"])) ? strip_tags(str_replace("'", "`", $_REQUEST["auto_approve"])) : '';
        $allow_registrations     = (!empty($_REQUEST["allow_registrations"])) ? strip_tags(str_replace("'", "`", $_REQUEST["allow_registrations"])) : '';
        $require_login_download  = (!empty($_REQUEST["require_login_download"])) ? strip_tags(str_replace("'", "`", $_REQUEST["require_login_download"])) : '';
        $public_directory        = (!empty($_REQUEST["public_directory"])) ? strip_tags(str_replace("'", "`", $_REQUEST["public_directory"])) : '';
        $auto_create_user_folder = (!empty($_REQUEST["auto_create_user_folder"])) ? strip_tags(str_replace("'", "`", $_REQUEST["auto_create_user_folder"])) : '';
        $quota       = (!empty($_REQUEST["quota"])) ? strip_tags(str_replace("'", "`", $_REQUEST["quota"])) : '';
        $filesize    = (!empty($_REQUEST["filesize"])) ? strip_tags(str_replace("'", "`", $_REQUEST["filesize"])) : '';
        $extensions  = (!empty($_REQUEST["extensions"])) ? $_REQUEST["extensions"] : array();
        $upload_dirs = (!empty($_REQUEST["upload_dirs"])) ? $_REQUEST["upload_dirs"] : array();
        $new_pass  = (!empty($_REQUEST["new_pass"])) ? strip_tags(str_replace("'", "`", $_REQUEST["new_pass"])) : '';
        $new_pass2 = (!empty($_REQUEST["new_pass2"])) ? strip_tags(str_replace("'", "`", $_REQUEST["new_pass2"])) : '';

        $email_from_name = (!empty($_REQUEST["email_from_name"])) ? strip_tags(str_replace("'", "`", $_REQUEST["email_from_name"])) : '';
        $email_from_email = (!empty($_REQUEST["email_from_email"])) ? strip_tags(str_replace("'", "`", $_REQUEST["email_from_email"])) : '';

        $smtp_protocol = (!empty($_REQUEST["smtp_protocol"])) ? strip_tags(str_replace("'", "`", $_REQUEST["smtp_protocol"])) : '';
        $smtp_port = (!empty($_REQUEST["smtp_port"])) ? strip_tags(str_replace("'", "`", $_REQUEST["smtp_port"])) : '';
        $smtp_password = (!empty($_REQUEST["smtp_password"])) ? strip_tags(str_replace("'", "`", $_REQUEST["smtp_password"])) : '';
        $smtp_username = (!empty($_REQUEST["smtp_username"])) ? strip_tags(str_replace("'", "`", $_REQUEST["smtp_username"])) : '';
        $smtp_server = (!empty($_REQUEST["smtp_server"])) ? strip_tags(str_replace("'", "`", $_REQUEST["smtp_server"])) : '';
        $sendmail_path = (!empty($_REQUEST["sendmail_path"])) ? strip_tags(str_replace("'", "`", $_REQUEST["sendmail_path"])) : '';

        if (!empty($_REQUEST["submit_email"]) && $_REQUEST["submit_email"] == "yes") {
            $sql = "UPDATE {$db_pr}settings SET
            notify_delete='" . $notify_delete . "',
            notify_edit='" . $notify_edit . "',
            notify_upload='" . $notify_upload . "',
            notify_email='" . $notify_email . "',
            email_from_name='" . $email_from_name . "',
            email_from_email='" . $email_from_email . "'
            WHERE id='1'";
            $result = mysqli_query($mysqli,$sql) or die("Error occurred - tried  to save settings.");
            $msg = "<div class='loginMessage loginSuccess' >Settings saved!</div>";
            addLog($_SESSION["idUser"], "Updated script settings.");
        }
        if (!empty($_REQUEST["submit_user"]) && $_REQUEST["submit_user"] == "yes") {
            $sql = "UPDATE {$db_pr}settings SET
              allow_registrations='" . $allow_registrations . "',
              auto_approve='" . $auto_approve . "',
              require_login_download='" . $require_login_download . "',
              public_directory='" . $public_directory . "',
              auto_create_user_folder='" . $auto_create_user_folder . "',

              extensions='" . join(",", $extensions) . "',
              upload_dirs='" . join(",", $upload_dirs) . "',
              quota='" . $quota . "',
              filesize='" . $filesize . "'

                WHERE id='1'";
            $result = mysqli_query($mysqli,$sql) or die("Error occurred - tried  to save settings.");
            $msg = "<div class='loginMessage loginSuccess' >Settings saved!</div>";
            addLog($_SESSION["idUser"], "Updated script settings.");
            $showTab = 3;
        }
        if (isset($_POST['restore']) && !$demo) {
            $file_extension = pathinfo($_FILES['dbfile']['name']);
            $file_extension = $file_extension['extension'];
            if (!empty($_FILES['dbfile']['name'])) {
                if ($file_extension == "sql") {
                    $restore_directory = "restore_db/";
                    $files             = glob($restore_directory . "*.sql");
                    foreach ($files as $file) {
                        unlink($file);
                    }
                    $path   = "restore_db" . "/" . $_FILES['dbfile']['name'];
                    $upload = move_uploaded_file($_FILES['dbfile']['tmp_name'], $path);
                    if ($upload) {
                        $restore_directory = "restore_db/";
                        $dbfile            = array();
                        $files             = glob($restore_directory . "*.sql");
                        $i                 = 0;
                        foreach ($files as $file) {
                            $dbfile[] = $file;
                            $i++;
                        }
                        rsort($dbfile);
                        getRestore($dbfile[0], $db_host, $db_user, $db_password, $db_name);
                        $msg .= "<div class='loginMessage loginSuccess'>Database has been restored successfully.</div>";
                    }
                } else {
                    $msg .= "<div class='loginMessage loginError'>File must be .SQL</div>";
                }
            } else {
                $msg .= "<div class='loginMessage loginError'>Please select file.</div>";
            }
            $showTab = 4;
        } else if(isset($_POST['restore']) && $demo){
            $msg .= "<div class='loginMessage loginError'>Backup restore not allowed in preview mode.</div>";
        }

        if (isset($_POST['smpt_settings'])) {
            $sql = "UPDATE {$db_pr}settings SET
                    smtp_protocol   ='" . $smtp_protocol . "',
                    smtp_port       ='" . $smtp_port . "',
                    smtp_password   ='" . $smtp_password . "',
                    smtp_username   ='" . $smtp_username . "',
                    smtp_server     ='" . $smtp_server . "',
                    sendmail_path   ='" . $sendmail_path . "'
                    WHERE id='1'";
            $result = mysqli_query($mysqli,$sql) or die("Error occurred - tried  to save settings.");
            $msg = "<div class='loginMessage loginSuccess' >Settings saved!</div>";
            addLog($_SESSION["idUser"], "Updated script settings.");
            $showTab = 2;
        }

        //select settings from databse
        $sSQL = "SELECT * FROM {$db_pr}settings WHERE id='1'";
        $result = mysqli_query($mysqli,$sSQL) or die("err: " . mysqli_error($mysqli) . $sSQL);
        if ($row = mysqli_fetch_assoc($result)) {
            foreach ($row as $key => $value) {
                $$key = $value;
            }
        }
        mysqli_free_result($result);
        $extStr         = "";
        $extensionsArr  = $extensions;
        $new_extensions = getExtensions();
        $cc             = 1;
        for ($i = 0; $i < count($new_extensions); $i++) {
            $extStr .= "<label class=\"checkbox-inline fixed\"><input type='checkbox' name='extensions[]' class='chkExtension' value=\"" . $new_extensions[$i] . "\" ";
            if (is_array($extensionsArr)) {
                if (in_array($new_extensions[$i], $extensionsArr)) {
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
        include "includes/header.php";
        ?>
        <link rel="stylesheet" href="includes/uploader/css/jquery.fileupload.css">
        <script>
            $(document).ready(function () {
                $("#approve_yes").click(function () {
                    document.getElementById('approve').style.display = 'block';
                });
                $("#approve_no").click(function () {
                    document.getElementById('approve').style.display = 'none';
                });
                $("[name='require_login_download']").click(function(){
                    var val = $(this).val();
                    if(val=='2'){
                        $("#public_directory_cont").show();
                    }else{
                        $("#public_directory_cont").hide();
                    }
                });
                $("[name='smtp_protocol']").change(function(){
                    var $el = $(this);
                    switch($el.val()){
                        case "php_mail": $(".sendmail_cont").hide();$(".smtp_cont").hide();
                            break;
                        case "sendmail":$(".sendmail_cont").show();$(".smtp_cont").hide();
                            break;
                        case "smtp":$(".sendmail_cont").hide();$(".smtp_cont").show();
                            break;
                    }
                });
            });
            function showEmail() {
                var emails = document.getElementById("li_emails");
                emails.classList.add("settings-tab-selected");
                var UserSetting = document.getElementById("li_UserSetting");
                UserSetting.classList.remove("settings-tab-selected");
                var showDatabaseTools = document.getElementById("li_DatabaseTools");
                showDatabaseTools.classList.remove("settings-tab-selected");
                var showAdminSettings = document.getElementById("li_AdminSettings");
                showAdminSettings.classList.remove("settings-tab-selected");
                document.getElementById('Page1').style.display = "block";
                document.getElementById('Page2').style.display = "none";
                document.getElementById('Page3').style.display = "none";
                document.getElementById('Page4').style.display = "none";
            }
            function showUserSetting() {
                var UserSetting = document.getElementById("li_UserSetting");
                UserSetting.classList.add("settings-tab-selected");
                var emails = document.getElementById("li_emails");
                emails.classList.remove("settings-tab-selected");
                var showDatabaseTools = document.getElementById("li_DatabaseTools");
                showDatabaseTools.classList.remove("settings-tab-selected");
                var showAdminSettings = document.getElementById("li_AdminSettings");
                showAdminSettings.classList.remove("settings-tab-selected");
                document.getElementById('Page1').style.display = "none";
                document.getElementById('Page2').style.display = "none";
                document.getElementById('Page3').style.display = "block";
                document.getElementById('Page4').style.display = "none";
            }
            function showDatabaseTools() {
                var showDatabaseTools = document.getElementById("li_DatabaseTools");
                showDatabaseTools.classList.add("settings-tab-selected");
                var UserSetting = document.getElementById("li_UserSetting");
                UserSetting.classList.remove("settings-tab-selected");
                var emails = document.getElementById("li_emails");
                emails.classList.remove("settings-tab-selected");
                var showAdminSettings = document.getElementById("li_AdminSettings");
                showAdminSettings.classList.remove("settings-tab-selected");
                document.getElementById('Page1').style.display = "none";
                document.getElementById('Page2').style.display = "none";
                document.getElementById('Page3').style.display = "none";
                document.getElementById('Page4').style.display = "block";
            }
            function showAdminSettings() {
                var showAdminSettings = document.getElementById("li_AdminSettings");
                showAdminSettings.classList.add("settings-tab-selected");
                var showDatabaseTools = document.getElementById("li_DatabaseTools");
                showDatabaseTools.classList.remove("settings-tab-selected");
                var UserSetting = document.getElementById("li_UserSetting");
                UserSetting.classList.remove("settings-tab-selected");
                var emails = document.getElementById("li_emails");
                emails.classList.remove("settings-tab-selected");
                document.getElementById('Page1').style.display = "none";
                document.getElementById('Page2').style.display = "block";
                document.getElementById('Page3').style.display = "none";
                document.getElementById('Page4').style.display = "none";
            }
        </script>
        <link rel="stylesheet" type="text/css" href="css/tabview.css"/>
        <script type="text/javascript">
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
            function download_backup() {
                document.getElementById('backup_img').innerHTML = "<img style=\"margin-left: 20px; width: 31px; position: relative; height: 31px;\" src=\"images/backup_loading.gif\">";
                var xhr;
                if (window.XMLHttpRequest) {
                    xhr = new XMLHttpRequest();
                }
                else {
                    xhr = new ActiveXObject("Microsoft.XMLHTTP");
                }
                xhr.onreadystatechange = function () {
                    if (xhr.readyState == 4 && xhr.status == 200) {
                        var file = xhr.responseText;
                        document.getElementById('backup_img').innerHTML = "<button type=\"button\" class=\"btn btn-info\"  onclick='window.location.href = \"download.php?file="+file+"\"'>Download Backup</button>";

                        //window.location.href = file;
                    }
                };
                xhr.open("GET", "includes/backup_create.php", true);
                xhr.send();
            }
        </script>
        <div id="content-main">
        <div class="content_block">
        <h2>Main Settings</h2>
        <div class="clear"></div>
        <?php if ($msg != '') { ?>
            <?php echo $msg; ?>
        <?php } ?>
        <div class="TabView" id="TabView">
            <ul>
                <li id="li_emails" class="<?php echo $showTab == 1 ? "settings-tab-selected" : "" ?>">
                    <a href="javascript:showEmail();">
                        <div class="settings-tab-icon"><span class='glyphicon glyphicon-envelope'></span></div>
                        <div class="settings-tab-text"><h3>Emails &amp; Notifications</h3>
                            <p>when, where, how...</p></div>
                    </a>
                </li>
                <li id="li_AdminSettings" class="<?php echo $showTab == 2 ? "settings-tab-selected" : "" ?>">
                    <a href="javascript:showAdminSettings();">
                        <div class="settings-tab-icon"><span class='glyphicon glyphicon-cog'></span></div>
                        <div class="settings-tab-text"><h3>SMTP Settings</h3>
                            <p>if you want to use SMTP</p></div>
                    </a>
                </li>
                <li id="li_UserSetting" class="<?php echo $showTab == 3 ? "settings-tab-selected" : "" ?>">
                    <a href="javascript:showUserSetting();">
                        <div class="settings-tab-icon"><span class='glyphicon glyphicon-user'></span></div>
                        <div class="settings-tab-text"><h3>User Settings</h3>

                            <p>new user related stuff</p></div>
                    </a>
                </li>
                <li id="li_DatabaseTools" class="<?php echo $showTab == 4 ? "settings-tab-selected" : "" ?>">
                    <a href="javascript:showDatabaseTools();">
                        <div class="settings-tab-icon"><span class='glyphicon glyphicon-align-justify'></span></div>
                        <div class="settings-tab-text"><h3>Database Tools</h3>

                            <p>backup and restore anytime</p></div>
                    </a>
                </li>
            </ul
        <div class="Pages">
        <div id="Page1" style="display:<?php echo $showTab == 1 ? "block" : "none" ?>">
            <div class="Pad">
                <form action="settings.php" enctype="multipart/form-data" method="post" name="ff1"
                      class="form-horizontal inner-form">
                    <input value="yes" name="submit_email" type="hidden"/>
                    <input value="<?php echo $id; ?>" name="id" type="hidden"/>
                    <h3>Email Notifications</h3>
                    <div class="form-group">
                        <label for="notify_delete" class="col-sm-6 control-label">Send notification about file
                            deletion:</label>
                        <label class="checkbox-inline">
                            <input name="notify_delete" type="radio"
                                   value="1" <?php echo $notify_delete == "1" ? "checked" : "" ?> />
                            Enabled&nbsp;&nbsp;
                        </label>
                        <label class="checkbox-inline">
                            <input name="notify_delete" type="radio"
                                   value="2" <?php echo $notify_delete == "2" ? "checked" : "" ?> />
                            Disabled
                        </label>
                    </div>
                    <div class="form-group">
                        <label for="notify_delete" class="col-sm-6 control-label">Send notification about file
                            editing:</label>
                        <label class="checkbox-inline">
                            <input name="notify_edit" type="radio"
                                   value="1" <?php echo $notify_edit == "1" ? "checked" : "" ?> />
                            Enabled&nbsp;&nbsp;
                        </label>
                        <label class="checkbox-inline">
                            <input name="notify_edit" type="radio"
                                   value="2" <?php echo $notify_edit == "2" ? "checked" : "" ?> />
                            Disabled
                        </label>
                    </div>
                    <div class="form-group">
                        <label for="notify_delete" class="col-sm-6 control-label">Send notification about new file
                            upload:</label>
                        <label class="checkbox-inline">
                            <input name="notify_upload" type="radio"
                                   value="1" <?php echo $notify_upload == "1" ? "checked" : "" ?> />
                            Enabled&nbsp;&nbsp;
                        </label>
                        <label class="checkbox-inline">
                            <input name="notify_upload" type="radio"
                                   value="2" <?php echo $notify_upload == "2" ? "checked" : "" ?> />
                            Disabled
                        </label>
                    </div>
                    <div class="form-group">
                        <label for="notify_email" class="col-sm-6 control-label">Notifications
                            Email:</label>

                        <div class="col-sm-6">
                            <input class="form-control" name="notify_email" type="text" placeholder="Email"
                                   value="<?php echo $notify_email; ?>">
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="notify_email" class="col-sm-6 control-label">Notifications Sender Email:</label>

                        <div class="col-sm-5">
                            <input class="form-control" name="email_from_email" type="text" placeholder="Email"
                                   value="<?php echo $email_from_email; ?>">
                        </div><div class="icon-info"><img src="images/info.png" height="18" width="18" class="tipTip"
                                                    title="Sender email of all the emails which will be sent from AFM3.<br/> We recommend setting this to noreply@yourdomain.com"></div>
                    </div>

                    <div class="form-group">
                        <label for="notify_email" class="col-sm-6 control-label">Notifications Sender Name:</label>

                        <div class="col-sm-5">
                            <input class="form-control" name="email_from_name" type="text" placeholder="Name"
                                   value="<?php echo $email_from_name; ?>">
                        </div><div class="icon-info"><img src="images/info.png" height="18" width="18" class="tipTip"
                                                    title="Sender name of all the emails which will be sent from AFM3"></div>
                    </div>
                    <div class="form-group">
                        <div class="col-sm-offset-6 col-sm-4">
                            <button type="submit" class="btn btn-primary btn-block">Submit</button>
                        </div>
                    </div>
                    <div class="clear"></div>
                </form>
            </div>
        </div>
        <div id="Page2" style="display:<?php echo $showTab == 2 ? "block" : "none" ?>">
            <div class="Pad">
                <form action="settings.php" enctype="multipart/form-data" method="post" name="ff4"
                      class="form-horizontal inner-form" >
                    <input value="yes" name="submit_changes" type="hidden"/>
                    <input value="<?php echo $id; ?>" name="id" type="hidden"/>


                    <div class="form-group">
                        <label for="smtp_protocol" class="col-sm-6 control-label">Protocol:</label>

                        <div class="col-sm-4">
                            <select name="smtp_protocol" class="form-control">
                                <option value="php_mail" <?php echo($smtp_protocol=='php_mail'?"selected":"") ?>>PHP Mail()</option>
                                <option value="sendmail" <?php echo($smtp_protocol=='sendmail'?"selected":"") ?>>Sendmail</option>
                                <option value="smtp" <?php echo($smtp_protocol=='smtp'?"selected":"") ?>>SMTP</option>
                            </select>
                        </div>
                        <div  class="col-sm-2">
                        <p>
                            <button  class="btn btn-success" onclick="$.colorbox({href:'includes/test_smtp.php',iframe:true,width:'500px',height:'350px'});return false;">Test</button>
                        </p>
                        </div>

                    </div>
                    <div class="form-group sendmail_cont" style="display: <?php echo($smtp_protocol=='sendmail'?"block":"none")?>">
                        <label for="sendmail_path" class="col-sm-6 control-label">Sendmail path:</label>

                        <div class="col-sm-6">
                            <input type="text" name="sendmail_path" value="<?php echo($sendmail_path) ?>" class="form-control">
                        </div>

                    </div>

                    <div class="form-group smtp_cont"  style="display: <?php echo($smtp_protocol=='smtp'?"block":"none")?>">
                        <label for="smtp_server" class="col-sm-6 control-label">Server:</label>

                        <div class="col-sm-6">
                            <input type="text" name="smtp_server" value="<?php echo($smtp_server) ?>" class="form-control">
                        </div>

                    </div>
                    <div class="form-group smtp_cont"  style="display: <?php echo($smtp_protocol=='smtp'?"block":"none")?>">
                        <label for="smtp_username" class="col-sm-6 control-label">Username:</label>

                        <div class="col-sm-6">
                            <input type="text" name="smtp_username" value="<?php echo($smtp_username) ?>" class="form-control">
                        </div>

                    </div>
                    <div class="form-group smtp_cont"  style="display: <?php echo($smtp_protocol=='smtp'?"block":"none")?>">
                        <label for="smtp_password" class="col-sm-6 control-label">Password:</label>

                        <div class="col-sm-6">
                            <input type="text" name="smtp_password" value="<?php echo($smtp_password) ?>" class="form-control">
                        </div>

                    </div>
                    <div class="form-group smtp_cont"  style="display: <?php echo($smtp_protocol=='smtp'?"block":"none")?>">
                        <label for="smtp_port" class="col-sm-6 control-label">Port:</label>

                        <div class="col-sm-6">
                            <input type="text" name="smtp_port" value="<?php echo($smtp_port) ?>" class="form-control">
                        </div>

                    </div>

                    <div class="form-group">
                        <div class="col-sm-offset-6 col-sm-4">
                            <button type="submit" class="btn btn-primary btn-block" name="smpt_settings">Submit</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        <div id="Page3" style="display:<?php echo $showTab == 3 ? "block" : "none" ?>">
            <div class="Pad">
                <form action="settings.php" enctype="multipart/form-data" method="post" name="ff2"
                      class="form-horizontal inner-form">
                    <input value="yes" name="submit_user" type="hidden"/>
                    <input value="<?php echo $id; ?>" name="id" type="hidden"/>

                    <h3>Users Settings</h3>
                    <div class="form-group">
                        <label for="notify_delete" class="col-sm-6 control-label">Allow New User Registrations?</label>
                        <label class="checkbox-inline">
                            <input name="allow_registrations" type="radio"
                                   value="1" <?php echo $allow_registrations == "1" ? "checked" : "" ?> /> Enabled&nbsp;&nbsp;
                        </label>
                        <label class="checkbox-inline">
                            <input name="allow_registrations" type="radio"
                                   value="2" <?php echo $allow_registrations == "2" ? "checked" : "" ?> /> Disabled
                        </label>
                    </div>
                    <div class="form-group">
                        <label for="notify_delete" class="col-sm-6 control-label">Automatically create "username" folder
                            for each new user?</label>
                        <label class="checkbox-inline">
                            <input name="auto_create_user_folder" type="radio"
                                   value="1" <?php echo $auto_create_user_folder == "1" ? "checked" : "" ?> /> Enabled&nbsp;&nbsp;
                        </label>
                        <label class="checkbox-inline">
                            <input name="auto_create_user_folder" type="radio"
                                   value="2" <?php echo $auto_create_user_folder == "2" ? "checked" : "" ?> /> Disabled
                        </label>
                    </div>
                    <div class="form-group">
                        <label for="notify_delete" class="col-sm-6 control-label">Automatically approve new
                            members?</label>
                        <label class="checkbox-inline">
                            <input name="auto_approve" type="radio" id="approve_yes"
                                   value="1" <?php echo $auto_approve == "1" ? "checked" : "" ?> /> Enabled&nbsp;&nbsp;
                        </label>
                        <label class="checkbox-inline">
                            <input name="auto_approve" type="radio" id="approve_no"
                                   value="2" <?php echo $auto_approve == "2" ? "checked" : "" ?> /> Disabled
                        </label>
                    </div>
                    <div class="clear"></div>
                    <div id="approve"
                        <?php $qry = mysqli_query($mysqli,"SELECT auto_approve FROM {$db_pr}settings WHERE id='1'");
                        $approve = mysqli_fetch_row($qry);
                        if ($approve[0] == 1) {
                            echo " style='display:block;'";
                        } elseif ($approve[0] == 2) {
                            echo " style='display:none;'";
                        }
                        ?>
                        >
                        <div class="form-group">
                            <label for="quota" class="col-sm-6 control-label">Default Bandwidth Quota:</label>

                            <div class="col-sm-3">
                                <input class="form-control" name="quota" type="text" id="quota" placeholder="MB"
                                       value="<?php echo $quota ?>"/>
                            </div>
                            <div class="col-sm-2"><p class="form-control-static">MB</p></div>
                        </div>
                        <div class="form-group">
                            <label for="quota" class="col-sm-6 control-label">Default Maximum Filesize:</label>

                            <div class="col-sm-3">
                                <input class="form-control" name="filesize" type="text" id="filesize" placeholder="MB"
                                       value="<?php echo $filesize ?>"/>
                            </div>
                            <div class="col-sm-2"><p class="form-control-static">MB</p></div>
                        </div>
                        <div class="form-group">
                            <label for="email" class="col-sm-6 control-label">Extensions:
                                <div class="subLabel">
                                    <a href="javascript:;" onclick="javascript:chkExtensions('ff2', true);">check
                                        all</a>
                                    | <a href="javascript:;" onclick="javascript:chkExtensions('ff2', false);">uncheck
                                        all</a>
                                </div>
                            </label>

                            <div class="col-sm-6">
                                <?php echo $extStr; ?>
                            </div>
                        </div>
                        <div class="form-group folder">
                            <label for="email" class="col-sm-6 control-label">Default Upload Directories:
                                <div class="subLabel">
                                    <a href="javascript:;" onclick="javascript:chkFolders( true);">check all</a>
                                    | <a href="javascript:;" onclick="javascript:chkFolders( false);">uncheck all</a>
                                </div>
                            </label>

                            <div class="col-sm-6">

                                <?php $upload_dirs = !is_array($upload_dirs)?explode(",", $upload_dirs):$upload_dirs;
                                 getFoldersCheckForUser($upload_dirs)?>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="notify_delete" class="col-sm-6 control-label">Require user login to
                            download?</label>
                        <label class="checkbox-inline">
                            <input name="require_login_download" type="radio"
                                   value="1" <?php if ($require_login_download == "1") {
                                echo "checked";
                            } else {
                                echo "";
                            } ?> /> Enabled&nbsp;&nbsp;
                        </label>
                        <label class="checkbox-inline">
                            <input name="require_login_download" type="radio"
                                   value="2" <?php if ($require_login_download == "2") {
                                echo "checked";
                            } else {
                                echo "";
                            } ?> /> Disabled
                        </label>
                    </div>

                    <div class="form-group" id="public_directory_cont" style="display: <?php echo($require_login_download==2?"block":"none")?>">
                        <label for="public_directory" class="col-sm-6 control-label">Enable users public directory?
                            <img width="18" height="18" class="tipTip" src="images/info.png" title="If enabled, users will be able to share folders which they are assigned to.<br/>In share file section, there will be new link to share the whole folder file belongs to."></label>
                        <label class="checkbox-inline">
                            <input name="public_directory" type="radio"
                                   value="1" <?php if ($public_directory == "1") {
                                echo "checked";
                            } else {
                                echo "";
                            } ?> /> Enabled&nbsp;&nbsp;
                        </label>
                        <label class="checkbox-inline">
                            <input name="public_directory" type="radio"
                                   value="2" <?php if ($public_directory == "2") {
                                echo "checked";
                            } else {
                                echo "";
                            } ?> /> Disabled
                        </label>
                    </div>

                    <div class="form-group">
                        <div class="col-sm-offset-6 col-sm-4">
                            <button type="submit" class="btn btn-primary btn-block">Submit</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        <div id="Page4" style="display:<?php echo $showTab == 4 ? "block" : "none" ?>">
            <div class="Pad">
                <form action="settings.php" name="ff3" method="post" enctype="multipart/form-data"
                      onsubmit="return confirm('Are you sure you want to restore database?');" class="form-horizontal inner-form">
                    <input value="yes" name="submit_database" type="hidden"/>
                    <input value="<?php echo $id; ?>" name="id" type="hidden"/>

                    <h3>Database tools</h3>
                    <div class="form-group">
                        <label for="" class="col-sm-6 control-label">Backup AFM database:</label>

                        <div class="col-sm-4" id="backup_img">
                            <button type="button" class="btn btn-primary col-sm-10" id=""
                                    onclick='download_backup();return false'>Download Backup
                            </button>
                            <img style="display: none" src="images/backup_loading.gif">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="" class="col-sm-6 control-label">Restore AFM database:</label>

                        <div class="col-sm-4">
                            <span class="btn btn-primary fileinput-button text-left col-sm-10">
                                                <i class="glyphicon glyphicon-plus"></i>
                                                <span>Select file...</span>
                                                <input type="file" multiple="" name="dbfile" id="fileupload">
                                            </span>

                        </div>
                        <div class="col-sm-2">
                            <button type="submit" class="btn btn-success text-left" name="restore" >Restore</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        </div>
        </div>
        </form>
        <div class="clear"></div>
        </div>
        <div class="clear"></div>
        </div>
        </div>
        <?php include "includes/footer.php";
    } else {
        header("Location: main.php");
    }
} ?>
