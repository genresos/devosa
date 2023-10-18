<?php
  include_once('../global/session.php');
  include_once('global.php');
  include_once('../global/common_data.php');
  include_once('../includes/datagrid/datagrid.php');
  include_once('../includes/form2/form2.php');
  include_once('../classes/hrd/hrd_work_schedule.php');


  $dataPrivilege = getDataPrivileges(basename($_SERVER['PHP_SELF']), $bolCanView, $bolCanEdit, $bolCanDelete, $bolCanApprove, $bolCanCheck, $bolCanAcknowledge, $bolCanApprove2);
  if (!$bolCanView) die(getWords('view denied'));
  $strWordsEntrySchedule = getWords("entry schedule");
  $strWordsScheduleList  = getWords("schedule list");
  //----MAIN PROGRAM -----------------------------------------------------
 
  $db = new CdbClass;
  if ($db->connect()) 
  {

    getUserEmployeeInfo();
    $arrUserList = getAllUserInfo($db);

    $strDataID          = getPostValue('dataID');
    $isNew              = ($strDataID == "");
    $strPageTitle = $dataPrivilege['menu_name'];
    $myDataGrid = new cDataGrid("formData","DataGrid1", '100%', '100%', false, false, true, true);
    $strDefaultStart  = substr(getSetting("start_time"), 0, 5); 
    $strDefaultFinish = substr(getSetting("finish_time"), 0, 5);
    $strDataID        = getPostValue('dataID');
    $strDataLinkCode  = getPostValue('dataLinkCode');
    $strDataTableName = getPostValue('dataTableName');

    scopeData($strDataEmployee, $strDataSubSection, $strDataSection, $strDataDepartment, $strDataDivision, $_SESSION['sessionUserRole'], $arrUserInfo);
    //generate form untuk select trip type
    //trip type harus dipilih dulu supaya jenis2 trip allowance dapat ditentukan
    $f = new clsForm("formFilter", 1, "100%", "");
    $f->caption = strtoupper("schedule list");
    $f->addSelect(getWords("table"), "dataTable", getDataListTable(null, true, array("value" => '', "text" => '', "selected" => true)), array("style" => "width:$strDefaultWidthPx"), "", false);

      $f->addInput(getWords('link code'), 'dataLinkCode', '', '', 'string', false);
      $f->addInput(getWords('link name'), 'dataLinkName', '', '', 'string', false);
    //$f->addSelect(getWords("link code"), "dataLinkCode", getDataListLinkCode(null, true, array("value" => '', "text" => '', "selected" => true)), array("style" => "width:$strDefaultWidthPx"), "", false);
    //$f->addSelect(getWords("link name"), "dataLinkName", getDataListSalaryGrade("", true), array("style" => "width:$strDefaultWidthPx"), "", false);
    $f->addSelect(getWords("workday"), "dataWorkday", getDataListDayName(null, true, array("value" => "", "text" => "", "selected" => true)), array("style" => "width:$strDefaultWidthPx"), "", false);

    $f->addSubmit("btnShow", getWords("show"), "", true, true, "", "", "");

    $formFilter = $f->render();
    getData($db);
  }

  function getData($db)
  {
    global $dataPrivilege;
    global $bolCanEdit, $bolCanDelete, $bolCanApprove, $bolCanCheck, $bolCanAcknowledge;

    global $f;
    global $myDataGrid;
    global $DataGrid;
    global $strDataCompany;
    global $strKriteriaCompany;


    $arrData = $f->getObjectValues();
    $strKriteria = "";
    // GENERATE CRITERIA

    if ($arrData['dataTable']!= "") {
      $strKriteria .= "AND table_name = '".$arrData['dataTable']."' ";
    }
    if ($arrData['dataLinkCode']!= "") {
      $strKriteria .= "AND LOWER(link_code) LIKE '%".strtolower($arrData['dataLinkCode'])."%' ";
    }
    if ($arrData['dataLinkName']!= "") {
        $strLinkCode = getLinkCode($arrData['dataLinkName'], $db);
        $strKriteria .= "AND link_code = '$strLinkCode' ";
    }
    if ($arrData['dataWorkday']!= "") {
      $strKriteria .= "AND workday = '".$arrData['dataWorkday']."' ";
    }

    $strKriteria .= $strKriteriaCompany;

    if ($db->connect())
    {
      $myDataGrid->caption = getWords("schedule list");
      $myDataGrid->setAJAXCallBackScript(basename($_SERVER['PHP_SELF']));
      
      $myDataGrid->addColumnCheckbox(new DataGrid_Column("chkID", "id", array('width' => '30'), array('align'=>'center', 'nowrap' => '')));
      $myDataGrid->addColumnNumbering(new DataGrid_Column("No.", "", array('width'=>'30'), array('nowrap'=>'')));
      $myDataGrid->addColumn(new DataGrid_Column(getWords("table name"), "table_name", array('width' => '100'), array('nowrap' => '')));
      $myDataGrid->addColumn(new DataGrid_Column(getWords("link code"), "link_code", "", array('nowrap' => '')));
      $myDataGrid->addColumn(new DataGrid_Column(getWords("link name"), "link_code", "", array('nowrap' => ''), true, true, "", "printLinkName()"));
      $myDataGrid->addColumn(new DataGrid_Column(getWords("workday"), "workday", "", array('nowrap' => ''), true, true, "", "printWorkday()"));
      $myDataGrid->addColumn(new DataGrid_Column(getWords("day off"), "day_off", "", array('align' => 'center'), true, true, "", "printDayOff()"));
      $myDataGrid->addColumn(new DataGrid_Column(getWords("start time"), "start_time", "", array('nowrap' => '')));
      $myDataGrid->addColumn(new DataGrid_Column(getWords("finish time"), "finish_time", "", array('nowrap' => '')));

    
      if ($bolCanEdit)
        $myDataGrid->addColumn(new DataGrid_Column("", "", array('width' => '60'), array('align' => 'center', 'nowrap' => ''), false, false, "","printEditLink()"));
      
      if ($bolCanDelete)
        $myDataGrid->addSpecialButton("btnDelete","btnDelete","submit", getWords("delete"),"onClick=\"javascript:return myClient.confirmDelete();\"","deleteData()");

        $title=(str_replace(' ', '_', $dataPrivilege['menu_name']));
        $myDataGrid->addButtonExportExcel(getWords("export excel"), $title.".xls", getWords($dataPrivilege['menu_name']));
    
      $myDataGrid->getRequest();
      //--------------------------------
      //get Data and set to Datagrid's DataSource by set the data binding (bind method)
        $strSQLCount       = "SELECT COUNT(*) FROM hrd_work_schedule AS t1 WHERE 1=1 $strKriteria";
        $strSQL       = "SELECT * FROM hrd_work_schedule AS t1 WHERE 1=1 $strKriteria";
      $myDataGrid->totalData = $myDataGrid->getTotalData($db, $strSQLCount);
      $dataset = $myDataGrid->getData($db, $strSQL);
      foreach($dataset as &$row)
      {
        $row['ori_table_name'] = $row['table_name'];
        if ($row['table_name'] == 'Section') $row['table_name'] = 'Unit';
        else if ($row['table_name'] == 'SubSection') $row['table_name'] = 'Section';
      }
      //bind Datagrid with array dataset
      $myDataGrid->bind($dataset);
      $DataGrid = $myDataGrid->render();
    }
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


  function getLinkCode($strLinkName, $db) {
      $strLinkCode = '';

      $strSQL = "SELECT link_code FROM hrd_work_schedule
                 WHERE link_code IN (SELECT employee_id FROM hrd_employee WHERE LOWER(employee_name) LIKE '%".strtolower($strLinkName)."%')
                 OR link_code IN (SELECT department_code FROM hrd_department WHERE LOWER(department_name) LIKE '%".strtolower($strLinkName)."%')
                 OR link_code IN (SELECT division_code FROM hrd_division WHERE LOWER(division_name) LIKE '%".strtolower($strLinkName)."%')
                 OR link_code IN (SELECT section_code FROM hrd_section WHERE LOWER(section_name) LIKE '%".strtolower($strLinkName)."%')
                 OR link_code IN (SELECT sub_section_code FROM hrd_sub_section WHERE LOWER(sub_section_name) LIKE '%".strtolower($strLinkName)."%')";
      $res = $db->execute($strSQL);
      if ($row = $db->fetchrow($res)) {
          $strLinkCode = $row['link_code'];
      }

      return $strLinkCode;
  }

  function printDayOff($params)
  {
    extract($params);
    if ($record['day_off'] == "t") 
      return "&radic;";
    else
      return "";
  }
  function printWorkday($params)
  {
    //global $ARRAY_DAY;
    $arrDay     = array(0 => getWords("sunday"), getWords("monday"), getWords("tuesday"), getWords("wednesday"), getWords("thursday"), getWords("friday"), getWords("saturday"));
    extract($params);
    if ($record['workday'] == -1)
        return "";
    else
        return $arrDay[$record['workday']];
  }
  function printLinkName($params)
  {
  global $db;
  extract($params);
   if ($record['table_name'] == 'Employee')
   {
      $strSQL = "SELECT employee_name FROM hrd_employee WHERE employee_id = '".$record['link_code']."'";
      $resExec = $db->execute($strSQL);
      if ($rowTmp  =  $db->fetchrow($resExec)) {
         $strLinkName = $rowTmp['employee_name'];
      } else {
         $strLinkName = ""; 
      }      
   } 
   else if  ($record['table_name'] == 'Division')
   {
      $strSQL = "SELECT division_name FROM hrd_division WHERE division_code = '".$record['link_code']."'";
      $resExec = $db->execute($strSQL);
      if ($rowTmp  =  $db->fetchrow($resExec)) {
         $strLinkName = $rowTmp['division_name'];
      } else {
         $strLinkName = ""; 
      } 
   } 
   else if  ($record['table_name'] == 'Department')
   {
      $strSQL = "SELECT department_name FROM hrd_department WHERE department_code = '".$record['link_code']."'";
      $resExec = $db->execute($strSQL);
      if ($rowTmp  =  $db->fetchrow($resExec)) {
         $strLinkName = $rowTmp['department_name'];
      } else {
         $strLinkName = ""; 
      } 
    }
   else if  ($record['table_name'] == 'Unit')
   {
      $strSQL = "SELECT section_name FROM hrd_section WHERE section_code = '".$record['link_code']."'";
      $resExec = $db->execute($strSQL);
      if ($rowTmp  =  $db->fetchrow($resExec)) {
         $strLinkName = $rowTmp['section_name'];
      } else {
         $strLinkName = ""; 
      } 
    }
   else if  ($record['table_name'] == 'Section')
   {
      $strSQL = "SELECT sub_section_name FROM hrd_sub_section WHERE sub_section_code = '".$record['link_code']."'";
      $resExec = $db->execute($strSQL);
      if ($rowTmp  =  $db->fetchrow($resExec)) {
         $strLinkName = $rowTmp['sub_section_name'];
      } else {
         $strLinkName = ""; 
      } 
    }
    
      return $strLinkName;
  }      

  
  // fungsi untuk menghapus data
  function deleteData() 
  {
    global $myDataGrid;
  
    $arrKeys = array();
    foreach ($myDataGrid->checkboxes as $strValue)
      $arrKeys['id'][] = $strValue;

    $dataHrdWorkSchedule = new cHrdWorkSchedule();    
    $dataHrdWorkSchedule->deleteMultiple($arrKeys);
    
    $myDataGrid->message = $dataHrdWorkSchedule->strMessage;
  } //deleteData

  function printEditLink($params)
  {
      extract($params);
      if (isset($record['id']) && $record['id'] != '') {
          return "<a href=\"work_schedule.php?dataID=".$record['id']."\">" .getWords('edit'). "</a>";
      }
  }
?>