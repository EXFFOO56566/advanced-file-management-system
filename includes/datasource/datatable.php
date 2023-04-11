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
class DataTable {

    protected $_sTable;
    protected $_aColumns = array();
    protected $_sJoin = '';
    protected $_sWhere_0 = '';
    protected $_sGroupBy = '';
    protected $_sIndexColumn = 'id';
    protected $_iFilteredTotal;
    protected $_iTotal;
    protected $_iResult;
    protected static $_dbh;

    public function __construct($sDatabase, $sTable, array $aColumns, $sJoin = '', $sWhere_0 = '', $sGroupBy = '', $sIndexColumn = '') {
        if(!isset(self::$_dbh)) self::$_dbh = $sDatabase;
        $this->_sTable = $sTable;
        $this->_aColumns = $aColumns;
        if($sJoin != '') $this->_sJoin = $sJoin;
        if($sWhere_0 != '') $this->_sWhere_0 = $sWhere_0;
        if($sGroupBy != '') $this->_sGroupBy = $sGroupBy;
        if($sIndexColumn != '') $this->_sIndexColumn = $sIndexColumn;
        $this->sQuery();
    }
    protected function sLimit() {
        if ( isset( $_GET['iDisplayStart'] ) && $_GET['iDisplayLength'] != '-1' ) {
             $sLimit = ' LIMIT ' . intval($_GET['iDisplayStart']) . ', ' . intval($_GET['iDisplayLength']);
        } else {
             $sLimit = '';
        }
        return $sLimit;
    }

    protected function sOrder() {
        $sOrder = '';
        if ( isset( $_GET['iSortCol_0'] ) ) {
             $sOrder = ' ORDER BY  ';
             for ( $i=0 ; $i<intval( $_GET['iSortingCols'] ) ; $i++ ) {
                if ( $_GET[ 'bSortable_'.intval($_GET['iSortCol_'.$i]) ] == 'true' ) {
                     $sOrder .= $this->_aColumns[ intval( $_GET['iSortCol_'.$i] ) ].' '.$_GET['sSortDir_'.$i] .', ';
                }
             }
             $sOrder = substr_replace( $sOrder, '', -2 );
             if ( $sOrder == ' ORDER BY' ) {
                  $sOrder = '';
             }  
        }
        return $sOrder;
    }

    protected function sWhere() {
        $sWhere = '';
        if ( $_GET['sSearch'] != '' ) {
             $sWhere = ' WHERE (';
             for ( $i=0 ; $i<count($this->_aColumns) ; $i++ ) {
                if ( $_GET['bSearchable_'.$i] == "true" ) {
                     $sWhere .= $this->_aColumns[$i]." LIKE '%". $_GET['sSearch'] ."%' OR ";
                }
             }
             $sWhere = substr_replace( $sWhere, "", -3 );
             $sWhere .= ')';
        }
        /* Conditions */
        if($this->_sWhere_0 != '') {
            if($sWhere != '') {
                $sWhere .= ' AND '.$this->_sWhere_0;    
            } else {
                $sWhere .= ' WHERE '.$this->_sWhere_0;
            }
        }
        /* Individual column filtering */
        for ( $i=0 ; $i<count($this->_aColumns) ; $i++ ) {
            if ( $_GET['bSearchable_'.$i] == "true" && $_GET['sSearch_'.$i] != '' ) {
                if ( $sWhere == '' ) {
                    $sWhere = ' WHERE ';
                } else {
                    $sWhere .= ' AND ';
                }
                $sWhere .= $this->_aColumns[$i]." LIKE '%".$_GET['sSearch_'.$i]."%' ";
            }
        }
        return $sWhere;
    }

    protected function sQuery() {
        $sQuery = "SELECT SQL_CALC_FOUND_ROWS ".str_replace(" , ", " ", implode(", ", $this->_aColumns)).' FROM '.
        $this->_sTable.
        $this->_sJoin.
        $this->sWhere().
        $this->_sGroupBy.
        $this->sOrder().
        $this->sLimit();
        $sth = self::$_dbh->query($sQuery);
        $this->_iResult = $sth->fetchAll(PDO::FETCH_NUM);
        /* Data set length after filtering */
        $sQuery = 'SELECT FOUND_ROWS()';
        $sth = self::$_dbh->query($sQuery);
        $aResultFilterTotal = $sth->fetchAll(PDO::FETCH_NUM);
        $this->_iFilteredTotal = $aResultFilterTotal[0][0];
        /* Total data set length */
        $sQuery = 'SELECT COUNT('.$this->_sIndexColumn.') FROM '.$this->_sTable;
        $sth = self::$_dbh->query($sQuery);
        $aResultTotal = $sth->fetchAll(PDO::FETCH_NUM);
        $this->_iTotal = $aResultTotal[0][0];
        return $this;
    }

    public function aaData() {
        $output = array(
            "sEcho" => intval($_GET['sEcho']),
            "iTotalRecords" => $this->_iTotal,
            "iTotalDisplayRecords" => $this->_iFilteredTotal,
            "aaData" => array()
        );
        return $output;
    }

    public function iResult() {
        return $this->_iResult;
    }
}
?>