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
$strWordsTitle = getWords("age");
$strWordsTotal = getWords("total");
$strDataMale = 0;
$strDataFemale = 0;
$strDataTotal = 0;
$strChartPath = "";
$strDirPath = "chartimg";
$strFilePath = strtolower($strWordsTitle) . ".png";
$strWordsDate = getWords("salary date");
$strDataInterval = 10;
$strResultInTable = "";

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
    (isset($_REQUEST['dataInterval']) && $_REQUEST['dataInterval'] > 0) ? $strDataInterval = $_REQUEST['dataInterval'] : $strDataInterval = 10;
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
	
}

$tbsPage = new clsTinyButStrong;

$arrResult = array();
		$datefrom 	= $_GET['datefrom'];
		$dateend  	= $_GET['dateend'];
		$division 	= $_GET['dataDivision'];
		$department	= $_GET['dataDepartment'];
	    $strSQL  = "select sum(total_time), ot_month, ot_year from
					(
					Select total_time, extract (MONTH from overtime_date) as ot_month, extract (YEAR from overtime_date) as ot_year from hrd_overtime_application_employee 
					where overtime_date between '$datefrom' and '$dateend'
					) as totals
					group by ot_month,ot_year order by ot_month asc";
	    $resDb = $db->execute($strSQL);
		$i = 0;
	    while ($rowDb = $db->fetchrow($resDb))
	    {
	    	$i ++;
	    	$arrResult[$i]['month'] 	 = $rowDb['ot_month'];
			$arrResult[$i]['total_time'] = round($rowDb['sum']/60);
			$arrResult[$i]['year'] 		 = $rowDb['ot_year'];
	    }
		
		$arrpiedata = "";
		foreach ($arrResult as $key => $value) {
			$arrpie 	 = "{ x: new Date(".$value['year'].",".$value['month']."), y: ".$value['total_time']." },";
			//$arrpie 	 = "{ x: ".$value['month'].", y: ".$value['total_time']." },";
			$arrpiedata	 = $arrpie.$arrpiedata;
		}
						
echo '
<script type="text/javascript">
window.onload = function () {
	var chart = new CanvasJS.Chart("chartContainer",
	{
		animationEnabled: true,
		theme: "theme2",
		exportEnabled: true,
		title:{
			text: "Overtime Report"
		},
		axisX:{  
            title: "BULAN",
	        gridThickness: 2,
	        valueFormatString: "MMMM"
        },
        axisY: {
              valueFormatString: "0.0#"
      	},
      	axisY:{
	        title: "per Jam",
	        gridThickness: 2
   		 },
		data: [
		{
			type: "bar", //change type to bar, line, area, pie, etc
			dataPoints: [
		
		'.$arrpiedata.'
			
			]
		}
		]
	});

	chart.render();
}
</script>
<script type="text/javascript" src="scripts/canvasjs.min.js"></script>
<div id="chartContainer" style="height: 300px; width: 100%; position: absolute; bottom: 200px;"></div>
';
//write this variable in every page
$strPageTitle = 'Graphical Overtime Report';
$strTemplateFile = getTemplate(str_replace(".php", ".html", basename($_SERVER['PHP_SELF'])));
//------------------------------------------------
//Load Master Template
$tbsPage->LoadTemplate($strMainTemplate);
$tbsPage->Show();
?>