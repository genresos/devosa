<?php
  include_once('../global/session.php');
  include_once('global.php');
  include_once('../global/common_data.php');
  include_once('../global/employee_function.php');
  include_once('../includes/datagrid2/datagrid.php');
  include_once('../includes/form2/form2.php');
  include_once('../classes/hrd/hrd_employee.php');

  $dataPrivilege = getDataPrivileges(basename($_SERVER['PHP_SELF']), $bolCanView, $bolCanEdit, $bolCanDelete, $bolCanApprove, $bolCanCheck, $bolCanAcknowledge, $bolCanApprove2);
  if (!$bolCanView) die(accessDenied($_SERVER['HTTP_REFERER']));

  
  $bolPrint = (isset($_REQUEST['btnPrint']) || isset($_REQUEST['btnPrintAll']) );

  //---- INISIALISASI ----------------------------------------------------
  $strHidden = "";
  $intTotalData = 0; // default, tampilan dibatasi (paging)
  $strWordsDataEntry        = getWords("data entry");
  $strWordsDonationList     = getWords("donation list");
  $strWordsDonationReport   = getWords("donation report");
  $strWordsDispositionForm  = getWords("disposition form");


  //----------------------------------------------------------------------

  //----MAIN PROGRAM -----------------------------------------------------
  $db = new CdbClass;
  if ($db->connect()) 
  {
    getUserEmployeeInfo();
    $bolIsEmployee = isUserEmployee();
    $strKriteria   = "";

    scopeData($strDataEmployee, $strDataSubSection, $strDataSection, $strDataDepartment, $strDataDivision, $_SESSION['sessionUserRole'], $arrUserInfo);
    $strReadonly = (scopeCBDataEntry(&$strDataEmployee, $_SESSION['sessionUserRole'], $arrUserInfo)) ? "readonly" : "";


    // generate data hidden input dan element form input
    $fFilter = new clsForm("formFilter", 3, "100%", "");
    $fFilter->caption = strtoupper($strWordsFILTERDATA);

    $fFilter->addInput(getWords("date from"), "dataDateFrom", getInitialValue("DateFrom", date("Y-m-")."01"), array("style" => "width:$strDateWidth"), "date", false, true, true);
    $fFilter->addInput(getWords("date thru"), "dataDateThru", getInitialValue("DateThru", date("Y-m-d")), array("style" => "width:$strDateWidth"), "date", false, true, true);
    $fFilter->addSelect(getWords("donation type"), "dataDonationType", getDataListDonationType(getInitialValue("DonationType"), true, array("value" => "", "text" => "", "selected" => true)), array("style" => "width:$strDefaultWidthPx"), "", false);
    $fFilter->addInputAutoComplete(getWords("employee ID"), "dataEmployeeID", getDataEmployee(getInitialValue("Employee", "", $strDataEmployee)), "style=width:$strDefaultWidthPx ".$strReadonly, "string", false);
    $fFilter->addLabelAutoComplete("", "dataEmployeeID", "");
    $fFilter->addInput(getWords("minimum amount"), "dataAmountFrom", getInitialValue("AmountFrom"), "", "numeric", false, true, true);
    $fFilter->addInput(getWords("maximum amount"), "dataAmountThru", getInitialValue("AmountThru"), "", "numeric", false, true, true);
    $fFilter->addSelect(getWords("request status"), "dataRequestStatus", getDataListRequestStatus(getInitialValue("RequestStatus"), true, array("value" => "", "text" => "", "selected" => true)), array("style" => "width:145"), "", false);  

    $fFilter->addSelect(getWords("branch"), "dataBranch", getDataListBranch(getInitialValue("Branch"), true), array("style" => "width:$strDefaultWidthPx"), "", false);  
    $fFilter->addSelect(getWords("level"), "dataPosition", getDataListPosition(getInitialValue("Position"), true), array("style" => "width:$strDefaultWidthPx"), "", false);  
    $fFilter->addSelect(getWords("grade"), "dataGrade", getDataListSalaryGrade(getInitialValue("Grade"), true), array("style" => "width:$strDefaultWidthPx"), "", false);    
    $fFilter->addSelect(getWords("status"), "dataEmployeeStatus", getDataListEmployeeStatus(getInitialValue("EmployeeStatus"), true, array("value" => "", "text" => "", "selected" => true)), array("style" => "width:$strDefaultWidthPx"), "", false);
    $fFilter->addSelect(getWords("active"), "dataActive", getDataListEmployeeActive(getInitialValue("Active"), true, array("value" => "", "text" => "", "selected" => true)), array("style" => "width:$strDefaultWidthPx"), "", false);  
    $fFilter->addSelect(getWords("bank"), "dataBank", getDataListBank(getInitialValue("Bank"), true), array("style" => "width:$strDefaultWidthPx"), "", false);  
    $fFilter->addLiteral("", "", "");
    $fFilter->addLiteral("", "", "");

    $fFilter->addSelect(getWords("company"), "dataCompany", getDataListCompany($strDataCompany, $bolCompanyEmptyOption, $arrCompanyEmptyData, $strKriteria2), array("style" => "width:$strDefaultWidthPx"), "", false);  


    $fFilter->addSelect(getWords("division"), "dataDivision", getDataListDivision(getInitialValue("Division", "", $strDataDivision), true), array("style" => "width:$strDefaultWidthPx"), "", false, ($ARRAY_DISABLE_GROUP['division'] == ""));
    $fFilter->addSelect(getWords("department "), "dataDepartment", getDataListDepartment(getInitialValue("Department", "", $strDataDepartment), true), array("style" => "width:$strDefaultWidthPx"), "", false, ($ARRAY_DISABLE_GROUP['department'] == ""));
    $fFilter->addSelect(getWords("section"), "dataSection", getDataListSection(getInitialValue("Section", "", $strDataSection), true), array("style" => "width:$strDefaultWidthPx"), "", false, ($ARRAY_DISABLE_GROUP['section'] == ""));
    $fFilter->addSelect(getWords("sub section"), "dataSubSection", getDataListSubSection(getInitialValue("SubSection", "", $strDataSubSection), true), array("style" => "width:$strDefaultWidthPx"), "", false, ($ARRAY_DISABLE_GROUP['sub_section'] == ""));
    $fFilter->addLiteral("", "", "");
    $fFilter->addLiteral("", "", "");

    $fFilter->addSubmit("btnShow", getWords("show"), "", true, true, "", "", "doNothing()");

    $formFilter = $fFilter->render();


    if ($bolCanView) 
    {
      $myDataGrid = new cDataGrid("formData", "DataGrid1", "100%", "100%");
      $myDataGrid->caption = getWords($dataPrivilege['menu_name']);
      $DataGrid = showData($fFilter);
    }
    else
    {
      showError("view_denied");
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



//end of main program
//--------------------------

  function showData($f)
  {   
    global $bolPrint;
    global $dataPrivilege;
    global $intTotalData;
    global $myDataGrid;
    $myDataGrid->strAdditionalHtml  = "";
    global $strKriteriaCompany;

    //global $arrUserInfo;

    $arrData = $f->getObjectValues();
    $strKriteria = "";


    if ($arrData['dataBank'] != "") {
      $strKriteria .= "AND \"bank_code\" = '". $arrData['dataBank']. "' ";
    }
    if ($arrData['dataBranch'] != "") {
      $strKriteria .= "AND \"branch_code\" = '". $arrData['dataBranch']. "' ";
    }
    if ($arrData['dataPosition'] != "") {
      $strKriteria .= "AND \"position_code\" = '". $arrData['dataPosition']. "' ";
    }
    if ($arrData['dataEmployeeStatus'] != "") {
      $strKriteria .= "AND \"employee_status\" = '". $arrData['dataEmployeeStatus']. "' ";
    }
    if ($arrData['dataActive'] != "") {
      $strKriteria .= "AND active = '". $arrData['dataActive']. "' ";
    }
    if ($arrData['dataEmployeeID'] != "") {
      $strKriteria .= "AND upper(\"employee_id\") = '". $arrData['dataEmployeeID']. "' ";
    }
    if ($arrData['dataDivision'] != "") {
      $strKriteria .= "AND \"division_code\" = '". $arrData['dataDivision']. "' ";
    }

    if ($arrData['dataDepartment'] != "") {
      $strKriteria .= "AND \"department_code\" = '". $arrData['dataDepartment']. "' ";
    }
    if ($arrData['dataSection'] != "") {
      $strKriteria .= "AND \"section_code\" = '". $arrData['dataSection']. "' ";
    }
    if ($arrData['dataSubSection'] != "") {
      $strKriteria .= "AND \"sub_section_code\" = '". $arrData['dataSubSection']. "' ";
    }
    if ($arrData['dataGrade'] != "") {
      $strKriteria .= "AND \"grade_code\" = '". $arrData['dataGrade']. "' ";
    }
    if ($arrData['dataAmountFrom'] != "")
    {
      $strKriteria .= "AND total_amount >= ".$arrData['dataAmountFrom']." ";
    }
    if ($arrData['dataAmountThru'] != "")
    {
      $strKriteria .= " AND total_amount <= ".$arrData['dataAmountThru']. " ";
    }

    $strKriteria .= $strKriteriaCompany;
    $myDataGrid->setCriteria($strKriteria);

    $myDataGrid->addColumnCheckbox(new DataGrid_Column("chkID", "id", array('width' => 30), array('align'=>'center', 'nowrap' => ''), false, false, "", "", "string", false));
    
    
    $myDataGrid->addColumnNumbering(new DataGrid_Column("No", "", array('width'=>30), array('nowrap'=>'')));
    $myDataGrid->addColumn(new DataGrid_Column("Employee ID", "employee_id", "", array("nowrap" => "nowrap"), true, true, "", "", "string", true, 15));
    $myDataGrid->addColumn(new DataGrid_Column(getWords("employee name"), "employee_name", "",  array("nowrap" => "nowrap"), true, true, "", "", "string", true, 35));
    $myDataGrid->addColumn(new DataGrid_Column(getWords("bank account"), "bank_account", "",  array("align" => "center"), true, true, "", "", "string", true, 20));
    $myDataGrid->addColumn(new DataGrid_Column(getWords("bank"), "bank_name", "",  array("nowrap" => "nowrap"), true, true, "", "", "string", true, 20));
    $myDataGrid->addColumn(new DataGrid_Column(getWords("bank account name"), "bank_account_name", array("nowrap" => "nowrap"),  array("align" => "center"), true, true, "", "", "string", true, 35));
    //$myDataGrid->addColumn(new DataGrid_Column(getWords("total amount"), "total_amount", "",  array("align" => "right"), true, true, "", "", "numeric", true, 15));
    $myDataGrid->addColumn(new DataGrid_Column(getWords("total amount"), "total_amount", array("width" => 150),  array("align" => "right"), false, false, "", "formatNumber()", "numeric", true, 15));

    $myDataGrid->addColumn(new DataGrid_Column(getWords("branch"), "branch_code", array(),  array("nowrap" => "nowrap"), true, true, "", "", "string", true, 10));
    $myDataGrid->addColumn(new DataGrid_Column(getWords("division"), "division_name", "",  array("nowrap" => "nowrap"), true, true, "", "", "string", false));
    $myDataGrid->addColumn(new DataGrid_Column(getWords("department"), "department_name", ""),  array("nowrap" => "nowrap"), true, true, "", "", "string", false);
    $myDataGrid->addColumn(new DataGrid_Column(getWords("section"), "section_name", "",  array("nowrap" => "nowrap"), true, true, "", "", "string", false));

    $myDataGrid->addButtonExportExcel("Export Excel", "employee_list.xls", getWords($dataPrivilege['menu_name']));

    foreach($arrData AS $key => $value)
    {
      $myDataGrid->strAdditionalHtml .= generateHidden($key, $value, "");
    }

 

    $myDataGrid->getRequest();
    //--------------------------------
    //get Data and set to Datagrid's DataSource by set the data binding (bind method)
    $strSQL       = "
      SELECT t1.id, employee_id, employee_name, bank_name, id_company, t1.division_code, division_name, active, t1.position_code, t1.employee_status, t1.grade_code, t1.sub_section_code,
      t1.department_code, department_name, t1.section_code, section_name, branch_code, t1.bank_code, t1.bank_account, t1.bank_account_name, ";

    if ($arrData['dataRequestStatus'] != "") 
      $strSQL    .= "SUM(CASE WHEN (t2.status = ".$arrData['dataRequestStatus'].") THEN amount ELSE 0 END) AS total_amount ";
    else      
      $strSQL    .= "SUM(amount) AS total_amount ";
    $strSQL    .= "
      FROM hrd_employee AS t1 LEFT JOIN hrd_donation AS t2 ON t1.id = t2.id_employee
      LEFT JOIN hrd_bank AS t3 ON t1.bank2_code = t3.bank_code 
      LEFT JOIN hrd_division AS t4 ON t1.division_code = t4.division_code 
      LEFT JOIN hrd_department AS t5 ON t1.department_code = t5.department_code 
      LEFT JOIN hrd_section AS t6 ON t1.section_code = t6.section_code  
      ";
    if(getPostValue('dataDonationType') != "") 
      $strSQL .= "WHERE donation_code = '".getPostValue('dataDonationType')."' ";

    $strSQL .= " GROUP BY t1.id, employee_id, employee_name, bank2_account, bank2_account_name, bank_name, id_company ,division_name , active, t1.division_code , t1.department_code, department_name, t1.section_code, section_name, branch_code ";
    $strSQL = "SELECT * FROM ($strSQL) AS t0 WHERE 1=1 ";

    $strSQLCOUNT = "SELECT count(*) FROM ($strSQL) AS t0 WHERE 1=1 ";
    $db = new CdbClass;
    if ($db->connect()) 
    {
      $myDataGrid->totalData = $myDataGrid->getTotalData($db, $strSQLCOUNT);

      $dataset = $myDataGrid->getData($db, $strSQL);

      $myDataGrid->bind($dataset );
    }    
    return $myDataGrid->render();
  }
  

  function printStatus($params)
  {
    extract($params);
    if ($value == 1)
      return getWords('active');
    else
      return getWords('not active');
  }

  function getCSSClassName($flag, $bolOrphan = false)
  {
    if ($bolOrphan) 
    {
      $strClass = "class=\"bgDenied\"";
      $strDisabled = "";
    } 
    else 
    {
      switch ($flag) 
      {
        case 0 :
          $strClass = "";
          break;
        case 1 :
          $strClass = "class=\"bgNewData\"";
          break;
        case 2 :
          $strClass = "class=\"bgCheckedData\"";
          break;
        case 3 : // ditolak
          $strClass = "class=\"bgDenied\"";
          break;
        default :
          $strClass = "";
          break;
      }
    }
    return $strClass;
  }

  function doNothing()  
  {
  }

?>
