<?
  include_once('../global/session.php');
  include_once('global.php');
  include_once('form_object.php');
  include_once('salary_func.php');
  include_once('../includes/tbsclass/plugin_excel/tbs_plugin_excel.php');
  //include_once("../includes/krumo/class.krumo.php");
  // periksa apakah sudah login atau belum, jika belum, harus login lagi
  $dataPrivilege = getDataPrivileges("salary_calculation_overtime.php", $bolCanView, $bolCanEdit, $bolCanDelete, $bolCanApprove, $bolCanCheck, $bolCanAcknowledge, $bolCanApprove2);
  if (!$bolCanView) die(accessDenied($_SERVER['HTTP_REFERER']));




  //---- INISIALISASI ----------------------------------------------------
  $strDataDetail = "";
  $strDataColumn = "";
  $strHidden = "";
  $strButtons = "";
  $intTotalData = 0;
  $strPaging = "";
  $strDataID = "";
  $strCalculationMenu = "";
  $strDataDateFrom = "";
  $strDataDateThru = "";
  $strKriteria = " ";
  $strWidth= "75px";
  $strSpan1 = 0; // colspan untuk colum allowance
  $strSpan2 = 7; // colspan untuk colum paging
  //---- INISIALISASI ----------------------------------------------------
  
  $strDataID = "";
  $strPeriode = "";
  $strKriteria = "";
  $arrData = array();
  $arrFields = array();
  $arrEmp = array();
  $arrEmpAllowance = array();
  $arrEmpDeduction = array();

  //----------------------------------------------------------------------

  //--- DAFTAR FUNSI------------------------------------------------------
  // fungsi untuk menampilkan data
  // $db = kelas database, $intRows = jumlah baris (return)
  // $strKriteria = query kriteria, $strOrder = query ORder by
  function getData($db, $strDataID, &$intRows, $strKriteria = "", $intPage = 1, $bolLimit = true, $strOrder = "") {
    global $words;
    global $arrData;
    global $arrFields;
    global $arrEmp;
    global $strPeriode;
    global $strCompany;
    global $ARRAY_CURRENCY_CODE;
   
    $strResult = "";
    if ($strDataID == "") {
      return "";
    } else {
      // cari info data
      $strSQL  = "SELECT *, company_name FROM \"hrd_salary_master\" AS t1 ";
      $strSQL .= "LEFT JOIN hrd_company AS t2 ON t1.id_company = t2.id ";
      $strSQL .= "WHERE t1.id = '$strDataID' ";
      $resDb = $db->execute($strSQL);
      if ($rowDb = $db->fetchrow($resDb)) {
        $strPeriode = strtoupper(date("M Y"));
        $strCompany = $rowDb['company_name'];
      }
      else
      {
        return "";
      }
    }

    $strSQL  = "SELECT irregular, hide_blank FROM hrd_salary_master WHERE id = '$strDataID' ";
    $resDb = $db->execute($strSQL);
    if ($rowDb = $db->fetchrow($resDb))
    {
      $bolIrregular = ($rowDb['irregular'] == 't');
      $bolHideBlank = ($rowDb['hide_blank'] == 't');
    }

    $strSQL  = "SELECT t1.overtime_allowance, t1.total_gross, t1.total_gross_irregular, t1.employee_id, t2.employee_name, t2.bank_account, t2.bank_account_name, t3.bank_name, 'IDR' as idr ";
    $strSQL .= "FROM hrd_salary_detail AS t1 LEFT JOIN hrd_employee AS t2 ON t1.\"id_employee\" = t2.id ";
    $strSQL .= "LEFT JOIN hrd_bank AS t3 ON t2.bank_code = t3.bank_code ";
    $strSQL .= "WHERE t1.id_salary_master = '$strDataID' $strKriteria ORDER BY t1.employee_id ";
    $resDb = $db->execute($strSQL);
    $i = 0;
    while($rowDb = $db->fetchrow($resDb))
    {
      if ($bolHideBlank)
      {
        if (!$bolIrregular && $rowDb['overtime_allowance'] <= 0) continue;
        elseif($bolIrregular && $rowDb['overtime_allowance'] <= 0) continue;
      }
      //if($bolIrregular) $rowDb['overtime_allowance'] = $rowDb['total_gross_irregular'];
      $rowDb['bank_account'] = strval($rowDb['bank_account']);
      $arrData[] = $rowDb;

    }
  } // showData

  //----------------------------------------------------------------------

  //----MAIN PROGRAM -----------------------------------------------------
  $strInfo = "";
  $db = new CdbClass;
  if ($db->connect()) {

    (isset($_REQUEST['dataID'])) ? $strDataID = $_REQUEST['dataID'] : $strDataID = "";
    if ($strDataID == "") {
      header("location:salary_calculation.php");
      exit();
    }

    $strCompanyCode = getSetting("company_code");
    $strCompanyAccount = getSetting("company_account");

    // ------ AMBIL DATA KRITERIA -------------------------

    (isset($_REQUEST['dataEmployee']))   ? $strDataEmployee   = $_REQUEST['dataEmployee'] : $strDataEmployee = "";
    (isset($_REQUEST['dataSection']))    ? $strDataSection    = $_REQUEST['dataSection'] : $strDataSection = "";
    (isset($_REQUEST['dataDepartment'])) ? $strDataDepartment = $_REQUEST['dataDepartment'] : $strDataDepartment = "";
    (isset($_REQUEST['dataType']))       ? $strDataType       = $_REQUEST['dataType'] : $strDataType = "0";
    (isset($_REQUEST['dataTransferStatus']))       ? $strDataTransferStatus       = $_REQUEST['dataTransferStatus'] : $strDataTransferStatus = "0";
    (isset($_REQUEST['dataFilterValue']))       ? $strDataFilterValue       = $_REQUEST['dataFilterValue'] : $strDataFilterValue = "0";

    // ------------ GENERATE KRITERIA QUERY,JIKA ADA -------------
    //$strKriteria = "";
   // print "<br>type data => ".$strDataType."<br>";
    if ($strDataEmployee != "") {
      $strKriteria .= "AND t1.\"employee_id\" = '$strDataEmployee' ";

    }
    if ($strDataSection != "") {
      $strKriteria .= "AND t1.\"section_code\" = '$strDataSection' ";
    }
    if ($strDataDepartment != "") {
      $strKriteria .= "AND t1.\"department_code\" = '$strDataDepartment' ";
    }
    if ($strDataType != "") {
      $strKriteria .= "AND t2.bank_code = '$strDataType' ";
    }
    if ($strDataTransferStatus != "") {
      $strKriteria .= "AND t1.transfer_status = '$strDataTransferStatus' ";
    }
    if ($strDataFilterValue != "") {
      if ($strDataFilterValue == 0)
        $strKriteria .= "AND t1.overtime_allowance >= 0 ";
      elseif ($strDataFilterValue == 1)
        $strKriteria .= "AND t1.overtime_allowance < 0 ";
      elseif ($strDataFilterValue == 2)
        $strKriteria .= " ";
    }else{
    	$strKriteria .= "AND t1.overtime_allowance >= 0 ";
    }

    if ($bolCanView) {
      $strDataDetail = getData($db, $strDataID, $intTotalData, $strKriteria);
    } else {
      showError("view_denied");
      $strDataDetail = "";
    }
    
      $tbsPage = new clsTinyButStrong ;
      $tbsPage->PlugIn(TBS_INSTALL,TBS_EXCEL);
      $tbsPage->LoadTemplate(getTemplate("bank_transfer_overtime.xml"));
      $tbsPage->MergeBlock('data',$arrData);
      $tbsPage->PlugIn(TBS_EXCEL,TBS_EXCEL_FILENAME,'banktransferovertime.xls');
      $tbsPage->Show() ;
   

  }

  

?>