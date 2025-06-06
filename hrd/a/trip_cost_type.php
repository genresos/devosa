<?php
  include_once('../global/session.php');
  include_once('global.php');
  include_once('../global/common_data.php');
  include_once('../includes/datagrid2/datagrid.php');
  include_once('../includes/form2/form2.php');
  include_once('../classes/hrd/hrd_trip_cost_type.php');
  include_once('../classes/hrd/hrd_trip_cost_platform.php');
  $dataPrivilege = getDataPrivileges(basename($_SERVER['PHP_SELF']), $bolCanView, $bolCanEdit, $bolCanDelete, $bolCanApprove, $bolCanCheck, $bolCanAcknowledge);
  if (!$bolCanView) die(accessDenied($_SERVER['HTTP_REFERER']));


 
  $db = new CdbClass;

  $strDataID = getPostValue('dataID');
  $isNew = ($strDataID == "");
  $strWordsBusinessTripType   = getWords("business trip type");
  $strWordsTripAllowanceType = getWords("trip allowance type");
  $strWordsTripDestination = getWords("trip destination");

  if ($bolCanEdit)
  {
    $f = new clsForm("formInput", 1, "100%", "");
    $f->caption = strtoupper($strWordsINPUTDATA);

    $f->addHidden("dataID", $strDataID);
    //$f->addHidden(getWords("old code"), "dataOldCode", "", array("size" => 30), "string", true, true, true);  
    $f->addInput(getWords("code"), "dataCode", "", array("size" => 30, "maxlength" => 31), "string", true, true, true);  
    $f->addInput(getWords("name"), "dataName", "", array("size" => 100, "maxlength" => 127), "string", true, true, true);  
    $f->addSelect(getWords("currency"), "dataCurrency", getDataListCurrency(), "", "", true);
    $f->addSubmit("btnSave", getWords("save"), array("onClick" => "javascript:myClient.confirmSave();"), true, true, "", "", "saveData()");
    $f->addButton("btnAdd", getWords("add new"), array("onClick" => "javascript:myClient.editData(0);"));
    
    $formInput = $f->render();
  }
  else
    $formInput = "";
  
  $myDataGrid = new cDataGrid("formData","DataGrid1");
  $myDataGrid->caption = getWords(strtoupper(vsprintf(getWords("list of %s"), getWords($dataPrivilege['menu_name']))));
  $myDataGrid->setAJAXCallBackScript(basename($_SERVER['PHP_SELF']));
  $myDataGrid->setPageLimit("all");
    if (!isset($_REQUEST['btnExportXLS']))
  $myDataGrid->addColumnCheckbox(new DataGrid_Column("chkID", "id", array('width' => '30'), array('align'=>'center', 'nowrap' => '')));
  $myDataGrid->addColumnNumbering(new DataGrid_Column("No", "", array('width'=>'30'), array('nowrap'=>'')));
  $myDataGrid->addColumn(new DataGrid_Column(getWords("code"), "trip_cost_type_code", array('width' => '150'),array('nowrap' => '')));
  $myDataGrid->addColumn(new DataGrid_Column(getWords("name"), "trip_cost_type_name", ""));
  $myDataGrid->addColumn(new DataGrid_Column(getWords("currency"), "currency", ""));
  
  if ($bolCanEdit)
    $myDataGrid->addColumn(new DataGrid_Column("", "", array('width' => '60'), array('align' => 'center', 'nowrap' => ''), false, false, "","printEditLink()", "", false /*show in excel*/));
  
  if ($bolCanDelete)
    $myDataGrid->addSpecialButton("btnDelete","btnDelete","submit","Delete","onClick=\"javascript:return myClient.confirmDelete();\"","deleteData()");

  $myDataGrid->addButtonExportExcel("Export Excel", $dataPrivilege['menu_name'].".xls", getWords($dataPrivilege['menu_name']));

  $myDataGrid->getRequest();
  //--------------------------------
  //get Data and set to Datagrid's DataSource by set the data binding (bind method)
  $strSQLCOUNT  = "SELECT COUNT(*) AS total FROM hrd_trip_cost_type ";
  $strSQL       = "SELECT * FROM hrd_trip_cost_type ";

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
      <input type=hidden name='detailID$counter' id='detailID$counter' value='".$record['id']."' />
      <input type=hidden name='detailCode$counter' id='detailCode$counter' value='".$record['trip_cost_type_code']."' />
      <input type=hidden name='detailName$counter' id='detailName$counter' value='".$record['trip_cost_type_name']."' />
      <input type=hidden name='detailCurrency$counter' id='detailCurrency$counter' value='".$record['currency']."' />
      <a href=\"javascript:myClient.editData($counter)\">" .getWords('edit'). "</a>";
  }
  
  function printFormat($params)
  {
    extract($params);
    return number_format($record['trip_cost_type']);
  }
    
  // fungsi untuk menyimpan data
  function saveData() 
  {
    global $f;
    global $isNew;

    $strmodified_byID = $_SESSION['sessionUserID'];
    
    $dataHrdTripCostType = new cHrdTripCostType();
    $dataHrdTripCostPlatform = new cHrdTripCostPlatform();
    $data = array("trip_cost_type_code" => $f->getValue('dataCode'),
                  "trip_cost_type_name" => $f->getValue('dataName'),
                  "currency" => $f->getValue('dataCurrency'));

    // simpan data -----------------------
    $bolSuccess = false;
    if ($isNew)
    {
      // data baru
      $bolSuccess = $dataHrdTripCostType->insert($data);
    } 
    else 
    {
      $bolSuccess = $dataHrdTripCostType->update("id='".$f->getValue('dataID')."'", $data);
    }
    if ($bolSuccess)
    {
      if ($isNew)
        $f->setValue('dataID', $dataHrdTripCostType->getLastInsertId());
      else
        $f->setValue('dataID', $f->getValue('dataID'));
    }

    $f->message = $dataHrdTripCostType->strMessage;
  } // saveData
  
  // fungsi untuk menghapus data
  function deleteData() 
  {
    global $myDataGrid;
  
    $arrKeys = array();
    foreach ($myDataGrid->checkboxes as $strValue)
      $arrKeys['id'][] = $strValue;

    $dataHrdTripCostType = new cHrdTripCostType();    
    $dataHrdTripCostType->deleteMultiple($arrKeys);
    
    $myDataGrid->message = $dataHrdTripCostType->strMessage;
  } //deleteData

?>