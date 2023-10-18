<?php
  include_once('../global/session.php');
  include_once('global.php');
  include_once('salary_func.php');
  include_once('../global/common_data.php');
  include_once('../includes/datagrid2/datagrid.php');
  include_once('../includes/form2/form2.php');
  include_once('../classes/hrd/hrd_employee.php');
  include_once('../classes/hrd/hrd_basic_salary_set.php');
  include_once('../classes/hrd/hrd_employee_basic_salary.php');
  include_once('../classes/hrd/hrd_employee_allowance.php');
  include_once('../classes/hrd/hrd_employee_deduction.php');
  include_once('../classes/hrd/hrd_basic_salary_set.php');

  
  $dataPrivilege = getDataPrivileges("salary_basic.php", $bolCanView, $bolCanEdit, $bolCanDelete, $bolCanApprove, $bolCanCheck, $bolCanAcknowledge, $bolCanApprove2);
  if (!$bolCanView) die(accessDenied($_SERVER['HTTP_REFERER']));

  $strWordsGeneralSetting    = getWords("general setting");
  $strWordsSalarySet     = getWords("salary set");
  $strWordsEmployeeAllowance = getWords("employee allowance");
  $strWordsManagerialAllowance = getWords("managerial allowance");
  $strWordsEmployeeDeduction = getWords("employee deduction");
  $strWordsManagerialDeduction = getWords("managerial deduction");
 
  $db = new CdbClass;
  if ($db->connect())
  {

    $strFilterCompany = getPostValue('filterCompany');
    $strDataID = getPostValue('dataID');
    $isNew = ($strDataID == "");
    $arrSetSource = array();

    $tblBasicSalarySet = new cHrdBasicSalarySet();
    $arrBasicSalarySet = $tblBasicSalarySet->findAll($strKriteriaCompany, "id, start_date, note, id_company", "", null, 1, "id");
    foreach($arrBasicSalarySet AS $keySet => $arrSet)
    {
      $arrSetSource[$keySet] = $arrSet['start_date']." - ". printCompanyName($arrSet['id_company']);
    }

    if ($bolCanEdit)
    {
      $f = new clsForm("formInput", 2, "100%", "");
      $f->caption = strtoupper($strWordsINPUTDATA);

      $f->addHidden("dataID", $strDataID);
      $f->addInput(getWords("start date"), "dataStartDate", "", array("style" => "width:$strDateWidth"), "date", true, true, true);
      $f->addSelect(getWords("company"), "dataCompany", getDataListCompany($strDataCompany, false, "", $strKriteria2), array("style" => "width:200"), "", true);
      $f->addSelect(getWords("source set"), "dataIDSalarySetSource", getDataList($arrSetSource, true, null, true, null), array("style" => "width:250"), "", (count($arrSetSource) != 0));

      $f->addTextArea(getWords("note"), "dataNote", "", array("cols"=>76, "rows"=>3), "string", false, true, true);

      $f->addSubmit("btnSave", getWords("save"), array("onClick" => "javascript:myClient.confirmSave();"), true, true, "", "", "saveData()");
      $f->addButton("btnAdd", getWords("add new"), array("onClick" => "javascript:myClient.editData(0);"));

      $formInput = $f->render();
    }
    else
      $formInput = "";

    $fFilter = new clsForm("formFilter", 8, "100%", "");
    $fFilter->caption = strtoupper($strWordsFILTERDATA);

    $fFilter->addSelect(getWords("company"), "filterCompany", getDataListCompany($strFilterCompany, $bolCompanyEmptyOption, $arrCompanyEmptyData, $strKriteria2), array("style" => "width:200"), "", false);

    $fFilter->addLiteral("", "buttonShow", generateButton("btnShow", "Show", "", "onclick = \"document.formFilter.submit()\""));
    //$fFilter->showCaption = false;
    $fFilter->hasButton = false;
    $formFilter = $fFilter->render();


    $myDataGrid = new cDataGrid("formData","DataGrid1");
    $myDataGrid->caption = strtoupper($strWordsLISTOF . " " . $dataPrivilege['menu_name']);
    $myDataGrid->setAJAXCallBackScript(basename($_SERVER['PHP_SELF']));
		$myDataGrid->pageSortBy = 'start_date DESC';    
    $myDataGrid->addColumnCheckbox(new DataGrid_Column("chkID", "id", array('width' => '30'), array('align'=>'center', 'nowrap' => '')));
    $myDataGrid->addColumnNumbering(new DataGrid_Column("No", "", array('width'=>'30'), array('nowrap'=>'')));
    $myDataGrid->addColumn(new DataGrid_Column(getWords("start date"), "start_date", array('width' => '150'),array('nowrap' => '')));
    $myDataGrid->addColumn(new DataGrid_Column(getWords("company"), "id_company", array('width' => '250'),array('nowrap' => ''), false, false, "","printCompanyName()"));
    $myDataGrid->addColumn(new DataGrid_Column(getWords("salary data source"), "id_salary_set_source", array('width' => '250'),array('nowrap' => ''), false, false, "","getSalaryStartDate()"));
    $myDataGrid->addColumn(new DataGrid_Column(getWords("note"), "note", ""));
    $myDataGrid->addColumn(new DataGrid_Column("", "id", array('width' => '75'), array('align' => 'center', 'nowrap' => ''), false, false, "","printDetailLink()"));
    if ($bolCanEdit)
      $myDataGrid->addColumn(new DataGrid_Column("", "", array('width' => '60'), array('align' => 'center', 'nowrap' => ''), false, false, "","printEditLink()", "", false /*show in excel*/));
    if ($bolCanDelete)
      $myDataGrid->addSpecialButton("btnDelete","btnDelete","submit","Delete","onClick=\"javascript:return myClient.confirmDelete();\"","deleteData()");

    $myDataGrid->addButtonExportExcel("Export Excel", $dataPrivilege['menu_name'].".xls", getWords($dataPrivilege['menu_name']));
    $myDataGrid->strAdditionalHtml = generateHidden("dataIDSalarySet", "", "");

    $myDataGrid->getRequest();
    //--------------------------------
    //get Data and set to Datagrid's DataSource by set the data binding (bind method)
    $strSQLCOUNT  = "SELECT COUNT(*) AS total FROM hrd_basic_salary_set ";
    $strSQL       = "SELECT * FROM hrd_basic_salary_set ";
    if ($strFilterCompany != "") 
    {
      $strSQLCOUNT .=  "WHERE id_company = $strFilterCompany " ;
      $strSQL      .=  "WHERE id_company = $strFilterCompany " ;
    }
    $myDataGrid->totalData = $myDataGrid->getTotalData($db, $strSQLCOUNT);
    $dataset = $myDataGrid->getData($db, $strSQL);

    //bind Datagrid with array dataset
    $myDataGrid->bind($dataset);
    $DataGrid = $myDataGrid->render();
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
//--------------------------------------------------------------------------------

  function printEditLink($params)
  {
    extract($params);
    return "
      <input type=hidden name='detailID$counter' id='detailID$counter' value='".$record['id']."' />
      <input type=hidden name='detailStartDate$counter' id='detailStartDate$counter' value='".$record['start_date']."' />
      <input type=hidden name='detailCompany$counter' id='detailCompany$counter' value='".$record['id_company']."' />
      <input type=hidden name='detailIDSalarySetSource$counter' id='detailIDSalarySetSource$counter' value='".$record['id_salary_set_source']."' />
      <input type=hidden name='detailNote$counter' id='detailNote$counter' value='".$record['note']."' />
      <a href=\"javascript:myClient.editData($counter)\">" .getWords('edit'). "</a>";
  }

  function printDetailLink($params)
  {
    extract($params);
    global $bolPrint;
    if ($bolPrint)
      return "";
    else
      return generateButton("btnDetail$counter", getWords("open"), "", "onclick =\"document.formData.dataIDSalarySet.value = '".$value."'; document.formData.action = 'salary_basic.php'; document.formData.submit();\"");
  }
  function getSalaryStartDate($params)
  {
    extract($params);
    global $bolPrint;
    global $arrBasicSalarySet;
    return (isset($arrBasicSalarySet[$value])) ? ($arrBasicSalarySet[$value]['start_date'] ." - ". printCompanyName($arrBasicSalarySet[$value]['id_company'])) : "";
  }

  // fungsi untuk menyimpan data
  function saveData() 
  {
    global $f;
    global $isNew;

    $strmodified_byID = $_SESSION['sessionUserID'];
    //echo $f->getValue('dataIDSalarySetSource');
    $dataHrdBasicSalarySet = new cHrdBasicSalarySet();
    $data = array("start_date" => $f->getValue('dataStartDate'),
                  "id_company" => $f->getValue('dataCompany'),
                  "id_salary_set_source" => $f->getValue('dataIDSalarySetSource'),
                  "note" => $f->getValue('dataNote'));

    // simpan data -----------------------
    $bolSuccess = false;
    if ($isNew)
    {
      // data baru
      $bolSuccess = $dataHrdBasicSalarySet->insert($data);
      //generateEmployeeSalaryEdition($dataHrdBasicSalarySet->getLastInsertId(), $f->getValue('dataCompany'));
    } 
    else 
    {
      $bolSuccess = $dataHrdBasicSalarySet->update(/*pk*/"id='".$f->getValue('dataID')."'", /*data to update*/ $data);
    }
    if ($bolSuccess)
    {
      if ($isNew)
        $f->setValue('dataID', $dataHrdBasicSalarySet->getLastInsertId());
      else
        $f->setValue('dataID', $f->getValue('dataID'));
    }

    $f->message = $dataHrdBasicSalarySet->strMessage;
    redirectPage("salary_set.php");

  } // saveData

  function generateEmployeeSalaryEdition($strIDSalarySet, $strIDCompany) 
  {
    echo $strIDCompany;
    global $db;
    $tblEmployeeBasicSalary = new cHrdEmployeeBasicSalary();
    if ($tblEmployeeBasicSalary->findCount("id_salary_set = ".$strIDSalarySet) > 0) return false;
    
    $arrTemp = $tblEmployeeBasicSalary->find("id_salary_set IN (SELECT id FROM hrd_basic_salary_set WHERE id_company = $strIDCompany)", "max(id_salary_set) as max_id");
    $strMaxIDSalarySet = $arrTemp['max_id'];
    if ($tblEmployeeBasicSalary->findCount("id_salary_set = $strMaxIDSalarySet") > 0)
    {
      $dataTempBasicSalary = $tblEmployeeBasicSalary->findAll("id_salary_set = $strMaxIDSalarySet", "id_employee, $strIDSalarySet as id_salary_set, basic_salary, transport_allowance, meal_allowance, vehicle_allowance, position_allowance", "", null, 1, "id_employee");
      foreach($dataTempBasicSalary as $strIDEmployee => $data)
      {
        $tblEmployeeBasicSalary->insert($data);
      }
    }
    else
    {
      $tblEmployee = new cHrdEmployee();
      $dataEmployee = $tblEmployee->findAll("active = 1 AND id_company = $strIDCompany", "id as id_employee", "", null, 1, "id");
      foreach($dataEmployee as $strIDEmployee => $data)
      {
        $data['basic_salary']         = 0;
        $data['transport_allowance']  = 0;
        $data['vehicle_allowance']    = 0;
        $data['meal_allowance']       = 0;
        $data['position_allowance']   = 0;
        $tblEmployeeBasicSalary->insert($data);
      }
    }
  }

  // fungsi untuk menghapus data
  function deleteData() 
  {
    global $myDataGrid;
  
    $arrKeys = array();
    foreach ($myDataGrid->checkboxes as $strValue)
    {
      $arrKeys['id'][] = $strValue;
      $arrKeys2['id_salary_set'][] = $strValue;
    }
    $dataBasicSalarySet = new cHrdBasicSalarySet();    
    $dataBasicSalarySet->deleteMultiple($arrKeys);
    $dataEmployeeBasicSalary = new cHrdEmployeeBasicSalary();    
    $dataEmployeeBasicSalary->deleteMultiple($arrKeys2);
    $dataEmployeeAllowance = new cHrdEmployeeAllowance();    
    $dataEmployeeAllowance->deleteMultiple($arrKeys2);
    $dataEmployeeDeduction = new cHrdEmployeeDeduction();    
    $dataEmployeeDeduction->deleteMultiple($arrKeys2); 
    $myDataGrid->message = $dataBasicSalarySet->strMessage;
    redirectPage("salary_set.php");
  } //deleteData

?>