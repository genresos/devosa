<?php
  // buat konstantanya
  define("SQL_TRUE", 't');
  define("SQL_FALSE", 'f');
  define("ROLE_EMPLOYEE", 0);
  define("ROLE_SUPERVISOR", 1);
  define("ROLE_SUPERVISOR_BRANCH", 6);
  define("ROLE_ADMIN", 2);
  define("ROLE_SUPER", 3);
  define("ROLE_BRANCH_ADMIN", 7);
  define("ROLE_DIVISION_BRANCH_ADMIN", 8);
  define("ROLE_CANDIDATE", -1);
  define("NEWBIE_LEAVE_QUOTA", 4);
  define("SPECIAL_ABSENCE", "LE");


  /* VARIABEL UNTUK USER ROLE (SCOPE DATA KARYAWAN YANG DAPAT DI VIEW)*/
  $ARRAY_GROUP_ROLE = array (
    ROLE_EMPLOYEE => "EMPLOYEE",
    ROLE_SUPERVISOR => "SUPERVISOR",
    ROLE_ADMIN => "ADMIN",
    ROLE_BRANCH_ADMIN => "BRANCH ADMIN",
    ROLE_DIVISION_BRANCH_ADMIN => "DIVISION BRANCH ADMIN",
    ROLE_SUPER => "SUPER",
    ROLE_CANDIDATE => "CANDIDATE",
    ROLE_SUPERVISOR_BRANCH => "SUPERVISOR BRANCH",
  );
  $ARRAY_DISABLE_GROUP = array (
    "division" => "",
    "department" => "",
    "section" => "",
    "sub_section" => ""
  );
  $strEmpReadonly = "";

    //define Types of overtime
  $ARRAY_OVERTIME_TYPE = array("lembur biasa", "lembur pada hari libur");
  define("OVERTIME_WORKDAY",0);
  define("OVERTIME_HOLIDAY",1);

  //define Types of partial absenc
  define("PARTIAL_ABSENCE_LATE",0);
  define("PARTIAL_ABSENCE_MID",1);
  define("PARTIAL_ABSENCE_EARLY",2);
  $ARRAY_PARTIAL_ABSENCE_TYPE = array( PARTIAL_ABSENCE_LATE => "late start", PARTIAL_ABSENCE_MID => "mid leave", PARTIAL_ABSENCE_EARLY => "early finish");


  define("AUTO_OT_MINIMUM_DURATION", 30);
  define("AUTO_OT_MAXIMUM_DURATION", 1000);

  //define Activity for logging user activity (Table adm_userlog)
  $ARRAY_ACTIVITY_TYPE = array("login", "logout", "view", "add", "edit", "delete","import","export", "search");
  define("ACTIVITY_LOGIN",0);
  define("ACTIVITY_LOGOUT",1);
  define("ACTIVITY_VIEW",2);
  define("ACTIVITY_ADD",3);
  define("ACTIVITY_EDIT",4);
  define("ACTIVITY_DELETE",5);
  define("ACTIVITY_IMPORT",6);
  define("ACTIVITY_EXPORT",7);
  define("ACTIVITY_SEARCH",8);
  define("ACTIVITY_PRINT",8);


  define("MODULE_ADMIN",0);
  define("MODULE_PAYROLL",1);
  define("MODULE_GA",2);
  define("MODULE_EMPLOYEE",3); // khusus yang bisa diakses karyawan
  define("MODULE_OTHER",4);
  define("MAX_ALLOWANCE_SET", 3);

  $ARRAY_ALLOWANCE_SET = array
  (
    "branch" => array("page_name" => "data_branch.php", "field_name" => "branch_allowance", "table_name" => "hrd_branch"),
    "grade" => array("page_name" => "data_salary_grade.php", "field_name" => "grade_allowance", "table_name" => "hrd_salary_grade"),
    "position" => array("page_name" => "data_position.php", "field_name" => "position_allowance", "table_name" => "hrd_position"),
    "family_status" => array("page_name" => "data_family_status.php", "field_name" => "family_status_allowance", "table_name" => "hrd_family_status"),
    "functional" => array("page_name" => "data_functional.php", "field_name" => "functional_allowance", "table_name" => "hrd_functional"),
  );



  DEFINE("HOURTOMIN", 60);
  DEFINE("SINGLE", 0);
  DEFINE("MARRIED", 1);
  DEFINE("MARRIED_SEPARATELY", 2);
  $ARRAY_MARITAL_STATUS = array(SINGLE => "Single", MARRIED => "Married", MARRIED_SEPARATELY => "Married but live separately");

  DEFINE("FEMALE", 0);
  DEFINE("MALE", 1);
  $ARRAY_GENDER = array(FEMALE => "female", MALE => "male");


  $ARRAY_CURRENCY_CODE = array(0 => "016", 1 => "000", 2 => "Yen", 3 => "000");
  $ARRAY_SALARY_PERIOD = array(0 => "Monthly", 1 => "Daily");
  $ARRAY_PAYMENT_METHOD = array(0 => "Standard", 1 => "All In");

  $ARRAY_BLOOD_TYPE = array("A" => "A", "B" => "B", "AB" => "AB", "O" => "O");

  define("STATUS_CONTRACT_1", 0);
  define("STATUS_CONTRACT_2", 1);
  define("STATUS_PERMANENT", 2);
  define("STATUS_OUTSOURCE", 3);
  define("STATUS_FREELANCE", 4);
//  define("STATUS_PROBATION", 4);
//  define("STATUS_RESIGNED", 5);
//  define("STATUS_PROMOTION", 6);
//  define("STATUS_CONTRACT_II", 7);

  $ARRAY_EMPLOYEE_STATUS = array(
    STATUS_CONTRACT_1 => "contract 1 ",
    STATUS_CONTRACT_2 => "contract 2 ",
    STATUS_PERMANENT => "permanent ",
    STATUS_OUTSOURCE => "outsource",
    STATUS_FREELANCE => "freelance"
//    STATUS_CONTRACT_II => "contract II",
//    STATUS_PERMANENT => "permanent",
//    STATUS_OUTSOURCE => "outsource",
//    STATUS_DAILY_WORKER => "daily worker",
//    STATUS_PROBATION => "probation",
//	STATUS_RESIGNED => "resigned",
//	STATUS_PROMOTION => "promotion"
  );

 $ARRAY_EMPLOYEE_STATUS_SYMBOL = array(
    STATUS_CONTRACT_1 => "cI",
    STATUS_CONTRACT_2 => "cII",
    STATUS_PERMANENT => "p",
    STATUS_OUTSOURCE => "os",
    STATUS_FREELANCE => "fl");



  $arrEmpty = array("value" => "", "text" => "", "selected" => true);

  $strEmptyOption = "<option value=''>&nbsp; </option>\n";

  //--DAFTAR ARRAY UNTUK TIPE
  $ARRAY_INSTRUCTOR_TYPE = array("","external","internal","");

  $ARRAY_FAMILY_RELATION = array("father", "mother", "wife", "husband", "child", "relative", "other");
  $ARRAY_LEAVE_TYPE = array("annual", "maternity", "other");
  $ARRAY_ACCESS_RIGHT = array("private", "public", "group");
  $ARRAY_HOLIDAY_TYPE = array("national", "company", "special");
  $ARRAY_SALARY_BASE = array("basic salary", "fixed salary");
  $ARRAY_SALARY_CALCULATION = array("start","basic and fix allowance","attendance allowance", "overtime", "deduction", "result","finish"); // proses hitung gaji
  define("SALARY_CALCULATION_FINISH",6);
  define("SALARY_CALCULATION_APPROVED",7);

  $ARRAY_DESTINATION_TYPE = array(0 => "domestic", 1 => "international");
  $ARRAY_TRIP_TYPE = array(0 => "tidak menginap", 1 => "menginap");

  $ARRAY_REQUEST_STATUS = array(  0=>"new", 1=>"checked", 2=>"approved", -1 => "denied", 6=>"approved 2"); // daftar status permohonan
  define("REQUEST_STATUS_DENIED", -1);
  define("REQUEST_STATUS_NEW", 0);
  define("REQUEST_STATUS_CHECKED", 1);
  define("REQUEST_STATUS_APPROVED", 2);
  define("REQUEST_STATUS_ACKNOWLEDGED", 3);
  define("REQUEST_STATUS_CLOSED", 4);
  define("REQUEST_STATUS_APPROVED_2", 6);

  define("MAX_LEAVE", 24);

  $ARRAY_CANDIDATE_STATUS = array("new", "invited", "on process", "accepted", "denied", "cancel"); //status pelamar/kandidat
  $ARRAY_RECRUITMENT_RESULT = array("", "accepted", "cancel", "denied", "considered"); // hasil recruitment
  $ARRAY_TRAINING_PARTICIPATION = array("", "accepted", "cancel"); // keikutsertaan training

  define("MEDICAL_TYPE_OUTPATIENT", 0);
  define("MEDICAL_TYPE_INPATIENT", 1);
  define("MEDICAL_TYPE_TEETH", 2);
  define("MEDICAL_TYPE_GLASSES", 3);
  define("MEDICAL_TYPE_PROTESA", 4);
  define("MEDICAL_TYPE_OTHER", 5);


 //	$ARRAY_MEDICAL_TREATMENT_GROUP = array(0 =>"outpatients","inpatient", "other"); //jenis perawtan medis
  $ARRAY_MEDICAL_TREATMENT_GROUP = array(0 => "outpatients","inpatient","teeth","glasses","protesa","other"); //jenis perawatan medis

  // ARRAY UNTUK GA
  $ARRAY_PO_TYPE = array("purchase", "rental", "service", "contract"); // jenis PO/SPK
  $ARRAY_PAYMENT_STATUS = array("unpaid", "paid", "cancel");
  $ARRAY_PAYMENT_TYPE = array("kas bon", "fpp", "fpk", "pum");



  define("INT_LIMIT_APPROVAL", "30");
  define("LATE_TOLERANCE", 0); // toleransi keterlambatan (dalam menit)

  $ARRAY_SCHEDULE_TABLENAME = array("Employee" => "employee_id", "SubSection" => "sub_section_code", "Section" => "section_code", "Department" => "department_code", "Division" => "division_code");
  $ARRAY_SCHEDULE_LEVEL = array(0 => "Employee", 1 => "SubSection", 2 => "Section", 3 => "Department", 4 => "Division");

  $strMainTemplate  = "../templates/master.html";

  define ("OTMA", 8500);
  define ("SENIORITY_ALLOWANCE_MARRIED_OPERATOR", 50000);

  define("POSITION_EXECUTIVE", 0);
  define("POSITION_MANAGERIAL", 1);
  define("POSITION_EMPLOYEE", 2);
  define("POSITION_NONSTAFF", 3);

  $ARRAY_POSITION_GROUP = array (
    POSITION_EXECUTIVE => "executive",
    POSITION_MANAGERIAL => "managerial",
    POSITION_EMPLOYEE => "employee",
	POSITION_NONSTAFF => "non staff");
  $ARRAY_GET_OT = array (0 => "none", 1 => "full", 2 => "half");
  $ARRAY_EMPLOYEE_ACTIVE = array (0 => "not active", 1 => "active");

  $ARR_DATA_MARITAL_STATUS_CANDIDATE = array(0 => "single", "married", "widow/widower", "divorce");

   // point 4
  $arrHouseOwnership = array();
  $arrHouseOwnership[0] = array("value" => 0, "text" => "my own", "checked" => false);
  $arrHouseOwnership[1] = array("value" => 1, "text" => "belong to parent", "checked" => false);
  $arrHouseOwnership[2] = array("value" => 2, "text" => "rent", "checked" => false);
  $arrHouseOwnership[3] = array("value" => 3, "text" => "Kost", "checked" => false);
  $arrHouseOwnership[4] = array("value" => 4, "text" => "others", "checked" => false);

  $strDefaultWidthPx = 200;
  $strDateWidth = 100;
  $intDefaultWidth = 30;

  define("OUTOFFICE_ABSENT", 0);
  define("OUTOFFICE_LEAVE", 1);
  define("OUTOFFICE_TRAINING", 2);
  define("OUTOFFICE_TRIP", 3);

  define("MUTATION_STATUS_APPROVED", 2);

  define("EDUCATION_HIGHSCHOOL", 0);
  define("EDUCATION_DIPLOMA", 1);
  define("EDUCATION_SARJANA", 2);
  define("EDUCATION_ELEMENTARY", 3);
  define("EDUCATION_JUNIORHIGHSCHOOL", 4);

  $ARRAY_EDUCATION_GROUP = array (
    EDUCATION_HIGHSCHOOL => "Senior High School",
    EDUCATION_DIPLOMA => "Diploma",
    EDUCATION_SARJANA => "Sarjana",
    EDUCATION_ELEMENTARY => "Elementary School",
    EDUCATION_JUNIORHIGHSCHOOL => "Junior High School"
  );

  $ARRAY_SORT_BANK = array("BII","BOT","CTB","BCA","RESONA","CASH_JPN","NIAGA","CASH_IND");

  define("CURRENCY_IDR", 0);
  define("CURRENCY_USD", 1);
  define("CURRENCY_JPY", 2);
  define("CURRENCY_AUD", 3);

  $ARRAY_CURRENCY = array (
    CURRENCY_IDR => "IDR (Rp.)",
    CURRENCY_USD => "USD ($)",
    CURRENCY_JPY => "JPY (¥)",
    CURRENCY_AUD => "AUD ($)"
  );

  $ARRAY_HOLIDAY_TYPE = array("national", "company", "special");

  define("NATIONALITY_INDONESIA", 0);
  define("NATIONALITY_NONINDONESIA", 1);

  $ARRAY_NATIONALITY = array (
    NATIONALITY_INDONESIA => "Indonesian (WNI)",
    NATIONALITY_NONINDONESIA => "Non-Indonesian (WNA)"
  );

  $ARRAY_YEAR = array ();
 date_default_timezone_set('Asia/Jakarta');
  $TODAY_YEAR = date('Y');

  for ($i = 0; $i < 50; $i++)
  {
      $ARRAY_YEAR[$TODAY_YEAR - $i] = $TODAY_YEAR - $i;
  }

  $ARRAY_MONTH = array (
    1 => "January",
    2 => "February",
    3 => "March",
    4 => "April",
    5 => "May",
    6 => "June",
    7 => "July",
    8 => "August",
    9 => "September",
    10 => "October",
    11 => "November",
    12 => "December"
  );

  define("RESIGN_REASON_A", 0);
  define("RESIGN_REASON_B", 1);
  define("RESIGN_REASON_C", 2);
  define("RESIGN_REASON_D", 3);

  $ARRAY_RESIGN_REASON = array (
    RESIGN_REASON_A => "a",
    RESIGN_REASON_B => "b",
    RESIGN_REASON_C => "c",
    RESIGN_REASON_D => "d"
  );

  define("CLOSE", 0);
  define("OPEN", 1);

  $ARRAY_EC_STATUS = array (
    CLOSE => "Close",
    OPEN => "Open"
  );

  define("LOAN_HRD", 0);
  define("LOAN_TOOLS", 1);
  define("LOAN_FINANCE", 2);

  $ARRAY_LOAN_CATEGORY = array (
    LOAN_HRD => "HRD",
    LOAN_TOOLS => "Tools",
    LOAN_FINANCE => "Finance"
  );

?>
