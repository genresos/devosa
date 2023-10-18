<?php

	date_default_timezone_set('Asia/Jakarta');
	
	include_once('../global/session.php');
	include_once('global.php');
	include_once('../includes/model/model.php');
	include_once('../includes/datagrid2/datagrid.php');
	include_once('../includes/form2/form2.php');
	include_once('form_object.php');
	include_once('salary_func.php');
  include_once('activity.php');
  include_once("../global/cls_date.php");
  include_once("cls_salary_calculation.php");
  include_once("cls_employee.php");
	include_once("../includes/krumo/class.krumo.php");
	
	$dataPrivilege = getDataPrivileges(basename($_SERVER['PHP_SELF']), $bolCanView, $bolCanEdit, $bolCanDelete, $bolCanApprove);
	$dataPrivilegeManagerial = getDataPrivileges("salary_calculation_managerial.php", $bolCanViewManagerial, $bolCanEditManagerial, $bolCanDeleteManagerial, $bolCanApproveManagerial);
  if (!$bolCanView) {
  	die(accessDenied($_SERVER['HTTP_REFERER']));
  }
	$db = new CDbClass();
	if ($db->connect()){
		$strWordsFILTERDATA = getWords('filter annual salary report');
		$f = new clsForm("form1", 1, "100%", "100%");
	  $f->showCaption = true;
	  $f->disableFormTag();
	  $f->caption = strtoupper($strWordsFILTERDATA);
	  $f->showMinimizeButton = true;
	  $f->showCloseButton = false;
		$f->addHidden("isShow", 1);
	  $f->addSelect("Year", "dataYear", getDataYearGlobal(), array("style" => "width:$strDefaultWidthPx"), "", true);
	  $f->addSelect(getWords("company"), "id_company", getDataListCompany($strDataCompany, false, $arrCompanyEmptyData, $strKriteria2), array("style" => "width:$strDefaultWidthPx"), "", false);
	  $f->addSelect(getWords("branch office"), "branch_code", getDataListBranch($strDataBranch, true), array("style" =>"width:250px;"), "string", false);
	  $f->addSelect(getWords("branch contract"), "branch_contract_code", getDataListBranch($strDataBranchContract, true), array("style" =>"width:250px;"), "string", false);
	  $f->addSelect(getWords("division"), "division_code", getDataListDivision($strDataDivision, true), array("style" => "width:250px;"), "string", false);
	  $f->addSelect(getWords("department"), "department_code", getDataListDepartment($strDataDepartment, true), array("style" =>"width:250px;"), "string", false);
	  $f->addSelect(getWords("section"), "section_code", getDataListSection($strDataSection, true), array("style" =>"width:250px;"), "string", false);
	  $f->addSelect(getWords("sub section"), "sub_section_code", getDataListSubSection($strDataSubSection, true), array("style" =>"width:250px;"), "string", false);
	  $f->addSelect(getWords("cost center"), "branch_cost_center_code", getDataListCostCenter($strDataCostCenter, true), array("style" =>"width:250px;"), "string", false);
	  $f->addInputAutoComplete(getwords("n i k"), "employee_id", getDataEmployee($strEmployeeId), "style=width:$strDefaultWidthPx ".$strReadonly, "string", false);
	  $f->addLabelAutoComplete("", "employee_id", "");
	  $f->addSubmit("btnShow", "Show Report", array("onClick" => "return validInput();"), true, true, "", "", "");
	  $f->addSubmit("btnExportXLS", "Export Excel", array("onClick" => "return validInput();"), true, true, "", "", "");
	  $formInput  = $f->render();	
	  $showReport = (isset($_POST['btnShow']) || isset($_POST['btnExportXLS']) || isset($_POST['isShow']));

  	$totalData = 0;
  	$dataGrid = "";
  	$strInitAction = "";

    $strEmployeeId = $f->getValue('employee_id');
    $idCompany = $f->getValue('id_company');
    $strDataBranch = $f->getValue('branch_code');
    if (empty($strDataBranch)){
    	if (isset($_POST['branch_code'])){
    		$strDataBranch = $_POST['branch_code'];
    	}
    }
    $strDataBranchContract = $f->getValue('branch_contract_code');
    $strDataDivision = $f->getValue('division_code');
    $strDataDepartment = $f->getValue('department_code');
    $strDataSection = $f->getValue('section_code');
    $strDataSubSection = $f->getValue('sub_section_code');
    $strDataCostCenter = $f->getValue('branch_cost_center_code');
    $strHidden = "";
    if ($showReport){
    	$intYear  = intval($f->getValue('dataYear'));
    	$strHidden .= '<input type="hidden" name="isShow" value="'.$showReport.'">';
    	if (empty($intYear)){
    		if (isset($_POST['dataYear'])){
    			$intYear = $_POST['dataYear'];
    		}
    	}
    	$strHidden .= '<input type="hidden" name="dataYear" value="'.$intYear.'">';
    	$arraySalaryMaster = getMasterSalaryByYearGlobal($db, $intYear);
    	for ($i = 0;$i < count($arraySalaryMaster);$i++){
    		$strDataID = $arraySalaryMaster[$i];
	    	$objSalary[$strDataID] = new clsSalaryCalculation($db, $strDataID);
	    }
	    $dataGrid = "";
	    if (count($objSalary)){
		    $strKriteria = "";
		    $strKriteria2 = "";
		    if (!empty($idCompany)){
		    	$strKriteria .= " AND id_company = $idCompany ";
		    	$strKriteria2 .= " AND id_company = $idCompany ";
		    	$strHidden .= '<input type="hidden" name="id_company" value="'.$idCompany.'">';
		    }
		    if (!empty($strDataBranch)){
		    	$strKriteria .= " AND t2.branch_code = '$strDataBranch' ";
		    	$strKriteria2 .= " AND branch_code = '$strDataBranch' ";
		    	$strHidden .= '<input type="hidden" name="branch_code" value="'.$strDataBranch.'">';
		    }
		    if (!empty($strDataBranchContract)){
		    	$strKriteria .= " AND t2.branch_penugasan_code = '$strDataBranchContract' ";
		    	$strKriteria2 .= " AND branch_penugasan_code = '$strDataBranchContract' ";
		    	$strHidden .= '<input type="hidden" name="branch_contract_code" value="'.$strDataBranchContract.'">';
		    }
		    if (!empty($strDataDivision)){
		    	$strKriteria .= " AND t2.division_code = '$strDataDivision' ";
		    	$strKriteria2 .= " AND division_code = '$strDataDivision' ";
		    	$strHidden .= '<input type="hidden" name="division_code" value="'.$strDataDivision.'">';
		    }
		    if (!empty($strDataDepartment)){
		    	$strKriteria .= " AND t2.department_code = '$strDataDepartment' ";
		    	$strKriteria2 .= " AND department_code = '$strDataDepartment' ";
		    	$strHidden .= '<input type="hidden" name="department_code" value="'.$strDataDepartment.'">';
		    }
		    if (!empty($strDataSection)){
		    	$strKriteria .= " AND t2.section_code = '$strDataSection' ";
		    	$strKriteria2 .= " AND section_code = '$strDataSection' ";
		    	$strHidden .= '<input type="hidden" name="section_code" value="'.$strDataSection.'">';
		    }
		    if (!empty($strDataSubSection)){
		    	$strKriteria .= " AND t2.sub_section_code = '$strDataSubSection' ";
		    	$strKriteria2 .= " AND sub_section_code = '$strDataSubSection' ";
		    	$strHidden .= '<input type="hidden" name="sub_section_code" value="'.$strDataSubSection.'">';
		    }
		    if (!empty($strDataCostCenter)){
		    	$strKriteria .= " AND t2.branch_cost_center_code = '$strDataCostCenter' ";
		    	$strKriteria2 .= " AND branch_cost_center_code = '$strDataCostCenter' ";
		    	$strHidden .= '<input type="hidden" name="branch_cost_center_code" value="'.$strDataCostCenter.'">';
		    }
		    if (!empty($strEmployeeId)){
		    	$strKriteria .= " AND t2.employee_id = '".$strEmployeeId."' ";
		    	$strKriteria2 .= " AND employee_id = '".$strEmployeeId."' ";
		    	$strHidden .= '<input type="hidden" name="employee_id" value="'.$strEmployeeId.'">';
		    }
		    $intTotalData = 0;
		    (isset($_POST['dataPage'])) ? $intCurrPage = $_POST['dataPage'] : $intCurrPage = 1;
		    $strHidden .= "<input type=hidden name=dataPage value=\"$intCurrPage\">";
				if (!is_numeric($intCurrPage)) $intCurrPage = 1;
		    getDataGrid($db, $strKriteria,$intCurrPage);
		  }else{
		  	$f->message = "No approved salary calculation available for Year : $intYear";
		  	$f->msgClass = "bgError";
		  	$formInput  = $f->render();	
		  	$dataGrid = "";	
		  }
    }
	}
	$tbsPage = new clsTinyButStrong ;
	//write this variable in every page
  $strPageTitle = getWords($dataPrivilege['menu_name']);
  $pageIcon = "../images/icons/".$dataPrivilege['icon_file'];
  $strTemplateFile = getTemplate(str_replace(".php", ".html", basename($_SERVER['PHP_SELF'])));
  //Load Master Template
  $tbsPage->LoadTemplate("../templates/master2.html") ;
  $tbsPage->Show() ;
  //end of main program
  
  function getDataGrid($db, $strCriteria, $bolLimit = true, $isFullView = false, $isExport = false)
  {
    global $bolPrint;
    global $bolCanDelete;
    global $bolCanEdit;
    global $intTotalData;
    global $objSalary;
    global $myDataGrid;
    global $ARRAY_LOAN_CATEGORY;
		global $arraySalaryMaster;
		global $dataGrid;
		$strSalaryMasterId = implode(',',$arraySalaryMaster);
    if (isset($_POST['btnExportXLS']) || isset($_POST['btnExportFull'])) $isExport = true;
    else $isExport = false;
		if (isset($_POST['btnExportFull'])){
			$isFull = true;
		}
    //class initialization
    $DEFAULTPAGELIMIT = getSetting("rows_per_page");
    if (!is_numeric($DEFAULTPAGELIMIT)) $DEFAULTPAGELIMIT = 50;
    $myDataGrid = new cDataGrid("formData", "DataGrid1", "100%", "100%", $bolLimit, false, true);
    $myDataGrid->caption = getWords("annual salary report");
    $myDataGrid->disableFormTag();
    $myDataGrid->pageSortBy = "t2.id, t3.salary_date";
    
    // kumpulkan jenis tunjangan lain-lain dan potongan lain-lain
    $arrOtherAllowance = array();
    $arrOtherDeduction = array();
    $arrIrrAllowance = array();
    $arrIrrFixAllowance = array();
    $intOtherAllowance = 0; // total jenis tunjangan lain-lain
    $intOtherDeduction = 0; // total jenis potongan lain-lain
    $intIrrAllowance = 0; // total jenis irregular income lain-lain
    $intIrrFixAllowance = 0; // total jenis irregular income lain-lain
    $strOtherAllowance = ""; // fields-fields tambahan untuk tunjangan lain-lain
    $strOtherDeduction = ""; // fields-fields tambahan untuk potongan lain-lain
    $strIrrAllowance = ""; // fields-fields tambahan untuk tunjangan lain-lain
    $strIrrFixAllowance = ""; // fields-fields tambahan untuk tunjangan lain-lain
    if (count($objSalary)){
    	for ($i = 0;$i < count($objSalary);$i++){
		    foreach ($objSalary[$i]->arrMA AS $strCode => $arrTmp) // looping data tunjangan lain-lain
		    {
		      if ($arrTmp['is_default'] == 't'){
		        if ($arrTmp['irregular'] == 't'){
		          $strIrrFixName = ($arrTmp['name'] == "") ? $arrTmp['allowance_code'] : $arrTmp['name'];
		          $arrIrrFixAllowance[$strCode] = $strIrrFixName;
		          $strIrrFixAllowance .= ", 0 AS alw_".$strCode;
		          if ($arrTmp['active'] == 't') $intIrrFixAllowance++;
		        }
		      }else{
		        if ($arrTmp['irregular'] == 't'){
		          $strIrrName = ($arrTmp['name'] == "") ? $arrTmp['allowance_code'] : $arrTmp['name'];
		          $arrIrrAllowance[$strCode] = $strIrrName;
		          $strIrrAllowance .= ", 0 AS alw_".$strCode;
		          if ($arrTmp['active'] == 't') $intIrrAllowance++;
		        }else{
		          $strName = ($arrTmp['name'] == "") ? $arrTmp['allowance_code'] : $arrTmp['name'];
		          $arrOtherAllowance[$strCode] = $strName;
		          $strOtherAllowance .= ", 0 AS alw_".$strCode;
		          $intOtherAllowance++;
		        }
		      }
		    }
		  }
	  }
	  if (count($objSalary)){
    	for ($i = 0;$i < count($objSalary);$i++){
		    foreach ($objSalary[$i]->arrMD AS $strCode => $arrTmp) // looping data tunjangan lain-lain
		    {
		      if ($arrTmp['is_default'] == 'f'){
		        $strName = ($arrTmp['name'] == "") ? $arrTmp['deduction_code'] : $arrTmp['name'];
		        $arrOtherDeduction[$strCode] = $strName;
		        $strOtherDeduction .= ", 0 AS alw_".$strCode;
		        $intOtherDeduction++;
		      }
		    }
		  }
		}
    //ambil list jenis-jenis iuran / loan
    $intLoanType = 0;
    $strSQL  = "SELECT id, type, category FROM hrd_loan_type ORDER BY note";
    $resDb = $db->execute($strSQL);
    $arrayLoanCategory = array();
    while ($rowDb = $db->fetchrow($resDb))
    {
      $intLoanType++;
      $arrLoanType[$rowDb['id']] = $rowDb['type'];
      $arrayLoanCategory[$rowDb['category']][$rowDb['id']] = $rowDb['type'];
    }
    if (count($objSalary)){
    	foreach($objSalary AS $idMaster => $objSalaryData){
    		$strSQL  = "SELECT t1.*, t2.id AS id_type, t3.resign_date FROM hrd_loan as t1
		                LEFT JOIN hrd_loan_type AS t2 ON t1.type = t2.type
		                LEFT JOIN hrd_employee AS t3 ON t1.id_employee = t3.id
		                WHERE status >=".REQUEST_STATUS_APPROVED_2."
		                AND payment_from < '". $objSalaryData->arrData['date_thru_salary']."'
		                AND (payment_thru + interval '1 months') > '". $objSalaryData->arrData['date_thru_salary']."'  ";
		    $resDb = $db->execute($strSQL);
		    while ($rowDb = $db->fetchrow($resDb)){
		      if ($rowDb['periode'] == 0){
		        $fltLoan = 0;
		      }else{
		        $fltLoan = round((((100 + $rowDb['interest']) / 100) * $rowDb['amount']) / $rowDb['periode']);
					}
		      if ($rowDb['resign_date'] != "" || $rowDb['resign_date'] != NULL){
		        if($rowDb['resign_date'] >= $objSalaryData->arrData['date_from_salary'] && $rowDb['resign_date'] <= $objSalaryData->arrData['date_thru_salary']){
		          $intPaymentThruMonth = date("n", strtotime($rowDb['payment_thru']));
		          $intPaymentThruYear = date("Y", strtotime($rowDb['payment_thru']));
		          $intResignDateMonth = date("n", strtotime($rowDb['resign_date']));
		          $intResignDateYear = date("Y", strtotime($rowDb['resign_date']));
		
		          $intMultiplier = ($intPaymentThruYear - $intResignDateYear) * 12 + $intPaymentThruMonth - $intResignDateMonth + 1;
		
		          $fltLoan = $intMultiplier * $fltLoan;
		        }
		      }
		      if (isset($arrEmployeeLoan[$idMaster][$rowDb['id_type']][$rowDb['id_employee']]['amount'])){
		        $arrEmployeeLoan[$idMaster][$rowDb['id_type']][$rowDb['id_employee']]['amount'] += $fltLoan;
		      }else{
		        $arrEmployeeLoan[$idMaster][$rowDb['id_type']][$rowDb['id_employee']]['amount'] = $fltLoan;
					}
		    }
		  }
	  }
    $latestSalarySet = max($arraySalaryMaster);
    $newArrayAllowance = newArrayAllowance($objSalary[$latestSalarySet]->arrMA);    
		$newArrayDeduction = newArrayDeduction($objSalary[$latestSalarySet]->arrMD);
		$myDataGrid->addColumnNumbering(new DataGrid_Column("No", "", array('rowspan' => 2, 'width'=>30), array('nowrap'=>''), false, false, "", "", "numeric", true, 6, false, "Sub Total ", true));
		$myDataGrid->addColumn(new DataGrid_Column(getwords("n i k"), "employee_id", array('rowspan' => 2, 'width' => 70), array("nowrap" => "nowrap"), true, true, "", "", "string", true, 12));
    $myDataGrid->addColumn(new DataGrid_Column(getwords("n i k corporate"), "employee_id_2", array('rowspan' => 2, 'width' => 70), array("nowrap" => "nowrap"), true, true, "", "", "string", true, 12));
    $myDataGrid->addColumn(new DataGrid_Column(getWords("employee name"), "employee_name", array("rowspan" => 2),  array("nowrap" => "nowrap"), true, true, "", "", "string", true, 35, false));
    $myDataGrid->addColumn(new DataGrid_Column(getWords("salary date"), "salary_date", array("rowspan" => 2, "width" => 270),  array("nowrap" => "nowrap"), true, true, "", "", "string", true, 12, false));
    $myDataGrid->addColumn(new DataGrid_Column(getWords("join date"), "join_date", array("rowspan" => 2, "width" => 270),  array("nowrap" => "nowrap"), true, true, "", "", "string", true, 12, false));
    $myDataGrid->addColumn(new DataGrid_Column(getWords("due date"), "due_date", array("rowspan" => 2, "width" => 270),  array("nowrap" => "nowrap"), true, true, "", "", "string", true, 12, false));
    $myDataGrid->addColumn(new DataGrid_Column(getWords("resign date"), "resign_date", array("rowspan" => 2, "width" => 270),  array("nowrap" => "nowrap"), true, true, "", "", "string", true, 12, false));
    $myDataGrid->addColumn(new DataGrid_Column(getWords("sex"), "gender", array("rowspan" => 2, "width" => 30),  array("align" => "center"), true, true, "", "printGender()", "string", true, 6, false));
    $myDataGrid->addColumn(new DataGrid_Column(getWords("fam."), "family_status_code", array("rowspan" => 2, "width" => 30),  null, true, true, "", "", "string", true, 12, false));
    $myDataGrid->addColumn(new DataGrid_Column(getWords("cost center"), "branch_cost_center_code", array("rowspan" => 2, "width" => 30), null, true, true, "", "", "string", true, 12, false));
    $myDataGrid->addColumn(new DataGrid_Column(getWords("division"), "division_code", array("rowspan" => 2, "width" => 30), null, true, true, "", "getDivisionName()", "string", true, 12, false));
    $myDataGrid->addColumn(new DataGrid_Column(getWords("department"), "department_code", array("rowspan" => 2, "width" => 30), null, true, true, "", "getDepartmentName()", "string", true, 12, false));
    $myDataGrid->addColumn(new DataGrid_Column(getWords("section"), "section_code", array("rowspan" => 2, "width" => 30), null, true, true, "", "getSectionName()", "string", true, 12, false));
    $myDataGrid->addColumn(new DataGrid_Column(getWords("sub"), "sub_section_code", array("rowspan" => 2, "width" => 30), null, true, true, "", "getSubSectionName()", "string", true, 12, false));
    $myDataGrid->addColumn(new DataGrid_Column(getWords("branch as contract"), "branch_penugasan_code", array("rowspan" => 2, "width" => 30), null, true, true, "", "", "string", true, 12, false));
    $myDataGrid->addColumn(new DataGrid_Column(getWords("branch office"), "branch_code", array("rowspan" => 2, "width" => 30), null, true, true, "", "", "string", true, 12, false));
    $myDataGrid->addColumn(new DataGrid_Column(getWords("branch bpjs Ks"), "branch_bpjs_ks_code", array("rowspan" => 2, "width" => 30), null, true, true, "", "", "string", true, 12, false));
    $myDataGrid->addColumn(new DataGrid_Column(getWords("grade"), "grade_code", array("rowspan" => 2, "width" => 30),  array("align" => "center", "nowrap" => "nowrap"), true, true, "", "", "string", true, 6, false));
    $myDataGrid->addColumn(new DataGrid_Column(getWords("level"), "position_code", array("rowspan" => 2, "width" => 80),  array("nowrap" => "nowrap"), true, true, "", "", "string", true, 6, false));
    $myDataGrid->addColumn(new DataGrid_Column(getWords("functional"), "functional_name", array("rowspan" => 2, "width" => 80),  array("nowrap" => "nowrap"), true, true, "", "", "string", true, 6, false));
    $myDataGrid->addColumn(new DataGrid_Column(getWords("status"), "employee_status", array("rowspan" => 2, "width" => 50),  array("align" => "center", "nowrap" => "nowrap"), true, true, "", "printStatus()", "string", true, 12));
    
    $totalAllowanceNonBenefitNonTax = count($newArrayAllowance['first_view_allowance']);
    $myDataGrid->addSpannedColumn(getWords("income"), $totalAllowanceNonBenefitNonTax);
    for($i = 0;$i < count($newArrayAllowance['other_allowance']);$i++){
    	$allowanceData = $newArrayAllowance['other_allowance'][$i];
      $myDataGrid->addColumn(new DataGrid_Column(getWords($allowanceData['name']), "alw_".$allowanceData['allowance_code'], array("rowspan" => 1, "width" => 270),  array("align" => "right"), false, true, "", "formatNumeric()", "numeric", true, 15, true, "alw_".$allowanceData['allowance_code']));
    }
    for($i = 0;$i < count($newArrayAllowance['default_allowance']);$i++){
    	$allowanceData = $newArrayAllowance['default_allowance'][$i];
      $myDataGrid->addColumn(new DataGrid_Column(getWords($allowanceData['name']), $allowanceData['allowance_code'], array("rowspan" => 1, "width" => 270),  array("align" => "right"), false, true, "", "formatNumeric()", "numeric", true, 15, true, $allowanceData['allowance_code']));
    }
    for($i = 0;$i < count($newArrayAllowance['benefit_tax']);$i++){
    	$allowanceData = $newArrayAllowance['benefit_tax'][$i];
      $myDataGrid->addColumn(new DataGrid_Column(getWords($allowanceData['name']), $allowanceData['allowance_code'], array("rowspan" => 1, "width" => 270),  array("align" => "right"), false, true, "", "formatNumeric()", "numeric", true, 15, true, $allowanceData['allowance_code']));
    }
    $totalDeduction = count($ARRAY_LOAN_CATEGORY) + count($newArrayDeduction['default_deduction']) + count($newArrayDeduction['other_deduction']) + count($newArrayAllowance['benefit_tax']) - 1;//Dikurangi 1 karena loan deduction total tidak ditampilkan
    $myDataGrid->addSpannedColumn(getWords("deduction"), $totalDeduction);
    for($i = (count($newArrayDeduction['default_deduction']) - 1);$i >= 0;$i--){
    	$deductionData = $newArrayDeduction['default_deduction'][$i];
    	if ($deductionData['deduction_code'] != 'loan_deduction'){
      	$myDataGrid->addColumn(new DataGrid_Column(getWords($deductionData['name']), $deductionData['deduction_code'], array("rowspan" => 1, "width" => 270),  array("align" => "right"), false, true, "", "formatNumeric()", "numeric", true, 15, true, $deductionData['deduction_code']));
      }
    }
    $arrayLoanCategoryCode = array();
    for($i = 0;$i < count($ARRAY_LOAN_CATEGORY);$i++){
    	$arrayLoanCategoryCode[$i] = 'loanCategory'.$i;
    	$myDataGrid->addColumn(new DataGrid_Column(getWords($ARRAY_LOAN_CATEGORY[$i]), $arrayLoanCategoryCode[$i], array("rowspan" => 1, "width" => 270),  array("align" => "right"), false, true, "", "formatNumeric()", "numeric", true, 15, true, $arrayLoanCategoryCode[$i]));
    }
    for($i = 0;$i < count($newArrayAllowance['benefit_tax']);$i++){
    	$allowanceData = $newArrayAllowance['benefit_tax'][$i];
    	$splitAllowanceName = explode(' ',$allowanceData['name']);
    	$deductionName = array();
    	for ($j = 0;$j < count($splitAllowanceName);$j++){
    		if (strtolower($splitAllowanceName[$j]) != 'allowance'){
    			$deductionName[] = $splitAllowanceName[$j];
    		}
    	}
    	$deductionNameView = implode(' ',$deductionName).' Deduction';
      $myDataGrid->addColumn(new DataGrid_Column(getWords($deductionNameView), $allowanceData['allowance_code']."_ded", array("rowspan" => 1, "width" => 270),  array("align" => "right"), false, true, "", "formatNumeric()", "numeric", true, 15, true, $allowanceData['allowance_code']."_ded"));
    }
    for($i = 0;$i < count($newArrayDeduction['other_deduction']);$i++){
    	$deductionData = $newArrayDeduction['other_deduction'][$i];
      $myDataGrid->addColumn(new DataGrid_Column(getWords($deductionData['name']), "ded_".$deductionData['deduction_code'], array("rowspan" => 1, "width" => 270),  array("align" => "right"), false, true, "", "formatNumeric()", "numeric", true, 15, true, "ded_".$deductionData['deduction_code']));	
    }
		$myDataGrid->addColumn(new DataGrid_Column(getWords("take home pay"), "total_gross", array("rowspan" => 2, "width" => 270),  array("align" => "right"), false, true, "", "formatNumeric()", "numeric", true, 15, true, ""));
		for($i = 0;$i < count($newArrayAllowance['benefit_non_tax']);$i++){
			$allowanceData = $newArrayAllowance['benefit_non_tax'][$i];
			$splitAllowanceName = explode(' ',$allowanceData['name']);
    	$deductionName = array();
    	for ($j = 0;$j < count($splitAllowanceName);$j++){
    		if (strtolower($splitAllowanceName[$j]) != 'allowance'){
    			$deductionName[] = $splitAllowanceName[$j];
    		}
    	}
    	$deductionNameView = implode(' ',$deductionName).' Deduction';
			$myDataGrid->addColumn(new DataGrid_Column(getWords($deductionNameView), $allowanceData['allowance_code'], array("rowspan" => 2, "width" => 270),  array("align" => "right"), false, true, "", "formatNumeric()", "numeric", true, 15, true, $allowanceData['allowance_code']));
		}
    if ($isExport)
    {
      $myDataGrid->DATAGRID_RENDER_OUTPUT = DATAGRID_RENDER_EXCEL_BIFF;
      $myDataGrid->strFileNameXLS = "salary.xls";
      $myDataGrid->strTitle1 = getWords("salary");
      if (!$isFull){
      	$myDataGrid->groupBy ( "branch_cost_center_code" );
      }
      $myDataGrid->hasGrandTotal = true;
    }

    if (!$bolPrint)
    {
      if ($bolCanEdit)
      {
        $myDataGrid->addButtonExportExcel("Export Excel", "salary.xls", getWords("list of salary"));
        $myDataGrid->addButton("btnExportFull", "btnExportFull", "submit", getWords("export without group"), "", "");
      }
    }

    $myDataGrid->getRequest();
    //--------------------------------
    //get Data and set to Datagrid's DataSource by set the data binding (bind method)

    $strCriteriaFlag = "";
    if ($bolLimit)
    {
      $strPageLimit = $myDataGrid->getPageLimit();
      $intPageNumber = $myDataGrid->getPageNumber();
    }
    else
    {
      $strPageLimit = null;
      $intPageNumber = null;
    }

    // cari total
    $strSQL = "SELECT COUNT(*) AS total
      FROM (
        SELECT id_employee, id_salary_master, basic_salary,position_allowance,transport_allowance,meal_allowance,tax,base_tax,tax_reduction,
				benefit,jamsostek_allowance,absence_deduction,total_gross,total_net,total_deduction,jamsostek_deduction,
				total_gross_round,loan_deduction,vehicle_allowance,unpaid_absence_day,overtime_allowance,
				actual_basic_salary,jkk_allowance,jkm_allowance,zakat_deduction,irregular_tax,
				base_irregular_tax,total_gross_irregular,total_net_irregular,zakat_deduction_irregular,
				leave_allowance,thr_allowance,position1_allowance,position2_allowance,position3_allowance,
				grade1_allowance,grade2_allowance,grade3_allowance,family_status1_allowance,family_status2_allowance,
				family_status3_allowance,branch1_allowance,branch2_allowance,branch3_allowance,
				seniority_allowance,shift_allowance,kerajinan_allowance,tax_allowance,late_round,
				early_round,otmeal_allowance,ottransport_allowance,bpjs_allowance,bpjs_deduction,
				overtime_allowance_auto_paid,medical_allowance,pension_allowance,pension_deduction,
				late_deduction,late_ti_deduction
        FROM hrd_salary_detail WHERE id_salary_master IN ($strSalaryMasterId)
      ) AS t1
      LEFT JOIN (
        SELECT id, employee_id, id_company, branch_code, branch_penugasan_code, division_code, department_code, section_code,
        sub_section_code,branch_cost_center_code
        FROM hrd_employee WHERE 1=1 $strCriteria2
      ) AS t2 ON t1.id_employee = t2.id
      LEFT JOIN (
      	SELECT id, salary_date FROM hrd_salary_master
     	) AS t3 ON t1.id_salary_master = t3.id
      WHERE 1=1 $strCriteria ";
    if ($bolHideBlank)
    {
      $strSQL .= "AND total_net > 0 AND total_gross > 0";
    }
    $res = $db->execute($strSQL);
    if ($row = $db->fetchrow($res))
    {
    	$myDataGrid->totalData = ($row['total'] == "") ? 0 : $row['total'];
    }

    //get Data and set to Datagrid's DataSource by set the data binding (bind method)
    $strSQL = "SELECT t3.salary_date, t2.id AS id_employee, t1.basic_salary,
    t1.id_salary_master, t1.position_allowance,t1.transport_allowance,t1.meal_allowance,t1.tax,t1.base_tax,
		t1.tax_reduction,t1.benefit,t1.jamsostek_allowance,t1.absence_deduction,t1.total_gross,
		t1.total_net,t1.total_deduction,t1.jamsostek_deduction,t1.total_gross_round,t1.loan_deduction,
		t1.vehicle_allowance,t1.unpaid_absence_day,t1.overtime_allowance,t1.actual_basic_salary,
		t1.jkk_allowance,t1.jkm_allowance,t1.zakat_deduction,t1.irregular_tax,t1.base_irregular_tax,
		t1.total_gross_irregular,t1.total_net_irregular,t1.zakat_deduction_irregular,
		t1.leave_allowance,t1.thr_allowance,t1.position1_allowance,t1.position2_allowance,
		t1.position3_allowance,t1.grade1_allowance,t1.grade2_allowance,t1.grade3_allowance,t1.family_status1_allowance,
		t1.family_status2_allowance,t1.family_status3_allowance,t1.branch1_allowance,t1.branch2_allowance,
		t1.branch3_allowance,t1.seniority_allowance,t1.shift_allowance,t1.kerajinan_allowance,
		t1.tax_allowance,t1.late_round,t1.early_round,t1.otmeal_allowance,t1.ottransport_allowance,
		t1.bpjs_allowance,t1.bpjs_deduction,t1.overtime_allowance_auto_paid,t1.medical_allowance,
		t1.pension_allowance,t1.pension_deduction,t1.late_deduction,t1.late_ti_deduction
      FROM (
        SELECT id_employee, id_salary_master, basic_salary,position_allowance,transport_allowance,meal_allowance,tax,base_tax,tax_reduction,
				benefit,jamsostek_allowance,absence_deduction,total_gross,total_net,total_deduction,jamsostek_deduction,
				total_gross_round,loan_deduction,vehicle_allowance,unpaid_absence_day,overtime_allowance,
				actual_basic_salary,jkk_allowance,jkm_allowance,zakat_deduction,irregular_tax,
				base_irregular_tax,total_gross_irregular,total_net_irregular,zakat_deduction_irregular,
				leave_allowance,thr_allowance,position1_allowance,position2_allowance,position3_allowance,
				grade1_allowance,grade2_allowance,grade3_allowance,family_status1_allowance,family_status2_allowance,
				family_status3_allowance,branch1_allowance,branch2_allowance,branch3_allowance,
				seniority_allowance,shift_allowance,kerajinan_allowance,tax_allowance,late_round,
				early_round,otmeal_allowance,ottransport_allowance,bpjs_allowance,bpjs_deduction,
				overtime_allowance_auto_paid,medical_allowance,pension_allowance,pension_deduction,
				late_deduction,late_ti_deduction
        FROM hrd_salary_detail WHERE id_salary_master IN ($strSalaryMasterId)
      ) AS t1
      LEFT JOIN (
        SELECT id, employee_id, id_company, branch_code, branch_penugasan_code, division_code, 
        department_code, section_code,sub_section_code,branch_cost_center_code
        FROM hrd_employee WHERE 1=1 $strCriteria2
      ) AS t2 ON t1.id_employee = t2.id
      LEFT JOIN (
      	SELECT id, salary_date FROM hrd_salary_master
     	) AS t3 ON t1.id_salary_master = t3.id
      WHERE 1=1 $strCriteria ";
    if ($bolHideBlank)
    {
      if($bolIrregular)
        $strSQL .= "AND total_net_irregular > 0 AND total_gross_irregular > 0";
      else
        $strSQL .= "AND total_net > 0 AND total_gross > 0";
    }
    //handle sort
    if ($myDataGrid->isShowSort){
      if ($myDataGrid->pageSortBy != ""){
      	$strOrder = "";
      	$splitSort = explode(",", $myDataGrid->pageSortBy);
      	for ($i = 0;$i < count($splitSort);$i++){
      		$splitSortData = explode(" ",$splitSort[$i]);
      		if ($strOrder == ""){
      			$strOrder = "ORDER BY";	
      		}
      		for ($j = 0;$j < count($splitSortData);$j++){
      			$strOrder .= " ".$splitSortData[$j];
      			if ($j + 1 >= count($splitSortData) && $i < count($splitSort) - 1){
      				$strOrder .= ",";
      			}
      		}
      	}
      	$strSQL .= $strOrder;
      }
    }
    //handle page limit
    if ($myDataGrid->isShowPageLimit && !$isExport)
      if (is_numeric($myDataGrid->pageLimit) && $myDataGrid->pageLimit > 0)
        $strSQL .= " LIMIT $myDataGrid->pageLimit OFFSET ".$myDataGrid->getOffsetStart();
    //get query
    $dataset = array();
    $resDb = $db->execute($strSQL);
    //put result to array dataset
    while ($rowDb = $db->fetchrow($resDb))
    {
      //$rowDb['total_ot_min'] = (1.5 * $rowDb['ot1_min']) * (2 * $rowDb['ot2_min']) * (3 * $rowDb['ot3_min']) * (4 * $rowDb['ot4_min']); // hardcode
      $rowDb['total_tax'] = $rowDb['tax'] + $rowDb['irregular_tax'];
      for($i = 0;$i < count($newArrayAllowance['other_allowance']);$i++){
				$allowanceData = $newArrayAllowance['other_allowance'][$i];
				$strCode = $allowanceData['allowance_code'];
				$rowDb['alw_'.$strCode] = $objSalary[$rowDb['id_salary_master']]->arrDA[$strCode][$rowDb['id_employee']]['amount'];  	
			}
			for($i = 0;$i < count($newArrayAllowance['benefit_tax']);$i++){
				$allowanceData = $newArrayAllowance['benefit_tax'][$i];
				$strCode = $allowanceData['allowance_code'];
				$rowDb[$strCode] = $objSalary[$rowDb['id_salary_master']]->arrDA[$strCode][$rowDb['id_employee']]['amount'];
			}
      foreach ($arrIrrAllowance AS $strCode => $strName)
      {
      	$rowDb['alw_'.$strCode] = $objSalary[$rowDb['id_salary_master']]->arrDA[$strCode][$rowDb['id_employee']]['amount'];
      }
      foreach ($arrOtherDeduction AS $strCode => $strName)
      {
      	$rowDb['ded_'.$strCode] = $objSalary[$rowDb['id_salary_master']]->arrDD[$strCode][$rowDb['id_employee']]['amount'];
      }
			
      foreach ($arrLoanType AS $strCode => $strName)
      {
        if (isset($arrEmployeeLoan[$strCode][$rowDb['id_employee']]))
          $rowDb['loan_'.$strCode] = $arrEmployeeLoan[$rowDb['id_salary_master']][$strCode][$rowDb['id_employee']]['amount'];
        else
          $rowDb['loan_'.$strCode] = 0;
        for($i = 0;$i < count($ARRAY_LOAN_CATEGORY);$i++){
        	$loanAmount = 0;
        	if (isset($arrayLoanCategory[$i][$strCode])){
        		$loanAmount = $arrEmployeeLoan[$rowDb['id_salary_master']][$strCode][$rowDb['id_employee']]['amount'];
        	}
        	if (isset($rowDb[$arrayLoanCategoryCode[$i]])){
        		$rowDb[$arrayLoanCategoryCode[$i]] = $rowDb[$arrayLoanCategoryCode[$i]] + $loanAmount;
        	}else{
        		$rowDb[$arrayLoanCategoryCode[$i]] = 0;
        	}
        }  
      }
      $rowDb['transfer_income'] = $rowDb['total_gross'] - $rowDb['cash_income'];
      $rowDb['meal'] = $rowDb['alw_meal_allowance'] + $rowDb['alw_meal_allowance_managerial'];
	    $rowDb['transport'] = $rowDb['alw_transport_allowance'] + $rowDb['alw_transport_allowance_managerial'];
      $intRound = (isset($objSalary[$rowDb['id_salary_master']]->arrConf['salary_round']) && is_numeric($objSalary[$rowDb['id_salary_master']]->arrConf['salary_round'])) ? $objSalary[$rowDb['id_salary_master']]->arrConf['salary_round'] : 1;
			//Hardcode, never use this lines on standard package
      //----------------------
      if (isset($rowDb['total_gross'])){
      	$rowDb['total_gross'] = $rowDb['total_gross'] + roundMoney($objSalary[$rowDb['id_salary_master']]->arrDetail[$rowDb['id_employee']]['total_gross_round'],$intRound);
      }else{
      	$rowDb['total_gross'] = roundMoney($objSalary[$rowDb['id_salary_master']]->arrDetail[$rowDb['id_employee']]['total_gross_round'],$intRound);
      }
			for($i = 0;$i < count($newArrayAllowance['benefit_tax']);$i++){
      	$allowanceData = $newArrayAllowance['benefit_tax'][$i];
      	$rowDb[$allowanceData['allowance_code'].'_ded'] = $rowDb[$allowanceData['allowance_code']];
      }
      for($i = 0;$i < count($newArrayAllowance['benefit_non_tax']);$i++){
      	$allowanceData = $newArrayAllowance['benefit_non_tax'][$i];
      	$rowDb[$allowanceData['allowance_code'].'_ded'] = $rowDb[$allowanceData['allowance_code']];
    	}
    	$employeeData = getEmployeeInfoById($rowDb['id_employee']);
    	if (count($employeeData)){
    		foreach ($employeeData as $strEmpCode => $empData){
    			$rowDb[$strEmpCode] = $empData;
    		}
    	}
      //----------------------
      $dataset[] = $rowDb;
    }
    $intTotalData = count($dataset);

    $myDataGrid->bind($dataset);
		$dataGrid = $myDataGrid->render();
    return true;
  }
  function getEmployeeInfoById($employeeId = null){
  	global $db;
  	$employeeData = array();
  	if (!empty($employeeId) && $db->connect()){
  		$strSQL = "SELECT employee_id,employee_id_2,employee_name,join_date,due_date,resign_date,";
  		$strSQL .= "gender,family_status_code,branch_cost_center_code,division_code,department_code,";
  		$strSQL .= "section_code,sub_section_code,branch_penugasan_code,branch_code,branch_bpjs_ks_code,";
  		$strSQL .= "grade_code,position_code,functional_name,employee_status FROM hrd_employee emp ";
  		$strSQL .= "LEFT JOIN hrd_functional emp_func ON emp.functional_code = emp_func.functional_code ";
  		$strSQL .= "WHERE id=$employeeId";
  		$resDb = $db->execute($strSQL);
  		$employeeData = $db->fetchrow($resDb);
  	}
  	return $employeeData;
  }
  // format tampilan gender
  function printGender($params){
    extract($params);
    return ($value == 0) ? "F" : "M";
  } // format tampilan gender
  // format tampilan employee status
  function printStatus($params){
    extract($params);
    global $ARRAY_EMPLOYEE_STATUS;
    return getWords($ARRAY_EMPLOYEE_STATUS[$value]);
  } // format tampilan employee status
  
?>