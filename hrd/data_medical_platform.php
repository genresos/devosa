<?php
  include_once('../global/session.php');
  include_once('global.php');

  $dataPrivilege = getDataPrivileges(basename($_SERVER['PHP_SELF']), $bolCanView, $bolCanEdit, $bolCanDelete, $bolCanApprove, $bolCanCheck, $bolCanAcknowledge, $bolCanApprove2);
  if (!$bolCanView) die(accessDenied($_SERVER['HTTP_REFERER']));


  include_once('form_object.php');

  $strContentFile = "templates/data_medical_platform.html";

  $strUserName = $_SESSION['sessionUserName'] ;

  //---- INISIALISASI ----------------------------------------------------
  $strWordsTreatmentTypeSetting = getWords("treatment type setting");
  $strWordsQuotaSetting = getWords("quota setting");
  $strWordsExtendedQuota = getWords("extended quota");

  $strWordsNO = getWords("no.");
  $strWordsJobGrade = getWords("job grade");
  $strWordsFamilyStatus = getWords("family status");
  $strWordsPercentageGlasses = getWords("percentage for glasses");
  $strWordsType = getWords("type");
  $strWordsPlatformPercentage = getWords("platform percentage (%)");
  $strWordsAmount = getWords("amount");
  $strWordsSave = getWords("save");
  $strDataDetail = "";
  $strColspan = "";
  $strColspanForGlasses = "";
  $strHidden = "";
  $strMsgClass = "";
  $strMessages = "";
  $intTotalData = 0;
  $intTotalDataSecondary= 0;
  $intTotalDataSecondaryAdd= 0;

  $strPaging = "";
  //----------------------------------------------------------------------

//--- DAFTAR FUNSI------------------------------------------------------
  // fungsi untuk menampilkan data
  // $db = kelas database, $intRows = jumlah baris (return)
  // $strKriteria = query kriteria, $strOrder = query ORder by
  function getData($db, &$intRows)
  {
    global $words;
    global $intTotalStatus;
    global $strColspan;
    global $bolCanEdit;
    $strReadonly = ($bolCanEdit) ? "" : "readonly";
    $strResult = "";

    $intRows = 0;
    // cari daftar family status
    // buat header untuk family status tersebut
    $strResult .= " <tr align=center class=tableHeader>";
    $strTmp = "<input type=hidden name='dataStatus$intRows' value=\"Male\">";
    $strResult .= "  <td align=center class=tableHeader nowrap>$strTmp&nbsp;Male</td>\n";
    $strTmp = "<input type=hidden name='dataStatus$intRows' value=\"Male (spouse)\">";
    $strResult .= "  <td align=center class=tableHeader nowrap>$strTmp&nbsp;Male<br>(spouse)</td>\n";
    $strTmp = "<input type=hidden name='dataStatus$intRows' value=\"Female\">";
    $strResult .= "  <td align=center class=tableHeader nowrap>$strTmp&nbsp;Female</td>\n";
    $strTmp = "<input type=hidden name='dataStatus$intRows' value=\"Female (spouse)\">";
    $strResult .= "  <td align=center class=tableHeader nowrap>$strTmp&nbsp;Female<br>(spouse)</td>\n";

    $intTotalStatus = 4;
    $strColspan = "colspan = 4 ";
    $strResult .= " </tr>\n";

    // cari daftar family status
    $strSQL  = "SELECT family_status_code FROM hrd_family_status ORDER BY family_status_code";
    $resDb = $db->execute($strSQL);
    while ($rowDb = $db->fetchrow($resDb)) {
      $arrFamilyStatus[$rowDb['family_status_code']] = $rowDb['family_status_code'];
    }
    // cari data medical platform yang sudah ada, simpan dalam array, biar lebih cepat
    $strSQL  = "SELECT * FROM hrd_medical_platform_primary ";
    $resDb = $db->execute($strSQL);
    while ($rowDb = $db->fetchrow($resDb)) {
      $arrPlatform[$rowDb['family_status_code']][$rowDb['gender']][$rowDb['inspouse']] = $rowDb;
    }



    foreach($arrFamilyStatus as $strFamilyStatusCode)
    {
      // ----- TAMPILKAN DATA ---------------------------------------
      $strResult .= "<tr valign=top id=detailData$intRows>\n";
      $strResult .= "  <td align=\"right\">&nbsp;" .($intRows/4+1). "&nbsp;</td>";
      $strResult .= "  <td nowrap>" .$strFamilyStatusCode. "&nbsp;</td>";



      // cari apakah ada data di appraisal, ambil nilainya
      $fltAmountMale0 = $fltAmountMale1 = $fltAmountFemale0 = $fltAmountFemale1 = 0;
      $intIDMale0 = $intIDMale1 = $intIDFemale0 = $intIDFemale1 = 0;
      //cek male non inspouse
      if (isset($arrPlatform[$strFamilyStatusCode][1]['f'])) {
        $fltAmountMale0 = $arrPlatform[$strFamilyStatusCode][1]['f']['amount'];
        $intIDMale0 = $arrPlatform[$strFamilyStatusCode][1]['f']['id'];
      }
      else
      {
        $fltAmountMale0 = $intIDMale0 = "";
      }
      //cek male inspouse
      if (isset($arrPlatform[$strFamilyStatusCode][1]['t'])) {
        $fltAmountMale1 = $arrPlatform[$strFamilyStatusCode][1]['t']['amount'];
        $intIDMale1 = $arrPlatform[$strFamilyStatusCode][1]['t']['id'];
      }
      else
      {
        $fltAmountMale1 = $intIDMale1 = "";
      }
      //cek female non inspouse
      if (isset($arrPlatform[$strFamilyStatusCode][0]['f'])) {
        $fltAmountFemale0 = $arrPlatform[$strFamilyStatusCode][0]['f']['amount'];
        $intIDFemale0 = $arrPlatform[$strFamilyStatusCode][0]['f']['id'];
      }
      else
      {
        $fltAmountFemale0 = $intIDFemale0 = "";
      }
      //cek female inspouse
      if (isset($arrPlatform[$strFamilyStatusCode][0]['t'])) {
        $fltAmountFemale1 = $arrPlatform[$strFamilyStatusCode][0]['t']['amount'];
        $intIDFemale1 = $arrPlatform[$strFamilyStatusCode][0]['t']['id'];
      }
      else
      {
        $fltAmountFemale1 = $intIDFemale1 = "";
      }

      $strTmpMale0 = "
        <input type=text $strReadonly size=10 maxlength=20 name=detailAmount_".$intRows." value=\"$fltAmountMale0\" class=numeric>
        <input type=hidden name='detailGender_".$intRows."' value='1'>
        <input type=hidden name='detailInspouse_".$intRows."' value='f'>
        <input type=hidden name='detailFamilyStatus_".$intRows."' value='$strFamilyStatusCode'>
        <input type=hidden name='chkID_".$intRows++."' value=$intIDMale0>";
      $strTmpMale1 = "
        <input type=text $strReadonly size=10 maxlength=20 name=detailAmount_".$intRows." value=\"$fltAmountMale1\" class=numeric>
        <input type=hidden name='detailGender_".$intRows."' value='1'>
        <input type=hidden name='detailInspouse_".$intRows."' value='t'>
        <input type=hidden name='detailFamilyStatus_".$intRows."' value='$strFamilyStatusCode'>
        <input type=hidden name='chkID_".$intRows++."' value=$intIDMale1>";
      $strTmpFemale0 = "
        <input type=text $strReadonly size=10 maxlength=20 name=detailAmount_".$intRows." value=\"$fltAmountFemale0\" class=numeric>
        <input type=hidden name='detailGender_".$intRows."' value='0'>
        <input type=hidden name='detailInspouse_".$intRows."' value='f'>
        <input type=hidden name='detailFamilyStatus_".$intRows."' value='$strFamilyStatusCode'>
        <input type=hidden name='chkID_".$intRows++."' value=$intIDFemale0>";
      $strTmpFemale1 = "
        <input type=text $strReadonly size=10 maxlength=20 name=detailAmount_".$intRows." value=\"$fltAmountFemale1\" class=numeric>
        <input type=hidden name='detailGender_".$intRows."' value='0'>
        <input type=hidden name='detailInspouse_".$intRows."' value='t'>
        <input type=hidden name='detailFamilyStatus_".$intRows."' value='$strFamilyStatusCode'>
        <input type=hidden name='chkID_".$intRows++."' value=$intIDFemale1>";
      $strTmp .= "";
      $strResult .= "  <td align=center>$strTmpMale0</td>\n";
      $strResult .= "  <td align=center>$strTmpMale1</td>\n";
      $strResult .= "  <td align=center>$strTmpFemale0</td>\n";
      $strResult .= "  <td align=center>$strTmpFemale1</td>\n";

      $strResult .= "</tr>\n";
    }

    writeLog(ACTIVITY_VIEW, MODULE_PAYROLL," ",0);

    return $strResult;
  } // showData

  //tampilkan data platform medical untuk non rawat jalan
  function getDataSecondary($db, &$intRows,&$intRowsAdd)
  {
    global $ARRAY_MEDICAL_TREATMENT_GROUP;
    global $strWordsJobGrade;
    global $bolCanEdit;
    $strReadonly = ($bolCanEdit) ? "" : "readonly";
    $intRows = $intRowsAdd = $intRowsRole = $i = 0;

    $strResult = "<tr class=tableHeader><td rowspan=2 nowrap>&nbsp;</td><td rowspan=2 nowrap>$strWordsJobGrade</td>\n";
    $strHeader1 = $strHeader2 = "";

    // cari data medical type sebagai acuan untuk header
    $strSQL  = "SELECT id, \"type\", code, value_type,ref_value FROM hrd_medical_type ORDER BY \"type\", code";
    $resDb = $db->execute($strSQL);
    while ($rowDb = $db->fetchrow($resDb)) {
      $arrMedicalType[$rowDb['type']][$rowDb['id']] = array($rowDb['code'],$rowDb['value_type'],$rowDb['ref_value']);
    }
    foreach ($arrMedicalType as $strType => $arrMedicalCode)
    {
      $strValuetype="";
      foreach ($arrMedicalCode as $strID => $strCode){
        if($strCode[1]=="percen")
          $strValuetype="(% * ".$strCode[2].")";
        else
        $strValuetype="(".$strCode[1].")";
      }
      if(count($arrMedicalCode) > 1)
        $strSpan = "colspan=".count($arrMedicalCode);
      else
        $strSpan = "rowspan=2";
      $strHeader1 .= "<td nowrap align=\"center\" $strSpan>".GetWords($ARRAY_MEDICAL_TREATMENT_GROUP[$strType])."&nbsp;<br/>".$strValuetype."</td>";

      foreach ($arrMedicalCode as $strID => $strCode)
      {
        if (count($arrMedicalCode) > 1)
          $strHeader2 .= "<td nowrap align=\"center\">".GetWords($strCode[0])."&nbsp;</td>";

        // cari data medical platform yang sudah ada, simpan dalam array, biar lebih cepat
        $strSQL  = "SELECT * FROM hrd_medical_platform_secondary WHERE id_medical_type = $strID ";
        $resDb = $db->execute($strSQL);
        while ($rowDb = $db->fetchrow($resDb)) {
          $arrPlatform[$rowDb['grade_code']][$strID]['id'] = $rowDb['id'];
          $arrPlatform[$rowDb['grade_code']][$strID]['amount'] = $rowDb['amount'];
        }
      }
    }
    $strResult .= $strHeader1 . "</tr><tr valign=top class=tableHeader>". $strHeader2;
    $strResult .= "</tr>\n";

    // cari data medical platform yang sudah ada, simpan dalam array, biar lebih cepat
    $strSQL  = "SELECT id, grade_code FROM hrd_salary_grade ORDER BY grade_code";
    $resDb = $db->execute($strSQL);
    while ($rowDb = $db->fetchrow($resDb)) {
      $arrGrade[$rowDb['id']] = $rowDb['grade_code'];
    }



    foreach($arrGrade as $strGradeID => $strGradeCode)
    { $i++;
      $strResult .= "<tr valign=top id=detailDataSecondary$intRows>\n";
      $strResult .= "<td align=\"right\">&nbsp;$i&nbsp;</td><td nowrap><input type=hidden name=detailIDSecondary$intRows value=\"" .$strGradeCode. "\">" .$strGradeCode. "&nbsp;</td>";
      foreach ($arrMedicalType as $strType => $arrMedicalCode)
      {
        foreach ($arrMedicalCode as $strID => $strCode)
        {
          $intRows++;
          if (isset($arrPlatform[$strGradeCode][$strID]))
          {
            $fltAmount  = $arrPlatform[$strGradeCode][$strID]['amount'];
            $intID      = $arrPlatform[$strGradeCode][$strID]['id'];
          }
          else
          {
            $fltAmount = 0;
            $intID = "";
          }

          $strTmp = "
          <input type=text $strReadonly size=10 maxlength=20 name=detailAmountSecondary_$intRows value=\"$fltAmount\" class=numeric>
          <input type=hidden name='detailIDMedicalType_$intRows' value=$strID>
          <input type=hidden name='detailGradeCode_$intRows' value=$strGradeCode>
          <input type=hidden name='chkIDSecondary_$intRows' value=$intID>";
          $strResult .= "<td  align=center>$strTmp</td>\n";
        }
      }
        $strResult .= "</tr>\n";

      //uddin
      // additional platform berdasarkan masa kerja
      $strSQL = "SELECT DISTINCT role_type,grade_code,role_value,role_value2 FROM hrd_medical_platform_additional
                  WHERE role_type='masa_kerja' and grade_code='" .$strGradeCode. "' ";
      $resDb = $db->execute($strSQL);
      while ($rowDb = $db->fetchrow($resDb)) {
        $strResult .= "<tr valign=top id=detailDataSecondary$intRows>\n";
        $strResult .= "<td align=\"right\">&nbsp;&nbsp;</td><td nowrap>
        <input type=hidden name=detailIDSecondaryAdd$intRowsAdd value=\"" .$strGradeCode. "\">" .$strGradeCode. "&nbsp;(#Of Service:".$rowDb["role_value"]." - ".$rowDb["role_value2"]." )</td>";
        foreach ($arrMedicalType as $strType => $arrMedicalCode)
        {
          foreach ($arrMedicalCode as $strID => $strCode)
          {
            $strSQL = "SELECT id,role_value,amount,id_medical_type,grade_code FROM hrd_medical_platform_additional
            WHERE role_type='masa_kerja' and role_value=".$rowDb['role_value']." and grade_code='" .$strGradeCode. "' and id_medical_type=".$strID;
            $resDb2 = $db->execute($strSQL);

            $fltAmount = 0;
            $intID = "";
            while ($rowDb2 = $db->fetchrow($resDb2)) {
              $fltAmount  = $rowDb2['amount'];
              $intID      = $rowDb2['id'];
            }
            $intRowsAdd++;

            $strTmp = "
            +<input type=text $strReadonly size=8 maxlength=20 name=detailAmountSecondaryAdd_$intRowsAdd value=\"$fltAmount\" class=numeric>
            <input type=hidden name='detailIDMedicalTypeAdd_$intRowsAdd' value=$strID>
            <input type=hidden name='detailGradeCodeAdd_$intRowsAdd' value=$strGradeCode>
            <input type=hidden name='chkIDSecondaryAdd_$intRowsAdd' value=$intID>
            <input type=hidden name='detailRoleTypeAdd_$intRowsAdd' value=".$rowDb["role_type"].">
            <input type=hidden name='detailRoleValueAdd_$intRowsAdd' value=".$rowDb["role_value"].">
            <input type=hidden name='detailRoleValue2Add_$intRowsAdd' value=".$rowDb["role_value2"].">";
            $strResult .= "<td  align=center>$strTmp</td>\n";
          }
        }
        $strResult .= "</tr>\n";
      }
      //end
      //uddin
      // additional role pengambilan jatah outpation untuk keluarga berdasarkan grade
      $strSQL = "SELECT DISTINCT role_type,grade_code,role_value,role_value2 FROM hrd_medical_platform_additional
      WHERE role_type='l_fam_limit_out' and grade_code='" .$strGradeCode. "' ";
      $resDb = $db->execute($strSQL);
      while ($rowDb = $db->fetchrow($resDb)) {
        $strResult .= "<tr valign=top id=detailDataSecondary$intRows>\n";
        $strResult .= "<td align=\"right\">&nbsp;&nbsp;</td><td nowrap>
        <input type=hidden name=detailIDSecondaryAdd$intRowsAdd value=\"" .$strGradeCode. "\">" .$strGradeCode. "&nbsp;(Family Approval Limit )</td>";
        foreach ($arrMedicalType as $strType => $arrMedicalCode)
        {
          foreach ($arrMedicalCode as $strID => $strCode)
          {
            $strSQL = "SELECT id,role_value,amount,id_medical_type,grade_code FROM hrd_medical_platform_additional
            WHERE role_type='l_fam_limit_out' and grade_code='" .$strGradeCode. "' and id_medical_type=".$strID;
            $resDb2 = $db->execute($strSQL);

            $fltAmount = "";
            $intID = "";
            // hanya muncul yg ada nilainya
            while ($rowDb2 = $db->fetchrow($resDb2)) {
              $fltAmount  = $rowDb2['amount'];
              $intID      = $rowDb2['id'];

              $intRowsAdd++;

              $strTmp = "
              %<input type=text $strReadonly size=5 maxlength=10 name=detailAmountSecondaryAdd_$intRowsAdd value=\"$fltAmount\" class=numeric>
              <input type=hidden name='detailIDMedicalTypeAdd_$intRowsAdd' value=$strID>
              <input type=hidden name='detailGradeCodeAdd_$intRowsAdd' value=$strGradeCode>
              <input type=hidden name='chkIDSecondaryAdd_$intRowsAdd' value=$intID>
              <input type=hidden name='detailRoleTypeAdd_$intRowsAdd' value=".$rowDb["role_type"].">
              <input type=hidden name='detailRoleValueAdd_$intRowsAdd' value=".$rowDb["role_value"].">
              <input type=hidden name='detailRoleValue2Add_$intRowsAdd' value=".$rowDb["role_value2"].">";
              $strResult .= "<td  align=center>$strTmp</td>\n";
            }
          }
        }
        $strResult .= "</tr>\n";
      }
      //end
    }

    writeLog(ACTIVITY_VIEW, MODULE_PAYROLL," ",0);

    return $strResult;
  } // showData

  // fungsi untuk menyimpan data, yang disimpan adalah data tidak ikut catering
  function saveData($db, &$strError)
  {
    global $_REQUEST;
    global $_SESSION;

    $strUpdaterID = $_SESSION['sessionUserID'];

    $bolOK = true;


    $intTotalData = (isset($_REQUEST['totalData'])) ? $_REQUEST['totalData'] : 0;
    if (!is_numeric($intTotalData)) { $intTotalData = 0; };
    for ($i = 0; $i < $intTotalData; $i++) {

      $strIDData            = (isset($_REQUEST['chkID_'.$i])) ? $_REQUEST['chkID_'.$i] : "";
      $fltAmount            = (isset($_REQUEST['detailAmount_'.$i]) && $_REQUEST['detailAmount_'.$i] != "") ? $_REQUEST['detailAmount_'.$i] : 0;
      $strFamilyStatus      = $_REQUEST['detailFamilyStatus_'.$i];
      $strGender            = $_REQUEST['detailGender_'.$i];
      $strInspouse          = $_REQUEST['detailInspouse_'.$i];


      if (!is_numeric($fltAmount))
      {
        $strError = "Some data are not valid. Please make sure that all platform entry are numeric";
        $bolOK = false;
        continue;
      }

      if ($strIDData == "") {
        $strSQL  = "INSERT INTO hrd_medical_platform_primary (created, modified_by, created_by, ";
        $strSQL .= "family_status_code, gender, inspouse, amount) ";
        $strSQL .= "VALUES(now(), '$strUpdaterID', '$strUpdaterID', ";
        $strSQL .= "'$strFamilyStatus', '$strGender', '$strInspouse', '$fltAmount') ";
        $resExec = $db->execute($strSQL);
      } else {
        $strSQL  = "UPDATE hrd_medical_platform_primary SET modified_by = '$strUpdaterID', ";
        $strSQL .= "amount = '$fltAmount' ";
        $strSQL .= "WHERE id = '$strIDData' ";
        $resExec = $db->execute($strSQL);

      }
    }

    //Save medical platform data of non outpatient medical treatment
    $intTotalDataSecondary = (isset($_REQUEST['totalDataSecondary'])) ? $_REQUEST['totalDataSecondary'] : 0;
    if (!is_numeric($intTotalDataSecondary)) { $intTotalDataSecondary = 0; };
    for ($i = 1; $i <= $intTotalDataSecondary; $i++)
    {

      $strIDData            = (isset($_REQUEST['chkIDSecondary_'.$i])) ? $_REQUEST['chkIDSecondary_'.$i] : "";
      $fltAmount            = (isset($_REQUEST['detailAmountSecondary_'.$i]) && $_REQUEST['detailAmountSecondary_'.$i] != "") ? $_REQUEST['detailAmountSecondary_'.$i] : 0;
      $strIDMedicalType     = $_REQUEST['detailIDMedicalType_'.$i];
      $strGradeCode         = $_REQUEST['detailGradeCode_'.$i];

      if (!is_numeric($fltAmount))
      {
        $strError = "Some data are not valid. Please make sure that all platform entry are numeric";
        $bolOK = false;
        continue;
      }
      // cari dulu
      if ($strIDData == "") {
        $strSQL  = "INSERT INTO hrd_medical_platform_secondary (created, modified_by, created_by, ";
        $strSQL .= "grade_code, id_medical_type, amount) ";
        $strSQL .= "VALUES(now(), '$strUpdaterID', '$strUpdaterID', ";
        $strSQL .= "'$strGradeCode', '$strIDMedicalType', '$fltAmount') ";
        $resExec = $db->execute($strSQL);
      } else {
        $strSQL  = "UPDATE hrd_medical_platform_secondary SET modified_by = '$strUpdaterID', ";
        $strSQL .= "amount = '$fltAmount' ";
        $strSQL .= "WHERE id = '$strIDData' ";
        $resExec = $db->execute($strSQL);
      }

    }

    //uddin p: takenaka
    //Save medical additional platform data of non outpatient medical treatment
    $intTotalDataSecondaryAdd = (isset($_REQUEST['totalDataSecondaryAdd'])) ? $_REQUEST['totalDataSecondaryAdd'] : 0;
    if (!is_numeric($intTotalDataSecondaryAdd)) { $intTotalDataSecondaryAdd = 0; };
    for ($i = 1; $i <= $intTotalDataSecondaryAdd; $i++)
    {

      $strIDData            = (isset($_REQUEST['chkIDSecondaryAdd_'.$i])) ? $_REQUEST['chkIDSecondaryAdd_'.$i] : "";
      $fltAmount            = (isset($_REQUEST['detailAmountSecondaryAdd_'.$i]) && $_REQUEST['detailAmountSecondaryAdd_'.$i] != "") ? $_REQUEST['detailAmountSecondaryAdd_'.$i] : 0;
      $strIDMedicalType     = $_REQUEST['detailIDMedicalTypeAdd_'.$i];
      $strGradeCode         = $_REQUEST['detailGradeCodeAdd_'.$i];
      $strRoleType         = $_REQUEST['detailRoleTypeAdd_'.$i];
      $strRoleValue         = $_REQUEST['detailRoleValueAdd_'.$i];
      $strRoleValue2         = $_REQUEST['detailRoleValue2Add_'.$i];

      if (!is_numeric($fltAmount))
      {
        $strError = "Some data are not valid. Please make sure that all platform entry are numeric";
        $bolOK = false;
        continue;
      }
      // cari dulu
      if ($strIDData == "") {
        $strSQL  = "INSERT INTO hrd_medical_platform_additional (created, modified_by, created_by, ";
        $strSQL .= "grade_code, id_medical_type, amount, role_type, role_value, role_value2) ";
        $strSQL .= "VALUES(now(), '$strUpdaterID', '$strUpdaterID', ";
        $strSQL .= "'$strGradeCode', '$strIDMedicalType', '$fltAmount','$strRoleType',$strRoleValue,$strRoleValue2) ";
        $resExec = $db->execute($strSQL);
      } else {
        $strSQL  = "UPDATE hrd_medical_platform_additional SET modified_by = '$strUpdaterID', ";
        $strSQL .= "amount = '$fltAmount' ";
        $strSQL .= "WHERE id = '$strIDData' ";
        $resExec = $db->execute($strSQL);
      }

    }

    writeLog(ACTIVITY_EDIT, MODULE_PAYROLL," ",0);
    return $bolOK;
  }

  //----------------------------------------------------------------------

  //----MAIN PROGRAM -----------------------------------------------------
  $strInfo = "";

  $db = new CdbClass;
  if ($db->connect())
  {

    // ------ AMBIL DATA KRITERIA -------------------------
    $dtNow = getdate();
    //getDefaultSalaryPeriode($strDefaultStart,$strDefaultFinish);
    $strDefaultStart = $dtNow['year']."-11-16";
    $strDefaultFinish = ($dtNow['year'] - 1)."-11-15";

    $strDataDateFrom = (isset($_REQUEST['dataDateFrom'])) ? $_REQUEST['dataDateFrom'] :  $strDefaultStart;
    $strDataDateThru = (isset($_REQUEST['dataDateThru'])) ? $_REQUEST['dataDateThru'] : $strDefaultFinish;
    $strDataAppraiser = (isset($_REQUEST['dataAppraiser'])) ? $_REQUEST['dataAppraiser'] : "";

    // simpan data jika ada perintah
    if (isset($_REQUEST['btnSave'])) {
      if ($bolCanEdit) {
        $bolOK = saveData($db, $strError);
        $strMessages = (isset($strError)) ? $strError : "Data Saved" ;
        $strMsgClass = ($bolOK) ? "class = bgOK" : "class = bgError";
      }
    }

    // ------------ GENERATE KRITERIA QUERY,JIKA ADA -------------
    $strKriteria = "";
    if ($strDataAppraiser != "") {
      $strKriteria .= "AND id_employee = '$strDataAppraiser' ";
    }

    if ($bolCanView) {
      $strDataDetail    = getData($db, $intTotalData ); // data rawat jalan
      $strDataSecondary = getDataSecondary($db, $intTotalDataSecondary,$intTotalDataSecondaryAdd);

    } else {
      showError("view_denied");
    }
    if ($bolCanEdit)
      $strButton = "<input type=\"submit\" name=\"btnSave\" value=\"". $strWordsSave ."\"> ";
    else
      $strButton = "";

  }


  $tbsPage = new clsTinyButStrong ;

  //write this variable in every page
  $strPageTitle = $dataPrivilege['menu_name'];
  $pageIcon = "../images/icons/".$dataPrivilege['icon_file'];
  $strTemplateFile = getTemplate(str_replace(".php", ".html", basename($_SERVER['PHP_SELF'])));
  //------------------------------------------------
  //Load Master Template
  $tbsPage->LoadTemplate($strMainTemplate) ;
  $tbsPage->Show() ;
?>
