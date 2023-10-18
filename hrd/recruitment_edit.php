<?php

  include_once('../global/session.php');
  include_once('global.php');
  include_once('../global/common_data.php');
  include_once('../global/common_function.php');
  include_once('../global/form_document.php');
  include_once('../includes/form2/form2.php');
  include_once '../global/email_func.php';
  //include_once("../includes/krumo/class.krumo.php");

  // Hak Akses-----------------------------------------------------------------------------------------------------------------
  $dataPrivilege = getDataPrivileges(basename($_SERVER['PHP_SELF']), $bolCanView, $bolCanEdit, $bolCanDelete, $bolCanApprove);
  if (!$bolCanView) die(getWords('view denied'));
  //----------------------------------------------------------------------------------------------------------------------------

  // Label form --------------------------------------------------
  $strConfirmSave = getWords("do you want to save this entry?");
  $strWordsDataEntry = getWords("data entry");
  $strWordsMRFList = getWords("list of mrf");

  $strBody = "";
  // END ---------------------------------------------------------


if (isset($_POST['requestAjax'])){
$tbl = new cModel;
  if (isset($_POST['codeDivision'])){
	$strSQL  = "SELECT department_code,department_name FROM hrd_department where division_code='$_POST[codeDivision]'";
	$resDb = $tbl->query($strSQL);
	$arrData[]="";
	if (count($resDb)!=0){
		foreach($resDb as $loop){
			$arrData[]= $loop['department_code']." - ".$loop['department_name'];
		}
	}
	$data = implode(",", $arrData);
	echo $data;
	die();
  }

  if (isset($_POST['codeDepartment'])){
	$strSQL  = "SELECT section_code,section_name FROM hrd_section where department_code='$_POST[codeDepartment]'";
	$resDb = $tbl->query($strSQL);
	$arrData[]="";
	if (count($resDb)!=0){
		foreach($resDb as $loop){
			$arrData[]= $loop['section_code']." - ".$loop['section_name'];
		}
	}
	$data = implode(",", $arrData);
	echo $data;
	die();
  }

  if (isset($_POST['codeSection'])){
	$strSQL  = "SELECT sub_section_code,sub_section_name FROM hrd_sub_section where section_code='$_POST[codeSection]'";
	$resDb = $tbl->query($strSQL);
	$arrData[]="";
	if (count($resDb)!=0){
		foreach($resDb as $loop){
			$arrData[]= $loop['sub_section_code']." - ".$loop['sub_section_name'];
		}
	}
	$data = implode(",", $arrData);
	echo $data;
	die();
  }

  if (isset($_POST['codePosition'])){
	$strSQL  = "SELECT count(*) as jml FROM hrd_employee where position_code='$_POST[codePosition]'";
	$resDb = $tbl->query($strSQL);
	$jml=0;
	if (count($resDb)!=0){
		foreach($resDb as $loop){
			$jml= $loop['jml'];
		}
	}
	echo $jml;
	die();
  }

}

  if (isset($_GET['ajax']) && $_GET['ajax'] == 1) getDataAJAX();

  $db = new CdbClass;
  $strDataID = (isset($_REQUEST['dataID'])) ? $_REQUEST['dataID'] : "";
  $isNew = ($strDataID == "");
  if ($db->connect()){
    getUserEmployeeInfo();
    $arrUserList = getAllUserInfo($db);
    scopeData($arrUserInfo['employee_id'], $strDataSubSection, $strDataSection, $strDataDepartment, $strDataDivision, $_SESSION['sessionUserRole'], $arrUserInfo,$strDataBranch);
  }
  $tblMRF = new cModel("hrd_recruitment_need", getWords("manpower requisition form"));
  $arrData = getData($strDataID);
  $f = new clsForm("formInput", 1, "100%", "");
  $f->caption = strtoupper($strWordsINPUTDATA);
  $f->bolRequiredEntryBeforeSubmit = false;
  $f->caption = strtoupper(vsprintf(getWords("input data %s"), "MRF"));
  $f->message = getGetValue('message');
  $f->action = basename($_SERVER['PHP_SELF']);

  $f->addHidden("dataID", $strDataID);
  $emptyData = array("value" => "", "text" => "");

  $f->addFieldSet(getWords("form information"), 2);
  $f->addInput(getWords("MRF No."), "request_number", $arrData['request_number'], array("size" => 20), "string", true, true, true, "", "", true, array("width" => 120));

  if ($objUP->isUserEmployee())
  {
    $f->addHidden("recruitment_date", $arrData['recruitment_date']);
    $f->addInputAutoComplete(getWords("Request By"), "user_name", getDataEmployeeRequest($arrData['user_name']), "style='width:250px' ". $strReadonly,
	     "string", true);
    $f->addLabelAutoComplete("", "user_name", "");
    $f->addLabel(getWords("request date"), "label_recruitment_date", $arrData['recruitment_date']);
  }
  else
  {
  	$f->addInput(getWords("request date"), "recruitment_date", $arrData['recruitment_date'], array("style" =>"width:100"), "date", true, true, true);
  	$f->addInputAutoComplete(getWords("Request By"), "user_name", getDataEmployee($arrData['user_name']),"style='width:250px' ". $strReadonly,
	    "string", true);
    $f->addLabelAutoComplete("", "user_name", "");
    //bisa ubah tanggal

  }

  $f->addFieldSet(getWords("position information"), 2);

  $f->addSelect(getWords("company"), "id_company", getDataListCompany($arrData['id_company'], true), array("style" =>"width:250"), "string", true, true, true);
  if ($strDataBranch != "") $arrData['branch_code'] = $strDataBranch;
  $f->addSelect(getWords("branch office"), "branch_code", getDataListBranch($arrData['branch_code'], true), array("style" => "width:$strDefaultWidthPx", $ARRAY_DISABLE_GROUP['branch'] => $ARRAY_DISABLE_GROUP['branch']), "", true);
  $f->addSelect(getWords("branch contract"), "branch_contract_code", getDataListBranch($arrData['branch_contract_code'], true), array("style" => "width:$strDefaultWidthPx"), "", true);
  if ($strDataDivision != "") $arrData['division_code'] = $strDataDivision;
  $f->addSelect(getWords("division"), "division_code", getDataListDivision($arrData['division_code'], true, "", ""), array("style" =>"width:250"), "string", true, true, true);

  $f->addSelect(getWords("department"), "department_code", getDataListDepartment($arrData['department_code'], true), array("style" =>"width:250"), "string", true, true, true);
  $f->addSelect(getWords("section"), "section_code", getDataListSection($arrData['section_code'], true), array("style" =>"width:250"), "string", false, true, true);
  $f->addSelect(getWords("sub section"), "sub_section_code", getDataListSubSection($arrData['sub_section_code'], true), array("style" =>"width:250"), "string", false, true, true);
  $f->addSelect(getWords("Employee Category"), "position_code", getDataListPosition($arrData['position_code'],true), array("style" =>"width:400"), "", true, true, true, "", "", true, array("width" => 120));
  $f->addSelect(getWords("Position"), "functional_code", getDataListFunctionalPosition($arrData['functional_code'],true), array(), "", true, true, true, "", "", true, array("width" => 120));
  $arrStatus = array(
    0 => array("text" => getWords("contract"), "value" => "0"),
    1 => array("text" => getWords("permanent"), "value" => "1"),
	2 => array("text" => getWords("outsource"), "value" => "2")
  );
  if ($arrData['employee_status'] == 0) $arrStatus[0]['selected'] = 'true';
  else $arrStatus[1]['selected'] = 'true';

  $f->addSelect(getWords("employee status"), "employee_status", $arrStatus, array(), "string", true, true, true);
  $f->addInput(getWords("Contract Duration(month)"), "contract_duration_m", $arrData['contract_duration_m'], array("size"=> 10), "integer", false, true, true, "", "", true, array("width" => 140));
  $f->addInput(getWords("Contract Duration(days)"), "contract_duration_d", $arrData['contract_duration_d'], array("size"=> 10), "integer", false, true, true, "", "", true, array("width" => 140));
  $f->addFieldSet(getWords("required information"), 2);
  $f->addInput(getWords("number required(Male)"), "number", $arrData['number'], array("size"=> 10), "integer", true, true, true, "", "", true, array("width" => 140));
  $f->addInput(getWords("number required(Female)"), "number_female", $arrData['number_female'], array("size"=> 10), "integer", true, true, true, "", "", true, array("width" => 140));
  $f->addInput(getWords("date required"), "due_date", $arrData['due_date'], array(), "date", false, true, true);
  $f->addHidden("actual_number", $arrData['actual_number']);
  $f->addLabel(getWords("total number of employees"), "label_actual_number", $arrData['actual_number']);

  $f->addFieldSet(getWords("request information"), 2);
  $f->addRadio(getWords("type of request"), "request_type", getDataCheckBoxMRFRequestType($arrData['request_type']), array(), "string", false, true, true, "", "", true, array("width" => 120));

  $f->addRadio(getWords("budget type"), "budget_type", getDataCheckBoxMRFBudgetType($arrData['budget_type']), array(), "string", true, true, true);

  $f->addFieldSet(getWords("other information"), 1);
  $f->addTextArea(getWords("reason"), "reason", $arrData['reason'], array("rows" => 3, "cols" => 48), "string", false, true, true);
  $f->addTextArea(getWords("job descriptions"), "description", $arrData['description'], array("rows" => 3, "cols" => 48), "string", false, true, true, "", "", true, array("width" => 120));
  $f->addTextArea(getWords("job qualifications"), "qualification", $arrData['qualification'], array("rows" => 3, "cols" => 48), "string", false, true, true);
  $f->addFieldSet(getWords("remuneration information"), 1);
  $f->addSelect(getWords("band / grade"), "grade_code", getDataListSalaryGrade($arrData['grade_code'], true, $emptyData), array(), "string", true, true, true, "", "", true, array("width" => 120));

  $totalAllowance = 0;
  $ARRAY_ALLOWANCE = array();
  AddCheckBoxOfAllowance($arrData['allowance'], $db);

  $f->addFieldSet(getWords("document status"));
  $f->addLabel(getWords("status"), "status", printStatus($arrData['status']));


  if ($bolCanEdit)
  {
      $f->addSubmit("btnSave", getWords("save"), array("onClick" => "return myClient.confirmSave();"), true, true, "", "", "saveData()");
  }

  if ($strDataID !="")
    $f->addButton("btnPrint", "Print", array("onClick" => "javascript:openWindowDialog('recruitment_print.php?dataID=" .$strDataID. "')"));

  $f->addButton("btnAddNew", getWords("add new"), array("onClick" => "location.href = 'recruitment_edit.php';"));

  $formInput = $f->render();

  $tbsPage = new clsTinyButStrong ;

  //write this variable in every page
  if ($isNew)
    $strPageTitle = getWords("add new candidate");
  else
    $strPageTitle = getWords("edit candidate");

  if (trim($dataPrivilege['icon_file']) == "") $pageIcon = "../images/icons/blank.gif";
  else $pageIcon = "../images/icons/".$dataPrivilege['icon_file'];
  $strTemplateFile = getTemplate(str_replace(".php", ".html", basename($_SERVER['PHP_SELF'])));
  //------------------------------------------------
  //Load Master Template
  $strPageTitle = getWords($dataPrivilege['menu_name']);
  $tbsPage->LoadTemplate($strMainTemplate) ;
  $tbsPage->Show() ;

  function getData($strDataID)
  {
    global $tblMRF;

    if ($strDataID != "")
    {
      if ($rowDb = $tblMRF->findById($strDataID))
      {
        return $rowDb;
      }
    }
    $arrData = $tblMRF->getEmptyRecord();
    $arrNumber = explode("|", getDocumentNumberMRF(date("Y-m-d")));
    $arrData["request_number_seq"] = intval($arrNumber[0]);
    $arrData["request_number"] = $arrNumber[1];

    $arrData['recruitment_date'] = date("Y-m-d");
    $arrData['number'] = 1; // default
    $arrData['number_female'] = 0; // default
    $arrData['contract_duraion_m'] = 0;
    $arrData['contract_duraion_d'] = 0;
    if ($arrData['user_name'] == "") $arrData['user_name'] = $_SESSION['sessionUserName'];

    if ($_SESSION['sessionCAR'] != -1) $arrData['id_company'] = $_SESSION['sessionCAR'];
    return $arrData;
  }

  function printStatus($intStatus)
  {
    global $ARRAY_CANDIDATE_STATUS;
    if (isset($ARRAY_CANDIDATE_STATUS[$intStatus]))
      return $ARRAY_CANDIDATE_STATUS[$intStatus];
    else
      return "";
  }

function saveData(){
	global $f;
	global $tblMRF;
	global $strEmailTo;
	global $strBody;
	global $totalAllowance;
	global $ARRAY_ALLOWANCE;

	// simpan data -----------------------
	$data = $_POST;
	$department_code = explode(" ", $_POST['department_code']);
	$data['department_code'] = $department_code[0];

	for($i=0;$i<$totalAllowance;$i++){
		if($data['allowance'.$i]=="on"){
			$arrAllowence[] = $ARRAY_ALLOWANCE[$i];
		}
	}
	$data['allowance']=implode(",",$arrAllowence);

	$tblMRF->begin();
	$isSuccess = false;
	if ($f->getValue('dataID') == ""){
		// data baru
		$arrNumber = explode("|", getDocumentNumberMRF($data['recruitment_date']));
		$data["request_number_seq"] = intval($arrNumber[0]);
		$data["request_number"] = $arrNumber[1];
		$data['status'] = 0;

		if ( $isSuccess = $tblMRF->insert($data)){
			$f->setValue('dataID', $tblMRF->getLastInsertId('id'));
			$strSubject = getSubject(0,'Recruitment',$data['request_number']);
		}
	}else{
		$isSuccess = $tblMRF->update(array("id" => $f->getValue('dataID')), $data);
		$strSubject = getSubject(0,'Recruitment Updated',$data['request_number']);
	}
	if ($isSuccess){
		$strBody = "MRF No. :".$data['request_number']."<br>";
		$strBody.= "Created Date : ".date("d-m-Y")."<br><br>";
		$strReason = ($data['reason'] == "") ? "-" : $data['reason'] ;
		$strDescription = ($data['description'] == "") ? "-" : $data['description'] ;
		$strQualification = ($data['qualification'] == "") ? "-" : $data['qualification'] ;
		$strBody.= "Reason: ".$strReason."<br><br>";
		$strBody.= "Job Description: ".$strDescription."<br>";
		$strBody.= "Job Qualification: ".$strQualification."<br><br>";
		$strBody.= "Details are listed in Recruitment List";
    	$f->message = $tblMRF->strMessage;
		$tblMRF->commit();
		header("location: recruitment_edit.php?dataID=".$f->getValue('dataID')."&message=".$f->message);
		exit();
	}else{
		$f->errorMessage  = getWords("failed to save data ")." ".$tblMRF->strEntityName." ";
		$tblMRF->rollback();
		return false;
	}
} // saveData

function AddCheckBoxOfAllowance($dataAllowence = NULL, $db)
{
	global $f;
    global $totalAllowance;
    global $ARRAY_ALLOWANCE;
	$arrAllowence=array();

	if($dataAllowence!=""){
		$arrAllowence=explode(",", $dataAllowence);
	}

	// untuk membuat semua allowance ada di sebelah kanan
	$f->addLabel("","","");

	$tbl = new cModel;
    $strSQL  = "SELECT code, value FROM salary_settings
                WHERE template_name = (SELECT DISTINCT(value) FROM all_setting WHERE code = 'template_name')
                AND code LIKE '%name%' AND code NOT LIKE '%deduction%'";
	$resDb = $tbl->query($strSQL);
	foreach($resDb as $loop){
		$arrDeduction=explode("_",$loop['code']);
		if ($arrDeduction[1]!="deduction"){
			if(!is_numeric($loop['value']) && $loop['value']!="f" && $loop['value']!="t" && $loop['value']!="-" &&  !empty($loop['value']) && $loop['value']!="basic salary" && $loop['value']!="tunjangan jabatan")
			$ARRAY_ALLOWANCE[]= $loop['value'];

			if($loop['value']=="tunjangan jabatan")
			$ARRAY_ALLOWANCE[]="Jabatan Allowance";
		}
	}
	$arrNonFixAllowance = getActiveAllowanceType($db);
	foreach ($arrNonFixAllowance as $allowance) {
		$ARRAY_ALLOWANCE[] = $allowance['name'];
	}

	foreach($ARRAY_ALLOWANCE as $key => $value){
		if(in_array($value, $arrAllowence)){
			$f->addCheckBox(getWords($value), "allowance".$key, true,"","",false);
		}else{
			$f->addCheckBox(getWords($value), "allowance".$key, false,"","",false);
		}
        $totalAllowance++;
	}
}

function getDocumentNumberMRF($dtTransDate){
	return implode("|", getFormDocumentNumber($dtTransDate, "hrd_recruitment_need", "recruitment_date", "request_number_seq", "formattingDocumentMRF")); // form_document.php
}
function getDataEmployeeRequest($default = null, $isHasEmpty = false, $emptyData = null){
	global $strKriteriaCompany;
	$tbl = new cModel("hrd_employee", getWords("employee"));
	$varCondition = "active = 1 AND section_code!='' AND
					department_code!='' AND division_code !='' AND management_code !=''
					AND sub_section_code=''";
	$arrData = $tbl->generateList($varCondition.$strKriteriaCompany, "employee_name", null, "employee_id", array("employee_name"));
	if ($default != null || $default != ""){
		while(list($key, $val) = each($arrData)){
			$temp = &$arrData[$key];
			if ($val['value'] == $default)
				$temp['selected'] = true;
			else
				$temp['selected'] = false;
		}
	}
	return $arrData;
}

function getDataCompany($default = null){
	$tbl = new cModel("hrd_company", "");
	$strSQLCompany = "SELECT id,company_name FROM hrd_company where id='$default'";
	$arrResultCompany = $tbl->query($strSQLCompany);
	$arrData['company_name'] = $arrResultCompany[0]['company_name'];
	$arrData['id_company'] = $arrResultCompany[0]['id'];
	return $arrData;
}
function getDataDivision($default = null){
	$tbl = new cModel("hrd_company", "");
	$strSQLDivision = "SELECT division_code,division_name FROM hrd_division where division_code='$default'";
	$arrResultDivision = $tbl->query($strSQLDivision);
	if(count($arrResultDivision)>0){
	$arrData['division_name'] = $arrResultDivision[0]['division_code']." - ".$arrResultDivision[0]['division_name'];
	$arrData['division_code'] = $arrResultDivision[0]['division_code'];
	}
	return $arrData;
}
function getDataDepartment($defaultDivision = null, $defaultDepartment = null){

	$tbl = new cModel("hrd_department", "");
	$strSQLDepartmentValue = "SELECT department_code,department_name FROM hrd_department where department_code ='$defaultDepartment'";
	$arrResultDepartmentValue = $tbl->query($strSQLDepartmentValue);
	$value = $arrResultDepartmentValue[0]['department_code'];

    if($defaultDivision != null || $defaultDivision != ""){
		$strSQLDepartment = "SELECT department_code,department_name FROM hrd_department where division_code='$defaultDivision'";
		$arrResultDepartment = $tbl->query($strSQLDepartment);
		if (count($arrResultDepartment)!=0){
			$i=0;
			foreach($arrResultDepartment as $loop){
				$arrData[$i]['value']= $loop['department_code']." - ".$loop['department_name'];
				$arrData[$i]['text']= $loop['department_code']." - ".$loop['department_name'];
				if($loop['department_code']==$value){
				$arrData[$i]['selected']= 1;
				}else{
				$arrData[$i]['selected']= "";
				}
			$i++;
			}
		}

	}
	return $arrData;
}


function getDataAJAX(){
	$strAction = getGetValue("action");
	switch ($strAction){
		case "changeDepartment" :
		$strDepartmentCode = getGetValue('department_code');
		$tbl = new cModel("hrd_employee");
		echo $tbl->findCount("department_code = '".$strDepartmentCode."'");
		break;
	}
	exit();
}
?>
