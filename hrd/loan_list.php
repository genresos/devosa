<?php
  include_once('../global/session.php');
  include_once('global.php');
  include_once('form_object.php');
  include_once('activity.php');
	//include_once("../includes/krumo/class.krumo.php");
	$dataPrivilege = getDataPrivileges(basename($_SERVER['PHP_SELF']), $bolCanView, $bolCanEdit, $bolCanDelete, $bolCanApprove, $bolCanCheck, $bolCanAcknowledge, $bolCanApprove2);
  $dataPrivilegeHRD = getDataPrivileges("loan_edit_hrd.php", $bolCanViewHRD, $bolCanEditHRD, $bolCanDeleteHRD, $bolCanApproveHRD, $bolCanCheckHRD, $bolCanAcknowledgeHRD);
  $dataPrivilegeFA = getDataPrivileges("loan_edit_finance.php", $bolCanViewFA, $bolCanEditFA, $bolCanDeleteFA, $bolCanApproveFA, $bolCanCheckFA, $bolCanAcknowledgeFA);
  $dataPrivilegeTLS = getDataPrivileges("loan_edit_tools.php", $bolCanViewTLS, $bolCanEditTLS, $bolCanDeleteTLS, $bolCanApproveTLS, $bolCanCheckTLS, $bolCanAcknowledgeTLS);

  if (!$bolCanView) die(accessDenied($_SERVER['HTTP_REFERER']));

  $bolPrint = (isset($_REQUEST['btnPrint']) || isset($_REQUEST['btnPrintStatus']) || isset($_REQUEST['btnPrintDepartment']) || isset($_REQUEST['btnPrintPosition']));



  //---- INISIALISASI ----------------------------------------------------
  $strDataDetail = "";
  $strHidden = "";
  $intTotalData = 0;
  $strWordsDataEntry  = getWords("data entry");
  $strWordsLoanList  = getWords("loan list");
  $strWordsLoanCategory  = getWords("loan category");
  $strWordsStatus  = getWords("status");
  $strWordsLoanType  = getWords("loan type");
  $strWordsLoanPurpose  = getWords("loan purpose / reason");
  $strWordsDateFrom = getWords("date from");
  $strWordsDateThru = getWords("date thru");
  $strWordsLoanType = getWords("loan type");
  $strWordsEmployeeID = getwords("n i k");
  $strWordsCompany = getWords("company");
  $strWordsDivision = getWords("division");
  $strWordsDepartment = getWords("department");
  $strWordsSection = getWords("section");
  $strWordsSubsection = getWords("sub section");
  $strWordsBranch = getWords("branch office");
  $strWordsActive = getWords("active");
  $strWordsListEmpLoan = getWords("list of employee loan");
  $strWordsName = getWords("name");
  $strWordsPosition = getWords("position");
  $strWordsLoanDate = getWords("loan date");
  $strWordsJoinDate = getWords("join date");
  $strWordsResignDate = getWords("resign date");
  $strWordsType = getWords("type");
  $strWordsAmount = getWords("amount");
  $strWordsInterest = getWords("interest");
  $strWordsPeriode = getWords("periode");
  $strWordsMonthlyPayment = getWords("monthly payment");
  $strWordsStartPayment = getWords("start payment");
  $strWordsFinishPayment = getWords("finish payment");
  $strWordsPaid = getWords("paid");
  $strWordsNote = getWords("note");
  $strWordsCreatedBy = getWords("created by");
  $strWordsExcel = getWords("excel");
  $strWordsShowData = getWords("show data");
  $strWordsNew       = getWords("new");
  $strWordsDenied    = getWords("denied");
  $strWordsChecked   = getWords("checked");
  $strWordsApproved  = getWords("approved");
  $strWordsApproved2 = getWords("approved 2");
  $strWordsEmployeeID2 = getWords("n i k corporate");
  $strWordsDivision = getWords("division code");
  $strWordsDepartment = getWords("department code");
  $strWordsFunctional = getWords("functional position code");

  //----------------------------------------------------------------------

  //--- DAFTAR FUNSI------------------------------------------------------
  // fungsi untuk menampilkan data
  // $db = kelas database, $intRows = jumlah baris (return)
  // $strKriteria = query kriteria, $strOrder = query ORder by
  function getData($db, $strDataDateFrom, $strDataDateThru, &$intRows, $strKriteria = "", $strOrder = "") {
    global $bolPrint;
    global $words;
    global $ARRAY_EMPLOYEE_STATUS;
    $dtToday = getdate();

    $intRows = 0;
    $strResult = "";

    // ambil dulu data employee, kumpulkan dalam array
    $arrEmployee = array();
    $intSalaryDate = getSetting("salary_date");
    if (!is_numeric($intSalaryDate)) $intSalaryDate = 25; // default
    $i = 0;
    $strSQL  = "SELECT t1.*, t2.employee_id, t2.employee_id_2, t2.division_code, t2.join_date, t2.resign_date, t2.functional_code, t2.department_code, t2.position_code, t2.employee_name, t1.status as loan_status, ";
    $strSQL .= "EXTRACT(month FROM AGE(payment_from)) AS paid, ";
    $strSQL .= "t2.section_code, t2.active, t2.sub_section_code FROM hrd_loan AS t1 ";
    $strSQL .= "LEFT JOIN hrd_employee AS t2 ON t1.id_employee = t2.id ";
    $strSQL .= "WHERE loan_date BETWEEN '$strDataDateFrom' AND '$strDataDateThru' ";
    $strSQL .= $strKriteria;
    $strSQL .= "ORDER BY $strOrder t2.employee_name, t1.loan_date, t1.periode ";
  //  die($strSQL);
    $resDb = $db->execute($strSQL);
    $strDateOld = "";
    while ($rowDb = $db->fetchrow($resDb)) {
      $intRows++;

      // cari total pembayaran
      $intTotalPayment = $rowDb['paid'];
      // jika lebih dari tgl 25, ditambah 1

      if ($dtToday['mday'] > $intSalaryDate) {
        $intTotalPayment++;
      }
      if ($intTotalPayment > $rowDb['periode']) {
        $intTotalPayment = $rowDb['periode'];
      }

      $strClass = getCssClass($rowDb['loan_status']);

      // hitung cicilan
      if ($rowDb['periode'] == 0) {
        $fltMonthlyPayment = 0;
      } else {
        $fltMonthlyPayment = ((((100 + $rowDb['interest']) / 100) * $rowDb['amount']) / $rowDb['periode']);
      }

      $strResult .= "<tr valign=top class=$strClass>\n";
      if ($bolPrint)
        $strResult .= "  <td>&nbsp;</td>\n";
      else
        $strResult .= "  <td><input type=checkbox name='chkID$intRows' value=\"" .$rowDb['id']. "\"></td>\n";
      $strResult .= "  <td>".$rowDb['employee_id']."</td>";
      $strResult .= "  <td>".$rowDb['employee_id_2']."</td>";
      $strResult .= "  <td>" .$rowDb['employee_name']. "&nbsp;</td>";
      $strResult .= "  <td>" .$rowDb['division_code']. "&nbsp;</td>";
      $strResult .= "  <td>" .$rowDb['department_code']. "&nbsp;</td>";
      $strResult .= "  <td>" .$rowDb['position_code']. "&nbsp;</td>";
      $strResult .= "  <td>" .$rowDb['functional_code']. "&nbsp;</td>";
      $strResult .= "  <td align=center>" .pgDateFormat($rowDb['join_date'],"d-M-y"). "&nbsp;</td>";
      $strResult .= "  <td align=center>" .pgDateFormat($rowDb['resign_date'],"d-M-y"). "&nbsp;</td>";
      $strResult .= "  <td align=center>" .printAct($rowDb['active']). "&nbsp;</td>";
      $strResult .= "  <td align=center>" .pgDateFormat($rowDb['loan_date'],"d-M-y"). "&nbsp;</td>";
      $strResult .= "  <td align=center>".printLoanCategory($rowDb['category']). "&nbsp;</td>";
      $strResult .= "  <td align=center>".$rowDb['purpose']. "&nbsp;</td>";
      $strResult .= "  <td align=center>".$rowDb['type']. "&nbsp;</td>";
      $strResult .= "  <td align=right style = 'mso-number-format:\"0\,00\"'>" .$rowDb['amount']. "</td>";
      $strResult .= "  <td align=right style = 'mso-number-format:\"0\,00\"'>" .$rowDb['interest']. "</td>";
      $strResult .= "  <td align=right style = 'mso-number-format:\"0\"'>" .$rowDb['periode']."</td>";
      $strResult .= "  <td align=right style = 'mso-number-format:\"0\,00\"'>" .$fltMonthlyPayment. "</td>";
      $strResult .= "  <td align=center>" .pgDateFormat($rowDb['payment_from'],"M-y"). "&nbsp;</td>";
      $strResult .= "  <td align=center>" .pgDateFormat($rowDb['payment_thru'],"M-y"). "&nbsp;</td>";
      $strResult .= "  <td align=right style = 'mso-number-format:\"0\.00\"'>" .$intTotalPayment. " x</td>";
      $strResult .= "  <td>" .$rowDb['note']. "&nbsp;</td>";
      $strResult .= "  <td>" .getUserName($rowDb['created_by']). "&nbsp;</td>";

      if (!$bolPrint){
        if ($rowDb['category'] == 0)
          $strResult .= "  <td align=center><a href=\"loan_edit_hrd.php?dataID=" .$rowDb['id']. "\">" .$words['edit']. "</a>&nbsp;</td>";
        elseif ($rowDb['category'] == 1)
          $strResult .= "  <td align=center><a href=\"loan_edit_tools.php?dataID=" .$rowDb['id']. "\">" .$words['edit']. "</a>&nbsp;</td>";
        else
          $strResult .= "  <td align=center><a href=\"loan_edit_finance.php?dataID=" .$rowDb['id']. "\">" .$words['edit']. "</a>&nbsp;</td>";
        $strResult .= "</tr>\n";
      }
    }

    if ($intRows > 0) {
      writeLog(ACTIVITY_VIEW, MODULE_PAYROLL,"$intRows data",0);
    }

    return $strResult;
  } // showData
  function printAct($a)
  {
    if ($a == 1)
      return "&radic;";
    else
      return "";
  }
	function printActXLS($a)
  {
  	$str = "&radic;";
    if ($a == 1)
      return html_entity_decode($str,ENT_QUOTES,'UTF-8');
    else
      return "";
  }
	/* 
	function name : getDataXLS 
	description		: untuk keperluan export ke excel menggunakan phpexcel
	parameter			: 
	*/
	function getDataXLS($db, $strDataDateFrom, $strDataDateThru, &$intRows, $strKriteria = "", $strOrder = ""){
  	global $headers1;
  	global $headers2;
  	global $objectName;
  	global $bolPrint;
    global $words;
    global $ARRAY_EMPLOYEE_STATUS;
    global $strWordsLoanList;
	  global $strWordsLoanCategory;
	  global $strWordsStatus;
	  global $strWordsLoanType;
	  global $strWordsLoanPurpose;
	  global $strWordsDateFrom;
	  global $strWordsDateThru;
	  global $strWordsLoanType;
	  global $strWordsEmployeeID;
	  global $strWordsCompany;
	  global $strWordsDivision;
	  global $strWordsDepartment;
	  global $strWordsSection;
	  global $strWordsSubsection;
	  global $strWordsActive;
	  global $strWordsListEmpLoan;
	  global $strWordsName;
	  global $strWordsPosition;
	  global $strWordsLoanDate;
	  global $strWordsJoinDate;
	  global $strWordsResignDate;
	  global $strWordsType;
	  global $strWordsAmount;
	  global $strWordsInterest;
	  global $strWordsPeriode;
	  global $strWordsMonthlyPayment;
	  global $strWordsStartPayment;
	  global $strWordsFinishPayment;
	  global $strWordsPaid;
	  global $strWordsNote;
	  global $strWordsCreatedBy;
	  global $strWordsEmployeeID2;
	  global $strWordsDivision;
	  global $strWordsDepartment;
	  global $strWordsFunctional;
	  
  	$headers1 = array(
			array('value' => $strWordsEmployeeID), array('value' => $strWordsEmployeeID2), array('value' => $strWordsName), 
			array('value' => $strWordsDivision),array('value' => $strWordsDepartment),array('value' => $strWordsPosition), 
			array('value' => $strWordsFunctional), array('value' => $strWordsJoinDate),array('value' => $strWordsResignDate), 
			array('value' => $strWordsActive),array('value' => $strWordsLoanDate),array('value' => $strWordsLoanCategory), 
			array('value' => $strWordsLoanPurpose), array('value' => $strWordsLoanType), array('value' => $strWordsAmount), 
			array('value' => $strWordsInterest),array('value' => $strWordsPeriode),array('value' => $strWordsMonthlyPayment), 
			array('value' => $strWordsStartPayment),array('value' => $strWordsFinishPayment),array('value' => $strWordsPaid), 
			array('value' => $strWordsNote),array('value' => $strWordsCreatedBy)
		);
		$headers2 = null;
		$objectName = array(
			'employee_id','employee_id_2','employee_name','division_code', 
			'department_code', 'position_code', 'functional_code', 'join_date',
			'resign_date', 'active','loan_date', 'category', 
			'purpose','type', 'amount', 'interest', 
			'periode', 'monthly_payment', 'payment_from', 
			'payment_thru', 'total_payment', 'note', 'created_by'
		);	
		
		$intRows = 0;
    // ambil dulu data employee, kumpulkan dalam array
    $arrEmployee = array();
    $intSalaryDate = getSetting("salary_date");
    if (!is_numeric($intSalaryDate)) $intSalaryDate = 25; // default
    $i = 0;
    $strSQL  = "SELECT t1.*, t2.employee_id, t2.employee_id_2, t2.division_code, t2.join_date, t2.resign_date, t2.functional_code, t2.department_code, t2.position_code, t2.employee_name, t1.status as loan_status, ";
    $strSQL .= "EXTRACT(month FROM AGE(payment_from)) AS paid, ";
    $strSQL .= "t2.section_code, t2.active, t2.sub_section_code FROM hrd_loan AS t1 ";
    $strSQL .= "LEFT JOIN hrd_employee AS t2 ON t1.id_employee = t2.id ";
    $strSQL .= "WHERE loan_date BETWEEN '$strDataDateFrom' AND '$strDataDateThru' ";
    $strSQL .= $strKriteria;
    $strSQL .= "ORDER BY $strOrder t2.employee_name, t1.loan_date, t1.periode ";
    $resDb = $db->execute($strSQL);
    $strDateOld = "";
    while ($rowDb = $db->fetchrow($resDb)) {
      $intRows++;
			$detailData = new stdClass();
      $intTotalPayment = $rowDb['paid'];
      if ($dtToday['mday'] > $intSalaryDate) {
        $intTotalPayment++;
      }
      if ($intTotalPayment > $rowDb['periode']) {
        $intTotalPayment = $rowDb['periode'];
      }
			$detailData->total_payment = $intTotalPayment;
      $strClass = getCssClass($rowDb['loan_status']);
      if ($rowDb['periode'] == 0) {
        $fltMonthlyPayment = 0;
      } else {
        $fltMonthlyPayment = ((((100 + $rowDb['interest']) / 100) * $rowDb['amount']) / $rowDb['periode']);
      }
			$detailData->monthly_payment = $fltMonthlyPayment;
			$detailData->employee_id = $rowDb['employee_id'];
			$detailData->employee_id_2 = $rowDb['employee_id_2'];
			$detailData->employee_name = $rowDb['employee_name'];
			$detailData->division_code = $rowDb['division_code'];
			$detailData->department_code = $rowDb['department_code'];
			$detailData->position_code = $rowDb['position_code'];
      $detailData->functional_code = $rowDb['functional_code'];
			$detailData->join_date = pgDateFormat($rowDb['join_date'],"d-M-y");
			$detailData->resign_date = pgDateFormat($rowDb['resign_date'],"d-M-y");
			$detailData->active = printActXLS($rowDb['active']);
			$detailData->loan_date = pgDateFormat($rowDb['loan_date'],"d-M-y");
			$detailData->category = printLoanCategory($rowDb['category']);
			$detailData->purpose = $rowDb['purpose'];
			$detailData->type = $rowDb['type'];
			$detailData->amount = $rowDb['amount'];
      $detailData->interest = $rowDb['interest'];
      $detailData->periode = $rowDb['periode'];
      $detailData->interest = $rowDb['interest'];
      $detailData->payment_from = pgDateFormat($rowDb['payment_from'],"M-y");
      $detailData->payment_thru = pgDateFormat($rowDb['payment_thru'],"M-y");
      $detailData->note = $rowDb['note'];
      $detailData->created_by = getUserName($rowDb['created_by']);
      $arrayData[] = $detailData;
    }
    return $arrayData;
  }
	
  function getUserName($a)
  {
    global $db;

    $strSQL = "SELECT employee_name FROM adm_user as t0 LEFT JOIN hrd_employee as t1 ON t0.employee_id = t1.employee_id WHERE t0.id_adm_user = $a";
    $resDb = $db->execute($strSQL);
    if ($rowDb = $db->fetchrow($resDb))
    {
      return $rowDb['employee_name'];
    }
    else {
      return $a;
    }

  }

  // fungsi untuk menghapus data
  function deleteData($db) {
    global $_REQUEST;

    $i = 0;
    foreach ($_REQUEST as $strIndex => $strValue) {
      if (substr($strIndex,0,5) == 'chkID') {
        $strSQL  = "DELETE FROM hrd_loan WHERE id = '$strValue' ";
        $resExec = $db->execute($strSQL);
        $i++;
      }
    }
    if ($i > 0) {
      writeLog(ACTIVITY_DELETE, MODULE_PAYROLL,"$i data ",0);
    }
  } //deleteData

  function callChangeStatus2() {

    global $_REQUEST;
    global $db;
    if (isset($_REQUEST['btnVerified'])) $intStatus = REQUEST_STATUS_VERIFIED;
    else if (isset($_REQUEST['btnChecked'])) $intStatus = REQUEST_STATUS_CHECKED;
    else if (isset($_REQUEST['btnApproved'])) $intStatus = REQUEST_STATUS_APPROVED;
    else if (isset($_REQUEST['btnApproved2'])) $intStatus = REQUEST_STATUS_APPROVED_2;
    else if (isset($_REQUEST['btnDenied'])) $intStatus = REQUEST_STATUS_DENIED;
    else if (isset($_REQUEST['btnPaid'])) $intStatus = REQUEST_STATUS_PAID;
    changeStatus($db, $intStatus);
  }


// fungsi untuk verify, check, deny, atau approve
  function changeStatus($db, $intStatus) {
    global $_REQUEST;
    global $_SESSION;
    if (!is_numeric($intStatus)) {
      return false;
    }

    $strUpdate = "";
    $strSQL  = "";
    $arrEmailData = "";
    $strmodified_byID = $_SESSION['sessionUserID'];
    $strUpdate = getStatusUpdateString($intStatus);

//     die(print_r($_REQUEST));
    foreach ($_REQUEST as $strIndex => $strValue)
    {
      $strBody = "";
      if (substr($strIndex,0,5) == 'chkID')
      {
        $strSQLx = "SELECT status, employee_name, t1.id_employee, t1.created, type, amount
                    FROM hrd_loan AS t1
                    LEFT JOIN hrd_employee AS t2 ON t1.id_employee = t2.id
                    WHERE t1.id = '$strValue' ";
        $resDb = $db->execute($strSQLx);
        if ($rowDb = $db->fetchrow($resDb))
        {
          //the status should be increasing
          //if ($rowDb['status'] < $intStatus && $rowDb['status'] != REQUEST_STATUS_DENIED )
          if (isProcessable($rowDb['status'], $intStatus))
              {
                $strSQL .= "UPDATE hrd_loan SET status = '$intStatus'  ";
                $strSQL .= "WHERE id = '$strValue'; ";
                $resExec = $db->execute($strSQL);
                writeLog(ACTIVITY_EDIT, MODULE_PAYROLL, $rowDb['employee_name']." - ". $rowDb['created'] ." - ". $rowDb['type'] . ": ". $rowDb['amount'], $intStatus);
              }
        }
      }
    }

  } //changeStatus

  //----------------------------------------------------------------------

  //----MAIN PROGRAM -----------------------------------------------------
  $strInfo = "";
  $intDefaultStart = "07:30";
  $intDefaultFinish = "16:30";
	$headers1 = array();
	$headers2 = array();
  $objectName = array();
  $db = new CdbClass;
  if ($db->connect()) {
  	deleteExcelDownloadedFile();
    GetUserEmployeeInfo($db);
    callChangeStatus2();
    // hapus data jika ada perintah
    if (isset($_REQUEST['btnDelete'])) {
      if ($bolCanDelete) {
        deleteData($db);
      }
    }

    // ------ AMBIL DATA KRITERIA -------------------------
    $dtFrom = date("Y-m-")."25";
    $dtFrom = getNextDateNextMonth($dtFrom, -1);
    $dtThru = date("Y-m-")."24";
    (isset($_REQUEST['dataDateFrom'])) ? $strDataDateFrom = $_REQUEST['dataDateFrom'] : $strDataDateFrom = $dtFrom;
    (isset($_REQUEST['dataDateThru'])) ? $strDataDateThru = $_REQUEST['dataDateThru'] : $strDataDateThru = $dtThru;
    (isset($_REQUEST['dataDivision'])) ? $strDataDivision = $_REQUEST['dataDivision'] : $strDataDivision = "";
    (isset($_REQUEST['dataDepartment'])) ? $strDataDepartment = $_REQUEST['dataDepartment'] : $strDataDepartment = "";
    (isset($_REQUEST['dataSection'])) ? $strDataSection = $_REQUEST['dataSection'] : $strDataSection = "";
    (isset($_REQUEST['dataStatus'])) ? $strDataStatus = $_REQUEST['dataStatus'] : $strDataStatus = "";
    (isset($_REQUEST['dataSubsection'])) ? $strDataSubsection = $_REQUEST['dataSubsection'] : $strDataSubsection = "";
    (isset($_REQUEST['dataBranch'])) ? $strDataBranch = $_REQUEST['dataBranch'] : $strDataBranch = "";
    (isset($_REQUEST['dataActive'])) ? $strDataActive = $_REQUEST['dataActive'] : $strDataActive = 1;
    (isset($_REQUEST['dataEmployee'])) ? $strDataEmployee = $_REQUEST['dataEmployee'] : $strDataEmployee = "";
    (isset($_REQUEST['dataLoanType'])) ? $strDataLoanType = $_REQUEST['dataLoanType'] : $strDataLoanType = "";
    (isset($_REQUEST['dataLoanCategory'])) ? $strDataLoanCategory = $_REQUEST['dataLoanCategory'] : $strDataLoanCategory = 0;
    scopeCBDataEntry($strDataEmployee, $_SESSION['sessionUserRole'], $arrUserInfo);

    if ($bolCanViewFA && $bolCanViewHRD && $bolCanViewTLS)
    {
      (isset($_REQUEST['dataLoanCategory'])) ? $strDataLoanCategory = $_REQUEST['dataLoanCategory'] : $strDataLoanCategory = -1;
    }
    else if ($bolCanViewHRD)
    {
      (isset($_REQUEST['dataLoanCategory'])) ? $strDataLoanCategory = $_REQUEST['dataLoanCategory'] : $strDataLoanCategory = 0;
    }
    else if ($bolCanViewTLS)
    {
      (isset($_REQUEST['dataLoanCategory'])) ? $strDataLoanCategory = $_REQUEST['dataLoanCategory'] : $strDataLoanCategory = 1;
    }
    else if ($bolCanViewFA)
    {
      (isset($_REQUEST['dataLoanCategory'])) ? $strDataLoanCategory = $_REQUEST['dataLoanCategory'] : $strDataLoanCategory = 2;
    }

    // ------------ GENERATE KRITERIA QUERY,JIKA ADA -------------
    $strKriteria = "";
    if ($strDataDivision != "") {
      $strKriteria .= "AND t2.division_code = '$strDataDivision' ";
    }
    if ($strDataLoanType != "") {
      $strKriteria .= "AND \"type\" = '$strDataLoanType' ";
    }
    if ($strDataLoanCategory >= 0) {
      $strKriteria .= "AND \"category\" = '$strDataLoanCategory' ";
    }
    if ($strDataStatus != "") {

      $strKriteria .= "AND status = '$strDataStatus' ";
    }
    if ($strDataDepartment != "") {
      $strKriteria .= "AND t2.department_code = '$strDataDepartment' ";
    }
    if ($strDataSection != "") {
      $strKriteria .= "AND t2.section_code = '$strDataSection' ";
    }
    if ($strDataSubsection != "") {
      $strKriteria .= "AND t2.sub_section_code = '$strDataSubsection' ";
    }
    if ($strDataBranch != "") {
      $strKriteria .= "AND t2.branch_code = '$strDataBranch' ";
    }
    // if ($strDataActive != "") {
    //   $strKriteria .= "AND t2.active = '$strDataActive' ";
    // }
    if ($strDataEmployee != "") {
      $strKriteria .= "AND t2.employee_id = '$strDataEmployee' ";
    }
    $strKriteria .= $strKriteriaCompany;


    if ($bolCanView) {
      if (validStandardDate($strDataDateFrom) && validStandardDate($strDataDateThru)) {
        // tampilkan hanya jika ada permintaan dan data tanggalnya tepat
        $strDataDetail = getData($db,$strDataDateFrom, $strDataDateThru, $intTotalData, $strKriteria);
      } else {
        $strDataDetail = "";
      }
    } else {
      showError("view_denied");
      $strDataDetail = "";
    }

    if ($bolCanView) {
      $arrayData = "";
      $xlsfilename = "";
      if (isset($_REQUEST['btnExcel'])) {
        $arrayData = getDataXLS($db,$strDataDateFrom, $strDataDateThru, $intTotalData, $strKriteria);
        $explodeDateFrom = explode('-', $strDataDateFrom);
        $explodeDateThru = explode('-', $strDataDateThru);
        $intDateFrom = mktime(0,0,0,$explodeDateFrom[1],$explodeDateFrom[2],$explodeDateFrom[0]);
        $intDateThru = mktime(0,0,0,$explodeDateThru[1],$explodeDateThru[2],$explodeDateThru[0]);
        $subtitle = strtoupper(date('d M Y', $intDateFrom)).' >> '.strtoupper(date('d M Y', $intDateThru));
        $xlsfilename = exportXLSX($arrayData,$headers1,$headers2,$objectName,$strWordsLoanList,$subtitle,'loan-list');
				$tblTempFile = new cModel("hrd_temporary_file", "Temporary File");
				$data = array();
				$data['filename'] = $xlsfilename;
				$data['created'] = date('Y-m-d H:i:s');
				$tblTempFile->insert($data);
      }
    }

    // generate data hidden input dan element form input
    $intDefaultWidthPx = 200;
    $strInputDateFrom = "<input type=text name=dataDateFrom id=dataDateFrom size=15 maxlength=10 value=\"$strDataDateFrom\">";
    $strInputDateThru = "<input type=text name=dataDateThru id=dataDateThru size=15 maxlength=10 value=\"$strDataDateThru\">";
    $strInputEmployee = "<input type=text name=dataEmployee id=dataEmployee size=15 maxlength=30 value=\"$strDataEmployee\" $strNonCbReadonly>";
    $strInputDivision = getDivisionList($db,"dataDivision",$strDataDivision, $strEmptyOption, "", "style=\"width:$intDefaultWidthPx\"");
    $strInputDepartment = getDepartmentList($db,"dataDepartment",$strDataDepartment, $strEmptyOption, "", "style=\"width:$intDefaultWidthPx\"");
    $strInputSection = getSectionList($db,"dataSection",$strDataSection, $strEmptyOption, "", "style=\"width:$intDefaultWidthPx\"");
    $strInputSubsection = getSubSectionList($db,"dataSubsection",$strDataSubsection, $strEmptyOption, "", "style=\"width:$intDefaultWidthPx\"");
    $strInputBranch = getBranchList($db,"dataBranch",$strDataBranch, $strEmptyOption, "", "style=\"width:$intDefaultWidthPx\" ".$ARRAY_DISABLE_GROUP['branch']);
    // $strInputActive = getEmployeeActiveList("dataActive", $strDataActive, $strEmptyOption2, "style=\"width:$intDefaultWidthPx\"");
    $strInputLoanType = getLoanTypeList($db, "dataLoanType", $strDataLoanType, $strEmptyOption,"", "style=\"width:$intDefaultWidthPx\"");
    $strInputStatus = getComboFromArray($ARRAY_REQUEST_STATUS, "dataStatus", ($strDataStatus = getInitialValue("Status")), $strEmptyOption, "style=width:$strDefaultWidthPx");
    $strInputLoanCategory = "<select name = \"dataLoanCategory\" id = \"dataLoanCategory\">";
    if ($bolCanViewFA && $bolCanViewHRD && $bolCanViewTLS)
    {
      $value = -1;
      if ($value == $strDataLoanCategory) $selected = "selected"; else $selected = "";
      $strInputLoanCategory .= "<option value= $value $selected> All Loan Category </option>";
    }
    if ($bolCanViewHRD)
    {
      $value = 0;
      if ($value == $strDataLoanCategory) $selected = "selected"; else $selected = "";
      $strInputLoanCategory .= "<option value= $value $selected> HRD Loan </option>";
    }
    if ($bolCanViewTLS)
    {
      $value = 1;
      if ($value == $strDataLoanCategory) $selected = "selected"; else $selected = "";
      $strInputLoanCategory .= "<option value= $value $selected> Tools Loan </option>";
    }
    if ($bolCanViewFA)
    {
      $value = 2;
      if ($value == $strDataLoanCategory) $selected = "selected"; else $selected = "";
      $strInputLoanCategory .= "<option value= $value $selected> Finance Loan </option>";
    }
    $strInputLoanCategory .= "</select>";
    //handle user company-access-right
    $strInputCompany = getCompanyList($db, "dataCompany",$strDataCompany, $strEmptyOption2, $strKriteria2, "style=\"width:$intDefaultWidthPx\"");

    // informasi tanggal kehadiran
    if ($strDataDateFrom == $strDataDateThru) {
      $strInfo .= "<br>".strtoupper(pgDateFormat($strDataDateFrom, "d-M-Y"));
    } else {
      $strInfo .= "<br>".strtoupper(pgDateFormat($strDataDateFrom, "d-M-Y"));
      $strInfo .= " >> ".strtoupper(pgDateFormat($strDataDateThru, "d-M-Y"));
    }

    $strHidden .= "<input type=hidden name=dataDateFrom value=\"$strDataDateFrom\">";
    $strHidden .= "<input type=hidden name=dataDateThru value=\"$strDataDateThru\">";
    $strHidden .= "<input type=hidden name=dataDivision value=\"$strDataDivision\">";
    $strHidden .= "<input type=hidden name=dataDepartment value=\"$strDataDepartment\">";
    $strHidden .= "<input type=hidden name=dataSection value=\"$strDataSection\">";
    $strHidden .= "<input type=hidden name=dataSubsection value=\"$strDataSubsection\">";
    $strHidden .= "<input type=hidden name=dataBranch value=\"$strDataBranch\">";
    $strHidden .= "<input type=hidden name=dataActive value=\"$strDataActive\">";
    $strHidden .= "<input type=hidden name=dataEmployee value=\"$strDataEmployee\">";
    $strHidden .= "<input type=hidden name=dataLoanType value=\"$strDataLoanType\">";
    $strHidden .= "<input type=hidden name=dataLoanCategory value=\"$strDataLoanCategory\">";
    $strHidden .= "<input type=hidden name=dataStatus value=$strDataStatus>";
  }

  $strButtonList = generateRoleButtons($bolCanEdit, $bolCanDelete, $bolCanCheck, $bolCanApprove, $bolCanApprove2);


  $tbsPage = new clsTinyButStrong ;

  //write this variable in every page
  $strPageTitle = getWords($dataPrivilege['menu_name']);
  $pageIcon = "../images/icons/".$dataPrivilege['icon_file'];
  if ($bolPrint)
    $strMainTemplate = getTemplate(str_replace(".php", "_print.html", basename($_SERVER['PHP_SELF'])));
  else
    $strTemplateFile = getTemplate(str_replace(".php", ".html", basename($_SERVER['PHP_SELF'])));    //------------------------------------------------
  //Load Master Template
  $tbsPage->LoadTemplate($strMainTemplate) ;
  $tbsPage->Show() ;
?>
