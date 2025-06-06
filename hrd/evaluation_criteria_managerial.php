<?php
  include_once('../global/session.php');
  include_once('global.php');
  include_once('../global/common_data.php');
  include_once('../global/employee_function.php');
  include_once('../includes/datagrid2/datagrid.php');
  include_once('../includes/form2/form2.php');
  include_once('../classes/hrd/hrd_evaluation_criteria.php');
  include_once('evaluation_func.php');

  $dataPrivilege = getDataPrivileges(basename($_SERVER['PHP_SELF']), $bolCanView, $bolCanEdit, $bolCanDelete, $bolCanApprove);
  if (!$bolCanView) die(accessDenied($_SERVER['HTTP_REFERER']));



 
  //---- INISIALISASI ----------------------------------------------------
  $strWordsEvaluationCategory = getWords("evaluation category");
  $strWordsGeneralKRAForManagerial = getWords ("general KRA for managerial");
  $strWordsGeneralKRAForStaff = getWords ("general KRA for staff");
$strWordsKRA = getWords ("general KRA");
  $strWordsEmployeeKRA = getWords ("employee individual KRA");
  $strWordsEvaluationFeedbackSetup = getWords ("evaluation feedback setup");

  $db = new CdbClass;
  $db->connect();

  $strDataIDCategory = (getPostValue('filterIDCategory') != "") ? getPostValue('filterIDCategory') : getPostValue('dataIDCategory');
  $strDataID = getPostValue('dataID');
  
  $strDataDateFrom = (getPostValue('dataDateFrom') == "") ? (date("Y")-1)."-01-01" : getPostValue('dataDateFrom');
  $strDataDateThru = (getPostValue('dataDateThru') == "") ? (date("Y")-1)."-12-31" : getPostValue('dataDateThru');

  $dataHrdEvaluationCriteria = new cHrdEvaluationCriteria();
  $arrTemp      = $dataHrdEvaluationCriteria->find(array("date_from" => $strDataDateFrom, "date_thru" => $strDataDateThru, "managerial" => 't'), "SUM (weight) AS total_weight");
  $fltRemainingWeight = (100 - $arrTemp['total_weight'] > 0) ? 100 - $arrTemp['total_weight'] : 0;

  $isNew = ($strDataID == "");

  if ($bolCanEdit)
  {
    $f = new clsForm("formInput", 2, "100%", "");
    $f->caption = strtoupper($strWordsINPUTDATA);
    $f->addHidden("dataID", $strDataID);
    //$f->addSelect(getWords("year"), "dataYear",   getDataListYear($strDataYear, false /*has empty*/, null /*empty data*/, 10 /*limit*/, true /*asc*/), "", "", false);
    $f->addInput(getWords("date from"), "dataDateFrom", $strDataDateFrom, array("style" => "width:$strDateWidth"), "date", true, true, true);
    $f->addInput(getWords("date thru"), "dataDateThru", $strDataDateThru, array("style" => "width:$strDateWidth"), "date", true, true, true);

    $f->addSelect(getWords("evaluation category"), "dataIDCategory", getDataListEvaluationCategory($strDataIDCategory, false, null, "individual_evaluation = 'f'"), array("style" => "width:270"));

    $f->addInputAutoComplete(getWords("sub header"), "dataSubheader", getEvaluationSubheader(), array("size" => 50), "string", false);
    $f->addInput(getWords("weight"), "dataWeight", $fltRemainingWeight, array("size" => 5), "decimal", true, true, true);  

    $f->addTextArea(getWords("key result area"), "dataCriteria", "", array("cols"=>50, "rows"=>3), "string", true, true, true);

    $f->addLiteral("", "dataScoreTable", getScoreTable($strDataID));
    $f->addLiteral("", "", "");
    $f->addLiteral("", "", "");
    $f->addLiteral("", "", "");
    $f->addLiteral("", "", "");

    $f->addSubmit("btnSave", getWords("save"), array("onClick" => "javascript:myClient.confirmSave();"), true, true, "", "", "saveData()");
    $f->addButton("btnAdd", getWords("add new"), array("onClick" => "document.formFilter.submit();"));
    
    $formInput = $f->render();
  }
  else
    $formInput = "";

  //$strDataIDCategory = (getPostValue('dataIDCategory'));

  $fFilter = new clsForm("formFilter", 8, "100%", "");
  $fFilter->caption = strtoupper($strWordsFILTERDATA);

  $fFilter->addSelect(getWords("category"), "filterIDCategory", getDataListEvaluationCategory($strDataIDCategory, true, null, "individual_evaluation = 'f'"), "", "", false);
  $fFilter->addSelect(getWords("period"), "dataDateFrom",   getDataListDateFrom($strDataDateFrom, true), "", "", false);
  $fFilter->addSelect(getWords("to"), "dataDateThru",   getDataListDateThru($strDataDateThru, true), "", "", false);

  $fFilter->addLiteral("", "buttonShow", generateButton("btnShow", getWords("show"), "", "onclick = \"document.formFilter.submit()\""));
  //$fFilter->showCaption = false;
  $fFilter->hasButton = false;

  $formFilter = $fFilter->render();

  $myDataGrid = new cDataGrid("formData","DataGrid");

  $myDataGrid->caption = strtoupper(getWords($dataPrivilege['menu_name']));
  $myDataGrid->setAJAXCallBackScript(basename($_SERVER['PHP_SELF']));
  
  $myDataGrid->addColumnCheckbox(new DataGrid_Column("chkID", "id", "", array('width' => '15', 'valign' => 'top', 'align'=>'center', 'nowrap' => '')));
  $myDataGrid->addColumnNumbering(new DataGrid_Column("No", "", "", array('width'=>'30', 'valign' => 'top', 'nowrap'=>'')));
  $myDataGrid->addColumn(new DataGrid_Column(getWords("sub header"), "subheader",  "", array('width' => '100', 'valign' => 'top', 'nowrap' => '')));
  $myDataGrid->addColumn(new DataGrid_Column(getWords("key result area"), "criteria",  "", array('width' => '200', 'valign' => 'top', 'nowrap' => '')));
  $myDataGrid->addColumn(new DataGrid_Column(getWords("weight"), "weight", "", array('valign' => 'top', 'align' => 'center', 'width' => '50',)));
  $myDataGrid->addColumn(new DataGrid_Column(getWords("score"), "","", array('align' => 'center', 'nowrap' => ''), false, false, "","printScoreList()"));
    if ($bolCanEdit)
    $myDataGrid->addColumn(new DataGrid_Column("", "", "", array('width' => '60', 'valign' => 'top', 'align' => 'center', 'nowrap' => ''), false, false, "","printEditLink()"));
    
  if ($bolCanDelete)
    $myDataGrid->addSpecialButton("btnDelete","btnDelete","submit","Delete","onClick=\"javascript:return myClient.confirmDelete();\"","deleteData()");


  $myDataGrid->getRequest();  

  //--------------------------------
  //get Data and set to Datagrid's DataSource by set the data binding (bind method)
  $strSQLCOUNT  =  "SELECT COUNT(*) AS total FROM hrd_evaluation_criteria 
                    WHERE (date_from, date_thru) OVERLAPS (DATE '$strDataDateFrom', DATE '$strDataDateThru')
                    AND managerial = 't'";
  $strSQL       =  "SELECT * FROM hrd_evaluation_criteria 
                    WHERE (date_from, date_thru) OVERLAPS (DATE '$strDataDateFrom', DATE '$strDataDateThru')
                    AND managerial = 't'";
  if ($strDataIDCategory != "")
  {
    $strSQL    .= " AND id_category = '$strDataIDCategory'";
    $strSQLCOUNT    .= " AND id_category = '$strDataIDCategory'";
  }
 
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
  function getScoreTable($strDataID)
  {
	  $strDataScore1Note = $_POST['dataScore1Note'];
	  $strDataScore2Note = $_POST['dataScore2Note'];
	  $strDataScore3Note = $_POST['dataScore3Note'];
	  $strDataScore4Note = $_POST['dataScore4Note'];
	  $strDataScore5Note = $_POST['dataScore5Note'];
	  
    if ($strDataID != "")
    {
      $dataHrdEvaluationCriteria = new cHrdEvaluationCriteria();
      $arrScore      = $dataHrdEvaluationCriteria->find(array("id" => $strDataID));
    }
    else 
      $arrScore = array("score1" => 5, "score2" => 4, "score3" => 3, "score4" => 2, "score5" => 1, 
                        "score1_note" => $strDataScore1Note, "score2_note" => $strDataScore2Note, "score3_note" => $strDataScore3Note, "score4_note" => $strDataScore4Note, "score5_note" => $strDataScore5Note); 
    $strResult = "";
    $strResult .= "<table border=1 cellpadding=1 cellspacing=0>
                   <tr>
                   <th>".getWords("score")."</th><th>".getWords("note")."</th></tr><tr>
                   <td>".generateInput("dataScore1", $arrScore['score1'], "size=7")."</td>
                   <td>".generateInput("dataScore1Note", $arrScore['score1_note'], "size=100")."</td></tr><tr>
                   <td>".generateInput("dataScore2", $arrScore['score2'], "size=7")."</td>
                   <td>".generateInput("dataScore2Note", $arrScore['score2_note'], "size=100")."</td></tr><tr>
                   <td>".generateInput("dataScore3", $arrScore['score3'], "size=7")."</td>
                   <td>".generateInput("dataScore3Note", $arrScore['score3_note'], "size=100")."</td></tr><tr>
                   <td>".generateInput("dataScore4", $arrScore['score4'], "size=7")."</td>
                   <td>".generateInput("dataScore4Note", $arrScore['score4_note'], "size=100")."</td></tr><tr>
                   <td>".generateInput("dataScore5", $arrScore['score5'], "size=7")."</td>
                   <td>".generateInput("dataScore5Note", $arrScore['score5_note'], "size=100")."</td>
                   </tr>
                   </table>";
    return $strResult;
  }

  function printEditLink($params)
  {
    extract($params);
    return "
      <input type=hidden name='detailID$counter' id='detailID$counter' value='".$record['id']."' />
      <input type=hidden name='detailIDCategory$counter' id='detailIDCategory$counter' value='".$record['id_category']."' />
      <input type=hidden name='detailSubheader$counter' id='detailSubheader$counter' value='".$record['subheader']."' />
      <input type=hidden name='detailCriteria$counter' id='detailCriteria$counter' value='".$record['criteria']."' />
      <input type=hidden name='detailWeight$counter' id='detailWeight$counter' value='".$record['weight']."' />
      <input type=hidden name='detailScore1$counter' id='detailScore1$counter' value='".$record['score1']."' />
      <input type=hidden name='detailScore2$counter' id='detailScore2$counter' value='".$record['score2']."' />
      <input type=hidden name='detailScore3$counter' id='detailScore3$counter' value='".$record['score3']."' />
      <input type=hidden name='detailScore4$counter' id='detailScore4$counter' value='".$record['score4']."' />
      <input type=hidden name='detailScore5$counter' id='detailScore5$counter' value='".$record['score5']."' />
      <input type=hidden name='detailScore1Note$counter' id='detailScore1Note$counter' value='".$record['score1_note']."' />
      <input type=hidden name='detailScore2Note$counter' id='detailScore2Note$counter' value='".$record['score2_note']."' />
      <input type=hidden name='detailScore3Note$counter' id='detailScore3Note$counter' value='".$record['score3_note']."' />
      <input type=hidden name='detailScore4Note$counter' id='detailScore4Note$counter' value='".$record['score4_note']."' />
      <input type=hidden name='detailScore5Note$counter' id='detailScore5Note$counter' value='".$record['score5_note']."' />
      <a href=\"javascript:myClient.editData($counter)\">" .getWords('edit'). "</a>";
  }
  
  // fungsi untuk menyimpan data
  function saveData() 
  {
    global $f;
    global $isNew;
    global $fltRemainingWeight;
    $arrScore = array();
    $db = new CdbClass;
    $bolSave = true;

    $strmodified_byID = $_SESSION['sessionUserID'];
    
    $dataHrdEvaluationCriteria = new cHrdEvaluationCriteria();
    
    //$strIDEmployee = ($f->getValue('dataEmployee') == "") ? -1 : getIDEmployee($db, $f->getValue('dataEmployee')); 

/*  Cek total bobot (tidak lebih dari 100%)
    if ($isNew)
    {
      if ($fltRemainingWeight < floatVal($f->getValue('dataWeight')))
      {
        $bolSave = false;
        $f->message = "Exceeded weight percentage, the remaining weight quota is ". $fltRemainingWeight;
      }
    }
    else
    {
      $arrTemp = $dataHrdEvaluationCriteria->find("id = '". $f->getValue('dataID')."'", "weight");
      if (($fltRemainingWeight + $arrTemp['weight']) < floatVal($f->getValue('dataWeight')))
      {
        $bolSave = false;
        $f->message = "Exceeded weight percentage, the remaining weight quota is ". ($fltRemainingWeight + $arrTemp['weight']);
      }
    }
*/

    if($bolSave)
    {
      for ($i = 1; $i <= 5; $i++)
      {
        $arrScore['dataScore'.$i] = getPostValue('dataScore'.$i);
        $arrScore['dataScore'.$i.'Note'] = getPostValue('dataScore'.$i.'Note');
      }
      $data = array("id_category" => $f->getValue('dataIDCategory'),
                    "managerial"   => "t",
                    "date_from"   => $f->getValue('dataDateFrom'),
                    "date_thru"   => $f->getValue('dataDateThru'),
                    "criteria"    => $f->getValue('dataCriteria'),
                    "subheader"   => $f->getValue('dataSubheader'),
                    "weight"      => floatVal($f->getValue('dataWeight')),
                    "score1"      => $arrScore['dataScore1'],
                    "score2"      => $arrScore['dataScore2'],
                    "score3"      => $arrScore['dataScore3'],
                    "score4"      => $arrScore['dataScore4'],
                    "score5"      => $arrScore['dataScore5'],
                    "score1_note" => $arrScore['dataScore1Note'],
                    "score2_note" => $arrScore['dataScore2Note'],
                    "score3_note" => $arrScore['dataScore3Note'],
                    "score4_note" => $arrScore['dataScore4Note'],
                    "score5_note" => $arrScore['dataScore5Note'],
        );
      // simpan data -----------------------
      $bolSuccess = false;
      if ($isNew)
      {
        // data baru
        $bolSuccess = $dataHrdEvaluationCriteria->insert($data);
        if ($bolSuccess)
        {
          $f->setValue('dataID', $dataHrdEvaluationCriteria->getLastInsertId());
        }
      } 
      else 
      {
        $bolSuccess = $dataHrdEvaluationCriteria->update(/*pk*/"id='".$f->getValue('dataID')."'", /*data to update*/ $data);
      }
      $f->message = $dataHrdEvaluationCriteria->strMessage;
    }
  } // saveData
  
  // fungsi untuk menghapus data
  function deleteData() 
  {
    global $myDataGrid;
  
    $arrKeys = array();
    foreach ($myDataGrid->checkboxes as $strValue)
      $arrKeys['id'][] = $strValue;

    $dataHrdEvaluationCriteria = new cHrdEvaluationCriteria();    
    $dataHrdEvaluationCriteria->deleteMultiple($arrKeys);
    
    $myDataGrid->message = $dataHrdEvaluationCriteria->strMessage;
  } //deleteData
  function printScoreList($params)
  {
    extract($params);
    global $bolPrint;
    if ($bolPrint)
      return stripslashes($value);
    else
    {
      return "<table class=\"gridTable\" width=\"100%\">
               <tr><td width=25>".$record['score1']."</td><td>&nbsp;".$record['score1_note']."</td></tr>
               <tr><td>".$record['score2']."</td><td>&nbsp;".$record['score2_note']."</td></tr>
               <tr><td>".$record['score3']."</td><td>&nbsp;".$record['score3_note']."</td></tr>
               <tr><td>".$record['score4']."</td><td>&nbsp;".$record['score4_note']."</td></tr>
               <tr><td>".$record['score5']."</td><td>&nbsp;".$record['score5_note']."</td></tr>
               </table>";
    } 
  }
?>