<?php
  include_once('../global/session.php');
  include_once('global.php');
  include_once('../includes/datagrid2/datagrid.php');
  include_once('../includes/form2/form2.php');
  include_once('../classes/hrd/hrd_absence_type.php');
  include_once('../classes/hrd/hrd_deduction_type.php');
  include_once('../classes/hrd/hrd_absence_deduction.php');


  $dataPrivilege = getDataPrivileges(basename($_SERVER['PHP_SELF']), $bolCanView, $bolCanEdit, $bolCanDelete, $bolCanApprove, $bolCanCheck, $bolCanAcknowledge, $bolCanApprove2);
  if (!$bolCanView) die(getWords('view denied'));

  $db = new CdbClass;
  $db->connect();
  $tblDeductionType = new cHrdDeductionType();
  $dataDeductionType = $tblDeductionType->findAll("active='t' and daily='t'", "code, name", "", null, 1, "code");

  $strDataID = getPostValue('dataID');
  $isNew = ($strDataID == "");
  $strDeductions = "";

  if ($bolCanEdit)
  {
    $f = new clsForm("formInput", 2, "100%", "");
    $f->caption = strtoupper(vsprintf(getWords("input data %s"), getWords("absence type")));

    $f->addHidden("dataID", $strDataID);
    $f->addInput(getWords("code"), "dataCode", "", array("size" => 20, "maxlength" => 20), "string", true, true, true);
    $f->addTextArea(getWords("note"), "dataNote", "", array("cols" => 68, "rows" => 2), "string", false, true, true,"", "");
    //$f->addCheckBox(getWords("full absence"), "dataFullAbsence", false, array(), "string", false, true, true,"", "");
    $f->addCheckBox(getWords("leave"), "dataIsLeave", false, null, "string", false, true, true,"", "");
    $f->addCheckBox(getWords("deduct annual leave"), "dataDeductLeave", false, array(), "string", false, true, true,"", "");
    $f->addCheckBox(getWords("deduct Additional leave"), "dataDeductAddLeave", false, array(), "string", false, true, true,"", "");
    $f->addCheckBox(getWords("cancel partial absence"), "dataCancelPartialAbsence", false, array(), "string", false, true, true,"", "<br>&nbsp;<br>");
    $f->addCheckBox(getWords("treated as attend (tipe absen ini dianggap hadir)"), "dataAttend", false, array(), "string", false, true, true,"", "");
    $f->addInput(getWords("leave weight"), "dataLeaveWeight", "0", array("size" => 10, "maxlength" => 5), "numeric", true, true, true);

    foreach($dataDeductionType as $strCode => $arrDeduction)
    {
      $f->addCheckBox(getWords("deducts ".$arrDeduction['name']), "data".$strCode, false, array(), "string", false, true, true,"", "<br>&nbsp;<br>");
      $strDeductions .="|$strCode";
    }

    $f->addSubmit("btnSave", getWords("save"), array("onClick" => "return confirm('".getWords('do you want to save this entry?')."');"), true, true, "", "", "saveData()");
//    $f->addButton("btnAdd", getWords("add new"), array("onClick" => "javascript:myClient.editData('0".$strDeductions.");"));
      $f->addButton("btnAdd", getWords("add new"), array("onClick" => "javascript:myClient.editData('0|functional|3');"));

    $formInput = $f->render();
  }
  else
    $formInput = "";

  $myDataGrid = new cDataGrid("formData","DataGrid1");
  $myDataGrid->caption = getWords(strtoupper(vsprintf(getWords("list of %s"), getWords("absence type"))));
  $myDataGrid->setAJAXCallBackScript(basename($_SERVER['PHP_SELF']));
  if(!isset($_REQUEST['btnExportXLS']))
  $myDataGrid->addColumnCheckbox(new DataGrid_Column("chkID", "code", array('width' => '30'), array('align'=>'center', 'nowrap' => '')));

  $myDataGrid->addColumnNumbering(new DataGrid_Column(getWords("no."), "", array('width'=>'30'), array('nowrap'=>'')));

  $myDataGrid->addColumn(new DataGrid_Column(getWords("code"), "code", array('width' => '100'), array('nowrap' => '')));

  $myDataGrid->addColumn(new DataGrid_Column(getWords("note"), "note", null, array('nowrap' => '')));
  //$myDataGrid->addColumn(new DataGrid_Column(getWords("full absence"), "full_absence", array("width" => 70), array('align' => 'center'), true, false, "", "printDeduct()"));

  $myDataGrid->addColumn(new DataGrid_Column(getWords("leave"), "is_leave", array("width" => 70), array('align' => 'center'), true, false, "", "printDeduct()"));

  $myDataGrid->addColumn(new DataGrid_Column(getWords("deduct annual leave"), "deduct_leave", array("width" => 70), array('align' => 'center'), true, false, "", "printDeduct()"));
  $myDataGrid->addColumn(new DataGrid_Column(getWords("deduct Additional leave"), "deduct_additional_leave", array("width" => 70), array('align' => 'center'), true, false, "", "printDeduct()"));
  $myDataGrid->addColumn(new DataGrid_Column(getWords("leave weight"), "leave_weight", array('width' => '100'), array('nowrap' => '')));


  $myDataGrid->addColumn(new DataGrid_Column(getWords("cancel partial absence"), "cancel_partial_absence", array("width" => 70), array('align' => 'center'), true, false, "", "printDeduct()"));
  $myDataGrid->addColumn(new DataGrid_Column(getWords("treated as attend (Tipe absen ini dianggap hadir)"), "treated_as_attend", array("width" => 70), array('align' => 'center'), true, false, "", "printDeduct()"));

  foreach($dataDeductionType as $strCode => $arrDeduction)
  {
    $myDataGrid->addColumn(new DataGrid_Column(getWords("deduct ".$arrDeduction['name']), $strCode, array("width" => 70), array('align' => 'center'), true, false, "", "printDeduct()"));
  }

  if ($bolCanEdit)
    $myDataGrid->addColumn(new DataGrid_Column("", "", array('width' => '60'), array('align' => 'center', 'nowrap' => ''), false, false, "","printEditLink()", "string", false));

  if ($bolCanDelete)
    $myDataGrid->addSpecialButton("btnDelete","btnDelete","submit",getWords("delete"),"onClick=\"javascript:return myClient.confirmDelete();\"","deleteData()");
  $myDataGrid->addButtonExportExcel(getWords("export excel"), $dataPrivilege['menu_name'].".xls", getWords($dataPrivilege['menu_name']));

  $myDataGrid->getRequest();
  //--------------------------------


  // uddin 20150121: copy dari blok diatas
  $strSQL  = "SELECT * FROM hrd_absence_deduction";
  $res = $db->execute($strSQL);
  while ($row = $db->fetchrow($res))
  {
    $dataAD[$row['absence_code']][$row['deduction_code']] = $row['is_dependant'];
  }
  // uddin end


  //get Data and set to Datagrid's DataSource by set the data binding (bind method)
  $strSQLCOUNT  = "SELECT COUNT(*) AS total FROM hrd_absence_type ";
  $strSQL       = "SELECT * FROM hrd_absence_type ";


  $myDataGrid->totalData = $myDataGrid->getTotalData($db, $strSQLCOUNT);
  $dataset = $myDataGrid->getData($db, $strSQL);
  foreach($dataset as $index => $arrData)
  {
    foreach($dataDeductionType as $strDeductionCode => $arrVal)
    {
      $dataset[$index][$strDeductionCode] = (isset($dataAD[$arrData['code']][$strDeductionCode])) ? $dataAD[$arrData['code']][$strDeductionCode] : "";
    }
  }
  //bind Datagrid with array dataset
  $myDataGrid->bind($dataset);
  $DataGrid = $myDataGrid->render();

  $strConfirmDelete = getWords("are you sure to delete this selected data?");
  $strConfirmSave = getWords("do you want to save this entry?");


  $tbsPage = new clsTinyButStrong ;

  //write this variable in every page
  $strPageTitle = getWords($dataPrivilege['menu_name']);
  $pageIcon = "../images/icons/".$dataPrivilege['icon_file'];
  $strTemplateFile = getTemplate(str_replace(".php", ".html", basename($_SERVER['PHP_SELF'])));
  //------------------------------------------------
  //Load Master Template
  $tbsPage->LoadTemplate($strMainTemplate) ;
  $tbsPage->Show() ;
//--------------------------------------------------------------------------------

  function printEditLink($params)
  {
    global $strDeductions;
    global $dataDeductionType;
    $strResult = "";
    extract($params);
    foreach($dataDeductionType as $strCode => $arrDeduction)
      $strResult .= "<input type=hidden name='detail".$strCode."$counter' id='detail".$strCode."$counter' value='".$record[$strCode]."' />";

    return "
      <input type=hidden name='detailID$counter' id='detailID$counter' value='".$record['code']."' />
      <input type=hidden name='detailCode$counter' id='detailCode$counter' value='".$record['code']."' />
      <input type=hidden name='detailNote$counter' id='detailNote$counter' value='".$record['note']."' />
      <input type=hidden name='detailIsLeave$counter' id='detailIsLeave$counter' value='".$record['is_leave']."' />
      <input type=hidden name='detailDeductLeave$counter' id='detailDeductLeave$counter' value='".$record['deduct_leave']."' />
      <input type=hidden name='detailDeductAddLeave$counter' id='detailDeductAddLeave$counter' value='".$record['deduct_additional_leave']."' />
      <input type=hidden name='detailLeaveWeight$counter' id='detailLeaveWeight$counter' value='".$record['leave_weight']."' />
      <input type=hidden name='detailCancelPartialAbsence$counter' id='detailCancelPartialAbsence$counter' value='".$record['cancel_partial_absence']."' />
      <input type=hidden name='detailAttend$counter' id='detailAttend$counter' value='".$record['treated_as_attend']."' />
      <a href=\"javascript:myClient.editData('$counter".$strDeductions."')\">" .getWords('edit'). "</a>".$strResult;

  }

  function printDeduct($params)
  {
    extract($params);
    if ($value == 't'){
        if(!isset($_REQUEST['btnExportXLS'])){
             return "&radic;";
        }else{
            return "Yes";
        }

    }else{
      return "-";
    }
  }

  // fungsi untuk menyimpan data
  function saveData()
  {
    global $f;
    global $isNew;
    global $dataDeductionType;

    $dataHrdAbsenceType = new cHrdAbsenceType();
    $data = array(
                  "code" => $f->getValue('dataCode'),
                  "note" => $f->getValue('dataNote'),
                  //"full_absence" => ($f->getValue('dataFullAbsence')) ? 't' : 'f',
                  "is_leave" => ($f->getValue('dataIsLeave')) ? 't' : 'f',
                  "deduct_leave" => ($f->getValue('dataDeductLeave')) ? 't' : 'f',
                  "deduct_additional_leave" => ($f->getValue('dataDeductAddLeave')) ? 't' : 'f',
                  "leave_weight" => $f->getValue('dataLeaveWeight'),
                  "cancel_partial_absence" => ($f->getValue('dataCancelPartialAbsence')) ? 't' : 'f',
                  /*"deduct_attendance" => ($f->getValue('dataDeductAttendance')) ? 't' : 'f',
                  "deduct_meal" => ($f->getValue('dataDeductMeal')) ? 't' : 'f',
                  "deduct_shift" => ($f->getValue('dataDeductShift')) ? 't' : 'f',
                  "deduct_transport" => ($f->getValue('dataDeductTransport')) ? 't' : 'f',
                  "deduct_salary" => ($f->getValue('dataDeductSalary')) ? 't' : 'f'*/
                  'treated_as_attend' => ($f->getValue('dataAttend')) ? 't' : 'f'
                  );
    // simpan data -----------------------
    if ($isNew)
    {
      // data baru
      $bolSuccess = $dataHrdAbsenceType->insert($data);
    }
    else
    {
      $bolSuccess = $dataHrdAbsenceType->update(/*pk*/"code='".$f->getValue('dataID')."'", /*data to update*/ $data);
    }
    if ($bolSuccess) $f->setValue('dataID', $f->getValue('dataCode'));
    // simpan data trip type cost setting
    $tblHrdAbsenceDeduction = new cHrdAbsenceDeduction();
    $tblHrdAbsenceDeduction->delete("absence_code = '".$f->getValue('dataCode')."'");
    $data2 = array("absence_code" => $f->getValue('dataCode'));
    foreach($dataDeductionType AS $strDeductionCode => $arrDetail)
    {
      $data2['deduction_code'] = $strDeductionCode;
      $data2['is_dependant']   = ($f->getValue('data'.$strDeductionCode)) ? 't' : 'f';
      $tblHrdAbsenceDeduction->insert($data2);
    }
    $f->message = $dataHrdAbsenceType->strMessage;
  } // saveData

  // fungsi untuk menghapus data
  function deleteData()
  {
    global $myDataGrid;

    $arrKeys = array();
    foreach ($myDataGrid->checkboxes as $strValue)
      $arrKeys['code'][] = $strValue;

    $dataHrdAbsenceType = new cHrdAbsenceType();
    $dataHrdAbsenceType->deleteMultiple($arrKeys);

    $myDataGrid->message = $dataHrdAbsenceType->strMessage;
  } //deleteData

?>
