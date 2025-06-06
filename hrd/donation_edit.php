<?php
  include_once('../global/session.php');
  include_once('global.php');
  include_once('../global/common_data.php');
  include_once('../global/employee_function.php');
  include_once('../includes/datagrid2/datagrid.php');
  include_once('../includes/form2/form2.php');
  include_once('../classes/hrd/hrd_donation_type.php');
  include_once('../classes/hrd/hrd_donation_platform.php');
  include_once('../classes/hrd/hrd_donation.php');

  
  $dataPrivilege = getDataPrivileges(basename($_SERVER['PHP_SELF']), $bolCanView, $bolCanEdit, $bolCanDelete, $bolCanApprove, $bolCanCheck, $bolCanAcknowledge, $bolCanApprove2);
  if (!$bolCanView) die(accessDenied($_SERVER['HTTP_REFERER']));

 
  $db = new CdbClass;
  if ($db->connect())
  {
    $strWordsDataEntry          = getWords("data entry");
    $strWordsDonationList       = getWords("donation list");
    $strWordsDonationReport     = getWords("donation report");
    $strWordsDispositionForm    = getWords("disposition form");
    getUserEmployeeInfo();

    //INISIALISASI------------------------------------------------------------------------------------------------------------------

    //ambil semua jenis trip
    $tblDonationType = new cHrdDonationType();
    $dataDonationType = $tblDonationType->findAll("", "code, name, note", "", null, 1, "code");

    $tblDonationPlatform = new cHrdDonationPlatform();
    $strDataID          = getRequestValue('dataID');
    $isNew              = ($strDataID == "");
    if($strDataID != "") 
    {
      $arrData = getDataByID($strDataID);
    }
    else
    {
      $arrData['dataTempFormCode'] = $arrData['dataFormCode']  = (getPostValue('dataFormCode') != "") ? getPostValue('dataFormCode') : getFormCode($db, "SDM.DNT-",date(".m.Y."), "hrd_donation");
      $arrData['dataDonationCode']    = getPostValue('dataDonationCode');
      $arrData['dataEmployee']        = getPostValue('dataEmployee');
      $arrData['dataCreated']         = (getPostValue('dataCreated') != "") ? getPostValue('dataCreated') : date("Y-m-d");
      $arrData['dataEventDateFrom']   = (getPostValue('dataEventDateFrom') != "") ? getPostValue('dataEventDateFrom') : date("Y-m-d");
      $arrData['dataEventDateThru']   = (getPostValue('dataEventDateThru') != "") ? getPostValue('dataEventDateThru') : date("Y-m-d");
      $arrData['dataAmount']          = getPostValue('dataAmount');
      $arrData['dataRelationName']    = getPostValue('dataRelationName');
      $arrData['dataRelationType']    = getPostValue('dataRelationType');
      $arrData['dataNote']            = getPostValue('dataNote');
    }
    /*
    $strDataTripTypeID    = $arrData['dataTripType'];
    $strDataEmployee      = $arrData['dataEmployee'];
    $strDataDateFrom      = $arrData['dataDateFrom'];
    $strDataDateThru      = $arrData['dataDateThru'];
    $strDataProposalDate  = $arrData['dataProposalDate'];
    $strDataDestination   = $arrData['dataDestination'];
    $strDataPurpose       = $arrData['dataPurpose'];
    $strDataTask          = $arrData['dataTask'];
    $strDataNote          = $arrData['dataNote'];
*/
    $tblDonation = new cHrdDonation();
  //  $dataTrip = $tblTrip->find("

    $strReadonly = (scopeGeneralDataEntry($arrData['dataEmployee'], $_SESSION['sessionUserRole'], $arrUserInfo)) ? "readonly" : "";
  // ------------------------------------------------------------------------------------------------------------------------------
    if ($bolCanEdit)
    {
      $f = new clsForm("formInput", 1, "100%", "");
      $f->caption = strtoupper($strWordsINPUTDATA);

      $f->addHidden("dataID", $strDataID);
      $f->addHidden("dataFormCode", $arrData['dataFormCode']);
      $f->addInput(getWords("form code"), "dataTempFormCode", $arrData['dataFormCode'], array("size" => 45, "maxlength" => 30), "string", true, ($strDataID == ""), true);  
      $f->addSelect(getWords("donation type"), "dataDonationCode", getDataListDonationType($arrData['dataDonationCode']), "style='width:250px'", "", true);
      $f->addInputAutoComplete(getWords("employee ID"), "dataEmployee", getDataEmployee($arrData['dataEmployee']), "style='width:250px' ". $strReadonly, "string", true);
      $f->addLabelAutoComplete("", "dataEmployee", "");
    
    $total = 0;
          if($f->getValue('dataEmployee')!=""){
         $strSQL = "SELECT * FROM hrd_employee WHERE employee_id = '".$f->getValue('dataEmployee')."' ;";
         $resDb = $db->execute($strSQL);
          if ($rowDb = $db->fetchrow($resDb)) {
          $ide = $rowDb['id'];
          $total+=1;
          }
      }

      if ($arrData['dataDonationCode'] != "" && $total>0)
      { 

        //$intDuration = getIntervalDate($strDataDateFrom, $strDataDateThru);

        $arrEmployee = getEmployeeInfoByCode($db, $arrData['dataEmployee'], "grade_code, id");
        $strGradeCode = $arrEmployee['grade_code'];
        $arrQuota = $tblDonationPlatform->find("donation_code = '".$arrData['dataDonationCode']."' AND grade_code = '".$strGradeCode."'", "amount");
        //$fltQuota = (isset($arrQuota[$strCostID])) ? $intDuration * $arrQuota[$strCostID]['amount'] : 0;

        $fltAmount = (isset($arrQuota['amount'])) ? $arrQuota['amount'] : 0;

        //$f->addHidden("dataTripTypeName", $dataDonation Type[$strDataTripTypeID]['trip_type_name']);

        //tambah baris blank supaya semua field allowance terletak di kolom kanan

        $f->addSelect(getWords("relation name <br><small><small><i> leave empty if donation is for employee</i></small></small><br>"), "dataRelationName", getDataListEmployeeFamily($arrData['dataRelationName'], true, array("value" => "", "text" => "", "selected" => true), "id_employee = ".$ide), "", "", false);
        //$f->addInput(getWords("relation"), "dataRelationType", $arrData['dataRelationType'], array("size" => 79, "maxlength" => 255), "string", true, true, true);  

        $strBtnReset = "
        <input type=\"button\" name=\"btnReset\" value=\"".getWords("reset")."\" 
        onClick=\"\$(dataAmount).value = (\$(dataDefaultAmount).value);\">&nbsp;";
        $f->addInput(getWords("amount"), "dataAmount", $fltAmount , array("size" => 30, "maxlength" => 12), "numeric", true, true, true, "", $strBtnReset);
        $f->addHidden("dataGradeCode", $strGradeCode);
        $f->addHidden("dataDefaultAmount", $fltAmount);

        $f->addSubmit("btnSave", getWords("save"), array("onClick" => "javascript:myClient.confirmSave();"), true, true, "", "", "saveData()");
        $f->addButton("btnAdd", getWords("add new"), array("onClick" => "location.href='".basename($_SERVER['PHP_SELF']."';")));
      }
      else
      {
        $f->addLiteral("", "btnInfo", generateSubmit("btnInfo", getWords("get info")));
        $f->addLiteral("", "", "");

      }

      $f->addLabel(getWords("created date"), "dataCreated", $arrData['dataCreated']);
      $f->addInput(getWords("event date from"), "dataEventDateFrom", $arrData['dataEventDateFrom'], array("style" => "width:$strDateWidth"), "date", true, true, true);
      $f->addInput(getWords("event date thru"), "dataEventDateThru", $arrData['dataEventDateThru'], array("style" => "width:$strDateWidth"), "date", true, true, true);
      $f->addTextArea(getWords("note"), "dataNote", $arrData['dataNote'], array("cols"=>76, "rows"=>3), "string", false, true, true);

      $formInput = $f->render();
    }
    else
      $formInput = "";
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


  function getDataByID($strDataID)
  {
    global $db;
    global $dataDonationType;
    $tblDonation = new cHrdDonation();
    $dataDonation = $tblDonation->findAll("id = $strDataID", "", "", null, 1, "id");
    $arrTemp = getEmployeeInfoByID($db, $dataDonation[$strDataID]['id_employee'], "employee_id");
    $arrResult['dataEmployee']      = $arrTemp['employee_id'];
    $arrResult['dataTempFormCode']  = $dataDonation[$strDataID]['form_code'];
    $arrResult['dataFormCode']      = $dataDonation[$strDataID]['form_code'];
    $arrResult['dataDonationCode']  = $dataDonation[$strDataID]['donation_code'];
    $arrResult['dataCreated']       = $dataDonation[$strDataID]['created'];
    $arrResult['dataEventDateFrom'] = $dataDonation[$strDataID]['event_date_from'];
    $arrResult['dataEventDateThru'] = $dataDonation[$strDataID]['event_date_thru'];
    //$arrResult['dataDestination']   = $dataDonation[$strDataID]['destination'];
    $arrResult['dataRelationName']  = $dataDonation[$strDataID]['relation_name'];
    $arrResult['dataRelationType']  = $dataDonation[$strDataID]['relation_type'];
    $arrResult['dataAmount']        = $dataDonation[$strDataID]['amount'];
    $arrResult['dataStatus']        = $dataDonation[$strDataID]['status'];
    $arrResult['dataNote']          = $dataDonation[$strDataID]['note'];
    //foreach($arrTripCost[$dataDonation ['trip_type']
    return $arrResult;
  }
  // fungsi untuk menyimpan data
  function saveData() 
  {
    global $f;
    global $db;
    global $isNew;
    global $dataDonationType;
    if ($db->connect())
    {

         $total = 0;
          if($f->getValue('dataEmployee')!=""){
         $strSQL = "SELECT * FROM hrd_employee WHERE employee_id = '".$f->getValue('dataEmployee')."' ;";
         $resDb = $db->execute($strSQL);
          if ($rowDb = $db->fetchrow($resDb)) {
          $ide = $rowDb['id'];
          $total+=1;
          }
      }

      if($total>0){
      $strRelationType = "";
      $strmodified_byID = $_SESSION['sessionUserID'];
      $strIDEmployee = getIDEmployee($db, $f->getValue('dataEmployee'));
      if($f->getValue('dataRelationName') != "")
        list($strTemp, $strRelationType) = explode(" - ", $f->objects['dataRelationName']['text']);

      $tblDonation = new cHrdDonation();

      $data = array("donation_code" => $f->getValue('dataDonationCode'),
                    "form_code" => $f->getValue('dataFormCode'),
                    "grade_code" => $f->getValue('dataGradeCode'),
                    "id_employee" => ($strIDEmployee),
                    "created" => $f->getValue('dataCreated'),
                    "event_date_from" => $f->getValue('dataEventDateFrom'),
                    "event_date_thru" => $f->getValue('dataEventDateThru'),
                    "relation_name" => $f->getValue('dataRelationName'),
                    "relation_type" => $strRelationType,
                    "amount" => $f->getValue('dataAmount'),
                    "note" => $f->getValue('dataNote'));

      // simpan data donation
      $bolSuccess = false;
      if ($isNew)
      {
        // data baru
        $bolSuccess = $tblDonation->insert($data);
      } 
      else 
      {
        $bolSuccess = $tblDonation->update("id='".$f->getValue('dataID')."'", $data);
      }
      if ($bolSuccess)
      {
        if ($isNew)
          $f->setValue('dataID', $tblDonation->getLastInsertId());
        else
          $f->setValue('dataID', $f->getValue('dataID'));
      }
    }
    else
    {
      $f->message = "no connection";
      $f->msgClass = "bgError";
    }


    $f->message = $tblDonation->strMessage;
}
  } // saveData
  


?>