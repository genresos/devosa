<?php
  include_once('../global/session.php');
  include_once('global.php');
  include_once('form_object.php');
  include_once('../global/employee_function.php');
  include_once '../global/email_func.php';
  include_once("../includes/krumo/class.krumo.php");
	$dataPrivilege = getDataPrivileges(basename($_SERVER['PHP_SELF']), $bolCanView, $bolCanEdit, $bolCanDelete, $bolCanApprove, $bolCanCheck, $bolCanAcknowledge, $bolCanApprove2);
  if (!$bolCanView) die(accessDenied($_SERVER['HTTP_REFERER']));

  $bolPrint = (isset($_REQUEST['btnPrint']) || isset($_REQUEST['btnPrintStatus']) || isset($_REQUEST['btnPrintDepartment']) || isset($_REQUEST['btnPrintPosition']) || isset($_REQUEST['btnPrintSalary']) || isset($_REQUEST['btnExcel']));

  //---- INISIALISASI ----------------------------------------------------
  $strWordsDateFrom               = getWords ("date from");
  $strWordsDateThru               = getWords ("date thru");
  $strWordsEmployeeID             = getwords ("n i k");
  // $strWordsIDEmployee          = getWords ("ID");
  $strWordsCompany                = getWords ("company");
  $strWordsDivision               = getWords ("division");
  $strWordsDepartment             = getWords ("department");
  $strWordsSection                = getWords ("section");
  $strWordsSubSection             = getWords ("sub section");
  $strWordsListOf                 = getWords ("list of");
  $strWordsProposalDate           = getWords ("proposal date");
  $strWordsLetterCode             = getWords ("letter code");
  $strWordsName                   = getWords ("name");
  $strWordsGender                 = getWords ("sex");
  $strWordsStatusConfirmation     = getWords ("status confirmation");
  $strWordsStatusChanges          = getWords ("status changes");
  $strWordsGradeChanges           = getWords ("grade changes");
  $strWordsDepartmentChanges      = getWords ("department changes");
  $strWordsPositionChanges        = getWords ("position changes");
  $strWordsNIKChanges             = getWords ("n i k changes");
  $strWordsBranchChanges          = getWords("branch changes");
  $strWordsCostCenterChanges      = getWords("cost center changes");
  $strWordsNote                   = getWords ("note");
  $strWordsStatus                 = getWords ("status");
  $strWordsStartDate              = getWords ("start date");
  $strWordsPositionOld            = getWords ("old position");
  $strWordsPositionNew            = getWords ("new position");
  $strWordsGradeOld               = getWords ("old grade");
  $strWordsGradeNew               = getWords ("new grade");
  $strWordsFunctionalOld          = getWords ("old functional");
  $strWordsFunctionalNew          = getWords ("new functional");
  $strWordsOldNIK                 = getWords ("old nik");
  $strWordsNewNIK                 = getWords ("new nik");
  $strWordsOldDepartment          = getWords ("old department");
  $strWordsNewDepartment          = getWords ("new department");
  $strWordsNewBasicSalary         = getWords ("new basic salary");
  $strWordsOldBasicSalary         = getWords ("old basic salary");
  $strWordsNewPositionAllow       = getWords ("new position allowance");
  $strWordsOldPositionAlow        = getWords ("old position allowance");
  $strWordsNewMealAllow           = getWords ("new meal allowance");
  $strWordsOldMealAllow           = getWords ("old meal allowance");
  $strWordsNewTransportAllow      = getWords ("new transport allowance");
  $strWordsOldTransportAllow      = getWords ("old transport allowance");
  $strWordsOldCompany             = getWords ("old company");
  $strWordsNewCompany             = getWords ("new company");
  $strWordsOldDivision            = getWords ("old division");
  $strWordsNewDivision            = getWords ("new division");
  $strWordsOldSection             = getWords ("old section");
  $strWordsNewSection             = getWords ("new section");
  $strWordsOldSubSection          = getWords ("old sub section");
  $strWordsNewSubSection          = getWords ("new sub section");
  $strWordsNewVehicleAllow        = getWords ("new vehicle allowance");
  $strWordsOldVehicleAllow        = getWords ("old vehicle allowance");
  $strWordsSalaryChanges          = getWords ("salary changes");
  $strWordsRequestStatus          = getWords ("request status");
  $strWordsProposalEntry          = getWords("proposal entry");
  $strWordsProposalList           = getWords("proposal list");
  $strWordsShowData               = getWords("show data");
  $strWordsExcel                  = getWords("export excel");
  $strWordsPrint                  = getWords("print");
  $strWordsPrintStatusChanges     = getWords("print status changes");
  $strWordsPrintGradeChanges      = getWords("print grade changes");
  $strWordsPrintDepartmentChanges = getWords("print department changes");
  $strWordsIdSalarySet            = getWords("salary set");
  $strWordsSalaryChanges2            = getWords("detail salary change");
  $strWordsBranchContractOld      = getWords("branch contract from");
  $strWordsBranchPenugasanOld     = getWords("branch penugasan from");
  $strWordsBranchContractNew      = getWords("branch contract to");
  $strWordsBranchPenugasanNew     = getWords("branch penugasan to");
  $strWordsBranchNewDate          = getWords("branch new date");
  $strWordsCostCenterChanges      = getWords("cost center changes");
  $strWordsCostCenterOld          = getWords("cost center from");
  $strWordsCostCenterNew          = getWords("cost center to");
  $strWordsCostCenterNewDate      = getWords("cost center new date");
	$strWordsChange									= getWords("Changes");
  $strHeader1    = "";
  $strHeader2    = "";
  $strDataDetail = "";
  $strHidden     = "";
  $intTotalData  = 0;
  $strButtons    = "";
  $strStyle      = "";
  $strHeaderEdit = "";
  $strWordsNew       = getWords("new");
  $strWordsDenied    = getWords("denied");
  $strWordsChecked   = getWords("checked");
  $strWordsApproved  = getWords("approved");
  $strWordsApproved2 = getWords("approved 2");
  if ($bolPrint)
  {
    $strDisplayAll = $strDisplayStatus = $strDisplayPosition = $strDisplayDepartment = $strDisplaySalary = "style=\"display:none\" ";
  }
  //----------------------------------------------------------------------

  //--- DAFTAR FUNSI------------------------------------------------------
  // fungsi untuk menampilkan data
  // $db = kelas database, $intRows = jumlah baris (return)
  // $strKriteria = query kriteria, $strOrder = query ORder by


  function getHeader(&$strHeader1, &$strHeader2, &$header1 = null, &$header2 = null)
  {
    global $strShowStatus;
    global $strShowPosition;
    global $strShowDepartment;
    global $strShowNIK;
    global $strShowSalary;
    global $strShowBranch;
    global $strShowCostCenter;
    global $strWordsStatusChanges;
    global $strWordsStatus;
    global $strWordsDateFrom;
    global $strWordsDateThru;
    global $strWordsIdSalarySet;
    global $strWordsDepartmentChanges;
    global $strWordsOldDepartment;
    global $strWordsNewDepartment;
    global $strWordsStartDate;
    global $strWordsNIKChanges;
    global $strWordsOldNIK;
    global $strWordsNewNIK;
    global $strWordsPositionChanges;
    global $strWordsPositionOld;
    global $strWordsGradeOld;
    global $strWordsFunctionalOld;
    global $strWordsPositionNew;
    global $strWordsGradeNew;
    global $strWordsFunctionalNew;
    global $strWordsSalaryChanges;
    global $strWordsSalaryChanges2;
    global $strWordsOldCompany;
    global $strWordsNewCompany;
    global $strWordsOldDivision;
    global $strWordsNewDivision;
    global $strWordsOldSection;
    global $strWordsNewSection;
    global $strWordsOldSubSection;
    global $strWordsNewSubSection;
    global $strWordsOldBasicSalary, $strWordsNewBasicSalary;
    global $strWordsOldPositionAlow, $strWordsNewPositionAllow;
    global $strWordsOldMealAllow, $strWordsNewMealAllow;
    global $strWordsOldTransportAllow, $strWordsNewTransportAllow;
    global $strWordsOldVehicleAllow, $strWordsNewVehicleAllow;
    global $strWordsBranchContractOld;
    global $strWordsBranchPenugasanOld;
    global $strWordsBranchContractNew;
    global $strWordsBranchPenugasanNew;
    global $strWordsBranchChanges;
    global $strWordsBranchNewDate;
    global $strWordsCostCenterChanges;
    global $strWordsCostCenterOld;
    global $strWordsCostCenterNew;
    global $strWordsCostCenterNewDate;
		global $strWordsProposalDate;
		global $strWordsLetterCode;
		global $strWordsEmployeeID;
		global $strWordsName;
		global $strWordsChange;
		global $strWordsNote;
		global $strWordsStatus;
    $strHeader1 = "";
    $strHeader2 = "<tr>";
    if (!empty($strShowStatus))
    {
      $strHeader1 .= "<td colspan=3 class=tableHeader>$strWordsStatusChanges</td>";
      $header1[] = array('value' => $strWordsProposalDate, 'rowspan' => 2);
      $header1[] = array('value' => $strWordsLetterCode, 'rowspan' => 2);
      $header1[] = array('value' => $strWordsEmployeeID, 'rowspan' => 2);
      $header1[] = array('value' => $strWordsName, 'rowspan' => 2);
      $header1[] = array('value' => $strWordsChange, 'rowspan' => 2);
      $header2[] = array('value' => null, 'align' => 'center');
      $header2[] = array('value' => null, 'align' => 'center');
      $header2[] = array('value' => null, 'align' => 'center');
      $header2[] = array('value' => null);
      $header2[] = array('value' => null);
      $header1[] = array('value' => $strWordsStatusChanges, 'colspan' => 3);
      $header2[] = array('value' => $strWordsStatus);
      $header2[] = array('value' => $strWordsDateFrom, 'align' => 'center');
      $header2[] = array('value' => $strWordsDateThru, 'align' => 'center');

      $strHeader2 .= "
            <td nowrap class=\"tableHeader\">$strWordsStatus</td>
            <td nowrap class=\"tableHeader\">$strWordsDateFrom</td>
            <td nowrap class=\"tableHeader\">$strWordsDateThru</td>
            ";
    }
    if (!empty($strShowPosition))
    {
      $strHeader1 .= "<td colspan=7 class=tableHeader>$strWordsPositionChanges</td>";
      $header1[] = array('value' => $strWordsPositionChanges, 'colspan' => 7);
      $header2[] = array('value' => $strWordsPositionOld);
      $header2[] = array('value' => $strWordsPositionNew);
      $header2[] = array('value' => $strWordsGradeOld);
      $header2[] = array('value' => $strWordsGradeNew);
      $header2[] = array('value' => $strWordsFunctionalOld);
      $header2[] = array('value' => $strWordsFunctionalNew);
      $header2[] = array('value' => $strWordsStartDate, 'align' => 'center');
      $strHeader2 .= "
            <td nowrap class=\"tableHeader\">$strWordsPositionOld</td>
            <td nowrap class=\"tableHeader\">$strWordsPositionNew</td>
            <td nowrap class=\"tableHeader\">$strWordsGradeOld</td>
            <td nowrap class=\"tableHeader\">$strWordsGradeNew</td>
            <td nowrap class=\"tableHeader\">$strWordsFunctionalOld</td>
            <td nowrap class=\"tableHeader\">$strWordsFunctionalNew</td>
            <td nowrap class=\"tableHeader\">$strWordsStartDate</td>
            ";
    }
    if (!empty($strShowDepartment))
    {
      $strHeader1 .= "<td colspan=11 class=tableHeader>$strWordsDepartmentChanges</td>";
      $header1[] = array('value' => $strWordsDepartmentChanges, 'colspan' => 11);
      $header2[] = array('value' => $strWordsOldCompany);
      $header2[] = array('value' => $strWordsNewCompany);
      $header2[] = array('value' => $strWordsOldDepartment);
      $header2[] = array('value' => $strWordsNewDepartment);
      $header2[] = array('value' => $strWordsOldDivision);
      $header2[] = array('value' => $strWordsNewDivision);
      $header2[] = array('value' => $strWordsOldSection);
      $header2[] = array('value' => $strWordsNewSection);
      $header2[] = array('value' => $strWordsOldSubSection);
      $header2[] = array('value' => $strWordsNewSubSection);
      $header2[] = array('value' => $strWordsStartDate, 'align' => 'center');
      $strHeader2 .= "
            <td nowrap class=\"tableHeader\">$strWordsOldCompany</td>
            <td nowrap class=\"tableHeader\">$strWordsNewCompany</td>
            <td nowrap class=\"tableHeader\">$strWordsOldDepartment</td>
            <td nowrap class=\"tableHeader\">$strWordsNewDepartment</td>
            <td nowrap class=\"tableHeader\">$strWordsOldDivision</td>
            <td nowrap class=\"tableHeader\">$strWordsNewDivision</td>
            <td nowrap class=\"tableHeader\">$strWordsOldSection</td>
            <td nowrap class=\"tableHeader\">$strWordsNewSection</td>
            <td nowrap class=\"tableHeader\">$strWordsOldSubSection</td>
            <td nowrap class=\"tableHeader\">$strWordsNewSubSection</td>
            <td nowrap class=\"tableHeader\">$strWordsStartDate</td>
            ";
    }
    if (!empty($strShowNIK))
    {
      $strHeader1 .= "<td colspan=2 class=tableHeader>$strWordsNIKChanges</td>";
      $header1[] = array('value' => $strWordsNIKChanges, 'colspan' => 2);
      $header2[] = array('value' => $strWordsOldNIK, 'align' => 'center');
      $header2[] = array('value' => $strWordsNewNIK, 'align' => 'center');
      $strHeader2 .= "
            <td nowrap class=\"tableHeader\">$strWordsOldNIK</td>
            <td nowrap class=\"tableHeader\">$strWordsNewNIK</td>
            ";
    }
    if (!empty($strShowBranch))
    {
       $strHeader1 .= "<td colspan=5 class=tableHeader>$strWordsBranchChanges</td>";
       $header1[] = array('value' => $strWordsBranchChanges, 'colspan' => 5);
       $header2[] = array('value' => 'Old Branch Office');
       $header2[] = array('value' => 'Old Branch Contract');
       $header2[] = array('value' => 'New Branch Office');
       $header2[] = array('value' => 'New Branch Contract');
       $header2[] = array('value' => $strWordsBranchNewDate, 'align' => 'center');
       $strHeader2 .= "
            <td nowrap class=\"tableHeader\">Old Branch Office</td>
            <td nowrap class=\"tableHeader\">Old Branch Contract</td>
            <td nowrap class=\"tableHeader\">New Branch Office</td>
            <td nowrap class=\"tableHeader\">New Branch Contract</td>
            <td nowrap class=\"tableHeader\">$strWordsBranchNewDate</td>";
    }
    if (!empty($strShowCostCenter)){
       $strHeader1 .= "<td colspan=3 class=tableHeader>$strWordsCostCenterChanges</td>";
       $header1[] = array('value' => $strWordsCostCenterChanges, 'colspan' => 3);
       $header2[] = array('value' => 'Old Cost Center');
       $header2[] = array('value' => 'New Cost Center');
       $header2[] = array('value' => $strWordsCostCenterNewDate, 'align' => 'center');
       $strHeader2 .= "
            <td nowrap class=\"tableHeader\">Old Cost Center</td>
            <td nowrap class=\"tableHeader\">New Cost Center</td>
            <td nowrap class=\"tableHeader\">$strWordsCostCenterNewDate</td>";
    }
    if (!empty($strShowSalary)){
    	$arrAllowance = getActiveAllowanceType();
    	$strHeader1 .= "<td colspan='5' class='tableHeader'>$strWordsSalaryChanges</td>";
      $strHeader2 .= "<td nowrap class=\"tableHeader\">$strWordsIdSalarySet</td>";
      $strHeader2 .= "<td nowrap class=\"tableHeader\" width='250'>Allowance Name</td>";
      $strHeader2 .= "<td nowrap class=\"tableHeader\">Old Amount</td>";
      $strHeader2 .= "<td nowrap class=\"tableHeader\">New Amount</td>";
      $strHeader2 .= "<td nowrap class=\"tableHeader\">$strWordsStartDate</td>";
      $header1[] = array('value' => $strWordsSalaryChanges, 'colspan' => 5);
      $header2[] = array('value' => $strWordsIdSalarySet, 'wraptext' => true, 'align' => 'left');
      $header2[] = array('value' => 'Allowance Name', 'wraptext' => true, 'align' => 'left');
      $header2[] = array('value' => 'Old Amount', 'wraptext' => true, 'align' => 'right');
      $header2[] = array('value' => 'New Amount', 'wraptext' => true, 'align' => 'right');
      $header2[] = array('value' => $strWordsStartDate, 'wraptext' => true, 'align' => 'center');
    }
    $header1[] = array('value' => $strWordsNote, 'rowspan' => 2);
    $header1[] = array('value' => $strWordsStatus, 'rowspan' => 2);
    $header1[] = array('value' => 'Created Time', 'rowspan' => 2);
    $header1[] = array('value' => 'Created By', 'rowspan' => 2);
    $header2[] = array('value' => null);
    $header2[] = array('value' => null, 'align' => 'center');
    $header2[] = array('value' => null, 'align' => 'center');
    $header2[] = array('value' => null, 'align' => 'center');
    $strHeader2 .= "</tr>";
  }
  function getData($db, $strDataDateFrom, $strDataDateThru, &$intRows, $strKriteria = "", $strOrder = "") {
    global $words;
    global $ARRAY_EMPLOYEE_STATUS;
    global $ARRAY_REQUEST_STATUS;
    global $bolPrint;
    global $strShowStatus;
    global $strShowPosition;
    global $strShowDepartment;
    global $strShowNIK;
    global $strShowBranch;
    global $strShowCostCenter;
    global $strShowSalary;
    global $strHeaderEdit;
    global $arrayData;
    global $objectName;
    global $_REQUEST;
    
    $intRows = 0;
    $strResult = "";
		
		$objectName[] = 'proposal_date';
    $objectName[] = 'letter_code';
    $objectName[] = 'employee_id';
    $objectName[] = 'employee_name';
    $objectName[] = 'change_desc';
    $objectName[] = 'str_status';
    $objectName[] = 'status_date_from';
    $objectName[] = 'status_date_thru';
		$objectName[] = 'position_old';
    $objectName[] = 'position_new';
    $objectName[] = 'grade_old';
    $objectName[] = 'grade_new';
    $objectName[] = 'functional_old';
    $objectName[] = 'functional_new';
    $objectName[] = 'position_new_date';
    $objectName[] = 'company_old';
    $objectName[] = 'company_new';
    $objectName[] = 'department_old';
    $objectName[] = 'department_new';
    $objectName[] = 'division_old';
    $objectName[] = 'division_new';
    $objectName[] = 'section_old';
    $objectName[] = 'section_new';
    $objectName[] = 'sub_section_old';
    $objectName[] = 'sub_section_new';
    $objectName[] = 'department_new_date';
    $objectName[] = 'id_employee_old';
    $objectName[] = 'id_employee_new';
    $objectName[] = 'branch_contract_old';
    $objectName[] = 'branch_penugasan_old';
    $objectName[] = 'branch_contract_new';
    $objectName[] = 'branch_penugasan_new';
    $objectName[] = 'branch_new_date';
    $objectName[] = 'cost_center_old';
		$objectName[] = 'cost_center_new';
		$objectName[] = 'cost_center_new_date';
		$objectName[] = 'salary_set';
  	$objectName[] = 'allowance_name';
  	$objectName[] = 'allowance_old_value';
  	$objectName[] = 'allowance_new_value';
  	$objectName[] = 'salary_new_date';
  	$objectName[] = 'note';
    $objectName[] = 'status';
    $objectName[] = 'created';
    $objectName[] = 'created_by';
    // ambil dulu data employee, kumpulkan dalam array

    $i = 0;
    $strSQL  = "SELECT t1.*, t2.employee_id, t2.employee_name, t2.gender,t2.id as id_employee ";
    $strSQL .= "FROM hrd_employee_mutation AS t1 LEFT JOIN hrd_employee AS t2 ON t1.id_employee = t2.id ";
    $strSQL .= "WHERE ";
    $strSQL .= "1=1 AND t1.type != 1 AND t1.id NOT IN (SELECT id_mutation FROM hrd_employee_mutation_resign) $strKriteria";
  //$strSQL .="proposal_date BETWEEN '$strDataDateFrom' AND '$strDataDateThru'";
    $strSQL .= "ORDER BY $strOrder t1.proposal_date, t2.employee_id ";
    $resDb = $db->execute($strSQL);
    $strDateOld = "";
    while ($rowDb = $db->fetchrow($resDb)) {
      $intRows++;
			$detailData = new stdClass();
      /*
      if ($rowDb['status'] == '0') {
        $strClass = "bgNewRevised";
      } else if ($rowDb['status'] == '3') {
        $strClass = "bgDenied";
      } else {
        $strClass = "";
      }
      */
      $strClass = getCssClass($rowDb['status']);
			
      $strEmployeeInfo = $rowDb['employee_id'] ." - ".$rowDb['employee_name'];

      $strResult .= "<tr valign=top title=\"$strEmployeeInfo\" class=$strClass>\n";
      if (!$bolPrint || isset($_REQUEST['btnExcel']))
        $strResult .= "  <td><input type=checkbox name='chkID$intRows' value=\"" .$rowDb['id']. "\"></td>\n";
      $detailData->proposal_date = pgDateFormat($rowDb['proposal_date'], "d-M-Y");
      $strResult .= "  <td align=center>" .pgDateFormat($rowDb['proposal_date'], "d-M-Y"). "&nbsp;</td>\n";
      $detailData->letter_code = $rowDb['letter_code'];
      $strResult .= "  <td>" .$rowDb['letter_code']. "&nbsp;</td>";
      $detailData->employee_id = $rowDb['employee_id'];
      $strResult .= "  <td><input type=hidden name='empID$intRows' value=\"".$rowDb['id_employee']."\">" .$rowDb['employee_id']. "&nbsp;</td>";
      $detailData->employee_name = $rowDb['employee_name'];
      $strResult .= "  <td>" .$rowDb['employee_name']. "&nbsp;</td>";
      $arrChanges = getPlacementChange($db,$rowDb['id']);
      $changeDesc = createPlacementChangeDesc($arrChanges);
      $detailData->change_desc = implode(', ',$changeDesc);
      $strResult .= "  <td>".implode(', ',$changeDesc)."&nbsp;</td>";
      //$strResult .= "  <td align=center>" .$strGender. "&nbsp;</td>";
			
      if (!empty($strShowStatus))
      {
        // status confirmation
        $strSQL  = "SELECT * FROM hrd_employee_mutation_status WHERE id_mutation = '" .$rowDb['id']."' ";
        $resTmp = $db->execute($strSQL);
        if ($rowTmp = $db->fetchrow($resTmp)) {
          $strStatus = ($rowTmp['status_new'] != "99") ? getWords($ARRAY_EMPLOYEE_STATUS[$rowTmp['status_new']]) : getWords("resigned");
          $strResult .= "  <td>" .$strStatus. "&nbsp;</td>";
          $strResult .= "  <td align=center>" .pgDateFormat($rowTmp['status_date_from'], "d-M-y"). "&nbsp;</td>";
          $strResult .= "  <td align=center>" .pgDateFormat($rowTmp['status_date_thru'], "d-M-y"). "&nbsp;</td>";
          $detailData->str_status = $strStatus;
          $detailData->status_date_from = pgDateFormat($rowTmp['status_date_from'], "d-M-y");
          $detailData->status_date_thru = pgDateFormat($rowTmp['status_date_thru'], "d-M-y");
        } else {
          $strResult .= "  <td>&nbsp;</td>";
          $strResult .= "  <td>&nbsp;</td>";
          $strResult .= "  <td>&nbsp;</td>";
          $detailData->str_status = '';
          $detailData->status_date_from = '';
          $detailData->status_date_thru = '';
        }
      }
      
      if(!empty($strShowPosition))
      {
        // position changes
        $strSQL  = "SELECT * FROM hrd_employee_mutation_position WHERE id_mutation = '" .$rowDb['id']."' ";
        $resTmp = $db->execute($strSQL);
        if ($rowTmp = $db->fetchrow($resTmp)) {
          $strResult .= "  <td align=center>" .$rowTmp['position_old']. "&nbsp;</td>";
          $strResult .= "  <td align=center>" .$rowTmp['position_new']. "&nbsp;</td>";
          $strResult .= "  <td align=center>" .$rowTmp['grade_old']. "&nbsp;</td>";
          $strResult .= "  <td align=center>" .$rowTmp['grade_new']. "&nbsp;</td>";
          $strResult .= "  <td align=center>" .$rowTmp['functional_old']. "&nbsp;</td>";
          $strResult .= "  <td align=center>" .$rowTmp['functional_new']. "&nbsp;</td>";
          $strResult .= "  <td align=center>" .pgDateFormat($rowTmp['position_new_date'], "d-M-y"). "&nbsp;</td>";
          $detailData->position_old = $rowTmp['position_old'];
          $detailData->position_new = $rowTmp['position_new'];
          $detailData->grade_old = $rowTmp['grade_old'];
          $detailData->grade_new = $rowTmp['grade_new'];
          $detailData->functional_old = $rowTmp['functional_old'];
          $detailData->functional_new = $rowTmp['functional_new'];
          $detailData->position_new_date = pgDateFormat($rowTmp['position_new_date'], "d-M-y");
        } else {
          $strResult .= "  <td>&nbsp;</td>";
          $strResult .= "  <td>&nbsp;</td>";
          $strResult .= "  <td>&nbsp;</td>";
          $strResult .= "  <td>&nbsp;</td>";
          $strResult .= "  <td>&nbsp;</td>";
          $strResult .= "  <td>&nbsp;</td>";
          $strResult .= "  <td>&nbsp;</td>";
          $detailData->position_old = '';
          $detailData->position_new = '';
          $detailData->grade_old = '';
          $detailData->grade_new = '';
          $detailData->functional_old = '';
          $detailData->functional_new = '';
          $detailData->position_new_date = '';
        }
      }

      if(!empty($strShowDepartment))
      {
        // department mutation
        $strSQL  = "SELECT * FROM hrd_employee_mutation_department WHERE id_mutation = '" .$rowDb['id']."' ";
        $resTmp = $db->execute($strSQL);
        if ($rowTmp = $db->fetchrow($resTmp)) {
          $strResult .= "  <td>" .printCompanyCode($rowTmp['company_old']). "&nbsp;</td>";
          $strResult .= "  <td>" .printCompanyCode($rowTmp['company_new']). "&nbsp;</td>";
          $strResult .= "  <td>" .getDepartmentName($rowTmp['department_old']). "&nbsp;</td>";
          $strResult .= "  <td>" .getDepartmentName($rowTmp['department_new']). "&nbsp;</td>";
          $strResult .= "  <td>" .getDivisionName($rowTmp['division_old']). "&nbsp;</td>";
          $strResult .= "  <td>" .getDivisionName($rowTmp['division_new']). "&nbsp;</td>";
          $strResult .= "  <td>" .getSectionName($rowTmp['section_old']). "&nbsp;</td>";
          $strResult .= "  <td>" .getSectionName($rowTmp['section_new']). "&nbsp;</td>";
          $strResult .= "  <td>" .getSubSectionName($rowTmp['sub_section_old']). "&nbsp;</td>";
          $strResult .= "  <td>" .getSubSectionName($rowTmp['sub_section_new']). "&nbsp;</td>";
          $strResult .= "  <td align=center>" .pgDateFormat($rowTmp['department_new_date'], "d-M-y"). "&nbsp;</td>";
          $detailData->company_old = printCompanyCode($rowTmp['company_old']);
          $detailData->company_new = printCompanyCode($rowTmp['company_new']);
          $detailData->department_old = getDepartmentName($rowTmp['department_old']);
          $detailData->department_new = getDepartmentName($rowTmp['department_new']);
          $detailData->division_old = getDivisionName($rowTmp['division_old']);
          $detailData->division_new = getDivisionName($rowTmp['division_new']);
          $detailData->section_old = getSectionName($rowTmp['section_old']);
          $detailData->section_new = getSectionName($rowTmp['section_new']);
          $detailData->sub_section_old = getSubSectionName($rowTmp['sub_section_old']);
          $detailData->sub_section_new = getSubSectionName($rowTmp['sub_section_new']);
          $detailData->department_new_date = pgDateFormat($rowTmp['department_new_date'], "d-M-y");
        } else {
          $strResult .= "  <td>&nbsp;</td>";
          $strResult .= "  <td>&nbsp;</td>";
          $strResult .= "  <td>&nbsp;</td>";
          $strResult .= "  <td>&nbsp;</td>";
          $strResult .= "  <td>&nbsp;</td>";
          $strResult .= "  <td>&nbsp;</td>";
          $strResult .= "  <td>&nbsp;</td>";
          $strResult .= "  <td>&nbsp;</td>";
          $strResult .= "  <td>&nbsp;</td>";
          $strResult .= "  <td>&nbsp;</td>";
          $strResult .= "  <td>&nbsp;</td>";
          $detailData->company_old = '';
          $detailData->company_new = '';
          $detailData->department_old = '';
          $detailData->department_new = '';
          $detailData->division_old = '';
          $detailData->division_new = '';
          $detailData->section_old = '';
          $detailData->section_new = '';
          $detailData->sub_section_old = '';
          $detailData->sub_section_new = '';
          $detailData->department_new_date = '';
        }
      }
        if(!empty($strShowNIK))
      {
        // ID change
        $strSQL  = "SELECT * FROM hrd_employee_mutation_id WHERE id_mutation = '" .$rowDb['id']."' ";
        $resTmp = $db->execute($strSQL);
        if ($rowTmp = $db->fetchrow($resTmp)) {
          $strResult .= "  <td>" .$rowTmp['id_employee_old']. "&nbsp;</td>";
          $strResult .= "  <td>" .$rowTmp['id_employee_new']. "&nbsp;</td>";
          $detailData->id_employee_old = $rowTmp['id_employee_old'];
          $detailData->id_employee_new = $rowTmp['id_employee_new'];
        } else {
          $strResult .= "  <td>&nbsp;</td>";
          $strResult .= "  <td>&nbsp;</td>";
          $detailData->id_employee_old = '';
          $detailData->id_employee_new = '';
        }
      }

      if(!empty($strShowBranch)){
	      // Branch Change
	      $strSQL  = "SELECT * FROM hrd_employee_mutation_branch WHERE id_mutation = '" .$rowDb['id']."' ";
	      $resTmp = $db->execute($strSQL);
	      if ($rowTmp = $db->fetchrow($resTmp)) {
	      	$strResult .= "  <td>" .$rowTmp['branch_contract_old']. "&nbsp;</td>";
	        $strResult .= "  <td>" .$rowTmp['branch_penugasan_old']. "&nbsp;</td>";
	        $strResult .= "  <td>" .$rowTmp['branch_contract_new']. "&nbsp;</td>";
	        $strResult .= "  <td>" .$rowTmp['branch_penugasan_new']. "&nbsp;</td>";
	        $strResult .= "  <td>" .$rowTmp['branch_new_date']. "&nbsp;</td>";
	        $detailData->branch_contract_old = $rowTmp['branch_contract_old'];
          $detailData->branch_penugasan_old = $rowTmp['branch_penugasan_old'];
          $detailData->branch_contract_new = $rowTmp['branch_contract_new'];
          $detailData->branch_penugasan_new = $rowTmp['branch_penugasan_new'];
          $detailData->branch_new_date = $rowTmp['branch_new_date'];
	      } else {
	      	$strResult .= "  <td>&nbsp;</td>";
	        $strResult .= "  <td>&nbsp;</td>";
	        $strResult .= "  <td>&nbsp;</td>";
	        $strResult .= "  <td>&nbsp;</td>";
	        $strResult .= "  <td>&nbsp;</td>";
	        $detailData->branch_contract_old = '';
          $detailData->branch_penugasan_old = '';
          $detailData->branch_contract_new = '';
          $detailData->branch_penugasan_new = '';
          $detailData->branch_new_date = '';
	      }
    	}
    	if(!empty($strShowCostCenter)){
		    // Cost Center Change
		    $strSQL  = "SELECT * FROM hrd_employee_mutation_cost_center WHERE id_mutation = '" .$rowDb['id']."' ";
		    $resTmp = $db->execute($strSQL);
		    if ($rowTmp = $db->fetchrow($resTmp)) {
		      $strResult .= "  <td>" .$rowTmp['cost_center_old']. "&nbsp;</td>";
		      $strResult .= "  <td>" .$rowTmp['cost_center_new']. "&nbsp;</td>";
		      $strResult .= "  <td>" .$rowTmp['cost_center_new_date']. "&nbsp;</td>";
		      $detailData->cost_center_old = $rowTmp['cost_center_old'];
		      $detailData->cost_center_new = $rowTmp['cost_center_new'];
		      $detailData->cost_center_new_date = $rowTmp['cost_center_new_date'];
		    } else {
		      $strResult .= "  <td>&nbsp;</td>";
		      $strResult .= "  <td>&nbsp;</td>";
		      $strResult .= "  <td>&nbsp;</td>";
		      $detailData->cost_center_old = '';
		      $detailData->cost_center_new = '';
		      $detailData->cost_center_new_date = '';
		    }
		  }

      if (!empty($strShowSalary)){
        // salary increase
        $arrAllowance = getActiveAllowanceType();
    		$strSQL  = "SELECT id_salary_set,salary_new_date FROM hrd_employee_mutation_salary ";
    		$strSQL  .= "WHERE id_mutation = '" .$rowDb['id']."' GROUP BY id_mutation,id_salary_set,salary_new_date";
        $resGroup = $db->execute($strSQL);
        $rowGroup = $db->fetchrow($resGroup);
        if (!isset($rowGroup['id_salary_set'])){
        	$rowGroup['id_salary_set'] = "";
        }
        if (!isset($rowGroup['salary_new_date'])){
        	$rowGroup['salary_new_date'] = "";
        }
        $strSQL  = "SELECT * FROM hrd_employee_mutation_salary WHERE id_mutation = '" .$rowDb['id']."' ";
        $resTmp = $db->execute($strSQL);
        $tableDetailChange = "";
        $i = 0;
        $totalOldAmount = 0;
        $totalNewAmount = 0;
        $detailData->salary_set = "";
  			$detailData->salary_new_date = "";
  			$detailData->allowance_name = "";
  			$detailData->allowance_old_value = "";
  			$detailData->allowance_new_value = "";
        while ($rowTmp = $db->fetchrow($resTmp)){
        	if ($i == 0){
        		$tableDetailChange = "<table cellspacing='0' cellpadding='0' border='0' style='width: 100%;'>";
        		/*$tableDetailChange .= "<thead>";
        		$tableDetailChange .= "<tr>";
        		$tableDetailChange .= "<th width='250'>SALARY & ALLOWANCES</th>";
						$tableDetailChange .= "<th nowrap=''>OLD AMOUNT</th>";
						$tableDetailChange .= "<th nowrap=''>NEW AMOUNT</th>";
						$tableDetailChange .= "</tr>";
						$tableDetailChange .= "</thead>";*/
						$tableDetailChange .= "<tbody>";
        	}
        	if (!(($rowTmp['old_value'] == $rowTmp['new_value']) && $rowTmp['old_value'] == 0)){
	        	$tableDetailChange .= "<tr>";
	        	if (($rowTmp['old_value'] == $rowTmp['new_value'])){
	        		$tableDetailChange .= "<td width='250'>".$arrAllowance[$rowTmp['allowance_type_id']]['name']."</td>";
		        	$tableDetailChange .= "<td style='text-align: right;'>".number_format($rowTmp['old_value'],0,',','.')."&nbsp;</td>";
		        	$tableDetailChange .= "<td style='text-align: right;'>".number_format($rowTmp['new_value'],0,',','.')."&nbsp;</td>";
		        }else{
		        	$tableDetailChange .= "<td width='250'><strong>".$arrAllowance[$rowTmp['allowance_type_id']]['name']."</strong></td>";
		        	$tableDetailChange .= "<td style='text-align: right;'><strong>".number_format($rowTmp['old_value'],0,',','.')."</strong>&nbsp;</td>";
		        	$tableDetailChange .= "<td style='text-align: right;'><strong>".number_format($rowTmp['new_value'],0,',','.')."</strong>&nbsp;</td>";
		        }
		        if ($i == 0){
      				$detailData->salary_set .= getSalarySetName($rowGroup['id_salary_set']);
      				$detailData->salary_new_date .= pgDateFormat($rowGroup['salary_new_date'], "d-M-y");
      			}else{
      				$detailData->salary_set .= "\n";
      				$detailData->salary_new_date .= "\n";
      			}
      			if ($i == 0){
        			$detailData->allowance_name .= $arrAllowance[$rowTmp['allowance_type_id']]['name'];
        			$detailData->allowance_old_value .= number_format($rowTmp['old_value'],0,'.',',');
        			$detailData->allowance_new_value .= number_format($rowTmp['new_value'],0,'.',',');
        		}else{
        			$detailData->allowance_name .= "\n".$arrAllowance[$rowTmp['allowance_type_id']]['name'];
        			$detailData->allowance_old_value .= "\n".number_format($rowTmp['old_value'],0,'.',',');
        			$detailData->allowance_new_value .= "\n".number_format($rowTmp['new_value'],0,'.',',');
        		}
	        	$tableDetailChange .= "</tr>";
	        	$totalOldAmount += $rowTmp['old_value'];
        		$totalNewAmount += $rowTmp['new_value'];
	        }
        	$i++;
        }
        if (!empty($tableDetailChange)){
        	$tableDetailChange .= "<tr>";
        	$tableDetailChange .= "<td><strong>Total Amount</strong></td>";
        	$tableDetailChange .= "<td style='text-align: right;'><strong>".number_format($totalOldAmount,0,',','.')."</strong>&nbsp;</td>";
        	$tableDetailChange .= "<td style='text-align: right;'><strong>".number_format($totalNewAmount,0,',','.')."</strong>&nbsp;</td>";
        	$tableDetailChange .= "</tr>";
        	$tableDetailChange .= "</tbody>";
        	$tableDetailChange .= "</table>";
        	$detailData->salary_set .= "\n";
      		$detailData->salary_new_date .= "\n";
        	$detailData->allowance_name .= "\nTotal Allowance";
        	$detailData->allowance_old_value .= "\n".number_format($totalOldAmount,0,'.',',');
        	$detailData->allowance_new_value .= "\n".number_format($totalNewAmount,0,'.',',');
        }
        $strResult .= "  <td align=right>" .getSalarySetName($rowGroup['id_salary_set']). "&nbsp;</td>";
        $strResult .= "  <td colspan='3'>".$tableDetailChange."</td>";
        $strResult .= "  <td align=center>" .pgDateFormat($rowGroup['salary_new_date'], "d-M-y"). "&nbsp;</td>";
      }
      $detailData->note = $rowDb['note'];
      $detailData->status = getWords($ARRAY_REQUEST_STATUS[$rowDb['status']]);
      $detailData->created = pgDateFormat($rowDb['created'], "d-m-Y");
      $detailData->created_by = getUserName($db,$rowDb['created_by']);
      $strResult .= "  <td>" .$rowDb['note']. "&nbsp;</td>";
      $strResult .= "  <td align=center>" .getWords($ARRAY_REQUEST_STATUS[$rowDb['status']]). "&nbsp;</td>";
      $strResult .= "  <td>" .pgDateFormat($rowDb['created'], "d-m-Y"). "&nbsp;</td>";
      $strResult .= "  <td>" .getUserName($db,$rowDb['created_by']). "&nbsp;</td>";
      if ((!$bolPrint || isset($_REQUEST['btnExcel'])) && ($rowDb['status'] == REQUEST_STATUS_NEW || $_SESSION['sessionUserRole'] >= ROLE_ADMIN)){
        $strHeaderEdit = "<td rowspan=2 nowrap class=tableHeader>&nbsp;</td>";
        $strResult .= "  <td align=center><a href=\"mutation_edit.php?dataID=" .$rowDb['id']. "\">" .$words['edit']. "</a>&nbsp;</td>";
      }else if (!$bolPrint || isset($_REQUEST['btnExcel'])){
        $strHeaderEdit = "<td rowspan=2 nowrap class=tableHeader>&nbsp;</td>";
        $strResult .= "  <td align=center><a href=\"mutation_denied.php?dataID=" .$rowDb['id']. "\">" .$words['edit']. "</a>&nbsp;</td>";
      }else{
        $strHeaderEdit = "";
      }
      $strResult .= "  <td align=center>".showApprover($rowDb, $intRows)."</td>";
      $strResult .= "</tr>\n";
      $arrayData[] = $detailData;
    }

    if ($intRows > 0) {
      writeLog(ACTIVITY_VIEW, MODULE_PAYROLL,"",0);
    }

    return $strResult;
  } // getData

  // fungsi untuk menampilkan data, tapi hanya perubahan status aja
  function getDataStatus($db, $strDataDateFrom, $strDataDateThru, &$intRows, $strKriteria = "", $strOrder = "") {
    global $words;
    global $ARRAY_EMPLOYEE_STATUS;
    global $ARRAY_REQUEST_STATUS;
    global $bolPrint;

    $intRows = 0;
    $strResult = "";

    // ambil dulu data employee, kumpulkan dalam array

    $i = 0;
    $strSQL  = "SELECT t1.*, t2.employee_id, t2.employee_name, t2.gender, ";
    $strSQL .= "t3.status_new, t3.status_date_from, t3.status_date_thru ";
    $strSQL .= "FROM hrd_employee_mutation_status AS t3 ";
    $strSQL .= "LEFT JOIN hrd_employee_mutation AS t1 ON t3.id_mutation = t1.id ";
    $strSQL .= "LEFT JOIN hrd_employee AS t2 ON t1.id_employee = t2.id ";
    $strSQL .= "WHERE t1.type=0 $strKriteria ";
    //$strSQL .= "AND proposal_date BETWEEN '$strDataDateFrom' AND '$strDataDateThru' ";
    $strSQL .= "ORDER BY $strOrder t1.proposal_date, t2.employee_id ";
    $resDb = $db->execute($strSQL);
    $strDateOld = "";
    while ($rowDb = $db->fetchrow($resDb)) {
      $intRows++;

      //$strGender = ($rowDb['gender'] == 0) ? $words['female'] : $words['male'];
      $strStatus = ($rowDb['status_new'] != "99") ? getWords($ARRAY_EMPLOYEE_STATUS[$rowDb['status_new']]) : getWords("resigned");

      $strClass = getCssClass($rowDb['status']);

      $strEmployeeInfo = $rowDb['employee_id'] ." - ".$rowDb['employee_name'];

      $strResult .= "<tr valign=top title=\"$strEmployeeInfo\" class=$strClass>\n";
      $strResult .= "  <td align=center>" .pgDateFormat($rowDb['proposal_date'], "d-M-Y"). "&nbsp;</td>\n";
      $strResult .= "  <td>" .$rowDb['letter_code']. "&nbsp;</td>\n";
      $strResult .= "  <td>" .$rowDb['employee_id']. "&nbsp;</td>";
      $strResult .= "  <td>" .$rowDb['employee_name']. "&nbsp;</td>";
      //$strResult .= "  <td align=center>" .$strGender. "&nbsp;</td>";
      $strResult .= "  <td>" .$strStatus. "&nbsp;</td>";
      $strResult .= "  <td align=center>" .pgDateFormat($rowDb['status_date_from'], "d-M-y"). "&nbsp;</td>";
      $strResult .= "  <td align=center>" .pgDateFormat($rowDb['status_date_thru'], "d-M-y"). "&nbsp;</td>";


      $strResult .= "  <td>" .$rowDb['note']. "&nbsp;</td>";
      $strResult .= "  <td align=center>" .getWords($ARRAY_REQUEST_STATUS[$rowDb['status']]). "&nbsp;</td>";
      $strResult .= "</tr>\n";
    }

    if ($intRows > 0) {
      writeLog(ACTIVITY_VIEW, MODULE_PAYROLL,"",0);
    }

    return $strResult;
  } // getDataStatus

  function getSalarySetName($value = null)
  {
      global $db;
      $name = "";
      if (!empty($value)){
	      $strSQL = "SELECT start_date FROM hrd_basic_salary_set WHERE id =".$value;
	      $res = $db->execute($strSQL);
	      if($rowDb = $db->fetchrow($res)){
	          $name = $rowDb['start_date'];
	      }
	    }
      return $name;
  }

// fungsi untuk menampilkan data, tapi hanya perubahan jabatan saja
  function getDataPosition($db, $strDataDateFrom, $strDataDateThru, &$intRows, $strKriteria = "", $strOrder = "") {
    global $words;
    global $ARRAY_EMPLOYEE_STATUS;
    global $ARRAY_REQUEST_STATUS;
    global $bolPrint;

    $intRows = 0;
    $strResult = "";

    // ambil dulu data employee, kumpulkan dalam array

    $i = 0;
    $strSQL  = "SELECT t1.*, t2.employee_id, t2.employee_name, t2.gender, ";
    $strSQL .= "t3.*";
    $strSQL .= "FROM hrd_employee_mutation_position AS t3 ";
    $strSQL .= "LEFT JOIN hrd_employee_mutation AS t1 ON t3.id_mutation = t1.id ";
    $strSQL .= "LEFT JOIN hrd_employee AS t2 ON t1.id_employee = t2.id ";
    $strSQL .= "WHERE t1.type=0 $strKriteria ";
    //$strSQL .= "AND proposal_date BETWEEN '$strDataDateFrom' AND '$strDataDateThru' ";
    $strSQL .= "ORDER BY $strOrder t1.proposal_date, t2.employee_id ";
    $resDb = $db->execute($strSQL);
    $strDateOld = "";
    while ($rowDb = $db->fetchrow($resDb)) {
      $intRows++;

      //($rowDb['gender'] == 0) ? $strGender = $words['female'] : $strGender = $words['male'];

      $strClass = getCssClass($rowDb['status']);

      $strEmployeeInfo = $rowDb['employee_id'] ." - ".$rowDb['employee_name'];

      $strResult .= "<tr valign=top title=\"$strEmployeeInfo\" class=$strClass>\n";
      $strResult .= "  <td align=center>" .pgDateFormat($rowDb['proposal_date'], "d-M-Y"). "&nbsp;</td>\n";
      $strResult .= "  <td>" .$rowDb['letter_code']. "&nbsp;</td>";
      $strResult .= "  <td>" .$rowDb['employee_id']. "&nbsp;</td>";
      $strResult .= "  <td>" .$rowDb['employee_name']. "&nbsp;</td>";
      //$strResult .= "  <td align=center>" .$strGender. "&nbsp;</td>";
      $strResult .= "  <td align=center>" .$rowDb['position_old']. "&nbsp;</td>";
      $strResult .= "  <td align=center>" .$rowDb['position_new']. "&nbsp;</td>";
      $strResult .= "  <td align=center>" .$rowDb['grade_old']. "&nbsp;</td>";
      $strResult .= "  <td align=center>" .$rowDb['grade_new']. "&nbsp;</td>";
      $strResult .= "  <td align=center>" .$rowDb['functional_old']. "&nbsp;</td>";
      $strResult .= "  <td align=center>" .$rowDb['functional_new']. "&nbsp;</td>";
      $strResult .= "  <td align=center>" .pgDateFormat($rowDb['position_new_date'], "d-M-y"). "&nbsp;</td>";

      $strResult .= "  <td>" .$rowDb['note']. "&nbsp;</td>";
      $strResult .= "  <td align=center>" .getWords($ARRAY_REQUEST_STATUS[$rowDb['status']]). "&nbsp;</td>";
      $strResult .= "</tr>\n";
    }

    if ($intRows > 0) {
      writeLog(ACTIVITY_VIEW, MODULE_PAYROLL,"",0);
    }

    return $strResult;
  } // getDataPosition

// fungsi untuk menampilkan data, tapi hanya perubahan department aja
  function getDataDepartmentSpecial($db, $strDataDateFrom, $strDataDateThru, &$intRows, $strKriteria = "", $strOrder = "") {
    global $words;
    global $ARRAY_EMPLOYEE_STATUS;
    global $ARRAY_REQUEST_STATUS;
    global $bolPrint;

    $intRows = 0;
    $strResult = "";

    // ambil dulu data employee, kumpulkan dalam array

    $i = 0;
    $strSQL  = "SELECT t1.*, t2.employee_id, t2.employee_name, t2.gender, ";
    $strSQL .= "t3.* ";
    $strSQL .= "FROM hrd_employee_mutation_department AS t3 ";
    $strSQL .= "LEFT JOIN hrd_employee_mutation AS t1 ON t3.id_mutation = t1.id ";
    $strSQL .= "LEFT JOIN hrd_employee AS t2 ON t1.id_employee = t2.id ";
    $strSQL .= "WHERE t1.type=0 $strKriteria ";
    //$strSQL .= "AND proposal_date BETWEEN '$strDataDateFrom' AND '$strDataDateThru' ";
    $strSQL .= "ORDER BY $strOrder t1.proposal_date, t2.employee_id ";
    $resDb = $db->execute($strSQL);
    $strDateOld = "";
    while ($rowDb = $db->fetchrow($resDb)) {
      $intRows++;

      //($rowDb['gender'] == 0) ? $strGender = $words['female'] : $strGender = $words['male'];

      $strClass = getCssClass($rowDb['status']);

      $strEmployeeInfo = $rowDb['employee_id'] ." - ".$rowDb['employee_name'];

      $strResult .= "<tr valign=top title=\"$strEmployeeInfo\" class=$strClass>\n";
      $strResult .= "  <td align=center>" .pgDateFormat($rowDb['proposal_date'], "d-M-Y"). "&nbsp;</td>\n";
      $strResult .= "  <td>" .$rowDb['letter_code']. "&nbsp;</td>";
      $strResult .= "  <td>" .$rowDb['employee_id']. "&nbsp;</td>";
      $strResult .= "  <td>" .$rowDb['employee_name']. "&nbsp;</td>";
      //$strResult .= "  <td align=center>" .$strGender. "&nbsp;</td>";
      $strResult .= "  <td>" .$rowDb['company_old']. "&nbsp;</td>";
      $strResult .= "  <td>" .$rowDb['company_new']. "&nbsp;</td>";
      $strResult .= "  <td>" .$rowDb['department_old']. "&nbsp;</td>";
      $strResult .= "  <td>" .$rowDb['department_new']. "&nbsp;</td>";
      $strResult .= "  <td>" .$rowDb['division_old']. "&nbsp;</td>";
      $strResult .= "  <td>" .$rowDb['division_new']. "&nbsp;</td>";
      $strResult .= "  <td>" .$rowDb['section_old']. "&nbsp;</td>";
      $strResult .= "  <td>" .$rowDb['section_new']. "&nbsp;</td>";
      $strResult .= "  <td>" .$rowDb['sub_section_old']. "&nbsp;</td>";
      $strResult .= "  <td>" .$rowDb['sub_section_new']. "&nbsp;</td>";
      $strResult .= "  <td align=center>" .pgDateFormat($rowDb['department_new_date'], "d-M-y"). "&nbsp;</td>";

      $strResult .= "  <td>" .$rowDb['note']. "&nbsp;</td>";
      $strResult .= "  <td align=center>" .getWords($ARRAY_REQUEST_STATUS[$rowDb['status']]). "&nbsp;</td>";
      $strResult .= "</tr>\n";
    }

    if ($intRows > 0) {
      writeLog(ACTIVITY_VIEW, MODULE_PAYROLL,"",0);
    }

    return $strResult;
  } // getDataDepartment

  /**
   * [[Description]]
   * @param  [[Type]] $db                 [[Description]]
   * @param  [[Type]] $strDataDateFrom    [[Description]]
   * @param  [[Type]] $strDataDateThru    [[Description]]
   * @param  [[Type]] &$intRows           [[Description]]
   * @param  [[Type]] [$strKriteria = ""] [[Description]]
   * @param  [[Type]] [$strOrder = ""]    [[Description]]
   * @return [[Type]] [[Description]]
   */
  function getDataSalary($db, $strDataDateFrom, $strDataDateThru, &$intRows, $strKriteria = "", $strOrder = "") {
    global $words;
    global $ARRAY_EMPLOYEE_STATUS;
    global $ARRAY_REQUEST_STATUS;
    global $bolPrint;

    $intRows = 0;
    $strResult = "";

    // ambil dulu data employee, kumpulkan dalam array
		$arrAllowance = getActiveAllowanceType();
    		
    $i = 0;
    $strSQL  = "SELECT t1.*, t2.employee_id, t2.employee_name, t2.gender, ";
    $strSQL .= "t3.id_salary_set,t3.allowance_type_id,t3.allowance_type_code,t3.old_value, ";
    $strSQL .= "t3.new_value, t3.salary_new_date ";
    $strSQL .= "FROM hrd_employee_mutation_salary AS t3 ";
    $strSQL .= "LEFT JOIN hrd_employee_mutation AS t1 ON t3.id_mutation = t1.id ";
    $strSQL .= "LEFT JOIN hrd_employee AS t2 ON t1.id_employee = t2.id ";
    $strSQL .= "WHERE t1.type=0 $strKriteria ";
    //$strSQL .= "AND proposal_date BETWEEN '$strDataDateFrom' AND '$strDataDateThru' ";
    $strSQL .= "ORDER BY $strOrder t1.proposal_date, t2.employee_id ";
    $resDb = $db->execute($strSQL);
    $strDateOld = "";
    while ($rowDb = $db->fetchrow($resDb)) {
    	if ($rowDb['old_value'] != $rowDb['new_value']){
	      $intRows++;
	      $strClass = getCssClass($rowDb['status']);
	      $strEmployeeInfo = $rowDb['employee_id'] ." - ".$rowDb['employee_name'];
	      $strResult .= "<tr valign=top title=\"$strEmployeeInfo\" class=$strClass>\n";
	      $strResult .= "  <td align=center>" .pgDateFormat($rowDb['proposal_date'], "d-M-Y"). "&nbsp;</td>\n";
	      $strResult .= "  <td>" .$rowDb['letter_code']. "&nbsp;</td>";
	      $strResult .= "  <td>" .$rowDb['employee_id']. "&nbsp;</td>";
	      $strResult .= "  <td>" .$rowDb['employee_name']. "&nbsp;</td>";
	      $strResult .= "  <td align=right>" .getSalarySetName($rowDb['id_salary_set']). "&nbsp;</td>";
	      $tableDetailSalaryChange = "<table cellspacing='0' cellpadding='0' border='0' style='width: 97%;'>";
	      $tableDetailSalaryChange .= "<tbody>";
	      $tableDetailSalaryChange .= "<tr>";
	      $tableDetailSalaryChange .= "<td width='250'>".$arrAllowance[$rowDb['allowance_type_id']]['name']."</td>";
	      $tableDetailSalaryChange .= "<td style='text-align: right;'>".number_format($rowDb['old_value'],0,',','.')."&nbsp;</td>";
	      $tableDetailSalaryChange .= "<td style='text-align: right;'>".number_format($rowDb['new_value'],0,',','.')."&nbsp;</td>";
	      $tableDetailSalaryChange .= "</tr>";
	      $tableDetailSalaryChange .= "</tbody>";
	      $tableDetailSalaryChange .= "</table>";
	      $strResult .= "  <td colspan='3'>" .$tableDetailSalaryChange. "</td>";
				$strResult .= "  <td align=center>" .pgDateFormat($rowDb['salary_new_date'], "d-M-y"). "&nbsp;</td>";
	      $strResult .= "  <td>" .$rowDb['note']. "&nbsp;</td>";
	      $strResult .= "  <td align=center>" .getWords($ARRAY_REQUEST_STATUS[$rowDb['status']]). "&nbsp;</td>";
	      $strResult .= "</tr>\n";
	    }
    }

    if ($intRows > 0) {
      writeLog(ACTIVITY_VIEW, MODULE_PAYROLL,"",0);
    }

    return $strResult;
  } // getDataSalary

  // fungsi untuk menghapus data
  /**
   * [[Description]]
   * @param [[Type]] $db [[Description]]
   */
  function deleteData($db) {
    global $_REQUEST;
    $i = 0;
    foreach ($_REQUEST as $strIndex => $strValue) {
      if (substr($strIndex,0,5) == 'chkID') {
        $i++;
        // cari data id Employee
        $strIDEmployee = "";
        $strSQL   = "SELECT id_employee, status FROM hrd_employee_mutation WHERE id = '$strValue' ";
        $resTmp = $db->execute($strSQL);
        if ($rowTmp = $db->fetchrow($resTmp)) {
          if ($_SESSION['sessionUserRole'] >= ROLE_ADMIN || $rowTmp['status'] < REQUEST_STATUS_APPROVED) {
            $strIDEmployee = $rowTmp['id_employee'];
            $strSQL  = "DELETE FROM hrd_employee_mutation_status WHERE id_mutation = '$strValue'; ";
            $strSQL .= "DELETE FROM hrd_employee_mutation_resign WHERE id_mutation = '$strValue'; ";
            $strSQL .= "DELETE FROM hrd_employee_mutation_department WHERE id_mutation = '$strValue'; ";
            $strSQL .= "DELETE FROM hrd_employee_mutation_position WHERE id_mutation = '$strValue'; ";
            $strSQL .= "DELETE FROM hrd_employee_mutation_id WHERE id_mutation = '$strValue'; ";
            $strSQL .= "DELETE FROM hrd_employee_mutation_salary WHERE id_mutation = '$strValue'; ";
            $strSQL .= "DELETE FROM hrd_employee_mutation_branch WHERE id_mutation = '$strValue'; ";
            $strSQL .= "DELETE FROM hrd_employee_mutation_cost_center WHERE id_mutation = '$strValue'; ";
            $strSQL .= "DELETE FROM hrd_employee_mutation WHERE id = '$strValue'; ";
            $resExec = $db->execute($strSQL);

          }
        }

      }
    }
    if ($i > 0) {
      writeLog(ACTIVITY_DELETE, MODULE_PAYROLL,"$i data",0);
    }

  } //deleteData


  // fungsi untuk menghapus data
  function changeStatus($db, $intStatus) {
    global $_REQUEST;
    global $_SESSION;
    global $ARRAY_EMPLOYEE_STATUS;
    $arrTempStatus = $ARRAY_EMPLOYEE_STATUS;
    $arrTempStatus[99] = "Resigned";



    if (!is_numeric($intStatus)) {
      return false;
    }

    $i = 0;
    $strUpdate = "";
    $strmodified_byID = $_SESSION['sessionUserID'];

    $strUpdate = getStatusUpdateString($intStatus);

    foreach ($_REQUEST as $strIndex => $strValue)
    {
      if (substr($strIndex,0,5) == 'chkID')
      {
        $strIDEmployee = $_REQUEST['empID'.substr($strIndex,5)];
        $i++;
        $strSQLx = "SELECT proposal_date, id_employee, status_old, status_new, grade_old, grade_new, division_old, department_old, section_old, division_new,
                    department_new, section_new, employee_name, t0.approved_time, t0.status
                    FROM hrd_employee_mutation AS t0
                    LEFT JOIN hrd_employee_mutation_status AS t1 ON t0.id = t1.id_mutation
                    LEFT JOIN hrd_employee_mutation_position AS t2 ON t0.id = t2.id_mutation
                    LEFT JOIN hrd_employee_mutation_department  AS t3 ON t0.id = t3.id_mutation
                    LEFT JOIN hrd_employee_mutation_branch  AS t4 ON t0.id = t4.id_mutation
                    LEFT JOIN hrd_employee_mutation_cost_center  AS t8 ON t0.id = t8.id_mutation
                    LEFT JOIN hrd_employee_mutation_id  AS t5 ON t0.id = t5.id_mutation
                    LEFT JOIN hrd_employee_mutation_salary  AS t6 ON t0.id = t6.id_mutation
                    LEFT JOIN hrd_employee AS t7 ON t0.id_employee = t7.id
                    WHERE t0.id = '$strValue' ";
        $resDb = $db->execute($strSQLx);
        if ($rowDb = $db->fetchrow($resDb))
        {
          $strBody.= "Name: ".getEmployeeNameEmail($rowDb['id_employee'])."<br>";
          $strBody.= "Proposal Date: ".$rowDb['proposal_date'];
          $strBody.= "<br><br>Details are listed in Placement Request List";
          $strSubject = getSubject(0,'Placement Request',getEmployeeIDEmail($rowDb['id_employee']));
          $strBody =  getBody($intStatus,'Absence',$strBody,$strmodified_byID);
          //only new entries can be edited and updated
          if (isProcessable($rowDb['status'], $intStatus))
          {
            $strSQL  = "UPDATE hrd_employee_mutation SET $strUpdate status = '$intStatus' WHERE id = $strValue ";
            //$strSQL .= "verification_date = now(), approval_date = NULL ";
            //$strSQL .= "WHERE id = '$strValue' AND status <>  ".REQUEST_STATUS_APPROVED; // yang udah apprve gak boleh diedit
            $resExec = $db->execute($strSQL);
            updateEmployeeCareerData($db, $strValue, $strIDEmployee);
            sendMail($strSubject,$strBody);
            $strBody = "";
            $strLog = "";
            if ($rowDb['status_old'] != $rowDb['status_new'])
              $strLog .= "status ". $arrTempStatus[$rowDb['status_old']]." to ". $arrTempStatus[$rowDb['status_new']];
            if ($rowDb['grade_old'] != $rowDb['grade_new'])
              $strLog .= "grade ". $rowDb['grade_old']." to ". $rowDb['grade_new'];
            if ($rowDb['section_old'] != $rowDb['section_new'])
              $strLog .= "section ". $rowDb['section_old']." to ". $rowDb['section_new'];
            else if ($rowDb['department_old'] != $rowDb['department_new'])
              $strLog .= "department ". $rowDb['department_old']." to ". $rowDb['department_new'];
            else if ($rowDb['division_old'] != $rowDb['division_new'])
              $strLog .= "division ". $rowDb['division_old']." to ". $rowDb['division_new'];
            writeLog(ACTIVITY_EDIT, MODULE_EMPLOYEE, $rowDb['employee_name']." - ". $rowDb['approved_time'].". \n".
              $strLog, $intStatus);
          }
        }
      }
    }
    if ($i > 0)
    {
    }

  } //changeStatus
  
  function showApprover($rowDb, $counter){
  	global $arrUserList;
  	$strResult  = "";
    $strDiv  = "<div id='detailRecord$counter' style=\"display:none\">\n";
    $strDiv .= "<strong>" .$rowDb['employee_id']."-".$rowDb['employee_name']."</strong><br>\n";
    $strDiv .= getWords("last modified"). ": ".substr($rowDb['modified'], 0,19) ." ";
    $strDiv .= (isset($arrUserList[$rowDb['modified_by']])) ? $arrUserList[$rowDb['modified_by']]['name']."<br>" : "<br>";
		$strDiv .= getWords("approved"). ": ".substr($rowDb['approved2_time'], 0,19) ." ";
    $strDiv .= (isset($arrUserList[$rowDb['approved2_by']])) ? $arrUserList[$rowDb['approved2_by']]['name']."<br>" : "<br>";
		$strDiv .= "</div>\n";
		$strResult .= $strDiv."<a href=\"javascript:openViewWindowByContentId('Record Information', 'detailRecord$counter', 400, 150)\" title=\"" .getWords("show record info")."\">" .getWords("show")."</a>";
    return $strResult;	
  }
  //----------------------------------------------------------------------

  //----MAIN PROGRAM -----------------------------------------------------
  $strInfo = "";

  $db = new CdbClass;
  if ($db->connect()) {
  	deleteExcelDownloadedFile();
		$arrUserList = getAllUserInfo($db);
    getUserEmployeeInfo();
    //$bolCanApprove2 = isEligibleApprove2($db);
    // hapus data jika ada perintah
    if (isset($_REQUEST['btnDelete'])) {
      if ($bolCanDelete) deleteData($db);
    }
    else
      callChangeStatus();

    // ------ AMBIL DATA KRITERIA -------------------------
    $dtFrom = date("Y-m-")."25";
    $dtFrom = getNextDateNextMonth($dtFrom, -1);
    $dtThru = date("Y-m-")."24";
    getDefaultSalaryPeriode($strDefaultFrom,$strDefaultThru);
    $strDataDateFrom        = (isset($_SESSION['sessionFilterDateFrom']))       ? $_SESSION['sessionFilterDateFrom']    : $dtFrom;
    $strDataDateThru        = (isset($_SESSION['sessionFilterDateThru']))       ? $_SESSION['sessionFilterDateThru']    : $dtThru;
    $strDataDivision        = (isset($_SESSION['sessionFilterDivision']))       ? $_SESSION['sessionFilterDivision']    : "";
    $strDataDepartment      = (isset($_SESSION['sessionFilterDepartment']))     ? $_SESSION['sessionFilterDepartment']  : "";
    $strDataSection         = (isset($_SESSION['sessionFilterSection']))        ? $_SESSION['sessionFilterSection']     : "";
    $strDataSubSection      = (isset($_SESSION['sessionFilterSubSection']))     ? $_SESSION['sessionFilterSubSection']  : "";
    $strDataEmployee        = (isset($_SESSION['sessionFilterEmployee']))       ? $_SESSION['sessionFilterEmployee']    : "";
    $strDataRequestStatus   = (isset($_SESSION['sessionFilterRequestStatus']))  ? $_SESSION['sessionFilterRequestStatus']  : "";
    $strDataEmployeeStatus  = (isset($_SESSION['sessionFilterEmployeeStatus'])) ? $_SESSION['sessionFilterEmployeeStatus'] : "";
    if (isset($_REQUEST['dataDateFrom']))   $strDataDateFrom    = $_REQUEST['dataDateFrom'];
    if (isset($_REQUEST['dataDateThru']))   $strDataDateThru    = $_REQUEST['dataDateThru'];
    if (isset($_REQUEST['dataDivision']))   $strDataDivision    = $_REQUEST['dataDivision'];
    if (isset($_REQUEST['dataDepartment'])) $strDataDepartment  = $_REQUEST['dataDepartment'];
    if (isset($_REQUEST['dataSection']))    $strDataSection     = $_REQUEST['dataSection'];
    if (isset($_REQUEST['dataSubSection'])) $strDataSubSection  = $_REQUEST['dataSubSection'];
    if (isset($_REQUEST['dataEmployee']))   $strDataEmployee    = $_REQUEST['dataEmployee'];
    if (isset($_REQUEST['dataRequestStatus'])) $strDataRequestStatus = $_REQUEST['dataRequestStatus'];
    if (isset($_REQUEST['dataEmployeeStatus'])) $strDataEmployeeStatus = $_REQUEST['dataEmployeeStatus'];
    if (isset($_REQUEST) && count($_REQUEST) > 1){
    	$strShowStatus = isset($_REQUEST['chkStatus']) ? 'checked' : '';
	    $strShowPosition = isset($_REQUEST['chkPosition']) ? 'checked' : '';
	    $strShowDepartment = isset($_REQUEST['chkDepartment']) ? 'checked' : '';
	    $strShowNIK = isset($_REQUEST['chkNIK']) ? 'checked' : '';
	    $strShowSalary = isset($_REQUEST['chkSalary']) ? 'checked' : '';
	    $strShowBranch = isset($_REQUEST['chkBranch']) ? 'checked' : '';
	    $strShowCostCenter = isset($_REQUEST['chkCostCenter']) ? 'checked' : '';
    }else{
    	$strShowStatus = 'checked';
	    $strShowPosition = 'checked';
	    $strShowDepartment = 'checked';
	    $strShowNIK = 'checked';
	    $strShowSalary = 'checked';
	    $strShowBranch = 'checked';
	    $strShowCostCenter = 'checked';
    }
    $_SESSION['sessionFilterDateFrom']       = $strDataDateFrom;
    $_SESSION['sessionFilterDateThru']       = $strDataDateThru;
    $_SESSION['sessionFilterDivision']       = $strDataDivision;
    $_SESSION['sessionFilterDepartment']     = $strDataDepartment;
    $_SESSION['sessionFilterSection']        = $strDataSection;
    $_SESSION['sessionFilterSubSection']     = $strDataSubSection;
    $_SESSION['sessionFilterEmployee']       = $strDataEmployee;
    $_SESSION['sessionFilterRequestStatus']  = $strDataRequestStatus;
    $_SESSION['sessionFilterEmployeeStatus'] = $strDataEmployeeStatus;
    scopeData($strDataEmployee, $strDataSubSection, $strDataSection, $strDataDepartment, $strDataDivision, $_SESSION['sessionUserRole'], $arrUserInfo);

    // ------------ GENERATE KRITERIA QUERY,JIKA ADA -------------
    $strKriteria = "";

    if ($strDataDateFrom != "" && $strDataDateThru != "") {
      $strKriteria .= "AND proposal_date BETWEEN '$strDataDateFrom' AND '$strDataDateThru' ";
    }

  if ($strDataDivision != "") {
      $strKriteria .= "AND division_code = '$strDataDivision' ";
    }
    if ($strDataDepartment != "") {
      $strKriteria .= "AND department_code = '$strDataDepartment' ";
    }
    if ($strDataSection != "") {
      $strKriteria .= "AND section_code = '$strDataSection' ";
    }
    if ($strDataSubSection != "") {
      $strKriteria .= "AND sub_section_code = '$strDataSubSection' ";
    }
    if ($strDataEmployee != "") {
      $strKriteria .= "AND employee_id = '$strDataEmployee' ";
    }
    if ($strDataRequestStatus != "") {
      $strKriteria .= "AND status = '$strDataRequestStatus' ";
    }
    $strKriteria .= $strKriteriaCompany;
		$xlsfilename = "";
    if ($bolCanView) {
      if (validStandardDate($strDataDateFrom) && validStandardDate($strDataDateThru)) {
        // tampilkan hanya jika ada permintaan dan data tanggalnya tepat
        getHeader($strHeader1, $strHeader2);
        $strDataDetail = getData($db,$strDataDateFrom, $strDataDateThru, $intTotalData, $strKriteria);
        if ($bolPrint) {
          // perintah printing, cek jenis yg diprint
          if (isset($_REQUEST['btnPrintStatus'])) {
            $strShowStatus = 'checked';
            $strShowPosition = '';
            $strShowDepartment = '';
            $strShowNIK = '';
            $strShowBranch = '';
            $strShowCostCenter = '';
            $strShowSalary = '';
            getHeader($strHeader1, $strHeader2);
            $strDataDetail = getDataStatus($db,$strDataDateFrom, $strDataDateThru, $intTotalData, $strKriteria);
          } else if (isset($_REQUEST['btnPrintPosition'])) {
            $strShowStatus = '';
            $strShowPosition = 'checked';
            $strShowDepartment = '';
            $strShowNIK = '';
            $strShowBranch = '';
            $strShowCostCenter = '';
            $strShowSalary = '';
            getHeader($strHeader1, $strHeader2);
            $strDataDetail = getDataPosition($db,$strDataDateFrom, $strDataDateThru, $intTotalData, $strKriteria);
          } else if (isset($_REQUEST['btnPrintDepartment'])) {
          	$strShowStatus = '';
            $strShowPosition = '';
            $strShowDepartment = 'checked';
            $strShowNIK = '';
            $strShowBranch = '';
            $strShowCostCenter = '';
            $strShowSalary = '';
            getHeader($strHeader1, $strHeader2);
            $strDataDetail = getDataDepartmentSpecial($db,$strDataDateFrom, $strDataDateThru, $intTotalData, $strKriteria);
          } else if (isset($_REQUEST['btnPrintSalary'])) {
            $strShowStatus = '';
            $strShowPosition = '';
            $strShowDepartment = '';
            $strShowNIK = '';
            $strShowBranch = '';
            $strShowCostCenter = '';
            $strShowSalary = 'checked';
            getHeader($strHeader1, $strHeader2);
            $strDataDetail = getDataSalary($db,$strDataDateFrom, $strDataDateThru, $intTotalData, $strKriteria);
          } else if (isset($_REQUEST['btnPrint'])){
            $strShowStatus = 'checked';
            $strShowPosition = 'checked';
            $strShowDepartment = 'checked';
            $strShowNIK = 'checked';
            $strShowBranch = 'checked';
            $strShowCostCenter = 'checked';
            $strShowSalary = 'checked';
            $strDisplayAll = "";
            getHeader($strHeader1, $strHeader2);
            $strDataDetail = getData($db,$strDataDateFrom, $strDataDateThru, $intTotalData, $strKriteria);
          } else {
            $strDisplayAll = "";
          }
          if (isset($_REQUEST['btnExcel'])) {
              // ambil data CSS-nya
            $strShowStatus = 'checked';
            $strShowPosition = 'checked';
            $strShowDepartment = 'checked';
            $strShowNIK = 'checked';
            $strShowBranch = 'checked';
            $strShowCostCenter = 'checked';
            $strShowSalary = 'checked';
            $strDisplayAll = "";
            $header1 = array();
            $header2 = array();
            getHeader($strHeader1, $strHeader2, $header1, $header2);
            $arrayData = array();
            $objectName = array();
            $strDataDetail = getData($db,$strDataDateFrom, $strDataDateThru, $intTotalData, $strKriteria);
            $strPageTitle = $dataPrivilege['menu_name'];
            $explodeDateFrom = explode('-', $strDataDateFrom);
        		$explodeDateThru = explode('-', $strDataDateThru);
		        $intDateFrom = mktime(0,0,0,$explodeDateFrom[1],$explodeDateFrom[2],$explodeDateFrom[0]);
		        $intDateThru = mktime(0,0,0,$explodeDateThru[1],$explodeDateThru[2],$explodeDateThru[0]);
		        $subtitle = strtoupper(date('d M Y', $intDateFrom)).' >> '.strtoupper(date('d M Y', $intDateThru));
		        $xlsfilename = exportXLSX($arrayData,$header1,$header2,$objectName,$strPageTitle,$subtitle,'mutation-list');
		        $tblTempFile = new cModel("hrd_temporary_file", "Temporary File");
		        $data = array();
						$data['filename'] = $xlsfilename;
						$data['created'] = date('Y-m-d H:i:s');
						$tblTempFile->insert($data);
            /*if (file_exists("../css/default_bw.css")) $strStyle = "../css/default_bw.css";
            $strPrintCss = "";
            $strPrintInit = "";
            headeringExcel("mutationList.xls");*/
          }
        }
      } else {
        $strDataDetail = "";
      }
    } else {
      showError("view_denied");
    }

    $intDefaultWidthPx = 200;
    $strInputDateFrom = "<input type=text name=dataDateFrom id=dataDateFrom size=15 maxlength=10 value=\"$strDataDateFrom\">";
    $strInputDateThru = "<input type=text name=dataDateThru id=dataDateThru size=15 maxlength=10 value=\"$strDataDateThru\">";
    $strInputEmployee = "<input type=text name=dataEmployee id=dataEmployee size=22 maxlength=30 value=\"$strDataEmployee\" $strEmpReadonly>";
    $strInputDivision = getDivisionList($db,"dataDivision",$strDataDivision, $strEmptyOption, "", "style=\"width:$intDefaultWidthPx\" ". $ARRAY_DISABLE_GROUP['division']);
    $strInputDepartment = getDepartmentList($db,"dataDepartment",$strDataDepartment, $strEmptyOption, "", "style=\"width:$intDefaultWidthPx\" ". $ARRAY_DISABLE_GROUP['department']);
    $strInputSection = getSectionList($db,"dataSection",$strDataSection, $strEmptyOption, "", "style=\"width:$intDefaultWidthPx\" ". $ARRAY_DISABLE_GROUP['section']);
    $strInputSubSection = getSubSectionList($db,"dataSubSection",$strDataSubSection, $strEmptyOption, "", "style=\"width:$intDefaultWidthPx\" ". $ARRAY_DISABLE_GROUP['sub_section']);
    $strInputRequestStatus = getComboFromArray($ARRAY_REQUEST_STATUS, "dataRequestStatus", $strDataRequestStatus, $strEmptyOption, "style=\"width:125\"");

    //handle user company-access-right
    $strInputCompany = getCompanyList($db, "dataCompany",$strDataCompany, $strEmptyOption2, $strKriteria2, "style=\"width:125\" ");
    $strInputShowStatus = generateCheckBox2("chkStatus", 1, "", $strShowStatus);
    $strInputShowPosition = generateCheckBox2("chkPosition", 1, "", $strShowPosition);
    $strInputShowDepartment = generateCheckBox2("chkDepartment", 1, "", $strShowDepartment);
    $strInputShowNIK = generateCheckBox2("chkNIK", 1, "", $strShowNIK);
    $strInputShowBranch = generateCheckBox2("chkBranch", 1, "", $strShowBranch);
    $strInputShowCostCenter = generateCheckBox2("chkCostCenter", 1, "", $strShowCostCenter);
    $strInputShowSalary = generateCheckBox2("chkSalary", 1, "", $strShowSalary);
    // informasi tanggal kehadiran
    if ($strDataDateFrom == $strDataDateThru) {
      $strInfo .= "<br>".strtoupper(pgDateFormat($strDataDateFrom, "d-M-Y"));
    } else {
      $strInfo .= "<br>".strtoupper(pgDateFormat($strDataDateFrom, "d-M-Y"));
      $strInfo .= " >> ".strtoupper(pgDateFormat($strDataDateThru, "d-M-Y"));
    }

    $strButtons = generateRoleButtons($bolCanEdit, $bolCanDelete, $bolCanCheck, $bolCanApprove, $bolCanApprove2);


    $strHidden .= "<input type=hidden name=dataDateFrom value=\"$strDataDateFrom\">";
    $strHidden .= "<input type=hidden name=dataDateThru value=\"$strDataDateThru\">";
    $strHidden .= "<input type=hidden name=dataDivision value=\"$strDataDivision\">";
    $strHidden .= "<input type=hidden name=dataDepartment value=\"$strDataDepartment\">";
    $strHidden .= "<input type=hidden name=dataSection value=\"$strDataSection\">";
    $strHidden .= "<input type=hidden name=dataSubSection value=\"$strDataSubSection\">";
    $strHidden .= "<input type=hidden name=dataEmployee value=\"$strDataEmployee\">";
    $strHidden .= "<input type=hidden name=dataRequestStatus value=\"$strDataRequestStatus\">";
    
  }

  $tbsPage = new clsTinyButStrong ;

  //write this variable in every page
  $strPageTitle = $dataPrivilege['menu_name'];
  $pageIcon = "../images/icons/".$dataPrivilege['icon_file'];
  //------------------------------------------------
  if ($bolPrint && !isset($_REQUEST['btnExcel'])) {

    $strTemplateFile = getTemplate("mutation_list_print.html");
    $tbsPage->LoadTemplate($strTemplateFile) ;
  } else {
    $strTemplateFile = getTemplate("mutation_list.html");
    $tbsPage->LoadTemplate($strMainTemplate) ;
  }
  $tbsPage->Show() ;

?>
