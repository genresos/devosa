<?php
  include_once('../global/session.php');
  include_once('global.php');
  include_once('form_object.php');

  $dataPrivilege = getDataPrivileges(basename($_SERVER['PHP_SELF']), $bolCanView, $bolCanEdit, $bolCanDelete, $bolCanApprove);
  if (!$bolCanView && $_POST['dataID'] == "") die(getWords('view denied'));
  
  //---- INISIALISASI ----------------------------------------------------
  $strWordsEmployeeData = getWords("employee data");
  $strWordsPrimaryInformation = getWords ("primary information ");
  $strWordsFacilities = getWords ("facilities ");
  $strWordsFamilyData = getWords ("family data");
  $strWordsEducationData = getWords ("education data");
  $strWordsTrainingData = getWords ("training data");
  $strWordsWorkExperiences = getWords ("work experiences");
  $strWordsResume = getWords ("resume");
  $strWordsINPUTDATA = getWords ("input data");
  $strWordsEmployeeID = getWords ("employee ID");
  $strWordsEmployeeName = getWords ("employee name");
  $strWordsFingerID = getWords ("finger id");
  $strWordsLetterCode = getWords ("letter code");
  $strWordsNickname = getWords ("nick name");
  $strWordsGender = getWords ("sex");
  $strWordsSalaryPaymentType = getWords ("salary payment type");
  $strWordsAddress = getWords ("address");
  $strWordsCityZip = getWords ("city / zip");
  $strWordsPhone = getWords ("phone");
  $strWordsEmail = getWords ("email");
  $strWordsEmergencyContact = getWords ("emergency contact");
  $strWordsRelation = getWords ("relation");
  $strWordsEmergencyAddress = getWords ("emergency address");
  $strWordsEmergencyPhone = getWords ("emergency phone");
  $strWordsBirthplace =  getWords ("birth place");
  $strWordsBirthday = getWords ("birthday");
  $strWordsWeight = getWords ("weight");
  $strWordsHeight = getWords ("height");
  $strWordsBloodType = getWords ("blood type");
  $strWordsIDCard = getWords ("K T P");
  $strWordsDriverLicenseA = getWords ("driving license A ");
  $strWordsDriverLicenseB = getWords ("driving license B");
  $strWordsDriverLicenseC = getWords ("driving license C");
  $strWordsNationality = getWords ("nationality");
  $strWordsPassport = getWords ("passport");
  $strWordsPhoto = getWords ("photo");
  $strWordsReligion = getWords ("religion");
  $strWordsEducationLevel = getWords ("education level");
  $strWordsFamilyStatus = getWords ("family status");
  $strWordsLivingCostStatus = getWords ("minimum living cost");
  $strWordsMedicalQuotaStatus = getWords ("medical quota status");
  $strWordsInspouse = getWords ("spouse");
  //$strWordsMaritalStatus = getWords ("marital status");
  $strWordsWeddingDate = getWords ("wedding date");
  $strWordsTransport = getWords ("transport");
  $strWordsTransportFee = getWords ("transport fee");
  $strWordsEmployeeStatus = getWords ("employee status");
  $strWordsSubsection = getWords ("subsection");
  $strWordsSection = getWords ("section");
  $strWordsCompany = getWords ("company");
  $strWordsDepartment = getWords ("department");
  $strWordsManagement = getWords ("management");
  $strWordsDivision = getWords ("division");
  $strWordsLevel = getWords ("level");
  $strWordsBranch = getWords ("branch");
  $strWordsFunctionalPosition = getWords ("functional position");
  $strWordsJobGrade = getWords ("job grade");
  $strWordsJoinDate = getWords ("join date");
  $strWordsFinishDate = getWords ("finish date");
  $strWordsPermanentDate = getWords ("permanent date");
  $strWordsStatus = getWords ("status");
  $strWordsActive = getWords ("active");
  $strWordsResignDate = getWords ("resign date");
  $strWordsBankCode = getWords ("bank code");
  $strWordsBankBranch = getWords ("bank branch");
  $strWordsBankAccountType = getWords ("bank account type");
  $strWordsBankAccount = getWords ("bank account");
  $strWordsBankAccountName = getWords ("bank account name");
  $strWordsBank2Code = getWords ("2nd bank code");
  $strWordsBank2Branch = getWords ("2nd bank branch");
  $strWordsBank2AccountType = getWords ("2nd bank account type");
  $strWordsBank2Account = getWords ("2nd bank account");
  $strWordsBank2AccountName = getWords ("2nd bank account name");
  $strWordsNPWP = strtoupper ("npwp");
  $strWordsJamsostekNo =  getWords ("jamsostek no");
  $strWordsZakat =  getWords ("zakat");
  $strWordsMembership = getWords ("membership");
  $strWordsNote = getWords ("note");
  $strWordsSave = getWords ("save");
  $strWordsAddNew =  getWords ("add new");
  $strWordsDeletePicture = getWords ("delete picture");
  
   
  $strDataDetail = "";
  $intDefaultWidth = 30;
  $intDefaultWidthPx = 210;
  $intDefaultHeight = 3;
  $strInputFiles = "";
  $strMessages = "";
  $strMsgClass = "";
  $strButtonNavigation = "";
  $bolError = false;

  // inisialisasi data
  $arrData = array();

  //----------------------------------------------------------------------

  //--- DAFTAR FUNSI------------------------------------------------------

  // fungsi untuk menghapus gambar employee
  function deletePicture($db, $strDataID = "") {
    global $words;

    $bolNewData = true;

    if ($strDataID != "") {
      $strSQL  = "SELECT * FROM hrd_employee ";
      $strSQL .= "WHERE id = '$strDataID' ";
      $resDb = $db->execute($strSQL);
      if ($rowDb = $db->fetchrow($resDb)) {

        $strPhoto = $rowDb['photo'];

        if ($strPhoto != "") {
          if (file_exists("photos/".$strPhoto)) {
            unlink("photos/".$strPhoto);
          }
          $strSQL  = "UPDATE hrd_employee SET photo = '' WHERE id = '$strDataID' ";
          $resExec = $db->execute($strSQL);
          writeLog(ACTIVITY_DELETE, MODULE_PAYROLL,"picture $strDataID",0);
        }

      }
    }

    return true;
  } // deletePicture

  // fungsi untuk menduplikat data manjadi file temporerynya
  // mengembalikan id dari duplikatnya
  function createTempData($db, $strDataID) {
    $intResult = "";

    $strFields  = "created, modified_by, created_by, employee_id, ";
    $strFields .= "employee_name, primary_address, primary_phone, birthplace, ";
    $strFields .= "nationality, barcode, id_card, bank_branch, bank_account_type,  bank_account, bank_account_name, ";
    $strFields .= "bank_code, ";
    $strFields .= "bank2_branch, bank2_account_type,  bank2_account, bank2_account_name, ";
    $strFields .= "bank2_code, ";
    $strFields .= "jamsostek_no, email, photo , active, gender, ";
    $strFields .= "salary_payment_type, birthday, is_birthday, ";
    $strFields .= "family_status_code, living_cost_code, medical_quota_status, inspouse, religion_code, education_level_code, employee_status, id_company, ";
    $strFields .= "management_code, division_code, department_code, section_code, sub_section_code, ";
    $strFields .= "branch_code, position_code, grade_code, join_date, due_date, permanent_date, ";
    $strFields .= "resign_date, note, emergency_contact, emergency_address, ";
    $strFields .= "emergency_phone, emergency_relation, driver_license_a, ";
    $strFields .= "driver_license_b, driver_license_c, wedding_date, weight, height, ";
    $strFields .= "blood_type, primary_city, primary_zip, transport, transport_fee, ";
    $strFields .= "functional_code, zakat";
    //$strFields .= "koperasi, pukfspmi, npwp, jamsostek, insurance, koperasi_no, ";
    //$strFields .= "pukfspmi_no, insurance_no, jamsostek_start, jamsostek_finish, ";
    //$strFields .= "koperasi_start, koperasi_finish, pukfspmi_start, pukfspmi_finish, ";
    //$strFields .= "insurance_start, insurance_finish,  ";
    $strFields .= "house_status, nickname, letter_code, passport ";
    //$strFields .= ",marital_status ";

    /*
    $strSQL  = "INSERT INTO hrd_employee  (created, modified_by, created_by, employee_id, ";
    $strSQL .= "employee_name, primary_address, primary_phone, birthplace, ";
    $strSQL .= "nationality, barcode, id_card, bank, bank_account, jamsostek_no, ";
    $strSQL .= "email, photo , active, gender, birthday, is_birthday, family_status_code, ";
    $strSQL .= "religion_code, education_level_code, employee_status, division_code, ";
    $strSQL .= "department_code, section_code, sub_section_code, position_code, ";
    $strSQL .= "grade_code, join_date, due_date, permanent_date, ";
    $strSQL .= "resign_date, note, emergency_contact, emergency_address, ";
    $strSQL .= "emergency_phone, emergency_relation, driver_license_a, ";
    $strSQL .= "driver_license_b, driver_license_c, wedding_date, weight, height, ";
    $strSQL .= "blood_type, primary_city, primary_zip, old_employee_id, ";
    $strSQL .= "functional_position, koperasi, pukfspmi, npwp, jamsostek, insurance, koperasi_no, ";
    $strSQL .= "pukfspmi_no, insurance_no, jamsostek_start, jamsostek_finish, ";
    $strSQL .= "koperasi_start, koperasi_finish, pukfspmi_start, pukfspmi_finish, ";
    $strSQL .= "insurance_start, insurance_finish, house_status, nickname, ";
    $strSQL .= "initial, passport, flag, link_id, marital_status) ";
    $strSQL .= "SELECT created, modified_by, created_by, employee_id, ";
    $strSQL .= "employee_name, primary_address, primary_phone, birthplace, ";
    $strSQL .= "nationality, barcode, id_card, bank, bank_account, jamsostek_no, ";
    $strSQL .= "email, photo , active, gender, birthday, is_birthday, family_status_code, living_cost_code, ";
    $strSQL .= "religion_code, education_level_code, employee_status, division_code, ";
    $strSQL .= "department_code, section_code, sub_section_code, position_code, ";
    $strSQL .= "grade_code, join_date, due_date, permanent_date, ";
    $strSQL .= "resign_date, note, emergency_contact, emergency_address, ";
    $strSQL .= "emergency_phone, emergency_relation, driver_license_a, ";
    $strSQL .= "driver_license_b, driver_license_c, wedding_date, weight, height, ";
    $strSQL .= "blood_type, primary_city, primary_zip, old_employee_id, ";
    $strSQL .= "functional_position, koperasi, pukfspmi, npwp, jamsostek, insurance, koperasi_no, ";
    $strSQL .= "pukfspmi_no, insurance_no, jamsostek_start, jamsostek_finish, ";
    $strSQL .= "koperasi_start, koperasi_finish, pukfspmi_start, pukfspmi_finish, ";
    $strSQL .= "insurance_start, insurance_finish, house_status, nickname, ";
    $strSQL .= "initial, passport, '1', id, marital_status FROM hrd_employee ";
    $strSQL .= "WHERE id = '$strDataID' ";
    $resExec = $db->execute($strSQL);

    // car IDnya
    $strSQL  = "SELECT id FROM hrd_employee WHERE link_id = '$strDataID' ";
    $resDb = $db->execute($strSQL);
    if ($rowDb = $db->fetchrow($resDb)) {
      $intResult = $rowDb['id'];
    }
    */
    $intResult = getTempData($db, "hrd_employee", $strFields, $strDataID,1);

    return $intResult;
  }//createTempData

  // fungsi untuk menampilkan data
  // $db = kelas database, $strDataID = ID data, jika ingin ditampilkan
  // $arrInputData = array untuk menampung data
  function getData($db, &$arrData) {
    global $words;
    global $_SESSION;
    global $arrUserInfo;
    global $bolIsEmployee;
    global $strDataID;

    $bolNewData = true;

    if ($strDataID == "") {
      //
    } else if ($bolIsEmployee && !isMe($strDataID)) {
      //
    } else {

      // jika ada temporernya, maka yang diedit adalah data yang temmporer, kecuali jika dia adlahmanager
      if ($_SESSION['sessionUserRole'] < ROLE_ADMIN) {
        // jika data bukan data temporery, coba cari apakah a data temporeri
        $strSQL  = "SELECT id, flag, link_id FROM hrd_employee WHERE id = '$strDataID' ";
        $resDb = $db->execute($strSQL);
        if ($rowDb = $db->fetchrow($resDb)) {
          if ($rowDb['flag'] == 0) {
            // bukan temprer, cari apakah ada temproernya
            $strSQL = "SELECT id FROM hrd_employee WHERE flag <> 0 AND link_id = '$strDataID' ";
            $resDb = $db->execute($strSQL);
            if ($rowDb = $db->fetchrow($resDb)) {
              $strDataID = $rowDb['id'];
            }
          }
        }
      }

      $strSQL  = "SELECT * FROM hrd_employee ";
      $strSQL .= "WHERE id = '$strDataID' ";
      $resDb = $db->execute($strSQL);
      if ($rowDb = $db->fetchrow($resDb)) {
        $bolNewData = false;

        $arrData['dataName'] = $rowDb['employee_name'];
        $arrData['dataFingerID'] = $rowDb['barcode'];
        $arrData['dataNick'] = $rowDb['nickname'];
        $arrData['dataLetterCode'] = $rowDb['letter_code'];
        $arrData['dataAddress'] = $rowDb['primary_address'];
        $arrData['dataCity'] = $rowDb['primary_city'];
        $arrData['dataZip'] = $rowDb['primary_zip'];
        $arrData['dataPhone'] = $rowDb['primary_phone'];
        $arrData['dataEmail'] = $rowDb['email'];
        $arrData['dataWeight'] = $rowDb['weight'];
        $arrData['dataHeight'] = $rowDb['height'];
        $arrData['dataBlood'] = $rowDb['blood_type'];
        $arrData['dataIDCard'] = $rowDb['id_card'];
        $arrData['dataLicenseA'] = $rowDb['driver_license_a'];
        $arrData['dataLicenseB'] = $rowDb['driver_license_b'];
        $arrData['dataLicenseC'] = $rowDb['driver_license_c'];
        $arrData['dataEmergencyContact'] = $rowDb['emergency_contact'];
        $arrData['dataEmergencyRelation'] = $rowDb['emergency_relation'];
        $arrData['dataEmergencyAddress'] = $rowDb['emergency_address'];
        $arrData['dataEmergencyPhone'] = $rowDb['emergency_phone'];
        $arrData['dataBirthPlace'] = $rowDb['birthplace'];
        $arrData['dataBirthday'] = $rowDb['birthday'];
        $arrData['dataIsBirthday'] = $rowDb['is_birthday'];
        $arrData['dataNationality'] = $rowDb['nationality'];
        $arrData['dataPassport'] = $rowDb['passport'];
        $arrData['dataGender'] = $rowDb['gender'];
        $arrData['dataSalaryPaymentType'] = $rowDb['salary_payment_type'];
        //$arrData['dataMaritalStatus'] = $rowDb['marital_status'];
        $arrData['dataWeddingDate'] = $rowDb['wedding_date'];
        $arrData['dataTransport'] = $rowDb['transport'];
        $arrData['dataTransportFee'] = $rowDb['transport_fee'];
        $arrData['dataReligion'] = $rowDb['religion_code'];
        $arrData['dataJamsostekNo'] = $rowDb['jamsostek_no'];

        // bagian yang hanya boleh diedit oleh admmin HRD

        $arrData['dataEmployeeID'] = $rowDb['employee_id'];
        $arrData['dataIsZakat'] = $rowDb['zakat'];
        $arrData['dataBankCode'] = "".$rowDb['bank_code'];
        $arrData['dataBankBranch'] = "".$rowDb['bank_branch'];
        $arrData['dataBankAccountType'] = "".$rowDb['bank_account_type'];
        $arrData['dataBankAccount'] = "".$rowDb['bank_account'];
        $arrData['dataBankAccountName'] = "".$rowDb['bank_account_name'];
        $arrData['dataBank2Code']         = "".$rowDb['bank2_code'];
        $arrData['dataBank2Branch']       = "".$rowDb['bank2_branch'];
        $arrData['dataBank2AccountType']  = "".$rowDb['bank2_account_type'];
        $arrData['dataBank2Account']      = "".$rowDb['bank2_account'];
        $arrData['dataBank2AccountName']  = "".$rowDb['bank2_account_name'];
        $arrData['dataNPWP'] = $rowDb['npwp'];
        $arrData['dataPhoto'] = $rowDb['photo'];
        $arrData['dataNote'] = $rowDb['note'];
        $arrData['dataActive'] = $rowDb['active'];
        $arrData['dataEducation'] = $rowDb['education_level_code'];
        $arrData['dataFamilyStatus'] = $rowDb['family_status_code'];
        $arrData['dataLivingCost'] = $rowDb['living_cost_code'];
        $arrData['dataMedicalQuotaStatus'] = $rowDb['medical_quota_status'];
        $arrData['dataInspouse'] = $rowDb['inspouse'];
        $arrData['dataEmployeeStatus'] = $rowDb['employee_status'];
        $arrData['dataManagement'] = $rowDb['management_code'];
        $arrData['dataDivision'] = $rowDb['division_code'];
        $arrData['dataCompany'] = $rowDb['id_company'];
        $arrData['dataDepartment'] = $rowDb['department_code'];
        $arrData['dataSection'] = $rowDb['section_code'];
        $arrData['dataSubSection'] = $rowDb['sub_section_code'];
        $arrData['dataFunctionalPosition'] = $rowDb['functional_code'];
        $arrData['dataBranch'] = $rowDb['branch_code'];
        $arrData['dataPosition'] = $rowDb['position_code'];
        //$arrData['dataTransport'] = $rowDb['transport_code'];
        $arrData['dataSalaryGrade'] = $rowDb['grade_code'];
        $arrData['dataJoinDate'] = $rowDb['join_date'];
        $arrData['dataDueDate'] = $rowDb['due_date'];
        $arrData['dataPermanentDate'] = $rowDb['permanent_date'];
        $arrData['dataResignDate'] = $rowDb['resign_date'];
        writeLog(ACTIVITY_VIEW, MODULE_PAYROLL,"$strDataID ->".$rowDb['employee_id'],0);
      }
    }

    if ($bolNewData) {
      $arrData['dataEmployeeID'] = "";
      $arrData['dataName'] = "";
      $arrData['dataFingerID'] = "";
      $arrData['dataNick'] = "";
      $arrData['dataLetterCode'] = "";
      $arrData['dataAddress'] = "";
      $arrData['dataCity'] = "";
      $arrData['dataZip'] = "";
      $arrData['dataPhone'] = "";
      $arrData['dataEmergencyContact'] = "";
      $arrData['dataEmergencyRelation'] = "";
      $arrData['dataEmergencyAddress'] = "";
      $arrData['dataEmergencyPhone'] = "";
      $arrData['dataEmail'] = "";
      $arrData['dataBirthPlace'] = "";
      $arrData['dataBirthday'] = date("Y-m-d");
      $arrData['dataIsBirthday'] = 'f';
      $arrData['dataNationality'] = "";
      $arrData['dataPassport'] = "";
      $arrData['dataGender'] = '0';
      $arrData['dataSalaryPaymentType'] = '0';
      $arrData['dataIsZakat'] = "";
      $arrData['dataJamsostekNo'] = "";
      $arrData['dataInsuranceFinish'] = "";
      $arrData['dataBankCode'] = "";
      $arrData['dataBankBranch'] = "";
      $arrData['dataBankAccountType'] = "";
      $arrData['dataBankAccount'] = "";
      $arrData['dataBankAccountName'] = "";
      $arrData['dataBank2Code'] = "";
      $arrData['dataBank2Branch'] = "";
      $arrData['dataBank2AccountType'] = "";
      $arrData['dataBank2Account'] = "";
      $arrData['dataBank2AccountName'] = "";
      $arrData['dataNPWP'] = "";
      $arrData['dataPhoto'] = "";
      $arrData['dataNote'] = "";
      $arrData['dataWeight'] = "0";
      $arrData['dataHeight'] = "0";
      $arrData['dataBlood'] = "";
      $arrData['dataIDCard'] = "";
      $arrData['dataLicenseA'] = "";
      $arrData['dataLicenseB'] = "";
      $arrData['dataLicenseC'] = "";
      $arrData['dataActive'] = "1";
      $arrData['dataReligion'] = "";
      $arrData['dataEducation'] = "";
      $arrData['dataFamilyStatus'] = "";
      $arrData['dataLivingCost'] = "";
      $arrData['dataMedicalQuotaStatus'] = "";
      $arrData['dataInspouse'] = "f";
      $arrData['dataWeddingDate'] = "";
      $arrData['dataEmployeeStatus'] = "";
      $arrData['dataManagement'] = "";
      $arrData['dataDivision'] = "";
      $arrData['dataCompany'] = "";
      $arrData['dataDepartment'] = "";
      $arrData['dataSection'] = "";
      $arrData['dataSubSection'] = "";
      $arrData['dataFunctionalPosition'] = "";
      $arrData['dataBranch'] = "";
      $arrData['dataPosition'] = "";
      $arrData['dataSalaryGrade'] = "";
      $arrData['dataTransport'] = "";
      $arrData['dataTransportFee'] = "";
      $arrData['dataJoinDate'] = date('Y-m-d');
      $arrData['dataDueDate'] = "";
      $arrData['dataPermanentDate'] = "";
      $arrData['dataResignDate'] = "";

    }

    return true;
  } // showData

  // fungsi untuk menyimpan data
  function saveData($db, &$strDataID, &$strError) {
    global $_REQUEST;
    global $_FILES;
    global $_SESSION;
    global $error;
    global $messages;

    $strError = "";
    $strToday = date("Y-m-d");
    $bolNew = ($strDataID == "");

    (isset($_REQUEST['dataEmployeeID'])) ? $strDataEmployeeID = trim($_REQUEST['dataEmployeeID']) : $strDataEmployeeID = "";
    (isset($_REQUEST['dataName'])) ? $strdataName = $_REQUEST['dataName'] : $strdataName = "";
    (isset($_REQUEST['dataFingerID'])) ? $strdataFingerID = $_REQUEST['dataFingerID'] : $strdataFingerID = "";
    (isset($_REQUEST['dataNick'])) ? $strdataNick = $_REQUEST['dataNick'] : $strdataNick = "";
    (isset($_REQUEST['dataLetterCode'])) ? $strDataLetterCode = $_REQUEST['dataLetterCode'] : $strDataLetterCode = "";
    (isset($_REQUEST['dataAddress'])) ? $strDataAddress = $_REQUEST['dataAddress'] : $strDataAddress = "";
    (isset($_REQUEST['dataCity'])) ? $strDataCity = $_REQUEST['dataCity'] : $strDataCity = "";
    (isset($_REQUEST['dataZip'])) ? $strDataZip = $_REQUEST['dataZip'] : $strDataZip = "";
    (isset($_REQUEST['dataPhone'])) ? $strDataPhone = $_REQUEST['dataPhone'] : $strDataPhone = "";
    (isset($_REQUEST['dataEmergencyContact'])) ? $strDataEmergencyContact = $_REQUEST['dataEmergencyContact'] : $strDataEmergencyContact = "";
    (isset($_REQUEST['dataEmergencyRelation'])) ? $strDataEmergencyRelation = $_REQUEST['dataEmergencyRelation'] : $strDataEmergencyRelation = "";
    (isset($_REQUEST['dataEmergencyAddress'])) ? $strDataEmergencyAddress = $_REQUEST['dataEmergencyAddress'] : $strDataEmergencyAddress = "";
    (isset($_REQUEST['dataEmergencyPhone'])) ? $strDataEmergencyPhone = $_REQUEST['dataEmergencyPhone'] : $strDataEmergencyPhone = "";
    (isset($_REQUEST['dataGender'])) ? $strDataGender = $_REQUEST['dataGender'] : $strDataGender = "0";
    (isset($_REQUEST['dataSalaryPaymentType'])) ? $strDataSalaryPaymentType = $_REQUEST['dataSalaryPaymentType'] : $strDataSalaryPaymentType = "0";
    (isset($_REQUEST['dataBirthPlace'])) ? $strDataBirthPlace = $_REQUEST['dataBirthPlace'] : $strDataBirthPlace = "";
    (isset($_REQUEST['dataBirthday'])) ? $strDataBirthday = $_REQUEST['dataBirthday'] : $strDataBirthday = "";
    (isset($_REQUEST['dataNationality'])) ? $strDataNationality = $_REQUEST['dataNationality'] : $strDataNationality = "";
    (isset($_REQUEST['dataPassport'])) ? $strDataPassport = $_REQUEST['dataPassport'] : $strDataPassport = "";
    (isset($_REQUEST['dataWeight'])) ? $strDataWeight = $_REQUEST['dataWeight'] : $strDataWeight = "0";
    (isset($_REQUEST['dataHeight'])) ? $strDataHeight = $_REQUEST['dataHeight'] : $strDataHeight = "0";
    (isset($_REQUEST['dataBlood'])) ? $strDataBlood = $_REQUEST['dataBlood'] : $strDataBlood = "";
    (isset($_REQUEST['dataIDCard'])) ? $strDataIDCard = $_REQUEST['dataIDCard'] : $strDataIDCard = "";
    (isset($_REQUEST['dataLicenseA'])) ? $strDataLicenseA = $_REQUEST['dataLicenseA'] : $strDataLicenseA = "";
    (isset($_REQUEST['dataLicenseB'])) ? $strDataLicenseB = $_REQUEST['dataLicenseB'] : $strDataLicenseB = "";
    (isset($_REQUEST['dataLicenseC'])) ? $strDataLicenseC = $_REQUEST['dataLicenseC'] : $strDataLicenseC = "";
    (isset($_REQUEST['dataReligion'])) ? $strDataReligion = $_REQUEST['dataReligion'] : $strDataReligion = "";
    (isset($_REQUEST['dataEducation'])) ? $strDataEducation = $_REQUEST['dataEducation'] : $strDataEducation = "";
    (isset($_REQUEST['dataWeddingDate'])) ? $strDataWeddingDate = $_REQUEST['dataWeddingDate'] : $strDataWeddingDate = "";
    (isset($_REQUEST['dataFamilyStatus'])) ? $strDataFamilyStatus = $_REQUEST['dataFamilyStatus'] : $strDataFamilyStatus = "";
    (isset($_REQUEST['dataLivingCost'])) ? $strDataLivingCost = $_REQUEST['dataLivingCost'] : $strDataLivingCost = "";
    (isset($_REQUEST['dataMedicalQuotaStatus'])) ? $strDataMedicalQuotaStatus = $_REQUEST['dataMedicalQuotaStatus'] : $strDataMedicalQuotaStatus = "";
    (isset($_REQUEST['dataInspouse'])) ? $strDataInspouse = "t" : $strDataInspouse = "f";
    (isset($_REQUEST['dataEmployeeStatus'])) ? $strDataEmployeeStatus = $_REQUEST['dataEmployeeStatus'] : $strDataEmployeeStatus = "";

    (isset($_REQUEST['dataCompany'])) ? $strDataCompany = $_REQUEST['dataCompany'] : $strDataCompany = "";

    (isset($_REQUEST['dataManagement'])) ? $strDataManagement = $_REQUEST['dataManagement'] : $strDataManagement = "";
    (isset($_REQUEST['dataDivision'])) ? $strDataDivision = $_REQUEST['dataDivision'] : $strDataDivision = "";
    (isset($_REQUEST['dataDepartment'])) ? $strDataDepartment = $_REQUEST['dataDepartment'] : $strDataDepartment = "";
    (isset($_REQUEST['dataSection'])) ? $strDataSection = $_REQUEST['dataSection'] : $strDataSection = "";
    (isset($_REQUEST['dataSubSection'])) ? $strDataSubSection = $_REQUEST['dataSubSection'] : $strDataSubSection = "";
    (isset($_REQUEST['dataFunctionalPosition'])) ? $strDataFunctionalPosition  = $_REQUEST['dataFunctionalPosition'] : $strDataFunctionalPosition  = "";
    (isset($_REQUEST['dataBranch'])) ? $strDataBranch = $_REQUEST['dataBranch'] : $strDataBranch = "";
    (isset($_REQUEST['dataPosition'])) ? $strDataPosition = $_REQUEST['dataPosition'] : $strDataPosition = "";
    (isset($_REQUEST['dataTransport'])) ? $strDataTransport = $_REQUEST['dataTransport'] : $strDataTransport = "";
    (isset($_REQUEST['dataTransportFee']) && is_numeric($_REQUEST['dataTransportFee'])) ? $strDataTransportFee = $_REQUEST['dataTransportFee'] : $strDataTransportFee = 0;
    (isset($_REQUEST['dataSalaryGrade'])) ? $strDataSalaryGrade = $_REQUEST['dataSalaryGrade'] : $strDataSalaryGrade = "";
    (isset($_REQUEST['dataEmail'])) ? $strDataEmail = $_REQUEST['dataEmail'] : $strDataEmail = "";
    (isset($_REQUEST['dataNPWP'])) ? $strDataNPWP = $_REQUEST['dataNPWP'] : $strDataNPWP = "";
    (isset($_REQUEST['dataBankCode'])) ? $strDataBankCode = $_REQUEST['dataBankCode'] : $strDataBankCode = "";
    (isset($_REQUEST['dataBankBranch'])) ? $strDataBankBranch = $_REQUEST['dataBankBranch'] : $strDataBankBranch = "";
    (isset($_REQUEST['dataBankAccount'])) ? $strDataBankAccount = $_REQUEST['dataBankAccount'] : $strDataBankAccount = "";
    (isset($_REQUEST['dataBankAccountType'])) ? $strDataBankAccountType = $_REQUEST['dataBankAccountType'] : $strDataBankAccountType = "";
    (isset($_REQUEST['dataBankAccountName'])) ? $strDataBankAccountName = $_REQUEST['dataBankAccountName'] : $strDataBankAccountName = "";
    (isset($_REQUEST['dataBank2Code']))         ? $strDataBank2Code         = $_REQUEST['dataBank2Code']        : $strDataBank2Code = "";
    (isset($_REQUEST['dataBank2Branch']))       ? $strDataBank2Branch       = $_REQUEST['dataBank2Branch']      : $strDataBank2Branch = "";
    (isset($_REQUEST['dataBank2Account']))      ? $strDataBank2Account      = $_REQUEST['dataBank2Account']     : $strDataBank2Account = "";
    (isset($_REQUEST['dataBank2AccountType']))  ? $strDataBank2AccountType  = $_REQUEST['dataBank2AccountType'] : $strDataBank2AccountType = "";
    (isset($_REQUEST['dataBank2AccountName']))  ? $strDataBank2AccountName  = $_REQUEST['dataBank2AccountName'] : $strDataBank2AccountName = "";
    (isset($_REQUEST['dataJoinDate'])) ? $strDataJoinDate = $_REQUEST['dataJoinDate'] : $strJoinDate = "";
    (isset($_REQUEST['dataDueDate'])) ? $strDataDueDate = $_REQUEST['dataDueDate'] : $strDueDate = "";
    (isset($_REQUEST['dataPermanentDate'])) ? $strDataPermanentDate = $_REQUEST['dataPermanentDate'] : $strPermanentDate = "";
    (isset($_REQUEST['dataResignDate'])) ? $strDataResignDate = $_REQUEST['dataResignDate'] : $strResignDate = "";
    (isset($_REQUEST['dataNote'])) ? $strDataNote = $_REQUEST['dataNote'] : $strDataNote = "";
    (isset($_POST['dataID'])) ? $strDataID = $_POST['dataID'] : $strDataID = "";
    (isset($_REQUEST['dataActive'])) ? $strDataActive = 1 : $strDataActive = 0;
    (isset($_REQUEST['dataJamsostekNo'])) ? $strDataJamsostekNo = $_REQUEST['dataJamsostekNo'] : $strDataJamsostekNo = "";
    (isset($_REQUEST['dataIsZakat'])) ? $strDataIsZakat = 't' : $strDataIsZakat = 'f';

    $strDataIsBirthday = 't';

    // cek validasi -----------------------
    if ($strDataEmployeeID == "") {
      $strError = $error['empty_code'];
      return false;
    }
    else if (!validStandardDate($strDataBirthday) && $strDataBirthday != "") {
      $strError = $error['invalid_date'];
      return false;
    }
    else if (!validStandardDate($strDataJoinDate)  && $strDataJoinDate != "") {
      $strError = $error['invalid_date'];
      return false;
    }
    else if (!validStandardDate($strDataDueDate) && $strDataDueDate != "") {
      $strError = $error['invalid_date'];
      return false;
    }
    else if (!validStandardDate($strDataPermanentDate) && $strDataPermanentDate != "") {
      $strError = $error['invalid_date'];
      return false;
    }
    else if (!validStandardDate($strDataResignDate) && $strDataResignDate != "" ) {
      $strError = $error['invalid_date'];
      return false;
    }
    else if (!validStandardDate($strDataWeddingDate) && $strDataWeddingDate != "") {
      $strError = $error['invalid_date'];
      return false;
    }
    else if (!is_numeric($strDataWeight)) {
      $strError = $error['invalid_number'];
      return false;
    }
    else if (!is_numeric($strDataHeight)) {
      $strError = $error['invalid_number'];
      return false;
    }
    else 
    {
      $strKriteria = ($strDataID == "") ? "" : "AND id <> '$strDataID' ";
      if (isDataExists($db,"hrd_employee","employee_id", $strDataEmployeeID, $strKriteria)) 
      {
        $strError = $error['duplicate_code']. "  Employee ID -> $strDataEmployeeID";
        return false;
      }
    }

    $strDataBirthday = ($strDataBirthday == "") ? "NULL" : "'$strDataBirthday'";
    $strDataJoinDate = ($strDataJoinDate == "") ? "NULL" : "'$strDataJoinDate'";
    $strDataDueDate = ($strDataDueDate == "") ? "NULL" : "'$strDataDueDate'";
    $strDataPermanentDate = ($strDataPermanentDate == "") ? "NULL" : "'$strDataPermanentDate'";
    $strDataResignDate = ($strDataResignDate == "") ? "NULL" : "'$strDataResignDate'";
    $strDataWeddingDate = ($strDataWeddingDate == "") ? "NULL" : "'$strDataWeddingDate'";
    $strDataGender = ($strDataGender == "") ? "NULL" : $strDataGender;


    // simpan data -----------------------
    if ($_SESSION['sessionUserRole'] > ROLE_ADMIN || ($_SESSION['sessionUserRole'] >= ROLE_ADMIN && $bolNew) )
    {
      if ($bolNew) {
        // data baru

        $strFlag = ($_SESSION['sessionUserRole'] > ROLE_ADMIN) ? 0 : 2; // jika bukan manager, statusnya baru check
        $strSQL  = "INSERT INTO hrd_employee (created,created_by,modified, modified_by, ";
        $strSQL .= "employee_id,employee_name, barcode, gender, salary_payment_type, primary_address, ";
        $strSQL .= "primary_city, primary_zip, primary_phone, ";
        $strSQL .= "is_birthday, birthplace, birthday, nationality, id_card, email, ";
        $strSQL .= "driver_license_a, driver_license_b, driver_license_c, ";
        $strSQL .= "emergency_contact, emergency_relation, ";
        $strSQL .= "emergency_address, emergency_phone, religion_code, ";
        $strSQL .= "education_level_code, family_status_code, living_cost_code, medical_quota_status, inspouse, employee_status, id_company, ";
        $strSQL .= "management_code, division_code, department_code, section_code, sub_section_code, ";
        $strSQL .= "branch_code, position_code, grade_code, join_date, due_date, ";
        $strSQL .= "permanent_date, resign_date, ";
        $strSQL .= "bank_branch, bank_account_type, bank_account, ";
        $strSQL .= "bank2_branch, bank2_account_type, bank2_account, ";
        $strSQL .= "active, note, ";
        $strSQL .= "wedding_date,weight, height,  ";
        $strSQL .= "blood_type, npwp, functional_code, ";
        $strSQL .= "zakat, jamsostek_no, transport, transport_fee, ";
        $strSQL .= "bank_account_name, bank_code, ";
        $strSQL .= "bank2_account_name, bank2_code, ";
        $strSQL .= "nickname, letter_code, passport, flag) ";
        $strSQL .= "VALUES(now(),'" .$_SESSION['sessionUserID']. "',now(),'" .$_SESSION['sessionUserID']. "', ";
        $strSQL .= "'$strDataEmployeeID','$strdataName','$strdataFingerID', '$strDataGender', ";
        $strSQL .= "'$strDataSalaryPaymentType', ";
        $strSQL .= "'$strDataAddress', '$strDataCity', '$strDataZip', '$strDataPhone', ";
        $strSQL .= "'$strDataIsBirthday', '$strDataBirthPlace',";
        $strSQL .= "$strDataBirthday, '$strDataNationality', '$strDataIDCard', '$strDataEmail', ";
        $strSQL .= "'$strDataLicenseA', '$strDataLicenseB', '$strDataLicenseC', ";
        $strSQL .= "'$strDataEmergencyContact', '$strDataEmergencyRelation', ";
        $strSQL .= "'$strDataEmergencyAddress', '$strDataEmergencyPhone', ";
        $strSQL .= "'$strDataReligion', '$strDataEducation', '$strDataFamilyStatus', '$strDataLivingCost','$strDataMedicalQuotaStatus', '$strDataInspouse', ";
        $strSQL .= "'$strDataEmployeeStatus',  '$strDataCompany', '$strDataManagement', '$strDataDivision', '$strDataDepartment', '$strDataSection', ";
        $strSQL .= "'$strDataSubSection', '$strDataBranch', '$strDataPosition', '$strDataSalaryGrade',  ";
        $strSQL .= "$strDataJoinDate, $strDataDueDate, $strDataPermanentDate,  ";
        $strSQL .= "$strDataResignDate, ";
        $strSQL .= "'$strDataBankBranch', '$strDataBankAccountType',  '$strDataBankAccount',  ";
        $strSQL .= "'$strDataBank2Branch', '$strDataBank2AccountType',  '$strDataBank2Account',  ";
        $strSQL .= "'$strDataActive', '$strDataNote',  ";
        $strSQL .= "$strDataWeddingDate, '$strDataWeight', '$strDataHeight',  ";
        $strSQL .= "'$strDataBlood', '$strDataNPWP','$strDataFunctionalPosition ', ";
        $strSQL .= "'$strDataIsZakat', '$strDataJamsostekNo', '$strDataTransport', $strDataTransportFee, ";
        $strSQL .= "'$strDataBankAccountName', '$strDataBankCode', ";
        $strSQL .= "'$strDataBank2AccountName', '$strDataBank2Code', ";
        $strSQL .= "'$strdataNick','$strDataLetterCode', '$strDataPassport', '$strFlag') ";
        $resExec = $db->execute($strSQL);

        // ambil data IDnya
        $strSQL  = "SELECT id FROM hrd_employee WHERE employee_id = '$strDataEmployeeID' ";
        $resDb = $db->execute($strSQL);
        if ($rowDb = $db->fetchrow($resDb)) {
          $strDataID = $rowDb['id'];
        }
        writeLog(ACTIVITY_ADD, MODULE_PAYROLL,"$strDataID -> $strDataEmployeeID",0);

        // ambil default fasilitas seperti yang ada di gradenya
        /*
        if ($strDataSalaryGrade != "" && $strDataID != "") {
          $strSQL  = "SELECT grade_code, facility FROM hrd_grade_facility ";
          $strSQL .= "WHERE grade_code = '$strDataSalaryGrade' ";
          $resTmp = $db->execute($strSQL);
          while ($rowTmp = $db->fetchrow($resTmp)) {
            // tambahkan ke default punya karyawan
            $strSQL  = "INSERT INTO hrd_employee_facility (created, modified_by, created_by, ";
            $strSQL .= "id_employee, facility, note) ";
            $strSQL .= "VALUES(now(), '" .$_SESSION['sessionUserID']."', '" .$_SESSION['sessionUserID']."', ";
            $strSQL .= "'$strDataID', '" .$rowTmp['facility']."','') ";
            $resExec = $db->execute($strSQL);
          }
        }*/

      } else {

        $bolTemp = false;
        $strLinkID = ""; // ID asli, jika merupakan temporer

        // cek dulu, apakah data temporer atau bukan
        $strSQL  = "SELECT id, flag, link_id FROM hrd_employee WHERE id = '$strDataID' ";
        $resDb = $db->execute($strSQL);
        if ($rowDb = $db->fetchrow($resDb)) {
          $bolTemp = ($rowDb['flag'] != 0);
          $strLinkID = $rowDb['link_id'];
        }

        if (!$bolTemp && $_SESSION['sessionUserRole'] <= ROLE_ADMIN) { // jika bukan temporer
          // cek, apakah sudah ada penambahan data temporery, kalau belum, update
          $strSQL  = "SELECT id FROM hrd_employee WHERE flag <> 0 AND link_id = '$strDataID' ";
          $resDb = $db->execute($strSQL);
          if ($rowDb = $db->fetchrow($resDb)) {
            // sudah ada
          } else {
            // belum ada, buat dulu
            $strDataID = createTempData($db, $strDataID);
            $bolTemp = true;
          }

        }

        $strSQL  = "UPDATE hrd_employee ";
        $strSQL .= "SET modified_by = '" .$_SESSION['sessionUserID']. "', ";
        // jika temp status flagnya, jadi 2
        if ($bolTemp) {
          $strSQL  .= "flag = 2, ";
        }
        $strSQL .= "employee_id = '$strDataEmployeeID', employee_name = '$strdataName',  barcode = '$strdataFingerID', ";
        $strSQL .= "gender = '$strDataGender', primary_address = '$strDataAddress', ";
        $strSQL .= "salary_payment_type = '$strDataSalaryPaymentType', ";
        $strSQL .= "primary_city = '$strDataCity', primary_zip = '$strDataZip', ";
        $strSQL .= "primary_phone = '$strDataPhone', is_birthday = '$strDataIsBirthday', ";
        $strSQL .= "birthplace = '$strDataBirthPlace', \"birthday\" = $strDataBirthday, ";
        $strSQL .= "emergency_contact = '$strDataEmergencyContact', ";
        $strSQL .= "emergency_relation = '$strDataEmergencyRelation', ";
        $strSQL .= "emergency_address = '$strDataEmergencyAddress', ";
        $strSQL .= "emergency_phone = '$strDataEmergencyPhone', ";
        $strSQL .= "nationality = '$strDataNationality', id_card = '$strDataIDCard', ";
        $strSQL .= "driver_license_a = '$strDataLicenseA', ";
        $strSQL .= "driver_license_b = '$strDataLicenseB', ";
        $strSQL .= "driver_license_c = '$strDataLicenseC', ";
        $strSQL .= "weight = '$strDataWeight', height = '$strDataHeight', blood_type = '$strDataBlood', ";
        $strSQL .= "religion_code = '$strDataReligion', education_level_code = '$strDataEducation', ";
        $strSQL .= "family_status_code = '$strDataFamilyStatus', employee_status = '$strDataEmployeeStatus', ";
        $strSQL .= "id_company= '$strDataCompany', living_cost_code = '$strDataLivingCost', medical_quota_status = '$strDataMedicalQuotaStatus', inspouse = '$strDataInspouse', ";
        $strSQL .= "department_code = '$strDataDepartment', management_code = '$strDataManagement', division_code = '$strDataDivision', ";
        $strSQL .= "section_code = '$strDataSection', sub_section_code = '$strDataSubSection', ";
        $strSQL .= "branch_code = '$strDataBranch', position_code = '$strDataPosition', grade_code = '$strDataSalaryGrade', ";
        $strSQL .= "join_date = $strDataJoinDate, due_date = $strDataDueDate, ";
        $strSQL .= "resign_date = $strDataResignDate, permanent_date = $strDataPermanentDate, ";
        $strSQL .= "bank_account = '$strDataBankAccount', bank_account_type = '$strDataBankAccountType', ";
        $strSQL .= "bank_branch = '$strDataBankBranch', bank_code = '$strDataBankCode', ";
        $strSQL .= "bank2_account = '$strDataBank2Account', bank2_account_type = '$strDataBank2AccountType', ";
        $strSQL .= "bank2_branch = '$strDataBank2Branch', bank2_code = '$strDataBank2Code', ";
        $strSQL .= "active = '$strDataActive', note = '$strDataNote', npwp = '$strDataNPWP', ";
        $strSQL .= "bank_account_name = '$strDataBankAccountName',";
        $strSQL .= "bank2_account_name = '$strDataBank2AccountName',";
        $strSQL .= "functional_code = '$strDataFunctionalPosition ', email = '$strDataEmail', ";
        $strSQL .= "wedding_date = $strDataWeddingDate, ";
        $strSQL .= "zakat = '$strDataIsZakat', jamsostek_no = '$strDataJamsostekNo', ";      
        $strSQL .= "transport = '$strDataTransport', transport_fee = '$strDataTransportFee', ";      
        $strSQL .= "nickname = '$strdataNick', letter_code = '$strDataLetterCode', passport = '$strDataPassport' ";
        $strSQL .= "WHERE id = '$strDataID' ";
        $resExec = $db->execute($strSQL);
        writeLog(ACTIVITY_EDIT, MODULE_PAYROLL,"$strDataID -> $strDataEmployeeID",0);

      }
    }
    // -- TAMBAHKAN DATA USER JIKA BELUM ADA ----
    // -- login, passw sesuai employee_id
    if ($strDataID != "" && $strDataEmployeeID != "") {
      $strSQL  = "SELECT id_adm_user FROM adm_user WHERE \"login_name\" = '$strDataEmployeeID' ";
      $resU = $db->execute($strSQL);
      if ($rowU = $db->fetchrow($resU)) {
        // dah ada
      } else {
        $strmodified_byID = $_SESSION['sessionUserID'];
        $strSQL  = "INSERT INTO adm_user (\"login_name\", pwd, ";
        $strSQL .= "employee_id, \"name\",  \"active\", id_adm_group, id_adm_company, id_adm_module) ";
        $strSQL .= "VALUES('$strDataEmployeeID', ";
        $strSQL .= "'" .md5($strDataEmployeeID)."', '$strDataEmployeeID', '$strdataName',  ";
        $strSQL .= " 't', 1, '" .ROLE_EMPLOYEE."', 0) ";
        $resExec = $db->execute($strSQL);
      }
    }

    // simpan data gambar, jika ada
    if ($strDataID != "") {
      if (is_uploaded_file($_FILES["dataPhoto"]['tmp_name'])) {
        $arrNamaFile = explode(".",$_FILES["dataPhoto"]['name']);
        $strNamaFile = strtolower($strDataEmployeeID);
        if (count($arrNamaFile) > 0) {
          $strNamaFile .= ".". $arrNamaFile[count($arrNamaFile) -1];
        }

        clearstatcache();
        if (!is_dir("photos")) {
          mkdir("photos", 0755);
        }

        $strNamaFileLengkap = "photos/".$strNamaFile;
        if (file_exists($strNamaFileLengkap)) {
          unlink($strNamaFileLengkap);
        }
        if (move_uploaded_file($_FILES['dataPhoto']['tmp_name'], $strNamaFileLengkap)) {
          // update data
          $strSQL  = "UPDATE hrd_employee SET photo = '$strNamaFile' WHERE id = '$strDataID' ";
          $resExec = $db->execute($strSQL);
        }

      }
    }

    $strError = $messages['data_saved']." >> ".date("d-M-Y H:i:s");
    return true;
  } // saveData

  // fungsi untuk menyimpan data, khuss untuk employee aja
  function saveDataEmployee($db, &$strDataID, &$strError) {
    global $_REQUEST;
    global $_FILES;
    global $_SESSION;
    global $error;
    global $messages;
    global $arrUserInfo;

    $strError = "";
    $strToday = date("Y-m-d");

    (isset($_REQUEST['dataEmployeeID'])) ? $strDataEmployeeID = $_REQUEST['dataEmployeeID'] : $strDataEmployeeID = "";
    (isset($_REQUEST['dataName'])) ? $strdataName = $_REQUEST['dataName'] : $strdataName = "";
    (isset($_REQUEST['dataFingerID'])) ? $strDataFingerID = $_REQUEST['dataFingerID'] : $strDataFingerID = "";
    (isset($_REQUEST['dataNick'])) ? $strdataNick = $_REQUEST['dataNick'] : $strdataNick = "";
    (isset($_REQUEST['dataLetterCode'])) ? $strDataLetterCode = $_REQUEST['dataLetterCode'] : $strDataLetterCode = "";
    (isset($_REQUEST['dataAddress'])) ? $strDataAddress = $_REQUEST['dataAddress'] : $strDataAddress = "";
    (isset($_REQUEST['dataCity'])) ? $strDataCity = $_REQUEST['dataCity'] : $strDataCity = "";
    (isset($_REQUEST['dataZip'])) ? $strDataZip = $_REQUEST['dataZip'] : $strDataZip = "";
    (isset($_REQUEST['dataPhone'])) ? $strDataPhone = $_REQUEST['dataPhone'] : $strDataPhone = "";
    (isset($_REQUEST['dataEmergencyContact'])) ? $strDataEmergencyContact = $_REQUEST['dataEmergencyContact'] : $strDataEmergencyContact = "";
    (isset($_REQUEST['dataEmergencyRelation'])) ? $strDataEmergencyRelation = $_REQUEST['dataEmergencyRelation'] : $strDataEmergencyRelation = "";
    (isset($_REQUEST['dataEmergencyAddress'])) ? $strDataEmergencyAddress = $_REQUEST['dataEmergencyAddress'] : $strDataEmergencyAddress = "";
    (isset($_REQUEST['dataEmergencyPhone'])) ? $strDataEmergencyPhone = $_REQUEST['dataEmergencyPhone'] : $strDataEmergencyPhone = "";
    (isset($_REQUEST['dataGender'])) ? $strDataGender = $_REQUEST['dataGender'] : $strDataGender = "0";
    (isset($_REQUEST['dataSalaryPaymentType'])) ? $strDataSalaryPaymentType = $_REQUEST['dataSalaryPaymentType'] : $strDataSalaryPaymentType = "0";
    (isset($_REQUEST['dataBirthPlace'])) ? $strDataBirthPlace = $_REQUEST['dataBirthPlace'] : $strDataBirthPlace = "";
    (isset($_REQUEST['dataBirthday'])) ? $strDataBirthday = $_REQUEST['dataBirthday'] : $strDataBirthday = "";
    (isset($_REQUEST['dataNationality'])) ? $strDataNationality = $_REQUEST['dataNationality'] : $strDataNationality = "";
    (isset($_REQUEST['dataEmail'])) ? $strDataEmail = $_REQUEST['dataEmail'] : $strDataEmail = "";
    (isset($_REQUEST['dataPassport'])) ? $strDataPassport = $_REQUEST['dataPassport'] : $strDataPassport = "";
    (isset($_REQUEST['dataWeight'])) ? $strDataWeight = $_REQUEST['dataWeight'] : $strDataWeight = "0";
    (isset($_REQUEST['dataHeight'])) ? $strDataHeight = $_REQUEST['dataHeight'] : $strDataHeight = "0";
    (isset($_REQUEST['dataBlood'])) ? $strDataBlood = $_REQUEST['dataBlood'] : $strDataBlood = "";
    (isset($_REQUEST['dataIDCard'])) ? $strDataIDCard = $_REQUEST['dataIDCard'] : $strDataIDCard = "";
    (isset($_REQUEST['dataLicenseA'])) ? $strDataLicenseA = $_REQUEST['dataLicenseA'] : $strDataLicenseA = "";
    (isset($_REQUEST['dataLicenseB'])) ? $strDataLicenseB = $_REQUEST['dataLicenseB'] : $strDataLicenseB = "";
    (isset($_REQUEST['dataLicenseC'])) ? $strDataLicenseC = $_REQUEST['dataLicenseC'] : $strDataLicenseC = "";
    (isset($_REQUEST['dataReligion'])) ? $strDataReligion = $_REQUEST['dataReligion'] : $strDataReligion = "";
    (isset($_REQUEST['dataWeddingDate'])) ? $strDataWeddingDate = $_REQUEST['dataWeddingDate'] : $strDataWeddingDate = "";
    $strDataIsBirthday = 't';

    $strDataBirthday = ($strDataBirthday == "") ? "NULL" : "'$strDataBirthday'";
    $strDataWeddingDate = ($strDataWeddingDate == "") ? "NULL" : "'$strDataWeddingDate'";

    // cek validasi -----------------------
    if ($strDataEmployeeID == "") {
      $strError = $error['empty_code'];
      return false;
    } else {
      $strKriteria = ($strDataID == "") ? "" : "AND id <> '$strDataID'";
      if (isDataExists("hrd_employee","employee_id",$strDataEmployeeID,$strKriteria)) {
        $strError = $error['duplicate_code']. " employee id -> $strDataEmployeeID";
        return false;
      }
    }

    // jika bukan datanya, langsung skip
    if ($strDataEmployeeID != $arrUserInfo['employee_id'])
    {
      $strError = getWords("edit_denied");
      return false;
    }

    // simpan data -----------------------
    if ($strDataID == "") {
      // skip, gak bleh nambah baru
    }/* 
    else 
    {
      $bolTemp = false;
      $strLinkID = ""; // ID asli, jika merupakan temporer

      // cek dulu, apakah data temporer atau bukan
      $strSQL  = "SELECT id, flag, link_id FROM hrd_employee WHERE id = '$strDataID' ";
      $resDb = $db->execute($strSQL);
      if ($rowDb = $db->fetchrow($resDb)) 
      {
        $bolTemp = ($rowDb['flag'] != 0);
        $strLinkID = $rowDb['link_id'];
      }

      if (!$bolTemp) 
      { // jika bukan temporer
        // cek, apakah sudah ada penambahan data temporery, kalau belum, update
        $strSQL  = "SELECT id FROM hrd_employee WHERE flag <> 0 AND link_id = '$strDataID' ";
        $resDb = $db->execute($strSQL);
        if ($rowDb = $db->fetchrow($resDb)) {
          // sudah ada
        } else {
          // belum ada, buat dulu
          $strDataID = createTempData($db, $strDataID);
          $bolTemp = true;
        }
       }

      $strSQL  = "UPDATE hrd_employee ";
      $strSQL .= "SET modified_by = '" .$_SESSION['sessionUserID']. "', ";
      // jika bukan temporery dan pegawai, ubah status ke 1
      if ($bolTemp && $_SESSION['sessionUserRole'] != ROLE_SUPERVISOR) {
        $strSQL .= "flag = 1, ";
      }
      $strSQL .= "employee_name = '$strdataName', barcode = '$strDataFingerID', ";
      $strSQL .= "gender = '$strDataGender', primary_address = '$strDataAddress', ";
      $strSQL .= "salary_payment_type = '$strDataSalaryPaymentType', ";
      $strSQL .= "primary_city = '$strDataCity', primary_zip = '$strDataZip', ";
      $strSQL .= "primary_phone = '$strDataPhone', is_birthday = '$strDataIsBirthday', ";
      $strSQL .= "birth_place = '$strDataBirthPlace', \"birthday\" = $strDataBirthday, ";
      $strSQL .= "emergency_contact = '$strDataEmergencyContact', ";
      $strSQL .= "emergency_relation = '$strDataEmergencyRelation', ";
      $strSQL .= "emergency_address = '$strDataEmergencyAddress', ";
      $strSQL .= "emergency_phone = '$strDataEmergencyPhone', ";
      $strSQL .= "nationality = '$strDataNationality', id_card = '$strDataIDCard', ";
      $strSQL .= "driver_license_a = '$strDataLicenseA', ";
      $strSQL .= "driver_license_b = '$strDataLicenseB', ";
      $strSQL .= "driver_license_c = '$strDataLicenseC', ";
      $strSQL .= "weight = '$strDataWeight', height = '$strDataHeight', blood_type = '$strDataBlood', ";
      $strSQL .= "religion_code = '$strDataReligion', email = '$strDataEmail', ";
      $strSQL .= "wedding_date = $strDataWeddingDate, ";
      $strSQL .= "nickname = '$strdataNick', assigntment_note = '$strDataLetterCode', passport = '$strDataPassport' ";
      $strSQL .= "WHERE id = '$strDataID' "; // yang diubah adalah field data temporer
      $resExec = $db->execute($strSQL);
      writeLog(ACTIVITY_EDIT, MODULE_PAYROLL,"$strDataID -> $strDataEmployeeID",0);

    }*/

    // simpan data gambar, jika ada
    if ($strDataID != "") {
      if (is_uploaded_file($_FILES["dataPhoto"]['tmp_name'])) {
        $arrNamaFile = explode(".",$_FILES["dataPhoto"]['name']);
        $strNamaFile = strtolower($strDataEmployeeID);
        if (count($arrNamaFile) > 0) {
          $strNamaFile .= ".". $arrNamaFile[count($arrNamaFile) -1];
        }

        clearstatcache();
        if (!is_dir("photos")) {
          mkdir("photos", 0755);
        }

        $strNamaFileLengkap = "photos/".$strNamaFile;
        if (file_exists($strNamaFileLengkap)) {
          unlink($strNamaFileLengkap);
        }
        if (move_uploaded_file($_FILES['dataPhoto']['tmp_name'], $strNamaFileLengkap)) {
          // update data
          $strSQL  = "UPDATE hrd_employee SET photo = '$strNamaFile' WHERE id = '$strDataID' ";
          $resExec = $db->execute($strSQL);
        }

      }
    }
    $strError = $messages['data_saved'];
    return true;
  } // saveDataEmployee

  // fungsi untuk mengambil daftar alamat lainnya , trmasuk pilihan
  function getMoreAddress($db,$strDataID = "") {
    global $words;
    global $intDefaultWidth;
    global $intDefaultWidthPx;
    global $intDefaultHeight;

    $strResult = "";
    $intAdd = 5;
    $intTotal = 5;

    $intCurr = 0;
    // cari data address tambahan, jika ada
    if ($strDataID != "") {
      $strSQL  = "SELECT * FROM hrd_employee_address ";
      $strSQL .= "WHERE id_employee = '$strDataID' ";
      $resDb = $db->execute($strSQL);
      while ($rowDb = $db->fetchrow($resDb)) {
        $intCurr++;
        $strResult .= "<tr valign=top id='detailAddress$intCurr'>\n";
        $strResult .= "  <td><input type=hidden name=dataMoreAddressID$intCurr value=\"" .$rowDb['id']. "\">&nbsp;</td>\n";
        $strResult .= "  <td>&nbsp;</td>\n";
        $strResult .= "  <td><textarea name='dataMoreAddress$intCurr' cols=$intDefaultWidth rows=$intDefaultHeight style=\"width:$intDefaultWidthPx\">" .$rowDb['address']. "</textarea></td>";
        $strResult .= "</tr>\n";

        $strResult .= "<tr valign=top id='detailCity$intCurr'>\n";
        $strResult .= "  <td>&nbsp;</td>\n";
        $strResult .= "  <td>&nbsp;</td>\n";
        $strResult .= "  <td><input type=text name=dataMoreCity$intCurr size=20 maxlength=50 value=\"" .$rowDb['city']. "\" style=\"width:" .floor(($intDefaultWidthPx / 3) * 2). "\"> / ";
        $strResult .= " <input type=text name=dataMoreZip$intCurr size=15 maxlength=30 value=\"" .$rowDb['zip']. "\" style=\"width:" .floor(($intDefaultWidthPx / 3)). "\"></td>";
        $strResult .= "</tr>\n";
      }
    }

    for ($i = 1; $i <= $intAdd; $i++) {
      $intCurr++;
      $strResult .= "<tr valign=top id='detailAddress$intCurr' style=\"display:none\">\n";
      $strResult .= "  <td>&nbsp;</td>\n";
      $strResult .= "  <td>&nbsp;</td>\n";
      $strResult .= "  <td><textarea name='dataMoreAddress$intCurr' cols=$intDefaultWidth rows=$intDefaultHeight style=\"width:$intDefaultWidthPx\"></textarea></td>";
      $strResult .= "</tr>\n";

      $strResult .= "<tr valign=top id='detailCity$intCurr' style=\"display:none\">\n";
      $strResult .= "  <td>&nbsp;</td>\n";
      $strResult .= "  <td>&nbsp;</td>\n";
      $strResult .= "  <td><input type=text name=dataMoreCity$intCurr size=20 maxlength=50 style=\"width:" .floor(($intDefaultWidthPx / 3) * 2). "\"> / ";
      $strResult .= " <input type=text name=dataMoreZip$intCurr size=15 maxlength=30 style=\"width:" .floor(($intDefaultWidthPx / 3)). "\"></td>";
      $strResult .= "</tr>\n";
    }

    $strResult .= "<tr valign=top>\n";
    $strResult .= "  <td>&nbsp;<input type=hidden name='numShowAddress' value=" .($intCurr-$intAdd). "></td>\n";
    $strResult .= "  <td>&nbsp;<input type=hidden name='maxDetailAddress' value=$intCurr></td>\n";
    $strResult .= "  <td><a href=\"javascript:showMoreAddress()\">" .$words["more address"]. "</a>&nbsp;</td>";
    $strResult .= "</tr>\n";

    return $strResult;
  } //getMoreAddress

  // fungsi untuk mengambil daftar phone lainnya , trmasuk pilihan
  function getMorePhone($db,$strDataID = "") {
    global $words;
    global $intDefaultWidth;
    global $intDefaultWidthPx;
    global $intDefaultHeight;

    $strResult = "";
    $intAdd = 5;
    $intTotal = 5;

    $intCurr = 0;
    // cari data phone tambahan, jika ada
    if ($strDataID != "") {
      $strSQL  = "SELECT * FROM hrd_employee_phone ";
      $strSQL .= "WHERE id_employee = '$strDataID' ";
      $resDb = $db->execute($strSQL);
      while ($rowDb = $db->fetchrow($resDb)) {
        $intCurr++;
        $strResult .= "<tr valign=top id='detailPhone$intCurr'>\n";
        $strResult .= "  <td><input type=hidden name=dataMorePhoneID$intCurr value=\"" .$rowDb['id']. "\">&nbsp;</td>\n";
        $strResult .= "  <td>&nbsp;</td>\n";
        $strResult .= "  <td><input type=text name='dataMorePhone$intCurr' size=$intDefaultWidth maxlength=50 value=\"" .$rowDb['phone']. "\" style=\"width:$intDefaultWidthPx\"></td>";
        $strResult .= "</tr>\n";
      }
    }

    for ($i = 1; $i <= $intAdd; $i++) {
      $intCurr++;
      $strResult .= "<tr valign=top id='detailPhone$intCurr' style=\"display:none\">\n";
      $strResult .= "  <td>&nbsp;</td>\n";
      $strResult .= "  <td>&nbsp;</td>\n";
      $strResult .= "  <td><input type=text name='dataMorePhone$intCurr' size=$intDefaultWidth maxlength=50 style=\"width:$intDefaultWidthPx\"></td>";
      $strResult .= "</tr>\n";
    }

    $strResult .= "<tr valign=top>\n";
    $strResult .= "  <td>&nbsp;<input type=hidden name='numShowPhone' value=" .($intCurr-$intAdd). "></td>\n";
    $strResult .= "  <td>&nbsp;<input type=hidden name='maxDetailPhone' value=$intCurr></td>\n";
    $strResult .= "  <td><a href=\"javascript:showMoreInput('Phone')\">" .$words["more phone"]. "</a>&nbsp;</td>";
    $strResult .= "</tr>\n";

    return $strResult;
  } //getMorePhone

  // fungsi untuk mengambil daftar emergency contact lainnya , trmasuk pilihan
  function getMoreContact($db,$strDataID = "") {
    global $words;
    global $intDefaultWidth;
    global $intDefaultWidthPx;
    global $intDefaultHeight;

    $strResult = "";
    $intAdd = 3;
    $intTotal = 5;

    $intCurr = 0;
    // cari data phone tambahan, jika ada
    if ($strDataID != "") {
      $strSQL  = "SELECT * FROM hrd_employee_contact ";
      $strSQL .= "WHERE id_employee = '$strDataID' ";
      $resDb = $db->execute($strSQL);
      while ($rowDb = $db->fetchrow($resDb)) {
        $intCurr++;
        $strResult .= "<tr valign=top id='detailEmergencyContact$intCurr'>\n";
        $strResult .= "  <td nowrap><input type=hidden name=dataMoreEmergencyID$intCurr value=\"" .$rowDb['id']. "\">&nbsp;" .$words['emergency contact']. " ".($intCurr+1). "</td>\n";
        $strResult .= "  <td>:&nbsp;</td>\n";
        $strResult .= "  <td><input type=text name='dataMoreEmergencyContact$intCurr' size=$intDefaultWidth maxlength=50 value=\"" .$rowDb['contact']. "\" style=\"width:$intDefaultWidthPx\"></td>";
        $strResult .= "</tr>\n";
        $strResult .= "<tr valign=top id='detailEmergencyRelation$intCurr'>\n";
        $strResult .= "  <td nowrap>&nbsp;" .$words['relation']. "</td>\n";
        $strResult .= "  <td>:&nbsp;</td>\n";
        $strResult .= "  <td><input type=text name='dataMoreEmergencyRelation$intCurr' size=$intDefaultWidth maxlength=50 value=\"" .$rowDb['relation']. "\" style=\"width:$intDefaultWidthPx\"></td>";
        $strResult .= "</tr>\n";
        $strResult .= "<tr valign=top id='detailEmergencyAddress$intCurr'>\n";
        $strResult .= "  <td nowrap>&nbsp;" .$words['emergency address']. "</td>\n";
        $strResult .= "  <td>:&nbsp;</td>\n";
        $strResult .= "  <td><textarea name='dataMoreEmergencyAddress$intCurr' cols=$intDefaultWidth rows=$intDefaultHeight style=\"width:$intDefaultWidthPx\">" .$rowDb['address']. "</textarea></td>";
        $strResult .= "</tr>\n";
        $strResult .= "<tr valign=top id='detailEmergencyPhone$intCurr'>\n";
        $strResult .= "  <td nowrap>&nbsp;" .$words['emergency phone']. "</td>\n";
        $strResult .= "  <td>:&nbsp;</td>\n";
        $strResult .= "  <td><input type=text name='dataMoreEmergencyPhone$intCurr' size=$intDefaultWidth maxlength=50 value=\"" .$rowDb['phone']. "\" style=\"width:$intDefaultWidthPx\"></td>";
        $strResult .= "</tr>\n";
      }
    }

    for ($i = 1; $i <= $intAdd; $i++) {
      $intCurr++;
      $strResult .= "<tr valign=top id='detailEmergencyContact$intCurr' style=\"display:none\">\n";
      $strResult .= "  <td nowrap>&nbsp;" .$words['emergency contact']." ".($intCurr+1). "</td>\n";
      $strResult .= "  <td>:&nbsp;</td>\n";
      $strResult .= "  <td><input type=text name='dataMoreEmergencyContact$intCurr' size=$intDefaultWidth maxlength=50 style=\"width:$intDefaultWidthPx\"></td>";
      $strResult .= "</tr>\n";
      $strResult .= "<tr valign=top id='detailEmergencyRelation$intCurr' style=\"display:none\">\n";
      $strResult .= "  <td nowrap>&nbsp;" .$words['relation']. "</td>\n";
      $strResult .= "  <td>:&nbsp;</td>\n";
      $strResult .= "  <td><input type=text name='dataMoreEmergencyRelation$intCurr' size=$intDefaultWidth maxlength=50 style=\"width:$intDefaultWidthPx\"></td>";
      $strResult .= "</tr>\n";
      $strResult .= "<tr valign=top id='detailEmergencyAddress$intCurr' style=\"display:none\">\n";
      $strResult .= "  <td nowrap>&nbsp;" .$words['emergency address']. "</td>\n";
      $strResult .= "  <td>:&nbsp;</td>\n";
      $strResult .= "  <td><textarea name='dataMoreEmergencyAddress$intCurr' cols=$intDefaultWidth rows=$intDefaultHeight style=\"width:$intDefaultWidthPx\"></textarea></td>";
      $strResult .= "</tr>\n";
      $strResult .= "<tr valign=top id='detailEmergencyPhone$intCurr' style=\"display:none\">\n";
      $strResult .= "  <td nowrap>&nbsp;" .$words['emergency phone']. "</td>\n";
      $strResult .= "  <td>:&nbsp;</td>\n";
      $strResult .= "  <td><input type=text name='dataMoreEmergencyPhone$intCurr' size=$intDefaultWidth maxlength=50 style=\"width:$intDefaultWidthPx\"></td>";
      $strResult .= "</tr>\n";
    }

    $strResult .= "<tr valign=top>\n";
    $strResult .= "  <td>&nbsp;<input type=hidden name='numShowContact' value=" .($intCurr-$intAdd). "></td>\n";
    $strResult .= "  <td>&nbsp;<input type=hidden name='maxDetailContact' value=$intCurr></td>\n";
    $strResult .= "  <td><a href=\"javascript:showMoreInputContact()\">" .$words["more emergency"]. "</a>&nbsp;</td>";
    $strResult .= "</tr>\n";

    return $strResult;
  } //getMorePhone

  //----------------------------------------------------------------------

  //----MAIN PROGRAM -----------------------------------------------------
  $db = new CdbClass;
  if ($db->connect()) {
    getUserEmployeeInfo();
 
    (isset($_REQUEST['dataID'])) ? $strDataID = $_REQUEST['dataID'] : $strDataID = "";
    $bolNew = ($strDataID == "");

    if (isset($_POST['btnSave'])) {
      if ($bolCanEdit) {
        if ($_SESSION['sessionUserRole'] > ROLE_ADMIN || ($_SESSION['sessionUserRole'] >= ROLE_ADMIN && $bolNew) ) {
          $bolError = !saveData($db, $strDataID, $strError);
        } else {

          $bolError = !saveDataEmployee($db, $strDataID, $strError); // save data khusus employee
        }
        if ($strError != "") {

          $strMessages = $strError;
          $strMsgClass = ($bolError) ? "class=bgError" : "class=bgOK";
        }
      }
    } else if (isset($_POST['btnDeletePic'])) {
      if ($bolCanEdit && $_SESSION['sessionUserRole'] != ROLE_EMPLOYEE) {
        deletePicture($db,$strDataID);
      } else {
        $strMessages = getWords('delete_denied');
        $strMsgClass = "class=bgError";
      }
    }
    if ($bolCanView || $strDataID != "") {
      getData($db, $arrData, $strDataID);

    } else {
      //showError("view_denied");
      $strMessages = $messages['view_denied'];
      $strMsgClass = "class=bgError";
      $strDataDetail = "";
    }

    //----- TAMPILKAN DATA ---------
    $strDataPhoto = "";
    if ($bolIsEmployee && !isMe($strDataID))
        redirectPage("employee_search.php");
    if (thisUserIs(ROLE_SUPERVISOR))
    {
      if($arrUserInfo['sub_section_code'] != "" && $arrUserInfo['sub_section_code'] != $arrData['dataSubSection'])
        redirectPage("employee_search.php");
      else if($arrUserInfo['section_code'] != "" && $arrUserInfo['section_code'] != $arrData['dataSection'])
        redirectPage("employee_search.php");
      else if($arrUserInfo['department_code'] != "" && $arrUserInfo['department_code'] != $arrData['dataDepartment'])
        redirectPage("employee_search.php");
      else if($arrUserInfo['division_code'] != "" && $arrUserInfo['division_code'] != $arrData['dataDivision'])
        redirectPage("employee_search.php");
    }

    // bagian yang hanya boleh diedit oleh admin/manager
    if ($_SESSION['sessionUserRole'] > ROLE_ADMIN || ($_SESSION['sessionUserRole'] >= ROLE_ADMIN && $bolNew)) {
      $strInputName = "<input type=text name=dataName size=$intDefaultWidth maxlength=127 value=\"" .$arrData['dataName']. "\" style=\"width:$intDefaultWidthPx\" class='string' onChange=\"changeEmployeeName();\">";
      $strInputFingerID = "<input type=text name=dataFingerID size=$intDefaultWidth maxlength=15 value=\"" .$arrData['dataFingerID']. "\" style=\"width:$intDefaultWidthPx\" >";
      $strInputNick = "<input type=text name=dataNick size=$intDefaultWidth maxlength=15 value=\"" .$arrData['dataNick']. "\" style=\"width:$intDefaultWidthPx\">";
      $strInputLetterCode = "<input type=text name=dataLetterCode size=$intDefaultWidth maxlength=63 value=\"" .$arrData['dataLetterCode']. "\" style=\"width:$intDefaultWidthPx\">";
      $strInputTransport = "<input type=text name=dataTransport size=$intDefaultWidth maxlength=63 value=\"" .$arrData['dataTransport']. "\" style=\"width:$intDefaultWidthPx\">";
      $strInputTransportFee = "<input type=text name=dataTransportFee size=$intDefaultWidth maxlength=63 value=\"" .$arrData['dataTransportFee']. "\" style=\"width:$intDefaultWidthPx\">";
      $strInputCity = "<input type=text name=dataCity size=20 maxlength=31 value=\"" .$arrData['dataCity']. "\" style=\"width:" .(floor(($intDefaultWidthPx / 3) * 2) - 13). "\">";
      $strInputZip = "<input type=text name=dataZip size=20 maxlength=15 value=\"" .$arrData['dataZip']. "\" style=\"width:" .floor(($intDefaultWidthPx / 3)). "\">";
      $strInputPhone = "<input type=text name=dataPhone size=$intDefaultWidth maxlength=31 value=\"" .$arrData['dataPhone']. "\" style=\"width:$intDefaultWidthPx\">";
      $strInputEmail = "<input type=text name=dataEmail size=$intDefaultWidth maxlength=63 value=\"" .$arrData['dataEmail']. "\" style=\"width:$intDefaultWidthPx\">";
      $strInputEmergencyContact = "<input type=text name=dataEmergencyContact size=$intDefaultWidth maxlength=63 value=\"" .$arrData['dataEmergencyContact']. "\" style=\"width:$intDefaultWidthPx\">";
      $strInputEmergencyRelation = "<input type=text name=dataEmergencyRelation size=$intDefaultWidth maxlength=31 value=\"" .$arrData['dataEmergencyRelation']. "\" style=\"width:$intDefaultWidthPx\">";
      $strInputEmergencyPhone = "<input type=text name=dataEmergencyPhone size=$intDefaultWidth maxlength=31 value=\"" .$arrData['dataEmergencyPhone']. "\" style=\"width:$intDefaultWidthPx\">";
      $strInputBirthPlace = "<input type=text name=dataBirthPlace size=$intDefaultWidth maxlength=63 value=\"" .$arrData['dataBirthPlace']. "\" style=\"width:$intDefaultWidthPx\">";
      $strInputWeight = "<input type=text name=dataWeight size=$intDefaultWidth class='numeric' maxlength=10 value=\"" .$arrData['dataWeight']. "\" style=\"width:$intDefaultWidthPx\">";
      $strInputHeight = "<input type=text name=dataHeight size=$intDefaultWidth class='numeric' maxlength=10 value=\"" .$arrData['dataHeight']. "\" style=\"width:$intDefaultWidthPx\">";
      $strInputBlood = "<input type=text name=dataBlood size=$intDefaultWidth maxlength=3 value=\"" .$arrData['dataBlood']. "\" style=\"width:$intDefaultWidthPx\">";
      $strInputIDCard = "<input type=text name=dataIDCard size=$intDefaultWidth maxlength=31 value=\"" .$arrData['dataIDCard']. "\" style=\"width:$intDefaultWidthPx\">";
      $strInputLicenseA = "<input type=text name=dataLicenseA size=$intDefaultWidth maxlength=31 value=\"" .$arrData['dataLicenseA']. "\" style=\"width:$intDefaultWidthPx\">";
      $strInputLicenseB = "<input type=text name=dataLicenseB size=$intDefaultWidth maxlength=31 value=\"" .$arrData['dataLicenseB']. "\" style=\"width:$intDefaultWidthPx\">";
      $strInputLicenseC = "<input type=text name=dataLicenseC size=$intDefaultWidth maxlength=31 value=\"" .$arrData['dataLicenseC']. "\" style=\"width:$intDefaultWidthPx\">";
      $strInputNationality = "<input type=text name=dataNationality size=$intDefaultWidth maxlength=31 value=\"" .$arrData['dataNationality']. "\" style=\"width:$intDefaultWidthPx\">";
      $strInputPassport = "<input type=text name=dataPassport size=$intDefaultWidth maxlength=31 value=\"" .$arrData['dataPassport']. "\" style=\"width:$intDefaultWidthPx\">";
      $strInputBirthday = "<input type=text name=dataBirthday id=dataBirthday size=" .($intDefaultWidth-5)." maxlength=10 value=\"" .$arrData['dataBirthday']. "\"  style=\"width:". ($intDefaultWidthPx). "\">";
      $strInputAddress = "<textarea name=dataAddress cols=$intDefaultWidth rows=$intDefaultHeight wrap='virtual' style=\"width:$intDefaultWidthPx\">" .$arrData['dataAddress']. "</textarea>";
      $strInputEmergencyAddress = "<textarea name=dataEmergencyAddress cols=$intDefaultWidth rows=$intDefaultHeight wrap='virtual' style=\"width:$intDefaultWidthPx\">" .$arrData['dataEmergencyAddress']. "</textarea>";
      $strInputGender = getGenderList("dataGender",$arrData['dataGender'],""," style=\"width:$intDefaultWidthPx\"");
      $strInputSalaryPaymentType = getComboFromArray($ARRAY_PAYMENT_METHOD, "dataSalaryPaymentType", $arrData['dataSalaryPaymentType'], $extra = "", $action = "");

      $strInputReligion = getReligionList($db,"dataReligion",$arrData['dataReligion'], $strEmptyOption,""," style=\"width:$intDefaultWidthPx\"");

      $strInputWeddingDate = "<input type=text name=dataWeddingDate id='dataWeddingDate' size=$intDefaultWidth maxlength=10 value=\"" .$arrData['dataWeddingDate']. "\" style=\"width:$intDefaultWidthPx\" title=\"format:yyyy-mm-dd\" class='date-empty'>";


      $strInputEmployeeID = "<input type=text name=dataEmployeeID size=$intDefaultWidth maxlength=50 value=\"" .$arrData['dataEmployeeID']. "\" style=\"width:$intDefaultWidthPx\" $strEmpReadonly>";

      $strInputNPWP = "<input type=text name=dataNPWP size=$intDefaultWidth maxlength=50 value=\"" .$arrData['dataNPWP']. "\" style=\"width:$intDefaultWidthPx\">";
      $strInputJamsostekNo = "<input type=text name=dataJamsostekNo size=$intDefaultWidth maxlength=50 value=\"" .$arrData['dataJamsostekNo']. "\" style=\"width:$intDefaultWidthPx\">";

      $strInputBankCode = getBankList($db,"dataBankCode",$arrData['dataBankCode'], $strEmptyOption,""," style=\"width:$intDefaultWidthPx\"");

      $strInputBankBranch = "<input type=text name=dataBankBranch size=$intDefaultWidth maxlength=63 value=\"" .$arrData['dataBankBranch']. "\" style=\"width:$intDefaultWidthPx\">";


      $strInputBankAccountType = "<input type=text name=dataBankBranchAccountType size=$intDefaultWidth maxlength=31 value=\"" .$arrData['dataBankAccountType']. "\" style=\"width:$intDefaultWidthPx\">";
      $strInputBankAccount = "<input type=text name=dataBankAccount size=$intDefaultWidth maxlength=31 value=\"" .$arrData['dataBankAccount']. "\" style=\"width:$intDefaultWidthPx\">";
      $strInputBankAccountName = "<input type=text name=dataBankAccountName size=$intDefaultWidth maxlength=127 value=\"" .$arrData['dataBankAccountName']. "\" style=\"width:$intDefaultWidthPx\">";

      $strInputBank2Code = getBankList($db,"dataBank2Code",$arrData['dataBank2Code'], $strEmptyOption,""," style=\"width:$intDefaultWidthPx\"");
      $strInputBank2Branch = "<input type=text name=dataBank2Branch size=$intDefaultWidth maxlength=63 value=\"" .$arrData['dataBank2Branch']. "\" style=\"width:$intDefaultWidthPx\">";
      $strInputBank2AccountType = "<input type=text name=dataBank2BranchAccountType size=$intDefaultWidth maxlength=31 value=\"" .$arrData['dataBank2AccountType']. "\" style=\"width:$intDefaultWidthPx\">";
      $strInputBank2Account = "<input type=text name=dataBank2Account size=$intDefaultWidth maxlength=31 value=\"" .$arrData['dataBank2Account']. "\" style=\"width:$intDefaultWidthPx\">";
      $strInputBank2AccountName = "<input type=text name=dataBank2AccountName size=$intDefaultWidth maxlength=127 value=\"" .$arrData['dataBank2AccountName']. "\" style=\"width:$intDefaultWidthPx\">";

      $strOnChange = "onChange = \"onBirthdayChange();\"";
      if ($arrData['dataIsBirthday'] == 't') {
        $strChecked = "checked";
        $strDisabled = "";
      } else {
        $strChecked = "";
        $strDisabled = "disabled";
      }
      $strInputJoinDate = "<input type=text name=dataJoinDate id='dataJoinDate' size=$intDefaultWidth maxlength=10 value=\"" .$arrData['dataJoinDate']. "\" style=\"width:$intDefaultWidthPx\" class='date'>";

      $strInputDueDate = "<input type=text name=dataDueDate id='dataDueDate' size=$intDefaultWidth maxlength=10 value=\"" .$arrData['dataDueDate']. "\" style=\"width:$intDefaultWidthPx\" class='date-empty'>";
      $strInputPermanentDate = "<input type=text name=dataPermanentDate id='dataPermanentDate' size=$intDefaultWidth maxlength=10 value=\"" .$arrData['dataPermanentDate']. "\" style=\"width:$intDefaultWidthPx\" class='date-empty'>";
      $strInputResignDate = "<input type=text name=dataResignDate id='dataResignDate' size=$intDefaultWidth maxlength=10 value=\"" .$arrData['dataResignDate']. "\" style=\"width:$intDefaultWidthPx\" class='date-empty'>";

      $strInputNote = "<textarea name=dataNote cols=$intDefaultWidth rows=$intDefaultHeight wrap='virtual' style=\"width:$intDefaultWidthPx\">" .$arrData['dataNote']. "</textarea>";
      $strInputEmployeeStatus = getEmployeeStatusList("dataEmployeeStatus",$arrData['dataEmployeeStatus'], ""," style=\"width:$intDefaultWidthPx\"");
      $strInputEducation = getEducationList($db,"dataEducation",$arrData['dataEducation'], $strEmptyOption,""," style=\"width:$intDefaultWidthPx\"");
      $strInputFamilyStatus = getFamilyStatusList($db,"dataFamilyStatus",$arrData['dataFamilyStatus'], $strEmptyOption,""," style=\"width:$intDefaultWidthPx\"");
      $strInputLivingCost = getLivingCostList($db,"dataLivingCost",$arrData['dataLivingCost'], $strEmptyOption,""," style=\"width:$intDefaultWidthPx\"");
      $strInputMedicalQuotaStatus = getFamilyStatusList($db,"dataMedicalQuotaStatus",$arrData['dataMedicalQuotaStatus'], $strEmptyOption,""," style=\"width:$intDefaultWidthPx\"");
      if ($arrData['dataInspouse'] == 't') {
        $strInputInspouse = "<input type=checkbox name=dataInspouse value=1 checked>";
      } else {
        $strInputInspouse = "<input type=checkbox name=dataInspouse value=0>";
      }      
      $strInputManagement = getManagementList($db,"dataManagement",$arrData['dataManagement'], $strEmptyOption,""," style=\"width:$intDefaultWidthPx\"");
      $strInputDivision = getDivisionList($db,"dataDivision",$arrData['dataDivision'], $strEmptyOption,""," style=\"width:$intDefaultWidthPx\" onChange=\"checkDivision()\"");
      $strInputDepartment = getDepartmentList($db,"dataDepartment",$arrData['dataDepartment'], $strEmptyOption,""," style=\"width:$intDefaultWidthPx\" onChange=\"checkDepartment()\"");
      $strInputCompany = getCompanyList($db, "dataCompany", $arrData['dataCompany'], "", $strKriteria2, "style=\"width:$intDefaultWidthPx\"");   

      $strInputSection = getSectionList($db,"dataSection",$arrData['dataSection'], $strEmptyOption,""," style=\"width:$intDefaultWidthPx\" onChange=\"checkSection()\"");
      $strInputSubSection = getSubSectionList($db,"dataSubSection",$arrData['dataSubSection'], $strEmptyOption,""," style=\"width:$intDefaultWidthPx\" onChange=\"checkSubSection()\"");
      $strInputFunctionalPosition = getFunctionalPositionList($db,"dataFunctionalPosition",$arrData['dataFunctionalPosition'], $strEmptyOption,""," style=\"width:$intDefaultWidthPx\"");
      //echo $arrData['dataFunctionalPosition'];
      $strInputBranch = getBranchList($db,"dataBranch",$arrData['dataBranch'], $strEmptyOption,""," style=\"width:$intDefaultWidthPx\"");
      $strInputPosition = getPositionList($db,"dataPosition",$arrData['dataPosition'], $strEmptyOption,""," style=\"width:$intDefaultWidthPx\"");

      $strInputSalaryGrade = getSalaryGradeList($db,"dataSalaryGrade",$arrData['dataSalaryGrade'], $strEmptyOption,""," style=\"width:$intDefaultWidthPx\"");
      if ($arrData['dataActive'] == 1) {
        $strInputActive = "<input type=checkbox name=dataActive value=1 checked>";
      } else {
        $strInputActive = "<input type=checkbox name=dataActive value=0>";
      }
      if ($arrData['dataIsZakat'] == 't') {
        $strInputIsZakat = "<input type=checkbox name=dataIsZakat value=1 checked>";
      } else {
        $strInputIsZakat = "<input type=checkbox name=dataIsZakat value=0>";
      }

    } 
    else 
    { // employee hanya bisa view

      $arrCompanyName = array();
      $strSQL  = "SELECT id, company_name FROM hrd_company ";
      $resTmp = $db->execute($strSQL);
      while ($rowTmp = $db->fetchrow($resTmp)) 
      {
        $arrCompanyName[$rowTmp['id']] = $rowTmp['company_name'];
      }
      $arrReligion = array();
      $strSQL  = "SELECT code, name FROM hrd_religion ";
      $resTmp = $db->execute($strSQL);
      while ($rowTmp = $db->fetchrow($resTmp)) 
      {
        $arrReligion[$rowTmp['code']] = $rowTmp['name'];
      }
      $arrSubSection = array();
      $strSQL  = "SELECT sub_section_code, sub_section_name FROM hrd_sub_section ";
      $resTmp = $db->execute($strSQL);
      while ($rowTmp = $db->fetchrow($resTmp)) 
      {
        $arrSubSection[$rowTmp['sub_section_code']] = $rowTmp['sub_section_name'];
      }
      $arrSection = array();
      $strSQL  = "SELECT section_code, section_name FROM hrd_section ";
      $resTmp = $db->execute($strSQL);
      while ($rowTmp = $db->fetchrow($resTmp)) 
      {
        $arrSection[$rowTmp['section_code']] = $rowTmp['section_name'];
      }
      $arrDepartment = array();
      $strSQL  = "SELECT department_code, department_name FROM hrd_department";
      $resTmp = $db->execute($strSQL);
      while ($rowTmp = $db->fetchrow($resTmp)) 
      {
        $arrDepartment[$rowTmp['department_code']] = $rowTmp['department_name'];
      }
      $arrDivision = array();
      $strSQL  = "SELECT division_code, division_name FROM hrd_division";
      $resTmp = $db->execute($strSQL);
      while ($rowTmp = $db->fetchrow($resTmp)) 
      {
        $arrDivision[$rowTmp['division_code']] = $rowTmp['division_name'];
      }
      $arrManagement = array();
      $strSQL  = "SELECT management_code, management_name FROM hrd_management ";
      $resTmp = $db->execute($strSQL);
      while ($rowTmp = $db->fetchrow($resTmp)) 
      {
        $arrManagement[$rowTmp['management_code']] = $rowTmp['management_name'];
      }
      $arrBranch = array();
      $strSQL  = "SELECT branch_code, branch_name FROM hrd_branch";
      $resTmp = $db->execute($strSQL);
      while ($rowTmp = $db->fetchrow($resTmp)) 
      {
        $arrBranch[$rowTmp['branch_code']] = $rowTmp['branch_code'] ." - ".$rowTmp['branch_name'];
      }
      $arrPosition = array();
      $strSQL  = "SELECT position_code, position_name FROM hrd_position";
      $resTmp = $db->execute($strSQL);
      while ($rowTmp = $db->fetchrow($resTmp)) 
      {
        $arrPosition[$rowTmp['position_code']] = $rowTmp['position_name'];
      }

      foreach ($arrData AS $strCode => $strValue)
      {
        $strTemp = str_replace("data", "strInput", $strCode);
        $$strTemp = ($strValue == "")? "-" : $strValue;
      }
      $strInputEmployeeID = "<input type=text name=dataEmployeeID size=$intDefaultWidth maxlength=50 value=\"" .$arrData['dataEmployeeID']. "\" style=\"width:$intDefaultWidthPx\" readonly>";
      $strInputBirthday = ($strInputBirthday == "") ? "-" : $strInputBirthday;
      $strInputJoinDate = ($strInputJoinDate == "") ? "-" : $strInputJoinDate;
      $strInputDueDate = ($strInputDueDate == "") ? "-" : $strInputDueDate;
      $strInputPermanentDate = ($strInputPermanentDate == "") ? "-" : $strInputPermanentDate;
      $strInputResignDate = ($strInputResignDate == "") ? "-" : $strInputResignDate;
      $strInputWeddingDate = ($strInputWeddingDate == "") ? "-" : $strInputWeddingDate;
      $strInputCompany = $arrCompanyName[$strInputCompany];
      $strInputEmployeeStatus = getWords($ARRAY_EMPLOYEE_STATUS[$strInputEmployeeStatus]);
      $strInputGender = getWords($ARRAY_GENDER[$strInputGender]);
      $strInputReligion = $arrReligion[$strInputReligion];
      $strInputActive = ($strInputActive == 1) ? "Yes" : "No";
      $strInputInspouse = ($strInputInspouse == 't') ? "Yes" : "No";
      $strInputIsZakat = ($strInputIsZakat == 't') ? "Yes" : "No";
      $strInputSubSection = ($strInputSubSection == "-" ) ? "-" : $arrSubSection[$strInputSubSection];
      $strInputSection = ($strInputSection == "-") ? "-" : $arrSection[$strInputSection];
      $strInputDepartment = ($strInputDepartment == "-") ? "-" : $arrDepartment[$strInputDepartment];
      $strInputDivision = ($strInputDivision == "-") ? "-" : $arrDivision[$strInputDivision];
      $strInputManagement = ($strInputManagement == "-") ? "-" : $arrManagement[$strInputManagement];
      $strInputBranch = ($strInputBranch == "") ? "" : $arrBranch[$strInputBranch];
      $strInputPosition = ($strInputPosition == "") ? "" : $arrPosition[$strInputPosition];
    }

    $strMoreAddress = "";//getMoreAddress($db, $strDataID);
    $strMorePhone = "";//getMorePhone($db,$strDataID);
    $strMoreContact = "";//getMoreContact($db,$strDataID);

    //tampilkan foto
    if ($arrData['dataPhoto'] == "") {
      $strDataPhoto = "<img src='../images/dummy.gif'>";
    } else {
      if (file_exists("photos/".$arrData['dataPhoto'])) {
        //$strDataPhoto = "<img src='photos/" .$arrData['dataPhoto']. "'>";
        $strDataPhoto = "<img src=\"employee_photo.php?dataID=$strDataID\">";
      } else {
        $strDataPhoto = "<img src='../images/dummy.gif'>";
      }
    }

    // tambahkan button untuk next/prev employee
    if ($strDataID != "" && $arrData['dataName'] != "") {
      if ($_SESSION['sessionUserRole'] > ROLE_ADMIN || ($_SESSION['sessionUserRole'] >= ROLE_ADMIN && $bolNew)) {
        $strOrderField = "join_date";
        $strPrevID = "";
        $strNextID = "";

        // cari prev
        $strSQL  = "SELECT id FROM hrd_employee WHERE \"$strOrderField\" < '" .addslashes($arrData['dataJoinDate'])."' ";
        $strSQL .= "AND employee_id <> '" .addslashes($arrData['dataEmployeeID'])."' ";
        $strSQL .= " $strKriteriaCompany ORDER BY \"$strOrderField\" DESC LIMIT 1 ";
        $resDb = $db->execute($strSQL);
        if ($rowDb = $db->fetchrow($resDb)) {
          $strPrevID = $rowDb['id'];
        }

        // cari next
        $strSQL  = "SELECT id FROM hrd_employee WHERE \"$strOrderField\" > '" .addslashes($arrData['dataJoinDate'])."' ";
        $strSQL .= "AND employee_id <> '" .addslashes($arrData['dataEmployeeID'])."' ";
        $strSQL .= " $strKriteriaCompany ORDER BY \"$strOrderField\" LIMIT 1 ";
        $resDb = $db->execute($strSQL);
        if ($rowDb = $db->fetchrow($resDb)) {
          $strNextID = $rowDb['id'];
        }

        if ($strPrevID == "") {
          $strPrevDisabled = "disabled";
          $strPrevAction = "";
        } else {
          $strPrevDisabled = "";
          $strPrevAction = " onClick=\"location.href='employee_edit.php?dataID=$strPrevID'\" ";
        }
        if ($strNextID == "") {
          $strNextDisabled = "disabled";
          $strNextAction = "";
        } else {
          $strNextDisabled = "";
          $strNextAction = " onClick=\"location.href='employee_edit.php?dataID=$strNextID'\" ";
        }

        $strButtonNavigation .= "&nbsp;<input type=button name=btnPrev value=\"&lt;&lt; " .$words['prev']." \" $strPrevAction $strPrevDisabled> ";
        $strButtonNavigation .= "&nbsp;<input type=button name=btnNext value=\"" .$words['next']." &gt;&gt; \" $strNextAction $strNextDisabled> ";
      }
    }
  }


  $tbsPage = new clsTinyButStrong ;
  
  //write this variable in every page
  $strPageTitle = getWords("employee data");
  if (trim($dataPrivilege['icon_file']) == "") $pageIcon = "../images/icons/blank.gif"; 
  else $pageIcon = "../images/icons/".$dataPrivilege['icon_file'];
  $strTemplateFile = getTemplate(str_replace(".php", ".html", basename($_SERVER['PHP_SELF'])));  
  //------------------------------------------------
  //Load Master Template
  $tbsPage->LoadTemplate($strMainTemplate) ;
  $tbsPage->Show() ;
  
?>