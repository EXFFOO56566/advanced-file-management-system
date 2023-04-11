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

$query = "SHOW TABLES";
$pr = "";
$result = mysqli_query($mysqli,$query) or die(mysqli_error($mysqli));
While ($row = mysqli_fetch_array($result)) {
    if( $db_pr."extensions"== $row[0]){
        $pr = $db_pr;
        break;
    }

}

$query = "ALTER TABLE  `{$pr}settings` ADD  `require_login_download` tinyint(5) NOT NULL DEFAULT '2',
                                  ADD `auto_create_user_folder` tinyint(5) NOT NULL DEFAULT '2'";
        if (mysqli_query($mysqli,$query)) {
            $msg .= "Alter settings <br />";
        }

        $query = "ALTER TABLE  `{$pr}settings` ADD  `extensions` VARCHAR( 200 ) NOT NULL ,
                    ADD  `upload_dirs` VARCHAR( 200 ) NOT NULL ,
                    ADD  `quota` INT NOT NULL ,
                    ADD  `filesize` INT NOT NULL,
                    ADD  `email_from_name` VARCHAR( 200 ) NOT NULL ,
                    ADD  `email_from_email` VARCHAR( 200 ) NOT NULL,
                    ADD  `public_directory` TINYINT( 5 ) NOT NULL DEFAULT  '2',
                    ADD  `smtp_protocol` ENUM(  'php_mail',  'sendmail',  'smtp' ) NOT NULL DEFAULT  'php_mail',
                    ADD  `smtp_port` VARCHAR( 200 ) NOT NULL ,
                    ADD  `smtp_password` VARCHAR( 200 ) NOT NULL ,
                    ADD  `smtp_username` VARCHAR( 200 ) NOT NULL ,
                    ADD  `smtp_server` VARCHAR( 200 ) NOT NULL ,
                    ADD  `sendmail_path` VARCHAR( 200 ) NOT NULL ;";

        if (mysqli_query($mysqli,$query)) {
            $msg .= "Alter settings <br />";
        }

        $query="ALTER TABLE  `{$pr}users` ADD  `dateCreated` DATETIME NOT NULL AFTER  `id` ;";
        if (mysqli_query($mysqli,$query)) {
            $msg .= "Alter users <br />";
        }

$query = "CREATE TABLE IF NOT EXISTS `{$pr}downloads` (
`id` int(11) NOT NULL AUTO_INCREMENT,
  `idUser` int(11) NOT NULL,
  `idFile` int(11) NOT NULL,
  `date` datetime NOT NULL,
  `size` DECIMAL( 10, 2 ) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;";

if (mysqli_query($mysqli,$query)) {
    $msg .= "Add {$pr}downloads <br />";
}


