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
$root = dirname(dirname(__FILE__));
include_once($root.'/dbconnect.php');
include_once($root.'/functions.php');
    /* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
     * Easy set variables
     */

$images = array('jpg' ,'png' , 'gif' ,'bmp');
$music = array('mp3' , 'wma' ,'ogg' ,'wav');
$video = array('mp4' , 'avi' , 'flv' , 'mpeg' , 'wmv' ,'mov');
$docs = array('doc' , 'docx','xls','xlsx' );
$scripts = array('txt' ,'bsh' , 'c' , 'cc' , 'cpp', 'cs' , 'csh' , 'css' , 'cyc'  , 'cv' , 'htm'  , 'html'  , 'java' , 'js' , 'm'  , 'mxml' ,'perl' , 'php' ,
 'pl'  , 'pm'  , 'py'  , 'rb'  , 'sh'  , 'xhtml'  , 'xml'  , 'xsl'  , 'sql'  , 'vb' );

        $q="SELECT upload_dirs,accesslevel FROM {$db_pr}users WHERE id='".$_SESSION["idUser"]."'";
        $res=mysqli_query($mysqli,$q);
        $aRow=mysqli_fetch_assoc($res);
        $folders = str_replace(',',"','",$aRow["upload_dirs"]);
        $access = $aRow["accesslevel"];

        //determin admin or not.
        if(stristr($access,"abcdef")){ $level="admin"; }else{ $level="user"; }
    /* Array of database columns which should be read and sent back to DataTables. Use a space where
     * you want to insert a non-database field (for example a counter or static image)
     */
    $aColumns = array('f.id','f.title', 'f.size', 'f.extension', 'fo.name' ,'f.dateUploaded','f.userID','f.catID','f.path');
    
    /* Indexed column (used for fast and accurate table cardinality) */
    $sIndexColumn = "id";
    
    /* DB table to use */
    $sTable = "{$db_pr}files f";
$sWhere="WHERE 1";
    //$sJoin=" LEFT JOIN folders ON files.catID=folders.id";
$sJoin=" INNER JOIN {$db_pr}folders fo ON f.catID=fo.id";

if($level=="user"){

    $sJoin=" INNER JOIN {$db_pr}folders fo ON f.catID=fo.id";
    $sWhere="AND f.catID IN('{$folders}')";

    if(!stristr($access,"i")){  //if user can't see all files - show only his files in his assigned folders

        if(stristr($access,"t")){ //if user can view only his uploaded files

            $sJoin=" INNER JOIN {$db_pr}folders fo ON f.catID=fo.id
                    INNER JOIN {$db_pr}users u ON f.userID=u.id ";
            $sWhere="AND f.userID = '".$_SESSION['idUser']."'";
        }

    }

}



    /* Database connection information */

    // $gaSql['user']       = "root";
    // $gaSql['password']   = "";
    // $gaSql['db']         = "rxnkcom_pgo_fileking";
    // $gaSql['server']     = "localhost";
    
    /* REMOVE THIS LINE (it just includes my SQL connection user/pass) */
    //include( $_SERVER['DOCUMENT_ROOT']."/datatables/mysql.php" );
    
    
    /* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
     * If you just want to use the basic configuration for DataTables with PHP server-side, there is
     * no need to edit below this line
     */
    
    /* 
     * Local functions
     */
    function fatal_error ( $sErrorMessage = '' )
    {
        header( $_SERVER['SERVER_PROTOCOL'] .' 500 Internal Server Error' );
        die( $sErrorMessage );
    }



    /* 
     * Paging
     */
    $sLimit = "";
    if ( isset( $_GET['iDisplayStart'] ) && $_GET['iDisplayLength'] != '-1' )
    {
        $sLimit = "LIMIT ".intval( $_GET['iDisplayStart'] ).", ".
            intval( $_GET['iDisplayLength'] );
    }
    
    
    /*
     * Ordering
     */
    $sOrder = "";
    if ( isset( $_GET['iSortCol_0'] ) )
    {
        $sOrder = "ORDER BY  ";
        for ( $i=0 ; $i<intval( $_GET['iSortingCols'] ) ; $i++ )
        {
            if ( $_GET[ 'bSortable_'.intval($_GET['iSortCol_'.$i]) ] == "true" )
            {
                $sOrder .=$aColumns[ intval( $_GET['iSortCol_'.$i] ) ]." ".
                    ($_GET['sSortDir_'.$i]==='asc' ? 'asc' : 'desc') .", ";
            }
        }
        
        $sOrder = substr_replace( $sOrder, "", -2 );
        if ( $sOrder == "ORDER BY" )
        {
            $sOrder = "";
        }
    }
    
    
    /* 
     * Filtering
     * NOTE this does not match the built-in DataTables filtering which does it
     * word by word on any field. It's possible to do here, but concerned about efficiency
     * on very large tables, and MySQL's regex functionality is very limited
     */

    if ( isset($_GET['sSearch']) && $_GET['sSearch'] != "" )
    {
        $sWhere = empty($sWhere)?"WHERE (":" AND (";
        for ( $i=0 ; $i<count($aColumns) ; $i++ )
        {
            if ( isset($_GET['bSearchable_'.$i]) && $_GET['bSearchable_'.$i] == "true" )
            {
                $sWhere .= "".$aColumns[$i]." LIKE '%".mysqli_real_escape_string($mysqli, $_GET['sSearch'] )."%' OR ";
            }
        }
        $sWhere = substr_replace( $sWhere, "", -3 );
        $sWhere .= ')';
    }
    
    /* Individual column filtering */
    for ( $i=0 ; $i<count($aColumns) ; $i++ )
    {
        if ( isset($_GET['bSearchable_'.$i]) && $_GET['bSearchable_'.$i] == "true" && $_GET['sSearch_'.$i] != '' )
        {
            if ( $sWhere == "" )
            {
                $sWhere = "WHERE ";
            }
            else
            {
                $sWhere .= " AND ";
            }
            $sWhere .= "".$aColumns[$i]." LIKE '%".mysqli_real_escape_string($mysqli,$_GET['sSearch_'.$i])."%' ";
        }
    }
    
    
    /*
     * SQL queries
     * Get data to display
     */
    $sQuery = "
        SELECT SQL_CALC_FOUND_ROWS ".str_replace(" , ", " ", implode(", ", $aColumns))."
        FROM   $sTable
        $sJoin
        $sWhere
        $sOrder
        $sLimit
        ";
        //echo $sQuery;
    $aRowesult = mysqli_query($mysqli, $sQuery ) or fatal_error( 'MySQL Error: ' . mysqli_errno($mysqli). $sQuery);
    
    /* Data set length after filtering */
    $sQuery = "
        SELECT FOUND_ROWS()
    ";
    $aRowesultFilterTotal = mysqli_query($mysqli, $sQuery ) or fatal_error( 'MySQL Error: ' . mysqli_errno($mysqli) );
    $aResultFilterTotal = mysqli_fetch_array($aRowesultFilterTotal);
    $iFilteredTotal = $aResultFilterTotal[0];
    
    /* Total data set length */
    $sQuery = "
        SELECT COUNT(`".$sIndexColumn."`)
        FROM   $sTable
    ";
    $aRowesultTotal = mysqli_query($mysqli, $sQuery ) or fatal_error( 'MySQL Error: ' . mysqli_errno($mysqli) );
    $aResultTotal = mysqli_fetch_array($aRowesultTotal);
    $iTotal = $aResultTotal[0];
    
    
    /*
     * Output
     */
    $output = array(
        "sEcho" => intval($_GET['sEcho']),
        "iTotalRecords" => $iTotal,
        "iTotalDisplayRecords" => $iFilteredTotal,
        "aaData" => array()
    );
    
    while ( $aRow = mysqli_fetch_array( $aRowesult ) )
    {
        $row = array();
        /*for ( $i=0 ; $i<count($aColumns) ; $i++ )
        {
            $editable = '';
                // General output
                $row[] = $aRow[ $aColumns[$i] ];
                if($aColumns[$i]=='path'){

                    if($aRow['iadUser']==$_SESSION["idUser"] && stristr($access,"k")){  // logged in users file + has right to edit his files
                        $editable="<a href=\"edit_file.php?id=".$aRow["id"]."\"><img src=\"images/pencil_16.png\" alt=\"Edit this file\" border=\"0\"/></a>"; }
                    else if(stristr($access,"l")){  // not current users file but has rigt to edit all files
                        $editable="<a href=\"edit_file.php?id=".$aRow["id"]."\"><img src=\"images/pencil_16.png\" alt=\"Edit this file\" border=\"0\"/></a>"; }
                    else { // doesnt have right to edit files.
                        $editable = "";
                    }
                    $row[] = $editable;
                }

        }*/
        $fileInfo = getFileInfo($aRow["id"]);
        $editable = "";


        if(in_array(strtolower($aRow["extension"]),$images)){
            $editable .="<a href=\"javascript:viewImage('{$aRow['title']}','{$aRow["path"]}');\" title=\"Preview\"><span class=\"glyphicon glyphicon-zoom-in\"></span></a>&nbsp;";
        }

        if(in_array(strtolower($aRow["extension"]),$music)){
            $editable .="<a class=\"iframe\" href=\"javascript:viewAudio('{$aRow['title']},".urlencode($aRow["path"])."');\" title=\"Preview\"><span class=\"glyphicon glyphicon-zoom-in\"></span></a>&nbsp;";

        }
        if(in_array(strtolower($aRow["extension"]),$video)){
            $editable .="<a class=\"iframe\" href=\"javascript:viewVideo('{$aRow['title']},{$aRow["path"]},{$aRow["extension"]}');\" title=\"Preview\"><span class=\"glyphicon glyphicon-zoom-in\"></span></a>&nbsp;";

        }

        if(strtolower($aRow["extension"])=='pdf'){
            $editable .="<a class=\"iframe\" href=\"javascript:viewPdf('http://{$_SERVER['SERVER_NAME']}{$script_dir}{$aRow["path"]}');\" title=\"Preview\"><span class=\"glyphicon glyphicon-zoom-in\"></span></a>&nbsp;";
        }

        if(in_array(strtolower($aRow["extension"]),$docs)){
            $editable .="<a class=\"iframe\" href=\"javascript:viewDoc('http://{$_SERVER['SERVER_NAME']}{$script_dir}{$aRow["path"]}');\" title=\"Preview\"><span class=\"glyphicon glyphicon-zoom-in\"></span></a>&nbsp;";

        }
        if(in_array(strtolower($aRow["extension"]),$scripts)){
            $editable .="<a class=\"iframe\" href=\"javascript:viewCode('{$aRow['title']},{$aRow["path"]},{$aRow["extension"]}');\" title=\"Preview\"><span class=\"glyphicon glyphicon-zoom-in\"></span></a>&nbsp;";

        }



        if($aRow['userID']==$_SESSION["idUser"] && stristr($access,"k")){  // logged in users file + has right to edit his files
            $editable.="<a href=\"edit_file.php?id=".$aRow["id"]."\" title='Edit'><span class=' glyphicon glyphicon-pencil'></span></a>&nbsp;"; }
        elseif(stristr($access,"l")){  // not current users file but has rigt to edit all files
            $editable.="<a href=\"edit_file.php?id=".$aRow["id"]."\" title='Edit'><span class=' glyphicon glyphicon-pencil'></span></a>&nbsp;"; }


        if(strstr($access,"m")==true){
            $editable .= "<a href=\"sharelink.php?id=".$aRow["id"]."\" title='Share'><span class=\"glyphicon glyphicon-share-alt\"></span></a>&nbsp;";
        } else {
            if($fileInfo[3]==$_SESSION["idUser"]){
                $editable .= "<a href=\"sharelink.php?id=".$aRow["id"]."\" title='Share'><span class=\"glyphicon glyphicon-share-alt\"></span></a>&nbsp;";
            } 
        }


        $editable .= "<a href=\"download.php?idFile=".$aRow["id"]."\"  title='Download' target='_blank'><span class='glyphicon glyphicon-cloud-download'></span></a> &nbsp; ";
        //if($fileInfo[3]==$_SESSION["idUser"] || $_SESSION["idUser"]=="1"){
        $editable .= "<a href=\"messages.php?file=".$aRow["id"]."\"  title='Messages'><span class=\"glyphicon glyphicon-comment\"></span></a>&nbsp;(".getCommentCount($aRow["id"]).")";




        $row[] = ((stristr($access,"h") && !stristr($access,"j") && $aRow["userID"]==$_SESSION["idUser"]) || (stristr($access,"j")) )?
        //if permissions allow to delete files.
        "<input name=\"files[]\" type=\"checkbox\" value=\"".$aRow["id"]."\"/>":
        "<input name=\"files[]\" type=\"checkbox\" disabled value=\"".$aRow["id"]."\" />";

        $row[] = $aRow['title'];
        $row[] =($aRow["size"]>1048576)?number_format(($aRow["size"]/1024/1024),2,".",",")."MB":number_format(($aRow["size"]/1024),2,".",",")."KB";
        $row[] = $aRow["extension"];
        $row[] = _getFolderName($aRow['catID']);
        $row[] = $aRow['dateUploaded'];
        $row[] = $editable;


        $output['aaData'][] = $row;
    }

    echo json_encode( $output );
?>