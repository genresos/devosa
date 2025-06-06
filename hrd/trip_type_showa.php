<?php
  include_once('../global/session.php');
  include_once('global.php');
  include_once('../global/common_data.php');
  include_once('../includes/datagrid2/datagrid.php');
  include_once('../includes/form2/form2.php');
  include_once('../classes/hrd/hrd_trip_type.php');
  include_once('../classes/hrd/hrd_trip_type_cost_setting.php');
  include_once('../classes/hrd/hrd_trip_cost_type.php');

  
  $dataPrivilege = getDataPrivileges(basename($_SERVER['PHP_SELF']), $bolCanView, $bolCanEdit, $bolCanDelete, $bolCanApprove, $bolCanCheck, $bolCanAcknowledge, $bolCanApprove2);
  if (!$bolCanView) die(accessDenied($_SERVER['HTTP_REFERER']));



 
  $db = new CdbClass;

  $strDataID = getPostValue('dataID');
  $isNew = ($strDataID == "");
  $strJSAdd = "";
  $strWordsBusinessTripType   = getWords("business trip type");
  $strWordsTripAllowanceType  = getWords("trip allowance type");
  $strWordsTripAllowanceQuota = getWords("trip allowance quota");
  $strWordsTripDestination    = getWords("trip destination");

  //ambil jenis2 trip allowance type untuk di assign mana saja yang digunakan pada tiap trip type
  $tblTripCostType = new cHrdTripCostType();
  $arrTripCostType = $tblTripCostType->findAll("", "id, trip_cost_type_name, currency", "trip_cost_type_name", null, 1, "id");
  if ($bolCanEdit)
  {


    $f = new clsForm("formInput", 4, "100%", "");
    $f->caption = strtoupper($strWordsINPUTDATA);

    $f->addHidden("dataID", $strDataID);
    $f->addFieldset("","","");
    $f->addInput(getWords("code"), "dataCode", "", array("size" => 30, "maxlength" => 31), "string", true, true, true);  
    $f->addInput(getWords("name"), "dataName", "", array("size" => 30, "maxlength" => 127), "string", true, true, true);  
    $f->addCheckBox(getWords("daily allowance") , "dataDailyAllowance", false, null, "string", false, true, true,"", "");
    $f->addFieldset("","","");

    //tambahkan baris kosong untuk kolom pertama supaya semua trip cost type terletak di kolom kedua
    /*
    for($i = 1; $i <= count($arrTripCostType) - 2; $i++)
      $f->addLiteral("", "", "");
    */

    foreach($arrTripCostType AS $strCostID => $arrCostDetail)
    {
      $f->addCheckBox(getWords($arrCostDetail['trip_cost_type_name'])." (".$arrCostDetail['currency'].")", "dataTripCost_".$strCostID, false, null, "string", false, true, true,"", "");
      $strJSAdd .= printJSAdd("dataTripCost_".$strCostID);
    }

    $f->addSubmit("btnSave", getWords("save"), array("onClick" => "javascript:myClient.confirmSave();"), true, true, "", "", "saveData()");
    $f->addButton("btnAdd", getWords("add new"), array("onClick" => "javascript:myClient.editData(0);"));
    
    $formInput = $f->render();
  }
  else
    $formInput = "";
  
  $myDataGrid = new cDataGrid("formData","DataGrid1");
  $myDataGrid->caption = getWords(strtoupper(vsprintf(getWords("list of %s"), getWords($dataPrivilege['menu_name']))));
  $myDataGrid->setAJAXCallBackScript(basename($_SERVER['PHP_SELF']));
  
  $myDataGrid->addColumnCheckbox(new DataGrid_Column("chkID", "id", array('width' => '30'), array('align'=>'center', 'nowrap' => '')));
  $myDataGrid->addColumnNumbering(new DataGrid_Column("No", "", array('width'=>'30'), array('nowrap'=>'')));
  $myDataGrid->addColumn(new DataGrid_Column(getWords("code"), "trip_type_code", array('width' => '150'), array('nowrap' => '')));
  $myDataGrid->addColumn(new DataGrid_Column(getWords("name"), "trip_type_name", ""));
  $myDataGrid->addColumn(new DataGrid_Column(getWords("daily allowance"), "daily_allowance", array('width' => '75'), array('align' => 'center'), false, false, "", "printIncludeSymbol()"));
  foreach($arrTripCostType AS $strCostID => $arrCostDetail)
  {
    $myDataGrid->addColumn(new DataGrid_Column(getWords($arrCostDetail['trip_cost_type_name'])." (".$arrCostDetail['currency'].")", "trip_cost_".$strCostID, array('width' => '75'), array('align' => 'center'), false, false, "", "printIncludeSymbol()"));
  }

  if ($bolCanEdit)
    $myDataGrid->addColumn(new DataGrid_Column("", "", array('width' => '60'), array('align' => 'center', 'nowrap' => ''), false, false, "","printEditLink()", "", false /*show in excel*/));
  
  if ($bolCanDelete)
    $myDataGrid->addSpecialButton("btnDelete","btnDelete","submit","Delete","onClick=\"javascript:return myClient.confirmDelete();\"","deleteData()");

  $myDataGrid->addButtonExportExcel("Export Excel", $dataPrivilege['menu_name'].".xls", getWords($dataPrivilege['menu_name']));

  $myDataGrid->getRequest();
  //--------------------------------
  //get Data and set to Datagrid's DataSource by set the data binding (bind method)
  $strSQLCOUNT  = "SELECT COUNT(*) AS total FROM hrd_trip_type ";
  $strSQL       = "SELECT * FROM hrd_trip_type ";
  $tblTripTypeCostSetting = new cHrdTripTypeCostSetting();

  $myDataGrid->totalData = $myDataGrid->getTotalData($db, $strSQLCOUNT);
  $dataset = $myDataGrid->getData($db, $strSQL);
  foreach($dataset AS $strKey => $arrDetail)
  {
    $arrTripTypeCostSetting = $tblTripTypeCostSetting->findAll("id_trip_type = ".$arrDetail['id'], "id_trip_cost_type, include", "", null, 1, "id_trip_cost_type");    
    foreach($arrTripCostType AS $strCostID => $arrCostDetail)
    {
      $dataset[$strKey]['trip_cost_'.$strCostID] = (isset($arrTripTypeCostSetting[$strCostID])) ? $arrTripTypeCostSetting[$strCostID]['include'] : 'f';
    }

  }
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

  function printJSAdd($strObjName)
  {
    return "\$(".$strObjName.").checked = false;";
  }
  function printEditLink($params)
  {
    global $arrTripCostType;
    extract($params);
    $strResult = "
      <input type=hidden name='detailID$counter' id='detailID$counter' value='".$record['id']."' />
      <input type=hidden name='detailCode$counter' id='detailCode$counter' value='".$record['trip_type_code']."' />
      <input type=hidden name='detailName$counter' id='detailName$counter' value='".$record['trip_type_name']."' />
      <input type=hidden name='detailDailyAllowance$counter' id='detailDailyAllowance$counter' value='".$record['daily_allowance']."' />
      <a href=\"javascript:myClient.editData($counter);";
    foreach($arrTripCostType AS $strCostID => $arrCostDetail)
    {
      $strChecked = ($record['trip_cost_'.$strCostID] == "t") ? "true" : "false";
      $strResult .= "javascript:myClient.editDataTripCost(".$strCostID.", ".$strChecked.");";
    }
    $strResult .= "\">" .getWords('edit'). "</a>";
    return $strResult;
  }
  function printIncludeSymbol($params)
  {
    extract($params);
    return ($value == 't') ? "&radic;" : "";
  }
    
  // fungsi untuk menyimpan data
  function saveData() 
  {
    global $f;
    global $isNew;
    global $arrTripCostType;

    $strmodified_byID = $_SESSION['sessionUserID'];
    $tblHrdTripType = new cHrdTripType();
    $tblHrdTripTypeCostSetting = new cHrdTripTypeCostSetting();
    $data = array("trip_type_code" => $f->getValue('dataCode'),
                  "trip_type_name" => $f->getValue('dataName'),
                  "daily_allowance" => ($f->getValue('dataDailyAllowance') != "") ? 't' : 'f');

    // simpan data trip type
    $bolSuccess = false;
    if ($isNew)
    {
      // data baru
      $bolSuccess = $tblHrdTripType->insert($data);
    } 
    else 
    {
      $bolSuccess = $tblHrdTripType->update("id='".$f->getValue('dataID')."'", $data);
    }
    if ($bolSuccess)
    {
      if ($isNew)
        $f->setValue('dataID', $tblHrdTripType->getLastInsertId());
      else
        $f->setValue('dataID', $f->getValue('dataID'));
    }
    // simpan data trip type cost setting
    $data2 = array("id_trip_type" => $f->getValue('dataID'));
    foreach($arrTripCostType AS $strCostID => $arrCostDetail)
    {
      $data2['id_trip_cost_type'] = $strCostID;
      $data2['include'] = ($f->getValue('dataTripCost_'.$strCostID) != "") ? 't' : 'f';
      // hapus data lama, insert data baru
      $tblHrdTripTypeCostSetting->delete("id_trip_type = ".$f->getValue('dataID')." AND id_trip_cost_type = $strCostID");
      $tblHrdTripTypeCostSetting->insert($data2);
    }


    $f->message = $tblHrdTripType->strMessage;
  } // saveData
  
  // fungsi untuk menghapus data
  function deleteData() 
  {
    global $myDataGrid;
  
    $arrKeys = array();
    foreach ($myDataGrid->checkboxes as $strValue)
    {
      $arrKeys['id'][] = $strValue;
      $arrKeys2['id_trip_type'][] = $strValue;
    }
    $tblHrdTripType = new cHrdTripType();    
    $tblHrdTripTypeCostSetting = new cHrdTripTypeCostSetting();    
    $tblHrdTripType->deleteMultiple($arrKeys);
    $tblHrdTripTypeCostSetting->deleteMultiple($arrKeys2);
 
    $myDataGrid->message = $tblHrdTripType->strMessage;
  } //deleteData

?>