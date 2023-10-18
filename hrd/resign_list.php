<?php
include_once '../global/email_func.php';
include_once('../global/session.php');
include_once('global.php');
include_once('form_object.php');
include_once('../global/common_data.php');
include_once('../classes/datagrid_modified.php');
include_once('../includes/form2/form2.php');
include_once('../classes/hrd/hrd_employee_mutation.php');
include_once('../classes/hrd/hrd_employee_mutation_resign.php');
//include_once("../includes/krumo/class.krumo.php");
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
                <td colspan=17>";

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
$dataPrivilege = getDataPrivileges("resign_edit.php", $bolCanView, $bolCanEdit, $bolCanDelete, $bolCanApprove, $bolCanCheck, $bolCanAcknowledge, $bolCanApprove2);
if (!$bolCanView)
    die(accessDenied($_SERVER['HTTP_REFERER']));

  $strWordsNew      = getWords("new");
  $strWordsDenied   = getWords("denied");
  $strWordsChecked  = getWords("checked");
  $strWordsApproved = getWords("approved");
  $strWordsFinished = getWords("finished");
  $strWordsApproved2 = getWords("approved 2");
//  $strWordsVerified = getWords("verified");
$DataGrid = "";
$formFilter = "";


//DAFTAR FUNGSI--------------------------------------------------------------------------------------------------------------
function getData($db) {
    global $dataPrivilege;
    global $f;
    global $myDataGrid;
    global $DataGrid;

    global $strKriteriaCompany;
    //$bolCanApprove2 = isEligibleApprove2($db);
    //global $arrUserInfo;

    $arrData = $f->getObjectValues();

    $strKriteria = "";
    // GENERATE CRITERIA

    if (validStandardDate($strDateFrom = $arrData['dataDateFrom']) && validStandardDate($strDateThru = $arrData['dataDateThru'])) {
        $strKriteria .= "AND (t3.resign_date BETWEEN '$strDateFrom' AND '$strDateThru') ";
    }
    if ($arrData['dataEmployee'] != "") {
        $strKriteria .= "AND t2.employee_id = '" . $arrData['dataEmployee'] . "' ";
    }
    if ($arrData['dataPosition'] != "") {
        $strKriteria .= "AND t2.position_code = '" . $arrData['dataPosition'] . "' ";
    }
    if ($arrData['dataBranch'] != "") {
        $strKriteria .= "AND t2.branch_code = '" . $arrData['dataBranch'] . "' ";
    }
    if ($arrData['dataGrade'] != "") {
        $strKriteria .= "AND t2.grade_code = '" . $arrData['dataGrade'] . "' ";
    }
    if ($arrData['dataEmployeeStatus'] != "") {
        $strKriteria .= "AND t2.employee_status = '" . $arrData['dataEmployeeStatus'] . "' ";
    }
    if ($arrData['dataActive'] != "") {
        $strKriteria .= "AND t2.active = '" . $arrData['dataActive'] . "' ";
    }
    if ($arrData['dataRequestStatus'] != "") {
        $strKriteria .= "AND t1.status = '" . $arrData['dataRequestStatus'] . "' ";
    }
    if ($arrData['dataDivision'] != "") {
        $strKriteria .= "AND t2.division_code = '" . $arrData['dataDivision'] . "' ";
    }
    if ($arrData['dataDepartment'] != "") {
        $strKriteria .= "AND t2.department_code = '" . $arrData['dataDepartment'] . "' ";
    }
    if ($arrData['dataSection'] != "") {
        $strKriteria .= "AND t2.section_code = '" . $arrData['dataSection'] . "' ";
    }
    if ($arrData['dataSubSection'] != "") {
        $strKriteria .= "AND t2.sub_section_code = '" . $arrData['dataSubSection'] . "' ";
    }
    $strKriteria .= $strKriteriaCompany;

    if ($db->connect()) {
        $myDataGrid = new cDataGrid2("formData", "DataGrid1", "100%", "100%", false, true, false);
        $myDataGrid->caption = getWords(strtoupper(vsprintf(getWords("list of %s"), getWords($dataPrivilege['menu_name']))));
        $myDataGrid->setAJAXCallBackScript(basename($_SERVER['PHP_SELF']));
        // $myDataGrid->setCriteria($strKriteria);

        // $myDataGrid->pageSortBy = "proposal_date";
        // $myDataGrid->sortOrder = "ASC";
        if (!isset($_REQUEST['btnExportXLS']))
            $myDataGrid->addColumnCheckbox(new DataGrid_Column("chkID", "id", array('width' => '30'), array('align' => 'center', 'nowrap' => '')), true /* bolDisableSelfStatusChange */);

        $myDataGrid->addColumnNumbering(new DataGrid_Column("No", "", array('width' => '30'), array('nowrap' => '')));
        $myDataGrid->addColumn(new DataGrid_Column(getWords("created"), "proposal_date", "", array('nowrap' => '')));
        $myDataGrid->addColumn(new DataGrid_Column(getWords("resign date"), "resign_date", "", array('nowrap' => '')));
        // $myDataGrid->addColumn(new DataGrid_Column(getWords("id employee"), "id_employee", "", array('nowrap' => '')));
        $myDataGrid->addColumn(new DataGrid_Column(getwords("n i k"), "employee_id", "", array('nowrap' => '')));
        $myDataGrid->addColumn(new DataGrid_Column(getwords("n i k corporate"), "employee_id_2", "", array('nowrap' => '')));
        $myDataGrid->addColumn(new DataGrid_Column(getWords("employee name"), "employee_name", "", array('nowrap' => '')));
        $myDataGrid->addColumn(new DataGrid_Column(getWords("division"), "division_name", "", array('nowrap' => '')));
        $myDataGrid->addColumn(new DataGrid_Column(getWords("department"), "department_name", "", array('nowrap' => '')));
        $myDataGrid->addColumn(new DataGrid_Column(getWords("functional"), "functional_code", "", array('nowrap' => '')));
        $myDataGrid->addColumn(new DataGrid_Column(getWords("branch office"), "branch_code", "", array('nowrap' => '')));
        $myDataGrid->addColumn(new DataGrid_Column(getWords("branch contract"), "branch_penugasan_code", "", array('nowrap' => '')));
        $myDataGrid->addColumn(new DataGrid_Column(getWords("join date"), "join_date", "", array('nowrap' => '')));
        $myDataGrid->addColumn(new DataGrid_Column(getWords("due date"), "due_date", "", array('nowrap' => '')));
        $myDataGrid->addColumn(new DataGrid_Column(getWords("department"), "department_code", "", array('nowrap' => ''), false, false, "", "getDepartmentName()"));
        $myDataGrid->addColumn(new DataGrid_Column(getWords("leave remain"), "leave_remain", "", array('nowrap' => '')));
        $myDataGrid->addColumn(new DataGrid_Column(getWords("meal"), "meal", "", array('nowrap' => '')));
        $myDataGrid->addColumn(new DataGrid_Column(getWords("conjuncture"), "conjuncture", "", array('nowrap' => '')));
        $myDataGrid->addColumn(new DataGrid_Column(getWords("leave allowance"), "leave_allowance1", "", array('nowrap' => '')));
        $myDataGrid->addColumn(new DataGrid_Column(getWords("pesangon"), "pesangon", "", array('nowrap' => '')));
        $myDataGrid->addColumn(new DataGrid_Column(getWords("other right"), "other_right", "", array('nowrap' => '')));
        $myDataGrid->addColumn(new DataGrid_Column(getWords("loan"), "loan", "", array('nowrap' => '')));
        $myDataGrid->addColumn(new DataGrid_Column(getWords("other loan"), "other_loan", "", array('nowrap' => '')));
        $myDataGrid->addColumn(new DataGrid_Column(getWords("other obligation"), "other_obligation", "", array('nowrap' => '')));
        $myDataGrid->addColumn(new DataGrid_Column(getWords("note"), "note", "", array('nowrap' => '')));
        $myDataGrid->addColumn(new DataGrid_Column(getWords("ec status"), "ec_status", "", array('nowrap' => ''), false, false, "", "printECStatus()"));
        $myDataGrid->addColumn(new DataGrid_Column(getWords("status"), "status", "", array('nowrap' => ''), false, false, "", "printRequestStatus()"));
        $myDataGrid->addColumn(new DataGrid_Column(getWords("created by"), "created_by",  "", array('nowrap' => ''), false, false, "", "printUserName()"));
        if ($dataPrivilege['edit'] == 't')
            //$myDataGrid->addColumn(new DataGrid_Column("", "", array("width" => "60"), array('align' => 'center', 'nowrap' => ''), false, false, "", "printGlobalEditLink(" . array("record" => $params) . ")", "", false /* show in excel */));
        $myDataGrid->addColumn(new DataGrid_Column("", "", array("width" => "60"), array('align' => 'center', 'nowrap' => ''), false, false, "","printGlobalEditLink()", "", false /*show in excel*/));
				$myDataGrid->addColumn(new DataGrid_Column("", "", array("width" => "60"), array('align' => 'center', 'nowrap' => ''), false, false, "","printShowLink()", "", false /*show in excel*/));
        foreach ($arrData AS $key => $value) {
            $myDataGrid->strAdditionalHtml .= generateHidden($key, $value, "");
        }

        //tampilkan buttons sesuai dengan otoritas, common_function.php
        generateRoleButtons($dataPrivilege['edit'], $dataPrivilege['delete'], $dataPrivilege['check'], $dataPrivilege['approve'], $dataPrivilege['approve2'], true, $myDataGrid);
        $myDataGrid->addButtonExportExcel("Export Excel", $dataPrivilege['menu_name'] . ".xls", getWords($dataPrivilege['menu_name']));

        if($_SESSION['sessionUserRole'] == ROLE_SUPER){
          $myDataGrid->addSpecialButton("btnECClose", "btnECClose", "submit", getWords("EC Status -> Closed"), "onClick=\"document.formData.target = '_self'\"", "setECStatusClose()");
          $myDataGrid->addSpecialButton("btnECOpen", "btnECOpen", "submit", getWords("EC Status -> Opened"), "onClick=\"document.formData.target = '_self'\"", "setECStatusOpen()");
        }


        $myDataGrid->getRequest();

        $strSQLCOUNT = "
        SELECT COUNT(*) AS total FROM hrd_employee_mutation_resign AS t3
        LEFT JOIN hrd_employee_mutation AS t1 ON t3.id_mutation = t1.id
        LEFT JOIN hrd_employee AS t2 ON t1.id_employee = t2.id";

        $strSQL = "SELECT t3.*, t2.employee_id, t2.employee_name, t2.gender, t2.join_date, t2.id as id_employee,  ";
        $strSQL .= "t2.employee_id_2, t4.division_name, t5.department_name, ";
        $strSQL .= "t2.branch_code, t2.branch_penugasan_code, t2.functional_code, t2.due_date, ";
        $strSQL .= "t1.\"note\", t1.\"id_employee\", t1.proposal_date, t1.\"status\", t3.ec_status, t1.id as idm, ";
        $strSQL .= "t1.approved_by, t1.approved2_by, t1.approved2_time, t1.approved_time, t1.modified as modified_time ";
        $strSQL .= "FROM hrd_employee_mutation_resign AS t3 ";
        $strSQL .= "LEFT JOIN hrd_employee_mutation AS t1 ON t3.id_mutation = t1.id ";
        $strSQL .= "LEFT JOIN hrd_employee AS t2 ON t1.id_employee = t2.id ";
        $strSQL .= "LEFT JOIN hrd_division AS t4 ON t2.division_code = t4.division_code ";
        $strSQL .= "LEFT JOIN hrd_department AS t5 ON t2.department_code = t5.department_code ";
        $strSQL .= "WHERE 1=1 $strKriteria";
        $strSQL .= " ORDER BY t1.proposal_date DESC";


        $myDataGrid->totalData = $myDataGrid->getTotalData($db, $strSQLCOUNT);
        $dataset = $myDataGrid->getData($db, $strSQL);
        //bind Datagrid with array dataset and branchCode
        $myDataGrid->bind($dataset);
        $DataGrid = $myDataGrid->render();
    }
    else
        $DataGrid = "";

    return $DataGrid;
}

// untuk menampilkan info yang mengubah data resign
  function printShowLink($params)
  {
    extract($params);
    global $arrUserList;
    $strResult  = "";
    // tambahkan info record info
    $strDiv  = "<div id='detailRecord$counter' style=\"display:none\">\n";
    $strDiv .= "<strong>" .$record['employee_id']."-".$record['employee_name']."</strong><br>\n";
    $strDiv .= getWords("last modified"). ": ".substr($record['modified_time'], 0,19) ." ";
    $strDiv .= (isset($arrUserList[$record['modified_by']])) ? $arrUserList[$record['modified_by']]['name']."<br>" : "<br>";
/*
    $strDiv .= getWords("verified"). ": ".substr($record['verified_time'], 0,19) ." ";
    $strDiv .= (isset($arrUserList[$record['verified_by']])) ? $arrUserList[$record['verified_by']]['name']."<br>" : "<br>";

    $strDiv .= getWords("checked"). ": ".substr($record['checked_time'], 0,19) ." ";
    $strDiv .= (isset($arrUserList[$record['checked_by']])) ? $arrUserList[$record['checked_by']]['name']."<br>" : "<br>";
*/
    $strDiv .= getWords("approved"). ": ".substr($record['approved2_time'], 0,19) ." ";
    $strDiv .= (isset($arrUserList[$record['approved2_by']])) ? $arrUserList[$record['approved2_by']]['name']."<br>" : "<br>";
/*
    $strDiv .= getWords("approved by director"). ": ".substr($record['dir_approval_time'], 0,19) ." ";
    $strDiv .= (isset($arrUserList[$record['dir_approval_by']])) ? $arrUserList[$record['dir_approval_by']]['name']."<br>" : "<br>";
    $strDiv .= getWords("denied"). ": ".substr($record['denied_time'], 0,19) ." ";
    $strDiv .= (isset($arrUserList[$record['denied_by']])) ? $arrUserList[$record['denied_by']]['name']."<br>" : "<br>";
*/

    $strDiv .= "</div>\n";

    $strResult .= $strDiv."<a href=\"javascript:openViewWindowByContentId('Record Information', 'detailRecord$counter', 400, 150)\" title=\"" .getWords("show record info")."\">" .getWords("show")."</a>";
    
    return $strResult;
  }

/**
 * Fungsi untuk mengubah EC Status
 * @param integer $intStatus EC Status value
 */
function setECStatusOpen()
{
  global $myDataGrid;
  global $db;
  global $_REQUEST;

  $intStatus = "";

  foreach ($myDataGrid->checkboxes as $strValue)
  {
    $strSQL  = "UPDATE hrd_employee_mutation_resign SET ec_status = 0 WHERE id = $strValue;";
    $resDb = $db->execute($strSQL);
    $strSQL2 = "SELECT id_employee FROM hrd_employee_mutation AS t1 LEFT JOIN hrd_employee_mutation_resign AS t2 ON t2.id_mutation = t1.id WHERE t2.id = $strValue;";
    $resDb2 = $db->execute($strSQL2);
    if($rowDb2 = $db->fetchrow($resDb2)){
      $strSQL3 = "UPDATE hrd_employee SET ec_status = 0 WHERE id = ".$rowDb2['id_employee'].";";
      $resDb3 = $db->execute($strSQL3);
    }
  }
}

/**
 * Fungsi untuk mengubah EC Status
 * @param integer $intStatus EC Status value
 */
function setECStatusClose()
{
  global $myDataGrid;
  global $db;
  global $_REQUEST;

  $intStatus = "";

  foreach ($myDataGrid->checkboxes as $strValue)
  {
    $strSQL  = "UPDATE hrd_employee_mutation_resign SET ec_status = 1 WHERE id = $strValue;";
    $resDb = $db->execute($strSQL);
    $strSQL2 = "SELECT id_employee FROM hrd_employee_mutation AS t1 LEFT JOIN hrd_employee_mutation_resign AS t2 ON t2.id_mutation = t1.id WHERE t2.id = $strValue;";
    $resDb2 = $db->execute($strSQL2);
    if($rowDb2 = $db->fetchrow($resDb2)){
      $strSQL3 = "UPDATE hrd_employee SET ec_status = 1 WHERE id = ".$rowDb2['id_employee'].";";
      $resDb3 = $db->execute($strSQL3);
    }
  }
}

/**
 * Untuk memprint Status EC Open/CLose
 * @param  integer $params EC Status code
 * @return string  EC Status Name
 */
function printECStatus($params)
{
  extract($params);
  if ($value == '1')
    return "Close";
  else
    return "Open";
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
    $strID = "";
    $strmodified_byID = $_SESSION['sessionUserID'];


    $strUpdate = getStatusUpdateString($intStatus);

    foreach ($_REQUEST as $strIndex => $strValue)
    {
      if (substr($strIndex,0,15) == 'DataGrid1_chkID')
      {
        $strSQLx = "SELECT id_mutation FROM hrd_employee_mutation_resign
                    WHERE id = '$strValue' ";
        $resDb = $db->execute($strSQLx);
        if ($rowDb = $db->fetchrow($resDb))
        {
           $strSQLx = "SELECT status, employee_name, t1.proposal_date, t1.id, t1.id_employee
                    FROM hrd_employee_mutation_resign AS t3
                    LEFT JOIN hrd_employee_mutation AS t1 ON t3.id_mutation = t1.id
                    LEFT JOIN hrd_employee AS t2 ON t1.id_employee = t2.id
                    WHERE t1.id = '".$rowDb['id_mutation']."'";
        $resDb = $db->execute($strSQLx);
        if ($rowDb = $db->fetchrow($resDb))
        {
          $strBody = "";
          $strBody.= "Name: ".getEmployeeNameEmail($rowDb['id_employee'])."<br>";
          $strBody.= "Proposal Date: ".$rowDb['proposal_date']."<br>";
          $strSubject = getSubject($intStatus,'Severance Proposal',$strmodified_byID);
       //the status should be increasing
          //if (isProcessable($rowDb['status'], $intStatus))
        if (($intStatus==-1)||(($rowDb['status']<$intStatus)&&($rowDb['status'] != -1)))
          {
            $strECStatus = ($rowDb2['ec_status'] == 0) ? "Open" : "Close";
            $strBody .= "EC Status: ".$strECStatus."<br>";
            $strBody .= "Resign Reason: ".$rowDb2['note']."<br>";
            $strBody =  getBody($intStatus,'Absence',$strBody,$strmodified_byID);
            sendMail($strSubject,$strBody);
            $strSQL .= "UPDATE hrd_employee_mutation SET $strUpdate status = '$intStatus'  ";
            $strSQL .= "WHERE id = '".$rowDb['id']."'; ";
           writeLog(ACTIVITY_EDIT, MODULE_EMPLOYEE, $rowDb['employee_name']." - ". $rowDb['proposal_date'] ." - ". $rowDb['resign_date'], $intStatus);


          }
        if ($rowDb['status']< 6 && $intStatus == 6)
            {
             $strSQL2 = "SELECT t1.note, t1.id_employee, t2.ec_status, t2.resign_date
                         FROM hrd_employee_mutation AS t1
                         LEFT JOIN hrd_employee_mutation_resign AS t2 ON t2.id_mutation = t1.id ";
             $strSQL2.= "WHERE t1.id = '".$rowDb['id']."'; ";
             $resDb2 = $db->execute($strSQL2);
             if($rowDb2 = $db->fetchrow($resDb2))
            {
                $strECStatus = ($rowDb2['ec_status'] == 0) ? "Open" : "Close";
                $strBody .= "EC Status: ".$strECStatus."<br>";
                $strBody .= "Resign Reason: ".$rowDb2['note']."<br>";
                $strBody =  getBody($intStatus,'Absence',$strBody,$strmodified_byID);
                sendMail($strSubject,$strBody);
                $strNote = $rowDb2['note'];
                $strId = $rowDb2['id_employee'];
                $strECStatus = $rowDb2['ec_status'];
                $strResignDate = $rowDb2['resign_date'];
                $strSQL2 = "UPDATE hrd_employee SET resign_reason = '$strNote' ,ec_status = $strECStatus ,active = 0, resign_date = '$strResignDate' WHERE id = $strId";
               $db->execute($strSQL2);
            }
        }
      }
        }
      }

      $resExec = $db->execute($strSQL);


    }

  } //changeStatus



// fungsi untuk menghapus data
function deleteData() {
    global $myDataGrid;
    global $db;

    $arrKeys = array();
    foreach ($myDataGrid->checkboxes as $strValue) {
        $arrKeys['id'][] = $strValue;

        //OPEN THIS COMMENT IF ONLY NEEDED!!!

        $strSQL = "SELECT id_employee FROM hrd_employee_mutation_resign as t0 LEFT JOIN hrd_employee_mutation as t1 ON t1.id = t0.id_mutation WHERE t0.id = $strValue ;";
        //
        $resDb = $db->execute($strSQL);
        if($rowDb = $db->fetchrow($resDb)){
	        $strSQL2 = "UPDATE hrd_employee SET resign_date = null, active = 1, ec_status = null, resign_reason = '' WHERE id = ".$rowDb['id_employee'].";";
	        $resDb2 = $db->execute($strSQL2);
        }

    }
    $tblResign = new cHrdEmployeeMutationResign();
    $tblResign->deleteMultiple($arrKeys);
    writeLog(ACTIVITY_DELETE, MODULE_PAYROLL, $arrKeys['id']);

    $myDataGrid->message = $tblResign->strMessage;
}

//deleteData
//----MAIN PROGRAM -----------------------------------------------------
$db = new CdbClass;
if ($db->connect()) {

    getUserEmployeeInfo();
    $arrUserList = getAllUserInfo($db);
    if (isset($_POST['btnShowAlert']) && $_POST['btnShowAlert'] == 1) {
      $dtFrom = date("Y-m-")."25";
      $dtFrom = getNextDateNextMonth($dtFrom, -1);
      $dtThru = date("Y-m-")."24";
        $reqStatus = 0;
        $_SESSION["sessiondataEmployee"] = "";
        $_SESSION["sessiondataPosition"] = "";
        $_SESSION["sessiondataSalaryGrade"] = "";
        $_SESSION["sessiondataEmployeeStatus"] = "";
        $_REQUEST["sessiondataEmployeeStatus"] = "";
        echo $_SESSION["sessiondataEmployeeStatus"];
    } else {
      $dtFrom = date("Y-m-")."25";
      $dtFrom = getNextDateNextMonth($dtFrom, -1);
      $dtThru = date("Y-m-")."24";
        $reqStatus = null;
    }
    $strDataID = getPostValue('dataID');
    scopeData($strDataEmployee, $strDataSubSection, $strDataSection, $strDataDepartment, $strDataDivision, $_SESSION['sessionUserRole'], $arrUserInfo);
    $f = new clsForm("formFilter", 3, "100%", "");
    $f->caption = strtoupper($strWordsFILTERDATA);
    $f->addInput(getWords("date from"), "dataDateFrom", getInitialValue("DateFrom", $dtFrom, $dtFrom), array("style" => "width:$strDateWidth"), "date", false, true, true);
    $f->addInput(getWords("date thru"), "dataDateThru", getInitialValue("DateThru", $dtThru, $dtThru), array("style" => "width:$strDateWidth"), "date", false, true, true);
    $f->addInputAutoComplete(getWords("employee"), "dataEmployee", getDataEmployee(getInitialValue("Employee", null, $strDataEmployee)), "style=width:$strDefaultWidthPx " . $strEmpReadonly, "string", false);
    $f->addLabelAutoComplete("", "dataEmployee", "");
    $f->addSelect(getWords("request status"), "dataRequestStatus", getDataListRequestStatus(getInitialValue("RequestStatus", $reqStatus, $reqStatus), true, array("value" => "", "text" => "", "selected" => true)), array("style" => "width:$strDefaultWidthPx"), "", false);
    $f->addSelect(getWords("branch"), "dataBranch", getDataListBranch(getInitialValue("Branch"), true), array("style" => "width:$strDefaultWidthPx"), "", false, ($ARRAY_DISABLE_GROUP['branch'] == ""));

    $f->addSelect(getWords("level"), "dataPosition", getDataListPosition(getInitialValue("Position"), true), array("style" => "width:$strDefaultWidthPx"), "", false);
    $f->addSelect(getWords("grade"), "dataGrade", getDataListSalaryGrade(getInitialValue("Grade"), true), array("style" => "width:$strDefaultWidthPx"), "", false);

    $f->addSelect(getWords("status"), "dataEmployeeStatus", getDataListEmployeeStatus(getInitialValue("EmployeeStatus"), true, array("value" => "", "text" => "", "selected" => true)), array("style" => "width:$strDefaultWidthPx"), "", false);

    $f->addSelect(getWords("active"), "dataActive", getDataListEmployeeActive(getInitialValue("Active"), true, array("value" => "", "text" => "", "selected" => true)), array("style" => "width:$strDefaultWidthPx"), "", false);

    $f->addLiteral("", "", "");
    $f->addSelect(getWords("company"), "dataCompany", getDataListCompany($strDataCompany, $bolCompanyEmptyOption, $arrCompanyEmptyData, $strKriteria2), array("style" => "width:$strDefaultWidthPx"), "", false);

    $f->addSelect(getWords("division"), "dataDivision", getDataListDivision(getInitialValue("Division", "", $strDataDivision), true), array("style" => "width:$strDefaultWidthPx"), "", false, ($ARRAY_DISABLE_GROUP['division'] == ""));
    $f->addSelect(getWords("department "), "dataDepartment", getDataListDepartment(getInitialValue("Department", "", $strDataDepartment), true), array("style" => "width:$strDefaultWidthPx"), "", false, ($ARRAY_DISABLE_GROUP['department'] == ""));
    $f->addSelect(getWords("section"), "dataSection", getDataListSection(getInitialValue("Section", "", $strDataSection), true), array("style" => "width:$strDefaultWidthPx"), "", false, ($ARRAY_DISABLE_GROUP['section'] == ""));
    $f->addSelect(getWords("sub section"), "dataSubSection", getDataListSubSection(getInitialValue("SubSection", "", $strDataSubSection), true), array("style" => "width:$strDefaultWidthPx"), "", false, ($ARRAY_DISABLE_GROUP['sub_section'] == ""));

    $f->addSubmit("btnShow", getWords("show"), "", true, true, "", "", "");

    $formFilter = $f->render();
    getData($db);
}
$tbsPage = new clsTinyButStrong;

//write this variable in every page
$strPageTitle = $dataPrivilege['menu_name'];
$pageIcon = "../images/icons/" . $dataPrivilege['icon_file'];
$strTemplateFile = getTemplate(str_replace(".php", ".html", basename($_SERVER['PHP_SELF'])));
//------------------------------------------------
//Load Master Template

$tbsPage->LoadTemplate('../templates/master2.html');
$tbsPage->Show();
//--------------------------------------------------------------------------------
?>
