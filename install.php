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
//Load the database file

$tt = "";
$BWContinue = true;
$success = false;

//1. check that includes/ is writable
//2. if not - throw error, else show form.
//3. form will have 4 fields for database and 1 field for license key and 1 key for user to enter future username name for this license key.
//4. after form submitted we need to show success message and further instructions.
if (!is_writable("includes/")) {
    @chmod("includes/", 0777);
    if (!is_writable("includes/")) {
        @chmod("includes/", 777);
        if (!is_writable("includes/")) {
            //chmoding didn't help. throw error
            $BWContinue = false;
            $BWMessage .= "<div class='loginMessage loginError'><b>ERROR!</b> Please set chmod 755 or 777 for directory \"includes\"</div>";
        }
    }
}

if (!is_writable("includes/backup_db/")) {
    @chmod("includes/backup_db/", 0777);
    if (!is_writable("includes/backup_db/")) {
        @chmod("includes/backup_db/", 777);
        if (!is_writable("includes/backup_db/")) {
            //chmoding didn't help. throw error
            $BWContinue = false;
            $BWMessage .= "<div class='loginMessage loginError'><b>ERROR!</b> Please set chmod 755 or 777 for directory \"includes/backup_db\"</div>";
        }
    }
}

if (!is_writable("uploads/")) {
    @chmod("uploads/", 0777);
    if (!is_writable("uploads/")) {
        @chmod("uploads/", 777);
        if (!is_writable("uploads/")) {
            //chmoding didn't help. throw error
            $BWContinue = false;
            $BWMessage .= "<div class='loginMessage loginError'><b>ERROR!</b> Please set chmod 755 or 777 for directory \"uploads\"</div>";
        }
    }
}
$admin_username = (!empty($_REQUEST['admin_username'])) ? strip_tags(str_replace("'", "`", $_REQUEST['admin_username'])) : '';
$admin_password = (!empty($_REQUEST['admin_password'])) ? strip_tags(str_replace("'", "`", $_REQUEST['admin_password'])) : '';
$admin_email = (!empty($_REQUEST['admin_email'])) ? strip_tags(str_replace("'", "`", $_REQUEST['admin_email'])) : '';
$admin_confirm_password = (!empty($_REQUEST['admin_confirm_password'])) ? strip_tags(str_replace("'", "`", $_REQUEST['admin_confirm_password'])) : '';

$email_from_name = (!empty($_REQUEST["email_from_name"])) ? strip_tags(str_replace("'", "`", $_REQUEST["email_from_name"])) : '';
$email_from_email = (!empty($_REQUEST["email_from_email"])) ? strip_tags(str_replace("'", "`", $_REQUEST["email_from_email"])) : '';

$dbn = (!empty($_REQUEST['dbn'])) ? strip_tags(str_replace("'", "`", $_REQUEST['dbn'])) : '';
$dbp = (!empty($_REQUEST['dbp'])) ? strip_tags(str_replace("'", "`", $_REQUEST['dbp'])) : '';
$dbu = (!empty($_REQUEST['dbu'])) ? strip_tags(str_replace("'", "`", $_REQUEST['dbu'])) : '';
$dbh = (!empty($_REQUEST['dbh'])) ? strip_tags(str_replace("'", "`", $_REQUEST['dbh'])) : '';
$bdir = (!empty($_REQUEST['bdir'])) ? strip_tags(str_replace("'", "`", $_REQUEST['bdir'])) : '';
$dbpr = (!empty($_REQUEST['dbpr'])) ? strip_tags(str_replace("'", "`", $_REQUEST['dbpr'])) : 'afm_';

$license = (!empty($_REQUEST['license'])) ? strip_tags(str_replace("'", "`", $_REQUEST['license'])) : '';
$username = (!empty($_REQUEST['username'])) ? strip_tags(str_replace("'", "`", $_REQUEST['username'])) : '';

$install = (!empty($_REQUEST['install'])) ? strip_tags(str_replace("'", "`", $_REQUEST['install'])) : '';
//$domain = (!empty($_REQUEST['domain']))?strip_tags(str_replace("'","`",$_REQUEST['domain'])):'';
$domain = $_SERVER['HTTP_HOST'];

if ($BWContinue) {
    //LOGIN VARIABLES
    // LOGIN
    //$domain==""
    if ($install == "yes") {
        if ($dbn == "" || $dbu == "" || $dbh == "" || $license == "" || $bdir == "" || $admin_email == ''|| $email_from_email == ''|| $email_from_name == '') {
            $tt = "<div class='loginMessage loginError'>Some fields were left empty. All fields are mandatory. Try again</div>";
        } elseif ($admin_password != $admin_confirm_password) {
            $tt = "<div class='loginMessage loginError'>Password did not match</div>";
        } else {
            //check DB connection.
            if ($mysqli = @mysqli_connect($dbh, $dbu, $dbp , $dbn)) {
                $BWContinue = true;
            } else {
                $BWContinue = false;
                $BWMessage  = "<div class='loginMessage loginError'><b>ERROR!</b> Couldn't connect to database with provided information. <br />Please check your input and try again.<br />";
                $BWMessage .= sprintf("Connect failed: %s\n", mysqli_connect_error());
                $BWMessage .= "</div>";
            }
            $l = $license;
            if (!is_writable("includes/dbconnect.php")) {
                @chmod("includes/dbconnect.php", 0777);
                if (!is_writable("includes/dbconnect.php")) {
                    //chmoding didn't help. throw error
                    $BWContinue = false;
                    $BWMessage .= "<div class='loginMessage loginError'><b>ERROR!</b> Please set chmod 755 or 777 for file \"includes/dbconnect.php\"</div>";
                }
            }
            include "./includes/grid.functions.php";
            if ($BWContinue) {
                $salt = md5(time());
                //create mysql.php file
                if (@mysqli_connect($dbh, $dbu, $dbp,$dbn) !== false) {
                    $ourFileName = "includes/dbconnect.php";
                    if($bdir=="/") {
                        $bdir = "/" . trim($bdir, "/");
                    } else {
                        $bdir        = "/" . trim($bdir, "/") . "/";
                    }
                    $fh          = fopen($ourFileName, 'w+');
                    $stringData  = '<?php
                error_reporting(E_ALL ^ E_NOTICE);
                $script_dir          = \'' . $bdir . '\'; // IF IN ROOT leave like this: \'/\'  if in root/files   then    \'/files/\'
                $upload_dir          = \'' . $bdir . 'uploads/\'; //NOTE: TRAILING FORWARD SLASHES! FULL PATH to current folder relative to root, DON\'T FORGET TO SET permissions for this folder to 777 on UNIX servers.
                $upload_notify_email = \'' . $admin_email . '\'; //email for notifications of new file upload.

                $db_host = \'' . $dbh . '\'; //hostname
                $db_user = \'' . $dbu . '\'; // username
                $db_password = \'' . $dbp . '\'; // password
                $db_name = \'' . $dbn . '\'; //database name

                $db_pr = \'' . $dbpr . '\'; //database prefix

                $md5_salt = \'' . $salt . '\';

                $demo        = false;
                @$mysqli = @mysqli_connect($db_host, $db_user, $db_password, $db_name);

                if (!$mysqli) {
                    header("Location:install.php");
                    exit();
                }
              ?>';
                    fwrite($fh, $stringData);
                    fclose($fh);
                    require_once("includes/dbconnect.php");
                    require_once("includes/functions.php");
                    require_once("includes/sql.php");
                    if ($BWContinue) {
                        $BWMessage .= "<br/><br/><div class='loginMessage loginSuccess'>Installation successful! Please delete this file now and go to <a href='index.php'>homepage</a></div>";
                        $success = true;
                        $a       = auth($l, $username, $domain);
                        @chmod("includes/dbconnect.php", 0644);
                    }
                } else {
                    $BWMessage .= "<div class='loginMessage loginError'><b>ERROR!</b> cannot connect to database. Check your input</div>";
                }
            }
        }
    }
}

include "includes/header_static.php";
?>
<h1 class="header_install">Advanced File Management - Installation</h1>
<div id="content">
<div class="content_block">
    <div class="install_container">
        <div class="login">
            <?php if (!empty($tt)) {
                echo $tt;
            } ?>
            <?php if (!empty($BWMessage)) {
                echo $BWMessage;
            }

            if ($success) {
            } else {
                ?>

                <br/>
                <form method="post" action="install.php" enctype="multipart/form-data" name="ff1"
                      class="form-horizontal install-form" role="form">
                    <p>Please follow on screen instructions to install AFM3 for the first time.<br/>
                        If you're upgrading from previous version - you should use <a href="upgrade.php">upgrade wizard</a> instead.</p>

                    <h3>Create Administrator Account</h3>

                    <div class="form-group">
                        <label class="col-sm-4 control-label" for="admin_username">Administrator Username:</label>

                        <div class="col-sm-4">
                            <input type="text" class="form-control" id="admin_username"
                                   placeholder="Administrator Username" name="admin_username"
                                   value="<?php if (isset($admin_username)) {
                                       echo $admin_username;
                                   } ?>">
                        </div>
                    </div>
                    <div class="clear"></div>
                    <div class="form-group">
                        <label class="col-sm-4 control-label" for="admin_password">Administrator Password:</label>

                        <div class="col-sm-4">
                            <input type="password" class="form-control" id="admin_password"
                                   placeholder="Administrator Password" name="admin_password">
                        </div>
                    </div>
                    <div class="clear"></div>
                    <div class="form-group">
                        <label class="col-sm-4 control-label" for="admin_confirm_password">Confirm Password:</label>

                        <div class="col-sm-4">
                            <input type="password" class="form-control" id="admin_confirm_password"
                                   placeholder="Confirm Password" name="admin_confirm_password">
                        </div>
                    </div>
                    <div class="clear"></div>
                    <div class="form-group">
                        <label class="col-sm-4 control-label" for="admin_email">Email:</label>

                        <div class="col-sm-4">
                            <input type="Email" class="form-control" id="admin_email"
                                   placeholder="Email" name="admin_email"
                                   value="<?php echo($admin_email) ?>">
                        </div>
                        <div class="icon-info"><img src="images/info.png" height="18" width="18" class="tipTip"
                                                    title="Email for notifications like new file upload, new user registration, new message added for file, etc."></div>
                    </div>
                    <div class="clear"></div>
                    <div class="form-group">
                        <label class="col-sm-4 control-label" for="admin_email">Notifications Sender Name:</label>

                        <div class="col-sm-4">
                            <input type="text" class="form-control" id="email_from_name"
                                   placeholder="Name" name="email_from_name"
                                   value="<?php echo($email_from_name) ?>">
                        </div>
                        <div class="icon-info"><img src="images/info.png" height="18" width="18" class="tipTip"
                                                    title="Sender name of all the emails which will be sent from AFM3"></div>
                    </div>
                    <div class="clear"></div>
                    <div class="form-group">
                        <label class="col-sm-4 control-label" for="admin_email">Notifications Sender Email:</label>

                        <div class="col-sm-4">
                            <input type="Email" class="form-control" id="email_from_email"
                                   placeholder="Email" name="email_from_email"
                                   value="<?php echo($email_from_email) ?>">
                        </div>
                        <div class="icon-info"><img src="images/info.png" height="18" width="18" class="tipTip"
                                                    title="Sender email of all the emails which will be sent from AFM3.<br/> We recommend setting this to noreply@yourdomain.com"></div>
                    </div>
                    <div class="clear"></div>
                    <h3>Database Information</h3>

                    <p class="alert alert-danger">Please enter your <strong>EXISTING</strong> database login information. AFM does not create a database, it only uses the one which you specify below to populate with tables, so it has to exist before proceeding!
                        All fields are <strong>mandatory</strong>.</p>

                    <div class="clear"></div>
                    <div class="form-group">
                        <label class="col-sm-4 control-label" for="dbh">Database host:</label>

                        <div class="col-sm-4">
                            <input type="text" class="form-control" id="dbh" placeholder="Database host" name="dbh"
                                   value="<?php echo($dbh) ?>">
                        </div>
                        <div class="icon-info"><img src="images/info.png" height="18" width="18" class="tipTip"
                                                    title="In most cases this is 'localhost' "></div>
                    </div>
                    <div class="clear"></div>
                    <div class="form-group">
                        <label class="col-sm-4 control-label" for="dbh">Database tables prefix:</label>

                        <div class="col-sm-4">
                            <input type="text" class="form-control" id="dbh" placeholder="Database tables prefix" name="dbpr"
                                   value="<?php echo($dbpr) ?>">
                        </div>
                        <div class="icon-info"><img src="images/info.png" height="18" width="18" class="tipTip"
                                                    title="All AFM database tables will begin with this prefix. It has to be unique,<br/>so there wouldn't be any conflicts with your existing data in db."></div>
                    </div>
                    <div class="clear"></div>
                    <div class="form-group">
                        <label class="col-sm-4 control-label" for="dbn">Database name:</label>

                        <div class="col-sm-4">
                            <input type="text" class="form-control" id="dbn" placeholder="Database name" name="dbn"
                                   value="<?php echo($dbn) ?>">
                        </div>

                    </div>
                    <div class="clear"></div>
                    <div class="form-group">
                        <label class="col-sm-4 control-label" for="dbu">Database username:</label>

                        <div class="col-sm-4">
                            <input type="text" class="form-control" id="dbu" placeholder="Database username" name="dbu"
                                   value="<?php echo($dbu) ?>">
                        </div>

                    </div>
                    <div class="clear"></div>
                    <div class="form-group">
                        <label class="col-sm-4 control-label" for="dbp">Database password:</label>

                        <div class="col-sm-4">
                            <input type="password" class="form-control" id="dbp" placeholder="Database password"
                                   name="dbp" value="<?php echo($dbp) ?>">
                        </div>

                    </div>
                    <div class="clear"></div>
                    <div class="form-group">
                        <label class="col-sm-4 control-label" for="bdir">Directory path:</label>

                        <div class="col-sm-4">
                            <input type="text" class="form-control" id="bdir" placeholder="Directory path" name="bdir"
                                   value="<?php echo empty($bdir)?str_replace(basename(__FILE__),"",$_SERVER["REQUEST_URI"]):$bdir; ?>">
                        </div>
                        <div class="icon-info"><img src="images/info.png" height="18" width="18" class="tipTip"
                                                    title="If you will be accessing AFM at  yourdomain.com/filemanager then you must enter in this field '/filemanager/',<br />if you plan to use yourdomain.com for filemanager then leave as '/'."></div>
                    </div>
                    <div class="clear"></div>
                    <h3>Envato License Verification</h3>

                    <p>
                        Please enter your ITEM PURCHASE CODE (located in the license certificate from Envato. You can login to your codecanyon account and go to downloads,
                        you will see green button DOWNLOAD next to our product, click it and select License Certificate (txt or PDF). Once you open that file - you will see item purchase code inside. <a href="http://support.convergine.com/bb-plugins/epcv/key_instructions.jpg" target="_blank">Example instructions</a>.
                    <br />Item purchase code looks like this: aa1111c11-111f-1111-b1a1-ce11f1ffa111</p>
                    <div class="clear"></div>
                    <div class="form-group">
                        <label class="col-sm-4 control-label" for="license">Item Purchase Code:</label>

                        <div class="col-sm-4">
                            <input type="text" class="form-control" id="license" placeholder="Item Purchase Code"
                                   name="license" value="<?php echo($license) ?>">
                        </div>
                        <div class="icon-info"><img src="images/info.png" height="18" width="18" class="tipTip"
                                                    title="If you don't have item purchase code - please read instructions above this field."></div>
                    </div>
                    <div class="clear"></div>
                    <div class="form-group">
                        <label class="col-sm-4 control-label" for="username">Username:</label>

                        <div class="col-sm-4">
                            <input type="text" class="form-control" id="username" placeholder="Username" name="username"
                                   value="<?php echo($username) ?>">
                        </div>
                        <div class="icon-info"><img src="images/info.png" height="18" width="18" class="tipTip"
                                                    title="YOUR username which you enter when you login to Envato marketplaces."></div>
                    </div>
                    <div class="clear"></div>
                    <div class="form-group">
                        <div class="col-sm-offset-4 col-sm-4">
                            <button type="submit" class="btn btn-primary btn-lg btn-block">Install</button>
                            <input type="hidden" name="install" value="yes"/>
                        </div>
                    </div>
                </form>
            <?php } ?>
            <div class="clear"></div>
        </div>
        <div class="clear"></div>
    </div>
</div>
<div class="clear"></div>
</div>
<?php include "includes/footer.php" ?>