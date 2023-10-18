<?php
  //if ( !session_id() ) session_start();
  ini_set("display_errors", 1);
  date_default_timezone_set('Asia/Jakarta');

   include_once('../global/session.php');
  include_once('global.php');
  include_once('../includes/model/model.php');
  include_once('../includes/datagrid2/datagrid.php');
  include_once('../includes/form2/form2.php');
  include_once('form_object.php');
  include_once('../includes/tbsclass/plugins/tbs_plugin_opentbs.php');
	include_once("cls_tax_calculation.php");

  $dataPrivilege = getDataPrivileges(basename($_SERVER['PHP_SELF']), $bolCanView, $bolCanEdit, $bolCanDelete, $bolCanApprove);
  $dataPrivilegeManagerial = getDataPrivileges("salary_calculation_managerial.php", $bolCanViewManagerial, $bolCanEditManagerial, $bolCanDeleteManagerial, $bolCanApproveManagerial);
	if (!$bolCanView) die(accessDenied($_SERVER['HTTP_REFERER']));


  $db = new CDbClass();
  $db->connect();
  
  $f = new clsForm("form1", /*2 column view*/1, "100%");
  $f->disableFormTag();
  $f->showCaption = false;
  $f->showMinimizeButton = false;
  $f->showCloseButton = false;
  
  $f->addHidden("isShow", 1);
  $f->addSelect(getWords("company"), "dataCompany", getDataListCompany($strDataCompany, false, $arrCompanyEmptyData, $strKriteria2), array("style" => "width:$strDefaultWidthPx"), "", true);

  $f->addSelect("Year", "dataYear", getDataYear(), array("style" => "width:$strDefaultWidthPx"), "", true);
  $f->addSelect("Month", "dataMonth", $ARRAY_MONTH, array("style" => "width:$strDefaultWidthPx"), "", true);
//  $f->addSelect("Employee Status","employeeStatus",$ARRAY_EMPLOYEE_STATUS,'','',false,true);
  $f->addSelect(getWords("employee status"), "employeeStatus", getDataListEmployeeStatus(getInitialValue("EmployeeStatus"), true, array("value" => "", "text" => "", "selected" => true)), array("style" => "width:$strDefaultWidthPx"), "", false);
  $f->addSelect(getWords("employee level"), "dataLevel", getDataLevel(), array("style" => "width:$strDefaultWidthPx"), "", false);
  $arrayAmountSelect = array(array('value' => '0', 'text' => 'Semua Nilai', 'selected' => true), array('value' => '1', 'text' => 'Besar dari 0'));
  $f->addSelect(getWords("tax amount"), "taxAmount", $arrayAmountSelect, array("style" => "width:$strDefaultWidthPx"), "", false);
  $f->addInputAutoComplete(getwords("n i k"), "employeeName", getDataEmployee($strDataEmployee), "style=width:$strDefaultWidthPx ".$strReadonly, "string", false);
  $f->addLabelAutoComplete("", "employeeName", "");
    
//  //this save button will hide after save <toggle>
  $f->addSubmit("btnShow", "Show Report", array(), true, true, "", "", "");
  $f->addSubmit("btnExportXLS", "Export Excel", array(), true, true, "", "", "");
  $f->addSubmit("btnExportCSV", "Export E-SPT", array(), true, true, "", "", "");

  
  $formInput  = $f->render();


  $showReport = (isset($_POST['btnShow']) || isset($_POST['btnExportXLS']) || isset($_POST['isShow']));
  
  $totalData = 0;
  $dataGrid = "";
  $strInitAction = "";

    $strStatus = $f->getValue('employeeStatus');
    $strName = $f->getValue('employeeName');
    $strLevel = $f->getValue('dataLevel');
    $strCompany = $f->getValue('dataCompany');
  	$taxAmount = $f->getValue('taxAmount');
  if ($showReport)
  {  
      
    $intYear  = intval($f->getValue('dataYear'));
    $intMonth  = intval($f->getValue('dataMonth'));
    $strKriteria = "";
    if($strStatus != "")
    {
        $strKriteria .= " AND t1.\"employee_status\" = $strStatus";
    }
    if($strName != "")
    {
        $strKriteria .= " AND t1.\"employee_id\" = '$strName' ";
    }    
    if($strCompany != "")
    {
        $strKriteria .= " AND t1.id_company = '$strCompany' ";
    }
    if ($taxAmount){
    		$strKriteria .= " AND (t0.tax > 0 OR t0.irregular_tax > 0) ";	
    }
    if($strLevel != "")
    {
      if($strLevel == 1)
        $strKriteria .= " AND t3.position_group::INTEGER >= 2 ";
      elseif ($strLevel == 2)
        $strKriteria .= " AND t3.position_group::INTEGER < 2 ";
      else $strKriteria .= " AND t3.position_group::INTEGER >= 0 ";
    }
      
    
    $dataMasterSalary  = getMasterSalaryByMonthAndYear($intMonth,$intYear);
    //echo $dataMasterSalary;
    
   
    if ($dataMasterSalary == 0)
    {
      $strErrorMessage = "Sorry, payroll calculation of ".$intYear." has not been done!";
      $strInitAction  .= "alert('".$strErrorMessage."');";
    }
    else
    {
        
      $strErrorMessage = "";
      $myDataGrid = new cDataGrid("form1","DataGrid1", "", "", true, false,false);
      $myDataGrid->disableFormTag();
      $intPageLimit =  $myDataGrid->getPageLimit();
      $intPageNumber =  $myDataGrid->getPageNumber();
      

      $arrAnnualTax = getMonthlyTax($db, $intMonth,$intYear,$strKriteria);   


      $myDataGrid->setCaption("Report Tax ".$arrMonth[$intMonth]." - $intYear");          
      $myDataGrid->pageSortBy = "h.\"employeeID\"";

      $myDataGrid->addColumnNumbering(new DataGrid_Column("No", "", array('width'=>'30'), array('nowrap'=>''), false, false, "","", "numeric", true, 4, true, "nomor"));

      $myDataGrid->addColumn(new DataGrid_Column("Name", "employeeName", "",array('nowrap' => ''), true, true, "", "", "string", true, 30));
      $myDataGrid->addColumn(new DataGrid_Column("NPWP", "npwp", array('width' => 120), array('align' => 'center'), false, false, "","", "string", true, 12));
      $myDataGrid->addColumn(new DataGrid_Column("Family Status for Pph21", "familyStatusCodePph21", array('width' => 120), array('align' => 'center'), false, false, "","", "string", true, 12));
      $myDataGrid->addColumn(new DataGrid_Column("Join Date", "joinDate", array('width' => 120), array('align' => 'center'), false, false, "","", "string", true, 12));
      $myDataGrid->addColumn(new DataGrid_Column("Resign Date", "resignDate", array('width' => 120), array('align' => 'center'), false, false, "","", "string", true, 12));
      $myDataGrid->addColumn(new DataGrid_Column("Base Tax", "baseTax", array('width' => 120), array('align' => 'right'), false, false, "","formatNumerica()", "numeric", true, 12));
      $myDataGrid->addColumn(new DataGrid_Column("Regular", "reg", array('width' => 120), array('align' => 'right'), false, false, "","formatNumerica()", "numeric", true, 12));
      $myDataGrid->addColumn(new DataGrid_Column("Irregular", "ireg", array('width' => 120), array('align' => 'right'), false, false, "","formatNumerica()", "numeric", true, 12));
     
    
      $myDataGrid->addColumn(new DataGrid_Column("Total", "totalMonthly", array('width' => 120), array('align' => 'right'), false, false, "","formatNumerica()", "numeric", true, 12));




      //if (!isset($_POST['btnExportXLS'])) 
      //  $myDataGrid->addColumn(new DataGrid_Column("", "get", array('width' => 80 , 'rowspan' => '2'), array('align' => 'right'), true, false, "","", "string", true, 15));
      if (isset($_POST['btnExportXLS']))
      {
          $myDataGrid->DATAGRID_RENDER_OUTPUT = DATAGRID_RENDER_EXCEL_BIFF;
          $myDataGrid->strFileNameXLS = "Tax Report.xls";
          $myDataGrid->strTitle1 = getWords("Report Tax - $intYear");
          $myDataGrid->hasGrandTotal = true;
      }
      // Report e-SPT
      if (isset($_POST['btnExportCSV'])){
        echo "export E-spt";
        //var_dump($arrAnnualTax);
        //exit();
        // output headers so that the file is downloaded rather than displayed
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=espt_masa_'.$intMonth.'_'.$intYear.'.csv');

        // create a file pointer connected to the output stream
        $output = fopen('php://output', 'w');

        // output the column headings
        fputcsv($output, array('Masa Pajak', 'Tahun Pajak', 'NPWP','Nama','Kode Pajak', 'Jumlah Bruto','Jumlah PPH','Kode Negara'));


        // loop over the rows, outputting them
        foreach ($arrAnnualTax as $arrItem){
          fputcsv($output, array($intMonth,$intYear,$arrItem["npwp"],$arrItem["employeeName"],"", $arrItem["baseTax"], $arrItem["reg"],""));
        }
        //while ($row = mysql_fetch_assoc($rows)) fputcsv($output, $row);
        exit();
      }
      //$myDataGrid->addButtonExportExcel(getWords("export excel"), "Tax Report.xls", getWords("Report Tax - $intYea"));

      //if you page can provide permission to view, edit, or delete, then you must set this to control datagrid permission
      //$myDataGrid->setPermission(/*view*/true, /*delete*/true, /*edit*/true);
      $myDataGrid->getRequest();

      $strCriteria = "";      
      $myDataGrid->totalData = $totalData;
      $myDataGrid->bind($arrAnnualTax);
      $dataGrid = $myDataGrid->render();
    }
  }

  $tbsPage = new clsTinyButStrong ;
  
  //write this variable in every page
  $strPageTitle = getWords($dataPrivilege['menu_name']);
  $pageIcon = "../images/icons/".$dataPrivilege['icon_file'];
  if ($bolPrint)
  {
     $strMainTemplate = getTemplate("employee_search_print.html");
  }
  else
  {
     $strTemplateFile = getTemplate(str_replace(".php", ".html", basename($_SERVER['PHP_SELF'])));  
  }
  //------------------------------------------------
  //Load Master Template
  $tbsPage->LoadTemplate($strMainTemplate) ;
  $tbsPage->Show() ;
  //end of main program
 
  // format numeric  
  function printNumeric($params)
  {
    extract($params);
    return number_format($value);
  }
 
  // fungsi untuk mengambil data total pajak tahunan karyawan, jika ada
  //  jika tidak ada, maka akan dilakukan perhitungan
  // output berupa array
  function getMonthlyTax($db,$intMonth, $intYear,$strKriteria = "")
  {      
    global $_POST;
    $arrResult = array();
    if ($intYear == "") return $arrResult;
    global $intStart;
    global $intPageLimit;
    global $intPageNumber;
    global $totalData;
    $intPage = $intPageNumber;
    $strSQL  = "SELECT id, EXTRACT(MONTH FROM \"salary_date\") as mon FROM \"hrd_salary_master\" WHERE EXTRACT(MONTH FROM \"salary_date\") = '$intMonth' and EXTRACT(YEAR FROM \"salary_date\") = '$intYear' AND status=".REQUEST_STATUS_APPROVED_2;
    $res = $db->execute($strSQL);
    $intStart = (($intPage -1) * $intPageLimit);
    
    while ($row = $db->fetchrow($res))
    {
        $salaryMasterID = $row['id'];
        $salaryMasterMonth = $row['mon'];
        $strSQL2 = "SELECT t0.\"id_employee\", t0.\"employee_id\", \"base_tax\", \"base_irregular_tax\", tax, \"irregular_tax\" , jkk_allowance, \"basic_salary\", t0.npwp, 
                        t1.\"family_status_code\", \"join_date\", \"resign_date\", t1.\"employee_name\", \"primary_address\",t1.\"primary_city\",t1.\"primary_zip\", t1.gender, t2.\"tax_reduction\", t3.\"position_name\", t2.children, t2.\"marital_status\"
                    FROM \"hrd_salary_detail\" AS t0 
                        LEFT JOIN \"hrd_employee\" AS t1 ON t0.\"id_employee\" = t1.id 
                        LEFT JOIN \"hrd_family_status\" AS t2 ON t2.family_status_code = t1.\"family_status_code\"
                        LEFT JOIN \"hrd_position\" AS t3 ON t3.\"position_code\" = t0.\"position_code\"
                    WHERE \"id_salary_master\" ='$salaryMasterID' $strKriteria ORDER BY t1.\"employee_name\"";
        $res3 = $db->execute($strSQL2);
        while ($row3 = $db->fetchrow($res3))
        {
           $totalData += 1;
        }
        if (!isset($_POST['btnExportXLS'])){
        $strSQL2 .= "LIMIT $intPageLimit OFFSET $intStart";
        }
        
        $res4 = $db->execute($strSQL2);
        if ($db->fetchrow($res4) < 1 ){
            $intPageNumber = 1;
            $intStart = 0;   
        }

        $res2 = $db->execute($strSQL2);
        while ($row2 = $db->fetchrow($res2))
        {
            $arrResult[$row2['id_employee']]['id'] = $row2['id_employee'];
            $arrResult[$row2['id_employee']]['nik'] = $row2['employee_id'];
            $arrResult[$row2['id_employee']]['position'] = $row2['position_name'];
            $arrResult[$row2['id_employee']]['marital'] = $row2['marital_status'];
            $arrResult[$row2['id_employee']]['children'] = $row2['children'];
            $arrResult[$row2['id_employee']]['baseTax'] += ($row2['base_tax'] > 0) ? $row2['base_tax'] : 0;
            $arrResult[$row2['id_employee']]['baseTaxIrregular'] += ($row2['base_irregular_tax'] > 0) ? $row2['base_irregular_tax'] : 0;
            $arrResult[$row2['id_employee']]['tax'] += ($row2['tax'] > 0) ? $row2['tax'] : 0;
            $arrResult[$row2['id_employee']]['taxIrregular'] += ($row2['irregular_tax'] > 0) ? $row2['irregular_tax'] : 0;
            $arrResult[$row2['id_employee']]['npwp'] = $row2['npwp'];
            $arrResult[$row2['id_employee']]['familyStatusCodePph21'] = $row2['family_status_code'];
            $arrResult[$row2['id_employee']]['joinDate'] = $row2['join_date'];
            $arrResult[$row2['id_employee']]['resignDate'] = $row2['resign_date'];
            $arrResult[$row2['id_employee']]['jkjkk'] += ($row2['jkk_allowance'] > 0) ? $row2['jkk_allowance'] : 0;
            $arrResult[$row2['id_employee']]['basicSalary'] += $row2['basic_salary'];
            $arrResult[$row2['id_employee']]['allowance'] += $row2['base_tax'] - $row2['basic_salary'] - $row2['jkk_allowance'] - $row2['tax'] - $row2['irregular_tax'];
            $arrResult[$row2['id_employee']]['employeeName'] = $row2['employee_name'];
            $arrResult[$row2['id_employee']]['primaryAddress'] = $row2['primary_address'];
            $arrResult[$row2['id_employee']]['primaryCity'] = $row2['primary_city'];
            $arrResult[$row2['id_employee']]['primaryZip'] = $row2['primary_zip'];
            $arrResult[$row2['id_employee']]['gender'] = $row2['gender'];
            $arrResult[$row2['id_employee']]['reg'] += $row2['tax'];
            $arrResult[$row2['id_employee']]['ireg'] += $row2['irregular_tax'];
            $arrResult[$row2['id_employee']]['taxAnnual'] = 0;
            $arrResult[$row2['id_employee']]['taxIrregularAnnual'] = 0;
            $arrResult[$row2['id_employee']]['jabatanAnnual'] = 0;
            $arrResult[$row2['id_employee']]['totalMonthly'] = 0;
            $arrResult[$row2['id_employee']]['totalAnnualTax'] = 0;
            $arrResult[$row2['id_employee']]['get'] = "";
            $arrResult[$row2['id_employee']]['year'] = $intYear;
            $arrResult[$row2['id_employee']]['ptkp'] = $row2['tax_reduction'];
            
       }
    }

            
    $i = 0;
    foreach ($arrResult as $arrEmployee)
    {
        $i++;
        $objTax                     = new clsTaxCalculation($db);
        $bolNPWP                    = (trim($arrEmployee['npwp']) != "");
        $strFamilyStatusPph21       = $arrEmployee['familyStatusCodePph21'];
        $fltBasic                   = $arrEmployee['baseTax'];
        $fltBasicIrregular          = $arrEmployee['baseTaxIrregular'];
        $taxMethod                  = 1;
        $strIDEmployee              = $arrEmployee['id'];
        $strJoinDate                = $arrEmployee['joinDate'];
        
        $objTax->setDataIncludeIrregular($fltBasic, $fltBasicIrregular, $strFamilyStatusPph21, $bolNPWP , 0, 0, 0, 0, $strIDEmployee, 1, "", 12, $intYear, $strJoinDate, "", false);
        $fltTax                     = $objTax->getTaxAnnual(true);    
        $fltIrregularTax            = $objTax->getTaxAnnual(false);

        $fltTax                     = ($fltTax < 0) ? 0 : $fltTax;
        $fltIrregularTax            = ($fltIrregularTax < 0) ? 0 : $fltIrregularTax;
        
        $arrResult[$strIDEmployee]['taxAnnual'] += $fltTax;
        $arrResult[$strIDEmployee]['taxIrregularAnnual'] += $fltIrregularTax;

        $intJabatan                 = 0.05 * ($fltBasic + $fltBasicIrregular);
        $intJabatan                 = ($intJabatan <= 6000000) ? $intJabatan : 6000000 ;

        $arrResult[$strIDEmployee]['jabatanAnnual'] += $intJabatan;

        unset($objTax);
        
        $arrResult[$strIDEmployee]['totalMonthly'] = $arrResult[$strIDEmployee]['tax'] + $arrResult[$strIDEmployee]['taxIrregular'];
        $arrResult[$strIDEmployee]['totalAnnualTax'] = $fltTax + $fltIrregularTax;
        $sResult = serialize($arrResult[$strIDEmployee]);
        $sResult = str_replace('"','$%^',$sResult);  
    }
    return $arrResult;
      
  }
 
  function getDataMonth()
  {
    global $arrMonth;
    $arrResult = array();
    foreach ($arrMonth as $key => $val)
      if ($key == intval(date("m")))
        $arrResult[] = array("value" => $key, "text" => $val, "selected" => true);
      else
        $arrResult[] = array("value" => $key, "text" => $val, "selected" => false);
    
    return $arrResult;
  }
  
  function getDataYear()
  {
    $currYear = intval(date("Y"));
    $arrResult = array();
    for($i = $currYear; $i > $currYear - 10; $i--)
      if ($i == $currYear)
        $arrResult[] = array("value" => $i, "text" => $i, "selected" => true);
      else
        $arrResult[] = array("value" => $i, "text" => $i, "selected" => false);
    
    return $arrResult;
  } 

  function getDataLevel()
  {
    global $bolCanViewManagerial;
    
    $arrResult = array();
    if (!$bolCanViewManagerial)
      $arrResult[] = array("value" => 1, "text" => "Staff Only", "selected" => true);
    else
    {
      $arrResult[] = array("value" => 0, "text" => "All Employee");
      $arrResult[] = array("value" => 1, "text" => "Staff Only");
      $arrResult[] = array("value" => 2, "text" => "Managerial Only");
    }
    return $arrResult;
  } 

function getMasterSalaryByMonthAndYear($intMonth, $intYear)
  {
      global $db;
      
      $bolExist = 0;
      
      $strSQL = "SELECT id FROM \"hrd_salary_master\" WHERE EXTRACT(MONTH FROM \"salary_date\") = $intMonth AND EXTRACT (YEAR FROM \"salary_date\") = $intYear";
      $res = $db->execute($strSQL);
      $rowDb = $db->fetchrow($res);
      if (pg_num_rows($res) > 0)
      {
          $bolExist = 1;
      }
      else
         $bolExist = 0;
      
      return $bolExist;
  }

function getMasterSalaryByYear($intYear)
  {
      global $db;
      
      $bolExist = 0;
      $strSQL = "SELECT id FROM \"hrd_salary_master\" WHERE EXTRACT (YEAR FROM \"salary_date\") = $intYear";
      $res = $db->execute($strSQL);
      if (pg_num_rows($res) > 1)
      {
          $bolExist = 1;
      }
      else{
         $bolExist = 0;
      }
      return $bolExist;
  }

function formatNumerica($params)
  {
    extract($params);
    return standardFormat($value);
  }

?>