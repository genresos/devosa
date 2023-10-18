<?php
  include_once('../global/session.php');
  include_once('global.php');
  include_once('form_object.php');
  include_once('salary_func.php');
  include_once('activity.php');
  include_once("../global/cls_date.php");
  include_once('../includes/model/model.php');
  include_once('../includes/datagrid2/datagrid.php');
  include_once("cls_salary_calculation_overtime.php");
  include_once("cls_employee.php");
	//include_once("../includes/krumo/class.krumo.php");
  $dataPrivilege = getDataPrivileges("salary_calculation_overtime.php", $bolCanView, $bolCanEdit, $bolCanDelete, $bolCanApprove, $bolCanCheck, $bolCanAcknowledge, $bolCanApprove2);
  if (!$bolCanView  || (!$bolCanEdit && $_SERVER['QUERY_STRING'] != "")) die(accessDenied($_SERVER['HTTP_REFERER']));

  $strDisplay = ($bolCanEdit) ? "table-row" : "none";

  $bolPrint = (isset($_POST['btnPrint']));
  //---- INISIALISASI ----------------------------------------------------
  $strDataDetail = "";
  $strColumnAllowance = "";
  $strColumnDeduction = "";
  $strHidden = "";
  $strButtons = "";
  $intTotalData = 0;
  $strPaging = "";
  $strDataID = "";
  $strCalculationMenu = "";
  $strReportType = "";
  $strDataDateFrom = "";
  $strDataDateThru = "";
  $strKriteria = "";
  $strSpan1 = 0;
  $strSpan2 = 0;
  $strSpan3 = 1;
  $strSpan4 = 8;
  $strWidth = "\"70px\"";
	$arrData = array();
	$bolNewData = true;
  $strWordsSalarySumary = getWords("salary summary");
  //----------------------------------------------------------------------


  //--- DAFTAR FUNSI------------------------------------------------------
  // fungsi untuk menyimpan data
  function saveData()
  {
    global $objSalary;

    $intID = $objSalary->strDataID;

    $objSalary->saveData($intID);
    redirectPage("salary_calculation_result_overtime.php?dataID=$intID");

  }

  // fungsi untuk meng-approve data
  function approveData($db)
  {
    global $objSalary;
    global $intStatus;

    if ($_SESSION['sessionUserRole'] != ROLE_ADMIN) return false;
    $objSalary->setApproved();
    $intStatus = $objSalary->arrData['status'];

  }// approveData


  // fungsi untuk melakukan proses slip gaji
  function getSlip()
  {
    global $bolIrregular;
    global $bolHideBlank;
    global $myDataGrid;
    global $db;
    global $objSalary;
    global $ARRAY_EMPLOYEE_STATUS;

    include_once('cls_annual_leave.php');
    $objLeave = new clsAnnualLeave($db);



    foreach ($objSalary->arrMA AS $strCode => $arrInfo)
    {        //echo $strCode."|".$arrInfo['show']."<br>";

      if ($arrInfo['is_default'] == "t")
      {
        $strVar  = "bolShow_".$strCode;
        $$strVar = (getSetting($strCode."_show") == 't');
      }
    }
   // die();

    $objDate = new clsCommonDate();

    $objEmp = new clsEmployees($db);
    $objEmp->loadData("id, employee_id, employee_name, id_company, join_date, grade_code, family_status_code, npwp, functional_code, branch_code, branch_penugasan_code, branch_bpjs_ks_code, employee_id_2, id_card, tax_status_code");


    // tampilkan header HTML dulu
    echo "
        <html>
        <head>
        <title>Slip</title>
        <meta http-equiv='Content-Type' content='application/vnd.ms-word; charset=iso-8859-1'>
        <meta http-equiv='Content-Disposition' content='attachment; charset=iso-8859-1'>
        <link href='../css/invosa.css' rel='stylesheet' type='text/css'>
        </head>
        <body onLoad=\"window.print();\" marginheight=0 marginwidth=0 leftmargin=10 rightmargin=0 topmargin=0>
        <table cellspacing=0 cellpadding=0 border=0 width='100%'>
    ";
    // inisialisasi
    $strThisPage  = "
                      <span>&nbsp;";
    $strNewPage   = "
                    <span style=\"page-break-before:always;\"></span>";

    $GLOBALS['strPeriod']    = $objDate->getDateFormat($objSalary->arrData['date_from_salary'], "d M Y")." - ".$objDate->getDateFormat($objSalary->arrData['date_thru_salary'], "d M Y");
    $GLOBALS['strAttendancePeriod']    = $objDate->getDateFormat($objSalary->arrData['date_from'], "d M Y")." - ".$objDate->getDateFormat($objSalary->arrData['date_thru'], "d M Y");
    $GLOBALS['strCredited']  = $objDate->getDateFormat(getNextDateNextMonth($objSalary->arrData['date_from_overtime'], 1), "d M Y");
    $GLOBALS['strUserName']  = $_SESSION['sessionUserName'];

    $bolEven = true; // apakah genap
    $i = 0;
    foreach ($myDataGrid->checkboxes as $strValue)
    {
      $bolEven = !$bolEven;
      $i++;

      // inisialisasi detail
      $GLOBALS['strCompany']       = "";
      $GLOBALS['strEmployeeName']  = "";
      $GLOBALS['strEmployeeID']    = "";
      $GLOBALS['strEmployeeID2']    = "";
      $GLOBALS['strDivision']    = "";
      $GLOBALS['strJoinDate']      = "";
      $GLOBALS['strWorkingDay']    = 0;
      $GLOBALS['strOvertimeHour']  = 0;
      $GLOBALS['strOvertimeBasic'] = 0;
      $GLOBALS['strIncome']        = "";
      $GLOBALS['strDeduction']     = "";
      $GLOBALS['strIncomeBlankSpace']        = "";
      $GLOBALS['strDeductionBlankSpace']     = "";
      $GLOBALS['strTotalIncome']   = "";
      $GLOBALS['strTotalDeduction']= "";
      $GLOBALS['strTotalSalary']   = "";
      $GLOBALS['strFamilyStatus']   = "";
      $GLOBALS['strNPWP']   = "";
      $GLOBALS['strFunctional']   = "";
      $GLOBALS['strBranch']   = "";

      // ambil ID employee
      $strIDEmployee = $objSalary->getIDEmployeeFromDetailID($strValue);
      $objLeave->generateEmployeeAnnualLeave($strIDEmployee);
      $arrCuti = $objLeave->getEmployeeLeaveInfo($strIDEmployee);
      $intCompany = $objEmp->getInfoByID($strIDEmployee, "id_company");
      $GLOBALS['strCompany'] = printCompanyName($intCompany);
      $strDiv = $objSalary->getEmployeeSalaryDetail($strIDEmployee, "division_code");
      $GLOBALS['strDivision']       = getDivisionName($strDiv);
      $strDept = $objSalary->getEmployeeSalaryDetail($strIDEmployee, "department_code");
      $GLOBALS['strDepartment']       = getDepartmentName($strDept);
      $GLOBALS['strEmployeeID']     = $objEmp->getInfoByID($strIDEmployee, "employee_id");
      $GLOBALS['strEmployeeID2']     = $objEmp->getInfoByID($strIDEmployee, "employee_id");
      $GLOBALS['strEmployeeName']   = $objEmp->getInfoByID($strIDEmployee, "employee_name");
      $GLOBALS['strFamilyStatus']   = $objEmp->getInfoByID($strIDEmployee, "family_status_code");
      $GLOBALS['strTaxStatus']   = $objEmp->getInfoByID($strIDEmployee, "tax_status_code");
      $GLOBALS['strIDCard']   = $objEmp->getInfoByID($strIDEmployee, "id_card");
      $GLOBALS['strNPWP']   = $objEmp->getInfoByID($strIDEmployee, "npwp");
      $strFunc   = $objEmp->getInfoByID($strIDEmployee, "functional_code");
      $GLOBALS['strFunctional']       = getFunctionalName($strFunc);
      $strBrch   = $objEmp->getInfoByID($strIDEmployee, "branch_code");
      $GLOBALS['strBranch']       = getBranchName($strBrch);
      $GLOBALS['strJoinDate']       = $objDate->getDateFormat($objEmp->getInfoByID($strIDEmployee, "join_date"), "d-M-y");
      $GLOBALS['strWorkingDay']     = $objSalary->getEmployeeSalaryDetail($strIDEmployee, "attendance_day");
      $GLOBALS['fltJamsostekAllowance']     = standardFormat($objSalary->getEmployeeSalaryDetail($strIDEmployee, "jamsostek_allowance"));
      $GLOBALS['fltPensionAllowance']     = standardFormat($objSalary->getEmployeeSalaryDetail($strIDEmployee, "pension_allowance"));
      $GLOBALS['fltBenefitAllowance']     = standardFormat($objSalary->getEmployeeSalaryDetail($strIDEmployee, "pension_allowance") + $objSalary->getEmployeeSalaryDetail($strIDEmployee, "jamsostek_allowance"));
      $GLOBALS['strSisaCuti']       = $arrCuti['curr']['remain'];
      $ot1 = $objSalary->getEmployeeSalaryDetail($strIDEmployee, "ot1_min") * 1.5;
      $ot2 = $objSalary->getEmployeeSalaryDetail($strIDEmployee, "ot2_min") * 2;
      $ot3 = $objSalary->getEmployeeSalaryDetail($strIDEmployee, "ot3_min") * 3;
      $ot4 = $objSalary->getEmployeeSalaryDetail($strIDEmployee, "ot4_min") * 4;
      if (($ot1 + $ot2 + $ot2b + $ot3 + $ot4) <> 0) $GLOBALS['strOvertimeHour'] = ($ot1 + $ot2 + $ot2b + $ot3 + $ot4) / 60;

      // tampilkan income
      $fltTotalIncome = $fltTotalDeduction = $fltSalary = 0;

      //-------------------------------------------------------------------------------------------------------------------------------------------//
      //---------------------------------------------INCOME---------------------------------------INCOME-------------------------------------------//
      //-------------------------------------------------------------------------------------------------------------------------------------------//

      foreach ($objSalary->arrMA AS $strCode => $arrInfo)
      {
        $fltAmount = $objSalary->getEmployeeSalaryDetail($strIDEmployee, $strCode);
        if ($arrInfo['is_default'] == 't' && $arrInfo['show'] == 't' )
        {
          if((!$bolIrregular && $arrInfo['irregular'] == 'f') || $arrInfo['irregular'] == 't')
          {
            //Jika hide if zero, dan nilainya zero tambahkan 1 baris blank space (berhubung printernya continues paper)
            if ($arrInfo['hidezero'] == 'f' || $fltAmount != 0)
            {
              $GLOBALS['strIncome'] .= wrapRowBorderLeftRight($arrInfo['name'], "Rp", standardFormat($fltAmount), true /*isNumeric*/);
              $fltTotalIncome += $fltAmount;
              if ($arrInfo['benefit'] == 't')
              {
                $GLOBALS['strDeduction'] .= wrapRowBorderLeftRight($arrInfo['name'], "Rp", standardFormat($fltAmount), true/*isNumeric*/);
                $fltTotalDeduction += $fltAmount;

              }
            }
            else
            {
              $GLOBALS['strIncomeBlankSpace'] .= wrapRowBorderLeftRight("", "", "", false /*isNumeric*/);
              if ($arrInfo['benefit'] == 't')
              {
                $GLOBALS['strDeductionBlankSpace'] .= wrapRowBorderLeftRight("", "", "", false /*isNumeric*/);
              }
            }
          }
          else
          {
            $GLOBALS['strIncomeBlankSpace'] .= wrapRowBorderLeftRight("", "", "", false /*isNumeric*/);
            if ($arrInfo['benefit'] == 't')
            {
              $GLOBALS['strDeductionBlankSpace'] .= wrapRowBorderLeftRight("", "", "", false /*isNumeric*/);
            }
          }
        }
      }

      foreach ($objSalary->arrMA AS $strCode => $arrInfo)
      {
        $fltAmount = $objSalary->getEmployeeAllowanceDetail($strIDEmployee, $strCode);
        if ($arrInfo['is_default'] == 'f' && $arrInfo['show'] == 't')
        {
          if((!$bolIrregular && $arrInfo['irregular'] == 'f') || $arrInfo['irregular'] == 't')
          {
            if ($arrInfo['hidezero'] == 'f' || $fltAmount != 0)
            {
              $GLOBALS['strIncome'] .= wrapRowBorderLeftRight($arrInfo['name'], "Rp", standardFormat($fltAmount), true/*isNumeric*/);
              $fltTotalIncome += $fltAmount;
              if ($arrInfo['benefit'] == 't')
              {
                $GLOBALS['strDeduction'] .= wrapRowBorderLeftRight($arrInfo['name'], "Rp", standardFormat($fltAmount), true/*isNumeric*/);
                $fltTotalDeduction += $fltAmount;
              }
            }
            else
            {
              $GLOBALS['strIncomeBlankSpace'] .= wrapRowBorderLeftRight("", "", "", false/*isNumeric*/);
              if ($arrInfo['benefit'] == 't')
              {
                $GLOBALS['strDeductionBlankSpace'] .= wrapRowBorderLeftRight("", "", "", false/*isNumeric*/);
              }
            }
          }
          else
          {
            $GLOBALS['strIncomeBlankSpace'] .= wrapRowBorderLeftRight("", "", "", false/*isNumeric*/);
            if ($arrInfo['benefit'] == 't')
            {
              $GLOBALS['strDeductionBlankSpace'] .= wrapRowBorderLeftRight("", "", "", false/*isNumeric*/);
            }
          }
        }
      }
//      $GLOBALS['strIncome'] .= wrapRow("Tax Allowance", "Rp", standardFormat($objSalary->getEmployeeSalaryDetail($strIDEmployee, "tax_allowance")), true /*isNumeric*/); //form_function.php
//      $fltTotalIncome += $objSalary->getEmployeeSalaryDetail($strIDEmployee, "tax_allowance");

      //-------------------------------------------------------------------------------------------------------------------------------------------//
      //-----------------------------------------END INCOME-----------------------------------END INCOME-------------------------------------------//
      //-------------------------------------------------------------------------------------------------------------------------------------------//

      //-------------------------------------------------------------------------------------------------------------------------------------------//
      //------------------------------------------DEDUCTION------------------------------------DEDUCTION-------------------------------------------//
      //-------------------------------------------------------------------------------------------------------------------------------------------//


      // tampilkan potongan
      if(!$bolIrregular)
      {
        //ambil list jenis-jenis iuran / loan
        $intLoanType = 0;
        $strSQL  = "SELECT id, type FROM hrd_loan_type ORDER BY note";
        $resDb = $db->execute($strSQL);
        while ($rowDb = $db->fetchrow($resDb))
        {
          $intLoanType++;
          $arrLoanType[$rowDb['id']] = $rowDb['type'];
        }

        $strSQL  = "SELECT t1.*, t2.id AS id_type, t3.resign_date FROM hrd_loan as t1
                    LEFT JOIN hrd_loan_type AS t2 ON t1.type = t2.type
                    LEFT JOIN hrd_employee AS t3 ON t1.id_employee = t3.id
                    WHERE payment_from < '". $objSalary->arrData['date_thru_salary']."'
                    AND (payment_thru + interval '1 months') > '". $objSalary->arrData['date_thru_salary']."'  ";
        $resDb = $db->execute($strSQL);

        //-----------------------------------------------OLD
        //--------------------------------------------------------OLD
        while ($rowDb = $db->fetchrow($resDb))
        {
          if ($rowDb['periode'] == 0)
            $fltLoan = 0;
          else
            $fltLoan = round((((100 + $rowDb['interest']) / 100) * $rowDb['amount']) / $rowDb['periode']);

          if ($rowDb['resign_date'] != "" || $rowDb['resign_date'] != NULL)
          {
            if($rowDb['resign_date'] >= $objSalary->arrData['date_from_salary'] && $rowDb['resign_date'] <= $objSalary->arrData['date_thru_salary'])
            {
              $intPaymentThruMonth = date("n", strtotime($rowDb['payment_thru']));
              $intPaymentThruYear = date("Y", strtotime($rowDb['payment_thru']));
              $intResignDateMonth = date("n", strtotime($rowDb['resign_date']));
              $intResignDateYear = date("Y", strtotime($rowDb['resign_date']));

              $intMultiplier = ($intPaymentThruYear - $intResignDateYear) * 12 + $intPaymentThruMonth - $intResignDateMonth + 1;

              $fltLoan = $intMultiplier * $fltLoan;
            }
          }

          if (isset($arrEmployeeLoan[$rowDb['category']][$rowDb['id_employee']]['amount']))
            $arrEmployeeLoan[$rowDb['category']][$rowDb['id_employee']]['amount'] += $fltLoan;
          else
            $arrEmployeeLoan[$rowDb['category']][$rowDb['id_employee']]['amount'] = $fltLoan;

          if (isset($arrEmployeeLoan[$rowDb['category']][$rowDb['id_employee']]['amount']))
            $arrEmployeeLoan[$rowDb['category']][$rowDb['id_employee']]['amount'] += $fltLoan;
          else
            $arrEmployeeLoan[$rowDb['category']][$rowDb['id_employee']]['amount'] = $fltLoan;

        }



        foreach ($objSalary->arrMD AS $strCode => $arrInfo)
        {
          if ($strCode != 'loan_deduction')
          {
            $fltAmount = $objSalary->getEmployeeSalaryDetail($strIDEmployee, $strCode);
            if ($arrInfo['is_default'] == 't' && $arrInfo['show'] == 't')
            {
              if($arrInfo['hidezero'] == 'f' || $fltAmount != 0)
              {
                $GLOBALS['strDeduction'] .= wrapRowBorderLeftRight($arrInfo['name'], "Rp", standardFormat($fltAmount), true/*isNumeric*/);
                $fltTotalDeduction += $fltAmount;
              }
              else
              {
                $GLOBALS['strDeductionBlankSpace'] .= wrapRowBorderLeftRight("", "", "", false/*isNumeric*/);
              }
            }
          }
        }

        foreach ($ARRAY_LOAN_CATEGORY AS $strCode => $arrInfo)
        {
          $fltAmount = $arrEmployeeLoan[$strCode][$strIDEmployee]['amount'];
            if($arrInfo['hidezero'] == 'f' || $fltAmount != 0)
            {
              $GLOBALS['strDeduction'] .= wrapRowBorderLeftRight($arrInfo, "Rp", standardFormat($fltAmount), true/*isNumeric*/);
              $fltTotalDeduction += $fltAmount;
            }
            else
            {
              $GLOBALS['strDeductionBlankSpace'] .= wrapRowBorderLeftRight("", "", "", false/*isNumeric*/);
            }
        }

        foreach ($objSalary->arrMD AS $strCode => $arrInfo)
        {
          $fltAmount = $objSalary->getEmployeeDeductionDetail($strIDEmployee, $strCode);
          if ($arrInfo['is_default'] == 'f' && $arrInfo['show'] == 't')
          {
            if($arrInfo['hidezero'] == 'f' || $fltAmount != 0)
            {
              $GLOBALS['strDeduction'] .= wrapRowBorderLeftRight($arrInfo['name'], "Rp", standardFormat($fltAmount), true/*isNumeric*/);
              $fltTotalDeduction += $fltAmount;
            }
            else
            {
              $GLOBALS['strDeductionBlankSpace'] .= wrapRowBorderLeftRight("", "", "", false/*isNumeric*/);
            }
          }
        }



      }
      else
        //tampilkan zakat utk irregular income
        $GLOBALS['strDeduction'] .= wrapRowBorderLeftRight("Zakat", "Rp", standardFormat($objSalary->getEmployeeSalaryDetail($strIDEmployee, "zakat_deduction_irregular")), true /*isNumeric*/); //form_function.php

      //tampilkan tax (tax reguler + irreguler)
//      $GLOBALS['strDeduction'] .= wrapRow("Tax", "Rp", standardFormat($objSalary->getEmployeeSalaryDetail($strIDEmployee, "tax") + $objSalary->getEmployeeSalaryDetail($strIDEmployee, "irregular_tax")), true /*isNumeric*/); //form_function.php
      $fltTotalDeduction += $objSalary->getEmployeeSalaryDetail($strIDEmployee, "tax");
      $fltTotalDeduction += $objSalary->getEmployeeSalaryDetail($strIDEmployee, "irregular_tax");

      //-------------------------------------------------------------------------------------------------------------------------------------------//
      //----------------------------------------END DEDUCTION----------------------------------END DEDUCTION---------------------------------------//
      //-------------------------------------------------------------------------------------------------------------------------------------------//



      $GLOBALS['strTotalIncome']    = standardFormat($fltTotalIncome);
      $GLOBALS['strTotalDeduction'] = standardFormat($fltTotalDeduction);
      $GLOBALS['strTotalSalary']    = standardFormat(round($fltTotalIncome, 0) - round($fltTotalDeduction,0));
      $GLOBALS['strTotalSalaryTerbilang']    = terbilang(round($fltTotalIncome, 0) - round($fltTotalDeduction,0));

      if ($bolEven) // genap
      {
        echo "<p>&nbsp;</p><p>&nbsp;</p><p>&nbsp;</p><table><tr><td height=60>&nbsp;</td></tr></table><span>";
      }
      else if ($i == 1)
      {
        echo $strThisPage;
      }
      else // ganjil, page berikutnya
      {
        echo $strNewPage;
      }

      $tbsPage = new clsTinyButStrong ;
      $tbsPage->LoadTemplate("templates/slip_template2.html") ;
      $tbsPage->Show(TBS_OUTPUT) ;
    }
    // tampilkan footer HTML
    echo "

<table>
</body>
</html>

    ";

    unset($objEmp);
    exit();
  }


  function setStatus()
  {
    global $myDataGrid;
    global $strDataID;
    global $db;
    global $_REQUEST;
    global $objSalary;
    global $arrSalaryTransferType;
    global $doRelease;
    global $strDataDetail;
    global $strKriteria;
    global $intCurrPage;
		$intStatus = "";
    foreach ($arrSalaryTransferType as $key => $index)
    {
      if (isset($_REQUEST["btn".$key]))
      {
        $intStatus = $key;
        break;
      }
    }
    if (!empty($doRelease)){
    	$intStatus = 0;
    }
    foreach ($myDataGrid->checkboxes as $strValue)
    {
      // ambil ID employee
      $strIDEmployee = $objSalary->getIDEmployeeFromDetailID($strValue);

      if ($intStatus != "" || $intStatus === 0)
      {
        $strSQL  = "DELETE FROM hrd_salary_transfer_status WHERE id_salary_master = $strDataID AND id_employee = $strIDEmployee;";
        $resDb = $db->execute($strSQL);
        if ((isset($_REQUEST['dataReleaseNote']) || isset($_REQUEST['dataReleaseNumber'])) && $intStatus == 0){
        	$releaseNote = isset($_REQUEST['dataReleaseNote']) && !empty($_REQUEST['dataReleaseNote']) ? $_REQUEST['dataReleaseNote'] : 'Release';
        	$releaseNumber = isset($_REQUEST['dataReleaseNumber']) && !empty($_REQUEST['dataReleaseNumber']) ? $_REQUEST['dataReleaseNumber'] : '1';
					$strSQL  = "INSERT INTO hrd_salary_transfer_status (id_salary_master, id_employee, transfer_code, note, release_number) VALUES ($strDataID, $strIDEmployee, '$intStatus','$releaseNote','$releaseNumber');";
				}else{
        	$strSQL  = "INSERT INTO hrd_salary_transfer_status (id_salary_master, id_employee, transfer_code) VALUES ($strDataID, $strIDEmployee, '$intStatus');";
        }
        $resDb = $db->execute($strSQL);
      }
    }
    if (!empty($doRelease)){
    	header("location: salary_calculation_result_overtime.php?dataID=".$strDataID);
    	//$strDataDetail = getDataGrid($db, $strKriteria,$intCurrPage);	
    }
  }


  //----------------------------------------------------------------------

  function getDataGrid($db, $strCriteria, $bolLimit = true, $isFullView = false, $isExport = false)
  {
    global $bolPrint;
    global $bolCanDelete;
    global $bolCanEdit;
    global $intTotalData;
    global $strDataID;
    global $objSalary;
    global $myDataGrid;
    global $bolIrregular;
    global $bolHideBlank;
    global $strKriteriaTransfer;
    global $arrSalaryTransferType;
    //global $strKriteriaCompany;

    if (isset($_POST['btnExportXLS']) || isset($_POST['btnExportFull'])) $isExport = true;
    else $isExport = false;
		if (isset($_POST['btnExportFull'])){
			$isFull = true;
		}
    //class initialization
    $DEFAULTPAGELIMIT = getSetting("rows_per_page");
    if (!is_numeric($DEFAULTPAGELIMIT)) $DEFAULTPAGELIMIT = 50;
    if ($bolPrint)
    {
      $myDataGrid = new cDataGrid("formData", "DataGrid1", "100%", "100%", false, false, false, false);
    }
    else
    {
      $myDataGrid = new cDataGrid("formData", "DataGrid1", "100%", "100%", $bolLimit, false, true);
      $myDataGrid->caption = getWords("list of salary");
    }
    $myDataGrid->disableFormTag();
    //$myDataGrid->setAJAXCallBackScript(basename($_SERVER['PHP_SELF']));
    $myDataGrid->pageSortBy = "t1.id_employee";
    //end of class initialization

/*
    $strSQL  = "SELECT include_irregular FROM hrd_salary_master WHERE id = $strDataID";
    $resDb = $db->execute($strSQL);
    if ($rowDb = $db->fetchrow($resDb))
    {
      $bolIncludeIrregular = ($rowDb['include_irregular'] == 't');
    }
*/
    // kumpulkan jenis tunjangan lain-lain dan potongan lain-lain
    $arrOtherAllowance = array();
    $arrOtherDeduction = array();
    $arrIrrAllowance = array();
    $arrIrrFixAllowance = array();
    $intOtherAllowance = 0; // total jenis tunjangan lain-lain
    $intOtherDeduction = 0; // total jenis potongan lain-lain
    $intIrrAllowance = 0; // total jenis irregular income lain-lain
    $intIrrFixAllowance = 0; // total jenis irregular income lain-lain
    $strOtherAllowance = ""; // fields-fields tambahan untuk tunjangan lain-lain
    $strOtherDeduction = ""; // fields-fields tambahan untuk potongan lain-lain
    $strIrrAllowance = ""; // fields-fields tambahan untuk tunjangan lain-lain
    $strIrrFixAllowance = ""; // fields-fields tambahan untuk tunjangan lain-lain
    foreach ($objSalary->arrMA AS $strCode => $arrTmp) // looping data tunjangan lain-lain
    {
      if ($arrTmp['is_default'] == 't')
      {
        if ($arrTmp['irregular'] == 't')
        {
          $strIrrFixName = ($arrTmp['name'] == "") ? $arrTmp['allowance_code'] : $arrTmp['name'];
          $arrIrrFixAllowance[$strCode] = $strIrrFixName;
          $strIrrFixAllowance .= ", 0 AS alw_".$strCode;
          if ($arrTmp['active'] == 't') $intIrrFixAllowance++;
        }
      }
      else
      {
        if ($arrTmp['irregular'] == 't')
        {
          $strIrrName = ($arrTmp['name'] == "") ? $arrTmp['allowance_code'] : $arrTmp['name'];
          $arrIrrAllowance[$strCode] = $strIrrName;
          $strIrrAllowance .= ", 0 AS alw_".$strCode;
          if ($arrTmp['active'] == 't') $intIrrAllowance++;
        }
        else
        {
          $strName = ($arrTmp['name'] == "") ? $arrTmp['allowance_code'] : $arrTmp['name'];
          $arrOtherAllowance[$strCode] = $strName;
          $strOtherAllowance .= ", 0 AS alw_".$strCode;
          $intOtherAllowance++;
        }
      }

    };
    foreach ($objSalary->arrMD AS $strCode => $arrTmp) // looping data tunjangan lain-lain
    {
      if ($arrTmp['is_default'] == 'f')
      {
        $strName = ($arrTmp['name'] == "") ? $arrTmp['deduction_code'] : $arrTmp['name'];
        $arrOtherDeduction[$strCode] = $strName;
        $strOtherDeduction .= ", 0 AS alw_".$strCode;
        $intOtherDeduction++;
      }
    }
    //ambil list jenis-jenis iuran / loan
    $intLoanType = 0;
    $strSQL  = "SELECT id, type FROM hrd_loan_type ORDER BY note";
    $resDb = $db->execute($strSQL);
    while ($rowDb = $db->fetchrow($resDb))
    {
      $intLoanType++;
      $arrLoanType[$rowDb['id']] = $rowDb['type'];
    }

    $strSQL  = "SELECT t1.*, t2.id AS id_type, t3.resign_date FROM hrd_loan as t1
                LEFT JOIN hrd_loan_type AS t2 ON t1.type = t2.type
                LEFT JOIN hrd_employee AS t3 ON t1.id_employee = t3.id
                WHERE status >=".REQUEST_STATUS_APPROVED_2."
                AND payment_from < '". $objSalary->arrData['date_thru_salary']."'
                AND (payment_thru + interval '1 months') > '". $objSalary->arrData['date_thru_salary']."'  ";
    $resDb = $db->execute($strSQL);


    //-----------------------------------------------OLD
    //--------------------------------------------------------OLD
    while ($rowDb = $db->fetchrow($resDb))
    {
      if ($rowDb['periode'] == 0)
        $fltLoan = 0;
      else
        $fltLoan = round((((100 + $rowDb['interest']) / 100) * $rowDb['amount']) / $rowDb['periode']);

      if ($rowDb['resign_date'] != "" || $rowDb['resign_date'] != NULL)
      {
        if($rowDb['resign_date'] >= $objSalary->arrData['date_from_salary'] && $rowDb['resign_date'] <= $objSalary->arrData['date_thru_salary'])
        {
          $intPaymentThruMonth = date("n", strtotime($rowDb['payment_thru']));
          $intPaymentThruYear = date("Y", strtotime($rowDb['payment_thru']));
          $intResignDateMonth = date("n", strtotime($rowDb['resign_date']));
          $intResignDateYear = date("Y", strtotime($rowDb['resign_date']));

          $intMultiplier = ($intPaymentThruYear - $intResignDateYear) * 12 + $intPaymentThruMonth - $intResignDateMonth + 1;

          $fltLoan = $intMultiplier * $fltLoan;
        }
      }

      if (isset($arrEmployeeLoan[$rowDb['id_type']][$rowDb['id_employee']]['amount']))
        $arrEmployeeLoan[$rowDb['id_type']][$rowDb['id_employee']]['amount'] += $fltLoan;
      else
        $arrEmployeeLoan[$rowDb['id_type']][$rowDb['id_employee']]['amount'] = $fltLoan;

    }
    //-------------------------------------------------OLD
    //-------------------------------------------------------------OLD

    if (!$bolPrint && !$isExport)
      $myDataGrid->addColumnCheckbox(new DataGrid_Column("chkID", "id", array('width' => 30), array('align'=>'center', 'nowrap' => '')));

    $myDataGrid->addColumnNumbering(new DataGrid_Column("No", "", array('width'=>30), array('nowrap'=>''), false, false, "", "", "numeric", true, 6, false, "Sub Total ", true));

    // $myDataGrid->addColumn(new DataGrid_Column(getWords("id employee"), "id_employee", array('rowspan' => 2, 'width' => 70), array("nowrap" => "nowrap"), true, true, "", "", "string", true, 12));
    $myDataGrid->addColumn(new DataGrid_Column(getwords("n i k"), "employee_id", array('width' => 70), array("nowrap" => "nowrap"), true, true, "", "", "string", true, 12));
    $myDataGrid->addColumn(new DataGrid_Column(getwords("n i k corporate"), "employee_id_2", array('width' => 70), array("nowrap" => "nowrap"), true, true, "", "", "string", true, 12));

    $myDataGrid->addColumn(new DataGrid_Column(getWords("employee name"), "employee_name", "",  array("nowrap" => "nowrap"), true, true, "", "", "string", true, 35, false));
    $myDataGrid->addColumn(new DataGrid_Column(getWords("join date"), "join_date", array("width" => 270),  array("nowrap" => "nowrap"), true, true, "", "", "string", true, 12, false));
    $myDataGrid->addColumn(new DataGrid_Column(getWords("resign date"), "resign_date", array("width" => 270),  array("nowrap" => "nowrap"), true, true, "", "", "string", true, 12, false));
    //$myDataGrid->addColumn(new DataGrid_Column(getWords("sex"), "gender", array("rowspan" => 2, "width" => 30),  array("align" => "center"), true, true, "", "printGender()", "string", true, 6, false));
    //$myDataGrid->addColumn(new DataGrid_Column(getWords("fam."), "family_status_code", array("rowspan" => 2, "width" => 30),  null, true, true, "", "", "string", true, 12, false));
    $myDataGrid->addColumn(new DataGrid_Column(getWords("grouper"), "grouper", array("width" => 30, "style" => "display:none"), array("style" => "display:none"), true, true, "", "", "string", true, 8, false));
    $myDataGrid->addColumn(new DataGrid_Column(getWords("cost center"), "branch_cost_center_code", array("width" => 30), null, true, true, "", "", "string", true, 12, false));
    $myDataGrid->addColumn(new DataGrid_Column(getWords("currency"), "salary_currency", array("width" => 30),  array("align" => "center"), true, true, "", "printCurrency()", "string", true, 6, false));
    $myDataGrid->addColumn(new DataGrid_Column(getWords("division"), "division_code", array("width" => 30), null, true, true, "", "getDivisionName()", "string", true, 12, false));
    $myDataGrid->addColumn(new DataGrid_Column(getWords("department"), "department_code", array("width" => 30), null, true, true, "", "getDepartmentName()", "string", true, 12, false));
    $myDataGrid->addColumn(new DataGrid_Column(getWords("section"), "section_code", array("width" => 30), null, true, true, "", "getSectionName()", "string", true, 12, false));
    $myDataGrid->addColumn(new DataGrid_Column(getWords("sub"), "sub_section_code", array("width" => 30), null, true, true, "", "getSubSectionName()", "string", true, 12, false));
    $myDataGrid->addColumn(new DataGrid_Column(getWords("branch as contract"), "branch_penugasan_code", array("width" => 30), null, true, true, "", "", "string", true, 12, false));
    $myDataGrid->addColumn(new DataGrid_Column(getWords("branch office"), "branch_code", array("width" => 30), null, true, true, "", "", "string", true, 12, false));
    //$myDataGrid->addColumn(new DataGrid_Column(getWords("branch bpjs Ks"), "branch_bpjs_ks_code", array("rowspan" => 2, "width" => 30), null, true, true, "", "", "string", true, 12, false));
    $myDataGrid->addColumn(new DataGrid_Column(getWords("grade"), "grade_code", array("width" => 30),  array("align" => "center", "nowrap" => "nowrap"), true, true, "", "", "string", true, 6, false));
    $myDataGrid->addColumn(new DataGrid_Column(getWords("level"), "position_code", array("width" => 80),  array("nowrap" => "nowrap"), true, true, "", "", "string", true, 6, false));
    $myDataGrid->addColumn(new DataGrid_Column(getWords("functional"), "functional_name", array("width" => 80),  array("nowrap" => "nowrap"), true, true, "", "", "string", true, 6, false));

    $myDataGrid->addColumn(new DataGrid_Column(getWords("status"), "employee_status", array("width" => 50),  array("align" => "center", "nowrap" => "nowrap"), true, true, "", "printStatus()", "string", true, 12));
    //$myDataGrid->addColumn(new DataGrid_Column(getWords("staff"), "position_group", array("rowspan" => 2, "width" => 50),  array("align" => "center", "nowrap" => "nowrap"), true, true, "", "printStaff()", "string", true, 12));
    //$myDataGrid->addColumn(new DataGrid_Column(getWords("working day"), "", array("colspan" => 3),  array("nowrap" => "nowrap"), true, true, "", "", "string", true, 15));
    //$myDataGrid->addColumn(new DataGrid_Column(getWords("total"), "working_day", array("width" => 50),  array("nowrap" => "nowrap", "align" => "right"), false, true, "", "", "numeric", true, 15));
    if (!$bolIrregular)
    {
      // $myDataGrid->addColumn(new DataGrid_Column(getWords("attendance day"), "attendance_day", array("rowspan" => 2, "width" => 50),  array("nowrap" => "nowrap", "align" => "right"), false, true, "", "", "numeric", true, 15, false));
      // $myDataGrid->addColumn(new DataGrid_Column(getWords("late day"), "late_day", array("rowspan" => 2, "width" => 50),  array("nowrap" => "nowrap", "align" => "right"), false, true, "", "", "numeric", true, 15, false));
      // $myDataGrid->addColumn(new DataGrid_Column("L/E", "l_e", array("rowspan" => 2, "width" => 50),  array("nowrap" => "nowrap", "align" => "right"), false, true, "", "", "numeric", true, 15, false));
      // //$myDataGrid->addColumn(new DataGrid_Column(getWords("shift"), "attendance_day", array("width" => 50),  array("nowrap" => "nowrap", "align" => "right"), false, true, "", "", "numeric", true, 15));
      // $myDataGrid->addSpannedColumn(getWords("overtime"), 4);
      // $myDataGrid->addColumn(new DataGrid_Column(getWords("ot1"), "ot1_min", array("width" => 30),  array("nowrap" => "nowrap", "align" => "right"), false, true, "", "", "numeric", true, 15));
      // $myDataGrid->addColumn(new DataGrid_Column(getWords("ot2"), "ot2_min", array("width" => 30),  array("nowrap" => "nowrap", "align" => "right"), false, true, "", "", "numeric", true, 15));
      // $myDataGrid->addColumn(new DataGrid_Column(getWords("ot3"), "ot3_min", array("width" => 30),  array("nowrap" => "nowrap", "align" => "right"), false, true, "", "", "numeric", true, 15));
      // $myDataGrid->addColumn(new DataGrid_Column(getWords("ot4"), "ot4_min", array("width" => 30),  array("nowrap" => "nowrap", "align" => "right"), false, true, "", "", "numeric", true, 15));
      // $myDataGrid->addColumn(new DataGrid_Column("Shift Hour", "shift_hour", array("rowspan" => 2, "width" => 30),  array("nowrap" => "nowrap", "align" => "right"), false, true, "", "", "numeric", true, 15, false));
      // $myDataGrid->addColumn(new DataGrid_Column("OT", "total_ot_min", array("rowspan" => 2, "width" => 30),  array("nowrap" => "nowrap", "align" => "right"), false, true, "", "", "numeric", true, 15, false));
      // $myDataGrid->addColumn(new DataGrid_Column(getWords("converted"), "otx_min", array("rowspan" => 2,"width" => 30),  array("nowrap" => "nowrap", "align" => "right"), false, true, "", "", "numeric", true, 15, false));
      // $myDataGrid->addColumn(new DataGrid_Column(getWords("overtime"), "overtime_allowance", array("rowspan" => 2, "width" => 270),  array("nowrap" => "nowrap", "align" => "right"), false, true, "", "formatNumeric()", "numeric", true, 15, true, "overtime_allowance"));
      //income
      //STANDARD
      //$myDataGrid->addSpannedColumn(getWords("income"), (1 + count($objSalary->arrMA) - $intIrrAllowance - $intIrrFixAllowance ));
      // $myDataGrid->addSpannedColumn(getWords("income"), (count($objSalary->arrMA) - $intIrrAllowance));
      // $myDataGrid->addColumn(new DataGrid_Column(getWords("overtime"), "overtime_allowance", array("width" => 270),  array("nowrap" => "nowrap", "align" => "right"), false, true, "", "formatNumeric()", "numeric", true, 15, true, "overtime_allowance"));
      // $myDataGrid->addColumn(new DataGrid_Column(getWords("tax allowance"), "tax_allowance", array("width" => 270),  array("nowrap" => "nowrap", "align" => "right"), false, true, "", "formatNumeric()", "numeric", true, 15, true, "tax_allowance"));
        // $myDataGrid->addSpannedColumn(getWords("income taxable"), 25);
        // $myDataGrid->addSpannedColumn(getWords("income"), 23);
        // $myDataGrid->addColumn(new DataGrid_Column(getWords("basic salary"), "alw_basic_salar", array("rowspan" => 2, "width" => 270),  array("align" => "right"), false, true, "", "formatNumeric()", "numeric", true, 15, true, "alw_basic_salary"));
        // $myDataGrid->addColumn(new DataGrid_Column(getWords("positional allowance"), "alw_positional_allowance", array("rowspan" => 1, "width" => 270),  array("align" => "right"), false, true, "", "formatNumeric()", "numeric", true, 15, true, "alw_positional_allowance"));
        // $myDataGrid->addColumn(new DataGrid_Column(getWords("skill allowance"), "alw_skill_allowance", array("rowspan" => 1, "width" => 270),  array("align" => "right"), false, true, "", "formatNumeric()", "numeric", true, 15, true, "alw_skill_allowance"));
        // $myDataGrid->addColumn(new DataGrid_Column(getWords("pmk allowance"), "seniority_allowance", array("rowspan" => 1, "width" => 270),  array("align" => "right"), false, true, "", "formatNumeric()", "numeric", true, 15, true, "seniority_allowance"));
        // $myDataGrid->addColumn(new DataGrid_Column(getWords("level allowance"), "position1_allowance", array("rowspan" => 1, "width" => 270),  array("align" => "right"), false, true, "", "formatNumeric()", "numeric", true, 15, true, "position1_allowance"));
        // $myDataGrid->addColumn(new DataGrid_Column(getWords("additional allowance"), "alw_additional_allowance", array("rowspan" => 1, "width" => 270),  array("align" => "right"), false, true, "", "formatNumeric()", "numeric", true, 15, true, "alw_additional_allowance"));
        // $myDataGrid->addColumn(new DataGrid_Column(getWords("transport allowance"), "transport", array("rowspan" => 1, "width" => 270),  array("align" => "right"), false, true, "", "formatNumeric()", "numeric", true, 15, true, "transport"));
        // $myDataGrid->addColumn(new DataGrid_Column(getWords("meal allowance"), "meal", array("rowspan" => 1, "width" => 270),  array("align" => "right"), false, true, "", "formatNumeric()", "numeric", true, 15, true, "meal"));
        // $myDataGrid->addColumn(new DataGrid_Column(getWords("prestasi allowance"), "alw_prestasi_allowance", array("rowspan" => 1, "width" => 270),  array("align" => "right"), false, true, "", "formatNumeric()", "numeric", true, 15, true, "alw_prestasi_allowance"));
        // $myDataGrid->addColumn(new DataGrid_Column(getWords("c o p allowance"), "alw_cop", array("rowspan" => 1, "width" => 270),  array("align" => "right"), false, true, "", "formatNumeric()", "numeric", true, 15, true, "alw_cop"));
//        $myDataGrid->addColumn(new DataGrid_Column(getWords("Total"), "", array("rowspan" => 2, "width" => 270),  array("align" => "right"), false, true, "", "formatNumeric()", "numeric", true, 15, true, ""));
        // $myDataGrid->addColumn(new DataGrid_Column(getWords("bpjs ks 4% allowance"), "bpjs_allowance", array("rowspan" => 1, "width" => 270),  array("align" => "right"), false, true, "", "formatNumeric()", "numeric", true, 15, true, "bpjs_allowance"));
//        $myDataGrid->addColumn(new DataGrid_Column(getWords("BPJS TK (JHT 3,7%) ALLOWANCE"), "jamsostek_allowance", array("rowspan" => 1, "width" => 270),  array("align" => "right"), false, true, "", "formatNumeric()", "numeric", true, 15, true, "jamsostek_allowance"));
        // $myDataGrid->addColumn(new DataGrid_Column(getWords("BPJS TK (JKK 0,54% dan 0,89%) ALLOWANCE"), "jkk_allowance", array("rowspan" => 1, "width" => 270),  array("align" => "right"), false, true, "", "formatNumeric()", "numeric", true, 15, true, "jkk_allowance"));
        // $myDataGrid->addColumn(new DataGrid_Column(getWords("BPJS TK (JKM 0,30%) ALLOWANCE"), "jkm_allowance", array("rowspan" => 1, "width" => 270),  array("align" => "right"), false, true, "", "formatNumeric()", "numeric", true, 15, true, "jkm_allowance"));
        // $myDataGrid->addColumn(new DataGrid_Column(getWords("Pension (2%) ALLOWANCE"), "pension_allowance", array("rowspan" => 1, "width" => 270),  array("align" => "right"), false, true, "", "formatNumeric()", "numeric", true, 15, true, "pension_allowance"));
        // $myDataGrid->addColumn(new DataGrid_Column(getWords("placement allowance"), "alw_placement_allowance", array("rowspan" => 1, "width" => 270),  array("align" => "right"), false, true, "", "formatNumeric()", "numeric", true, 15, true, "alw_placement_allowance"));
        // $myDataGrid->addColumn(new DataGrid_Column(getWords("additional allowance"), "alw_additional_allowance_benefit", array("rowspan" => 1, "width" => 270),  array("align" => "right"), false, true, "", "formatNumeric()", "numeric", true, 15, true, "alw_additional_allowance_benefit"));
        // $myDataGrid->addColumn(new DataGrid_Column(getWords("total overtime days"), "total_ot_day", array("rowspan" => 2, "width" => 270),  array("align" => "right"), false, true, "", "formatNumeric()", "numeric", true, 15, true, "total_ot_day"));
        $myDataGrid->addColumn(new DataGrid_Column(getWords("total overtime hours"), "total_ot_min", array("width" => 270),  array("align" => "right"), false, true, "", "formatNumeric()", "numeric", true, 15, true, "total_ot_min"));
        $myDataGrid->addColumn(new DataGrid_Column(getWords("overtime allowance"), "overtime_allowance", array("width" => 270),  array("align" => "right"), false, true, "", "formatNumeric()", "numeric", true, 15, true, "overtime_allowance"));
        // $myDataGrid->addColumn(new DataGrid_Column(getWords("perjalanan dinas"), "alw_business_trip", array("rowspan" => 1, "width" => 270),  array("align" => "right"), false, true, "", "formatNumeric()", "numeric", true, 15, true, "alw_business_trip"));
        // $myDataGrid->addColumn(new DataGrid_Column(getWords("bonus"), "alw_bonus", array("rowspan" => 1, "width" => 270),  array("align" => "right"), false, true, "", "formatNumeric()", "numeric", true, 15, true, "alw_bonus"));
        // $myDataGrid->addColumn(new DataGrid_Column(getWords("thr"), "thr_allowance", array("rowspan" => 1, "width" => 270),  array("align" => "right"), false, true, "", "formatNumeric()", "numeric", true, 15, true, "thr_allowance"));
        // $myDataGrid->addColumn(new DataGrid_Column(getWords("rapel salary gross"), "alw_rapel_salary_gross", array("rowspan" => 1, "width" => 270),  array("align" => "right"), false, true, "", "formatNumeric()", "numeric", true, 15, true, "alw_rapel_salary_gross"));
//        $myDataGrid->addColumn(new DataGrid_Column(getWords("tax allowance"), "tax_allowance", array("rowspan" => 1, "width" => 270),  array("align" => "right"), false, true, "", "formatNumeric()", "numeric", true, 15, true, ""));
        // $myDataGrid->addColumn(new DataGrid_Column(getWords("excess"), "alw_excess", array("rowspan" => 1, "width" => 270),  array("align" => "right"), false, true, "", "formatNumeric()", "numeric", true, 15, true, "alw_excess"));
        // $myDataGrid->addColumn(new DataGrid_Column(getWords("rapel salary net"), "alw_rapel_salary_net", array("rowspan" => 1, "width" => 270),  array("align" => "right"), false, true, "", "formatNumeric()", "numeric", true, 15, true, "alw_rapel_salary_net"));
        // $myDataGrid->addColumn(new DataGrid_Column(getWords("severance"), "alw_severance", array("rowspan" => 1, "width" => 270),  array("align" => "right"), false, true, "", "formatNumeric()", "numeric", true, 15, true, "alw_severance"));
//        $myDataGrid->addColumn(new DataGrid_Column(getWords("total gross income"), "", array("rowspan" => 2, "width" => 270),  array("align" => "right"), false, true, "", "formatNumeric()", "numeric", true, 15, true, ""));
        // $myDataGrid->addSpannedColumn(getWords("deduction"), 19 + $intLoanType);
        // $myDataGrid->addColumn(new DataGrid_Column(getWords("absen deduction"), "absence_deduction", array("rowspan" => 1, "width" => 270),  array("align" => "right"), false, true, "", "formatNumeric()", "numeric", true, 15, true, "absence_deduction"));
        // $myDataGrid->addColumn(new DataGrid_Column(getWords("late absent deduction"), "late_deduction", array("rowspan" => 1, "width" => 270),  array("align" => "right"), false, true, "", "formatNumeric()", "numeric", true, 15, true, "late_deduction"));
        // $myDataGrid->addColumn(new DataGrid_Column(getWords("position deduction"), "ded_position_deduction", array("rowspan" => 1, "width" => 270),  array("align" => "right"), false, true, "", "formatNumeric()", "numeric", true, 15, true, "ded_position_deduction"));
//        $myDataGrid->addColumn(new DataGrid_Column(getWords("sp deduction"), "ded_sp_deduction", array("rowspan" => 1, "width" => 270),  array("align" => "right"), false, true, "", "formatNumeric()", "numeric", true, 15, true, ""));
//        $myDataGrid->addColumn(new DataGrid_Column(getWords("salary deduction"), "ded_salary_deduction", array("rowspan" => 1, "width" => 270),  array("align" => "right"), false, true, "", "formatNumeric()", "numeric", true, 15, true, ""));
//        $myDataGrid->addColumn(new DataGrid_Column(getWords("excess payment salary"), "ded_excess_payment_salary", array("rowspan" => 1, "width" => 270),  array("align" => "right"), false, true, "", "formatNumeric()", "numeric", true, 15, true, ""));
//        $myDataGrid->addColumn(new DataGrid_Column(getWords("other hrd deduction"), "ded_other_hrd_deduction", array("rowspan" => 1, "width" => 270),  array("align" => "right"), false, true, "", "formatNumeric()", "numeric", true, 15, true, ""));
        // $myDataGrid->addColumn(new DataGrid_Column(getWords("late absen ti deduction"), "late_ti_deduction", array("rowspan" => 1, "width" => 270),  array("align" => "right"), false, true, "", "formatNumeric()", "numeric", true, 15, true, "late_ti_deduction"));
//        $myDataGrid->addColumn(new DataGrid_Column(getWords("underpayment tax pph21 periode 2014"), "ded_underpayment_tax_pph21_prev", array("rowspan" => 1, "width" => 270),  array("align" => "right"), false, true, "", "formatNumeric()", "numeric", true, 15, true, ""));
//        $myDataGrid->addColumn(new DataGrid_Column(getWords("sim deduction"), "ded_sim_deduction", array("rowspan" => 1, "width" => 270),  array("align" => "right"), false, true, "", "formatNumeric()", "numeric", true, 15, true, ""));
//        $myDataGrid->addColumn(new DataGrid_Column(getWords("shoes deduction"), "ded_shoes_deduction", array("rowspan" => 1, "width" => 270),  array("align" => "right"), false, true, "", "formatNumeric()", "numeric", true, 15, true, ""));
//        $myDataGrid->addColumn(new DataGrid_Column(getWords("camera deduction"), "ded_camera_deduction", array("rowspan" => 1, "width" => 270),  array("align" => "right"), false, true, "", "formatNumeric()", "numeric", true, 15, true, ""));
//        $myDataGrid->addColumn(new DataGrid_Column(getWords("laptop deduction"), "ded_laptop_deduction", array("rowspan" => 1, "width" => 270),  array("align" => "right"), false, true, "", "formatNumeric()", "numeric", true, 15, true, ""));
//        $myDataGrid->addColumn(new DataGrid_Column(getWords("additional tools deduction"), "ded_additional_tools_deduction", array("rowspan" => 1, "width" => 270),  array("align" => "right"), false, true, "", "formatNumeric()", "numeric", true, 15, true, ""));
//        $myDataGrid->addColumn(new DataGrid_Column(getWords("personal loan deduction"), "", array("rowspan" => 1, "width" => 270),  array("align" => "right"), false, true, "", "formatNumeric()", "numeric", true, 15, true, ""));
//         if($intLoanType > 1)
//         {
// //          $myDataGrid->addSpannedColumn(getWords("loan and payment"), $intLoanType);
//           foreach ($arrLoanType AS $strCode => $strName) // looping data tunjangan lain-lain
//           {
//             $myDataGrid->addColumn(new DataGrid_Column($strName, "loan_".$strCode, array("width" => 270),  array("nowrap" => "nowrap", "align" => "right"), false, true, "", "formatNumeric()", "numeric", true, 15, true, "loan_".$strCode));
//           }
//         }
//         else
//         {
//           foreach ($arrLoanType AS $strCode => $strName) // looping data tunjangan lain-lain
//           {
//             $myDataGrid->addColumn(new DataGrid_Column($strName, "loan_".$strCode, array("rowspan" => 2, "width" => 270),  array("nowrap" => "nowrap", "align" => "right"), false, true, "", "formatNumeric()", "numeric", true, 15, true, "loan_".$strCode));
//           }
//         }
//        $myDataGrid->addColumn(new DataGrid_Column(getWords("cash bon deduction"), "ded_cash_bon", array("rowspan" => 1, "width" => 270),  array("align" => "right"), false, true, "", "formatNumeric()", "numeric", true, 15, true, ""));
//        $myDataGrid->addColumn(new DataGrid_Column(getWords("jpk"), "ded_jpk", array("rowspan" => 1, "width" => 270),  array("align" => "right"), false, true, "", "formatNumeric()", "numeric", true, 15, true, ""));
//        $myDataGrid->addColumn(new DataGrid_Column(getWords("master side allowance"), "ded_master_side_allowance", array("rowspan" => 1, "width" => 270),  array("align" => "right"), false, true, "", "formatNumeric()", "numeric", true, 15, true, ""));
//        $myDataGrid->addColumn(new DataGrid_Column(getWords("additional finance deduction"), "ded_additional_finance_deduction", array("rowspan" => 1, "width" => 270),  array("align" => "right"), false, true, "", "formatNumeric()", "numeric", true, 15, true, ""));
        // $myDataGrid->addColumn(new DataGrid_Column(getWords("overtime deduction"), "overtime_allowance_ded", array("rowspan" => 1, "width" => 270),  array("align" => "right"), false, true, "", "formatNumeric()", "numeric", true, 15, true, "overtime_allowance_ded"));
        // $myDataGrid->addColumn(new DataGrid_Column(getWords("placement deduction"), "alw_placement_allowance_ded", array("rowspan" => 1, "width" => 270),  array("align" => "right"), false, true, "", "formatNumeric()", "numeric", true, 15, true, "alw_placement_allowance_ded"));
        // $myDataGrid->addColumn(new DataGrid_Column(getWords("business trip deduction"), "alw_business_trip_ded", array("rowspan" => 1, "width" => 270),  array("align" => "right"), false, true, "", "formatNumeric()", "numeric", true, 15, true, "alw_business_trip_ded"));
        // $myDataGrid->addColumn(new DataGrid_Column(getWords("other deduction"), "alw_additional_allowance_benefit_ded", array("rowspan" => 1, "width" => 270),  array("align" => "right"), false, true, "", "formatNumeric()", "numeric", true, 15, true, "additional_allowance_benefit_ded"));
        // $myDataGrid->addColumn(new DataGrid_Column(getWords("other deduction managerial"), "ded_other_deduction_managerial", array("rowspan" => 1, "width" => 270),  array("align" => "right"), false, true, "", "formatNumeric()", "numeric", true, 15, true, "ded_other_deduction_managerial"));
        // $myDataGrid->addColumn(new DataGrid_Column(getWords("bpjs ks employee 1% deduction"), "bpjs_deduction", array("rowspan" => 1, "width" => 270),  array("align" => "right"), false, true, "", "formatNumeric()", "numeric", true, 15, true, "bpjs_deduction"));
        // $myDataGrid->addColumn(new DataGrid_Column(getWords("bpjs ks company 4% deduction"), "bpjs_allowance_ded", array("rowspan" => 1, "width" => 270),  array("align" => "right"), false, true, "", "formatNumeric()", "numeric", true, 15, true, "bpjs_allowance_ded"));
        // $myDataGrid->addColumn(new DataGrid_Column(getWords("BPJS TK (JKK 0,54% dan 0,89%) ALLOWANCE"), "jkk_allowance_ded", array("rowspan" => 1, "width" => 270),  array("align" => "right"), false, true, "", "formatNumeric()", "numeric", true, 15, true, "jkk_allowance_ded"));
        // $myDataGrid->addColumn(new DataGrid_Column(getWords("BPJS TK (JKM 0,30%) DEDUCTION"), "jkm_allowance_ded", array("rowspan" => 1, "width" => 270),  array("align" => "right"), false, true, "", "formatNumeric()", "numeric", true, 15, true, "jkm_allowance_ded"));
        // $myDataGrid->addColumn(new DataGrid_Column(getWords("BPJS TK (JHT 2%) DEDUCTION"), "jamsostek_deduction", array("rowspan" => 1, "width" => 270),  array("align" => "right"), false, true, "", "formatNumeric()", "numeric", true, 15, true, "jamsostek_deduction"));
        // $myDataGrid->addColumn(new DataGrid_Column(getWords("Pension (1%) DEDUCTION"), "pension_deduction", array("rowspan" => 1, "width" => 270),  array("align" => "right"), false, true, "", "formatNumeric()", "numeric", true, 15, true, "pension_deduction"));
//        $myDataGrid->addColumn(new DataGrid_Column(getWords("tax pph21 deduction (by system)"), "total_tax", array("rowspan" => 1, "width" => 270),  array("align" => "right"), false, true, "", "formatNumeric()", "numeric", true, 15, true, ""));
        // $myDataGrid->addColumn(new DataGrid_Column(getWords("tax pph21 deduction"), "ded_tax_manual", array("rowspan" => 1, "width" => 270),  array("align" => "right"), false, true, "", "formatNumeric()", "numeric", true, 15, true, "ded_tax_manual"));
        // $myDataGrid->addColumn(new DataGrid_Column(getWords("hrd deduction"), "ded_deduction_hrd", array("rowspan" => 1, "width" => 270),  array("align" => "right"), false, true, "", "formatNumeric()", "numeric", true, 15, true, "ded_deduction_hrd"));
        // $myDataGrid->addColumn(new DataGrid_Column(getWords("finance deduction"), "ded_deduction_fa", array("rowspan" => 1, "width" => 270),  array("align" => "right"), false, true, "", "formatNumeric()", "numeric", true, 15, true, "ded_deduction_fa"));
        // $myDataGrid->addColumn(new DataGrid_Column(getWords("tools deduction"), "ded_deduction_tools", array("rowspan" => 1, "width" => 270),  array("align" => "right"), false, true, "", "formatNumeric()", "numeric", true, 15, true, "ded_deduction_tools"));
//        $myDataGrid->addColumn(new DataGrid_Column(getWords("severance tax deduction"), "ded_severance_tax_deduction", array("rowspan" => 1, "width" => 270),  array("align" => "right"), false, true, "", "formatNumeric()", "numeric", true, 15, true, ""));
//        $myDataGrid->addColumn(new DataGrid_Column(getWords("total deduction"), "", array("rowspan" => 2, "width" => 270),  array("align" => "right"), false, true, "", "formatNumeric()", "numeric", true, 15, true, ""));
        // $myDataGrid->addColumn(new DataGrid_Column(getWords("take home pay"), "total_gross", array("rowspan" => 2, "width" => 270),  array("align" => "right"), false, true, "", "formatNumeric()", "numeric", true, 15, true, ""));
        // $myDataGrid->addColumn(new DataGrid_Column(getWords("BPJS TK (JHT 3.7%) DEDUCTION"), "jamsostek_allowance_ded", array("rowspan" => 2, "width" => 270),  array("align" => "right"), false, true, "", "formatNumeric()", "numeric", true, 15, true, "jamsostek_allowance_ded"));
        // $myDataGrid->addColumn(new DataGrid_Column(getWords("Pension (2%) DEDUCTION"), "pension_allowance_ded", array("rowspan" => 2, "width" => 270),  array("align" => "right"), false, true, "", "formatNumeric()", "numeric", true, 15, true, "pension_allowance_ded"));

      // array_walk($objSalary->arrMA, 'printColumn', $myDataGrid);

/*

      $myDataGrid->addColumn(new DataGrid_Column(getWords("overtime"), "overtime_allowance", array("width" => 270),  array("nowrap" => "nowrap", "align" => "right"), false, true, "", "formatNumeric()", "numeric", true, 15, true, "overtime_allowance"));
      //$myDataGrid->addColumn(new DataGrid_Column(getWords("overtime"), "overtime_allowance", array("width" => 270),  array("nowrap" => "nowrap", "align" => "right"), false, true, "", "formatNumeric()", "numeric", true, 15, true, "overtime_allowance"));
      $myDataGrid->addColumn(new DataGrid_Column(getWords("jamsostek"), "jamsostek_allowance", array("width" => 270),  array("nowrap" => "nowrap", "align" => "right"), false, true, "", "formatNumeric()", "numeric", true, 15,  true, "jamsostek_allowance"));
      $myDataGrid->addColumn(new DataGrid_Column(getWords("jkk"), "jkk_allowance", array("width" => 270),  array("nowrap" => "nowrap", "align" => "right"), false, true, "", "formatNumeric()", "numeric", true, 15, true, "jkk_allowance"));
      $myDataGrid->addColumn(new DataGrid_Column(getWords("jkm"), "jkm_allowance", array("width" => 270),  array("nowrap" => "nowrap", "align" => "right"), false, true, "", "formatNumeric()", "numeric", true, 15, true, "jkm_allowance"));
*/

      foreach ($arrOtherAllowance AS $strCode => $strName) // looping data tunjangan lain-lain
      {
        // $myDataGrid->addColumn(new DataGrid_Column($strName, "alw_".$strCode, array("width" => 270),  array("nowrap" => "nowrap", "align" => "right"), false, true, "", "formatNumeric()", "numeric", true, 15, true, "alw_".$strCode));
      }
//    $myDataGrid->addColumn(new DataGrid_Column(getWords("total allowance"), "total_net", array("width" => 270),  array("nowrap" => "nowrap", "align" => "right"), false, true, "", "formatNumeric()", "numeric", true, 15, true, "total_net"));

    }
    if ($intIrrAllowance + $intIrrFixAllowance > 1)
    {
      // $myDataGrid->addSpannedColumn(getWords("irregular income"), ($intIrrAllowance + $intIrrFixAllowance ));
      // foreach ($arrIrrFixAllowance AS $strCode => $strName) // looping data irregular income
      // {
      //   $myDataGrid->addColumn(new DataGrid_Column($strName, $strCode, array("width" => 270),  array("nowrap" => "nowrap", "align" => "right"), false, true, "", "formatNumeric()", "numeric", true, 15, true, $strCode));
      // }
      // foreach ($arrIrrAllowance AS $strCode => $strName) // looping data irregular income
      // {
      //   $myDataGrid->addColumn(new DataGrid_Column($strName, "alw_".$strCode, array("width" => 270),  array("nowrap" => "nowrap", "align" => "right"), false, true, "", "formatNumeric()", "numeric", true, 15, true, "alw_".$strCode));
      // }
      // $myDataGrid->addColumn(new DataGrid_Column(getWords("sub total"), "total_net_irregular", array("rowspan" => 2, "width" => 270),  array("nowrap" => "nowrap", "align" => "right"), false, true, "", "formatNumeric()", "numeric", true, 15, true, "total_irregular"));

    }
    else
    {
      // foreach ($arrIrrFixAllowance AS $strCode => $strName) // looping data irregular income
      // {
      //   $myDataGrid->addColumn(new DataGrid_Column($strName, $strCode, array("rowspan" => 2, "width" => 270),  array("nowrap" => "nowrap", "align" => "right"), false, true, "", "formatNumeric()", "numeric", true, 15, true, $strCode));
      // }
      // foreach ($arrIrrAllowance AS $strCode => $strName) // looping data irregular income
      // {
      //   $myDataGrid->addColumn(new DataGrid_Column($strName, "alw_".$strCode, array("rowspan" => 2, "width" => 270),  array("nowrap" => "nowrap", "align" => "right"), false, true, "", "formatNumeric()", "numeric", true, 15, true, "alw_".$strCode));
      // }
    }

    if ($bolIrregular)
    {
      //$myDataGrid->addSpannedColumn(getWords("deduction"), 2);
      // $myDataGrid->addColumn(new DataGrid_Column(getWords("irregular tax"), "irregular_tax", array("width" => 270),  array("nowrap" => "nowrap", "align" => "right"), false, true, "", "formatNumeric()", "numeric", true, 15, true, "irregular_tax_deduction"));
      // //$myDataGrid->addColumn(new DataGrid_Column(getWords("zakat"), "zakat_deduction_irregular", array("width" => 270),  array("nowrap" => "nowrap", "align" => "right"), false, true, "", "formatNumeric()", "numeric", true, 15, true, "zakat_deduction_irregular"));
      // $myDataGrid->addColumn(new DataGrid_Column(getWords("total"), "total_gross_irregular", array("rowspan" => 2, "width" => 270),  array("align" => "right"), false, true, "", "formatNumeric()", "numeric", true, 15, true, "total_gross_irregular"));
      // $myDataGrid->addColumn(new DataGrid_Column(getWords("no rekening"), "", array("rowspan" => 2, "width" => 270),  array("align" => "right"), false, true, "", "", "numeric", true, 15, true, ""));
      // $myDataGrid->addColumn(new DataGrid_Column(getWords("no rekening"), "", array("rowspan" => 2, "width" => 270),  array("align" => "right"), false, true, "", "", "numeric", true, 15, true, ""));
      // $myDataGrid->addColumn(new DataGrid_Column(getWords("code"), "", array("rowspan" => 2, "width" => 270),  array("align" => "right"), false, true, "", "", "numeric", true, 15, true, ""));
      // $myDataGrid->addColumn(new DataGrid_Column(getWords("status"), "", array("rowspan" => 2, "width" => 270),  array("align" => "right"), false, true, "", "", "numeric", true, 15, true, ""));


    }
    else
    {

//       //deduction
//       $myDataGrid->addSpannedColumn(getWords("deduction"), (3 + $intOtherDeduction));
//       //$myDataGrid->addColumn(new DataGrid_Column(getWords("loan"), "loan_deduction", array("width" => 270),  array("nowrap" => "nowrap", "align" => "right"), false, true, "", "formatNumeric()", "numeric", true, 15));
//       $myDataGrid->addColumn(new DataGrid_Column(getWords("jamsostek"), "jamsostek_deduction", array("width" => 270),  array("nowrap" => "nowrap", "align" => "right"), false, true, "", "formatNumeric()", "numeric", true, 15, true, "jamsostek_deduction"));
//       $myDataGrid->addColumn(new DataGrid_Column(getWords("tax"), "total_tax", array("width" => 270),  array("nowrap" => "nowrap", "align" => "right"), false, true, "", "formatNumeric()", "numeric", true, 15, true, "tax_deduction"));
//       foreach ($arrOtherDeduction AS $strCode => $strName) // looping data tunjangan lain-lain
//       {
//         $myDataGrid->addColumn(new DataGrid_Column($strName, "ded_".$strCode, array("width" => 270),  array("nowrap" => "nowrap", "align" => "right"), false, true, "", "formatNumeric()", "numeric", true, 15, true, "ded_".$strCode));
//       }
//       //Potongan Terlambat dan Pulang Cepat
//       $myDataGrid->addColumn(new DataGrid_Column(getWords("potongan L/E"), "absence_deduction", array("width" => 270),  array("nowrap" => "nowrap", "align" => "right"), false, true, "", "formatNumeric()", "numeric", true, 15, true, "absence_deduction"));
// //      $myDataGrid->addColumn(new DataGrid_Column(getWords("total deduction"), "total_deduction", array("width" => 270),  array("nowrap" => "nowrap", "align" => "right"), false, true, "", "formatNumeric()", "numeric", true, 15, true, "total_deduction"));


//      //loan
//      if($intLoanType > 1)
//      {
//        $myDataGrid->addSpannedColumn(getWords("loan and payment"), $intLoanType);
//        foreach ($arrLoanType AS $strCode => $strName) // looping data tunjangan lain-lain
//        {
//          $myDataGrid->addColumn(new DataGrid_Column($strName, "loan_".$strCode, array("width" => 270),  array("nowrap" => "nowrap", "align" => "right"), false, true, "", "formatNumeric()", "numeric", true, 15, true, "loan_".$strCode));
//        }
//      }
//      else
//      {
//        foreach ($arrLoanType AS $strCode => $strName) // looping data tunjangan lain-lain
//        {
//          $myDataGrid->addColumn(new DataGrid_Column($strName, "loan_".$strCode, array("rowspan" => 2, "width" => 270),  array("nowrap" => "nowrap", "align" => "right"), false, true, "", "formatNumeric()", "numeric", true, 15, true, "loan_".$strCode));
//        }
//      }
//      $myDataGrid->addColumn(new DataGrid_Column(getWords("total"), "total_gross", array("rowspan" => 2, "width" => 270),  array("align" => "right"), false, true, "", "formatNumeric()", "numeric", true, 15, true, "total_gross"));
      $myDataGrid->addColumn(new DataGrid_Column(getWords("no rekening"), "bank_account", array("width" => 270),  array("align" => "right"), false, true, "", "", "string", true, 6, false, ""));
      $myDataGrid->addColumn(new DataGrid_Column(getWords("bank"), "bank_name", array("width" => 270),  array("align" => "right"), false, true, "", "", "string", true, 6, false, ""));
      // $myDataGrid->addColumn(new DataGrid_Column(getWords("code"), "transfer_code", array("rowspan" => 2, "width" => 270),  array("align" => "right"), false, true, "", "", "string", true, 6, false, ""));
      $myDataGrid->addColumn(new DataGrid_Column(getWords("transfer status"), "transfer_status_name", array("width" => 270),  array("align" => "right"), false, true, "", "", "string", true, 6, false, "transfer_status"));
      $myDataGrid->addColumn(new DataGrid_Column(getWords("release note"), "release_note", array("width" => 270),  array("align" => "right"), false, true, "", "", "string", true, 6, false, "release_note"));
    }
    if ($isExport)
    {
      $myDataGrid->DATAGRID_RENDER_OUTPUT = DATAGRID_RENDER_EXCEL_BIFF;
      $myDataGrid->strFileNameXLS = "overtime.xls";
      $myDataGrid->strTitle1 = getWords("overtime");
      if (!$isFull){
	      $myDataGrid->groupBy ( "branch_cost_center_code" );
	      $myDataGrid->hasGrandTotal = true;
	    }
    }

    $myDataGrid->addRepeaterFunction("printDeniedNote()");


    if (!$bolPrint)
    {
      /*
      if($_SESSION['sessionUserRole'] == ROLE_SUPER)
      {
         $myDataGrid->addSpecialButton("btnFinish", "btnFinish", "submit", getWords("check"), "onClick=\"return confirmCheck();\"", "finishData()");
         myDataGrid->addSpecialButton("btnApprove", "btnApprove", "submit", getWords("approve"), "onClick=\"return confirmCheck();\"", "approveData()");
      }
      */
      $myDataGrid->addSpecialButton("btnSlip", "btnSlip", "submit", getWords("get overtime slip"), "onClick=\"document.formData.target = '_blank'\"", "getSlip()");
      foreach ($arrSalaryTransferType as $key => $index)
      {
      	$myDataGrid->addSpecialButton("btn".$key, "btn".$key, "submit", getWords($index), "", "setStatus()", "salary-status-click");
      }

      if ($bolCanEdit)
      {
        //$myDataGrid->addButton("btnPrint", "btnPrint", "submit", getWords("print"), "onClick=\"document.formData.target = '_blank';\"");
        $myDataGrid->addButton("btnCalculate", "btnCalculate", "submit", getWords("recalculate"), "onClick=\"return confirm('Are you sure want to recalculate this salary calculation?');\"", "saveData()");
        $myDataGrid->addButtonExportExcel("Export Excel", "salary.xls", getWords("list of salary"));
        $myDataGrid->addButton("btnExportFull", "btnExportFull", "submit", getWords("export without group"), "", "");
        $myDataGrid->addButton("btnAutoHoldAccount", "btnAutoHoldAccount", "submit", getWords("Auto Hold Empty Account"), "onClick=\"return confirm('Are you sure to auto hold all employee with empty bank account?');\"", "autoHoldBankAccount()");
        //$myDataGrid->addButton("btnAutoReleaseAccount", "btnAutoReleaseAccount", "submit", getWords("Auto Release"), "onClick=\"return confirm('Are you sure to auto release all employee with not empty bank account?');\"", "autoReleaseBankAccount()");
      }
    }

    $myDataGrid->getRequest();
    //--------------------------------
    //get Data and set to Datagrid's DataSource by set the data binding (bind method)


    $strCriteriaFlag = "";//$myDataGrid->getCriteria()." AND (flag = 0 OR (flag=0 AND (\"link_id\" IS NULL))) ".$strCriteria;
//    $strOrderBy = "employee_name";
    if ($bolLimit)
    {
      $strPageLimit = $myDataGrid->getPageLimit();
      $intPageNumber = $myDataGrid->getPageNumber();
    }
    else
    {
      $strPageLimit = null;
      $intPageNumber = null;
    }

    // cari total
    $strSQL = "
      SELECT COUNT(t1.id) AS total
      FROM (
        SELECT *
        FROM hrd_salary_detail WHERE id_salary_master = '$strDataID'
      ) AS t1
      LEFT JOIN (
        SELECT id, employee_name, join_date, id_company, gender, salary_currency, branch_cost_center_code
        FROM hrd_employee WHERE 1=1 $strCriteria
      ) AS t2 ON t1.id_employee = t2.id
      LEFT JOIN (
        SELECT id_employee, transfer_code FROM hrd_salary_transfer_status WHERE id_salary_master = '$strDataID'
      ) AS t5 ON t5.id_employee = t1.id_employee
      WHERE 1=1 $strCriteria $strKriteriaTransfer

    ";
    if ($bolHideBlank)
    {
      if($bolIrregular)
        $strSQL .= "AND total_net_irregular > 0 AND total_gross_irregular > 0";
      else
        $strSQL .= "AND total_net > 0 AND total_gross > 0";
    }

    $res = $db->execute($strSQL);
    if ($row = $db->fetchrow($res))
    {
      $myDataGrid->totalData = ($row['total'] == "") ? 0 : $row['total'];
    }

    //get Data and set to Datagrid's DataSource by set the data binding (bind method)
    $strSQL = "
      SELECT t1.*, t2.employee_name, t2.join_date, t2.resign_date,  t2.gender, t2.salary_currency, 
      t2.active, t3.position_group, t4.functional_name, bank_account, branch_penugasan_code, 
      branch_bpjs_ks_code, t5.transfer_code, t2.employee_id_2, t2.bank_code, branch_cost_center_code,
      t5.note as release_note , t5.release_number, t6.bank_name
        $strOtherAllowance $strOtherDeduction
      FROM (
        SELECT *
        FROM hrd_salary_detail WHERE id_salary_master = '$strDataID'
      ) AS t1
      LEFT JOIN (
        SELECT id, employee_name, join_date, resign_date,  id_company, gender, salary_currency, active, functional_code, bank_account, branch_code, branch_cost_center_code, branch_penugasan_code, branch_bpjs_ks_code, employee_id_2, bank_code
        FROM hrd_employee WHERE 1=1 $strCriteria
      ) AS t2 ON t1.id_employee = t2.id
      LEFT JOIN (
        SELECT position_code, position_group
        FROM hrd_position
      ) AS t3 ON t1.position_code= t3.position_code
      LEFT JOIN (
        SELECT functional_code, functional_name
        FROM hrd_functional
      ) AS t4 ON t2.functional_code= t4.functional_code
      LEFT JOIN (
        SELECT id_employee, transfer_code,note,release_number FROM hrd_salary_transfer_status WHERE id_salary_master = '$strDataID'
      ) AS t5 ON t5.id_employee = t1.id_employee
      LEFT JOIN (
        SELECT bank_code, bank_name FROM hrd_bank
      ) AS t6 ON t2.bank_code = t6.bank_code
      WHERE 1=1 $strCriteria $strKriteriaTransfer
    ";

// die($strSQL);

    if ($bolHideBlank)
    {
      if($bolIrregular)
        $strSQL .= "AND total_net_irregular > 0 AND total_gross_irregular > 0";
      else
        $strSQL .= "AND total_net > 0 AND total_gross > 0";
    }
    //handle sort

    if ($isExport) $strSQL .= " ORDER BY branch_cost_center_code, t2.join_date ";
    else if ($myDataGrid->isShowSort)
      if ($myDataGrid->pageSortBy!="") $strSQL .= " ORDER BY " . $myDataGrid->sortName . " " . $myDataGrid->sortOrder;

    //handle page limit
    if ($myDataGrid->isShowPageLimit && !$isExport)
      if (is_numeric($myDataGrid->pageLimit) && $myDataGrid->pageLimit > 0)
        $strSQL .= " LIMIT $myDataGrid->pageLimit OFFSET ".$myDataGrid->getOffsetStart();


    //get query
    $dataset = array();
    $resDb = $db->execute($strSQL);
    //put result to array dataset
    while ($rowDb = $db->fetchrow($resDb))
    {
      //$rowDb['total_ot_min'] = (1.5 * $rowDb['ot1_min']) * (2 * $rowDb['ot2_min']) * (3 * $rowDb['ot3_min']) * (4 * $rowDb['ot4_min']); // hardcode
      $rowDb['total_tax'] = $rowDb['tax'] + $rowDb['irregular_tax'];
      //$rowDb['tax_allowance'] = $rowDb['total_tax'];
      $rowDb['shift_hour'] = $rowDb['shift_hour'] / 60;
      $rowDb['l_e'] = standardFormat($rowDb['late_round'] + $rowDb['early_round'], true, 2) ;
      $rowDb['ot1_min'] = ($rowDb['ot1_min']) / 60;
      $rowDb['ot2_min'] = ($rowDb['ot2_min'] + $rowDb['ot2b_min']) / 60;
      $rowDb['ot3_min'] = $rowDb['ot3_min'] / 60;
      $rowDb['ot4_min'] = $rowDb['ot4_min'] / 60;
      foreach ($arrSalaryTransferType as $key => $index)
      {
        if ($rowDb['transfer_code'] == $key)
        {
          $rowDb['transfer_status_name'] = $index;
        }
      }

      $rowDb['total_ot_min'] = $rowDb['total_ot_min'] / 60;
      $rowDb['otx_min'] = $rowDb['ot1_min']*1.5 + $rowDb['ot2_min']*2 + $rowDb['ot3_min']*3 + $rowDb['ot4_min']*4;

      foreach ($arrOtherAllowance AS $strCode => $strName)
      {
        if (isset($objSalary->arrDA[$strCode][$rowDb['id_employee']]))
          $rowDb['alw_'.$strCode] = $objSalary->arrDA[$strCode][$rowDb['id_employee']]['amount'];
      }

      foreach ($arrIrrAllowance AS $strCode => $strName)
      {
        if (isset($objSalary->arrDA[$strCode][$rowDb['id_employee']]))
          $rowDb['alw_'.$strCode] = $objSalary->arrDA[$strCode][$rowDb['id_employee']]['amount'];
      }
      foreach ($arrOtherDeduction AS $strCode => $strName)
      {
        if (isset($objSalary->arrDD[$strCode][$rowDb['id_employee']])){

              $rowDb['ded_'.$strCode] = $objSalary->arrDD[$strCode][$rowDb['id_employee']]['amount'];
        }

      }

      foreach ($arrLoanType AS $strCode => $strName)
      {
        if (isset($arrEmployeeLoan[$strCode][$rowDb['id_employee']]))
          $rowDb['loan_'.$strCode] = $arrEmployeeLoan[$strCode][$rowDb['id_employee']]['amount'];
        else
          $rowDb['loan_'.$strCode] = 0;
      }
      $intRound = (isset($objSalary->arrConf['salary_round']) && is_numeric($objSalary->arrConf['salary_round'])) ? $objSalary->arrConf['salary_round'] : 1;


      //Hardcode, never use this lines on standard package
      //----------------------
      $rowDb['total_gross']=$objSalary->arrDetail[$rowDb['id_employee']]['total_gross_round'];
      $rowDb['total_gross']=roundMoney($rowDb['total_gross'], $intRound);
      $rowDb['transfer_income'] = $rowDb['total_gross'] - $rowDb['cash_income'];
      $rowDb['meal'] = $rowDb['alw_meal_allowance'] + $rowDb['alw_meal_allowance_managerial'];
      $rowDb['transport'] = $rowDb['alw_transport_allowance'] + $rowDb['alw_transport_allowance_managerial'];
      $rowDb['overtime_allowance_ded'] = $rowDb['overtime_allowance'];
      $rowDb['bpjs_allowance_ded'] = $rowDb['bpjs_allowance'];
      $rowDb['jamsostek_allowance_ded'] = $rowDb['jamsostek_allowance'];
      $rowDb['jkk_allowance_ded'] = $rowDb['jkk_allowance'];
      $rowDb['jkm_allowance_ded'] = $rowDb['jkm_allowance'];
      $rowDb['pension_allowance_ded'] = $rowDb['pension_allowance'];
      $rowDb['alw_placement_allowance_ded'] = $rowDb['alw_placement_allowance'];
      $rowDb['alw_business_trip_ded'] = $rowDb['alw_business_trip'];
      $rowDb['alw_additional_allowance_benefit_ded'] = $rowDb['alw_additional_allowance_benefit'];
			$releaseNumber = isset($rowDb['release_number']) && !is_null($rowDb['release_number']) ? $rowDb['release_number'] : null;
			if (!empty($releaseNumber)){
				$rowDb['release_note'] = $rowDb['release_note'].', Release - '.$releaseNumber;
			}
      //----------------------

      $dataset[] = $rowDb;
    }


    $intTotalData = count($dataset);

    $myDataGrid->bind($dataset);

    return $myDataGrid->render();
  }
  // format tampilan gender
  function printGender($params)
  {
    extract($params);
    return ($value == 0) ? "F" : "M";
  }    // format tampilan gender
  function printCurrency($params)
  {
    global $ARRAY_CURRENCY;
    extract($params);
    return $ARRAY_CURRENCY[$value];
  }
  // format tampilan gender
  function printColumn($item, $key, &$myDataGrid)
  {
    if ($item['is_default'] == 't')
    {
        $myDataGrid->addColumn(new DataGrid_Column($item['name'], $item['allowance_code'], array("width" => 270),  array("nowrap" => "nowrap", "align" => "right"), false, true, "", "formatNumeric()", "numeric", true, 15, true, $item['allowance_code']));
    }
  }
  // format tampilan employee status
  function printStatus($params)
  {
    extract($params);
    global $ARRAY_EMPLOYEE_STATUS;
    return getWords($ARRAY_EMPLOYEE_STATUS[$value]);
  }
  // format tampilan staff/nonstaff
  function printStaff($params)
  {
    global $POSITION_STAFF;
    extract($params);
    $str = ($value == $POSITION_STAFF) ? getWords("staff") : getWords("non staff");
    return $str;
  }
  function dialogBoxReleased(){
  	$dialogBox = '<div id="basic-modal-content">';
  	$dialogBox .= '<table>';
  	$dialogBox .= '<tbody>';
  	$dialogBox .= '<tr>';
  	$dialogBox .= '<td width="120" valign="top">Released Note</td><td><textarea id="release_note" name="release_note" style="width: 245px; height: 60px;"></textarea></td>';
  	$dialogBox .= '</tr>';
  	$dialogBox .= '<tr>';
  	$dialogBox .= '<td width="120" valign="top">Released Number</td><td><input type="text" id="release_number" id="release_number" class="numeric" style="width: 70px;"></td>';
  	$dialogBox .= '</tr>';
  	$dialogBox .= '<tr>';
  	$dialogBox .= '<td colspan="2">&nbsp;</td>';
  	$dialogBox .= '</tr>';
  	$dialogBox .= '<tr>';
  	$dialogBox .= '<td colspan="2"><button id="release_ok">Released</button></td>';
  	$dialogBox .= '</tr>';
  	$dialogBox .= '</tbody>';
  	$dialogBox .= '</table>';
  	$dialogBox .= '</div>';
  	return $dialogBox;
  }
  function autoHoldBankAccount(){
  	global $db;
  	global $strDataID;
  	autoHoldSalaryAndOvertime($db, $strDataID);
  }
  function autoReleaseBankAccount(){
  	global $db;
  	global $strDataID;
  	autoReleaseOvertimeAndSalaryByBankAccount($db, $strDataID);
  }
  // format tampilan tanggal
  // format tampilan angka


  //----MAIN PROGRAM -----------------------------------------------------
  $strInfo = "";
  $db = new CdbClass;
  if ($db->connect()) {
    getUserEmployeeInfo($db);
    
    $bolIsEmployee = ($_SESSION['sessionUserRole'] != ROLE_ADMIN);

    (isset($_REQUEST['dataID'])) ? $strDataID = $_REQUEST['dataID'] : $strDataID = "";
    if ($strDataID == "") {
      header("location:salary_calculation_overtime.php");
      exit();
    }
		$objSalary = new clsSalaryCalculationOvertime($db, $strDataID); // cls_salary_calculation.php

    $arrSalaryTransferType = array();
    $arrSalaryCostCenter = array();
    $arrSalaryFilterValue = array(0=>"Positive Only", 1=>"Negative Only", 2=>"All Value");

    if ($objSalary->strDataID != "")
    {
      $strDateFrom      = $objSalary->arrData['date_from'];
      $strDateThru      = $objSalary->arrData['date_thru'];
      $strCompany       = $objSalary->arrData['id_company'];
      $strDataDateFrom  = pgDateFormat($objSalary->arrData['date_from'],"d M Y");
      $strDataDateThru  = pgDateFormat($objSalary->arrData['date_thru'],"d M Y");
      $strDataDateFromSalary  = pgDateFormat($objSalary->arrData['date_from_salary'],"d M Y");
      $strDataDateThruSalary  = pgDateFormat($objSalary->arrData['date_thru_salary'],"d M Y");
      $strDataDate      = pgDateFormat($objSalary->arrData['salary_date'],"d M Y");
      $intStatus        = $objSalary->arrData['status'];
      $bolIrregular     = ($objSalary->irregular == "t");
      $bolHideBlank     = ($objSalary->arrData['hide_blank'] == "t");
//print_r($objSalary->arrData);die();
    }
    else
    {
      // gak ada, keluar
      header("location:salary_calculation_overtime.php");
      exit();
    }

    // hitung ulang data jika ada perintah
    if (isset($_POST['btnFinish']))
    {

      $strSQL = "UPDATE hrd_salary_master SET status = " .SALARY_CALCULATION_FINISH." ";
      $strSQL .= "WHERE id = '$strDataID' ";
      $resExec = $db->execute($strSQL);
      writeLog(ACTIVITY_EDIT, MODULE_PAYROLL,"Finish : $strDataID",0);
    } else if (isset($_POST['btnApprove'])) {
      if ($_SESSION['sessionUserRole'] == ROLE_ADMIN) {
        approveData($db);
      }
    }

    $strCalculationMenu = "<b><a href='salary_calculation_overtime.php'>" .getWords("salary calculation overtime list")."</a></b>";//getCalculationMenu($strDataID, 5, $intStatus);

    // ------ AMBIL DATA KRITERIA -------------------------

    (isset($_POST['dataEmployee'])) ? $strDataEmployee = $_POST['dataEmployee'] : $strDataEmployee = "";
    (isset($_POST['dataSalaryCurrency'])) ? $strDataSalaryCurrency = $_POST['dataSalaryCurrency'] : $strDataSalaryCurrency = "";
    (isset($_POST['dataCompany'])) ? $strDataCompany = $_POST['dataCompany'] : $strDataCompany = "";
    (isset($_POST['dataBranch'])) ? $strDataBranch = $_POST['dataBranch'] : $strDataBranch = "";
    (isset($_POST['dataDivision'])) ? $strDataDivision = $_POST['dataDivision'] : $strDataDivision = "";
    (isset($_POST['dataDepartment'])) ? $strDataDepartment = $_POST['dataDepartment'] : $strDataDepartment = "";
    (isset($_POST['dataSection'])) ? $strDataSection = $_POST['dataSection'] : $strDataSection = "";
    (isset($_POST['dataSubSection'])) ? $strDataSubSection = $_POST['dataSubSection'] : $strDataSubSection = "";
    (isset($_POST['dataTransferStatus'])) ? $strDataTransferStatus = $_POST['dataTransferStatus'] : $strDataTransferStatus = "";
    (isset($_POST['dataCostCenter'])) ? $strDataCostCenter = $_POST['dataCostCenter'] : $strDataCostCenter = "";
    (isset($_POST['dataFilterValue'])) ? $strDataFilterValue = $_POST['dataFilterValue'] : $strDataFilterValue = 0;
    (isset($_POST['dataPage'])) ? $intCurrPage = $_POST['dataPage'] : $intCurrPage = 1;

    if (!is_numeric($intCurrPage)) $intCurrPage = 1;
    scopeCBDataEntry($strDataEmployee, $_SESSION['sessionUserRole'], $arrUserInfo);

    // ------------ GENERATE KRITERIA QUERY,JIKA ADA -------------
    //$strKriteria = "";
    $strKriteriaTransfer = "";
    if ($strDataEmployee != "") {
      $strKriteria .= "AND employee_id = '$strDataEmployee' ";
    }
    if ($strDataBranch != "") {
      $strKriteria .= "AND branch_code = '$strDataBranch' ";
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
    if ($strDataSalaryCurrency != "") {
      $strKriteria .= "AND salary_currency = '$strDataSalaryCurrency' ";
    }
    if ($strDataCostCenter != "") {
      $strKriteria .= "AND branch_cost_center_code = '$strDataCostCenter' ";
    }
    if ($strDataTransferStatus != "" && $strDataTransferStatus != "-1") {
      $strKriteriaTransfer .= "AND transfer_code = '$strDataTransferStatus' ";
    }
    if ($strDataTransferStatus == "-1"){
    	$strKriteriaTransfer .= "AND transfer_code != '0' ";
    }
    if ($strDataFilterValue != "") {
      if ($strDataFilterValue == 0)
        $strKriteriaTransfer .= "AND overtime_allowance >= 0 ";
      elseif ($strDataFilterValue == 1)
        $strKriteriaTransfer .= "AND overtime_allowance < 0 ";
      elseif ($strDataFilterValue == 2)
        $strKriteriaTransfer .= " ";
    }else{
    	$strKriteriaTransfer .= "AND overtime_allowance >= 0 ";
    }
    //$strKriteria .= $strKriteriaCompany;

    $strSQL  = "SELECT id, code, remark FROM hrd_salary_transfer_type ";
    $resDb = $db->execute($strSQL);
    while ($rowDb = $db->fetchrow($resDb)) {
        $strCode = $rowDb['code'];
		$arrSalaryTransferType[$strCode] = $rowDb['remark'];
	}

    $strSQL  = "SELECT code, name FROM hrd_cost_center ";
    $resDb = $db->execute($strSQL);
    while ($rowDb = $db->fetchrow($resDb)) {
        $strCode = $rowDb['code'];
    $arrCostCenter[$strCode] = $rowDb['name'];
  }


    if ($bolCanView) {
      //$strDataDetail = getData($db, $strDataID, $intTotalData, $strKriteria,$intCurrPage);
      $strDataDetail = getDataGrid($db, $strKriteria,$intCurrPage);
    } else {
      showError("view_denied");
      $strDataDetail = "";
    }
		$doRelease = null;
		if (isset($_POST['doRelease']) && !empty($_POST['doRelease'])){
			$doRelease = $_POST['doRelease'];
			setStatus();
		}	
    // generate data hidden input dan element form input
    $strInputEmployee = "<input type=text name=dataEmployee id=dataEmployee style=\"width:$strDefaultWidthPx\" maxlength=30 value=\"" .$strDataEmployee. "\" >";
    $strInputDataCurrency = getComboFromArray($ARRAY_CURRENCY, "dataSalaryCurrency", $strDataSalaryCurrency, $strEmptyOption, " style=\"width:$strDefaultWidthPx\"");
    $strInputBranch = getBranchList($db, "dataBranch", $strDataBranch, $strEmptyOption, "", "style=\"width:$strDefaultWidthPx\"");
    $strInputDivision = getDivisionList($db, "dataDivision", $strDataDivision, $strEmptyOption, "", "style=\"width:$strDefaultWidthPx\"");
    $strInputDepartment = getDepartmentList($db, "dataDepartment", $strDataDepartment, $strEmptyOption, "", "style=\"width:$strDefaultWidthPx\"");
    $strInputSection = getSectionList($db,"dataSection",$strDataSection, $strEmptyOption, "", "style=\"width:$strDefaultWidthPx\"");
    $strInputSubSection = getSubSectionList($db, "dataSubSection", $strDataSubSection, $strEmptyOption, "", "style=\"width:$strDefaultWidthPx\"");

    $strInputCostCenter = getComboFromArray($arrCostCenter, "dataCostCenter", $strDataCostCenter, $strEmptyOption, " style=\"width:$strDefaultWidthPx\"");
    if ($strDataTransferStatus == "-1"){
    	$strAllHoldOption = "<option value='-1' selected>All Hold</option>";
    }else{
    	$strAllHoldOption = "<option value='-1'>All Hold</option>";
    }
    $strInputTransferStatus = getComboFromArray($arrSalaryTransferType, "dataTransferStatus", $strDataTransferStatus, $strEmptyOption.$strAllHoldOption, " style=\"width:$strDefaultWidthPx\"");
    $strInputFilterValue = getComboFromArray($arrSalaryFilterValue, "dataFilterValue", $strDataFilterValue, false, " style=\"width:$strDefaultWidthPx\"");
		$strDialogBoxRelease = dialogBoxReleased();

	//untuk filter ketika meng-export dengan filter bank (by Farhan)
    //global $intDefaultWidthPx;

	$strSQL  = "SELECT bank_code FROM hrd_employee ";
    $strSQL .= "WHERE id = '$strDataID' ";
    $resDb = $db->execute($strSQL);
    if ($rowDb = $db->fetchrow($resDb)) {
		$bolNewData = false;
		$arrData['dataBankCode'] = "".$rowDb['bank_code'];
	}
	if ($bolNewData) {
		$arrData['dataBankCode'] = "";
	}


    $strReportType = getBankList($db,"dataBankCode",$arrData['dataBankCode'], $strEmptyOption,""," style=\"width:250\"");
	//---------------------------------------------------------------------------------------------------

    $strHidden .= "<input type=hidden name=dataCompany value=\"$strDataCompany\">";
    $strHidden .= "<input type=hidden name=dataBranch value=\"$strDataBranch\">";
    $strHidden .= "<input type=hidden name=dataSalaryCurrency value=\"$strDataSalaryCurrency\">";
    $strHidden .= "<input type=hidden name=dataDivision value=\"$strDataDivision\">";
    $strHidden .= "<input type=hidden name=dataDepartment value=\"$strDataDepartment\">";
    $strHidden .= "<input type=hidden name=dataSection value=\"$strDataSection\">";
    $strHidden .= "<input type=hidden name=dataSubSection value=\"$strDataSubSection\">";
    $strHidden .= "<input type=hidden name=dataTransferStatus value=\"$strDataTransferStatus\">";
    $strHidden .= "<input type=hidden name=dataCostCenter value=\"$strDataCostCenter\">";
    $strHidden .= "<input type=hidden name=dataFilterValue value=\"$strDataFilterValue\">";
    $strHidden .= "<input type=hidden name=dataEmployee value=\"$strDataEmployee\">";
    $strHidden .= "<input type=hidden name=dataPage value=\"$intCurrPage\">";
    $strHidden .= "<input type=hidden name=dataID value=\"$strDataID\">";
		$strHidden .= "<input type=hidden id=dataReleaseNote name=dataReleaseNote value=\"\">";
    $strHidden .= "<input type=hidden id=dataReleaseNumber name=dataReleaseNumber value=\"\">";
    $strHidden .= "<input type=hidden id=doRelease name=doRelease value=\"\">";
  }

  if ($bolPrint) {
    $strMainTemplate = getTemplate("salary_calculation_result_print.html");
  } else {
    $strTemplateFile = getTemplate("salary_calculation_result_overtime.html");
  }

  $tbsPage = new clsTinyButStrong ;

  //write this variable in every page
  $strPageTitle = $dataPrivilege['menu_name'];
  $pageIcon = "../images/icons/".$dataPrivilege['icon_file'];
  //------------------------------------------------
  //Load Master Template
  //$tbsPage->LoadTemplate($strMainTemplate) ;
  $tbsPage->LoadTemplate("../templates/master2.html");
  $tbsPage->Show() ;
?>
