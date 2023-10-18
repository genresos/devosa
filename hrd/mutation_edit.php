<?php
  include_once('../global/session.php');
  //include_once("../includes/krumo/class.krumo.php");
  include_once('global.php');
  include_once('form_object.php');
  include_once '../global/email_func.php';
	
  $dataPrivilege = getDataPrivileges(basename($_SERVER['PHP_SELF']), $bolCanView, $bolCanEdit, $bolCanDelete, $bolCanApprove, $bolCanCheck, $bolCanAcknowledge, $bolCanApprove2);
  if (!$bolCanView) die(accessDenied($_SERVER['HTTP_REFERER']));


  $bolPrint = (isset($_REQUEST['btnPrint']));
  //---- INISIALISASI ----------------------------------------------------
  $strDataDetail = "";
  $intDefaultWidth = 30;
  $intDefaultHeight = 3;
  $strNow = date("Y-m-d");
  $strMessages = "";
  $strMsgClass = "";
  $bolError = false;
  $strBtnPrint = "";

$strWordsProposalDate               = getWords("proposal date");
$strWordsEmployeeID                 = getwords("n i k");
$strWordsLetterCode                 = getWords("letter code");
$strWordsEmployeeStatusConfirmation = getWords("employee status confirmation");
$strWordsEmployeeStatus             = getWords("employee status");
$strWordsDateFrom                   = getWords("date from");
$strWordsStartDate                  = getWords("start date");
$strWordsStartDate                  = getWords("start date");
$strWordsEmployeeDpartmentChanges   = getWords("employee department changes");
$strWordsEmployeeSalaryChanges      = getWords("employee salary changes");
$strWordsEmployeePositionChanges    = getWords("employee position changes");
$strWordsEmployeeNIKChanges         = getWords("employee n i k changes");
$strWordsPositionAllow              = getwords("position allowance");
$strWordsMealAllow                  = getwords("meal allowance");
$strWordsTransportAllow             = getwords("transport allowance");
$strWordsVehicleAllow               = getwords("vehicle allowance");
$strWordsBasicSalary                = getwords("basic salary");
$strWordsRecentDepartment           = getWords("recent department");
$strWordsNewDepartment              = getWords("new department");
$strWordsNote                       = getWords("note");
$strWordsStatus                     = getWords("status");
$strWordsManagement                 = getWords("management");
$strWordsCompany                    = getWords("company");
$strWordsDivision                   = getWords("division");
$strWordsDepartment                 = getWords("department");
$strWordsSection                    = getWords("section");
$strWordsSubSection                 = getWords("sub section");
$strWordsRecent                     = getWords("recent");
$strWordsNew                        = getWords("new");
$strWordsPromotion                  = getWords("promotion");
$strWordsProposalEntry              = getWords("proposal entry");
$strWordsProposalList               = getWords("proposal list");
$strWordsGetInfo                    = getWords("get info");
$strWordsUntil                      = getWords("until");
$strWordsSave                       = getWords("save");
$strWordsClearForm                  = getWords("clear form");
$strWordsPosition                   = getWords("position");
$strWordsGrade                      = getWords("grade");
$strWordsFunctional                 = getWords("functional");
$strWordsNIK                        = getWords("n i k");
$strWordsSalarySet                  = getWords("salary set");
$strWordsSalarySetOld                  = getWords("select old salary set");
$strWordsSalarySetNew                  = getWords("select new salary set");
$strWordsBasicSalary                = getWords("basic salary");
$strWordsBranch                     = getWords("branch");
$strWordsBranchChanges              = getWords("employee branch changes");
$strWordsCostCenterChanges          = getWords("employee cost center changes");
$strWordsBranchContract             = getWords("branch contract");
$strWordsBranchPenugasan            = getWords("branch penugasan");
$strWordsBranchOld                  = getWords("old branch");
$strWordsBranchNew                  = getWords("new branch");
$strWordsCostCenterOld                  = getWords("old cost center");
$strWordsCostCenterNew                  = getWords("new cost center");


  $arrData = array(
    "dataDate" => $strNow,
    "dataEmployee" => "",
    "dataIDEmployee" => "",
    "dataLetterCode" => "",
    "dataIsStatus" => false, // aapkah ada perubahan status
    "dataIsResign" => false, // apakah ada pemberhentian
    "dataIsPosition" => false, // apakah ada perubahan jabatan
    "dataIsDepartment" => false, // apakah ada perubahan department
    "dataIsSalary" => false, // apakah ada perubahan gaji
    "dataIsNIK" => false, // apakah ada perubahan NIK
    "dataIsBranch" => false, // apakah ada perubahan NIK
    "dataIsCostCenter" => false, // apakah ada perubahan Cost Center


    "dataStatusNew" => "", // status karyawan yang lama
    "dataStatusDateFrom" => "",//$strNow,
    "dataStatusDateThru" => "",//$strNow,


  "dataPositionOld" => "",
    "dataPositionNew" => "",
    "dataGradeOld" => "",
    "dataGradeNew" => "",
    "dataFunctionalOld" => "",
    "dataFunctionalNew" => "",
    "dataPositionNewDate" => "",
    "dataCostCenterOld" => "",
    "dataCostCenterNew" => "",
    "dataCostCenterNewDate" => "",


  "dataManagementOld" => "",
    "dataManagementNew" => "",
    "dataCompanyOld" => "",
    "dataCompanyNew" => "",
    "dataDivisionOld" => "",
    "dataDivisionNew" => "",
    "dataDepartmentOld" => "",
    "dataDepartmentNew" => "",
    "dataSectionOld" => "",
    "dataSectionNew" => "",
    "dataSubSectionOld" => "",
    "dataSubSectionNew" => "",
    "dataDepartmentNewDate" => "",


    "dataBasicSalaryOld" => "",
    "dataBasicSalaryNew" => "",
    "dataPositionAllowOld" => "",
    "dataPositionAllowNew" => "",
    "dataMealOld" => "",
    "dataMealNew" => "",
    "dataTransportOld" => "",
    "dataTransportNew" => "",
    "dataVehicleOld" => "",
    "dataVehicleNew" => "",
    "dataStartOldDate" => "",
    "dataStartNewDate" => "",
    "dataBranchContractOld" => "",
    "dataBranchContractNew" => "",
    "dataBranchPenugasanOld" => "",
    "dataBranchPenugasanNew" => "",
		"dataSalarySetOld" => "",
		"dataSalarySetNew" => "",

    "dataStatus" => "0", //status dari proposal (baru, verifikasi, setuju, tolak)
    "dataNote" => "",
    "dataID" => "",
  );
  //----------------------------------------------------------------------

  //--- DAFTAR FUNSI------------------------------------------------------
  // fungsi untuk menampilkan data
  // $db = kelas database, $strDataID = ID data, jika ingin ditampilkan
  // $arrInputData = array untuk menampung data
  function getData($db) {
    global $words;
    global $arrData;
    global $strDataID;
		global $strInputDetailSalary;
    if ($strDataID != "")
    {
      writeLog(ACTIVITY_VIEW, MODULE_PAYROLL,"ID=$strDataID",0);

      $strSQL  = "SELECT t1.*, t2.id AS employee_auto_id, t2.employee_id, t2.employee_name, t2.join_date,  ";
      $strSQL .= "t2.management_code, t2.division_code, t2.department_code, t2.section_code, t2.sub_section_code, ";
      $strSQL .= "t2.grade_code, t2.employee_status, t2.position_code,t2.id_company,t2.functional_code,t2.branch_code,t2.branch_penugasan_code ";
      $strSQL .= "FROM hrd_employee_mutation AS t1 ";
      $strSQL .= "LEFT JOIN hrd_employee AS t2 ON t1.id_employee = t2.id ";
      $strSQL .= "WHERE t1.id = '$strDataID' ";
      $resDb = $db->execute($strSQL);
      if ($rowDb = $db->fetchrow($resDb)) {
				$arrData['dataIDEmployee'] = $rowDb['employee_auto_id'];
        $arrData['dataEmployee'] = getNIK($db, $rowDb['employee_id']);
        $arrData['dataNIKOld'] = $arrData['dataEmployee'];
        $arrData['dataID'] = $rowDb['id'];
        $arrData['dataDate'] = $rowDb['proposal_date'];
        $arrData['dataLetterCode'] = $rowDb['letter_code'];
        $arrData['dataStatus'] = $rowDb['status'];
        $arrData['dataNote'] = $rowDb['note'];
        $strStatus = $rowDb['status'];
        $arrData['dataEmployeeName'] = $rowDb['employee_name'];
        $arrData['dataPosition'] = $rowDb['position_code'];
        $arrData['dataGrade'] = $rowDb['grade_code'];
        $arrData['dataFunctional'] = $rowDb['functional_code'];
        $arrData['dataEmployeeStatus'] = $rowDb['employee_status'];
        $arrData['dataJoinDate'] = $rowDb['join_date'];
        $arrData['dataContractFrom'] = $rowDb['contract_from'];
        $arrData['dataManagement'] = $rowDb['management_code'];
        $arrData['dataCompany'] = $rowDb['id_company'];
        $arrData['dataDivision'] = $rowDb['division_code'];
        $arrData['dataDepartment'] = $rowDb['department_code'];
        $arrData['dataSection'] = $rowDb['section_code'];
        $arrData['dataSubSection'] = $rowDb['sub_section_code'];
        $arrData['dataBranch'] = $rowDb['branch_code'];

				// cari perubahan NIK
        $strSQL = "SELECT id_mutation,id_employee_old,id_employee_new FROM hrd_employee_mutation_id ";
        $strSQL .= "WHERE id_mutation = '$strDataID' ";
        $resTmp = $db->execute($strSQL);
        if ($rowTmp = $db->fetchrow($resTmp)) {
          $arrData['dataIsNIK'] = true;
          $arrData['dataNIKOld'] = $rowTmp['id_employee_old'];
          $arrData['dataNIKNew'] = $rowTmp['id_employee_new'];
        }
				
        // cari status
        $strSQL  = "SELECT * FROM hrd_employee_mutation_status WHERE id_mutation = '$strDataID' ";
        $resTmp = $db->execute($strSQL);
        if ($rowTmp = $db->fetchrow($resTmp)) {
          $arrData['dataIsStatus'] = true;
          $arrData['dataStatusNew'] = $rowTmp['status_new'];
          $arrData['dataStatusDateFrom'] = $rowTmp['status_date_from'];
          $arrData['dataStatusDateThru'] = $rowTmp['status_date_thru'];
        }
  
        $strSQL  = "SELECT * FROM hrd_employee_mutation_branch WHERE id_mutation = '$strDataID' ";
        $resTmp = $db->execute($strSQL);
        if ($rowTmp = $db->fetchrow($resTmp)) {
        	$arrData['dataIsBranch'] = true;
          $arrData['dataBranchContractOld'] = $rowTmp['branch_contract_old'];
          $arrData['dataBranchContractNew'] = $rowTmp['branch_contract_new'];
          $arrData['dataBranchPenugasanOld'] = $rowTmp['branch_penugasan_old'];
          $arrData['dataBranchPenugasanNew'] = $rowTmp['branch_penugasan_new'];
          $arrData['dataBranchNewDate'] = $rowTmp['branch_new_date'];
        }else{
        	$arrData['dataBranchContractOld'] = $arrData['dataBranch'];
        	$arrData['dataBranchPenugasanOld'] = $arrData['dataBranch'];
        }

        $strSQL  = "SELECT * FROM hrd_employee_mutation_cost_center WHERE id_mutation = '$strDataID' ";
        $resTmp = $db->execute($strSQL);
        if ($rowTmp = $db->fetchrow($resTmp)) {
        	$arrData['dataIsCostCenter'] = true;
          $arrData['dataCostCenterOld'] = $rowTmp['cost_center_old'];
          $arrData['dataCostCenterNew'] = $rowTmp['cost_center_new'];
          $arrData['dataCostCenterNewDate'] = $rowTmp['cost_center_new_date'];
        }else{
        	$arrData['dataCostCenterOld'] = $arrData['dataCostCenter'];
        }

        // cari salary
        $strSQL  = "SELECT salary_new_date, id_salary_set, id_salary_set_old FROM hrd_employee_mutation_salary WHERE id_mutation = '$strDataID' GROUP BY id_mutation, salary_new_date, id_salary_set, id_salary_set_old";
        $resTmp = $db->execute($strSQL);
        if ($rowTmp = $db->fetchrow($resTmp)) {
          $arrData['dataIsSalary'] = true;
          $arrData['dataSalaryNewDate'] = $rowTmp['salary_new_date'];
          $arrData['dataSalarySetOld'] = $rowTmp['id_salary_set_old'];
          $arrData['dataSalarySetNew'] = $rowTmp['id_salary_set'];
        }
        // cari posiition
    		$strSQL  = "SELECT * FROM hrd_employee_mutation_position WHERE id_mutation = '$strDataID' ";
        $resTmp = $db->execute($strSQL);
        if ($rowTmp = $db->fetchrow($resTmp)) {
          $arrData['dataIsPosition'] = true;
          $arrData['dataPositionOld'] = $rowTmp['position_old'];
          $arrData['dataPositionNew'] = $rowTmp['position_new'];
          $arrData['dataGradeOld'] = $rowTmp['grade_old'];
          $arrData['dataGradeNew'] = $rowTmp['grade_new'];
          $arrData['dataFunctionalOld'] = $rowTmp['functional_old'];
          $arrData['dataFunctionalNew'] = $rowTmp['functional_new'];
          $arrData['dataPositionNewDate'] = $rowTmp['position_new_date'];
        }else{
        	$arrData['dataPositionOld'] = $arrData['dataPosition'];
        	$arrData['dataGradeOld'] = $arrData['dataGrade'];
        	$arrData['dataFunctionalOld'] = $arrData['dataFunctional'];
        }
        // cari department
        $strSQL  = "SELECT * FROM hrd_employee_mutation_department WHERE id_mutation = '$strDataID' ";
        $resTmp = $db->execute($strSQL);
        if ($rowTmp = $db->fetchrow($resTmp)) {
          $arrData['dataIsDepartment'] = true;
          $arrData['dataCompanyOld'] = $rowTmp['company_old'];
          $arrData['dataCompanyNew'] = $rowTmp['company_new'];
          $arrData['dataManagementOld'] = $rowTmp['management_old'];
          $arrData['dataManagementNew'] = $rowTmp['management_new'];
          $arrData['dataDivisionOld'] = $rowTmp['division_old'];
          $arrData['dataDivisionNew'] = $rowTmp['division_new'];
          $arrData['dataDepartmentOld'] = $rowTmp['department_old'];
          $arrData['dataDepartmentNew'] = $rowTmp['department_new'];
          $arrData['dataSectionOld'] = $rowTmp['section_old'];
          $arrData['dataSectionNew'] = $rowTmp['section_new'];
          $arrData['dataSubSectionOld'] = $rowTmp['sub_section_old'];
          $arrData['dataSubSectionNew'] = $rowTmp['sub_section_new'];
          $arrData['dataDepartmentNewDate'] = $rowTmp['department_new_date'];
        }else{
        	$arrData['dataCompanyOld'] = $arrData['dataCompany'];
          $arrData['dataManagementOld'] = $arrData['dataManagement'];
          $arrData['dataDivisionOld'] = $arrData['dataDivision'];
          $arrData['dataDepartmentOld'] = $arrData['dataDepartment'];
          $arrData['dataSectionOld'] = $arrData['dataSection'];
          $arrData['dataSubSectionOld'] = $arrData['dataSubSection'];
        }
        $strInputDetailSalary = getDetailSalary($strDataID);
      }
    }


    return true;
  } // showData

  // fungsi untuk menyimpan data absen
  function saveData($db, &$strError) {
    global $_REQUEST;
    global $_SESSION;
    global $error;
    global $messages;
    global $arrData;
    global $strDataID;
    global $strStatus;
    $strmodified_byID = $_SESSION['sessionUserID'];
    $strBody = "";
    $strSubjet = "";


    $strError = "";
    $bolOK = true;
    $strToday = date("Y-m-d");

    (isset($_REQUEST['dataEmployee'])) ? $strDataEmployee = trim($_REQUEST['dataEmployee']) : $strDataEmployee = "";
    (isset($_REQUEST['dataDate'])) ? $strDataDate = $_REQUEST['dataDate'] : $strDataDate = "";
    (isset($_REQUEST['dataLetterCode'])) ? $strDataLetterCode = $_REQUEST['dataLetterCode'] : $strDataLetterCode = "";
    (isset($_REQUEST['dataNote'])) ? $strDataNote = $_REQUEST['dataNote'] : $strDataNote = "";
    (isset($_REQUEST['dataNIKNew'])) ? $strDataNIKNew = $_REQUEST['dataNIKNew'] : $strDataNIKNew = "";
    (isset($_REQUEST['dataBranchTemp'])) ? $strDataBranchTemp = $_REQUEST['dataBranchTemp'] : $strDataBranchTemp = "";
    $strDateDepartment = (isset($_REQUEST['dataDepartmentNewDate'])) ? $_REQUEST['dataDepartmentNewDate'] : "";
    $strDatePosition = (isset($_REQUEST['dataPositionNewDate'])) ? $_REQUEST['dataPositionNewDate'] : "";
    $strDateFrom = (isset($_REQUEST['dataStatusDateFrom'])) ? $_REQUEST['dataStatusDateFrom'] : "";
    $strDateThru = (isset($_REQUEST['dataStatusDateThru'])) ? $_REQUEST['dataStatusDateThru'] : "";
//    $strSalaryOldDate = (isset($_REQUEST['dataStartOldDate'])) ? $_REQUEST['dataStartOldDate'] : "";
    $strDateSalary = (isset($_REQUEST['dataSalaryNewDate'])) ? $_REQUEST['dataSalaryNewDate'] : "";
    $strSalarySet = (isset($_REQUEST['dataSalarySetNew'])) ? $_REQUEST['dataSalarySetNew'] : "";
    $strDateBranch = (isset($_REQUEST['dataBranchNewDate'])) ? $_REQUEST['dataBranchNewDate'] : "";
    // cek validasi -----------------------
    if ($strDataEmployee == "") {
      $strError = $error['empty_code'];
      $bolOK = false;
    } else if (!validStandardDate($strDataDate)) {
      $strError = $error['invalid_date'];
      $bolOK =  false;
    } else if (!validStandardDate($strDateFrom) && isset($_REQUEST['dataIsStatus'])) {
      $strError = $error['invalid_date'];
      $bolOK =  false;

    }
		
		if (isset($_REQUEST['dataIsBranch']) && !validStandardDate($strDateBranch) && $strDateBranch == "") {
      $strError = $error['invalid_date'];
      $bolOK =  false;
    }
    
    if (!validStandardDate($strDateDepartment) && isset($_REQUEST['dataIsDepartment']) && $strDateDepartment == "") {
      $strError = $error['invalid_date'];
      $bolOK =  false;
    }

    if (!validStandardDate($strDatePosition) && isset($_REQUEST['dataIsPosition']) && $strDatePosition == "") {
      $strError = $error['invalid_date'];
      $bolOK =  false;
    }

    if (($strDataNIKNew == "") && isset($_REQUEST['dataIsNIK'])) {
      $strError = "Error: NIK Empty";
      $bolOK =  false;
    }

    if (!validStandardDate($strDateSalary) && isset($_REQUEST['dataIsSalary']) && $strDateSalary == "") {
      $strError = $error['invalid_date'];
      $bolOK =  false;
    }
    if ($strSalarySet == "" && isset($_REQUEST['dataIsSalary'])) {
      $strError = "Error: Salary Set Empty";
      $bolOK = false;
    }

    // cari dta Employee ID, apakah ada atau tidak
    // $strSQL  = "SELECT id FROM hrd_employee WHERE employee_id = '$strDataEmployee' AND flag = 0 ";
	  $strSQL  = "SELECT id FROM hrd_employee WHERE employee_id = '$strDataEmployee' ";
	  $resDb = $db->execute($strSQL);
	  if ($rowDb = $db->fetchrow($resDb)) {
	  	$strIDEmployee = $rowDb['id'];
	  }else{
	  	$strIDEmployee = "";
	  }
		
		if ($strIDEmployee == "") {
	  	$strError = $error['data_not_found'];
	    $bolOK = false;
	  }
		if ($strDataID != "") {
	  	$strSQL  = "SELECT id, status FROM hrd_employee_mutation ";
	    $strSQL .= "WHERE id = '$strDataID' ";
	    $resDb = $db->execute($strSQL);
	    if ($rowDb = $db->fetchrow($resDb)) {
	    	$strStatus = $rowDb['status'];
	    } else {
	    	$strStatus = 0;
	    }
	  }
	  if ($strStatus == 3){
	    $bolOK = false;
	    $strSQL  = "UPDATE hrd_employee_mutation";
	    $strSQL .= "SET modified_by = '" .$_SESSION['sessionUserID']. "', ";
	    $strSQL .= "note = '$strDataNote', letter_code = '$strDataLetterCode'  WHERE id = '$strDataID' ";
	    $resExec = $db->execute($strSQL);
	  }
		// simpan data -----------------------
    if ($bolOK) { // input OK, tinggal disimpan
      if ($strDataID == "") {
        // data baru
        $strSQL  = "INSERT INTO hrd_employee_mutation (created,created_by,modified_by, ";
        $strSQL .= "id_employee,proposal_date, note, letter_code, status, type) ";
        $strSQL .= "VALUES(now(),'$strmodified_byID','$strmodified_byID', ";
        $strSQL .= "'$strIDEmployee','$strDataDate', '$strDataNote', '$strDataLetterCode', " .REQUEST_STATUS_NEW.", 0)  ";
        $resExec = $db->execute($strSQL);
        $strSubject = getSubject(0,'Placement Request',getEmployeeIDEmail($strIDEmployee));
        $strBody.= "Name: ".getEmployeeNameEmail($strIDEmployee)."<br>";
        // $strBody.= "Absence Type: ".$strDataType."<br>";
        $strBody.= "Proposal Date: ".$strDataDate."<br>";
        $strBody.= "Change List:<br><br>";
        // $strBody.= "Details: <br><br>";

        // cari ID
        $strSQL  = "SELECT id FROM hrd_employee_mutation ";
        $strSQL .= "WHERE id_employee = '$strIDEmployee' AND proposal_date = '$strDataDate' ";
        $strSQL .= "AND type = 0 AND status = ".REQUEST_STATUS_NEW;
        $resDb = $db->execute($strSQL);
        if ($rowDb = $db->fetchrow($resDb)) {
          $strDataID = $rowDb['id'];
        }
      } else {
        $strSubject = getSubject(0,'Placement Request Update',getEmployeeIDEmail($strIDEmployee));
        $strBody.= "Name: ".getEmployeeName($db,$strIDEmployee)."<br>";
        $strBody.= "Proposal Date: ".$strDataDate."<br>";
        $strBody.= "Change List:<br><br>";
        $strSQL  = "UPDATE hrd_employee_mutation ";
        $strSQL .= "SET modified_by = '" .$_SESSION['sessionUserID']. "', ";
        $strSQL .= "id_employee = '$strIDEmployee', proposal_date = '$strDataDate', ";
        $strSQL .= "note = '$strDataNote', letter_code = '$strDataLetterCode'  WHERE id = '$strDataID' ";
        $resExec = $db->execute($strSQL);
      }

      // simpan data detilnya, jika ada
      if ($strDataID != "") {

        //hapus dulu semua data
        $strSQL  = "DELETE FROM hrd_employee_mutation_status WHERE id_mutation = '$strDataID'; ";
        $strSQL .= "DELETE FROM hrd_employee_mutation_department WHERE id_mutation = '$strDataID'; ";
        $strSQL .= "DELETE FROM hrd_employee_mutation_position WHERE id_mutation = '$strDataID'; ";
        $strSQL .= "DELETE FROM hrd_employee_mutation_salary WHERE id_mutation = '$strDataID'; ";
        $strSQL .= "DELETE FROM hrd_employee_mutation_branch WHERE id_mutation = '$strDataID'; ";
        $strSQL .= "DELETE FROM hrd_employee_mutation_id WHERE id_mutation = '$strDataID'; ";
        $strSQL .= "DELETE FROM hrd_employee_mutation_cost_center WHERE id_mutation = '$strDataID'; ";
        $resDb = $db->execute($strSQL);
        if (isset($_REQUEST['dataIsStatus'])) {
          // simpan data status
          $strEmployeeStatus = (isset($_REQUEST['dataStatusNew'])) ? $_REQUEST['dataStatusNew'] : 0;
          $strDateFrom = ($strDateFrom == "") ? "NULL" : "'$strDateFrom'";
          $strDateThru = ($strDateThru == "") ? "NULL" : "'$strDateThru'";

          $strSQL  = "INSERT INTO hrd_employee_mutation_status (created, modified_by, created_by, ";
          $strSQL .= "id_mutation, status_new, status_date_from, status_date_thru) ";
          $strSQL .= "VALUES(now(), '$strmodified_byID', '$strmodified_byID', '$strDataID', ";
          $strSQL .= "'$strEmployeeStatus', $strDateFrom, $strDateThru) ";
          $resExec = $db->execute($strSQL);

          $strBody .= "- Status Changed<br>";
          $strBody .= "Date From: $strDateFrom<br>";
          $strBody .= "Date Thru: $strDateThru<br><br>";



        }
        if (isset($_REQUEST['dataIsSalary'])) {
          // simpan data salary
          $strBasicSalaryOld = (isset($_REQUEST['dataBasicSalaryOld'])) ? $_REQUEST['dataBasicSalaryOld'] : 0;
          $strBasicSalaryNew = (isset($_REQUEST['dataBasicSalaryNew'])) ? $_REQUEST['dataBasicSalaryNew'] : 0;
          $strSalaryNewDate = (isset($_REQUEST['dataSalaryNewDate'])) ? $_REQUEST['dataSalaryNewDate'] : "";
          $strSalarySetOld = (isset($_REQUEST['dataSalarySetOld'])) ? $_REQUEST['dataSalarySetOld'] : "";
          $strSalarySet = (isset($_REQUEST['dataSalarySetNew'])) ? $_REQUEST['dataSalarySetNew'] : "";
          $totalSalaryType = isset($_REQUEST['hNumShowDetail']) ? $_REQUEST['hNumShowDetail'] : 0;
         	$strSQL = "";
         	$salaryChanges = "";
         	for ($i = 1;$i <= $totalSalaryType;$i++){
          	if (isset($_REQUEST['id_allowance_type'.$i]) && isset($_REQUEST['amount_old'.$i]) && isset($_REQUEST['amount_new'.$i])){
          		$allowanceTypeID = $_REQUEST['id_allowance_type'.$i];
          		$allowanceTypeCode = isset($_REQUEST['allowance_type_code'.$i]) ? $_REQUEST['allowance_type_code'.$i] : '';
          		$allowanceTypeName = isset($_REQUEST['allowance_type_name'.$i]) ? $_REQUEST['allowance_type_name'.$i] : '';
          		$oldValue = $_REQUEST['amount_old'.$i];
          		$newValue = $_REQUEST['amount_new'.$i];
          		$strSQL .= "INSERT INTO hrd_employee_mutation_salary (created, modified_by, created_by, ";
          		$strSQL .= "id_mutation, salary_new_date, id_salary_set, allowance_type_id, allowance_type_code,";
          		$strSQL .= "old_value, new_value, id_salary_set_old) VALUES ";
          		$strSQL .= "(now(), '$strmodified_byID', '$strmodified_byID', '$strDataID', ";
          		$strSQL .= "'$strSalaryNewDate', '$strSalarySet', '$allowanceTypeID',";
          		$strSQL .= "'$allowanceTypeCode','$oldValue','$newValue','$strSalarySetOld');";
          		if ($oldValue != $newValue){
          			$salaryChanges .= $allowanceTypeName." Old Amount : Rp. ".number_format($oldValue,0,',','.').", New Amount : ".number_format($newValue,0,',','.')."<br>";
          		}
          	}
          }
          $resExec = $db->execute($strSQL);

          $strBody .= "- Salary Changed<br>";
          $strBody .= $salaryChanges."<br>";
        }


        if (isset($_REQUEST['dataIsPosition'])) {
          // simpan data status
          $strPositionOld = (isset($_REQUEST['dataPositionOld'])) ? $_REQUEST['dataPositionOld'] : "";
          $strPositionNew = (isset($_REQUEST['dataPositionNew'])) ? $_REQUEST['dataPositionNew'] : "";
          $strGradeOld = (isset($_REQUEST['dataGradeOld'])) ? $_REQUEST['dataGradeOld'] : "";
          $strGradeNew = (isset($_REQUEST['dataGradeNew'])) ? $_REQUEST['dataGradeNew'] : "";
          $strFunctionalOld = (isset($_REQUEST['dataFunctionalOld'])) ? $_REQUEST['dataFunctionalOld'] : "";
          $strFunctionalNew = (isset($_REQUEST['dataFunctionalNew'])) ? $_REQUEST['dataFunctionalNew'] : "";
          //$strDateOld = (isset($_REQUEST['dataPositionOldDate'])) ? $_REQUEST['dataPositionOldDate'] : "";
          $strDateNew = (isset($_REQUEST['dataPositionNewDate'])) ? $_REQUEST['dataPositionNewDate'] : "";

          //$strDateOld = ($strDateOld == "") ? "NULL" : "'$strDateOld'";
          $strDateNew = ($strDateNew == "") ? "NULL" : "'$strDateNew'";

          $strSQL  = "INSERT INTO hrd_employee_mutation_position (created, modified_by, created_by, ";
          $strSQL .= "id_mutation, position_old, position_new, grade_old, ";
          $strSQL .= "grade_new, functional_old, functional_new, position_new_date) ";
          $strSQL .= "VALUES(now(), '$strmodified_byID', '$strmodified_byID', '$strDataID', ";
          $strSQL .= "'$strPositionOld', '$strPositionNew', '$strGradeOld', '$strGradeNew', ";
          $strSQL .= " '$strFunctionalOld', '$strFunctionalNew', $strDateNew) ";
          $resExec = $db->execute($strSQL);
					$strBody .= "- Position Changed<br>";
          $strBody .= "Start Date: $strDateNew<br><br>";
          // $strBody .= "Position: $strPositionOld to $strPositionNew<br>";
          // $strBody .= "Grade: $strGradeOld to $strGradeNew<br>";
          // $strBody .= "Functional: $strFunctionalOld to $strFunctionalNew<br><br>";

        }


        if (isset($_REQUEST['dataIsNIK'])) {
          // simpan data status
          $strNIKOld = (isset($_REQUEST['dataNIKOld'])) ? $_REQUEST['dataNIKOld'] : "";
          $strNIKNew = (isset($_REQUEST['dataNIKNew'])) ? $_REQUEST['dataNIKNew'] : "";

          $strSQL  = "INSERT INTO hrd_employee_mutation_id (created, modified_by, created_by, ";
          $strSQL .= "id_mutation, id_employee_old, id_employee_new) ";
          $strSQL .= "VALUES(now(), '$strmodified_byID', '$strmodified_byID', '$strDataID', ";
          $strSQL .= "'$strNIKOld', '$strNIKNew')";
          $resExec = $db->execute($strSQL);
					$strBody .= "- NIK Changed<br>";
          $strBody .= "Old NIK: $strNIKOld<br>";
          $strBody .= "New NIK: $strNIKNew<br><br>";

        }

        if (isset($_REQUEST['dataIsBranch'])) {
          // simpan data status
          $strBranchContractOld = (isset($_REQUEST['dataBranchContractOld'])) ? $_REQUEST['dataBranchContractOld'] : "";
          $strBranchContractNew = (isset($_REQUEST['dataBranchContractNew'])) ? $_REQUEST['dataBranchContractNew'] : "";
          $strBranchPenugasanOld = (isset($_REQUEST['dataBranchPenugasanOld'])) ? $_REQUEST['dataBranchPenugasanOld'] : "";
          $strBranchPenugasanNew = (isset($_REQUEST['dataBranchPenugasanNew'])) ? $_REQUEST['dataBranchPenugasanNew'] : "";
          $strBranchNewDate = (isset($_REQUEST['dataBranchNewDate'])) ? $_REQUEST['dataBranchNewDate'] : "";

          $strSQL  = "INSERT INTO hrd_employee_mutation_branch (created, modified_by, created_by, ";
          $strSQL .= "id_mutation, branch_contract_old, branch_contract_new, branch_penugasan_old, branch_penugasan_new, branch_new_date) ";
          $strSQL .= "VALUES(now(), '$strmodified_byID', '$strmodified_byID', '$strDataID', ";
          $strSQL .= "'$strBranchContractOld', '$strBranchContractNew', '$strBranchPenugasanOld', '$strBranchPenugasanNew','$strBranchNewDate')";
          $resExec = $db->execute($strSQL);
					$strBody .= "- Branch Changed<br>";
          $strBody .= "Old Branch Contract: $strBranchContractOld<br>";
          $strBody .= "New Branch Contract: $strBranchContractNew<br>";
          $strBody .= "Old Branch Penugasan: $strBranchPenugasanOld<br>";
          $strBody .= "New Branch Penugasan: $strBranchPenugasanNew<br><br>";

        }

        if (isset($_REQUEST['dataIsCostCenter'])) {
          // die('tomi');
          // simpan data status
          $strCostCenterOld = (isset($_REQUEST['dataCostCenterOld'])) ? $_REQUEST['dataCostCenterOld'] : "";
          $strCostCenterNew = (isset($_REQUEST['dataCostCenterNew'])) ? $_REQUEST['dataCostCenterNew'] : "";
          $strCostCenterNewDate = (isset($_REQUEST['dataCostCenterNewDate'])) ? $_REQUEST['dataCostCenterNewDate'] : "";

          $strSQL  = "INSERT INTO hrd_employee_mutation_cost_center (created, modified_by, created_by, ";
          $strSQL .= "id_mutation, cost_center_old, cost_center_new,  cost_center_new_date) ";
          $strSQL .= "VALUES(now(), '$strmodified_byID', '$strmodified_byID', '$strDataID', ";
          $strSQL .= "'$strCostCenterOld', '$strCostCenterNew','$strCostCenterNewDate')";
          $resExec = $db->execute($strSQL);
					$strBody .= "- Cost Center Changed<br>";
          $strBody .= "Old Cost Center : $strCostCenterOld<br>";
          $strBody .= "New Cost Center : $strCostCenterNew<br>";
        }


        if (isset($_REQUEST['dataIsDepartment'])) {
          // simpan data status
          $strManagementOld = (isset($_REQUEST['dataManagementOld'])) ? $_REQUEST['dataManagementOld'] : "";
          $strManagementNew = (isset($_REQUEST['dataManagementNew'])) ? $_REQUEST['dataManagementNew'] : "";
          $strCompanyOld    = (isset($_REQUEST['dataCompanyOld'])) ? $_REQUEST['dataCompanyOld'] : "";
          $strCompanyNew    = (isset($_REQUEST['dataCompanyNew'])) ? $_REQUEST['dataCompanyNew'] : "";
          $strDivisionOld   = (isset($_REQUEST['dataDivisionOld'])) ? $_REQUEST['dataDivisionOld'] : "";
          $strDivisionNew   = (isset($_REQUEST['dataDivisionNew'])) ? $_REQUEST['dataDivisionNew'] : "";
          $strDepartmentOld = (isset($_REQUEST['dataDepartmentOld'])) ? $_REQUEST['dataDepartmentOld'] : "";
          $strDepartmentNew = (isset($_REQUEST['dataDepartmentNew'])) ? $_REQUEST['dataDepartmentNew'] : "";
          $strSectionOld    = (isset($_REQUEST['dataSectionOld'])) ? $_REQUEST['dataSectionOld'] : "";
          $strSectionNew    = (isset($_REQUEST['dataSectionNew'])) ? $_REQUEST['dataSectionNew'] : "";
          $strSubSectionOld = (isset($_REQUEST['dataSubSectionOld'])) ? $_REQUEST['dataSubSectionOld'] : "";
          $strSubSectionNew = (isset($_REQUEST['dataSubSectionNew'])) ? $_REQUEST['dataSubSectionNew'] : "";
          $strDepartmentNewDate = (isset($_REQUEST['dataDepartmentNewDate'])) ? $_REQUEST['dataDepartmentNewDate'] : date("Y-m-d");


          $strSQL  = "INSERT INTO hrd_employee_mutation_department (created, modified_by, created_by, ";
          $strSQL .= "id_mutation, ";
          $strSQL .= "company_old, company_new, ";
          $strSQL .= "management_old, management_new, ";
          $strSQL .= "division_old, division_new, ";
          $strSQL .= "department_old, department_new, ";
          $strSQL .= "section_old, section_new, ";
          $strSQL .= "sub_section_old, sub_section_new, department_new_date)";
          $strSQL .= "VALUES(now(), '$strmodified_byID', '$strmodified_byID', '$strDataID', ";
          $strSQL .= "'$strCompanyOld','$strCompanyNew', ";
          $strSQL .= "'$strManagementOld','$strManagementNew', ";
          $strSQL .= "'$strDivisionOld','$strDivisionNew', ";
          $strSQL .= "'$strDepartmentOld','$strDepartmentNew', ";
          $strSQL .= "'$strSectionOld','$strSectionNew', ";
          $strSQL .= "'$strSubSectionOld','$strSubSectionNew','$strDepartmentNewDate')";
          $resExec = $db->execute($strSQL);
					$strBody .= "- Department Changed<br>";
          $strBody .= "Start Date : $strDepartmentNewDate";

        }

      }
      $strBody .= "<br><br>Details are listed in Placement Request List";
      $strBody =  getBody(0,'Placement Request',$strBody,$strmodified_byID);
      sendMail($strSubject,$strBody);
      $strBody = "";
      writeLog(ACTIVITY_EDIT, MODULE_PAYROLL,"MUTATION DATA",0);

      $strError = $messages['data_saved'];
    } else { // ---- data SALAH

      // gunakan data yang diisikan tadi
      $arrData['dataEmployee'] = $strDataEmployee;
      $arrData['dataDate'] = $strDataDate;
      $arrData['dataLetterCode'] = $strDataLetterCode;
      $arrData['dataNote'] = $strDataNote;
      $arrData['dataID'] = $strDataID;
      getInfoEmployee($db);
      $arrData['dataIsDepartment'] = (isset($_REQUEST['dataIsDepartment'])) ? "true" : "";
      $arrData['dataIsPosition'] = (isset($_REQUEST['dataIsPosition'])) ? "true" : "";
      $arrData['dataIsSalary'] = (isset($_REQUEST['dataIsSalary'])) ? "true" : "";
      $arrData['dataIsStatus'] = (isset($_REQUEST['dataIsStatus'])) ? "true" : "";
      $arrData['dataIsNIK'] = (isset($_REQUEST['dataIsNIK'])) ? "true" : "";
      $arrData['dataIsBranch'] = (isset($_REQUEST['dataIsBranch'])) ? "true" : "";
      $arrData['dataIsCostCenter'] = (isset($_REQUEST['dataIsCostCenter'])) ? "true" : "";

      writeLog(ACTIVITY_EDIT, MODULE_PAYROLL,"ABSENCE DATA",0);
    }
    return $bolOK;
  } // saveData

  // fungsi untuk mengambil data employee yang terakhir
  function getInfoEmployee($db) {
    global $_REQUEST;
    global $_SESSION;
    global $arrData;
		$strID = (isset($_REQUEST['dataEmployee'])) ? $_REQUEST['dataEmployee'] : "";
    //(isset($_REQUEST['dataEmployee'])) ? $strDataEmployee = trim($_REQUEST['dataEmployee']) : $strDataEmployee = "";
    (isset($_REQUEST['dataDate'])) ? $strDataDate = $_REQUEST['dataDate'] : $strDataDate = "";
    (isset($_REQUEST['dataLetterCode'])) ? $strDataLetterCode = $_REQUEST['dataLetterCode'] : $strDataLetterCode = "";
    (isset($_REQUEST['dataNote'])) ? $strDataNote = $_REQUEST['dataNote'] : $strDataNote = "";
    if ($strID != "")
    {
      $arrData['dataEmployee'] = getNIK($db,$strID);
      $arrData['dataDate'] = $strDataDate;
      $arrData['dataLetterCode'] = $strDataLetterCode;
      $arrData['dataNote'] = $strDataNote;

      $strSQL  = "SELECT * FROM hrd_employee WHERE employee_id = '$strID' ";
      $resDb = $db->execute($strSQL);
      if ($rowDb  =  $db->fetchrow($resDb)) {
        $arrData['dataStatusNew'] = $rowDb['employee_status'];
        if ($rowDb['employee_status'] == STATUS_PERMANENT) { // permanent
          $arrData['dataStatusDateFrom'] = $rowDb['permanent_date'];
          $arrData['dataStatusDateThru'] = "";
        } else {
          $arrData['dataStatusDateFrom'] = $rowDb['contract_from'];
          $arrData['dataStatusDateThru'] = $rowDb['due_date'];
        }
        $arrData['dataIDEmployee'] 		 = $rowDb['id'];
        $arrData['dataNIKOld']         = $arrData['dataEmployee'];
        $arrData['dataPositionOld']    = $rowDb['position_code'];
        $arrData['dataGradeOld']       = $rowDb['grade_code'];
        $arrData['dataFunctionalOld']  = $rowDb['functional_code'];
        $arrData['dataCompanyOld']     = $rowDb['id_company'];
        $arrData['dataManagementOld']  = $rowDb['management_code'];
        $arrData['dataDivisionOld']    = $rowDb['division_code'];
        $arrData['dataDepartmentOld']  = $rowDb['department_code'];
        $arrData['dataSectionOld']     = $rowDb['section_code'];
        $arrData['dataSubSectionOld']  = $rowDb['sub_section_code'];
        $arrData['dataBranchContractOld']  = $rowDb['branch_code'];
        $arrData['dataCostCenterOld']  = $rowDb['branch_cost_center_code'];
        $arrData['dataBranchPenugasanOld']  = ($rowDb['branch_penugasan_code']) ? $rowDb['branch_penugasan_code'] : $rowDb['branch_code'];

        // cari data history masing-masing
        $strSQL  = "SELECT t2.approved_time::date AS approved_date FROM hrd_employee_mutation_position AS t1,  ";
        $strSQL .= "hrd_employee_mutation AS t2 WHERE t1.id_mutation = t2.id ";
        $strSQL .= "AND t2.id_employee = '" .$rowDb['id']."' AND t2.status = ".MUTATION_STATUS_APPROVED;
        $strSQL .= " ORDER BY t2.proposal_date DESC LIMIT 1 ";
        $resTmp =  $db->execute($strSQL);
        //echo $strSQL;
        /*
    if ($rowTmp = $db->fetchrow($resTmp))
        {
          $arrData['dataPositionOldDate'] = $rowTmp['approved_date'];
        }
        else
        {
          $arrData['dataPositionOldDate'] = $rowDb['join_date'];
        }*/

        $idSalarySet = "";
        // cari data salary
        $strSQL  = "SELECT id, start_date FROM hrd_basic_salary_set ";
        $strSQL .= "WHERE id_company = '" .$rowDb['id_company']."' ";
        $resTmp1 = $db->execute($strSQL);
        while ($rowTmp = $db->fetchrow($resTmp1)) {
            $strSQL  = "SELECT * FROM hrd_employee_allowance WHERE allowance_code = 'basic_salary' ";
            $strSQL .= "AND id_employee = '" .$rowDb['id']."' ";
//          $strSQL  = "SELECT basic_salary, position_allowance, meal_allowance, transport_allowance, vehicle_allowance, start_date FROM hrd_employee_basic_salary AS t1 ";
//          $strSQL .= "LEFT JOIN hrd_basic_salary_set AS t2 ON t1.id_salary_set = t2.id ";
//          $strSQL .= "WHERE id_salary_set = '$rowTmp[id]' ";
//          $strSQL .= "AND id_employee = '" .$rowDb['id']."' ";
          //$strSQL .= "ORDER BY t1.salary_new_date DESC LIMIT 1 ";
          $resTmp =  $db->execute($strSQL);
          if ($rowTmp = $db->fetchrow($resTmp)) {
            $arrData['dataBasicSalaryOld'] = (float)$rowTmp['amount'] ;;
            $arrData['dataSalarySet'] = $rowTmp['id_salary_set'] ;;
//            $arrData['dataPositionAllowOld'] = $rowTmp['position_allowance'];
//            $arrData['dataMealOld'] = $rowTmp['meal_allowance'];
//            $arrData['dataTransportOld'] = $rowTmp['transport_allowance'];
//            $arrData['dataVehicleOld'] = $rowTmp['vehicle_allowance'];
//            $arrData['dataStartOldDate'] = $rowTmp['start_date'];
          }
        }
      }

    }
  }

  // fungsi untuk mengambil info sstatus karyawan, untuk perpanjangan
  function getEmployeeStatusInfo($db) {
    global $_REQUEST;
    global $arrData;
    $strID = (isset($_REQUEST['dataIDEmployee'])) ? $_REQUEST['dataIDEmployee'] : "";
    if ($strID === "") return false;

    $strSQL  = "SELECT * FROM hrd_employee WHERE id = '$strID' ";
    $resDb = $db->execute($strSQL);
    if ($rowDb = $db->fetchrow($resDb)) {
      $arrData['dataEmployee'] = $rowDb['employee_id'];
      $arrData['dataStatusNew'] = $rowDb['employee_status'];
      $arrData['dataStatusDateFrom'] = getNextDate($rowDb['due_date']);
      $arrData['dataStatusDateThru'] = getNextDate($arrData['dataStatusDateFrom'],365);
      $arrData['dataIsStatus'] = true;
      $arrData['dataIDEmployee'] = $rowDb['id'];
    }
    return false;
  }

  function getSalarySetListSelection($db, $varname, $default = "", $extra = "", $criteria = "", $action = "", $listonly = false) {
    $strResult  = "";

    if (!$listonly) {
      $strResult .= "<select id=\"$varname\" name=\"$varname\" $action>\n";
    }
    $strResult .= $extra;
		if (empty($criteria)) $criteria = "1=1";
    $strSQL  = "SELECT bset.id, bset.start_date, comp.company_name FROM hrd_basic_salary_set bset LEFT JOIN hrd_company comp 
    ON bset.id_company = comp.id  WHERE ".$criteria." ORDER BY id ";
    $resDb = $db->execute($strSQL);
    while ($rowDb = $db->fetchrow($resDb)) {
      $strCode = $rowDb['id'];
      $strName = $rowDb['start_date'].' - '.$rowDb['company_name'];
			($strCode == $default) ? $strSelect = "selected" : $strSelect = "";
      $strResult .= "<option value=\"$strCode\" $strSelect>$strName</option>\n";
    }

    if (!$listonly) {
      $strResult .= "</select>\n";
    }

    return $strResult;
  }// getSalarySetListSelection*/
  //----------------------------------------------------------------------
	
	function getDetailSalary($strMutationID = null, $gradeCode = "")
  {
  	global $_REQUEST;
  	global $db;
  	global $strInputSalarySet;
  	$strEmployeeID = (isset($_REQUEST['dataEmployee'])) ? $_REQUEST['dataEmployee'] : "";
    if (!empty($strMutationID)){
  		$strSQL = "SELECT emp.employee_id FROM hrd_employee_mutation hem LEFT JOIN hrd_employee emp ON ";
  		$strSQL .= "hem.id_employee = emp.id WHERE hem.id='$strMutationID'";	
  		if ($db->connect()){
	  		$resEmp = $db->execute($strSQL);
				$rowEmp = $db->fetchrow($resEmp);
				$strEmployeeID = $rowEmp['employee_id'];
			}
 	 	}
  	$arrFix = array();
  	if ($strEmployeeID != ""){
	  	$strSQL = "SELECT id_company FROM hrd_employee WHERE employee_id='$strEmployeeID'";  
		  $resDb = $db->execute($strSQL);
			$rowDb = $db->fetchrow($resDb);
			$strIDCompany = $rowDb['id_company'];
			$strSQL1 ="SELECT a.id, b.company_name, a.start_date FROM 
			hrd_basic_salary_set a LEFT JOIN hrd_company b ON a.id_company = b.id 
			WHERE a.id_company='$strIDCompany' ORDER BY a.start_date DESC LIMIT 1";
			$res1 = $db->execute($strSQL1);
			$row1 = $db->fetchrow($res1);
			$setID = $row1['id'];
			$strInputSalarySet = '<strong>'.$row1['start_date'].' '.$row1['company_name'].'</strong>'.generateHidden("dataSalarySet", $setID);
			$strSQL  = "SELECT t1.id AS id_employee_key, t4.id AS allowance_type_id, t4.code, t4.amount FROM hrd_employee AS t1 ";
      $strSQL .= "LEFT JOIN ((SELECT id_employee, allowance_code, amount FROM hrd_employee_allowance WHERE id_salary_set = $setID) AS t2 ";
      $strSQL .= " LEFT JOIN (SELECT id, code, active FROM hrd_allowance_type WHERE active = 't') AS t3 ON t2.allowance_code = t3.code) AS t4 ";
      $strSQL .= " ON t1.id = t4.id_employee ";
      $strSQL .= "WHERE t1.employee_id='$strEmployeeID'";
	    $resTmp = $db->execute($strSQL);
	    while ($rowTmp = $db->fetchRow($resTmp)){
	    	$arrFix[$rowTmp['allowance_type_id']] = $rowTmp['amount'];
	    }
		}
	  $jumlahOld = 0;
    $jumlahNew = 0;
    $strResult = "
      <table class=\"dataGrid\" border=\"0\" cellpadding=\"0\" cellspacing=\"0\">
        <tr>
          <th width=30>NO</th>
          <th width=250>".strtoupper(getWords("salary")." & ".getWords("allowances"))."</th>
          <th nowrap>".strtoupper(getWords("old amount"))."</th>
          <th nowrap>".strtoupper(getWords("new amount"))."</th>
        </tr>
        <tbody>";
    $counter = 0;
    $arrAllowance = getActiveAllowanceType();
	 	$tbl=new cModel;
	 	$strSQL  = "SELECT grade_allowance1 FROM hrd_salary_grade ";
      $strSQL .= "WHERE grade_code = '$gradeCode' ";
      $resDb = $tbl->query($strSQL);
      foreach($resDb as $loop){
			$mount= $loop['grade_allowance1'];
		}
		//$arrAllowance[0]['name']="Basic Salary";
		//$arrAllowance[0]['amount']=$mount;
		if (!empty($strMutationID)){
			$strSQL = "SELECT id, id_mutation, salary_new_date,id_salary_set,allowance_type_id,";
			$strSQL .= "allowance_type_code,old_value,new_value FROM hrd_employee_mutation_salary WHERE ";	
			$strSQL .= "id_mutation = '$strMutationID'";
			$resDb = $tbl->query($strSQL);
			$salaryMutationOldData = array();
			$salaryMutationNewData = array();
			if (count($resDb)){
				foreach($resDb as $keyIdx => $mutationDetailData){
					$salaryMutationOldData[$mutationDetailData['allowance_type_id']] = $mutationDetailData['old_value'];
					$salaryMutationNewData[$mutationDetailData['allowance_type_id']] = $mutationDetailData['new_value'];
				}
			}
		}
		foreach($arrAllowance as $idAllowance => $allowance){
      $counter++;
      $row = $arrResult[$idAllowance];
      $oldValue = isset($arrFix[$idAllowance]) ? $arrFix[$idAllowance] : 0;
      if (isset($salaryMutationOldData[$idAllowance])){
      	$oldValue = $salaryMutationOldData[$idAllowance];
      }
      $newValue = isset($arrFix[$idAllowance]) ? $arrFix[$idAllowance] : 0;
      if (isset($salaryMutationNewData[$idAllowance])){
      	$newValue = $salaryMutationNewData[$idAllowance];
      }
      $strResult .= "
        <tr>
          <td align=center>".$counter.".</td>
          <td nowrap>".
            generateHidden("id".$counter, $allowance['id']).
            generateHidden("deleted".$counter, 0).
            generateHidden("id_allowance_type".$counter, $idAllowance).
            generateHidden("allowance_type_code".$counter, $allowance['code']).
            generateHidden("allowance_type_name".$counter, $allowance['name']).
            ucwords(strtolower($allowance['name']))."
          </td>
          <td nowrap>".generateInput("amount_old".$counter , $oldValue, "class='numberformat numeric allowance_item' style='width:100%' readonly='readonly'")."</td>
          <td nowrap>".generateInput("amount_new".$counter , $newValue, "class='numberformat numeric allowance_item2' style='width:100%'")."</td>
        </tr>";
        
      $jumlahOld += $oldValue;
      $jumlahNew += $newValue;  
    }
    $strResult .= "
        <tr >
           <td nowrap colspan=2 bgcolor= \"#c8c5c6\" align=center>Total</td>
           <td nowrap bgcolor=\"#c8c5c6\">".generateInput("amount_old_total" , $jumlahOld, "class='numberformat numeric' style='width:100%' readonly='readonly'")."</td>
           <td nowrap bgcolor=\"#c8c5c6\">".generateInput("amount_new_total" , $jumlahNew, "class='numberformat numeric' style='width:100%' readonly='readonly'")."</td>
        </tr>";
    $strResult .= "
        </tbody>
      </table>".
      generateHidden("hNumShowDetail", $counter);
    return $strResult;
  }
	
  //----MAIN PROGRAM -----------------------------------------------------
  $db = new CdbClass;

  if ($db->connect()) {
    getUserEmployeeInfo();
    $bolIsEmployee = isUserEmployee();

    (isset($_REQUEST['dataID'])) ? $strDataID = $_REQUEST['dataID'] : $strDataID = "";
		if ($_SESSION['sessionUserRole'] == ROLE_EMPLOYEE) {
      if (!$arrUserInfo['isDeptHead'] && !$arrUserInfo['isGroupHead'])
        $bolCanDelete = $bolCanEdit = $bolCanView = false;
    }


    if ($bolCanEdit) {
      if (isset($_REQUEST['btnSave'])) {
        $bolOK = saveData($db, $strError);

        if ($strError != "") {

          $strMessages = $strError;
          $strMsgClass = ($bolOK) ? "class=bgOK" : "class=bgError";
        }
        //$strDataID = ""; // biar tidak mengambil dta, melainkan pakai data baru atau data yang dikirim (jika error)
      }
    }
    if (isset($_REQUEST['btnGet'])) {
      // ambil info tentang employee
      getInfoEmployee($db);

    } else if (isset($_REQUEST['btnRenew'])) {

      getEmployeeStatusInfo($db);
    }
    //$arrData['dataEmployee'] = $arrUserInfo['employee_id']; // beri default user
    $strInputSalarySet = "";
    $strInputDetailSalary = "";
    $strEmployeeID = 0;
    if ($bolCanView) {
      getData($db);
    } else {
      showError("view_denied");
    }


    //----- TAMPILKAN DATA ---------
    $strReadonly = ($arrData['dataStatus'] == 6) ? "readonly" : ""; // kalau dah approve, jadi readonly
    $strStatus = $arrData['dataStatus'];
    (isset($_REQUEST['dataEmployee'])) ? $strDataEmployee = trim($_REQUEST['dataEmployee']) : $strDataEmployee = "";
   //echo $strStatus;
   	$strEmployeeID = $arrData['dataIDEmployee'];
   	$strInputDate = "<input type=text size=15 maxlength=10 name=dataDate id=dataDate value=\"" .$arrData['dataDate']. "\" $strReadonly>";
    $strInputStatusDateFrom = "<input type=text size=15 maxlength=10 name=dataStatusDateFrom id=dataStatusDateFrom value=\"" .$arrData['dataStatusDateFrom']. "\" $strReadonly>";
    $strInputStatusDateThru = "<input type=text size=15 maxlength=10 name=dataStatusDateThru id=dataStatusDateThru value=\"" .$arrData['dataStatusDateThru']. "\" $strReadonly>";
    $strInputDepartmentNewDate = "<input type=text size=15 maxlength=10 name=dataDepartmentNewDate id=dataDepartmentNewDate value=\"" .$arrData['dataDepartmentNewDate']. "\" $strReadonly>";
    $strInputPositionNewDate = "<input type=text size=15 maxlength=10 name=dataPositionNewDate id=dataPositionNewDate value=\"" .$arrData['dataPositionNewDate']. "\" $strReadonly>";
    $strInputSalaryNewDate = "<input type=text size=15 maxlength=10 name=dataSalaryNewDate id=dataSalaryNewDate value=\"" .$arrData['dataSalaryNewDate']. "\" $strReadonly>";
    $strInputBranchNewDate = "<input type=text size=15 maxlength=10 name=dataBranchNewDate id=dataBranchNewDate value=\"" .$arrData['dataBranchNewDate']. "\" $strReadonly>";
    $strInputCostCenterNewDate = "<input type=text size=15 maxlength=10 name=dataCostCenterNewDate id=dataCostCenterNewDate value=\"" .$arrData['dataCostCenterNewDate']. "\" $strReadonly>";

    $strInputEmployee = "<input type=text name=dataEmployee id=dataEmployee size=15 maxlength=30 value=\"" .$arrData['dataEmployee']. "\" style=\"width:$strDefaultWidthPx\" $strReadonly>";
    $strInputLetterCode = "<input type=\"text\" name=dataLetterCode size=15 maxlength=63 value=\"" .$arrData['dataLetterCode']. "\" style=\"width:$strDefaultWidthPx\"  >";
    $strInputNote = "<textarea name=dataNote cols=30 rows=3 wrap='virtual' style=\"width:$strDefaultWidthPx\" >" .$arrData['dataNote']. "</textarea>";

    $strInputBasicSalaryOld = "<input type=text name=dataBasicSalaryOld id=dataBasicSalaryOld size=20   value=\"" .$arrData['dataBasicSalaryOld']. "\"  $strReadonly>";
    $strInputBasicSalaryNew = "<input type=text name=dataBasicSalaryNew id=dataBasicSalaryNew size=20   value=\"" .$arrData['dataBasicSalaryNew']. "\" $strReadonly>";
    $strInputPositionAllowOld = "<input type=text name=dataPositionAllowOld id=dataPositionAllowOld size=20  value=\"" .$arrData['dataPositionAllowOld']. "\" $strReadonly>";
    $strInputPositionAllowNew = "<input type=text name=dataPositionAllowNew id=dataPositionAllowNew size=20 value=\"" .$arrData['dataPositionAllowNew']. "\"  $strReadonly>";
    $strInputMealOld = "<input type=text name=dataMealOld id=dataMealOld size=20  value=\"" .$arrData['dataMealOld']. "\"  $strReadonly>";
    $strInputMealNew = "<input type=text name=dataMealNew id=dataMealNew size=20  value=\"" .$arrData['dataMealNew']. "\" $strReadonly>";
    $strInputTransportOld = "<input type=text name=dataTransportOld id=dataTransportOld size=20  value=\"" .$arrData['dataTransportOld']. "\"  $strReadonly>";
    $strInputTransportNew = "<input type=text name=dataTransportNew id=dataTransportNew size=20  value=\"" .$arrData['dataTransportNew']. "\"  $strReadonly>";
    $strInputVehicleOld = "<input type=text name=dataVehicleOld id=dataVehicleOld size=20 maxlength=30 value=\"" .$arrData['dataVehicleOld']. "\"  $strReadonly>";
    $strInputVehicleNew = "<input type=text name=dataVehicleNew id=dataVehicleNew size=20 maxlength=30 value=\"" .$arrData['dataVehicleNew']. "\"  $strReadonly>";
    $strInputStartOldDate = "<input type=text name=dataStartOldDate id=dataStartOldDate size=20 maxlength=30 value=\"" .$arrData['dataStartOldDate']. "\"  $strReadonly>";
    $strInputStartNewDate = "<input type=text name=dataStartNewDate id=dataStartNewDate size=20 maxlength=30 value=\"" .$arrData['dataStartNewDate']. "\"  $strReadonly>";
    $strInputNIKOld = "<input type=text name=dataNIKOld id=dataNIKOld size=20 maxlength=30 value=\"" .$arrData['dataNIKOld']. "\"  $strReadonly>";
    $strInputNIKNew = "<input type=text name=dataNIKNew id=dataNIKNew size=20 maxlength=30 value=\"" .$arrData['dataNIKNew']. "\"  $strReadonly>";
    $strInputPositionOld = getPositionList($db,"dataPositionOld",$arrData['dataPositionOld'], "$strEmptyOption", "", " style=\"width:$strDefaultWidthPx \" $strReadonly");
    $strInputPositionNew = getPositionList($db,"dataPositionNew",$arrData['dataPositionNew'], "$strEmptyOption", "", " style=\"width:$strDefaultWidthPx \" $strReadonly");
    $strInputGradeOld = getSalaryGradeList($db,"dataGradeOld",$arrData['dataGradeOld'], "$strEmptyOption", "", " style=\"width:$strDefaultWidthPx \" $strReadonly");
    $strInputGradeNew = getSalaryGradeList($db,"dataGradeNew",$arrData['dataGradeNew'], "$strEmptyOption", "", " style=\"width:$strDefaultWidthPx \" $strReadonly");
    $strInputFunctionalOld = getFunctionalPositionList($db,"dataFunctionalOld",$arrData['dataFunctionalOld'], "$strEmptyOption", "", " style=\"width:$strDefaultWidthPx \" $strReadonly");
    $strInputFunctionalNew = getFunctionalPositionList($db,"dataFunctionalNew",$arrData['dataFunctionalNew'], "$strEmptyOption", "", " style=\"width:$strDefaultWidthPx \" $strReadonly");
    $strInputBranchContractNew = getBranchList($db,"dataBranchContractNew",$arrData['dataBranchContractNew'], "$strEmptyOption", "", " style=\"width:$strDefaultWidthPx \" $strReadonly");
    $strInputBranchPenugasanNew = getBranchList($db,"dataBranchPenugasanNew",$arrData['dataBranchPenugasanNew'], "$strEmptyOption", "", " style=\"width:$strDefaultWidthPx \" $strReadonly");
    $strInputBranchContractOld = getBranchList($db,"dataBranchContractOld",$arrData['dataBranchContractOld'], "$strEmptyOption", "", " style=\"width:$strDefaultWidthPx \" $strReadonly");
    $strInputBranchPenugasanOld = getBranchList($db,"dataBranchPenugasanOld",$arrData['dataBranchPenugasanOld'], "$strEmptyOption", "", " style=\"width:$strDefaultWidthPx \" $strReadonly");
    $strInputCostCenterOld = getCostCenterList($db,"dataCostCenterOld",$arrData['dataCostCenterOld'], "$strEmptyOption", "", " style=\"width:$strDefaultWidthPx \" $strReadonly");
    $strInputCostCenterNew = getCostCenterList($db,"dataCostCenterNew",$arrData['dataCostCenterNew'], "$strEmptyOption", "", " style=\"width:$strDefaultWidthPx \" $strReadonly");
    $strInputCompanyOld = getCompanyList($db,"dataCompanyOld",$arrData['dataCompanyOld'], "$strEmptyOption", "", " style=\"width:$strDefaultWidthPx \" $strReadonly");
    $strInputCompanyNew = getCompanyList($db,"dataCompanyNew",$arrData['dataCompanyNew'], "$strEmptyOption", "", " style=\"width:$strDefaultWidthPx \" $strReadonly");
    $strInputManagementOld = getManagementList($db,"dataManagementOld",$arrData['dataManagementOld'], "$strEmptyOption", "", " style=\"width:$strDefaultWidthPx \" $strReadonly");
    $strInputManagementNew = getManagementList($db,"dataManagementNew",$arrData['dataManagementNew'], "$strEmptyOption", "", " style=\"width:$strDefaultWidthPx \" $strReadonly");
    $strInputDivisionOld = getDivisionList($db,"dataDivisionOld",$arrData['dataDivisionOld'], "$strEmptyOption", "", " style=\"width:$strDefaultWidthPx \" onChange=\"checkDivision()\" $strReadonly");
    $strInputDivisionNew = getDivisionList($db,"dataDivisionNew",$arrData['dataDivisionNew'], "$strEmptyOption", "", " style=\"width:$strDefaultWidthPx \" onChange=\"checkDivision()\" $strReadonly");
    $strInputDepartmentOld = getDepartmentList($db,"dataDepartmentOld",$arrData['dataDepartmentOld'], "$strEmptyOption", "", " style=\"width:$strDefaultWidthPx \" onChange=\"checkDepartment()\" $strReadonly");
    $strInputDepartmentNew = getDepartmentList($db,"dataDepartmentNew",$arrData['dataDepartmentNew'], "$strEmptyOption", "", " style=\"width:$strDefaultWidthPx \" onChange=\"checkDepartment()\" $strReadonly");
    $strInputSectionOld = getSectionList($db,"dataSectionOld",$arrData['dataSectionOld'], "$strEmptyOption", "", " style=\"width:$strDefaultWidthPx \" onChange=\"checkSection()\"$strReadonly");
    $strInputSectionNew = getSectionList($db,"dataSectionNew",$arrData['dataSectionNew'], "$strEmptyOption", "", " style=\"width:$strDefaultWidthPx \" onChange=\"checkSection()\" $strReadonly");
    $strInputSubSectionOld = getSubSectionList($db,"dataSubSectionOld",$arrData['dataSubSectionOld'], "$strEmptyOption", "", " style=\"width:$strDefaultWidthPx \" onChange=\"checkSubSection()\"$strReadonly");
    $strInputSubSectionNew = getSubSectionList($db,"dataSubSectionNew",$arrData['dataSubSectionNew'], "$strEmptyOption", "", " style=\"width:$strDefaultWidthPx \" onChange=\"checkSubSection()\" $strReadonly");
    if (!empty($arrData['dataCompanyOld'])){
    	$strSalarySetOld = getSalarySetListSelection($db,"dataSalarySetOld",$arrData['dataSalarySetOld'], "$strEmptyOption", "id_company = '".$arrData['dataCompanyOld']."'", " style=\"width:$strDefaultWidthPx \" $strReadonly");
    }else{
    	$strSalarySetOld = getSalarySetListSelection($db,"dataSalarySetOld",$arrData['dataSalarySetOld'], "$strEmptyOption", "", " style=\"width:$strDefaultWidthPx \" $strReadonly");
    }
    if (!empty($arrData['dataCompanyNew'])){
    	$strSalarySetNew = getSalarySetListSelection($db,"dataSalarySetNew",$arrData['dataSalarySetNew'], "$strEmptyOption", "id_company = '".$arrData['dataCompanyNew']."'", " style=\"width:$strDefaultWidthPx \" $strReadonly");
    }else{
    	$strSalarySetNew = getSalarySetListSelection($db,"dataSalarySetNew",$arrData['dataSalarySetNew'], "$strEmptyOption", "", " style=\"width:$strDefaultWidthPx \" $strReadonly");
    }
    $strInputIDEmployee = "<input type='hidden' id='strIDEmployee' value='$strEmployeeID'>";
    //$strInputSalarySet = getSalarySetListSelection($db,"dataSalarySet",$arrData['dataSalarySet'], "$strEmptyOption", "", " style=\"width:$strDefaultWidthPx \" $strReadonly");
    $strEmptyOption = "<option value=''>&nbsp; </option>\n";
    //12/18/2012
    /*if ($strDataID != ""){
    	$strInputDetailSalary = getDetailSalary($strDataID);
    }else{
    	$strInputDetailSalary = getDetailSalary();
    }*/
    if ($strDataID == ""){
    	$strInputDetailSalary = getDetailSalary();
    }
    $strInputStatusNew = getEmployeeStatusList("dataStatusNew", $arrData['dataStatusNew'], "", " style=\"width:$strDefaultWidthPx \" $strReadonly", "");
  //<option value='99'>Resigned</option>\n <option value='8'>Promotion</option>
		$strInputStatus = $ARRAY_REQUEST_STATUS[$arrData['dataStatus']];

    $strChecked = ($arrData['dataIsStatus']) ? "checked" : "";
    $strClick = " onClick = \"checkStatus()\" ";
    $strInputIsStatus = "<input type=checkbox name=dataIsStatus value=0 $strChecked $strClick $strReadonly>";
    $strChecked = ($arrData['dataIsNIK']) ? "checked" : "";
    $strClick = " onClick = \"checkNIK()\" ";
    $strInputIsNIK = "<input type=checkbox name=dataIsNIK value=0 $strChecked $strClick $strReadonly>";
    $strChecked = ($arrData['dataIsPosition']) ? "checked" : "";
    $strClick = " onClick = \"checkPosition()\" ";
    $strInputIsPosition = "<input type=checkbox name=dataIsPosition value=0 $strChecked $strClick $strReadonly>";
    $strChecked = ($arrData['dataIsDepartment']) ? "checked" : "";
    $strClick = " onClick = \"checkOrganization()\" ";
    $strInputIsDepartment = "<input type=checkbox name=dataIsDepartment value=0 $strChecked $strClick $strReadonly>";
    $strChecked = ($arrData['dataIsSalary']) ? "checked" : "";
    $strClick = " onClick = \"checkSalary()\" ";
    $strInputIsSalary = "<input type=checkbox name=dataIsSalary value=0 $strChecked $strClick $strReadonly>";
    $strChecked = ($arrData['dataIsBranch']) ? "checked" : "";
    $strClick = " onClick = \"checkBranch()\" ";
    $strInputIsBranch = "<input type=checkbox name=dataIsBranch value=0 $strChecked $strClick $strReadonly>";
    $strChecked = ($arrData['dataIsCostCenter']) ? "checked" : "";
    $strClick = " onClick = \"checkCostCenter()\" ";
    $strInputIsCostCenter = "<input type=checkbox name=dataIsCostCenter value=0 $strChecked $strClick $strReadonly>";

    if ($bolPrint) {
      $strInputEmployee = $arrData['dataEmployee']." / ".$arrData['dataEmployeeName'];
      $strInputDepartment = $arrData['dataDepartment'];
      $strInputPosition = $arrData['dataPosition'];
      //$strInputGrade = $arrData['dataGrade'];
      $strInputJoinDate = pgDateFormat($arrData['dataJoinDate'], "d M Y");
      $strInputEmployeeStatus = getWords($ARRAY_EMPLOYEE_STATUS[$arrData['dataEmployeeStatus']]);

      $strInputStatusNew = ($arrData['dataStatusNew'] === "") ? "" : getWords($ARRAY_EMPLOYEE_STATUS[$arrData['dataStatusNew']]);
      $strInputStatusDateFrom = pgDateFormat($arrData['dataStatusDateFrom'], "d M Y");
      $strInputStatusDateThru = pgDateFormat($arrData['dataStatusDateThru'], "d M Y");

      $strInputManagementOld = $arrData['dataManagementOld'];
      $strInputManagementNew = $arrData['dataManagementNew'];
      $strInputCompanyOld = $arrData['dataCompanyOld'];
      $strInputCompanyNew = $arrData['dataCompanyNew'];
      $strInputDivisionOld = $arrData['dataDivisionOld'];
      $strInputDivisionNew = $arrData['dataDivisionNew'];
      $strInputDepartmentOld = $arrData['dataDepartmentOld'];
      $strInputDepartmentNew = $arrData['dataDepartmentNew'];
      $strInputSectionOld = $arrData['dataSectionOld'];
      $strInputSectionNew = $arrData['dataSectionNew'];
      $strInputSuSectionOld = $arrData['dataSubSectionOld'];
      $strInputSubSectionNew = $arrData['dataSubSectionNew'];
      $strInputDepartmentNewDate = pgDateFormat($arrData['dataDepartmentNewDate'], "d M Y");

      $strInputBasicSalaryOld = $arrData['dataBasicSalaryOld'];
      $strInputBasicSalaryNew = $arrData['dataBasicSalaryNew'];
      $strInputPositionAllowOld = $arrData['dataPositionAllowOld'];
      $strInputPositionAllowNew = $arrData['dataPositionAllowNew'];
      $strInputMealAllowOld = $arrData['dataMealOld'];
      $strInputMealAllowNew = $arrData['dataMealNew'];
      $strInputTransportAllowOld = $arrData['dataTransportOld'];
      $strInputTransportAllowNew = $arrData['dataTransportNew'];
      $strInputVehicleAllowOld = $arrData['dataVehicleOld'];
      $strInputVehicleAllowNew = $arrData['dataVehicleNew'];
      $strInputStartNewDate = pgDateFormat($arrData['dataStartNewDate'], "d M Y");
      $strInputNote = nl2br($arrData['dataNote']);
    }

    // tambahan tombol
    $strDisabledPrint = ($strDataID != "") ? "" : "disabled";
    $strBtnPrint .= "<input type=button name=btnPrint onClick=\"window.open('mutation_edit.php?btnPrint=Print&dataID=$strDataID');\" value=\"" .getWords("print")."\" $strDisabledPrint>";
  }


  ($bolPrint) ? $strMainTemplate = getTemplate("mutation_edit_print.html", false) : $strTemplateFile = getTemplate("mutation_edit.html");

  $tbsPage = new clsTinyButStrong ;

  //write this variable in every page
  $strPageTitle = $dataPrivilege['menu_name'];
  $pageIcon = "../images/icons/".$dataPrivilege['icon_file'];
  //------------------------------------------------
  //Load Master Template
  $tbsPage->LoadTemplate($strMainTemplate) ;
  $tbsPage->Show() ;

?>
