<?
  include_once('../global/session.php');
  include_once('global.php');
  include_once('form_object.php');
	//include_once("../includes/krumo/class.krumo.php");
  $dataPrivilege = getDataPrivileges(basename($_SERVER['PHP_SELF']), $bolCanView, $bolCanEdit, $bolCanDelete, $bolCanApprove, $bolCanCheck, $bolCanAcknowledge, $bolCanApprove2);
  if (!$bolCanView) die(accessDenied($_SERVER['HTTP_REFERER']));




  $strTemplateFile = getTemplate("employee_report.html");
  //---- INISIALISASI ----------------------------------------------------

  $strWordsCompany = getWords("company");
  $strWordsManagement = getWords("management");
  $strWordsDivision = getWords("division");
  $strWordsDepartment = getWords("department");
  $strWordsSection = getWords("section");
  $strWordsSubSection = getWords("sub section");
  $strWordsBranch = getWords("branch office");
  $strWordsGrade = getWords("grade");
  $strWordsLevel = getWords("employee category");
  $strWordsPosition = getWords("position");
  $strWordsSearchEmployee = getWords("search employee");
  $strWordsSimpleResume = getWords("simple resume");
  $strWordsReport = getWords("report");
  $strWordsShow = getWords("show");
  $strWordsExcel = getWords("excel");
  $strDataDetail = "";
  $strHidden = "";
  $strResult = "";
  $strNow = date("Y-m-d");
  $dateFrom = date("Y-m-d", mktime(0,0,0,date('m') - 1,25,date('Y')));
	$dateThru = date("Y-m-d", mktime(0,0,0,date('m'),24,date('Y')));
  
  //----------------------------------------------------------------------

  //--- DAFTAR FUNSI------------------------------------------------------


  //----------------------------------------------------------------------

  //----MAIN PROGRAM -----------------------------------------------------
  $db = new CdbClass;
  if ($db->connect()) {
    getUserEmployeeInfo();
    $intDefaultWidthPx = 200;
		$strDataCompany    = (isset($_REQUEST['dataCompany'])) ?    $_REQUEST['dataCompany'] : "";
		$strDataManagement    = (isset($_REQUEST['dataManagement'])) ?    $_REQUEST['dataManagement'] : "";
    $strDataDivision    = (isset($_REQUEST['dataDivision'])) ?    $_REQUEST['dataDivision'] : "";
    $strDataDepartment  = (isset($_REQUEST['dataDepartment'])) ?  $_REQUEST['dataDepartment'] : "";
    $strDataSection     = (isset($_REQUEST['dataSection'])) ?     $_REQUEST['dataSection'] : "";
    $strDataSubSection  = (isset($_REQUEST['dataSubSection'])) ?  $_REQUEST['dataSubSection'] : "";
    $strDataGrade       = (isset($_REQUEST['dataGrade'])) ?  $_REQUEST['dataGrade'] : "";
    $strDataPosition    = (isset($_REQUEST['dataPosition'])) ?  $_REQUEST['dataPosition'] : "";
    $strDataBranch    = (isset($_REQUEST['dataBranch'])) ?  $_REQUEST['dataBranch'] : "";
    $strDataEmployee = "";
    scopeData($strDataEmployee, $strDataSubSection, $strDataSection, $strDataDepartment, $strDataDivision, $_SESSION['sessionUserRole'], $arrUserInfo, $strDataBranch,$strDataManagement);
		$strInputCompany = getCompanyList($db, "dataCompany",15, $strEmptyOption2, $strKriteria2, "style=\"width:$intDefaultWidthPx\"");   
		$strInputManagement   = getManagementList($db,"dataManagement", 21, $strDataManagement, $strKriteria2," style=\"width:$intDefaultWidthPx\" ".$ARRAY_DISABLE_GROUP['division'] );
    $strInputDivision   = getDivisionList($db,"dataDivision", $strDataDivision, $strEmptyOption, ""," style=\"width:$intDefaultWidthPx\" ".$ARRAY_DISABLE_GROUP['division'] );
    $strInputDepartment = getDepartmentList($db,"dataDepartment", $strDataDepartment, $strEmptyOption, ""," style=\"width:$intDefaultWidthPx\" ".$ARRAY_DISABLE_GROUP['department'] );
    $strInputSection    = getSectionList($db,"dataSection", $strDataSection, $strEmptyOption,""," style=\"width:$intDefaultWidthPx\" ".$ARRAY_DISABLE_GROUP['section'] );
    $strInputSubSection = getSubSectionList($db,"dataSubsection", $strDataSubSection, $strEmptyOption,""," style=\"width:$intDefaultWidthPx\" ".$ARRAY_DISABLE_GROUP['sub_section'] );
    // $strInputCompany    = getCompanyList($db, "dataCompany",$strDataCompany, $strEmptyOption2, $strKriteria2, "style=\"width:$intDefaultWidthPx\"");
    $strInputGrade    	= getSalaryGradeList($db, "dataGrade", $strDataGrade, $strEmptyOption, "", "style=\"width:$intDefaultWidthPx\"");
    $strInputPosition   = getPositionList($db, "dataPosition", $strDataPosition, $strEmptyOption, "", "style=\"width:$intDefaultWidthPx\"");
		$strInputBranch			= getBranchList($db, "dataBranch", ($strDataBranch = getInitialValue("Branch", "", $strDataBranch)), $strEmptyOption, "", "style=\"width:$strDefaultWidthPx\" ". $ARRAY_DISABLE_GROUP['branch']);
    if (!$bolCanView) {
      showError("view_denied");
      $strDataDetail = "";
    }
  }

  $tbsPage = new clsTinyButStrong ;
  
  //write this variable in every page
  $strPageTitle = getWords($dataPrivilege['menu_name']);
  if (trim($dataPrivilege['icon_file']) == "") $pageIcon = "../images/icons/blank.gif"; 
  else $pageIcon = "../images/icons/".$dataPrivilege['icon_file'];
  $strTemplateFile = getTemplate(str_replace(".php", ".html", basename($_SERVER['PHP_SELF'])));  
  //------------------------------------------------
  //Load Master Template
  $tbsPage->LoadTemplate($strMainTemplate) ;
  $tbsPage->Show() ;

?>