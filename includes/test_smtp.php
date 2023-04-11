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
require_once("dbconnect.php"); //Load the settings
require_once("functions.php"); //Load the functions
$msg = "";
$showTab = 1;

if (isset($_SESSION["logged_in"]) && $_SESSION["logged_in"] != true) {
    die("access denied");
} else {

$action = (!empty($_REQUEST["action"])) ? strip_tags(str_replace("'", "`", $_REQUEST["action"])) : '';
$sendTo = (!empty($_REQUEST["sendTo"])) ? strip_tags(str_replace("'", "`", $_REQUEST["sendTo"])) : '';


    if($action=='sendMail'){
        require_once 'mailer/swift_required.php';
        if (getSettings("smtp_protocol") == "smtp") {
            // Create the Transport
            $smtp_server = getSettings("smtp_server");
            $smtp_port = getSettings("smtp_port");
            $smtp_username = getSettings("smtp_username");
            $smtp_password = getSettings("smtp_password");

            $transport = Swift_SmtpTransport::newInstance($smtp_server, $smtp_port,"ssl")
                ->setUsername($smtp_username)
                ->setPassword($smtp_password);

        } elseif (getSettings("smtp_protocol") == "sendmail") {
            //You could alternatively use a different transport such as Sendmail or Mail:

            // Sendmail
            $transport = Swift_SendmailTransport::newInstance(getSettings("sendmail_path"));
        } else {
            // Mail
            $transport = Swift_MailTransport::newInstance();

        }
// Create the Mailer using your created Transport
        $mailer = Swift_Mailer::newInstance($transport);

        $message .= "Mailer work fine!";
        $subject = "Test email from ".$_SERVER['SERVER_NAME'];
        try {
// Create a message
            $message = Swift_Message::newInstance($subject)
                ->setFrom(array(getSettings('email_from_email') => getSettings('email_from_name')))
                ->setTo(array($sendTo))
                ->setBody($message, 'text/html');


// Send the message
            $result = $mailer->send($message);
            $msg= "<p class='bg-success'>Email has been successfully sent</p>";
        } catch (Exception $e) {

            $msg= "<p class='bg-danger'>ERROR: ".$e->getMessage()."</p>";
        }
    }
?>
    <html xmlns="http://www.w3.org/1999/html">
    <head>
        <link rel="stylesheet" type="text/css" href="../css/bootstrap.css">
        <style>
            form {
                padding: 20px
            }

            p {
                padding: 10px
            }
            .inner-form h2 {
                color: #444444;
                font: 25px "Open Sans",Arial;
            }
        </style>
    </head>
    <body>
    <br>



        <form action="" method="get" class="form-horizontal inner-form">
            <input type="hidden" name="action" value="sendMail">
            <h2>Test Email sending</h2>

            <?php echo($msg) ?>

            <div class="form-group">
                <label for="smtp_protocol" class="col-xs-3 control-label">Send to Email:</label>

                <div class="col-xs-6">
                    <input type="text" name="sendTo" value="<?php echo($sendTo) ?>" class="form-control">
                </div>
            </div>
            <div class="form-group">
                <div class="col-xs-offset-3 col-xs-4">
                    <button type="submit" class="btn btn-primary btn-block" name="smpt_settings">Submit</button>
                </div>
            </div>

        </form>
    </body>

    </html>

<?php
}