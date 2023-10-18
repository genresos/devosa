<?php
include_once('../global/session.php');
include_once('global.php');
include_once('../global/common_data.php');
include_once('../global/employee_function.php');
include_once('../global/common_function.php');
include_once('../includes/form2/form2.php');
include_once('../includes/datagrid2/datagrid.php');
include_once('../classes/hrd/hrd_absence_partial.php');
include_once('../classes/hrd/hrd_absence_detail.php');
include_once('overtime_func.php');
include_once('activity.php');
include_once('form_object.php');
include_once('attendance_functions.php');
include_once("../includes/krumo/class.krumo.php");
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
$boolExcell = false;
if (isset($_REQUEST['btnExportXLS'])) {
  $boolExcell = true;
}
$bolSync = isset($_REQUEST['btnSync']);
$bolAutoAlpha = isset($_REQUEST['btnSetAutoAlpa']);
$bolPrint = (isset($_REQUEST['btnPrint']) || isset($_REQUEST['btnExportXLS']) || isset($_REQUEST['btnExcel']));
//---- INISIALISASI ----------------------------------------------------
$strWordsATTENDANCEDATA = getWords("attendance data");
$strWordsEntryAttendance = getWords("entry attendance");
$strWordsImportAttendance = getWords("import attendance");
$strWordsAttendanceList = getWords("attendance list");
$strWordsAttendanceReport = getWords("attendance report");
$strWordsDateFrom = getWords("date from");
$strWordsDateThru = getWords("date thru");
$strWordsEmployeeID = getwords("n i k");
$strWordsCompany = getWords("company");
$strWordsShow = getWords("show");
$strWordsDivision = getWords("division");
$strWordsDepartment = getWords("department");
$strWordsSection = getWords("section");
$strWordsSubSection = getWords("subsection");
$strWordsEmployeeStatus = getWords("employee status");
$strWordsActive = getWords("active");
$strWordsOutdated = getWords("outdated");
$strWordsSalary = getWords("salary");
$strWordsActive = getWords("active");
$DataGrid = "";
$strDataDetail = "";
$strHidden = "";
$intTotalData = 0;
$strButtons = "";
$strButtonsTop = "";
$hiddenInput = "";
//----------------------------------------------------------------------
//--- DAFTAR FUNSI------------------------------------------------------
// fungsi untuk mengambil kriteria query
function getKriteria()
{
  global $dataPrivilege;
  global $f;
  global $strKriteriaCompany;
  global $arrCalendarLibur;
  $arrData = $f->getObjectValues();
  $strKriteria = "";
  // GENERATE CRITERIA
  if ($arrData['dataEmployee'] != "") {
    $strKriteria .= "AND employee_id = '" . $arrData['dataEmployee'] . "' ";
  } else {
    if (isset($_POST['dataEmployee']) && !empty($_POST['dataEmployee'])) {
      $strKriteria .= "AND employee_id = '" . $_POST['dataEmployee'] . "' ";
      $arrData['dataEmployee'] = $_POST['dataEmployee'];
    }
  }
  if ($arrData['dataPosition'] != "") {
    $strKriteria .= "AND t1.position_code = '" . $arrData['dataPosition'] . "' ";
  } else {
    if (isset($_POST['dataPosition']) && !empty($_POST['dataPosition'])) {
      $arrData['dataPosition'] = $_POST['dataPosition'];
      $strKriteria .= "AND t1.position_code = '" . $arrData['dataPosition'] . "' ";
    }
  }
  if ($arrData['dataBranch'] != "") {
    $strKriteria .= "AND branch_code = '" . $arrData['dataBranch'] . "' ";
  } else {
    if (isset($_POST['dataBranch']) && !empty($_POST['dataBranch'])) {
      $arrData['dataBranch'] = $_POST['dataBranch'];
      $strKriteria .= "AND branch_code = '" . $arrData['dataBranch'] . "' ";
    }
  }
  if ($arrData['dataGrade'] != "") {
    $strKriteria .= "AND grade_code = '" . $arrData['dataGrade'] . "' ";
  } else {
    if (isset($_POST['dataGrade']) && !empty($_POST['dataGrade'])) {
      $arrData['dataGrade'] = $_POST['dataGrade'];
      $strKriteria .= "AND grade_code = '" . $arrData['dataGrade'] . "' ";
    }
  }
  if ($arrData['dataEmployeeStatus'] != "") {
    $strKriteria .= "AND employee_status = '" . $arrData['dataEmployeeStatus'] . "' ";
  } else {
    if (isset($_POST['dataEmployeeStatus']) && !empty($_POST['dataEmployeeStatus'])) {
      $arrData['dataEmployeeStatus'] = $_POST['dataEmployeeStatus'];
      $strKriteria .= "AND employee_status = '" . $arrData['dataEmployeeStatus'] . "' ";
    }
  }
  if ($arrData['dataActive'] != "") {
    $strKriteria .= "AND active = '" . $arrData['dataActive'] . "' ";
  } else {
    if (isset($_POST['dataActive']) && !empty($_POST['dataActive'])) {
      $arrData['dataActive'] = $_POST['dataActive'];
      $strKriteria .= "AND active = '" . $arrData['dataActive'] . "' ";
    }
  }
  if ($arrData['dataDivision'] != "") {
    $strKriteria .= "AND division_code = '" . $arrData['dataDivision'] . "' ";
  } else {
    if (isset($_POST['dataDivision']) && !empty($_POST['dataDivision'])) {
      $arrData['dataDivision'] = $_POST['dataDivision'];
      $strKriteria .= "AND division_code = '" . $arrData['dataDivision'] . "' ";
    }
  }
  if ($arrData['dataDepartment'] != "") {
    $strKriteria .= "AND department_code = '" . $arrData['dataDepartment'] . "' ";
  } else {
    if (isset($_POST['dataDepartment']) && !empty($_POST['dataDepartment'])) {
      $arrData['dataDepartment'] = $_POST['dataDepartment'];
      $strKriteria .= "AND department_code = '" . $arrData['dataDepartment'] . "' ";
    }
  }
  if ($arrData['dataSection'] != "") {
    $strKriteria .= "AND section_code = '" . $arrData['dataSection'] . "' ";
  } else {
    if (isset($_POST['dataSection']) && !empty($_POST['dataSection'])) {
      $arrData['dataSection'] = $_POST['dataSection'];
      $strKriteria .= "AND section_code = '" . $arrData['dataSection'] . "' ";
    }
  }
  if ($arrData['dataSubSection'] != "") {
    $strKriteria .= "AND sub_section_code = '" . $arrData['dataSubSection'] . "' ";
  } else {
    if (isset($_POST['dataSubSection']) && !empty($_POST['dataSubSection'])) {
      $arrData['dataSubSection'] = $_POST['dataSubSection'];
      $strKriteria .= "AND sub_section_code = '" . $arrData['dataSubSection'] . "' ";
    }
  }
  if ($arrData['dataCrops'] != "") {
  } else {
    if (isset($_POST['dataCrops'])) {
      $arrData['dataCrops'] = $_POST['dataCrops'];
    }
  }
  $strCorps = $arrData['dataCrops'];
  $strKriteria .= $strKriteriaCompany;
  return $strKriteria;
}

// fungsi untuk menampilkan data
// fungsi untuk menampilkan data
function getData($db, $bolSync = false)
{
  //global $words;
  global $dataPrivilege;
  global $f;
  global $myDataGrid;
  global $DataGrid;
  global $strKriteriaCompany;
  global $arrCalendarLibur;
  global $hiddenInput;
  if ($db->connect()) {
    $arrData = $f->getObjectValues();
    $strKriteria = "";
    if (isset($_POST['dataDateFrom2']) && !empty($_POST['dataDateFrom2'])) {
      $strDateFrom = $_POST['dataDateFrom2'];
      $hiddenInput .= '<input type="hidden" name="dataDateThru" value="' . $_POST['dataDateFrom2'] . '">';
      $arrData['dataDateFrom'] = $_POST['dataDateFrom2'];
    } else {
      $strDateFrom = $arrData['dataDateFrom'];
      $hiddenInput .= '<input type="hidden" name="dataDateFrom2" value="' . $arrData['dataDateFrom'] . '">';
    }
    if (isset($_POST['dataDateThru2']) && !empty($_POST['dataDateThru2'])) {
      $strDateThru = $_POST['dataDateThru2'];
      $hiddenInput .= '<input type="hidden" name="dataDateThru" value="' . $_POST['dataDateThru2'] . '">';
      $arrData['dataDateThru'] = $_POST['dataDateFrom2'];
    } else {
      $strDateThru = $arrData['dataDateThru'];
      $hiddenInput .= '<input type="hidden" name="dataDateThru2" value="' . $arrData['dataDateThru'] . '">';
    }
    // GENERATE CRITERIA
    if ($arrData['dataEmployee'] != "") {
      $strKriteria .= "AND employee_id = '" . $arrData['dataEmployee'] . "' ";
      $hiddenInput .= '<input type="hidden" name="dataEmployee" value="' . $arrData['dataEmployee'] . '">';
    } else {
      if (isset($_POST['dataEmployee']) && !empty($_POST['dataEmployee'])) {
        $strKriteria .= "AND employee_id = '" . $_POST['dataEmployee'] . "' ";
        $hiddenInput .= '<input type="hidden" name="dataEmployee" value="' . $_POST['dataEmployee'] . '">';
        $arrData['dataEmployee'] = $_POST['dataEmployee'];
      }
    }
    $strIDEmployee = getIDEmployee($db, $arrData['dataEmployee']);
    if ($arrData['dataPosition'] != "") {
      $strKriteria .= "AND t1.position_code = '" . $arrData['dataPosition'] . "' ";
      $hiddenInput .= '<input type="hidden" name="dataPosition" value="' . $arrData['dataPosition'] . '">';
    } else {
      if (isset($_POST['dataPosition']) && !empty($_POST['dataPosition'])) {
        $arrData['dataPosition'] = $_POST['dataPosition'];
        $strKriteria .= "AND t1.position_code = '" . $arrData['dataPosition'] . "' ";
        $hiddenInput .= '<input type="hidden" name="dataPosition" value="' . $arrData['dataPosition'] . '">';
      }
    }
    if ($arrData['dataBranch'] != "") {
      $strKriteria .= "AND branch_code = '" . $arrData['dataBranch'] . "' ";
      $hiddenInput .= '<input type="hidden" name="dataBranch" value="' . $arrData['dataBranch'] . '">';
    } else {
      if (isset($_POST['dataBranch']) && !empty($_POST['dataBranch'])) {
        $arrData['dataBranch'] = $_POST['dataBranch'];
        $strKriteria .= "AND branch_code = '" . $arrData['dataBranch'] . "' ";
        $hiddenInput .= '<input type="hidden" name="dataBranch" value="' . $arrData['dataBranch'] . '">';
      }
    }
    if ($arrData['dataGrade'] != "") {
      $strKriteria .= "AND grade_code = '" . $arrData['dataGrade'] . "' ";
      $hiddenInput .= '<input type="hidden" name="dataGrade" value="' . $arrData['dataGrade'] . '">';
    } else {
      if (isset($_POST['dataGrade']) && !empty($_POST['dataGrade'])) {
        $arrData['dataGrade'] = $_POST['dataGrade'];
        $strKriteria .= "AND grade_code = '" . $arrData['dataGrade'] . "' ";
        $hiddenInput .= '<input type="hidden" name="dataBranch" value="' . $arrData['dataGrade'] . '">';
      }
    }
    if ($arrData['dataEmployeeStatus'] != "") {
      $strKriteria .= "AND employee_status = '" . $arrData['dataEmployeeStatus'] . "' ";
      $hiddenInput .= '<input type="hidden" name="dataEmployeeStatus" value="' . $arrData['dataEmployeeStatus'] . '">';
    } else {
      if (isset($_POST['dataEmployeeStatus']) && !empty($_POST['dataEmployeeStatus'])) {
        $arrData['dataEmployeeStatus'] = $_POST['dataEmployeeStatus'];
        $strKriteria .= "AND employee_status = '" . $arrData['dataEmployeeStatus'] . "' ";
        $hiddenInput .= '<input type="hidden" name="dataEmployeeStatus" value="' . $arrData['dataEmployeeStatus'] . '">';
      }
    }
    if ($arrData['dataActive'] != "") {
      $strKriteria .= "AND active = '" . $arrData['dataActive'] . "' ";
      $hiddenInput .= '<input type="hidden" name="dataActive" value="' . $arrData['dataActive'] . '">';
    } else {
      if (isset($_POST['dataActive']) && !empty($_POST['dataActive'])) {
        $arrData['dataActive'] = $_POST['dataActive'];
        $strKriteria .= "AND active = '" . $arrData['dataActive'] . "' ";
        $hiddenInput .= '<input type="hidden" name="dataActive" value="' . $arrData['dataActive'] . '">';
      }
    }
    if ($arrData['dataDivision'] != "") {
      $strKriteria .= "AND division_code = '" . $arrData['dataDivision'] . "' ";
      $hiddenInput .= '<input type="hidden" name="dataDivision" value="' . $arrData['dataDivision'] . '">';
    } else {
      if (isset($_POST['dataDivision']) && !empty($_POST['dataDivision'])) {
        $arrData['dataDivision'] = $_POST['dataDivision'];
        $strKriteria .= "AND division_code = '" . $arrData['dataDivision'] . "' ";
        $hiddenInput .= '<input type="hidden" name="dataDivision" value="' . $arrData['dataDivision'] . '">';
      }
    }
    if ($arrData['dataDepartment'] != "") {
      $strKriteria .= "AND department_code = '" . $arrData['dataDepartment'] . "' ";
      $hiddenInput .= '<input type="hidden" name="dataDepartment" value="' . $arrData['dataDepartment'] . '">';
    } else {
      if (isset($_POST['dataDepartment']) && !empty($_POST['dataDepartment'])) {
        $arrData['dataDepartment'] = $_POST['dataDepartment'];
        $strKriteria .= "AND department_code = '" . $arrData['dataDepartment'] . "' ";
        $hiddenInput .= '<input type="hidden" name="dataDepartment" value="' . $arrData['dataDepartment'] . '">';
      }
    }
    if ($arrData['dataSection'] != "") {
      $strKriteria .= "AND section_code = '" . $arrData['dataSection'] . "' ";
      $hiddenInput .= '<input type="hidden" name="dataSection" value="' . $arrData['dataSection'] . '">';
    } else {
      if (isset($_POST['dataSection']) && !empty($_POST['dataSection'])) {
        $arrData['dataSection'] = $_POST['dataSection'];
        $strKriteria .= "AND section_code = '" . $arrData['dataSection'] . "' ";
        $hiddenInput .= '<input type="hidden" name="dataSection" value="' . $arrData['dataSection'] . '">';
      }
    }
    if ($arrData['dataSubSection'] != "") {
      $strKriteria .= "AND sub_section_code = '" . $arrData['dataSubSection'] . "' ";
      $hiddenInput .= '<input type="hidden" name="dataSubSection" value="' . $arrData['dataSubSection'] . '">';
    } else {
      if (isset($_POST['dataSubSection']) && !empty($_POST['dataSubSection'])) {
        $arrData['dataSubSection'] = $_POST['dataSubSection'];
        $strKriteria .= "AND sub_section_code = '" . $arrData['dataSubSection'] . "' ";
        $hiddenInput .= '<input type="hidden" name="dataSubSection" value="' . $arrData['dataSubSection'] . '">';
      }
    }
    if ($arrData['dataCrops'] != "") {
      $hiddenInput .= '<input type="hidden" name="dataCrops" value="' . $arrData['dataCrops'] . '">';
    } else {
      if (isset($_POST['dataCrops'])) {
        $arrData['dataCrops'] = $_POST['dataCrops'];
        $hiddenInput .= '<input type="hidden" name="dataCrops" value="' . $arrData['dataCrops'] . '">';
      }
    }
    $strCorps = $arrData['dataCrops'];
    $strKriteria .= $strKriteriaCompany;
    $strKriteriaBackup = $strKriteria;
    if ($bolSync) {
      syncShiftAttendance($db, $strDateFrom, $strDateThru, $strKriteria);
      syncOvertimeApplication($db, $strDateFrom, $strDateThru, "", $strKriteria);
      syncLateEarly($db, $strDateFrom, $strDateThru, "", $strKriteria);
      syncIsAbsence($db, $strDateFrom, $strDateThru, "", $strKriteria);
    }
    //get approved late or early
    $tblAbsencePartial = new cHrdAbsencePartial();
    $strCriteria = "partial_absence_date BETWEEN '$strDateFrom' AND '$strDateThru' AND status >= " . REQUEST_STATUS_APPROVED_2 . " ";
    if ($arrData['dataEmployee'] != "") {
      $strCriteria .= "AND id_employee = '" . getIDEmployee($db, $arrData['dataEmployee']) . "' ";
    }
    $dataAbsencePartial = $tblAbsencePartial->findAll($strCriteria, "", "", null, 1, "id");
    foreach ($dataAbsencePartial as $strID => $detailAbsencePartial) {
      $arrAbsencePartial[$detailAbsencePartial['partial_absence_date']][$detailAbsencePartial['id_employee']][$detailAbsencePartial['partial_absence_type']] = $detailAbsencePartial;
    }
    //get absence which cancels late/early
    $strSQL = "SELECT t1.*, cancel_partial_absence, status FROM hrd_absence_detail AS t1 ";
    $strSQL .= "LEFT JOIN hrd_absence_type AS t2 ON t1.absence_type = t2.code ";
    $strSQL .= "LEFT JOIN hrd_absence AS t3 ON t1.id_absence = t3.id ";
    $strSQL .= "WHERE absence_date BETWEEN '$strDateFrom' AND '$strDateThru' AND cancel_partial_absence = TRUE AND status >= " . REQUEST_STATUS_APPROVED . " ";
    $resDb = $db->execute($strSQL);
    while ($rowDb = $db->fetchrow($resDb)) {
      $arrCancelLate[$rowDb['absence_date']][$rowDb['id_employee']] = true;
    }
    $intRows = 0;
    $strResult = "";
    $dataset = [];
    $objAttendanceClass = new clsAttendanceClass($db);
    $objAttendanceClass->resetAttendance();
    $objAttendanceClass->setFilter($strDateFrom, $strDateThru, $strIDEmployee, $strKriteria);
    $objAttendanceClass->getAttendanceResource();
    $objToday = new clsAttendanceInfo($db);
    $intLate = 0;
    $intEarly = 0;
    $intTotalLate = 0;
    $intTotalEarly = 0;
    $intApprovedLate = 0;
    $intApprovedEarly = 0;
    $intTotalApprovedLate = 0;
    $intTotalApprovedEarly = 0;
    $intTotalOT = 0;
    $intTotalCalculatedOT = 0;
    $strCurrDate = $strDateFrom;
    while (dateCompare($strCurrDate, $strDateThru) <= 0) {
      $arrAttendance = (isset($objAttendanceClass->arrAttendance[$strCurrDate])) ? $objAttendanceClass->arrAttendance[$strCurrDate] : [];
      foreach ($objAttendanceClass->arrEmployee as $strIDEmployee => $arrEmployee) {
        $objToday->newInfo($strIDEmployee, $strCurrDate);
        $objToday->initAttendanceInfo($objAttendanceClass);
        if (isset($arrCancelLate[$strCurrDate][$strIDEmployee])) {
          $intLate = "";
          $intEarly = "";
          $intApprovedLate = "";
          $intApprovedEarly = "";
        } else {
          if (isset($arrAbsencePartial[$strCurrDate][$strIDEmployee])) {
            if (isset($arrAbsencePartial[$strCurrDate][$strIDEmployee][PARTIAL_ABSENCE_LATE]) && is_numeric(
                    $arrAbsencePartial[$strCurrDate][$strIDEmployee][PARTIAL_ABSENCE_LATE]['approved_duration']
                )
            ) {
              $intLate = $objToday->intLate - $arrAbsencePartial[$strCurrDate][$strIDEmployee][PARTIAL_ABSENCE_LATE]['approved_duration'];
              $intApprovedLate = $arrAbsencePartial[$strCurrDate][$strIDEmployee][PARTIAL_ABSENCE_LATE]['approved_duration'];
              $intLate = ($intLate < 0) ? 0 : "";
            } else {
              $intLate = ($objToday->intLate == 0) ? "" : $objToday->intLate;
              $intApprovedLate = "";
            }
            if (isset($arrAbsencePartial[$strCurrDate][$strIDEmployee][PARTIAL_ABSENCE_EARLY]) && is_numeric(
                    $arrAbsencePartial[$strCurrDate][$strIDEmployee][PARTIAL_ABSENCE_EARLY]['approved_duration']
                )
            ) {
              $intEarly = $objToday->intEarly - $arrAbsencePartial[$strCurrDate][$strIDEmployee][PARTIAL_ABSENCE_EARLY]['approved_duration'];
              $intApprovedEarly = $arrAbsencePartial[$strCurrDate][$strIDEmployee][PARTIAL_ABSENCE_EARLY]['approved_duration'];
              $intEarly = ($intEarly < 0) ? 0 : "";
            } else {
              $intEarly = ($objToday->intEarly == 0) ? "" : $objToday->intEarly;
              $intApprovedEarly = "";
            }
          } else {
            $intLate = ($objToday->intLate == 0) ? "" : $objToday->intLate;
            $intEarly = ($objToday->intEarly == 0) ? "" : $objToday->intEarly;
            $intApprovedLate = "";
            $intApprovedEarly = "";
          }
        }
        if ($strCorps == "Time In") {
          $strCorps = 1;
        } else if ($strCorps == "Time Out") {
          $strCorps = 2;
        } else if ($strCorps == "Time In & Time Out") {
          $strCorps = 3;
        } else if ($strCorps == "Less Duration (Late)") {
          $strCorps = 4;
        } else if ($strCorps == "Less Duration (Early)") {
          $strCorps = 5;
        } else if ($strCorps == "Time In = Time Out") {
          $strCorps = 6;
        }
        $AttStartHour = substr($objToday->strAttendanceStart, 0, 2) * 1 * 60;
        $AttStartMin = substr($objToday->strAttendanceStart, 3, 2) * 1;
        $AttStart = $AttStartHour + $AttStartMin;
        $AttFinishHour = substr($objToday->strAttendanceFinish, 0, 2) * 1 * 60;
        $AttFinishMin = substr($objToday->strAttendanceFinish, 3, 2) * 1;
        $AttFinish = $AttFinishHour + $AttFinishMin;
        $selisihAtt = $AttFinish - $AttStart;
        $strTotalDuration = ($objToday->intTotalDuration < 480) ? $objToday->intTotalDuration : "";
        if ($arrData['dataShift'] != "") {
          if ($objToday->strShiftCode != $arrData['dataShift']) {
            continue;
          }
        }
        if ((($selisihAtt >= 2 or $selisihAtt <= -2) or ($objToday->strAttendanceFinish == "" or $objToday->strAttendanceStart == "")) and $strCorps == 6) {
          continue;
        }
        if (($objToday->strAttendanceStart != "" or $objToday->strAttendanceFinish == "") and $strCorps == 1) {
          continue;
        }
        if (($objToday->strAttendanceFinish != "" or $objToday->strAttendanceStart == "") and $strCorps == 2) {
          continue;
        }
        if ($arrEmployee['resign_date'] != "") {
          $resign = $arrEmployee['resign_date'];
        } else {
          $resign = "9999-01-01";
        }
        if ($strCorps == 3) {
          if ($strCurrDate < $arrEmployee['join_date'] or $strCurrDate >= $resign) {
            continue;
          } else if (($objToday->strAttendanceFinish != "" or $objToday->strAttendanceStart != "")) {
            continue;
          }
        }
        if ($strCorps == 4) {
          if ($intLate == 0) {
            continue;
          }
        }
        if ($strCorps == 5) {
          if ($intEarly == 0) {
            continue;
          }
        }
        $dataset[] = [
            "attendance_date"       => $strCurrDate,
            "id_employee"           => $strIDEmployee,
            "employee_id"           => $arrEmployee['employee_id'],
            "employee_id_2"         => $arrEmployee['employee_id_2'],
            "employee_name"         => $arrEmployee['employee_name'],
            "division_code"         => $arrEmployee['division_code'],
            "division_name"         => $arrEmployee['division_name'],
            "department_code"       => $arrEmployee['department_code'],
            "department_name"       => $arrEmployee['department_name'],
            "section_code"          => $arrEmployee['section_code'],
            "section_name"          => $arrEmployee['section_name'],
            "absence_code"          => $objToday->strAbsenceCode,
            "shift_code"            => $objToday->strShiftCode,
            "attendance_start"      => $objToday->strAttendanceStart,
            "attendance_finish"     => $objToday->strAttendanceFinish,
            "normal_start"          => $objToday->strNormalStart,
            "normal_finish"         => $objToday->strNormalFinish,
            "late"                  => $intLate,
            "early"                 => $intEarly,
            "approved_late"         => $intApprovedLate,
            "approved_early"        => $intApprovedEarly,
            "overtime_start_early"  => $objToday->strOvertimeStartEarly,
            "overtime_finish_early" => $objToday->strOvertimeFinishEarly,
            "overtime_start"        => $objToday->strOvertimeStart,
            "overtime_finish"       => $objToday->strOvertimeFinish,
            "normal_finish"         => $objToday->strNormalFinish,
            "ot"                    => $objToday->fltTotalOT,
            "calculated_ot"         => $objToday->totOTCalculated,
            "data_source"           => $objToday->strDataSource
        ];
        $intTotalLate += ((is_numeric($intLate)) ? $intLate : 0);
        $intTotalEarly += ((is_numeric($intEarly)) ? $intEarly : 0);
        $intTotalApprovedLate += ((is_numeric($intApprovedLate)) ? $intApprovedLate : 0);
        $intTotalApprovedEarly += ((is_numeric($intApprovedEarly)) ? $intApprovedEarly : 0);
        $intTotalOT += ((is_numeric($objToday->fltTotalOT)) ? $objToday->fltTotalOT : 0);
        $intTotalCalculatedOT += ((is_numeric($objToday->totOTCalculated)) ? $objToday->totOTCalculated : 0);
      }//end foreach
      $strCurrDate = getNextDate($strCurrDate);
    }
    foreach ($dataset[0] as $key => $value) {
      $tempDataset[$key] = "";
    }
    if (count($dataset) != 0) {
      $tempDataset = [];
      foreach ($dataset[0] as $key => $value) {
        switch ($key) {
          case "shift" :
            $tempValue = strtoupper(getWords("total"));
            break;
          case "late" :
            $tempValue = $intTotalLate;
            break;
          case "early" :
            $tempValue = $intTotalEarly;
            break;
          case "approved_late" :
            $tempValue = $intTotalApprovedLate;
            break;
          case "approved_early" :
            $tempValue = $intTotalApprovedEarly;
            break;
          case "ot" :
            $tempValue = $intTotalOT;
            break;
          case "calculated_ot" :
            $tempValue = $intTotalCalculatedOT;
            break;
          default:
            $tempValue = "";
        }
        $tempDataset[$key] = $tempValue;
      }
      $dataset[] = $tempDataset;
    }
    //tambahkan baris kosong dan total countable minute
    $tblData = new cModel();
    $strSQL = "SELECT holiday,note FROM hrd_calendar WHERE holiday >='" . $strDateFrom . "' AND holiday <='" . $strDateThru . "'";
    foreach ($tblData->query($strSQL) as $arrTemp) {
      $arrCalendarLibur[] = ["holiday" => $arrTemp['holiday'], "note" => $arrTemp['note']];
    }
    $myDataGrid = new cDataGrid("formData", "DataGrid1", "100%", "100%", true, true, true);
    $myDataGrid->caption = getWords(
        strtoupper(vsprintf(getWords("list of %s"), getWords($dataPrivilege['menu_name'])))
    );
    $myDataGrid->setCriteria($strKriteria);
    $myDataGrid->pageSortBy = "shift";
    $myDataGrid->addColumnNumbering(new DataGrid_Column("", "", ['rowspan' => '2', 'width' => '30'], ['nowrap' => '']));
    $myDataGrid->addColumn(
        new DataGrid_Column(
            getWords("date"),
            "attendance_date",
            ['rowspan' => '2'],
            ['nowrap' => ''],
            true,
            false,
            "",
            "printNDate()"
        )
    );
    $myDataGrid->addColumn(
        new DataGrid_Column(
            getWords("day"),
            "attendance_date",
            ['rowspan' => '2'],
            ['nowrap' => ''],
            true,
            false,
            "",
            "printWDay()"
        )
    );
    $myDataGrid->addColumn(new DataGrid_Column(getwords("n i k"), "employee_id", ['rowspan' => '2'], ['nowrap' => '']));
    $myDataGrid->addColumn(
        new DataGrid_Column(getwords("n i k corporate"), "employee_id_2", ['rowspan' => '2'], ['nowrap' => ''])
    );
    $myDataGrid->addColumn(
        new DataGrid_Column(
            getWords("employee name"),
            "employee_name",
            ['rowspan' => '2', 'width' => '80'],
            ['nowrap' => '']
        )
    );
    $myDataGrid->addColumn(
        new DataGrid_Column(getWords("division"), "division_name", ['rowspan' => '2'], ['nowrap' => ''])
    );
    $myDataGrid->addColumn(
        new DataGrid_Column(getWords("department"), "department_name", ['rowspan' => '2'], ['nowrap' => ''])
    );
    $myDataGrid->addColumn(
        new DataGrid_Column(getWords("section"), "section_name", ['rowspan' => '2'], ['nowrap' => ''])
    );
    $myDataGrid->addColumn(
        new DataGrid_Column(
            getWords("absence"),
            "absence_code",
            ['rowspan' => '2'],
            ['style' => 'color:red;font-size:11;font-weight:bold', 'nowrap' => '']
        )
    );
    $myDataGrid->addColumn(new DataGrid_Column(getWords("shift"), "shift_code", ['rowspan' => '2'], ['nowrap' => '']));
    $myDataGrid->addSpannedColumn(getWords("attendance"), 2);
    $myDataGrid->addColumn(
        new DataGrid_Column(
            getWords("start"),
            "attendance_start",
            "",
            ["nowrap" => "nowrap"],
            true,
            true,
            "",
            "",
            "string",
            true,
            10,
            true
        )
    );
    $myDataGrid->addColumn(
        new DataGrid_Column(
            getWords("finish"),
            "attendance_finish",
            "",
            ["nowrap" => "nowrap"],
            true,
            true,
            "",
            "",
            "string",
            true,
            10,
            true
        )
    );
    $myDataGrid->addSpannedColumn(getWords("normal"), 2);
    $myDataGrid->addColumn(
        new DataGrid_Column(
            getWords("start"),
            "normal_start",
            "",
            ['nowrap' => ''],
            true,
            true,
            "",
            "",
            "string",
            true,
            10,
            true
        )
    );
    $myDataGrid->addColumn(
        new DataGrid_Column(
            getWords("finish"),
            "normal_finish",
            "",
            ['nowrap' => ''],
            true,
            true,
            "",
            "",
            "string",
            true,
            10,
            true
        )
    );
    $myDataGrid->addColumn(
        new DataGrid_Column(
            getWords("late"),
            "late",
            ['rowspan' => '2'],
            ['style' => 'color:red;font-size:11;font-weight:bold', 'nowrap' => ''],
            true,
            true,
            "",
            "formatTime()"
        )
    );
    $myDataGrid->addColumn(
        new DataGrid_Column(
            getWords("app. late"),
            "approved_late",
            ['rowspan' => '2', 'width' => '30'],
            ['nowrap' => ''],
            true,
            false,
            "",
            "formatTime()"
        )
    );
    $myDataGrid->addColumn(
        new DataGrid_Column(
            getWords("early"),
            "early",
            ['rowspan' => '2'],
            ['style' => 'color:red;font-size:11;font-weight:bold', 'nowrap' => ''],
            true,
            true,
            "",
            "formatTime()"
        )
    );
    $myDataGrid->addColumn(
        new DataGrid_Column(
            getWords("app. early"),
            "approved_early",
            ['rowspan' => '2', 'width' => '30'],
            ['nowrap' => ''],
            true,
            false,
            "",
            "formatTime()"
        )
    );
    $myDataGrid->addSpannedColumn(getWords("early overtime"), 2);
    $myDataGrid->addColumn(new DataGrid_Column(getWords("start"), "overtime_start_early", "", ['nowrap' => '']));
    $myDataGrid->addColumn(new DataGrid_Column(getWords("finish"), "overtime_finish_early", "", ['nowrap' => '']));
    $myDataGrid->addSpannedColumn(getWords("afternoon overtime"), 2);
    $myDataGrid->addColumn(new DataGrid_Column(getWords("start"), "overtime_start", "", ['nowrap' => '']));
    $myDataGrid->addColumn(new DataGrid_Column(getWords("finish"), "overtime_finish", "", ['nowrap' => '']));
    $myDataGrid->addColumn(
        new DataGrid_Column(
            getWords("total ot"),
            "ot",
            ['rowspan' => '2'],
            ['nowrap' => ''],
            true,
            false,
            "",
            "formatTime()"
        )
    );
    $myDataGrid->addColumn(
        new DataGrid_Column(getWords("source"), "data_source", ['rowspan' => '2'], ['nowrap' => ''])
    );
    $myDataGrid->addButtonExportExcel(
        getWords("export excel"),
        str_replace(" ", "_", $dataPrivilege['menu_name'] . ".xls"),
        getWords($dataPrivilege['menu_name'])
    );
    $myDataGrid->setPageLimit("all");
    $myDataGrid->getRequest();
    $myDataGrid->hasGrandTotal = true;
    $intTotalData = $myDataGrid->totalData = count($dataset);
    // PROCESS TO FILTER THE dataset based on the AJAX VALUE.
    // Special case for attendance list since the dataset is generated not by DataGrid class
    $datasetFiltered = filterBasedDataGrid($dataset);
    $intTotalData = $myDataGrid->totalData = count($datasetFiltered);
    //bind Datagrid with array dataset
    $myDataGrid->bind($datasetFiltered);
    return $myDataGrid->render();
  }
} // getData
function filterBasedDataGrid($dataset)
{
  $newDataSet = [];
  $criteria = "";
  $searchBy = "";
  $notNull = false;
  $found = false;
  if (isset($_REQUEST['datagridajax'])) {
    if (isset($_REQUEST['pageSearchCriteriaDataGrid1']) && $_REQUEST['pageSearchCriteriaDataGrid1'] != "") {
      if (isset($_REQUEST['pageSearchByDataGrid1'])) {
        $found = true;
        $criteria = $_REQUEST['pageSearchCriteriaDataGrid1'];
        $searchBy = $_REQUEST['pageSearchByDataGrid1'];
        if (strtolower($criteria) === "not empty") {
          $notNull = true;
        }
      }
    }
  }
  if (isset($_REQUEST['pageSortByDataGrid1'])) {
    $found = true;
    $criteria = $_REQUEST['pageSearchCriteriaDataGrid1'];
    $searchBy = $_REQUEST['pageSearchByDataGrid1'];
    $sortBy = $_REQUEST['pageSortByDataGrid1'];
    if (strtolower($criteria) === "not empty") {
      $notNull = true;
    }
  }
  if (!$found) {
    return $dataset;
  }
  foreach ($dataset as $idx => $data) {
    foreach ($data as $key => $value) {
      if ($key == $searchBy) {
        if ($notNull) {
          if (strlen($value) >= 1) {
            $newDataSet[] = $data;
          }
        } else if (strpos(strtolower($value), strtolower($criteria)) !== false) {
          $newDataSet[] = $data;
        }
      }
    }
  }
  if ($sortBy != "") {
    $arrSort = explode(" ", $sortBy);
    $sortBy = $arrSort[0];
    if (count($newDataSet) == 0) {
      $newDataSet = $dataset;
    }
    $sortArray = [];
    foreach ($newDataSet as $person) {
      foreach ($person as $key => $value) {
        if (!isset($sortArray[$key])) {
          $sortArray[$key] = [];
        }
        $sortArray[$key][] = $value;
      }
    }
    $orderby = $sortBy; //change this to whatever key you want from the array
    if (@$arrSort[1] == "DESC") {
      array_multisort($sortArray[$orderby], SORT_DESC, $newDataSet);
    } else {
      array_multisort($sortArray[$orderby], SORT_ASC, $newDataSet);
    }
  }
  return $newDataSet;
}

function printWDay($params)
{
  global $bolPrint;
  extract($params);
  $strDay = getNamaHariSingkat(getWDay($value));
  return (($strDay == "Sat" || $strDay == "Sun") && !$bolPrint) ? "<strong><font color=red size=-1>$strDay</font></strong>" : $strDay;
}

function syncIsAbsence($db, $strDateFrom, $strDateThru, $strIDEmployee = "", $strKriteria = "")
{
  // cari info Attendance
  $arrAttendance = [];
  $strSQL = "SELECT id_employee, attendance_date FROM hrd_attendance AS t1
                 LEFT JOIN hrd_employee AS t3 ON t1.id_employee = t3.id
                 WHERE (attendance_start is not null OR attendance_finish is not null)
                 AND attendance_date BETWEEN '$strDateFrom' AND '$strDateThru'
                 AND attendance_date NOT IN
                 (
                 SELECT absence_date FROM hrd_absence_detail AS t1
                 LEFT JOIN hrd_employee AS t2 ON t1.id_employee = t2.id
                 WHERE absence_date BETWEEN '$strDateFrom' AND '$strDateThru' $strKriteria
                 )
                 AND is_absence = TRUE
                 $strKriteria";
  if ($strIDEmployee != "") {
    $strSQL .= " AND id_employee ='$strIDEmployee' ";
  }
  $resS = $db->execute($strSQL);
  while ($rowS = $db->fetchrow($resS)) {
    $strSQL = "UPDATE hrd_attendance SET is_absence = FALSE
                   WHERE attendance_date = '" . $rowS['attendance_date'] . "'
                   AND id_employee ='" . $rowS['id_employee'] . "' ";
    $res = $db->execute($strSQL);
  }
}//syncIsAbsence
function getDataListCrops($default = null, $isHasEmpty = false, $emptyData = null)
{
  $arrData = [];
  if ($isHasEmpty) {
    if ($emptyData == null) {
      $emptyData = ["value" => "", "text" => ""];
    }
    $arrData[] = $emptyData;
  }
  $arrListCrops = [
      "Time In",
      "Time Out",
      "Time In & Time Out",
      "Less Duration (Late)",
      "Less Duration (Early)",
      "Time In = Time Out"
  ];
  foreach ($arrListCrops as $value) {
    if ($value == $default) {
      $arrData[] = ["value" => $value, "text" => $value, "selected" => true];
    } else {
      $arrData[] = ["value" => $value, "text" => $value, "selected" => false];
    }
  }
  return $arrData;
}

function printNDate($params)
{
  global $arrCalendarLibur;
  global $boolExcell;
  extract($params);
  foreach ($arrCalendarLibur as $key => $value) {
    if ($record['attendance_date'] == $value['holiday'] && !$boolExcell) {
      //return "<strong><font color=red size=-3>".$record['attendance_date']."<br>". $value['note']."</font></strong>";
      return "<strong><font color=red size=-3>" . $record['attendance_date'] . "<br>" . $value['note'] . "</font></strong>";
      //return $record['attendance_date']."<br>". $value['note'];
    }
  }
  return $record['attendance_date'];
}

function setAutoAlpha($db, $strDateFrom, $strDateThru, $strKriteria)
{
  $strCurDate = $strDateFrom;
  while (dateCompare($strCurDate, $strDateThru) <= 0) {
    $strSQL = "SELECT t1.id, t2.shift_code, t1.position_code, t3.is_all_day, t3.is_sat_in FROM hrd_employee AS t1
                     LEFT JOIN (SELECT id_employee,shift_code FROM hrd_shift_schedule_employee WHERE shift_date = '$strCurDate') AS t2 ON t1.id = t2.id_employee
                     LEFT JOIN (SELECT position_code, is_all_day, is_sat_in FROM hrd_position) AS t3 ON t3.position_code = t1.position_code
                     WHERE active=1 AND join_date <= '$strCurDate' AND (resign_date is null or resign_date >= '$strCurDate')
                     AND (is_immune_auto_alpha = 0 OR is_immune_auto_alpha IS NULL)
                     AND id NOT IN (SELECT id_employee FROM hrd_attendance WHERE attendance_date = '$strCurDate')
                     AND id NOT IN (SELECT a.id_employee FROM hrd_absence_detail a LEFT JOIN hrd_absence b ON a.id_absence = b.id
                     WHERE a.absence_date = '$strCurDate' AND b.status != -1)
                     AND id NOT IN (SELECT c.id_employee FROM hrd_shift_schedule_employee AS c LEFT JOIN hrd_shift_type AS d ON c.shift_code = d.code
                     WHERE c.shift_date = '$strCurDate' AND d.shift_off = TRUE AND d.is_shift = FALSE) $strKriteria";
    $resDb = $db->execute($strSQL);
    while ($rowDb = $db->fetchrow($resDb)) {
      $intLastID = 0;
      $strIDEmployee = $rowDb['id'];
      $bolHoliday = true;
      if ($rowDb['shift_code'] == "" OR $rowDb['shift_code'] == "OFF") {
        $bolHoliday = isHoliday($strCurDate, true, $rowDb['is_all_day'], $rowDb['is_sat_in']);
      } else {
        $bolHoliday = false;
      }
      if ($bolHoliday == false) {
        $strSQL2 = "INSERT INTO hrd_absence (id_employee, date_from, date_thru, absence_type_code, note, status) VALUES
                             ($strIDEmployee, '$strCurDate', '$strCurDate', 'A', 'alpha generated by system', 0)";
        $resDb2 = $db->execute($strSQL2);
        $strSQL3 = "SELECT max(id) as last_id FROM hrd_absence WHERE id_employee = " . $rowDb['id'] . " AND note = 'alpha generated by system' ";
        $resDb3 = $db->execute($strSQL3);
        while ($rowDb3 = $db->fetchrow($resDb3)) {
          $intLastID = $rowDb3['last_id'];
        }
        $strSQL4 = "INSERT INTO hrd_absence_detail (id_absence, id_employee, absence_date, absence_type) VALUES
                              ($intLastID, $strIDEmployee, '$strCurDate', 'A')";
        $resDb4 = $db->execute($strSQL4);
      }
    }
    $strCurDate = getNextDate($strCurDate);
  }
}

//----------------------------------------------------------------------
//----MAIN PROGRAM -----------------------------------------------------
$strInfo = "";
$intDefaultStart = "08:00";
$intDefaultFinish = "17:00";
$db = new CdbClass;
if ($db->connect()) {
  getUserEmployeeInfo();
  $arrUserList = getAllUserInfo($db);
  $dtFrom = date("Y-m-") . "25";
  $dtFrom = getNextDateNextMonth($dtFrom, -1);
  $dtThru = date("Y-m-") . "24";
  $strDataID = getPostValue('dataID');
  scopeData(
      $strDataEmployee,
      $strDataSubSection,
      $strDataSection,
      $strDataDepartment,
      $strDataDivision,
      $_SESSION['sessionUserRole'],
      $arrUserInfo,
      $strDataBranch
  );
  //generate form untuk select trip type
  //trip type harus dipilih dulu supaya jenis2 trip allowance dapat ditentukan
  $f = new clsForm("formFilter", 3, "100%", "");
  $f->caption = strtoupper($strWordsFILTERDATA);
  $f->addInput(
      getWords("date from"),
      "dataDateFrom",
      ($strDateFrom = getInitialValue("DateFrom", $dtFrom, $dtFrom)),
      ["style" => "width:$strDateWidth"],
      "date",
      false,
      true,
      true
  );
  $f->addInput(
      getWords("date thru"),
      "dataDateThru",
      ($strDateThru = getInitialValue("DateFrom", $dtThru, $dtThru)),
      ["style" => "width:$strDateWidth"],
      "date",
      false,
      true,
      true
  );
  $f->addInputAutoComplete(
      getWords("employee"),
      "dataEmployee",
      getDataEmployee(getInitialValue("Employee", null, $strDataEmployee)),
      "style=width:$strDefaultWidthPx " . $strEmpReadonly,
      "string",
      false
  );
  $f->addLabelAutoComplete("", "dataEmployee", "");
  $f->addSelect(
      getWords("crops"),
      "dataCrops",
      getDataListCrops(getInitialValue("crop"), true),
      ["style" => "width:$strDefaultWidthPx"],
      "",
      false
  );
  $f->addLiteral("", "", "");
  $f->addSelect(
      getWords("branch"),
      "dataBranch",
      getDataListBranch(getInitialValue("Branch", "", $strDataBranch), true),
      ["style" => "width:$strDefaultWidthPx"],
      "",
      false,
      ($ARRAY_DISABLE_GROUP['branch'] == "")
  );
  $f->addSelect(
      getWords("employee category"),
      "dataPosition",
      getDataListPosition(getInitialValue("Position"), true),
      ["style" => "width:$strDefaultWidthPx"],
      "",
      false
  );
  $f->addSelect(
      getWords("grade"),
      "dataGrade",
      getDataListSalaryGrade(getInitialValue("Grade"), true),
      ["style" => "width:$strDefaultWidthPx"],
      "",
      false
  );
  $f->addSelect(
      getWords("status"),
      "dataEmployeeStatus",
      getDataListEmployeeStatus(
          getInitialValue("EmployeeStatus"),
          true,
          ["value" => "", "text" => "", "selected" => true]
      ),
      ["style" => "width:$strDefaultWidthPx"],
      "",
      false
  );
  $f->addSelect(
      getWords("active"),
      "dataActive",
      getDataListEmployeeActive(
          getInitialValue("Active"),
          true,
          ["value" => "", "text" => "", "selected" => true]
      ),
      ["style" => "width:$strDefaultWidthPx"],
      "",
      false
  );
  $f->addLiteral("", "", "");
  $f->addSelect(
      getWords("company"),
      "dataCompany",
      getDataListCompany($strDataCompany, $bolCompanyEmptyOption, $arrCompanyEmptyData, $strKriteria2),
      ["style" => "width:$strDefaultWidthPx"],
      "",
      false
  );
  $f->addSelect(
      getWords("division"),
      "dataDivision",
      getDataListDivision(getInitialValue("Division", "", $strDataDivision), true),
      ["style" => "width:$strDefaultWidthPx"],
      "",
      false,
      ($ARRAY_DISABLE_GROUP['division'] == "")
  );
  $f->addSelect(
      getWords("department "),
      "dataDepartment",
      getDataListDepartment(getInitialValue("Department", "", $strDataDepartment), true),
      ["style" => "width:$strDefaultWidthPx"],
      "",
      false,
      ($ARRAY_DISABLE_GROUP['department'] == "")
  );
  $f->addSelect(
      getWords("section"),
      "dataSection",
      getDataListSection(getInitialValue("Section", "", $strDataSection), true),
      ["style" => "width:$strDefaultWidthPx"],
      "",
      false,
      ($ARRAY_DISABLE_GROUP['section'] == "")
  );
  $f->addSelect(
      getWords("sub section"),
      "dataSubSection",
      getDataListSubSection(getInitialValue("SubSection", "", $strDataSubSection), true),
      ["style" => "width:$strDefaultWidthPx"],
      "",
      false,
      ($ARRAY_DISABLE_GROUP['sub_section'] == "")
  );
  $f->addSubmit("btnShow", getWords("show"), "", true, true, "", "", "");
  if ($bolCanApprove) {
    $f->addSubmit("btnSync", getWords("sync"), "", true, true, "", "", "");
  }
  if ($bolCanApprove) {
    $f->addSubmit("btnSetAutoAlpa", getWords("Auto Alpa"), "", true, true, "", "", "");
  }
  $formFilter = $f->render();
  if (validStandardDate($strDateFrom) && validStandardDate(
          $strDateThru
      ) && (isset($_REQUEST['btnShow']) || isset($_REQUEST['btnExportXLS']) || $bolSync || $bolAutoAlpha)
  ) {
    // tampilkan hanya jika ada permintaan dan data tanggalnya tepat
    if ($bolSync) {
      $strKriteria = getKriteria();
      syncShiftAttendance($db, $_REQUEST['dataDateFrom'], $_REQUEST['dataDateThru'], $strKriteria);
      syncOvertimeApplication($db, $_REQUEST['dataDateFrom'], $_REQUEST['dataDateThru'], "", $strKriteria);
      syncLateEarly($db, $_REQUEST['dataDateFrom'], $_REQUEST['dataDateThru'], "", $strKriteria);
      syncIsAbsence($db, $_REQUEST['dataDateFrom'], $_REQUEST['dataDateThru'], "", $strKriteria);
      //recheckAttendanceData($db, $strDateFrom, $strDateThru, $strKriteria);
    } else if ($bolAutoAlpha) {
      $strKriteria = getKriteria();
      setAutoAlpha($db, $_REQUEST['dataDateFrom'], $_REQUEST['dataDateThru'], $strKriteria);
    } else {
      $myDataGrid = new cDataGrid("formData", "DataGrid", "100%", "100%", false, false, false);
      $myDataGrid->caption = $dataPrivilege['menu_name'];
      $DataGrid = getData($db, $bolSync);
      $strHidden .= "<input type=hidden name=btnShow value=show>";
    }
  } else if (isset($_REQUEST['datagridajax']) && $_REQUEST['datagridajax'] == 1) {
    $myDataGrid = new cDataGrid("formData", "DataGrid", "100%", "100%", false, false, false);
    $myDataGrid->caption = $dataPrivilege['menu_name'];
    $DataGrid = getData($db, true);
  }
}
$tbsPage = new clsTinyButStrong;
//write this variable in every page
$strPageTitle = $dataPrivilege['menu_name'];
if (trim($dataPrivilege['icon_file']) == "") {
  $pageIcon = "../images/icons/blank.gif";
} else {
  $pageIcon = "../images/icons/" . $dataPrivilege['icon_file'];
}
$strTemplateFile = getTemplate(str_replace(".php", ".html", basename($_SERVER['PHP_SELF'])));
//------------------------------------------------
//Load Master Template
$tbsPage->LoadTemplate($strMainTemplate);
$tbsPage->Show();
?>
