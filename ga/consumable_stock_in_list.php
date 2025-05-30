<?php
  include_once('../global/session.php');
  include_once('global.php');
  include_once('form_object.php');
  include_once('../global/common_data.php');
  include_once('../includes/datagrid2/datagrid.php');
  include_once('../includes/form2/form2.php');
  include_once('../classes/ga/consumable_stock_in.php');
  //================ END INCLUDE=====================================

  
  $dataPrivilege = getDataPrivileges(basename($_SERVER['PHP_SELF']), $bolCanView, $bolCanEdit, $bolCanDelete, $bolCanApprove, $bolCanCheck);
  if (!$bolCanView) die(accessDenied($_SERVER['HTTP_REFERER']));

  //INISIALISASI---------------------------------------------------------------------------------------------------------------
  $strWordsFILTERDATA	 				 = getWords ("Form Filter Data");
  $strWordsEntryConsumableStockIn        = getWords ("entry consumable stock in");
  $strWordsConsumableStockInList         = getWords ("consumable stock in list");
  
  // Get tanggal hari ini
  $strNow = date("Y-m-d");
  $DataGrid = "";
  $myDataGrid = new cDataGrid("formData","DataGrid1", "100%", "100%", false, true, false);

  
  //DAFTAR FUNGSI--------------------------------------------------------------------------------------------------------------
  function getData($db)
  {
    global $dataPrivilege, $bolCanEdit, $bolCanDelete, $bolCanApprove, $bolCanCheck;
    global $f;
    global $DataGrid;
    global $myDataGrid;
    //global $strKriteriaCompany;
    
    $arrData = $f->getObjectValues();

    $strKriteria = "";
    // GENERATE CRITERIA
   
    if ($arrData['dataIdCategory']!= "") {
      $strKriteria .= "AND csi.id_item = '".$arrData['dataIdCategory']."'";
    }
    if ($arrData['dataTransactionDate']!= "") {
      $strKriteria .= "AND csi.transaction_date = '".$arrData['dataTransactionDate']."'";
    }
    if ($arrData['dataItemAmount']!= "") {
      $strKriteria .= "AND csi.item_amount = '".$arrData['dataItemAmount']."'";
    }
    if ($arrData['dataDocNo']!= "") {
      $strKriteria .= "AND csi.document_no = '".$arrData['dataDocNo']."'";
    }
    

    if ($db->connect())
    {
      $myDataGrid = new cDataGrid("formData","DataGrid1");
	  $myDataGrid->caption = getWords(strtoupper(vsprintf(getWords("list of %s"), getWords($dataPrivilege['menu_name']))));
      $myDataGrid->setAJAXCallBackScript(basename($_SERVER['PHP_SELF']));
      $myDataGrid->setCriteria($strKriteria);
      $myDataGrid->addColumnCheckbox(new DataGrid_Column("chkID", "id", array('width' => '30'), array('align'=>'center', 'nowrap' => '')), false);
   
    //-------------------------------------- BEGIN Data Grid---------------------------------------------------------------------------------// 
	$myDataGrid->addColumnNumbering(new DataGrid_Column("No", "", array('width'=>'30'), array('nowrap'=>'')));
    $myDataGrid->addColumn(new DataGrid_Column(getWords("Item"), "item_name", array('width' => '100'),array('nowrap' => '')));
    $myDataGrid->addColumn(new DataGrid_Column(getWords("transaction Date"), "transaction_date", array('width' => '100'),array('nowrap' => '')));
    $myDataGrid->addColumn(new DataGrid_Column(getWords("item amount"), "item_amount", array('width' => '150'),array('nowrap' => '')));
    $myDataGrid->addColumn(new DataGrid_Column(getWords("document no"), "document_no", array('width' => '40'),array('nowrap' => '')));
    
	if (!isset($_POST['btnExportXLS']) && $bolCanEdit)
        $myDataGrid->addColumn(new DataGrid_Column("", "", array("width" => "60"), array('align' => 'center', 'nowrap' => ''), false, false, "","printEditLink()", "", false /*show in excel*/));

      foreach($arrData AS $key => $value)
      {
         $myDataGrid->strAdditionalHtml .= generateHidden($key, $value, "");
      }
  
      //-----------------BEGIN Jika Punya Hak Akses Hapus-----------------------------//
      if ($bolCanDelete)
     $myDataGrid->addSpecialButton("btnDelete","btnDelete","submit","Delete","onClick=\"javascript:return myClient.confirmDelete();\"","deleteData()");
     //---------------- END Jika Punya Hak Akses Hapus-------------------------//  

     $myDataGrid->addButtonExportExcel("Export Excel", $dataPrivilege['menu_name'].".xls", getWords($dataPrivilege['menu_name']));
     $myDataGrid->getRequest();

     //get Data and set to Datagrid's DataSource by set the data binding (bind method)
	 $strSQLCOUNT  = "SELECT COUNT(*) AS total FROM ga_consumable_stock_in AS csi LEFT JOIN ga_item AS i ON csi.id_item=i.id ";
	 $strSQL       = "SELECT csi.id AS id, i.item_name, csi.* FROM ga_consumable_stock_in as csi LEFT JOIN ga_item AS i ON csi.id_item=i.id";

     $myDataGrid->totalData = $myDataGrid->getTotalData($db, $strSQLCOUNT);
     $dataset = $myDataGrid->getData($db, $strSQL);			

     //bind Datagrid with array dataset and branchCode
     $myDataGrid->bind($dataset);
     $DataGrid = $myDataGrid->render();
    }
    else $DataGrid = "";
    return $DataGrid;
   }

   //*********************************** FUNGSI TOMBOL EDIT******************************************
   function printEditLink($params)
   {
    extract($params);
    return "<a href=\"consumable_stock_in_edit.php?dataID=".$record['id']."\">" .getWords('edit'). "</a>";
   }
  //******************************* END FUNGSI TOMBOL EDIT *******************************************
    
  function deleteData() 
  {
    global $myDataGrid;

    $arrKeys = array();
    foreach ($myDataGrid->checkboxes as $strValue)
    {
      $arrKeys['id'][] = $strValue;
    }
    $tblDelete = new cGaConsumableStockIn();    
    $tblDelete->deleteMultiple($arrKeys);
 
    $myDataGrid->message = $tblDelete->strMessage;
  } 
  //************************************************END deleteData **************************************



  //================================================== BEGIN MAIN PROGRAM =============================================================================
  $db = new CdbClass;
  if ($db->connect()) 
  {
    getUserEmployeeInfo();
    $arrUserList = getAllUserInfo($db);

    $strDataID   = getPostValue('dataID');
    scopeData($strDataEmployee, $strDataSubSection, $strDataSection, $strDataDepartment, $strDataDivision, $_SESSION['sessionUserRole'], $arrUserInfo);
    $strReadonly = (scopeCBDataEntry($strDataEmployee, $_SESSION['sessionUserRole'], $arrUserInfo)) ? "readonly" : "";
    
	//generate form untuk select trip type
    $f = new clsForm("formFilter", 1, "100%", "");
    $f->caption = strtoupper($strWordsFILTERDATA);
    $f->addHidden("dataID", $strDataID);
    $f->addSelect(getWords("item "), "dataIdCategory", getDataListItem(""),array("style" => "width:$strDefaultWidthPx"),"",false);
    $f->addInput(getWords("transaction date"), "dataTransactionDate",  null ,array("style" => "width:$strDateWidth"), "date", false, true, true);
    $f->addInput(getWords("item amount"), "dataItemAmount",  null ,array("style" => "width:$strDateWidth"), "string", false, true, true);
    $f->addInput(getWords("document no"), "dataDocNo",  null ,array("style" => "width:$strDateWidth"), "string", false, true, true);
	$f->addSubmit("btnShow", getWords("show"), "", true, true, "", "", "");
	$f->addButton("btnAdd", getWords("Clear"), array("onClick" => "location.href='".basename($_SERVER['PHP_SELF']."';")));
    $formInput = $f->render();
    getData($db);
  }
  $tbsPage = new clsTinyButStrong ;
  
  //write this variable in every page
  $strPageTitle = $dataPrivilege['menu_name'];
  $pageIcon = "../images/icons/".$dataPrivilege['icon_file'];
  $strTemplateFile = getTemplate(str_replace(".php", ".html", basename($_SERVER['PHP_SELF'])));  
  //------------------------------------------------
  //Load Master Template
  $tbsPage->LoadTemplate($strMainTemplate) ;
  $tbsPage->Show() ;
//============================================= END MAIN PROGRAM ==========================================================================================



?>