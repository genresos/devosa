<?php
  include_once('../global/session.php');
  include_once('global.php');
  include_once('../includes/datagrid2/datagrid.php');
  include_once('../includes/form2/form2.php');
  include_once('../classes/hrd/hrd_position.php');
  include_once('../classes/hrd/hrd_employee.php');


  $dataPrivilege = getDataPrivileges(basename($_SERVER['PHP_SELF']), $bolCanView, $bolCanEdit, $bolCanDelete, $bolCanApprove, $bolCanCheck, $bolCanAcknowledge, $bolCanApprove2);
  if (!$bolCanView) die(accessDenied($_SERVER['HTTP_REFERER']));




  $db = new CdbClass;
  if ($db->connect())
  {

    $strDataID = getPostValue('dataID');
    $isNew = ($strDataID == "");
    $strSet = "position";
    $strDataApprover = (isset($_REQUEST['approver_id'])) ?  $_REQUEST['approver_id'] : $strDataApprover = "";

    if ($bolCanEdit)
    {
      $f = new clsForm("formInput", 1, "100%", "");
      $f->caption = strtoupper($strWordsINPUTDATA);

  //    $dataOrganizationStructure = $dataHrdOrganization->generateList(null, "levelling", null, "id", "name");

      $f->addHidden("dataID", $strDataID);
      $f->addLabel("","","","","",false,true,true, "<h3>General Position Info</h3>");
      $f->addInput(getWords("position code"), "position_code", "", array("size" => 25 , "maxlength" => 15), "string", true, true, true);
      $f->addInput(getWords("position name"), "position_name", "", array("size" => 100, "maxlength" => 63), "string", true, true, true);
      $f->addTextArea(getWords("note"), "note", "", array("cols" => 97, "rows" => 2), "string", false, true, true);
      $f->addSelect(getWords("position group"), "position_group", getDataListPositionGroup(), array(), "numeric", true, true, true);

      $f->addLabel("","","","","",false,true,true, "<br><h3>Overtime Setting by Position</h3>");
      $f->addSelect(getWords("get overtime"), "get_ot", getDataListGetOvertime(), array(), "numeric", true, true, true, "", "<small><br>Explanation: <br>- Full : Get Overtime without limit<br>- Half : Get Overtime with limit<br>- None : No overtime</small>");
      $f->addCheckBox(getWords("get auto overtime"), "get_auto_ot", false, array(), "string", false, true, true,"", "<small>If checked, overtime is automatically calculated if employee in this position work until late.</small>");
      $f->addCheckBox(getWords("overtime is hourly basis"), "is_hourly_basis", false, array(), "string", false, true, true,"", "<small>If checked, overtime is calculated by hourly rate.</small>");
      $f->addInput(getWords("overtime hourly rate (workday)"), "workday_hourly_rate", "0", array("size" => 25 , "maxlength" => 15), "numeric", true, true, true, "", "<small>In rupiah, change only if Overtime by Hourly Basis is checked</small>");
      $f->addInput(getWords("overtime hourly rate (holiday)"), "holiday_hourly_rate", "0", array("size" => 25 , "maxlength" => 15), "numeric", true, true, true, "", "<small>In rupiah, change only if Overtime by Hourly Basis is checked</small>");
      $f->addInput(getWords("overtime limit (value)"), "max_overtime_allowance", "0", array("size" => 25 , "maxlength" => 15), "numeric", true, true, true, "", "<small>Change only if Get Overtime Type is equal to Half</small>");
      $f->addCheckBox(getWords("overtime base if not by hourly rate is UMK"), "is_overtime_base_umk", false, array(), "string", false, true, true,"", "<small><font color=red>IMPORTANT!</font> If checked, Overtime Base if Overtime by Hourly Basis is the same as UMK by Branch.</small>");
      $f->addInput(getWords("overtime base if not by hourly rate"), "overtime_base", "0", array("size" => 25 , "maxlength" => 15), "numeric", true, true, true, "", "<small><font color=red>IMPORTANT!</font> If Overtime by Hourly Basis and Overtime Base if Overtime by Hourly Basis is the same as UMK are not checked, this field must be changed!</small>");

      $f->addLabel("","","","","",false,true,true, "<br><h3>BPJS Setting by Position</h3>");
      $f->addCheckBox(getWords("BPJS Ketenagakerjaan base by UMK"), "is_bpjs_tk_base_umk", false, array(), "string", false, true, true,"", "<small>If checked, BPJS Ketenagakerjaan Base is equal to UMK by Branch.</small>");
      $f->addInput(getWords("BPJS Ketenagakerjaan base if not by UMK"), "bpjs_tk_base", "0", array("size" => 25 , "maxlength" => 15), "numeric", true, true, true, "", "<small><font color=red>IMPORTANT!</font> Change only if BPJS Ketenagakerjaan base by UMK is not checked!</small>");
      $f->addCheckBox(getWords("BPJS Kesehatan base by UMK"), "is_bpjs_ks_base_umk", false, array(), "string", false, true, true,"", "<small>If checked, BPJS Kesehatan Base is equal to UMK by Branch.</small>");
      $f->addInput(getWords("BPJS Kesehatan base if not by UMK"), "bpjs_ks_base", "0", array("size" => 25 , "maxlength" => 15), "numeric", true, true, true, "", "<small><font color=red>IMPORTANT!</font> Change only if BPJS Kesehatan base by UMK is not checked!</small>");

      $f->addLabel("","","","","",false,true,true, "<br><h3>Workday Setting by Position</h3>");
      $f->addCheckBox(getWords("work all day (include saturday & sunday)"), "is_all_day", false, array(), "string", false, true, true,"", "<small>If checked, the employee is considered to work everyday. This setting overwrites the General Setting.</small>");
      $f->addCheckBox(getWords("work saturday in (include saturday)"), "is_sat_in", false, array(), "string", false, true, true,"", "<small>If checked, the employee is considered to also work on Saturday. This setting overwrites the General Setting.</small>");
      $f->addLabel("","","","","",false,true,true, "<br><h3>Late Setting by Position</h3>");
      $f->addCheckBox(getWords("late calculated by frequency ( T i division)"), "is_late_frequency", false, array(), "string", false, true, true,"", "<small>If checked, late calculation is calculated by the frequency of lateness, not total minutes of lateness.</small>");
      $f->addInput(getWords("minimum late minutes (late by frequency)"), "min_late_minutes", "0", array("size" => 25 , "maxlength" => 15), "numeric", true, true, true, "", "<small>In minutes, change only if Late by Frequency is checked</small>");
      $f->addInput(getWords("late by frequency deduction value per lateness"), "frequency_coefficient", "0", array("size" => 25 , "maxlength" => 15), "numeric", true, true, true, "", "<small>In rupiah, change only if Late by Frequency is checked</small>");
      $f->addLabel("","","","","",false,true,true, "<br><h3>Allowance Setting by Position</h3>");
      for ($i = 1; $i <= MAX_ALLOWANCE_SET; $i++)
        $f->addInput(getSetting($strSet.$i."_allowance_name"), $strSet.$i, "0",   array("size" => 30, "maxlength" => 10), "numeric", false, true, true);
			$f->addInputAutoComplete(getWords("approver ID"), "approver_id", getDataEmployee($strDataApprover), "style='width:250px' ", "string", false);
      $f->addLabelAutoComplete("", "approver_id", "");
      $f->addSubmit("btnSave", getWords("save"), array("onClick" => "javascript:myClient.confirmSave();"), true, true, "", "", "saveData()");
      $f->addButton("btnAdd", getWords("add new"), array("onClick" => "javascript:myClient.editData('0|position|3');"));

      $formInput = $f->render();
    }
    else
      $formInput = "";

    $myDataGrid = new cDataGrid("formData","DataGrid1");
    $myDataGrid->caption = strtoupper($strWordsLISTOF . " " . getWords($dataPrivilege['menu_name']));
    $myDataGrid->setAJAXCallBackScript(basename($_SERVER['PHP_SELF']));

    $myDataGrid->addColumnCheckbox(new DataGrid_Column("chkID", "position_code", array('width' => '30'), array('align'=>'center', 'nowrap' => '')));
    $myDataGrid->addColumnNumbering(new DataGrid_Column(getWords("no."), "", array('width'=>'30'), array('nowrap'=>'')));
    $myDataGrid->addColumn(new DataGrid_Column(getWords("position code"), "position_code", array('width' => '130'),array('nowrap' => '')));
    $myDataGrid->addColumn(new DataGrid_Column(getWords("position name"), "position_name", array('width' => '200'), array('nowrap' => '')));
    $myDataGrid->addColumn(new DataGrid_Column(getWords("position group"), "position_group", array("width" => 100), array('align' => 'center'), true, true, "", "printPositionGroup()"));
    $myDataGrid->addColumn(new DataGrid_Column(getWords("get overtime"), "get_ot", array("width" => 30), array('align' => 'center'), true, true, "", "printGetOvertime()"));
    $myDataGrid->addColumn(new DataGrid_Column(getWords("get auto overtime"), "get_auto_ot", array("width" => 70), array('align' => 'center'), true, false, "", "printAutoOT()"));
    $myDataGrid->addColumn(new DataGrid_Column(getWords("overtime is hourly basis"), "is_hourly_basis", array("width" => 70), array('align' => 'center'), true, false, "", "printAutoOT()"));
    $myDataGrid->addColumn(new DataGrid_Column(getWords("overtime hourly rate (workday)"), "workday_hourly_rate", array("width" => 70), array('align' => 'center'), true, false, ""));
    $myDataGrid->addColumn(new DataGrid_Column(getWords("overtime hourly rate (holiday)"), "holiday_hourly_rate", array("width" => 70), array('align' => 'center'), true, false, ""));
    $myDataGrid->addColumn(new DataGrid_Column(getWords("overtime limit (value)"), "max_overtime_allowance", array("width" => 70), array('align' => 'center'), true, false, ""));

    $myDataGrid->addColumn(new DataGrid_Column(getWords("overtime base if not by hourly rate is UMK"), "is_overtime_base_umk", array("width" => 70), array('align' => 'center'), true, false, "", "printAutoOT()"));
    $myDataGrid->addColumn(new DataGrid_Column(getWords("overtime base if not by hourly rate"), "overtime_base", array("width" => 70), array('align' => 'center'), true, false, ""));
    $myDataGrid->addColumn(new DataGrid_Column(getWords("BPJS Ketenagakerjaan base by UMK"), "is_bpjs_tk_base_umk", array("width" => 70), array('align' => 'center'), true, false, "", "printAutoOT()"));
    $myDataGrid->addColumn(new DataGrid_Column(getWords("BPJS Ketenagakerjaan base if not by UMK"), "bpjs_tk_base", array("width" => 70), array('align' => 'center'), true, false, ""));
    $myDataGrid->addColumn(new DataGrid_Column(getWords("BPJS Kesehatan base by UMK"), "is_bpjs_ks_base_umk", array("width" => 70), array('align' => 'center'), true, false, "", "printAutoOT()"));
    $myDataGrid->addColumn(new DataGrid_Column(getWords("BPJS Kesehatan base if not by UMK"), "bpjs_ks_base", array("width" => 70), array('align' => 'center'), true, false, ""));

    $myDataGrid->addColumn(new DataGrid_Column(getWords("work all day (include saturday & sunday)"), "is_all_day", array('width' => ''), array('nowrap' => ''), true, false, "", "printAutoOT()"));
    $myDataGrid->addColumn(new DataGrid_Column(getWords("work Saturday (include saturday)"), "is_sat_in", array('width' => ''), array('nowrap' => ''), true, false, "", "printAutoOT()"));
    $myDataGrid->addColumn(new DataGrid_Column(getWords("late calculated by frequency ( T i division)"), "is_late_frequency", array('width' => ''), array('nowrap' => ''), true, false, "", "printAutoOT()"));
    $myDataGrid->addColumn(new DataGrid_Column(getWords("minimum late minutes (late by frequency)"), "min_late_minutes", array('width' => ''), array('nowrap' => ''), true, false, "", ""));
    $myDataGrid->addColumn(new DataGrid_Column(getWords("late by frequency multiplier (value)"), "frequency_coefficient", array('width' => ''), array('nowrap' => ''), true, false, "", ""));
    for ($i = 1; $i <= MAX_ALLOWANCE_SET; $i++)
      $myDataGrid->addColumn(new DataGrid_Column(getSetting($strSet.$i."_allowance_name"), $ARRAY_ALLOWANCE_SET[$strSet]['field_name'].$i, array('width' => '80'), array('align' => 'right'), true, true, "", "formatNumber()"));
		$myDataGrid->addColumn(new DataGrid_Column(getWords("approver ID"), "approver_id", array('width' => '200'), array('nowrap' => '')));

    $myDataGrid->addColumn(new DataGrid_Column(getWords("note"), "note", null, array('nowrap' => '')));


    if ($bolCanEdit)
      $myDataGrid->addColumn(new DataGrid_Column("", "", array('width' => '60'), array('align' => 'center', 'nowrap' => ''), false, false, "","printEditLink()", "", false /*show in excel*/));
    if ($bolCanDelete)
    $myDataGrid->addSpecialButton("btnDelete","btnDelete","submit",getWords("delete"),"onClick=\"javascript:return myClient.confirmDelete();\"","deleteData()");

    //tampilkan buttons sesuai dengan otoritas, common_function.php
    generateRoleButtons($bolCanEdit, $bolCanDelete, $bolCanCheck, $bolCanApprove, $bolCanAcknowledge, false, $myDataGrid);

    $myDataGrid->addButtonExportExcel(getWords("export excel"), $dataPrivilege['menu_name'].".xls", getWords($dataPrivilege['menu_name']));

    $myDataGrid->getRequest();
    //--------------------------------
    //get Data and set to Datagrid's DataSource by set the data binding (bind method)
    $strSQLCOUNT  = "SELECT COUNT(*) AS total FROM hrd_position ";
    $strSQL       = "SELECT * FROM hrd_position ";

    $myDataGrid->totalData = $myDataGrid->getTotalData($db, $strSQLCOUNT);
    $dataset = $myDataGrid->getData($db, $strSQL);

    //bind Datagrid with array dataset
    $myDataGrid->bind($dataset);
    $DataGrid = $myDataGrid->render();


    $strConfirmSave = getWords("do you want to save this entry?");

  }
  $tbsPage = new clsTinyButStrong ;

  //write this variable in every page
  $strPageTitle = getWords($dataPrivilege['menu_name']);
  $pageIcon = "../images/icons/".$dataPrivilege['icon_file'];
  $strTemplateFile = getTemplate(str_replace(".php", ".html", basename($_SERVER['PHP_SELF'])));
  //------------------------------------------------
  //Load Master Template
  $tbsPage->LoadTemplate("../templates/master.html") ;
  $tbsPage->Show() ;
//--------------------------------------------------------------------------------

  function printEditLink($params)
  {
    global $ARRAY_ALLOWANCE_SET, $strSet;
    $strResult = "";
    extract($params);
    for ($i = 1; $i <= MAX_ALLOWANCE_SET; $i++)
      $strResult .= "<input type=hidden name='detailAllowance".$i."_$counter' id='detailAllowance".$i."_$counter' value='".$record[$ARRAY_ALLOWANCE_SET[$strSet]['field_name'].$i]."' />";
    return "
      <input type=hidden name='detailID$counter' id='detailID$counter' value='".$record['position_code']."' />
      <input type=hidden name='detailCode$counter' id='detailCode$counter' value='".$record['position_code']."' />
      <input type=hidden name='detailName$counter' id='detailName$counter' value='".$record['position_name']."' />
      <input type=hidden name='detailNote$counter' id='detailNote$counter' value='".$record['note']."' />
      <input type=hidden name='detailPositionGroup$counter' id='detailPositionGroup$counter' value='".$record['position_group']."' />
      <input type=hidden name='detailGetOvertime$counter' id='detailGetOvertime$counter' value='".$record['get_ot']."' />
      <input type=hidden name='detailGetAutoOvertime$counter' id='detailGetAutoOvertime$counter' value='".$record['get_auto_ot']."' />
      <input type=hidden name='detailIsHourlyBasis$counter' id='detailIsHourlyBasis$counter' value='".$record['is_hourly_basis']."' />
      <input type=hidden name='detailWorkdayHourlyRate$counter' id='detailWorkdayHourlyRate$counter' value='".$record['workday_hourly_rate']."' />
      <input type=hidden name='detailMaxOvertimeAllowance$counter' id='detailMaxOvertimeAllowance$counter' value='".$record['max_overtime_allowance']."' />
      <input type=hidden name='detailHolidayHourlyRate$counter' id='detailHolidayHourlyRate$counter' value='".$record['holiday_hourly_rate']."' />
      <input type=hidden name='detailIsAllDay$counter' id='detailIsAllDay$counter' value='".$record['is_all_day']."' />
      <input type=hidden name='detailIsSatIn$counter' id='detailIsSatIn$counter' value='".$record['is_sat_in']."' />
      <input type=hidden name='detailIsLateFrequency$counter' id='detailIsLateFrequency$counter' value='".$record['is_late_frequency']."' />
      <input type=hidden name='detailMinLateFrequency$counter' id='detailMinLateFrequency$counter' value='".$record['min_late_minutes']."' />
      <input type=hidden name='detailCoefficientLateFrequency$counter' id='detailCoefficientLateFrequency$counter' value='".$record['frequency_coefficient']."' />
      <input type=hidden name='detailIsOvertimeBaseByUMK$counter' id='detailIsOvertimeBaseByUMK$counter' value='".$record['is_overtime_base_umk']."' />
      <input type=hidden name='detailOvertimeBase$counter' id='detailOvertimeBase$counter' value='".$record['overtime_base']."' />
      <input type=hidden name='detailIsBPJSKetenagakerjaanBaseByUMK$counter' id='detailIsBPJSKetenagakerjaanBaseByUMK$counter' value='".$record['is_bpjs_tk_base_umk']."' />
      <input type=hidden name='detailBPJSKetenagakerjaanBase$counter' id='detailBPJSKetenagakerjaanBase$counter' value='".$record['bpjs_tk_base']."' />
      <input type=hidden name='detailIsBPJSKesehatanBaseByUMK$counter' id='detailIsBPJSKesehatanBaseByUMK$counter' value='".$record['is_bpjs_ks_base_umk']."' />
      <input type=hidden name='detailBPJSKesehatanBase$counter' id='detailBPJSKesehatanBase$counter' value='".$record['bpjs_ks_base']."' />

      <input type=hidden name='detailApproverID$counter' id='detailApproverID$counter' value='".$record['approver_id']."' />
      <a href=\"javascript:myClient.editData('$counter"."|$strSet|".MAX_ALLOWANCE_SET."')\">" .getWords('edit'). "</a>".$strResult;
  }

  // fungsi untuk menyimpan data
  function saveData()
  {
    global $f;
    global $db;
    global $error;
    global $isNew;
    global $strSet;
    global $ARRAY_ALLOWANCE_SET;
    $strDataCode = $f->getValue('position_code');

    $dataHrdPosition = new cHrdPosition();
    $dataHrdEmployee = new cHrdEmployee();
    $data = array("position_code" => $strDataCode,
                  "position_name" => $f->getValue('position_name'),
                  "position_group" => ($f->getValue('position_group') == '') ? null :   intval($f->getValue('position_group')),
                  "note" => $f->getValue('note'),
									"approver_id" => $f->getValue('approver_id'),
                  "get_ot" => $f->getValue('get_ot'),
                  "get_auto_ot" => (($f->getValue('get_auto_ot')) ? 't' : 'f'),
                  "is_hourly_basis" => (($f->getValue('is_hourly_basis')) ? 't' : 'f'),
                  "workday_hourly_rate" => $f->getValue('workday_hourly_rate'),
                  "max_overtime_allowance" => $f->getValue('max_overtime_allowance'),
                  "is_all_day" => (($f->getValue('is_all_day')) ? 't' : 'f'),
                  "is_sat_in" => (($f->getValue('is_sat_in')) ? 't' : 'f'),
                  "is_late_frequency" => (($f->getValue('is_late_frequency')) ? 't' : 'f'),
                  "holiday_hourly_rate" => $f->getValue('holiday_hourly_rate'),
                  "min_late_minutes" => $f->getValue('min_late_minutes'),
                  "frequency_coefficient" => $f->getValue('frequency_coefficient'),
                  "is_overtime_base_umk" => (($f->getValue('is_overtime_base_umk')) ? 't' : 'f'),
                  "overtime_base" =>$f->getValue('overtime_base'),
                  "is_bpjs_tk_base_umk" => (($f->getValue('is_bpjs_tk_base_umk')) ? 't' : 'f'),
                  "bpjs_tk_base" => $f->getValue('bpjs_tk_base'),
                  "is_bpjs_ks_base_umk" => (($f->getValue('is_bpjs_ks_base_umk')) ? 't' : 'f'),
                  "bpjs_ks_base" => $f->getValue('bpjs_ks_base')
                  );
    for ($i = 1; $i <= MAX_ALLOWANCE_SET; $i++)
      $data[$ARRAY_ALLOWANCE_SET[$strSet]['field_name'].$i] = $f->getValue($strSet.$i);

    $data2 = array("position_code" => $f->getValue('position_code'));

    // simpan data -----------------------
    $bolSuccess = false;

    // data baru
    if ($isNew)
    {
      if (isDataExists($db, $ARRAY_ALLOWANCE_SET[$strSet]['table_name'], $strSet."_code", $strDataCode))
        $f->message = $error['duplicate_code']. " of $strSet -> $strDataCode";
      $bolSuccess = $dataHrdPosition->insert($data);
    }
    else
    {
      $bolSuccess = $dataHrdPosition->update(/*pk*/"position_code='".$f->getValue('dataID')."'", /*data to update*/ $data);
      $dataHrdEmployee->update(/*pk*/"position_code='".$f->getValue('dataID')."'", /*data to update*/ $data2);
    }
    if ($bolSuccess)
    {
      $f->setValue('dataID', $data['position_code']);
      $f->message = $dataHrdPosition->strMessage;
    }
    $f->msgClass = ($bolSuccess) ? "bgOK" : "bgError";
  } // saveData

  // fungsi untuk menghapus data
  function deleteData()
  {
    global $myDataGrid;

    $arrKeys = array();
    foreach ($myDataGrid->checkboxes as $strValue)
      $arrKeys['position_code'][] = $strValue;

    $dataHrdPosition = new cHrdPosition();
    $dataHrdPosition->deleteMultiple($arrKeys);

    $myDataGrid->message = $dataHrdPosition->strMessage;
  } //deleteData

  // fungsi untuk menampilkan group posisi
  function getDataListPositionGroup($default = null, $isHasEmpty = false, $emptyData = null)
  {
    global $ARRAY_POSITION_GROUP;
    $arrData = array();
    if ($isHasEmpty) $arrData[] = $emptyData;
    foreach($ARRAY_POSITION_GROUP as $key => $value)
    {
      if ($key == $default)
        $arrData[] = array("value" => $key, "text" => getWords($value), "selected" => true);
      else
        $arrData[] = array("value" => $key, "text" => getWords($value), "selected" => false);
    }
    return $arrData;
  }
  function getDataListGetOvertime($default = 1, $isHasEmpty = false, $emptyData = false)
  {
    global $ARRAY_GET_OT;
    $arrData = array();
    if ($isHasEmpty) $arrData[] = $emptyData;
    foreach($ARRAY_GET_OT as $key => $value)
    {
      if ($key == $default)
        $arrData[] = array("value" => $key, "text" => getWords($value), "selected" => true);
      else
        $arrData[] = array("value" => $key, "text" => getWords($value), "selected" => false);
    }
    return $arrData;
  }

  function printAutoOT($params)
  {
    extract($params);
    if ($value == 't')
      return "Yes";
    else
      return "No";
  }

  // print Position Group
  function printPositionGroup($params)
  {
    global $ARRAY_POSITION_GROUP;
    extract($params);
    if ($record['position_group'] == "" )
      return "";
    else
      return getWords($ARRAY_POSITION_GROUP[$record['position_group']]);
  }  // print Position Group
/*  function printFormatBasicSalary($params)
  {
    extract($params);
    return number_format($record['basic_salary']);
  }
  function printFormatSeniorityAllowance($params)
  {
    extract($params);
    return number_format($record['seniority_allowance']);
  }
  function printFormatHousingAllowance($params)
  {
    extract($params);
    return number_format($record['housing_allowance']);
  }
    function printIsLeader($params)
  {
    extract($params);
    if ($value == SQL_TRUE)
      return "*";
    else
      return "";
  }*/

  function printGetOvertime($params)
  {
    global $ARRAY_GET_OT;
    extract($params);
    if ($record['get_ot'] == "" )
      return "";
    else
      return getWords($ARRAY_GET_OT[$record['get_ot']]);
  }

?>
