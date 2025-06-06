<?php
  include_once('../global/session.php');
  include_once('global.php');
  include_once('../global/common_data.php');
  include_once('../includes/datagrid2/datagrid.php');
  include_once('../includes/form2/form2.php');
  include_once('../classes/hrd/hrd_evaluation_category.php');

  
  $dataPrivilege = getDataPrivileges(basename($_SERVER['PHP_SELF']), $bolCanView, $bolCanEdit, $bolCanDelete, $bolCanApprove);
  if (!$bolCanView) die(accessDenied($_SERVER['HTTP_REFERER']));




  //---- INISIALISASI ----------------------------------------------------
  $strWordsEvaluationCategory = getWords("evaluation category");
  $strWordsGeneralKRAForManagerial = getWords ("general KRA for managerial");
$strWordsKRA = getWords ("general KRA");
  $strWordsGeneralKRAForStaff = getWords ("general KRA for staff");
  $strWordsEmployeeKRA = getWords ("employee individual KRA");
  $strWordsEvaluationFeedbackSetup = getWords ("evaluation feedback setup");
  
  $db = new CdbClass;

  $strDataID = getPostValue('dataIDCategory');
  $isNew = ($strDataID == "");

  if ($bolCanEdit)
  {
    $f = new clsForm("formInput", 1, "100%", "");
    $f->caption = strtoupper($strWordsINPUTDATA);
 
    $f->addHidden("dataIDCategory", $strDataID);
    //$f->addHidden("filterIDCategory", $strDataID);
    $f->addInput(getWords("sequence"), "dataSequence", "", array("size" => 5),  "integer", true, true, true);  
    $f->addInput(getWords("weight"),   "dataWeight",   "", array("size" => 5),  "", true, true, true, "", "(%)");  
    $f->addInput(getWords("category"), "dataCategory", "", array("size" => 49), "string",  true, true, true);  
    $f->addCheckBox(getWords("individual evaluation"), "dataIndividualEvaluation", false);
    $f->addTextArea(getWords("note"),  "dataNote",     "", array("cols"=>46, "rows"=>3), "string", false, true, true);

    $f->addSubmit("btnSave", getWords("save"), array("onClick" => "javascript:myClient.confirmSave();"), true, true, "", "", "saveData()");
    $f->addButton("btnAdd", getWords("add new"), array("onClick" => "javascript:myClient.editData(0);"));
    
    $formInput = $f->render();
  }
  else
    $formInput = "";
  
  $myDataGrid = new cDataGrid("formData","DataGrid1");
  $myDataGrid->caption = getWords(strtoupper(vsprintf(getWords("list of %s"), getWords("family status"))));
  $myDataGrid->setAJAXCallBackScript(basename($_SERVER['PHP_SELF']));
  
  $myDataGrid->addColumnCheckbox(new DataGrid_Column("chkID", "id", array('width' => '15'), array('align'=>'center', 'nowrap' => '')));
  $myDataGrid->addColumn(new DataGrid_Column(getWords("sequence"), "sequence", array('width'=>'100'), array('nowrap'=>'')));
  $myDataGrid->addColumn(new DataGrid_Column(getWords("category"), "category", array('width' => '250'),array('nowrap' => '')));
  $myDataGrid->addColumn(new DataGrid_Column(getWords("individual evaluation"), "individual_evaluation", "", array('width' => '15', 'valign' => 'top', 'align' => 'center', 'nowrap' => ''), false, false, "","printActiveSymbol()"));
  $myDataGrid->addColumn(new DataGrid_Column(getWords("note"), "note", "", array('align' => 'center')));
  $myDataGrid->addColumn(new DataGrid_Column(getWords("weight (%)"), "weight", array('width' => '100'), array('align' => 'center')));
 
  if ($bolCanEdit)
    $myDataGrid->addColumn(new DataGrid_Column("", "", array('width' => '60'), array('align' => 'center', 'nowrap' => ''), false, false, "","printEditLink()"));
    
  //$myDataGrid->addColumn(new DataGrid_Column("", "", array('width' => '75'), array('align' => 'center', 'nowrap' => ''), false, false, "","printDetailLink()"));
  
  if ($bolCanDelete)
    $myDataGrid->addSpecialButton("btnDelete","btnDelete","submit","Delete","onClick=\"javascript:return myClient.confirmDelete();\"","deleteData()");
  $myDataGrid->strAdditionalHtml = generateHidden("flterIDCategory", "", "");

  
  $myDataGrid->getRequest();
  //--------------------------------
  //get Data and set to Datagrid's DataSource by set the data binding (bind method)
  $strSQLCOUNT  = "SELECT COUNT(*) AS total FROM hrd_evaluation_category ";
  $strSQL       = "SELECT * FROM hrd_evaluation_category ";

  $myDataGrid->totalData = $myDataGrid->getTotalData($db, $strSQLCOUNT);
  $intNextSequence = $myDataGrid->totalData + 1;
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
      <input type=hidden name='detailSequence$counter' id='detailSequence$counter' value='".$record['sequence']."' />
      <input type=hidden name='detailCategory$counter' id='detailCategory$counter' value='".$record['category']."' />
      <input type=hidden name='detailIndividualEvaluation$counter' id='detailIndividualEvaluation$counter' value='".$record['individual_evaluation']."' />
      <input type=hidden name='detailWeight$counter' id='detailWeight$counter' value='".$record['weight']."' />
      <input type=hidden name='detailNote$counter' id='detailNote$counter' value='".$record['note']."' />
      <a href=\"javascript:myClient.editData($counter)\">" .getWords('edit'). "</a>";
  }
  
  // fungsi untuk menyimpan data
  function saveData() 
  {
    global $f;
    global $isNew;

    $strmodified_byID = $_SESSION['sessionUserID'];
    
    $dataHrdEvaluationCategory = new cHrdEvaluationCategory();
    $data = array("category" => $f->getValue('dataCategory'),
                  "weight" => floatval($f->getValue('dataWeight')),
                  "individual_evaluation" => ($f->getValue('dataIndividualEvaluation') == 't') ? 't' : 'f',
                  "note" => $f->getValue('dataNote'),
                  "sequence" => intval($f->getValue('dataSequence')));
    // simpan data -----------------------
    $bolSuccess = false;
    if ($isNew)
    {
      // data baru
      $bolSuccess = $dataHrdEvaluationCategory->insert($data);
    } 
    else 
    {
      $bolSuccess = $dataHrdEvaluationCategory->update(/*pk*/"id='".$f->getValue('dataIDCategory')."'", /*data to update*/ $data);
    }
    if ($bolSuccess)
    {
      if (isset($data['id'])) $f->setValue('dataIDCategory', $data['id']);
      else $f->setValue('dataIDCategory', "");
      if ($isNew)
      {
        //berikan parent id sesuai dengan id pada data baru. Mempermudah sorting pada datagrid (by parent_id, created)
        $f->setValue('dataIDCategory', $dataHrdEvaluationCategory->getLastInsertId() );
      }
    }
    $f->message = $dataHrdEvaluationCategory->strMessage;
  } // saveData
  
  // fungsi untuk menghapus data
  function deleteData() 
  {
    global $myDataGrid;
  
    $arrKeys = array();
    foreach ($myDataGrid->checkboxes as $strValue)
      $arrKeys['id'][] = $strValue;

    $dataHrdEvaluationCategory = new cHrdEvaluationCategory();    
    $dataHrdEvaluationCategory->deleteMultiple($arrKeys);
    
    $myDataGrid->message = $dataHrdEvaluationCategory->strMessage;
  } //deleteData
  function printDetailLink($params)
  {
    extract($params);
    global $bolPrint;
    if ($bolPrint)
      return stripslashes($value);
    else
      return generateButton("btnDetail$counter", getWords("show detail"), "", "onclick =\"document.formData.filterIDCategory.value=document.formData.detailID$counter.value; document.formData.action = 'evaluation_criteria.php'; document.formData.submit()\"");
  }
  
?>