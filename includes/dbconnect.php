<?php
                error_reporting(E_ALL ^ E_NOTICE);
                $script_dir          = '/'; // IF IN ROOT leave like this: '/'  if in root/files   then    '/files/'
                $upload_dir          = '/uploads/'; //NOTE: TRAILING FORWARD SLASHES! FULL PATH to current folder relative to root, DON'T FORGET TO SET permissions for this folder to 777 on UNIX servers.
                $upload_notify_email = ''; //email for notifications of new file upload.

                $db_host = ''; //hostname
                $db_user = ''; // username
                $db_password = ''; // password
                $db_name = ''; //database name

                $demo        = false;

                $md5_salt = "bc453b8cede9c340f5a154a18184ea5f";
                $mysqli = @mysqli_connect($db_host, $db_user, $db_password, $db_name);

                if (!$mysqli) {
                    header("Location:install.php");
                    exit();
                }


              ?>