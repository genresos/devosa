<?php
  include_once('../global/session.php');
  include_once('global.php');
  include_once('../global/common_data.php');
  include_once('../global/employee_function.php');
  include_once('../includes/datagrid2/datagrid.php');
  include_once('../includes/form2/form2.php');
  include_once('../classes/hrd/hrd_absence_partial.php');
  include_once('../classes/hrd/hrd_attendance.php');


  $dataPrivilege = getDataPrivileges(basename($_SERVER['PHP_SELF']), $bolCanView, $bolCanEdit, $bolCanDelete, $bolCanApprove, $bolCanCheck, $bolCanAcknowledge, $bolCanApprove2);
  if (!$bolCanView) die(accessDenied($_SERVER['HTTP_REFFERER']));

  $strWordsEntryAbsence               = getWords ("entry absence");
  $strWordsAbsenceList                = getWords ("absence list");
  $strWordsEntryPartialAbsence        = getWords ("entry partial absence");
  $strWordsPartialAbsenceList         = getWords ("partial absence list");
  $strWordsAnnualLeave                = getWords ("annual Leave");
  $strWordsAbsenceSlip                = getWords ("absence slip");

  $db = new CdbClass;

  if (isset($_REQUEST['ajax']) && $_REQUEST['ajax'] == "getAttendance")
  {
      //$ajEmployee = str_replace("0", "a", $_REQUEST['ajEmployee']);
     $db->connect();
     //echo $ajEmployee;
     $ajEmployee = getIDEmployee($db, $_REQUEST['ajEmployee']);
     echo getAttendance($db, $_REQUEST['ajDate'], $ajEmployee , $_REQUEST['ajType']);
     exit();
  }
  function getAttendance($db, $ajDate, $ajEmployee, $ajType)
  {
     $tblAttendance = new cHrdAttendance;
     $data = $tblAttendance->find("attendance_date = '$ajDate' AND id_employee = $ajEmployee", "attendance_start, attendance_finish, normal_start, normal_finish", "");

     if ($ajType == 0)
       return "0|".substr($data['normal_start'],0,5)."|".substr($data['attendance_start'],0,5)."|(".getWords("normal start").")|(". getWords("attendance start").")";
     else if ($ajType == 1)
       return "||(".getWords("out start").")|(". getWords("out finish").")";
     else if ($ajType == 2)
       return substr($data['attendance_finish'],0,5)."|".substr($data['normal_finish'],0,5)."|(".getWords("attendance finish").")|(". getWords("normal finish").")";
  }

  if ($db->connect())
  {
    getUserEmployeeInfo();

    $strDataID = getRequestValue('dataID');
    if($strDataID != "" )
      $arrData = getDataByID($strDataID);
    else
    {
      $arrData['dataDate']        = (getPostValue('dataDate') != "") ? getPostValue('dataDate') : date("Y-m-d");
      $arrData['dataType']        = (getPostValue('dataType') != "") ? getPostValue('dataType') : PARTIAL_ABSENCE_LATE;
      $arrData['dataEmployee']    = getPostValue('dataEmployee');
      $arrData['dataStartTime']   = (getPostValue('dataStartTime') != "") ? getPostValue('dataDateFrom') : substr(getSetting("start_time"),0,5);
      $arrData['dataFinishTime']  = (getPostValue('dataFinishTime') != "") ? getPostValue('dataFinishTime') :  substr(getNextMinute(getSetting("start_time"),30),0,5);
      $arrData['dataDuration']    = getIntervalHour($arrData['dataStartTime'], $arrData['dataFinishTime']);
      $arrData['dataApprovedDuration'] = (getPostValue('dataApprovedDuration') != "") ? getPostValue('dataApprovedDuration') : 0;
      $arrData['dataNote']        = getPostValue('dataNote');
    }
    $strDataDate               = $arrData['dataDate'];
    $strDataType               = $arrData['dataType'];
    $strDataEmployee           = $arrData['dataEmployee'];
    $strDataStartTime          = $arrData['dataStartTime'];
    $strDataFinishTime         = $arrData['dataFinishTime'];
    $strDataDuration           = minuteToTime($arrData['dataDuration']);
    $strDataApprovedDuration   = ($arrData['dataApprovedDuration'] == 0) ? "00:00" : minuteToTime($arrData['dataApprovedDuration']);
    $strDataNote               = $arrData['dataNote'];

    $isNew = ($strDataID == "");
    $strReadonly = (scopeGeneralDataEntry($strDataEmployee, $_SESSION['sessionUserRole'], $arrUserInfo)) ? "" : "" ;
    if ($bolCanEdit)
    {
      $f = new clsForm("formInput", 1, "100%", "");
      $f->caption = strtoupper($strWordsINPUTDATA);

      $f->addHidden("dataID", $strDataID);
      $f->addInputAutoComplete(getwords("n i k"), "dataEmployee", getDataEmployee($strDataEmployee), "style='width:250px' ". $strReadonly, "string", true);
      $f->addLabelAutoComplete("", "dataEmployee", "");

      $f->addInput(getWords("date"), "dataDate", $strDataDate, array("style" => "width:$strDateWidth"), "date", true, true, true);
      $f->addSelect(getWords("partial absence type"), "dataType", getDataListPartialAbsenceType($strDataType), array("style" => "width:$strDateWidth"), "", true, true, true, "", generateButton("btnGetInfo", getWords("get attendance info"), "", "onClick=\"getAttendanceInfo()\""));
      $f->addInput(getWords("start time"), "dataStartTime", $strDataStartTime, array("size" => 10, "maxlength" => 5, "class" => "t input_mask mask_time", "onBlur" => "setDuration();"), "string", false, true, true, "", generateLabel("labelStart", "", ""));
      $f->addInput(getWords("finish time"), "dataFinishTime",$strDataFinishTime, array("size" => 10, "maxlength" => 5, "class" => "t input_mask mask_time", "onBlur" => "setDuration();"), "string", false, true, true, "", generateLabel("labelFinish", "", ""));
      $f->addInput(getWords("duration"), "dataDuration", $strDataDuration, array("readonly" => "true","size" => 10, "maxlength" => 5, "class" => "t input_mask mask_time"), "string", false, true, true);
      $f->addInput(getWords("approved duration"), "dataApprovedDuration", $strDataApprovedDuration, array("size" => 10, "maxlength" => 5, "class" => "t input_mask mask_time", $strReadonly), "string", false, true, true);

      $f->addTextArea(getWords("note"), "dataNote", $strDataNote, array("cols" => 48, "rows" => 2, "maxlength" => 127), "string", false, true, true);

      $f->addSubmit("btnSave", getWords("save"), "", true, true, "", "", "saveData()");
      $f->addButton("btnAdd", getWords("add new"), array("onClick" => "location.href = 'absence_partial_edit.php'"));

      $formInput = $f->render();
    }
    else
      $formInput = "";
  }

  $strConfirmSave = getWords("do you want to save this entry?");


  $tbsPage = new clsTinyButStrong ;

  //write this variable in every page
  $strPageTitle = $dataPrivilege['menu_name'];
  $pageIcon = "../images/icons/".$dataPrivilege['icon_file'];
  $strTemplateFile = getTemplate(str_replace(".php", ".html", basename($_SERVER['PHP_SELF'])));
  //------------------------------------------------
  //Load Master Template
  $tbsPage->LoadTemplate($strMainTemplate) ;
  $tbsPage->Show() ;
//--------------------------------------------------------------------------------

  function getDataByID($strDataID)
  {
    global $db;
    $tblAbsencePartial = new cHrdAbsencePartial();
    $arrTrip = $tblAbsencePartial ->findAll("id = $strDataID", "", "", null, 1, "id");
    $arrTemp = getEmployeeInfoByID($db, $arrTrip[$strDataID]['id_employee'], "employee_id");
    $arrResult['dataEmployee']   = $arrTemp['employee_id'];
    $arrResult['dataDate']       = $arrTrip[$strDataID]['partial_absence_date'];
    $arrResult['dataType']       = $arrTrip[$strDataID]['partial_absence_type'];
    $arrResult['dataStartTime']  = $arrTrip[$strDataID]['start_time'];
    $arrResult['dataFinishTime'] = $arrTrip[$strDataID]['finish_time'];
    $arrResult['dataDuration']   = $arrTrip[$strDataID]['duration'];
    $arrResult['dataApprovedDuration'] = $arrTrip[$strDataID]['approved_duration'];
    $arrResult['dataNote']       = $arrTrip[$strDataID]['note'];

    //foreach($arrTripCost[$arrTrip['trip_type']
    //g78
    return $arrResult;
  }



  // fungsi untuk menyimpan data
  function saveData()
  {
    global $f;
    global $db;
    global $isNew;

    $strmodified_byID = $_SESSION['sessionUserID'];
    $strIDEmployee = getIDEmployee($db, $f->getValue('dataEmployee'));
    $strDate = $f->getValue('dataDate');
    $strType = $f->getValue('dataType');

    $dataAbsencePartial = new cHrdAbsencePartial();
    $data = array("partial_absence_date" => $strDate,
                  "partial_absence_type" => $strType,
                  "id_employee" => $strIDEmployee,
                  "start_time" => $f->getValue('dataStartTime'),
                  "finish_time" => $f->getValue('dataFinishTime'),
                  "duration" => getIntervalHour("00:00", $f->getValue('dataDuration')),
                  "approved_duration" => getIntervalHour("00:00", $f->getValue('dataApprovedDuration')),
                  "note" => pg_escape_string($f->getValue('dataNote')));
    // simpan data -----------------------
    $bolSuccess = false;
    if ($isNew)
    {
      // data baru
      $dataExisting = $dataAbsencePartial->find("partial_absence_date = '$strDate' AND partial_absence_type = '$strType' AND id_employee = '$strIDEmployee'", "id", "");
      if (count($dataExisting) == 0)
        $bolSuccess = $dataAbsencePartial->insert($data);
      else
      {
        $bolSuccess = false;
      }
    }
    else
    {
      $bolSuccess = $dataAbsencePartial->update("id='".$f->getValue('dataID')."'", $data);
    }
    if ($bolSuccess)
    {
      if ($isNew)
        $f->setValue('dataID', $dataAbsencePartial->getLastInsertId());
      else
        $f->setValue('dataID', $f->getValue('dataID'));
      $f->message = $dataAbsencePartial->strMessage ;
    }
    else
    {
      $f->message = "Duplicated data. Please check employee, date, and absence type." ;
      $f->msgClass = "bgError";
    }
  } // saveData

  // fungsi untuk menghapus data
  function deleteData()
  {
    global $myDataGrid;

    $arrKeys = array();
    foreach ($myDataGrid->checkboxes as $strValue)
      $arrKeys['code'][] = $strValue;

    $dataAbsencePartial = new cHrdAbsencePartial();
    $dataAbsencePartial->deleteMultiple($arrKeys);

    $myDataGrid->message = $dataAbsencePartial->strMessage;
  } //deleteData

?>
