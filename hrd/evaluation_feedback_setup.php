<?php
  include_once('../global/session.php');
  include_once('global.php');
  include_once('../global/common_data.php');
  include_once('../global/employee_function.php');
  include_once('../includes/datagrid2/datagrid.php');
  include_once('../includes/form2/form2.php');
  include_once('../classes/hrd/hrd_evaluation_feedback_criteria.php');

  
  $dataPrivilege = getDataPrivileges(basename($_SERVER['PHP_SELF']), $bolCanView, $bolCanEdit, $bolCanDelete, $bolCanApprove);
  if (!$bolCanView) die(accessDenied($_SERVER['HTTP_REFERER']));


  //---- INISIALISASI ----------------------------------------------------
  $strWordsEvaluationCategory = getWords("evaluation category");
  $strWordsGeneralKRAForManagerial = getWords ("general KRA for managerial");
  $strWordsGeneralKRAForStaff = getWords ("general KRA for staff");
  $strWordsEmployeeKRA = getWords ("employee individual KRA");
  $strWordsFeedbackSetup = getWords ("evaluation feedback setup");
  $strWordsKRA = getWords ("general KRA");

  $db = new CdbClass;
  $db->connect();

  $strDataID = getPostValue('dataID');
  $strFilterActive = getPostValue('filterActive');
  
  $isNew = ($strDataID == "");

  if ($bolCanEdit)
  {
    $f = new clsForm("formInput", 2, "100%", "");
    $f->caption = strtoupper($strWordsINPUTDATA);
    $f->addHidden("dataID", $strDataID);
    $f->addTextArea(getWords("question"), "dataQuestion", "", array("cols"=>90, "rows"=>1), "string", true, true, true);
    //$f->addTextArea(getWords("answer options"), "dataOptions", "", array("cols"=>90, "rows"=>1), "string", false, true, true, "", "<br>".getWords("use pipe '|' symbol as delimiter"));
    $f->addHidden("dataSequence", "");  
    $f->addLiteral("","", "<table border=0><tr><td>".generateRadio("dataForEvaluator", "t", "checked")."</td><td>".getWords("for evaluator")."</td></tr><tr><td>".generateRadio("dataForEvaluator", "f")."</td><td>".getWords("for employee")."</td></tr></table>")
    ;
    
    //$f->addCheckBox(getWords("for evaluator"), "dataForEvaluator", true);
    $f->addSubmit("btnSave", getWords("save"), array("onClick" => "javascript:myClient.confirmSave();"), true, true, "", "", "saveData()");
    $f->addButton("btnAdd", getWords("add new"), array("onClick" => "javascript:myClient.editData(0);"));
    
    $formInput = $f->render();
  }
  else
    $formInput = "";

  $myDataGrid = new cDataGrid("formData","DataGrid", "100%", "100%", false, false, false);

  $myDataGrid->setAJAXCallBackScript(basename($_SERVER['PHP_SELF']));
  
  $myDataGrid->addColumnCheckbox(new DataGrid_Column("chkID", "id", "", array('width' => '15', 'valign' => 'top', 'align'=>'center', 'nowrap' => '')));
  $myDataGrid->addColumnNumbering(new DataGrid_Column("No", "", "", array('width'=>'30', 'nowrap'=>'')));
  //$myDataGrid->addColumn(new DataGrid_Column(getWords("sequence"), "sequence", array('width'=>'100'), array('nowrap'=>'')));
  $myDataGrid->addColumn(new DataGrid_Column(getWords("question"), "question",  ""));
  //$myDataGrid->addColumn(new DataGrid_Column(getWords("answer options"), "options",  "", array('width' => '300'), false, false, "","printAnswerOption()"));
  //$myDataGrid->addColumn(new DataGrid_Column(getWords("for evaluator"), "for_evaluator", "", array('width' => '30', 'valign' => 'top', 'align' => 'center', 'nowrap' => ''), false, false, "",""));

  if ($bolCanEdit)
    $myDataGrid->addColumn(new DataGrid_Column("", "", "", array('width' => '60', 'valign' => 'top', 'align' => 'center', 'nowrap' => ''), false, false, "","printEditLink()"));
    
  if ($bolCanDelete)
    $myDataGrid->addSpecialButton("btnDelete","btnDelete","submit","Delete","onClick=\"javascript:return myClient.confirmDelete();\"","deleteData()");


  $myDataGrid->getRequest();  

  //--------------------------------
  //get Data and set to Datagrid's DataSource by set the data binding (bind method)
  $strSQLCOUNT  =  "SELECT COUNT(*) AS total FROM hrd_evaluation_feedback_criteria WHERE for_evaluator = TRUE";
  $strSQL       =  "SELECT * FROM hrd_evaluation_feedback_criteria WHERE for_evaluator = TRUE";
  $myDataGrid->totalData = $myDataGrid->getTotalData($db, $strSQLCOUNT);
  $myDataGrid->caption = getWords("feedback criteria for evaluator");
  $intNextSequence = $myDataGrid->totalData;
  $dataset = $myDataGrid->getData($db, $strSQL);
  $myDataGrid->bind($dataset);
  $DataGrid = $myDataGrid->render();

  //--------------------------------
  //get Data and set to Datagrid's DataSource by set the data binding (bind method)
  $strSQLCOUNT  =  "SELECT COUNT(*) AS total FROM hrd_evaluation_feedback_criteria WHERE for_evaluator = FALSE";
  $strSQL       =  "SELECT * FROM hrd_evaluation_feedback_criteria WHERE for_evaluator = FALSE";
  $myDataGrid->totalData = $myDataGrid->getTotalData($db, $strSQLCOUNT);
  $myDataGrid->caption = getWords("feedback criteria for employee");
  $dataset = $myDataGrid->getData($db, $strSQL);
  $myDataGrid->bind($dataset);
  $DataGrid2 = $myDataGrid->render();
  $intNextSequence += $myDataGrid->totalData + 1;


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
  function printAnswerOption($params)
  {
    extract($params);
    return str_replace("|", "<br>- ", str_replace("| ", "<br>- ", "- ".$value));
  }



  function printEditLink($params)
  {
    extract($params);
    return 
      generateHidden("detailID$counter", $record['id']).
      generateHidden("detailQuestion$counter", $record['question']).
      generateHidden("detailSequence$counter", $record['sequence']).
      generateHidden("detailForEvaluator$counter", $record['for_evaluator']).
      "<a href=\"javascript:myClient.editData($counter)\">" .getWords('edit'). "</a>";
  }
  
  // fungsi untuk menyimpan data
  function saveData() 
  {
    global $f;
    global $isNew;
    $arrScore = array();
    $db = new CdbClass;
    $bolSave = true;

    $strmodified_byID = $_SESSION['sessionUserID'];
    
    $tblHrdEvaluationFeedbackCriteria = new cHrdEvaluationFeedbackCriteria();
    
    if($bolSave)
    {
      $data = array("question" => $f->getValue('dataQuestion'),
                    "sequence"   => $f->getValue('dataSequence'),
                    "for_evaluator"   => getPostValue('dataForEvaluator')
                   );
      // simpan data -----------------------
      $bolSuccess = false;
      if ($isNew)
      {
        // data baru
        $bolSuccess = $tblHrdEvaluationFeedbackCriteria->insert($data);
        if ($bolSuccess)
        {
          $f->setValue('dataID', $tblHrdEvaluationFeedbackCriteria->getLastInsertId());
        }
      } 
      else 
      {
        $bolSuccess = $tblHrdEvaluationFeedbackCriteria->update(/*pk*/"id='".$f->getValue('dataID')."'", /*data to update*/ $data);
      }
    }
    $f->message = $tblHrdEvaluationFeedbackCriteria->strMessage;
  } // saveData
  
  // fungsi untuk menghapus data
  function deleteData() 
  {
    global $myDataGrid;
  
    $arrKeys = array();
    foreach ($myDataGrid->checkboxes as $strValue)
      $arrKeys['id'][] = $strValue;

    $tblHrdEvaluationFeedbackCriteria= new cHrdEvaluationFeedbackCriteria();    
    $tblHrdEvaluationFeedbackCriteria->deleteMultiple($arrKeys);
    
    $myDataGrid->message = $tblHrdEvaluationFeedbackCriteria->strMessage;
  } //deleteData

?>