<?php
  include_once('../global/session.php');
  include_once('global.php');
  include_once('form_object.php');
  include_once('../global/employee_function.php');
  include_once '../global/email_func.php';

  $dataPrivilege = getDataPrivileges(basename($_SERVER['PHP_SELF']), $bolCanView, $bolCanEdit, $bolCanDelete, $bolCanApprove);
  if (!$bolCanView) die(accessDenied($_SERVER['HTTP_REFERER']));




  //---- INISIALISASI ----------------------------------------------------
  $strDataDetail = "";
  $intDefaultWidth = 10;
  $intDefaultHeight = 3;
  $strNow = date("Y-m-d");
  $dtNow = getdate();

  $arrData = array(
    "dataDate" => $strNow,
    "dataEmployee" => "",
    "dataCode" => "1",
    "dataNote" => "",
    "dataID" => "",
  );
  //----------------------------------------------------------------------

  //--- DAFTAR FUNSI------------------------------------------------------
  // fungsi untuk menampilkan data
  // $db = kelas database, $strDataID = ID data, jika ingin ditampilkan
  // $arrInputData = array untuk menampung data
  function getData($db, $strDataID = "") {
    global $words;
    global $arrData;

    if ($strDataID != "") {
      $strSQL  = "SELECT t1.*, t2.employee_id FROM hrd_employee_warning AS t1 ";
      $strSQL .= "LEFT JOIN hrd_employee AS t2 ON t1.id_employee = t2.id ";
      $strSQL .= "WHERE t1.id = '$strDataID' ";
      $resDb = $db->execute($strSQL);
      if ($rowDb = $db->fetchrow($resDb)) {

        $arrData['dataEmployee'] = $rowDb['employee_id'];
        $arrData['dataCode'] = $rowDb['warning_code'];
        $arrData['dataDate'] = $rowDb['warning_date'];
        $arrData['dataNote'] = $rowDb['note'];
      }
    }

    writeLog(ACTIVITY_VIEW, MODULE_PAYROLL,"$strDataID",0);
    return true;
  } // showData

  // fungsi untuk menyimpan data
  function saveData($db, $strDataID, &$strError) {
    global $_REQUEST;
    global $_SESSION;
    global $error;
    global $messages;
    global $arrData;

    $strError = "";
    $bolOK = true;
    $strToday = date("Y-m-d");
    $strmodified_byID = $_SESSION['sessionUserID'];
    

    (isset($_REQUEST['dataEmployee'])) ? $strDataEmployee = $_REQUEST['dataEmployee'] : $strDataEmployee = "";
    (isset($_REQUEST['dataCode'])) ? $strDataCode = $_REQUEST['dataCode'] : $strDataCode = "";
    (isset($_REQUEST['dataDate'])) ? $strDataDate = $_REQUEST['dataDate'] : $strDataDate = "";
    (isset($_REQUEST['dataNote'])) ? $strDataNote = $_REQUEST['dataNote'] : $strDataNote = "";

    // cek validasi -----------------------
    if ($strDataEmployee == "") {
      $strError = $error['empty_code'];
      $bolOK = false;
    } else if ($strDataCode == "") {
      $strError = $error['empty_code'];
      $bolOK = false;
    } else if (!validStandardDate($strDataDate)) {
      $strError = $error['invalid_date'];
      $bolOK =  false;
    }

    // cari dta Employee ID, apakah ada atau tidak
    $strIDEmployee = getIDEmployee($db, $strDataEmployee);
    if ($strIDEmployee == "") {
      $strError = $error['data_not_found'];
      $bolOK = false;
    }

    // cari kode warning, ada atau tidak
    $strDataDuration = 0;
    if ($bolOK) {
      $strSQL  = "SELECT * FROM hrd_warning_type WHERE code = '$strDataCode' ";
      $resDb = $db->execute($strSQL);
      if ($rowDb = $db->fetchrow($resDb)) {
        $strDataDuration = $rowDb['duration'];
        $strDataDueDate = " (date '$strDataDate' + INTERVAL '$strDataDuration days') ";
      } else {
        $strDataDueDate = "'$strDataDate'";
      }
    }

    // simpan data -----------------------
    if ($bolOK) { // input OK, tinggal disimpan
      if ($strDataID == "") {
        // data baru
        $strSQL  = "INSERT INTO hrd_employee_warning (created,created_by,modified_by, ";
        $strSQL .= "id_employee,warning_date, warning_code, duration, due_date, note,status) ";
        $strSQL .= "VALUES(now(),'" .$_SESSION['sessionUserID']. "','" .$_SESSION['sessionUserID']. "', ";
        $strSQL .= "'$strIDEmployee','$strDataDate', '$strDataCode', ";
        $strSQL .= "'$strDataDuration', $strDataDueDate,'$strDataNote',0) ";
        $resExec = $db->execute($strSQL);
        $strSubject = getSubject(0,'Warning',getEmployeeIDEmail($strIDEmployee));

        writeLog(ACTIVITY_ADD, MODULE_PAYROLL,"$strIDEmployee - $strDataDate",0);
      } else {
        $strSQL  = "UPDATE hrd_employee_warning SET modified_by = '" .$_SESSION['sessionUserID']. "', ";
        $strSQL .= "id_employee = '$strIDEmployee', warning_date = '$strDataDate', ";
        $strSQL .= "warning_code = '$strDataCode', duration = '$strDataDuration', ";
        $strSQL .= "due_date = $strDataDueDate, note = '$strDataNote'  ";
        $strSQL .= "WHERE id = '$strDataID' ";
        $resExec = $db->execute($strSQL);
        $strSubject = getSubject(0,'Warning Updated',getEmployeeIDEmail($strIDEmployee));

        writeLog(ACTIVITY_EDIT, MODULE_PAYROLL,"$strIDEmployee - $strDataDate",0);
      }
        $strBody = "Name: ".getEmployeeNameEmail($strIDEmployee)."<br>";
        $strBody.= "Warning Type: ".$strDataCode."<br>";
        $strBody.= "Date: ".$strDataDate;
        $strBody.= "<br>Duration: ".$strDataDuration." day(s)";
        $strBody =  getBody(0,'Warning',$strBody,$strmodified_byID);
        sendMail($strSubject,$strBody);

      $strError = $messages['data_saved'];
    } else { // ---- data SALAH

      // gunakan data yang diisikan tadi
      $arrData['dataEmployee'] = $strDataEmployee;
      $arrData['dataDate'] = $strDataDate;
      $arrData['dataNote'] = $strDataNote;
      $arrData['dataID'] = $strDataID;
      $arrData['dataCode'] = $strDataCode;
      writeLog(ACTIVITY_EDIT, MODULE_PAYROLL,"$strDataID",1);
    }
    return $bolOK;
  } // saveData

  //----------------------------------------------------------------------

  //----MAIN PROGRAM -----------------------------------------------------
  $db = new CdbClass;
  if ($db->connect()) {
    (isset($_REQUEST['dataID'])) ? $strDataID = $_REQUEST['dataID'] : $strDataID = "";

    if ($bolCanEdit) {
      if (isset($_REQUEST['btnSave'])) {
        $bolOK = saveData($db, $strDataID, $strError);
        if ($strError != "") {
          echo "<script>alert(\"$strError\")</script>"; // tampilkan pesan tersimpan
        }
        //$strDataID = ""; // biar tidak mengambil dta, melainkan pakai data baru atau data yang dikirim (jika error)
      }
    }
    if ($bolCanView) {
      getData($db, $strDataID);
    } else {
      showError("view_denied");
      $strDataDetail = "";
    }

    //----- TAMPILKAN DATA ---------

    $strInputDate = "<input type=text size=15 maxlength=10 name=dataDate id=dataDate value=\"" .$arrData['dataDate']. "\">";
    $strInputEmployee = "<input type=text name=dataEmployee id=dataEmployee size=15 maxlength=30 value=\"" .$arrData['dataEmployee']. "\" style=\"width:$strDefaultWidthPx\">";
    $strInputNote = "<textarea name=dataNote cols=30 rows=2 wrap='virtual' style=\"width:$strDefaultWidthPx\">" .$arrData['dataNote']. "</textarea>";
    $strInputCode = getWarningTypeList($db, "dataCode", $arrData['dataCode'], $strEmptyOption,"", "style=\"width:$strDefaultWidthPx\"");
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
