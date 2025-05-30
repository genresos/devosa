<?php
  include_once('../global/session.php');
  include_once('global.php');
  include_once('../global/common_data.php');
  include_once('../includes/datagrid2/datagrid.php');
  include_once('../includes/form2/form2.php');
  include_once('../classes/hrd/hrd_minimum_living_cost.php');

  
  $dataPrivilege = getDataPrivileges(basename($_SERVER['PHP_SELF']), $bolCanView, $bolCanEdit, $bolCanDelete, $bolCanApprove, $bolCanCheck, $bolCanAcknowledge, $bolCanApprove2);
  if (!$bolCanView) die(accessDenied($_SERVER['HTTP_REFERER']));



 
  $db = new CdbClass;

  $strDataID = getPostValue('dataID');
  $isNew = ($strDataID == "");

  if ($bolCanEdit)
  {
    $f = new clsForm("formInput", 1, "100%", "");
    $f->caption = strtoupper($strWordsINPUTDATA);

    $f->addHidden("dataID", $strDataID);
    $f->addInput(getWords("code"), "dataCode", "", array("size" => 30, "maxlength" => 31), "string", true, true, true);  
    $f->addInput(getWords("minimum living cost"), "dataMinimumLivingCost", "", array("size" => 10), "numeric", true, true, true);  
    $f->addTextArea(getWords("note"), "dataNote", "", array("cols" => 48, "rows" => 2), "string", false, true, true);  
    $f->addSubmit("btnSave", getWords("save"), array("onClick" => "javascript:myClient.confirmSave();"), true, true, "", "", "saveData()");
    $f->addButton("btnAdd", getWords("add new"), array("onClick" => "javascript:myClient.editData(0);"));
    
    $formInput = $f->render();
  }
  else
    $formInput = "";
  
  $myDataGrid = new cDataGrid("formData","DataGrid1");
  $myDataGrid->caption = strtoupper($strWordsLISTOF . " " . $dataPrivilege['menu_name']);
  $myDataGrid->setAJAXCallBackScript(basename($_SERVER['PHP_SELF']));
  
  $myDataGrid->addColumnCheckbox(new DataGrid_Column("chkID", "code", array('width' => '30'), array('align'=>'center', 'nowrap' => '')));
  $myDataGrid->addColumnNumbering(new DataGrid_Column("No", "", array('width'=>'30'), array('nowrap'=>'')));
  $myDataGrid->addColumn(new DataGrid_Column(getWords("code"), "code", array('width' => '150'),array('nowrap' => '')));
  $myDataGrid->addColumn(new DataGrid_Column(getWords("minimum living cost"), "minimum_living_cost", array('width' => '200'), array('align' => 'right'), true, true, "", "printFormat()"));
  $myDataGrid->addColumn(new DataGrid_Column(getWords("note"), "note", null, array('nowrap' => '')));
  
  if ($bolCanEdit)
    $myDataGrid->addColumn(new DataGrid_Column("", "", array('width' => '60'), array('align' => 'center', 'nowrap' => ''), false, false, "","printEditLink()", "", false /*show in excel*/));
  
  if ($bolCanDelete)
    $myDataGrid->addSpecialButton("btnDelete","btnDelete","submit","Delete","onClick=\"javascript:return myClient.confirmDelete();\"","deleteData()");

  $myDataGrid->addButtonExportExcel("Export Excel", $dataPrivilege['menu_name'].".xls", getWords($dataPrivilege['menu_name']));

  $myDataGrid->getRequest();
  //--------------------------------
  //get Data and set to Datagrid's DataSource by set the data binding (bind method)
  $strSQLCOUNT  = "SELECT COUNT(*) AS total FROM hrd_minimum_living_cost ";
  $strSQL       = "SELECT * FROM hrd_minimum_living_cost ";

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

  function printEditLink($params)
  {
    extract($params);
    return "
      <input type=hidden name='detailID$counter' id='detailID$counter' value='".$record['code']."' />
      <input type=hidden name='detailCode$counter' id='detailCode$counter' value='".$record['code']."' />
      <input type=hidden name='detailMinimumLivingCost$counter' id='detailMinimumLivingCost$counter' value='".$record['minimum_living_cost']."' />
      <input type=hidden name='detailNote$counter' id='detailNote$counter' value='".$record['note']."' />
      <a href=\"javascript:myClient.editData($counter)\">" .getWords('edit'). "</a>";
  }
  
  function printFormat($params)
  {
    extract($params);
    return number_format($record['minimum_living_cost']);
  }
    
  // fungsi untuk menyimpan data
  function saveData() 
  {
    global $f;
    global $isNew;

    $strmodified_byID = $_SESSION['sessionUserID'];
    
    $dataHrdMinimumLivingCost = new cHrdMinimumLivingCost();
    $data = array("code" => $f->getValue('dataCode'),
                  "minimum_living_cost" => floatval($f->getValue('dataMinimumLivingCost')),
                  "note" => pg_escape_string($f->getValue('dataNote')));
    // simpan data -----------------------
    $bolSuccess = false;
    if ($isNew)
    {
      // data baru
      $bolSuccess = $dataHrdMinimumLivingCost->insert($data);
    } 
    else 
    {
      $bolSuccess = $dataHrdMinimumLivingCost->update(/*pk*/"code='".$f->getValue('dataID')."'", /*data to update*/ $data);
    }
    if ($bolSuccess)
    {
      $f->setValue('dataID', $data['code']);
    }
    $f->message = $dataHrdMinimumLivingCost->strMessage;
  } // saveData
  
  // fungsi untuk menghapus data
  function deleteData() 
  {
    global $myDataGrid;
  
    $arrKeys = array();
    foreach ($myDataGrid->checkboxes as $strValue)
      $arrKeys['code'][] = $strValue;

    $dataHrdMinimumLivingCost = new cHrdMinimumLivingCost();    
    $dataHrdMinimumLivingCost->deleteMultiple($arrKeys);
    
    $myDataGrid->message = $dataHrdMinimumLivingCost->strMessage;
  } //deleteData

?>