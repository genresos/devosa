<?php
  include_once('../global/session.php');
  include_once('global.php');
  include_once('../includes/datagrid2/datagrid.php');
  include_once('../includes/form2/form2.php');
  include_once('../classes/hrd/hrd_company.php');

  
  $dataPrivilege = getDataPrivileges(basename($_SERVER['PHP_SELF']), $bolCanView, $bolCanEdit, $bolCanDelete, $bolCanApprove, $bolCanCheck, $bolCanAcknowledge, $bolCanApprove2);
  if (!$bolCanView) die(accessDenied($_SERVER['HTTP_REFERER']));



 
  $db = new CdbClass;

  $strDataID = getPostValue('dataID');
  $isNew = ($strDataID == "");
  $strDefaultPath = getSetting("attendance_file_path");
  $strDefaultType = getSetting("attendance_file_type");

  if ($bolCanEdit)
  {
    $f = new clsForm("formInput", 1, "100%", "");
    $f->caption = strtoupper($strWordsINPUTDATA);

    $f->addHidden("dataID", $strDataID);

    $f->addInput(getWords("company code"), "dataCompanyCode", "", array("size" => 31, "maxlength" => 31), "string", true, true, true);  
    $f->addInput(getWords("company name"), "dataCompanyName", "", array("size" => 100, "maxlength" => 127), "string", true, true, true);  
    $f->addInput(getWords("j k k percentage (%) (b p j s  t k)"), "dataJkk", "", array("size" => 100, "maxlength" => 127), "numeric", true, true, true);  
//    $f->addInput(getWords("attendance file path"), "dataAttendanceFilePath", $strDefaultPath, array("size" => 100, "maxlength" => 255), "string", false, true, true);  
//    $f->addInput(getWords("attendance file type"), "dataAttendanceFileType", $strDefaultType, array("size" => 100, "maxlength" => 7), "string", false, true, true);  

    $f->addTextArea(getWords("note"), "dataNote", "", array("cols" => 97, "rows" => 2), "string", false, true, true);  


    $f->addSubmit("btnSave", getWords("save"), array("onClick" => "javascript:myClient.confirmSave();"), true, true, "", "", "saveData()");
    $f->addButton("btnAdd", getWords("add new"), array("onClick" => "javascript:myClient.editData(0);"));
    
    $formInput = $f->render();
  }
  else
    $formInput = "";
  
  $myDataGrid = new cDataGrid("formData","DataGrid1");
  $myDataGrid->caption = strtoupper($strWordsLISTOF . " " . getWords($dataPrivilege['menu_name']));
  $myDataGrid->setAJAXCallBackScript(basename($_SERVER['PHP_SELF']));
  
  $myDataGrid->addColumnCheckbox(new DataGrid_Column("chkID", "id", array('width' => '30'), array('align'=>'center', 'nowrap' => '')));
  $myDataGrid->addColumnNumbering(new DataGrid_Column(getWords("no."), "", array('width'=>'30'), array('nowrap'=>'')));
  $myDataGrid->addColumn(new DataGrid_Column(getWords("company code"), "company_code", array('width' => '150'), array('nowrap' => '')));
  $myDataGrid->addColumn(new DataGrid_Column(getWords("company name"), "company_name", "", array('nowrap' => '')));
  $myDataGrid->addColumn(new DataGrid_Column(getWords("j k k percentage"), "jkk_percentage", "", array('nowrap' => '')));
//  $myDataGrid->addColumn(new DataGrid_Column(getWords("attendance file path"), "attendance_file_path", array(), array('nowrap' => '')));
//  $myDataGrid->addColumn(new DataGrid_Column(getWords("file type"), "attendance_file_type", array(), array('nowrap' => '')));

  $myDataGrid->addColumn(new DataGrid_Column(getWords("note"), "note", null, array('nowrap' => '')));

  if ($bolCanEdit)
    $myDataGrid->addColumn(new DataGrid_Column("", "", array('width' => '60'), array('align' => 'center', 'nowrap' => ''), false, false, "","printEditLink()", "", false /*show in excel*/));
  
  if ($bolCanDelete)
    $myDataGrid->addSpecialButton("btnDelete","btnDelete","submit",getWords("delete"),"onClick=\"javascript:return myClient.confirmDelete();\"","deleteData()");

  $myDataGrid->addButtonExportExcel(getWords("export excel"), $dataPrivilege['menu_name'].".xls", getWords($dataPrivilege['menu_name']));

  
  $myDataGrid->getRequest();
  //--------------------------------
  //get Data and set to Datagrid's DataSource by set the data binding (bind method)
  $strSQLCOUNT  = "SELECT COUNT(*) AS total FROM hrd_company ";
  $strSQL       = "SELECT * FROM hrd_company ";

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
      <input type=hidden name='detailCompanyCode$counter' id='detailCompanyCode$counter' value='".$record['company_code']."' />
      <input type=hidden name='detailCompanyName$counter' id='detailCompanyName$counter' value='".$record['company_name']."' />
      <input type=hidden name='detailJkk$counter' id='detailJkk$counter' value='".$record['jkk_percentage']."' />
      <input type=hidden name='detailNote$counter' id='detailNote$counter' value='".$record['note']."' />
      <a href=\"javascript:myClient.editData($counter)\">" .getWords('edit'). "</a>";
  }
  
  // fungsi untuk menyimpan data
  function saveData() 
  {
    global $f;
    global $isNew;

    $strmodified_byID = $_SESSION['sessionUserID'];
    
    $dataHrdCompany = new cHrdCompany();
    $data = array("company_code" => $f->getValue('dataCompanyCode'),
                  "company_name" => $f->getValue('dataCompanyName'),
                  "jkk_percentage" => $f->getValue('dataJkk'),
                  "note" => $f->getValue('dataNote'));    
    // simpan data -----------------------
    $bolSuccess = false;
    if ($isNew)
    {
      // data baru
      $bolSuccess = ($dataHrdCompany->insert($data));
    } 
    else 
    {
      $bolSuccess = ($dataHrdCompany->update(/*pk*/"id='".$f->getValue('dataID')."'", /*data to update*/ $data));
    }
    if ($bolSuccess)
    {
      $f->setValue('dataID', $f->getValue('dataID'));
    }
    $f->message = $dataHrdCompany->strMessage;
  } // saveData
  
  // fungsi untuk menghapus data
  function deleteData() 
  {
    global $myDataGrid;
  
    $arrKeys = array();
    foreach ($myDataGrid->checkboxes as $strValue)
      $arrKeys['id'][] = $strValue;

    $dataHrdCompany = new cHrdCompany();    
    $dataHrdCompany->deleteMultiple($arrKeys);
    
    $myDataGrid->message = $dataHrdCompany->strMessage;
  } //deleteData

?>