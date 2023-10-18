<?php
  include_once('../global/session.php');
  include_once('global.php');
  include_once('form_object.php');

  $dataPrivilege = getDataPrivileges(basename($_SERVER['PHP_SELF']), $bolCanView, $bolCanEdit, $bolCanDelete, $bolCanApprove, $bolCanCheck, $bolCanAcknowledge, $bolCanApprove2);
  if (!$bolCanView) die(accessDenied($_SERVER['HTTP_REFERER']));

  //---- INISIALISASI ----------------------------------------------------
  $strModule = "payroll";

  $strMessages = "";
  $strMsgClass = "";

  $arrSetting = array(
    "strCompanyName" => array("code" => "company_name", "value" => "", "note" => "company_name", "default" => "", "oldparameter" => "oldCompanyName"),
    "strCompanyCode" => array("code" => "company_code", "value" => "", "note" => "company code", "default" => "", "oldparameter" => "oldCompanyCode"),
    "strCompanyBankAccountNo" => array("code" => "company_account", "value" => "", "note" => "company bank account no", "default" => "", "oldparameter" => "oldCompanyBankAccountNo"),

    "strStartTime" => array("code" => "start_time", "value" => "", "note" => "normal start time to work", "default" => "07:30:00", "oldparameter" => "oldStartTime"),
    "strFinishTime" => array("code" => "finish_time", "value" => "", "note" => "normal finish time to work", "default" => "16:30:00", "oldparameter" => "oldFinishTime"),
    "strFridayFinishTime" => array("code" => "friday_finish_time", "value" => "", "note" => "finish time to work at friday", "default" => "16:45:00", "oldparameter" => "oldFridayFinishTime"),
    "strSaturday" => array("code" => "saturday", "value" => "", "note" => "saturday is holiday or not", "default" => "t", "oldparameter" => "oldSaturday"),
    /*"strDeptHead" => array("code" => "department_head", "value" => "", "note" => "code for department head", "default" => "", "oldparameter" => "oldDeptHead"),
    "strGroupHead" => array("code" => "group_head", "value" => "", "note" => "code for group head", "default" => "", "oldparameter" => "oldGroupHead"),*/

    "strSignature" => array("code" => "signature", "value" => "", "note" => "signature replacement in printout form", "default" => "", "oldparameter" => "oldSignature"),
    "rdGrouping" => array("code" => "grouping", "value" => "", "note" => "shift group selection", "default" => "0", "oldparameter" => "oldgrouping"),

    "strMaxOTMember" => array("code" => "max_ot_member", "value" => "", "note" => "maximum member on an SPL", "default" => "20", "oldparameter" => "oldMaxOTMember"),
  /*new by chen*/
  "strMinAutoOT" => array("code" => "min_auto_ot", "value" => "", "note" => "minimum auto OT", "default" => "3", "oldparameter" => "oldMinAutoOT"),
  "strMaxAutoOT" => array("code" => "max_auto_ot", "value" => "", "note" => "maximum auto OT", "default" => "3", "oldparameter" => "oldMaxAutoOT"),
  /*end*/
    "strAttendanceFilePath" => array("code" => "attendance_file_path", "value" => "", "note" => "path for importing hand key attendance record ", "default" => "", "oldparameter" => "oldAttendanceFilePath"),
    "strAttendanceFileType" => array("code" => "attendance_file_type", "value" => "", "note" => "file type of hand key attendance record", "default" => "", "oldparameter" => "oldAttendanceFileType"),

    "strLeaveMethod" => array("code" => "leave_method", "value" => "0", "note" => "Skema Cuti 0:JoinDate 1:Prorate 2:JoinDate+Cutoff", "default" => "0", "oldparameter" => "oldLeaveMethod"),
    "strMBCN" => array("code" => "mbcn", "value" => "13", "note" => "Masa Berlaku Cuti Normal", "default" => "13", "oldparameter" => "oldMBCN"),
    "strMBCB" => array("code" => "mbcb", "value" => "48", "note" => "Masa Berlaku Cuti Besar", "default" => "48", "oldparameter" => "oldMBCB"),
    "strHCM" => array("code" => "hcm", "value" => "3", "note" => "Hutang Cuti Maksimal", "default" => "3", "oldparameter" => "oldHCM"),
    "strJCI" => array("code" => "jci", "value" => "4", "note" => "Jatah Cuti Initial", "default" => "4", "oldparameter" => "oldJCI"),
    "strPCB" => array("code" => "pcb", "value" => "5", "note" => "Periode Cuti Besar", "default" => "5", "oldparameter" => "oldPCB"),
    "strJCB" => array("code" => "jcb", "value" => "44", "note" => "Jatah Cuti Besar", "default" => "44", "oldparameter" => "oldJCB"),
    "strJCN" => array("code" => "jcn", "value" => "12", "note" => "Jatah Cuti Normal", "default" => "12", "oldparameter" => "oldJCn"),
    "strProgressive" => array("code" => "progressive", "value" => "", "note" => "JCB is progressive or not", "default" => "t", "oldparameter" => "oldProggressive"),
    "strSlipPersonnel" => array("code" => "slipPersonnel", "value" => "-", "note" => "Name shown on salary slip at the personnel section", "default" => "t", "oldparameter" => "oldSlipPersonnel"),
    "strSlipFinance" => array("code" => "slipFinance", "value" => "-", "note" => "Name shown on salary slip at the finance section", "default" => "t", "oldparameter" => "oldSlipFinance"),

  );
  // untuk breakTime, dipisiahkan, karena tabelnya beda
  $strBreakNormal = "";
  $strBreakFriday = "";
  $strBreakHoliday = "";
  $strNormalID0 = "";
  $strFridayID0 = "";
  $strHolidayID0 = "";
  $strNormalBreak0 = "";
  $strFridayBreak0 = "";
  $strHolidayBreak0 = "";
  $strNormalDuration0 = "";
  $strFridayDuration0 = "";
  $strHolidayDuration0 = "";
  $strNormalNote0 = "";
  $strFridayNote0 = "";
  $strHolidayNote0 = "";
  $strNormalFinish0 = "";
  $strHolidayFinish0 = "";
  $strFridayFinish0 = "";
  $arrFirstData = array();

  // untuk salary transfer status
  $strTransferStatus                = "";
  $strTransferStatusID0             = "";
  $strTransferStatusCode0           = "";
  $strTransferStatusRemark0         = "";
  $arrTransferStatusData            = array();

  $arrCompanyList = array();
  $arrManagementList = array();
  $arrDivisionst = array();
  $arrDepartmentList = array();
  $arrSectionList = array();
  $arrSubSectionList = array();


  $strWordsDays = getWords("days");
  $strWordsMonths = getWords("months");
  $strWordsYears = getWords("years");
  $strWordsCompanyIdentity = getWords("company identity");
  $strWordsCompanyName = getWords("company name");
  $strWordsCompanyCode = getWords("company code");
  $strWordsCompanyBankAccountNo = getWords("company bank account no");
  $strWordsDailySetting = getWords("daily setting");
  $strWordsStartTime = getWords("start time");
  $strWordsFinishTime = getWords("finish time");
  $strWordsFridayFinishTime = getWords("friday finish time");
  $strWordsSaturdayOff = getWords("saturday off (holiday)");
  $strWordsBreakTime = getWords("break time");
  $strWordsDayType = getWords("day type");
  $strWordsNote = getWords("note");
  $strWordsRoleGradeCode = getWords("role grade (optional)");
  $strWordsDur = getWords("dur. (min)");
  $strWordsNormalDay = getWords("normal day");
  $strWordsFriday = getWords("friday");
  $strWordsHoliday = getWords("holiday");
  $strWordsLeave = getWords("leave");
  $strWordsInitialLeaveQuota = getWords("initial leave quota");
  $strWordsMaximumAdvanceLeave = getWords("maximum advance leave");
  $strWordsGrandLeavePeriod = getWords("grand leave period");
  $strWordsGrandLeaveQuota = getWords("grand leave quota");
  $strWordsLeaveQuota = getWords("leave quota");
  $strWordsLeaveAreValidFor = getWords("leave are valid for");
  $strWordsGrandLeaveAreValidFor = getWords("grand leave are valid for");
  $strWordsMaximumMemberOnSPL = getWords("maximum member on spl");
  $strWordsAttendanceImportSetting = getWords("attendance import setting");
  $strWordsLeaveMethod = getWords("leave method");
  $strWordsJoinDate = getWords("join date");
  $strWordsProrate = getWords("prorate");
  $strWordsJoinDateCutoff = getWords("join date with december cutoff");
  $strWordsSave = getWords("save");
  $strWordsMinimumAutoOT = getWords("minimum auto overtime");
  $strWordsMaximumAutoOT = getWords("maximum auto overtime");
  $strWordsFilePath = getWords("file path");
  $strWordsProgressive = getWords("grand leave quota is progressive");
  $strWordsFileType = getWords("file type");
  $strWordsSlipPersonnel = getWords("name shown on salary slip ( personnel section )");
  $strWordsSlipFinance = getWords("name shown on salary slip ( finance section )");
  $strWordsOvertime = getWords("overtime");
  $strWordsOvertimeDuration = getWords("overtime per overtime duration (not u u no. 13 ketenagakerjaan)");
  $strWordsAmountSetting = getWords("amount setting");
  $strWordsOvertimeMeal = getWords("overtime meal");
  $strWordsOvertimeAllowance = getWords("overtime allowance");
  $strWordsSalaryTransferStatus = getWords("salary transfer status");
  $strWordsTransferStatusCode = getWords("code");
  $strWordsTransferStatusRemark = getWords("remark");
  //----------------------------------------------------------------------

  //--- DAFTAR FUNSI------------------------------------------------------
  // fungsi untuk menampilkan data
  // $db = kelas database
  // return berubah $arrOvertime (parameter)
  function getData($db) {
    global $words;
    global $_SESSION;
    global $strModule;
    global $arrSetting;
    global $arrCompanyList,$arrManagementList,$arrDivisionList,$arrDepartmentList,$arrSectionList,$arrSubSectionList;

    $arrCompanyList = array();
    $strSQL  = "SELECT * FROM hrd_company ORDER BY id ";
    $resTmp = $db->execute($strSQL);
    while ($rowTmp = $db->fetchrow($resTmp))
    {
      $arrCompanyList[$rowTmp['id']] = $rowTmp['company_code']." - ".$rowTmp['company_name'];
    }
    $arrManagementList = array();
    $strSQL  = "SELECT * FROM hrd_management ORDER BY management_code  ";
    $resTmp = $db->execute($strSQL);
    while ($rowTmp = $db->fetchrow($resTmp))
    {
      $arrManagementList[$rowTmp['management_code']] = $rowTmp['management_code']." - ".$rowTmp['management_name'];
    }
    $arrSubSectionList = array();
    $strSQL  = "SELECT * FROM hrd_sub_section ORDER BY section_code, sub_section_code ";
    $resTmp = $db->execute($strSQL);
    while ($rowTmp = $db->fetchrow($resTmp))
    {
      $arrSubSectionList[$rowTmp['sub_section_code']] = $rowTmp['sub_section_code']." - ".$rowTmp['sub_section_name'];
    }
    $arrSectionList = array();
    $strSQL  = "SELECT * FROM hrd_section ORDER BY department_code, section_code ";
    $resTmp = $db->execute($strSQL);
    while ($rowTmp = $db->fetchrow($resTmp))
    {
      $arrSectionList[$rowTmp['section_code']] = $rowTmp['section_code']." - ".$rowTmp['section_name'];
    }
    $arrDepartmentList = array();
    $strSQL  = "SELECT * FROM hrd_department ORDER BY department_code ";
    $resTmp = $db->execute($strSQL);
    while ($rowTmp = $db->fetchrow($resTmp))
    {
      $arrDepartmentList[$rowTmp['department_code']] = $rowTmp['department_code']." - ".$rowTmp['department_name'];
    }
    $arrDivisionList = array();
    $strSQL  = "SELECT * FROM hrd_division ORDER BY division_code";
    $resTmp = $db->execute($strSQL);
    while ($rowTmp = $db->fetchrow($resTmp))
    {
      $arrDivisionList[$rowTmp['division_code']] = $rowTmp['division_code']." - ".$rowTmp['division_name'];
    }

    $intIDmodified_by = $_SESSION['sessionUserID'];

    $tblSetting = new cModel("all_setting");
    foreach ($arrSetting AS $kode => $arrData)
    {
      if ($arrData['code'] != "")
      {
        if ($arrHasil = $tblSetting->findByCode($arrData['code']))
        {
          $arrSetting[$kode]["value"] = $arrHasil['value'];
        }
        else
        {
          $data = array("code"   => $arrData['code'],
                        "value"  => $arrData['default'],
                        "note"   => $arrData['note'],
                        "module" => $strModule
                        );
          $tblSetting->insert($data);
        }
      }
    }
    writeLog(ACTIVITY_VIEW, MODULE_PAYROLL,"",0);

    return true;
  } // showData

  // fungsi untuk mengambil data tambahan jam istirahat
  // tipe = 0,1,2
  function getDataBreak($db, $tipe) {
    global $arrFirstData;

    $strDefaultBreak = "00:00";
    $intMaxDetail = 20;

    $strResult = "";

    //inisialisasi
    $arrFirstData[$tipe]['id'] = "";
    $arrFirstData[$tipe]['break'] = $strDefaultBreak;
    $arrFirstData[$tipe]['note'] = "";
    $arrFirstData[$tipe]['finish'] = $strDefaultBreak;
    $arrFirstData[$tipe]['duration'] = 0;

    $strSQL  = "SELECT * FROM hrd_break_time WHERE type = '$tipe' ORDER BY start_time ";
    $resDb = $db->execute($strSQL);
    $i = 0;
    while ($rowDb = $db->fetchrow($resDb)) {
      if ($i == 0) {
        $arrFirstData[$tipe]['id'] = $rowDb['id'];
        $arrFirstData[$tipe]['break'] = substr($rowDb['start_time'],0,5);
        $arrFirstData[$tipe]['duration'] = $rowDb['duration'];
        $arrFirstData[$tipe]['finish'] = getNextMinute($rowDb['start_time'],$rowDb['duration']);
        $arrFirstData[$tipe]['note'] = $rowDb['note'];
        $arrFirstData[$tipe]['role_grade_code'] = $rowDb['role_grade_code'];
      } else {
        $strResult .= "<tr valign=top id='detailData$tipe"."_$i'>\n";
        $strResult .= "  <td>&nbsp;</td>\n";
        $strResult .= "  <td>:<input type=hidden name=dataID$tipe"."_$i value=\"" .$rowDb['id']."\"></td>\n";
        $strResult .= "  <td><input type=text name=dataBreak$tipe"."_$i size=10 maxlength=10 value=\"" .substr($rowDb['start_time'],0,5). "\"></td>\n";
        $strResult .= "  <td><input type=text name=dataDuration$tipe"."_$i size=10 maxlength=10 value=\"" .$rowDb['duration']. "\"></td>\n";
        $strResult .= "  <td nowrap>&nbsp;" .getNextMinute($rowDb['start_time'],$rowDb['duration']). "</td>\n";
        $strResult .= "  <td><input type=text name=dataNote$tipe"."_$i size=30 maxlength=30 value=\"" .$rowDb['note']. "\"></td>\n";
        $strResult .= "  <td><input type=text name=dataRoleGradeCode$tipe"."_$i size=10 maxlength=30 value=\"" .$rowDb['role_grade_code']. "\"></td>\n";
        $strResult .= "</tr>\n";
      }
      $i++;
    }

    if ($i == 0) {
      $intNumShow = 1;
      $i = 1;
    } else {
      $intNumShow = $i + 1;
    }

    // tambahkan detail tambahan
    while ($i <= $intMaxDetail) {
      $strResult .= "<tr valign=top id='detailData$tipe"."_$i' style=\"display:none\">\n";
      $strResult .= "  <td>&nbsp;</td>\n";
      $strResult .= "  <td>:</td>\n";
      $strResult .= "  <td><input type=text name=dataBreak$tipe"."_$i size=10 maxlength=10 value=\"$strDefaultBreak\" disabled></td>\n";
      $strResult .= "  <td><input type=text name=dataDuration$tipe"."_$i size=10 maxlength=10 value=\"0\" disabled></td>\n";
      $strResult .= "  <td>&nbsp;</td>\n";
      $strResult .= "  <td><input type=text name=dataNote$tipe"."_$i size=30 maxlength=30 value=\"\" disabled></td>\n";
      $strResult .= "  <td><input type=text name=dataRoleGradeCode$tipe"."_$i size=10 maxlength=30 value=\"\" disabled></td>\n";
      $strResult .= "</tr>\n";
      $i++;
    }

    // tambahkan hidden value
    $strResult .= "<input type=hidden name='numShow$tipe' value=$intNumShow>";
    $strResult .= "<input type=hidden name='maxDetail$tipe' value=$intMaxDetail>";

    return $strResult;
  }//getDataBreak


// fungsi untuk mengambil data transfer Status Salary
  function getDataTransferStatus($db)
  {
    global $arrTransferStatusData;

    $strDefaultBreak = "";
    $intMaxDetail = 20;

    $strResult = "";

    //inisialisasi
    $arrTransferStatusData['id'] = "";
    $arrTransferStatusData['code'] = "";
    $arrTransferStatusData['remark'] = "";

    $strSQL  = "SELECT * FROM hrd_salary_transfer_type order by code asc";
    $resDb = $db->execute($strSQL);
    $i = 0;
    while ($rowDb = $db->fetchrow($resDb)) {
      if ($i == 0) {
        $arrTransferStatusData['id'] = $rowDb['id'];
        $arrTransferStatusData['code'] = $rowDb['code'];
        $arrTransferStatusData['remark'] =  $rowDb['remark'];
      } else {
        $strResult .= "<tr valign=top id='detailTransferStatus_$i'>\n";
//        $strResult .= "  <td></td>\n";
        $strResult .= "  <td><input type=hidden name=dataTransferStatusID_$i value=\"" .$rowDb['id']."\"></td>\n";
        $strResult .= "  <td><input type=text name=dataTransferStatusCode_$i size=20 maxlength=200 value=\"" .$rowDb['code']. "\"></td>\n";
        $strResult .= "  <td><input type=text name=dataTransferStatusRemark_$i size=50 maxlength=200 value=\"" .$rowDb['remark']. "\"></td>\n";
        $strResult .= "</tr>\n";
      }
      $i++;
    }


    if ($i == 0) {
      $intNumShow = 1;
      $i = 1;
    } else {
      $intNumShow = $i + 1;
    }

    // tambahkan detail tambahan
    while ($i <= $intMaxDetail) {
      $strResult .= "<tr valign=top id='detailTransferStatus_$i' style=\"display:none\">\n";
      $strResult .= "  <td></td>\n";
      $strResult .= "  <td><input type=text name=dataTransferStatusCode_$i size=20 maxlength=200 value=\"" .$rowDb['code']. "\" disabled ></td>\n";
      $strResult .= "  <td><input type=text name=dataTransferStatusRemark_$i size=50 maxlength=200 value=\"" .$rowDb['remark']. "\" disabled ></td>\n";
      $strResult .= "</tr>\n";
      $i++;
    }

    // tambahkan hidden value
    $strResult .= "<input type=hidden name='numShow' value=$intNumShow>";
    $strResult .= "<input type=hidden name='maxDetail' value=$intMaxDetail>";


    return $strResult;
  }//getDataDocument

  // fungsi untuk menyimpan data
  function saveData($db, &$strError)
  {

    global $_REQUEST;
    global $_SESSION;
    global $arrSetting;
    global $arrBreakTime;
    global $messages;
    $strmodified_byID = $_SESSION['sessionUserID'];

    foreach ($arrSetting AS $kode => $arrData) {
      if (isset($_REQUEST[$kode])) {
        $strValue = $_REQUEST[$kode];

        $strSQL  = "UPDATE all_setting SET modified_by = '" .$_SESSION['sessionUserID']. "', ";
        $strSQL .= "created = now(), value = '$strValue' ";
        $strSQL .= "WHERE code = '" .$arrData['code']. "' ";
        $resExec = $db->execute($strSQL);
      }
    }

    // simpan dta libur hari sabtu
    $strKode = (isset($_REQUEST['strSaturday'])) ? "t" : "f";
    $strSQL  = "UPDATE all_setting SET modified_by = '" .$_SESSION['sessionUserID']. "', ";
    $strSQL .= "created = now(), value = '$strKode' ";
    $strSQL .= "WHERE code = 'saturday' ";
    $resExec = $db->execute($strSQL);

    // simpan data apakah grand leave quota progressive
    $strKode = (isset($_REQUEST['strProgressive'])) ? "t" : "f";
    $strSQL  = "UPDATE all_setting SET modified_by = '" .$_SESSION['sessionUserID']. "', ";
    $strSQL .= "created = now(), value = '$strKode' ";
    $strSQL .= "WHERE code = 'progressive' ";
    $resExec = $db->execute($strSQL);

    // simpan jam istirahat
    for ($tipe = 0;$tipe <=2;$tipe++) {
      $intMax = 20;

      for ($i = 0;$i<=$intMax;$i++) {
        $strID = (isset($_REQUEST['dataID'.$tipe.'_'.$i])) ? $_REQUEST['dataID'.$tipe.'_'.$i] : "";
        $strBreak = (isset($_REQUEST['dataBreak'.$tipe.'_'.$i])) ? $_REQUEST['dataBreak'.$tipe.'_'.$i] : "";
        $strDuration = (isset($_REQUEST['dataDuration'.$tipe.'_'.$i])) ? $_REQUEST['dataDuration'.$tipe.'_'.$i] : "";
        $strNote = (isset($_REQUEST['dataNote'.$tipe.'_'.$i])) ? $_REQUEST['dataNote'.$tipe.'_'.$i] : "";
        $roleGradeCode = (isset($_REQUEST['dataRoleGradeCode'.$tipe.'_'.$i])) ? $_REQUEST['dataRoleGradeCode'.$tipe.'_'.$i] : "";

        if (!is_numeric($strDuration)) {
          $strDuration = 0;
        }

        if ($strBreak == "") { // ada kemungkinan ndihapus
          if ($strID != "") {
            //hapus data
            $strSQL  = "DELETE FROM hrd_break_time WHERE id = '$strID' ";
            $resExec = $db->execute($strSQL);
          }
        } else {
          if ($strID == "") { // insert new
            $strSQL  = "INSERT INTO hrd_break_time (created,modified_by,created_by, ";
            $strSQL .= "\"start_time\",duration, note, type,role_grade_code) ";
            $strSQL .= "VALUES(now(), '$strmodified_byID', '$strmodified_byID', ";
            $strSQL .= "'$strBreak', '$strDuration', '$strNote', '$tipe','$roleGradeCode') ";
            $resExec = $db->execute($strSQL);
          } else {//update
            $strSQL  = "UPDATE hrd_break_time SET modified_by = '$strmodified_byID', ";
            $strSQL .= "\"start_time\" = '$strBreak', duration = '$strDuration', note = '$strNote' ,role_grade_code='$roleGradeCode'";
            $strSQL .= "WHERE id = '$strID' ";
            $resExec = $db->execute($strSQL);
          }
        }
        $strSQL  = "DELETE FROM  hrd_break_time WHERE duration = 0";
        $resExec = $db->execute($strSQL);
      }
    }

      //simpan data transferStatus
      $intMax = 20;

      for ($i = 0;$i<=$intMax;$i++) {
        $strTransferStatusID     = (isset($_REQUEST['dataTransferStatusID_'.$i])) ? $_REQUEST['dataTransferStatusID_'.$i] : "";
        $strTransferStatusCode     = (isset($_REQUEST['dataTransferStatusCode_'.$i])) ? $_REQUEST['dataTransferStatusCode_'.$i] : "";
        $strTransferStatusRemark     = (isset($_REQUEST['dataTransferStatusRemark_'.$i])) ? $_REQUEST['dataTransferStatusRemark_'.$i] : "";

        if ($strTransferStatusCode == "") { // ada kemungkinan ndihapus
          if ($strTransferStatusID != "") {
            //hapus data
            $strSQL  = "DELETE FROM hrd_salary_transfer_type WHERE id = '$strTransferStatusID' ";
            $resExec = $db->execute($strSQL);
          }
        } else {
          if ($strTransferStatusID == "") { // insert new
            $strSQL  = "INSERT INTO hrd_salary_transfer_type (code, remark, created, created_by) ";
            $strSQL .= "VALUES($strTransferStatusCode, '$strTransferStatusRemark', now(), '$strmodified_byID')";
            $resExec = $db->execute($strSQL);
          } else {//update
            $strSQL  = "UPDATE hrd_salary_transfer_type SET modified_by = '$strmodified_byID', modified = now(), ";
            $strSQL .= " code = '$strTransferStatusCode', remark = '$strTransferStatusRemark' ";
            $strSQL .= "WHERE id = '$strTransferStatusID' ";
            $resExec = $db->execute($strSQL);
          }
        }
      }

    saveDataOvertime($db);
    writeLog(ACTIVITY_EDIT, MODULE_PAYROLL,"",0);
    $strError = $messages['data_saved']. " ".date("d-M-y H:i:s");
    return true;
  } // saveData

function saveDataOvertime($db)
{
  global $_REQUEST;
  $strmodified_byID = $_SESSION['sessionUserID'];
  //Saving data from Overtime Duration Input
  $intMax = (isset($_REQUEST['numShowovertime_duration'])) ? $_REQUEST['numShowovertime_duration'] : 0;

  for ($i = 0;$i<$intMax;$i++) {
    $strID = (isset($_REQUEST['dataID_overtime_duration_'.$i])) ? $_REQUEST['dataID_overtime_duration_'.$i] : "";
    $strCompanyOTDuration = (isset($_REQUEST['dataCompany_overtime_duration_'.$i])) ? $_REQUEST['dataCompany_overtime_duration_'.$i] : "";
    $strManagementOTDuration = (isset($_REQUEST['dataManagement_overtime_duration_'.$i])) ? $_REQUEST['dataManagement_overtime_duration_'.$i] : "";
    $strDivisionOTDuration = (isset($_REQUEST['dataDivision_overtime_duration_'.$i])) ? $_REQUEST['dataDivision_overtime_duration_'.$i] : "";
    $strDepartmentOTDuration = (isset($_REQUEST['dataDepartment_overtime_duration_'.$i])) ? $_REQUEST['dataDepartment_overtime_duration_'.$i] : "";
    $strSectionOTDuration = (isset($_REQUEST['dataSection_overtime_duration_'.$i])) ? $_REQUEST['dataSection_overtime_duration_'.$i] : "";
    $strSubSectionOTDuration = (isset($_REQUEST['dataSubSection_overtime_duration_'.$i])) ? $_REQUEST['dataSubSection_overtime_duration_'.$i] : "";
    if($strCompanyOTDuration != "")
    {
      if($strID == ""){
        $strSQL = "INSERT INTO _hrd_overtime_duration_department (created_by,created,
                 id_company,management_code,division_code,department_code,section_code,sub_section_code
                 ) VALUES ('$strmodified_byID',now(),'$strCompanyOTDuration','$strManagementOTDuration','$strDivisionOTDuration',
               '$strDepartmentOTDuration','$strSectionOTDuration','$strSubSectionOTDuration'
               )";
      }
      else
        {
          $strSQL = "UPDATE _hrd_overtime_duration_department SET id_company = '$strCompanyOTDuration',
               management_code = '$strManagementOTDuration', division_code = '$strDivisionOTDuration',
               department_code = '$strDepartmentOTDuration', section_code ='$strSectionOTDuration',
               sub_section_code= '$strSubSectionOTDuration', modified_by = '$strmodified_byID', modified = now() WHERE id = $strID ";
        }
    }
    if($strCompanyOTDuration == "" && $strManagementOTDuration == "" && $strDivisionOTDuration == "" && $strDepartmentOTDuration == "" && $strSectionOTDuration == "" && $strSubSectionOTDuration == "" && $strID != "" ){
      $strSQL = "DELETE FROM _hrd_overtime_duration_department WHERE id = $strID";
    }
   $db->execute($strSQL);
  }

  //Saving data from Workday Amount Setting Input
  $intMax = (isset($_REQUEST['numShowWorkday'])) ? $_REQUEST['numShowWorkday'] : 0;
  $strSQL = "";
  for ($i = 0;$i<$intMax;$i++) {
    $strID          = (isset($_REQUEST['dataID_Workday_'.$i])) ? $_REQUEST['dataID_Workday_'.$i] : "";
    $intWorkdayFrom = (isset($_REQUEST['dataWorkdayFrom_'.$i])) ? $_REQUEST['dataWorkdayFrom_'.$i] : "";
    $intWorkdayUntil= (isset($_REQUEST['dataWorkdayUntil_'.$i])) ? $_REQUEST['dataWorkdayUntil_'.$i] : "";
    $intWorkdayValue= (isset($_REQUEST['dataWorkdayValue_'.$i])) ? $_REQUEST['dataWorkdayValue_'.$i] : "";
    if($intWorkdayFrom != "")
    {
      if($strID == ""){
        $strSQL = "INSERT INTO _hrd_overtime_duration_setting (created_by,created,duration_from,
                 duration_thru,amount,holiday) VALUES ('$strmodified_byID',now(),
                 $intWorkdayFrom,$intWorkdayUntil,$intWorkdayValue,FALSE)";
        //        die($strSQL);
      }
      else
        {
          $strSQL = "UPDATE _hrd_overtime_duration_setting SET duration_from = $intWorkdayFrom,
                     duration_thru = $intWorkdayUntil, amount = $intWorkdayValue,
                     modified_by = '$strmodified_byID', modified = now() WHERE id = $strID ";
      }
    }
    if($intWorkdayFrom == "" && $intWorkdayUntil == "" && $intWorkdayValue == "" && $strID != ""){
      $strSQL = "DELETE FROM _hrd_overtime_duration_setting WHERE id = $strID";
    }
    $db->execute($strSQL);
  }

  //Saving data from Holiday Amount Setting Input
  $intMax = (isset($_REQUEST['numShowHoliday'])) ? $_REQUEST['numShowHoliday'] : 0;
  $strSQL = "";
  for ($i = 0;$i<$intMax;$i++) {
    $strID          = (isset($_REQUEST['dataID_Holiday_'.$i])) ? $_REQUEST['dataID_Holiday_'.$i] : "";
    $intHolidayFrom = (isset($_REQUEST['dataHolidayFrom_'.$i])) ? $_REQUEST['dataHolidayFrom_'.$i] : "";
    $intHolidayUntil= (isset($_REQUEST['dataHolidayUntil_'.$i])) ? $_REQUEST['dataHolidayUntil_'.$i] : "";
    $intHolidayValue= (isset($_REQUEST['dataHolidayValue_'.$i])) ? $_REQUEST['dataHolidayValue_'.$i] : "";
    if($intHolidayFrom != "")
    {
      if($strID == ""){
        $strSQL = "INSERT INTO _hrd_overtime_duration_setting (created_by,created,duration_from,duration_thru,
                   amount,holiday) VALUES ('$strmodified_byID',now(),$intHolidayFrom,
                   $intHolidayUntil,$intHolidayValue,TRUE)";
        //        die($strSQL);
      }
      else
        {
          $strSQL = "UPDATE _hrd_overtime_duration_setting SET duration_from = $intHolidayFrom,
                     duration_thru = $intHolidayUntil, amount = $intHolidayValue,
                     modified_by = '$strmodified_byID', modified = now() WHERE id = $strID ";
        }
    }
    if($intHolidayFrom == "" && $intHolidayUntil == "" && $intHolidayValue == "" && $strID != ""){
          $strSQL = "DELETE FROM _hrd_overtime_duration_setting WHERE id = $strID";
    }
    $db->execute($strSQL);
  }

  //Saving data from Overtime Meal Workday
  $intMax = (isset($_REQUEST['numShowovertime_meal_workday'])) ? $_REQUEST['numShowovertime_meal_workday'] : 0;
  $strSQL = "";
  for ($i = 0;$i<$intMax;$i++) {
    $strID = (isset($_REQUEST['dataID_overtime_meal_workday_'.$i])) ? $_REQUEST['dataID_overtime_meal_workday_'.$i] : "";
    $strCompanyOTMealWorkday = (isset($_REQUEST['dataCompany_overtime_meal_workday_'.$i])) ? $_REQUEST['dataCompany_overtime_meal_workday_'.$i] : "";
    $strManagementOTMealWorkday = (isset($_REQUEST['dataManagement_overtime_meal_workday_'.$i])) ? $_REQUEST['dataManagement_overtime_meal_workday_'.$i] : "";
    $strDivisionOTMealWorkday = (isset($_REQUEST['dataDivision_overtime_meal_workday_'.$i])) ? $_REQUEST['dataDivision_overtime_meal_workday_'.$i] : "";
    $strDepartmentOTMealWorkday = (isset($_REQUEST['dataDepartment_overtime_meal_workday_'.$i])) ? $_REQUEST['dataDepartment_overtime_meal_workday_'.$i] : "";
    $strSectionOTMealWorkday = (isset($_REQUEST['dataSection_overtime_meal_workday_'.$i])) ? $_REQUEST['dataSection_overtime_meal_workday_'.$i] : "";
    $strSubSectionOTMealWorkday = (isset($_REQUEST['dataSubSection_overtime_meal_workday_'.$i])) ? $_REQUEST['dataSubSection_overtime_meal_workday_'.$i] : "";
    $intValueOTMealWorkday = (isset($_REQUEST['dataValue_overtime_meal_workday_'.$i])) ? $_REQUEST['dataValue_overtime_meal_workday_'.$i] : "";
    if($strCompanyOTMealWorkday != "")
    {
      if($strID == ""){
        $strSQL = "INSERT INTO _hrd_overtime_meal (created_by,created,id_company,management_code,
               division_code,department_code,section_code,sub_section_code,amount,holiday) VALUES (
               '$strmodified_byID',now(),'$strCompanyOTMealWorkday','$strManagementOTMealWorkday','$strDivisionOTMealWorkday',
               '$strDepartmentOTMealWorkday','$strSectionOTMealWorkday','$strSubSectionOTMealWorkday',
               $intValueOTMealWorkday,FALSE
               )";
      }
      else
        {
          $strSQL = "UPDATE _hrd_overtime_meal SET id_company = '$strCompanyOTMealWorkday',
               management_code = '$strManagementOTMealWorkday', division_code = '$strDivisionOTMealWorkday',
               department_code = '$strDepartmentOTMealWorkday', section_code='$strSectionOTMealWorkday',
               sub_section_code= '$strSubSectionOTMealWorkday', amount = $intValueOTMealWorkday,
               modified_by = '$strmodified_byID', modified = now() WHERE id = $strID ";
        }
    }
    if($strCompanyOTMealWorkday == "" && $strManagementOTMealWorkday == "" && $strDivisionOTMealWorkday == "" && $strDepartmentOTMealWorkday == "" && $strSectionOTMealWorkday == "" && $strSubSectionOTMealWorkday == "" && $intValueOTMealWorkday == "" && strID != ""){
      $strSQL = "DELETE FROM _hrd_overtime_meal WHERE id = $strID";
    }
    $db->execute($strSQL);
  }

  //Saving data from Overtime Meal Holiday
  $intMax = (isset($_REQUEST['numShowovertime_meal_holiday'])) ? $_REQUEST['numShowovertime_meal_holiday'] : 0;
  $strSQL = "";
  for ($i = 0;$i<$intMax;$i++) {
    $strID = (isset($_REQUEST['dataID_overtime_meal_holiday_'.$i])) ? $_REQUEST['dataID_overtime_meal_holiday_'.$i] : "";
    $strCompanyOTMealHoliday = (isset($_REQUEST['dataCompany_overtime_meal_holiday_'.$i])) ? $_REQUEST['dataCompany_overtime_meal_holiday_'.$i] : "";
    $strManagementOTMealHoliday = (isset($_REQUEST['dataManagement_overtime_meal_holiday_'.$i])) ? $_REQUEST['dataManagement_overtime_meal_holiday_'.$i] : "";
    $strDivisionOTMealHoliday = (isset($_REQUEST['dataDivision_overtime_meal_holiday_'.$i])) ? $_REQUEST['dataDivision_overtime_meal_holiday_'.$i] : "";
    $strDepartmentOTMealHoliday = (isset($_REQUEST['dataDepartment_overtime_meal_holiday_'.$i])) ? $_REQUEST['dataDepartment_overtime_meal_holiday_'.$i] : "";
    $strSectionOTMealHoliday = (isset($_REQUEST['dataSection_overtime_meal_holiday_'.$i])) ? $_REQUEST['dataSection_overtime_meal_holiday_'.$i] : "";
    $strSubSectionOTMealHoliday = (isset($_REQUEST['dataSubSection_overtime_meal_holiday_'.$i])) ? $_REQUEST['dataSubSection_overtime_meal_holiday_'.$i] : "";
    $intValueOTMealHoliday = (isset($_REQUEST['dataValue_overtime_meal_holiday_'.$i])) ? $_REQUEST['dataValue_overtime_meal_holiday_'.$i] : "";
    if($strCompanyOTMealHoliday != "")
    {
      if($strID == ""){
        $strSQL = "INSERT INTO _hrd_overtime_meal (created_by,created,id_company,management_code,
               division_code,department_code,section_code,sub_section_code,amount,holiday) VALUES (
               '$strmodified_byID',now(),'$strCompanyOTMealHoliday','$strManagementOTMealHoliday','$strDivisionOTMealHoliday',
               '$strDepartmentOTMealHoliday','$strSectionOTMealHoliday','$strSubSectionOTMealHoliday',
               $intValueOTMealWorkday,TRUE
               )";

      }
        else
        {
          $strSQL = "UPDATE _hrd_overtime_meal SET id_company = '$strCompanyOTMealHoliday',
               management_code = '$strManagementOTMealHoliday', division_code = '$strDivisionOTMealHoliday',
               department_code = '$strDepartmentOTMealHoliday', section_code='$strSectionOTMealHoliday',
               sub_section_code= '$strSubSectionOTMealHoliday', amount = $intValueOTMealHoliday,
               modified_by = '$strmodified_byID', modified = now() WHERE id = $strID ";
        }
    }
    if($strCompanyOTMealHoliday == "" && $strManagementOTMealHoliday == "" && $strDivisionOTMealHoliday == "" && $strDepartmentOTMealHoliday == "" && $strSectionOTMealHoliday == "" && $strSubSectionOTMealHoliday == "" && $intValueOTMealHoliday == "" && $strID != ""){
      $strSQL = "DELETE FROM _hrd_overtime_meal WHERE id = $strID";
    }

    $db->execute($strSQL);
  }

  //Saving data from Overtime Max Allowance
  $intMax = (isset($_REQUEST['numShowovertime_allowance'])) ? $_REQUEST['numShowovertime_allowance'] : 0;
  $strSQL = "";
  for ($i = 0;$i<$intMax;$i++) {
    $strID = (isset($_REQUEST['dataID_overtime_allowance_'.$i])) ? $_REQUEST['dataID_overtime_allowance_'.$i] : "";
    $strCompanyOTAllowance = (isset($_REQUEST['dataCompany_overtime_allowance_'.$i])) ? $_REQUEST['dataCompany_overtime_allowance_'.$i] : "";
    $strManagementOTAllowance = (isset($_REQUEST['dataManagement_overtime_allowance_'.$i])) ? $_REQUEST['dataManagement_overtime_allowance_'.$i] : "";
    $strDivisionOTAllowance = (isset($_REQUEST['dataDivision_overtime_allowance_'.$i])) ? $_REQUEST['dataDivision_overtime_allowance_'.$i] : "";
    $strDepartmentOTAllowance = (isset($_REQUEST['dataDepartment_overtime_allowance_'.$i])) ? $_REQUEST['dataDepartment_overtime_allowance_'.$i] : "";
    $strSectionOTAllowance = (isset($_REQUEST['dataSection_overtime_allowance_'.$i])) ? $_REQUEST['dataSection_overtime_allowance_'.$i] : "";
    $strSubSectionOTAllowance = (isset($_REQUEST['dataSubSection_overtime_allowance_'.$i])) ? $_REQUEST['dataSubSection_overtime_allowance_'.$i] : "";
    $intValueOTAllowance = (isset($_REQUEST['dataValue_overtime_allowance_'.$i])) ? $_REQUEST['dataValue_overtime_allowance_'.$i] : "";
    if($strCompanyOTAllowance != "")
    {
      if($strID == ""){
        $strSQL = "INSERT INTO _hrd_overtime_max_allowance (created_by,created,id_company,management_code,
               division_code,department_code,section_code,sub_section_code,amount) VALUES (
               '$strmodified_byID',now(),'$strCompanyOTAllowance','$strManagementOTAllowance','$strDivisionOTAllowance',
               '$strDepartmentOTAllowance','$strSectionOTAllowance','$strSubSectionOTAllowance',
               $intValueOTAllowance
               )";
      }
        else
        {
          $strSQL = "UPDATE _hrd_overtime_max_allowance SET id_company = '$strCompanyOTAllowance',
               management_code = '$strManagementOTAllowance', division_code = '$strDivisionOTAllowance',
               department_code = '$strDepartmentOTAllowance', section_code='$strSectionOTAllowance',
               sub_section_code= '$strSubSectionOTAllowance', amount = $intValueOTAllowance,
               modified_by = '$strmodified_byID', modified = now() WHERE id = $strID ";
        }
    }
    if($strCompanyOTAllowance == "" && $strManagementOTAllowance == "" && $strDivisionOTAllowance == "" && $strDepartmentOTAllowance == "" && $strSectionOTAllowance == "" && $strSubSectionOTAllowance == "" && $intValueOTAllowance == "" && $strID != ""){
          $strSQL = "DELETE FROM _hrd_overtime_max_allowance WHERE id = $strID";
    }

    $db->execute($strSQL);
  }
  //Saving data from Max Allowance Special
  $intMax = (isset($_REQUEST['numShowSpecial'])) ? $_REQUEST['numShowSpecial'] : 0;
  $strSQL = "";
  for ($i = 0;$i<$intMax;$i++) {
    $strID = (isset($_REQUEST['dataID_Special_'.$i])) ? $_REQUEST['dataID_Special_'.$i] : "";
    $intIDEmployeeSpecial = (isset($_REQUEST['dataEmployeeSpecial_'.$i])) ? $_REQUEST['dataEmployeeSpecial_'.$i] : "";
    $strIDEmployee = "SELECT id from hrd_employee where employee_id = '$intIDEmployeeSpecial'";
    $res = $db->fetchrow($db->execute($strIDEmployee));
    $strIDEmployee = $res['id'];
    $intValueSpecial = (isset($_REQUEST['dataEmployeeSpecialValue_'.$i])) ? $_REQUEST['dataEmployeeSpecialValue_'.$i] : "";
    if($intIDEmployeeSpecial != "")
    {
      if($strID == ""){
      $strSQL = "INSERT INTO _hrd_overtime_max_allowance_special(created_by,created,id_employee, amount)
               VALUES ('$strmodified_byID',now(),$strIDEmployee,$intValueSpecial)";
    }
    else
      {
        $strSQL = "UPDATE _hrd_overtime_max_allowance_special SET id_employee = $strIDEmployee,
               amount = $intValueSpecial, modified_by = '$strmodified_byID', modified = now() WHERE id = $strID ";
      }
    }
    if($intIDEmployeeSpecial == "" && $intValueSpecial == "" && $strID != ""){
        $strSQL = "DELETE FROM _hrd_overtime_max_allowance_special WHERE id = $strID";
    }
    $db->execute($strSQL);
  }
}

function getInputHoliday($db)
{
  $intRows = 0;
  $intMaxDetail = 30;
  $strInputHoliday = "";
  $strSQL = "SELECT * FROM _hrd_overtime_duration_setting WHERE holiday = TRUE";
  $resExec = $db->execute($strSQL);
  while($rowDb=$db->fetchrow($resExec)){
    $strResult .= "<tr valign=top id='detailDataHoliday_$intRows'>\n";
    $strResult .= "<td nowrap><input type=hidden name=dataID_Holiday_$intRows value=".$rowDb['id']."></td>\n";
    $strResult .= "  <td><input type=text name=dataHolidayFrom_$intRows value=".$rowDb['duration_from']." size=15 maxlength=40 class='string-empty'> minutes</td>\n";
    $strResult .= "  <td>until <input type=text name=dataHolidayUntil_$intRows value=".$rowDb['duration_thru']." size=15 maxlength=40 > minutes:</td>\n";
    $strResult .= "  <td><input type=text name=dataHolidayValue_$intRows  value=".$rowDb['amount']." size=20 maxlength=20 class='string-empty' value=0></td>\n";
    $strResult .= "</tr>\n";
    $intRows++;
  }
  if($intRows == 0)
  {
    $strResult .= "<tr valign=top id='detailDataHoliday"."_$intRows'>\n";
    $strResult .= "  <td nowrap>&nbsp;</td>\n";
    $strResult .= "  <td><input type=text name=dataHolidayFrom"."_$intRows size=15 maxlength=40  class='string-empty'> minutes</td>\n";
    $strResult .= "  <td>until <input type=text name=dataHolidayUntil"."_$intRows size=15 maxlength=40 > minutes:</td>\n";
    $strResult .= "  <td><input type=text name=dataHolidayValue"."_$intRows  size=20 maxlength=20 class='string-empty' value=0></td>\n";
    $strResult .= "</tr>\n";
    $intRows++;
  }
  $intNumShow = $intRows;
  while ($intRows <= $intMaxDetail) {
    $strResult .= "<tr valign=top id='detailDataHoliday"."_$intRows' style=\"display:none\">\n";
    $strResult .= "  <td nowrap>&nbsp;</td>\n";
    $strResult .= "  <td><input type=text name=dataHolidayFrom"."_$intRows size=15 maxlength=40 disabled class='string-empty'> minutes</td>\n";
    $strResult .= "  <td>until <input type=text name=dataHolidayUntil"."_$intRows size=15 maxlength=40 disabled> minutes:</td>\n";
    $strResult .= "  <td><input type=text name=dataHolidayValue"."_$intRows  size=20 maxlength=20 class='string-empty' disabled value=0></td>\n";
    $strResult .= "</tr>\n";
    $intRows++;
  }
  $strResult .= "<input type=hidden name='numShowHoliday' value=$intNumShow>";
  $strResult .= "<input type=hidden name='maxDetailHoliday' value=$intMaxDetail>";
  return $strResult;
}

function getInputWorkday($db)
{
  $intRows = 0;
  $intMaxDetail = 30;
  $strResult = "";
  $strSQL = "SELECT * FROM _hrd_overtime_duration_setting WHERE holiday = FALSE";
  $resExec = $db->execute($strSQL);
  while($rowDb=$db->fetchrow($resExec)){
      $strResult .= "<tr valign=top id='detailDataWorkday_$intRows'>\n";
      $strResult .= "<td nowrap><input type=hidden name=dataID_Workday_$intRows value=".$rowDb['id']."></td>\n";
      $strResult .= "  <td><input type=text name=dataWorkdayFrom_$intRows value=".$rowDb['duration_from']." size=15 maxlength=40 class='string-empty'> minutes</td>\n";
      $strResult .= "  <td>until <input type=text name=dataWorkdayUntil_$intRows value=".$rowDb['duration_thru']." size=15 maxlength=40 > minutes  :</td>\n";
      $strResult .= "  <td><input type=text name=dataWorkdayValue_$intRows value=".$rowDb['amount']." size=20 maxlength=20 class='string-empty' value=0></td>\n";
      $strResult .= "</tr>\n";
      $intRows++;
    }
  if($intRows == 0)
  {
    $strResult .= "<tr valign=top id='detailDataWorkday"."_$intRows'>\n";
    $strResult .= "  <td nowrap>&nbsp;</td>\n";
    $strResult .= "  <td><input type=text name=dataWorkdayFrom_$intRows size=15 maxlength=40  class='string-empty'> minutes</td>\n";
    $strResult .= "  <td>until <input type=text name=dataWorkdayUntil_$intRows size=15 maxlength=40 > minutes  :</td>\n";
    $strResult .= "  <td><input type=text name=dataWorkdayValue_$intRows  size=20 maxlength=20 class='string-empty' value=0></td>\n";
    $strResult .= "</tr>\n";
    $intRows++;
  }
  $intNumShow = $intRows;
  while ($intRows <= $intMaxDetail) {
    $strResult .= "<tr valign=top id='detailDataWorkday"."_$intRows' style=\"display:none\">\n";
    $strResult .= "  <td nowrap>&nbsp;</td>\n";
    $strResult .= "  <td><input type=text name=dataWorkdayFrom_$intRows size=15 maxlength=40 disabled class='string-empty'> minutes</td>\n";
    $strResult .= "  <td>until <input type=text name=dataWorkdayUntil_$intRows size=15 maxlength=40 disabled> minutes  :</td>\n";
    $strResult .= "  <td><input type=text name=dataWorkdayValue_$intRows  size=20 maxlength=20 class='string-empty' disabled value=0></td>\n";
    $strResult .= "</tr>\n";
    $intRows++;
  }

  $strResult .= "<input type=hidden name='numShowWorkday' value=$intNumShow>";
  $strResult .= "<input type=hidden name='maxDetailWorkday' value=$intMaxDetail>";
  return $strResult;
}

function getInputListAll($db,$tipe)
{
  global $strEmptyOption;
  global $arrCompanyList,$arrManagementList,$arrDivisionList,$arrDepartmentList,$arrSectionList,$arrSubSectionList;
  $intRows = 0;
  $intMaxDetail = 50;
  $strResult = "";

  if($tipe == "overtime_duration")
    $strSQL = "SELECT * FROM _hrd_overtime_duration_department";
  elseif($tipe == "overtime_meal_workday")
    $strSQL = "SELECT * FROM _hrd_overtime_meal WHERE holiday = FALSE";
  elseif($tipe == "overtime_meal_holiday")
    $strSQL = "SELECT * FROM _hrd_overtime_meal WHERE holiday = TRUE";
  else
    $strSQL = "SELECT * FROM _hrd_overtime_max_allowance";

  $resExec = $db->execute($strSQL);
  while($rowDb = $db->fetchrow($resExec)){
    $strResult .= "<tr valign=top id=detailData$tipe"."_$intRows>\n";
    $strResult .= "<td nowrap><input type=hidden name=dataID_$tipe"."_$intRows value=".$rowDb['id']."></td>\n";
    $strResult .= "<td>".getComboFromArray($arrCompanyList,"dataCompany_$tipe"."_$intRows",$rowDb['id_company'], $strEmptyOption, " style=\"width:200 \" $strReadonly")."</td>";
    $strResult .= "<td>".getComboFromArray($arrManagementList,"dataManagement_$tipe"."_$intRows",$rowDb['management_code'], $strEmptyOption, " style=\"width:200 \" $strReadonly")."</td>";
    $strResult .= "<td>".getComboFromArray($arrDivisionList,"dataDivision_$tipe"."_$intRows",$rowDb['division_code'], $strEmptyOption, " style=\"width:200 \" $strReadonly onChange=\"checkDivision('$intRows','$tipe')\"")."</td>";
    $strResult .= "<td>".getComboFromArray($arrDepartmentList,"dataDepartment_$tipe"."_$intRows",$rowDb['department_code'], $strEmptyOption, " style=\"width:200 \" $strReadonly onChange=\"checkDepartment('$intRows','$tipe')\"")."</td>";
    $strResult .= "<td>".getComboFromArray($arrSectionList,"dataSection_$tipe"."_$intRows",$rowDb['section_code'], $strEmptyOption, " style=\"width:200 \" $strReadonly onChange=\"checkSection('$intRows','$tipe')\"")."</td>";
    $strResult .= "<td>".getComboFromArray($arrSubSectionList,"dataSubSection_$tipe"."_$intRows",$rowDb['sub_section_code'], $strEmptyOption, " style=\"width:200 \" $strReadonly onChange=\"checkSubSection('$intRows','$tipe')\"")."</td>";
    if($tipe == "overtime_meal_workday" || $tipe == "overtime_meal_holiday" || $tipe == "overtime_allowance")
    {
      $strResult .= "<td><input type=text name=dataValue_$tipe"."_$intRows size=10 maxlength=50 value=".$rowDb['amount']."></td>\n";
    }
    $strResult .="</tr>";
    $intRows++;
  }
  if($intRows == 0)
  {
    $strResult .= "<tr valign=top id=detailData$tipe"."_$intRows>\n";
    $strResult .= "<td nowrap>&nbsp;</td>\n";
    $strResult .= "<td>".getComboFromArray($arrCompanyList,"dataCompany_$tipe"."_$intRows", "", $strEmptyOption, "style=\"width:200 \" $strReadonly")."</td>";
    $strResult .= "<td>".getComboFromArray($arrManagementList,"dataManagement_$tipe"."_$intRows", "", $strEmptyOption, "style=\"width:200 \" $strReadonly ")."</td>";
    $strResult .= "<td>".getComboFromArray($arrDivisionList,"dataDivision_$tipe"."_$intRows", "", $strEmptyOption, "style=\"width:200 \" $strReadonly onChange=\"checkDivision('$intRows','$tipe')\"")."</td>";
    $strResult .= "<td>".getComboFromArray($arrDepartmentList,"dataDepartment_$tipe"."_$intRows", "", $strEmptyOption, "style=\"width:200 \" $strReadonly onChange=\"checkDepartment('$intRows','$tipe')\"")."</td>";
    $strResult .= "<td>".getComboFromArray($arrSectionList,"dataSection_$tipe"."_$intRows", "", $strEmptyOption, "style=\"width:200 \" $strReadonly onChange=\"checkSection('$intRows','$tipe')\"")."</td>";
    $strResult .= "<td>".getComboFromArray($arrSubSectionList,"dataSubSection_$tipe"."_$intRows", "", $strEmptyOption, "style=\"width:200 \" $strReadonly onChange=\"checkSubSection('$intRows','$tipe')\"")."</td>";
    if($tipe == "overtime_meal_workday" || $tipe == "overtime_meal_holiday" || $tipe == "overtime_allowance")
    {
      $strResult .= "<td><input type=text name=dataValue_$tipe"."_$intRows size=10 maxlength=50 value=0></td>\n";
    }
    $strResult .= "</tr>";
    $intRows++;
  }
  $intNumShow = $intRows;

  while ($intRows <= $intMaxDetail) {
    $strResult .= "<tr valign=top id=detailData$tipe"."_$intRows style=\"display:none\">\n";
    $strResult .= "<td nowrap>&nbsp;</td>\n";
    $strResult .= "<td>".getComboFromArray($arrCompanyList,"dataCompany_$tipe"."_$intRows", "", $strEmptyOption, "style=\"width:200 \" $strReadonly")."</td>";
    $strResult .= "<td>".getComboFromArray($arrManagementList,"dataManagement_$tipe"."_$intRows", "", $strEmptyOption, "style=\"width:200 \" $strReadonly")."</td>";
    $strResult .= "<td>".getComboFromArray($arrDivisionList,"dataDivision_$tipe"."_$intRows", "", $strEmptyOption, "style=\"width:200 \" $strReadonly onChange=\"checkDivision('$intRows','$tipe')\"")."</td>";
    $strResult .= "<td>".getComboFromArray($arrDepartmentList,"dataDepartment_$tipe"."_$intRows", "", $strEmptyOption, "style=\"width:200 \" $strReadonly onChange=\"checkDepartment('$intRows','$tipe')\"")."</td>";
    $strResult .= "<td>".getComboFromArray($arrSectionList,"dataSection_$tipe"."_$intRows", "", $strEmptyOption, "style=\"width:200 \" $strReadonly onChange=\"checkSection('$intRows','$tipe')\"")."</td>";
    $strResult .= "<td>".getComboFromArray($arrSubSectionList,"dataSubSection_$tipe"."_$intRows", "", $strEmptyOption, "style=\"width:200 \" $strReadonly onChange=\"checkSubSection('$intRows','$tipe')\"")."</td>";
    if($tipe == "overtime_meal_workday" || $tipe == "overtime_meal_holiday" || $tipe == "overtime_allowance")
    {
      $strResult .= "<td><input type=text name=dataValue_$tipe"."_$intRows size=10 maxlength=50 value=0></td>\n";
    }
    $strResult .= "</tr>";
    $intRows++;
  }
  $strResult .= "<input type=hidden name='numShow$tipe' value=$intNumShow>";
  $strResult .= "<input type=hidden name='maxDetail$tipe' value=$intMaxDetail>";
  return $strResult;
}

function getInputSpecial($db)
{
  $intRows = 0;
  $intMaxDetail = 20;
  $strResult = "";
  $strSQL = "SELECT t1.employee_id,t1.employee_name, t2.amount, t2.id
             FROM hrd_employee as t1
             JOIN _hrd_overtime_max_allowance_special as t2 ON t2.id_employee = t1.id";
  $resExec = $db->execute($strSQL);

  while($rowDb = $db->fetchrow($resExec)){
    $strResult .= "<tr valign=top id='detailDataSpecial_$intRows'>\n";
    $strResult .= "<td nowrap><input type=hidden name=dataID_Special"."_$intRows value=".$rowDb['id']."></td>\n";
    $strResult .= "  <td><input align=right type=text name=dataEmployeeSpecial_$intRows id=dataEmployeeSpecial_$intRows size=20 value=".$rowDb['employee_id']."></td>\n";
    $strResult .= "  <td><strong>".$rowDb['employee_name']."</strong></td>";
    $strResult .= "  <td>:<input type=text name=dataEmployeeSpecialValue_$intRows size=10 value=".$rowDb['amount']."></td>\n";
    $strResult .= "</tr>\n";
    $intRows++;
  }
  if($intRows == 0)
  {
    $strResult .= "<tr valign=top id='detailDataSpecial_$intRows'>\n";
    $strResult .= "  <td nowrap>&nbsp;</td>\n";
    $strResult .= "  <td><input align=right type=text name=dataEmployeeSpecial_$intRows id=dataEmployeeSpecial_$intRows size=20 ></td>\n";
    $strResult .= "  <td></td>";
    $strResult .= "  <td>:<input type=text name=dataEmployeeSpecialValue_$intRows size=10 value=0></td>\n";
    $strResult .= "</tr>\n";
    $intRows++;
  }
  $intNumShow = $intRows;

  while ($intRows <= $intMaxDetail) {
    $strResult .= "<tr valign=top id='detailDataSpecial_$intRows' style=\"display:none\">\n";
    $strResult .= "  <td nowrap>&nbsp;</td>\n";
    $strResult .= "  <td><input align=right type=text name=dataEmployeeSpecial_$intRows id=dataEmployeeSpecial_$intRows size=20 disabled></td>\n";
    $strResult .= "  <td></td>";
    $strResult .= "  <td>:<input type=text name=dataEmployeeSpecialValue_$intRows size=10 value=0 disabled></td>\n";
    $strResult .= "</tr>\n";
    $intRows++;
  }

  $strResult .= "<input type=hidden name='numShowSpecial' value=$intNumShow>";
  $strResult .= "<input type=hidden name='maxDetailSpecial' value=$intMaxDetail>";
  return $strResult;

}

  //----------------------------------------------------------------------

  //----MAIN PROGRAM -----------------------------------------------------
  $db = new CdbClass;
  if ($db->connect()) {
    if ($bolCanView) {
      if ($bolCanEdit) {
        if (isset($_REQUEST['btnSave'])) {
          $bolOK = saveData($db, $strError);
          if ($strError != "") {
            //echo "<script>alert(\"$strError\")</script>";
            $strMessages = $strError;
            $strMsgClass = ($bolOK) ? "class=bgOK" : "class=bgError";
          }
        }
      }

      getData($db);
      $strTransferStatus = getDataTransferStatus($db);
      $strBreakNormal = getDataBreak($db,0);
      $strBreakFriday = getDataBreak($db,1);
      $strBreakHoliday = getDataBreak($db,2);

      //tampilkan default baris pertama 0
      $strNormalID0             = $arrFirstData[0]['id'];
      $strFridayID0             = $arrFirstData[1]['id'];
      $strHolidayID0            = $arrFirstData[2]['id'];
      $strNormalBreak0          = $arrFirstData[0]['break'];
      $strFridayBreak0          = $arrFirstData[1]['break'];
      $strHolidayBreak0         = $arrFirstData[2]['break'];
      $strNormalDuration0       = $arrFirstData[0]['duration'];
      $strFridayDuration0       = $arrFirstData[1]['duration'];
      $strHolidayDuration0      = $arrFirstData[2]['duration'];
      $strNormalNote0           = $arrFirstData[0]['note'];
      $strFridayNote0           = $arrFirstData[1]['note'];
      $strHolidayNote0          = $arrFirstData[2]['note'];
      $strNormalFinish0         = $arrFirstData[0]['finish'];
      $strFridayFinish0         = $arrFirstData[1]['finish'];
      $strHolidayFinish0        = $arrFirstData[2]['finish'];
      $strTransferStatusID0     = $arrTransferStatusData['id'];
      $strTransferStatusCode0   = $arrTransferStatusData['code'];
      $strTransferStatusRemark0 = $arrTransferStatusData['remark'];

      //$strInputSalaryFrom = getDayList("strSalaryDateFrom",$arrSetting['strSalaryDateFrom']['value']);
      //$strInputSalaryThru = getDayList("strSalaryDateThru",$arrSetting['strSalaryDateThru']['value']);
      //$strInputDeptHead = getPositionList($db, "strDeptHead", $arrSetting['strDeptHead']['value'], $strEmptyOption);
      //$strInputGroupHead = getPositionList($db, "strGroupHead", $arrSetting['strGroupHead']['value'], $strEmptyOption);
      if (isset($arrSetting['rdGrouping']['value']))
      {
        $strChecked0 = "";
        $strChecked1 = "";
        if ($arrSetting['rdGrouping']['value'] == 0)
        {
          $strChecked0 = "checked";
        }
        else
        {
          $strChecked1 = "checked";
        }
      }
      $strInputGroupingShift = "<input type='radio' name='rdGrouping' value='0' $strChecked0>Group</input>
                                <br>
                                <input type='radio' name='rdGrouping' value='1' $strChecked1>Section</input>";
                                //print_r ($arrSetting['rdGrouping']['value']);
    } else {
      showError("view_denied");
      $strDataDetail = "";
    }

  }

  // tampilkan data
  $strCompanyName          = $arrSetting['strCompanyName']['value'];
  $oldCompanyName          = $arrSetting['strCompanyName']['value'];
  $strCompanyCode          = $arrSetting['strCompanyCode']['value'];
  $oldCompanyCode          = $arrSetting['strCompanyCode']['value'];
  $strCompanyBankAccountNo = $arrSetting['strCompanyBankAccountNo']['value'];
  $strCompanyBankAccountNo = $arrSetting['strCompanyBankAccountNo']['value'];
  $strStartTime            = $arrSetting['strStartTime']['value'];
  $oldStartTime            = $arrSetting['strStartTime']['value'];
  $strFinishTime           = $arrSetting['strFinishTime']['value'];
  $oldFinishTime           = $arrSetting['strFinishTime']['value'];
  $strFridayFinishTime     = $arrSetting['strFridayFinishTime']['value'];
  $oldFridayFinishTime     = $arrSetting['strFridayFinishTime']['value'];
  $strMaxOTMember          = $arrSetting['strMaxOTMember']['value'];
  $oldMaxOTMember          = $arrSetting['strMaxOTMember']['value'];
  $strLeaveMethod          = $arrSetting['strLeaveMethod']['value'];
  $oldLeaveMethod          = $arrSetting['strLeaveMethod']['value'];
  $strHCM                  = $arrSetting['strHCM']['value'];
  $oldHCM                  = $arrSetting['strHCM']['value'];
  $strJCI                  = $arrSetting['strJCI']['value'];
  $oldJCI                  = $arrSetting['strJCI']['value'];
  $strPCB                  = $arrSetting['strPCB']['value'];
  $oldPCB                  = $arrSetting['strPCB']['value'];
  $strJCB                  = $arrSetting['strJCB']['value'];
  $oldJCB                  = $arrSetting['strJCB']['value'];
  $strJCN                  = $arrSetting['strJCN']['value'];
  $oldJCN                  = $arrSetting['strJCN']['value'];
  $strMBCN                 = $arrSetting['strMBCN']['value'];
  $oldMBCN                 = $arrSetting['strMBCN']['value'];
  $strMBCB                 = $arrSetting['strMBCB']['value'];
  $oldMBCB                 = $arrSetting['strMBCB']['value'];
  $strAttendanceFilePath = $arrSetting['strAttendanceFilePath']['value'];
  $oldAttendanceFilePath = $arrSetting['strAttendanceFilePath']['value'];
  $strAttendanceFileType = $arrSetting['strAttendanceFileType']['value'];
  $oldAttendanceFileType = $arrSetting['strAttendanceFileType']['value'];
  $strSlipPersonnel = $arrSetting['strSlipPersonnel']['value'];
  $oldSlipPersonnel = $arrSetting['strSlipPersonnel']['value'];
  $strSlipFinance = $arrSetting['strSlipFinance']['value'];
  $oldSlipFinance = $arrSetting['strSlipFinance']['value'];
  $strMinAutoOT = $arrSetting['strMinAutoOT']['value'];
  $oldMinAutoOT = $arrSetting['strMinAutoOT']['value'];
  $strMaxAutoOT = $arrSetting['strMaxAutoOT']['value'];
  $oldMaxAutoOT = $arrSetting['strMaxAutoOT']['value'];
  if ($arrSetting['strSaturday']['value'] == 't') {
    $strSaturday = "checked";
    $oldSaturday = "t";
  } else {
    $strSaturday = "";
    $oldSaturday = "f";
  }
  if ($arrSetting['strProgressive']['value'] == 't') {
    $strProgressive = "checked";
    $oldProgressive = "t";
  } else {
    $strProgressive = "";
    $oldProgressive = "f";
  }

  $strSignature = $arrSetting['strSignature']['value'];

//  $strInputOvertimeDuration   = getInputListAll($db,'overtime_duration');
//  $strInputWorkdayMeal        = getInputListAll($db,'overtime_meal_workday');
//  $strInputHolidayMeal        = getInputListAll($db,'overtime_meal_holiday');
//  $strInputOvertimeAllowance  = getInputListAll($db,'overtime_allowance');
//  $strInputSpecial            = getInputSpecial($db);
//  $strInputWorkday            = getInputWorkday($db);
//  $strInputHoliday            = getInputHoliday($db);

  $tbsPage = new clsTinyButStrong ;

  //write this variable in every page
  $strPageTitle = getWords($dataPrivilege['menu_name']);
  $pageIcon = "../images/icons/".$dataPrivilege['icon_file'];
  $strTemplateFile = getTemplate(str_replace(".php", ".html", basename($_SERVER['PHP_SELF'])));
  //------------------------------------------------
  //Load Master Template
  $tbsPage->LoadTemplate($strMainTemplate) ;
  $tbsPage->Show() ;
?>
