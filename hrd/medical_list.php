<?
  include_once('../global/session.php');
  include_once('global.php');
  include_once('form_object.php');


  $dataPrivilege = getDataPrivileges(basename($_SERVER['PHP_SELF']), $bolCanView, $bolCanEdit, $bolCanDelete, $bolCanApprove, $bolCanCheck, $bolCanAcknowledge, $bolCanApprove2);
  if (!$bolCanView) die(accessDenied($_SERVER['HTTP_REFERER']));

  $bolPrint = (isset($_REQUEST['btnPrint']) || isset($_REQUEST['btnPrintApproved']) || isset($_REQUEST['btnExcel']));
  $strUserName = $_SESSION['sessionUserName'] ;

 
  //---- INISIALISASI ----------------------------------------------------
  $strWordsMedicalData = getWords("medical data");
  $strWordsEmployeeQuotaList  = getWords("employee quota list");
  $strWordsInputMedicalClaim = getWords("input claim");
  $strWordsMedicalClaimList = getWords("claim list");
  $strWordsEmployeeMedicalReport = getWords("employee medical report");
  $strWordsDateFrom = getWords("date from");
  $strWordsDateThru = getWords("date thru");
  $strWordsEmployeeID = getWords("employee id");
  $strWordsShowData = getWords("show data");
  $strWordsPrint = getWords("print");
  $strWordsPrintAll = getWords("print all");
  $strWordsPrintApproved = getWords("print approved");
  $strWordsBranch = getWords("branch");
  $strWordsBank = getWords("bank");
  $strWordsDivision = getWords("division");
  $strWordsDepartment = getWords("department");
  $strWordsSection = getWords("section");
  $strWordsSubSection = getWords("sub section");
  $strWordsCompany = getWords("company");
  $strWordsEmployeeStatus = getWords("employee status");
  $strWordsRequestStatus = getWords("request status");
  $strWordsLISTOFEMPLOYEEMEDICALCLAIM = getWords("list of employee medical claim");
  $strWordsEMPLID = getWords("empl.id");
  $strWordsNAME = getWords("name");
  $strWordsGENDER = getWords("sex");
  $strWordsPOSITION = getWords("level");
  $strWordsDEPT = getWords("dept.");
  $strWordsFORMNO = getWords("form no.");
  $strWordsNAME = getWords("name");
  $strWordsRELATION = getWords("relation");
  $strWordsTreatmentType = getWords("treatment type");
  $strWordsTYPE = getWords("type");
  $strWordsCODE = getWords("code");
  $strWordsTreatmentCode = getWords("treatment code");
  $strWordsTREATMENTDISEASE = getWords("treatment/disease");
  $strWordsMedicine = getWords("medicine");
  $strWordsMEDDATE = getWords("med. date");
  $strWordsMEDDATETHRU = getWords("med. date thru");
  $strWordsCLAIMDATE = getWords("claim date");
  $strWordsCOST = getWords("cost");
  $strWordsAPVCOST = getWords("apv. cost");
  $strWordsSTATUS = getWords("status");
  $strWordsPAYMENTREQUEST = getWords("payment request");
  $strWordsRECORDINFO = getWords("record info");
  $strWordsAll = getWords("all");
$strWordsNew      = getWords("new");
  $strWordsDenied   = getWords("denied");
  $strWordsChecked  = getWords("checked");
  $strWordsApproved = getWords("approved");
  $strWordsFinished = getWords("finished");
  $strDataDetail = "";
  $strHidden = "";
  $intTotalData = 0;
  $strButtonList = "";
  $strStyle = "";
  $int = 0;
  //----------------------------------------------------------------------

  //--- DAFTAR FUNSI------------------------------------------------------
  // fungsi untuk menampilkan data
  // $db = kelas database, $intRows = jumlah baris (return)
  // $strKriteria = query kriteria, $strOrder = query ORder by
  function getData($db, $strDataDateFrom, $strDataDateThru, &$intRows, $strKriteria = "", $strOrder = "")
  {
    global $words;
    global $ARRAY_EMPLOYEE_STATUS;
    global $ARRAY_REQUEST_STATUS;
    global $ARRAY_PAYMENT_METHOD;
    global $ARRAY_FAMILY_RELATION;
    global $ARRAY_MEDICAL_TREATMENT_GROUP;
    global $_REQUEST;
    global $_SESSION;
    global $bolPrint;
    global $bolCanEdit;
    global $arrUserInfo;
    global $strDataTreatmentType;
    global $strDataTreatmentCode;
    $strFormatter = (isset($_REQUEST['btnExcel'])) ? "floatval" : "standardFormat";

    $intRows = 0;
    $strResult = "";
    $cExec = new CexecutionTime();
    $fltGrandTotalCost = 0;
    $fltGrandTotalApprovedCost = 0;
    
    // ambil dulu data employee, kumpulkan dalam array
    //$arrEmployee = array();
    $i = 0;
    if (!isset($_REQUEST['btnShowAlert']))
        $strKriteria .= "AND t4.claim_date BETWEEN '$strDataDateFrom' AND '$strDataDateThru' ";    
    $strSQL  = "SELECT DISTINCT(t1.id) AS id, t1.*, t4.claim_date, t2.employee_id, t2.employee_name, t2.position_code, t2.branch_code, t3.branch_name, 
                case when t2.gender = 0 then 'female' else 'male' end as gender, t2.division_code, t2.sub_section_code,t2.department_code, t2.section_code, t2.grade_code 
                FROM hrd_medical_claim_master AS t1 
                LEFT JOIN hrd_medical_claim AS t4 ON t4.id_master = t1.id              
                LEFT JOIN hrd_employee AS t2 ON t1.id_employee = t2.id 
                LEFT JOIN hrd_branch AS t3 ON t2.branch_code = t3.branch_code
                WHERE 1=1 ";

    $strSQL .= $strKriteria;
    $strSQL .= "ORDER BY t4.claim_date, $strOrder t1.year_code DESC, t2.division_code,  t2.employee_name ";
    $resDb = $db->execute($strSQL);
    $strDateOld = "";
    while ($rowDb = $db->fetchrow($resDb)) {

      $intTotal = 0;
      $fltTotalCost = 0;
      $fltTotalCostApproved = 0;
      $strDetail = "";

      $bolDenied = false;

      switch ($rowDb['status']) {
        case 0 :
          $strClass = "class=bgNewData";
          break;
        case 1 :
          $strClass = "class=bgVerifiedData";
          break;
        case 2 :
          $strClass = "class=bgCheckedData";
          break;
        case -1 :
          $strClass = "class=bgDenied";
          $bolDenied = true;
          break;
        case 5 :
          $strClass = "class=bgNewRevised";
          break;
        default :
          $strClass = "";
          break;
      }


      // cari data detail claim
      $strSQL  = "SELECT * FROM hrd_medical_claim WHERE id_master = '" .$rowDb['id']."' ";
      if ($strDataTreatmentType != "")
        $strSQL .= "AND type = '$strDataTreatmentType' ";
      if ($strDataTreatmentCode != "")
        $strSQL .= "AND medical_code = '$strDataTreatmentCode' ";
      if (!isset($_REQUEST['btnShowAlert'])) {
        $strSQL .= "AND claim_date BETWEEN '$strDataDateFrom' AND '$strDataDateThru' ";
      }
      $strSQL .= " ORDER BY claim_date";
      $resTmp = $db->execute($strSQL);
      while ($rowTmp = $db->fetchrow($resTmp)) {
        $intTotal++;

        $strRelation = ($rowTmp['relation'] >= 0) ? $words[$ARRAY_FAMILY_RELATION[$rowTmp['relation']]] : "";
        //$strRoom = ($rowTmp['room'] == "t") ? "&radic;" : "";
        //$strRelation = $words[$ARRAY_FAMILY_RELATION[$rowTmp['relation']]];
        if ($intTotal == 1) {
          // tambahkan di baris sejajar dengan nama
          $strResult .= "<tr valign=top title=\"".$rowDb['employee_id'] ." - ".$rowDb['employee_name']."\" $strClass>\n";
          //if (!$bolPrint && $rowDb['id_employee'] != $arrUserInfo['id_employee']) {
		  if (!$bolPrint) {
            $intRows++;
            $int = 6;
            $strResult .= "  <td><input type=checkbox name='chkID$intRows' value=\"" .$rowDb['id']. "\"></td>\n";
          } else {
            //$strResult .= "  <td>&nbsp;</td>\n";
            $int = 5;
          }
          
          $strResult .= "  <td nowrap>" .$rowDb['employee_id']. "&nbsp;</td>";
          $strResult .= "  <td nowrap nowrap>" .$rowDb['employee_name']. "&nbsp;</td>";
          $strResult .= "  <td align=center>" .getWords($rowDb['gender']). "&nbsp;</td>";
          $strResult .= "  <td align=center>" .getDepartmentName($rowDb['department_code']). "&nbsp;</td>";
          $strResult .= "  <td align=center nowrap>" .$rowDb['branch_code']. " - " .$rowDb['branch_name']. "&nbsp;</td>";

          $strResult .= "  <td nowrap>&nbsp;" .$rowTmp['name']."</td>\n";
          $strResult .= "  <td align=center>&nbsp;" .$strRelation."</td>\n";
          $strResult .= "  <td>&nbsp;" .getWords($ARRAY_MEDICAL_TREATMENT_GROUP[$rowTmp['type']])."</td>\n";
          $strResult .= "  <td>&nbsp;" .$rowTmp['medical_code']."</td>\n";
          $strResult .= "  <td>&nbsp;" .$rowTmp['disease']."</td>\n";
          $strResult .= "  <td>&nbsp;" .$rowTmp['medicine']."</td>\n";
          $strResult .= "  <td align=center>&nbsp;" .pgDateFormat($rowTmp['medical_date'],"d-M-y")."</td>\n";
          $strResult .= "  <td align=center>&nbsp;" .pgDateFormat($rowTmp['medical_date_thru'],"d-M-y")."</td>\n";
          $strResult .= "  <td align=center>&nbsp;" .pgDateFormat($rowTmp['claim_date'],"d-M-y")."</td>\n";
          $strResult .= "  <td align=right>" .$strFormatter($rowTmp['cost'])."</td>\n";
          $strResult .= "  <td align=right>" .$strFormatter($rowTmp['approved_cost'])."</td>\n";
        } else {       

          // tambahkan di bawah
          $strDetail .= " <tr valign=top $strClass>";
		  //if (!$bolPrint){
          $strDetail .= "  <td colspan=$int>&nbsp;</td>\n";
		  //}else{$strDetail .= "  <td colspan=($int-1)>&nbsp;</td>\n";}
		  
          $strDetail .= "  <td>&nbsp;" .$rowTmp['name']."</td>\n";
          $strDetail .= "  <td align=center>&nbsp;" .$strRelation."</td>\n";
          $strDetail .= "  <td>&nbsp;" .getWords($ARRAY_MEDICAL_TREATMENT_GROUP[$rowTmp['type']])."</td>\n";
          $strDetail .= "  <td>&nbsp;" .$rowTmp['medical_code']."</td>\n";
          $strDetail .= "  <td>&nbsp;" .$rowTmp['disease']."</td>\n";
          $strDetail .= "  <td>&nbsp;" .$rowTmp['medicine']."</td>\n";
          $strDetail .= "  <td align=center>&nbsp;" .pgDateFormat($rowTmp['medical_date'],"d-M-y")."</td>\n";
          $strDetail .= "  <td align=center>&nbsp;" .pgDateFormat($rowTmp['medical_date_thru'],"d-M-y")."</td>\n";
          $strDetail .= "  <td align=center>&nbsp;" .pgDateFormat($rowTmp['claim_date'],"d-M-y")."</td>\n";
          $strDetail .= "  <td align=right>" .$strFormatter($rowTmp['cost'])."</td>\n";
          $strDetail .= "  <td align=right>" .$strFormatter($rowTmp['approved_cost'])."</td>\n";
          
          if (!$bolPrint)
          {
            $strDetail .= "  <td>&nbsp;</td>";
            $strDetail .= "  <td>&nbsp;</td>";
            $strDetail .= "  <td>&nbsp;</td>";
          }
          else
          $strDetail .= "  <td>&nbsp;</td>";
          $strDetail .= "</tr>";
        }

        $fltTotalCost += $rowTmp['cost'];
        $fltTotalCostApproved += $rowTmp['approved_cost'];
      }
      $fltGrandTotalCost += $fltTotalCost;
      $fltGrandTotalApprovedCost += $fltTotalCostApproved ;

      if ($intTotal == 0  && $strDataTreatmentType == "" && $strDataTreatmentCode == "") {
          // kosongkan data
        $strResult .= "  <td>&nbsp;</td>";
        $strResult .= "  <td>&nbsp;</td>";
        $strResult .= "  <td>&nbsp;</td>";
        $strResult .= "  <td>&nbsp;</td>";
        $strResult .= "  <td>&nbsp;</td>";
        $strResult .= "  <td>&nbsp;</td>";
        $strResult .= "  <td>&nbsp;</td>";
        $strResult .= "  <td>&nbsp;</td>";
        $strResult .= "  <td>&nbsp;</td>";
        $strResult .= "  <td>&nbsp;</td>";
        $strResult .= "  <td>&nbsp;</td>";
        $strResult .= "  <td>&nbsp;</td>";
        $strResult .= "  <td>&nbsp;</td>";
        $strResult .= "  <td>&nbsp;</td>";
        $strResult .= "  <td>&nbsp;</td>";
        $strResult .= "  <td>&nbsp;</td>";
        $strResult .= "  <td>&nbsp;</td>";
      }

      //$strPaymentStatus = "";
      if(!(($strDataTreatmentType != "" || $strDataTreatmentCode != "") && $intTotal == 0))
      {

        $strResult .= "  <td align=center>".$words[$ARRAY_REQUEST_STATUS[$rowDb['status']]]. "&nbsp;</td>";
        //$strResult .= "  <td align=center>" .$strPaymentStatus. "&nbsp;</td>";
        if (!$bolPrint && $bolCanEdit && $rowDb['status'] != REQUEST_STATUS_DENIED && $rowDb['status'] < REQUEST_STATUS_APPROVED) {
          $strResult .= "  <td align=center><a href=\"medical_edit.php?dataID=" .$rowDb['id']. "\">" .$words['edit']. "</a>&nbsp;</td>";

          // tambahkan info record info

          $strDiv  = "<div id='detailRecord$intRows' style=\"display:none\">\n";
          $strDiv .= "<strong>" .$rowDb['employee_id']."-".$rowDb['employee_name']."</strong><br>\n";
          $strDiv .= getWords("last updated"). ": ".substr($rowDb['modified'], 0,19) ." ";
          $strDiv .= (isset($arrUserList[$rowDb['modified_by']])) ? $arrUserList[$rowDb['modified_by']]['name']."<br>" : "<br>";

          $strDiv .= getWords("verified"). ": ".substr($rowDb['verified_time'], 0,19) ." ";
          $strDiv .= (isset($arrUserList[$rowDb['verified_by']])) ? $arrUserList[$rowDb['verified_by']]['name']."<br>" : "<br>";

          $strDiv .= getWords("checked"). ": ".substr($rowDb['checked_time'], 0,19) ." ";
          $strDiv .= (isset($arrUserList[$rowDb['checked_by']])) ? $arrUserList[$rowDb['checked_by']]['name']."<br>" : "<br>";

          $strDiv .= getWords("approved"). ": ".substr($rowDb['approved_time'], 0,19) ." ";
          $strDiv .= (isset($arrUserList[$rowDb['approved_by']])) ? $arrUserList[$rowDb['approved_by']]['name']."<br>" : "<br>";

          $strDiv .= getWords("paid"). ": ".substr($rowDb['paid_time'], 0,19) ." ";
          $strDiv .= (isset($arrUserList[$rowDb['paid_by']])) ? $arrUserList[$rowDb['paid_by']]['name']."<br>" : "<br>";

          $strDiv .= getWords("denied"). ": ".substr($rowDb['denied_time'], 0,19) ." ";
          $strDiv .= (isset($arrUserList[$rowDb['denied_by']])) ? $arrUserList[$rowDb['denied_by']]['name']."<br>" : "<br>";

          $strDiv .= "</div>\n"; 

          //$strResult .= "  <td nowrap align=center>/*<a href=\"javascript:openWindowById('detailRecord$intRows')\" title=\"" .getWords("show record info")."\">" .getWords("show")."$strDiv</a></td>\n";
          $strResult .= "  <td nowrap align=center>&nbsp;</td>\n";

        }

        $strResult .= "</tr>\n";          

        if ($intTotal > 0) {
          $strResult .= $strDetail;

          // tambahkan total di sini


        }
      }
    }
    $strColspan = (!$bolPrint) ? "colspan=5" : "";
    if(($strDataTreatmentType != "" || $strDataTreatmentCode != "") && $intRows > 0)
      $strResult .= "  <tr><td colspan=15 align=right><strong>Total : </strong></td><td align=right><strong>".$strFormatter($fltGrandTotalCost)."&nbsp;</strong></td><td align=right><strong>".$strFormatter($fltGrandTotalCost)."&nbsp;</strong></td><td $strColspan>&nbsp;</td></tr></strong>";
    

    $strDur = $cExec->getDuration();
    if ($intRows > 0) {
      writeLog(ACTIVITY_VIEW, MODULE_PAYROLL,"$intRows data in $strDur",0);
    }

    return $strResult;
  } // showData


  // fungsi untuk menghapus data
  function deleteData($db)
  {
    global $_REQUEST;
    global $_SESSION;
    global $arrUserInfo;

    $i = 0;

    foreach ($_REQUEST as $strIndex => $strValue) {
      if (substr($strIndex,0,5) == 'chkID') {
        $i++;

        $strSQL  = "DELETE FROM hrd_medical_claim WHERE id_master IN ";
        $strSQL .= "(SELECT id FROM hrd_medical_claim_master WHERE id = '$strValue') ";
        $resExec = $db->execute($strSQL);
        $strSQL  = "DELETE FROM hrd_medical_claim_master WHERE id = '$strValue'";
        $resExec = $db->execute($strSQL);

        }
    }
    if ($i > 0) {
      writeLog(ACTIVITY_DELETE, MODULE_PAYROLL,"$i data",0);
    }

  } //deleteData

  // fungsi untuk mencetak slip klaim medis
  function getSlip($db)
  {
     global $_REQUEST;
     global $ARRAY_FAMILY_RELATION;

     $i = 0;
     foreach ($_REQUEST as $strIndex => $strValue) 
     {
        if (substr($strIndex,0,5) == 'chkID') 
        {
           $i++;
           $arrValue[] = $strValue;
        }
     }
     
     if ($i == 0) return False;
          
     $strSQL  = "SELECT position_code, position_name FROM hrd_position";
     $resDb = $db->execute($strSQL);
     while ($rowDb = $db->fetchrow($resDb))
     {
        $arrPosition[$rowDb['position_code']] = $rowDb['position_name'];
     }
     $strSQL  = "SELECT department_code, department_name FROM hrd_department";
     $resDb = $db->execute($strSQL);
     while ($rowDb = $db->fetchrow($resDb))
     {
        $arrDepartment[$rowDb['department_code']] = $rowDb['department_name'];
     }

     $strValue = join(",", $arrValue);

     $strSQL  = "SELECT t1.id, t1.id_employee, t1.no, t1.created,  t2.employee_id, t2.employee_name, 
                 t2.department_code, t2.position_code, t2.grade_code
                 FROM hrd_medical_claim_master AS t1
                 LEFT JOIN hrd_employee AS t2 ON t1.id_employee = t2.id
                 WHERE t1.id IN ($strValue) ORDER BY t1.no";
     $resDb = $db->execute($strSQL);
     while ($rowDb = $db->fetchrow($resDb))
     {
        $arrAllData[$rowDb['id']]         = $rowDb;
        $arrAllData[$rowDb['id']]['cost'] = 0;
        $arrAllData[$rowDb['id']]['family'] = "";
        $arrAllData[$rowDb['id']]['department_name'] = (isset ($arrDepartment[$rowDb['department_code']])) ? $arrDepartment[$rowDb['department_code']] : $rowDb['department_code'];
        $arrAllData[$rowDb['id']]['position_name'] = (isset ($arrPosition[$rowDb['position_code']])) ? $arrPosition[$rowDb['position_code']] : $rowDb['position_code'];
        $arrEmployee[] = $rowDb['id_employee'];
     }

     $strSQL  = "SELECT id_master, name, relation, cost FROM hrd_medical_claim
                 WHERE id_master IN ($strValue) ORDER BY relation";
     $resDb = $db->execute($strSQL);
     $intCost = 0;
     while ($rowDb = $db->fetchrow($resDb))
     {
        $arrAllData[$rowDb['id_master']]['claims']['name'][] = ucwords(strtolower($rowDb['name']));
        $arrAllData[$rowDb['id_master']]['claims']['relation'][] = ($rowDb['relation'] == -1) ? "Ybs" : getwords(strtolower($ARRAY_FAMILY_RELATION[$rowDb['relation']]));
        $arrAllData[$rowDb['id_master']]['cost']     += $rowDb['cost'];

     }

     if(!empty($arrEmployee))
     {
        $strSQL  = "SELECT t1.id_employee, t1.name, t1.relation, t1.birthday, t2.\"id\" as id_claim,
                    EXTRACT (YEAR FROM AGE(t1.birthday)) AS age_year FROM hrd_employee_family AS t1
                    LEFT JOIN hrd_medical_claim_master AS t2 ON t1.id_employee = t2.id_employee
                    WHERE t1.id_employee IN (". join(",", $arrEmployee) .") 
                    AND relation IN (2,3,4) ORDER BY birthday";
        $resDb = $db->execute($strSQL);
        while ($rowDb = $db->fetchrow($resDb))
        {
           $arrFamily[$rowDb['id_employee']][] = $rowDb;
           $arrAllData[$rowDb['id_claim']]['family'] .= 
                   "<tr><td>&nbsp;&nbsp;".ucwords(strtolower($rowDb['name']))."</td>
                    <td align=\"center\" nowrap>&nbsp;".$rowDb['birthday']."</td>
                    <td align=\"center\" nowrap>&nbsp;".$rowDb['age_year']."</td>
                    <td>&nbsp;".getWords(strtolower($ARRAY_FAMILY_RELATION[$rowDb['relation']]))."</td></tr>";
        }
     }

     if(!empty($arrAllData))
     {
        global $arrData;
        global $arrNIK;
        global $strDate;
        global $strFinanceSpv;
        global $strHrdSpv;
        $strDate = date("d / m / Y");
        $strFinanceSpv = $_REQUEST['spvFin'];
        $strHrdSpv = $_REQUEST['spvHrd'];
        $i = 0;

        echo "<html><head>
              <title>Medical Claim Slip</title>
              <meta http-equiv=\"Content-Type\" content=\"text/html; charset=iso-8859-1\">
              <style>
              .fonts
              {
                 font-family     : arial;
                 font-size       : 9pt;
              }
              .gridTable
              {
                 border-left     : 1px solid;
                 border-top      : 1px solid;
              }
              .gridTable  td
              {
                 border-right      : 1px solid;
                 border-bottom      : 1px solid;
                 font-size   : 11px;
              }
              </style>
              </head>
              <body  onLoad=\"window.print();\" marginheight=\"0px\" marginwidth=\"0px\" leftmargin=\"0px\" topmargin=\"0px\"><table width=\"100%\" border=0 >";

        foreach($arrAllData AS $strIDMaster => $arrData)
        {  
           $i++;
           $arrData['no'] = intval($arrData['no']);
           $arrData['claim_name'] = join(", ", $arrData['claims']['name']);
           $arrData['relation'] = join(", ", $arrData['claims']['relation']);
           $arrNIK = array(substr($arrData['employee_id'],0,1), substr($arrData['employee_id'],1,2), substr($arrData['employee_id'],3,2), substr($arrData['employee_id'],5,3));
           $tbsPage = new clsTinyButStrong ;
           $tbsPage->LoadTemplate(getTemplate("medical_claim_slip.html"));
           $tbsPage->Show(TBS_OUTPUT) ;
           if ($i % 2 == 0)
              echo "<br style='page-break-before:always'>";
        }
        
        echo "</table></html>";
        return true;
        writeLog(ACTIVITY_PRINT, MODULE_PAYROLL,"$i data",0);
     }
     else return false;
     

  } //getSlip

  // fungsi untuk verify, check, deny, atau approve
  function changeStatus($db, $intStatus) {
    global $_REQUEST;
    global $_SESSION;


    if (!is_numeric($intStatus)) {
      return false;
    }

    $strUpdate = "";
    $strSQL  = "";
    $strmodified_byID = $_SESSION['sessionUserID'];

    $strUpdate = getStatusUpdateString($intStatus);


    foreach ($_REQUEST as $strIndex => $strValue) 
    {
      if (substr($strIndex,0,5) == 'chkID') 
      {
        $strSQLx = "SELECT status, employee_name, SUM(approved_cost) AS cost, t4.created
                    FROM hrd_medical_claim AS t1 
                    LEFT JOIN 
                      (SELECT t3.id, t3.status, employee_name, t3.created FROM hrd_medical_claim_master AS t3 
                      LEFT JOIN hrd_employee AS t2 ON t3.id_employee = t2.id) AS t4
                    ON t1.id_master = t4.id
                    WHERE t4.id = '$strValue' 
                    GROUP BY t4.id, status, employee_name, t4.created";
        $resDb = $db->execute($strSQLx);
        if ($rowDb = $db->fetchrow($resDb)) 
        { 
          //the status should be increasing
          if (isProcessable($rowDb['status'], $intStatus))
          {
            $strSQL .= "UPDATE hrd_medical_claim_master SET $strUpdate status = '$intStatus'  ";
            $strSQL .= "WHERE id = '$strValue'; "; 
            writeLog(ACTIVITY_EDIT, MODULE_PAYROLL, $rowDb['employee_name']." - ". $rowDb['created'] ." - ". $rowDb['cost'], $intStatus);
          }
        }
      }
      $resExec = $db->execute($strSQL);

    }

  } //changeStatus
  //----------------------------------------------------------------------

  //----MAIN PROGRAM -----------------------------------------------------
  $strInfo = "";

  $db = new CdbClass;
  if ($db->connect())
  {

    getUserEmployeeInfo();
    $arrUserList = getAllUserInfo($db);

    if ($bolCanView)
    {
       if (isset($_REQUEST['btnPrintSlip']))
       {
          if (getSlip($db)) die();
       }
    } 
    if (isset($_REQUEST['btnDelete'])) {
      if ($bolCanDelete) deleteData($db);
    } 
    else 
      callChangeStatus();

    // ------ AMBIL DATA KRITERIA -------------------------
//     getDefaultSalaryPeriode($strDefaultFrom,$strDefaultThru);

    $strDataDateFrom = (isset($_SESSION['sessionFilterDateFrom'])) ? $_SESSION['sessionFilterDateFrom'] :  date("Y-m-d");
    $strDataDateThru = (isset($_SESSION['sessionFilterDateThru'])) ? $_SESSION['sessionFilterDateThru'] : date("Y-m-d");
    $strDataBranch = (isset($_SESSION['sessionFilterBranch'])) ? $_SESSION['sessionFilterBranch'] : "";
    $strDataBank = (isset($_SESSION['sessionFilterBank'])) ? $_SESSION['sessionFilterBank'] : "";
    $strDataDivision = (isset($_SESSION['sessionFilterDivision'])) ? $_SESSION['sessionFilterDivision'] : "";
    $strDataDepartment = (isset($_SESSION['sessionFilterDepartment'])) ? $_SESSION['sessionFilterDepartment'] : "";
    $strDataSection = (isset($_SESSION['sessionFilterSection'])) ? $_SESSION['sessionFilterSection'] : "";
    $strDataSubSection = (isset($_SESSION['sessionFilterSubSection'])) ? $_SESSION['sessionFilterSubSection'] : "";
    $strDataEmployee = (isset($_SESSION['sessionFilterEmployee'])) ? $_SESSION['sessionFilterEmployee'] : "";
    $strDataEmployeeStatus = (isset($_REQUEST['dataEmployeeStatus'])) ? $_REQUEST['dataEmployeeStatus'] : "";

    if (isset($_REQUEST['dataDateFrom'])) $strDataDateFrom = $_REQUEST['dataDateFrom'];
    if (isset($_REQUEST['dataDateThru'])) $strDataDateThru = $_REQUEST['dataDateThru'];
    if (isset($_REQUEST['dataBranch'])) $strDataBranch = $_REQUEST['dataBranch'];
    if (isset($_REQUEST['dataBank'])) $strDataBank = $_REQUEST['dataBank'];
    if (isset($_REQUEST['dataDivision'])) $strDataDivision = $_REQUEST['dataDivision'];
    if (isset($_REQUEST['dataDepartment'])) $strDataDepartment = $_REQUEST['dataDepartment'];
    if (isset($_REQUEST['dataSection'])) $strDataSection = $_REQUEST['dataSection'];
    if (isset($_REQUEST['dataSubSection'])) $strDataSubSection = $_REQUEST['dataSubSection'];
    if (isset($_REQUEST['dataEmployee'])) $strDataEmployee = $_REQUEST['dataEmployee'];

    // simpan dalam session
    $_SESSION['sessionFilterDateFrom'] = $strDataDateFrom;
    $_SESSION['sessionFilterDateThru'] = $strDataDateThru;
    $_SESSION['sessionFilterBranch'] = $strDataBranch;
    $_SESSION['sessionFilterBank'] = $strDataBank;
    $_SESSION['sessionFilterDivision'] = $strDataDivision;
    $_SESSION['sessionFilterDepartment'] = $strDataDepartment;
    $_SESSION['sessionFilterSection'] = $strDataSection;
    $_SESSION['sessionFilterSubSection'] = $strDataSubSection;
    $_SESSION['sessionFilterEmployee'] = $strDataEmployee;

    $strDataTreatmentType = (isset($_REQUEST['dataTreatmentType'])) ? $_REQUEST['dataTreatmentType'] : ""; 
    $strDataTreatmentCode = (isset($_REQUEST['dataTreatmentCode'])) ? $_REQUEST['dataTreatmentCode'] : "";
    $strDataRequestStatus = (isset($_REQUEST['dataRequestStatus'])) ? $_REQUEST['dataRequestStatus'] : "";

    // ------------ GENERATE KRITERIA QUERY,JIKA ADA -------------
    $strKriteria = "";

    if ($strDataRequestStatus != "") {
      $strKriteria .= "AND status = '$strDataRequestStatus' ";
    }
    if ($strDataBranch != "") {
      $strKriteria .= "AND t2.branch_code = '$strDataBranch' ";
    }
    if ($strDataBank != "") {
      //$strKriteria .= "AND bank2_code = '$strDataBank' ";
      $strKriteria .= "AND bank_code = '$strDataBank' ";
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
    if ($strDataEmployee != "") {
      $strKriteria .= "AND employee_id = '$strDataEmployee' ";

    }   
    if ($strDataEmployeeStatus != "") {
      $strKriteria .= "AND employee_status = '$strDataEmployeeStatus' ";
    }
    if (isset($_REQUEST['btnPrintApproved'])) {
      $strKriteria .= "AND status >= " .REQUEST_STATUS_APPROVED. " ";
    }
    
    $strKriteria .= $strKriteriaCompany;

    if(isset($_REQUEST['btnShowAlert'])) { //request dari alert click
      $status = (isset($_REQUEST['dataStatus'])) ? $_REQUEST['dataStatus'] : "";
      if (is_numeric($status)) $strKriteria .= "AND status = $status ";
      else $strKriteria .= "AND status < ". REQUEST_STATUS_APPROVED. " ";
    }


    scopeData($strDataEmployee, $strDataSubSection, $strDataSection, $strDataDepartment, $strDataDivision, $_SESSION['sessionUserRole'], $arrUserInfo);
    scopeCBDataEntry($strDataEmployee, $_SESSION['sessionUserRole'], $arrUserInfo);

    if ($bolCanView) {
      if (validStandardDate($strDataDateFrom) && validStandardDate($strDataDateThru)) {
        // tampilkan hanya jika ada permintaan dan data tanggalnya tepat
         //dw  
        $yearFrom = substr($strDataDateFrom,0,4);
        $yearThru = substr($strDataDateThru,0,4);
        $monthFrom = substr($strDataDateFrom,5,2);
        $monthThru = substr($strDataDateThru,5,2);
        $dayFrom = substr($strDataDateFrom,8,2);
        $dayThru = substr($strDataDateThru,8,2);
      
        if ($yearThru > $yearFrom) //jika tahun 'Dari' lebih besar
          $hasilDate = true;
        else { //jika tahun 'Dari' lebih kecil atau sama dengan
          if ($yearThru == $yearFrom) { //jika tahun 'Dari' sama dengan
            if ($monthThru > $monthFrom) //jika bulan 'Dari' lebih besar
              $hasilDate = true;
            else { //jika bulan 'Dari' lebih kecil atau sama dengan
              if ($monthThru == $monthFrom) { //jika bulan 'Dari' sama dengan
                if ($dayThru > $dayFrom) //jika hari 'Dari' lebih besar
                  $hasilDate = true;
                else //jika hari 'Dari' lebih kecil dan sama dengan
                  if ($dayThru == $dayFrom) //jika hari 'Dari' sama dengan
                    $hasilDate = true;
                  else //jika hari 'Dari' lebih kecil
                    $hasilDate = false;
              }
              else //jika bulan 'Dari' lebih kecil
                $hasilDate = false;
            }
          }
          else //jika tahun 'Dari' lebih kecil
            $hasilDate = false;
        } 
        $strMsg='';
        if (!$hasilDate){
          $strMessages = $error['finish_time_before_start_time'];
        //dw
        }
        else 
        {
              if ((isset($_REQUEST['btnShow'])) || $bolPrint)
                $strDataDetail = getData($db,$strDataDateFrom, $strDataDateThru, $intTotalData, $strKriteria);
              else
                $strDataDetail = "";

              if (isset($_REQUEST['btnExcel'])) 
              {
                  // ambil data CSS-nya
                if (file_exists("../css/default_bw.css")) $strStyle = "../css/default_bw.css";
                $strPrintCss = "";
                $strPrintInit = "";
                headeringExcel("medical_claim.xls");
              }
        }
      } else {
        $strDataDetail = "";
      }
    } else {
      showError("view_denied");
    }

    $strButtonList = generateRoleButtons($bolCanEdit, $bolCanDelete, $bolCanCheck, $bolCanApprove, true);


    // generate data hidden input dan element form input

    $strDefaultWidthPx = 200;
    $strInputDateFrom = "<input type=text name=dataDateFrom id=dataDateFrom size=15 maxlength=10 value=\"$strDataDateFrom\">";
    $strInputDateThru = "<input type=text name=dataDateThru id=dataDateThru size=15 maxlength=10 value=\"$strDataDateThru\">";
    $strInputEmployee = "<input type=text name=dataEmployee id=dataEmployee size=22 maxlength=30 value=\"$strDataEmployee\" $strNonCbReadonly>";


    $strInputDivision = getDivisionList($db,"dataDivision", $strDataDivision, $strEmptyOption, "", "style=\"width:$strDefaultWidthPx\" ". $ARRAY_DISABLE_GROUP['division']);
    $strInputDepartment = getDepartmentList($db,"dataDepartment", $strDataDepartment, $strEmptyOption, "", "style=\"width:$strDefaultWidthPx\" ". $ARRAY_DISABLE_GROUP['department']);

    $strInputSection = getSectionList($db,"dataSection", $strDataSection, $strEmptyOption, "", "style=\"width:$strDefaultWidthPx\" ". $ARRAY_DISABLE_GROUP['section']);
    $strInputSubSection = getSubSectionList($db,"dataSubSection", $strDataSubSection, $strEmptyOption, "", "style=\"width:$strDefaultWidthPx\" ". $ARRAY_DISABLE_GROUP['sub_section']);
    //handle user company-access-right
    $strInputCompany = getCompanyList($db, "dataCompany", $strDataCompany, $strEmptyOption2, $strKriteria2, "style=\"width:$strDefaultWidthPx\"");   
    
    $strInputEmployeeStatus = getComboFromArray($ARRAY_EMPLOYEE_STATUS, "dataEmployeeStatus", $strDataEmployeeStatus,  $strEmptyOption, "style=\"width:$strDefaultWidthPx\"");

    $strInputBank = getBankList($db,"dataBank", $strDataBank, $strEmptyOption, "", "style=\"width:$strDefaultWidthPx\" ");
    $strInputBranch = getBranchList($db,"dataBranch", $strDataBranch, $strEmptyOption, "", "style=\"width:$strDefaultWidthPx\"");
    $strInputRequestStatus = getComboFromArray($ARRAY_REQUEST_STATUS, "dataRequestStatus", $strDataRequestStatus, $strEmptyOption, "style=\"width:$strDefaultWidthPx\"");
    $strInputTreatmentType = getMedicalTreatmentTypeList("dataTreatmentType", true, $strDataTreatmentType, $strEmptyOption, "style=\"width:$strDefaultWidthPx\"");
    $strInputTreatmentCode = getMedicalTreatmentCodeList($db, "dataTreatmentCode", $strDataTreatmentCode, $strEmptyOption, "where code <> '0'", "style=\"width:$strDefaultWidthPx\"");
    // informasi tanggal kehadiran
    if ($strDataDateFrom == $strDataDateThru) {
      $strInfo .= "<br>".strtoupper(pgDateFormat($strDataDateFrom, "d-M-Y"));
    } else {
      $strInfo .= "<br>".strtoupper(pgDateFormat($strDataDateFrom, "d-M-Y"));
      $strInfo .= " >> ".strtoupper(pgDateFormat($strDataDateThru, "d-M-Y"));
    }

  if ($strDataBranch != "") $strInfo .= "<br>". getWords("branch") ." : ". $strDataBranch;
  if ($strDataBank != "") $strInfo .= "<br>". getWords("bank")." : ". $strDataBank;
  if ($strDataDivision != "") $strInfo .= "<br>". getWords("division") ." : ". $strDataDivision;
  if ($strDataDepartment != "") $strInfo .= "<br>". getWords("department") ." : ". $strDataDepartment;
  if ($strDataSection != "") $strInfo .= "<br>". getWords("section")." : ". $strDataSection;
  if ($strDataSubSection != "") $strInfo .= "<br>". getWords("sub section")." : ". $strDataSubSection;
  if ($strDataCompany != "") $strInfo .= "<br>". getWords("company")." : ". $strDataCompany;
  if ($strDataEmployeeStatus != "") $strInfo .= "<br>". getWords("employee status")." : ". $strDataEmployeeStatus;
  $strInfo .= "&nbsp;<br>&nbsp;";

    $strHidden .= "<input type=hidden name=dataDateFrom value=\"$strDataDateFrom\">";
    $strHidden .= "<input type=hidden name=dataDateThru value=\"$strDataDateThru\">";
    $strHidden .= "<input type=hidden name=dataBranch value=\"$strDataBranch\">";
    $strHidden .= "<input type=hidden name=dataBank value=\"$strDataBank\">";
    $strHidden .= "<input type=hidden name=dataDivision value=\"$strDataDivision\">";
    $strHidden .= "<input type=hidden name=dataDepartment value=\"$strDataDepartment\">";
    $strHidden .= "<input type=hidden name=dataSection value=\"$strDataSection\">";
    $strHidden .= "<input type=hidden name=dataSubSection value=\"$strDataSubSection\">";
    $strHidden .= "<input type=hidden name=dataCompany value=\"$strDataCompany\">";
    $strHidden .= "<input type=hidden name=dataEmployee value=\"$strDataEmployee\">";
    $strHidden .= "<input type=hidden name=dataEmployeeStatus value=\"$strDataEmployeeStatus\">";
    $strHidden .= "<input type=hidden name=dataRequestStatus value=\"$strDataRequestStatus\">";
    $strHidden .= "<input type=hidden name=dataTreatmentType value=\"$strDataTreatmentType\">";
    $strHidden .= "<input type=hidden name=dataTreatmentCode value=\"$strDataTreatmentCode\">";
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

?>