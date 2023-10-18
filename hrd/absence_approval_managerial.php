<?php
  include_once('../global/session.php');
  include_once('global.php');
  include_once('form_object.php');
  include_once('activity.php');
  include_once('../global/common_data.php');
  include_once('../global/employee_function.php');
  //include_once('../includes/datagrid2/datagrid.php');
  include_once('../classes/datagrid_modified.php');
  include_once('../includes/form2/form2.php');
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
  $strWordsAbsenceSlip          = getWords("absence slip");
  $strConfirmSave = getWords("save ?");

  $DataGrid = "";
  $formFilter = "";



  //DAFTAR FUNGSI--------------------------------------------------------------------------------------------------------------
  function getData($db)
  {
    global $dataPrivilege;
    global $bolCanEdit, $bolCanDelete, $bolCanApprove, $bolCanCheck, $bolCanAcknowledge,$bolCanApprove2;
    global $f;
    global $myDataGrid;
    global $DataGrid;
    global $strKriteriaCompany;

    //global $arrUserInfo;

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


    if ($arrData['dataEmployee'] != "") {
      $strKriteria .= "AND employee_id = '".$arrData['dataEmployee']."'";
    }
    if ($arrData['dataApproverID'] != "") {
      $strKriteria .= "AND approver_id = '".$arrData['dataApproverID']."'";
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
    if ($arrData['dataEmployeeStatus']!= "") {
      $strKriteria .= "AND employee_status = '".$arrData['dataEmployeeStatus']."'";
    }
    if ($arrData['dataActive']!= "") {
      $strKriteria .= "AND active = '".$arrData['dataActive']."'";
    }
    if ($arrData['dataDeductLeave'] != "" && $arrData['dataDeductLeave'] != "undefined") {
      $strKriteria .= "AND deduct_leave = '".$arrData['dataDeductLeave']."'";
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
    $strKriteria .=  "AND approver_id IS NOT NULL ";
    $strKriteria .= $strKriteriaCompany;

    if ($db->connect())
    {
      $myDataGrid = new cDataGrid2("formData","DataGrid1", "100%", "100%", false, true, false);
      $myDataGrid->caption = getWords(strtoupper(vsprintf(getWords("list of %s"), getWords($dataPrivilege['menu_name']))));
      $myDataGrid->setAJAXCallBackScript(basename($_SERVER['PHP_SELF']));
      $myDataGrid->setCriteria($strKriteria);
      $myDataGrid->pageSortBy = "date_from,employee_name";
      $myDataGrid->addColumnCheckbox(new DataGrid_Column("chkID", "id", array('width' => '30'), array('align'=>'center', 'nowrap' => '')), true /*bolDisableSelfStatusChange*/);

      $myDataGrid->addColumnNumbering(new DataGrid_Column(getWords("no."), "", array('width'=>'30'), array('nowrap'=>'')));
      $myDataGrid->addColumn(new DataGrid_Column(getWords("created"), "created_date", "", array('nowrap' => '')));
      $myDataGrid->addColumn(new DataGrid_Column(getWords("date from"), "date_from", "", array('nowrap' => '')));
      $myDataGrid->addColumn(new DataGrid_Column(getWords("date thru"), "date_thru", "", array('nowrap' => '')));
      $myDataGrid->addColumn(new DataGrid_Column(getwords("n i k"), "employee_id", "", array('nowrap' => '')));
      $myDataGrid->addColumn(new DataGrid_Column(getWords("employee name"), "employee_name", "", array('nowrap' => '')));
      $myDataGrid->addColumn(new DataGrid_Column(getWords("department"), "department_code",  "", array('nowrap' => ''), false, false, "","getDepartmentName()"));
      $myDataGrid->addColumn(new DataGrid_Column(getWords("is leave"), "deduct_leave",  "", array('nowrap' => ''), false, false, "","printActiveSymbol()"));
      $myDataGrid->addColumn(new DataGrid_Column(getWords("absence type"), "absence_type_name", "", array('nowrap' => '')));
      $myDataGrid->addColumn(new DataGrid_Column(getWords("duration"), "duration",  "", array('nowrap' => ''), false, false, "",""));
      $myDataGrid->addColumn(new DataGrid_Column(getWords("status"), "status",  "", array('nowrap' => ''), false, false, "","printRequestStatus()"));
      $myDataGrid->addColumn(new DataGrid_Column(getWords("note"), "note",  "", array('nowrap' => '','align' => 'left'), false, false, "",""));
      $myDataGrid->addColumn(new DataGrid_Column(getWords("doc"), "doc",  "", array('nowrap' => '','align' => 'left'), false, false, "","printDocLink()"));
      $myDataGrid->addColumn(new DataGrid_Column(getWords("created by"), "created_by",  "", array('nowrap' => ''), false, false, "", "printUserName()"));
      if ($bolCanEdit){
        $myDataGrid->addColumn(new DataGrid_Column("", "", array("width" => "60"), array('align' => 'center', 'nowrap' => ''), false, false, "","printGlobalEditLink()", "", false /*show in excel*/));
        $myDataGrid->addColumn(new DataGrid_Column("", "", array("width" => "60"), array('align' => 'center', 'nowrap' => 'nowrap'), false, false, "","printShowLink()", "string", false));
			}
      foreach($arrData AS $key => $value)
      {
        $myDataGrid->strAdditionalHtml .= generateHidden($key, $value, "");
      }
      //tampilkan buttons sesuai dengan otoritas, common_function.php
      generateRoleButtons($bolCanEdit, $bolCanDelete, $bolCanCheck, $bolCanApprove, $bolCanApprove2, true, $myDataGrid);
      $myDataGrid->addButtonExportExcel(getWords("export excel"), $dataPrivilege['menu_name'].".xls", getWords($dataPrivilege['menu_name']));

      $myDataGrid->getRequest();

      $strSQLCOUNT  = "SELECT COUNT(*) AS total FROM hrd_absence AS t1 LEFT JOIN hrd_employee  AS t2 ON t1.id_employee = t2.id ";
      $strSQLCOUNT .= "LEFT JOIN hrd_absence_type AS t3 ON t1.absence_type_code = t3.code  ";
      $strSQLCOUNT .= "LEFT JOIN hrd_position AS t4 ON t2.position_code = t4.position_code";

      $strSQL  = "select * from (SELECT t1.*, approver_id, t1.created::date as created_date, t3.deduct_leave, t3.leave_weight, t2.id AS idemployee, ";
      $strSQL .= "t2.employee_id, t2.employee_name, t2.id_company, t2.active, t2.employee_status, t2.grade_code, t2.branch_code, ";
      $strSQL .= "t2.position_code, t2.division_code, t2.department_code, t2.section_code, t2.sub_section_code, t3.note AS absence_type_name ";
      $strSQL .= "FROM hrd_absence AS t1 ";
      $strSQL .= "LEFT JOIN hrd_employee AS t2 ON t1.id_employee = t2.id ";
      $strSQL .= "LEFT JOIN hrd_absence_type AS t3 ON t1.absence_type_code = t3.code ";
      $strSQL .= "LEFT JOIN hrd_position AS t4 ON t2.position_code = t4.position_code) as t  ";

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

	function printDocLink($params){
		extract($params);
		if ($record['doc'] != ""){
    	return "<a target=\"_blank\" href=\"absencedoc/".$record['doc']."\">" .getWords('view'). "</a>";
    }else{
    	return "-";
    }
	}

	function printShowLink($params)
  {
    extract($params);
    global $arrUserList;
    $strResult  = "";
    // tambahkan info record info
    $strDiv  = "<div id='detailRecord$counter' style=\"display:none\">\n";
    $strDiv .= "<strong>" .$record['employee_id']."-".$record['employee_name']."</strong><br>\n";
    $strDiv .= getWords("created"). ": ".substr($record['created'], 0,19) ." ";
    $strDiv .= (isset($arrUserList[$record['created_by']])) ? $arrUserList[$record['created_by']]['name']."<br>" : "<br>";
    $strDiv .= getWords("last modified"). ": ".substr($record['modified'], 0,19) ." ";
    $strDiv .= (isset($arrUserList[$record['modified_by']])) ? $arrUserList[$record['modified_by']]['name']."<br>" : "<br>";
    $strDiv .= getWords("checked"). ": ".substr($record['checked_time'], 0,19) ." ";
    $strDiv .= (isset($arrUserList[$record['checked_by']])) ? $arrUserList[$record['checked_by']]['name']."<br>" : "<br>";

    $record['approved1'] = !empty($record['approved1']) ? $record['approved1'] : $record['approved_time'];
    $record['approved1_by'] = !empty($record['approved1_by']) ? $record['approved1_by'] : $record['approved_by'];
    $strDiv .= getWords("approved"). ": ".substr($record['approved1'], 0,19) ." ";
    $strDiv .= (isset($arrUserList[$record['approved1_by']])) ? $arrUserList[$record['approved1_by']]['name']."<br>" : "<br>";

    $strDiv .= getWords("approved 2"). ": ".substr($record['approved2_time'], 0,19) ." ";
    $strDiv .= (isset($arrUserList[$record['approved2_by']])) ? $arrUserList[$record['approved2_by']]['name']."<br>" : "<br>";

    $strDiv .= getWords("denied"). ": ".substr($record['denied_time'], 0,19) ." ";
    $strDiv .= (isset($arrUserList[$record['denied_by']])) ? $arrUserList[$record['denied_by']]['name']."<br>" : "<br>";
    $strDiv .= "</div>\n";
    $strResult .= $strDiv."<a href=\"javascript:openViewWindowByContentId('Record Information', 'detailRecord$counter', 400, 150)\" title=\"" .getWords("show record info")."\">" .getWords("show")."</a>";

    return $strResult;
  }

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
      if (substr($strIndex,0,15) == 'DataGrid1_chkID') 
      {
        $strSQLx = "SELECT status, id_employee, employee_name, t1.created, date_from, absence_type_code, duration, deduct_leave, leave_weight
                    FROM hrd_absence AS t1 
                    LEFT JOIN hrd_employee AS t2 ON t1.id_employee = t2.id
                    LEFT JOIN hrd_absence_type AS t3 ON t1.absence_type_code = t3.code  
                    WHERE t1.id = '$strValue' ";
        $resDb = $db->execute($strSQLx  );
        if ($rowDb = $db->fetchrow($resDb)) 
        {  
          //the status should be increasing
          if (isProcessable($rowDb['status'], $intStatus))
          {
            //Jika merupakan data cuti, kurangi jatah cuti
            if ($intStatus == REQUEST_STATUS_APPROVED_2 && $rowDb['deduct_leave'] == 't')
            {
              $intDuration = $rowDb['duration'];
              $strYear = updateEmployeeLeave($db, $rowDb['id_employee'], $intDuration, $rowDb['leave_weight']);
              if ($strYear != "") 
                $strSQL .= "UPDATE hrd_absence SET leave_year = '$strYear', leave_duration = $intDuration WHERE id = '$strValue'; ";
            }
            $strSQL .= "UPDATE hrd_absence SET $strUpdate status = '$intStatus'  ";
            $strSQL .= "WHERE id = '$strValue'; "; 
            writeLog(ACTIVITY_EDIT, MODULE_HR, $rowDb['employee_name']." - ". $rowDb['date_from'] ." - ". $rowDb['absence_type_code'], $intStatus);
          }
        }
      }
      $resExec = $db->execute($strSQL);
    }

  } //changeStatus

  // fungsi untuk menghapus data
  function deleteData() 
  {
    global $myDataGrid;
    global $db;
    $tblAbsence = new cHrdAbsence();    
    $tblAbsenceDetail = new cHrdAbsenceDetail();    
    $arrKeys = array();
    foreach ($myDataGrid->checkboxes as $strValue)
    {
      $arrKeys['id'][] = $strValue;
      $arrKeys2['id_absence'][] = $strValue;
      $arrAbsence = $tblAbsence->find(array("id" => $strValue), "id_employee, leave_duration, date_from, absence_type_code", "id", null, 1, "id");
      if ($arrAbsence['leave_duration'] > 0)
      {
        $strIDEmployee = $arrAbsence['id_employee'];
        $intDuration   = 0 - $arrAbsence['leave_duration'];
        updateEmployeeLeave($db, $strIDEmployee, $intDuration);        
      }

    }
    $tblAbsence->deleteMultiple($arrKeys);
    $tblAbsenceDetail->deleteMultiple($arrKeys2); 
    writeLog(ACTIVITY_DELETE, MODULE_HR, implode(",", $arrKeys2['id_absence']));
    $myDataGrid->message = $tblAbsence->strMessage;
  } //deleteData



  //----MAIN PROGRAM -----------------------------------------------------
  $db = new CdbClass;
  if ($db->connect()) 
  {

    getUserEmployeeInfo();
    $arrUserList = getAllUserInfo($db);
    $_getInitialValue = (isset($_POST['btnShowAlert']) && $_POST['btnShowAlert'] == 1) ? "getInitialValueAlert" : "getInitialValue";
    $strDataID   = getPostValue('dataID');
    $strDeductLeave = getPostValue('dataDeductLeave');

    scopeData($strDataEmployee, $strDataSubSection, $strDataSection, $strDataDepartment, $strDataDivision, $_SESSION['sessionUserRole'], $arrUserInfo);
    $f = new clsForm("formFilter", 3, "100%", "");
    $f->caption = strtoupper($strWordsFILTERDATA);

    $f->addInput(getWords("date from"), "dataDateFrom", $_getInitialValue("DateFrom", date("Y-m-")."01"), array("style" => "width:$strDateWidth"), "date", false, true, true);
    $f->addInput(getWords("date thru"), "dataDateThru", $_getInitialValue("DateThru", date("Y-m-d")), array("style" => "width:$strDateWidth"), "date", false, true, true);
    $f->addInputAutoComplete(getWords("employee"), "dataEmployee", getDataEmployee($_getInitialValue("Employee", null, $strDataEmployee)), "style=width:$strDefaultWidthPx ".$strEmpReadonly, "string", false);
    $f->addLabelAutoComplete("", "dataEmployee", "");
    $f->addInput(getWords("approver id"), "dataApproverID", $arrUserInfo['employee_id'], array("style" => "width:$strDateWidth"), "string", false, false, true);

    $f->addSelect(getWords("absence type"), "dataAbsenceType", getDataListAbsenceType("", true, $arrEmpty), array("style" => "width:$strDefaultWidthPx"), "", false);
    $f->addSelect(getWords("deduct leave"), "dataDeductLeave", getDataListEmployeeActive($strDeductLeave, true, $arrEmpty), array("style" => "width:$strDefaultWidthPx"), "", false);  

    $f->addSelect(getWords("request status"), "dataRequestStatus", getDataListRequestStatus($_getInitialValue("RequestStatus"), true, $arrEmpty), array("style" => "width:$strDefaultWidthPx"), "", false);  
    $f->addLiteral("","","");

    $f->addSelect(getWords("branch"), "dataBranch", getDataListBranch($_getInitialValue("Branch"), true), array("style" => "width:$strDefaultWidthPx"), "", false);  
    $f->addSelect(getWords("level"), "dataPosition", getDataListPosition($_getInitialValue("Position"), true), array("style" => "width:$strDefaultWidthPx"), "", false);  
    $f->addSelect(getWords("grade"), "dataGrade", getDataListSalaryGrade($_getInitialValue("Grade"), true), array("style" => "width:$strDefaultWidthPx"), "", false);    
    $f->addSelect(getWords("status"), "dataEmployeeStatus", getDataListEmployeeStatus($_getInitialValue("EmployeeStatus", "", ""), true, $arrEmpty), array("style" => "width:$strDefaultWidthPx"), "", false);  

    $f->addSelect(getWords("active"), "dataActive", getDataListEmployeeActive($_getInitialValue("Active"), true, $arrEmpty), array("style" => "width:$strDefaultWidthPx"), "", false);  
    $f->addLiteral("","","");

    $f->addSelect(getWords("company"), "dataCompany", getDataListCompany($strDataCompany, $bolCompanyEmptyOption, $arrCompanyEmptyData, $strKriteria2), array("style" => "width:$strDefaultWidthPx"), "", false);

    $f->addSelect(getWords("division"), "dataDivision", getDataListDivision($_getInitialValue("Division", "", $strDataDivision), true), array("style" => "width:$strDefaultWidthPx"), "", false, ($ARRAY_DISABLE_GROUP['division'] == ""));
    $f->addSelect(getWords("department "), "dataDepartment", getDataListDepartment($_getInitialValue("Department", "", $strDataDepartment), true), array("style" => "width:$strDefaultWidthPx"), "", false, ($ARRAY_DISABLE_GROUP['department'] == ""));
    $f->addSelect(getWords("section"), "dataSection", getDataListSection($_getInitialValue("Section", "", $strDataSection), true), array("style" => "width:$strDefaultWidthPx"), "", false, ($ARRAY_DISABLE_GROUP['section'] == ""));
    $f->addSelect(getWords("sub section"), "dataSubSection", getDataListSubSection($_getInitialValue("SubSection", "", $strDataSubSection), true), array("style" => "width:$strDefaultWidthPx"), "", false, ($ARRAY_DISABLE_GROUP['sub_section'] == ""));

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
  $tbsPage->LoadTemplate('../templates/master2.html') ;//$strMainTemplate
  $tbsPage->Show() ;
//--------------------------------------------------------------------------------

?>