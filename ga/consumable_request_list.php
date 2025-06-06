<?php
  include_once('../global/session.php');
  include_once('global.php');
  include_once('form_object.php');
  include_once('../global/common_data.php');
  include_once('../includes/datagrid2/datagrid.php');
  include_once('../includes/form2/form2.php');
  include_once('../classes/ga/consumable_request.php');
  //================ END INCLUDE==========================================

  $dataPrivilege = getDataPrivileges(basename($_SERVER['PHP_SELF']), $bolCanView, $bolCanEdit, $bolCanDelete, $bolCanApprove, $bolCanCheck);
  if (!$bolCanView) die(accessDenied($_SERVER['HTTP_REFERER']));

  //INISIALISASI---------------------------------------------------------------------------------------------------------------
  $strWordsDataEntry        = getWords("data entry");
  $DataGrid = "";
  $myDataGrid = new cDataGrid("formData","DataGrid1", "100%", "100%", false, true, false);

  
  // *************************** BEGIN Fungsi ISI DATA GRID  ********************************************************************
   function getData($db)
  {
    global $dataPrivilege, $bolCanEdit, $bolCanDelete, $bolCanApprove, $bolCanCheck;
    global $f;
    global $DataGrid;
    global $myDataGrid;
    global $strKriteriaCompany;

    //global $arrUserInfo
    $arrData = $f->getObjectValues();

    $strKriteria = "";
    // GENERATE CRITERIA
    if ($arrData['dataIdItem'] != "") {
      $strKriteria .= "AND a.id_item = '".$arrData['dataIdItem']."'";
    }
    if (validStandardDate($arrData['dataRequestDateFrom']) && validStandardDate($arrData['dataRequestDateThru'])) {
      $strKriteria .= "AND (a.request_date::date BETWEEN '".$arrData['dataRequestDateFrom']."' AND '".$arrData['dataRequestDateThru']."')  ";
    }
    if ($arrData['dataEmployee']!= "") {
      $strKriteria .= "AND e.employee_id = '".$arrData['dataEmployee']."'";
    }
    if ($arrData['dataDepartment']!= "") {
      $strKriteria .= "AND a.department_code = '".$arrData['dataDepartment']."'";
    }
    $strKriteria .= $strKriteriaCompany;

    if ($db->connect())
    {	
    $myDataGrid = new cDataGrid("formData","DataGrid1");
    $myDataGrid->caption = getWords(strtoupper(vsprintf(getWords("list of %s"), getWords($dataPrivilege['menu_name']))));
    $myDataGrid->setAJAXCallBackScript(basename($_SERVER['PHP_SELF']));
    $myDataGrid->setCriteria($strKriteria);
    $myDataGrid->addColumnCheckbox(new DataGrid_Column("chkID", "id", array('width' => '30'), array('align'=>'center', 'nowrap' => '')), true);
    $myDataGrid->addColumnNumbering(new DataGrid_Column("No", "", array('width'=>'30'), array('nowrap'=>'')));
     $myDataGrid->addColumn(new DataGrid_Column(getWords("No.Req"), "consumable_req_no", array('width' => '100'),array('nowrap' => '')));  
    $myDataGrid->addColumn(new DataGrid_Column(getWords("Item"), "item_name", array('width' => '100'),array('nowrap' => '')));   
    $myDataGrid->addColumn(new DataGrid_Column(getWords("Employee"), "employee_name", array('width' => '100'),array('nowrap' => '')));
    $myDataGrid->addColumn(new DataGrid_Column(getWords("amount"), "item_amount", array('nowrap' => ''),  array("align" => "right"), false, false,
	  "", "formatNumber()", "numeric", true, 15));
    $myDataGrid->addColumn(new DataGrid_Column(getWords("department code"), "department_code", array('width' => '150'),array('nowrap' => '')));
    $myDataGrid->addColumn(new DataGrid_Column(getWords("request Date"), "request_date", array('width' => '150'),array('nowrap' => '')));
    $myDataGrid->addColumn(new DataGrid_Column(getWords("remark"), "remark", array('width' => '100'),array('nowrap' => '')));
    $myDataGrid->addColumn(new DataGrid_Column(getWords("status"), "status", "", "", true, true, "","printRequestStatus()"));
    
	// Jika punya hal akses edit
	if (!isset($_POST['btnExportXLS']) && $bolCanEdit)
    $myDataGrid->addColumn(new DataGrid_Column("", "", array("width" => "60"), array('align' => 'center', 'nowrap' => ''), false, false, "",
	 "printEditLink()", "", false /*show in excel*/));
    
	foreach($arrData AS $key => $value){
        $myDataGrid->strAdditionalHtml .= generateHidden($key, $value, "");
     }
   
	//tampilkan buttons sesuai dengan otoritas, common_function.php
    generateRoleButtons($bolCanEdit, $bolCanDelete, $bolCanCheck, $bolCanApprove, false, true, $myDataGrid);
    $myDataGrid->addButtonExportExcel("Export Excel", $dataPrivilege['menu_name'].".xls", getWords($dataPrivilege['menu_name']));
	$myDataGrid->getRequest();

      //get Data and set to Datagrid's DataSource by set the data binding (bind method)
	  $strSQLCOUNT = "SELECT COUNT(*) AS total FROM ga_consumable_request AS a LEFT JOIN hrd_employee AS e ON a.id_employee = e.id";
      $strSQL      = "SELECT i.item_name AS item_name,
	  				e.employee_name AS employee_name,
				    e.employee_id AS employee_id,
				    a.* 
                    FROM ga_consumable_request as a LEFT JOIN ga_item AS i ON a.id_item=i.id
				    LEFT JOIN hrd_employee AS e ON a.id_employee=e.id";

    $myDataGrid->totalData = $myDataGrid->getTotalData($db, $strSQLCOUNT);
    $dataset = $myDataGrid->getData($db, $strSQL);			

    //bind Datagrid with array dataset and branchCode
    $myDataGrid->bind($dataset);
    $DataGrid = $myDataGrid->render();
      
    }
    else $DataGrid = "";
    return $DataGrid;
   }
   //************** END FUNGSI ISI DATA GRID ****************************************************************************************

   //*********************************** FUNGSI TOMBOL EDIT******************************************
   function printEditLink($params)
   {
    extract($params);
    return "<a href=\"consumable_request_edit.php?dataID=".$record['id']."\">" .getWords('edit'). "</a>";
   }
  //******************************* END FUNGSI TOMBOL EDIT *******************************************
  
  //*************************** FUNGSI GENERATE BUTTON ***********************************//
  //
  
  //********************************** BEGIN FUNGSI PERBARUHI STATUS ******************************************************
  	function callChangeStatus() {

    global $_REQUEST;
    //print_r($_REQUEST);
    global $db;
    if (isset($_REQUEST['btnVerified'])) $intStatus = REQUEST_STATUS_VERIFIED;
    else if (isset($_REQUEST['btnChecked'])) $intStatus = REQUEST_STATUS_CHECKED;
    else if (isset($_REQUEST['btnApproved'])) $intStatus = REQUEST_STATUS_APPROVED;
    else if (isset($_REQUEST['btnDenied'])) $intStatus = REQUEST_STATUS_DENIED;
    else if (isset($_REQUEST['btnPaid'])) $intStatus = REQUEST_STATUS_PAID;
    changeStatus($db, $intStatus);
    }
  //************************************ END FUNGSI PERBARUHI STATUS **************************************************/
  
    
  //************************************ BEGIN FUNGSI VERIVY, CHECK, DENY, atau APROVE ********************************/
  function changeStatus($db, $intStatus) {
    global $_REQUEST;
    global $_SESSION;

    if (!is_numeric($intStatus)) {
      return false;
    }
    $strUpdate = "";
    $strSQL  = "";
    $strmodified_byID = $_SESSION['sessionUserID'];
    
    ///-- Buat perintah sql untuk status  
    if ($intStatus == REQUEST_STATUS_VERIFIED)
      $strUpdate = "verified_by = '" .$_SESSION['sessionUserID']."', verified_time = now(), ";
    else if ($intStatus == REQUEST_STATUS_CHECKED)
      $strUpdate = "checked_by = '" .$_SESSION['sessionUserID']."', checked_time = now(), ";
    else if ($intStatus == REQUEST_STATUS_APPROVED)
      $strUpdate = "approved_by = '" .$_SESSION['sessionUserID']."', approved_time = now(), ";
    else if ($intStatus == REQUEST_STATUS_DENIED)
      $strUpdate = "denied_by = '" .$_SESSION['sessionUserID']."', denied_time = now(), ";
    else if ($intStatus == REQUEST_STATUS_PAID)
      $strUpdate = "paid_by = '" .$_SESSION['sessionUserID']."', paid_time = now(), ";
    //-- END perintah sql 
    
    foreach ($_REQUEST as $strIndex => $strValue) 
    {
      if (substr($strIndex,0,15) == 'DataGrid1_chkID') 
      {
        $strSQLx = "SELECT status, employee_name,employee_id,t1.request_date
                    FROM ga_consumable_request AS t1 
                    LEFT JOIN hrd_employee AS t2 ON t1.id_employee = t2.id
                    WHERE t1.id = '$strValue' ";
        $resDb = $db->execute($strSQLx);
        if ($rowDb = $db->fetchrow($resDb)) 
        {  
          //the status should be increasing
          if ($rowDb['status'] < $intStatus && $rowDb['status'] != REQUEST_STATUS_DENIED )
          {
            $strSQL .= "UPDATE ga_consumable_request SET $strUpdate status = '$intStatus'  ";
            $strSQL .= "WHERE id = '$strValue'; "; 
            writeLog(ACTIVITY_EDIT, MODULE_PAYROLL, $rowDb['employee_name']." - ". $rowDb['employee_id'] ." - ". $rowDb['request_date'], $intStatus);
          }
        }
      }
      $resExec = $db->execute($strSQL);
    }
  } 
  //************************************ BEGIN FUNGSI VERIVY, CHECK, DENY, atau APROVE ********************************/
 
  
  
  /*********************************BEGIN  fungsi untuk menghapus data ***************************/
  function deleteData() 
  {
    global $myDataGrid;
  
    $arrKeys = array();
    foreach ($myDataGrid->checkboxes as $strValue)
    $arrKeys['id'][] = $strValue;

    $dataItem = new cGaConsumableRequest();    
    $dataItem->deleteMultiple($arrKeys);
    
    $myDataGrid->message = $dataItem->strMessage;
  }
  /*********************************END fungsi untuk menghapus data ****************************/

  //============================================================ MAIN PROGRAM ==========================================================
  $db = new CdbClass;
  if ($db->connect()) 
  {
    getUserEmployeeInfo();
    $arrUserList = getAllUserInfo($db);

    $strDataID   = getPostValue('dataID');
    scopeData($strDataEmployee, $strDataSubSection, $strDataSection, $strDataDepartment, $strDataDivision, $_SESSION['sessionUserRole'], $arrUserInfo);
    $strReadonly = (scopeCBDataEntry($strDataEmployee, $_SESSION['sessionUserRole'], $arrUserInfo)) ? "readonly" : "";
    
	/// Form ==================================================================================
    $f = new clsForm("formFilter", 2, "100%", "");
    $f->caption = strtoupper($strWordsFILTERDATA);
    $f->addSelect(getWords("Item"), "dataIdItem",getDataListItemCriteria($db,$arrData['dataIdItem'], true, array("value" => "",
	     "text" => "", "selected" => true),"Consumable"),  array ("style" => "width:200","size" =>10), "", false, true, true);
    $f->addInputAutoComplete(getWords("employee ID"), "dataEmployeeID", getDataEmployee(getInitialValue("Employee", null, $strDataEmployee)), 
	    "style=width:$strDefaultWidthPx ".$strReadonly, "string", false);
    $f->addLabelAutoComplete("", "dataEmployeeID", "");
	   // Jika St ReadOnly kosong-------------------------------------------------------------------------------------------------------------
	   if ($strReadonly==""){
		  $f->addSelect(getWords("departement"), "dataDepartment",getDataListDepartment($arrData['dataDepartement'], true, array("value" => "",
	      "text" => "", "selected" => true)),  array ("style" => "width:200"), "", false, true, true);
		}else{
	      $f->addSelect(getWords("department"), "dataDepartment", getDataListDepartment(getInitialValue("Department", "", $strDataDepartment), true),
	      array("style" => "width:$strDefaultWidthPx"), "", false, ($ARRAY_DISABLE_GROUP['department'] == ""));
	    }
	    //--------------------------------------------------------------------------------------------------------------------------------------
	$f->addInput(getWords("assignment date From"), "dataAssignmentDateFrom","", array("style" => "width:$strDateWidth"), "date", false, true, true);
	$f->addInput(getWords("assignment date Thru"), "dataAssignmentDateThru","", array("style" => "width:$strDateWidth"), "date", false, true, true);  
	$f->addSubmit("btnShow", getWords("show"), "", true, true, "", "", "");
	$f->addButton("btnAdd", getWords("Clear"), array("onClick" => "location.href='".basename($_SERVER['PHP_SELF']."';")));
    $formFilter = $f->render();
    getData($db);
    // END FORM====================================================================================
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