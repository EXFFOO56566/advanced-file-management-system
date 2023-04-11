<?php
/*
 * jQuery File Upload Plugin PHP Example 5.14
 * https://github.com/blueimp/jQuery-File-Upload
 *
 * Copyright 2010, Sebastian Tschan
 * https://blueimp.net
 *
 * Licensed under the MIT license:
 * http://www.opensource.org/licenses/MIT
 */
error_reporting(E_ALL | E_STRICT);
@session_start();
require('../dbconnect.php');
$folder   = $_SESSION['uploadFolder'];
$_folder  = explode("/", $_SESSION['uploadFolder']);
$_folder  = end($_folder);
$sql      = "SELECT * FROM {$db_pr}users WHERE id='" . $_SESSION["idUser"] . "'";
$res      = mysqli_query($mysqli,$sql);
$userData = mysqli_fetch_assoc($res);
$q        = "SELECT id,parentID FROM {$db_pr}folders WHERE name='" . $_folder . "'";
$res      = mysqli_query($mysqli,$q);
$rrr      = mysqli_fetch_assoc($res);
$folderID = $rrr["id"];
$extensions = implode("|", explode(",", $userData['extensions']));

$sql      = "SELECT SUM(size) AS files FROM {$db_pr}files WHERE userID='" . $_SESSION["idUser"] . "'";
$res      = mysqli_query($mysqli,$sql);
$files_size = mysqli_fetch_assoc($res);
$files_size = $files_size['size'];

$allowedSize = ($userData['quota'] * 1024 * 1024)-$files_size;

$options = array('delete_type' => 'POST',
    'db_host' => $db_host, 'db_user' => $db_user,
    'db_pass' => $db_password, 'db_name' => $db_name,
    'db_table' => 'files',
    'upload_dir' => dirname(dirname(dirname($_SERVER['SCRIPT_FILENAME']))) . "/{$folder}/",
    'upload_url' => "http://{$_SERVER['SERVER_NAME']}" . "{$script_dir}{$folder}/",
    'accept_file_types' => '/\.(' . strtolower($extensions) . ')$/i',

    // take precedence over the following max_file_size setting:
    'max_file_size' => $userData['filesize'] * 1024 * 1024,
    'min_file_size' => 1,
    'folderID' => $folderID,
    'folder' => $folder,
    'idUser' => $userData['id']);
require('UploadHandler.php');
class CustomUploadHandler extends UploadHandler
{
    protected function initialize()
    {

        parent::initialize();

    }
    protected function handle_form_data($file, $index)
    {
        $file->title       = @$_REQUEST['title'][$index];
        $file->description = @$_REQUEST['description'][$index];
    }
    protected function validate($uploaded_file, $file, $error, $index){
    global $db_pr;global $mysqli;global $demo;
        if(parent::validate($uploaded_file, $file, $error, $index)){

            if($demo){
                $file->error = 'Can\'t upload files in preview version of the script.';
                return false;
            }

            $content_length = $this->fix_integer_overflow(intval($this->get_server_var('CONTENT_LENGTH')));
            if ($uploaded_file && is_uploaded_file($uploaded_file)) {
                $file_size = $this->get_file_size($uploaded_file);
            } else {
                $file_size = $content_length;
            }

            $sql      = "SELECT * FROM {$db_pr}users WHERE id='" . $_SESSION["idUser"] . "'";
            $res      = mysqli_query($mysqli,$sql);
            $userData = mysqli_fetch_assoc($res);

            $sql      = "SELECT SUM(size) AS files_size FROM {$db_pr}files WHERE userID='" . $_SESSION["idUser"] . "'";
            $res      = mysqli_query($mysqli,$sql);
            $files_size = mysqli_fetch_assoc($res);
            $files_size = $files_size['files_size'];


            $allowedSize = ($userData['quota'] * 1024 * 1024)-$files_size;
            if($allowedSize-$file_size<1){
                $file->error = 'You have only '.round($allowedSize/1024,2)."KB to upload";
                return false;
            }else{
                return true;
            }


        }else{
            return false;
        }
    }
    protected function handle_file_upload($uploaded_file, $name, $size, $type, $error, $index = null, $content_range = null)
    {
        global $db_pr;global $mysqli;
        $file = parent::handle_file_upload($uploaded_file, $name, $size, $type, $error, $index, $content_range);
        //print_r($file);
        if (empty($file->error)) {

            $path     = $this->options['folder'] . "/" . $file->name;
            $pathInfo = pathinfo($path);
            $sql      = "INSERT INTO {$db_pr}files (`title`,`path`,`size`,`extension`,`userID`,`catID`,`dateUploaded`) VALUES
                    ('{$file->name}','{$path}','{$file->size}','{$pathInfo['extension']}','{$this->options['idUser']}','{$this->options['folderID']}','" . date("Y-m-d H:i:s") . "')";
            $res      = mysqli_query($mysqli,$sql);
            $file->id = mysqli_insert_id($mysqli);
        }
        return $file;
    }
    protected function set_additional_file_properties($file)
    {
        global $db_pr;global $mysqli;
        parent::set_additional_file_properties($file);
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            /*$sql = 'SELECT `id`, `type`, `title`, `description` FROM `'
                .$this->options['db_table'].'` WHERE `name`=?';
            $query = $this->db->prepare($sql);
            $query->bind_param('s', $file->name);
            $query->execute();
            $query->bind_result(
                $id,
                $type,
                $title,
                $description
            );*/
            $sql = "SELECT * FROM {$db_pr}files WHERE id='{$file->id}'";
            $res = mysqli_query($mysqli,$sql);
            while ($row = mysqli_fetch_assoc($res)) {
                $file->id          = $row['id'];
                $file->type        = '';
                $file->title       = $row['title'];
                $file->description = '';
            }
        }
    }
    public function delete($print_response = true)
    {
        global $db_pr;global $mysqli;
        $response = parent::delete(false);
        foreach ($response as $name => $deleted) {
            if ($deleted) {
                $sql = 'DELETE FROM `{$db_pr}files` WHERE `name`=?';
                $res = mysqli_query($mysqli,$sql);
            }
        }
        return $this->generate_response($response, $print_response);
    }
}
$upload_handler = new CustomUploadHandler($options);
