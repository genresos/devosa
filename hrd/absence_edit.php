<?php
include_once('../global/session.php');
include_once('global.php');
include_once('activity.php');
include_once('form_object.php');
include_once('../global/employee_function.php');
include_once('cls_annual_leave.php');
include_once('../global/email_func.php');
//include_once("../includes/krumo/class.krumo.php");
$dataPrivilege = getDataPrivileges(
    basename($_SERVER['PHP_SELF']),
    $bolCanView,
    $bolCanEdit,
    $bolCanDelete,
    $bolCanApprove,
    $bolCanCheck,
    $bolCanAcknowledge,
    $bolCanApprove2
);
if (!$bolCanView) {
  die(accessDenied($_SERVER['HTTP_REFERER']));
}
$bolPrint = (isset($_REQUEST['btnPrint']));
//---- INISIALISASI ----------------------------------------------------
$strWordsAbsenceData = getWords("absence data");
$strWordsEntryAbsence = getWords("entry absence");
$strWordsAbsenceList = getWords("absence list");
$strWordsAbsenceSlip = getWords("Absence slip");
$strWordsEntryPartialAbsence = getWords("entry partial absence");
$strWordsPartialAbsenceList = getWords("partial absence list");
$strWordsAnnualLeave = getWords("annual leave");
$strWordsAbsenceRequestDate = getWords("absence request date");
$strWordsAbsenceDateFrom = getWords("absence date from");
$strWordsAbsenceDateThru = getWords("absence date thru");
$strWordsAbsenceType = getWords("absence type");
$strWordsEmployeeID = getwords("n i k");
$strWordsNote = getWords("note");
$strWordsStatus = getWords("status");
$strWordsDuration = getWords("duration");
$strWordsLeaveDuration = getWords("leave ");
$strWordsSave = getWords("save");
$strWordsAddNew = getWords("add new");
$strWordsDocument = getWords("document");
//$strSpecialAbsenceCode       = SPECIAL_ABSENCE_CODE;
$strDataDetail = "";
$strButtons = "";
$strMsgClass = "";
$strMessages = "";
$intDefaultWidth = 50;
$intDefaultHeight = 3;
$strNow = date("Y-m-d");
// inisialisasi untuk data array
// $arrData['dataSection'] = "";
$strUserRole = "";
$arrData = [
    "dataDate"          => $strNow,
    "dataDateFrom"      => $strNow,
    "dataDateThru"      => $strNow,
    "dataEmployee"      => "",
    "dataEmployeeName"  => "",
    "dataSection"       => "",
    "dataType"          => "",
    "dataSpecial"       => "",
    "dataDuration"      => "1",
    "dataLeaveDuration" => "0",
    "dataNote"          => "",
    "dataDoc"           => "",
    //"dataCode" => "ABSEN-HRD",
    //"dataNo" => "",
    //"dataMonth" => "",
    //"dataYear" => "",
    "dataStatus"        => 0,
    "dataID"            => "",
    // untuk keperluan print aja
    "dataDateCreated"   => "",
    "dataDateVerified"  => "",
    "dataDateApproved"  => "",
];
//----------------------------------------------------------------------
//--- DAFTAR FUNSI------------------------------------------------------
// fungsi untuk menampilkan data
// $db = kelas database, $strDataID = ID data, jika ingin ditampilkan
// $arrInputData = array untuk menampung data
function getData($db, $strDataID = "")
{
  global $words;
  global $arrData;
  if ($strDataID != "") {
    $strSQL = "SELECT t1.*, t2.employee_id, t2.id as id_employee, t2.employee_name, ";
    $strSQL .= "t3.section_name FROM hrd_absence AS t1 ";
    $strSQL .= "LEFT JOIN hrd_employee AS t2 ON t1.id_employee = t2.id ";
    $strSQL .= "LEFT JOIN hrd_section AS t3 ON t2.section_code = t3.section_code ";
    $strSQL .= "WHERE t1.id = '$strDataID' ";
    $resDb = $db->execute($strSQL);
    if ($rowDb = $db->fetchrow($resDb)) {
      $arrData['dataEmployee'] = $rowDb['employee_id'];
      $arrData['dataEmployeeName'] = $rowDb['employee_name'];
      $arrData['dataSection'] = $rowDb['section_name'];
      $arrData['dataID'] = $rowDb['id'];
      $arrData['dataType'] = $rowDb['absence_type_code'];
      $arrData['dataSpecial'] = $rowDb['special_type_code'];
      $arrData['dataDuration'] = $rowDb['duration'];
      $arrData['dataLeaveDuration'] = $rowDb['leave_duration'];
      $arrData['dataDate'] = $rowDb['request_date'];
      $arrData['dataDateFrom'] = $rowDb['date_from'];
      $arrData['dataDateThru'] = $rowDb['date_thru'];
      $arrData['dataNote'] = $rowDb['note'];
      $arrData['dataDoc'] = $rowDb['doc'];
      $arrData['dataStatus'] = $rowDb['status'];
      //$arrData['dataNo'] = $rowDb['no'];
      //$arrData['dataCode'] = $rowDb['code'];
      //$arrData['dataMonth'] = $rowDb['month_code'];
      //$arrData['dataYear'] = $rowDb['year_code'];
      $arrData['dataDateCreated'] = substr($rowDb['request_date'], 0, 10);
      $arrData['dataDateVerified'] = substr($rowDb['verified_time'], 0, 10);
      $arrData['dataDateApproved'] = substr($rowDb['approved_time'], 0, 10);
    }
  }
  return true;
} // showData
// fungsi untuk menyimpan data
function saveData($db, &$strDataID, &$strError)
{
  global $_REQUEST;
  global $_SESSION;
  global $error;
  global $messages;
  global $arrData;
  global $arrUserInfo;
  $strError = "";
  $bolOK = true;
  $strToday = date("Y-m-d");
  $strBody = "";
  (isset($_REQUEST['dataEmployee'])) ? $strDataEmployee = $_REQUEST['dataEmployee'] : $strDataEmployee = "";
  (isset($_REQUEST['dataDate'])) ? $strDataDate = $_REQUEST['dataDate'] : $strDataDate = "";
  (isset($_REQUEST['dataDateFrom'])) ? $strDataDateFrom = $_REQUEST['dataDateFrom'] : $strDataDateFrom = "";
  (isset($_REQUEST['dataDateThru'])) ? $strDataDateThru = $_REQUEST['dataDateThru'] : $strDataDateThru = "";
  (isset($_REQUEST['dataType'])) ? $strDataType = $_REQUEST['dataType'] : $strDataType = "";
  (isset($_REQUEST['dataSpecial'])) ? $strDataSpecial = $_REQUEST['dataSpecial'] : $strDataSpecial = "";
  (isset($_REQUEST['dataNote'])) ? $strDataNote = $_REQUEST['dataNote'] : $strDataNote = "";
  (isset($_REQUEST['dataStatus'])) ? $strDataStatus = $_REQUEST['dataStatus'] : $strDataStatus = "0";
  (isset($_REQUEST['detailDoc'])) ? $detailDoc = $_REQUEST['detailDoc'] : $detailDoc = "";
  // cek validasi -----------------------
  if ($strDataEmployee == "") {
    $strError = $error['empty_code'];
    $bolOK = false;
  } else if (!validStandardDate($strDataDateFrom)) {
    $strError = $error['invalid_date'];
    $bolOK = false;
  } else if (!validStandardDate($strDataDateThru)) {
    $strError = $error['invalid_date'];
    $bolOK = false;
  } else if (substr_count($strDataNote, "'")) {
    $strError = $error['invalid_text'];
    $bolOK = false;
  }
  // cari dta Employee ID, apakah ada atau tidak
  $arrEmployee = getEmployeeInfoByCode($db, $strDataEmployee, "id, employee_name");
  if (count($arrEmployee) == 0) {
    $strError = $error['employee_data_not_found'];
    $bolOK = false;
  }
  $strIDEmployee = $arrEmployee['id'];
  $strDataDuration = totalWorkDayEmployee($db, $strIDEmployee, $strDataDateFrom, $strDataDateThru);
  $strDataLeaveDuration = 0;
  if (!is_numeric($strDataDuration)) {
    $strError = $error['invalid_number'];
    $bolOK = false;
  }
  $strSQL = "SELECT * FROM hrd_absence_partial AS t1 ";
  $strSQL .= "WHERE partial_absence_date BETWEEN '$strDataDateFrom' AND '$strDataDateThru' AND id_employee = '$strIDEmployee'";
  $resS = $db->execute($strSQL);
  if ($rowS = $db->fetchrow($resS)) {
    $strError = "The selected date is used for partial absence";
    $bolOK = false;
  }
  //untuk cek jika tipe absen mengurangi cuti maka return false jika ada attendance
  $strSQL = "SELECT leave_weight FROM hrd_absence_type ";
  $strSQL .= "WHERE code = '$strDataType'";
  $resS = $db->execute($strSQL);
  if ($rowL = $db->fetchrow($resS)) {
    if ($rowL['leave_weight'] >= 1 || $rowL['leave_weight'] == "") {
      $strSQL = "SELECT * FROM hrd_attendance AS t1 ";
      $strSQL .= "WHERE attendance_date BETWEEN '$strDataDateFrom' AND '$strDataDateThru' AND id_employee = '$strIDEmployee'";
      $resS = $db->execute($strSQL);
      if ($rowS = $db->fetchrow($resS)) {
        $strError = "The selected date is not valid, please check attendance data";
        $bolOK = false;
      }
    }
  }
  // jika bukan data baru, cukup update data note
  // revisi : update data from,thru, dan duration juga
  /* remark by uddin (tidak jelas maksudnya)
    if($strDataID != "" && $bolOK)
    {
	  $strUpdatedDuration = getIntervalDate($strDataDateFrom, $strDataDateThru) +1;
	  $strSQL  = "UPDATE hrd_absence ";
	  $strSQL .= "SET modified_by = '$_SESSION[sessionUserID]', ";
	  $strSQL .= "date_from = '$strDataDateFrom' , ";
	  $strSQL .= "date_thru = '$strDataDateThru' , ";
	  $strSQL .= "duration = $strUpdatedDuration ";
	  $strSQL .= "WHERE id = '$strDataID' ";
	  $resExec = $db->execute($strSQL);
      updateNote($db, "hrd_absence", $strDataID, $_SESSION['sessionUserID'], $arrEmployee['employee_name']." - ". $strDataType ." - ". $strDataDateFrom ." - ", $strDataNote, $strDataStatus, ACTIVITY_EDIT, MODULE_EMPLOYEE);
      $strError = $messages['data_saved'];
      return true;
    }
  */
  $arrShift = [];
  $strSQL = "SELECT *, t2.shift_off FROM hrd_shift_schedule_employee AS t1 ";
  $strSQL .= "LEFT JOIN hrd_shift_type AS t2 ON t1.shift_code = t2.code ";
  $strSQL .= "WHERE shift_date BETWEEN '$strDataDateFrom' AND '$strDataDateThru'";
  $resS = $db->execute($strSQL);
  if ($rowS = $db->fetchrow($resS)) {
    $arrShift[$rowS['id_employee']]['shift_date'] = $rowS;
  }
  // Cek Deduction normal leave
  $strSQL = "SELECT code, deduct_leave FROM hrd_absence_type ";
  $strSQL .= "WHERE deduct_leave = TRUE AND code = '$strDataType'";
  $resS = $db->execute($strSQL);
  $arrLeave = [];
  if ($rowS = $db->fetchrow($resS)) {
    $intHCM = getSetting("hcm"); //hutang cuti maksimal
    $intJCI = getSetting("jci"); //jatah cuti inisial
    $strDataLeaveDuration = $strDataDuration;
    $objLeave = new clsAnnualLeave($db);
    $tempInfo = $objLeave->arrHistory;
    $objLeave->generateEmployeeAnnualLeave($strIDEmployee);
    $arrCuti = $objLeave->getEmployeeLeaveInfo($strIDEmployee);
    //echo "<br/><br/><br/>";
    //var_dump($arrCuti["prev"]);
    //echo "<br/><br/><br/>";
    //var_dump($arrCuti["curr"]);
    //echo "<br/><br/><br/>";
    $arrCuti["prev"]["add_taken"] = (isset($tempInfo[$strIDEmployee][$arrCuti["prev"]["year"]]["additional"])) ? $tempInfo[$strIDEmployee][$arrCuti["prev"]["year"]]["additional"] : 0;
    $arrCuti["curr"]["add_taken"] = (isset($tempInfo[$strIDEmployee][$arrCuti["curr"]["year"]]["additional"])) ? $tempInfo[$strIDEmployee][$arrCuti["curr"]["year"]]["additional"] : 0;
    $arrCuti["prev"]["add_quota"] = (isset($tempInfo[$strIDEmployee][$arrCuti["prev"]["year"]]["additional_quota"])) ? $tempInfo[$strIDEmployee][$arrCuti["prev"]["year"]]["additional_quota"] : 0;
    $arrCuti["curr"]["add_quota"] = (isset($tempInfo[$strIDEmployee][$arrCuti["curr"]["year"]]["additional_quota"])) ? $tempInfo[$strIDEmployee][$arrCuti["curr"]["year"]]["additional_quota"] : 0;
    if (dateCompare($strDataDateFrom, $strDataDateThru) > 0 || dateCompare(
            $strDataDateFrom,
            $arrCuti['next']['finish']
        ) > 0 || dateCompare($strDataDateThru, $arrCuti['next']['finish']) > 0 || (dateCompare(
                $strDataDateFrom,
                $arrCuti['curr']['finish']
            ) <= 0 && dateCompare($strDataDateThru, $arrCuti['curr']['finish']) > 0)
    ) {
      $strError = $error['invalid_date'];
      $bolOK = false;
    } else if (dateCompare($strDataDateThru, $arrCuti['curr']['finish']) <= 0) {
      if ($arrCuti["prev"]["overdue"] == 't') { //echo "1";
        //if ($strDataDuration > ($arrCuti['curr']['remain'] - $arrCuti['curr']['add_taken'] + $arrCuti['curr']['add_quota'] ))
        if ($strDataDuration > ($arrCuti['curr']['remain'])) { //echo "2";
          $strError = $error['leave_overquota'];
          $bolOK = false;
        }
      } else { //echo "3:";
        //if ($strDataDuration > ($arrCuti['curr']['remain'] - $arrCuti['curr']['add_taken'] + $arrCuti['curr']['add_quota']) + ($arrCuti['prev']['remain'] - $arrCuti['prev']['add_taken'] + $arrCuti['prev']['add_quota']))
        if ($strDataDuration > ($arrCuti['curr']['remain'] + $arrCuti['prev']['remain'])) { //echo "4";
          $strError = $error['leave_overquota'];
          $bolOK = false;
        }
      }
    } //else if ((dateCompare($strDataDateThru , $arrCuti['curr']['finish']) <= 0 && $strDataDuration > ($arrCuti['curr']['remain'] - $arrCuti['curr']['add_taken'] + $arrCuti['curr']['add_quota'] + $intHCM)) || (dateCompare($strDataDateThru , $arrCuti['curr']['finish']) > 0 && $strDataDuration > ($arrCuti['next']['remain'] + $intHCM))) {
    else if ((dateCompare(
                $strDataDateThru,
                $arrCuti['curr']['finish']
            ) <= 0 && $strDataDuration > ($arrCuti['curr']['remain'] + $intHCM)) || (dateCompare(
                $strDataDateThru,
                $arrCuti['curr']['finish']
            ) > 0 && $strDataDuration > ($arrCuti['next']['remain'] + $intHCM))
    ) {
      $strError = $error['leave_overquota'];
      $bolOK = false;
    }
    //die(KELUAR);
  }
  // Cek Deduction additional leave
  $strSQL = "SELECT code, deduct_leave FROM hrd_absence_type ";
  $strSQL .= "WHERE deduct_additional_leave = TRUE AND code = '$strDataType'";
  $resS = $db->execute($strSQL);
  $arrLeave = [];
  if ($rowS = $db->fetchrow($resS)) {
    $intHCM = getSetting("hcm"); //hutang cuti maksimal
    $intJCI = getSetting("jci"); //jatah cuti inisial
    $strDataLeaveDuration = $strDataDuration;
    $objLeave = new clsAnnualLeave($db);
    $tempInfo = $objLeave->arrHistory;
    $objLeave->generateEmployeeAnnualLeave($strIDEmployee);
    $arrCuti = $objLeave->getEmployeeLeaveInfo($strIDEmployee);
    //echo "<br/><br/><br/>";
    //var_dump($arrCuti["prev"]);
    //echo "<br/><br/><br/>";
    //var_dump($arrCuti["curr"]);
    //echo "<br/><br/><br/>";
    $arrCuti["prev"]["add_taken"] = (isset($tempInfo[$strIDEmployee][$arrCuti["prev"]["year"]]["additional"])) ? $tempInfo[$strIDEmployee][$arrCuti["prev"]["year"]]["additional"] : 0;
    $arrCuti["curr"]["add_taken"] = (isset($tempInfo[$strIDEmployee][$arrCuti["curr"]["year"]]["additional"])) ? $tempInfo[$strIDEmployee][$arrCuti["curr"]["year"]]["additional"] : 0;
    $arrCuti["prev"]["add_quota"] = (isset($tempInfo[$strIDEmployee][$arrCuti["prev"]["year"]]["additional_quota"])) ? $tempInfo[$strIDEmployee][$arrCuti["prev"]["year"]]["additional_quota"] : 0;
    $arrCuti["curr"]["add_quota"] = (isset($tempInfo[$strIDEmployee][$arrCuti["curr"]["year"]]["additional_quota"])) ? $tempInfo[$strIDEmployee][$arrCuti["curr"]["year"]]["additional_quota"] : 0;
    if (dateCompare($strDataDateFrom, $strDataDateThru) > 0 || dateCompare(
            $strDataDateFrom,
            $arrCuti['next']['finish']
        ) > 0 || dateCompare($strDataDateThru, $arrCuti['next']['finish']) > 0 || (dateCompare(
                $strDataDateFrom,
                $arrCuti['curr']['finish']
            ) <= 0 && dateCompare($strDataDateThru, $arrCuti['curr']['finish']) > 0)
    ) {
      $strError = $error['invalid_date'];
      $bolOK = false;
    } else if (dateCompare($strDataDateThru, $arrCuti['curr']['finish']) <= 0) {
      if ($arrCuti["prev"]["overdue"] == 't') {
        //if ($strDataDuration > ($arrCuti['curr']['remain'] - $arrCuti['curr']['add_taken'] + $arrCuti['curr']['add_quota'] ))
        if ($strDataDuration > ($arrCuti['curr']['add_quota'] - $arrCuti['curr']['add_taken'])) {
          $strError = $error['leave_overquota'];
          $bolOK = false;
        }
      } else {
        //if ($strDataDuration > ($arrCuti['curr']['remain'] - $arrCuti['curr']['add_taken'] + $arrCuti['curr']['add_quota']) + ($arrCuti['prev']['remain'] - $arrCuti['prev']['add_taken'] + $arrCuti['prev']['add_quota']))
        if ($strDataDuration > (($arrCuti['curr']['add_quota'] - $arrCuti['curr']['add_taken']) + ($arrCuti['prev']['add_quota'] - $arrCuti['prev']['add_taken']))) {
          $strError = $error['leave_overquota'];
          $bolOK = false;
        }
      }
    } //else if ((dateCompare($strDataDateThru , $arrCuti['curr']['finish']) <= 0 && $strDataDuration > ($arrCuti['curr']['remain'] - $arrCuti['curr']['add_taken'] + $arrCuti['curr']['add_quota'] + $intHCM)) || (dateCompare($strDataDateThru , $arrCuti['curr']['finish']) > 0 && $strDataDuration > ($arrCuti['next']['remain'] + $intHCM))) {
    else if ((dateCompare(
                $strDataDateThru,
                $arrCuti['curr']['finish']
            ) <= 0 && $strDataDuration > (($arrCuti['prev']['add_quota'] - $arrCuti['prev']['add_taken']) + $intHCM)) || (dateCompare(
                $strDataDateThru,
                $arrCuti['curr']['finish']
            ) > 0 && $strDataDuration > (($arrCuti['next']['add_quota'] - $arrCuti['next']['add_taken']) + $intHCM))
    ) {
      $strError = $error['leave_overquota'];
      $bolOK = false;
    }
    //die(KELUAR);
  }
  // simpan data -----------------------
  if ($bolOK) { // input OK, tinggal disimpan
    if (!is_numeric($strDataLeaveDuration)) {
      $strDataLeaveDuration = 0;
    }
    if ($strDataLeaveDuration > $strDataDuration) {
      $strDataLeaveDuration = $strDataDuration;
    }
    $strBody .= "Name: " . getEmployeeNameEmail($strIDEmployee) . "<br>";
    $strBody .= "Absence Type: " . $strDataType . "<br>";
    $strBody .= "Date: " . $strDataDateFrom . " until " . $strDataDateThru . "<br>";
    $strBody .= "Note: " . $strDataNote . "<br>";
    $strBody .= "http://192.168.0.15/devosa";
    $strBody = getBody(0, 'Absence', $strBody, $_SESSION['sessionUserID']);
    // Cek overlaping date, tidak hanya untuk absen baru,
    // untuk edit absen juga perlu di cek
    $strSQL = "SELECT id FROM hrd_absence WHERE id_employee = '$strIDEmployee' ";
    $strSQL .= "AND ((date_from, date_thru) ";
    $strSQL .= "    OVERLAPS (DATE '$strDataDateFrom', DATE '$strDataDateThru') ";
    $strSQL .= "    OR (date_thru = DATE '$strDataDateFrom') ";
    $strSQL .= "    OR (date_thru = DATE '$strDataDateThru')) ";
    $strSQL .= "AND STATUS <> " . REQUEST_STATUS_DENIED;
    $resS = $db->execute($strSQL);
    if ($rowDb = $db->fetchrow($resS)) {
      if ($strDataID == "") {
        $strError = $error['overlaping_date_entry'];
        $bolOK = false;
      } else {
        if ($strDataID != $rowDb['id']) {
          $strError = $error['overlaping_date_entry'];
          $bolOK = false;
        } else {
          $bolOK = true;
        }
      }
    }
    if ($strDataID == "") {
      if ($bolOK) {
        $strSQL = "INSERT INTO hrd_absence (created, created_by, modified_by, ";
        $strSQL .= "id_employee, request_date, date_from, date_thru, ";
        $strSQL .= "absence_type_code, special_type_code, ";
        $strSQL .= "duration, leave_duration, note,  status) ";
        $strSQL .= "VALUES(now(),'" . $_SESSION['sessionUserID'] . "','" . $_SESSION['sessionUserID'] . "', ";
        $strSQL .= "'$strIDEmployee','$strDataDate', '$strDataDateFrom', '$strDataDateThru', ";
        $strSQL .= "'$strDataType', '$strDataSpecial', ";
        $strSQL .= "'$strDataDuration', '$strDataLeaveDuration', '$strDataNote', ";
        $strSQL .= "$strDataStatus)  ";
        $resExec = $db->execute($strSQL);
        $strSubject = getSubject(0, 'Absence', getEmployeeIDEmail($strIDEmployee));
        sendMail($strSubject, $strBody);
        $strBody = "";
        // cari ID
        $strSQL = "SELECT id FROM hrd_absence ";
        $strSQL .= "WHERE id_employee = '$strIDEmployee' AND request_date = '$strDataDate' ";
        $strSQL .= "AND date_from = '$strDataDateFrom' ";
        $strSQL .= "AND date_thru = '$strDataDateThru' ";
        $strSQL .= "ORDER BY id DESC";
        $resDb = $db->execute($strSQL);
        if ($rowDb = $db->fetchrow($resDb)) {
          $strDataID = $rowDb['id'];
        }
        // simpan data doc, jika ada
        writeLog(
            ACTIVITY_ADD,
            MODULE_EMPLOYEE,
            $arrEmployee['employee_name'] . " - " . $strDataType . " - " . $strDataDateFrom . " - " . $strDataDuration . " days",
            0
        );
      }
    } else {
      if ($bolOK) {
        $strSQL = "UPDATE hrd_absence ";
        $strSQL .= "SET modified_by = '" . $_SESSION['sessionUserID'] . "', modified = now(), ";
        $strSQL .= "id_employee = '$strIDEmployee', absence_type_code = '$strDataType', ";
        $strSQL .= "special_type_code = '$strDataSpecial', ";
        $strSQL .= "request_date = '$strDataDate', ";
        $strSQL .= "date_from = '$strDataDateFrom', date_thru = '$strDataDateThru', ";
        $strSQL .= "leave_duration = '$strDataLeaveDuration', ";
        $strSQL .= "note = '$strDataNote', duration = '$strDataDuration' ";
        $strSQL .= "WHERE id = '$strDataID' ";
        //die($strSQL);
        $resExec = $db->execute($strSQL);
        $strSubject = getSubject(0, 'Absence Updated', getEmployeeIDEmail($strIDEmployee));
        sendMail($strSubject, $strBody);
        writeLog(ACTIVITY_EDIT, MODULE_PAYROLL, "ABSENCE DATA", 0);
      }
    }
    if ($strDataID != "") {
      //cek jika file kosong
      if ($_FILES["detailDoc"]['name'] != "") {
        if (is_uploaded_file($_FILES["detailDoc"]['tmp_name'])) {
          $arrNamaFile = explode(".", $_FILES["detailDoc"]['name']);
          $strNamaFile = $strDataID . "_" . $_FILES["detailDoc"]['name'];
          if (strlen($strNamaFile) > 40) {
            $strNamaFile = substr($strNamaFile, 0, 40);
          }
          $strNamaFile .= "";
          clearstatcache();
          if (!is_dir("absencedoc")) {
            mkdir("absencedoc", 0777);
          }
          $strNamaFileLengkap = "absencedoc/" . $strNamaFile;
          if (file_exists($strNamaFileLengkap)) {
            unlink($strNamaFileLengkap);
          }
          move_uploaded_file($_FILES["detailDoc"]['tmp_name'], $strNamaFileLengkap);
          // update data
          $strSQL = "UPDATE hrd_absence SET doc = '$strNamaFile' WHERE id = '$strDataID' ";
          $resExec = $db->execute($strSQL);
          // move_uploaded_file($_FILES["detailDoc"]["tmp_name"], "absencedoc/" . $_FILES["detailDoc"]["name"]);
        }
      }
    }
    // Update data kehadiran pada saat itu
    $strSQL = "UPDATE hrd_attendance SET is_absence = 't' WHERE id_employee = '$strIDEmployee' ";
    $strSQL .= "AND attendance_date BETWEEN '$strDataDateFrom' AND '$strDataDateThru' ";
    $resExec = $db->execute($strSQL);
    if ($bolOK) {
      $strCurrDate = $strDataDateFrom;
      $strSQL = "DELETE FROM hrd_absence_detail WHERE id_absence = '$strDataID'; ";
      while (dateCompare($strCurrDate, $strDataDateThru) <= 0) {
        $arrShift = getShiftScheduleByDate($db, $strCurrDate, "", "", $strIDEmployee);
        $arrWorkSchedule = getWorkSchedule($db, $strCurrDate, $strIDEmployee);
        $bolIsAllDay = getEmployeeIsAllDay($db, $strIDEmployee);
        $bolIsSatIn = getEmployeeIsSatIn($db, $strIDEmployee);
        // 1. cek dari shift schedule
        if (isset($arrShift[$strIDEmployee]['shift_off']) && $arrShift[$strIDEmployee]['shift_off'] == 't') {
          $bolHoliday = isHoliday($strCurrDate, true, $bolIsAllDay, $bolIsSatIn);
        } // 2. cek dari work schedule
        //else if (isset($arrWorkSchedule[$strIDEmployee]['day_off']) && $arrWorkSchedule[$strIDEmployee]['day_off'] == 't') {
        //  $bolHoliday = isHoliday($strCurrDate, true, $bolIsAllDay, $bolIsSatIn);
        //} // 2. cek general setting
        else {
          $bolHoliday = false;
        }
        $dateDay = date("w", strtotime($strCurrDate));
        if ($bolHoliday == false) //jika bukan hari libur, masukkan datanya
        {
          $strSQL .= "INSERT INTO hrd_absence_detail (created,modified_by,created_by, id_absence, id_employee, absence_date, absence_type) ";
          $strSQL .= "VALUES ( now(),'" . $_SESSION['sessionUserID'] . "','" . $_SESSION['sessionUserID'] . "', '$strDataID', '$strIDEmployee', '$strCurrDate','$strDataType'); ";
        }
        $strCurrDate = getNextDate($strCurrDate);
      }
      $res = $db->execute($strSQL);
      $strError = $messages['data_saved'];
    }
  } else { // ---- data SALAH
    echo "data salah";
    // gunakan data yang diisikan tadi
    $arrData['dataEmployee'] = $strDataEmployee;
    $arrData['dataDate'] = $strDataDate;
    $arrData['dataDateFrom'] = $strDataDateFrom;
    $arrData['dataDateThru'] = $strDataDateThru;
    $arrData['dataType'] = $strDataType;
    $arrData['dataDuration'] = $strDataDuration;
    $arrData['dataNote'] = $strDataNote;
    $arrData['dataDoc'] = $detailDoc;
    $arrData['dataID'] = $strDataID;
    //writeLog(ACTIVITY_EDIT, MODULE_EMPLOYEE, "data not saved - error: ".$strError, 0);
  }
  // echo "success";
  //einsert
  return $bolOK;
} // saveData
//----------------------------------------------------------------------
//----MAIN PROGRAM -----------------------------------------------------
$db = new CdbClass;
if ($db->connect()) {
  getUserEmployeeInfo();
  $strUserRole = $_SESSION['sessionUserRole'];
  if (isset($_REQUEST['dataID'])) {
    $bolIsNew = false;
    $strDataID = $_REQUEST['dataID'];
  } else {
    $strDataID = "";
    $bolIsNew = true;
  }
  if ($bolCanEdit) {
    if (isset($_REQUEST['btnSave'])) {
      $bolOK = saveData($db, $strDataID, $strError);
      $strMessages = $strError;
      $strMsgClass = ($bolOK) ? "class = bgOK" : "class = bgError";
    }
  }
  $dtNow = getdate();
  $arrData['dataMonth'] = getRomans($dtNow['mon']);
  $arrData['dataYear'] = $dtNow['year'];
  //$strInputLastNo = getLastFormNumber($db, "hrd_absence", "no", $arrData['dataMonth'], $arrData['dataYear']);
  //$intLastNo = ($strInputLastNo == "") ? 0 : (int)$strInputLastNo;
  //$arrData['dataNo'] = addPrevZero($intLastNo + 1,$intFormNumberDigit);
  getData($db, $strDataID);
  if (!empty($strDataID) && $arrData['dataStatus'] >= REQUEST_STATUS_CHECKED) {
    header('location: absence_list.php');
  }
  $strIDEmployee = getIDEmployee($db, $arrData['dataEmployee']);
  if ($strIDEmployee != "") {
    $objLeave = new clsAnnualLeave($db);
    $tempInfo = $objLeave->arrHistory;
    $objLeave->generateEmployeeAnnualLeave($strIDEmployee);
    $arrCuti = $objLeave->getEmployeeLeaveInfo($strIDEmployee);
    $intJCI = getSetting("jci"); //hutang cuti maksimal
    $intHCM = ($arrCuti['curr']['quota'] == $intJCI) ? 0 : getSetting("hcm"); //hutang cuti maksimal
    $arrCuti["prev"]["add_taken"] = (isset($tempInfo[$strIDEmployee][$arrCuti["prev"]["year"]]["additional"])) ? $tempInfo[$strIDEmployee][$arrCuti["prev"]["year"]]["additional"] : 0;
    $arrCuti["curr"]["add_taken"] = (isset($tempInfo[$strIDEmployee][$arrCuti["curr"]["year"]]["additional"])) ? $tempInfo[$strIDEmployee][$arrCuti["curr"]["year"]]["additional"] : 0;
    $arrCuti["prev"]["add_quota"] = (isset($tempInfo[$strIDEmployee][$arrCuti["prev"]["year"]]["additional_quota"])) ? $tempInfo[$strIDEmployee][$arrCuti["prev"]["year"]]["additional_quota"] : 0;
    $arrCuti["curr"]["add_quota"] = (isset($tempInfo[$strIDEmployee][$arrCuti["curr"]["year"]]["additional"])) ? $tempInfo[$strIDEmployee][$arrCuti["curr"]["year"]]["additional_quota"] : 0;
    $strLeaveDetail = "&nbsp;<br><strong>Leave Detail</strong><br><table border=1><tr><th>Year</th><th>Quota</th><th>Add. Quota</th><th>Adv. Quota</th><th>Holiday</th><th>Taken</th><th>Add. Taken</th><th>Remaining</th><th>Add. Remaining</th></tr>";
    $strLeaveDetail .= "<tr><td>" . $arrCuti['prev']['year'] . "</td><td>" . $arrCuti['prev']['quota'] . "</td><td>" . $arrCuti['prev']['add_quota'] . "</td><td>$intHCM</td><td>" . $arrCuti['prev']['holiday'] . "</td><td>" . $arrCuti['prev']['taken'] . "</td><td>" . $arrCuti['prev']['add_taken'] . "</td><td>" . ($arrCuti['prev']['remain']) . "</td><td>" . ($arrCuti['prev']['add_quota'] - $arrCuti['prev']['add_taken']) . "</td></tr>";
    $strLeaveDetail .= "<tr><td><b>" . $arrCuti['curr']['year'] . "</b></td><td><b>" . $arrCuti['curr']['quota'] . "</b></td><td><b>" . $arrCuti['curr']['add_quota'] . "</b></td><td><b>$intHCM</b></td><td><b>" . $arrCuti['curr']['holiday'] . "</b></td><td><b>" . $arrCuti['curr']['taken'] . "</b></td><td><b>" . $arrCuti['curr']['add_taken'] . "</b></td><td><b>" . ($arrCuti['curr']['remain']) . "</b></td><td><b>" . ($arrCuti['curr']['add_quota'] - $arrCuti['curr']['add_taken']) . "</b></td></tr>";
  } else {
    $strLeaveDetail = "";
  }
  //----- TAMPILKAN DATA ---------
  //see common_function.php
  $strReadonly = (scopeGeneralDataEntry(
      $arrData['dataEmployee'],
      $_SESSION['sessionUserRole'],
      $arrUserInfo,
      $bolIsNew
  )) ? "readonly" : "";
  $strInputDate = "<input type=hidden size=15 maxlength=10 name=dataDate id=dataDate value=\"" . $arrData['dataDate'] . "\" >" . $arrData['dataDate'];
  $strInputDateFrom = "<input type=text size=15 maxlength=10 name=dataDateFrom id=dataDateFrom value=\"" . $arrData['dataDateFrom'] . "\">";
  $strInputDateThru = "<input type=text size=15 maxlength=10 name=dataDateThru id=dataDateThru value=\"" . $arrData['dataDateThru'] . "\" >";
  $strInputEmployee = "<input type=text name=dataEmployee id=dataEmployee size=10 maxlength=30 value=\"" . $arrData['dataEmployee'] . "\" style=\"width:$strDefaultWidthPx\" $strReadonly >";
  $strInputDuration = "<input type=text name=dataDuration id=dataDuration size=30 maxlength=10 value=\"" . $arrData['dataDuration'] . "\" style=\"width:$strDefaultWidthPx\" readonly class='numeric' >";
  $strInputNote = "<textarea name=dataNote cols=30 rows=3 wrap='virtual' style=\"width:$strDefaultWidthPx\">" . $arrData['dataNote'] . "</textarea>";
  $strSpecial = "";
  $strInputType = getAbsenceTypeList(
      $db,
      "dataType",
      $arrData['dataType'],
      "$strSpecial",
      "",
      " style=\"width:$strDefaultWidthPx\" onChange=\"onAbsenceTypeChange()\""
  );
  $strInputLeaveDuration = "<input type=text name=dataLeaveDuration id=dataLeaveDuration size=30 maxlength=10 value=\"" . $arrData['dataLeaveDuration'] . "\" style=\"width:$strDefaultWidthPx\" disabled class='numeric'>";
  $strInputLeaveDuration .= " &nbsp;<input type='checkbox' name='chkEditLeave' onClick=\"editLeaveDuration()\" title=\"Click here to edit leave duration\">";
  $strInputDoc = "<input name=\"detailDoc\" type=\"file\" id=\"detailDoc\" value=\"" . $arrData['dataDoc'] . "\"></td></tr>";
  $strInputStatus = $words[$ARRAY_REQUEST_STATUS[$arrData['dataStatus']]] . generateHidden(
          "dataStatus",
          $arrData['dataStatus']
      );
  if (isset($_POST['nik'])) {
    $strSQL = "SELECT * FROM hrd_employee WHERE employee_id = '$_POST[nik]'";
    $resDb = $db->execute($strSQL);
    $rowDb = $db->fetchrow($resDb);
    $strResult = "<tr><td><font color='red'>Name</font></td><td><font color='red'>:</font></td><td><font color='red'>" . $rowDb['employee_name'] . "</font><td></tr>\n";
    $strResult .= "<tr><td><font color='red'>&nbsp;</font></tr>\n";
    $strResult .= "<tr><td><font color='red'>Branch</font></td><td><font color='red'>:</font></td><td><font color='red'>" . getBranchName(
            $rowDb['branch_code']
        ) . "</font><td></tr>\n";
    $strResult .= "<tr><td><font color='red'>Branch Penugasan</font></td><td><font color='red'></font></td><td><font color='red'>" . getBranchName(
            $rowDb['branch_penugasan_code']
        ) . "</font><td></tr>\n";
    $strResult .= "<tr><td><font color='red'>Division</font></td><td><font color='red'>:</font></td><td><font color='red'>" . getDivisionName(
            $rowDb['division_code']
        ) . "</font><td></tr>\n";
    $strResult .= "<tr><td><font color='red'>Deparment</font></td><td><font color='red'>:</font></td><td><font color='red'>" . getDepartmentName(
            $rowDb['department_code']
        ) . "</font><td></tr>\n";
    $strResult .= "<tr><td><font color='red'>Section</font></td><td><font color='red'>:</font></td><td><font color='red'>" . getSectionName(
            $rowDb['section_code']
        ) . "</font><td></tr>\n";
    $strResult .= "<tr><td><font color='red'>Sub Section</font></td><td><font color='red'>:</font></td><td><font color='red'>" . getSubSectionName(
            $rowDb['sub_section_code']
        ) . "</font><td></tr>\n";
    $strResult .= "<tr><td><font color='red'>&nbsp;</font></tr>\n";
    $strResult .= "<tr><td><font color='red'>Status</font></td><td><font color='red'>:</font></td><td><font color='red'>" . printEmployeeStatus2(
            $rowDb['employee_status']
        ) . "</font><td></tr>\n";
    $strResult .= "<tr><td><font color='red'>Contract Due</font></td><td><font color='red'>:</font></td><td><font color='red'>" . pgDateFormat(
            $rowDb['due_date'],
            "d-M-y"
        ) . "</font><td></tr>\n";
    echo $strResult;
    die();
  }
}
$tbsPage = new clsTinyButStrong;
//write this variable in every page
$strPageTitle = getWords($dataPrivilege['menu_name']);
if (trim($dataPrivilege['icon_file']) == "") {
  $pageIcon = "../images/icons/blank.gif";
} else {
  $pageIcon = "../images/icons/" . $dataPrivilege['icon_file'];
}
if ($bolPrint) {
  $strMainTemplate = getTemplate("absence_edit_print.html");
} else {
  $strTemplateFile = getTemplate(str_replace(".php", ".html", basename($_SERVER['PHP_SELF'])));
}
//------------------------------------------------
//Load Master Template
$tbsPage->LoadTemplate($strMainTemplate);
$tbsPage->Show();
?>
