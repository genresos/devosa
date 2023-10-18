<?
  /*
   Generate data untuk diolah oleh javascript
   Author: Yudi K
   Versi 1:
   Update: 2005-02-03
  */
  session_start();
  include_once("global.php");

  // array untuk daftar conversi character
  $strL = "abcdefghijklmnopqrstuvwxyz"; // daftar karakter lower case
  $strU = "ABCDEFGHIJKLMNOPQRSTUVWXYZ"; // daftar karakter upper case

  // generate array daftar konversi
  for ($i =0; $i < 26; $i++) {
    if ($i < 13) {
      $arrEncoder[$strL[$i]] = $strL[$i + 13];
      $arrEncoder[$strU[$i]] = $strU[$i + 13];
    } else {
      $arrEncoder[$strL[$i]] = $strL[$i - 13];
      $arrEncoder[$strU[$i]] = $strU[$i - 13];
    }
  }

  $db = new CdbClass;
  if ($db->connect()) {
    $strAllCanons = "";
    $strNickTokens = "";
    $i = 0;

    if (isset($_SESSION['sessionUserID'])) {
        $strUserID = $_SESSION['sessionUserID'];
      if ($strUserID == "") return 0;
    } else {
      return 0;
    }

    $arrUserInfo = array();
		getUserEmployeeInfo();
    $strDataUserRole = $_SESSION['sessionUserRole'];
    if ($strDataUserRole == ROLE_SUPERVISOR)
    {
      if ($arrUserInfo['division_code'] != "")    $strDataDivision =    $arrUserInfo['division_code'];
      if ($arrUserInfo['department_code'] != "")  $strDataDepartment =  $arrUserInfo['department_code'];
      if ($arrUserInfo['section_code'] != "")     $strDataSection =     $arrUserInfo['section_code'];
      if ($arrUserInfo['sub_section_code'] != "") $strDataSubSection =  $arrUserInfo['sub_section_code'];
    }
    else if ($strDataUserRole == ROLE_SUPERVISOR_BRANCH)
    {
      if ($arrUserInfo['division_code'] != "")    $strDataDivision =    $arrUserInfo['division_code'];
      if ($arrUserInfo['department_code'] != "")  $strDataDepartment =  $arrUserInfo['department_code'];
      if ($arrUserInfo['section_code'] != "")     $strDataSection =     $arrUserInfo['section_code'];
      if ($arrUserInfo['sub_section_code'] != "") $strDataSubSection =  $arrUserInfo['sub_section_code'];
      if ($arrUserInfo['branch_code'] != "")      $strDataBranch =  $arrUserInfo['branch_code'];
    }
    else if ($strDataUserRole == ROLE_EMPLOYEE)
    {
      $strDataEmployee = $arrUserInfo['employee_id'];
      if ($arrUserInfo['division_code'] != "")    $strDataDivision =    $arrUserInfo['division_code'];
      if ($arrUserInfo['department_code'] != "")  $strDataDepartment =  $arrUserInfo['department_code'];
      if ($arrUserInfo['section_code'] != "")     $strDataSection =     $arrUserInfo['section_code'];
      if ($arrUserInfo['sub_section_code'] != "") $strDataSubSection =  $arrUserInfo['sub_section_code'];
      if ($arrUserInfo['branch_code'] != "")      $strDataBranch =  $arrUserInfo['branch_code'];
    }
    else if ($strDataUserRole == ROLE_BRANCH_ADMIN)
    {
      if ($arrUserInfo['branch_code'] != "")      $strDataBranch =  $arrUserInfo['branch_code'];
    }
    else if ($strDataUserRole == ROLE_DIVISION_BRANCH_ADMIN)
    {
      if ($arrUserInfo['branch_code'] != "")	$strDataBranch =  $arrUserInfo['branch_code'];
      if ($arrUserInfo['division_code'] != "")	$strDataDivision =  $arrUserInfo['division_code'];
    }
  $strKriteria="";
  /*
    if ($arrData['dataBranch']!= "") {
      $strKriteria .= "AND branch_code = '".$arrData['dataBranch']."' ";
    }
    */
    if ($strDataDivision!= "") {
      $strKriteria .= "AND division_code = '".$strDataDivision."' ";
    }
    if ($strDataDepartment!= "") {
      $strKriteria .= "AND department_code = '".$strDataDepartment."' ";
    }
    if ($strDataSection!= "") {
      $strKriteria .= "AND section_code = '".$strDataSection."' ";
    }
    if ($strDataSubSection!= "") {
      $strKriteria .= "AND sub_section_code = '".$strDataSubSection."' ";
    }
    if ($strDataBranch!= "") {
      $strKriteria .= "AND branch_code = '".$strDataBranch."' ";
    }
		if ($strDataEmployee != ""){
			$strKriteria .= "AND employee_id = '".$strDataEmployee."' ";
		}
    $strSQL = "SELECT id, employee_id, employee_name FROM hrd_employee WHERE 1=1 $strKriteria $strKriteriaCompany $strKriteriaOrganizational ORDER BY employee_id ";
    
    $resDb = $db->execute($strSQL);
    while ($rowDb = $db->fetchrow($resDb)) {
      $strAllCanons .= "\"" .$rowDb["employee_id"]. "\", ";
      $strAllCanons .= "\"" .$rowDb["employee_name"]. "\", ";
      $strNickTokens .= "[\"" .$rowDb["employee_id"]. ",".($i * 2)."\"], ";
      $i++;
    }
    $strAllCanons = "[" .$strAllCanons. "];\n";
    $strNickTokens = "[" .$strNickTokens. "];\n";

  }
  /*
  $strNickTokens = nl2br($strNickTokens);
  $strAllCanons  = nl2br($strAllCanons);
  */

  echo "var AC_listStr = \"List\";\n";
  echo "var AC_nickNameStr = \"Kode\";\n";
  echo "var AC_nickTokens = $strNickTokens ";
  echo "var AC_allCanons = $strAllCanons";

?>
