<?php
  include_once('../global/session.php');
  include_once('global.php');
  include_once('../global/common_data.php');
  include_once('../includes/datagrid2/datagrid.php');
  include_once('../includes/form2/form2.php');
  include_once('../classes/hrd/hrd_work_schedule.php');

  
  $dataPrivilege = getDataPrivileges(basename($_SERVER['PHP_SELF']), $bolCanView, $bolCanEdit, $bolCanDelete, $bolCanApprove, $bolCanCheck, $bolCanAcknowledge, $bolCanApprove2);
  if (!$bolCanView) die(getWords('view denied'));
 
  $db = new CdbClass;
  $arrData = array();
  if ($db->connect()) 
  {
      $strDataID = getRequestValue('dataID');
      if ($strDataID != '') {
          $strSQL = "SELECT start_time, finish_time, workday, table_name, link_code FROM hrd_work_schedule WHERE id = '$strDataID';";
          $res = $db->execute($strSQL);
          if ($row = $db->fetchrow($res)) {
              $arrData = $row;
          }
      }
      else {
          $arrData['start_time']  = substr(getSetting("start_time"), 0, 5);
          $arrData['finish_time'] = substr(getSetting("finish_time"), 0, 5);
          $arrData['workday'] = -1;
      }
      $isNew = ($strDataID == "");
      $strDivision = (isset($arrData['table_name']) && $arrData['table_name'] == 'Division') ? $arrData['link_code'] : '';
      $strDepartment = (isset($arrData['table_name']) && $arrData['table_name'] == 'Department') ? $arrData['link_code'] : '';
      $strSection = (isset($arrData['table_name']) && $arrData['table_name'] == 'Section') ? $arrData['link_code'] : '';
      $strSubSection = (isset($arrData['table_name']) && $arrData['table_name'] == 'SubSection') ? $arrData['link_code'] : '';
      $strEmployeeID = (isset($arrData['table_name']) && $arrData['table_name'] == 'Employee') ? $arrData['link_code'] : '';

    if ($bolCanEdit)
    {
      $f = new clsForm("formInput", 2, "100%", "");
      $f->caption = strtoupper($strWordsINPUTDATA);

      $f->addHidden("dataID", $strDataID);
      $f->addHidden("dataLinkCode", $strDataLinkCode);
      $f->addHidden("dataTableName", $strDataTableName);

      $f->addSelect(getWords("division"), "dataDivision", getDataListDivision($strDivision, true, ""), array("style" => "width: 200px", "onChange" => "javascript:myClient.setTableName('Division', this.value)"), "string", false, true, true);

      $f->addSelect(getWords("department"), "dataDepartment", getDataListDepartment($strDepartment, true, ""), array("style" => "width: 200px", "onChange" => "javascript:myClient.setTableName('Department', this.value)"),  "string", false, true, true);

      $f->addSelect(getWords("unit"), "dataSection", getDataListSection($strSection, true, ""), array("style" => "width: 200px", "onChange" => "javascript:myClient.setTableName('Section', this.value)"), "string", false, true, true);
      $f->addSelect(getWords("section"), "dataSubSection", getDataListSubSection($strSubSection, true, ''), array("style" => "width: 200px", "onChange" => "javascript:myClient.setTableName('SubSection', this.value)"), "string", false, true, true);


      $f->addInputAutoComplete(getwords("n i k"), "dataEmployee", getDataEmployee($strEmployeeID),  array("onChange" => "javascript:myClient.setTableName('Employee', this.value)", "style" => "width:200"), "string",  false);
      $f->addLabelAutoComplete("", "dataEmployee", "");



      $f->addSelect(getWords("workday"), "dataWorkday", getDataListDayName($arrData['workday'], true, -1),"", "integer", false, true, true);
      $f->addCheckBox(getWords("day off"),"dataDayOff", null,  array("onChange" => "javascript:myClient.setDayOff(this.checked)"), null, false, true, true);
      $f->addInput(getWords("start time"), "dataStartTime", $arrData['start_time'], array("size" => 10, "maxlength" => 5, "class" => "t input_mask mask_time"), "string", false, true, true);
      $f->addInput(getWords("finish time"), "dataFinishTime", $arrData['finish_time'], array("size" => 10, "maxlength" => 5, "class" => "t input_mask mask_time"), "string", false, true, true);

      $f->addSubmit("btnSave", getWords("save"), array("onClick" => "fixLinkCode(); return confirm('".getWords('do you want to save this entry?')."');"), true, true, "", "", "saveData()");
      $f->addButton("btnAdd", getWords("add new"), array("onClick" => "javascript:myClient.editData(0);"));
      
      $formInput = $f->render();
    }
    else
      $formInput = "";
  }
  $strConfirmDelete = getWords("are you sure to delete this selected data?");
  $strConfirmSave   = getWords("do you want to save this entry?");
  
  
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

  function printEditLink($params)
  {
    extract($params);
    return "
    <input type=hidden name='detailID$counter' id='detailID$counter' value='".$record['id']."' />
     <input type=hidden name='detailTableName$counter' id='detailTableName$counter' value='".$record['ori_table_name']."' />
     <input type=hidden name='detailLinkCode$counter' id='detailLinkCode$counter' value='".$record['link_code']."' />
     <input type=hidden name='detailWorkday$counter' id='detailWorkday$counter' value='".$record['workday']."' />
     <input type=hidden name='detailDayOff$counter' id='detailDayOff$counter' value='".$record['day_off']."' />
     <input type=hidden name='detailStartTime$counter' id='detailStartTime$counter' value='".$record['start_time']."' />
     <input type=hidden name='detailFinishTime$counter' id='detailFinishTime$counter' value='".$record['finish_time']."' />
     <a href=\"javascript:myClient.editData($counter)\">" .getWords('edit'). "</a>";
  }
  
  function printDayOff($params)
  {
    extract($params);
    if ($record['day_off'] == "t"){ 
            if (!isset($_REQUEST['btnExportXLS'])){    
             return "&radic;";
                }else{
             return "Yes";
            }

    }else{
      return "";
    }
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
      
  // fungsi untuk menyimpan data
  function saveData() 
  {
    global $f;
    global $isNew;
    
    $strDataID = $f->getValue('dataID');
    if ($f->getValue('dataTableName') != "")
    {
       $strmodified_byID = $_SESSION['sessionUserID'];
       
       if ($f->getValue('dataDayOff')) 
       {
          $strDayOff = 't';
          $strStart  = null;
          $strFinish = null;
       }
       else
       {
          $strDayOff = 'f';
          $strStart  = $f->getValue('dataStartTime');
          $strFinish = $f->getValue('dataFinishTime');
       }
       $strWorkday = ($f->getValue('dataWorkday') == "") ? -1 : $f->getValue('dataWorkday');

       $dataHrdWorkSchedule = new cHrdWorkSchedule();
       $data = array("link_code" => $f->getValue('dataLinkCode'),
                     "table_name" => $f->getValue('dataTableName'),
                     "workday" => $strWorkday,
                     "day_off" => $strDayOff,
                     "start_time" => $strStart,
                     "finish_time" => $strFinish);
       // simpan data -----------------------
       $bolSuccess = false;
       
       if ($isNew)
       {
         // data baru
         $bolSuccess = $dataHrdWorkSchedule->insert($data);
       } 
       else 
       {
         $bolSuccess = $dataHrdWorkSchedule->update(/*pk*/"id='".$strDataID."'", /*data to update*/ $data);
       }
       $f->message = $dataHrdWorkSchedule->strMessage;
       $f->msgClass = "style=\"border-color:green; color:green\"";
       header("location:work_schedule.php");
    }
    else
       $f->message = "Please fill either the division, department, section, or sub section";
  } // saveData
  
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

  function getLinkCode() {

  }

?>