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
//GET USERNAME BY USER ID
function getUser($userID)
{
    global $db_pr; global $mysqli;
    $sqll = "SELECT id,username FROM {$db_pr}users WHERE id='" . $userID . "'";
    $result1 = mysqli_query( $mysqli,$sqll);
    $row11 = mysqli_fetch_assoc($result1);
    $processor = $row11["username"];
    return $processor;
}

function getExtensions()
{
    global $db_pr; global $mysqli;
    $processor = array();
    $sqll = "SELECT * FROM {$db_pr}extensions ORDER BY name ASC";
    $result1 = mysqli_query( $mysqli,$sqll);
    while ($row11 = mysqli_fetch_assoc($result1)) {
        $processor[] = $row11["name"];
    }
    return $processor;
}

function getExtensionById($id)
{
    global $db_pr; global $mysqli;
    $processor = array();
    $sqll = "SELECT * FROM {$db_pr}extensions WHERE id='{$id}'";
    $result1 = mysqli_query( $mysqli,$sqll);
    if (mysqli_num_rows($result1))
        return $processor;
    return false;
}

function getCommentCount($fileID)
{
    global $db_pr; global $mysqli;
    $sqll = "SELECT COUNT(id) as countme FROM {$db_pr}messages WHERE fileID='" . $fileID . "'";
    $result1 = mysqli_query( $mysqli,$sqll);
    $row11 = mysqli_fetch_assoc($result1);
    return $row11["countme"];
}

function getUploadDir($user)
{
    global $db_pr; global $mysqli;
    $sqll = "SELECT id,upload_dir FROM {$db_pr}users WHERE id='" . $user . "'";
    $result1 = mysqli_query( $mysqli,$sqll);
    $row11 = mysqli_fetch_assoc($result1);
    return $row11["upload_dir"];
}

function send_mail($subject = "Notification", $message = "")
{
    $upload_notify_email = getSystemMail();
    $from = 'AdvancedFileManager@' . $_SERVER['HTTP_HOST'];
    $return_path = '-f ' . $from;
    mail($upload_notify_email, $subject, $message, "From: $from\nX-Mailer: PHP/ . $phpversion()");
}

function get_gravatar($email, $size = "80")
{
    return '<img src="http://www.gravatar.com/avatar/' . md5($email) . '?s=80&d=' . urlencode("default_avatar") . '" width="' . $size . 'px" height="' . $size . 'px" />';
}

//GET SYSTEM EMAIL TO SEND NOTICE TO
function getSystemMail()
{
    global $db_pr; global $mysqli;
    $sqll = "SELECT notify_email FROM {$db_pr}settings WHERE id='1'";
    $result1 = mysqli_query( $mysqli,$sqll);
    $row11 = mysqli_fetch_assoc($result1);
    $processor = $row11["notify_email"];
    return $processor;
}

//TO SEND OR NOT TO SEND. THAT IS THE QUESTION.
function sendNotice($type)
{
    global $db_pr; global $mysqli;
    $sqll = "SELECT " . $type . " FROM {$db_pr}settings WHERE id='1'";
    $result1 = mysqli_query( $mysqli,$sqll);
    $row11 = mysqli_fetch_assoc($result1);
    $processor = $row11[$type];
    if ($processor == "1") {
        return true;
    } else {
        return false;
    }
}

function sendNoticeMsg($fileID)
{
    //get user from file
    //send email to user and admin.
    $tempUID = getFileInfo($fileID);
    $userID = $tempUID[3];
    $userInfo = getUserInfo($userID);

    /*mail(getSystemMail(),"New message for file ".getFileTitle($fileID)."!", getUser($_SESSION["idUser"])." added message for file ".getFileTitle($fileID),"From: $from\nX-Mailer: PHP/ . $phpversion()");
    mail($userInfo[1],"New message for file ".getFileTitle($fileID)."!", getUser($_SESSION["idUser"])." added message for file ".getFileTitle($fileID),"From: $from\nX-Mailer: PHP/ . $phpversion()");*/
    mail(getSystemMail(), "New message for file " . getFileTitle($fileID) . "!", getUser($_SESSION["idUser"]) . " added message for file " . getFileTitle($fileID), "MIME-Version: 1.0\n Content-type: text/html; charset=utf-8\n From: 'AdvancedFileManager' <noreply@" . $_SERVER['HTTP_HOST'] . "> \n");
    mail($userInfo[1], "New message for file " . getFileTitle($fileID) . "!", getUser($_SESSION["idUser"]) . " added message for file " . getFileTitle($fileID), "MIME-Version: 1.0\n Content-type: text/html; charset=utf-8\n From: 'AdvancedFileManager' <noreply@" . $_SERVER['HTTP_HOST'] . "> \n");


}

//GET USER EMAIL / NAME BY USER ID
function getUserInfo($userID)
{
    global $db_pr; global $mysqli;
    $sqll = "SELECT id,username,email FROM {$db_pr}users WHERE id='" . $userID . "'";
    $result1 = mysqli_query( $mysqli,$sqll);
    $row11 = mysqli_fetch_assoc($result1);
    $processor = array();
    $processor[0] = $row11["username"];
    $processor[1] = $row11["email"];
    return $processor;
}

//GET FILE INFO BY FILE ID
function getFileInfo($id)
{
    global $db_pr; global $mysqli;
    $sqll = "SELECT * FROM {$db_pr}files WHERE id='" . $id . "'";
    $result1 = mysqli_query( $mysqli,$sqll);
    $row11 = mysqli_fetch_assoc($result1);
    $processor = array();
    $processor[0] = $row11["title"];
    $processor[1] = $row11["extension"];
    $processor[2] = $row11["path"];
    $processor[3] = $row11["userID"];
    $processor[4] = $row11["catID"];
    return $processor;
}

//GET FILE INFO BY FILE ID
function getFileTitle($id)
{
    global $db_pr; global $mysqli;
    $sqll = "SELECT * FROM {$db_pr}files WHERE id='" . $id . "'";
    $result1 = mysqli_query( $mysqli,$sqll);
    $row11 = mysqli_fetch_assoc($result1);
    $processor = $row11["title"];
    return $processor;
}


function getReg()
{
    global $db_pr; global $mysqli;
    $txt = false;
    $sqll = "SELECT allow_registrations FROM {$db_pr}settings WHERE id='1'";
    $result1 = mysqli_query( $mysqli,$sqll);
    $row11 = mysqli_fetch_assoc($result1);
    if ($row11["allow_registrations"] == "1") {
        $txt = true;
    }
    return $txt;
}

function autoApprove()
{
    global $db_pr; global $mysqli;
    $txt = false;
    $sqll = "SELECT auto_approve FROM {$db_pr}settings WHERE id='1'";
    $result1 = mysqli_query( $mysqli,$sqll);
    $row11 = mysqli_fetch_assoc($result1);
    if ($row11["auto_approve"] == "1") {
        $txt = true;
    }
    return $txt;
}

function getSettings($field)
{
    global $db_pr; global $mysqli;
    $sqll = "SELECT * FROM {$db_pr}settings WHERE id='1'";
    $result1 = mysqli_query( $mysqli,$sqll);
    $row11 = mysqli_fetch_assoc($result1);
    return $row11[$field];
}

// ACTIVITY LOG FUNCTION
function addLog($user, $action)
{
    global $db_pr; global $mysqli;

    $sSQL = "INSERT INTO {$db_pr}activitylogs (date,userID,description) VALUES (NOW(),'" . $user . "','" . $action . "')";
    $result = mysqli_query( $mysqli,$sSQL) or die("Invalid query: " . mysqli_error($mysqli) . "$sSQL");
    $actID = mysqli_insert_id($mysqli);
}

//FILE UPLOADER 
function uploadFile($inputFile, $sFolderPictures)
{
    $image_path = $inputFile['tmp_name'];
    $photoFileNametmp = $inputFile['name'];
    $fileNamePartstmp = explode(".", $photoFileNametmp);
    $fileExtensiontmp = strtolower(end($fileNamePartstmp)); // part behind last dot
    // $arrAllow=array("jpeg", "jpg", "png", "gif");//, "BMP", "TIFF"
    //	if (!in_array($fileExtensiontmp, $arrAllow)) {
    //	$err.= "Picture's extension should be .jpg, .jpeg, .png, or .gif<br />";
    //	}
    if ($inputFile['size'] > 20971520) {
        $ssize = sprintf("%01.2f", $inputFile['size'] / 1048576);
        $err = "Your file is " . $ssize . ". Max file size is 20 MB.";
    }
    if (!isset($err)) {
        $newFile = $_SERVER['DOCUMENT_ROOT'] . $sFolderPictures; //print $newFile;
        $ret = move_uploaded_file($inputFile['tmp_name'], $newFile);
        if (!$ret) {
            ?>
            Upload failed. No file recieved    <?php
        } else {
            $imgPath = $sFolderPictures;
        }
    } else {
        ?>Upload failed. No file recieved
    <?php
    }
    if (file_exists($inputFile['tmp_name'])) {
        @unlink($inputFile['tmp_name']);
    }
    return $imgPath;
}


function randomPassword(
    $length = 8, //string length
    $uselower = 1, //use lowercase letters
    $useupper = 1, // use uppercase letters
    $usespecial = 0, //use special characters
    $usenumbers = 1, //use numbers
    $prefix = ''
)
{
    $key = $prefix;
// Seed random number generator
    srand((double)microtime() * rand(1000000, 9999999));
    $charset = "";
    if ($uselower == 1) $charset .= "abcdefghijkmnopqrstuvwxyz";
    if ($useupper == 1) $charset .= "ABCDEFGHIJKLMNPQRSTUVWXYZ";
    if ($usenumbers == 1) $charset .= "0123456789";
    if ($usespecial == 1) $charset .= "~#$%^*()_+-={}|][";
    while ($length > 0) {
        $key .= $charset[rand(0, strlen($charset) - 1)];
        $length--;
    }
    return $key;
}

function getFolderName($fileID)
{
    global $db_pr; global $mysqli;
    $sqll = "SELECT id,name FROM {$db_pr}folders WHERE id='" . $fileID . "'";
    $result1 = mysqli_query( $mysqli,$sqll);
    $row11 = mysqli_fetch_assoc($result1);
    $folder = $row11["name"];
    echo $folder;
    return $folder;
}

function _getFolderName($fileID)
{
    global $db_pr; global $mysqli;
    $sqll = "SELECT id,name FROM {$db_pr}folders WHERE id='" . $fileID . "'";
    $result1 = mysqli_query( $mysqli,$sqll);
    $row11 = mysqli_fetch_assoc($result1);
    $folder = $row11["name"];
    return $folder;
}

function getFolder($fileID)
{
    global $db_pr; global $mysqli;
    $sqll = "SELECT * FROM {$db_pr}folders WHERE id='" . $fileID . "'";
    $result1 = mysqli_query( $mysqli,$sqll);
    $row11 = mysqli_fetch_assoc($result1);
    return $row11;
}
function getFoldersByParent($idFolder)
{
    global $db_pr; global $mysqli;
    $folders = array();
    $sqll = "SELECT * FROM {$db_pr}folders WHERE parentID='" . $idFolder . "'";
    $result1 = mysqli_query( $mysqli,$sqll);
    if(mysqli_num_rows($result1)){
        while($row = mysqli_fetch_assoc($result1)){
            $folders[]=$row;
        }
    }
    return $folders;
}

function getFolderPathById($id,$str=false){
    global $db_pr; global $mysqli;
    $qq             = "SELECT * FROM {$db_pr}folders WHERE id='" . $id . "'";
    $ress           = mysqli_query($mysqli,$qq);
    $currFolderInfo            = mysqli_fetch_assoc($ress);
    if(!empty($currFolderInfo['parentID'])){
        $q             = "SELECT * FROM {$db_pr}folders WHERE id='" . $currFolderInfo['parentID'] . "'";
        $r           = mysqli_query($mysqli,$q);
        $parentFolderInfo           = mysqli_fetch_assoc($r);
        if(!empty($parentFolderInfo['parentID'])){
            $q1             = "SELECT * FROM {$db_pr}folders WHERE id='" . $parentFolderInfo['parentID'] . "'";
            $r1           = mysqli_query($mysqli,$q1);
            $subParentFolderInfo           = mysqli_fetch_assoc($r1);
            return $str?"{$subParentFolderInfo['name']} / {$parentFolderInfo['name']} / {$currFolderInfo['name']}":
                "{$subParentFolderInfo['name']}/{$parentFolderInfo['name']}/{$currFolderInfo['name']}";
        }else{
            return $str?"{$parentFolderInfo['name']} / {$currFolderInfo['name']}":"{$parentFolderInfo['name']}/{$currFolderInfo['name']}";
        }



    }else{
        return $currFolderInfo['name'];
    }
}

function getFoldersDrop($selected=null){
    $foldersList = getFoldersList(true);
 foreach($foldersList as $id=>$info){?>
    <option value="<?php echo($id)?>" <?php echo($id==$selected?"selected":"")?>><?php echo($info['name'])?></option>
    <?php if(isset($info['children'])){?>

        <?php foreach($info['children'] as $sid=>$sinfo){?>
            <option value="<?php echo($sid)?>" <?php echo($sid==$selected?"selected":"")?>>|- <?php echo($sinfo['name'])?></option>

             <?php if(isset($sinfo['children'])){?>
                 <?php foreach($sinfo['children'] as $ssid=>$ssinfo){?>
                     <option value="<?php echo($ssid)?>" <?php echo($ssid==$selected?"selected":"")?>>&nbsp;|- <?php echo($ssinfo['name'])?></option>
                 <?php }?>
             <?php }?>
        <?php }?>

    <?}?>
<?}

}


function getFoldersCheck($selected=null){
    $foldersList = getFoldersList(true);
    foreach($foldersList as $id=>$info){?>

        <div class="checkbox"><input type='checkbox'  value='<?php echo($id)?>,<?php echo(getFolderPathById($id))?>' name='folders[]' /><?php echo($info['name'])?></div>
        <?php if(isset($info['children'])){?>

            <?php foreach($info['children'] as $sid=>$sinfo){?>

                <div class="checkbox sub"><input type='checkbox'  value='<?php echo($sid)?>,<?php echo(getFolderPathById($sid))?>' name='folders[]' /><?php echo($sinfo['name'])?></div>
                <?php if(isset($sinfo['children'])){?>
                    <?php foreach($sinfo['children'] as $ssid=>$ssinfo){?>

                        <div class="checkbox subSub"><input type='checkbox'  value='<?php echo($ssid)?>,<?php echo(getFolderPathById($ssid))?>' name='folders[]' /><?php echo($ssinfo['name'])?></div>
                    <?php }?>
                <?php }?>
            <?php }?>

        <?}?>
    <?}

}

function getFoldersCheckForUser($selected=array()){
    $foldersList = getFoldersList(true);
    foreach($foldersList as $id=>$info){?>

        <div class="checkbox"><input type='checkbox'  value='<?php echo($id)?>' name='upload_dirs[]' <?php echo(in_array($id,$selected)?"checked":"")?>/><?php echo($info['name'])?></div>
        <?php if(isset($info['children'])){?>

            <?php foreach($info['children'] as $sid=>$sinfo){?>

                <div class="checkbox sub"><input type='checkbox'  value='<?php echo($sid)?>' name='upload_dirs[]' <?php echo(in_array($sid,$selected)?"checked":"")?>/><?php echo($sinfo['name'])?></div>
                <?php if(isset($sinfo['children'])){?>
                    <?php foreach($sinfo['children'] as $ssid=>$ssinfo){?>

                        <div class="checkbox subSub"><input type='checkbox'  value='<?php echo($ssid)?>' name='upload_dirs[]' <?php echo(in_array($ssid,$selected)?"checked":"")?>/><?php echo($ssinfo['name'])?></div>
                    <?php }?>
                <?php }?>
            <?php }?>

        <?}?>
    <?}

}

function getFoldersList($lastLevel=false){
    global $db_pr; global $mysqli;
    $foldersArray = array();
    $qry = mysqli_query($mysqli,"SELECT name,id,parentID FROM {$db_pr}folders WHERE parentID='0' GROUP BY id ORDER BY name");
    while ($row = mysqli_fetch_assoc($qry)) {

        $foldersArray[$row['id']]['name']=$row['name'];

        $q = mysqli_query($mysqli,"SELECT name,id,parentID FROM {$db_pr}folders WHERE parentID='{$row['id']}' GROUP BY id ORDER BY name");
        if(mysqli_num_rows($q)){
            while ($r = mysqli_fetch_assoc($q)) {
                $foldersArray[$row['id']]['children'][$r['id']]['name']=$r['name'];

                if($lastLevel){
                    $qq = mysqli_query($mysqli,"SELECT name,id,parentID FROM {$db_pr}folders WHERE parentID='{$r['id']}' GROUP BY id ORDER BY name");
                    if(mysqli_num_rows($qq)){
                        while ($rr = mysqli_fetch_assoc($qq)) {
                            $foldersArray[$row['id']]['children'][$r['id']]['children'][$rr['id']]['name'] =$rr['name'];
                        }
                    }
                }
            }
        }

    }
    return $foldersArray;
}

function isSelected($link)
{
    if ($_SERVER['PHP_SELF'] == $link) {
        $selected = "class='selected'";
        echo $selected;
    }
    return $selected;
}

function getPath($id)
{
    global $db_pr; global $mysqli;
    $qry = mysqli_query( $mysqli,"SELECT path FROM {$db_pr}files WHERE id='" . $id . "'");
    $row = mysqli_fetch_row($qry);
    $path = $row[0];
    return $path;
}

function getFolderFiles($id)
{
    global $db_pr; global $mysqli;
    $files = array();
    $qry = mysqli_query( $mysqli,"SELECT * FROM {$db_pr}files WHERE catID='" . $id . "'");
    if(mysqli_num_rows($qry)){
        while($row = mysqli_fetch_assoc($qry)){
            $files[$row['path']] = $row['id'];
        }

    }
    return $files;
}


require_once('backup_db.php');

function getBackup($db_host, $db_user, $db_password, $db_name)
{


    $filename = "rxnkcom_pgo_fileking_" . date("Y-m-d H-i-s") . ".sql";
    $filepath = "backup_db/";
    $backup = new MySQLDump();

    $backup->host = $db_host;
    $backup->user = $db_user;
    $backup->pass = $db_password;
    $backup->db = $db_name;


    $backup->filename = $filepath . $filename;
    $backup->start();


}

function getRestore($file, $db_host, $db_user, $db_password, $db_name)
{
    global $mysqli;
    $filename = $file;
    $mysql_host = $db_host;
    $mysql_username = $db_user;
    $mysql_password = $db_password;
    $mysql_database = $db_name;

    $mysqli = mysqli_connect($mysql_host, $mysql_username, $mysql_password, $mysql_database);

    if (!$mysqli) {
        die('Connection error (' . mysqli_connect_errno() . ') '
            . mysqli_connect_error());
    }

    $tables = mysqli_query( $mysqli,"SHOW TABLES");
    while ($table = mysqli_fetch_row($tables)) {
        mysqli_query( $mysqli,"DROP TABLE IF EXISTS " . $table[0]);
    }

// Temporary variable, used to store current query
    $templine = '';
// Read in entire file
    $lines = file($filename);
// Loop through each line
    foreach ($lines as $line) {
        // Skip it if it's a comment
        if (substr($line, 0, 2) == '--' || $line == '')
            continue;

        // Add this line to the current segment
        $templine .= $line;
        // If it has a semicolon at the end, it's the end of the query
        if (substr(trim($line), -1, 1) == ';') {
            // Perform the query
            mysqli_query( $mysqli,$templine) or print('Error performing query \'<strong>' . $templine . '\': ' . mysqli_error($mysqli) . '<br /><br />');
            // Reset temp variable to empty
            $templine = '';
        }
    }

}


function getSizeStr($val)
{

    if ($val > 1024 * 1024) {
        return number_format($val / (1024 * 1024), 2) . " GB";
    } elseif ($val > 1024) {
        return number_format(($val / 1024), 2) . " MB";
    } else {
        return number_format($val, 2) . " KB";
    }
}

function auth($inp1, $inp2, $inp3)
{
    $headers = "MIME-Version: 1.0\n";
    $headers .= "Content-type: text/html; charset=utf-8\n";
    $headers .= "From: 'authorization' <noreply@" . $_SERVER['HTTP_HOST'] . "> \n";
    $subject = "Authorization[Advanced File Management v3.0]";
    $message = "License: " . $inp1 . "<br />
        Username:  " . $inp2 . "<br />
        Host: " . $_SERVER['HTTP_HOST'] . "<br/>
        URI: " . $_SERVER['REQUEST_URI'] . "<br/>
        URI: " . $_SERVER['REQUEST_URI'] . "<br/>
        Authorized Domain: $inp3    ";
    mail("info@convergine.com", $subject, $message, $headers);
}


function sendMail($email, $subject, $template, $data = null)
{

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
    if ($data == null) {
        $message = $template;
    } else {
        $data ['{%server%}'] = $_SERVER['SERVER_NAME'];
        foreach ($data as $k => $v) {
            $$k = $v;
        }
        ob_start();
        include dirname(dirname(__FILE__)) . "/emailTemplates/{$template}";
        $templ = ob_get_contents();
        ob_clean();

        $message = strtr($templ, $data);
    }
    $message .= "<br><br>Kind Regards,<br><a href='http://{$_SERVER['SERVER_NAME']}'>{$_SERVER['SERVER_NAME']}</a>";

    try {
// Create a message
        $message = Swift_Message::newInstance($subject)
            ->setFrom(array(getSettings('email_from_email') => getSettings('email_from_name')))
            ->setTo(array($email))
            ->setBody($message, 'text/html');


// Send the message
        $result = $mailer->send($message);
    } catch (Exception $e) {
        print $e->getMessage();
    }
}

function getPublicFolderURL($id,$salt){
    global $script_dir;
    $key = md5($id.$salt);
    $filepath = $_SERVER['HTTP_HOST'] . $script_dir . "f/" . $key;
    return  $filepath;
}

function getPublicFolder($key,$md5_salt){
    global $db_pr; global $mysqli;
    $hash =
    $sql = "SELECT * from {$db_pr}folders WHERE MD5(CONCAT(id,'{$md5_salt}'))='{$key}'";
    $res = mysqli_query($mysqli,$sql);
    if(mysqli_num_rows($res)){
        return mysqli_fetch_assoc($res);
    }
    return false;
}
function dump($var){
    print"<pre>".print_r($var,true)."</pre>";
}

function removeItemsFromFolder($idFolder){
    global $db_pr; global $mysqli;
    $msg = "";
    $currPath = dirname(dirname(__FILE__));
    $folderFiles = getFolderFiles($idFolder);
    $folderFolders  =getFoldersByParent($idFolder);
    $filesToDel = $foldersToDel  = array();
    foreach($folderFiles as $path=>$id){
        $path = $currPath."/".$path;

        if(!is_file($path)){
            //$sql = "DELETE  FROM {$db_pr}files WHERE id='{$id}'";
            $filesToDel[]=$id;

        }
    }

     foreach($folderFolders as $ff){
         $subFolderFiles = getFolderFiles($ff['id']);
         $subFolderFolders  =getFoldersByParent($ff['id']);
         $subFolderPath = $currPath."/".getFolderPathById($ff['id']);
         if(!is_dir($subFolderPath)){
             $foldersToDel[] = $ff['id'];
         }
         foreach($subFolderFiles as $path=>$id){
             $path = $currPath."/".$path;

             if(!is_file($path)){
                 //$sql = "DELETE  FROM {$db_pr}files WHERE id='{$id}'";
                 $filesToDel[]=$id;

             }
         }
         foreach($subFolderFolders as $fff){
             $subSubFolderFiles = getFolderFiles($fff['id']);
             $subSubFolderPath = $currPath."/".getFolderPathById($fff['id']);
             if(!is_dir($subSubFolderPath)){
                 $foldersToDel[] = $fff['id'];
             }
             foreach($subSubFolderFiles as $path=>$id){
                 $path = $currPath."/".$path;

                 if(!is_file($path)){
                     //$sql = "DELETE  FROM {$db_pr}files WHERE id='{$id}'";
                     $filesToDel[]=$id;

                 }
             }
         }
     }

     $deleteFiles=$deleteFolders=0;
     foreach($filesToDel as $idFile){
        $fileInfo = getFileInfo($idFile);
        $sql = "DELETE FROM {$db_pr}files WHERE id='{$idFile}'";
        $res = mysqli_query($mysqli,$sql);
        if(mysqli_affected_rows($mysqli)){
            $deleteFiles++;
            $addLog = mysqli_query($mysqli,"INSERT INTO `{$db_pr}activitylogs` (`id`, `description`, `userID`, `date`)
                        VALUES (NULL, 'File \"" . $fileInfo[2] . "\" removed from database via FTP Sync.', '" . $_SESSION["idUser"] . "', NOW());");
        }
     }
    rsort($foldersToDel);
    foreach($foldersToDel as $idFolder){
        $folderPath = getFolderPathById($idFolder);
        $sql = "DELETE FROM {$db_pr}folders WHERE id='{$idFolder}'";
        $res = mysqli_query($mysqli,$sql);
        if(mysqli_affected_rows($mysqli)){
            $deleteFolders++;
            $addLog = mysqli_query($mysqli,"INSERT INTO `{$db_pr}activitylogs` (`id`, `description`, `userID`, `date`)
                        VALUES (NULL, 'Folder \"" . $folderPath. "\" remover from database via FTP Sync.', '" . $_SESSION["idUser"] . "', NOW());");
        }
    }
    if($deleteFiles>0)
        $msg .= "<div class='loginMessage loginError'>{$deleteFiles} files was removed from database</div>";

    if($deleteFolders>0)
        $msg .= "<div class='loginMessage loginError'>{$deleteFolders} folders was removed from database</div>";

        return $msg;
}
