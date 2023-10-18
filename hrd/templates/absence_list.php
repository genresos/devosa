<?php
  include_once('../global/session.php');
  include_once('global.php');
  include_once('form_object.php');
  include_once('../global/common_data.php');
  include_once('../global/common_function.php');
//  include_once('../includes/datagrid2/datagrid.php');
  include_once('../classes/datagrid_modified.php');
  include_once('../includes/form2/form2.php');
  include_once('../global/email_func.php');
  include_once('../classes/hrd/hrd_absence_type.php');
  include_once('../classes/hrd/hrd_absence.php');
  include_once('../classes/hrd/hrd_absence_detail.php');


class cDataGrid2 extends cDataGridNew
  {
    /*override this function*/
    function printOpeningRow($intRows, $rowDb)
    {
      $strResult = "";
      $strClass = getCssClass($rowDb['status']);
      if ($strClass != "") $strClass = "class=\"".$strClass."\"";
      $strResult .= "
            <tr $strClass valign=\"top\">";
      return $strResult;
    }

    /*override this function*/
    function _printGridButtons()
    {
      global $bolCanEdit;

      $strResult = "";
      if ($this->DATAGRID_RENDER_OUTPUT == DATAGRID_RENDER_NORMAL)
      {
        $colSpan = count($this->columnSet);
        if ($this->hasCheckbox && (count($this->dataset) > 0))
          //have checkbox
          $strResult.= "
              <!-- grid footer -->
              <tfoot>
              <tr>
                <td align=\"center\">".$this->_printCheckboxAllBottom()."</td>
                <td colspan=12>";
        else
          //don't have checkbox
          $strResult.= "
              <!-- grid footer -->
              <tfoot>
              <tr>
                <td colspan=13>";

        $counter = 0;
        if (count($this->buttons)>0)
        {
          foreach($this->buttons as $button)
          {
            if ($button['special'] && (count($this->dataset) == 0)) continue;
            $counter++;
            if ($button['class']=="")
              $className = "";
            else
              $className = "class=\"". $button['class'] . "\"";

            $strResult.= "
                <input ".$className." name=\"" . $button['name'] . "\" type=\"" . $button['type'] . "\" id=\"" . $button['id'] . "\" value=\"" . $button['value'] . "\" " . $button['clientAction'] . ">&nbsp;";
          }
        }

        if ($counter == 0) return "";

        $strResult.= "&nbsp;</td>
                <td nowrap=nowrap>";
        $strButtons = "";
        /*
        if ($_SESSION['sessionUserRole'] == ROLE_ADMIN || $_SESSION['sessionUserRole'] == ROLE_MANAGER || $_SESSION['sessionUserRole'] == ROLE_SUPERVISOR || $_SESSION['sessionUserRole'] == ROLE_DIRECTOR)
        {
          $strButtons .= "<input type=submit name=btnRecommend value=\"" .getWords('recommend'). "\" onClick=\"return confirmStatusChanges(false)\">";
          $strButtons .= "&nbsp;<input type=submit name=btnSkip value=\"" .getWords('skip'). "\" onClick=\"return confirmStatusChanges(false)\">";
          $strButtons .= "&nbsp;<input type=submit name=btnCancel value=\"" .getWords('clear status'). "\" onClick=\"return confirmStatusChanges(false)\">";
        }
        */
        $strResult .= $strButtons."&nbsp;</td>";
        if ($bolCanEdit) $strResult .= "<td colSpan=2>&nbsp;</td>";
        $strResult .= "
              </tr>
              </tfoot>
              <!-- end of grid footer -->";
      }
      return $strResult;
    }

  }

  $dataPrivilege = getDataPrivileges(basename($_SERVER['PHP_SELF']), $bolCanView, $bolCanEdit, $bolCanDelete, $bolCanApprove, $bolCanCheck, $bolCanAcknowledge, $bolCanApprove2);
  if (!$bolCanView) die(accessDenied($_SERVER['HTTP_REFERER']));

  $strWordsEntryAbsence         = getWords("entry absence");
  $strWordsAbsenceList          = getWords("absence list");
  $strWordsEntryPartialAbsence  = getWords("partial absence entry");
  $strWordsPartialAbsenceList   = getWords("partial absence list");
  $strWordsAnnualLeave          = getWords("annual leave");
  $strConfirmSave               = getWords("save");
  $strWordsNew                  = getWords("new");
  $strWordsDenied               = getWords("denied");
  $strWordsChecked              = getWords("checked");
  $strWordsApproved             = getWords("approved");
  $strWordsApproved2            = getWords("approved 2");
  $strWordsFinished             = getWords("finished");
  $DataGrid = "";
  $formFilter = "";

  //DAFTAR FUNGSI--------------------------------------------------------------------------------------------------------------
  function getData($db)
  {
    global $dataPrivilege;
//    global $bolCanApprove2;
    global $f;
    global $myDataGrid;
    global $DataGrid;
    global $strKriteriaCompany;
//    //$bolCanApprove2 = isEligibleApprove2($db);
//    if ($bolCanApprove2 = true) echo "a"; else echo "b";

    $arrData = $f->getObjectValues();

    $strKriteria = "";
    // GENERATE CRITERIA
    if ($arrData['dataAbsenceType'] != "") {
      $strKriteria .= "AND absence_type_code = '".$arrData['dataAbsenceType']."'";
    }

    if (validStandardDate($strDateFrom = $arrData['dataDateFrom']) && validStandardDate($strDateThru = $arrData['dataDateThru'])) {
      $strKriteria .= "AND ((date_from, date_thru) ";
      $strKriteria .= "    OVERLAPS (DATE '$strDateFrom', DATE '$strDateThru') ";
      $strKriteria .= "    OR (date_thru = DATE '$strDateFrom') ";
      $strKriteria .= "    OR (date_thru = DATE '$strDateThru')) ";
    }


    if ($arrData['dataEmployee']!= "") {
      $strKriteria .= "AND employee_id = '".$arrData['dataEmployee']."'";
    }
    if ($arrData['dataPosition']!= "") {
      $strKriteria .= "AND position_code = '".$arrData['dataPosition']."'";
    }
    if ($arrData['dataBranch']!= "") {
      $strKriteria .= "AND branch_code = '".$arrData['dataBranch']."'";
    }
    if ($arrData['dataGrade']!= "") {
      $strKriteria .= "AND grade_code = '".$arrData['dataGrade']."'";
    }
    if ($arrData['dataStatus']!= "") {
      $strKriteria .= "AND employee_status = '".$arrData['dataStatus']."'";
    }
    if ($arrData['dataActive']!= "") {
      $strKriteria .= "AND active = '".$arrData['dataActive']."'";
    }
    if ($arrData['dataRequestStatus']!= "") {
      $strKriteria .= "AND status = '".$arrData['dataRequestStatus']."'";
    }
    if ($arrData['dataDivision']!= "") {
      $strKriteria .= "AND division_code = '".$arrData['dataDivision']."'";
    }
    if ($arrData['dataDepartment']!= "") {
      $strKriteria .= "AND department_code = '".$arrData['dataDepartment']."'";
    }
    if ($arrData['dataSection']!= "") {
      $strKriteria .= "AND section_code = '".$arrData['dataSection']."'";
    }
    if ($arrData['dataSubSection']!= "") {
      $strKriteria .= "AND sub_section_code = '".$arrData['dataSubSection']."'";
    }
    $strKriteria .= $strKriteriaCompany;

    if ($db->connect())
    {
      $myDataGrid = new cDataGrid2("formData","DataGrid1", "100%", "100%", false, true, false);
      $myDataGrid->caption = getWords(strtoupper(vsprintf(getWords("list of %s"), getWords($dataPrivilege['menu_name']))));
      $myDataGrid->setAJAXCallBackScript(basename($_SERVER['PHP_SELF']));
      $myDataGrid->setCriteria($strKriteria);
      $myDataGrid->pageSortBy = "created";
      $myDataGrid->sortOrder = "ASC";
      $myDataGrid->addColumnCheckbox(new DataGrid_Column("chkID", "id", array('width' => '30'), array('align'=>'center', 'nowrap' => '')), true /*bolDisableSelfStatusChange*/);

      $myDataGrid->addColumnNumbering(new DataGrid_Column(getWords("no."), "", array('width'=>'30'), array('nowrap'=>'')));
      $myDataGrid->addColumn(new DataGrid_Column(getWords("created"), "created_date", "", array('nowrap' => '')));
      $myDataGrid->addColumn(new DataGrid_Column(getWords("date from"), "date_from", "", array('nowrap' => '')));
      $myDataGrid->addColumn(new DataGrid_Column(getWords("date thru"), "date_thru", "", array('nowrap' => '')));
      // $myDataGrid->addColumn(new DataGrid_Column(getWords("id employee"), "id_employee", "", array('nowrap' => '')));
      $myDataGrid->addColumn(new DataGrid_Column(getwords("n i k"), "employee_id", "", array('nowrap' => '')));
      $myDataGrid->addColumn(new DataGrid_Column(getWords("employee name"), "employee_name", "", array('nowrap' => '')));
      $myDataGrid->addColumn(new DataGrid_Column(getWords("department"), "department_code",  "", array('nowrap' => ''), false, false, "","getDepartmentName()"));
      $myDataGrid->addColumn(new DataGrid_Column(getWords("is leave"), "deduct_leave",  "", array('nowrap' => '','align' => 'center'), false, false, "","printActiveSymbol()"));
      $myDataGrid->addColumn(new DataGrid_Column(getWords("is add leave"), "deduct_additional_leave",  "", array('nowrap' => '','align' => 'center'), false, false, "","printActiveSymbol()"));
      $myDataGrid->addColumn(new DataGrid_Column(getWords("absence type"), "absence_type_code", "", array('nowrap' => '','align' => 'center')));
      $myDataGrid->addColumn(new DataGrid_Column(getWords("duration"), "duration",  "", array('nowrap' => '','align' => 'center'), false, false, "",""));
      $myDataGrid->addColumn(new DataGrid_Column(getWords("status"), "status",  "", array('nowrap' => '','align' => 'center'), false, false, "","printRequestStatus()"));
      $myDataGrid->addColumn(new DataGrid_Column(getWords("created by"), "created_by",  "", array('nowrap' => ''), false, false, "", "printUserName()"));
      if ($dataPrivilege['edit'] == 't')
        $myDataGrid->addColumn(new DataGrid_Column("", "", array("width" => "60"), array('align' => 'center', 'nowrap' => ''), false, false, "","printEditLink()", "", false /*show in excel*/));

      foreach($arrData AS $key => $value)
      {
        $myDataGrid->strAdditionalHtml .= generateHidden($key, $value, "");
      }
// print_r($dataPrivilege);die();
      //tampilkan buttons sesuai dengan otoritas, common_function.php
      // generateRoleButtons($bolCanEdit, $bolCanDelete, $bolCanCheck, $bolCanApprove, $bolCanApprove2);
      generateRoleButtons($dataPrivilege['edit'], $dataPrivilege['delete'], $dataPrivilege['check'], $dataPrivilege['approve'], $dataPrivilege['approve2'], true, $myDataGrid);
      $myDataGrid->addButtonExportExcel(getWords("export excel"), $dataPrivilege['menu_name'].".xls", getWords($dataPrivilege['menu_name']));

      $myDataGrid->getRequest();

      $strSQLCOUNT  = "SELECT COUNT(*) AS total FROM hrd_absence AS t1 LEFT JOIN hrd_employee  AS t2 ON t1.id_employee = t2.id";
      $strSQL  = "select * from (SELECT t1.*, t1.created::date as created_date, t3.deduct_additional_leave, t3.deduct_leave, t3.leave_weight, t2.id AS idemployee, t2.employee_id, t2.employee_name, t2.id_company, t2.active, t2.employee_status, t2.grade_code, t2.branch_code, ";
      $strSQL .= "t2.position_code, t2.division_code, t2.department_code, t2.section_code, t2.sub_section_code ";
      $strSQL .= "FROM hrd_absence AS t1 ";
      $strSQL .= "LEFT JOIN hrd_employee AS t2 ON t1.id_employee = t2.id ";
      $strSQL .= "LEFT JOIN hrd_absence_type AS t3 ON t1.absence_type_code = t3.code) as t  ";
      $strSQL .= "WHERE 1=1 $strKriteria";

      $myDataGrid->totalData = $myDataGrid->getTotalData($db, $strSQLCOUNT);
      $dataset = $myDataGrid->getData($db, $strSQL);

      //bind Datagrid with array dataset and branchCode
      $myDataGrid->bind($dataset);
      $DataGrid = $myDataGrid->render();
    }
    else $DataGrid = "";
    return $DataGrid;
  }

  function printEditLink($params)
  {
    extract($params);
    return "
      <a href=\"absence_edit.php?dataID=".$record['id']."\">" .getWords('edit'). "</a>";
  }


//  function callChangeStatus() {
//
//    global $_REQUEST;
////    print_r($_REQUEST);
//    global $db;
//    if (isset($_REQUEST['btnVerified'])) $intStatus = REQUEST_STATUS_VERIFIED;
//    else if (isset($_REQUEST['btnChecked'])) $intStatus = REQUEST_STATUS_CHECKED;
//    else if (isset($_REQUEST['btnApproved'])) $intStatus = REQUEST_STATUS_APPROVED;
//    else if (isset($_REQUEST['btnDenied'])) $intStatus = REQUEST_STATUS_DENIED;
//    else if (isset($_REQUEST['btnPaid'])) $intStatus = REQUEST_STATUS_PAID;
//    changeStatus($db, $intStatus);
//  }
  // fungsi untuk verify, check, deny, atau approve
  function changeStatus($db, $intStatus) {
    global $_REQUEST;
    global $_SESSION;
    if (!is_numeric($intStatus)) {
      return false;
    }

    $strUpdate = "";
    $strSQL  = "";
    $arrEmailData = "";
    $strmodified_byID = $_SESSION['sessionUserID'];
    $strUpdate = getStatusUpdateString($intStatus);

    // die(print_r($_REQUEST));
    foreach ($_REQUEST as $strIndex => $strValue)
    {
      $strBody = "";
      if (substr($strIndex,0,15) == 'DataGrid1_chkID')
      {
        $strSQLx = "SELECT status, employee_name, t1.id_employee, t1.created, absence_type_code, t1.date_from, t1.date_thru
                    FROM hrd_absence AS t1
                    LEFT JOIN hrd_employee AS t2 ON t1.id_employee = t2.id
                    WHERE t1.id = '$strValue' ";
        $resDb = $db->execute($strSQLx);
        if ($rowDb = $db->fetchrow($resDb))
        {
          $strBody.= "Name: ".getEmployeeNameEmail($rowDb['id_employee'])."<br>";
          $strBody.= "Absence Type: ".$rowDb['absence_type_code']."<br>";
          $strBody.= "Date: ".$rowDb['date_from']." until ".$rowDb['date_thru'];
          $strBody =  getBody($intStatus,'Absence',$strBody,$strmodified_byID);
          //the status should be increasing
          //if ($rowDb['status'] < $intStatus && $rowDb['status'] != REQUEST_STATUS_DENIED )
          if (isProcessable($rowDb['status'], $intStatus))
              {
                $strSubject = getSubject($intStatus,'Absence',getEmployeeIDEmail($rowDb['id_employee']));
                sendMail($strSubject,$strBody);
                $strSQL .= "UPDATE hrd_absence SET $strUpdate status = '$intStatus'  ";
                $strSQL .= "WHERE id = '$strValue'; ";
                $resExec = $db->execute($strSQL);
                writeLog(ACTIVITY_EDIT, MODULE_PAYROLL, $rowDb['employee_name']." - ". $rowDb['created'] ." - ". $rowDb['absence_type_code'], $intStatus);
              }
        }
      }
    }

  } //changeStatus

  // fungsi untuk menghapus data
  function deleteData()
  {
    global $myDataGrid;

    $arrKeys = array();
    foreach ($myDataGrid->checkboxes as $strValue)
    {
      $arrKeys['id'][] = $strValue;
      $arrKeys2['id_absence'][] = $strValue;
    }
    $tblAbsence = new cHrdAbsence();
    $tblAbsenceDetail = new cHrdAbsenceDetail();
    $tblAbsence->deleteMultiple($arrKeys);
    $tblAbsenceDetail->deleteMultiple($arrKeys2);
    writeLog(ACTIVITY_DELETE, MODULE_PAYROLL, implode(",", $arrKeys2['id_absence']));

    $myDataGrid->message = $tblAbsence->strMessage;
  } //deleteData



  //----MAIN PROGRAM -----------------------------------------------------
  $db = new CdbClass;
  if ($db->connect())
  {

    getUserEmployeeInfo();
    $arrUserList = getAllUserInfo($db);
    if (isset($_POST['btnShowAlert']) && $_POST['btnShowAlert'] == 1)
    {
      $dtFrom = date("Y-m-")."25";
      $dtFrom = getNextDateNextMonth($dtFrom, -1);
      $dtThru = date("Y-m-")."24";
      $reqStatus = "";
      $_SESSION["sessiondataEmployee"] = "";
      $_SESSION["sessiondataPosition"] = "";
      $_SESSION["sessiondataSalaryGrade"] = "";
      $_SESSION["sessiondataEmployeeStatus"] = "";
      $_REQUEST["sessiondataEmployeeStatus"] = "";
      echo       $_SESSION["sessiondataEmployeeStatus"];

    }
    else
    {
      $dtFrom = date("Y-m-")."25";
      $dtFrom = getNextDateNextMonth($dtFrom, -1);
      $dtThru = date("Y-m-")."24";
      $reqStatus = null;
    }
    $strDataID   = getPostValue('dataID');
    scopeData($strDataEmployee, $strDataSubSection, $strDataSection, $strDataDepartment, $strDataDivision, $_SESSION['sessionUserRole'], $arrUserInfo, $strDataBranch);
    // echo $_SESSION['sessionUserRole']."jasdj";die();
    $f = new clsForm("formFilter", 3, "100%", "");
    $f->caption = strtoupper($strWordsFILTERDATA);
    $f->addInput(getWords("date from"), "dataDateFrom", getInitialValue("DateFrom", $dtFrom, $dtFrom), array("style" => "width:$strDateWidth"), "date", false, true, true);
    $f->addInput(getWords("date thru"), "dataDateThru", getInitialValue("DateThru", $dtThru, $dtThru), array("style" => "width:$strDateWidth"), "date", false, true, true);
    $f->addSelect(getWords("absence type"), "dataAbsenceType", getDataListabsenceType("", true, array("value" => "", "text" => "", "selected" => true)), array("style" => "width:$strDefaultWidthPx"), "", false);
    $f->addInputAutoComplete(getWords("employee"), "dataEmployee", getDataEmployee(getInitialValue("Employee", null, $strDataEmployee)), "style=width:$strDefaultWidthPx ".$strEmpReadonly, "string", false);
    $f->addLabelAutoComplete("", "dataEmployee", "");
    $f->addSelect(getWords("request status"), "dataRequestStatus", getDataListRequestStatus(getInitialValue("RequestStatus", null, null), true, array("value" => "", "text" => "", "selected" => true)), array("style" => "width:$strDefaultWidthPx"), "", false);
      //$f->addSelect(getWords("request status"), "dataRequestStatus", getDataListRequestStatus("", true, array("value" => "", "text" => "", "selected" => true)), array("style" => "width:$strDefaultWidthPx"), "", false);

    $f->addSelect(getWords("branch"), "dataBranch", getDataListBranch(getInitialValue("Branch", "", $strDataBranch), true), array("style" => "width:$strDefaultWidthPx"), "", false, ($ARRAY_DISABLE_GROUP['branch'] == ""));

    $f->addSelect(getWords("level"), "dataPosition", getDataListPosition(getInitialValue("Position"), true), array("style" => "width:$strDefaultWidthPx"), "", false);
    $f->addSelect(getWords("grade"), "dataGrade", getDataListSalaryGrade(getInitialValue("Grade"), true), array("style" => "width:$strDefaultWidthPx"), "", false);

    $f->addSelect(getWords("status"), "dataStatus", getDataListEmployeeStatus(getInitialValue("EmployeeStatus", "", ""), true/*, array("value" => "", "text" => "", "selected" => true)*/), array("style" => "width:$strDefaultWidthPx"), "", false);

      // print_r(getDataListEmployeeStatus(getInitialValue("EmployeeStatus", "", "")));
      // print_r(getDataListEmployeeActive(getInitialValue("Active")));

    $f->addSelect(getWords("active"), "dataActive", getDataListEmployeeActive(getInitialValue("Active"), true, array("value" => "", "text" => "", "selected" => true)), array("style" => "width:$strDefaultWidthPx"), "", false);

    $f->addLiteral("","","");
    $f->addSelect(getWords("company"), "dataCompany", getDataListCompany($strDataCompany, $bolCompanyEmptyOption, $arrCompanyEmptyData, $strKriteria2), array("style" => "width:$strDefaultWidthPx"), "", false);

    $f->addSelect(getWords("division"), "dataDivision", getDataListDivision(getInitialValue("Division", "", $strDataDivision), true), array("style" => "width:$strDefaultWidthPx"), "", false, ($ARRAY_DISABLE_GROUP['division'] == ""));
    $f->addSelect(getWords("department "), "dataDepartment", getDataListDepartment(getInitialValue("Department", "", $strDataDepartment), true), array("style" => "width:$strDefaultWidthPx"), "", false, ($ARRAY_DISABLE_GROUP['department'] == ""));
    $f->addSelect(getWords("section"), "dataSection", getDataListSection(getInitialValue("Section", "", $strDataSection), true), array("style" => "width:$strDefaultWidthPx"), "", false, ($ARRAY_DISABLE_GROUP['section'] == ""));
    $f->addSelect(getWords("sub section"), "dataSubSection", getDataListSubSection(getInitialValue("SubSection", "", $strDataSubSection), true), array("style" => "width:$strDefaultWidthPx"), "", false, ($ARRAY_DISABLE_GROUP['sub_section'] == ""));

    $f->addSubmit("btnShow", getWords("show"), "", true, true, "", "", "");

    $formFilter = $f->render();
    getData($db);
  }
  $tbsPage = new clsTinyButStrong ;

  //write this variable in every page
  $strPageTitle = getWords($dataPrivilege['menu_name']);
  $pageIcon = "../images/icons/".$dataPrivilege['icon_file'];
  $strTemplateFile = getTemplate(str_replace(".php", ".html", basename($_SERVER['PHP_SELF'])));
  //------------------------------------------------
  //Load Master Template
  $tbsPage->LoadTemplate($strMainTemplate) ;
  $tbsPage->Show() ;
//--------------------------------------------------------------------------------



?>
