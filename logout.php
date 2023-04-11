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
    require_once("includes/dbconnect.php"); //Load the settings
	$msg="";
	
	if(isset($_SESSION["logged_in"]) && $_SESSION["logged_in"]!=true){ 
	header("Location: index.php");
exit();
	} else {
	$_SESSION['idUser']="";
	$_SESSION['username']= "";
	$_SESSION['accesslevel']= "";
	$_SESSION['logged_in'] = false;
	session_destroy();
	header("Location: index.php");
	}
?>