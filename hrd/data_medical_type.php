<?php
  include_once('../global/session.php');
  include_once('global.php');
  include_once('../global/common_data.php');
  include_once('../includes/datagrid2/datagrid.php');
  include_once('../includes/form2/form2.php');
  include_once('../classes/hrd/hrd_medical_type.php');

  $dataPrivilege = getDataPrivileges(basename($_SERVER['PHP_SELF']), $bolCanView, $bolCanEdit, $bolCanDelete, $bolCanApprove, $bolCanCheck, $bolCanAcknowledge, $bolCanApprove2);
  if (!$bolCanView) die(accessDenied($_SERVER['HTTP_REFERER']));

  $strWordsTreatmentTypeSetting = getWords("treatment type setting");
  $strWordsQuotaSetting = getWords("quota setting");
  $strWordsExtendedQuota = getWords("extended quota");
  $db = new CdbClass;

  $strDataID = getPostValue('dataID');
  $isNew = ($strDataID == "");

  if ($bolCanEdit)
  {
    $f = new clsForm("formInput", 1, "100%", "");
    $f->caption = strtoupper($strWordsINPUTDATA);

    $f->addHidden("dataID", $strDataID);
    $f->addSelect(getWords("treatment type"), "dataType", getDataListMedicalTreatmentType("", false, null), array("style" => "width:270"));

    $f->addInput(getWords("treatment code"), "dataCode", "", array("size" => 50, "maxlength" => 30), "string", true, true, true);
    //$f->addCheckBox(getWords("permanent only"), "dataPermanentOnly", true );
    //$f->addCheckBox(getWords("prorate"), "dataProrate", true);
	  $f->addCheckBox(getWords("permanent only"), "dataPermanentOnly", true, false, true,"", "<br>&nbsp;<br>");
    $f->addCheckBox(getWords("employee only"), "dataEmployeeOnly", true, false, true,"", "<br>&nbsp;<br>");
    $f->addCheckBox(getWords("family only"), "dataFamilyOnly", true, false, true,"", "<br>&nbsp;<br>");
    $f->addCheckBox(getWords("Per Person"), "dataPerPerson", true, false, true,"", "<br>&nbsp;<br>");
    $f->addCheckBox(getWords("prorate"), "dataProrate", true, false, true,"", "<br>&nbsp;<br>");
    $f->addTextArea(getWords("note"), "dataNote", "", array("cols"=>50, "rows"=>2), "string", false, true, true);

    $f->addSelect(getWords("value type"), "dataValueType", array(array("value"=>"nominal","text"=>"Nominal"),array("value"=>"percen","text"=>"Percentage")), array("style" => "width:270"));
    $f->addInput(getWords("Salary Code(for Procentage type)"), "dataRefValue", "", array("size" => 50, "maxlength" => 30), "string", false, true, true);

    $f->addSubmit("btnSave", getWords("save"), array("onClick" => "javascript:myClient.confirmSave();"), true, true, "", "", "saveData()");
    $f->addButton("btnAdd", getWords("add new"), array("onClick" => "javascript:myClient.editData(0);"));

    $formInput = $f->render();
  }
  else
    $formInput = "";
  $myDataGrid = new cDataGrid("formData","DataGrid1");
  $myDataGrid->caption = strtoupper($strWordsLISTOF . " " . $dataPrivilege['menu_name']);
  $myDataGrid->setAJAXCallBackScript(basename($_SERVER['PHP_SELF']));
  if (!isset($_REQUEST['btnExportXLS']))
  $myDataGrid->addColumnCheckbox(new DataGrid_Column("chkID", "id", array('width' => '30'), array('align'=>'center', 'nowrap' => '')));
  $myDataGrid->addColumnNumbering(new DataGrid_Column("No", "", array('width'=>'30'), array('nowrap'=>'')));
  $myDataGrid->addColumn(new DataGrid_Column(getWords("treatment type"), "type", array('width' => '150'),array('nowrap' => ''), true, true, "","printTreatmentTypeName()"));
  $myDataGrid->addColumn(new DataGrid_Column(getWords("treatment code"), "code", array('width' => '150'),array('nowrap' => '')));
  $myDataGrid->addColumn(new DataGrid_Column(getWords("permanent only"), "permanent_only", "", array('width' => '15', 'valign' => 'top', 'align' => 'center', 'nowrap' => ''), false, false, "","printActiveSymbol()"));
  $myDataGrid->addColumn(new DataGrid_Column(getWords("employee only"), "employee_only", "", array('width' => '15', 'valign' => 'top', 'align' => 'center', 'nowrap' => ''), false, false, "","printActiveSymbol()"));
  $myDataGrid->addColumn(new DataGrid_Column(getWords("family only"), "family_only", "", array('width' => '15', 'valign' => 'top', 'align' => 'center', 'nowrap' => ''), false, false, "","printActiveSymbol()"));
  $myDataGrid->addColumn(new DataGrid_Column(getWords("per person"), "per_person", "", array('width' => '15', 'valign' => 'top', 'align' => 'center', 'nowrap' => ''), false, false, "","printActiveSymbol()"));
  $myDataGrid->addColumn(new DataGrid_Column(getWords("prorate"), "prorate", "", array('width' => '15', 'valign' => 'top', 'align' => 'center', 'nowrap' => ''), false, false, "","printActiveSymbol()"));
  $myDataGrid->addColumn(new DataGrid_Column(getWords("Value Type"), "value_type", ""));
  $myDataGrid->addColumn(new DataGrid_Column(getWords("Ref Salary Code"), "ref_value", ""));
  $myDataGrid->addColumn(new DataGrid_Column(getWords("note"), "note", ""));

  if ($bolCanEdit)
    $myDataGrid->addColumn(new DataGrid_Column("", "", array('width' => '60'), array('align' => 'center', 'nowrap' => ''), false, false, "","printEditLink()", "", false /*show in excel*/));

  if ($bolCanDelete)
    $myDataGrid->addSpecialButton("btnDelete","btnDelete","submit","Delete","onClick=\"javascript:return myClient.confirmDelete();\"","deleteData()");

  $myDataGrid->addButtonExportExcel(getWords("export excel"), $dataPrivilege['menu_name'].".xls", getWords($dataPrivilege['menu_name']));

  $myDataGrid->getRequest();
  //--------------------------------
  //get Data and set to Datagrid's DataSource by set the data binding (bind method)
  $strSQLCOUNT  = "SELECT COUNT(*) AS total FROM hrd_medical_type";
  $strSQL       = "SELECT * FROM hrd_medical_type ";

  $myDataGrid->totalData = $myDataGrid->getTotalData($db, $strSQLCOUNT);
  $dataset = $myDataGrid->getData($db, $strSQL);

  //bind Datagrid with array dataset
  $myDataGrid->bind($dataset);
  $DataGrid = $myDataGrid->render();


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
  function printTreatmentTypeName($params)
  {
    global $ARRAY_MEDICAL_TREATMENT_GROUP;
    extract($params);
    return (isset($ARRAY_MEDICAL_TREATMENT_GROUP[$value])) ?  $value." - ".$ARRAY_MEDICAL_TREATMENT_GROUP[$value] : "UNKNOWN";
  }

  function printEditLink($params)
  {
    extract($params);
    return "
      <input type=hidden name='detailID$counter' id='detailID$counter' value='".$record['id']."' />
      <input type=hidden name='detailType$counter' id='detailType$counter' value='".$record['type']."' />
      <input type=hidden name='detailCode$counter' id='detailCode$counter' value='".$record['code']."' />
      <input type=hidden name='detailPermanentOnly$counter' id='detailPermanentOnly$counter' value='".$record['permanent_only']."' />
      <input type=hidden name='detailEmployeeOnly$counter' id='detailEmployeeOnly$counter' value='".$record['employee_only']."' />
      <input type=hidden name='detailFamilyOnly$counter' id='detailFamilyOnly$counter' value='".$record['family_only']."' />
      <input type=hidden name='detailPerPerson$counter' id='detailPerPerson$counter' value='".$record['per_person']."' />
      <input type=hidden name='detailProrate$counter' id='detailProrate$counter' value='".$record['prorate']."' />
      <input type=hidden name='detailNote$counter' id='detailNote$counter' value='".$record['note']."' />
      <input type=hidden name='detailValueType$counter' id='detailValueType$counter' value='".$record['value_type']."' />
      <input type=hidden name='detailRefValue$counter' id='detailRefValue$counter' value='".$record['ref_value']."' />
      <a href=\"javascript:myClient.editData($counter)\">" .getWords('edit'). "</a>";
  }

  // fungsi untuk menyimpan data
  function saveData()
  {
    global $f;
    global $db;
    global $error;
    $isNew = ($f->getValue('dataID') == "");

    $strModifiedByID = $_SESSION['sessionUserID'];
    // cek validasi -----------------------
    $strKriteria = ($isNew) ?  "" : "AND id <> '".$f->getValue('dataID')."' ";
    if (isDataExists($db, "hrd_medical_type", "code", $f->getValue('dataCode'), "AND \"type\" = ".$f->getValue('dataType'). $strKriteria))
    {
      $f->message = $error['duplicate_code']. "  -> ".$f->getValue('dataCode');
      $f->msgClass = "bgError";
      return false;
    }

    $dataMedicalType = new cHrdMedicalType();
    $data = array("type" => $f->getValue('dataType'),
                  "code" => $f->getValue('dataCode'),
                  "permanent_only" => ($f->getValue('dataPermanentOnly') == "") ? "f" : "t",
                  "employee_only" => ($f->getValue('dataEmployeeOnly') == "") ? "f" : "t",
                  "family_only" => ($f->getValue('dataFamilyOnly') == "") ? "f" : "t",
                  "per_person" => ($f->getValue('dataPerPerson') == "") ? "f" : "t",
                  "prorate" => ($f->getValue('dataProrate') == "") ? "f" : "t",
                  "value_type" => $f->getValue('dataValueType'),
                  "ref_value" => $f->getValue('dataRefValue'),
                  "note" => $f->getValue('dataNote'));

    // simpan data -----------------------
    $bolSuccess = false;
    if ($isNew)
    {
      // data baru
      $bolSuccess = $dataMedicalType->insert($data);
    }
    else
    {
      $bolSuccess = $dataMedicalType->update(/*pk*/"id='".$f->getValue('dataID')."'", /*data to update*/ $data);
    }
    if ($bolSuccess)
    {
      if ($isNew)
        $f->setValue('dataID', $dataMedicalType->getLastInsertId());
      else
        $f->setValue('dataID', $f->getValue('dataID'));
    }

    $f->message = $dataMedicalType->strMessage;
  } // saveData

  // fungsi untuk menghapus data
  function deleteData()
  {
    global $myDataGrid;

    $arrKeys = array();
    foreach ($myDataGrid->checkboxes as $strValue)
      $arrKeys['id'][] = $strValue;

    $dataMedicalType= new cHrdMedicalType();
    $dataMedicalType->deleteMultiple($arrKeys);

    $myDataGrid->message = $dataMedicalType->strMessage;
  } //deleteData

?>
