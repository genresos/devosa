<?php
  include_once('../global/session.php');
  include_once('global.php');
  include_once('../global/common_data.php');
  include_once('../includes/datagrid2/datagrid.php');
  include_once('../includes/form2/form2.php');
  include_once('../classes/hrd/hrd_loan_type.php');

  
  $dataPrivilege = getDataPrivileges(basename($_SERVER['PHP_SELF']), $bolCanView, $bolCanEdit, $bolCanDelete, $bolCanApprove, $bolCanCheck, $bolCanAcknowledge, $bolCanApprove2);
  if (!$bolCanView) die(accessDenied($_SERVER['HTTP_REFERER']));
 
  $strWordsLoanCategory     = getWords("loan category");
  $strWordsLoanType     = getWords("loan type");
  $strWordsLoanPurpose  = getWords("loan purpose");
  $db = new CdbClass;

  $strDataID = getPostValue('dataID');
  $isNew = ($strDataID == "");

  if ($bolCanEdit)
  {
    $f = new clsForm("formInput", 1, "100%", "");
    $f->caption = strtoupper($strWordsINPUTDATA);

    $f->addHidden("dataID", $strDataID); 
    $f->addSelect(getWords("loan category"), "dataCategory", getDataListLoanCategory(), array(), "numeric", true, true, true);  
    $f->addInput(getWords("loan type"), "dataType", "", array("size" => 100, "maxlength" => 127), "string", true, true, true);  
    $f->addCheckBox(getWords("paid to external party"), "dataExternalTransfer", false );
    $f->addInput(getWords("account no."), "dataExternalAccount", "", array("size" => 100, "maxlength" => 127), "string", false);  
    $f->addInput(getWords("account name"), "dataExternalAccountName", "", array("size" => 100, "maxlength" => 127), "string", false);  
    $f->addSelect(getWords("bank"), "dataExternalBankCode", getDataListBank("",true,""), "", "", false);  
    $f->addTextArea(getWords("note"), "dataNote", "", array("cols"=>50, "rows"=>1), "string", false, true, true);

    $f->addSubmit("btnSave", getWords("save"), array("onClick" => "javascript:myClient.confirmSave();"), true, true, "", "", "saveData()");
    $f->addButton("btnAdd", getWords("add new"), array("onClick" => "javascript:myClient.editData(0);"));
    
    $formInput = $f->render();
  }
  else
    $formInput = "";
  
  $myDataGrid = new cDataGrid("formData","DataGrid1");
  $myDataGrid->caption = strtoupper($strWordsLISTOF . " " . $dataPrivilege['menu_name']);
  $myDataGrid->setAJAXCallBackScript(basename($_SERVER['PHP_SELF']));
  $myDataGrid->pageSortBy = "category";
  if (!isset($_REQUEST['btnExportXLS']))
  $myDataGrid->addColumnCheckbox(new DataGrid_Column("chkID", "id", array("rowspan" => 2, 'width' => '30'), array('align'=>'center', 'nowrap' => '')));
  $myDataGrid->addColumnNumbering(new DataGrid_Column(getWords("no."), "", array("rowspan" => 2, 'width'=>'30'), array('nowrap'=>'')));
  $myDataGrid->addColumn(new DataGrid_Column(getWords("loan category"), "category", array("rowspan" => 2, "width" => 100), array('align' => 'center'), true, true, "", "printLoanCategoryType()"));
  $myDataGrid->addColumn(new DataGrid_Column(getWords("loan type"), "type", array("rowspan" => 2, 'width' => '150'),array('nowrap' => '')));
  $myDataGrid->addColumn(new DataGrid_Column(getWords("paid to external party"), "external_transfer", array("rowspan" => 2), array('width' => '15', 'valign' => 'top', 'align' => 'center', 'nowrap' => ''), false, false, "","printActiveSymbol()"));
  $myDataGrid->addColumn(new DataGrid_Column(getWords("transfered to:"), "", array("colspan" => 3)));
  $myDataGrid->addColumn(new DataGrid_Column(getWords("account no."), "external_account", array('width' => '150'),array('nowrap' => '')));
  $myDataGrid->addColumn(new DataGrid_Column(getWords("account name"), "external_account_name", array('width' => '150'),array('nowrap' => '')));
  $myDataGrid->addColumn(new DataGrid_Column(getWords("bank"), "bank_name", array('width' => '150'),array('nowrap' => '')));
  $myDataGrid->addColumn(new DataGrid_Column(getWords("note"), "note", array("rowspan" => 2)));
  if (!isset($_REQUEST['btnExportXLS'])){
	if ($bolCanEdit)
		$myDataGrid->addColumn(new DataGrid_Column("", "", array('width' => '60', "rowspan" => 2), array('align' => 'center', 'nowrap' => ''), false, false, "","printEditLink()", "", false )); //show in excel
  }
  // generateRoleButtons($dataPrivilege['edit'], $dataPrivilege['delete'], $dataPrivilege['check'], $dataPrivilege['approve'], true, true, $myDataGrid);
  generateRoleButtons($dataPrivilege['edit'], $dataPrivilege['delete'], false, false,  false,  true, $myDataGrid);
 
  $myDataGrid->addButtonExportExcel(getWords("export excel"), $dataPrivilege['menu_name'].".xls", getWords($dataPrivilege['menu_name']));

  $myDataGrid->getRequest();
  //--------------------------------
  //get Data and set to Datagrid's DataSource by set the data binding (bind method)
  $strSQLCOUNT  = "SELECT COUNT(*) AS total FROM hrd_loan_type";
  $strSQL       = "SELECT t1.*, t2.bank_name FROM hrd_loan_type AS t1 LEFT JOIN hrd_bank AS t2 ON t1.external_bank_code = t2.bank_code ";

  $myDataGrid->totalData = $myDataGrid->getTotalData($db, $strSQLCOUNT);
  $dataset = $myDataGrid->getData($db, $strSQL);

  //bind Datagrid with array dataset
  $myDataGrid->bind($dataset);
  $DataGrid = $myDataGrid->render();
  

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
    extract($params);
    return "
      <input type=hidden name='detailID$counter' id='detailID$counter' value='".$record['id']."' />
      <input type=hidden name='detailCategory$counter' id='detailCategory$counter' value='".$record['category']."' />
      <input type=hidden name='detailType$counter' id='detailType$counter' value='".$record['type']."' />
      <input type=hidden name='detailExternalTransfer$counter' id='detailExternalTransfer$counter' value='".$record['external_transfer']."' />
      <input type=hidden name='detailExternalAccount$counter' id='detailExternalAccount$counter' value='".$record['external_account']."' />
      <input type=hidden name='detailExternalAccountNam$counter' id='detailExternalAccountName$counter' value='".$record['external_account_name']."' />
      <input type=hidden name='detailExternalBankCode$counter' id='detailExternalBankCode$counter' value='".$record['external_bank_code']."' />
      <input type=hidden name='detailNote$counter' id='detailNote$counter' value='".$record['note']."' />
      <a href=\"javascript:myClient.editData($counter)\">" .getWords('edit'). "</a>";
  }
  
  // fungsi untuk menyimpan data
  function saveData() 
  {
    global $f;
    global $error;
    $isNew = ($f->getValue('dataID') == "");
 
    $dataLoanType= new cHrdLoanType();    
 
    $strModifiedByID = $_SESSION['sessionUserID'];
    // cek validasi -----------------------
    $strKriteria = ($isNew) ?  "" : "AND id <> '".$f->getValue('dataID')."' ";
    
    $data = array("type" => $f->getValue('dataType'),
                  "category" => $f->getValue('dataCategory'),
                  "external_transfer" => ($f->getValue('dataExternalTransfer') == "") ? "f" : "t",
                  "external_account" => $f->getValue('dataExternalAccount'),
                  "external_account_name" => $f->getValue('dataExternalAccountName'),
                  "external_bank_code" => $f->getValue('dataExternalBankCode'),
                  "note" => $f->getValue('dataNote'));

    // simpan data -----------------------
    $bolSuccess = false;
    if ($isNew)
    {
      // data baru
      $bolSuccess = $dataLoanType->insert($data);
    } 
    else 
    {
      $bolSuccess = $dataLoanType->update(/*pk*/"id='".$f->getValue('dataID')."'", /*data to update*/ $data);
    }
    if ($bolSuccess)
    {
      if ($isNew)
        $f->setValue('dataID', $dataLoanType->getLastInsertId());
      else
        $f->setValue('dataID', $f->getValue('dataID'));
    }

    $f->message = $dataLoanType->strMessage;
  } // saveData
  
  // fungsi untuk menghapus data
  function deleteData() 
  {
    global $myDataGrid;
  
    $arrKeys = array();
    foreach ($myDataGrid->checkboxes as $strValue)
      $arrKeys['id'][] = $strValue;

    $dataLoanType= new cHrdLoanType();    
    $dataLoanType->deleteMultiple($arrKeys);
    
    $myDataGrid->message = $dataLoanType->strMessage;
  } //deleteData

  // fungsi untuk menampilkan group posisi
  function getDataListLoanCategory($default = null, $isHasEmpty = false, $emptyData = null)
  {
    global $ARRAY_LOAN_CATEGORY;
    $arrData = array();
    if ($isHasEmpty) $arrData[] = $emptyData;
    foreach($ARRAY_LOAN_CATEGORY as $key => $value)
    {
      if ($key == $default)
        $arrData[] = array("value" => $key, "text" => getWords($value), "selected" => true);
      else
        $arrData[] = array("value" => $key, "text" => getWords($value), "selected" => false);
    }
    return $arrData;
  } 

  // print Position Group
  function printLoanCategoryType($params)
  {
    global $ARRAY_LOAN_CATEGORY;
    extract($params);
    if ($record['category'] == "" ) 
      return "";
    else
      return getWords($ARRAY_LOAN_CATEGORY[$record['category']]);
  }  // print Position Group

?>