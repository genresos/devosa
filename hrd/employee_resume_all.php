<?php
  include_once('../global/session.php');
  include_once('global.php');
  include_once('form_object.php');
include_once("../includes/krumo/class.krumo.php");
  $dataPrivilege = getDataPrivileges("employee_search.php", $bolCanView, $bolCanEdit, $bolCanDelete, $bolCanApprove, $bolCanCheck, $bolCanAcknowledge, $bolCanApprove2);
  if (!$bolCanView) die(accessDenied($_SERVER['HTTP_REFERER']));





  $bolPrint = (isset($_REQUEST['btnPrint']) || isset($_REQUEST['btnPrintAll']) || isset($_REQUEST['btnExcel']));
  $bolFull = (isset($_REQUEST['filterFull']));
/*
  session_start();

  include_once('global.php');
  include_once('form_object.php');
  //include_once(getTemplate("words.inc"));

  $dataPrivilege = getDataPrivileges(basename("employee_search.php"), $bolCanView, $bolCanEdit, $bolCanDelete, $bolCanApprove, $bolCanCheck, $bolCanAcknowledge, $bolCanApprove2);
  if (!$bolCanView) die(accessDenied($_SERVER['HTTP_REFERER']));




  $bolPrint = (isset($_REQUEST['btnPrint']) || isset($_REQUEST['btnPrintAll']) || isset($_REQUEST['btnExcel']));
  $bolFull = (isset($_REQUEST['filterFull']));

  if ($bolPrint) {
    $strMainTemplate = getTemplate("employee_resume_all_print.html");
  } else {
    $strTemplateFile = getTemplate("employee_resume_all.html");
  }
*/
  //---- INISIALISASI ----------------------------------------------------
  $strDataDetail = "";
  $strPaging = "";
  $strHidden = "";
  $strBtnPrint = "";
  $intTotalData = 0;
  $bolLimit = false; // default, tampilan dibatasi (paging)
  $strButtons = "";
  $strStyle = "";

  $strWordsFunctional = getWords("position");
  $strWordsCompany = getWords("company");
  $strWordsSubSection = getWords("subsect.");
  $strWordsBranch = getWords("branch office");
  $strWordsSection = getWords("section");
  $strWordsDepartment = getWords("department");
  $strWordsDivision = getWords("division");
  $strWordsLevel = getWords("employee category");
  $strWordsEmployeeStatus = getWords("employee status");
  $strWordsEmployeeID = getwords("n i k");
  $strWordsEmployeeId2 = getwords("n i k corporate");
  $strWordsSearchEmployee = getWords("search employee");
  $strWordsSimpleResume = getWords("simple resume");
  $strWordsReport = getWords("report");
  $strWordsShow = getWords("show");
  $strWordsExcel = getWords("excel");
  $strWordsListOfEmployee = getWords("list of employee");
  $strWordsEmployeeData = getWords("employee data");
  $strWordsTrainingData = getWords("training data");
  $strWordsEducationData = getWords("education data");
  $strWordsWorkExperience = getWords("work experience");
  $strWordsNo = getWords("no");
  $strWordsEmployeeId = getwords("n i k");
  // $strWordsIDEmployee = getWords("ID");
  $strWordsEmployeeName = getWords("empl.name");
  $strWordsDept = getWords("dept.");
  $strWordsSect = getWords("sect.");
  $strWordsLevel = getWords("employee category");
  $strWordsFunctional = getWords("position");
  $strWordsSubject = getWords("subject");
  $strWordsInstitution = getWords("institution");
  $strWordsLocation = getWords("location");
  $strWordsTrainer = getWords("trainer");
  $strWordsPeriode = getWords("periode");
  $strWordsCost = getWords("cost");
  $strWordsFaculty = getWords("faculty");
  $strWordsPosition = getWords("position");
  $strWordsDinas = getWords("masa ikatan dinas");
  $strWordsNote = getWords('Note');
  $strWordsCertificate = getWords('Certificate');
  // $strWordsFunctional = getWords("functional");
  // $strWordsFunctional = getWords("functional");

  //----------------------------------------------------------------------


  //--- DAFTAR FUNSI------------------------------------------------------

  // fungsi untuk menampilkan data
  // $db = kelas database, $intRows = jumlah baris (return)
  // $strDataDate adalah tanggal yang diinginkan
  // $strKriteria = query kriteria, $strOrder = query ORder by
  // $intStart = record mulai, $bolLimit = dibatasi sesuai limit global
  function getData($db, &$intRows, $strKriteria = "", $intPage = 1, $bolLimit = true, $strOrder = "") {
    //global $words;
    //global $bolPrint;
    //global $ARRAY_EMPLOYEE_STATUS;
    global $strPaging;
    global $intTotalData;
    global $intRowsLimit;
    global $bolIsEmployee;

    $intRowsLimit = getSetting("rows_per_page");
    if (!is_numeric($intRowsLimit)) $intRowsLimit = 50;

    $intRows = 0;
    $strResult = "";
    $strKriteria .= "AND active = 1 "; // hanya yang aktif

    // cari total data
    $intTotal = 0;
    $strSQL  = "SELECT count(id) AS total FROM hrd_employee ";
    $strSQL .= "WHERE 1=1 $strKriteria ";
    $resDb = $db->execute($strSQL);
    if ($rowDb = $db->fetchrow($resDb)) {
      if (is_numeric($rowDb['total'])) {
        $intTotal = $rowDb['total'];
      }
    }
    $strPaging = getPaging($intPage,$intTotal,"javascript:goPage('[PAGE]')");
    if ($strPaging == "") {
      $strPaging = "1&nbsp;";
    }
    $intStart = (($intPage -1) * $intRowsLimit);

    //-----------------

    $strSQL  = "SELECT * FROM hrd_employee ";
    $strSQL .= "WHERE 1=1 $strKriteria ";
    $strSQL .= "ORDER BY $strOrder employee_name ";
    if ($bolLimit) {
      $strSQL .= "LIMIT $intRowsLimit OFFSET $intStart ";
    }
    $resDb = $db->execute($strSQL);
    while ($rowDb = $db->fetchrow($resDb)) {
      $intRows++;
      $strEmployeeInfo = $rowDb['employee_id'] ." - ".$rowDb['employee_name'];

      $intMax = 0;
      // cari data training
      $arrTraining = array();
      $strSQL  = "SELECT * FROM hrd_employee_training ";
      $strSQL .= "WHERE id_employee = '" .$rowDb['id']."' ORDER BY year_from, month_from, day_from ";
      $resTmp = $db->execute($strSQL);
      while ($rowTmp = $db->fetchrow($resTmp)) {
        $arrTraining[] = $rowTmp;
      }
      $intTraining = count($arrTraining);
      $intMax = $intTraining;

      // cari data education
      $arrEducation = array();
      $strSQL  = "SELECT * FROM hrd_employee_education ";
      $strSQL .= "WHERE id_employee = '" .$rowDb['id']."' ORDER BY year_from, month_from, day_from ";
      $resTmp = $db->execute($strSQL);
      while ($rowTmp = $db->fetchrow($resTmp)) {
        $arrEducation[] = $rowTmp;
      }
      $intEducation = count($arrEducation);
      if ($intMax < $intEducation) $intMax = $intEducation;

      // cari data pengalaman kerja
      $arrWork = array();
      $strSQL  = "SELECT * FROM hrd_employee_work ";
      $strSQL .= "WHERE id_employee = '" .$rowDb['id']."' ORDER BY year_from, month_from, day_from ";
      $resTmp = $db->execute($strSQL);
      while ($rowTmp = $db->fetchrow($resTmp)) {
        $arrWork[] = $rowTmp;
      }
      $intWork = count($arrWork);
      if ($intMax < $intWork) $intMax = $intWork;

      $strResult .= "<tr valign=top title=\"$strEmployeeInfo\">\n";
      $strResult .= "  <td nowrap align=right>$intRows</td>\n";
      $strResult .= "  <td nowrap>" .$rowDb['employee_id']. "</td>";
      $strResult .= "  <td nowrap>" .$rowDb['employee_id_2']. "</td>";
      $strResult .= "  <td nowrap>" .$rowDb['employee_name']. "</td>";
      $strResult .= "  <td>" .$rowDb['division_code']. "</td>";
      $strResult .= "  <td>" .$rowDb['department_code']. "</td>";
      $strResult .= "  <td>" .$rowDb['section_code']. "</td>";
      $strResult .= "  <td>" .$rowDb['position_code']. "</td>";
      $strResult .= "  <td>" .$rowDb['functional_position_code']. "</td>";

      if ($intTraining > 0) { // tambahkan info training jika ada
        $arrTmp = $arrTraining[0];
        $strPeriode1 = "";
        if ($arrTmp['day_from'] != "") $strPeriode1 .= $arrTmp['day_from'];
        if ($arrTmp['month_from'] != "") $strPeriode1 .= " " .getBulanSingkat($arrTmp['month_from']);
        if ($arrTmp['year_from'] != "") $strPeriode1 .= " " .$arrTmp['year_from'];

        $strPeriode2 = "";
        if ($arrTmp['day_thru'] != "") $strPeriode2 .= $arrTmp['day_thru'];
        if ($arrTmp['month_thru'] != "") $strPeriode2 .= " " .getBulanSingkat($arrTmp['month_thru']);
        if ($arrTmp['year_thru'] != "") $strPeriode2 .= " " .$arrTmp['year_thru'];

        if ($strPeriode1 == "" || $strPeriode2 == "") $strPeriode = $strPeriode1 . $strPeriode2;
        else $strPeriode = $strPeriode1 ." - ". $strPeriode2;

        $strResult .= "  <td nowrap>" .$arrTmp['subject']. "</td>\n";
        $strResult .= "  <td nowrap>" .$arrTmp['institution']. "</td>\n";
        $strResult .= "  <td nowrap>" .$arrTmp['location']. "</td>\n";
        $strResult .= "  <td nowrap>" .$arrTmp['trainer']. "</td>\n";
        $strResult .= "  <td nowrap>" .$strPeriode. "</td>\n";
        $strResult .= "  <td nowrap>" .$arrTmp['masa_ikatan_dinas']. "</td>\n";
        $strResult .= "  <td nowrap>" .$arrTmp['note']. "</td>\n";
        $strResult .= "  <td nowrap align='center'>" .((isset($arrTmp['certificate']) && $arrTmp['certificate'] === TRUE) ? '&#10004' : '-' ). "</td>\n";
      } else {
        $strResult .= "  <td>&nbsp;</td>";
        $strResult .= "  <td>&nbsp;</td>";
        $strResult .= "  <td>&nbsp;</td>";
        $strResult .= "  <td>&nbsp;</td>";
        $strResult .= "  <td>&nbsp;</td>";
        $strResult .= "  <td>&nbsp;</td>";
        $strResult .= "  <td>&nbsp;</td>";
        $strResult .= "  <td>&nbsp;</td>";
      }

      if ($intEducation > 0) { // tambahkan info pendidikan jika ada
        $arrTmp = $arrEducation[0];
        $strPeriode1 = "";
        if ($arrTmp['day_from'] != "") $strPeriode1 .= $arrTmp['day_from'];
        if ($arrTmp['month_from'] != "") $strPeriode1 .= " " .getBulanSingkat($arrTmp['month_from']);
        if ($arrTmp['year_from'] != "") $strPeriode1 .= " " .$arrTmp['year_from'];

        $strPeriode2 = "";
        if ($arrTmp['day_thru'] != "") $strPeriode2 .= $arrTmp['day_thru'];
        if ($arrTmp['month_thru'] != "") $strPeriode2 .= " " .getBulanSingkat($arrTmp['month_thru']);
        if ($arrTmp['year_thru'] != "") $strPeriode2 .= " " .$arrTmp['year_thru'];

        if ($strPeriode1 == "" || $strPeriode2 == "") $strPeriode = $strPeriode1 . $strPeriode2;
        else $strPeriode = $strPeriode1 ." - ". $strPeriode2;

        $strResult .= "  <td nowrap>" .$arrTmp['education_level_code']. "</td>\n";
        $strResult .= "  <td nowrap>" .$arrTmp['institution']. "</td>\n";
        $strResult .= "  <td nowrap>" .$arrTmp['faculty']. "</td>\n";
        $strResult .= "  <td nowrap>" .$arrTmp['location']. "</td>\n";
        $strResult .= "  <td nowrap>" .$strPeriode. "</td>\n";
      } else {
        $strResult .= "  <td>&nbsp;</td>";
        $strResult .= "  <td>&nbsp;</td>";
        $strResult .= "  <td>&nbsp;</td>";
        $strResult .= "  <td>&nbsp;</td>";
        $strResult .= "  <td>&nbsp;</td>";
      }

      if ($intWork > 0) { // tambahkan info pengalaman kerja jika ada
        $arrTmp = $arrWork[0];
        $strPeriode1 = "";
        if ($arrTmp['day_from'] != "") $strPeriode1 .= $arrTmp['day_from'];
        if ($arrTmp['month_from'] != "") $strPeriode1 .= " " .getBulanSingkat($arrTmp['month_from']);
        if ($arrTmp['year_from'] != "") $strPeriode1 .= " " .$arrTmp['year_from'];

        $strPeriode2 = "";
        if ($arrTmp['day_thru'] != "") $strPeriode2 .= $arrTmp['day_thru'];
        if ($arrTmp['month_thru'] != "") $strPeriode2 .= " " .getBulanSingkat($arrTmp['month_thru']);
        if ($arrTmp['year_thru'] != "") $strPeriode2 .= " " .$arrTmp['year_thru'];

        if ($strPeriode1 == "" || $strPeriode2 == "") $strPeriode = $strPeriode1 . $strPeriode2;
        else $strPeriode = $strPeriode1 ." - ". $strPeriode2;

        $strResult .= "  <td nowrap>" .$arrTmp['institution']. "</td>\n";
        $strResult .= "  <td nowrap>" .$arrTmp['location']. "</td>\n";
        $strResult .= "  <td nowrap>" .$arrTmp['position']. "</td>\n";
        $strResult .= "  <td nowrap>" .$strPeriode. "</td>\n";
      } else {
        $strResult .= "  <td>&nbsp;</td>";
        $strResult .= "  <td>&nbsp;</td>";
        $strResult .= "  <td>&nbsp;</td>";
        $strResult .= "  <td>&nbsp;</td>";
      }

      $strResult .= "</tr>\n";

      // tambahkan data training, pendidikan, atau kerja yang tersisa
      if ($intMax > 1) {
        for($i = 1; $i < $intMax; $i++) {

          $strResult .= "<tr valign=top title=\"$strEmployeeInfo\">\n";
          $strResult .= " <td colspan=9>&nbsp;</td>\n";

          if ($intTraining > $i) { // tambahkan info training jika ada
            $arrTmp = $arrTraining[$i];
            $strPeriode1 = "";
            if ($arrTmp['day_from'] != "") $strPeriode1 .= $arrTmp['day_from'];
            if ($arrTmp['month_from'] != "") $strPeriode1 .= " " .getBulanSingkat($arrTmp['month_from']);
            if ($arrTmp['year_from'] != "") $strPeriode1 .= " " .$arrTmp['year_from'];

            $strPeriode2 = "";
            if ($arrTmp['day_thru'] != "") $strPeriode2 .= $arrTmp['day_thru'];
            if ($arrTmp['month_thru'] != "") $strPeriode2 .= " " .getBulanSingkat($arrTmp['month_thru']);
            if ($arrTmp['year_thru'] != "") $strPeriode2 .= " " .$arrTmp['year_thru'];

            if ($strPeriode1 == "" || $strPeriode2 == "") $strPeriode = $strPeriode1 . $strPeriode2;
            else $strPeriode = $strPeriode1 ." - ". $strPeriode2;

            $strResult .= "  <td nowrap>" .$arrTmp['subject']. "</td>\n";
            $strResult .= "  <td nowrap>" .$arrTmp['institution']. "</td>\n";
            $strResult .= "  <td nowrap>" .$arrTmp['location']. "</td>\n";
            $strResult .= "  <td nowrap>" .$arrTmp['trainer']. "</td>\n";
            $strResult .= "  <td nowrap>" .$strPeriode. "</td>\n";
            $strResult .= "  <td nowrap>" .$arrTmp['masa_ikatan_dinas']. "</td>\n";
            $strResult .= "  <td nowrap>" .$arrTmp['note']. "</td>\n";
            $strResult .= "  <td nowrap align='center'>" .((isset($arrTmp['certificate']) && $arrTmp['certificate'] == TRUE) ? '&#10004' : '-' ). "</td>\n";
          } else {
            $strResult .= "  <td>&nbsp;</td>";
            $strResult .= "  <td>&nbsp;</td>";
            $strResult .= "  <td>&nbsp;</td>";
            $strResult .= "  <td>&nbsp;</td>";
            $strResult .= "  <td>&nbsp;</td>";
            $strResult .= "  <td>&nbsp;</td>";
            $strResult .= "  <td>&nbsp;</td>";
            $strResult .= "  <td>&nbsp;</td>";
          }

          if ($intEducation > $i) { // tambahkan info pendidikan jika ada
            $arrTmp = $arrEducation[$i];
            $strPeriode1 = "";
            if ($arrTmp['day_from'] != "") $strPeriode1 .= $arrTmp['day_from'];
            if ($arrTmp['month_from'] != "") $strPeriode1 .= " " .getBulanSingkat($arrTmp['month_from']);
            if ($arrTmp['year_from'] != "") $strPeriode1 .= " " .$arrTmp['year_from'];

            $strPeriode2 = "";
            if ($arrTmp['day_thru'] != "") $strPeriode2 .= $arrTmp['day_thru'];
            if ($arrTmp['month_thru'] != "") $strPeriode2 .= " " .getBulanSingkat($arrTmp['month_thru']);
            if ($arrTmp['year_thru'] != "") $strPeriode2 .= " " .$arrTmp['year_thru'];

            if ($strPeriode1 == "" || $strPeriode2 == "") $strPeriode = $strPeriode1 . $strPeriode2;
            else $strPeriode = $strPeriode1 ." - ". $strPeriode2;

            $strResult .= "  <td nowrap>" .$arrTmp['education_level_code']. "</td>\n";
            $strResult .= "  <td nowrap>" .$arrTmp['institution']. "</td>\n";
            $strResult .= "  <td nowrap>" .$arrTmp['location']. "</td>\n";
            $strResult .= "  <td>" .$arrTmp['faculty']. "</td>\n";
            $strResult .= "  <td nowrap>" .$strPeriode. "</td>\n";
          } else {
            $strResult .= "  <td>&nbsp;</td>";
            $strResult .= "  <td>&nbsp;</td>";
            $strResult .= "  <td>&nbsp;</td>";
            $strResult .= "  <td>&nbsp;</td>";
            $strResult .= "  <td>&nbsp;</td>";
          }

          if ($intWork > $i) { // tambahkan info pengalaman kerja jika ada
            $arrTmp = $arrWork[$i];
            $strPeriode1 = "";
            if ($arrTmp['day_from'] != "") $strPeriode1 .= $arrTmp['day_from'];
            if ($arrTmp['month_from'] != "") $strPeriode1 .= " " .getBulanSingkat($arrTmp['month_from']);
            if ($arrTmp['year_from'] != "") $strPeriode1 .= " " .$arrTmp['year_from'];

            $strPeriode2 = "";
            if ($arrTmp['day_thru'] != "") $strPeriode2 .= $arrTmp['day_thru'];
            if ($arrTmp['month_thru'] != "") $strPeriode2 .= " " .getBulanSingkat($arrTmp['month_thru']);
            if ($arrTmp['year_thru'] != "") $strPeriode2 .= " " .$arrTmp['year_thru'];

            if ($strPeriode1 == "" || $strPeriode2 == "") $strPeriode = $strPeriode1 . $strPeriode2;
            else $strPeriode = $strPeriode1 ." - ". $strPeriode2;

            $strResult .= "  <td nowrap>" .$arrTmp['institution']. "</td>\n";
            $strResult .= "  <td nowrap>" .$arrTmp['location']. "</td>\n";
            $strResult .= "  <td nowrap>" .$arrTmp['position']. "</td>\n";
            $strResult .= "  <td nowrap>" .$strPeriode. "</td>\n";
          } else {
            $strResult .= "  <td>&nbsp;</td>";
            $strResult .= "  <td>&nbsp;</td>";
            $strResult .= "  <td>&nbsp;</td>";
            $strResult .= "  <td>&nbsp;</td>";
          }

          $strResult .= "</tr>\n";
        }
      }
    }

    $intTotalData = $intRows;
    if ($intRows > 0) {
      writeLog(ACTIVITY_VIEW, MODULE_PAYROLL,"$intRows data",0);
    }


    return $strResult;
  } // showData

  // fungsi untuk menampilkan data, dalam excel
  // $db = kelas database, $intRows = jumlah baris (return)
  // $strDataDate adalah tanggal yang diinginkan
  // $strKriteria = query kriteria, $strOrder = query ORder by
  // $intStart = record mulai, $bolLimit = dibatasi sesuai limit global
  function getExcelData($db, &$intRows, $strKriteria = "",$strOrder = "", $bolFull = false) {
    //include("../global/class.excelExport.php");

    global $words;
    global $bolPrint;
    global $ARRAY_EMPLOYEE_STATUS;
    global $intTotalData;
    global $bolIsEmployee;

    //-----------------
    $arrHeader = array();
    $arrData = array();

    // bikin dulu header, apa aja yang mau ditampilkan
    $intCols = 0;
    $arrHeader[$intCols++] = array("text" => "NO", "type" => "numeric", "width" => 5);
    $arrHeader[$intCols++] = array("text" => "EMPL. ID", "type" => "", "width" => 10);
    $arrHeader[$intCols++] = array("text" => "EMPL. NAME", "type" => "", "width" => 17);
    $arrHeader[$intCols++] = array("text" => "ASSIGNMENT NOTE", "type" => "", "width" => 6);
    $arrHeader[$intCols++] = array("text" => "NICK", "type" => "", "width" => 8);
    $arrHeader[$intCols++] = array("text" => "AGE", "type" => "numeric", "width" => 5);
    $arrHeader[$intCols++] = array("text" => "EMP. STATUS", "type" => "", "width" => 10);
    $arrHeader[$intCols++] = array("text" => "JOIN DATE", "type" => "date", "width" => 10);
    $arrHeader[$intCols++] = array("text" => "DUE DATE", "type" => "date", "width" => 10);
    $arrHeader[$intCols++] = array("text" => "PERM. DATE", "type" => "date", "width" => 10);
    $arrHeader[$intCols++] = array("text" => "RESIGN DATE", "type" => "date", "width" => 10);
    $arrHeader[$intCols++] = array("text" => "STATUS", "type" => "", "width" => 7);
    $arrHeader[$intCols++] = array("text" => "DEPT", "type" => "", "width" => 6);
    $arrHeader[$intCols++] = array("text" => "SECT", "type" => "", "width" => 6);
    $arrHeader[$intCols++] = array("text" => "FUNCTION", "type" => "", "width" => 10);
    $arrHeader[$intCols++] = array("text" => "LEVEL", "type" => "", "width" => 7);
    $arrHeader[$intCols++] = array("text" => "JOB GRADE", "type" => "", "width" => 7);
    $arrHeader[$intCols++] = array("text" => "FAM. ST", "type" => "", "width" => 6);
    $arrHeader[$intCols++] = array("text" => "ADDRESS", "type" => "", "width" => 20);
    $arrHeader[$intCols++] = array("text" => "CITY", "type" => "", "width" => 7);
    $arrHeader[$intCols++] = array("text" => "ZIP", "type" => "", "width" => 7);
    $arrHeader[$intCols++] = array("text" => "PHONE", "type" => "", "width" => 10);
    $arrHeader[$intCols++] = array("text" => "EMAIL", "type" => "", "width" => 10);
    $arrHeader[$intCols++] = array("text" => "BIRTHPLACE", "type" => "", "width" => 10);
    $arrHeader[$intCols++] = array("text" => "BIRTHDATE", "type" => "date", "width" => 10);
    $arrHeader[$intCols++] = array("text" => "WEIGHT", "type" => "", "width" => 8);
    $arrHeader[$intCols++] = array("text" => "HEIGHT", "type" => "", "width" => 8);
    $arrHeader[$intCols++] = array("text" => "BLOOD", "type" => "", "width" => 7);
    $arrHeader[$intCols++] = array("text" => "ID CARD", "type" => "", "width" => 10);
    $arrHeader[$intCols++] = array("text" => "DRIVE A", "type" => "", "width" => 10);
    $arrHeader[$intCols++] = array("text" => "DRIVE B", "type" => "", "width" => 10);
    $arrHeader[$intCols++] = array("text" => "DRIVE C", "type" => "", "width" => 10);
    $arrHeader[$intCols++] = array("text" => "PASSPORT", "type" => "", "width" => 10);
    $arrHeader[$intCols++] = array("text" => "NPWP", "type" => "", "width" => 10);
    $arrHeader[$intCols++] = array("text" => "BANK 1", "type" => "", "width" => 10);
    $arrHeader[$intCols++] = array("text" => "BRANCH 1", "type" => "", "width" => 10);
    $arrHeader[$intCols++] = array("text" => "ACCOUNT 1", "type" => "", "width" => 15);
    $arrHeader[$intCols++] = array("text" => "ACCOUNT NAME 1", "type" => "", "width" => 20);
    $arrHeader[$intCols++] = array("text" => "BANK 2", "type" => "", "width" => 10);
    $arrHeader[$intCols++] = array("text" => "BRANCH 2", "type" => "", "width" => 10);
    $arrHeader[$intCols++] = array("text" => "ACCOUNT 2", "type" => "", "width" => 15);
    $arrHeader[$intCols++] = array("text" => "ACCOUNT NAME 2", "type" => "", "width" => 20);

    $intRows = 0;
    $strSQL  = "SELECT *,(EXTRACT(YEAR FROM AGE(birthday))) AS umur FROM hrd_employee ";
    $strSQL .= "WHERE flag=0 $strKriteria ";
    $strSQL .= "ORDER BY $strOrder employee_name ";

    $resDb = $db->execute($strSQL);
    while ($rowDb = $db->fetchrow($resDb)) {
      $intCols = 0;
      $arrData[$intRows][$intCols++] = ($intRows + 1);
      $arrData[$intRows][$intCols++] = "".$rowDb['employee_id'];
      $arrData[$intRows][$intCols++] = "".$rowDb['employee_name'];
      $arrData[$intRows][$intCols++] = "".$rowDb['letter_code'];
      $arrData[$intRows][$intCols++] = "".$rowDb['nickname'];
      $arrData[$intRows][$intCols++] = "".$rowDb['umur'];
      $arrData[$intRows][$intCols++] = $words[$ARRAY_EMPLOYEE_STATUS[$rowDb['employee_status']]];
      $arrData[$intRows][$intCols++] = pgDateFormat($rowDb['join_date'], "d-M-y");
      $arrData[$intRows][$intCols++] = pgDateFormat($rowDb['due_date'], "d-M-y");
      $arrData[$intRows][$intCols++] = pgDateFormat($rowDb['permanent_date'], "d-M-y");
      $arrData[$intRows][$intCols++] = pgDateFormat($rowDb['resign_date'], "d-M-y");
      $arrData[$intRows][$intCols++] = ($rowDb['active'] == 1) ? $words['active'] : $words['not active'];
      $arrData[$intRows][$intCols++] = "".$rowDb['department_code'];
      $arrData[$intRows][$intCols++] = "".$rowDb['section_code'];
      $arrData[$intRows][$intCols++] = "".$rowDb['function'];
      $arrData[$intRows][$intCols++] = "".$rowDb['position_code'];
      $arrData[$intRows][$intCols++] = "".$rowDb['grade_code'];
      $arrData[$intRows][$intCols++] = "".$rowDb['family_status_code'];
      $arrData[$intRows][$intCols++] = "".$rowDb['primary_address'];
      $arrData[$intRows][$intCols++] = "".$rowDb['primary_city'];
      $arrData[$intRows][$intCols++] = "".$rowDb['primary_zip'];
      $arrData[$intRows][$intCols++] = "".$rowDb['primary_phone'];
      $arrData[$intRows][$intCols++] = "".$rowDb['email'];
      $arrData[$intRows][$intCols++] = "".$rowDb['birthplace'];
      $arrData[$intRows][$intCols++] = pgDateFormat($rowDb['birthday'],"d-M-y");
      $arrData[$intRows][$intCols++] = "".$rowDb['weight'];
      $arrData[$intRows][$intCols++] = "".$rowDb['height'];
      $arrData[$intRows][$intCols++] = "".$rowDb['blood_type'];
      $arrData[$intRows][$intCols++] = "".$rowDb['id_card'];
      $arrData[$intRows][$intCols++] = "".$rowDb['driver_license_a'];
      $arrData[$intRows][$intCols++] = "".$rowDb['driver_license_b'];
      $arrData[$intRows][$intCols++] = "".$rowDb['driver_license_c'];
      $arrData[$intRows][$intCols++] = "".$rowDb['passport'];
      $arrData[$intRows][$intCols++] = "".$rowDb['npwp'];

      $intRows++;

    }

    $objExl = new CxlsExport("employee.xls");
    $objExl->setHeaders("LIST OF EMPLOYEE", "", "");
    $objExl->setData($arrHeader, $arrData);
    $objExl->showExcel();

    if ($intRows > 0) {
      writeLog(ACTIVITY_EXPORT, MODULE_PAYROLL,"$intRows data",0);
    }

    return $strResult;
  } // showData

  //----------------------------------------------------------------------

  //----MAIN PROGRAM -----------------------------------------------------
  $db = new CdbClass;
  if ($db->connect()) {
    getUserEmployeeInfo();

    // ------ AMBIL DATA KRITERIA -------------------------

    (isset($_REQUEST['filterEmployeeID'])) ? $strFilterEmployeeID = trim($_REQUEST['filterEmployeeID']) : $strFilterEmployeeID = "";
    (isset($_REQUEST['filterPosition'])) ? $strFilterPosition = trim($_REQUEST['filterPosition']) : $strFilterPosition = "";
    (isset($_REQUEST['filterStatus'])) ? $strFilterStatus = $_REQUEST['filterStatus'] : $strFilterStatus = "";
    (isset($_REQUEST['filterDivision'])) ? $strFilterDivision = $_REQUEST['filterDivision'] : $strFilterDivision = "";
    (isset($_REQUEST['filterDepartment'])) ? $strFilterDepartment = $_REQUEST['filterDepartment'] : $strFilterDepartment = "";
    (isset($_REQUEST['filterSection'])) ? $strFilterSection = $_REQUEST['filterSection'] : $strFilterSection = "";
    (isset($_REQUEST['filterSubSection'])) ? $strfilterSubSection = $_REQUEST['filterSubSection'] : $strfilterSubSection = "";
    (isset($_REQUEST['filterBranch'])) ? $strfilterBranch = $_REQUEST['filterBranch'] : $strfilterBranch = "";
    (isset($_REQUEST['filterGrade'])) ? $strFilterGrade = $_REQUEST['filterGrade'] : $strFilterGrade = "";
    (isset($_REQUEST['filterFunction'])) ? $strFilterFunction = $_REQUEST['filterFunction'] : $strFilterFunction = "";
    (isset($_REQUEST['dataPage'])) ? $intCurrPage = $_REQUEST['dataPage'] : $intCurrPage = 1;
    (isset($_REQUEST['dataSort'])) ? $strSortBy = $_REQUEST['dataSort'] : $strSortBy = "";
    $strInputSortBy = $strSortBy;

    if (!is_numeric($intCurrPage)) $intCurrPage = 1;
    if ($strSortBy != "") $strSortBy = "\"$strSortBy\", ";

    scopeData($strFilterEmployeeID, $strfilterSubSection, $strFilterSection, $strFilterDepartment, $strFilterDivision, $_SESSION['sessionUserRole'], $arrUserInfo, $strfilterBranch);

    // ------------ GENERATE KRITERIA QUERY,JIKA ADA -------------

    $strKriteria = "";

    $strInfoKriteria = "";

    if ($strFilterEmployeeID != "") {
      $strKriteria .= "AND employee_id = '$strFilterEmployeeID' ";
    }
    if ($strFilterStatus != "") {
      $strKriteria .= "AND employee_status = '$strFilterStatus' ";
    }
    if ($strFilterEmployeeID != "") {
      $strKriteria .= "AND upper(employee_id) = '" .strtoupper($strFilterEmployeeID). "' ";
    }
    if ($strFilterPosition != "") {
      $strKriteria .= "AND position_code = '$strFilterPosition' ";
    }
    if ($strFilterDivision != "") {
      $strKriteria .= "AND division_code = '$strFilterDivision' ";
    }
    if ($strFilterDepartment != "") {
      $strKriteria .= "AND department_code = '$strFilterDepartment' ";
    }
    if ($strFilterSection != "") {
      $strKriteria .= "AND section_code = '$strFilterSection' ";
    }
    if ($strfilterSubSection != "") {
        $strKriteria .= "AND sub_section_code = '$strfilterSubSection' ";
    }
    if ($strfilterBranch != "") {
       $strKriteria .= "AND branch_code = '$strfilterBranch' ";
    }
    if ($strFilterFunction != "") {
      $strKriteria .= "AND \"functional_code\" = '$strFilterFunction' ";
    }
    $strKriteria .= $strKriteriaCompany;

    if ($bolCanView) {
      if (isset($_REQUEST['btnExcel'])) {

        //getExcelData($db,$intTotalData, $strKriteria, $strSortBy, $bolFull);
        $strDataDetail = getData($db,$intTotalData, $strKriteria, $intCurrPage, $bolLimit, $strSortBy);
        // ambil data CSS-nya
        if (file_exists("bw.css")) $strStyle = "bw.css";
        $strPrintCss = "";
        $strPrintInit = "";
        headeringExcel("employee_resume.xls");
      } else if (isset($_REQUEST['btnShow']) || isset($_REQUEST['btnPrint']))
      {
        $strDataDetail = getData($db,$intTotalData, $strKriteria, $intCurrPage, $bolLimit, $strSortBy);

      }
    } else {
      showError("view_denied");
      $strDataDetail = "";
    }
    // generate data hidden input dan element form input
    $intDefaultWidth = 30;
    $intDefaultWidthPx = 200;
    $intDefaultHeight = 3;

    $strInputFilterEmployeeID = "<input type=text name=\"filterEmployeeID\" id=\"filterEmployeeID\" size=$intDefaultWidth value=\"$strFilterEmployeeID\" style=\"width:$intDefaultWidthPx\" $strEmpReadonly>";
    $strInputFilterPosition = getPositionList($db, "filterPosition", $strFilterPosition, $strEmptyOption,""," style=\"width:$intDefaultWidthPx\"");
    $strInputFilterStatus = getEmployeeStatusList("filterStatus", $strFilterStatus, $strEmptyOption," style=\"width:$intDefaultWidthPx\"");
    $strInputFilterDivision = getDivisionList($db, "filterDivision", $strFilterDivision, $strEmptyOption, ""," style=\"width:$intDefaultWidthPx\" ". $ARRAY_DISABLE_GROUP['division']);
    $strInputFilterDepartment = getDepartmentList($db, "filterDepartment", $strFilterDepartment, $strEmptyOption, ""," style=\"width:$intDefaultWidthPx\" ". $ARRAY_DISABLE_GROUP['department']);
    $strInputFilterSection = getSectionList($db, "filterSection", $strFilterSection, $strEmptyOption, ""," style=\"width:$intDefaultWidthPx\" ". $ARRAY_DISABLE_GROUP['section']);
    $strInputFilterSubSection = getSubSectionList($db, "filterSubSection",$strfilterSubSection, $strEmptyOption,""," style=\"width:$intDefaultWidthPx\" ". $ARRAY_DISABLE_GROUP['sub_section']);
    $strInputFilterBranch = getBranchList($db, "filterBranch",$strfilterBranch, $strEmptyOption,""," style=\"width:$intDefaultWidthPx\" ". $ARRAY_DISABLE_GROUP['branch']);
    $strInputFilterFunction = getFunctionalPositionList($db, "filterFunction", $strFilterFunction, $strEmptyOption,""," style=\"width:$intDefaultWidthPx\"");
    $strInputCompany = getCompanyList($db, "dataCompany",$strDataCompany, $strEmptyOption2, $strKriteria2, "style=\"width:$intDefaultWidthPx\" ");

    $strHidden .= "<input type=hidden name=filterEmployeeID value=\"$strFilterEmployeeID\">";
    $strHidden .= "<input type=hidden name=filterPosition value=\"$strFilterPosition\">";
    $strHidden .= "<input type=hidden name=filterStatus value=\"$strFilterStatus\">";
    $strHidden .= "<input type=hidden name=filterDivision value=\"$strFilterDivision\">";
    $strHidden .= "<input type=hidden name=filterDepartment value=\"$strFilterDepartment\">";
    $strHidden .= "<input type=hidden name=filterSection value=\"$strFilterSection\">";
    $strHidden .= "<input type=hidden name=filterSubSection value=\"$strfilterSubSection\">";
    $strHidden .= "<input type=hidden name=filterBranch value=\"$strfilterBranch\">";
    $strHidden .= "<input type=hidden name=filterFunction value=\"$strFilterFunction\">";
  }
  //$strInitAction .= "    document.formInput.filterEmployeeID.focus();   ";


  $tbsPage = new clsTinyButStrong ;

  //write this variable in every page
  $strPageTitle =   $strPageTitle = getWords($dataPrivilege['menu_name']);
  if (trim($dataPrivilege['icon_file']) == "") $pageIcon = "../images/icons/blank.gif";
  else $pageIcon = "../images/icons/".$dataPrivilege['icon_file'];

  if ($bolPrint) {

    $strTemplateFile = getTemplate("employee_resume_all_print.html");
    $tbsPage->LoadTemplate($strTemplateFile) ;
  } else {
    $strTemplateFile = getTemplate("employee_resume_all.html");
    $tbsPage->LoadTemplate($strMainTemplate) ;
  }
  //------------------------------------------------
  //Load Master Template
  $tbsPage->Show() ;

?>
