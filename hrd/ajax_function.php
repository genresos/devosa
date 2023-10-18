<?php
  if ( !session_id() ) session_start();
  // periksa apakah sudah login atau belum, jika belum, harus login lagi
  if (!isset($_SESSION['sessionUserID'])) 
  {
    echo "Session time out, please re-login!";
    exit();
  }

  include_once('global.php');
  
  if ((!isset($_GET['ajax'])) || ($_GET['ajax'] != 1)) 
  {
    echo "Sorry, must be called from AJAX request!";
    exit();
  }
  
  $db = new cDbClass;
  $db->connect();
  
  $action = getGetValue("action");
  switch ($action)
  {
    case "getRecruitmentPlan" : 
      $strPlanID = getGetValue('idPlan');
      echo getRecruitmentPlan($strPlanID);
      break;
    case "getWarningHistory" : 
      $strEmployeeID = getGetValue('employeeID');
      echo getWarningHistory($strEmployeeID);
      break;
    case "getOvertimeInfo" : 
      $strEmployeeID = getGetValue('employeeID');
      $strDate = getGetValue('date');
      echo getOvertimeInfo($strEmployeeID, $strDate);
      break;
    case "getAnnualLeaveQuota" : 
      $strEmployeeID = getGetValue('employee_id');
      echo getAnnualLeaveQuota($strEmployeeID);
      break;
    case "getAbsenceDuration" : 
      $strEmployeeID = getGetValue('employee_id');
      $strStartDate = getGetValue('start_date');
      $strFinishDate = getGetValue('finish_date');
      echo getAbsenceDuration($strEmployeeID, $strStartDate, $strFinishDate);
      break;
    case "getLeaveTolerance" : 
      $strAbsenceCode = getGetValue('absence_type_code');
      echo getLeaveTolerance($strAbsenceCode);
      break;
    case "getAttendanceInfo" : 
      $strEmployeeID = getGetValue('employee_id');
      $strStartDate = getGetValue("date");
      echo getAttendanceInfo($strEmployeeID, $strStartDate);
      break;
    case "getSalarySetByCompany" :  
    	$strIDCompany = getGetValue('id_company');
    	$salarySet = getSalarySetByCompany('id',true,$strIDCompany);
    	if (count($salarySet)){
    		$returnData[] = $salarySet;
    		print json_encode($returnData);
    	}else{
    		$returnData = null;
    		print json_encode($returnData);
    	}
    	break;
    case "getDetailSalaryChangeTable"	 :
    	$strEmployeeID = getGetValue('employee_id');
    	$strSetID = getGetValue('salary_set_id');
    	print getDetailSalaryChangeTable($strEmployeeID,$strSetID);
    	break;
    case "getApprovedOvertimeByCompany" :
    	$strCompanyId = getGetValue('id_company');
    	$returnData[] = getApprovedOvertimeByCompany($strCompanyId);
    	print json_encode($returnData);
    	break;	
    default :
      echo "You must set any action first!";
      break;
  }
  exit();
  
  function getWarningHistory($strEmployeeID)
  {
    global $db;
    
    $strDataID = getGetValue('dataID');
    $strResult  = "";
    $strResult .= "<span style=\"font-size: 11pt; font-weight: bold\">List of Warning History</span>";
    $strResult .= "<table class=\"gridTable\" border=0 cellpadding=1 cellspacing=0>\n";
    $strResult .= "<tr>\n";
    $strResult .= "  <th>".getWords("warning date")."</th>\n";
    $strResult .= "  <th>".getWords("warning type")."</th>\n";
    $strResult .= "  <th>".getWords("duration (days)")."</th>\n";
    $strResult .= "  <th>".getWords("due date")."</th>\n";
    $strResult .= "  <th>".getWords("note")."</th>\n";
    $strResult .= "</tr>\n";
    $counter = 0;
    if ($strEmployeeID != "")
    {
      $strSQL  = "
        SELECT t1.*, t2.employee_id
          FROM hrd_employee_warning AS t1 
                INNER JOIN hrd_employee AS t2 
                  ON t1.id_employee = t2.id 
          WHERE t2.employee_id = '$strEmployeeID' 
          ORDER BY t1.warning_date DESC ";
      $resDb = $db->execute($strSQL);
      while ($rowDb = $db->fetchrow($resDb)) 
      {
        $counter++;
        if ($rowDb['id'] == $strDataID)
          $strCSSClass = " class=\"bgCheckedData\"";
        else
          $strCSSClass = "";
        $strResult .= "<tr $strCSSClass>\n";
        $strResult .= "  <td nowrap>".pgDateFormat($rowDb['warning_date'], "d-M-y")."</td>\n";
        $strResult .= "  <td nowrap>".$rowDb['warning_code']."</td>\n";
        $strResult .= "  <td align=right nowrap>".$rowDb['duration']."</td>\n";
        $strResult .= "  <td nowrap>".pgDateFormat($rowDb['due_date'], "d-M-y")."</td>\n";
        $strResult .= "  <td>".$rowDb['note']."</td>\n";
        $strResult .= "</tr>\n";
      }
    }
    if ($counter == 0)
      $strResult .= "<tr><td colspan=5>There are no warning data</td></tr>\n";
    
    $strResult .= "</table>\n";
    return $strResult;
  }
  
  
  function getRecruitmentPlan( $strPlanID )
  {
    global $db;
    $arrData = array();
    if ($strPlanID != "")
    {
      $strSQL  = "SELECT t1.* FROM \"hrdRecruitmentPlan\" AS t1 ";
      $strSQL .= "WHERE t1.id = '$strPlanID' ";
      $resDb = $db->execute($strSQL);
      if ($rowDb = $db->fetchrow($resDb)) 
      {
        //$arrData['dataDate'] = $rowDb['recruitmentDate'];
        $arrData['dataDepartment'] = $rowDb['departmentCode'];
        $arrData['dataPosition'] = $rowDb['position'];
        $arrData['dataEmployeeStatus'] = $rowDb['employeeStatus'];
        $arrData['dataNumber'] = $rowDb['number'];
        $arrData['dataDueDate'] = $rowDb['dueDate'];
        $arrData['dataDescription'] = $rowDb['description'];
        $arrData['dataMinAge'] = $rowDb['minAge'];
        $arrData['dataMaxAge'] = $rowDb['maxAge'];
        $arrData['dataGender'] = $rowDb['gender'];
        $arrData['dataMarital'] = $rowDb['maritalStatus'];
        $arrData['dataEducationLevel'] = $rowDb['educationLevel'];
        $arrData['dataEducation'] = $rowDb['education'];
        $arrData['dataWork'] = $rowDb['workExperience'];
        $arrData['dataQualification'] = $rowDb['qualification'];
        //$arrData['dataStatus'] = $rowDb['status'];
        $arrData['dataCost'] = $rowDb['cost'];
        $arrData['dataPIC'] = $rowDb['PIC'];
        $arrData['dataPlan'] = $rowDb['id'];
      }
    }
    return implode("|||", $arrData);
  }
  
  function getOvertimeInfo($strID, $strDate)
  {
    global $db;
    
    include_once('functionEmployee.php');

    if ($strID == "") return "";
    
    $strID = getIDEmployee($db, $strID);
    if (!is_numeric($strID)) return "";

    $arrResult = array();
    

    //get department head
    /*$strSQL = "SELECT \"departmentCode\" FROM \"hrdEmployee\" WHERE id = '$strID'";
    $res = $db->execute($strSQL);
    if ($row = $db->fetchrow($res)) 
    {
      $strSQL = "SELECT \"id\", \"employeeName\", \"positionCode\" FROM \"hrdEmployee\" WHERE \"departmentCode\" = '".$row['departmentCode']."' AND \"salaryGradeCode\" = 'B'";
      $res = $db->execute($strSQL);

      if ($row = $db->fetchrow($res))
      {
        $arrResult[] = $row['id']."_".$row['employeeName']."_ (".$row['positionCode'].")";
        while ($row = $db->fetchrow($res))
          $arrResult[] = $row['id']."_".$row['employeeName']."_ (".$row['positionCode'].")";
      }
      else
      {
        $strSQL = "SELECT \"id\", \"employeeName\", \"positionCode\" FROM \"hrdEmployee\" WHERE \"salaryGradeCode\" = 'B'";
        $res = $db->execute($strSQL);
        while ($row = $db->fetchrow($res))
          $arrResult[] = $row['id']."_".$row['employeeName']."_ (".$row['positionCode'].")";
      }
    }

    $strResult = join($arrResult, "|");*/

    $strResult = "";
    
    // get Shift Data
    $strSQL = "SELECT \"shiftCode\" FROM \"hrdShiftScheduleEmployee\" WHERE \"shiftDate\" = '$strDate' AND \"idEmployee\" = '$strID'";
    $resS = $db->execute($strSQL);
    if ($rowS = $db->fetchrow($resS))
    {
      $strResult .= $rowS['shiftCode'];
      //ambil jam selesai kerja berdasarkan shift
      $strResult .= "|||".getEmployeeWorkingTime($rowS['shiftCode'], $strDate, "|||");
    }
    else 
    {
      //ambil jam selesai kerja normal
      $strResult .= "|||".getEmployeeWorkingTime(null, $strDate, "|||");
    }
    // get Attendance Data
    $strSQL = "SELECT \"attendanceStart\", \"attendanceFinish\" FROM \"hrdAttendance\" WHERE \"attendanceDate\" = '$strDate' AND \"idEmployee\" = '$strID'";
    $resS = $db->execute($strSQL);
    if ($rowS = $db->fetchrow($resS))
    {
      //$strResult .= "[".substr($rowS['attendanceStart'],0,5) ."] until [". substr($rowS['attendanceFinish'],0,5)."]";
      //$strResult .= "|||";
      $strResult .= "|||".substr($rowS['attendanceStart'],0,5);
      $strResult .= "|||".substr($rowS['attendanceFinish'],0,5);
    }
    else $strResult .= "||||||";

    unset($db);
    return $strResult;
  }
  
  function getAnnualLeaveQuota($strEmployeeID)
  {
    // include_once("../global/employee_function.php");

    include_once("cls_annual_leave.php");
    global $db;
    
    $intRows = 0;
    $strResult  = "";
    $strResult .= "<span style=\"font-size: 11pt; font-weight: bold\">Annual Leave Quota</span>";
    $strResult .= "<table class=\"gridTable\" border=0 cellpadding=1 cellspacing=0>\n";
    $strResult .= "<tr>\n";
    $strResult .= "  <th colspan=6>".getWords("previous year")."</th>\n";
    $strResult .= "  <th colspan=6>".getWords("current year")."</th>\n";
    $strResult .= "  <th rowspan=2>".getWords("total remaining")."</th>\n";
    $strResult .= "</tr>\n";
    $strResult .= "<tr>\n";
    $strResult .= "  <th>".getWords("year")."</th>\n";
    $strResult .= "  <th>".getWords("quota")."</th>\n";
    $strResult .= "  <th>".getWords("holiday")."</th>\n";
    $strResult .= "  <th>".getWords("prev. over")."</th>\n";
    $strResult .= "  <th>".getWords("taken")."</th>\n";
    $strResult .= "  <th>".getWords("remain")."</th>\n";
    $strResult .= "  <th>".getWords("year")."</th>\n";
    $strResult .= "  <th>".getWords("quota")."</th>\n";
    $strResult .= "  <th>".getWords("holiday")."</th>\n";
    $strResult .= "  <th>".getWords("prev. over")."</th>\n";
    $strResult .= "  <th>".getWords("taken")."</th>\n";
    $strResult .= "  <th>".getWords("remain")."</th>\n";
    $strResult .= "</tr>\n";
    $counter = 0;
    
    $strDateFrom = "";
    $strDateThru = "";
    $strKriteria = "AND employee_id = '".$strEmployeeID."' AND active = 1 AND flag = 0 ";
    $objLeave = new clsAnnualLeaveTakaful($db, $strKriteria);
    $objLeave->generateAnnualLeave();
    $strSQL  = "
        SELECT id, employee_id, employee_name, gender, section_code, employee_status, 
          EXTRACT(YEAR FROM AGE(join_date)) AS durasi, 
          EXTRACT(MONTH FROM join_date) AS bulan, 
          EXTRACT(YEAR FROM join_date) AS tahun, 
          join_date, resign_date
        FROM hrd_employee 
        WHERE 1=1 $strKriteria
    ";
    $resDb = $db->execute($strSQL);
    while ($rowDb = $db->fetchrow($resDb)) 
    {
      $intRows++;
      $strGender = ($rowDb['gender'] == FEMALE) ? "F" : "M";
      $strInfo = $rowDb['employee_id'] ." - ". $rowDb['employee_name'];

      //$arrCuti = getEmployeeLeaveQuota($db, $rowDb['id']);
      $arrCuti = $objLeave->getEmployeeLeaveInfo($rowDb['id']);

      // cek nilai sisa cuti
      $strPrevYear        = $arrCuti['prev']['year'];
      $strPrevPeriod      = $arrCuti['prev']['start'] ." | ".$arrCuti['prev']['finish'];
      $intLeaveQuotaPrev  = $arrCuti['prev']['quota'];
      $intLeaveHolidayPrev = $arrCuti['prev']['holiday'];
      $intLeaveTakenPrev  = $arrCuti['prev']['taken'];
      $intLeaveRemainPrev = $arrCuti['prev']['remain'];
      $strCurrYear        = $arrCuti['curr']['year'];
      $strCurrPeriod      = $arrCuti['curr']['start'] ." | ".$arrCuti['prev']['finish'];
      $intLeaveQuotaCurr  = $arrCuti['curr']['quota'];
      $intLeaveHolidayCurr = $arrCuti['curr']['holiday'];
      $intLeaveTakenCurr  = $arrCuti['curr']['taken'];
      $intLeaveRemainCurr = $arrCuti['curr']['remain'];
      $intOverPrev        = $arrCuti['curr']['prev_taken'];
      $intOverCurr        = $arrCuti['curr']['prev_taken'];
      
      $strPrevClass = "";
      if ($arrCuti['prev']['overdue']) 
      {
        $strPrevClass = "style=\"background-color:darkred;color:white\" ";
      }
      /*
      else
      {
        $strPrevClass = "";
        $intLeaveRemain += $intLeaveRemainPrev;
      }
      */
      //$intLeaveRemain = $intLeaveRemainCurr;
      $intLeaveRemain = $objLeave->getEmployeeLeaveRemain($rowDb['id']);
      
      $strClass = "";
      /*
      if ($rowDb['employee_status'] == STATUS_CONTRACT_1)
        $strClass = "class=bgConsidered";
      else  if ($rowDb['employee_status'] == STATUS_CONTRACT_2)
        $strClass = "class=bgConsidered";
      */
      if ($intLeaveQuotaCurr == 0 && $intLeaveQuotaPrev == 0) 
      {
        $strClass = "class=bgDenied";
      }
      $strResult .= "<tr valign=top title=\"$strInfo\" $strClass>\n";
      
      $strResult .= "  <td align=center>".$strPrevYear."</td>";
      $strResult .= "  <td align=right>".$intLeaveQuotaPrev."</td>";
      $strResult .= "  <td align=right>".$intLeaveHolidayPrev."</td>";
      $strResult .= "  <td align=right>".$intOverPrev."</td>";
      $strResult .= "  <td align=right>".$intLeaveTakenPrev."</td>";
      $strResult .= "  <td align=right $strPrevClass>".$intLeaveRemainPrev."&nbsp;</td>";

      $strResult .= "  <td align=center>".$strCurrYear."</td>";
      $strResult .= "  <td align=right>".$intLeaveQuotaCurr."</td>";
      $strResult .= "  <td align=right>".$intLeaveHolidayCurr."&nbsp;</td>";
      $strResult .= "  <td align=right>".$intOverCurr."</td>";
      $strResult .= "  <td align=right>".$intLeaveTakenCurr."</td>";
      $strResult .= "  <td align=right>".$intLeaveRemainCurr."&nbsp;</td>";
      $strResult .= "  <td align=right><strong>".$intLeaveRemain."</strong>&nbsp;</td>";
      $strResult .= "</tr>\n";
    }
    $strResult .= "</table>\n";
    if ($intRows == 0)
    {
      $strResult = "ERROR: Cannot find employee ID: ".$strEmployeeID." in the database!";
    }
    return $strResult;

  }
  
  function getAbsenceDuration($strEmployeeID, $strStartDate, $strFinishDate)
  {
    include_once('function_employee.php');
    include_once('../global/cls_date.php');
    global $db;
    

    $strIDEmployee = getIDEmployee($db, $strEmployeeID);
    if ($strIDEmployee == "")
      $strDataDuration = "ERROR: Cannot find employee ID: ".$strEmployeeID." in the database!";
    else if (validStandardDate($strStartDate) == false)
      $strDataDuration = "ERROR: Invalid Date: ".$strStartDate." !";
    else if (validStandardDate($strFinishDate) == false)
      $strDataDuration = "ERROR: Invalid Date: ".$strFinishDate." !";
    else if (dateCompare($strStartDate, $strFinishDate) == 1)
      $strDataDuration = 0;
    else
      $strDataDuration = totalWorkDayEmployee($db, $strIDEmployee, $strStartDate, $strFinishDate); // common_functions.php
    //$strDataDuration = totalWorkDay($db, $strStartDate, $strFinishDate);
    
    return $strDataDuration;
  }
  
  function getLeaveTolerance($strAbsenceCode)
  {
    include_once("../includes/model/model.php");
    
    $tbl = new cModel("hrd_absence_type");
    if ($arrData = $tbl->findByCode($strAbsenceCode))
    {
      $strLeaveDeduct = ($arrData['deduct_leave'] == 't' || $arrData['is_leave'] == 't') ? 't' : 'f';
      return (float)($arrData['leave_tolerance'])."|".$arrData['unlimited_free_day']."|".$strLeaveDeduct;//$arrData['deduct_leave'];
    }
    return "0|f|t";
  }

  function getAttendanceInfo($strEmployeeID, $strStartDate)
  {
    $tblAttendance = new cModel("hrd_attendance");
    if ($arrAttendanceData = $tblAttendance->find("attendance_date = '".$strStartDate."' AND
                                id_employee IN (SELECT id FROM hrd_employee WHERE employee_id = '".$strEmployeeID."')",
                                null, null, null, null,
                                "id_employee"))
      return $arrAttendanceData['attendance_start']."|".$arrAttendanceData['attendance_finish'];

    return "|";
  }
	
	function getSalarySetByCompany($indexKey = 'id', $sortDesc = true, $idCompany = null){
  	$tblBasicSalarySet = new cModel("hrd_basic_salary_set");
  	$strCompany = "";
  	if (!empty($idCompany)){
  		$strCompany = "id_company = '$idCompany'";	
  	}
    $arrBasicSalarySet = $tblBasicSalarySet->findAll($strCompany, "id, start_date, note, id_company", "start_date DESC", null, 1, $indexKey);
    foreach($arrBasicSalarySet AS $keySet => $arrSet)
    {
    	$companyData = getCompanyName($arrSet['id_company']);
      $arrSetSource[$keySet] = $arrSet['start_date']." - ".$companyData[0]['company_name'];
    }
    if ($sortDesc){
			krsort($arrSetSource);
		}else{
			ksort($arrSetSource);
		}
		return $arrSetSource;	
  }
  
	function getDetailSalaryChangeTable($strEmployeeID = null, $salarySetID = null){
  	global $_REQUEST;
  	global $db;
  	global $strInputSalarySet;
  	$arrFix = array();
  	if (!empty($strEmployeeID) && !empty($salarySetID)){
	  	$strSQL1 ="SELECT a.id, b.company_name, a.start_date FROM 
			hrd_basic_salary_set a LEFT JOIN hrd_company b ON a.id_company = b.id 
			WHERE a.id='$salarySetID' ORDER BY a.start_date DESC LIMIT 1";
			$res1 = $db->execute($strSQL1);
			$row1 = $db->fetchrow($res1);
			$setID = $row1['id'];
			$strSQL  = "SELECT t1.id AS id_employee_key, t4.id AS allowance_type_id, t4.code, t4.amount FROM hrd_employee AS t1 ";
      $strSQL .= "LEFT JOIN ((SELECT id_employee, allowance_code, amount FROM hrd_employee_allowance WHERE id_salary_set = $setID) AS t2 ";
      $strSQL .= " LEFT JOIN (SELECT id, code, active FROM hrd_allowance_type WHERE active = 't') AS t3 ON t2.allowance_code = t3.code) AS t4 ";
      $strSQL .= " ON t1.id = t4.id_employee ";
      $strSQL .= "WHERE t1.id='$strEmployeeID'";
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
  
  function getApprovedOvertimeByCompany($idCompany = null){
  	global $db;
  	$arrayApprovedOvertime = array();
  	if (!empty($idCompany) && $db->connect()){
  		$strSQL = "SELECT sm.id, salary_date, company_name, date_from_overtime, date_thru_overtime ";
    	$strSQL .= "FROM hrd_salary_master sm LEFT JOIN hrd_company comp ON ";
    	$strSQL .= "sm.id_company = comp.id WHERE is_overtime_only is true AND ";
    	$strSQL .= "sm.id_company='$idCompany' AND status >= ".REQUEST_STATUS_APPROVED_2;
    	$resDb = $db->execute($strSQL);
	    while ($rowDb = $db->fetchrow($resDb)) {
	    	$arrApprovedOvertime[$rowDb['id']] = $rowDb['company_name'].' : '.$rowDb['date_from_overtime'].' - '.$rowDb['date_thru_overtime'];
	    }
  	}
  	return $arrApprovedOvertime;
  }
  
?>