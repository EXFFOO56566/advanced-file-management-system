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


        $query = "CREATE TABLE `{$db_pr}activitylogs`
        (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `description` longtext,
        `userID` int(20) DEFAULT NULL,
        `date` datetime DEFAULT '0000-00-00 00:00:00',
        PRIMARY KEY (`id`)
        ) ";

        if(mysqli_query($mysqli,$query)){
          $tt .= "Created table '{$db_pr}activitylogs' (1/8)<br/>";
        } else { $tt .= "<div class=error><b>ERROR!</b> can't create {$db_pr}activitylogs (1/8)!<br /></div>"; $BWContinue = false; }
        
        //create categories
        $query = "CREATE TABLE `{$db_pr}categories` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `title` varchar(255) DEFAULT NULL,
            `userID` int(20) DEFAULT NULL,
            `dateCreated` datetime DEFAULT '0000-00-00 00:00:00',
            PRIMARY KEY (`id`)
          )";
        if(mysqli_query($mysqli,$query)){
          $tt .= "Created table '{$db_pr}categories' (2/8)<br/>";
        } else { $tt .= "<div class=error><b>ERROR!</b> can't create {$db_pr}categories (2/8)!<br /></div>"; $BWContinue = false; }
        
        //create extensions
        $query = "CREATE TABLE `{$db_pr}extensions` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `dateCreated` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
            `name` varchar(255) NOT NULL,
            PRIMARY KEY (`id`)
          )";
        if(mysqli_query($mysqli,$query)){
          $tt .=  "Created table '{$db_pr}extensions' (3/8)<br/>";

            $query= "INSERT INTO `{$db_pr}extensions` (`dateCreated`, `name`) VALUES (NOW(),'AVI'), (NOW(),'BMP'), (NOW(),'BSH'), (NOW(),'C'), (NOW(),'CC'), (NOW(),'CPP'), (NOW(),'CS'),
             (NOW(),'CSH'), (NOW(),'CSS'), (NOW(),'CV'), (NOW(),'CYC'), (NOW(),'DOC'), (NOW(),'GIF'), (NOW(),'HTM'), (NOW(),'HTML'), (NOW(),'JAVA'), (NOW(),'JPEG'), (NOW(),'JPG'), (NOW(),'JS'), (NOW(),'M'),
             (NOW(),'MOV'), (NOW(),'MP3'), (NOW(),'MP4'), (NOW(),'MXML'), (NOW(),'PDF'), (NOW(),'PERL'), (NOW(),'PHP'), (NOW(),'PL'), (NOW(),'PM'), (NOW(),'PNG'), (NOW(),'PY'), (NOW(),'RAR'), (NOW(),'RB'),
             (NOW(),'SH'), (NOW(),'SQL'), (NOW(),'TXT'), (NOW(),'VB'), (NOW(),'WMA'), (NOW(),'WMV'), (NOW(),'XHTML'), (NOW(),'XLS'), (NOW(),'XLSX'), (NOW(),'XML'), (NOW(),'XSL'), (NOW(),'ZIP');";
            if(mysqli_query($mysqli,$query)){
                $tt .= "Default settings record created<br/>";
            } else { $tt .= "<div class=error><b>ERROR!</b> can't create default settings<br /></div>"; $BWContinue = false; }


        } else { $tt .= "<div class=error><b>ERROR!</b> can't create {$db_pr}extensions (3/8)!<br /></div>"; $BWContinue = false; }
        


        //create files
        $query = "CREATE TABLE `{$db_pr}files` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `title` varchar(255) DEFAULT NULL,
          `path` varchar(255) DEFAULT NULL,
          `size` double DEFAULT NULL,
          `extension` varchar(20) DEFAULT NULL,
          `userID` int(20) DEFAULT NULL,
          `catID` int(20) DEFAULT NULL,
          `dateUploaded` datetime DEFAULT '0000-00-00 00:00:00',
          PRIMARY KEY (`id`)
        )";

        if(mysqli_query($mysqli,$query)){
          $tt .= "Created table '{$db_pr}files' (4/8)<br/>";
        } else { $tt .= "<div class=error><b>ERROR!</b> can't create {$db_pr}files (4/8)!<br /></div>"; $BWContinue = false; }
  

      //create folders
            $query = "CREATE TABLE `{$db_pr}folders` (
              `id` int(11) NOT NULL AUTO_INCREMENT,
              `dateCreated` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
              `parentID` int(20) NOT NULL DEFAULT '0',
              `name` varchar(255) NOT NULL,
              PRIMARY KEY (`id`)
            )";

            if(mysqli_query($mysqli,$query)){
              $tt .= "Created table '{$db_pr}folders' (5/8)<br/>";
              
              $query="INSERT INTO `{$db_pr}folders` (`id`, `dateCreated`, `parentID`, `name`) VALUES ('1', NOW(), '0', 'uploads')";
               if(mysqli_query($mysqli,$query)){
              $tt .= "Default folder created<br/>";
                } else { $tt .= "<div class=error><b>ERROR!</b> can't create default folder<br /></div>"; $BWContinue = false; }

            } else { $tt .= "<div class=error><b>ERROR!</b> can't create {$db_pr}folders (5/8)!<br /></div>"; $BWContinue = false; }
      
       //create messages
            $query = "CREATE TABLE `{$db_pr}messages` (
              `id` int(11) NOT NULL AUTO_INCREMENT,
              `fileID` int(20) NOT NULL,
              `dateCreated` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
              `name` varchar(255) NOT NULL,
              `email` varchar(255) NOT NULL,
              `text` longtext NOT NULL,
              PRIMARY KEY (`id`)
            )";

            if(mysqli_query($mysqli,$query)){
              $tt .= "Created table '{$db_pr}messages' (6/8)<br/>";
            } else { $tt .= "<div class=error><b>ERROR!</b> can't create {$db_pr}messages (6/8)!<br /></div>"; $BWContinue = false; }

      //create settings
            $query = "CREATE TABLE `{$db_pr}settings` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `notify_delete` int(20) DEFAULT '2',
                `notify_upload` int(20) DEFAULT '2',
                `notify_edit` int(20) DEFAULT '2',
                `notify_email` varchar(255) DEFAULT '".$admin_email."',
                `allow_registrations` tinyint(5) NOT NULL DEFAULT '2' COMMENT '1 - allow 2 - dont allow',
                `public_directory` TINYINT( 5 ) NOT NULL DEFAULT  '2' COMMENT '1 - enabled 2 - disabled',
                `auto_approve` tinyint(5) NOT NULL DEFAULT '2' COMMENT '1 - auto 2 - manual',
                `require_login_download` tinyint(5) NOT NULL DEFAULT '2',
                `auto_create_user_folder` tinyint(5) NOT NULL DEFAULT '2',
                `extensions` varchar(255) DEFAULT '',
                `upload_dirs` varchar(255) DEFAULT '',
                `quota` INT NOT NULL ,
                `filesize` INT NOT NULL,
                `email_from_name` VARCHAR( 200 ) DEFAULT '".$email_from_name."',
                `email_from_email` VARCHAR( 200 ) DEFAULT '".$email_from_email."',
                `smtp_protocol` ENUM(  'php_mail',  'sendmail',  'smtp' ) NOT NULL DEFAULT  'php_mail',
                 `smtp_port` VARCHAR( 200 ) NOT NULL ,
                 `smtp_password` VARCHAR( 200 ) NOT NULL ,
                 `smtp_username` VARCHAR( 200 ) NOT NULL ,
                 `smtp_server` VARCHAR( 200 ) NOT NULL ,
                 `sendmail_path` VARCHAR( 200 ) NOT NULL,

                PRIMARY KEY (`id`)
              )";

            if(mysqli_query($mysqli,$query)){
              $tt .= "Created table '{$db_pr}settings' (7/8)<br/>";

              $query= "INSERT INTO `{$db_pr}settings` (`id`, `notify_delete`, `notify_upload`, `notify_edit`, `notify_email`, `allow_registrations`, `auto_approve`, `require_login_download`, `auto_create_user_folder`) VALUES ('1', '2', '2', '2', 'admin@something.com', '2', '2', '2', '2')";
              if(mysqli_query($mysqli,$query)){
              $tt .= "Default settings record created<br/>";
                } else { $tt .= "<div class=error><b>ERROR!</b> can't create default settings<br /></div>"; $BWContinue = false; }


            } else { $tt .= "<div class=error><b>ERROR!</b> can't create {$db_pr}settings (7/8)!<br /></div>"; $BWContinue = false; }
       
      //create users
            $query = "CREATE TABLE `{$db_pr}users` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `dateCreated` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
                `username` varchar(255) DEFAULT NULL,
                `password` varchar(255) DEFAULT NULL,
                `email` varchar(255) DEFAULT NULL,
                `extensions` varchar(255) DEFAULT NULL,
                `quota` double DEFAULT NULL,
                `filesize` double DEFAULT NULL,
                `active` tinyint(5) DEFAULT '1',
                `last_login` datetime DEFAULT '0000-00-00 00:00:00',
                `accesslevel` varchar(255) DEFAULT NULL,
                `upload_dir` varchar(255) NOT NULL DEFAULT 'uploads',
                `upload_dirs` varchar(255) NOT NULL,
                PRIMARY KEY (`id`)
              )";

            if(mysqli_query($mysqli,$query)){
              $tt .= "Created table '{$db_pr}users' (8/8)<br/>";
         //create admin

            $query="INSERT INTO `{$db_pr}users` (`id`,`dateCreated`, `username`, `password`, `email`, `extensions`, `quota`, `filesize`, `active`, `last_login`, `accesslevel`, `upload_dir`, `upload_dirs`)
             VALUES ('1',now(), '".$admin_username."', '".md5($admin_password)."', '".$admin_email."', 'AVI,BMP,BSH,C,CC,CPP,CS,CSH,CSS,CV,CYC,DOC,GIF,HTM,HTML,JAVA,JPEG,JPG,JS,M,MOV,MP3,MP4,MXML,PDF,PERL,PHP,PL,PM,PNG,PY,RAR,RB,SH,SQL,TXT,VB,WMA,WMV,XHTML,XLS,XLSX,XML,XSL,ZIP', '10000', '10000', '1', NOW(), 'abcdefghijklmnopqrstuvwxyz', 'uploads', '1')";
			
			if(mysqli_query($mysqli,$query)){
              $tt .= "Default administrator added<br/>";
                } else { $tt .= "<div class=error><b>ERROR!</b> can't add administrator<br /></div>"; $BWContinue = false; }
			

            } else { $tt .= "<div class=error><b>ERROR!</b> can't create {$db_pr}users (8/8)!<br /></div>"; $BWContinue = false; }
       
 
            $query = "CREATE TABLE IF NOT EXISTS `{$db_pr}downloads` (
                    `id` int(11) NOT NULL AUTO_INCREMENT,
                      `idUser` int(11) NOT NULL,
                      `idFile` int(11) NOT NULL,
                      `date` datetime NOT NULL,
                      `size` DECIMAL( 10, 2 ) NOT NULL,
                      PRIMARY KEY (`id`)
                    ) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ";
    if(mysqli_query($mysqli,$query)){
        $tt .= "Table {$db_pr}downloads added<br/>";
    } else { $tt .= "<div class=error><b>ERROR!</b> can't create {$db_pr}Downloads<br /></div>"; $BWContinue = false; }



?>