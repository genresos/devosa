<?php
  include_once('../global/session.php');
  include_once('global.php');
  include_once('../global/common_data.php');
  include_once('../classes/datagrid_modified.php');
  include_once('../includes/form2/form2.php');
  include_once('../includes/datagrid2/datagrid.php');
  include_once('cls_employee.php');
include_once("../includes/krumo/class.krumo.php");
  $dataPrivilege = getDataPrivileges(basename($_SERVER['PHP_SELF']), $bolCanView, $bolCanEdit, $bolCanDelete, $bolCanApprove);
  if (!$bolCanView) die(getWords('view denied'));

  $db = new CdbClass;
  $db->connect();
  $arrUserList = getAllUserInfo($db);//ambil semua info user]
  $strDataID = getPostValue('dataID');
  if (isset($_POST['dataStatus'])){
      $_POST['status'] = getPostValue('dataStatus');
  }
  $isNew = ($strDataID == "");

  //if ($bolCanEdit)
  //{
    $f = new clsForm("form1", 2, "100%", "");
    $f->disableFormTag();
    $f->showCaption = false;

    $f->addHidden("dataID", $strDataID);
    $f->addFieldSet(getWords("search criteria"), 2);
    $f->addInput(getWords("employee name"), "employee_name", "", array("size" => 20, "maxlength" => 20), "string", true, true, true);
    $f->addSelect(getWords("branch"), "dataBranch", getDataListBranch(getInitialValue("Branch"), true), array("style" => "width:$strDefaultWidthPx"), "", false);
    $f->addInput(getWords("position"), "position_code", "", array("size" => 30), "string");
    $f->addSelect(getWords("grade"), "dataGrade", getDataListSalaryGrade(getInitialValue("Grade"), true), array("style" => "width:$strDefaultWidthPx"), "", false);
		
    $arrFKRStatus = array("" => "", REQUEST_STATUS_NEW => "new", REQUEST_STATUS_APPROVED => "approved");
    $f->addSelect(getWords("status"), "status", $arrFKRStatus, array(), "string", false);
		
		$f->addSelect(getWords("company"), "id_company", getDataListCompany($strDataCompany, $bolCompanyEmptyOption, $arrCompanyEmptyData, $strKriteria2), array("style" => "width:$strDefaultWidthPx"), "", false);
	  $f->addSelect(getWords("division"), "division_code", getDataListDivision($strDataDivision, true), array("style" => "width:$strDefaultWidthPx"), "", false, ($ARRAY_DISABLE_GROUP['division'] == ""));
	  $f->addSelect(getWords("department"), "department_code", getDataListDepartment($strDataDepartment, true), array("style" => "width:$strDefaultWidthPx"), "", false, ($ARRAY_DISABLE_GROUP['department'] == ""));
	  $f->addSelect(getWords("section"), "section_code", getDataListDepartment($strDataSection, true), array("style" => "width:$strDefaultWidthPx"), "", false, ($ARRAY_DISABLE_GROUP['department'] == ""));
	  $f->addSelect(getWords("sub section"), "sub_section_code", getDataListDepartment($strDataSubSection, true), array("style" => "width:$strDefaultWidthPx"), "", false, ($ARRAY_DISABLE_GROUP['department'] == ""));
		$f->addSelect(getWords("created by"), "created_by", getDataListUser(), array("style" => "width:$strDefaultWidthPx"), "", false);
		
    $f->addSubmit("btnSearch", getWords("search"), array("onClick" => "javascript:doSearch()"), true, true, "", "", "");
    $f->addSubmit("btnPrint", getWords("print"), array("onClick" => "javascript:printList()"), true, true, "", "", "");
    $f->addSubmit("btnExportXLS", getWords("excel"), array("onClick" => "javascript:exportExcel()"), true, true, "", "", "");

    $formInput = $f->render();

    // handle jika ada permintaan pembuatan data karyawan dari FKR
    if (isset($_REQUEST['btnCreateEmployee']) && isset($_REQUEST['dataID']))
    {
    	$bolOK = generateEmployeeFromFKR($db);
	  
	  // if($bolOK)
	  // echo "sadas";
    }
  //}
  //else
  //  $formInput = "";

  $bolPrint = false;
  $bolExcel = false;
  if (isset($_POST['btnPrint']))
    $bolPrint = true;
  if (isset($_POST['btnExportXLS']))
    $bolExcel = true;
	class cDataGrid2 extends cDataGridNew
  {
    /*override this function*/
    function printOpeningRow($intRows, $rowDb)
    {
      $strResult = "";
      $strClass = getCssClass($rowDb['status_flag']);
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
  $myDataGrid = new cDataGrid2("form1","DataGrid1", "100%", "100%", !($bolPrint || $bolExcel), true, true);
  $myDataGrid->disableFormTag();
  $myDataGrid->caption = getWords("FKR");
  $myDataGrid->pageSortBy = 'id DESC';
  //$myDataGrid->setAJAXCallBackScript(basename($_SERVER['PHP_SELF']));

  if ($bolPrint || $bolExcel){
  	$myDataGrid->addColumnNumbering(new DataGrid_Column("No", "", array("rowspan" => 2, 'width'=>30), array('nowrap'=>'')));
    $myDataGrid->addColumn(new DataGrid_Column(getWords("name"), "employee_name", array("rowspan" => 2, 'width' => 150), array('nowrap' => 'nowrap'), true, true, "", "", "string", true, 32));
    $myDataGrid->addColumn(new DataGrid_Column(strtoupper(getWords("mrf no")), "mrf_no", array("rowspan" => 2, 'width' => 150), array('nowrap' => 'nowrap'), false, false, "", "", "string", true, 12));
  }else{
    $myDataGrid->addColumnCheckbox(new DataGrid_Column("chkID", "id", array("rowspan" => 2, 'width' => 30), array('align'=>'center', 'nowrap' => 'nowrap')));
    $myDataGrid->addColumnNumbering(new DataGrid_Column("No", "", array("rowspan" => 2, 'width'=>30), array('nowrap'=>'')));
    $myDataGrid->addColumn(new DataGrid_Column(getWords("name"), "employee_name", array("rowspan" => 2, "width" => 150), array('nowrap' => 'nowrap'), true, true, "", "printViewLink()", "string", true, 32));
    $myDataGrid->addColumn(new DataGrid_Column(strtoupper(getWords("mrf no")), "mrf_no", array("rowspan" => 2, 'width' => 150), array('nowrap' => 'nowrap'), false, false, "", "", "string", true, 12));
  }
  $myDataGrid->addColumn(new DataGrid_Column(getWords("employee category"), "position_code", array("rowspan" => 2, "width" => 100), array(), false, false, "", "", "string", true, 12));
  $myDataGrid->addColumn(new DataGrid_Column(getWords("position"), "functional_code", array("rowspan" => 2, "width" => 100), array(), false, false, "", "", "string", true, 12));
  $myDataGrid->addColumn(new DataGrid_Column(getwords("n i k"), "employee_id", array("rowspan" => 2, "width" => 150), array('nowrap' => 'nowrap'), false, false, "", "", "string", true, 12));
  $myDataGrid->addColumn(new DataGrid_Column(getWords("family"), "family_status_code", array("rowspan" => 2, "width" => 50), array(), false, false, "", "", "string", true, 6));
  $myDataGrid->addColumn(new DataGrid_Column(getWords("status"), "employee_status", array("rowspan" => 2, "width" => 50), array('nowrap' => 'nowrap'), false, false, "", "printEmployeeStatus1()", "string", true, 6));
  $myDataGrid->addColumn(new DataGrid_Column(getWords("join date"), "join_date", array("rowspan" => 2, "width" => 50), array(), false, false, "", "", "string", true, 6));
  $myDataGrid->addColumn(new DataGrid_Column(getWords("band"), "salary_grade_code", array("rowspan" => 2, "width" => 50), array(), false, false, "", "", "string", true, 6));
  $myDataGrid->addColumn(new DataGrid_Column(getWords("organization structure"), "", array("colspan" => 5), array('nowrap' => 'nowrap'), true, false, "", "", "string", true));
  $myDataGrid->addColumn(new DataGrid_Column(getWords("company"), "id_company", array("width" => 100), array('nowrap' => 'nowrap'), false, false, "", "", "string", true, 12));
  $myDataGrid->addColumn(new DataGrid_Column(getWords("division"), "division_name", array("width" => 100), array('nowrap' => 'nowrap'), true, false, "", "", "string", true, 12));
  $myDataGrid->addColumn(new DataGrid_Column(getWords("department"), "department_name", array("width" => 100), array('nowrap' => 'nowrap'), true, false, "", "", "string", true, 12));
  $myDataGrid->addColumn(new DataGrid_Column(getWords("section"), "section_name", array("width" => 100), array('nowrap' => 'nowrap'), true, false, "", "", "string", true, 12));
	$myDataGrid->addColumn(new DataGrid_Column(getWords("sub section"), "sub_section_name", array("width" => 100), array('nowrap' => 'nowrap'), true, false, "", "", "string", true, 12));
	
	$myDataGrid->addColumn(new DataGrid_Column(getWords("branch office"), "branch_code", array("rowspan" => 2, "width" => 120), array("nowrap" => "nowrap"), true, true, "", "", "string", true, 16));
  $myDataGrid->addColumn(new DataGrid_Column(getWords("branch contract"), "branch_contract_code", array("rowspan" => 2, "width" => 120), array("nowrap" => "nowrap"), true, true, "", "", "string", true, 16));
  $myDataGrid->addColumn(new DataGrid_Column(getWords("status"), "status", array("rowspan" => 2, "width" => 60), array('nowrap' => 'nowrap'), true, false, "", "", "string", true, 12));
  $myDataGrid->addColumn(new DataGrid_Column(getWords("note"), "note", array("rowspan" => 2), array('nowrap' => 'nowrap'), true, true, "", "", "string", true, 12));
	$myDataGrid->addColumn(new DataGrid_Column(getWords("created by"), "created_by", array("rowspan" => 2), array("nowrap" => "nowrap"), false, false, "", "", "string", true, 30));
  if (!($bolPrint || $bolExcel))
  {
    if ($bolCanEdit)
    {
      $myDataGrid->addColumn(new DataGrid_Column("", "", array("rowspan" => 2, 'width' => 45), array('align' => 'center', 'nowrap' => 'nowrap'), false, false, "","printEditLink()", "string", false));
      $myDataGrid->addColumn(new DataGrid_Column("", "", array("rowspan" => 2, 'width' => 45), array('align' => 'center', 'nowrap' => 'nowrap'), false, false, "","printPrintLink()", "string", false));
      $myDataGrid->addColumn(new DataGrid_Column(getWords("employee"), "", array("rowspan" => 2, 'width' => 45), array('align' => 'center', 'nowrap' => 'nowrap'), false, false, "","printEmployeeLink()", "string", false));
      $myDataGrid->addColumn(new DataGrid_Column("", "", array("rowspan" => 2), array('align' => 'center', 'nowrap' => 'nowrap'), false, false, "","printShowLink()", "string", false));
    }
  }
  /*if ($bolCanDelete)
    $myDataGrid->addSpecialButton("btnDelete","btnDelete","submit", getWords("delete"),"onClick=\"javascript:return myClient.confirmDelete();\"","deleteData()");
  if ($bolCanApprove && $objUP->isManagerHR())
  {
    $myDataGrid->addSpecialButton("btnApprove","btnApprove","submit", getWords("approve"),"onClick=\"\"","approveData()");
    $myDataGrid->addSpecialButton("btnUnApprove","btnUnApprove","submit", getWords("unapprove"),"onClick=\"\"","unApproveData()");
  }*/
	generateRoleButtons($dataPrivilege['edit'], $dataPrivilege['delete'], $dataPrivilege['check'], $dataPrivilege['approve'], $dataPrivilege['approve2'], true, $myDataGrid);
  $myDataGrid->getRequest();
  //--------------------------------
  //get Data and set to Datagrid's DataSource by set the data binding (bind method)

  $tblComp = new cModel("hrd_company", "Company");
  $arrComp = $tblComp->findAll("", "id, company_name, company_code", "", null, 1, "id");
  $tblDiv = new cModel("hrd_division", "Division");
  $arrDiv = $tblDiv->findAll("", "id, division_code, division_name", "", null, 1, "division_code");
  $tblDep = new cModel("hrd_department", "Department");
  $arrDep = $tblDep->findAll("", "id, department_code, department_name", "", null, 1, "department_code");
  $tblSec = new cModel("hrd_section", "Section");
  $arrSec = $tblSec->findAll("", "id, section_code, section_name", "", null, 1, "section_code");
	$tblSubSec = new cModel("hrd_sub_section", "Sub Section");
  $arrSubSec = $tblSubSec->findAll("", "id, sub_section_code, sub_section_name", "", null, 1, "sub_section_code");

  $tbl = new cModel("hrd_fkr", "FKR");

  $arrCriteria = array();
  if ($f->getValue('employee_name') != '') $arrCriteria[] = "upper(employee_name) LIKE '%".strtoupper($f->getValue('employee_name'))."%'";
  if ($f->getValue('status') != '' || getPostValue('dataStatus') != '') {
      $statusFkr = $f->getValue('status');
      if (getPostValue('dataStatus') != ''){
          $statusFkr = getPostValue('dataStatus');
      }
      $arrCriteria[] = "status =".intval($statusFkr);
  }
  if ($f->getValue('position_code') != '') $arrCriteria[] = "upper(position_code) LIKE '%".strtoupper($f->getValue('position_code'))."%'";
  
  if ($f->getValue('dataBranch') != '') $arrCriteria[] = "upper(branch_code) LIKE '%".strtoupper($f->getValue('dataBranch'))."%'";
  if ($f->getValue('dataGrade') != '') $arrCriteria[] = "upper(salary_grade_code) LIKE '%".strtoupper($f->getValue('dataGrade'))."%'";
  if ($f->getValue('id_company') != '') $arrCriteria[] = "id_company = '".$f->getValue('id_company')."'";
	if ($f->getValue('division_code') != '') $arrCriteria[] = "upper(division_code) LIKE '%".strtoupper($f->getValue('division_code'))."%'";
	if ($f->getValue('department_code') != '') $arrCriteria[] = "upper(department_code) LIKE '%".strtoupper($f->getValue('department_code'))."%'";
	if ($f->getValue('section_code') != '') $arrCriteria[] = "upper(section_code) LIKE '%".strtoupper($f->getValue('section_code'))."%'";
	if ($f->getValue('sub_section_code') != '') $arrCriteria[] = "upper(sub_section_code) LIKE '%".strtoupper($f->getValue('sub_section_code'))."%'";
	if ($f->getValue('created_by') != '') $arrCriteria[] = "created_by = '".$f->getValue('created_by')."'";
  $strCriteria = implode(" AND ", $arrCriteria);
  if ($strCriteria != "") $strCriteria = " AND ".$strCriteria;

  // tambah kriteria berdasarkan band
  /*
  $strBandList = getBandAccessCriteria();
  if ($strBandList == "") $strCriteria .= "AND salary_grade_code = '' ";
  else if ($strBandList == "all") $strCriteria .= "";
  else $strCriteria .= "AND salary_grade_code IN ($strBandList) ";
  */
  // tambah kriteria untuk data karyawan

  $strCriteria .= $objUP->genFilterCompany(0);
  $strCriteria .= $objUP->genFilterDivision();

  $strCriteriaFlag = $myDataGrid->getCriteria().$strCriteria;


  $myDataGrid->totalData = $tbl->findCount($strCriteriaFlag);
  if ($bolExcel)
  {
    $myDataGrid->DATAGRID_RENDER_OUTPUT = DATAGRID_RENDER_EXCEL_BIFF;
    $myDataGrid->strFileNameXLS = "FKR_result.xls";
    $myDataGrid->strTitle1 = "List of Form Kesepakatan Remunerasi";
    $myDataGrid->strTitle2 = "Printed Date: ".date("d/m/Y h:i:s");

    $strPageLimit = null;
    $strPageNumber = null;
  }
  elseif ($bolPrint)
  {
    $myDataGrid->DATAGRID_RENDER_OUTPUT = DATAGRID_RENDER_PRINT_HTML;
    $myDataGrid->strTitle1 = "List of Form Kesepakatan Remunerasi";
    $myDataGrid->strTitle2 = "Printed Date: ".date("d/m/Y h:i:s");

    $strPageLimit = null;
    $strPageNumber = null;
  }
  else
  {
    $strPageLimit = $myDataGrid->getPageLimit();
    $strPageNumber = $myDataGrid->getPageNumber();
  }
  $dataset = $tbl->findAll($strCriteriaFlag,
                           null, //all field
                           $myDataGrid->getSortBy(),
                           $strPageLimit,
                           $strPageNumber);
  $tblRN = new cModel("hrd_recruitment_need"); 
  foreach($dataset as &$rowDb)
  {
  	$rowDb['status_flag'] = $rowDb['status'];
    if ($rowDb['status'] == REQUEST_STATUS_APPROVED) $rowDb['status'] = getWords("approved");
    else if ($rowDb['status'] == REQUEST_STATUS_NEW) $rowDb['status'] = getWords("new");
    else if ($rowDb['status'] == REQUEST_STATUS_CHECKED) $rowDb['status'] = getWords("checked");
    else if ($rowDb['status'] == REQUEST_STATUS_APPROVED_2) $rowDb['status'] = getWords("approved 2");
    else if ($rowDb['status'] == REQUEST_STATUS_DENIED) $rowDb['status'] = getWords("denied");
    else $rowDb['status'] = "";
		$rowDb['mrf_no'] = '-';
		$rowDb['branch_code'] = '-';
		$rowDb['branch_contract_code'] = '-';
		if (!empty($rowDb['id_recruitment_need'])){
			$mrfData = $tblRN->findById($rowDb['id_recruitment_need']);
			$rowDb['mrf_no'] = $mrfData['request_number'];	
			$rowDb['branch_code'] = !empty($mrfData['branch_code']) ? $mrfData['branch_code'] : '-';
			$rowDb['branch_contract_code'] = !empty($mrfData['branch_contract_code']) ? $mrfData['branch_contract_code'] : '-';
			$rowDb['functional_code'] = !empty($mrfData['functional_code']) ? $mrfData['functional_code'] : '-';
		}
		if (isset($arrUserList[$rowDb['created_by']])){
			$rowDb['created_by'] = $arrUserList[$rowDb['created_by']]['name'];
		}else{
			$rowDb['created_by'] = "";
		}
    $rowDb['note'] = nl2br($rowDb['note']);
    $rowDb['join_date'] = pgDateFormat($rowDb['join_date'], "d-M-y");
    $rowDb['division_name'] = $rowDb['division_code'];
    $rowDb['department_name'] = $rowDb['department_code'];
    $rowDb['section_name'] = $rowDb['section_code'];
    $rowDb['sub_section_name'] = $rowDb['sub_section_code'];
    if (isset($arrComp[$rowDb['id_company']])) $rowDb['id_company'] = $arrComp[$rowDb['id_company']]['company_name'];
    if (isset($arrDiv[$rowDb['division_code']])) $rowDb['division_name'] = $arrDiv[$rowDb['division_code']]['division_name'];
    if (isset($arrDep[$rowDb['department_code']])) $rowDb['department_name'] = $arrDep[$rowDb['department_code']]['department_name'];
    if (isset($arrSec[$rowDb['section_code']])) $rowDb['section_name'] = $arrSec[$rowDb['section_code']]['section_name'];
    if (isset($arrSubSec[$rowDb['sub_section_code']])) $rowDb['sub_section_name'] = $arrSubSec[$rowDb['sub_section_code']]['sub_section_name'];
  }
  //bind Datagrid with array dataset
  $myDataGrid->bind($dataset);
  $DataGrid = $myDataGrid->render();

  $strConfirmDelete = getWords("are you sure to delete this selected data?");
  $strConfirmSave = getWords("do you want to save this entry?");


  $tbsPage = new clsTinyButStrong ;

  //write this variable in every page
  $strPageTitle = getWords($dataPrivilege['menu_name']);
  $pageIcon = "../images/icons/".$dataPrivilege['icon_file'];
  $strTemplateFile ="./templates/fkr_list.html";
  //------------------------------------------------
  //Load Master Template
  $tbsPage->LoadTemplate("../templates/master2.html") ;
  //$tbsPage->LoadTemplate($strMainTemplate) ;
  $tbsPage->Show() ;
//--------------------------------------------------------------------------------

  // fungsi untuk meng-handle jika input data adalah kosong
  // jika kosong, diganti dengan NULL, jika tidak, apit dengan ''
  function handleNull($str)
  {
    if ($str === "") return "NULL";
    else return "'$str'";
  }
  // handle tanggal, jika kosong
  function handleDate($str)
  {
    if ($str == "") return "NULL";
    else return "'$str'";
  }
  
  // untuk menampilkan info yang mengubah data MRF
  function printShowLink($params)
  {
    extract($params);
    global $arrUserList;
    $strResult  = "";
    // tambahkan info record info
    $strDiv  = "<div id='detailRecord$counter' style=\"display:none\">\n";
    $strDiv .= "<strong>" .$record['employee_id']."-".$record['employee_name']."</strong><br>\n";
    $strDiv .= getWords("created"). ": ".substr($record['created'], 0,19) ." ";
    $strDiv .= (isset($record['created_by'])) ? $record['created_by']."<br>" : "<br>";
    $strDiv .= getWords("last modified"). ": ".substr($record['modified'], 0,19) ." ";
    $strDiv .= (isset($arrUserList[$record['modified_by']])) ? $arrUserList[$record['modified_by']]['name']."<br>" : "<br>";
/*
    $strDiv .= getWords("verified"). ": ".substr($record['verified_time'], 0,19) ." ";
    $strDiv .= (isset($arrUserList[$record['verified_by']])) ? $arrUserList[$record['verified_by']]['name']."<br>" : "<br>";

    $strDiv .= getWords("checked"). ": ".substr($record['checked_time'], 0,19) ." ";
    $strDiv .= (isset($arrUserList[$record['checked_by']])) ? $arrUserList[$record['checked_by']]['name']."<br>" : "<br>";
*/
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
 

  // copy data keluarga, dari kandidat ke karyawan
  function copyFamily($db, $strCandidateID, $strEmployeeID)
  {
    if ($strCandidateID == "" || $strEmployeeID == "") return false;
    $bolOK = true;
		$strUpdate = "";
    $strSQL = "
      SELECT t1.*, t2.name as name_type, t2.is_married
      FROM hrd_candidate_family AS t1
      LEFT JOIN hrd_family AS t2 ON t1.id_family = t2.id
      WHERE id_candidate = '$strCandidateID';
    ";
    $res = $db->execute($strSQL);
    while ($row = $db->fetchrow($res))
    {
      $strRel = strtoupper($row['name_type']);
      if (strstr($strRel, "AYAH")) $strRelation = 0;
      else if (strstr($strRel, "IBU")) $strRelation = 1;
      else if (strstr($strRel, "ISTRI") && $row['id_gender'] == FEMALE) $strRelation = 2;
      else if (strstr($strRel, "SUAMI") && $row['id_gender'] == MALE) $strRelation = 3;
      else if (strstr($strRel, "ANAK")) $strRelation = 4;
      else if (strstr($strRel, "SAUDARA")) $strRelation = 5;
      else $strRelation = 6;

      if ($row['name'] != "")
        $strUpdate .= "
          INSERT INTO hrd_employee_family (
            id_employee, name, relation, birthday,
            education_code, \"position\", company, gender
          )
          VALUES (
            '$strEmployeeID', '".check_plain(substr($row['name'], 0, 50))."',
            $strRelation, " .handleDate($row['dob']).",
            '" .check_plain(substr($row['education'], 0, 20))."',
            '" .check_plain(substr($row['position'], 0, 30))."',
            '" .check_plain(substr($row['company_name'], 0, 100))."',
            " .handleNull($row['id_gender'])."
          );
        ";
    }

    if ($strUpdate != "")
    {
      $resExec = $db->execute($strUpdate);
      if ($resExec == false) $bolOK = false;
    }

    return $bolOK;
  }

  // copy data gaji karyawan
  function copySalary($db, $strCandidateID, $strEmployeeID, $strSalarySet)
  {
    if ($strCandidateID == "" || $strEmployeeID == "" && !empty($strSalarySet)) return false;
    $bolOK = true;
		$setID = $strSalarySet;
    $strUpdate = "
      DELETE FROM hrd_employee_allowance WHERE id_employee = '$strEmployeeID';
    ";
    $strSQL = "
      SELECT t1.*, t2.code as allowance_code
      FROM hrd_fkr_detail AS t1
      INNER JOIN hrd_allowance_type AS t2 ON t1.id_allowance_type = t2.id
      WHERE t1.id_fkr IN
      ( SELECT id FROM hrd_fkr WHERE id_candidate = '$strCandidateID' )
      ;
    ";
    $res = $db->execute($strSQL);
    while ($row = $db->fetchrow($res))
    {
      $fltAmount = ($row['amount_next'] == "" || $row['amount_next'] == 0) ? $row['amount_start'] : $row['amount_next'];
      $strCode = $row['allowance_code'];
      if ($strCode != "" && $fltAmount <> 0 && is_numeric($fltAmount))
        $strUpdate .= "
          INSERT INTO hrd_employee_allowance (
            id_employee, allowance_code, amount, created, id_salary_set
          )
          VALUES (
            '$strEmployeeID', '".check_plain(substr($strCode, 0, 100))."',
            '$fltAmount', now(), '$setID'
          );
        ";
    }
  	$sqlInsertEmpBasicSalary = "INSERT INTO hrd_employee_basic_salary (id_employee, created_by, id_salary_set)";
  	$sqlInsertEmpBasicSalary .= " VALUES ('$strEmployeeID','".$_SESSION['sessionUserID']."','$setID');";
  	$strUpdate .= $sqlInsertEmpBasicSalary;
    if ($strUpdate != "")
    {
      $resExec = $db->execute($strUpdate);
      if ($resExec == false) $bolOK = false;
    }
	
    return $bolOK;
  }

  // copy data riwayat pengalaman kerja, dari kandidat ke karyawan
  function copyWork($db, $strCandidateID, $strEmployeeID)
  {
    if ($strCandidateID == "" || $strEmployeeID == "") return false;
    $bolOK = true;

    $strUpdate = "";
    $strSQL = "
      SELECT *
      FROM hrd_candidate_working_experience
      WHERE id_candidate = '$strCandidateID';
    ";
    $res = $db->execute($strSQL);
    while ($row = $db->fetchrow($res))
    {
      $strPosition = ($row['position_end'] == "") ? $row['position_start'] : $row['position_end'];
      if ($row['company_name'] != "")
        $strUpdate .= "
          INSERT INTO hrd_employee_work (
            id_employee, institution, \"location\", \"position\",
            day_from, month_from, year_from,
            day_thru, month_thru, year_thru, note
          )
          VALUES (
            '$strEmployeeID', '".check_plain(substr($row['company_name'], 0, 100))."',
            '" .check_plain(substr($row['company_address'], 0, 50))."',
            '" .check_plain(substr($strPosition, 0, 50))."',
            '" .$row['start_day']."', '" .$row['start_month']."', '" .$row['start_year']."',
            '" .$row['end_day']."', '" .$row['end_month']."', '" .$row['end_year']."',
            '" .check_plain(substr($row['job_description'], 0, 250))."'
          );
        ";
    }

    if ($strUpdate != "")
    {
      $resExec = $db->execute($strUpdate);
      if ($resExec == false) $bolOK = false;
    }

    return $bolOK;
  }

  // copy data riwayat training, dari kandidat ke karyawan
  function copyTraining($db, $strCandidateID, $strEmployeeID)
  {
    if ($strCandidateID == "" || $strEmployeeID == "") return false;
    $bolOK = true;

    $strUpdate = "";
    $strSQL = "
      SELECT *
      FROM hrd_candidate_course
      WHERE id_candidate = '$strCandidateID';
    ";
    $res = $db->execute($strSQL);
    while ($row = $db->fetchrow($res))
    {
      if ($row['course_type'] != "")
        $strUpdate .= "
          INSERT INTO hrd_employee_training (
            id_employee, subject, institution, \"location\",
            year_from, year_thru, note
          )
          VALUES (
            '$strEmployeeID', '".check_plain(substr($row['course_type'], 0, 50))."',
            '" .check_plain(substr($row['institution'], 0, 100))."',
            '" .check_plain(substr($row['place'], 0, 100))."',
            '" .$row['start_year']."',
            '" .$row['start_year']."',
            '" .check_plain(substr($row['funded_by'], 0, 250))."'
          );
        ";
    }

    if ($strUpdate != "")
    {
      $resExec = $db->execute($strUpdate);
      if ($resExec == false) $bolOK = false;
    }

    return $bolOK;
  }

  // copy data riwayat training, dari kandidat ke karyawan
  function copyEducation($db, $strCandidateID, $strEmployeeID)
  {
    if ($strCandidateID == "" || $strEmployeeID == "") return false;
    $bolOK = true;

    $strUpdate = "";
    $strSQL = "
      SELECT *
      FROM hrd_candidate_education
      WHERE id_candidate = '$strCandidateID';
    ";
    $res = $db->execute($strSQL);
    while ($row = $db->fetchrow($res))
    {
      if ($row['academic'] != "" || $row['school'] != "")
        $strUpdate .= "
          INSERT INTO hrd_employee_education (
            id_employee, education_level_code, institution, \"location\",
            faculty, year_from, year_thru
          )
          VALUES (
            '$strEmployeeID', '".check_plain(substr($row['academic'], 0, 20))."',
            '" .check_plain(substr($row['school'], 0, 100))."',
            '" .check_plain(substr($row['place'], 0, 50))."',
            '" .check_plain(substr($row['major'], 0, 100))."',
            '" .$row['year_from']."',
            '" .$row['year_to']."'
          );
        ";
    }

    if ($strUpdate != "")
    {
      $resExec = $db->execute($strUpdate);
      if ($resExec == false) $bolOK = false;
    }

    return $bolOK;
  }


  // fungsi untuk mengupdate family status,jika ada perubahan data terkait dengan status keluarga
  // agar lebih cepat, status single (0) atau married (1) disebutkan terlebih dahulu
  // juga gender disebutkan
  function updateFamilyStatus($db, $strIDEmployee, $intGender = "", $intStatus = SINGLE)
  {
    $bolOK = true;
    if ($strIDEmployee == "") return $bolOK;

    // ambil dulu jenis
    if ($intGender == "") $intGender = MALE; //anggap aja
    $intChildren = 0;
    $strFamilyStatus = "";

    if ($intGender == FEMALE || $intStatus == SINGLE) // wanita atau single, wanita dianggap single
    {
      $intStatus = SINGLE; //
    }
    else
    {
      $strSQL = "
        SELECT COUNT(id) AS total FROM hrd_employee_family
        WHERE id_employee = '$strIDEmployee'
          AND relation = '4' AND (status = '0' OR status is null)
      ";
      $res = $db->execute($strSQL);
      if ($row = $db->fetchrow($res))
      {
        if (is_numeric($row['total'])) $intChildren = $row['total'];
        if ($intChildren > 3) $intChildren = 3;
      }

    }

    // cari data di jenis keluarga
    $strSQL = "
      SELECT * FROM hrd_family_status
      WHERE marital_status = '$intStatus'
        AND children = '$intChildren'
    ";
    $res = $db->execute($strSQL);
    if ($row = $db->fetchrow($res))
    {
      $strFamilyStatus = $row['code'];
    }

    // update data
    $strSQL = "
      UPDATE hrd_employee SET family_status_code = '$strFamilyStatus'
      WHERE id = '$strIDEmployee';
    ";
    $resExec = $db->execute($strSQL);
    if ($resExec == false) $bolOK = false;

    return $bolOK;
  }


  // fungsi untuk membuat data karyawan dari data FKR
  function generateEmployeeFromFKR($db)
  {
    global $arrHouseOwnership; // common_variable.php

    $bolOK = true;
    $strFKR = getRequestValue("dataID");
    $strCandidate = ""; // id dari candidate

    if ($strFKR == "") return false;
    $arrFKR = array();
    $strSQL = "
      SELECT * FROM hrd_fkr WHERE id = '$strFKR'
    ";
    $res = $db->execute($strSQL);
    if ($row = $db->fetchrow($res))
    {
      $arrFKR = $row;
      $strCandidate = $row['id_candidate'];
      if (!empty($row['id_employee'])) return false; //anggap sudah ada, tidak perlu dibuat lagi
    }
    if (empty($strCandidate)) return false; // kandidat tidak ada, abaikan
    $arrCandidate = array();
    $strSQL = "
      SELECT * FROM hrd_candidate WHERE id = '$strCandidate'
    ";
    $res = $db->execute($strSQL);
    if ($row = $db->fetchrow($res))
    {
      $arrCandidate = $row;
    }
    else return false;
		/* Get emergency Address Candidate */
		$strSQLEM = "SELECT * FROM hrd_candidate_emergency WHERE id_candidate='$strCandidate' LIMIT 1";
		$resEm = $db->execute($strSQLEM);
		$arrEmergency = $db->fetchrow($resEm);
    // start proses generate data karyawan
    $db->execute("begin");

    $strEmployeeID = $db->getNextID("hrd_employee_id_seq");
    $strGender = ($arrCandidate['gender'] == "") ? 0 : $arrCandidate['gender'];
    $strMaritalStatus = ($arrCandidate['marital_status'] == "1") ? MARRIED : SINGLE; // di kandidat agak aneh, gak standard
    $strPhone = ($arrCandidate['phone'] != "" && $arrCandidate['hp'] != "") ? $arrCandidate['phone'].", ".$arrCandidate['hp'] : $arrCandidate['phone'].$arrCandidate['hp'];
    $strJoinDate = $strDueDate = $strJoinDate2 = $strDueDate2 = "";
    $strJoinDateLeave = $arrFKR['join_date']; // join date untuk leave
    $strPermanent = $strProbationEnd = $strPermanentAssign = "";
    $strProbation = 'f';
    if ($arrFKR['employee_status'] == 0)
    {
      $strJoinDate = $arrFKR['join_date'];
      $strPeriod = ($arrFKR['contract_month'] == "") ? 0 : $arrFKR['contract_month'];
      $strDueDate = getNextDateNextMonthNew($strJoinDate, $strPeriod);// date_functions.php
      $strDueDate = getNextDate($strDueDate, -1);
      //$arrFKR['employee_status'] = STATUS_CONTRACT_ADW;
      //$arrFKR['employee_status'] = STATUS_CONTRACT_1;
    }
    else //if ($arrFKR['employee_status'] == 1)
    {
      $strPermanent = $arrFKR['join_date'];
      $strPermanentAssign = $arrFKR['join_date'];
      $strPeriod = ($arrFKR['contract_month'] == "") ? 0 : $arrFKR['contract_month'];
      if (is_numeric($strPeriod) && $strPeriod > 0) //percobaan
      {
        $strProbation = 't';
        $strProbationEnd = getNextDateNextMonth($strPermanent, $strPeriod);// date_functions.php
        $strProbationEnd = getNextDate($strProbationEnd,-1);
        $strPermanentAssign = "";
      }
      //$arrFKR['employee_status'] = STATUS_PERMANENT;
    }

    if (isset($arrHouseOwnership[$arrCandidate['house_ownership']]))
      $strHouse = $arrHouseOwnership[$arrCandidate['house_ownership']]['text'];
    else
      $strHouse = $arrCandidate['house_ownership_other'];

	  
	  $transport=0;
	 $strSQL = "
      SELECT t1.*, t2.code as allowance_code
      FROM hrd_fkr_detail AS t1
      INNER JOIN hrd_allowance_type AS t2 ON t1.id_allowance_type = t2.id
      WHERE t1.id_fkr IN
      ( SELECT id FROM hrd_fkr WHERE id_candidate = '$strCandidate' )
      ;
    "; 
    $res = $db->execute($strSQL);
    while ($row = $db->fetchrow($res))
    {
      $fltAmount = ($row['amount_next'] == "" || $row['amount_next'] == 0) ? $row['amount_start'] : $row['amount_next'];
      if($row['allowance_code']=="tunjangan_transport"){
	  $transport = $fltAmount;
	  }
    }
	  // $transport;
		// die();
	$arrCandidate['weight'] = empty($arrCandidate['weight']) ? 0 : $arrCandidate['weight'];
    $arrCandidate['height'] = empty($arrCandidate['height']) ? 0 : $arrCandidate['height'];
    $strSQL = "
      INSERT INTO hrd_employee (
        id, employee_id, employee_name, gender,
        primary_address, primary_phone, primary_city,
        primary_zip, emergency_address, emergency_phone,
        birthplace, birthday, nationality,
        driver_license_a, driver_license_b, driver_license_c,
        id_card, email, passport, bank_branch,
        bank_account, bank_account_name,
        weight, height, religion_code, employee_status,
        id_company, division_code, department_code,
        section_code, sub_section_code,
        position_code, grade_code,
        join_date, due_date, permanent_date,
        house_status, marital_status,
        photo, family_status_code, active, created,
        education_level_code,transport,transport_fee,branch_code,nickname,
        branch_penugasan_code, branch_cost_center_code, management_code,functional_code,
        emergency_contact, emergency_relation, bank_code, contract_from
      )
      VALUES (
        '$strEmployeeID',
        '".$arrFKR['employee_id']."',
        '" .check_plain($arrFKR['employee_name'])."',
        '" .$strGender."',
        '" .check_plain($arrCandidate['current_address'])."',
        '" .check_plain($strPhone)."',
        '" .check_plain($arrCandidate['current_address_city'])."',
        '" .check_plain($arrCandidate['current_address_zip'])."',
        '" .check_plain($arrEmergency['address'])."',
        '" .check_plain($arrEmergency['phone'])."',
        '" .check_plain($arrCandidate['birthplace'])."',
         " .handleDate($arrCandidate['birthdate']).",
        '" .check_plain($arrCandidate['nationality'])."',
        '" .check_plain($arrCandidate['driver_license_a'])."',
        '" .check_plain($arrCandidate['driver_license_b'])."',
        '" .check_plain($arrCandidate['driver_license_c'])."',
        '" .check_plain($arrCandidate['id_card'])."',
        '" .check_plain($arrCandidate['email'])."',
        '" .check_plain($arrCandidate['passport'])."',
        '" .check_plain($arrFKR['bank'])."',
        '" .check_plain($arrFKR['bank_account_no'])."',
        '" .check_plain($arrFKR['bank_account_name'])."',
         " .handleNull($arrCandidate['weight']).",
         " .handleNull($arrCandidate['height']).",
         " .handleNull($arrCandidate['religion_code']).",
         " .handleNull($arrFKR['employee_status']).",
         " .handleNull($arrFKR['id_company']).",
        '" .check_plain($arrFKR['division_code'])."',
        '" .check_plain($arrFKR['department_code'])."',
        '" .check_plain($arrFKR['section_code'])."',
        '" .check_plain($arrFKR['sub_section_code'])."',
        '" .check_plain($arrFKR['position_code'])."',
        '" .check_plain($arrFKR['salary_grade_code'])."',
         " .handleDate($strJoinDate).",
         " .handleDate($strDueDate).",
         " .handleDate($strPermanent).",
        '" .check_plain($strHouse)."',
         " .handleNull($strMaritalStatus).",
         " .handleNull($arrCandidate['file_photo']).",
        '" .check_plain($arrFKR['family_status_code'])."',
         '1', now(),
         " .handleNull($arrCandidate['education_level_code']).",
		 " .handleNull($arrCandidate['transport']).",
		 $transport,
		  '" .check_plain($arrFKR['branch_code'])."',
		   '" .check_plain($arrCandidate['nickname'])."',
		   '" .check_plain($arrFKR['branch_contract_code'])."',
		   '" .check_plain($arrFKR['branch_cost_center_code'])."',
		   '" .check_plain($arrFKR['management_code'])."',
		   '" .check_plain($arrFKR['functional_code'])."',
		   '" .check_plain($arrEmergency['name'])."',
		   '" .check_plain($arrEmergency['relation'])."',
		   '" .check_plain($arrFKR['bank_code'])."',
		   " .handleDate($strJoinDate)."
      );
    ";
    $resExec = $db->execute($strSQL);
    if ($resExec == false) $bolOK = false;
    if ($bolOK)
    {
      // update juga data fkr, agar ada link ke id karyawan
      $strSQL = "
        UPDATE hrd_fkr SET id_employee = '$strEmployeeID'
        WHERE id = '$strFKR';
      ";
      $resExec = $db->execute($strSQL);
      if ($resExec == false) $bolOK = false;
    }
    if ($bolOK)
    {
      // update juga data keluarga
      $bolOK = copyFamily($db, $strCandidate, $strEmployeeID);
    }
    if ($bolOK)
    {
      // update juga data pendidikan
      $bolOK = copyEducation($db, $strCandidate, $strEmployeeID);
    }
    if ($bolOK)
    {
      // update juga data training
      $bolOK = copyTraining($db, $strCandidate, $strEmployeeID);
    }
    if ($bolOK)
    {
      // update juga data pengalaman kerja
      $bolOK = copyWork($db, $strCandidate, $strEmployeeID);
    }
    if ($bolOK)
    {
      // update juga data pengalaman kerja
      $bolOK = copySalary($db, $strCandidate, $strEmployeeID, $arrFKR['id_salary_set']);
    }

    if ($bolOK)
    {
      // update juga status keluarga (pajak)
      // $bolOK = updateFamilyStatus($db, $strEmployeeID, $strGender, $strMaritalStatus);
    }

    if ($bolOK)
    {
      $db->execute("commit");
       $myDataGrid->message = getWords("Data Employee Succes  be created!");
	  header("location:employee_edit.php?dataID=".$strEmployeeID);
      exit();
    }
    else
    {
      $db->execute("rollback");
    }
    return $bolOK;
  }

  // buat tampilkan untuk link pembuatan data karyawan dari data FKR
  function printEmployeeLink($params)
  {
    extract($params);
    
    if ($record['status'] != REQUEST_STATUS_APPROVED_2 && $record['status'] != getWords("approved 2")) return "";

    if ($record['id_employee'] == "")
    {
      if ($record['employee_id'] == "") // belum diisi niknya
        $str = "<a href=\"javascript:alert('Employee ID Does not Exists')\">".getWords('create employee')."</a>";
      else
        $str = "<a onclick=\"return confirm('Do you want to create Employee based on this FKR?');\" href='fkr_list.php?btnCreateEmployee=true&dataID=" .$record['id']. "'>".getWords('create employee')."</a>";
    }
    else
      $str = "<a href='employee_edit.php?dataID=" .$record['id_employee']."'>" .getWords("view")."</a>";
    return $str;
  }

  function printViewLink($params)
  {
    extract($params);
    return "<a href='fkr_edit.php?dataID=" .$record['id']. "&dataCandidateID=".$record['id_candidate']."'>".$value."</a>";
  }

  function printEditLink($params)
  {
    extract($params);
    $bolOK = false;
    if ($record['salary_grade_code'] == "") $bolOK = true;
    else $bolOK = (isBandAccess($record['salary_grade_code']));

    $str = ($bolOK) ?  "<a href='fkr_edit.php?dataID=" .$record['id']. "&dataCandidateID=".$record['id_candidate']."'>" .getWords('edit'). "</a>" : "";
    return $str;
  }
  function printPrintLink($params)
  {
    extract($params);
    return "<a href=\"javascript:openWindowDialog('fkr_print.php?dataID=" .$record['id']. "&dataCandidateID=".$record['id_candidate']."')\">" .getWords('print'). "</a>";
  }

  
  // fungsi untuk menghapus data
  function deleteData()
  {
    global $myDataGrid;

    $arrKeys = array();
    foreach ($myDataGrid->checkboxes as $strValue)
      $arrKeys['id'][] = $strValue;

    $tbl = new cModel("hrd_fkr");
    if ($tbl->deleteMultiple($arrKeys))
      $myDataGrid->message = $tbl->strMessage;
    else
      $myDataGrid->errorMessage = $tbl->strMessage;
  } //deleteData

  // handle cara menampilkan jenis status karyawan
  function printEmployeeStatus1($params)
  {
    extract($params);
    global $ARRAY_EMPLOYEE_STATUS;
    
    //if ($value == STATUS_PERMANENT) return getWords("permanent");
    //else return getWords("contract");
    
    if (isset($ARRAY_EMPLOYEE_STATUS[$value]))
      return getWords($ARRAY_EMPLOYEE_STATUS[$value]);
    else
      return "";
    
  }

  // handle approve/unapprove
  function approveData()
  {

    global $myDataGrid;
    global $objUP;

    if (!$objUP->isManagerHR()) {
      return false;
    }

    $i = 0;
    $tbl = new cModel("hrd_fkr");
    foreach ($myDataGrid->checkboxes as $strValue)
    {
      $i++;
      $strSQL  = "
        UPDATE hrd_fkr
        SET status = '" .REQUEST_STATUS_APPROVED."', approved1 = now(),
          approved1_by = '" .$_SESSION['sessionUserID']."'
        WHERE id = '".$strValue."';
        UPDATE hrd_recruitment_need SET number_ok = number_ok + 1 
        WHERE id IN (
          SELECT id_recruitment_need FROM hrd_fkr WHERE id = '$strValue'
        ) AND NOT (number_ok is null);
        UPDATE hrd_recruitment_need SET number_ok = 1 
        WHERE id IN (
          SELECT id_recruitment_need FROM hrd_fkr WHERE id = '$strValue'
        ) AND number_ok is null;
      ";
      $tbl->execute($strSQL);
    }

  }
	function changeStatus($db, $intStatus){
		global $myDataGrid;
    global $objUP;
    if (!$objUP->isManagerHR()) {
      return false;
    }
		if (!is_numeric($intStatus)) {
      return false;
    }
		// tambahan info
		$strUpdate = getStatusUpdateString($intStatus);
    $i = 0;
    $tbl = new cModel("hrd_fkr");
    foreach ($myDataGrid->checkboxes as $strValue)
    {
      $i++;
      $strSQL .= "UPDATE hrd_fkr SET $strUpdate status = '$intStatus'  ";
      $strSQL .= "WHERE id = '".$strValue."';";  
      if ($intStatus == REQUEST_STATUS_APPROVED_2){
	      $strSQL  .= "  
	        UPDATE hrd_recruitment_need SET number_ok = 0
	        WHERE id IN (
	          SELECT id_recruitment_need FROM hrd_fkr WHERE id = '$strValue'
	        );
	        UPDATE hrd_recruitment_need SET number_ok = fkr.total
	        FROM (
	          SELECT COUNT(id) AS total, id_recruitment_need FROM hrd_fkr
	          WHERE id_recruitment_need IN (
	            SELECT id_recruitment_need FROM hrd_fkr WHERE id = '$strValue'
	            AND status = '" .$intStatus. "'
	          )
	          GROUP BY id_recruitment_need
	        ) AS fkr 
	        WHERE fkr.id_recruitment_need = hrd_recruitment_need.id
	          AND hrd_recruitment_need.id IN (
	          SELECT id_recruitment_need FROM hrd_fkr WHERE id = '$strValue'
	        );
	          
	      ";
	    }
      $tbl->execute($strSQL);
    }
	}
  // handle approve/unapprove
  function unApproveData()
  {

    global $myDataGrid;
    global $objUP;

    if (!$objUP->isManagerHR()) {
      return false;
    }

    $i = 0;
    $tbl = new cModel("hrd_fkr");
    foreach ($myDataGrid->checkboxes as $strValue)
    {
      $i++;
      $strSQL  = "
        UPDATE hrd_fkr
        SET status = '" .REQUEST_STATUS_NEW."', approved1 = null,
          approved1_by = null
        WHERE id = '".$strValue."' AND status <> '" .REQUEST_STATUS_NEW. "';
        
        UPDATE hrd_recruitment_need SET number_ok = 0
        WHERE id IN (
          SELECT id_recruitment_need FROM hrd_fkr WHERE id = '$strValue'
        );
        UPDATE hrd_recruitment_need SET number_ok = fkr.total
        FROM (
          SELECT COUNT(id) AS total, id_recruitment_need FROM hrd_fkr
          WHERE id_recruitment_need IN (
            SELECT id_recruitment_need FROM hrd_fkr WHERE id = '$strValue'
            AND status = '" .REQUEST_STATUS_APPROVED. "'
          )
          GROUP BY id_recruitment_need
        ) AS fkr 
        WHERE fkr.id_recruitment_need = hrd_recruitment_need.id
          AND hrd_recruitment_need.id IN (
          SELECT id_recruitment_need FROM hrd_fkr WHERE id = '$strValue'
        );
          
      ";
      $tbl->execute($strSQL);
    }

  }

?>