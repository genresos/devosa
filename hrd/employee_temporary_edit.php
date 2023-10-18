<?php
  include_once('../global/session.php');
  include_once('global.php');
  include_once('../global/common_data.php');
  include_once('../global/employee_function.php');
  include_once('../includes/datagrid2/datagrid.php');
  include_once('../includes/form2/form2.php');
  include_once('../classes/hrd/hrd_employee.php');
  include_once('../classes/hrd/hrd_employee_temporary.php');
  include_once '../global/email_func.php';


  $dataPrivilege = getDataPrivileges(basename($_SERVER['PHP_SELF']), $bolCanView, $bolCanEdit, $bolCanDelete, $bolCanApprove, $bolCanCheck, $bolCanAcknowledge, $bolCanApprove2);
  if (!$bolCanView) die(accessDenied($_SERVER['HTTP_REFERER']));

  $db = new CdbClass;
  if ($db->connect())
  {
    $strWordsDataEntry = getWords("data entry");
    $strWordsEmployeeTemporaryDataList = getWords("employee temporary data list");
    getUserEmployeeInfo();
    $strIDEmployee = $arrUserInfo['id_employee'];
    //INISIALISASI------------------------------------------------------------------------------------------------------------------

    $strDataID          = getRequestValue('dataID');
    $isNew              = ($strDataID == "");
    $intDefaultWidth    = "250px";
    $arrData = getDataByID($strDataID);
 /*
  else
  {
    $arrData['dataEmployeeID']        = getPostValue('dataEmployeeID');
    $arrData['dataNickname']          = getPostValue('dataNickname');
    $arrData['dataPrimaryAddress']    = getPostValue('dataPrimaryAddress');
    $arrData['dataPrimaryPhone']      = getPostValue('dataPrimaryPhone');
    $arrData['dataPrimaryCity']       = getPostValue('dataPrimaryCity');
    $arrData['dataPrimaryZip']        = getPostValue('dataPrimaryZip');
    $arrData['dataIDCard']            = getPostValue('dataIDCard');
    $arrData['dataBirthplace']        = getPostValue('dataBirthplace');
    $arrData['dataBirthday']          = getPostValue('dataBirthday');
    $arrData['dataWeddingDate']       = getPostValue('dataWeddingDate');
    $arrData['dataGender']            = getPostValue('dataGender');
    $arrData['dataEmail']             = getPostValue('dataEmail');
    $arrData['dataWeight']            = getPostValue('dataWeight');
    $arrData['dataHeight']            = getPostValue('dataHeight');
    $arrData['dataBloodType']         = getPostValue('dataBloodType');
    $arrData['dataEmergencyContact']  = getPostValue('dataEmergencyContact');
    $arrData['dataEmergencyAddress']  = getPostValue('dataEmergencyAddress');
    $arrData['dataEmergencyPhone']    = getPostValue('dataEmergencyPhone');
    $arrData['dataEmergencyRelation'] = getPostValue('dataEmergencyRelation');
    $arrData['dataDriverLicenseA']    = getPostValue('dataDriverLicenseA');
    $arrData['dataDriverLicenseB']    = getPostValue('dataDriverLicenseB');
    $arrData['dataDriverLicenseC']    = getPostValue('dataDriverLicenseC');
    $arrData['dataNPWP']              = getPostValue('dataNPWP');
    $arrData['dataJamsostekNo']       = getPostValue('dataJamsostekNo');
    $arrData['dataPassport']          = getPostValue('dataPassport');
    $arrData['dataInspouse']          = getPostValue('dataInspouse');
    $arrData['dataBankAccount']       = getPostValue('dataBankAccount');
    $arrData['dataBankAccountName']   = getPostValue('dataBankAccountName');
    $arrData['dataBankAccountType']   = getPostValue('dataBankAccountType');
    $arrData['dataBankBranch']        = getPostValue('dataBankBranch');
    $arrData['dataBankCode']          = getPostValue('dataBankCode');
    $arrData['dataBank2Account']      = getPostValue('dataBank2Account');
    $arrData['dataBank2AccountName']  = getPostValue('dataBank2AccountName');
    $arrData['dataBank2AccountType']  = getPostValue('dataBank2AccountType');
    $arrData['dataBank2Branch']       = getPostValue('dataBank2Branch');
    $arrData['dataBank2Code']         = getPostValue('dataBank2Code');
    $arrData['dataNote']              = getPostValue('dataNote');
  }*/

    scopeGeneralDataEntry($strDataEmployee, $_SESSION['sessionUserRole'], $arrUserInfo);
  // ------------------------------------------------------------------------------------------------------------------------------
    if ($bolCanEdit)
    {
      $f = new clsForm("formInput", 2, "100%", "");
      $f->caption = strtoupper($strWordsINPUTDATA);

      $f->addHidden("dataID", $strDataID);
      if ($isNew)
        $f->addInputAutoComplete(getwords("n i k"), "dataEmployeeID", getDataEmployee($strDataEmployee), "style='width:250px' ". $strEmpReadonly, "string", true);
      else
        $f->addInputAutoComplete(getwords("n i k"), "dataEmployeeID", getDataEmployee($arrData['dataEmployeeID']), "style='width:250px' ". $strEmpReadonly, "string", true);
      $f->addLabelAutoComplete("", "dataEmployeeID", "");
      $f->addInput(getWords("n i k corporate"), "dataEmployeeID2", $arrData['dataEmployeeID2'], array("style" => "width:$intDefaultWidth", "maxlength" => 15), "string", false, true, true);
      $f->addInput(getWords("Name"), "dataEmployeeName", $arrData['dataEmployeeName'], array("style" => "width:$intDefaultWidth", "maxlength" => 15), "string", false, true, true);
      $f->addInput(getWords("nickname"), "dataNickname", $arrData['dataNickname'], array("style" => "width:$intDefaultWidth", "maxlength" => 15), "string", false, true, true);
      $f->addInput(getWords("birthday"), "dataBirthday", $arrData['dataBirthday'], array("style" => "width:$intDefaultWidth"), "date", false, true, true);
      $f->addInput(getWords("birthplace"), "dataBirthplace", $arrData['dataBirthplace'], array("style" => "width:$intDefaultWidth","maxlength" => 50), "string", false, true, true);
      $f->addSelect(getWords("sex"), "dataGender", getDataListGender($arrData['dataGender'], true, array("value" => "1", "text" => "", "selected" => false)),  array ("style" => "width:$intDefaultWidth"), "", false,true);
      $f->addTextArea(getWords("address"), "dataPrimaryAddress", $arrData['dataPrimaryAddress'], array("style" => "width:$intDefaultWidth", "maxlength" => 255), "string", false, true, true);
      $f->addInput(getWords("city"), "dataPrimaryCity", $arrData['dataPrimaryCity'], array("style" => "width:$intDefaultWidth", "maxlength" => 131), "string", false, true, true);
      $f->addInput(getWords("zip"), "dataPrimaryZip", $arrData['dataPrimaryZip'], array("style" => "width:$intDefaultWidth", "maxlength" => 15), "string", false, true, true);
      $f->addInput(getWords("phone"), "dataPrimaryPhone", $arrData['dataPrimaryPhone'], array("style" => "width:$intDefaultWidth", "maxlength" => 31), "string", false, true, true);
      $f->addInput(getWords("email"), "dataEmail", $arrData['dataEmail'], array("style" => "width:$intDefaultWidth","maxlength" => 63), "string", false, true, true);
      $f->addInput(getWords("emergency contact"), "dataEmergencyContact", $arrData['dataEmergencyContact'], array("style" => "width:$intDefaultWidth", "maxlength" => 63), "string", false, true, true);
      $f->addInput(getWords("relation"), "dataEmergencyRelation", $arrData['dataEmergencyRelation'], array("style" => "width:$intDefaultWidth", "maxlength" => 31), "string", false, true, true);
      $f->addTextArea(getWords("emergency address"), "dataEmergencyAddress", $arrData['dataEmergencyAddress'], array("style" => "width:$intDefaultWidth", "maxlength" => 255), "string", false, true, true);
      $f->addInput(getWords("emergency phone"), "dataEmergencyPhone", $arrData['dataEmergencyPhone'], array("style" => "width:$intDefaultWidth", "maxlength" => 31), "string", false, true, true);
      $f->addInput(getWords("weight")." (kg)", "dataWeight", $arrData['dataWeight'], array("style" => "width:$intDefaultWidth","maxlength" => 7), "numeric", false, true, true);
      $f->addInput(getWords("height")." (cm)", "dataHeight", $arrData['dataHeight'], array("style" => "width:$intDefaultWidth","maxlength" => 7), "numeric", false, true, true);
      $f->addSelect(getWords("blood type"), "dataBloodType", getDataListBloodType($arrData['dataBloodType'],true, array( "value" => "", "text" => "", "selected" => false)),  array ("style" => "width:$intDefaultWidth"), "", false, true,true);
      $f->addInput(getWords("K T P"), "dataIDCard", $arrData['dataIDCard'], array("style" => "width:$intDefaultWidth", "maxlength" => 31), "string", false, true, true);
      $f->addInput(getWords("K T P valid until"), "dataIDCardValid", $arrData['dataIDCardValid'], array("style" => "width:$intDefaultWidth", "maxlength" => 31), "date", false, true, true);
      $f->addInput(getWords("driver license a"), "dataDriverLicenseA", $arrData['dataDriverLicenseA'], array("style" => "width:$intDefaultWidth", "maxlength" => 31), "string", false, true, true);
      $f->addInput(getWords("driver license a valid until"), "dataDriverLicenseAValid", $arrData['dataDriverLicenseAValid'], array("style" => "width:$intDefaultWidth", "maxlength" => 31), "date", false, true, true);
      $f->addInput(getWords("driver license b"), "dataDriverLicenseB", $arrData['dataDriverLicenseB'], array("style" => "width:$intDefaultWidth", "maxlength" => 31), "string", false, true, true);
      $f->addInput(getWords("driver license b valid until"), "dataDriverLicenseBValid", $arrData['dataDriverLicenseBValid'], array("style" => "width:$intDefaultWidth", "maxlength" => 31), "date", false, true, true);
      $f->addInput(getWords("driver license c"), "dataDriverLicenseC", $arrData['dataDriverLicenseC'], array("style" => "width:$intDefaultWidth", "maxlength" => 31), "string", false, true, true);
      $f->addInput(getWords("driver license c valid until"), "dataDriverLicenseCValid", $arrData['dataDriverLicenseCValid'], array("style" => "width:$intDefaultWidth", "maxlength" => 31), "date", false, true, true);
      $f->addSelect(getWords("nationality"), "dataNationality", getDataListNationality($arrData['dataNationality'], true, array("value" => "", "text" => "", "selected" => false)),  array ("style" => "width:$intDefaultWidth"), "", false, true, true);

      $f->addInput(getWords("passport"), "dataPassport", $arrData['dataPassport'], array("style" => "width:$intDefaultWidth", "maxlength" => 31), "string", false, true, true);
      $f->addInput(getWords("passport valid until"), "dataPassportValid", $arrData['dataPassportValid'], array("style" => "width:$intDefaultWidth", "maxlength" => 31), "date", false, true, true);
      $f->addSelect(getWords("religion"), "dataReligion", getDataListReligion($arrData['dataReligion'], true, array("value" => "", "text" => "", "selected" => false)),  array ("style" => "width:$intDefaultWidth"), "", false, true, true);
      $f->addSelect(getWords("education level"), "dataEducation", getDataListEducationLevel($arrData['dataEducation'], true, array("value" => "", "text" => "", "selected" => true)),  array ("style" => "width:$intDefaultWidth"), "", false, true, true);
      $f->addSelect(getWords("major"), "dataMajor", getDataListMajor($arrData['dataMajor'], true, array("value" => "", "text" => "", "selected" => true)),  array ("style" => "width:$intDefaultWidth"), "", false, true, true);
      $f->addInput(getWords("wedding date"), "dataWeddingDate", $arrData['dataWeddingDate'], array("style" => "width:$intDefaultWidth"), "date", false, true, true);
      $f->addInput(getWords("transport"), "dataTransport", $arrData['dataTransport'], array("style" => "width:$intDefaultWidth", "maxlength" => 31), "string", false, true, true);
      $f->addInput(getWords("transport fee"), "dataTransportFee", $arrData['dataTransportFee'], array("style" => "width:$intDefaultWidth", "maxlength" => 31), "string", false, false, true);
      //$f->addSelect(getWords("family status"), "dataFamily", getDataListFamilyStatus($arrData['dataFamily'], true, array("value" => "", "text" => "", "selected" => false)),  array ("style" => "width:$intDefaultWidth"), "", false, true, true);
      //$f->addSelect(getWords("living cost status"), "dataLivingCost", getDataListLivingCost($arrData['dataLivingCost'], true, array("value" => "", "text" => "", "selected" => false)),  array ("style" => "width:$intDefaultWidth"), "", false, true, true);
      //$f->addSelect(getWords("medical quota status"), "dataMedicalQuota", getDataListFamilyStatus($arrData['dataMedicalQuota'], true, array("value" => "", "text" => "", "selected" => false)),  array ("style" => "width:$intDefaultWidth"), "", false, true, true);
      $f->addCheckBox(getWords("spouse"), "dataInspouse", $arrData['dataInspouse'], array(), "string", false, false, true,"", "<br>&nbsp;<br>");
      $f->addSelect(getWords("company"), "dataCompany", getDataListCompany($arrData['dataCompany'], true, array("value" => "", "text" => "", "selected" => true),$strKriteria2 ),  array ("style" => "width:$intDefaultWidth"), "", false, false, true);
      $f->addSelect(getWords("employee status"), "dataEmployeeStatus", getDataListEmployeeStatus($arrData['dataEmployeeStatus'], true, array("value" => "", "text" => "", "selected" => "")),  array ("style" => "width:$intDefaultWidth"), "", false, false, true);
      $f->addSelect(getWords("subsection"), "dataSubsection", getDataListSubSection($arrData['dataSubsection'], true, array("value" => "", "text" => "", "selected" => true)),  array ("style" => "width:$intDefaultWidth"), "", false, false, true);
      $f->addSelect(getWords("section"), "dataSection", getDataListSection($arrData['dataSection'], true, array("value" => "", "text" => "", "selected" => true)),  array ("style" => "width:$intDefaultWidth"), "", false, false, true);
      $f->addSelect(getWords("departement"), "dataDepartement", getDataListDepartment($arrData['dataDepartement'], true, array("value" => "", "text" => "", "selected" => true)),  array ("style" => "width:$intDefaultWidth"), "", false, false, true);
      $f->addSelect(getWords("division"), "dataDivision", getDataListDivision($arrData['dataDivision'], true, array("value" => "", "text" => "", "selected" => true)),  array ("style" => "width:$intDefaultWidth"), "", false, false, true);
      $f->addSelect(getWords("management"), "dataManagement", getDataListManagement($arrData['dataManagement'], true, array("value" => "", "text" => "", "selected" => true)),  array ("style" => "width:$intDefaultWidth"), "", false, false, true);
      $f->addSelect(getWords("branch"), "dataBranch", getDataListBranch($arrData['dataBranch'], true, array("value" => "", "text" => "", "selected" => true)),  array ("style" => "width:$intDefaultWidth"), "", false, false, true);
      $f->addSelect(getWords("branch penugasan"), "dataBranchPenugasan", getDataListBranch($arrData['dataBranchPenugasan'], true, array("value" => "", "text" => "", "selected" => true)),  array ("style" => "width:$intDefaultWidth"), "", false, false, true);
      $f->addSelect(getWords("level"), "dataLevel", getDataListPosition($arrData['dataLevel'], true, array("value" => "", "text" => "", "selected" => true)),  array ("style" => "width:$intDefaultWidth"), "", false, false, true);
      $f->addSelect(getWords("functional position"), "dataFunctional", getDataListFunctionalPosition($arrData['dataFunctional'], true, array("value" => "", "text" => "", "selected" => true)),  array ("style" => "width:$intDefaultWidth"), "", false, false, true);
      $f->addInput(getWords("npwp"), "dataNPWP", $arrData['dataNPWP'], array("style" => "width:$intDefaultWidth", "maxlength" => 63), "string", false, true, true);
      $f->addInput(getWords("n p w p registered on"), "dataNPWPRegis", $arrData['dataNPWPRegis'], array("style" => "width:$intDefaultWidth", "maxlength" => 31), "date", false, true, true);
      $f->addInput(getWords("B P J S ketenagakerjaan no"), "dataJamsostekNo", $arrData['dataJamsostekNo'], array("style" => "width:$intDefaultWidth", "maxlength" => 50), "string", false, true, true);
      $f->addInput(getWords("B P J S ketenagakerjaan registered on"), "dataJamsostekRegis", $arrData['dataJamsostekRegis'], array("style" => "width:$intDefaultWidth", "maxlength" => 31), "date", false, true, true);
      $f->addInput(getWords("B P J S kesehatan no"), "dataBPJSNo", $arrData['dataBPJSNo'], array("style" => "width:$intDefaultWidth", "maxlength" => 50), "string", false, true, true);
      $f->addInput(getWords("B P J S kesehatan registered on"), "dataBPJSRegis", $arrData['datBPJSRegis'], array("style" => "width:$intDefaultWidth", "maxlength" => 31), "date", false, true, true);
      $f->addCheckBox(getWords("zakat"), "dataZakat", $arrData['dataZakat'], array(), "string", false, true, true,"", "<br>&nbsp;<br>");
      $f->addSelect(getWords("bank code"), "dataBankCode", getDataListBank($arrData['dataBankCode'], true, array("value" => "", "text" => "", "selected" => true)), array ("style" => "width:$intDefaultWidth"), "", false);
      $f->addInput(getWords("bank branch"), "dataBankBranch", $arrData['dataBankBranch'], array("style" => "width:$intDefaultWidth", "maxlength" => 63), "string", false, true, true);
      $f->addInput(getWords("bank account type"), "dataBankAccountType", $arrData['dataBankAccountType'], array("style" => "width:$intDefaultWidth", "maxlength" => 31), "string", false, true, true);
      $f->addInput(getWords("bank account"), "dataBankAccount", $arrData['dataBankAccount'], array("style" => "width:$intDefaultWidth", "maxlength" => 59), "string", false, true, true);
      $f->addInput(getWords("bank account name"), "dataBankAccountName", $arrData['dataBankAccountName'], array("style" => "width:$intDefaultWidth", "maxlength" => 127), "string", false, true, true);

//      $f->addSelect(getWords("2nd bank code"), "dataBank2Code", getDataListBank($arrData['dataBank2Code'], true, array("value" => "", "text" => "", "selected" => true)), array ("style" => "width:$intDefaultWidth"), "", false);
//      $f->addInput(getWords("2nd bank branch"), "dataBank2Branch", $arrData['dataBank2Branch'], array("style" => "width:$intDefaultWidth", "maxlength" => 63), "string", false, true, true);
//      $f->addInput(getWords("2nd bank account type"), "dataBank2AccountType", $arrData['dataBank2AccountType'], array("style" => "width:$intDefaultWidth", "maxlength" => 31), "string", false, true, true);
//      $f->addInput(getWords("2nd bank account"), "dataBank2Account", $arrData['dataBank2Account'], array("style" => "width:$intDefaultWidth", "maxlength" => 59), "string", false, true, true);
//      $f->addInput(getWords("2nd bank account name"), "dataBank2AccountName", $arrData['dataBank2AccountName'], array("style" => "width:$intDefaultWidth", "maxlength" => 127), "string", false, true, true);
      $f->addTextArea(getWords("note"), "dataNote", $arrData['dataNote'], array("style" => "width:$intDefaultWidth", "maxlength" => 255), "string", false, true, true);
//      $f->addFile(getWords("attachment"), "dataAttachment", $arrData['dataAttachment'], array("style" => "width:$intDefaultWidth", "maxlength" => 127), "string", false);

      $strConfirmSave = getWords("save");
      $f->addSubmit("btnSave", $strConfirmSave, array("onClick" => "javascript:myClient.confirmSave();"), true, true, "", "", "saveData()");
      $f->addButton("btnClear", getWords("clear form"), array("onClick" => "addNew();"));
      $formInput = $f->render();
    }
    else
      $formInput = "";
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


  function getDataByID($strDataID)
  {
    global $db;
    global $strIDEmployee;


    if ($strIDEmployee == "")
    {
      $dataEmployeeTemporary = array('employee_id' => "", 'employee_id_2' => "", 'employee_name' => '', 'nickname' => "", 'primary_address' => "", 'primary_phone' => "",
      'primary_city' => "", 'primary_zip' => "", 'id_card' => "", 'employee_status' => "",'birthplace' => "", 'birthday' => "", 'wedding_date' => "",
      'gender' => "", 'email' => "", 'weight' => "", 'height' => "", 'blood_type' => "", 'emergency_contact' => "", 'emergency_address' => "",
      'emergency_phone' => "", 'emergency_relation' => "", 'driver_license_a' => "", 'driver_license_b' => "", 'driver_license_c' => "", 'npwp' => "",
      'jamsostek_no' => "", 'passport' => "", 'inspouse' => "", 'bank_account' => "", 'bank_account_name' => "", 'bank_account_type' => "", 'bank_branch' => "",
      'bank_code' => "", 'note' => "",
      'nationality' => "", 'religion_code' => "", 'education_level_code' => "", 'transport_code' => "", 'transport_fee' => "", 'family_status_code' => "",
      'living_cost_code' => "", 'medical_quota_status' => "", 'id_company' => "", 'sub_section_code' => "", 'section_code' => "", 'department_code' => "",
      'division_code' => "", 'management_code' => "", 'branch_code' => "", 'branch_penugasan_code' => "", 'position_code' => "", 'functional_code' => "", 'zakat' => "", 'id_card_valid' => "", 'driver_license_a_valid' => "", 'driver_license_b_valid' => "", 'driver_license_c_valid' => ""
      , 'passport_valid' => "", 'major' => "", 'npwp_regis' => "", 'jamsostek_regis' => "", 'bpjs_no' => "", 'bpjs_regis' => "");
    }
    else if ($strDataID == "")
    {
      $tblEmployeeTemporary = new cHrdEmployee();
      $dataEmployeeTemporary = $tblEmployeeTemporary->find("id = $strIDEmployee");
    }
    if ($strDataID != "")
    {

      $tblEmployeeTemporary = new cHrdEmployeeTemporary();
      $dataEmployeeTemporary = $tblEmployeeTemporary->findAll("id = $strDataID", "", "", null, 1, "id");
      $dataEmployeeTemporary = $dataEmployeeTemporary[$strDataID];
    }

    $arrResult['dataEmployeeID']        = $dataEmployeeTemporary['employee_id'];
    $arrResult['dataEmployeeID2']       = $dataEmployeeTemporary['employee_id_2'];
    $arrResult['dataEmployeeName']      = $dataEmployeeTemporary['employee_name'];
    $arrResult['dataNickname']          = $dataEmployeeTemporary['nickname'];
    $arrResult['dataPrimaryAddress']    = $dataEmployeeTemporary['primary_address'];
    $arrResult['dataPrimaryPhone']      = $dataEmployeeTemporary['primary_phone'];
    $arrResult['dataPrimaryCity']       = $dataEmployeeTemporary['primary_city'];
    $arrResult['dataPrimaryZip']        = $dataEmployeeTemporary['primary_zip'];
    $arrResult['dataIDCard']            = $dataEmployeeTemporary['id_card'];
    $arrResult['dataBirthplace']        = $dataEmployeeTemporary['birthplace'];
    $arrResult['dataBirthday']          = $dataEmployeeTemporary['birthday'];
    $arrResult['dataWeddingDate']       = $dataEmployeeTemporary['wedding_date'];
    $arrResult['dataGender']            = $dataEmployeeTemporary['gender'];
    $arrResult['dataEmail']             = $dataEmployeeTemporary['email'];
    $arrResult['dataWeight']            = $dataEmployeeTemporary['weight'];
    $arrResult['dataHeight']            = $dataEmployeeTemporary['height'];
    $arrResult['dataBloodType']         = $dataEmployeeTemporary['blood_type'];
    $arrResult['dataEmergencyContact']  = $dataEmployeeTemporary['emergency_contact'];
    $arrResult['dataEmergencyAddress']  = $dataEmployeeTemporary['emergency_address'];
    $arrResult['dataEmergencyPhone']    = $dataEmployeeTemporary['emergency_phone'];
    $arrResult['dataEmergencyRelation'] = $dataEmployeeTemporary['emergency_relation'];
    $arrResult['dataDriverLicenseA']    = $dataEmployeeTemporary['driver_license_a'];
    $arrResult['dataDriverLicenseB']    = $dataEmployeeTemporary['driver_license_b'];
    $arrResult['dataDriverLicenseC']    = $dataEmployeeTemporary['driver_license_c'];
    $arrResult['dataNPWP']              = $dataEmployeeTemporary['npwp'];
    $arrResult['dataNationality']       = $dataEmployeeTemporary['nationality'];
    $arrResult['dataReligion']          = $dataEmployeeTemporary['religion_code'];
    $arrResult['dataEducation']         = $dataEmployeeTemporary['education_level_code'];
    $arrResult['dataTransport']         = $dataEmployeeTemporary['transport_code'];
    $arrResult['dataTransportFee']      = $dataEmployeeTemporary['transport_fee'];
    //$arrResult['dataFamily']            = $dataEmployeeTemporary['family_status_code'];
    //$arrResult['dataLivingCost']        = $dataEmployeeTemporary['living_cost_code'];
    //$arrResult['dataMedicalQuota']      = $dataEmployeeTemporary['medical_quota_status'];
    $arrResult['dataCompany']           = $dataEmployeeTemporary['id_company'];
    $arrResult['dataSubsection']        = $dataEmployeeTemporary['sub_section_code'];
    $arrResult['dataSection']           = $dataEmployeeTemporary['section_code'];
    $arrResult['dataDepartement']       = $dataEmployeeTemporary['department_code'];
    $arrResult['dataDivision']          = $dataEmployeeTemporary['division_code'];
    $arrResult['dataManagement']        = $dataEmployeeTemporary['management_code'];
    $arrResult['dataBranch']            = $dataEmployeeTemporary['branch_code'];
    $arrResult['dataBranchPenugasan']   = $dataEmployeeTemporary['branch_penugasan_code'];
    $arrResult['dataLevel']             = $dataEmployeeTemporary['position_code'];
    $arrResult['dataFunctional']        = $dataEmployeeTemporary['functional_code'];
    $arrResult['dataZakat']             = $dataEmployeeTemporary['zakat'];
    $arrResult['dataJamsostekNo']       = $dataEmployeeTemporary['jamsostek_no'];
    $arrResult['dataPassport']          = $dataEmployeeTemporary['passport'];
    $arrResult['dataInspouse']          = $dataEmployeeTemporary['inspouse'];
    $arrResult['dataBankAccount']       = $dataEmployeeTemporary['bank_account'];
    $arrResult['dataBankAccountName']   = $dataEmployeeTemporary['bank_account_name'];
    $arrResult['dataBankAccountType']   = $dataEmployeeTemporary['bank_account_type'];
    $arrResult['dataBankBranch']        = $dataEmployeeTemporary['bank_branch'];
    $arrResult['dataBankCode']          = $dataEmployeeTemporary['bank_code'];
//    $arrResult['dataBank2Account']      = $dataEmployeeTemporary['bank2_account'];
//    $arrResult['dataBank2AccountName']  = $dataEmployeeTemporary['bank2_account_name'];
//    $arrResult['dataBank2AccountType']  = $dataEmployeeTemporary['bank2_account_type'];
//    $arrResult['dataBank2Branch']       = $dataEmployeeTemporary['bank2_branch'];
//    $arrResult['dataBank2Code']         = $dataEmployeeTemporary['bank2_code'];
    $arrResult['dataNote']              = $dataEmployeeTemporary['note'];
    $arrResult['dataEmployeeStatus']    = $dataEmployeeTemporary['employee_status'];

    $arrResult['dataIDCardValid']               = $dataEmployeeTemporary['id_card_valid'];
    $arrResult['dataDriverLicenseAValid']       = $dataEmployeeTemporary['driver_license_a_valid'];
    $arrResult['dataDriverLicenseBValid']       = $dataEmployeeTemporary['driver_license_b_valid'];
    $arrResult['dataDriverLicenseCValid']       = $dataEmployeeTemporary['driver_license_c_valid'];
    $arrResult['dataPassportValid']             = $dataEmployeeTemporary['passport_valid'];
    $arrResult['dataMajor']                     = $dataEmployeeTemporary['major'];
    $arrResult['dataNPWPRegis']                 = $dataEmployeeTemporary['npwp_regis'];
    $arrResult['dataJamsostekRegis']            = $dataEmployeeTemporary['jamsostek_regis'];
    $arrResult['dataBPJSRegis']                 = $dataEmployeeTemporary['bpjs_regis'];
    $arrResult['dataBPJSNo']                    = $dataEmployeeTemporary['bpjs_no'];

    return $arrResult;

  }


  // fungsi untuk menyimpan data
  function saveData()
  {
    global $f;
    global $db;
    global $isNew;

    echo $f->getValue('dataInspouse');
    if ($db->connect())
    {
      $strmodified_byID = $_SESSION['sessionUserID'];
      $tblEmployeeTemporary = new cHrdEmployeeTemporary();
      $data = array("employee_id" => $f->getValue('dataEmployeeID'),
                    "employee_name" => $f->getValue('dataEmployeeName'),
                    "employee_id_2" => $f->getValue('dataEmployeeID2'),
                    "nickname" => $f->getValue('dataNickname'),
                    "primary_address" => $f->getValue('dataPrimaryAddress'),
                    "primary_phone" => $f->getValue('dataPrimaryPhone'),
                    "primary_city" => $f->getValue('dataPrimaryCity'),
                    "primary_zip" => $f->getValue('dataPrimaryZip'),
                    "id_card" => $f->getValue('dataIDCard'),
                    "birthplace" => $f->getValue('dataBirthplace'),
                    "birthday" => $f->getValue('dataBirthday'),
                    "wedding_date" => $f->getValue('dataWeddingDate'),
                    "gender" => $f->getValue('dataGender'),
                    "email" => $f->getValue('dataEmail'),
                    "weight" => $f->getValue('dataWeight'),
                    "height" => $f->getValue('dataHeight'),
                    "blood_type" => $f->getValue('dataBloodType'),
                    "emergency_contact" => $f->getValue('dataEmergencyContact'),
                    "emergency_address" => $f->getValue('dataEmergencyAddress'),
                    "emergency_phone" => $f->getValue('dataEmergencyPhone'),
                    "emergency_relation" => $f->getValue('dataEmergencyRelation'),
                    "driver_license_a" => $f->getValue('dataDriverLicenseA'),
                    "driver_license_b" => $f->getValue('dataDriverLicenseB'),
                    "driver_license_c" => $f->getValue('dataDriverLicenseC'),
                    "npwp" => $f->getValue('dataNPWP'),
                    "jamsostek_no" => $f->getValue('dataJamsostekNo'),
                    "passport" => $f->getValue('dataPassport'),
                    "inspouse" => ($f->getValue('dataInspouse') == 't') ? 't' : 'f',
                    "bank_account" => $f->getValue('dataBankAccount'),
                    "bank_account_name" => $f->getValue('dataBankAccountName'),
                    "bank_account_type" => $f->getValue('dataBankAccountType'),
                    "bank_branch" => $f->getValue('dataBankBranch'),
                    "bank_code" => $f->getValue('dataBankCode'),
//                    "bank2_account" => $f->getValue('dataBank2Account'),
//                    "bank2_account_name" => $f->getValue('dataBank2AccountName'),
//                    "bank2_account_type" => $f->getValue('dataBank2AccountType'),
//                    "bank2_branch" => $f->getValue('dataBank2Branch'),
//                    "bank2_code" => $f->getValue('dataBank2Code'),
                    //"family_status_code" => $f->getValue('dataFamily'),
                    "religion_code" => $f->getValue('dataReligion'),
                    "education_level_code" => $f->getValue('dataEducation'),
                    "division_code" => $f->getValue('dataDivision'),
                    "department_code" => $f->getValue('dataDepartement'),
                    "section_code" => $f->getValue('dataSection'),
                    "sub_section_code" => $f->getValue('dataSubsection'),
                    "position_code" => $f->getValue('dataLevel'),
                    "transport_code" => $f->getValue('dataTransport'),
                    "functional_code" => $f->getValue('dataFunctional'),
                    "zakat" => ($f->getValue('dataZakat') == 't') ? 't' : 'f',
                    "id_company" => $f->getValue('dataCompany'),
                    //"living_cost_code" => $f->getValue('dataLivingCost'),
                    //"medical_quota_status" => $f->getValue('dataMedicalQuota'),
                    "nationality" => $f->getValue('dataNationality'),
                    "management_code" => $f->getValue('dataManagement'),
                    "branch_code" => $f->getValue('dataBranch'),
                    "branch_penugasan_code" => $f->getValue('dataBranchPenugasan'),
                    "transport" => $f->getValue('dataTransport'),
                    "transport_fee" => $f->getValue('dataTransportFee'),
                    "employee_status" => $f->getValue('dataEmployeeStatus'),
                    "note" => $f->getValue('dataNote'),
                    "id_card_valid" => $f->getValue('dataIDCardValid'),
                    "driver_license_a_valid" => $f->getValue('dataDriverLicenseAValid'),
                    "driver_license_b_valid" => $f->getValue('dataDriverLicenseBValid'),
                    "driver_license_c_valid" => $f->getValue('dataDriverLicenseCValid'),
                    "passport_valid" => $f->getValue('dataPassportValid'),
                    "major" => $f->getValue('dataMajor'),
                    "npwp_regis" => $f->getValue('dataNPWPRegis'),
                    "jamsostek_regis" => $f->getValue('dataJamsostekRegis'),
                    "bpjs_regis" => $f->getValue('dataBPJSRegis'),
                    "bpjs_no" => $f->getValue('dataBPJSNo'));
      // simpan data trip type
      $bolSuccess = false;
      if ($isNew)
      {
        // data baru
        $bolSuccess = $tblEmployeeTemporary->insert($data);
        $strBody = "Name: ".$data['employee_name']." [".$data['employee_id']."]<br>";
        $strBody.= "Date: ".date("d-m-Y")."<br><br>";
        $strBody.= "Details are listed in Employee Temporary List";
        $strBody =  getBody(0,'Employee Temporary List',$strBody,$_SESSION['sessionUserID']);
        $strSubject = getSubject(0,'Employee Temporary List',$data['employee_id']);
        sendMail($strSubject,$strBody);
      }
      else
      {
        $bolSuccess = $tblEmployeeTemporary->update("id='".$f->getValue('dataID')."'", $data);
        $strBody = "Name: ".$data['employee_name']." [".$data['employee_id']."]<br>";
        $strBody.= "Date: ".date("d-m-Y")."<br><br>";
        $strBody.= "Details are listed in Employee Temporary List";
        $strBody =  getBody(0,'Employee Temporary List Updated',$strBody,$_SESSION['sessionUserID']);
        $strSubject = getSubject(0,'Employee Temporary List Updated',$data['employee_id']);
        sendMail($strSubject,$strBody);
      }
      if ($bolSuccess)
      {
        if ($isNew)
          $f->setValue('dataID', $tblEmployeeTemporary->getLastInsertId());
        else
          $f->setValue('dataID', $f->getValue('dataID'));
      }
      if (is_uploaded_file($_FILES["dataAttachment"]['tmp_name'])) {
        $arrNamaFileAttach = explode(".",$_FILES["dataAttachment"]['name']);
        $strNamaFileAttach = strtolower($data['employee_id']);

        if (count($arrNamaFileAttach) > 0) {
          $strNamaFileAttach .= ".". $arrNamaFileAttach[count($arrNamaFileAttach) -1];
        }

        clearstatcache();
        if (!is_dir("attachment")) {
          mkdir("attachment", 0755);
        }

        $strNamaFileLengkap = "attachment/temp/".$strNamaFileAttach;
        if (file_exists($strNamaFileLengkap)) {
          unlink($strNamaFileLengkap);
        }
        if (move_uploaded_file($_FILES['dataAttachment']['tmp_name'], $strNamaFileLengkap)) {
          // update data
          $strSQL  = "UPDATE hrd_employee_temporary SET attachment = '$strNamaFileAttach' WHERE id = '$strDataID' ";
          $resExec = $db->execute($strSQL);
        }
    }
    }

    $f->message = $tblEmployeeTemporary->strMessage;
  } // saveData
?>
