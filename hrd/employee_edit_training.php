<?php

  include_once('../global/session.php');
  include_once('global.php');
  include_once('form_object.php');
  // Data Privilage followed from parent (employee_edit.php)
  $dataPrivilege = getDataPrivileges(basename("employee_edit.php"), $bolCanView, $bolCanEdit, $bolCanDelete, $bolCanApprove, $bolCanCheck, $bolCanAcknowledge, $bolCanApprove2);
  if (!$bolCanView && $_POST['dataID'] == "") die(getWords('view denied'));

  //---- INISIALISASI ----------------------------------------------------
  $strDataDetail = "";
  $strEmployeeID = "";
  $stremployee_name = "";
  $intTotalData = 0;
  $intDefaultWidth = 30;
  $intDefaultWidthPx = 250;
  $strMessages = "";
  $strMsgClass = "";
  $bolError = false;
  //----------------------------------------------------------------------

  //--- DAFTAR FUNSI------------------------------------------------------
  // fungsi untuk menampilkan data
  // $db = kelas database, $intRows = jumlah baris (return)
  // $strKriteria = query kriteria, $strOrder = query ORder by
  function getData($db, &$intRows, $strKriteria = "", $strOrder = "") {
    global $words;
    global $ARRAY_FAMILY_RELATION;
    global $intDefaultWidth;
    global $intDefaultWidthPx;
    global $strEmptyOption;

    $intRows = 0;
    $intShown = 0;
    $intAdd = 6; // maksimum tambahan
    $strResult = "";
    $strNow = date("Y-m-d");

    $strSQL  = "SELECT * FROM hrd_employee_training ";
    $strSQL .= "WHERE 1=1 $strKriteria ORDER BY $strOrder year_from ";
    $resDb = $db->execute($strSQL);
    $rowDb = $db->fetchrow($resDb);

    while ($rowDb = $db->fetchrow($resDb)) {
      $intRows++;
      $intShown++;
      $strResult .= "<tr valign=top id=\"detailRows$intRows\"><td><table cellspacing=0 cellpadding=1 border=0 width=100%>";

      $strResult .= "<tr valign=top>\n";
      $strResult .= "  <td nowrap>&nbsp;" .$words['subject']."</td>";
      $strResult .= "  <td nowrap>:&nbsp;</td>";
      $strResult .= "  <td>&nbsp;<input type=text size=$intDefaultWidth maxlength=127 name=detailSubject$intRows value=\"" .$rowDb['subject']. "\" style=\"width:$intDefaultWidthPx\"></td>";
      $strResult .= "</tr>\n";

      $strResult .= "<tr valign=top>\n";
      $strResult .= "  <td nowrap>&nbsp;" .$words['institution']."</td>";
      $strResult .= "  <td nowrap><input type=hidden name=detailID$intRows value=\"" .$rowDb['id']. "\">:&nbsp;</td>";
      $strResult .= "  <td>&nbsp;<input type=text size=$intDefaultWidth maxlength=63 name=detailInstitution$intRows value=\"" .$rowDb['institution']. "\" style=\"width:$intDefaultWidthPx\"></td>";
      $strResult .= "</tr>\n";

      $strResult .= "<tr valign=top>\n";
      $strResult .= "  <td nowrap>&nbsp;" .$words['trainer']."</td>";
      $strResult .= "  <td nowrap>:&nbsp;</td>";
      $strResult .= "  <td>&nbsp;<input type=text size=$intDefaultWidth maxlength=63 name=detailTrainer$intRows value=\"" .$rowDb['trainer']. "\" style=\"width:$intDefaultWidthPx\"></td>";
      $strResult .= "</tr>\n";
      $strResult .= "<tr valign=top>\n";
      $strResult .= "  <td nowrap>&nbsp;" .$words['location']."</td>";
      $strResult .= "  <td nowrap>:&nbsp;</td>";
      $strResult .= "  <td>&nbsp;<input type=text size=$intDefaultWidth maxlength=31 name=detailLocation$intRows value=\"" .$rowDb['location']. "\" style=\"width:$intDefaultWidthPx\"></td>";
      $strResult .= "</tr>\n";

      $strResult .= "<tr valign=top>\n";
      $strResult .= "  <td nowrap>&nbsp;" .$words['date from']."</td>";
      $strResult .= "  <td nowrap>:&nbsp;</td>";
      $strTmp  = getDayList("detailDayFrom$intRows",$rowDb['day_from'],$strEmptyOption);
      $strTmp .= getMonthList("detailMonthFrom$intRows",$rowDb['month_from'],$strEmptyOption);
      $strTmp .= getYearList("detailYearFrom$intRows",$rowDb['year_from'],$strEmptyOption);
      $strResult .= "  <td nowrap>&nbsp;$strTmp</td>";
      $strResult .= "</tr>\n";

      $strResult .= "<tr valign=top>\n";
      $strResult .= "  <td nowrap>&nbsp;" .$words['date thru']."</td>";
      $strResult .= "  <td nowrap>:&nbsp;</td>";
      $strTmp  = getDayList("detailDayThru$intRows",$rowDb['day_thru'],$strEmptyOption);
      $strTmp .= getMonthList("detailMonthThru$intRows",$rowDb['month_thru'],$strEmptyOption);
      $strTmp .= getYearList("detailYearThru$intRows",$rowDb['year_thru'],$strEmptyOption);
      $strResult .= "  <td nowrap>&nbsp;$strTmp</td>";
      $strResult .= "</tr>\n";

      $strResult .= "<tr valign=top>\n";
      $strResult .= "  <td nowrap>&nbsp;" .$words['masa ikatan dinas']."</td>";
      $strResult .= "  <td nowrap>:&nbsp;</td>";
      $strTmp  = getMasaIkatanDinas("detailMasaIkatanDinas$intRows",$rowDb['masa_ikatan_dinas'],$strEmptyOption,$strDisabled);
      $strResult .= "  <td nowrap>&nbsp;$strTmp</td>";
      $strResult .= "</tr>\n";

      ($rowDb['certificate'] == 't') ? $strCheck = "checked" : $strCheck = "";


      $strResult .= "<tr valign=top>\n";
      $strResult .= "  <td nowrap>&nbsp;" .$words['certificate']."</td>";
      $strResult .= "  <td nowrap>:&nbsp;</td>";
      $strResult .= "  <td>&nbsp;<input type=checkbox name=detailCertificate$intRows value=\"".$rowDb['id']."\" $strCheck></td>";
      $strResult .= "</tr>\n";

      $strResult .= "<tr valign=top>\n";
      $strResult .= "  <td nowrap>&nbsp;" .$words['note']."</td>";
      $strResult .= "  <td nowrap>:&nbsp;</td>";
      $strResult .= "  <td>&nbsp;<textarea cols=$intDefaultWidth rows=2 name=detailNote$intRows style=\"width:$intDefaultWidthPx\">" .$rowDb['note']. "</textarea></td>";
      $strResult .= "</tr>\n";

      $strAction = " onChange = \"chkDeleteChanged($intRows);\" ";
      $strResult .= "<tr valign=top>\n";
      $strResult .= "  <td nowrap>&nbsp;" .$words['delete']."</td>";
      $strResult .= "  <td nowrap>:&nbsp;</td>";
      $strResult .= "  <td>&nbsp;<input type=checkbox name='chkID$intRows' value=\"" .$rowDb['id']. "\" $strAction></td>\n";
      $strResult .= "</tr>\n";
      $strResult .= "</table></td></tr>\n";

      writeLog(ACTIVITY_VIEW, MODULE_PAYROLL,"",0);
    }

    // tambahkan dengan data kosong
    for ($i = 1; $i <= $intAdd; $i++) {
      $intRows++;
      if ($intRows == 1) {
        $strResult .= "<tr valign=top id=\"detailRows$intRows\"><td><table cellspacing=0 cellpadding=1 border=0 width=100%>\n";
        $intShown++;
        $strDisabled = "";
      } else {
        $strResult .= "<tr valign=top id=\"detailRows$intRows\" style=\"display:none\"> <td> <table cellspacing=0 cellpadding=1 border=0 width=100%>\n";
        $strDisabled = "disabled";
      }

      $strResult .= "<tr valign=top>\n";
      $strResult .= "  <td nowrap>&nbsp;" .$words['subject']."</td>";
      $strResult .= "  <td nowrap>:&nbsp;</td>";
      $strResult .= "  <td>&nbsp;<input type=text size=$intDefaultWidth maxlength=50 name=detailSubject$intRows $strDisabled style=\"width:$intDefaultWidthPx\"></td>";
      $strResult .= "</tr>\n";

      $strResult .= "<tr valign=top>\n";
      $strResult .= "  <td nowrap>&nbsp;" .$words['institution']."</td>";
      $strResult .= "  <td nowrap>:&nbsp;</td>";
      $strResult .= "  <td>&nbsp;<input type=text size=$intDefaultWidth maxlength=90 name=detailInstitution$intRows $strDisabled style=\"width:$intDefaultWidthPx\"></td>";
      $strResult .= "</tr>\n";

      $strResult .= "<tr valign=top>\n";
      $strResult .= "  <td nowrap>&nbsp;" .$words['trainer']."</td>";
      $strResult .= "  <td nowrap>:&nbsp;</td>";
      $strResult .= "  <td>&nbsp;<input type=text size=$intDefaultWidth maxlength=50 name=detailTrainer$intRows $strDisabled style=\"width:$intDefaultWidthPx\"></td>";
      $strResult .= "</tr>\n";

      $strResult .= "<tr valign=top>\n";
      $strResult .= "  <td nowrap>&nbsp;" .$words['location']."</td>";
      $strResult .= "  <td nowrap>:&nbsp;</td>";
      $strResult .= "  <td>&nbsp;<input type=text size=$intDefaultWidth maxlength=50 name=detailLocation$intRows $strDisabled style=\"width:$intDefaultWidthPx\"></td>";
      $strResult .= "</tr>\n";

      $strResult .= "<tr valign=top>\n";
      $strResult .= "  <td nowrap>&nbsp;" .$words['date from']."</td>";
      $strResult .= "  <td nowrap>:&nbsp;</td>";
      $strTmp  = getDayList("detailDayFrom$intRows","",$strEmptyOption, $strDisabled);
      $strTmp .= getMonthList("detailMonthFrom$intRows","",$strEmptyOption, $strDisabled);
      $strTmp .= getYearList("detailYearFrom$intRows","",$strEmptyOption,$strDisabled);
      $strResult .= "  <td nowrap>&nbsp;$strTmp</td>";
      $strResult .= "</tr>\n";

      $strResult .= "<tr valign=top>\n";
      $strResult .= "  <td nowrap>&nbsp;" .$words['date thru']."</td>";
      $strResult .= "  <td nowrap>:&nbsp;</td>";
      $strTmp  = getDayList("detailDayThru$intRows","",$strEmptyOption,$strDisabled);
      $strTmp .= getMonthList("detailMonthThru$intRows","",$strEmptyOption,$strDisabled);
      $strTmp .= getYearList("detailYearThru$intRows","",$strEmptyOption,$strDisabled);
      $strResult .= "  <td nowrap>&nbsp;$strTmp</td>";
      $strResult .= "</tr>\n";

      $strResult .= "<tr valign=top>\n";
      $strResult .= "  <td nowrap>&nbsp;" .getwords("masa ikatan dinas")."</td>";
      $strResult .= "  <td nowrap>:&nbsp;</td>";
      $strTmp  = getMasaIkatanDinas("detailMasaIkatanDinas$intRows",$rowDb['masa_ikatan_dinas'],$strEmptyOption,$strDisabled);
      $strResult .= "  <td nowrap>&nbsp;$strTmp&nbsp".getwords("year")."</td>";
      $strResult .= "</tr>\n";

      $strResult .= "<tr valign=top>\n";
      $strResult .= "  <td nowrap>&nbsp;" .$words['certificate']."</td>";
      $strResult .= "  <td nowrap>:&nbsp;</td>";
      $strResult .= "  <td>&nbsp;<input type=checkbox name=detailCertificate$intRows $strDisabled></textarea></td>";
      $strResult .= "</tr>\n";

      $strResult .= "<tr valign=top>\n";
      $strResult .= "  <td nowrap>&nbsp;" .$words['note']."</td>";
      $strResult .= "  <td nowrap>:&nbsp;</td>";
      $strResult .= "  <td>&nbsp;<textarea cols=$intDefaultWidth rows=2 name=detailNote$intRows $strDisabled style=\"width:$intDefaultWidthPx\"></textarea></td>";
      $strResult .= "</tr>\n";

      $strResult .= "<tr valign=top>\n";
      $strResult .= "  <td nowrap>&nbsp;" .$words['delete']."</td>";
      $strResult .= "  <td nowrap>:&nbsp;</td>";
      $strAction = " onChange = \"chkDeleteChanged($intRows);\" ";
      $strResult .= "  <td>&nbsp;<input type=checkbox name='chkID$intRows' value=\"" .$rowDb['id']. "\" $strAction></td>\n";
      $strResult .= "</tr>\n";
      $strResult .= "</table></td></tr>\n";

    }

    // tambahkan hidden data
    $strResult .= "<input type=hidden name=maxDetail value=$intRows>";
    $strResult .= "<input type=hidden name=numShow value=$intShown>";

    return $strResult;
  } // showData

  // fungsi untuk menyimpan data
  function saveData($db, $strDataID, &$strError) {
    global $_REQUEST;
    global $_SESSION;
    global $messages;

    $strError = "";

    (isset($_REQUEST['maxDetail'])) ? $intMax = $_REQUEST['maxDetail'] : $intMax = 0;
    for ($i = 1; $i <= $intMax; $i++) {
      (isset($_REQUEST['detailID'.$i])) ? $strID = $_REQUEST['detailID'.$i] : $strID = "";
      (isset($_REQUEST['detailLocation'.$i])) ? $strLocation = $_REQUEST['detailLocation'.$i] : $strLocation = "";
      (isset($_REQUEST['detailSubject'.$i])) ? $strSubject = $_REQUEST['detailSubject'.$i] : $strSubject = "";
      (isset($_REQUEST['detailTrainer'.$i])) ? $strTrainer = $_REQUEST['detailTrainer'.$i] : $strTrainer = "";
      (isset($_REQUEST['detailInstitution'.$i])) ? $strInstitution = $_REQUEST['detailInstitution'.$i] : $strInstitution = "";
      (isset($_REQUEST['detailDayFrom'.$i])) ? $strDayFrom = $_REQUEST['detailDayFrom'.$i] : $strDayFrom = "";
      (isset($_REQUEST['detailMonthFrom'.$i])) ? $strMonthFrom = $_REQUEST['detailMonthFrom'.$i] : $strMonthFrom = "";
      (isset($_REQUEST['detailYearFrom'.$i])) ? $strYearFrom = $_REQUEST['detailYearFrom'.$i] : $strYearFrom = "";
      (isset($_REQUEST['detailDayThru'.$i])) ? $strDayThru = $_REQUEST['detailDayThru'.$i] : $strDayThru = "";
      (isset($_REQUEST['detailMonthThru'.$i])) ? $strMonthThru = $_REQUEST['detailMonthThru'.$i] : $strMonthThru = "";
      (isset($_REQUEST['detailYearThru'.$i])) ? $strYearThru = $_REQUEST['detailYearThru'.$i] : $strYearThru = "";
      (isset($_REQUEST['detailMasaIkatanDinas'.$i])) ? $strMasaIkatanDinas = $_REQUEST['detailMasaIkatanDinas'.$i] : $strMasaIkatanDinas = "";
      (isset($_REQUEST['detailNote'.$i])) ? $strNote = $_REQUEST['detailNote'.$i] : $strNote = "";
      (isset($_REQUEST['detailCertificate'.$i])) ? $strCertificate = 't' : $strCertificate = 'f';
			$strMasaIkatanDinas = empty($strMasaIkatanDinas) ? 0 : $strMasaIkatanDinas;
      if ($strID == "") {
        if ($strSubject != "") { // insert new data
          $strSQL  = "INSERT INTO hrd_employee_training (created,modified, created_by, modified_by,";
          $strSQL .= "id_employee, institution, location, subject, note, certificate, ";
          $strSQL .= "day_from, month_from, year_from, ";
          $strSQL .= "day_thru, month_thru, year_thru, trainer, masa_ikatan_dinas) ";
          $strSQL .= "VALUES(now(), now(), '" .$_SESSION['sessionUserID']. "', '" .$_SESSION['sessionUserID']. "', ";
          $strSQL .= "'$strDataID', '$strInstitution','$strLocation', '$strSubject', '$strNote', '$strCertificate', ";
          $strSQL .= "'$strDayFrom', '$strMonthFrom', '$strYearFrom', ";
          $strSQL .= "'$strDayThru', '$strMonthThru', '$strYearThru', '$strTrainer', '$strMasaIkatanDinas') ";
          $resDb = $db->execute($strSQL);
          writeLog(ACTIVITY_ADD, MODULE_PAYROLL,"$strDataID",0);
        }
      } else {
        if ($strSubject == "") {
          // delete data
          $strSQL  = "DELETE FROM hrd_employee_training WHERE id = '$strID' ";
          $resDb = $db->execute($strSQL);
          writeLog(ACTIVITY_DELETE, MODULE_PAYROLL,"$strDataID",0);
        } else {
          // update data
          $strSQL  = "UPDATE hrd_employee_training SET modified_by = '" .$_SESSION['sessionUserID']. "', ";
          $strSQL .= "modified = now(), location = '$strLocation', subject = '$strSubject', ";
          $strSQL .= "institution = '$strInstitution', note = '$strNote', ";
          $strSQL .= "certificate = '$strCertificate', ";
          $strSQL .= "trainer = '$strTrainer', ";
          $strSQL .= "day_from = '$strDayFrom', month_from = '$strMonthFrom', year_from = '$strYearFrom', ";
          $strSQL .= "day_thru = '$strDayThru', month_thru = '$strMonthThru', year_thru = '$strYearThru', ";
          $strSQL .= "masa_ikatan_dinas = '$strMasaIkatanDinas'";
          $strSQL .= "WHERE id = '$strID' ";
          $resDb = $db->execute($strSQL);
          writeLog(ACTIVITY_EDIT, MODULE_PAYROLL,"$strDataID",0);
        }
      }
    }

    $strError = $messages['data_saved']." >> ".date("d-M-Y H:i:s");
    return true;
  } // saveData

  //----------------------------------------------------------------------

  //----MAIN PROGRAM -----------------------------------------------------
  $db = new CdbClass;
  if ($db->connect()) {
    getUserEmployeeInfo();

    $bolEmployee = ($_SESSION['sessionUserRole'] < ROLE_ADMIN);

    (isset($_POST['dataID'])) ? $strDataID = $_POST['dataID'] : $strDataID = "";

    if ($bolCanEdit && $strDataID != "") {
      if (isset($_POST['btnSave'])) {
        if ($bolEmployee || !saveData($db, $strDataID, $strError)) {
          //echo "<script>alert(\"$strError\")</script>";
          $bolError = true;
          if($bolEmployee) $strError = getWords("sorry, you can not edit this page");
        }
        if ($strError != "") {
          $strMessages = $strError;
          $strMsgClass = ($bolError) ? "class=bgError" : "class=bgOK";
        }
      }
    }
    if ($strDataID == "") {
      redirectPage("employee_search.php");
      exit();
    } else {

      // cari info karyawan
      $strSQL  = "SELECT employee_id, employee_name,flag, link_id FROM hrd_employee WHERE id = '$strDataID' ";
      $resDb = $db->execute($strSQL);
      if ($rowDb = $db->fetchrow($resDb)) {
        if ($rowDb['flag'] != 0 AND $rowDb['link_id'] != "") { // folder temporer
          $strDataID = $rowDb['link_id'];
        }
        $strEmployeeID = $rowDb['employee_id'];
        $strEmployeeName = strtoupper($rowDb['employee_name']);

        if ($bolEmployee && ($strEmployeeID != $arrUserInfo['employee_id'])) {
          redirectPage("employee_search.php");
          exit();
        }
      } else {
        redirectPage("employee_search.php");
        exit();
      }
      ($strDataID == "") ? $strKriteria = " AND 1=2 " : $strKriteria = " AND id_employee = '$strDataID' ";
      if ($strDataID != "") {
        
        $strDataDetail = getData($db,$intTotalData, $strKriteria);
      } else {
        showError("view_denied");
        $strDataDetail = "";
      }
    }
  }
  $strInitAction = "
  ";
  $tbsPage = new clsTinyButStrong ;

  //write this variable in every page
  $strPageTitle = getWords("employee data");
  if (trim($dataPrivilege['icon_file']) == "") $pageIcon = "../images/icons/blank.gif";
  else $pageIcon = "../images/icons/".$dataPrivilege['icon_file'];

  $strTemplateFile = getTemplate(str_replace(".php", ".html", basename($_SERVER['PHP_SELF'])));
  //------------------------------------------------
  //Load Master Template
  $tbsPage->LoadTemplate($strMainTemplate) ;
  $tbsPage->Show() ;

?>
