<?php
  include_once('../global/session.php');
  include_once('global.php');
  include_once('../global/common_data.php');
  include_once('../global/employee_function.php');
  include_once('../includes/datagrid2/datagrid.php');
  include_once('../includes/form2/form2.php');
  include_once('../classes/hrd/hrd_evaluation_category.php');
  include_once('../classes/hrd/hrd_evaluation_criteria.php');
  include_once('../classes/hrd/hrd_evaluation_criteria_employee.php');
  include_once('../classes/hrd/hrd_evaluation_detail.php');
  include_once('../classes/hrd/hrd_evaluation_master.php');
  include_once('../classes/hrd/hrd_evaluation_feedback.php');
  include_once('../includes/tbs-3.7.0/plugins/opentbs/tbs_plugin_opentbs.php');
  include_once('../includes/tbs-3.7.0/plugins/excel/tbs_plugin_excel.php');

  $dataPrivilege = getDataPrivileges(basename($_SERVER['PHP_SELF']), $bolCanView, $bolCanEdit, $bolCanDelete, $bolCanApprove, $bolCanCheck);
  if (!$bolCanView) die(accessDenied($_SERVER['HTTP_REFERER']));

  //---- INISIALISASI ----------------------------------------------------
  $strWordsEvaluationEntry = getWords("evaluation entry");
  $strWordsEvaluationList = getWords ("evaluation list");
  $strWordsEvaluationApproval = getWords ("evaluation approval");

  $strWordsFinalResult = getWords ("final result");
  $intMainRow = 0;
  $DataGrid = "";
  $strLastSubheader= "";
  $strLastCategory= "";

  $db = new CdbClass;
  if($db->connect())
  {
    getUserEmployeeInfo();
    scopeData($strDataEmployee, $strDataSubSection, $strDataSection, $strDataDepartment, $strDataDivision, $_SESSION['sessionUserRole'], $arrUserInfo);

    //$strDataID          = getRequestValue('dataID');
	$strDataID 			= $_POST['dataID'];
    if($strDataID != "" ) 
    {
      $tblEvaluationMaster = new cHrdEvaluationMaster();
      $dataEvaluationMaster = $tblEvaluationMaster->findAll("id = $strDataID", "id, id_employee, id_evaluator, evaluation_period_from, evaluation_period_thru", "", null, 1, "id");
      $strDataIDEvaluator = $dataEvaluationMaster[$strDataID]['id_evaluator'];
      $strDataEmployee = getEmployeeCode($db, $dataEvaluationMaster[$strDataID]['id_employee']);
      $strDataDateFrom = $dataEvaluationMaster[$strDataID]['evaluation_period_from'];
      $strDataDateThru = $dataEvaluationMaster[$strDataID]['evaluation_period_thru'];
    }
    else
    {
      redirectPage("evaluation_list.php");
    }

    $arrEmployee = array();
    if ($strDataEmployee != "")
    {
      $arrEmployee  = getEmployeeInfoByCode($db, $strDataEmployee, "id , hrd_employee.position_code,employee_name, grade_code, division_code, department_code, section_code, sub_section_code ");
      $arrEvaluator  = getEmployeeInfoByID($db, $strDataIDEvaluator, "employee_id , employee_name");
      $infoEmployee = $arrEmployee['id']." - ". $arrEmployee['employee_name'];
		$strPositionCode = $arrEmployee['position_code'];
      $infoEvaluator = $arrEvaluator['employee_id']." - ". $arrEvaluator['employee_name'];
      if (count($arrEmployee) == 0) $strIDEmployee = "";
      else
      {
        $strIDEmployee = $arrEmployee['id'];
        $strManagerial = ($arrEmployee['grade_code'] >= 9) ? 't' : 'f';
        $tblEvaluationCriteriaEmployee = new cHrdEvaluationCriteriaEmployee();
        $tblEvaluationCriteria = new cHrdEvaluationCriteria();
        $tblCategory = new cHrdEvaluationCategory();
      }
    }
    else
      $strIDEmployee = "";


    if ($strIDEmployee != "" /*&& isValidEvaluation()*/)
    {
      
      $myDataGrid = new cDataGrid("formData", "DataGrid", "100%", "100%", false, false, false);
      //$myDataGrid->caption = strtoupper($strWordsEvaluationDetail);
      $myDataGrid->setAJAXCallBackScript(basename($_SERVER['PHP_SELF']));
      $myDataGrid->setPageLimit("all");
      $myDataGrid->pageSortBy = "category_sequence,subheader,created";
      $myDataGrid->addColumnNumbering(new DataGrid_Column("No", "", "", array('width'=>'30', 'nowrap'=>'')));
      $myDataGrid->addColumn(new DataGrid_Column(getWords("category"), "category",  "", array('width' => '100', 'nowrap' => ''), false, false, "","printNewCategory()", "", true, 30));
      $myDataGrid->addColumn(new DataGrid_Column(getWords("subheader"), "subheader",  "", array('width' => '100', 'nowrap' => ''), false, false, "","printNewSubheader()", "", true, 25));
      $myDataGrid->addColumn(new DataGrid_Column(getWords("key result area"), "criteria", "", "", false, false, "", "", "", true, 50));
      $myDataGrid->addColumn(new DataGrid_Column(getWords("target"), "target_achievement", "", array('align' => 'center', 'width' => '50'), false, false, "", "", "", true, 10));
      $myDataGrid->addColumn(new DataGrid_Column(getWords("accomplished"), "accomplishment","", array('align' => 'center', 'nowrap' => '', 'width' => '85'), false, false, "", "", "", true, 15));
      $myDataGrid->addColumn(new DataGrid_Column(getWords("weight")." (%)", "weight", "", array( 'align' => 'center', 'width' => '40'), false, false, "", "", "", true, 7));
      $myDataGrid->addColumn(new DataGrid_Column(getWords("parameter"), "score","", array('width' => '250', 'align' => 'center', 'nowrap' => '',), false, false, ""));
      $myDataGrid->addColumn(new DataGrid_Column(getWords("score"), "var_score","",  array('width' => '25', 'align' => 'center', 'nowrap' => '',), false, false));

      $myDataGrid->addColumn(new DataGrid_Column(getWords("result"), "result","", array('width' => '40', 'align' => 'center', 'nowrap' => ''), false, false));
      $myDataGrid->addColumn(new DataGrid_Column(getWords("note"), "achieved","",  array('width' => '75', 'align' => 'center', 'nowrap' => '',), false, false));
      //hidden column for score calculation
      $myDataGrid->addColumn(new DataGrid_Column("", "id_category",  array('style' => 'display:none'), array('style' => 'display:none'), false, false, "","", "", false));
      $myDataGrid->addColumn(new DataGrid_Column("", "category_weight",  array('style' => 'display:none'), array('style' => 'display:none'), false, false, "","", "", false));
      $myDataGrid->addColumn(new DataGrid_Column("", "category_sequence",  array('style' => 'display:none'), array('style' => 'display:none'), false, false, "","", "", false));
      //$myDataGrid->addButton("btnSave","btnSave","submit", getWords("save"),"onClick=\"javascript:return myClient.confirmSave();\"", "saveData()");
      //$myDataGrid->addButtonExportExcel("Export Excel", "Employee Evaluation.xls", getWords("Employee Evaluation Period ").$strDataDateFrom." to ".$strDataDateThru, getWords("employee").": ".$arrEmployee['employee_name'], getWords("evaluator").": ".$arrUserInfo['employee_name']);
      $myDataGrid->addButton("btnExcel2","btnExcel2","submit", getWords("Export Excel"),"", "");

      $myDataGrid->getRequest();  


      $strSQL    = "
                    SELECT t1.*, t2.category, t2.sequence AS category_sequence, t2.weight AS category_weight, '' as achieved, '' as score,'' as var_score, 0 as result
                    FROM
                    (
                    SELECT id, id_category, subheader, criteria, weight, target_achievement, accomplishment, score1, score2, score3, score4, score5, score1_note, score2_note, score3_note, score4_note, score5_note, date_from, date_thru 
                    FROM hrd_evaluation_criteria_employee 
                    WHERE is_last_updated = 't' AND active = 't' AND id_employee = '".$strIDEmployee."'
                    UNION 
                    SELECT id, id_category, subheader, criteria, weight, null AS target_achievement, null AS accomplishment, score1, score2, score3, score4, score5, score1_note, score2_note, score3_note, score4_note, score5_note, date_from, date_thru 
                    FROM hrd_evaluation_criteria WHERE managerial = '$strManagerial' ) AS t1
                    LEFT JOIN hrd_evaluation_category AS t2 ON t1.id_category = t2.id
                    WHERE date_from = '$strDataDateFrom' AND date_thru = '$strDataDateThru'  ";
					
					
	//uddin
	  //
	  $whereemployeegrade=" (position_code =''  or position_code ='$strPositionCode')";
      $strSQL    = "
                    SELECT t1.*, t2.category, t2.sequence AS category_sequence, t2.weight AS category_weight, '' as achieved, '' as score,'' as var_score, 0 as result
                    FROM
                    (
                    SELECT id, id_category, subheader, criteria, weight, target_achievement, accomplishment, score1, score2, score3, score4, score5, score1_note, score2_note, score3_note, score4_note, score5_note, date_from, date_thru 
                    FROM hrd_evaluation_criteria_employee 
                    WHERE is_last_updated = 't' AND active = 't' AND id_employee = '".$strIDEmployee."' 
                    UNION 
                    SELECT id, id_category, subheader, criteria, weight, null AS target_achievement, null AS accomplishment, score1, score2, score3, score4, score5, score1_note, score2_note, score3_note, score4_note, score5_note, date_from, date_thru 
                    FROM hrd_evaluation_criteria WHERE $whereemployeegrade  ) AS t1
                    LEFT JOIN hrd_evaluation_category AS t2 ON t1.id_category = t2.id
                    WHERE date_from = '$strDataDateFrom' AND date_thru = '$strDataDateThru'  $wherecategory ";
      $strSQLCOUNT  = "SELECT COUNT(*) AS total FROM ($strSQL) AS t0"; 
      $myDataGrid->totalData = $myDataGrid->getTotalData($db, $strSQLCOUNT);

      $myDataGrid->strAdditionalHtml .= 
        generateHidden("dataID", $strDataID, "").
        generateHidden("dataIDEmployee", $strIDEmployee, "").
        generateHidden("dataEmployee", $strDataEmployee, "").
        generateHidden("dataIDEvaluator", $strDataIDEvaluator, "").
        generateHidden("dataTotal", $myDataGrid->totalData , "").
        generateHidden("dataFrom", $strDataDateFrom, "").
        generateHidden("dataThru", $strDataDateThru, "");

      $dataset = $myDataGrid->getData($db, $strSQL);
      if ($strDataID != "")
      {
        $tblEvaluationDetail = new cHrdEvaluationDetail();
        $dataEvaluationDetail = $tblEvaluationDetail->findAll(array("id_evaluation" => $strDataID), "id_criteria || '_' || id_category as id_key, id_criteria, id_category, achieved, score, var_score, result", "id_criteria",  null, null, "id_key");
        foreach($dataset as $intKey => $arrDetailCriteria)
        {
          if (isset($dataEvaluationDetail[$arrDetailCriteria['id']."_".$arrDetailCriteria['id_category']]))
          {
            $dataset[$intKey]['achieved'] = $dataEvaluationDetail[$arrDetailCriteria['id']."_".$arrDetailCriteria['id_category']]['achieved'];
            $dataset[$intKey]['score'] = $dataEvaluationDetail[$arrDetailCriteria['id']."_".$arrDetailCriteria['id_category']]['score'];
            $dataset[$intKey]['var_score'] = $dataEvaluationDetail[$arrDetailCriteria['id']."_".$arrDetailCriteria['id_category']]['var_score'];
            $dataset[$intKey]['result'] = $dataEvaluationDetail[$arrDetailCriteria['id']."_".$arrDetailCriteria['id_category']]['result'];
          }
        }
      }
      //echo "<pre>";print_r($dataset);echo "</pre>";die("");
      $myDataGrid->bind($dataset);
    }
    else if(isset($_POST['btnShow']) && $strIDEmployee != "") 
      $DataGrid  = "<br><p><h2>Please make sure that total weight of KRA percentage (general and individual) is exactly 100%</h2></p>";
    else if(isset($_POST['btnShow']) && $strIDEmployee == "")
      $DataGrid  = "<br><p><h2>Please fill in valid employee ID and make sure he or she is within your organization subline</h2></p>";
    else $DataGrid  = "";

    if ($strDataID != "")
    {
      //datagrid2 = score summary by category
      calculateScore($myDataGrid);
      $myDataGrid2 = new cDataGrid("formData2", "DataGrid2", "100%", "100%", false, false, false);
      $myDataGrid2->caption = strtoupper($strWordsFinalResult);
      $myDataGrid2->setAJAXCallBackScript(basename($_SERVER['PHP_SELF']));
      $myDataGrid2->setPageLimit("all");
      $myDataGrid2->pageSortBy = "sequence";
      $myDataGrid2->addColumnNumbering(new DataGrid_Column("No", "", "", array('width'=>'30', 'nowrap'=>'')));
      $myDataGrid2->addColumn(new DataGrid_Column(getWords("category"), "category",  "", array('width' => '48%', 'nowrap' => ''), false, false));
      $myDataGrid2->addColumn(new DataGrid_Column(getWords("weight")." (%)", "weight",  "", array('nowrap' => ''), false, false));
      $myDataGrid2->addColumn(new DataGrid_Column(getWords("score"), "score",  "", array('nowrap' => ''), false, false));
      $myDataGrid2->addColumn(new DataGrid_Column(getWords("result"), "result",  "", array('nowrap' => ''), false, false, null, null, null, null, null, null, null, true));
      $myDataGrid2->totalData = count($arrCategoryScore);
      $myDataGrid2->bind($arrCategoryScore);
      $DataGrid2 = $myDataGrid2->render();

      //datagrid3 = feedback by evaluator
      $myDataGrid3 = new cDataGrid("formData3", "DataGrid", "100%", "100%", false, false, false);
      $myDataGrid3->caption = strtoupper(getWords("feedback by evaluator"));
      $myDataGrid3->setAJAXCallBackScript(basename($_SERVER['PHP_SELF']));
      $myDataGrid3->setPageLimit("all");
      $myDataGrid3->addColumnNumbering(new DataGrid_Column("No", "", "", array('width'=>'30', 'nowrap'=>'')));
      //$myDataGrid3->addColumn(new DataGrid_Column(getWords("category"), "for_evaluator",  "", array('width' => '50', 'nowrap' => ''), false, false, "","printFeedbackCategory()", "", true, 30));
      $myDataGrid3->addColumn(new DataGrid_Column(getWords("question"), "question", array('width' => '48%'), "", false, false, "", "", "", true, 50));
      $myDataGrid3->addColumn(new DataGrid_Column(getWords("answer"), "answer", "", "", false, false, "", "", "", true, 50));

      $myDataGrid3->getRequest();  
      $strSQL    = "SELECT *, '' as answer FROM hrd_evaluation_feedback_criteria WHERE for_evaluator = TRUE";
      $strSQLCOUNT  = "SELECT COUNT(*) AS total FROM ($strSQL) AS t0"; 
      $myDataGrid3->totalData = $myDataGrid3->getTotalData($db, $strSQLCOUNT);
      //$myDataGrid3->addButton("btnSave","btnSave","button", getWords("save"), "onClick=\"document.formData.btnSave.click();\"");
      $dataset3 = $myDataGrid3->getData($db, $strSQL);
      $tblEvaluationFeedback = new cHrdEvaluationFeedback();
      $dataEvaluationFeedback = $tblEvaluationFeedback->findAll(array("id_evaluation" => $strDataID, "for_evaluator" => 't'), null, "sequence",  null, null, "question");
      foreach($dataset3 as $key => $arrDetailFeedback)
      {
        $intKey = $key + 1;
        if (isset($dataEvaluationFeedback[$arrDetailFeedback['question']]))
          $dataset3[$key]['answer'] = $dataEvaluationFeedback[$arrDetailFeedback['question']]['answer'];
      }
      $myDataGrid3->bind($dataset3);
      $DataGrid3  = $myDataGrid3->render();

      //datagrid4 = feedback by employee
      $myDataGrid4 = new cDataGrid("formData3", "DataGrid", "100%", "100%", false, false, false);
      $myDataGrid4->caption = strtoupper(getWords("feedback by employee"));
      $myDataGrid4->setAJAXCallBackScript(basename($_SERVER['PHP_SELF']));
      $myDataGrid4->setPageLimit("all");
      $myDataGrid4->addColumnNumbering(new DataGrid_Column("No", "", "", array('width'=>'30', 'nowrap'=>'')));
      //$myDataGrid4->addColumn(new DataGrid_Column(getWords("category"), "for_evaluator",  "", array('width' => '50', 'nowrap' => ''), false, false, "","printFeedbackCategory()", "", true, 30));
      $myDataGrid4->addColumn(new DataGrid_Column(getWords("question"), "question", array('width' => '48%'), "", false, false, "", "", "", true, 50));
      if (isMe($strIDEmployee))
      {
        $myDataGrid4->addColumn(new DataGrid_Column(getWords("answer"), "answer","",  array('width' => '250', 'align' => 'center', 'nowrap' => '',), false, false, "", "printFeedbackAnswer()", "", false));
        $myDataGrid4->addColumn(new DataGrid_Column(getWords("answer"), "answer", array('width' => '250', 'style' => 'display:none'), array('width' => '100', 'style'=>'display:none'), false, false, "", "", "", true, 5));
        $myDataGrid4->addButton("btnSave","btnSave","submit", getWords("save"),"onClick=\"javascript:return myClient.confirmSave();\"", "saveData()");
      }
      else
        $myDataGrid4->addColumn(new DataGrid_Column(getWords("answer"), "answer", "", "", false, false, "", "", "", true, 50));

      $myDataGrid4->getRequest();  
      $strSQL    = "SELECT *, '' as answer FROM hrd_evaluation_feedback_criteria WHERE for_evaluator = FALSE";
      $strSQLCOUNT  = "SELECT COUNT(*) AS total FROM ($strSQL) AS t0"; 
      $myDataGrid4->totalData = $myDataGrid4->getTotalData($db, $strSQLCOUNT);

      $dataset4 = $myDataGrid4->getData($db, $strSQL);
      $dataEvaluationFeedback = $tblEvaluationFeedback->findAll(array("id_evaluation" => $strDataID, "for_evaluator" => 'f'), null, "sequence",  null, null, "question");
      foreach($dataset4 as $key => $arrDetailFeedback)
      {
        $intKey = $key + 1;
        if (isset($dataEvaluationFeedback[$arrDetailFeedback['question']]))
          $dataset4[$key]['answer'] = $dataEvaluationFeedback[$arrDetailFeedback['question']]['answer'];
      }
      $myDataGrid4->strAdditionalHtml .= generateHidden("dataTotalFeedback", $intKey);
      $myDataGrid4->bind($dataset4);
      $DataGrid4  = $myDataGrid4->render();

    
    }
    else 
    {
      $DataGrid2 = "";
      $DataGrid3 = "";
      $DataGrid4 = "";
    }    
    $DataGrid = (isset($myDataGrid)) ? $myDataGrid->render() : "" ;


  }

  if (getRequestValue('btnExcel2') == "Export Excel") {
      $cat_before = "";
      $subheader_before = "";
      foreach($dataset as $intKey => $arrDetailCriteria) {
            $dataset[$intKey]['category_new'] = $dataset[$intKey]['category'] . " (".$dataset[$intKey]['category_weight']."%)";
            $dataset[$intKey]['criteria'] = trim($dataset[$intKey]['criteria']);
            if ($dataset[$intKey]['category'] == $cat_before) {
               $cat_before = $dataset[$intKey]['category'];
               $dataset[$intKey]['category'] = "";
               $dataset[$intKey]['category_new'] = "";
            }
            else {
               $cat_before = $dataset[$intKey]['category'];
            }
            if ($dataset[$intKey]['subheader'] == $subheader_before) {
               $subheader_before = $dataset[$intKey]['subheader'];
               $dataset[$intKey]['subheader'] = "";
            }
            else {
               $subheader_before = $dataset[$intKey]['subheader'];
            }
      }
      //echo "<pre>";print_r($dataset);echo "</pre>";die("");
    //die("excel here");
    $infoTglA = $strDataDateFrom;
    $infoTglB = $strDataDateThru;
    $TBS = new clsTinyButStrong370; // new instance of TBS
    $TBS->NoErr = true;
    //$TBS->Plugin(TBS_INSTALL, OPENTBS_PLUGIN); // load OpenTBS plugin
    //$TBS->LoadTemplate("templates/evaluation_view.ods");
    $TBS->PlugIn(TBS_INSTALL, TBS_EXCEL);
    $TBS->LoadTemplate('templates/evaluation_view.xml');
    $TBS->MergeBlock('a', $dataset);
    //$arrCategoryScore = array();
    $TBS->MergeBlock('b', $arrCategoryScore);
    $TBS->MergeBlock('c', $dataset3);
    $TBS->MergeBlock('d', $dataset4);
    //$TBS->Show(OPENTBS_DOWNLOAD, "evaluation_view.ods");
    //$TBS->Show(OPENTBS_DOWNLOAD+TBS_EXIT+OPENTBS_DEBUG_XML*1, "evaluation_view.ods");
    $TBS->Show(TBS_EXCEL_DOWNLOAD, "evaluation_view.xml");
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
//--------------------------------------------------------------------------------

  function printNewCategory($params)
  {
    global $strLastCategory;
    extract($params);
    $bolNew = ($value != $strLastCategory);
    $strLastCategory = $value;

    return ($bolNew) ? $strLastCategory." (".$record['category_weight']." %)" : "";
  }

  function printNewSubheader($params)
  {
    global $strLastSubheader;
    extract($params);
    $bolNew = ($value != $strLastSubheader);
    $strLastSubheader = $value;
    return ($bolNew) ? $strLastSubheader : "";
  }

  function printFeedbackAnswer($params)
  {
    extract($params);
    return generateHidden("detailFeedbackQuestion_".$counter, $record['question'])." ".generateTextArea("detailFeedbackAnswer_".$counter, $value, "cols=110 maxlength=255");
  }

  // fungsi untuk cek apakah employee yang di evaluasi merupakan bawahan evaluator
  // if true return id_employee else return ""
  function assignIDEmployeeForValidScope() 
  {
    global $f;
    global $strIDEmployee;
    global $arrEmployee;
    if ($f->getValue('dataDivision') != "" && $f->getValue('dataDivision') != $arrEmployee['division_code']) $strIDEmployee = "";
    else if ($f->getValue('dataDepartment') != "" && $f->getValue('dataDepartment') != $arrEmployee['department_code']) $strIDEmployee = "";
    else if ($f->getValue('dataSection') != "" && $f->getValue('dataSection') != $arrEmployee['section_code']) $strIDEmployee = "";
    else if ($f->getValue('dataSubSection') != "" && $f->getValue('dataSubSection') != $arrEmployee['sub_section_code']) $strIDEmployee = "";
  }

  function checkEvaluationRecord($strIDEvaluator, $strIDEmployee, $strDateFrom, $strDateThru) 
  {
    global $strDataID;
    $tblEvaluationMaster = new cHrdEvaluationMaster();
    $dataEvaluationMaster = $tblEvaluationMaster->find(array("id_evaluator" => $strIDEvaluator, "id_employee" => $strIDEmployee, "evaluation_period_from" => $strDateFrom, "evaluation_period_thru" => $strDateThru), "id");
    if ($strDataID != "") return true;
    else if(count($dataEvaluationMaster) == 0) return false;
    else 
    {
      $strDataID = $dataEvaluationMaster['id'];
      return true;
    }

  }
  //fungsi untuk cek total weight category dan criteria

  function isValidEvaluation()
  {
    // 1. Karyawan harus sudah mengisi KRA individu, dan total weightnya harus 100%
    // 2. Total dari weight evaluaton category = 100%
    // 3. Cek total criteria weight pada setiap category yang jenisnya non individual_evaluation (karena yang individual sudah di cek)
    global $strIDEmployee, $strManagerial, $strDataDateFrom, $strDataDateThru, $tblCategory, $tblEvaluationCriteria, $tblEvaluationCriteriaEmployee;

    $dataWeight = $tblEvaluationCriteriaEmployee-> find(array("id_employee" => $strIDEmployee, "date_from" => $strDataDateFrom, "date_thru" => $strDataDateThru, "active" => 't', "is_last_updated" => 't'), "SUM(weight) as total_weight");
    if ($dataWeight['total_weight'] != 100) return false;
    else
    {
      $dataWeight = $tblCategory-> find("", "SUM(weight) as total_weight");
      if ($dataWeight['total_weight'] != 100) return false;
      else
      {
        $dataCategory = $tblCategory->findAll("individual_evaluation = 'f'", "id, category, weight, individual_evaluation", "sequence", null, null, "id"); 
        foreach($dataCategory as $strIDCategory => $arrCategoryDetail)
        {
          $dataWeight = $tblEvaluationCriteria-> find(array("id_category" => $strIDCategory, "managerial" => $strManagerial, "date_from" => $strDataDateFrom, "date_thru" => $strDataDateThru,), "SUM(weight) as total_weight");
          if ($dataWeight['total_weight'] != 100) return false;
        }
      }
    }
    return true;
  }    
  function calculateScore($myDataGrid)
  {
    global $arrCategoryScore;
    //calculate score
    $fltTotalScore = 0;
    $arrCategoryScore = array();
    for($i = 1; $i <= $myDataGrid->totalData; $i++)
    {
      if (!isset($arrCategoryScore[$myDataGrid->dataset[$i-1]['id_category']]))
      {
        $arrCategoryScore[$myDataGrid->dataset[$i-1]['id_category']]['category'] = $myDataGrid->dataset[$i-1]['category'] ;
        $arrCategoryScore[$myDataGrid->dataset[$i-1]['id_category']]['sequence'] = $myDataGrid->dataset[$i-1]['category_sequence'] ;
        $arrCategoryScore[$myDataGrid->dataset[$i-1]['id_category']]['weight'] = $myDataGrid->dataset[$i-1]['category_weight'] ;
        $arrCategoryScore[$myDataGrid->dataset[$i-1]['id_category']]['score'] = $myDataGrid->dataset[$i-1]['result'] ;
      }
      else
        $arrCategoryScore[$myDataGrid->dataset[$i-1]['id_category']]['score'] += $myDataGrid->dataset[$i-1]['result'] ;
    }
    foreach($arrCategoryScore as $strIDCategory => $arrCategoryDetail)
    {
      $arrCategoryScore[$strIDCategory]['result'] = /*$arrCategoryScore[$strIDCategory]['weight']/100 * */ $arrCategoryScore[$strIDCategory]['score'];
      $fltTotalScore += $arrCategoryScore[$strIDCategory]['result'];
    }
    return $fltTotalScore;
  }
  // fungsi untuk menyimpan data
  function saveData() 
  {
    global $db;
    global $f;
    global $strDataID;


    if ($db->connect())
    {
      $strModifiedBy = $_SESSION['sessionUserID'];

      // hanya simpan evaluation feedback by employee
      // hapus data lama, insert data baru
      $tblEvaluationFeedback = new cHrdEvaluationFeedback();
      $tblEvaluationFeedback->delete(array("id_evaluation" => $strDataID, "for_evaluator" => "f"));
      for($i = 1; $i <= getPostValue('dataTotalFeedback'); $i++)
      {
        $data = array();
        $data['id_evaluation'] = $strDataID;
        $data['question'] = getPostValue("detailFeedbackQuestion_".$i);
        $data['answer'] = getPostValue("detailFeedbackAnswer_".$i);
        $data['modified_by'] = $strModifiedBy;
        $data['for_evaluator'] = "f";
        $bolSuccess = $tblEvaluationFeedback->insert($data);
      }

    }


    $f->message = $tblEvaluationFeedback->strMessage;
  } // saveData
  

?>
