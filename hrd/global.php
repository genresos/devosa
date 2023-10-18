<?php

  /*
    Daftar variabel dan fungsi global
      Author: Yudi K.
  */
  include("../global.php");
  include_once("../global/common_variable.php");
  include_once("../global/common_data.php");
  include_once("../global/common_function.php");
  include_once("../global/date_function.php");
  include_once("../global/approval_function.php");
  include_once("../global/form_function.php");
  include_once("../global/words.php");

  $strPrintCss = "../css/bw.css"; // file CSS untuk print
  $strPrintInit = "window.print();";
  $strCopyright = COPYRIGHT;
  $strCompanyName = getSetting("company_name");
  $strWordsINPUTDATA = getWords("input data");
  $strWordsLISTOF = getWords("list of");
  $strWordsFILTERDATA = getWords("filter data");
  $strConfirmSave = $messages['confirm_save'];
  $strConfirmApprove = $messages['confirm_approve'];
  $strConfirmDelete = $messages['confirm_delete'];
  $strConfirmChangeStatus = $messages['confirm_change_status'];

  if (isset($_REQUEST['dataCompany'])) $intCAR = $_REQUEST['dataCompany'];
  else if (isset($_REQUEST['filterCompany'])) $intCAR = $_REQUEST['filterCompany'];
  else if (isset($_SESSION['sessionCAR'])) $intCAR = $_SESSION['sessionCAR'];
  else $intCAR = -1;
  $bolCAR = ($intCAR == -1 || $intCAR == "");
  if (!$bolCAR)
  { 
     $strKriteriaCompany = " AND id_company = '$intCAR' ";
     $strDataCompany     = $intCAR ;
     $strFilterCompany   = $intCAR ;
     if (isset($_SESSION['sessionCAR']) && $_SESSION['sessionCAR'] != -1 && $_SESSION['sessionCAR'] != "" ) 
     {
         $strKriteria2       = "WHERE id = ".$_SESSION['sessionCAR']." ";
         $strEmptyOption2    = "";
         $bolCompanyEmptyOption = false;
         $arrCompanyEmptyData = null;
     }
     else
     {
         $strKriteria2       = "";
         $strEmptyOption2    = $strEmptyOption;
         $bolCompanyEmptyOption = true;
         $arrCompanyEmptyData = array("value" => "", "text" => "", "selected" => true);
     }
  }
  else
  {
     $strKriteriaCompany = "";
     $strDataCompany     = "";
     $strFilterCompany   = "";
     $strKriteria2       = "";
     $strEmptyOption2    = $strEmptyOption;
     $bolCompanyEmptyOption = true;
     $arrCompanyEmptyData = array("value" => "", "text" => "", "selected" => true);
  }

  $strKriteriaOrganizational = "";
  $strKriteriaOrganizational .= ( !isset($_SESSION['sessionUserDivision']) || $_SESSION['sessionUserDivision'] == "" ) ? "" : "AND division_code = '".$_SESSION['sessionUserDivision']."' ";
  $strKriteriaOrganizational .= (!isset($_SESSION['sessionUserDepartment']) || $_SESSION['sessionUserDepartment'] == "" ) ? "" : "AND department_code  = '".$_SESSION['sessionUserDepartment']."' ";
  $strKriteriaOrganizational .= (!isset($_SESSION['sessionUserSection']) || $_SESSION['sessionUserSection'] == "" ) ? "" : "AND section_code = '".$_SESSION['sessionUserSection']."' ";
  $strKriteriaOrganizational .= (!isset($_SESSION['sessionUserSubsection']) || $_SESSION['sessionUserSubsection'] == ""  ) ? "" : "AND sub_section_code = '".$_SESSION['sessionUserSubsection']."' ";


  $intPageLimit = 10; // jumlah link page maksimal yang ditampilkan
  $intRowsLimit = 50; // jumlah baris yang ditampilkan satu page

?>
