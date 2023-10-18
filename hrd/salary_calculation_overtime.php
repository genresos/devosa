<?php
  include_once('../global/session.php');
  include_once('global.php');
  include_once('../global/employee_function.php');
  include_once('form_object.php');
  include_once('salary_func.php');
  include_once('../includes/model/model.php');
  include_once('../includes/datagrid2/datagrid.php');
  include_once('../classes/hrd/hrd_basic_salary_set.php');
  include_once '../global/email_func.php';

  $dataPrivilege = getDataPrivileges(basename($_SERVER['PHP_SELF']), $bolCanView, $bolCanEdit, $bolCanDelete, $bolCanApprove, $bolCanCheck, $bolCanAcknowledge, $bolCanApprove2);
  if (!$bolCanView) die(accessDenied($_SERVER['HTTP_REFERER']));



  $strDisplay = ($bolCanEdit) ? "table-row" : "none";

  //---- INISIALISASI ----------------------------------------------------
  $strDataDetail          = "";
  $strWordsCompany        = getWords("company");
  //$strWordsCurrency        = getWords("currency");
  $strWordsSalaryDates     = getWords("salary calculation date");
  $strWordsAttendanceDate = getWords("period for attendance");
  $strWordsOvertimeDate = getWords("period for overtime");
  $strWordsSalaryDate = getWords("period for salary");
  $strWordsPeriodForTHR   = getWords("period for THR");
  $strWordsSalarySet      = getWords("salary set");
  $strWordsIrregular      = getWords("irregular");
  $strWordsHideIfBlank    = getWords("hide if blank");
  $strWordsStartCalculation = getWords("start calculation");  $strWordsStartCalculation = getWords("start calculation");

  //$strWordsTaxRate        = getWords("tax rate");
  $strWordsNote           = getWords("note");
  $strWordsNew       = getWords("new");
  $strWordsDenied    = getWords("denied");
  $strWordsChecked   = getWords("checked");
  $strWordsApproved  = getWords("approved");
  $strWordsApproved2 = getWords("approved 2");
  $strWordsFinished  = getWords("finished");
  $strHidden              = "";
  $strButtons             = "";
  $intTotalData           = 0;


  //----------------------------------------------------------------------

  //--- DAFTAR FUNSI------------------------------------------------------

  // menyimpan perintah perhitungan gaji
  // output : ID perhitungan gaji, jika sukses, jika gagal, return ""
  function saveData($db, &$strError)
  {
    global $_REQUEST;
    global $error;
    global $_SESSION;


    $strDataTHRDateFrom = (isset($_REQUEST['dataTHRDateFrom'])) ? $_REQUEST['dataTHRDateFrom']  : "";
    $strDataTHRDateThru = (isset($_REQUEST['dataTHRDateThru'])) ? $_REQUEST['dataTHRDateThru']  : "";
    $strDataDateFrom    = (isset($_REQUEST['dataDateFrom']))    ? $_REQUEST['dataDateFrom']     : date("Y-m-d");
    $strDataDateThru    = (isset($_REQUEST['dataDateThru']))    ? $_REQUEST['dataDateThru']     : date("Y-m-d");
    $strDataOvertimeDateFrom    = (isset($_REQUEST['dataOvertimeDateFrom']))    ? $_REQUEST['dataOvertimeDateFrom']     : date("Y-m-d");
    $strDataOvertimeDateThru    = (isset($_REQUEST['dataOvertimeDateThru']))    ? $_REQUEST['dataOvertimeDateThru']     : date("Y-m-d");
    $strDataSalaryDateFrom    = (isset($_REQUEST['dataSalaryDateFrom']))    ? $_REQUEST['dataSalaryDateFrom']     : date("Y-m-d");
    $strDataSalaryDateThru    = (isset($_REQUEST['dataSalaryDateThru']))    ? $_REQUEST['dataSalaryDateThru']     : date("Y-m-d");
    $strDataDate        = (isset($_REQUEST['dataDate']))        ? $_REQUEST['dataDate']         : date("Y-m-d");
    //$strDataCurrency    = (isset($_REQUEST['dataCurrency']))    ? $_REQUEST['dataCurrency']     : "";
    $strDataCompany     = (isset($_REQUEST['dataCompany']))     ? $_REQUEST['dataCompany']      : "";
    $bolIrregular       = (isset($_REQUEST['dataIrregular']))   ? true                          : false;
    $bolHideBlank       = (isset($_REQUEST['dataHideBlank']))   ? true                          : false;
    $strDataNote        = (isset($_REQUEST['dataNote']))        ? $_REQUEST['dataNote']         : "";
    //$strDataTaxRate     = (isset($_REQUEST['dataTaxRate']))     ? $_REQUEST['dataTaxRate']      : "";
    $strDataIDSalarySet = (isset($_REQUEST['dataIDSalarySet'])) ? $_REQUEST['dataIDSalarySet']  : "";

    // if (!validStandardDate($strDataTHRDateFrom) && $strDataTHRDateFrom != "") {
    //   $strError = $error['invalid_date']." ".$strDataTHRDateFrom;
    //   return 0;
    // } else if (!validStandardDate($strDataTHRDateThru) && $strDataTHRDateFrom != "") {
    //   $strError = $error['invalid_date']." ".$strDataTHRDateThru;
    //   return 0;
    // } else     if (!validStandardDate($strDataDateFrom)) {
    //   $strError = $error['invalid_date']." ".$strDataDateFrom;
    //   return 0;
    // } else if (!validStandardDate($strDataDateThru)) {
    //   $strError = $error['invalid_date']." ".$strDataDateThru;
    //   return 0;
    if (!validStandardDate($strDataOvertimeDateFrom)) {
      $strError = $error['invalid_date']." ".$strDataOvertimeDateFrom;
      return 0;
    } else if (!validStandardDate($strDataOvertimeDateThru)) {
      $strError = $error['invalid_date']." ".$strDataOvertimeDateThru;
      return 0;
    // } else     if (!validStandardDate($strDataSalaryDateFrom)) {
    //   $strError = $error['invalid_date']." ".$strDataSalaryDateFrom;
    //   return 0;
    // } else if (!validStandardDate($strDataSalaryDateThru)) {
    //   $strError = $error['invalid_date']." ".$strDataSalaryDateThru;
    //   return 0;
    } else if (!validStandardDate($strDataDate)) {
      $strError = $error['invalid_date']." ".$strDataDate;
      return 0;
    } /*else if (!is_numeric($strDataTaxRate)) {
      $strError = $error['invalid_number']." ".$strDataDate;
      return 0;
    }*/
    if ($strDataCompany == "")
    {
      $strError = "please choose one company to start salary calculation";
      return 0;
    }
    /*if ($strDataCurrency == "")
    {
      $strError = "please choose one currency to start salary calculation";
      return 0;
    }*/
/*
    if (!$bolIrregular)
    {
      // cek apakah untuk tanggal ini sudah pernah ada
      $strSQL  = "SELECT id FROM hrd_salary_master WHERE ";
      $strSQL .= "date_from = '$strDataDateFrom' AND date_thru = '$strDataDateThru' AND id_company = '$strDataCompany' ";
      $resDb = $db->execute($strSQL);
      if ($rowDb = $db->fetchrow($resDb)) {
        // error , sudah pernah ada
        $strError = "Duplicate!";
        return 0;
      }
    }
    */
    $intID = "";

    include_once("cls_salary_calculation_overtime.php");
    $objSalary = new clsSalaryCalculationOvertime($db, "", $bolIrregular, $strDataDate, array("id_company" => $strDataCompany),  $strDataDateFrom, $strDataDateThru, $strDataSalaryDateFrom, $strDataSalaryDateThru, false);

    $objSalary->setSalaryDate($strDataDate, $strDataDateFrom, $strDataDateThru, $strDataOvertimeDateFrom, $strDataOvertimeDateThru, $strDataSalaryDateFrom, $strDataSalaryDateThru, $strDataTHRDateFrom, $strDataTHRDateThru, $strDataCompany, $strDataIDSalarySet, $bolHideBlank, $strDataNote);

    $objSalary->saveData();
    $intID = $objSalary->strDataID;
		
		$arrAllEmployee = array();
    $arrAllEmployee = getEmployeeInSalary($db, $intID);
    setEmployeeTransferRelease($db, $intID, $arrAllEmployee);

    //set auto hold
    $arrECOpen = array();
    $arrECOpen = getEmployeeECOpen($db, $strDataSalaryDateFrom, $strDataSalaryDateThru);
    setEmployeeECOpentoHold($db, $intID, $arrECOpen);

    $arrAlpha = array();
    $arrAlpha = getEmployeeAlpha($db, $strDataDateFrom, $strDataDateThru);
    setEmployeeAlphatoHold($db, $intID, $arrAlpha);

    $arrNoAccount = array();
    $arrNoAccount = getEmployeeNoAccount($db, $intID);
    setEmployeeNoAccounttoHold($db, $intID, $arrNoAccount);

    $arrOverdue = array();
    $arrOverdue = getEmployeeOverdue($db, $strDataDateThru);
    setEmployeeOverduetoHold($db, $intID, $arrOverdue);
		
    unset($objSalary);

    return $intID;

  }// saveData

  // fungsi untuk menghapus data
  function deleteData()
  {
    global $db;
    global $myDataGrid;

    $arrKeys = array();
    $db->execute("begin");
    $isSuccess = false;
    $counter = 0;
    foreach ($myDataGrid->checkboxes as $strValue)
    {
      $counter++;
      $strSQL  = "";
      $strSQL .= "
        DELETE FROM hrd_salary_master_allowance WHERE id_salary_master = '$strValue';
        DELETE FROM hrd_salary_master_deduction WHERE id_salary_master = '$strValue';
        DELETE FROM hrd_salary_deduction WHERE id_salary_master = '$strValue';
        DELETE FROM hrd_salary_allowance WHERE id_salary_master = '$strValue';
        DELETE FROM hrd_salary_detail WHERE id_salary_master = '$strValue';
        DELETE FROM hrd_salary_master WHERE id = '$strValue';
        DELETE FROM hrd_leave_allowance WHERE  id_salary_master = '$strValue';
      ";
      $isSuccess = $db->execute($strSQL);

      if (!$isSuccess) break;
    }
    if ($isSuccess)
    {
      $db->execute("commit");
      $myDataGrid->message = $counter." record(s) ".getWords("salary data deleted!");
    }
    else
    {
      $db->execute("rollback");
      $myDataGrid->errorMessage = getWords("failed to delete salary data!");
    }

  } //deleteData

  // fungsi untuk verify, check, deny, atau approve
  function changeStatus($db, $intStatus) {
    global $_REQUEST;
    global $_SESSION;
    //global $ARRAY_CURRENCY;

    if (!is_numeric($intStatus)) {
      return false;
    }

    $strUpdate = "";
    $strSQL  = "";
    $strmodified_byID = $_SESSION['sessionUserID'];


    $strUpdate = getStatusUpdateString($intStatus);

    foreach ($_REQUEST as $strIndex => $strValue)
    {
      if (substr($strIndex,0,15) == 'DataGrid1_chkID')
      {
        $strSQLx = "SELECT id, status, salary_date, id_company, irregular
                    FROM hrd_salary_master WHERE id = '$strValue' ";
        $resDb = $db->execute($strSQLx);
        if ($rowDb = $db->fetchrow($resDb))
        {
          $strBody.= "ID: ".$rowDb['id']."<br>";
          $strBody.= "Salary Date: ".$rowDb['salary_date']."<br>";
          $strIrregular = ($rowDb['irregular'] == "FALSE") ? "NO" : "YES" ;
          $strBody.= "Irregular: ".$strIrregular."<br>";
          $strBody.= "Details are listed in Salary Calculation";
          $strBody =  getBody($intStatus,'Salary Calculation',$strBody,$strmodified_byID);
          //the status should be increasing
          //if (isProcessable($rowDb['status'], $intStatus))
		  if (($intStatus==-1)||(($rowDb['status']<$intStatus)&&($rowDb['status'] != -1)))
          {
            $strSubject = getSubject($intStatus,'Salary Calculation',$rowDb['id']);
            // sendMail($strSubject,$strBody);
            $strSQL .= "UPDATE hrd_salary_master SET $strUpdate status = '$intStatus'  ";
            $strSQL .= "WHERE id = '$strValue'; ";
            writeLog(ACTIVITY_EDIT, MODULE_PAYROLL, getCompanyCode($rowDb['id_company']) ." - ". $rowDb['salary_date'], $intStatus);
          }
        }
      }
      $resExec = $db->execute($strSQL);

    }

  } //changeStatus
  //----------------------------------------------------------------------


  //class inheritance from cDataGrid
  class cDataGrid2 extends cDataGrid
  {
    /*you can inherit this function to created your own TR class or style*/
     function printOpeningRow($intRows, $rowDb)
    {
      $strResult = "";
      $strClass = getCssClass($rowDb['status']);
      if ($strClass != "") $strClass = "class=\"".$strClass."\"";
      $strResult .= "
            <tr $strClass valign=\"top\">";
      return $strResult;
    }
  }

  // fungsi getData dengan datagrid
  function getDataGrid($db, $strCriteria, $bolLimit = true, $isFullView = false)
  {
    global $bolPrint;
    global $dataPrivilege, $bolCanView, $bolCanEdit, $bolCanDelete, $bolCanApprove, $bolCanCheck, $bolCanAcknowledge;

    global $intTotalData;
    global $myDataGrid;
    //$bolCanApprove2 = isEligibleApprove2($db);
    $tblSalary = new cModel("hrd_salary_master", getWords("salary calculation"));

    $DEFAULTPAGELIMIT = getSetting("rows_per_page");
    if (!is_numeric($DEFAULTPAGELIMIT)) $DEFAULTPAGELIMIT = 50;
    if ($bolPrint)
    {
      $myDataGrid = new cDataGrid2("formData", "DataGrid1", "", "", false, false, false, false);
    }
    else
    {
      $myDataGrid = new cDataGrid2("formData", "DataGrid1", "100%", "", $bolLimit, false, true);
      $myDataGrid->caption = getWords("list of overtime calculation");
    }
    $myDataGrid->disableFormTag();
    //$myDataGrid->setAJAXCallBackScript(basename($_SERVER['PHP_SELF']));
    $myDataGrid->pageSortBy = "id DESC";
    $myDataGrid->setCriteria($strCriteria);

    //end of class initialization
    if(!isset($_REQUEST['btnExportXLS']))
    $myDataGrid->addColumnCheckbox(new DataGrid_Column("chkID", "id", array('width' => 15), array('align'=>'center', 'nowrap' => '')));

    $myDataGrid->addColumnNumbering(new DataGrid_Column("No", "", array('width'=>30), array('nowrap'=>'')));
    $myDataGrid->addColumn(new DataGrid_Column(getWords("company"), "id_company", array("width" => 70),  array("nowrap" => "nowrap"), true, true, "", "printCompanyName()", "string", true, 12));
    $myDataGrid->addColumn(new DataGrid_Column("id", "id", array('width' => 15), array('align'=>'center', 'nowrap' => '')));
    //$myDataGrid->addColumn(new DataGrid_Column(getWords("currency"), "salary_currency", array("width" => 70),  array("nowrap" => "nowrap"), true, true, "", "printSalaryCurrency()", "string", true, 12));
    $myDataGrid->addColumn(new DataGrid_Column(getWords("salary date"), "salary_date", array("width" => 70),  array("nowrap" => "nowrap", "align" => "center"), true, true, "", "formatDate()", "string", true, 12));
    $myDataGrid->addColumn(new DataGrid_Column(getWords("salary periode"), "salary_periode", array("width" => 70),  array("nowrap" => "nowrap", "align" => "center"), true, true, "", "", "string", true, 12));
    $myDataGrid->addColumn(new DataGrid_Column(getWords("salary period from"), "date_from_salary", array("width" => 70),  array("nowrap" => "nowrap"), true, true, "", "formatDate()", "string", true, 12));
    $myDataGrid->addColumn(new DataGrid_Column(getWords("salary period thru"), "date_thru_salary", array("width" => 70), array("nowrap" => "nowrap"), true, true, "", "formatDate()", "string", true, 12));
   	$myDataGrid->addColumn(new DataGrid_Column(getWords("attendance period from"), "date_from", array("width" => 70),  array("nowrap" => "nowrap"), true, true, "", "formatDate()", "string", true, 12));
    $myDataGrid->addColumn(new DataGrid_Column(getWords("attendance period thru"), "date_thru", array("width" => 70), array("nowrap" => "nowrap"), true, true, "", "formatDate()", "string", true, 12));
    $myDataGrid->addColumn(new DataGrid_Column(getWords("overtime period from"), "date_from_overtime", array("width" => 70),  array("nowrap" => "nowrap", "align" => "center"), true, true, "", "formatDate()", "string", true, 12));
    $myDataGrid->addColumn(new DataGrid_Column(getWords("overtime period thru"), "date_thru_overtime", array("width" => 70), array("nowrap" => "nowrap", "align" => "center"), true, true, "", "formatDate()", "string", true, 12));
    // $myDataGrid->addColumn(new DataGrid_Column(getWords("thr period from"), "date_from_thr", array("width" => 70),  array("nowrap" => "nowrap"), true, true, "", "formatDate()", "string", true, 12));
    // $myDataGrid->addColumn(new DataGrid_Column(getWords("thr period thru"), "date_thru_thr", array("width" => 70), array("nowrap" => "nowrap"), true, true, "", "formatDate()", "string", true, 12));
    // $myDataGrid->addColumn(new DataGrid_Column(getWords("irregular income"), "irregular", array("width" => 50),  array("align" => "center", "nowrap" => "nowrap"), true, true, "", "printActiveSymbol()", "string", true, 12));
    // $myDataGrid->addColumn(new DataGrid_Column(getWords("hide if blank"), "hide_blank", array("width" => 50),  array("align" => "center", "nowrap" => "nowrap"), true, true, "", "printActiveSymbol()", "string", true, 12));
    $myDataGrid->addColumn(new DataGrid_Column(getWords("note"), "note", array(),  array("nowrap" => "nowrap"), true, true, "", "", "string", true));
    //$myDataGrid->addColumn(new DataGrid_Column(getWords("tax rate"), "tax_rate", array(),  array("nowrap" => "nowrap"), true, true, "", "", "string", true));
    $myDataGrid->addColumn(new DataGrid_Column(getWords("created"), "created", array("width" => 50),  array( "nowrap" => "nowrap", "align" => "center"), true, true, "", "formatDate()", "string", true, 12));
    $myDataGrid->addColumn(new DataGrid_Column(getWords("approved"), "approved_time", array("width" => 50),  array( "nowrap" => "nowrap", "align" => "center"), true, true, "", "formatDate()", "string", true, 12));
    $myDataGrid->addColumn(new DataGrid_Column(getWords("approved by"), "approved_by", array("width" => 50),  array("align" => "center", "nowrap" => "nowrap"), true, true, "", "printUserName()", "string", true, 12));
    $myDataGrid->addColumn(new DataGrid_Column(getWords("status"), "status", array('width' => '60'), array( "nowrap" => "nowrap", "align" => "center"), true, true, "","printRequestStatus()"));
    $myDataGrid->addColumn(new DataGrid_Column(getWords("show"), "", array("width" => 30),  array("align" => "center"), true, true, "", "printShowLink()", "string", false, 12));

    generateRoleButtons($bolCanEdit, $bolCanDelete, $bolCanCheck, $bolCanApprove,  true, true, $myDataGrid);

    //$myDataGrid->addButton("btnPrint", "btnPrint", "submit", getWords("print"), "onClick=\"document.formData.target = '_blank';\"");
    $myDataGrid->addButtonExportExcel(getWords("export excel"), "overtimelist.xls", getWords("list of overtime"));
    $myDataGrid->getRequest();

    //--------------------------------
    //get Data and set to Datagrid's DataSource by set the data binding (bind method)


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
    $strCriteria.= "AND is_overtime_only is true";
    $myDataGrid->totalData = $tblSalary->findCount($strCriteria);
    $dataset = $tblSalary->findAll($strCriteria,
                             "id, id_company, salary_date, to_char(salary_date, 'Mon YYYY') AS salary_periode, date_from, date_thru, date_from_thr, date_thru_thr, date_from_salary, date_thru_salary, date_from_overtime, date_thru_overtime, irregular, hide_blank, note, created :: date, approved_time :: date, approved_by,status" ,
                             $strOrderBy,
                             $strPageLimit,
                             $intPageNumber);

    $intTotalData = count($dataset);
    $myDataGrid->bind($dataset);

    return $myDataGrid->render();
  }

  function printShowLink($params)
  {
    extract($params);
    //2011-04-21:agnes;
    // return generateHidden("dataID_".$record['id'], $record['id'], ""). generateButton("btnReferer1".$record['id'], getWords("show"), "style=\"background-color:white;border:none;color:blue;width:50px;text-align:left;\"".getWords("show"), "onclick=\"document.formReferer1.dataID.value = '".$record['id']."';document.formReferer1.submit()\"");
    return "<a href='salary_calculation_result_overtime.php?dataID=" .$record['id']. "'>" .getWords("show"). "</a>";
  }



  //----MAIN PROGRAM -----------------------------------------------------
  $strInfo = "";
  $db = new CdbClass;
  if ($db->connect())
  {
    if (isset($_REQUEST['btnStart']))
    {
      if ($bolCanEdit) {
        $strError = "";
        $intID = saveData($db,$strError);
        if ($intID > 0) { // error
          // langsung redirect
           redirectPage("salary_calculation_overtime.php?dataID=$intID");
          //echo "<script>postToURL('salary_calculation_result.php', {'dataID':'$intID'})</script>";
          exit();
        } else {
          if ($strError != "") {
            echo "<script>alert('$strError');</script>";
          }
        }
      }
    }

    // ------ AMBIL DATA KRITERIA -------------------------

    // ------------ GENERATE KRITERIA QUERY,JIKA ADA -------------
    $strKriteria = "";
    $strKriteria .= $strKriteriaCompany;
    $bolLimit = false;
    $bolPrint = false;

    if ($bolCanView)
    {
      //$strDataDetail = getData($db, $intTotalData, $strKriteria);

      $strDataDetail = getDataGrid($db, $strKriteria);
    } else {
      showError("view_denied");
      $strDataDetail = "";
    }

    // generate data hidden input dan element form input
    $intDefaultWidthPx = 200;
    //getDefaultSalaryPeriode($strDefaultFrom,$strDefaultThru);

    $strDefaultDate = date("Y-m-d");
    $arrDateThisMonth = explode("-", $strDefaultDate);
    $strTempDate = getNextDateNextMonth($strDefaultDate, -1);
    $arrDatePrevMonth = explode("-", $strTempDate);
    $arrDt = explode("-", $strTempDate);

    $strSalaryDateFrom = $arrDatePrevMonth[0]."-".$arrDatePrevMonth[1]."-25";
    $strSalaryDateThru = $arrDateThisMonth[0]."-".$arrDateThisMonth[1]."-24";
    $strOvertimeDateFrom = $arrDatePrevMonth[0]."-".$arrDatePrevMonth[1]."-25";
    $strOvertimeDateThru = $arrDateThisMonth[0]."-".$arrDateThisMonth[1]."-24";
    $strAttendanceDateFrom = $arrDatePrevMonth[0]."-".$arrDatePrevMonth[1]."-25";
    $strAttendanceDateThru = $arrDateThisMonth[0]."-".$arrDateThisMonth[1]."-24";


    $strDefaultFrom = $arrDt[0]."-".$arrDt[1]."-"."01";
    $strDefaultThru  = $arrDt[0]."-".$arrDt[1]."-".lastday($arrDt[1], $arrDt[0]);


    if (!validStandardDate($strDefaultDate)) $strDefaultDate = $strDefaultThru;
    /*
    $dtNow = getdate();
    $strDefaultThru = $dtNow['year']."-".$dtNow['mon']."-15";
    if ($dtNow['mon'] == 1) {
      $strDefaultFrom = ($dtNow['year'] - 1)."-12-16";
    } else {
      $strDefaultFrom = $dtNow['year']."-".($dtNow['mon']-1)."-16";
    }
    */

    $strInputDateFrom     = "<input type=text name=dataDateFrom id=dataDateFrom size=15 maxlength=10 value=\"$strAttendanceDateFrom\">";
    $strInputDateThru     = "<input type=text name=dataDateThru id=dataDateThru size=15 maxlength=10 value=\"$strAttendanceDateThru\">";
    $strInputOvertimeDateFrom     = "<input type=text name=dataOvertimeDateFrom id=dataOvertimeDateFrom size=15 maxlength=10 value=\"$strOvertimeDateFrom\">";
    $strInputOvertimeDateThru     = "<input type=text name=dataOvertimeDateThru id=dataOvertimeDateThru size=15 maxlength=10 value=\"$strOvertimeDateThru\">";
    $strInputSalaryDateFrom     = "<input type=text name=dataSalaryDateFrom id=dataSalaryDateFrom size=15 maxlength=10 value=\"$strSalaryDateFrom\">";
    $strInputSalaryDateThru     = "<input type=text name=dataSalaryDateThru id=dataSalaryDateThru size=15 maxlength=10 value=\"$strSalaryDateThru\">";
    $strInputTHRDateFrom  = "<input type=text name=dataTHRDateFrom id=dataTHRDateFrom size=15 maxlength=10 value=\"\">";
    $strInputTHRDateThru  = "<input type=text name=dataTHRDateThru id=dataTHRDateThru size=15 maxlength=10 value=\"\">";
    $strInputDate         = "<input type=text name=dataDate id=dataDate size=15 maxlength=10 value=\"$strDefaultDate\">";
    //$strInputCurrency     = getComboFromArray($ARRAY_CURRENCY, "dataCurrency", "", $strEmptyOption, " style=\"width:$intDefaultWidthPx\"");
    $strInputCompany      = getCompanyList($db, "dataCompany",$strDataCompany, $strEmptyOption2, $strKriteria2, "style=\"width:$intDefaultWidthPx\" ");
    $strInputIrregular    = generateCheckBox("dataIrregular", false, "");
    $strInputHideBlank    = generateCheckBox("dataHideBlank", false, "");
    //$strInputTaxRate      = generateInput("dataTaxRate", 1);
    $strInputNote         = generateTextArea("dataNote", getWords("(note)"), "rows=1, cols=45 style=\"color:gray\"", "onFocus=\"this.value=''\" onBlur=\"if(this.value == '') this.value='".getWords("(note)")."'\"");

    $tblBasicSalarySet = new cHrdBasicSalarySet();
    $arrBasicSalarySet = $tblBasicSalarySet->findAll($strKriteriaCompany, "id, start_date, id_company", "start_date desc", null, 1, "id");
    foreach($arrBasicSalarySet AS $keySet => $arrSet)
    {
      $arrSetSource[$keySet] = $arrSet['start_date']." - ". printCompanyName($arrSet['id_company']);
    }

    $strInputSalarySet = getComboFromArray($arrSetSource, "dataIDSalarySet", "", "", "style=\"width:$intDefaultWidthPx\"");
  }


  $tbsPage = new clsTinyButStrong ;

  //write this variable in every page
  $strPageTitle = $dataPrivilege['menu_name'];
  $pageIcon = "../images/icons/".$dataPrivilege['icon_file'];
  $strTemplateFile = getTemplate(str_replace(".php", ".html", basename($_SERVER['PHP_SELF'])));
  //------------------------------------------------
  //Load Master Template
  $tbsPage->LoadTemplate($strMainTemplate) ;
  $tbsPage->Show() ;

?>
