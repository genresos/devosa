<?php
  include_once('../global/session.php');
  include_once('global.php');
  include_once('../classes/datagrid_modified.php');
  include_once('../includes/form2/form2.php');
  include_once('../global/common_data.php');
	$dataPrivilege = getDataPrivileges(basename($_SERVER['PHP_SELF']), $bolCanView, $bolCanEdit, $bolCanDelete, $bolCanApprove, $bolCanAcknowledge, $bolCanApprove2);
  if (!$bolCanView) die(getWords('view denied'));

  //cek dulu apakah ada punya hak mengakses FKR
  $dataPrivFKR = getDataPrivileges("fkr_list.php", $bolViewFKR, $bolEditFKR, $bolDeleteFKR, $bolApproveFKR);

  $db = new CdbClass;
	if ($db->connect()){
		getUserEmployeeInfo();
  	$arrUserList = getAllUserInfo($db);
  }
  $strDataID = getPostValue('dataID');
  $isNew = ($strDataID == "");
  $emptyData = array("value" => "", "text" => "", "selected" => true);

    $f = new clsForm("form1", 2, "100%", "");
    $f->disableFormTag();
    $f->caption = strtoupper($strWordsFILTERDATA);
    $f->addHidden("dataID", $strDataID);
    $f->addInput(getWords("candidate name"), "candidate_name", "", array("size" => 20, "maxlength" => 20), "string", true, true, true);
    $f->addSelect(getWords("gender"), "gender", getDataListGender("", true, $emptyData), array(), "string", false, true, true);
    $f->addInput(getWords("age"), "age", "", array("size" => 30), "string", false);
    $f->addSelect(getWords("position"), "position", getDataListPosition($arrData['position_code'], true, $emptyData), array("style" =>"width:400"), "string", false, true, true);
    $f->addSelect(getWords("education"), "education_level_code", getDataListEducationLevel("", true, $emptyData), array(), "string", false, true, true);
    $f->addInput(getWords("application date from"), "application_date_from", "", array(), "date", false, true, true);
    $f->addInput(getWords("application date to"), "application_date_to", "", array(), "date", false, true, true);
		$f->addSelect(getWords("job reference"), "reference",getDataListReference(), array(), "string", false, true, true);
		$f->addSelect(getWords("created by"), "created_by", getDataListUser(), array("style" => "width:$strDefaultWidthPx"), "", false);
    $f->addSubmit("btnSearch", getWords("search"), array("onClick" => "javascript:doSearch()"), true, true, "", "", "");
    $f->addSubmit("btnPrint", getWords("print"), array("onClick" => "javascript:printList()"), true, true, "", "", "");
    $f->addSubmit("btnExportXLS", getWords("excel"), array("onClick" => "javascript:exportExcel()"), true, true, "", "", "");

    $formInput = $f->render();

  $bolPrint = false;
  $bolExcel = false;
  if (isset($_POST['btnPrint']))
    $bolPrint = true;
  if (isset($_POST['btnExportXLS']))
    $bolExcel = true;

  $myDataGrid = new cDataGridNew("form1","DataGrid1");
  $myDataGrid->disableFormTag();
  $myDataGrid->caption = getWords("candidate");
	$myDataGrid->pageSortBy = 'id DESC';
  if ($bolPrint || $bolExcel)
  {
    $myDataGrid->addColumnNumbering(new DataGrid_Column("No", "", array("rowspan" => 2, 'width'=>30), array('nowrap'=>'')));
    $myDataGrid->addColumn(new DataGrid_Column(getWords("name"), "candidate_name", array("rowspan" => 2, 'width' => 150), array('nowrap' => 'nowrap'), true, true, "", "", "string", true, 32));
    $myDataGrid->addColumn(new DataGrid_Column(strtoupper(getWords("mrf no.")), "mrf_number", array("rowspan" => 2, 'width' => 150), array('nowrap' => 'nowrap'), false, false, "", "", "string", true, 16));
  }
  else
  {
    $myDataGrid->addColumnCheckbox(new DataGrid_Column("chkID", "id", array("rowspan" => 2, 'width' => 30), array('align'=>'center', 'nowrap' => 'nowrap')));
    $myDataGrid->addColumnNumbering(new DataGrid_Column("No", "", array("rowspan" => 2, 'width'=>30), array('nowrap'=>'')));
    $myDataGrid->addColumn(new DataGrid_Column(strtoupper(getWords("id")), "id", array("rowspan" => 2, 'width' => 150), array('nowrap' => 'nowrap'), true, true, "", "", "string", true, 32));
    $myDataGrid->addColumn(new DataGrid_Column(getWords("name"), "candidate_name", array("rowspan" => 2, 'width' => 150), array('nowrap' => 'nowrap'), true, true, "", "printViewLink()", "string", true, 32));
    $myDataGrid->addColumn(new DataGrid_Column(strtoupper(getWords("mrf no.")), "mrf_number", array("rowspan" => 2, 'width' => 150), array('nowrap' => 'nowrap'), false, false, "", "", "string", true, 16));
  }
  $myDataGrid->addColumn(new DataGrid_Column(getWords("sex"), "gender", array("rowspan" => 2, "width" => 40), array('nowrap' => 'nowrap'), true, false, "", "", "string", true, 4));
  $myDataGrid->addColumn(new DataGrid_Column(getWords("age"), "age", array("rowspan" => 2, "width" => 50), array('align' => 'right'), false, false, "", "", "integer", true, 4));
  $myDataGrid->addColumn(new DataGrid_Column(getWords("phone"), "phone", array("rowspan" => 2, "width" => 80), array('nowrap' => 'nowrap'), true, true, "", "", "string", true, 12));
  $myDataGrid->addColumn(new DataGrid_Column(getWords("email"), "email", array("rowspan" => 2, "width" => 100), array('nowrap' => 'nowrap'), true, true, "", "", "string", true, 18));
  $myDataGrid->addColumn(new DataGrid_Column(getWords("application"), "", array("colspan" => 3), array(), false, false, "", "", "string", true));
  $myDataGrid->addColumn(new DataGrid_Column(getWords("date"), "application_date", array(), array('nowrap' => 'nowrap'), true, false, "", "formatDate()", "string", true, 12));
  $myDataGrid->addColumn(new DataGrid_Column(getWords("position"), "position", array(), array('nowrap' => 'nowrap'), true, false, "", "", "string", true, 12));
  $myDataGrid->addColumn(new DataGrid_Column(getWords("entry date"), "candidate_entry_date", array("nowrap" => "nowrap"), array('nowrap' => 'nowrap'), true, false, "", "formatDate()", "string", true, 12));
  $myDataGrid->addColumn(new DataGrid_Column(getWords("marital status"), "marital_status", array("rowspan" => 2, "width" => 100), array('nowrap' => 'nowrap'), false, false, "", "", "string", true, 16));
  $myDataGrid->addColumn(new DataGrid_Column(getWords("job reference"), "reference", array("rowspan" => 2, "width" => 120), array('nowrap' => 'nowrap'), true, false, "", "", "string", true, 32));
  $myDataGrid->addColumn(new DataGrid_Column(getWords("job posting date"), "job_reference_date", array("rowspan" => 2, "nowrap" => "nowrap"), array('align' => 'center'), true, false, "", "formatDate()", "string", true, 12));

  $myDataGrid->addColumn(new DataGrid_Column(getWords("note"), "note", array("rowspan" => 2, "width" => 120), array(), true, false, "", "", "string", true, 12));
  $myDataGrid->addColumn(new DataGrid_Column(getWords("education"), "education_level_code", array("rowspan" => 2, "width" => 120), array(), true, false, "", "", "string", true, 12));

  $myDataGrid->addColumn(new DataGrid_Column(getWords("login id"), "candidate_code", array("rowspan" => 2, "width" => 60), array('nowrap' => 'nowrap'), true, false, "", "", "string", true, 12));
  $myDataGrid->addColumn(new DataGrid_Column(getWords("recruitment process"), "", array("colspan" => 2), array(), false, false, "", "", "string", true));
  if ($bolPrint || $bolExcel)
    $myDataGrid->addColumn(new DataGrid_Column(getWords("process"), "process2", array("width" => 90), array("nowrap" => "nowrap"), false, false, "", "", "string", true, 12));
  else
    $myDataGrid->addColumn(new DataGrid_Column(getWords("process"), "process", array("width" => 90), array("nowrap" => "nowrap"), false, false, "", "", "string", true, 12));
  $myDataGrid->addColumn(new DataGrid_Column(getWords("result"), "result", array("width" => 75, "nowrap" => "nowrap"), array(), false, false, "", "", "string", true, 12));
	$myDataGrid->addColumn(new DataGrid_Column(getWords("created by"), "created_by", array("rowspan" => 2), array("nowrap" => "nowrap"), false, false, "", "", "string", true, 30));
  if (!($bolPrint || $bolExcel))
  {
    $myDataGrid->addColumn(new DataGrid_Column("", "", array("rowspan" => 2), array(), false, false, "", "printViewLink2()", "string", false));
    if ($bolCanEdit)
    {
      $myDataGrid->addColumn(new DataGrid_Column("", "", array("rowspan" => 2, 'width' => 45), array('align' => 'center', 'nowrap' => 'nowrap'), false, false, "","printEditLink()", "string", false));
      $myDataGrid->addColumn(new DataGrid_Column("", "", array("rowspan" => 2, 'width' => 45), array('align' => 'center', 'nowrap' => 'nowrap'), false, false, "","printPrintLink()", "string", false));
      if ($bolViewFKR)
        $myDataGrid->addColumn(new DataGrid_Column("", "", array("rowspan" => 2, 'width' => 60), array('align' => 'center', 'nowrap' => 'nowrap'), false, false, "","printFKRLink()", "string", false));
      $myDataGrid->addColumn(new DataGrid_Column(getWords("login"), "", array("rowspan" => 2, 'width' => 60), array('align' => 'center', 'nowrap' => 'nowrap'), false, false, "","printLogin()", "string", false));
    }
  }
  if ($bolCanDelete)
    $myDataGrid->addSpecialButton("btnDelete","btnDelete","submit",getWords("delete"),"onClick=\"javascript:return myClient.confirmDelete();\"","deleteData()");

  $myDataGrid->getRequest();
  //--------------------------------
  //get Data and set to Datagrid's DataSource by set the data binding (bind method)

  $tbl = new cModel("hrd_candidate", "candidate");

  $arrCriteria = array();
  if ($f->getValue('candidate_name') != '') $arrCriteria[] = "upper(candidate_name) LIKE '%".strtoupper($f->getValue('candidate_name'))."%'";
  if ($f->getValue('gender') != '') $arrCriteria[] = "gender =".intval($f->getValue('gender'));
  if ($f->getValue('reference') != '')$arrCriteria[] = "reference ='".($f->getValue('reference')."'");
  if ($f->getValue('position') != '') $arrCriteria[] = "upper(position) LIKE '%".strtoupper($f->getValue('position'))."%'";
  if ($f->getValue('education_level_code') != '') $arrCriteria[] = "upper(education_level_code) LIKE '%".strtoupper($f->getValue('education_level_code'))."%'";
  if ($f->getValue('created_by') != '')$arrCriteria[] = "created_by ='".($f->getValue('created_by')."'");
  if ($f->getValue('age') != '')
  {
    $arrCriteria[] = "EXTRACT(year FROM age(birthdate)) = '".strtoupper($f->getValue('age'))."'";
  }
  if ($f->getValue('application_date_from') != '' && $f->getValue('application_date_to') != '')
    $arrCriteria[] = "application_date between '".($f->getValue('application_date_from'))."' AND '".($f->getValue('application_date_to'))."' ";

  $strCriteria = implode(" AND ", $arrCriteria);
  if ($strCriteria != "") $strCriteria = " AND ".$strCriteria;
  $strCriteriaFlag = $myDataGrid->getCriteria().$strCriteria;

  $myDataGrid->totalData = $tbl->findCount($strCriteriaFlag);
  if ($bolExcel)
  {
    $myDataGrid->DATAGRID_RENDER_OUTPUT = DATAGRID_RENDER_EXCEL_BIFF;
    $myDataGrid->strFileNameXLS = "candidate_result.xls";
    $myDataGrid->strTitle1 = "Candidate List";
    $myDataGrid->strTitle2 = "Printed Date: ".date("d/m/Y h:i:s");

    $strPageLimit = null;
    $strPageNumber = null;
  }
  elseif ($bolPrint)
  {
    $myDataGrid->DATAGRID_RENDER_OUTPUT = DATAGRID_RENDER_PRINT_HTML;
    $myDataGrid->strTitle1 = "Candidate List";
    $myDataGrid->strTitle2 = "Printed Date: ".date("d/m/Y h:i:s");

    $strPageLimit = null;
    $strPageNumber = null;
  }
  else
  {
    $strPageLimit = $myDataGrid->getPageLimit();
    $strPageNumber = $myDataGrid->getPageNumber();
  }
  $dataset = $tbl->findAll($strCriteriaFlag,
                           "*,(EXTRACT(YEAR FROM AGE(birthdate))) AS \"age\" ", //all field
                           $myDataGrid->getSortBy(),
                           $strPageLimit,
                           $strPageNumber);
  if (count($dataset) > 0)
  {
    //find id_candidate
    $arrIdCandidate = array();
    foreach($dataset as $rowDb)
    {
      $arrIdCandidate[] = $rowDb['id'];
    }
    $strCriteriaCandidate = implode(", ", $arrIdCandidate);
    $tblRecruitmentProcess = new cModel("hrd_recruitment_process");
    $tblRecruitmentProcessDetail = new cModel("hrd_recruitment_process_detail");

    $arrProcess = $tblRecruitmentProcess->findAll("id_candidate IN ($strCriteriaCandidate)", null, "id_candidate", null, null, "id_candidate");
    foreach($arrProcess as &$row)
    {
      $row['process'] = "<li>".getWords("invitation")."</li>";
      $row['process2'] = getWords("invitation");
      if ($arrProcessDetail = $tblRecruitmentProcessDetail->findAll("
        id_recruitment_process = ".$row['id']." AND
        schedule_date is not null AND process_date is not null ",
        "schedule_date, subject, process_name", "schedule_date"))
      {

        foreach($arrProcessDetail as $detail)
        {
          $row['process'] .= "<li>".(($detail['subject'] == "") ? $detail['process_name'] : $detail['subject'])."</li>";
          $row['process2'] .= "\n".(($detail['subject'] == "") ? $detail['process_name'] : $detail['subject']);
        }
      }
      $row['process'] = "<ol>".$row['process']."</ol>";
    }
  }
  $tblUser = new cModel("adm_user");
  $arrDataUser = $tblUser->findAll(null, "id_adm_user, login_name", null, null, null, "login_name");

  $tblFKR = new cModel("hrd_fkr");
  $arrDataFKR = $tblFKR->findAll(null, "id, id_candidate", null, null, null, "id_candidate");

  $tblEdu = new cModel("hrd_education_level", "education");
	$tblRN = new cModel("hrd_recruitment_need");
  foreach($dataset as $key => &$rowDb)
  {
    $rowDb['gender'] = ($rowDb['gender'] == FEMALE) ? "F" : "M";
    if (!isset($ARR_DATA_MARITAL_STATUS_CANDIDATE[$rowDb['marital_status']]))
      $ARR_DATA_MARITAL_STATUS_CANDIDATE[$rowDb['marital_status']] = "";

     $rowDb['marital_status'] = getWords($ARR_DATA_MARITAL_STATUS_CANDIDATE[$rowDb['marital_status']]);

     $rowDb['status'] = REQUEST_STATUS_NEW; // default saja, untuk tampilan new, jika ada result disesuaikan.
    if (isset($arrProcess[$rowDb['id']]))
    {
      $intResult = $arrProcess[$rowDb['id']]['result'];
      if ($intResult == 1 && !isset($arrDataFKR[$rowDb['id']])) $rowDb['status'] = REQUEST_STATUS_NEW; // biar warnanya beda

      $rowDb['note'] = $arrProcess[$rowDb['id']]['note'];
      $rowDb['process'] = $arrProcess[$rowDb['id']]['process'];
      $rowDb['process2'] = $arrProcess[$rowDb['id']]['process2'];
      $rowDb['id_process'] = $arrProcess[$rowDb['id']]['id'];
      //Penyesuaian Resul dan status system
      if ($intResult == 3){
      	$rowDb['status'] = REQUEST_STATUS_DENIED;
      }else if ($intResult == 1){
      	$rowDb['status'] = REQUEST_STATUS_APPROVED_2;
      }
      $rowDb['result'] = getWords($ARRAY_RECRUITMENT_RESULT[$arrProcess[$rowDb['id']]['result']]);
    }
    else
    {
      $rowDb['note'] = "";
      $rowDb['id_process'] = "";
      $rowDb['process'] = "";
      $rowDb['process2'] = "";
      $rowDb['result'] = "";
    }

    if ($rowDb['education_level_code'] != "")
    {
      $arrTmp = $tblEdu->find(array("code" => $rowDb['education_level_code']), "name");
      if (isset($arrTmp['name'])) $rowDb['education_level_code'] = $arrTmp['name'];
    }
    if (isset($arrUserList[$rowDb['created_by']])){
			$rowDb['created_by'] = $arrUserList[$rowDb['created_by']]['name'];
		}else{
			$rowDb['created_by'] = "";
		}
		if (!empty($rowDb['id_recruitment_need'])){
			$mrfData = $tblRN->findById($rowDb['id_recruitment_need']);
			$rowDb['mrf_number'] = $mrfData['request_number'];
			if (!empty($arrUserInfo['branch_code']) && $_SESSION['sessionUserRole'] == ROLE_BRANCH_ADMIN && $mrfData['branch_code'] != $arrUserInfo['branch_code']){
				unset($dataset[$key]);
			}
		}else{
			$rowDb['mrf_number'] = '';
		}
  }
  //bind Datagrid with array dataset
  $myDataGrid->bind($dataset);
  $DataGrid = $myDataGrid->render();

  $strConfirmDelete = getWords("are you sure to delete this selected data?");
  $strConfirmSave = getWords("do you want to save this entry?");


  $tbsPage = new clsTinyButStrong ;

  //write this variable in every page
  $strPageTitle = getWords($dataPrivilege['menu_name']);
  $pageIcon = "../images/icons/".$dataPrivilege['icon_file'];
  $strTemplateFile = getTemplate("candidate_search.html");
  //------------------------------------------------
  //Load Master Template
  $tbsPage->LoadTemplate("../templates/master2.html") ;
  $tbsPage->Show() ;
//--------------------------------------------------------------------------------

  function printViewLink($params)
  {
    extract($params);
    return "<a href='candidate_edit.php?dataID=" .$record['id']. "'>".$record['candidate_name']."</a>";
  }

  function printViewLink2($params)
  {
    extract($params);
    $strProcessID = "dataID=".$record['id_process'];
	if (strtolower($record['result']) == 'accepted')
	return "<a href=\"javascript:alert('Process is finish')\">".getWords('process')."</a>";
	else
    return "<a href='recruitment_process_edit.php?dataCandidateID=" .$record['id']."&$strProcessID'>".getWords("process")."</a>";
  }

  function printEditLink($params)
  {
    extract($params);
    return "<a href='candidate_edit.php?dataID=" .$record['id']. "'>" .getWords('edit'). "</a>";
  }

  function printPrintLink($params)
  {
    extract($params);
    //return "<a href=\"javascript:openWindowDialog('candidate_print.php?dataID=" .$record['id']. "')\">" .getWords('print'). "</a>";
    return "<a href=\"candidate_print.php?dataID=" .$record['id']. "\" target='_blank'>" .getWords('print'). "</a>";
  }

  // tampilan info FKR
  function printFKRLink($params)
  {
    extract($params);
    global $arrDataFKR;
    if (isset($arrDataFKR[$record['id']]))
      return "<a href=\"javascript:openViewWindow('View FKR', 'fkr_edit.php?view=1&dataID=".$arrDataFKR[$record['id']]['id']."', 700, 650)\">" .getWords('view'). " FKR</a>";
    else if (strtolower($record['result']) == 'accepted')
      return "<a href=\"fkr_edit.php?dataCandidateID=".$record['id']."\">" .getWords('create'). " FKR</a>";
    else
      return "";
  }

  function printLogin($params)
  {
    extract($params);
    global $arrDataUser;

    if (isset($arrDataUser[$record['candidate_code']]))
      return "&radic;";
    else
      return "";
  }

  function formatDate__($params) // sudah ada di form_functions
  {
    extract($params);
    return pgDateFormat($value, "d-M-y");
  }

  // fungsi untuk menghapus data
  function deleteData()
  {
    global $myDataGrid;

    $arrKeys = array();
    foreach ($myDataGrid->checkboxes as $strValue)
      $arrKeys['id'][] = $strValue;

    $tbl = new cModel("hrd_candidate");
    if ($tbl->deleteMultiple($arrKeys))
      $myDataGrid->message = $tbl->strMessage;
    else
      $myDataGrid->errorMessage = $tbl->strMessage;
  } //deleteData

?>
