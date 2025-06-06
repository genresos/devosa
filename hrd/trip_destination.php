<?php
  include_once('../global/session.php');
  include_once('global.php');
  include_once('../includes/datagrid2/datagrid.php');
  include_once('../includes/form2/form2.php');
  include_once('../classes/hrd/hrd_destination.php');

  
  $dataPrivilege = getDataPrivileges(basename($_SERVER['PHP_SELF']), $bolCanView, $bolCanEdit, $bolCanDelete, $bolCanApprove, $bolCanCheck, $bolCanAcknowledge, $bolCanApprove2);
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
    $f->caption = strtoupper(vsprintf(getWords("input data %s"), getWords("destination")));

    $f->addHidden("dataID", $strDataID);
    $f->addInput(getWords("destination code"), "dataDestinationCode", "", array("size" => 20), "string", true, true, true);  
    $f->addInput(getWords("destination name"), "dataDestinationName", "", array("size" => 50), "string", true, true, true);  

    $f->addSubmit("btnSave", getWords("save"), array("onClick" => "javascript:myClient.confirmSave();"), true, true, "", "", "saveData()");
    $f->addButton("btnAdd", getWords("add new"), array("onClick" => "javascript:myClient.editData(0);"));
    
    $formInput = $f->render();
  }
  else
    $formInput = "";
  
  $myDataGrid = new cDataGrid("formData","DataGrid1");
  $myDataGrid->caption = getWords(strtoupper(vsprintf(getWords("list of %s"), getWords("destination"))));
  $myDataGrid->setAJAXCallBackScript(basename($_SERVER['PHP_SELF']));
  
if (!isset($_REQUEST['btnExportXLS']))
  $myDataGrid->addColumnCheckbox(new DataGrid_Column("chkID", "destination_code", array('width' => '30'), array('align'=>'center', 'nowrap' => '')));
  $myDataGrid->addColumnNumbering(new DataGrid_Column("No", "", array('width'=>'30'), array('nowrap'=>'')));
  $myDataGrid->addColumn(new DataGrid_Column(getWords("destination code"), "destination_code", array('width' => '150'),array('nowrap' => '')));
  $myDataGrid->addColumn(new DataGrid_Column(getWords("destination name"), "destination_name", "", array('nowrap' => '')));

  if ($bolCanEdit)
    $myDataGrid->addColumn(new DataGrid_Column("", "", array('width' => '60'), array('align' => 'center', 'nowrap' => ''), false, false, "","printEditLink()", "", false /*show in excel*/));
  
  if ($bolCanDelete)
    $myDataGrid->addSpecialButton("btnDelete","btnDelete","submit","Delete","onClick=\"javascript:return myClient.confirmDelete();\"","deleteData()");

  $myDataGrid->addButtonExportExcel("Export Excel", $dataPrivilege['menu_name'].".xls", getWords($dataPrivilege['menu_name']));

  $myDataGrid->getRequest();
  //--------------------------------
  //get Data and set to Datagrid's DataSource by set the data binding (bind method)
  $strSQLCOUNT  = "SELECT COUNT(*) AS total FROM hrd_destination ";
  $strSQL       = "SELECT * FROM hrd_destination ";

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
      <input type=hidden name='detailID$counter' id='detailID$counter' value='".$record['destination_code']."' />
      <input type=hidden name='detailDestinationCode$counter' id='detailDestinationCode$counter' value='".$record['destination_code']."' />
      <input type=hidden name='detailDestinationName$counter' id='detailDestinationName$counter' value='".$record['destination_name']."' />
      <a href=\"javascript:myClient.editData($counter)\">" .getWords('edit'). "</a>";
  }
  
  // fungsi untuk menyimpan data
  function saveData() 
  {
    global $f;
    global $isNew;

    $strmodified_byID = $_SESSION['sessionUserID'];
    
    $dataHrdDestination = new cHrdDestination();
    $data = array("destination_code" => $f->getValue('dataDestinationCode'),
                  "destination_name" => $f->getValue('dataDestinationName'));    
    // simpan data -----------------------
    if ($isNew)
    {
      // data baru
      $bolSuccess = $dataHrdDestination->insert($data);
    } 
    else 
    {
      $bolSuccess = $dataHrdDestination->update(/*pk*/"destination_code='".$f->getValue('dataID')."'", /*data to update*/ $data);
    }
    if ($bolSuccess) $f->setValue('dataID', $data['destination_code']);
    
    $f->message = $dataHrdDestination->strMessage;
  } // saveData
  
  // fungsi untuk menghapus data
  function deleteData() 
  {
    global $myDataGrid;
  
    $arrKeys = array();
    foreach ($myDataGrid->checkboxes as $strValue)
      $arrKeys['destination_code'][] = $strValue;

    $dataHrdDestination = new cHrdDestination();    
    $dataHrdDestination->deleteMultiple($arrKeys);
    
    $myDataGrid->message = $dataHrdDestination->strMessage;
  } //deleteData

?>