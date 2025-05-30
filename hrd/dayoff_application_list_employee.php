<?php
  include_once('../global/session.php');
  include_once('global.php');
  include_once('form_object.php');
  include_once('activity.php');
  include_once('overtime_func.php');
  
  $dataPrivilege = getDataPrivileges(basename($_SERVER['PHP_SELF']), $bolCanView, $bolCanEdit, $bolCanDelete, $bolCanApprove, $bolCanCheck);
  if (!$bolCanView) die(accessDenied($_SERVER['HTTP_REFERER']));
//if (!$bolCanView && $_POST['dataID'] == "") die(getWords('view denied'));



  $bolShow = (isset($_REQUEST['btnShow']));
  $bolPrint = (isset($_REQUEST['btnPrint']) || isset($_REQUEST['btnPrintApproved']) || isset($_REQUEST['btnExcel']));
  $bolPrintReport = (isset($_REQUEST['btnPrintReport']));
    //---- INISIALISASI ----------------------------------------------------
  $strWordsOvertimeApplication      = getWords("overtime application");
  $strWordsDataEntry                = getWords("data entry");
  $strWordsOvertimeList             = getWords("dayoff list");
  $strWordsDayoffRealization        = getWords("dayoff realization");
  $strWordsHolidayOTApproval        = getWords("holiday OT approval");
  $strWordsWorkdayOTApproval        = getWords("workday OT approval");
  $strWordsEmployeeStatus           = getWords("employee status");
  $strWordsActive                   = getWords("active");
  $strWordsOvertimeReport           = getWords("overtime report");
  $strWordsOvertimeDate             = getWords("overtime date");
  $strWordsDateFrom                 = getWords("date from");
  $strWordsDateTo                   = getWords("date thru");
  $strWordsStatus                   = getWords("status");
  $strWordsEmployeeID               = getWords("employee id");
  $strWordsDivision                 = getWords("division");
  $strWordsDepartment               = getWords("department");
  $strWordsSection                  = getWords("section");
  $strWordsSubSection               = getWords("subsection");
  $strWordsGrade                    = getWords("grade");
  $strWordsLevel                    = getWords("level");
  $strWordsBranch                   = getWords("branch");
  $strWordsEmployee                 = getWords("employee");
  $strWordsEmployeeName             = getWords("employee name");
  $strWordsTransport                = getWords("transport");
  $strWordsTransportFee             = getWords("transport fee");
  $strWordsDueDate                  = getWords("due date");
  $strWordsUsedDate                  = getWords("used date");
  $strWordsNote                     = getWords("note");
  $strWordsDelete                   = getWords("delete");
  $strWordsApprove                  = getWords("approve");
  $strWordsShow                     = getWords("show data");
  $strWordsDATE                     = getWords("date");
  $strWordsRequestStatus            = getWords("request status");
  $strWordsPlan                     = getWords("plan");
  $strWordsStart                    = getWords("start");
  $strWordsFinish                   = getWords("finish");
  //$strWordsl1                       = getWords("l1");
  //$strWordsl2                       = getWords("l2");
  //$strWordsl3                       = getWords("l3");
  //$strWordsTotal                    = getWords("total");
  $strWordsID                       = strtoupper("id");
  //$strWordsOutdated                 = getWords("outdated");
  //$strWordsSalary                   = getWords("salary");
  $strWordsApprovedBy               = getWords("approved by");
  $strWordsDiv                      = getWords("div.");
  $strWordsDept                     = getWords("dept.");
  $strWordsSect                     = getWords("sect.");
  $strWordsEarlyOT                  = getWords("early")." ".strtoupper("ot");
  $strWordsAfternoonOT              = getWords("afternoon")." ".strtoupper("ot");
  $strWordsOvertime                 = getWords("overtime");
  $strWordsWorkDay                  = getWords("work day");
  $strWordsHoliday                  = getWords("holiday");
  $strDataDetail                    = "";
  $strHidden                        = "";
  $strInputStatus                   = "";
  $intTotalData                     = 0;
  //----------------------------------------------------------------------
  
  //--- DAFTAR FUNSI------------------------------------------------------data
  // $db = kelas database, $intRows = jumlah baris (return)
  // $strKriteria = query kriteria, $strOrder = query ORder by
  function getData($db, $strDateFrom, $strDateThru, &$intRows, $strKriteria = "",$strOrder = "")
  {
    global $bolCanEdit, $bolCanCheck, $bolCanDelete, $bolCanApprove ;
    global $words;
    global $ARRAY_APPLICATION_STATUS;
    global $ARRAY_REQUEST_STATUS;
    global $bolPrint;
    global $arrUserInfo;
    $intRows = 0;
    $strResult = "";
    // cari jumlah employee
    $strSQL  = "
    SELECT t1.*,
    CASE WHEN holiday_ot = 't' THEN l2 ELSE 0 END AS hol_ot1,
    CASE WHEN holiday_ot = 't' THEN l3 ELSE 0 END AS hol_ot2,
    CASE WHEN holiday_ot = 't' THEN l4 ELSE 0 END AS hol_ot3,
    CASE WHEN holiday_ot = 'f' THEN l1 ELSE 0 END AS work_ot1,
    CASE WHEN holiday_ot = 'f' THEN l2 ELSE 0 END AS work_ot2,
    t2.employee_id,  t2.employee_name, t2.division_code, t2.department_code, t2.section_code, t2.sub_section_code, t2.grade_code, t3.due_date
    FROM hrd_dayoff_application_employee AS t1 
    LEFT JOIN hrd_employee AS t2 ON t1.id_employee = t2.id     
    LEFT JOIN hrd_dayoff_application AS t3 ON t1.id_application = t3.id 
    WHERE (t1.do_date BETWEEN '$strDateFrom' AND '$strDateThru' 
    OR t1.do_date = '$strDateFrom') 
    $strKriteria ORDER BY t1.do_date DESC, division_code, employee_name ASC";
    $resDb = $db->execute($strSQL);
    while ($rowDb = $db->fetchrow($resDb))
    {
      $rowDb['status'] = ($rowDb['status'] == "") ? 0 : $rowDb['status'];
      $strClass = getRequestStatusClass($rowDb['status']);
      $strResult .= "<tr id=\"detail$intRows\" $strClass>\n";
      if (!$bolPrint && !isMe($rowDb['id_employee'])) {
        $intRows++;
        $strResult .= "  <td><input type=checkbox name='chkID$intRows' value=\"" .$rowDb['id']. "\"></td>\n";
      } else {
        $strResult .= "  <td>&nbsp;</td>\n";
      }
      $strResult .= " <td>".pgDateFormat($rowDb['do_date'], "d-m-Y")."</td>";
      $strResult .= " <td>".$rowDb['employee_id']."</td>";
      $strResult .= " <td>".$rowDb['employee_name']."</td>";
      //$strResult .= " <td>".(($rowDb['is_outdated'] == 't') ? "&radic" : "")."</td>";
      //$strResult .= " <td>".$rowDb['salary_month']."-".$rowDb['salary_year']."</td>";
      //$strResult .= " <td>".substr($rowDb['start_early_plan'],0,5)."</td>";
      //$strResult .= " <td>".substr($rowDb['finish_early_plan'],0,5)."</td>";
      //$strResult .= " <td>".substr($rowDb['start_early_actual'],0,5)."</td>";
      //$strResult .= " <td>".substr($rowDb['finish_early_actual'],0,5)."</td>";
      $strResult .= " <td>".substr($rowDb['start_plan'],0,5)."</td>";
      $strResult .= " <td>".substr($rowDb['finish_plan'],0,5)."</td>";
      $strResult .= " <td>".substr($rowDb['start_actual'],0,5)."</td>";
      $strResult .= " <td>".substr($rowDb['finish_actual'],0,5)."</td>";
      //$strResult .= ($rowDb['work_ot1'] == 0) ? " <td>&nbsp</td>" : " <td>".round(($rowDb['work_ot1']/60),2)."</td>";
      //$strResult .= ($rowDb['work_ot2'] == 0) ? " <td>&nbsp</td>" : " <td>".round(($rowDb['work_ot2']/60),2)."</td>";
      //$strResult .= ($rowDb['hol_ot1'] == 0) ? " <td>&nbsp</td>" : " <td>".round(($rowDb['hol_ot1']/60),2)."</td>";
      //$strResult .= ($rowDb['hol_ot2'] == 0) ? " <td>&nbsp</td>" : " <td>".round(($rowDb['hol_ot2']/60),2)."</td>";
      //$strResult .= ($rowDb['hol_ot3'] == 0) ? " <td>&nbsp</td>" : " <td>".round(($rowDb['hol_ot3']/60),2)."</td>";
      //$strResult .= ($rowDb['total_time'] == 0) ? " <td>&nbsp</td>" : " <td>".round(($rowDb['total_time']/60),2)."</td>";
      $strResult .= " <td>&nbsp;".getWords($ARRAY_REQUEST_STATUS[$rowDb['status']])."</td>";
      $strResult .= " <td>&nbsp;".($rowDb['transport'])."</td>";
      $strResult .= " <td>&nbsp;".($rowDb['transport_fee'])."</td>";
      $strResult .= " <td>&nbsp;".($rowDb['due_date'])."</td>";
      $strResult .= " <td>&nbsp;".($rowDb['used_date'])."</td>";
      //$strResult .= " <td>&nbsp;".standardFormat($rowDb['transport_fee'], false, 0)."</td>";
      $strResult .= " <td>&nbsp;".($rowDb['note'])."</td>";
      $strResult .= "<td align=center>&nbsp;";
      if ($rowDb['status'] == REQUEST_STATUS_APPROVED || $bolCanApprove)
        //$strResult .= "    <a href=dayoff_application_edit.php?dataID=" .$rowDb['id_application']. ">" .getWords('use');
		$strResult .= "    <a href=dayoff_realization.php?dataID=" .$rowDb['id_application']. ">" .getWords('use');  
      else
        $strResult .= "    <a href=overtime_denied.php?dataID=" .$rowDb['id']. ">" .getWords('use');
		//$strResult .= "    <a href=overtime_denied.php?dataID=" .$rowDb['id']. ">" .getWords('use'); 
      
	  $strResult .= "<td align=center>&nbsp;";
      if ($rowDb['status'] == REQUEST_STATUS_NEW || $bolCanApprove)
        $strResult .= "    <a href=dayoff_application_edit.php?dataID=" .$rowDb['id_application']. ">" .getWords('edit');
		//$strResult .= "    <a href=dayoff_realization.php?dataID=" .$rowDb['id_application']. ">" .getWords('use');  
      else
        $strResult .= "    <a href=overtime_denied.php?dataID=" .$rowDb['id']. ">" .getWords('edit');
		//$strResult .= "    <a href=overtime_denied.php?dataID=" .$rowDb['id']. ">" .getWords('use'); 
		  

      $strResult .= "    </a>&nbsp;</td>";
      $strResult .= " </tr>\n";
    }
    if (isset($_REQUEST['btnShow'])) {
      $strLogNote = str_replace("'", "", $strKriteria);
      $strLogNote = str_replace("AND", ", ", $strLogNote);
      writeLog(ACTIVITY_SEARCH, MODULE_EMPLOYEE, "date = $strDateFrom to $strDateThru $strLogNote", 0);
    }
    return $strResult;
  } // showData
  
  
  // fungsi untuk menghapus data
  function deleteData($db)
  {
    global $_REQUEST;
    $i = 0;
    foreach ($_REQUEST as $strIndex => $strValue) {
      if (substr($strIndex,0,5) == 'chkID') {
        $strSQL  = "SELECT employee_name, id_employee, employee_id, do_date FROM hrd_dayoff_application_employee AS t1 
                    LEFT JOIN hrd_employee AS t2 ON t1.id_employee = t2.id WHERE t1.id = '$strValue' ";
        $resDb   = $db->execute($strSQL);
        $strSQL  = "DELETE FROM hrd_dayoff_application_employee WHERE id = '$strValue' ";
        $resExec = $db->execute($strSQL);
        if ($rowDb = $db->fetchrow($resDb))
        {
          syncOvertimeApplication($db, $rowDb['do_date'], $rowDb['do_date'], $rowDb['id_employee']);
        }
        $i++;
        writeLog(ACTIVITY_DELETE, MODULE_EMPLOYEE, $rowDb['employee_name']." - ". $rowDb['do_date'], 0);
      }
    }
    
  } //deleteData
  //----------------------------------------------------------------------

  // fungsi untuk verify, check, deny, atau approve
  function changeStatus($db, $intStatus) {
    global $_REQUEST;
    global $_SESSION;


    if (!is_numeric($intStatus)) {
      return false;
    }

    $strUpdate = "";
    $strSQL  = "";
    $strmodified_byID = $_SESSION['sessionUserID'];

    if ($intStatus == REQUEST_STATUS_VERIFIED)
      $strUpdate = "verified_by = '" .$_SESSION['sessionUserID']."', verified_time = now(), ";
    else if ($intStatus == REQUEST_STATUS_CHECKED)
      $strUpdate = "checked_by = '" .$_SESSION['sessionUserID']."', checked_time = now(), ";
    else if ($intStatus == REQUEST_STATUS_APPROVED)
      $strUpdate = "approved_by = '" .$_SESSION['sessionUserID']."', approved_time = now(), ";
    else if ($intStatus == REQUEST_STATUS_DENIED)
      $strUpdate = "denied_by = '" .$_SESSION['sessionUserID']."', denied_time = now(), ";

    foreach ($_REQUEST as $strIndex => $strValue) 
    {
      if (substr($strIndex,0,5) == 'chkID') 
      {
        $strSQLx = "SELECT id_application, employee_name, do_date, status 
                    FROM hrd_dayoff_application_employee AS t1 LEFT JOIN hrd_employee AS t2 ON t1.id_employee = t2.id
                    WHERE t1.id = '$strValue' ";
        $resDb = $db->execute($strSQLx);
        if ($rowDb = $db->fetchrow($resDb)) 
        { 
          //the status should be increasing
          if ($rowDb['status'] < $intStatus)
          {
            $strSQL .= "UPDATE hrd_dayoff_application SET status = '$intStatus' ";
            $strSQL .= "WHERE id = '".$rowDb['id_application'] ."'; ";
            $strSQL .= "UPDATE hrd_dayoff_application_employee SET $strUpdate status = '$intStatus'  ";
            $strSQL .= "WHERE id = '$strValue'; "; 
            writeLog(ACTIVITY_EDIT, MODULE_EMPLOYEE, $rowDb['employee_name']." - ". $rowDb['do_date'], $intStatus);
          }
        }
      }
      $resExec = $db->execute($strSQL);

    }

  } //changeStatus
  
  
  //----MAIN PROGRAM -----------------------------------------------------
  $strInfo = "";
  $strButtonList = "";
  
  $db = new CdbClass;
  if ($db->connect()) {
    getUserEmployeeInfo($db);
    $arrUserList = getAllUserInfo($db);

    scopeData($strDataEmployee, $strDataSubSection, $strDataSection, $strDataDepartment, $strDataDivision, $_SESSION['sessionUserRole'], $arrUserInfo);


    // generate data hidden input dan element form input

    $strInputDateFrom = "<input type=text name=dataDateFrom id=dataDateFrom size=15 maxlength=10 value=".($strDateFrom = getInitialValue("DateFrom", date("Y-m-d"))).">";
    $strInputDateThru = "<input type=text name=dataDateThru id=dataDateThru size=15 maxlength=10 value=".($strDateThru = getInitialValue("DateThru", date("Y-m-d"))).">";
    $strInputStatus = getComboFromArray($ARRAY_REQUEST_STATUS, "dataStatus", ($strDataStatus = getInitialValue("Status")), $strEmptyOption, "style=width:$strDefaultWidthPx");
    $strInputEmployee = "<input type=text name=dataEmployee id=dataEmployee size=15 maxlength=30 value=".($strDataEmployee = getInitialValue("Employee", null, $strDataEmployee))." $strEmpReadonly>";
    $strInputPosition       = getPositionList($db, "dataPosition", ($strDataPosition = getInitialValue("Position")), $strEmptyOption, "", "style=\"width:$strDefaultWidthPx\" ");
    $strInputGrade         = getSalaryGradeList($db, "dataGrade", ($strDataGrade = getInitialValue("Grade")), $strEmptyOption, "", "style=\"width:$strDefaultWidthPx\" ");
    $strInputBranch          = getBranchList($db, "dataBranch", ($strDataBranch = getInitialValue("Branch")), $strEmptyOption, "", "style=\"width:$strDefaultWidthPx\" ");
    $strInputDivision       = getDivisionList($db, "dataDivision", ($strDataDivision = getInitialValue("Division", "", $strDataDivision)), $strEmptyOption, "", "style=\"width:$strDefaultWidthPx\" ". $ARRAY_DISABLE_GROUP['division']);
    $strInputDepartment     = getDepartmentList($db, "dataDepartment", ($strDataDepartment = getInitialValue("Department", "", $strDataDepartment)), $strEmptyOption, "", "style=\"width:$strDefaultWidthPx\" ". $ARRAY_DISABLE_GROUP['department']);
    $strInputSection        = getSectionList($db,"dataSection", ($strDataSection = getInitialValue("Section", "", $strDataSection)), $strEmptyOption, "", "style=\"width:$strDefaultWidthPx\" ". $ARRAY_DISABLE_GROUP['section']);
    $strInputSubSection     = getSubSectionList($db,"dataSubSection", ($strDataSubSection = getInitialValue("SubSection", "", $strDataSubSection)), $strEmptyOption, "", "style=\"width:$strDefaultWidthPx\" ". $ARRAY_DISABLE_GROUP['sub_section']);
    $strInputEmployeeStatus = getEmployeeStatusList("dataEmployeeStatus", ($strDataEmployeeStatus = getInitialValue("EmployeeStatus")),  $strEmptyOption, "style=\"width:$strDefaultWidthPx\"");
     
    //handle user company-access-right
    $strInputCompany = getCompanyList($db, "dataCompany", $strDataCompany, $strEmptyOption2, $strKriteria2, "style=\"width:$strDefaultWidthPx\"");  
    $strInputActive  = getEmployeeActiveList("dataActive", ($strDataActive = getInitialValue("Active")), $strEmptyOption, "style=\"width:$strDefaultWidthPx\" ");
	
    // informasi tanggal kehadiran
    if ($strDateFrom == $strDateThru) {
      $strInfo .= "<br>".strtoupper(pgDateFormat($strDateFrom, "d-M-Y"));
    } else {
      $strInfo .= "<br>".strtoupper(pgDateFormat($strDateFrom, "d-M-Y"));
      $strInfo .= " >> ".strtoupper(pgDateFormat($strDateThru, "d-M-Y"));
    }
    $strHidden .= "<input type=hidden name=dataDateFrom value=$strDateFrom>";
    $strHidden .= "<input type=hidden name=dataDateThru value=$strDateThru>";
    $strHidden .= "<input type=hidden name=dataEmployee value=$strDataEmployee>";
    $strHidden .= "<input type=hidden name=dataDivision value=$strDataDivision>";
    $strHidden .= "<input type=hidden name=dataDepartment value=$strDataDepartment>";
    $strHidden .= "<input type=hidden name=dataSection value=$strDataSection>";
    $strHidden .= "<input type=hidden name=dataSubSection value=$strDataSubSection>";
    $strHidden .= "<input type=hidden name=dataPosition value=$strDataPosition>";
    $strHidden .= "<input type=hidden name=dataGrade value=$strDataGrade>";
    $strHidden .= "<input type=hidden name=dataBranch value=$strDataBranch>";
    $strHidden .= "<input type=hidden name=dataEmployeeStatus value=$strDataEmployeeStatus>";
    $strHidden .= "<input type=hidden name=dataActive value=$strDataActive>";
    $strHidden .= "<input type=hidden name=dataStatus value=$strDataStatus>";

    $strButtonList = generateRoleButtons($bolCanEdit, $bolCanDelete, $bolCanCheck, $bolCanApprove,$bolCanApprove2 );



    if (isset($_REQUEST['btnDelete'])) {
      if ($bolCanDelete) deleteData($db);
    } else if (isset($_POST['btnVerified'])) {
      if ($bolCanEdit) changeStatus($db,REQUEST_STATUS_VERIFIED);
    } else if (isset($_POST['btnChecked'])) {
      if ($bolCanCheck) changeStatus($db,REQUEST_STATUS_CHECKED);
    } else if (isset($_POST['btnApproved'])) {
      if ($bolCanApprove) changeStatus($db,REQUEST_STATUS_APPROVED);
    } else if (isset($_POST['btnDenied'])) {
      if ($bolCanApprove) changeStatus($db,REQUEST_STATUS_DENIED);
    }
    if ($bolCanView) {
      if (validStandardDate($strDateFrom) && validStandardDate($strDateThru) && $bolShow)
      {
        // ------------ GENERATE KRITERIA QUERY,JIKA ADA -------------
        $strKriteria = "";

        
        if ($strDataDivision != "") {
          $strKriteria .= "AND t2.division_code = '$strDataDivision' ";
        }
        if ($strDataDepartment != "") {
          $strKriteria .= "AND t2.department_code = '$strDataDepartment' ";
        }
        if ($strDataSection != "") {
          $strKriteria .= "AND t2.section_code = '$strDataSection' ";
        }
        if ($strDataSubSection != "") {
          $strKriteria .= "AND t2.sub_section_code = '$strDataSubSection' ";
        }

        if ($strDataEmployee != "") {
          $strKriteria .= "AND t2.employee_id = '$strDataEmployee' ";
        }
        if ($strDataActive != "") {
          $strKriteria .= "AND active = '$strDataActive' ";
        }   
        if ($strDataEmployeeStatus != "") {
          $strKriteria .= "AND employee_status = '$strDataEmployeeStatus' ";
        }
        if ($strDataPosition != "") {
          $strKriteria .= "AND t2.position_code = '$strDataPosition' ";
        }
        if ($strDataGrade != "") {
          $strKriteria .= "AND t2.grade_code = '$strDataGrade' ";
        }
        if ($strDataBranch != "") {
          $strKriteria .= "AND t2.branch_code = '$strDataBranch' ";
        }
        if ($strDataStatus != "") {
          $strKriteria .= "AND t1.status = '$strDataStatus' ";
        }
        $strKriteria .= $strKriteriaCompany;
        // tampilkan hanya jika ada permintaan dan data tanggalnya tepat
        
        $strDataDetail = getData($db,$strDateFrom, $strDateThru, $intTotalData, $strKriteria );
        
      } else {
        $strDataDetail = "";
      }
    } else {
      showError("view_denied");
      $strDataDetail = "";
    }
    
  }

 $tbsPage = new clsTinyButStrong ;
  
  //write this variable in every page
  $strPageTitle = $dataPrivilege['menu_name'];
  if (trim($dataPrivilege['icon_file']) == "") $pageIcon = "../images/icons/blank.gif"; 
  else $pageIcon = "../images/icons/".$dataPrivilege['icon_file'];
  if ($bolPrint) 
    $strMainTemplate = getTemplate(str_replace(".php", "_print.html", basename($_SERVER['PHP_SELF'])));  
  else
    $strTemplateFile = getTemplate(str_replace(".php", ".html", basename($_SERVER['PHP_SELF'])));  
  //------------------------------------------------
  //Load Master Template
  $tbsPage->LoadTemplate($strMainTemplate) ;
  $tbsPage->Show() ;
?>