<?php
  include_once('../global/session.php');
  include_once('global.php');
  include_once('form_object.php');
  include_once('activity.php');
  include_once '../global/email_func.php';
  include_once("../includes/krumo/class.krumo.php");
  $dataPrivilege = getDataPrivileges("warning_edit.php", $bolCanView, $bolCanEdit, $bolCanDelete, $bolCanApprove, $bolCanCheck, $bolCanAcknowledge, $bolCanApprove2);
  if (!$bolCanView) die(accessDenied($_SERVER['HTTP_REFERER']));


  //---- INISIALISASI ----------------------------------------------------
  $strDataDetail = "";
  $strHidden = "";
  $intTotalData = 0;
  $strWordsNew       = getWords("new");
  $strWordsDenied    = getWords("denied");
  $strWordsChecked   = getWords("checked");
  $strWordsApproved  = getWords("approved");
  $strWordsApproved2 = getWords("approved 2");
  $strWordsFinished  = getWords("finished");
  //----------------------------------------------------------------------

  //--- DAFTAR FUNSI------------------------------------------------------
  // fungsi untuk menampilkan data
  // $db = kelas database, $intRows = jumlah baris (return)
  // $strKriteria = query kriteria, $strOrder = query ORder by
  function getData($db, $strDataDateFrom, $strDataDateThru, &$intRows, $strKriteria = "", $strOrder = "") {
    global $words;
    global $ARRAY_EMPLOYEE_STATUS;
    $dtToday = getdate();
    $intRows = 0;
    $strResult = "";

    // ambil dulu data employee, kumpulkan dalam array
    $arrEmployee = array();
    $i = 0;
    $strSQL  = "SELECT t1.*, t2.employee_id, t2.employee_id_2, t2.position_code, t2.employee_name, t2.active, t2.id as id_employee, ";
    $strSQL .= "t2.section_code, t2.sub_section_code FROM hrd_employee_warning AS t1 ";
    $strSQL .= "LEFT JOIN hrd_employee AS t2 ON t1.id_employee = t2.id ";
    $strSQL .= "WHERE 1=1 ";
    if ((isset($strDataDateFrom) && $strDataDateFrom != '') || (isset($strDataDateThru) && $strDataDateThru != '')) {
      $strSQL .= " AND ((warning_date, t1.due_date) OVERLAPS ";
      $strSQL .= "(date '$strDataDateFrom', date '$strDataDateThru') OR t1.due_date = '$strDataDateThru') ";
    }
    $strSQL .= $strKriteria;
    $strSQL .= "ORDER BY $strOrder t2.employee_name, t1.warning_date, t1.duration ";
    $resDb = $db->execute($strSQL);
    $strDateOld = "";
    while ($rowdb = $db->fetchrow($resDb)) {
      $intRows++;


      $strClass = getCssClass($rowdb['status']);

      $strResult .= "<tr valign=top class=$strClass>\n";
      $strResult .= "  <td><input type=checkbox name='chkID$intRows' value=\"" .$rowdb['id']. "\"></td>\n";
      $strResult .= "  <td>" .$rowdb['employee_id']. "&nbsp;</td>";
      $strResult .= "  <td>" .$rowdb['employee_id_2']. "&nbsp;</td>";
      $strResult .= "  <td>" .$rowdb['employee_name']. "&nbsp;</td>";
      $strResult .= "  <td>" .$rowdb['position_code']. "&nbsp;</td>";
      $strResult .= "  <td>" .$rowdb['section_code']. "&nbsp;</td>";
      $strResult .= "  <td>" .$rowdb['sub_section_code']. "&nbsp;</td>";
      $strResult .= "  <td align=center>" .printAct($rowdb['active']). "&nbsp;</td>";
      $strResult .= "  <td align=center>" .pgDateFormat($rowdb['warning_date'],"d-M-y"). "&nbsp;</td>";
      $strResult .= "  <td>" .$rowdb['warning_code']. "&nbsp;</td>";
      $strResult .= "  <td align=right>" .$rowdb['duration']. "&nbsp;</td>";
      $strResult .= "  <td align=center>" .pgDateFormat($rowdb['due_date'],"d-M-y"). "&nbsp;</td>";
      $strResult .= "  <td>" .$rowdb['note']. "&nbsp;</td>";
      $strResult .= "  <td>" .printRequestStatus($rowdb['status']). "&nbsp;</td>";
      $strResult .= "  <td align=center><a href=\"warning_edit.php?dataID=" .$rowdb['id']. "\">" .$words['edit']. "</a>&nbsp;</td>";
      $strResult .= "</tr>\n";
    }
    if ($intRows > 0) {
      writeLog(ACTIVITY_VIEW, MODULE_PAYROLL,"$intRows data",0);
    }
    return $strResult;
  } // showData
  function printAct($a)
  {
    if ($a == 1)
      return "&radic;";
    else
      return "";
  }
  // fungsi untuk menghapus data
  function deleteData($db) {
    global $_REQUEST;

    $i = 0;
    foreach ($_REQUEST as $strIndex => $strValue) {
      if (substr($strIndex,0,5) == 'chkID') {
        $strSQL  = "DELETE FROM hrd_employee_warning WHERE id = '$strValue' ";
        $resExec = $db->execute($strSQL);
        $i++;
      }
    }
    if ($i > 0) {
      writeLog(ACTIVITY_DELETE, MODULE_PAYROLL,"$i data",0);
    }

  } //deleteData
    function checkData($db) {
    global $_REQUEST;

    $strmodified_byID = $_SESSION['sessionUserID'];
    $i = 0;
    foreach ($_REQUEST as $strIndex => $strValue) {
      if (substr($strIndex,0,5) == 'chkID') {
        $strSQL = "SELECT id_employee, status, warning_code, warning_date, due_date FROM hrd_employee_warning WHERE id = '$strValue'";
        // echo $strSQL; die();
        $resExec = $db->execute($strSQL);
        $rowdb = $db->fetchrow($resExec);
        if($rowdb['status'] == 0)
            {
              $strBody = "Name: ".getEmployeeNameEmail($rowdb['id_employee'])."<br>";
              $strBody.= "Warning Type: ".$rowdb['warning_code']."<br>";
              $strBody.= "Date: ".$rowdb['warning_date']." until ".$rowdb['due_date'];
              $strBody =  getBody(1,'Warning',$strBody,$strmodified_byID);
              $strSubject = getSubject(1,'Warning',getEmployeeIDEmail($rowdb['id_employee']));
              sendMail($strSubject,$strBody);
              $strSQL  = "UPDATE hrd_employee_warning SET status = 1 WHERE id = '$strValue' ";
        }
        $resExec = $db->execute($strSQL);
        $i++;
      }

    }
    }

    function approveData($db) {
    global $_REQUEST;

    $i = 0;

    foreach ($_REQUEST as $strIndex => $strValue) {
      if (substr($strIndex,0,5) == 'chkID') {
        $strSQL = "SELECT id_employee, status, warning_code, warning_date, due_date FROM hrd_employee_warning WHERE id = '$strValue'";        $resExec = $db->execute($strSQL);
        $resExec = $db->execute($strSQL);
        $rowdb = $db->fetchrow($resExec);
        if($rowdb['status'] == 0 || $rowdb['status'] == 1)
            {
              // $strBody = "Name: ".getEmployeeNameEmail($rowdb['id_employee'])."<br>";
              // $strBody.= "Warning Type: ".$rowdb['warning_code']."<br>";
              // $strBody.= "Date: ".$rowdb['warning_date']." until ".$rowdb['due_date'];
              // $strBody =  getBody(2,'Warning',$strBody,$strmodified_byID);
              // $strSubject = getSubject(2,'Warning',getEmployeeIDEmail($rowdb['id_employee']));
              // sendMail($strSubject,$strBody);
              $strSQL  = "UPDATE hrd_employee_warning SET status = 2 WHERE id = '$strValue' ";
        }
        $resExec = $db->execute($strSQL);
        $i++;
      }

    }
    }

    function approveData2($db) {
      global $_REQUEST;
      $i = 0;

      $strmodified_byID = $_SESSION['sessionUserID'];
      foreach ($_REQUEST as $strIndex => $strValue) {
        if (substr($strIndex,0,5) == 'chkID') {
          $strSQL = "SELECT id_employee, status, warning_code, warning_date, due_date FROM hrd_employee_warning WHERE id = '$strValue'";      $resExec = $db->execute($strSQL);
          $resExec = $db->execute($strSQL);
          $rowdb = $db->fetchrow($resExec);
          if($rowdb['status'] == 0 || $rowdb['status'] == 1 || $rowdb['status'] == 2)
          {
            // $strBody = "Name: ".getEmployeeNameEmail($rowdb['id_employee'])."<br>";
            // $strBody.= "Warning Type: ".$rowdb['warning_code']."<br>";
            // $strBody.= "Date: ".$rowdb['warning_date']." until ".$rowdb['due_date'];
            // $strBody =  getBody(6,'Warning',$strBody,$strmodified_byID);
            // $strSubject = getSubject(6,'Warning',getEmployeeIDEmail($rowdb['id_employee']));
            // sendMail($strSubject,$strBody);
            $strSQL  = "UPDATE hrd_employee_warning SET status = 6 WHERE id = '$strValue' ";
          }
          $resExec = $db->execute($strSQL);
          $i++;
        }

      }
    }

    function deniedData($db) {
    global $_REQUEST;

    $i = 0;
    foreach ($_REQUEST as $strIndex => $strValue) {
      if (substr($strIndex,0,5) == 'chkID') {
        $strSQL  = "UPDATE hrd_employee_warning SET status = -1 WHERE id = '$strValue' ";
        $resExec = $db->execute($strSQL);
        $i++;
      }

    }
    }
  //----------------------------------------------------------------------

  //----MAIN PROGRAM -----------------------------------------------------
  $strInfo = "";

  $db = new CdbClass;
  if ($db->connect()) {
    //$bolCanApprove2 = isEligibleApprove2($db);
    // hapus data jika ada perintah
    if (isset($_REQUEST['btnDelete']) && !isset($_REQUEST['btnShow'])) {
      if ($bolCanDelete) {
        deleteData($db);
      }
    }
      if (isset($_REQUEST['btnChecked']) && !isset($_REQUEST['btnShow'])) {
      if ($bolCanCheck) {
        checkData($db);
      }
    }
      if (isset($_REQUEST['btnApproved']) && !isset($_REQUEST['btnShow'])) {
      if ($bolCanApprove) {
        approveData($db);
      }
    }
      if (isset($_REQUEST['btnApproved2']) && !isset($_REQUEST['btnShow'])) {
      if ($bolCanApprove2) {
        approveData2($db);
      }
    }
      if (isset($_REQUEST['btnDenied']) && !isset($_REQUEST['btnShow'])) {
      if ($bolCanApprove) {
        deniedData($db);
      }
    }

    // ------ AMBIL DATA KRITERIA -------------------------
    $dtFrom = date("Y-m-")."25";
    $dtFrom = getNextDateNextMonth($dtFrom, -1);
    $dtThru = date("Y-m-")."24";
    getUserEmployeeInfo();
    (isset($_REQUEST['dataDateFrom'])) ? $strDataDateFrom = $_REQUEST['dataDateFrom'] : $strDataDateFrom = '';
    (isset($_REQUEST['dataDateThru'])) ? $strDataDateThru = $_REQUEST['dataDateThru'] : $strDataDateThru = '';
    (isset($_REQUEST['dataDivision'])) ? $strDataDivision = $_REQUEST['dataDivision'] : $strDataDivision = "";
    (isset($_REQUEST['dataDepartment'])) ? $strDataDepartment = $_REQUEST['dataDepartment'] : $strDataDepartment = "";
    (isset($_REQUEST['dataSection'])) ? $strDataSection = $_REQUEST['dataSection'] : $strDataSection = "";
    (isset($_REQUEST['dataSubsection'])) ? $strDataSubsection = $_REQUEST['dataSubsection'] : $strDataSubsection = "";
    (isset($_REQUEST['dataEmployee'])) ? $strDataEmployee = $_REQUEST['dataEmployee'] : $strDataEmployee = "";
  (isset($_REQUEST['dataActive'])) ? $strDataActive = $_REQUEST['dataActive'] : $strDataActive = '';
    (isset($_REQUEST['dataEmployeeStatus'])) ? $strDataEmployeeStatus = $_REQUEST['dataEmployeeStatus'] : $strDataEmployeeStatus = "";
    (isset($_REQUEST['dataStatus'])) ? $strDataStatus = $_REQUEST['dataStatus'] : $strDataStatus = '';
    scopeData($strDataEmployeeID, $strDataSubSection, $strDataSection, $strDataDepartment, $strDataDivision, $_SESSION['sessionUserRole'], $arrUserInfo, $strDataBranch);
    // ------------ GENERATE KRITERIA QUERY,JIKA ADA -------------
    $strKriteria = "";
    if ($strDataBranch != ""){
        $strKriteria .= "AND t2.branch_code = '$strDataBranch' ";
    }
    if ($strDataDivision != "") {
      $strKriteria .= "AND t2.division_code = '$strDataDivision' ";
    }
    if ($strDataDepartment != "") {
      $strKriteria .= "AND t2.department_code = '$strDataDepartment' ";
    }
    if ($strDataSection != "") {
      $strKriteria .= "AND t2.section_code = '$strDataSection' ";
    }
    if ($strDataSubsection != "") {
      $strKriteria .= "AND t2.sub_section_code = '$strDataSubsection' ";
    }
    if ($strDataEmployee != "") {
      $strKriteria .= "AND t2.employee_id = '$strDataEmployee' ";
    }
  if ($strDataActive != "") {
      $strKriteria .= "AND t2.Active = '$strDataActive' ";
    }
    if ($strDataEmployeeStatus != "") {
      $strKriteria .= "AND employee_status = '$strDataEmployeeStatus' ";
    }
    if ($strDataStatus != "") {
      $strKriteria .= "AND t1.status = '$strDataStatus' ";
    }
    $strKriteria .= $strKriteriaCompany;

    if ($bolCanView) {
      //if (validStandardDate($strDataDateFrom) && validStandardDate($strDataDateThru)) {
        // tampilkan hanya jika ada permintaan dan data tanggalnya tepat
        $strDataDetail = getData($db,$strDataDateFrom, $strDataDateThru, $intTotalData, $strKriteria);
      //} else {
      //  $strDataDetail = "";
      //}
    } else {
      showError("view_denied");
      $strDataDetail = "";
    }

    // generate data hidden input dan element form input
    $intDefaultWidthPx = 200;
    $strInputDateFrom = "<input type=text name=dataDateFrom id=dataDateFrom size=15 maxlength=10 value=\"$strDataDateFrom\">";
    $strInputDateThru = "<input type=text name=dataDateThru id=dataDateThru size=15 maxlength=10 value=\"$strDataDateThru\">";
    $strInputEmployee = "<input type=text name=dataEmployee id=dataEmployee size=15 maxlength=30 value=\"$strDataEmployee\">";
    $strInputDivision = getDivisionList($db,"dataDivision",$strDataDivision, $strEmptyOption, "", "style=\"width:$intDefaultWidthPx\"".$ARRAY_DISABLE_GROUP['division']);
    $strInputDepartment = getDepartmentList($db,"dataDepartment",$strDataDepartment, $strEmptyOption, "", "style=\"width:$intDefaultWidthPx\"".$ARRAY_DISABLE_GROUP['department']);
    $strInputSection = getSectionList($db,"dataSection",$strDataSection, $strEmptyOption, "", "style=\"width:$intDefaultWidthPx\"".$ARRAY_DISABLE_GROUP['section']);
    $strInputSubsection = getSubSectionList($db,"dataSubsection",$strDataSubsection, $strEmptyOption, "", "style=\"width:$intDefaultWidthPx\"".$ARRAY_DISABLE_GROUP['sub_section']);
  $strInputActive = getEmployeeActiveList("dataActive", $strDataActive, $strEmptyOption2, "style=\"width:$intDefaultWidthPx\"");
    $strInputEmployeeStatus = getEmployeeStatusList("dataEmployeeStatus",$strDataEmployeeStatus,  $strEmptyOption2, "style=\"width:$intDefaultWidthPx\"");
    $strInputStatus = getComboFromArray($ARRAY_REQUEST_STATUS, "dataStatus", ($strDataStatus = getInitialValue("Status")), $strEmptyOption, "style=width:$strDefaultWidthPx");
     //handle user company-access-right
    $strInputCompany = getCompanyList($db, "dataCompany",$strDataCompany, $strEmptyOption2, $strKriteria2, "style=\"width:$intDefaultWidthPx\" ");

      $strButtons = generateRoleButtons($dataPrivilege['edit'], $dataPrivilege['delete'], $dataPrivilege['check'], $dataPrivilege['approve'], $dataPrivilege['approve2']);
    // informasi tanggal kehadiran
    if ($strDataDateFrom == $strDataDateThru) {
      $strInfo .= "<br>".strtoupper(pgDateFormat($strDataDateFrom, "d-M-Y"));
    } else {
      $strInfo .= "<br>".strtoupper(pgDateFormat($strDataDateFrom, "d-M-Y"));
      $strInfo .= " >> ".strtoupper(pgDateFormat($strDataDateThru, "d-M-Y"));
    }

    $strHidden .= "<input type=hidden name=dataDateFrom value=\"$strDataDateFrom\">";
    $strHidden .= "<input type=hidden name=dataDateThru value=\"$strDataDateThru\">";
    $strHidden .= "<input type=hidden name=dataDivision value=\"$strDataDivision\">";
    $strHidden .= "<input type=hidden name=dataDepartment value=\"$strDataDepartment\">";
    $strHidden .= "<input type=hidden name=dataSection value=\"$strDataSection\">";
    $strHidden .= "<input type=hidden name=dataSubsection value=\"$strDataSubsection\">";
    $strHidden .= "<input type=hidden name=dataEmployee value=\"$strDataEmployee\">";
    $strHidden .= "<input type=hidden name=dataActive value=\"$strDataActive\">";
    $strHidden .= "<input type=hidden name=dataEmployeeStatus value=\"$strDataEmployeeStatus\">";
    $strHidden .= "<input type=hidden name=dataStatus value=\"$strDataStatus\">";
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
