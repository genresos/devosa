﻿<?
  include_once('../global/session.php');
  include_once('global.php');
  include_once('form_object.php');
  include_once('../includes/model/model.php');
  include_once('../includes/datagrid2/datagrid.php');

  $dataPrivilege = getDataPrivileges("employee_report.php", $bolCanView, $bolCanEdit, $bolCanDelete, $bolCanApprove, $bolCanCheck, $bolCanAcknowledge, $bolCanApprove2);
  if (!$bolCanView) die(accessDenied($_SERVER['HTTP_REFERER']));




  $tblEmployee = new cModel("hrd_employee", getWords("employee"));
  $bolPrint = (isset($_REQUEST['btnPrint']) || isset($_REQUEST['btnPrintAll']) || isset($_REQUEST['btnExportXLS']));
  $bolLimit = false;// (getRequestValue('dataLimit', 0) == 1);
  //---- INISIALISASI ----------------------------------------------------
  $strWordsListOfEndContractedEmployee   = getWords("list of end of contract employee");
  $strWordsDivision                 = getWords("division"); 
  $strWordsDepartment               = getWords("department");
  $strWordsSection                  = getWords("section"); 
  $strWordsSubSection               = getWords("sub section"); 
  $strWordsDate                     = getWords("date"); 
  $strDataDetail = "";
  $strDataDate = "";
  $strDivisionName = "";
  $strDepartmentName = "";
  $strSectionName = "";
  $strSubSectionName = "";
  $strStyle = "";
  $strHidden = "";
  $intTotalData = 0; // default, tampilan dibatasi (paging)
  $strSearchDisplay = "display:none";
  //----------------------------------------------------------------------
  //class inheritance from cDataGrid
  class cDataGrid2 extends cDataGrid 
  {
    /*you can inherit this function to created your own TR class or style*/
    function printOpeningRow($intRows, $rowDb)
    {
      $strResult = "";
      $strClass = getCSSClassName($rowDb['flag'], false);
      if (($intRows % 2) == 0)
        $strResult .= "
            <tr $strClass valign=\"top\">";
      else
        $strResult .= "
            <tr $strClass valign=\"top\">";
      
      return $strResult;
    }
  }


  //--- DAFTAR FUNSI------------------------------------------------------
  // fungsi untuk menampilkan data
  // $db = kelas database, $intRows = jumlah baris (return)
  // $strDataDate adalah tanggal yang diinginkan
  // $strKriteria = query kriteria, $strOrder = query ORder by
  // $intStart = record mulai, $bolLimit = dibatasi sesuai limit global

  function showData($strCriteria, $bolLimit = true, $isExport = false)
  {   
//    echo $strCriteria;
    global $tblEmployee;
    global $bolPrint;
    global $bolCanDelete;
    global $bolCanEdit;    
    global $intTotalData;
    global $myDataGrid;
    
    $db = new CdbClass;
    $db->connect();

    $bolUpdateOnly = (isset($_REQUEST['btnShowAlert']));
    if ($bolUpdateOnly) $bolLimit = false;
    $intdataFlag = (isset($_REQUEST['dataStatus'])) ? $_REQUEST['dataStatus'] : -1;

    $myDataGrid->addColumnNumbering(new DataGrid_Column("No", "", array('width'=>30), array('nowrap'=>'')));
    $myDataGrid->addColumn(new DataGrid_Column("ID", "employee_id", array('width' => 70), array("nowrap" => "nowrap"), true, true, "", "printResumeLink()", "string", true, 12, false));
    $myDataGrid->addColumn(new DataGrid_Column(getWords("employee name"), "employee_name", null,  array("nowrap" => "nowrap"), true, true, "", "printEmployeeName()", "string", true, 35));
    $myDataGrid->addColumn(new DataGrid_Column(getWords("finger ID"), "barcode", array("width" => 80),  array("nowrap" => "nowrap"), true, true, "", "", "integer", true, 12));
    $myDataGrid->addColumn(new DataGrid_Column(getWords("sex"), "gender", array("width" => 30),  array("align" => "center"), true, true, "", "printGender()", "string", true, 6));
    $myDataGrid->addColumn(new DataGrid_Column(getWords("age"), "umur", array("width" => 30),  array("align" => "right"), true, true, "", "", "string", true, 6));
    $myDataGrid->addColumn(new DataGrid_Column(getWords("fam."), "family_status_code", array("width" => 30),  null, true, true, "", "", "string", true, 12));
    $myDataGrid->addColumn(new DataGrid_Column(getWords("birthplace"), "birthplace", array("width" => 120),  array("nowrap" => "nowrap"), true, true, "", "", "string", true, 30));
    $myDataGrid->addColumn(new DataGrid_Column(getWords("birthday"), "birthday", array("width" => 80), null, true, true, "", "formatDate()", "string", true, 12));
    $myDataGrid->addColumn(new DataGrid_Column(getWords("employee status"), "employee_status", array("width" => 100),  array("nowrap" => "nowrap"), true, true, "", "printEmployeeStatus()", "string", true, 15));
    $myDataGrid->addColumn(new DataGrid_Column(getWords("dept."), "department_code", array("width" => 50),  array("nowrap" => "nowrap"), true, true, "", "", "string", true, 12));
    $myDataGrid->addColumn(new DataGrid_Column(getWords("sect."), "section_code", array("width" => 50),  array("nowrap" => "nowrap"), true, true, "", "", "string", true, 12));
    $myDataGrid->addColumn(new DataGrid_Column(getWords("level"), "position_code", array("width" => 70),  array("nowrap" => "nowrap"), true, true, "", "", "string", true, 12));
    $myDataGrid->addColumn(new DataGrid_Column(getWords("grade"), "grade_code", array("width" => 40),  array("align" => "center"), true, true, "", "", "string", true, 6));
    $myDataGrid->addColumn(new DataGrid_Column(getWords("join date"), "join_date", array("width" => 70),  array("nowrap" => "nowrap"), true, true, "", "formatDate()", "string", true, 12));
    $myDataGrid->addColumn(new DataGrid_Column(getWords("permanent date"), "permanent_date", array("width" => 70), array("nowrap" => "nowrap"), true, true, "", "formatDate()", "string", true, 12));
    $myDataGrid->addColumn(new DataGrid_Column(getWords("end contract date"), "due_date", array("width" => 70),  array("nowrap" => "nowrap"), true, true, "", "formatDate()", "string", true, 12));
    $myDataGrid->addColumn(new DataGrid_Column(getWords("status"), "active", array("width" => 50),  array("align" => "center", "nowrap" => "nowrap"), true, true, "", "printStatus()", "string", true, 12));
    $myDataGrid->addColumn(new DataGrid_Column(getWords("jamsostek no."), "jamsostek_no", array("width" => 80), null, true, true, "", "", "string", true, 15));

    if ($isExport)
    {
      $myDataGrid->DATAGRID_RENDER_OUTPUT = DATAGRID_RENDER_EXCEL_BIFF;
      $myDataGrid->strFileNameXLS = "end_contracted_employee_list.xls";
      $myDataGrid->strTitle1 = getWords("list of end contracted employee");
    }
    if (!$bolPrint)
    {
      $myDataGrid->addButtonExportExcel("Export Excel", "end_contracted_employee_list.xls", getWords("list of end contracted employee"));
    }

    $myDataGrid->getRequest();
    //--------------------------------
    //get Data and set to Datagrid's DataSource by set the data binding (bind method)

    
    $strCriteriaFlag = $myDataGrid->getCriteria().$strCriteria;
    $strOrderBy = $myDataGrid->getSortBy();
    if ($bolLimit) 
    {
      $strPageLimit = $myDataGrid->getPageLimit();
      $intPageNumber = $myDataGrid->getPageNumber();
    }
    else 
    {
      $strPageLimit = null;
      $intPageNumber = null;
    }
    $myDataGrid->totalData = $tblEmployee->findCount($strCriteriaFlag);
    $dataset = $tblEmployee->findAll($strCriteriaFlag, 
                             "*,(EXTRACT(YEAR FROM AGE(birthday))) AS umur", 
                             $strOrderBy,
                             $strPageLimit, 
                             $intPageNumber);
    $arrTmpData = getEmployeeUpdated($strCriteria);
        
    $newDataset = array();
    foreach($dataset as $rowDb)
    {
      // cek apakah tampil semua atau yang berubah aja
      if ($bolUpdateOnly)
      {
        if (isset($arrTmpData[$rowDb['id']]))
        {
          if ($intdataFlag == -1 || $intdataFlag == $arrTmpData[$rowDb['id']]['flag'])
          {
            $newDataset[] = $rowDb;
            $newDataset[] = $arrTmpData[$rowDb['id']];
            unset($arrTmpData[$rowDb['id']]);
          } 
          else
            unset($arrTmpData[$rowDb['id']]);
        }
      }
      else
      {
        $newDataset[] = $rowDb;
        if (isset($arrTmpData[$rowDb['id']])) 
        {
          $newDataset[] = $arrTmpData[$rowDb['id']];
          unset($arrTmpData[$rowDb['id']]);
        }
      }
    }    
    $intTotalData = count($newDataset);

    $myDataGrid->bind($newDataset);
    
    return $myDataGrid->render();
  }
  function getEmployeeUpdated($strCriteria)
  {
    global $tblEmployee;
    // CARI DATA RECORD YANG DIUPDATE/BARU, TAPI BELUM DI APPROVE MA MANAGER, SIMPAN DI ARRAY, tapi yang sifatnya UPDATE
    return $tblEmployee->findAll($strCriteria, 
                                  "*, (EXTRACT(YEAR FROM AGE(birthday))) AS umur",
                                  null, null, null
                                 );
  }

  
  function getCSSClassName($flag, $bolOrphan = false)
  {
    if ($bolOrphan) 
    {
      $strClass = "class=\"bgDenied\"";
      $strDisabled = "";
    } 
    else 
    {
      switch ($flag) 
      {
        case 0 :
          $strClass = "";
          break;
        case 1 :
          $strClass = "class=\"bgNewData\"";
          break;
        case 2 :
          $strClass = "class=\"bgCheckedData\"";
          break;
        case 3 : // ditolak
          $strClass = "class=\"bgDenied\"";
          break;
        default :
          $strClass = "";
          break;
      }
    }
    return $strClass;
  }
  function printEmployeeName($params)
  {
    extract($params);
    global $bolPrint;
    if ($bolPrint)
      return $value;
    else
    {
      $strHiddenInfo  = "<input type=hidden name='detailName$counter' value='" .stripslashes($value)."' disabled>";
      $strHiddenInfo .= "<input type=hidden name='detailDenied$counter' value='' disabled>";
      return $value.$strHiddenInfo;
    }
  }

/*  function printEmployeeStatus($params)
  {
    extract($params);
    global $ARRAY_EMPLOYEE_STATUS;
    return getWords($ARRAY_EMPLOYEE_STATUS[$value]);
  }
*/
  function printStatus($params)
  {
    extract($params);
    if ($value == 1)
      return getWords('active');
    else
      return getWords('not active');
  } 
  function printGender($params)
  {
    extract($params);
    return ($value == 0) ? "F" : "M";
  }
  //----------------------------------------------------------------------

  //----MAIN PROGRAM -----------------------------------------------------
  $db = new CdbClass;

  if ($db->connect()) {
    getUserEmployeeInfo();

    // ------ AMBIL DATA KRITERIA -------------------------
    (isset($_REQUEST['dataDateFrom']))   ? $strDate       = $_REQUEST['dataDateFrom']   : $strDate = "";
    (isset($_REQUEST['dataDateThru']))   ? $strDateThru   = $_REQUEST['dataDateThru']   : $strDateThru = "";
    (isset($_REQUEST['dataDivision']))   ? $strDataDivision   = $_REQUEST['dataDivision']   : $strDataDivision = "";
    (isset($_REQUEST['dataDepartment'])) ? $strDataDepartment = $_REQUEST['dataDepartment'] : $strDataDepartment = "";
    (isset($_REQUEST['dataSection']))    ? $strDataSection    = $_REQUEST['dataSection']    : $strDataSection = "";
    (isset($_REQUEST['dataSubSection'])) ? $strDataSubSection = $_REQUEST['dataSubSection'] : $strDataSubSection = "";
    $strHidden  = "<input type=\"hidden\" name=\"dataDateFrom\"  value=\"$strDate\">";
    $strHidden .= "<input type=\"hidden\" name=\"dataDateThru\"  value=\"$strDateThru\">";
    $strHidden .= "<input type=\"hidden\" name=\"dataDivision\" value=\"$strDataDivision\">";
    $strHidden .= "<input type=\"hidden\" name=\"dataDepartment\" value=\"$strDataDepartment\">";
    $strHidden .= "<input type=\"hidden\" name=\"dataSection\"   value=\"$strDataSection\">";
    $strHidden .= "<input type=\"hidden\" name=\"dataSubSection\"   value=\"$strDataSubSection\">";

    scopeData($strDataEmployee, $strDataSubSection, $strDataSection, $strDataDepartment, $strDataDivision, $_SESSION['sessionUserRole'], $arrUserInfo);

    // ------------ GENERATE KRITERIA QUERY,JIKA ADA -------------

    $strKriteria = "";
    if ($strDataDivision != "") {
      $strKriteria .= "AND division_code = '$strDataDivision' ";
      $strSQL  = "SELECT division_name FROM hrd_division WHERE division_code = '$strDataDivision' ";
      $resDb = $db->execute($strSQL);
      if ($rowDb = $db->fetchrow($resDb)) {
        $strDivisionName = $rowDb['division_name'];
      }
    }
    if ($strDataDepartment != "") {
      $strKriteria .= "AND department_code = '$strDataDepartment' ";
      $strSQL  = "SELECT department_name FROM hrd_department WHERE department_code = '$strDataDepartment' ";
      $resDb = $db->execute($strSQL);
      if ($rowDb = $db->fetchrow($resDb)) {
        $strDepartmentName = $rowDb['department_name'];
      }
    }
    if ($strDataSection != "") {
      $strKriteria .= "AND section_code = '$strDataSection' ";
      $strSQL  = "SELECT section_name FROM hrd_section WHERE section_code = '$strDataSection' ";
      $resDb = $db->execute($strSQL);
      if ($rowDb = $db->fetchrow($resDb)) {
        $strSectionName = $rowDb['section_name'];
      }
    }
    if ($strDataSubSection != "") {
      $strKriteria .= "AND sub_section_code = '$strDataSubSection' ";
      $strSQL  = "SELECT sub_section_name FROM hrd_sub_section WHERE sub_section_code = '$strDataSubSection' ";
      $resDb = $db->execute($strSQL);
      if ($rowDb = $db->fetchrow($resDb)) {
        $strSubSectionName = $rowDb['sub_section_name'];
      }
    }
    $strKriteria .= "AND (due_date BETWEEN '$strDate' AND '$strDateThru') ";
	$strKriteria .= $strKriteriaCompany;
//    $strKriteria .= " OR (due_date BETWEEN '$strDate' AND '$strDateThru'))";
    if ($bolCanView) 
    {
      if (isset($_REQUEST['btnExportXLS'])) 
       //{
         $isExport = true;  //print_r($_REQUEST);}
      else $isExport = false;

      //class initialization
      $DEFAULTPAGELIMIT = getSetting("rows_per_page");
      if (!is_numeric($DEFAULTPAGELIMIT)) $DEFAULTPAGELIMIT = 50;
      if ($bolPrint)
      {
        $myDataGrid = new cDataGrid2("formData", "DataGrid1", "100%", "100%", false, false, false, false);
      }
      else
      {
        $myDataGrid = new cDataGrid2("formData", "DataGrid1", "100%", "100%", $bolLimit, false, true);
        $myDataGrid->caption = getWords("list of end contracted employee");
      }
      $myDataGrid->disableFormTag();
      //$myDataGrid->setAJAXCallBackScript(basename($_SERVER['PHP_SELF']));
      $myDataGrid->pageSortBy = "employee_name";
      //end of class initialization
      
      $DataGrid = showData($strKriteria, $bolLimit, $isExport);
    } 
    else 
    {
      showError("view_denied");
      $strDataDetail = "";
    }
    $strDataDate = pgDateFormat($strDate,"d F Y") ." -". pgDateFormat($strDateThru, "d F Y");
  }


 $tbsPage = new clsTinyButStrong ;
  //write this variable in every page
  $strPageTitle = $dataPrivilege['menu_name'];
  $strTemplateFile = getTemplate(str_replace(".php", ".html", basename($_SERVER['PHP_SELF'])));  
  //------------------------------------------------
  //Load Master Template
  $tbsPage->LoadTemplate($strMainTemplate) ;
  $tbsPage->Show() ;


?>