<?
include_once('../global/session.php');
include_once('global.php');
include_once('form_object.php');
include_once('activity.php');
include_once('overtime_func.php');
include_once '../global/email_func.php';
$dataPrivilege = getDataPrivileges(
    basename($_SERVER['PHP_SELF']),
    $bolCanView,
    $bolCanEdit,
    $bolCanDelete,
    $bolCanApprove,
    $bolCanCheck,
    $bolCanAcknowledge,
    $bolCanApprove2
);
if (!$bolCanView) {
    die(accessDenied($_SERVER['HTTP_REFERER']));
}
$bolPrint = (isset($_REQUEST['btnPrint']) || isset($_REQUEST['btnPrintApproved']) || isset($_REQUEST['btnExcel']));
$bolShow = (isset($_REQUEST['btnShow']));
$bolPrintReport = (isset($_REQUEST['btnPrintReport']));
//---- INISIALISASI ----------------------------------------------------
$strWordsOvertimeApplication = getWords("overtime application");
$strWordsDataEntry = getWords("data entry");
$strWordsOvertimeList = getWords("overtime list");
$strWordsHolidayOTApproval = getWords("holiday OT approval");
$strWordsWorkdayOTApproval = getWords("workday OT approval");
$strWordsEmployeeStatus = getWords("employee status");
$strWordsActive = getWords("active");
$strWordsOvertimeReport = getWords("overtime report");
$strWordsOvertimeDate = getWords("overtime date");
$strWordsDateFrom = getWords("date from");
$strWordsDateTo = getWords("date thru");
$strWordsStatus = getWords("status");
$strWordsEmployeeID = getwords("n i k");
$strWordsDivision = getWords("division");
$strWordsDepartment = getWords("department");
$strWordsSection = getWords("section");
$strWordsSubSection = getWords("subsection");
$strWordsGrade = getWords("grade");
$strWordsLevel = getWords("level");
$strWordsBranch = getWords("branch");
$strWordsEmployee = getWords("employee");
$strWordsEmployeeName = getWords("employee name");
$strWordsPosition = getWords("position");
$strWordsBranch = getWords("branch");
// $strWordsIDEmp                    = getWords("id employee");
$strWordsTransport = getWords("transport");
$strWordsFee = getWords("fee");
$strWordsNoteEmp = getWords("note employee");
$strWordsNoteApp = getWords("note application");
$strWordsDelete = getWords("delete");
$strWordsApprove = getWords("approve");
$strWordsShow = getWords("show data");
$strWordsDATE = getWords("date");
$strWordsRequestStatus = getWords("request status");
$strWordsPlan = getWords("plan");
$strWordsStart = getWords("start");
$strWordsFinish = getWords("finish");
$strWordsl1 = getWords("l1");
$strWordsl2 = getWords("l2");
$strWordsl3 = getWords("l3");
$strWordsl5 = getWords("lShift");
$strWordsTotal = getWords("total");
$strWordsID = strtoupper("id");
$strWordsOutdated = getWords("outdated");
$strWordsSalary = getWords("salary");
$strWordsApprovedBy = getWords("approved by");
$strWordsDiv = getWords("div.");
$strWordsDept = getWords("dept.");
$strWordsSect = getWords("sect.");
$strWordsEarlyOT = getWords("early OT");
$strWordsAfternoonOT = getWords("afternoon OT");
$strWordsOvertime = getWords("overtime");
$strWordsWorkDay = getWords("work day");
$strWordsHoliday = getWords("holiday");
$strCompany = getWords("company");
$strWordsNew = getWords("new");
$strWordsDenied = getWords("denied");
$strWordsChecked = getWords("checked");
$strWordsApproved = getWords("approved");
$strWordsApproved2 = getWords("approved 2");
$strWordsFinished = getWords("finished");
$strDataDetail = "";
$strHidden = "";
$strInputStatus = "";
$intTotalData = 0;
//----------------------------------------------------------------------
//--- DAFTAR FUNSI------------------------------------------------------
// fungsi untuk menampilkan data
// $db = kelas database, $intRows = jumlah baris (return)
// $strKriteria = query kriteria, $strOrder = query ORder by
function getData($db, $strDateFrom, $strDateThru, &$intRows, $strKriteria = "", $strOrder = "")
{
    global $bolCanEdit, $bolCanCheck, $bolCanDelete, $bolCanApprove, $bolCanApprove2;
    global $words;
    global $ARRAY_REQUEST_STATUS;
    global $bolPrint;
    global $bolShow;
    global $arrUserInfo;
    $intRows = 0;
    $strResult = "";
    // cari jumlah employee
    $strSQL = "SELECT
                    t1.*, CASE
                WHEN holiday_ot = 't' THEN
                    l2
                ELSE
                    0
                END AS hol_ot1,
                 CASE
                WHEN holiday_ot = 't' THEN
                    l3
                ELSE
                    0
                END AS hol_ot2,
                 CASE
                WHEN holiday_ot = 't' THEN
                    l4
                ELSE
                    0
                END AS hol_ot3,
                 CASE
                WHEN holiday_ot = 'f' THEN
                    l1
                ELSE
                    0
                END AS work_ot1,
                 CASE
                WHEN holiday_ot = 'f' THEN
                    l2
                ELSE
                    0
                END AS work_ot2,
                 t1.l5,
                 t2.employee_id,
                 t2.employee_name,
                 t2.division_code,
                 t2.department_code,
                 t4.department_name,
                 t2.section_code,
                 t2.branch_code,
                 t2.sub_section_code,
                 t2.grade_code,
                 t5.position_name,
                 t3.is_outdated,
                 t3.salary_month,
                 t3.salary_year,
                 t3.note AS note2
                FROM
                    hrd_overtime_application_employee AS t1
                LEFT JOIN hrd_employee AS t2 ON t1.id_employee = t2. ID
                LEFT JOIN hrd_overtime_application AS t3 ON t1.id_application = t3. ID
                LEFT JOIN hrd_department AS t4 ON t2.department_code = t4.department_code
                LEFT JOIN hrd_position AS t5 ON t2.position_code = t5.position_code
                WHERE
                    (
                        t1.overtime_date BETWEEN '$strDateFrom'
                        AND '$strDateThru'
                        OR t1.overtime_date = '$strDateFrom'
                    ) $strKriteria
                ORDER BY
                    t1.overtime_date DESC,
                    division_code,
                    employee_name ASC";
    $resDb = $db->execute($strSQL);
    $counter = 0;
    while ($rowDb = $db->fetchrow($resDb)) {
        $counter++;
        $rowDb['status'] = ($rowDb['status'] == "") ? 0 : $rowDb['status'];
        $params = $rowDb;
        $params['id'] = $params['id_application'];
        $strClass = getRequestStatusClass($rowDb['status']);
        $strResult .= "<tr id=\"detail$intRows\" $strClass>\n";
        if (!$bolPrint && !isMe($rowDb['id_employee'])) {
            $intRows++;
            $strResult .= "  <td><input type=checkbox name='chkID$intRows' value=\"" . $rowDb['id'] . "\"></td>\n";
        }
        //else {
        //  $strResult .= "  <td>&nbsp;</td>\n";
        //}
        $strResult .= " <td>" . pgDateFormat($rowDb['overtime_date'], "d-m-Y") . "</td>";
        $strResult .= " <td>" . $rowDb['employee_id'] . "</td>";
        $strResult .= " <td>" . $rowDb['employee_name'] . "</td>";
        $strResult .= " <td>" . $rowDb['department_name'] . "</td>";
        $strResult .= " <td>" . $rowDb['sub_section_code'] . "</td>";
        $strResult .= " <td>" . $rowDb['branch_code'] . "</td>";
        $strResult .= " <td>" . $rowDb['position_name'] . "</td>";
        $strResult .= " <td>" . (($rowDb['is_outdated'] == 't') ? "&radic;" : "") . "</td>";
        $strResult .= " <td>" . $rowDb['salary_month'] . "-" . $rowDb['salary_year'] . "</td>";
        $strResult .= " <td>" . substr($rowDb['start_early_plan'], 0, 5) . "</td>";
        $strResult .= " <td>" . substr($rowDb['finish_early_plan'], 0, 5) . "</td>";
        $strResult .= " <td>" . substr($rowDb['start_early_actual'], 0, 5) . "</td>";
        $strResult .= " <td>" . substr($rowDb['finish_early_actual'], 0, 5) . "</td>";
        $strResult .= " <td>" . substr($rowDb['start_plan'], 0, 5) . "</td>";
        $strResult .= " <td>" . substr($rowDb['finish_plan'], 0, 5) . "</td>";
        $strResult .= " <td>" . substr($rowDb['start_actual'], 0, 5) . "</td>";
        $strResult .= " <td>" . substr($rowDb['finish_actual'], 0, 5) . "</td>";
        $strResult .= ($rowDb['work_ot1'] == 0) ? " <td>&nbsp</td>" : " <td>" . round(
                ($rowDb['work_ot1'] / 60),
                2
            ) . "</td>";
        $strResult .= ($rowDb['work_ot2'] == 0) ? " <td>&nbsp</td>" : " <td>" . round(
                ($rowDb['work_ot2'] / 60),
                2
            ) . "</td>";
        $strResult .= ($rowDb['hol_ot1'] == 0) ? " <td>&nbsp</td>" : " <td>" . round(
                ($rowDb['hol_ot1'] / 60),
                2
            ) . "</td>";
        $strResult .= ($rowDb['hol_ot2'] == 0) ? " <td>&nbsp</td>" : " <td>" . round(
                ($rowDb['hol_ot2'] / 60),
                2
            ) . "</td>";
        $strResult .= ($rowDb['hol_ot3'] == 0) ? " <td>&nbsp</td>" : " <td>" . round(
                ($rowDb['hol_ot3'] / 60),
                2
            ) . "</td>";
        $strResult .= ($rowDb['l5'] == 0) ? " <td>&nbsp</td>" : " <td>" . round(($rowDb['l5'] / 60), 2) . "</td>";
        $strResult .= ($rowDb['total_time'] == 0) ? " <td>&nbsp</td>" : " <td>" . round(
                ($rowDb['total_time'] / 60),
                2
            ) . "</td>";
        $strResult .= " <td>&nbsp;" . getWords($ARRAY_REQUEST_STATUS[$rowDb['status']]) . "</td>";
        //      $strResult .= " <td>&nbsp;".($rowDb['transport'])."</td>";
        //$strResult .= " <td>&nbsp;".standardFormat($rowDb['transport_fee'], false, 0)."</td>";
        $strResult .= " <td>&nbsp;" . ($rowDb['note']) . "</td>";
        $strResult .= " <td>&nbsp;" . ($rowDb['note2']) . "</td>";
        if (!$bolPrint) {
            $strResult .= " <td>&nbsp;" . pgDateFormat($rowDb['created'], "d-m-Y") . "</td>";
            $strResult .= " <td>" . getUserName($db, $rowDb['created_by']) . "</td>";
            $strResult .= "<td>&nbsp;" . printGlobalEditLink(["record" => $params]) . "</td>";
            $strResult .= "<td align=\"center\">" . printShowLink($rowDb, $counter) . "</td>";
        }
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
        if (substr($strIndex, 0, 5) == 'chkID') {
            $strSQL = "SELECT employee_name, id_employee, employee_id, overtime_date FROM hrd_overtime_application_employee AS t1
                    LEFT JOIN hrd_employee AS t2 ON t1.id_employee = t2.id WHERE t1.id = '$strValue' ";
            $resDb = $db->execute($strSQL);
            $strSQL = "DELETE FROM hrd_overtime_application_employee WHERE id = '$strValue' ";
            $resExec = $db->execute($strSQL);
            if ($rowDb = $db->fetchrow($resDb)) {
                syncOvertimeApplication($db, $rowDb['overtime_date'], $rowDb['overtime_date'], $rowDb['id_employee']);
            }
            $i++;
            writeLog(ACTIVITY_DELETE, MODULE_EMPLOYEE, $rowDb['employee_name'] . " - " . $rowDb['overtime_date'], 0);
        }
    }
} //deleteData
//----------------------------------------------------------------------
// fungsi untuk verify, check, deny, atau approve
function changeStatus($db, $intStatus)
{
    global $_REQUEST;
    global $_SESSION;
    if (!is_numeric($intStatus)) {
        return false;
    }
    $strUpdate = "";
    $strSQL = "";
    $strmodified_byID = $_SESSION['sessionUserID'];
    $strUpdate = getStatusUpdateString($intStatus);
    foreach ($_REQUEST as $strIndex => $strValue) {
        if (substr($strIndex, 0, 5) == 'chkID') {
            $strSQLx = "SELECT t1.id_application, employee_name, t1.id_employee, t1.overtime_date, t1.status, t1.note AS detail_note, t3.note
                    FROM hrd_overtime_application_employee AS t1 LEFT JOIN hrd_employee AS t2 ON t1.id_employee = t2.id
                    LEFT JOIN hrd_overtime_application AS t3 ON t1.id_application = t3.id
                    WHERE t1.id = '$strValue' ";
            $resDb = $db->execute($strSQLx);
            if ($rowDb = $db->fetchrow($resDb)) {
                $strNote = !empty($rowDb['detail_note']) ? $rowDb['detail_note'] : $rowDb['note'];
                $strBody = "";
                $strBody .= "Name: " . getEmployeeNameEmail($rowDb['id_employee']) . "<br>";
                $strBody .= "Overtime Date: " . $rowDb['overtime_date'] . "<br>";
                $strBody .= "Note: " . $strNote . "<br>";
                $strBody .= "http://192.168.0.15/devosa";
                $strSubject = getSubject($intStatus, 'Overtime', getEmployeeIDEmail($rowDb['id_employee']));
                $strBody = getBody($intStatus, 'Overtime', $strBody, $strmodified_byID);
                //the status should be increasing
                if (isProcessable($rowDb['status'], $intStatus)) {
                    {
                        sendMail($strSubject, $strBody);
                        $strSQL .= "UPDATE hrd_overtime_application SET status = '$intStatus' ";
                        $strSQL .= "WHERE id = '" . $rowDb['id_application'] . "'; ";
                        $strSQL .= "UPDATE hrd_overtime_application_employee SET $strUpdate status = '$intStatus'  ";
                        $strSQL .= "WHERE id = '$strValue'; ";
                        writeLog(
                            ACTIVITY_EDIT,
                            MODULE_EMPLOYEE,
                            $rowDb['employee_name'] . " - " . $rowDb['overtime_date'],
                            $intStatus
                        );
                    }
                }
            }
        }
        $resExec = $db->execute($strSQL);
    }
} //changeStatus
function printShowLink($record = null, $counter = 0)
{
    $strResult = "";
    if (!empty($record) && count($record)) {
        global $arrUserList;
        // tambahkan info record info
        $strDiv = "<div id='detailRecord$counter' style=\"display:none\">\n";
        $strDiv .= "<strong>" . $record['employee_id'] . "-" . $record['employee_name'] . "</strong><br>\n";
        $strDiv .= getWords("created") . ": " . substr($record['created'], 0, 19) . " ";
        $strDiv .= (isset($arrUserList[$record['created_by']])) ? $arrUserList[$record['created_by']]['name'] . "<br>" : "<br>";
        $strDiv .= getWords("last modified") . ": " . substr($record['modified'], 0, 19) . " ";
        $strDiv .= (isset($arrUserList[$record['modified_by']])) ? $arrUserList[$record['modified_by']]['name'] . "<br>" : "<br>";
        $strDiv .= getWords("checked") . ": " . substr($record['checked_time'], 0, 19) . " ";
        $strDiv .= (isset($arrUserList[$record['checked_by']])) ? $arrUserList[$record['checked_by']]['name'] . "<br>" : "<br>";
        $record['approved1'] = !empty($record['approved1']) ? $record['approved1'] : $record['approved_time'];
        $record['approved1_by'] = !empty($record['approved1_by']) ? $record['approved1_by'] : $record['approved_by'];
        $strDiv .= getWords("approved") . ": " . substr($record['approved1'], 0, 19) . " ";
        $strDiv .= (isset($arrUserList[$record['approved1_by']])) ? $arrUserList[$record['approved1_by']]['name'] . "<br>" : "<br>";
        $strDiv .= getWords("approved 2") . ": " . substr($record['approved2_time'], 0, 19) . " ";
        $strDiv .= (isset($arrUserList[$record['approved2_by']])) ? $arrUserList[$record['approved2_by']]['name'] . "<br>" : "<br>";
        $strDiv .= getWords("denied") . ": " . substr($record['denied_time'], 0, 19) . " ";
        $strDiv .= (isset($arrUserList[$record['denied_by']])) ? $arrUserList[$record['denied_by']]['name'] . "<br>" : "<br>";
        $strDiv .= "</div>\n";
        $strResult .= $strDiv . "<a href=\"javascript:openViewWindowByContentId('Record Information', 'detailRecord$counter', 400, 150)\" title=\"" . getWords(
                "show record info"
            ) . "\">" . getWords("show") . "</a>";
    }
    return $strResult;
}

//----MAIN PROGRAM -----------------------------------------------------
$strInfo = "";
$strButtonList = "";
$strDefaultDate = date("Y-m-d");
$arrDateThisMonth = explode("-", $strDefaultDate);
$strTempDate = getNextDateNextMonth($strDefaultDate, -1);
$arrDatePrevMonth = explode("-", $strTempDate);
$arrDt = explode("-", $strTempDate);
$dtFrom = $arrDatePrevMonth[0] . '-' . $arrDatePrevMonth[1] . '-25';
$dtThru = $arrDateThisMonth[0] . '-' . $arrDateThisMonth[1] . '-24';
$db = new CdbClass;
if ($db->connect()) {
    getUserEmployeeInfo($db);
    $arrUserList = getAllUserInfo($db);
    //    //$bolCanApprove2 = isEligibleApprove2($db);
    scopeData(
        $strDataEmployee,
        $strDataSubSection,
        $strDataSection,
        $strDataDepartment,
        $strDataDivision,
        $_SESSION['sessionUserRole'],
        $arrUserInfo,
        $strDataBranch
    );
    $strEmpReadonly = ($strDataEmployee != "") ? "readonly" : "";
    // generate data hidden input dan element form input
    $strInputDateFrom = "<input type=text name=dataDateFrom id=dataDateFrom size=15 maxlength=10 value=" . ($strDateFrom = getInitialValue(
            "DateFrom",
            $dtFrom
        )) . ">";
    $strInputDateThru = "<input type=text name=dataDateThru id=dataDateThru size=15 maxlength=10 value=" . ($strDateThru = getInitialValue(
            "DateThru",
            $dtThru
        )) . ">";
    $strInputStatus = getComboFromArray(
        $ARRAY_REQUEST_STATUS,
        "dataStatus",
        ($strDataStatus = getInitialValue("Status")),
        $strEmptyOption,
        "style=width:$strDefaultWidthPx"
    );
    $strInputEmployee = "<input type=text name=dataEmployee id=dataEmployee size=15 maxlength=30 value=" . ($strDataEmployee = getInitialValue(
            "Employee",
            $strDataEmployee
        )) . " $strEmpReadonly>";
    $strInputPosition = getPositionList(
        $db,
        "dataPosition",
        ($strDataPosition = getInitialValue("Position")),
        $strEmptyOption,
        "",
        "style=\"width:$strDefaultWidthPx\" "
    );
    $strInputGrade = getSalaryGradeList(
        $db,
        "dataGrade",
        ($strDataGrade = getInitialValue("Grade")),
        $strEmptyOption,
        "",
        "style=\"width:$strDefaultWidthPx\" "
    );
    $strInputBranch = getBranchList(
        $db,
        "dataBranch",
        ($strDataBranch = getInitialValue("Branch", $strDataBranch)),
        $strEmptyOption,
        "",
        "style=\"width:$strDefaultWidthPx\" " . $ARRAY_DISABLE_GROUP['branch']
    );
    $strInputDivision = getDivisionList(
        $db,
        "dataDivision",
        ($strDataDivision = getInitialValue("Division", $strDataDivision)),
        $strEmptyOption,
        "",
        "style=\"width:$strDefaultWidthPx\" " . $ARRAY_DISABLE_GROUP['division']
    );
    $strInputDepartment = getDepartmentList(
        $db,
        "dataDepartment",
        ($strDataDepartment = getInitialValue("Department", $strDataDepartment)),
        $strEmptyOption,
        "",
        "style=\"width:$strDefaultWidthPx\" " . $ARRAY_DISABLE_GROUP['department']
    );
    $strInputSection = getSectionList(
        $db,
        "dataSection",
        ($strDataSection = getInitialValue("Section", $strDataSection)),
        $strEmptyOption,
        "",
        "style=\"width:$strDefaultWidthPx\" " . $ARRAY_DISABLE_GROUP['section']
    );
    $strInputSubSection = getSubSectionList(
        $db,
        "dataSubSection",
        ($strDataSubSection = getInitialValue("SubSection", $strDataSubSection)),
        $strEmptyOption,
        "",
        "style=\"width:$strDefaultWidthPx\" " . $ARRAY_DISABLE_GROUP['sub_section']
    );
    $strInputEmployeeStatus = getEmployeeStatusList(
        "dataEmployeeStatus",
        ($strDataEmployeeStatus = getInitialValue("EmployeeStatus")),
        $strEmptyOption,
        "style=\"width:$strDefaultWidthPx\""
    );
    //handle user company-access-right
    $strInputCompany = getCompanyList(
        $db,
        "dataCompany",
        $strDataCompany,
        $strEmptyOption2,
        $strKriteria2,
        "style=\"width:$strDefaultWidthPx\""
    );
    $strInputActive = getEmployeeActiveList(
        "dataActive",
        ($strDataActive = getInitialValue("Active")),
        $strEmptyOption,
        "style=\"width:$strDefaultWidthPx\" "
    );
    // informasi tanggal kehadiran
    if ($strDateFrom == $strDateThru) {
        $strInfo .= "<br>" . strtoupper(pgDateFormat($strDateFrom, "d-M-Y"));
    } else {
        $strInfo .= "<br>" . strtoupper(pgDateFormat($strDateFrom, "d-M-Y"));
        $strInfo .= " >> " . strtoupper(pgDateFormat($strDateThru, "d-M-Y"));
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
    $strButtonList = generateRoleButtons($bolCanEdit, $bolCanDelete, $bolCanCheck, $bolCanApprove, $bolCanApprove2);
    if (isset($_REQUEST['btnDelete'])) {
        if ($bolCanDelete) {
            deleteData($db);
        }
    } else {
        callChangeStatus();
    }
    if ($bolCanView) {
        if (validStandardDate($strDateFrom) && validStandardDate($strDateThru)) {
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
                // die('kajsdkaskdah');
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
            $strDataDetail = getData($db, $strDateFrom, $strDateThru, $intTotalData, $strKriteria);
        } else {
            $strDataDetail = "";
        }
    } else {
        showError("view_denied");
        $strDataDetail = "";
    }
}
$tbsPage = new clsTinyButStrong;
//write this variable in every page
$strPageTitle = getWords($dataPrivilege['menu_name']);
if (trim($dataPrivilege['icon_file']) == "") {
    $pageIcon = "../images/icons/blank.gif";
} else {
    $pageIcon = "../images/icons/" . $dataPrivilege['icon_file'];
}
if ($bolPrint) {
    $strMainTemplate = getTemplate(str_replace(".php", "_print.html", basename($_SERVER['PHP_SELF'])));
} else {
    $strTemplateFile = getTemplate(str_replace(".php", ".html", basename($_SERVER['PHP_SELF'])));
}
//------------------------------------------------
if (isset($_REQUEST['btnExcel'])) {
    $strDataDetail = getData($db, $strDateFrom, $strDateThru, $intTotalData, $strKriteria);
    // ambil data CSS-nya
    if (file_exists("../css/bw.css")) {
        $strStyle = "../css/bw.css";
    }
    $strPrintCss = "";
    $strPrintInit = "";
    headeringExcel("overtime.xls");
}
//Load Master Template
//$tbsPage->LoadTemplate($strMainTemplate) ;
$tbsPage->LoadTemplate("$strMainTemplate");
$tbsPage->Show();
?>
