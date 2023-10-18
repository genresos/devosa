<?php
  include_once('../global/session.php');
  include_once('global.php');
  include_once('../includes/model/model.php');
  include_once('../includes/datagrid2/datagrid.php');
  include_once('form_object.php');
	//include_once("../includes/krumo/class.krumo.php");
  $dataPrivilege = getDataPrivileges(basename($_SERVER['PHP_SELF']), $bolCanView, $bolCanEdit, $bolCanDelete, $bolCanApprove);
  $dataPrivilegeManagerial = getDataPrivileges("salary_calculation_managerial.php", $bolCanViewManagerial, $bolCanEditManagerial, $bolCanDeleteManagerial, $bolCanApproveManagerial);
  if (!$bolCanView) die(accessDenied($_SERVER['HTTP_REFERER']));



  $bolPrint = (isset($_REQUEST['btnPrint']) || isset($_REQUEST['btnPrintAll']));

  if ($bolPrint) {
    $strTemplateFile = getTemplate("salary_report_print.html");
  } else {
    //$strTemplateFile = getTemplate("salary_report.html");
    $strTemplateFile = getTemplate(str_replace(".php", ".html", basename($_SERVER['PHP_SELF'])));
  }

  //---- INISIALISASI ----------------------------------------------------
  $strDataDetail = "";
  $strPaging = "";
  $strHidden = "";
  $strBtnPrint = "";
  $intTotalData = 0;
  $bolLimit = true; // default, tampilan dibatasi (paging)
  $strYear = date("Y");
  $strStyle = "";
  //----------------------------------------------------------------------

  //--- DAFTAR FUNSI------------------------------------------------------
  // fungsi untuk menampilkan data
  // $db = kelas database, $intRows = jumlah baris (return)
  // $strDataDate adalah tanggal yang diinginkan
  // $strKriteria = query kriteria, $strOrder = query ORder by
  // $intStart = record mulai, $bolLimit = dibatasi sesuai limit global
  function getData($db, $intRows, $strKriteria = "", $intPage = 1, $bolLimit = true, $strOrder = "", $strKriteriaLevel = "") {
    global $words;
    global $bolPrint;
    global $strPaging;
    global $intTotalData;
    global $intRowsLimit;
    global $strYear;

    //$intRowsLimit = getSetting($db,"rows_per_page");
    //if (!is_numeric($intRowsLimit)) $intRowsLimit = 50;
    $intRows = 0;
    $arrResult = array();

    $bolLimit = false;

    // cari data gaji, kumpulkan di array dulu
    $arrGaji = array();
    $strSQL  = "SELECT SUM(\"total_net\") AS gaji, EXTRACT(MONTH FROM \"salary_date\") AS bulan, \"id_employee\" ";
    $strSQL .= "FROM \"hrd_salary_master\" AS t1, \"hrd_salary_detail\" AS t2 ";
    $strSQL .= "WHERE t1.id = t2.\"id_salary_master\" AND t1.status >= ".REQUEST_STATUS_APPROVED_2." ";
    $strSQL .= "AND t1.is_overtime_only IS FALSE ";
    $strSQL .= "AND EXTRACT(YEAR FROM t1.\"salary_date\") = '$strYear' $strKriteriaLevel";
    $strSQL .= "GROUP BY \"salary_date\", \"id_employee\" ";
    $resDb = $db->execute($strSQL);
    while ($rowDb = $db->fetchrow($resDb)) {
      if (isset($arrGaji[$rowDb['id_employee']]['bulan'])) {
        $arrGaji[$rowDb['id_employee']][$rowDb['bulan']] += $rowDb['gaji'];
      } else {
        $arrGaji[$rowDb['id_employee']][$rowDb['bulan']] = $rowDb['gaji'];
      }
    }

    // cari total data karyawan
    $intTotal = 0;
    $strSQL  = "SELECT count(id) AS total FROM \"hrd_employee\" ";
    $strSQL .= "WHERE \"join_date\" is not null AND active = 1 $strKriteria "; // hanya ambil yang statusnya permanent
    $resDb = $db->execute($strSQL);
    if ($rowDb = $db->fetchrow($resDb)) {
      if (is_numeric($rowDb['total'])) {
        $intTotal = $rowDb['total'];
      }
    }

    if ($bolLimit)
    {
      $strPaging = getPaging($intPage,$intTotal,"javascript:goPage('[PAGE]')");
      if ($strPaging == "") {
        $strPaging = "1&nbsp;";
      }
    }
    $intStart = (($intPage -1) * $intRowsLimit);

    $strSQL  = "SELECT id, \"employee_id\", \"employee_name\", gender, \"department_code\", \"position_code\", \"section_code\" ";
    $strSQL .= "FROM \"hrd_employee\" WHERE 1 = 1 ";
    $strSQL .= "AND (active = 1 OR EXTRACT(YEAR FROM \"join_date\") = '$strYear' ";
    $strSQL .= "OR EXTRACT(YEAR FROM \"resign_date\") = '$strYear') ";
    $strSQL .= " $strKriteria ORDER BY $strOrder \"employee_id\" ";
    if ($bolLimit) {
      $strSQL .= "LIMIT $intRowsLimit OFFSET $intStart ";
    }
    //echo ">>".$strSQl;
    $resDb = $db->execute($strSQL);
    while ($rowDb = $db->fetchrow($resDb)) {
      $intRows++;
      $strGender = ($rowDb['gender'] == 0) ? "F" : "M";
      $fltTotal = 0;

      $arrResult[$intRows]['id'] = $rowDb['id'];
      $arrResult[$intRows]['employeeID'] = $rowDb['employee_id'];
      $arrResult[$intRows]['employeeName'] = $rowDb['employee_name'];
      $arrResult[$intRows]['departmentCode'] = $rowDb['department_code'];
      $arrResult[$intRows]['sectionCode'] = $rowDb['section_code'];
      $arrResult[$intRows]['gender'] = $strGender;

      $strHiddenID = "";//"<input type=hidden name='chkID$intRows' value=\"" .$rowDb['id']."\">";
      /*
      $strResult .= "<tr valign=top>\n";
      $strResult .= "  <td nowrap>" .$rowDb['employeeID']. "&nbsp;</td>";
      $strResult .= "  <td nowrap>" .$rowDb['employeeName']. "&nbsp;</td>";
      $strResult .= "  <td align=center>$strGender&nbsp;</td>";
      $strResult .= "  <td>" .$rowDb['departmentCode']. "&nbsp;</td>";
      $strResult .= "  <td>" .$rowDb['positionCode']. "&nbsp;</td>";
      */
      for ($i=1; $i<=12;$i++) {
        $fltGaji = (isset($arrGaji[$rowDb['id']][$i])) ? $arrGaji[$rowDb['id']][$i] : 0;
        $fltTotal += $fltGaji;
        //$strGaji = ($fltGaji == 0) ? "" : standardFormat($fltGaji);
        //$strResult .= "  <td align=right>" .$strGaji. "&nbsp;</td>";

        $arrResult[$intRows]['salary_'.$i] = $fltGaji;
      }

      //$strTotal = ($fltTotal == 0) ? "" : standardFormat($fltTotal);
      //$strResult .= "  <td align=right>" .$strTotal. "&nbsp;</td>";
      //$strResult .= "</tr>\n";

      $arrResult[$intRows]['total'] = $fltTotal;


    }
    $intTotalData = $intRows;
    if ($intRows > 0) {
      writeLog(ACTIVITY_VIEW, MODULE_PAYROLL,"",0);
    }
    //var_dump($arrResult);
    return $arrResult;
  } // showData

  // fungsi buat nampilin data per baris doank
  function showRows($strNo, $rowData, $strClass = "") {
//var_dump($rowData);
    $strResult = "";

    $strResult .= "<tr valign=top class=\"$strClass\">\n";
    $strResult .= "  <td nowrap>" .$rowData['employeeID']. "&nbsp;</td>";
    $strResult .= "  <td nowrap>" .$rowData['employeeName']. "&nbsp;</td>";
    $strResult .= "  <td align=center>" .$rowData['gender']. "&nbsp;</td>";
    for ($i=1; $i<=12;$i++) {
      $fltGaji = $rowData['salary_'.$i];
      $strGaji = ($fltGaji == 0) ? "" : standardFormat($fltGaji);
      $strResult .= "  <td align=right>" .$strGaji. "&nbsp;</td>";

    }

    $strTotal = ($rowData['total'] == 0) ? "" : standardFormat($rowData['total']);
    $strResult .= "  <td align=right>" .$strTotal. "&nbsp;</td>";
    $strResult .= "</tr>\n";

    return $strResult;
  } //showRows

  // fungsi untuk nampilin data per employee
  // input: dbclass, nomor urut, data
  function showData($arrData) {
    global $words;

    $intRows = 0;
    $strResult = "";

//     $strResult .= "<table cellspacing=0 cellpadding=1 border=0 class=gridTable>\n";

    // bikin header table
    $strDefaultWidth = "width=40";
//     $strResult .= showHeader();

    foreach ($arrData AS $x => $rowDb) {
      $intRows++;

      $strResult .= showRows($intRows, $rowDb);

    }

//     $strResult .= "</table>\n";
    return $strResult;
  } // showData

  // menampilkan data, digroup berdasar departemen
  function showDataDepartment($db, $arrData) {
    global $words;
    global $_SESSION;
    global $strFilterDepartment;
    global $strFilterSection;
    global $strFilterEmployeeID;
    global $bolDetailChecked;
    global $intFilterCostCenter;

    $intRows = 0;
    $strResult = "";

    $strKriteriaDept = "";
    $strKriteriaSect = "";
    $bolShowTotal = $bolShowTotalDept = $bolShowTotalSect = true;

    // cek jika cuma 1 employee yg dicari
    if ($strFilterEmployeeID != "" && isset($arrData[1]))
    {
      $strKriteriaDept .= "AND hd.\"department_code\" = '" .$arrData[1]['departmentCode']."' ";
      $strKriteriaSect .= "AND \"department_code\" = '" .$arrData[1]['departmentCode']."' ";
      $strKriteriaSect .= "AND \"section_code\" = '" .$arrData[1]['sectionCode']."' ";
      $bolShowTotal = $bolShowTotalDept = $bolShowTotalSect = false;
    }

    // cek filter cost center
    /*if ($intFilterCostCenter!=""){
      //echo "masuk".$intFilterCostCenter;
      $strSQl = "SELECT * FROM hrd_cost_center_member WHERE id_cost_center=".$intFilterCostCenter;
      $resDb = $db->execute($strSQl);
      $arrFilterCs=array();
      //echo "----";
      while ($rowDb = $db->fetchrow($resDb)) {
        //echo "<br/>type:".$rowDb['attribute_type']."";
        $arrFilterCs = unserialize($rowDb['attribute_value']);
        //var_dump($arrFilterCs);
        if ($rowDb['attribute_type']=="1"){ // MANAGEMENT
           // $strKriteriaDept .= "AND hd.\"department_code\" = '" .$arrData[1]['departmentCode']."' ";
        }elseif ($rowDb['attribute_type']=="2") { // DIVISION
           // $strKriteriaDept .= "AND hd.\"department_code\" = '" .$arrData[1]['departmentCode']."' ";
        }elseif ($rowDb['attribute_type']=="3") { // DEPARTMENT
            $strKriteriaDept .= "AND ( ";
            foreach ($arrFilterCs AS $itemCs) {
                $strKriteriaDept .= "hd.\"department_code\" = '" .$itemCs."' OR ";
            }
                $strKriteriaDept .= "hd.\"department_code\" = '" .$arrFilterCs[0]."') ";
        }elseif ($rowDb['attribute_type']=="4") { // SECTION
            $strKriteriaSect .= "AND ( ";
            foreach ($arrFilterCs AS $itemCs) {
                $strKriteriaSect .= "\"section_code\" = '" .$itemCs."' OR ";
            }
                $strKriteriaSect .= "\"section_code\" = '" .$arrFilterCs[0]."') ";
        }elseif ($rowDb['attribute_type']=="5") { // SUBSECTION
           // $strKriteriaDept .= "AND hd.\"department_code\" = '" .$arrData[1]['departmentCode']."' ";
        }elseif ($rowDb['attribute_type']=="6") { // BRANCH
           // $strKriteriaDept .= "AND hd.\"department_code\" = '" .$arrData[1]['departmentCode']."' ";
        }
      }
    }*/
    // cari data section
    $arrSect = array();
    if ($strFilterSection != "") {
      $strKriteriaSect .= "AND \"section_code\" =  '$strFilterSection' ";
      $bolShowTotal = $bolShowTotalDept = false;
    }
    $strSQL  = "SELECT * FROM \"hrd_section\" WHERE 1=1 $strKriteriaSect ";
    $strSQL .= "ORDER BY \"section_code\" ";
    $resDb = $db->execute($strSQL);
    while ($rowDb = $db->fetchrow($resDb)) {
      $arrSect[$rowDb['department_code']][$rowDb['section_code']] = $rowDb['section_name'];
      if ($strFilterSection != "")
        $strKriteriaDept = "AND \"department_code\" = '" .$rowDb['department_code']."' ";
    }
    // cari data Department
    if ($strFilterDepartment != "") {
      $strKriteriaDept .= " AND hd.\"department_code\" = '$strFilterDepartment' ";
      $bolShowTotal = false;
    }
    $arrDept = array();
    $strSQL  = "SELECT hd.*, hs.* FROM \"hrd_department\" AS hd ";
    $strSQL .= "LEFT JOIN \"hrd_section\" AS hs ON hd.\"department_code\" = hs.\"department_code\" WHERE 1=1 $strKriteriaDept ";
    $strSQL .= "ORDER BY hd.\"department_code\" ";
    $resDb = $db->execute($strSQL);
    while ($rowDb = $db->fetchrow($resDb)) {
      $arrDept[$rowDb['department_code']] = $rowDb['department_name'];
    }


    // tentukan keanggotaan department/section
    $arrDeptEmployee = array(); // daftar anggota sebuah departement, tapi gak punya section
    $arrSectEmployee = array(); // daftar anggota sebuah section
//var_dump($arrData);
    foreach ($arrData AS $x => $rowDb) {
      //echo $rowDb['departmentCode'].",".$rowDb['sectionCode']."<br/>";
      if ($rowDb['departmentCode'] != "" && $rowDb['sectionCode'] != "")
      {
        // masuk ke dalam section
        if (isset($arrSectEmployee[$rowDb['sectionCode']])){
          $arrSectEmployee[$rowDb['sectionCode']][] = $x;
        }else{
          $arrSectEmployee[$rowDb['sectionCode']][0] = $x;
        }
      } else if ($rowDb['departmentCode'] != "") { // cuma ada departemen aja
        // masukkan ke dalam department, tapi gak di section tertentu
        if (isset($arrDeptEmployee[$rowDb['departmentCode']]))
          $arrDeptEmployee[$rowDb['departmentCode']][] = $x;
        else
          $arrDeptEmployee[$rowDb['departmentCode']][0] = $x;
      }
    }
    //var_dump($arrSectEmployee);
    // array temporer untuk reset data
    $arrEmptyData = array(
      "id" => "", "total" => 0, "gender" => "",
      "employee_id" => "", "employee_name" => "",
    );
    for ($i = 1; $i < 13;$i++) {
      $arrEmptyData['salary_'.$i] = 0;
    }

    $arrTotal = $arrEmptyData;
    $arrTotal['employeeName'] = "<strong>".strtoupper(getWords("grand total")). "</strong>";

    // bikin header table
    $strDefaultWidth = "width=40";

    $intColspan = 4 + 12;
    foreach ($arrDept AS $strDeptCode => $strDeptName) {
      if ($bolShowTotalDept) {
        $strResult .= " <tr valign=top>\n";
        $strResult .= "  <td nowrap colspan=$intColspan>&nbsp;<strong>[$strDeptCode] $strDeptName</strong></td>\n";
        $strResult .= " </tr>\n";
      }

      $arrTotalDept = $arrEmptyData;
      $arrTotalDept['employeeName'] = "<strong>".strtoupper(getWords("total"). " ".$strDeptCode). "</strong>";

      // tampilkan data karyawan anggota departemen
      $arrTmp = (isset($arrDeptEmployee[$strDeptCode])) ? $arrDeptEmployee[$strDeptCode] : array();
      foreach ($arrTmp AS $x => $strX) {
        $rowDb = $arrData[$strX];

        $arrTotal['total'] += $rowDb['total'];
        $arrTotalDept['total'] += $rowDb['total'];

        for ($i = 1; $i < 13;$i++) {

          $arrTotal['salary_'.$i] += $rowDb['salary_'.$i];
          $arrTotalDept['salary_'.$i] += $rowDb['salary_'.$i];
        }
        // munculkan detail jika show data di centang
        if ($bolDetailChecked=="")
          $strResult .= showRows("",$rowDb);
      }

      $arrTmp = (isset($arrSect[$strDeptCode])) ? $arrSect[$strDeptCode] : array();
      foreach ($arrTmp AS $strSectCode => $strSectName) {
        if ($bolShowTotalSect) {
          $strResult .= " <tr valign=top>\n";
          $strResult .= "  <td nowrap colspan=$intColspan>&nbsp;&nbsp;!-- <strong>[$strSectCode] $strSectName</strong></td>\n";
          $strResult .= " </tr>\n";
        }

        $arrTotalSect = $arrEmptyData;
        $arrTotalSect['employeeName'] = "<strong>".strtoupper(getWords("total"). " ".$strSectCode)."</strong>";

        // cari karyawan dalam section ini
        //var_dump($arrSectEmployee);
        $arrTmp1 = (isset($arrSectEmployee[$strSectCode])) ? $arrSectEmployee[$strSectCode] : array();
        //var_dump($arrTmp1);
        foreach ($arrTmp1 AS $x => $strX) {
          $rowDb = $arrData[$strX];

          // hitung total dulu
          $arrTotal['total'] += $rowDb['total'];
          $arrTotalDept['total'] += $rowDb['total'];
          $arrTotalSect['total'] += $rowDb['total'];

          for ($i = 1; $i < 13;$i++) {
            $arrTotal['salary_'.$i] += $rowDb['salary_'.$i];
            $arrTotalDept['salary_'.$i] += $rowDb['salary_'.$i];
            $arrTotalSect['salary_'.$i] += $rowDb['salary_'.$i];
          }

          // munculkan detail jika show data di centang
          if ($bolDetailChecked=="")
            $strResult .= showRows("",$rowDb);
        }

        // tampilkan total per section
        if ($bolShowTotalSect)
          $strResult .= showRows("",$arrTotalSect, "bgNewRevised");
      }
      if ($bolShowTotalDept)
        $strResult .= showRows("",$arrTotalDept, "bgNewRevised");
    }
    if ($bolShowTotal)
      $strResult .= showRows("",$arrTotal, "tableHeader");

//     $strResult .= "</table>\n";
    return $strResult;
  } // showDataDepartment

  function getDataLevel()
  {
    global $bolCanViewManagerial;

    $arrResult = array();
    if (!$bolCanViewManagerial)
      $arrResult = array("Staff Only");
    else
    {
      $arrResult = array("Staff Only", "Managerial Only", "All Employee");
    }
    return $arrResult;
  }
  //----------------------------------------------------------------------

  //----MAIN PROGRAM -----------------------------------------------------
  $db = new CdbClass;
  if ($db->connect()) {

    // ------ AMBIL DATA KRITERIA -------------------------

    $strFilterYear = (isset($_REQUEST['filterYear'])) ? trim($_REQUEST['filterYear']) :  $strYear;
    if (!is_numeric($strFilterYear)) $strFilterYear = $strYear;

    $strFilterEmployeeID = (isset($_REQUEST['filterEmployeeID'])) ? trim($_REQUEST['filterEmployeeID']) :  "";
    $strFilterDepartment = (isset($_REQUEST['filterDepartment'])) ? $_REQUEST['filterDepartment'] : "";
    $strFilterSection = (isset($_REQUEST['filterSection'])) ? $_REQUEST['filterSection'] : "";
    $strFilterType = (isset($_REQUEST['filterType'])) ? $_REQUEST['filterType'] : "";
    $strFilterLevel = (isset($_REQUEST['filterLevel'])) ? $_REQUEST['filterLevel'] : "";
    $intCurrPage = (isset($_REQUEST['dataPage'])) ? $_REQUEST['dataPage'] : 1;
    // $intType = (isset($_REQUEST['filterType'])) ? $_REQUEST['filterType'] : 0;
    $bolDetailChecked =(isset($_REQUEST['filterDetailEmp'])) ? "checked": "";
    $intFilterCostCenter = (isset($_REQUEST['filterCostCenter'])) ? $_REQUEST['filterCostCenter'] : "";

    if (!is_numeric($intCurrPage)) $intCurrPage = 1;

    $strBtnPrint = "<input type=button name='btnPrint' value=\"" .$words['print']. "\" onClick=\"printData($intCurrPage);\">";

    // ------------ GENERATE KRITERIA QUERY,JIKA ADA -------------
    $strKriteria = "";
    $strKriteriaLevel = "";
    $bolShow = false;
    if (isset($_REQUEST['btnShowAll']) || isset($_REQUEST['btnPrintAll'])) {
      $strKriteria = "";
      $bolLimit = false;
      $bolShow = true;
    } else if (isset($_REQUEST['btnShow']) || isset($_REQUEST['btnPrint'])) {

      $strInfoKriteria = "";
      if ($strFilterEmployeeID != "") {
        $strKriteria .= "AND upper(\"employee_id\") like '%" .strtoupper($strFilterEmployeeID). "%' ";
      }
      if ($strFilterDepartment != "") {
        $strKriteria .= "AND \"department_code\" = '$strFilterDepartment' ";
      }
      if ($strFilterSection != "") {
        $strKriteria .= "AND \"section_code\" = '$strFilterSection' ";
      }
      if ($strFilterType != "") {
        $strKriteria .= "AND \"employee_status\" = '$strFilterType' ";
      }
      if ($strFilterLevel != 2){
        $strKriteriaLevel .= "AND \"is_managerial\" = (1 = $strFilterLevel) ";
      }
      if ($intFilterCostCenter != ""){
          $strKriteria .= "AND \"branch_cost_center_code\" = '$intFilterCostCenter' ";
      }
      // if ($intType == 1) $strKriteria .= "AND \"employee_status\" <> '" .STATUS_OUTSOURCE."' ";
      // else if ($intType == 2) $strKriteria .= "AND \"employee_status\" = '" .STATUS_OUTSOURCE."' ";
      $bolShow = true;
    } else { // jngan tampilkan data
      $strKriteria .= "AND 1=2 ";
      $strBtnPrint = ""; // tidak perlu tampil
    }

    if ($bolCanView) {
      $strYear = $strFilterYear;
      $arrDataDetail = getData($db,$intTotalData, $strKriteria, $intCurrPage, $bolLimit, "",$strKriteriaLevel);

      //$strDataDetail = showData($arrDataDetail);
      if ($bolShow)
        $strDataDetail = showDataDepartment($db, $arrDataDetail);

    } else {
      showError("view_denied");
      $strDataDetail = "";
    }
    // generate data hidden input dan element form input
    $intDefaultWidth = 30;
    $intDefaultWidthPx = 200;
    $intDefaultHeight = 3;

    //$strInputFilterYear = "<input type=input name=filterYear size=4 maxlength=4 value=\"$strFilterYear\">";
    $strInputFilterYear       = getYearList("filterYear", $strFilterYear, false," style=\"width:$intDefaultWidthPx\"");
    $strInputFilterEmployeeID = "<input type=input name=filterEmployeeID id=filterEmployeeID size=$intDefaultWidth value=\"$strFilterEmployeeID\" style=\"width:$intDefaultWidthPx\"width:$intDefaultWidthPx\">";
    $strInputFilterDepartment = getDepartmentList($db, "filterDepartment", $strFilterDepartment, $strEmptyOption,""," style=\"width:$intDefaultWidthPx\"");
    $strInputFilterSection    = getSectionList($db, "filterSection", $strFilterSection, $strEmptyOption,""," style=\"width:$intDefaultWidthPx\"");
    $strInputFilterType       = getEmployeeStatusList("filterType", $strFilterType, $strEmptyOption," style=\"width:$intDefaultWidthPx\"");
    $strInputFilterLevel      = getComboFromArray(getDataLevel(),"filterLevel", $strFilterLevel, false," style=\"width:$intDefaultWidthPx\"");
    $strInputViewDetail       = "<input type=\"checkbox\" name=\"filterDetailEmp\" value=\"1\" $bolDetailChecked > Yes";
    $strInputFilterCostCenter = getDataCostCenterByCode($db, "filterCostCenter", $intFilterCostCenter , $strEmptyOption,""," style=\"width:$intDefaultWidthPx\"");;
//getComboFromArray($array, $varname, $default = "", $extra = "", $action = "", $indexed = true)
    $strHidden .= "<input type=hidden name=filterEmployeeID value=\"$strFilterEmployeeID\">";
    $strHidden .= "<input type=hidden name=filterDepartment value=\"$strFilterDepartment\">";
    $strHidden .= "<input type=hidden name=filterSection value=\"$strFilterSection\">";
    $strHidden .= "<input type=hidden name=filterYear value=\"$strFilterYear\">";
    $strHidden .= "<input type=hidden name=filterType value=\"$intType\">";
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

?>
