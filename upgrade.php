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
$showForm = true;

    ######################### DO NOT MODIFY (UNLESS SURE) END ########################
        $license = (!empty($_REQUEST['license'])) ? strip_tags(str_replace("'", "`", $_REQUEST['license'])) : '';
        $username = (!empty($_REQUEST['username'])) ? strip_tags(str_replace("'", "`", $_REQUEST['username'])) : '';
        $db__pr = (!empty($_REQUEST['db__pr'])) ? strip_tags(str_replace("'", "`", $_REQUEST['db__pr'])) : '';
        if (!empty($_REQUEST["install"]) && $_REQUEST['install'] == "yes") {


            if ($license == "" || $username == '') {
                $msg = "<div class='loginMessage loginError'>Some fields were left empty. All fields are mandatory. Try again</div>";
            } else {
                eval(base64_decode("JGl0ZW1fbmFtZSA9ICI2MTY5MyI7DQokZW52YXRvX2FwaWtleSA9ICdrdnRrYnExMXg5N3dqOWptbzhjdW02ZHJqc200c3c5Nyc7DQokZW52YXRvX3VzZXJuYW1lID0gIkNvbnZlcmdpbmUiOw=="));
                $license_to_check = preg_replace('/[^a-zA-Z0-9_ -]/s', '', !empty($license) ? $license : "");
                $continue         = false;
                if (!empty($username) && !empty($license)) {
                    if (!empty($license_to_check) && !empty($envato_apikey) && !empty($envato_username)) {
                        //Initialize curl
                        $api_url = 'http://marketplace.envato.com/api/edge/' . $envato_username . '/' . $envato_apikey . '/verify-purchase:' . $license_to_check . '.json';
                        $ch = curl_init();
                        curl_setopt($ch, CURLOPT_URL, $api_url);
                        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
                        curl_setopt($ch, CURLOPT_USERAGENT, "FileKing (Advanced File Management) license validation during upgrade");
                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                        $ch_data = curl_exec($ch);
                        curl_close($ch);
                        if (!empty($ch_data)) {
                            $json_data = json_decode($ch_data, true);
                            if (isset($json_data['verify-purchase']) && count($json_data['verify-purchase']) > 0) {
                                $continue = true;
                                if (strtolower($json_data['verify-purchase']['buyer']) != strtolower(trim($username))) {
                                    $msg .= "<div class='loginMessage loginError'>License key and username do not match.</div>";
                                    $continue = false;
                                }
                            } else {
                                $msg .= "<div class='loginMessage loginError'>Error fetching the info. Possible reason: license key invalid or wrong username. Make sure CURL is enabled on your server!</div>";
                            }
                        } else {
                            $msg .= "<div class='loginMessage loginError'>Something went terribly wrong!</div>";
                        }
                    } else {
                        $msg .= "<div class='loginMessage loginError'>You either didn`t pass the license key into the url or didn`t enter your envato username/apikey into configuration</div>";
                    }
                } else {
                    $msg .= "<div class='loginMessage loginError'>License Key and Username fields are required</div>";
                }
                if ($continue) {

                    if(!isset($db_pr)){
                        $db__pr = trim($db__pr,"_");
                        $dbConnectString = file_get_contents("includes/dbconnect.php");
                        $dbConnectStringTmp= explode('$db_host',$dbConnectString);
                        $dbConnectString = $dbConnectStringTmp[0]."\n\$db_pr='{$db__pr}_';\n\$db_host".$dbConnectStringTmp[1]."\$db_host".$dbConnectStringTmp[2];
                        $dbConnectString = file_put_contents("includes/dbconnect.php",$dbConnectString);
                    }


                    require_once("includes/upgrade.php");
                    $showForm = false;
                    $msg .= "<div class='loginMessage loginSuccess'>Advanced File Management has been successfully upgraded to v3.<br>
                            <a href='index.php'>Back to home page</a></div>";
                }
            }
        }
        include "includes/header_static.php";
        ?>

        <h1 class="header_install">Advanced File Management 3 - Upgrade</h1>
        <div id="content">


            <div class="content_block">
                <div class="install_container">
                    <div class="login">
                        <strong><?php echo $msg; ?></strong>

                        <?php if ($showForm) { ?>
                            <form action="" enctype="multipart/form-data" method="post" name="ff1"
                                  class="form-horizontal inner-form inner-form-upgrade" >
                                <input type="hidden" name="install" value="yes"/>

                                <p class="alert alert-warning">At the moment, it is possible to upgrade only from v2.0 to new v3.0</p>
                                <h3>Envato License Verification</h3>
                                <p>
                                    Please enter your ITEM PURCHASE CODE (located in the license certificate from Envato. You can login to your codecanyon account and go to downloads,
                                    you will see green button DOWNLOAD next to our product, click it and select License Certificate (txt or PDF). Once you open that file - you will see item purchase code inside. <a href="http://support.convergine.com/bb-plugins/epcv/key_instructions.jpg" target="_blank">Example instructions</a>.
                                    Item purchase code looks like this: aa1111c11-111f-1111-b1a1-ce11f1ffa111
                                </p>

                                <div class="form-group">
                                    <label for="inputEmail3" class="col-sm-5 control-label">Your Envato
                                        Username:</label>

                                    <div class="col-sm-5">
                                        <input type="text" class="form-control" id="username" name="username"
                                               placeholder="Username" value="<?php if (isset($username)) {
                                            echo $username;
                                        } ?>" tabindex="1">
                                    </div> <div class="icon-info"><img src="images/info.png" height="18" width="18" class="tipTip"
                                                                       title="YOUR username which you enter when you login to Envato marketplaces."></div>
                                </div>
                                <div class="form-group">
                                    <label for="inputEmail3" class="col-sm-5 control-label">Item Purchase Code:</label>

                                    <div class="col-sm-5">
                                        <input type="text" class="form-control" id="license" placeholder="Code"
                                               name="license" value="<?php if (isset($license)) {
                                            echo $license;
                                        } ?>" tabindex="2">
                                    </div> <div class="icon-info"><img src="images/info.png" height="18" width="18" class="tipTip"
                                                                       title="If you don't have item purchase code - please read instructions above this field."></div>
                                </div>
                                <?php if(!isset($db_pr)){?>
                                    <div class="form-group">
                                        <label for="inputEmail3" class="col-sm-5 control-label">Database tables prefix:</label>

                                        <div class="col-sm-5">
                                            <input type="text" class="form-control" id="db__pr" placeholder="Example: afm"
                                                   name="db__pr" value="" tabindex="3">
                                        </div>
                                    </div>
                                <?php }?>
                                <div class="clear"></div>
                                <div class="form-group">
                                    <div class="col-sm-offset-4 col-sm-4">
                                        <button type="submit" class="btn btn-primary btn-lg btn-block">Upgrade to v3
                                        </button>
                                    </div>
                                </div>
                            </form>
                        <?php } ?>
                    </div>
                    <div class="clear"></div>
                </div>
            </div>
        </div>
        <div class="clear"></div>
    </div>
<?php include "includes/footer.php"; ?>