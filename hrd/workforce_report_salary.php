<?php
include_once('../global/session.php');
include_once('global.php');
include_once('form_object.php');
include_once('../includes/dbclass/dbclass.php');
include_once('../includes/datagrid2/datagrid.php');
include_once('../global/libchart/classes/libchart.php');
$dataPrivilege = getDataPrivileges("workforce_report.php", $bolCanView, $bolCanEdit, $bolCanDelete, $bolCanApprove);
if (!$bolCanView) {
    die(accessDenied($_SERVER['HTTP_REFERER']));
}
//$tblEmployee = new cModel("hrd_employee", getWords("employee"));
$bolPrint = (isset($_REQUEST['btnPrint']) || isset($_REQUEST['btnPrintAll']) || isset($_REQUEST['btnExportXLS']));
$bolLimit = false;//(getRequestValue('dataLimit', 0) == 1);
//---- INISIALISASI ----------------------------------------------------
$strWordsListOfNewEmployee = getWords("list of new employee");
$strWordsCompany = getWords("company");
$strWordsDivision = getWords("division");
$strWordsDepartment = getWords("department");
$strWordsSection = getWords("section");
$strWordsSubSection = getWords("sub section");
$strWordsDate = getWords("date");
$strDataDetail = "";
$strDataDate = "";
$strDivisionName = "";
$strDepartmentName = "";
$strSectionName = "";
$strSubSectionName = "";
$strStyle = "";
$strHidden = "";
$intTotalData = 0; // default, tampilan dibatasi (paging)
$strSearchDisplay = "display:none";
$strWordsNoOfPeople = getWords("no of people");
$strWordsTitle = getWords("salary distribution");
$strWordsTotal = getWords("total");
$strDataMale = 0;
$strDataFemale = 0;
$strDataTotal = 0;
$strChartPath = "";
$strDirPath = "chartimg";
$strFilePath = "$strWordsTitle.png";
$strWordsDate = getWords("salary date");
$strDataInterval = 10;
$strResultInTable = "";
//--- DAFTAR FUNSI------------------------------------------------------
// fungsi untuk menampilkan data
// $db = kelas database,
// $strDataDate adalah tanggal yang diinginkan
// $strKriteria = query kriteria, $strOrder = query ORder by
// $intStart = record mulai, $bolLimit = dibatasi sesuai limit global
function getData($strKriteria)
{
    global $tblEmployee;
    global $strDataTotal;
    global $strChartPath;
    global $strPageTitle;
    global $strDirPath;
    global $strFilePath;
    global $strDate;
    global $strPageTitle;
    global $strDataInterval;
    global $strResultInTable;
    global $db;
    //getmaxSalary
    $strSQL = "SELECT MAX(total_net) AS max FROM hrd_salary_detail WHERE id_salary_master = $strDate";
    $maxSalary = $db->execute($strSQL);
    $maxSalary = $db->fetchrow($maxSalary);
    $maxSalary = $maxSalary["max"];

    $strDataPoints = '';
    for ($i = 0; $i < ($maxSalary + 1) / $strDataInterval; $i++) {
        $strSQL = "SELECT COUNT(*) FROM hrd_employee AS t1
		LEFT JOIN hrd_salary_detail AS t2 ON t1.id = t2.id_employee ";
        $minRange = $i * $strDataInterval;
        $maxRange = $minRange + $strDataInterval - 1;
        $strExecuteKriteria = " AND total_net BETWEEN $minRange AND $maxRange ";
        $strExecuteSQL = $strSQL . $strKriteria . $strExecuteKriteria;
        $numOfEmployee = $db->execute($strExecuteSQL);
        $numOfEmployee = $db->fetchrow($numOfEmployee);
        $numOfEmployee = $numOfEmployee["count"];
        if ($numOfEmployee == 0) {
            continue;
        }
        if (isset($strDataPoints) && $strDataPoints !== '') {
            $strDataPoints .= ',';
        }
        $strDataPoints .= '{y: ' . $numOfEmployee . ', label: "' . $minRange . '-'. $maxRange .'"}';
    }
    $strSQL = "SELECT COUNT(*) FROM hrd_employee AS t1
	LEFT JOIN hrd_salary_detail AS t2 ON t1.id = t2.id_employee ";
    $strExecuteKriteria = " AND total_net IS NULL ";
    $strExecuteSQL = $strSQL . $strKriteria . $strExecuteKriteria;
    $numOfEmployee = $db->execute($strExecuteSQL);
    $numOfEmployee = $db->fetchrow($numOfEmployee);
    $numOfEmployee = $numOfEmployee["count"];

    $strDataPoints .= ', {y: ' . $numOfEmployee . ', label: " No Salary Data "}';

    $strGraphic = '<script type="text/javascript">
                   window.onload = function () {
                   var chart = new CanvasJS.Chart("chartContainer",
                   {
                    title:{
                     text: "Salary Graphical Report"
                    },
                    axisY: {
                     title: "Amount",
                     interval: 2
                    },
                    axisX: {
                     labelMaxWidth: 80,
                     interval: 1
                    },
                    theme: "theme2",
                    data: [{
                     type: "column",
                     dataPoints: [' . $strDataPoints . ']
                    }]
                   });
                   chart.render();
                   }
                   </script>';
    return $strGraphic;
}

//----------------------------------------------------------------------
//----MAIN PROGRAM -----------------------------------------------------
$db = new CdbClass;
if ($db->connect()) {
    getUserEmployeeInfo();
    // ------ AMBIL DATA KRITERIA -------------------------
    (isset($_REQUEST['dataSalaryDate'])) ? $strDate = $_REQUEST['dataSalaryDate'] : $strDate = "";
    (isset($_REQUEST['dataDivision'])) ? $strDataDivision = $_REQUEST['dataDivision'] : $strDataDivision = "";
    (isset($_REQUEST['dataDepartment'])) ? $strDataDepartment = $_REQUEST['dataDepartment'] : $strDataDepartment = "";
    (isset($_REQUEST['dataSection'])) ? $strDataSection = $_REQUEST['dataSection'] : $strDataSection = "";
    (isset($_REQUEST['dataSubsection'])) ? $strDataSubSection = $_REQUEST['dataSubsection'] : $strDataSubSection = "";
    (isset($_REQUEST['dataInterval']) && $_REQUEST['dataInterval'] >= 500) ? $strDataInterval = $_REQUEST['dataInterval'] : $strDataInterval = 1000;
    $strHidden = "<input type=\"hidden\" name=\"dataSalaryDate\"  value=\"$strDate\">";
    //$strHidden .= "<input type=\"hidden\" name=\"dataDateThru\"  value=\"$strDateThru\">";
    $strHidden .= "<input type=\"hidden\" name=\"dataDivision\" value=\"$strDataDivision\">";
    $strHidden .= "<input type=\"hidden\" name=\"dataDepartment\" value=\"$strDataDepartment\">";
    $strHidden .= "<input type=\"hidden\" name=\"dataSection\"   value=\"$strDataSection\">";
    $strHidden .= "<input type=\"hidden\" name=\"dataSubSection\"   value=\"$strDataSubSection\">";
    // ------------ GENERATE KRITERIA QUERY,JIKA ADA -------------
    $strDataEployee = "";
    scopeData(
        $strDataEmployee,
        $strDataSubSection,
        $strDataSection,
        $strDataDepartment,
        $strDataDivision,
        $_SESSION['sessionUserRole'],
        $arrUserInfo
    );
    $strKriteria = "WHERE 1=1 ";
    if ($strDataDivision != "") {
        $strSQL = "SELECT division_name FROM hrd_division WHERE division_code = '$strDataDivision' ";
        $resDb = $db->execute($strSQL);
        if ($rowDb = $db->fetchrow($resDb)) {
            $strDivisionName = $rowDb['division_name'];
        }
        $strKriteria .= "AND t1.division_code = '$strDataDivision' ";
    }
    if ($strDataDepartment != "") {
        $strSQL = "SELECT department_name FROM hrd_department WHERE department_code = '$strDataDepartment' ";
        $resDb = $db->execute($strSQL);
        if ($rowDb = $db->fetchrow($resDb)) {
            $strDepartmentName = $rowDb['department_name'];
        }
        $strKriteria .= "AND t1.department_code = '$strDataDepartment' ";
    }
    if ($strDataSection != "") {
        $strSQL = "SELECT section_name FROM hrd_section WHERE section_code = '$strDataSection' ";
        $resDb = $db->execute($strSQL);
        if ($rowDb = $db->fetchrow($resDb)) {
            $strSectionName = $rowDb['section_name'];
        }
        $strKriteria .= "AND t1.section_code = '$strDataSection' ";
    }
    if ($strDataSubSection != "") {
        $strSQL = "SELECT sub_section_name FROM hrd_sub_section WHERE sub_section_code = '$strDataSubSection' ";
        $resDb = $db->execute($strSQL);
        if ($rowDb = $db->fetchrow($resDb)) {
            $strSubSectionName = $rowDb['sub_section_name'];
        }
        $strKriteria .= "AND t1.sub_section_code = '$strDataSubSection' ";
    }
    //$strDate untuk join HRD SALARY DETAIL DGN HRD SALARY MASTER
    $strKriteria .= "AND id_salary_master = '$strDate' ";
    //$strKriteria .= "AND join_date BETWEEN '$strDate' AND '$strDateThru'";
    $strKriteriaCompany = str_replace("id", "t1.id", $strKriteriaCompany);
    $strKriteria .= $strKriteriaCompany;
    if ($bolCanView) {
        $strGraphic = getData($strKriteria);
    } else {
        showError("view_denied");
        $strDataDetail = "";
    }
    $strSQL = "SELECT salary_date FROM hrd_salary_master WHERE id = $strDate";
    $strDataDate = $db->execute($strSQL);
    $strDataDate = $db->fetchrow($strDataDate);
    $strDataDate = $strDataDate["salary_date"];
    $strDataDate = pgDateFormat($strDataDate, "d F Y");// ." -". pgDateFormat($strDateThru, "d F Y");
}
$tbsPage = new clsTinyButStrong;
//write this variable in every page
$strPageTitle = 'Salary Graphical Report';
$strTemplateFile = getTemplate(str_replace(".php", ".html", basename($_SERVER['PHP_SELF'])));
//------------------------------------------------
//Load Master Template
$tbsPage->LoadTemplate($strMainTemplate);
$tbsPage->Show();
?>