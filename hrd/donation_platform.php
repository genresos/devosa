<?php
  include_once('../global/session.php');
  include_once('global.php');
  include_once('../global/common_data.php');
  include_once('../includes/datagrid2/datagrid.php');
  include_once('../includes/form2/form2.php');
  include_once('../classes/hrd/hrd_salary_grade.php');
  include_once('../classes/hrd/hrd_donation_type.php');
  include_once('../classes/hrd/hrd_donation_platform.php');

  
  $dataPrivilege = getDataPrivileges(basename($_SERVER['PHP_SELF']), $bolCanView, $bolCanEdit, $bolCanDelete, $bolCanApprove, $bolCanCheck, $bolCanAcknowledge, $bolCanApprove2);
  if (!$bolCanView) die(accessDenied($_SERVER['HTTP_REFERER']));

  $db = new CdbClass;
  $strWordsDonationTypeSetup = getWords("donation type setup");
  $strWordsDonationQuotaSetup = getWords("donation quota setup");


  //INISIALISASI----------------------------------------------------------------------------------------------
  //ambil semua jenis trip
  $tblDonationType = new cHrdDonationType();
  $dataDonationType = $tblDonationType->findAll("", "code, name", "", null, 1, "code");


    $myDataGrid = new cDataGrid("formData","DataGrid1", "100%", "100%", false, false, false, false);
    $myDataGrid->caption = getWords(strtoupper(vsprintf(getWords("list of %s"), getWords($dataPrivilege['menu_name']))));
    $myDataGrid->pageSortBy = "grade_code";

    $myDataGrid->setAJAXCallBackScript(basename($_SERVER['PHP_SELF']));
    $myDataGrid->setPageLimit("all");
    $myDataGrid->addColumnNumbering(new DataGrid_Column("No", "", array('width'=>'30'), array('nowrap'=>'')));
    $myDataGrid->addColumn(new DataGrid_Column(getWords("grade code"), "grade_code", array('width' => '150'), array('nowrap' => '')));
    
    foreach($dataDonationType AS $strDonationCode => $arrDonationDetail)
    {
      $myDataGrid->addColumn(new DataGrid_Column(getWords($arrDonationDetail['name']), "amount_".$strDonationCode, array('width' => '75'), array('align' => 'center'), false, false, "", ($bolCanEdit) ? "printQuota()" : "", "", false));
      $myDataGrid->addColumn(new DataGrid_Column(getWords($arrDonationDetail['name']), "amount_".$strDonationCode, array('width' => '75','style' => 'display:none'), array('align' => 'center','style' => 'display:none'), false, false, "", "", "", true));
    }
    if($bolCanEdit)
      $myDataGrid->addButton("btnSave", "btnSave", "submit", getWords("save"), "onClick=\"javascript:return myClient.confirmSave();\"", "saveData()");

    $myDataGrid->addButtonExportExcel("Export Excel", $dataPrivilege['menu_name'].".xls", getWords($dataPrivilege['menu_name']));

    $myDataGrid->getRequest();
    //--------------------------------


    //get Data and set to Datagrid's DataSource by set the data binding (bind method)
    $tblDonationPlatform = new cHrdDonationPlatform();
    $tblSalaryGrade = new cHrdSalaryGrade();
    $dataSalaryGrade = $tblSalaryGrade->findAll("", "grade_code", "", null, 1, "grade_code");
    $myDataGrid->totalData  = $tblSalaryGrade->findCount();
    $dataset = $dataSalaryGrade;

    foreach($dataset AS $strGradeCode => $arrDetail)
    {

      //strGradeCode juga digunakan sebagai index pada dataset 
      $dataDonationPlatform = $tblDonationPlatform->findAll("grade_code = '".$strGradeCode."'", "grade_code, donation_code, amount", "", null, 1, "donation_code");    
      foreach($dataDonationPlatform AS $strDonationCode => $arrDonationDetail)
      {
        $dataset[$strGradeCode]['amount_'.$strDonationCode] = (isset($dataDonationPlatform[$strDonationCode])) ? $dataDonationPlatform[$strDonationCode]['amount'] : 0;
      }
    }

    $myDataGrid->bind($dataset);
    $DataGrid = $myDataGrid->render();


  function printQuota($params)
  {
    extract($params);
    $strDonationCode = substr($field, 7);
    return  generateInput("detailQuota_".$record['grade_code']."_".$strDonationCode, $value, "style=\"text-align:right\"");
  }
  // fungsi untuk menyimpan data
   function saveData() 
  {
    $tblDonationPlatform = new cHrdDonationPlatform();
    $tblSalaryGrade = new cHrdSalaryGrade();
    $dataSalaryGrade = $tblSalaryGrade->findAll("", "grade_code", "", null, 1, "grade_code");
    global $dataDonationType;
    global $myDataGrid;
    global $error;
    $strError = "";
    $bolSuccess = true;
    $strModifiedByID = $_SESSION['sessionUserID'];

    $arrData = $myDataGrid->checkboxes;
    //$strTripTypeID   = $arrData['dataTripType'];
    $data = array();
    foreach($dataSalaryGrade AS $strGradeCode => $arrGradeDetail)
    {
      $data['grade_code'] = $strGradeCode;
      foreach($dataDonationType AS $strDonationCode => $arrDonationDetail)
      {
        $data['donation_code'] = $strDonationCode;
        if(isset($arrData['detailQuota_'.$strGradeCode.'_'.$strDonationCode]))
        {
          if(!is_numeric($arrData['detailQuota_'.$strGradeCode.'_'.$strDonationCode])) 
          {
            $bolSuccess = false;
            $strError   = $error['invalid_number'];
            continue;
          }
          $data['amount'] = $arrData['detailQuota_'.$strGradeCode.'_'.$strDonationCode];
          $tblDonationPlatform->delete(array("donation_code" => $strDonationCode, "grade_code" => $strGradeCode));
          $tblDonationPlatform->insert($data);
        }
      }
    }

    if($bolSuccess)
      $myDataGrid->message = $tblDonationPlatform->strMessage;
    else
      $myDataGrid->errorMessage = $strError;


  } // saveData
 
  
    

  // fungsi untuk menghapus data
  function deleteData() 
  {
    global $myDataGrid;
  
    $arrKeys = array();
    foreach ($myDataGrid->checkboxes as $strValue)
    {
      $arrKeys['id'][] = $strValue;
      $arrKeys2['id_trip_type'][] = $strValue;
    }
    $tblHrdTripType = new cHrdTripType();    
    $tblHrdTripTypeCostSetting = new cHrdTripTypeCostSetting();    
    $tblHrdTripType->deleteMultiple($arrKeys);
    $tblHrdTripTypeCostSetting->deleteMultiple($arrKeys2);
 
    $myDataGrid->message = $tblHrdTripType->strMessage;
  } //deleteData

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



?>