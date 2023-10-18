<?php
  include_once(dirname(__FILE__).'/../includes/model/model.php');

  function getDataListGroup($default = null, $isHasEmpty = false, $emptyData = null)
  {
    $tbl = new cModel("adm_group", getWords("group"));
    $arrData = $tbl->generateList("active = 1", "id_adm_group", null, "id_adm_group", array("code", "name"), $isHasEmpty, $emptyData);
    if ($default != null || $default != "")
      while(list($key, $val) = each($arrData))
      {
        $temp = &$arrData[$key];
        if ($val['value'] == $default)
          $temp['selected'] = true;
        else
          $temp['selected'] = false;
      }
    return $arrData;
  }

  function getDataListReligion($default = null, $isHasEmpty = false, $emptyData = null)
  {
    $tbl = new cModel("hrd_religion", getWords("religion"));
    $arrData = $tbl->generateList(null, null, null, "code", "name", $isHasEmpty, $emptyData);
    if ($default != null || $default != "")
      while(list($key, $val) = each($arrData))
      {
        $temp = &$arrData[$key];
        if ($val['value'] == $default)
          $temp['selected'] = true;
        else
          $temp['selected'] = false;
      }
    return $arrData;
  }

  function getDataListMajor($default = null, $isHasEmpty = false, $emptyData = null)
  {
    $tbl = new cModel("hrd_major", getWords("major"));
    $arrData = $tbl->generateList(null, null, null, "code", "name", $isHasEmpty, $emptyData);
    if ($default != null || $default != "")
      while(list($key, $val) = each($arrData))
      {
        $temp = &$arrData[$key];
        if ($val['value'] == $default)
          $temp['selected'] = true;
        else
          $temp['selected'] = false;
      }
    return $arrData;
  }

  function getDataListEducation($default = null, $isHasEmpty = false, $emptyData = null)
  {
    $tbl = new cModel("hrd_education_level", getWords("education"));
    $arrData = $tbl->generateList(null, null, null, "code", "name", $isHasEmpty, $emptyData);
    if ($default != null || $default != "")
      while(list($key, $val) = each($arrData))
      {
        $temp = &$arrData[$key];
        if ($val['value'] == $default)
          $temp['selected'] = true;
        else
          $temp['selected'] = false;
      }
    return $arrData;
  }
  /*
  function getDataListPosition($default = null, $isHasEmpty = false, $emptyData = null)
  {
    $tbl = new cModel("hrd_position", getWords("position_aplied"));
    $arrData = $tbl->generateList(null, null, null, "position_code",$isHasEmpty, $emptyData);
    if ($default != null || $default != "")
      while(list($key, $val) = each($arrData))
      {
        $temp = &$arrData[$key];
        if ($val['value'] == $default)
          $temp['selected'] = true;
        else
          $temp['selected'] = false;
      }
    return $arrData;
  }*/


  function getDataListEducationLevel($default = null, $isHasEmpty = false, $emptyData = null)
  {
    $tbl = new cModel("hrd_education_level", getWords("education level"));
    $arrData = $tbl->generateList(null, null, null, "code", "name", $isHasEmpty, $emptyData);
    if ($default != null || $default != "")
      while(list($key, $val) = each($arrData))
      {
        $temp = &$arrData[$key];
        if ($val['value'] == $default)
          $temp['selected'] = true;
        else
          $temp['selected'] = false;
      }
    return $arrData;
  }

  function getDataListFamilyStatus($default = null, $isHasEmpty = false, $emptyData = null)
  {
    $tbl = new cModel("hrd_family_status", getWords("family status"));
    $arrData = $tbl->generateList(null, null, null, "family_status_code", "note", $isHasEmpty, $emptyData);
    if ($default != null || $default != "")
      while(list($key, $val) = each($arrData))
      {
        $temp = &$arrData[$key];
        if ($val['value'] == $default)
          $temp['selected'] = true;
        else
          $temp['selected'] = false;
      }
    return $arrData;
  }
  function getDataListLivingCost($default = null, $isHasEmpty = false, $emptyData = null)
  {
    $tbl = new cModel("hrd_minimum_living_cost", getWords("minimum living cost"));
    $arrData = $tbl->generateList(null, null, null, "code", "note", $isHasEmpty, $emptyData);
    if ($default != null || $default != "")
      while(list($key, $val) = each($arrData))
      {
        $temp = &$arrData[$key];
        if ($val['value'] == $default)
          $temp['selected'] = true;
        else
          $temp['selected'] = false;
      }
    return $arrData;
  }


  function getDataListOrganizationDetailByIdOrganization($idOrganization, $default = null, $isHasEmpty = false, $emptyData = null)
  {
    $tbl = new cModel("hrd_organization_detail", getWords("organization"));
    $arrData = $tbl->generateList(/*WHERE */"id_hrd_organization = ".intval($idOrganization), null, null, "id", array("code", "name"), $isHasEmpty, $emptyData);
    if ($default != null || $default != "")
      while(list($key, $val) = each($arrData))
      {
        $temp = &$arrData[$key];
        if ($val['value'] == $default)
          $temp['selected'] = true;
        else
          $temp['selected'] = false;
      }
    return $arrData;
  }

  /*parameter $idParentOrganizationDetail : can be array or single string*/
  function getDataListOrganizationDetailByIdParent($idParentOrganizationDetail, $default = null, $isHasEmpty = false, $emptyData = null)
  {
    $tbl = new cModel("hrd_organization_detail", getWords("organization"));
    if (is_array($idParentOrganizationDetail))
    {
      if (count($idParentOrganizationDetail) > 0)
        $arrData = $tbl->generateList(/*WHERE */"id_hrd_organization_detail IN (".implode(", ", $idParentOrganizationDetail).")", null, null, "id", array("code", "name"), $isHasEmpty, $emptyData);
      else
        $arrData = array();
    }
    else
    {
      $arrData = $tbl->generateList(/*WHERE */"id_hrd_organization_detail = ".intval($idParentOrganizationDetail), null, null, "id", array("code", "name"), $isHasEmpty, $emptyData);
    }
    if ($default != null || $default != "")
      while(list($key, $val) = each($arrData))
      {
        $temp = &$arrData[$key];
        if ($val['value'] == $default)
          $temp['selected'] = true;
        else
          $temp['selected'] = false;
      }
    return $arrData;
  }

  function getDataListShiftType($default = null, $isHasEmpty = false, $emptyData = null)
  {
    $tbl = new cModel("hrd_shift_type", getWords("shift type"));
    $arrData = $tbl->generateList(null, null, null, "code", "code", $isHasEmpty, $emptyData);
    if ($default != null || $default != "")
      while(list($key, $val) = each($arrData))
      {
        $temp = &$arrData[$key];
        if ($val['value'] == $default)
          $temp['selected'] = true;
        else
          $temp['selected'] = false;
      }
    return $arrData;
  }

  function getDataListModule($default = null, $isHasEmpty = false, $emptyData = null)
  {
    $tbl = new cModel("adm_module", getWords("module"));
    $arrData = $tbl->generateList("visible = 1", "id_adm_module", null, "id_adm_module", "name", $isHasEmpty, $emptyData);
    if ($default != null || $default != "")
      while(list($key, $val) = each($arrData))
      {
        $temp = &$arrData[$key];
        if ($val['value'] == $default)
          $temp['selected'] = true;
        else
          $temp['selected'] = false;
      }
    return $arrData;
  }


  function getDataListMonth($default = null, $isHasEmpty = false, $emptyData = null)
  {
    $arrMonth = array(1 => "Jan", "Feb", "Mar", "Apr", "May", "Jun",
                           "Jul", "Aug", "Sep", "Oct", "Nov", "Dec");
    $arrData = array();
    if ($isHasEmpty)
      $arrData[] = $emptyData;
    foreach($arrMonth as $key => $val)
    {
      if ($default == $key)
        $arrData[] = array("value" => $key, "text" => $val, "selected" => true);
      else
        $arrData[] = array("value" => $key, "text" => $val, "selected" => false);
    }
    return $arrData;
  }

  function getDataListYear($default = null, $isHasEmpty = false, $emptyData = null, $limit = 10, $isAsc = false)
  {
    $arrData = array();
    if ($isHasEmpty)
      $arrData[] = $emptyData;

    $currentYear = intval(date("Y")) ;
    if ($isAsc)
      $currentYear -= ($limit/2);
    else
      $currentYear += ($limit/2);


    for ($i = 1-$limit; $i <= 0; $i++)
    {
      if ($currentYear == $default)
        $arrData[] = array("value" => $currentYear, "text" => $currentYear, "selected" => true);
      else
        $arrData[] = array("value" => $currentYear, "text" => $currentYear, "selected" => false);
      if ($isAsc) $currentYear++;
      else $currentYear--;
    }
    for ($i = 1; $i <= $limit; $i++)
    {
      if ($currentYear == $default)
        $arrData[] = array("value" => $currentYear, "text" => $currentYear, "selected" => true);
      else
        $arrData[] = array("value" => $currentYear, "text" => $currentYear, "selected" => false);
      if ($isAsc) $currentYear++;
      else $currentYear--;
    }
    return $arrData;
  }

  function getDataListShiftGroup($default = null, $isHasEmpty = false, $emptyData = null)
  {
    $tbl = new cModel("hrd_shift_group", getWords("shift group"));
    $arrData = $tbl->generateList(null, "name", null, "id", "name", $isHasEmpty, $emptyData);
    if ($default != null || $default != "")
      while(list($key, $val) = each($arrData))
      {
        $temp = &$arrData[$key];
        if ($val['value'] == $default)
          $temp['selected'] = true;
        else
          $temp['selected'] = false;
      }
    return $arrData;
  }

  function getDataListHolidayType($default = null, $isHasEmpty = false, $emptyData = null)
  {
    global $ARRAY_HOLIDAY_TYPE;

    $arrData = array();
    if ($isHasEmpty)
      $arrData[] = $emptyData;

    foreach($ARRAY_HOLIDAY_TYPE as $key => $val)
    {
      if ($default == $key)
        $arrData[] = array("value" => $key, "text" => getWords($ARRAY_HOLIDAY_TYPE[$key]), "selected" => true);
      else
        $arrData[] = array("value" => $key, "text" => getWords($ARRAY_HOLIDAY_TYPE[$key]), "selected" => false);
    }
    return $arrData;
  }//getHolidayType

function getDataListNationality($default = null, $isHasEmpty = false, $emptyData = null)
  {
    global $ARRAY_NATIONALITY;

    $arrData = array();
    if ($isHasEmpty)
      $arrData[] = $emptyData;

    foreach($ARRAY_NATIONALITY as $key => $val)
    {
      if ($default == $key)
        $arrData[] = array("value" => $key, "text" => getWords($ARRAY_NATIONALITY[$key]), "selected" => true);
      else
        $arrData[] = array("value" => $key, "text" => getWords($ARRAY_NATIONALITY[$key]), "selected" => false);
    }
    return $arrData;
  }//getHolidayType

  // fungsi untuk mencari info apakah libur nasional atau tidak untuk tanggal tertentu
  // input: db, tanggal : YYYY-MM-DD (dianggap sudah valid)
  //TODO: tambahkan validasi untuk mengecek parameter
  function isCompanyHoliday($strDate)
  {
    //find day of week
    $arrDate = explode("-", $strDate);
    $intTimeStamp = mktime(10, 0, 0, intval($arrDate[1]), intval($arrDate[2]), intval($arrDate[0]));
    $dow = intval(date("w", $intTimeStamp)); //$dow = 0, sunday, 6 = saturday
    if (!isset($GLOBALS['isSundayHoliday'])) $GLOBALS['isSundayHoliday'] = isSundayHoliday();
    if (!isset($GLOBALS['isSaturdayHoliday'])) $GLOBALS['isSaturdayHoliday'] = isSaturdayHoliday();
    if ($dow == 0 && $GLOBALS['isSundayHoliday']) return true;
    if ($dow == 6 && $GLOBALS['isSaturdayHoliday']) return true;

    $tblCalendar = new cModel("hrd_calendar", "calendar");
    if ($tblCalendar->findCount("CONVERT(varchar(10), holiday, 120) = '$strDate' AND status = 1") > 0)
      return true;

    return false;
  }

  function getDefaultStartTime()
  {
    return getSetting("start_time");
  }

  function getDefaultFinishTime()
  {
    return getSetting("finish_time");
  }

  function isSaturdayHoliday()
  {
    return getSetting("saturday") == 't';
  }

  function isSundayHoliday()
  {
    return getSetting("sunday") == 't';
  }

  function getDataListAbsenceType($default = null, $isHasEmpty = false, $emptyData = null)
  {
    $tbl = new cModel("hrd_absence_type", getWords("absence type"));
    $arrData = $tbl->generateList(null, "code", null, "code", array("code", "note"), $isHasEmpty, $emptyData);
    if ($default != null || $default != "")
      while(list($key, $val) = each($arrData))
      {
        $temp = &$arrData[$key];
        if ($val['value'] == $default)
          $temp['selected'] = true;
        else
          $temp['selected'] = false;
      }
    return $arrData;
  }

  function getDataHolidayByRange($strFrom, $strThru)
  {
    //cek holiday
    $arrHoliday = array();
    $tblCalendar = new cModel("hrd_calendar");
    $strCriteria = "CONVERT(varchar(10), holiday, 120) >= '$strFrom' AND CONVERT(varchar(10), holiday, 120) <= '$strThru' ";
    $strCriteria .= " AND status=1";
    $arrHoliday = $tblCalendar->findAll($strCriteria, "CONVERT(VARCHAR(10), holiday, 120) AS holiday_date", null, null, null, "holiday_date");
    return $arrHoliday;
  }

  function getDataListGender($default = null, $isHasEmpty = false, $emptyData = null)
  {
    global $ARRAY_GENDER;
    $arrData = array();
    if ($isHasEmpty) $arrData[] = $emptyData;

    foreach($ARRAY_GENDER as $key => $value)
      if ($key == $default)
        $arrData[] = array("value" => $key, "text" => getWords($value), "selected" => true);
      else
        $arrData[] = array("value" => $key, "text" => getWords($value), "selected" => false);
    return $arrData;
  }

  function getDataListBloodType($default = null, $isHasEmpty = false, $emptyData = null)
  {

    global $ARRAY_BLOOD_TYPE;
    $arrData = array();
    if ($isHasEmpty) $arrData[] = $emptyData;
    foreach($ARRAY_BLOOD_TYPE as $key => $value)
      if ($key == $default)
        $arrData[] = array("value" => $key, "text" => $value, "selected" => true);
      else
        $arrData[] = array("value" => $key, "text" => $value, "selected" => false);

    return $arrData;
  }


  function getDataListMaritalStatus($default = null, $isHasEmpty = false, $emptyData = null)
  {
    global $ARRAY_MARITAL_STATUS;
    $arrData = array();
    if ($isHasEmpty) $arrData[] = $emptyData;
    foreach($ARRAY_MARITAL_STATUS as $key => $value)
      if ($key == $default)
        $arrData[] = array("value" => $key, "text" => getWords($value), "selected" => true);
      else
        $arrData[] = array("value" => $key, "text" => getWords($value), "selected" => false);
    echo var_dump();
    return $arrData;
  }

  function getDataListEmployeeStatus($default = null, $isHasEmpty = false, $emptyData = null)
  {
    global $ARRAY_EMPLOYEE_STATUS;
    $arrData = array();
    if ($default != null) $emptyData['selected'] = false;
    if ($isHasEmpty) $arrData[] = $emptyData;
		foreach($ARRAY_EMPLOYEE_STATUS as $key => $value)
      if ($key == $default && !($emptyData['selected']))
        $arrData[] = array("value" => $key, "text" => getWords($value), "selected" => true);
      else
        $arrData[] = array("value" => $key, "text" => getWords($value), "selected" => false);
    return $arrData;
  }

  function getDataListPartialAbsenceType($default = null, $isHasEmpty = false, $emptyData = null)
  {
    global $ARRAY_PARTIAL_ABSENCE_TYPE;
    $arrData = array();
    if ($isHasEmpty) $arrData[] = $emptyData;
    foreach($ARRAY_PARTIAL_ABSENCE_TYPE as $key => $value)
      if ($key == $default)
        $arrData[] = array("value" => $key, "text" => getWords($value), "selected" => true);
      else
        $arrData[] = array("value" => $key, "text" => getWords($value), "selected" => false);
    return $arrData;
  }

  function getDataListEmployeeActive($default = null, $isHasEmpty = false, $emptyData = null)
  {
    global $ARRAY_EMPLOYEE_ACTIVE;
    $arrData = array();
    if ($isHasEmpty) $arrData[] = $emptyData;
    foreach($ARRAY_EMPLOYEE_ACTIVE as $key => $value)
      if ($key === $default)
        $arrData[] = array("value" => $key, "text" => getWords($value), "selected" => true);
      else
        $arrData[] = array("value" => $key, "text" => getWords($value), "selected" => false);
    return $arrData;
  }
  function getDataListRequestStatus($default = null, $isHasEmpty = false, $emptyData = null)
  {

    global $ARRAY_REQUEST_STATUS;
    $arrData = array();
    if ($default != null) $emptyData['selected'] = false;
    if ($isHasEmpty) $arrData[] = $emptyData;
    foreach($ARRAY_REQUEST_STATUS as $key => $value)
      if ($key == $default && !($emptyData['selected']))
        $arrData[] = array("value" => $key, "text" => getWords($value), "selected" => true);
      else
        $arrData[] = array("value" => $key, "text" => getWords($value), "selected" => false);
    return $arrData;
  }

  // added for OTMA function [bm]
  function getDataListCompany($default = null, $isHasEmpty = false, $emptyData = null, $criteria = null)
  {
    $tbl = new cModel("hrd_company", getWords("Company"));
    $arrData = $tbl->generateList($criteria, "id", null, "id", "company_name", $isHasEmpty, $emptyData);
    if ($default != null || $default != "")
      while(list($key, $val) = each($arrData))
      {
        $temp = &$arrData[$key];
        if ($val['value'] == $default)
          $temp['selected'] = true;
        else
          $temp['selected'] = false;
      }
    return $arrData;
    /*
    global $ARRAY_COMPANY;
    $arrData = array();
    if ($isHasEmpty) $arrData[] = $emptyData;
    foreach($ARRAY_COMPANY as $key => $value)
      if ($key == $default)
        $arrData[] = array("value" => $key, "text" => $value, "selected" => true);
      else
        $arrData[] = array("value" => $key, "text" => $value, "selected" => false);
    return $arrData;*/
  }


  function getDataListManagement($default = null, $isHasEmpty = false, $emptyData = null, $criteria = "")
  {
    global $strDataCompany;
    $criteria .= "AND management_code LIKE '%". printCompanyCode($strDataCompany)."%'";

    $tbl = new cModel("hrd_management", getWords("Management"));
    $arrData = $tbl->generateList("1=1 ". $criteria , "id", null, "management_code", array("management_code", "management_name"), $isHasEmpty, $emptyData);
    if ($default != null || $default != "")
      while(list($key, $val) = each($arrData))
      {
        $temp = &$arrData[$key];
        if ($val['value'] == $default)
          $temp['selected'] = true;
        else
          $temp['selected'] = false;
      }
    return $arrData;
  }//getDataListManagement
	
	function getDataListManagement2($default = null, $isHasEmpty = false, $emptyData = null, $criteria = "")
  {
    
    $tbl = new cModel("hrd_management", getWords("Management"));
    $arrData = $tbl->generateList("1=1 " , "id", null, "management_code", array("management_code", "management_name"), $isHasEmpty, $emptyData);
    if ($default != null || $default != "")
      while(list($key, $val) = each($arrData))
      {
        $temp = &$arrData[$key];
        if ($val['value'] == $default)
          $temp['selected'] = true;
        else
          $temp['selected'] = false;
      }
    return $arrData;
  }//getDataListManagement
	
  function getDataListDivision($default = null, $isHasEmpty = false, $emptyData = null, $criteria = "")
  {
    global $strDataCompany;
    $criteria .= (($strDataCompany == "") ? "" : "AND management_code LIKE '%". printCompanyCode($strDataCompany)."%'");
    $tbl = new cModel("hrd_division", getWords("division"));
    $arrData = $tbl->generateList("1=1 ". $criteria, "id", null, "division_code", array("division_code", "division_name"), $isHasEmpty, $emptyData);
    if ($default != null || $default != "")

      while(list($key, $val) = each($arrData))
      {
        $temp = &$arrData[$key];
        if ($val['value'] == $default)
          $temp['selected'] = true;
        else
          $temp['selected'] = false;
      }
    return $arrData;
  }//getDataListDivision

  function getDataListDepartment($default = null, $isHasEmpty = false, $emptyData = null, $criteria = "")
  {
    global $strDataCompany;
    $criteria .= (($strDataCompany == "") ? "" : "AND management_code LIKE '%". printCompanyCode($strDataCompany)."%'");
    $tbl = new cModel("hrd_department", getWords("Departemen"));
    $arrData = $tbl->generateList("1=1 ". $criteria, "id", null, "department_code", array("department_code", "department_name"), $isHasEmpty, $emptyData);
    if ($default != null || $default != "")
      while(list($key, $val) = each($arrData))
      {
        $temp = &$arrData[$key];
        if ($val['value'] == $default)
          $temp['selected'] = true;
        else
          $temp['selected'] = false;
      }
    return $arrData;
  }//getDataListDepartment

  function getDataListSection($default = null, $isHasEmpty = false, $emptyData = null, $criteria = "")
  {
    global $strDataCompany;
    $criteria .= (($strDataCompany == "") ? "" : "AND management_code LIKE '%". printCompanyCode($strDataCompany)."%'");
    $tbl = new cModel("hrd_section", getWords("section"));
    $arrData = $tbl->generateList("1=1 ". $criteria, "id", null, "section_code", array("section_code", "section_name"), $isHasEmpty, $emptyData);
    if ($default != null || $default != "")
      while(list($key, $val) = each($arrData))
      {
        $temp = &$arrData[$key];
        if ($val['value'] == $default)
          $temp['selected'] = true;
        else
          $temp['selected'] = false;
      }
    return $arrData;
  }//getDataListSection

  function getDataListSubSection($default = null, $isHasEmpty = false, $emptyData = null, $criteria = "")
  {
    global $strDataCompany;
    $criteria .= (($strDataCompany == "") ? "" : "AND management_code LIKE '%". printCompanyCode($strDataCompany)."%'");
    $tbl = new cModel("hrd_sub_section", getWords("subsection"));
    $arrData = $tbl->generateList("1=1 ". $criteria, "id", null, "sub_section_code", array("sub_section_code", "sub_section_name"), $isHasEmpty, $emptyData);

    if ($default != null || $default != "")
      while(list($key, $val) = each($arrData))
      {
        $temp = &$arrData[$key];
        if ($val['value'] == $default)
          $temp['selected'] = true;
        else
          $temp['selected'] = false;
      }
    return $arrData;
  }//getDataListSubSection

  function getDataListPosition($default = null, $isHasEmpty = false, $emptyData = null)
  {
    $tbl = new cModel("hrd_position", getWords("position"));
    $arrData = $tbl->generateList(null, "position_code", null, "position_code", array("position_code", "position_name", "note"), $isHasEmpty, $emptyData);
    if ($default != null || $default != "")
      while(list($key, $val) = each($arrData))
      {
        $temp = &$arrData[$key];
        if ($val['value'] == $default)
          $temp['selected'] = true;
        else
          $temp['selected'] = false;
      }
    return $arrData;
  }
  function getDataListFunctionalPosition($default = null, $isHasEmpty = false, $emptyData = null)
  {
    $tbl = new cModel("hrd_functional", getWords("functional position"));
    $arrData = $tbl->generateList(null, "functional_code", null, "functional_code", array("functional_code", "functional_name"), $isHasEmpty, $emptyData);
    if ($default != null || $default != "")
      while(list($key, $val) = each($arrData))
      {
        $temp = &$arrData[$key];
        if ($val['value'] == $default)
          $temp['selected'] = true;
        else
          $temp['selected'] = false;
      }
    return $arrData;
  }
  function getDataListSalaryGrade($default = null, $isHasEmpty = false, $emptyData = null)
  {
    $tbl = new cModel("hrd_salary_grade", getWords("salary grade"));
    $arrData = $tbl->generateList(null, "grade_code", null, "grade_code", array("grade_code", "note"), $isHasEmpty, $emptyData);
    if ($default != null || $default != "")
      while(list($key, $val) = each($arrData))
      {
        $temp = &$arrData[$key];
        if ($val['value'] == $default)
          $temp['selected'] = true;
        else
          $temp['selected'] = false;
      }
    return $arrData;
  }
  function getDataEmployee($default = null, $isHasEmpty = false, $emptyData = null)
  {
    global $strKriteriaCompany;
    global $_SESSION;
    global $arrUserInfo;
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
      if ($arrUserInfo['branch_code'] != "")      $strDataBranch =  $arrUserInfo['branch_code'];
      if ($arrUserInfo['division_code'] != "")      $strDataDivision =  $arrUserInfo['division_code'];
    }
  	$strKriteria = "";
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
    $tbl = new cModel("hrd_employee", getWords("employee"));
    $arrData = $tbl->generateList("1=1 ".$strKriteriaCompany." ".$strKriteria, "employee_id", null, "employee_id", array("employee_name"));
    if ($default != null || $default != "")
    {
      while(list($key, $val) = each($arrData))
      {
        $temp = &$arrData[$key];
        if ($val['value'] == $default)
        {
          $temp['selected'] = true;
        }
        else
          $temp['selected'] = false;
      }
    }

    return $arrData;
  }
  function getEvaluationSubheader($default = null, $isHasEmpty = false, $emptyData = null)
  {
    $tbl = new cModel("hrd_evaluation_criteria", getWords("evaluation criteria"));
    $arrData = $tbl->generateList(null, "subheader", null, "subheader", array("subheader"), true, true);
    return $arrData;

  }
  function getDataListEvaluationCategory($default = null, $isHasEmpty = false, $emptyData = null, $criteria = "")
  {
    $tbl = new cModel("hrd_evaluation_category", getWords("evaluation_category"));
    $arrData = $tbl->generateList($criteria, "id", null, "id", array("category"), $isHasEmpty, $emptyData);
    if ($default != null || $default != "")
      while(list($key, $val) = each($arrData))
      {
        $temp = &$arrData[$key];
        if ($val['value'] == $default)
          $temp['selected'] = true;
        else
          $temp['selected'] = false;
      }
    return $arrData;
  }

  function getDataListTrainingCategory($default = null, $isHasEmpty = false, $emptyData = null)
  {
    $tbl = new cModel("hrd_training_category", getWords("training category"));
    $arrData = $tbl->generateList(null, "id", null, "id", array("training_category"), $isHasEmpty, $emptyData);
    if ($default != null || $default != "")
      while(list($key, $val) = each($arrData))
      {
        $temp = &$arrData[$key];
        if ($val['value'] == $default)
          $temp['selected'] = true;
        else
          $temp['selected'] = false;
      }
    return $arrData;
  }

  function getDataListTrainingCategoryType($default = null, $isHasEmpty = false, $emptyData = null)
  {
    $arrData = array();
    if ($isHasEmpty)
      $arrData[] = array("value" => $emptyData, "text" => "");

    $db = new CdbClass;
    if ($db->connect())
    {

      $strSQL = "SELECT t1.id, t1.code, t1.name, t2.id AS id_category, t2.training_category FROM hrd_training_type AS t1 LEFT JOIN hrd_training_category AS t2
                ON t1.id_category = t2.id";
      $resDb = $db->execute($strSQL);
      while ($rowDb = $db->fetchrow($resDb))
      {
        $arrData[] = array("value" => $rowDb['id']."|".$rowDb['id_category'], "text" => $rowDb['training_category']." - " .$rowDb['code']. "(". $rowDb['name'] .")");
      }
      if ($default != null || $default != "")
        while(list($key, $val) = each($arrData))
        {
          $temp = &$arrData[$key];
          if ($val['value'] == $default)
            $temp['selected'] = true;
          else
            $temp['selected'] = false;
        }
    }
    return $arrData;
  }
  function getDataListBranch($default = null, $isHasEmpty = false, $emptyData = null)
  {
    $tbl = new cModel("hrd_branch", getWords("branch"));
    $arrData = $tbl->generateList(null, "branch_code", null, "branch_code", array("branch_code", "branch_name"), $isHasEmpty, $emptyData);
    if ($default != null || $default != "")
      while(list($key, $val) = each($arrData))
      {
        $temp = &$arrData[$key];
        if ($val['value'] == $default)
          $temp['selected'] = true;
        else
          $temp['selected'] = false;
      }
    return $arrData;
  }

  function getDataListBank($default = null, $isHasEmpty = false, $emptyData = null)
  {
    $tbl = new cModel("hrd_bank", getWords("bank"));
    $arrData = $tbl->generateList(null, "bank_code", null, "bank_code", array("bank_code", "bank_name"), $isHasEmpty, $emptyData);
    if ($default != null || $default != "")
      while(list($key, $val) = each($arrData))
      {
        $temp = &$arrData[$key];
        if ($val['value'] == $default)
          $temp['selected'] = true;
        else
          $temp['selected'] = false;
      }
    return $arrData;
  }

  function getDataListTripType($default = null, $isHasEmpty = false, $emptyData = null)
  {
    $tbl = new cModel("hrd_trip_type", getWords("trip type"));
    $arrData = $tbl->generateList(null, "id", null, "id", array("trip_type_code", "trip_type_name"), $isHasEmpty, $emptyData);
    if ($default != null || $default != "")
      while(list($key, $val) = each($arrData))
      {
        $temp = &$arrData[$key];
        if ($val['value'] == $default)
          $temp['selected'] = true;
        else
          $temp['selected'] = false;
      }
    return $arrData;
  }
  function getDataListDestination($default = null, $isHasEmpty = false, $emptyData = null)
  {
    $tbl = new cModel("hrd_destination", getWords("trip type"));
    $arrData = $tbl->generateList(null, "destination_name", null, "destination_name", array("destination_code", "destination_name"), $isHasEmpty, $emptyData);
    if ($default != null || $default != "")
      while(list($key, $val) = each($arrData))
      {
        $temp = &$arrData[$key];
        if ($val['value'] == $default)
          $temp['selected'] = true;
        else
          $temp['selected'] = false;
      }
    return $arrData;
  }
  function getDataListCurrency($default = null, $isHasEmpty = false, $emptyData = null)
  {
    global $ARRAY_CURRENCY;
    $arrData = array();
    if ($isHasEmpty) $arrData[] = $emptyData;

    foreach($ARRAY_CURRENCY as $key => $value)
      if ($key == $default)
        $arrData[] = array("value" => $value, "text" => $value, "selected" => true);
      else
        $arrData[] = array("value" => $value, "text" => $value, "selected" => false);

    return $arrData;
  }
  function getDataListActive($default = null, $isHasEmpty = false, $emptyData = null)
  {
    $arrActive = array('' => '', 't' => 'active' , 'f' => 'not active');
    $arrData = array();
    if ($isHasEmpty) $arrData[] = $emptyData;
    foreach($arrActive as $key => $value)
      if ($key === $default)
        $arrData[] = array("value" => $key, "text" => getWords($value), "selected" => true);
      else
        $arrData[] = array("value" => $key, "text" => getWords($value), "selected" => false);
    return $arrData;
  }
  function getDataList($data, $indexed = true, $default = null, $isHasEmpty = false, $emptyData = null)
  {
    $arrData = array();
    if ($isHasEmpty) $arrData[] = $emptyData;
    if ($indexed)
    {
      foreach($data as $key => $value)
      {
        if ($key == $default)
          $arrData[] = array("value" => $key, "text" => getWords($value), "selected" => true);
        else
          $arrData[] = array("value" => $key, "text" => getWords($value), "selected" => false);
      }
    }
    else
    {
      foreach($data as $value)
      {
        if ($value == $default)
          $arrData[] = array("value" => $value, "text" => getWords($value), "selected" => true);
        else
          $arrData[] = array("value" => $value, "text" => getWords($value), "selected" => false);
      }
    }
    if ($default == null && $isHasEmpty) $arrData[0]['selected'] = true;
    if ($default == null && !$isHasEmpty) $arrData[0]['selected'] = true;
    return $arrData;
  }

  function getDataListTrainingType($default = null, $isHasEmpty = false, $emptyData = null, $criteria = "")
  {
    $tbl = new cModel("hrd_training_type", getWords("training type"));
    $arrData = $tbl->generateList($criteria, "training_type", null, "training_type", array("training_type", "note"), $isHasEmpty, $emptyData);
    if ($default != null || $default != "")
      while(list($key, $val) = each($arrData))
      {
        $temp = &$arrData[$key];
        if ($val['value'] == $default)
          $temp['selected'] = true;
        else
          $temp['selected'] = false;
      }
    return $arrData;
  }
  function getDataListTrainingVendor($default = null, $isHasEmpty = false, $emptyData = null, $criteria = "")
  {
    $tbl = new cModel("hrd_training_vendor", getWords("training vendor"));
    $arrData = $tbl->generateList($criteria, "id", null, "id", array("id", "name_vendor"), $isHasEmpty, $emptyData);
    if ($default != null || $default != "")
      while(list($key, $val) = each($arrData))
      {
        $temp = &$arrData[$key];
        if ($val['value'] == $default)
          $temp['selected'] = true;
        else
          $temp['selected'] = false;
      }
    return $arrData;
  }

  function getDataListEmployeeFamily($default = null, $isHasEmpty = false, $emptyData = null, $criteria = "")
  {
    global $ARRAY_FAMILY_RELATION;
    $tbl = new cModel("hrd_employee_family", getWords("family member"));
    $arrData = $tbl->generateList($criteria, "name", null, "name", array("name", "relation"), $isHasEmpty, $emptyData);
    foreach($arrData as $index => $arrDetail)
    {
      if ($arrDetail['value'] != "")
      {
        list($strTemp1, $strTemp2) = explode(" - ", $arrDetail['text']);
        $arrData[$index]['text'] = $strTemp1." - ".$ARRAY_FAMILY_RELATION[$strTemp2];
      }
    }
    if ($default != null || $default != "")
    {
      while(list($key, $val) = each($arrData))
      {
        $temp = &$arrData[$key];
        if ($val['value'] == $default)
          $temp['selected'] = true;
        else
          $temp['selected'] = false;
      }
    }
    return $arrData;
  }




  function getDataListMedicalTreatmentType($default = null, $isHasEmpty = false, $emptyData = null, $bolIncludeOutpatient = false)
  {
    global $ARRAY_MEDICAL_TREATMENT_GROUP;
    $arrData = array();
    if ($isHasEmpty) $arrData[] = $emptyData;
    foreach($ARRAY_MEDICAL_TREATMENT_GROUP as $key => $value)
    {
      if ($bolIncludeOutpatient || $value != "outpatient")
      {
        if ($key === $default)
          $arrData[] = array("value" => $key, "text" => getWords($value), "selected" => true);
        else
          $arrData[] = array("value" => $key, "text" => getWords($value), "selected" => false);
      }
    }
    return $arrData;
  }

  function getDataListDonationType($default = null, $isHasEmpty = false, $emptyData = null)
  {
    $tbl = new cModel("hrd_donation_type", getWords("donation type"));
    $arrData = $tbl->generateList(null, "name", null, "code", array("code", "name"), $isHasEmpty, $emptyData);

    if ($default != null || $default != "")
      while(list($key, $val) = each($arrData))
      {
        $temp = &$arrData[$key];
        if ($val['value'] == $default)
          $temp['selected'] = true;
        else
          $temp['selected'] = false;
      }
    return $arrData;
  }

  function getDataListDayName($default = null, $isHasEmpty = false, $emptyData = null)
  {
    $arrDay     = array(0 => "Sunday", "Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday");
    $arrData = array();
    if ($isHasEmpty)
      $arrData[] = $emptyData;
    foreach($arrDay as $key => $val)
    {
      if ($default == $key)
        $arrData[] = array("value" => $key, "text" => $val, "selected" => true);
      else
        $arrData[] = array("value" => $key, "text" => $val, "selected" => false);
    }
    return $arrData;
  }

  function getDataLivingCost($default = null, $isHasEmpty = false, $emptyData = null)
  {
    $tbl = new cModel("hrd_minimum_living_cost", getWords("living cost"));
    $arrData = $tbl->generateList(null, null, null, "code", "note", $isHasEmpty, $emptyData);
    if ($default != null || $default != "")
      while(list($key, $val) = each($arrData))
      {
        $temp = &$arrData[$key];
        if ($val['value'] == $default)
          $temp['selected'] = true;
        else
          $temp['selected'] = false;
      }
    return $arrData;
  }

  function getDataCheckBoxMRFRequestType($default = null, $isHasEmpty = false, $emptyData = null)
  {
    $arrData = array();
    if ($isHasEmpty)
    {
      if ($emptyData == null) $emptyData = array("value" => "", "text" => "");
      $arrData[] = $emptyData;
    }
    $ARRAY_MRF_REQUEST_TYPE = array(0 => "additional", "subtitution");
    foreach($ARRAY_MRF_REQUEST_TYPE as $key => $value)
      if ($key == $default)
        $arrData[] = array("value" => $key, "text" => getWords($value), "checked" => true);
      else
        $arrData[] = array("value" => $key, "text" => getWords($value), "checked" => false);
    return $arrData;
  }//

function getAllowanceTypeMRF($default = null, $isHasEmpty = false, $emptyData = null) {
    $arrData = array();
    if($isHasEmpty) {
        if ($emptyData == null) $emptyData = array("value" => "", "text" => "");
        $arrData[] = $emptyData;
    }
    $ARRAY_MRF_ALLOWANCE_TYPE = array(0 => "UMK + MEAL", "UMK", "Other Allowance");
    foreach($ARRAY_MRF_ALLOWANCE_TYPE as $key => $value)
        if($key == $default)
            $arrData[] = array("value" => $key, "text" => getWords($value), "checked" => true);
        else
            $arrData[] = array("value" => $key, "text" => getWords($value), "checked" => false);
    //var_dump($arrData);
    return $arrData;
}

  function getDataCheckBoxMRFBudgetType($default = null, $isHasEmpty = false, $emptyData = null)
  {
    $arrData = array();
    if ($isHasEmpty)
    {
      if ($emptyData == null) $emptyData = array("value" => "", "text" => "");
      $arrData[] = $emptyData;
    }
    $ARRAY_MRF_BUDGET_TYPE = array(0 => "according to budget", "over budget");
    foreach($ARRAY_MRF_BUDGET_TYPE as $key => $value)
      if ($key == $default)
        $arrData[] = array("value" => $key, "text" => getWords($value), "checked" => true);
      else
        $arrData[] = array("value" => $key, "text" => getWords($value), "checked" => false);
    return $arrData;
  }//

  // fungsi untuk buat array data MRF, index adalah ID,
  // input : bolAll = jika false hanya menampilkan yang masih aktif (belum terpenuhi)
  //         includeID = tampilkan juga data dengan id tertentu
  // value adalah : Jabatan | Departemen | Nomor MRF
  function getDataListMRF($default = null, $isHasEmpty = false, $emptyData = null, $bolAll = false, $includeID = "")
  {
  	global $strDataBranch;
    $tbl = new cModel("hrd_recruitment_need", getWords("recruitment need"));
    if ($isHasEmpty) if ($emptyData == null) $emptyData = array("value" => "", "text" => "");

    $strActive = ($bolAll) ? "" : " AND \"number\" + number_female > number_ok ";
    $strInclude = ($includeID == "") ? "" : "OR id = '$includeID' ";
    //$strKriteria = "(status <> ".REQUEST_STATUS_DENIED. " $strActive) $strInclude " ;
    $strKriteria = "(status >= ".REQUEST_STATUS_APPROVED. " $strActive) $strInclude " ; // hanya ambil yang sudah approve saja
    if ($strDataBranch != "") $strKriteria .= "AND branch_code = '$strDataBranch' ";
    $arrData = $tbl->generateList($strKriteria, "position_code", null, "id", array("request_number", "position_code", "department_code", "functional_code"), $isHasEmpty, $emptyData);
    if ($default != null || $default != "")
      while(list($key, $val) = each($arrData))
      {
        $temp = &$arrData[$key];
        if ($val['value'] == $default)
          $temp['selected'] = true;
        else
          $temp['selected'] = false;
      }

    return $arrData;
  }

  function getDataCheckBoxGender($default = null, $isHasEmpty = false, $emptyData = null)
  {
    global $ARRAY_GENDER ;
    $arrData = array();
    if ($isHasEmpty) $arrData[] = $emptyData;

    foreach($ARRAY_GENDER  as $key => $value)
      if ($key == $default)
        $arrData[] = array("value" => $key, "text" => getWords($value), "checked" => true);
      else
        $arrData[] = array("value" => $key, "text" => getWords($value), "checked" => false);

    return $arrData;
  }

  function getDataListCandidateLanguage($default = null, $isHasEmpty = false, $emptyData = null)
  {
    $arrData = array();
    if ($isHasEmpty)
    {
      if ($emptyData == null) $emptyData = array("value" => "", "text" => "");
      $arrData[] = $emptyData;
    }
    $arrLanguageList = array("English", "Japanese", "Mandarin", "French");
    foreach($arrLanguageList as $value)
      if ($value == $default)
        $arrData[] = array("value" => $value, "text" => $value, "selected" => true);
      else
        $arrData[] = array("value" => $value, "text" => $value, "selected" => false);
    return $arrData;
  }//

  function getDataCheckBoxCandidateLanguageSkill($default = null)
  {
    $arrData = array();
    $arrLanguageList = array(1, 2, 3, 4, 5);
    foreach($arrLanguageList as $value)
      if ($value == $default)
        $arrData[] = array("value" => $value, "text" => $value, "checked" => true);
      else
        $arrData[] = array("value" => $value, "text" => $value, "checked" => false);
    return $arrData;
  }//

  function getDataSelectCandidateLanguageSkill($default = null)
  {
    $arrData = getDataCheckBoxCandidateLanguageSkill();
	foreach ($arrData as $value)
		unset($value["checked"]);
	$arrData[0]["selected"] = true;
    return $arrData;
  }//

  function getDataListTable($default = null, $isHasEmpty = false, $emptyData = null)
  {
    $arrData = array();
    if ($isHasEmpty)
    {
      $arrData[] = $emptyData;
    }
    $arrTableList = array('Employee' => 'Employee',
                          'Division' => 'Division',
                          'Department' => 'Department',
                          'Unit' => 'Section',
                          'Section' => 'SubSection');
    foreach($arrTableList as $key => $value)
      if ($value == $default)
        $arrData[] = array("value" => $value, "text" => $key, "selected" => true);
      else
        $arrData[] = array("value" => $value, "text" => $key, "selected" => false);
    return $arrData;
  }
  /*
  ====================================================================================================================
  --------------------------------------------------------------------------------------------------------------------
  DESK 		: Fungsi- fungsi dibawah ini digunakan untuk migrasi modul recruitment
  			  dari aplikasi smart-U ke DeVosa.
  Update	: 2011/12/13
  --------------------------------------------------------------------------------------------------------------------
  ====================================================================================================================
  */

  //====================== Begin GET ROLE=============================================================================
  function getDataGroupByGroupRole($idGroupRole)
  {
    $tbl = new cModel("adm_group");
    $arrData = $tbl->findByGroupRole($idGroupRole, null, "id_adm_group");
    return $arrData;
  }

  function getDataGroupRoleCandidate()
  {
    return getDataGroupByGroupRole(ROLE_CANDIDATE);
  }

  function getDataGroupRoleSupervisor()
  {
    return getDataGroupByGroupRole(ROLE_SUPERVISOR);
  }

  function getDataGroupRoleEmployee()
  {
    return getDataGroupByGroupRole(ROLE_EMPLOYEE);
  }
  //========================= END ROLE =============================================================================

  //============BEGIN object global, untuk mengatur hak akses =======================================================
  include_once ("cls_permission.php");
  $objUP = new clsUserPermission(); //Cek class ini di cls_permision.php
  $strGlobalEmployeeFilter = $objUP->genFilterEmployee(); // untuk keperluan melakuan filter terhadap setiap data karyawan
  //=============== End ==============================================================================================


  /*================= BEGIN code candidate next =====================================================================
   mengambil kode user untuk data kandidat
   strDate : jika diisi, maka jadi acuan tahun, jika kosong dianggap tahun sekarang */

  function getDataNextCandidateCode($strDate = "")
  {
    include_once("common_function_fujiko.php");
    $tbl = new cModel("hrd_candidate");
    if ($strDate == "") $strDate = date("Y-m-d");

    $strSQL = "
      SELECT max(UPPER(candidate_code)) AS candidate_code FROM hrd_candidate
      WHERE (NOT candidate_code IS NULL) AND candidate_code <> ''
      AND EXTRACT(year FROM application_date) = EXTRACT(year FROM date '$strDate')
    ";

    $arrData = $tbl->query($strSQL);
    if (count($arrData) > 0)
    {
      if ($arrData[0]['candidate_code'] != "")
      {
        //increment 1
        //format candidate code adalah CIGYYNNNN
        $number = intval(substr($arrData[0]['candidate_code'], 5));

        $number += 1;
        $number = leadingZero($number, 4);
        $newCode = substr($arrData[0]['candidate_code'], 0, 5).$number;

        return $newCode;
      }
    }

    //not found/error or no candidate code in the table...then....
    $number = 1;
    $number = leadingZero($number, 4);
    $newCode = "CIG".date("y").$number;
    return $newCode;
  }
  //============================ END CANDICATE CODE ===========================================================



  //==========================BEGIN GET DATA MARITAL STATUS===================================================
  function getDataListMaritalStatusCandidate($default = null, $isHasEmpty = false, $emptyData = null)
  {
    global $ARR_DATA_MARITAL_STATUS_CANDIDATE;
    $arrData = array();
    if ($isHasEmpty) $arrData[] = $emptyData;
	$arrData[0]["selected"] = false;
    foreach($ARR_DATA_MARITAL_STATUS_CANDIDATE as $key => $value){
      if ($key == $default)
        $arrData[] = array("value" => $key, "text" => getWords($value), "selected" => true);
      else
        $arrData[] = array("value" => $key, "text" => getWords($value), "selected" => false);
	}
    return $arrData;
  }
  //=================== END DATA MARITAL STATUS ===============================================================


  /// ========================== GET DATA RECRUITMENT STEP=========================================================
   function getDataListRecruitmentProcessTypeStep($default = null, $isHasEmpty = false, $emptyData = null)
  {
    //$strSQL  = "SELECT MAX(step) AS maks FROM hrd_recruitment_process_type ";
    $tbl = new cModel("hrd_recruitment_process_type", getWords("recruitment process type"));
    $arrQuery = $tbl->query("SELECT MAX(step) AS step FROM ".$tbl->strTableName.";");
    $intStep = 1;
    if (count($arrQuery) > 0)
      $intStep = $arrQuery[0]['step'] + 1;

    $arrData = array();
    if ($isHasEmpty)
    {
      if ($emptyData == null) $emptyData = array("value" => "", "text" => "");
      $arrData[] = $emptyData;
    }
    for ($i = 1; $i <= $intStep; $i++)
    {
      if ($default != null || $default != "")
      {
        if ($default == $i)
          $arrData[] = array("value" => $i, "text" => $i, "selected" => true);
        else
          $arrData[] = array("value" => $i, "text" => $i, "selected" => false);
      }
      else
        $arrData[] = array("value" => $i, "text" => $i, "selected" => false);
    }
    return $arrData;
  }
  //============================ END DATA RECRUITMENT  ===============================================================

  // ============================ BEGIN GET DATA LIST CANDIDATE =====================================================
  function getDataListCandidateReference($type, $default = null, $isHasEmpty = false, $emptyData = null)
  {
    $tbl = new cModel("hrd_candidate_reference", getWords("reference"));
    if ($isHasEmpty) if ($emptyData == null) $emptyData = array("value" => "", "text" => "");
    $arrData = $tbl->generateList("type=$type", "reference", null, "reference", "reference", $isHasEmpty, $emptyData);
    if ($default != null || $default != "")
      while(list($key, $val) = each($arrData))
      {
        $temp = &$arrData[$key];
        if ($val['value'] == $default)
          $temp['selected'] = true;
        else
          $temp['selected'] = false;
      }
    return $arrData;
  }
  //=========================== END CANDIDATR REFERE==================================================================

  //================BEGIN Mengambil daftar data wilayah ==============================================================
  function getDataListWilayah($default = null, $isHasEmpty = false, $emptyData = null, $kriteria = null)
  {
    $tbl = new cModel("hrd_wilayah", getWords("Wilayah"));
    $arrData = $tbl->generateList($kriteria, "wilayah_name", null, "id", "wilayah_name", $isHasEmpty, $emptyData);
    if ($default != null || $default != "")
      while(list($key, $val) = each($arrData))
      {
        $temp = &$arrData[$key];
        if ($val['value'] == $default)
          $temp['selected'] = true;
        else
          $temp['selected'] = false;
      }
    return $arrData;
  }
  //=========================== END ======================================================================================

  //============== GET DATA VENDOR======================================================================
  function getDataListReference($default = null, $isHasEmpty = false, $emptyData = null, $criteria = "")
  {
    global $strDataCompany;
    $tbl = new cModel("hrd_candidate_reference", getWords("candidate reference"));
    $arrData = $tbl->generateList("1=1 ". $criteria, "id", null, "reference", array("name", "reference"), $isHasEmpty, $emptyData);
    if ($default != null || $default != "")
      while(list($key, $val) = each($arrData))
      {
        $temp = &$arrData[$key];
        if ($val['value'] == $default)
          $temp['selected'] = true;
        else
          $temp['selected'] = false;
      }
 	return $arrData;
  }

  //============================================================================================================

  /// Untuk mendapatkan infromasi level pendidikan karyawan =====================================================
  function getDataListAcademic($default = null, $isHasEmpty = false, $emptyData = null)
  {
    $arrData = array();
    if ($isHasEmpty)
    {
      if ($emptyData == null) $emptyData = array("value" => "", "text" => "");
      $arrData[] = $emptyData;
    }
    $arrAcademicList = array("SD", "SMP", "SMA", "D1, D2, D3, Akademi", "S1, Sarjana", "S2, Pasca Sarjana", "S3, PHD", "OTHER");
    foreach($arrAcademicList as $value)
      if ($value == $default)
        $arrData[] = array("value" => $value, "text" => $value, "selected" => true);
      else
        $arrData[] = array("value" => $value, "text" => $value, "selected" => false);
    return $arrData;

  }//
    //=================================================== END ================================================
  /* Custom & Modification for FKR */  
  function getDataListCostCenter($default = null, $isHasEmpty = false, $emptyData = null){
  	$arrData = array();
    if ($isHasEmpty){
      if ($emptyData == null) $emptyData = array("value" => "", "text" => "");
    }
    $tbl = new cModel("hrd_cost_center", getWords("cost center"));
    $arrData = $tbl->generateList(null, null, null, "code", "name", $isHasEmpty, $emptyData);
    if (!empty($default))
      while(list($key, $val) = each($arrData))
      {
        $temp = &$arrData[$key];
        if ($val['value'] == $default)
          $temp['selected'] = true;
        else
          $temp['selected'] = false;
      }
    return $arrData;
  }
  function getActiveAllowanceType($db = null){
  	global $db;
  	$activeTemplate = $_SESSION['currentActiveTemplate'];
  	$tblAllowanceType = new cModel("hrd_allowance_type");
  	if (!empty($activeTemplate)){
    	$arrAllowance = $tblAllowanceType->findAll("active = true AND template_name='$activeTemplate'", null, "seq", null, null, "id");
    }else{
    	$activeTemplate = getSalarySettingActiveTemplate($db);
    	if (!empty($activeTemplate)){
    		$arrAllowance = $tblAllowanceType->findAll("active = true AND template_name='$activeTemplate'", null, "seq", null, null, "id");
    	}else{
    		$arrAllowance = $tblAllowanceType->findAll("active = true", null, "seq", null, null, "id");
    	}
    }
    return $arrAllowance;
  }
  function getSalarySettingActiveTemplate($db = null){
  	$strTemplateName = null;
  	if (!empty($db) && $db->connect()){
	  	$strtempSQL  = "SELECT value FROM all_setting where code='template_name'";
	    $resDb = $db->execute($strtempSQL);
	    $rowDb = $db->fetchrow($resDb);
	    $strTemplateName = $rowDb['value'];	
	  }
	  return $strTemplateName;
  }
  function getSalarySetList($indexKey = 'id', $sortDesc = true, $idCompany = null){
  	$tblBasicSalarySet = new cModel("hrd_basic_salary_set");
  	$strCompany = "";
  	if (!empty($idCompany)){
  		$strCompany = "id_company = '$idCompany'";	
  	}
    $arrBasicSalarySet = $tblBasicSalarySet->findAll($strCompany, "id, start_date, note, id_company", "start_date DESC", null, 1, $indexKey);
    foreach($arrBasicSalarySet AS $keySet => $arrSet)
    {
    	$companyData = getCompanyName($arrSet['id_company']);
      $arrSetSource[$keySet] = $arrSet['start_date']." - ".$companyData[0]['company_name'];
    }
    if ($sortDesc){
			krsort($arrSetSource);
		}else{
			ksort($arrSetSource);
		}
		return $arrSetSource;	
  }
  function getCompanyName($companyID = null){
  	if (!empty($companyID)){
  		$tblCompany = new cModel("hrd_company");
    	$arrCompany = $tblCompany->findAll("id = '$companyID'", "id, company_name", "", null, 1);	
  	}
  	return $arrCompany;
  }
  function getPlacementChange($db = null, $strIDMutation = null){
  	$arrayChange = null;
  	if (!empty($db) && $db->connect() && !empty($strIDMutation)){
  		$strSQL = "SELECT (SELECT COUNT(*) FROM hrd_employee_mutation_branch WHERE id_mutation = mut.id) AS branch_mutation, ";
  		$strSQL .= "(SELECT COUNT(*) FROM hrd_employee_mutation_cost_center WHERE id_mutation = mut.id) AS cost_center_mutation, ";
  		$strSQL .= "(SELECT COUNT(*) FROM hrd_employee_mutation_department WHERE id_mutation = mut.id) AS department_mutation, ";
  		$strSQL .= "(SELECT COUNT(*) FROM hrd_employee_mutation_id WHERE id_mutation = mut.id) AS nik_mutation, ";
  		$strSQL .= "(SELECT COUNT(*) FROM hrd_employee_mutation_position WHERE id_mutation = mut.id) AS position_mutation, ";
  		$strSQL .= "(SELECT COUNT(*) FROM hrd_employee_mutation_salary WHERE id_mutation = mut.id) AS salary_mutation, ";
  		$strSQL .= "(SELECT COUNT(*) FROM hrd_employee_mutation_status WHERE id_mutation = mut.id) AS status_mutation ";
  		$strSQL .= "FROM hrd_employee_mutation AS mut WHERE id='$strIDMutation'";
  		$resDb = $db->execute($strSQL);
	    $rowDb = $db->fetchrow($resDb);
  		$arrayChange = $rowDb;
  	}
  	return $arrayChange;
  }
  function createPlacementChangeDesc($arrayChanges = null){
  	$changeDesc = null;
  	if (!empty($arrayChanges)){
  		$changeDesc = array();
  		if (count($arrayChanges)){
  			foreach($arrayChanges as $keyChange => $value){
  				if ($keyChange == 'branch_mutation' && (int)$value > 0){
  					$changeDesc[] = 'Branch Change';	
  				}else if ($keyChange == 'cost_center_mutation' && (int)$value > 0){
  					$changeDesc[] = 'Cost Center Change';	
  				}else if ($keyChange == 'department_mutation' && (int)$value > 0){
  					$changeDesc[] = 'Department Change';	
  				}else if ($keyChange == 'nik_mutation' && (int)$value > 0){
  					$changeDesc[] = 'NIK Change';	
  				}else if ($keyChange == 'position_mutation' && (int)$value > 0){
  					$changeDesc[] = 'Position Change';	
  				}else if ($keyChange == 'salary_mutation' && (int)$value > 0){
  					$changeDesc[] = 'Salary Change';	
  				}else if ($keyChange == 'status_mutation' && (int)$value > 0){
  					$changeDesc[] = 'Status Change';	
  				}
  			}
  		}
  	}
  	return $changeDesc;
  }
  function createSalaryStatusTypeList($db = null){
  	$arrSalaryTransferType = null;
  	if (!empty($db) && $db->connect()){
	  	$strSQL  = "SELECT id, code, remark FROM hrd_salary_transfer_type ";
	    $resDb = $db->execute($strSQL);
	    while ($rowDb = $db->fetchrow($resDb)) {
	      $strCode = $rowDb['code'];
				$arrSalaryTransferType[$strCode] = $rowDb['remark'];
			}
		}
		return $arrSalaryTransferType;
  }
  function autoHoldSalaryAndOvertime($db = null, $strIDMaster = null){
  	$arrayEmployeeOnHold = null;
  	if (!empty($strIDMaster) && !empty($db) && $db->connect()){
  		$arrSalaryTransferType = createSalaryStatusTypeList($db);
  		$strSQL = "SELECT id, bank_account FROM hrd_employee WHERE bank_account = '' OR bank_account is null OR bank_account = '0'";
  		$resDb = $db->execute($strSQL);
  		while ($rowDb = $db->fetchrow($resDb)){
  			$strCheckStatusExists = "SELECT COUNT(*) AS transfer_status_exists FROM hrd_salary_transfer_status ";
  			$strCheckStatusExists .= "WHERE id_employee='".$rowDb['id']."' AND id_salary_master='".$strIDMaster."'";
  			$resDbCheckStatus = $db->execute($strCheckStatusExists);
  			$rowDbCheckStatus = $db->fetchrow($resDbCheckStatus);
  			if ($rowDbCheckStatus['transfer_status_exists']){
  				$strUpdate = "UPDATE hrd_salary_transfer_status SET transfer_code='5', note='Auto ".$arrSalaryTransferType[5]."', release_number = null ";
  				$strUpdate .= "WHERE id_employee='".$rowDb['id']."' AND id_salary_master='".$strIDMaster."'";	
  				$updateStatus = $db->execute($strUpdate);
  				$arrayEmployeeOnHold[] = $rowDb;
  			}else{
  				$strCheckSalaryDetail = "SELECT COUNT(*) AS employee_exists FROM hrd_salary_detail WHERE ";
  				$strCheckSalaryDetail .= "id_employee = '".$rowDb['id']."' AND id_salary_master='".$strIDMaster."'";
  				$resDbCheck = $db->execute($strCheckSalaryDetail);
  				$rowDbCheck = $db->fetchrow($resDbCheck);
  				if ($rowDbCheck['employee_exists']){
  					$strInsert = "INSERT INTO hrd_salary_transfer_status ";
  					$strInsert .= "(id_salary_master,id_employee,transfer_code,note, release_number) VALUES ";	
  					$strInsert .= "($strIDMaster,".$rowDb['id'].",'5','Auto ".$arrSalaryTransferType[5]."', null)";
  					$insertStatus = $db->execute($strInsert);
  					if ($insertStatus){
  						$arrayEmployeeOnHold[] = $rowDb;
  					}
  				}
  			}
  		}
  	}
  	return $arrayEmployeeOnHold;
  }
  function autoReleaseOvertimeAndSalaryByBankAccount($db = null, $strIDMaster = null){
  	$arrayEmployeeOnRelease = null;
  	if (!empty($strIDMaster) && !empty($db) && $db->connect()){
  		$arrayEmployeeOnRelease = array();
  		$strSQL = "SELECT trans_stat.id, trans_stat.id_employee, emp.bank_account FROM hrd_salary_transfer_status trans_stat ";
  		$strSQL .= "LEFT JOIN hrd_employee emp ON trans_stat.id_employee = emp.id ";
  		$strSQL .= "WHERE trans_stat.transfer_code='5' AND id_salary_master='".$strIDMaster."' ";
  		$strSQL .= "AND emp.bank_account != '' AND emp.bank_account is not null AND emp.bank_account != '0'";
  		$resDb = $db->execute($strSQL);
  		while ($rowDb = $db->fetchrow($resDb)){
  			$strUpdate = "UPDATE hrd_salary_transfer_status SET transfer_code='0',note='Auto Release' ";
				$strUpdate .= "WHERE id='".$rowDb['id']."'";	
				$updateStatus = $db->execute($strUpdate);
				$arrayEmployeeOnRelease[] = $rowDb;
  		}
  	}
  	return $arrayEmployeeOnRelease;
  }
  function syncMRFCandidate($db = null, $limitMRF = 0){
  	$mrfUpdated = null;
  	if (!empty($db) && $db->connect()){
  		$strSQL = "SELECT id, request_number,number_ok FROM hrd_recruitment_need ORDER BY id DESC ";
  		if ($limitMRF != 0){
  			$strSQL .= "LIMIT $limitMRF";
  		}
  		$resDb = $db->execute($strSQL);
  		$mrfUpdated = array();
  		while ($rowDb = $db->fetchrow($resDb)){
  			$numberOkOld = $rowDb['number_ok'];
  			$strSQLTotCandidate = "SELECT COUNT(*) AS tot_candidate FROM hrd_fkr WHERE ";
  			$strSQLTotCandidate .= "id_recruitment_need=".$rowDb['id']." AND status >= 2";
  			$resDbTot = $db->execute($strSQLTotCandidate);
  			$rowDbTot = $db->fetchrow($resDbTot);
  			$numberOk = isset($rowDbTot['tot_candidate']) && !empty($rowDbTot['tot_candidate']) ? $rowDbTot['tot_candidate'] : 0;
  			if ($numberOk != $numberOkOld){
	  			$strUpdateMRF = "UPDATE hrd_recruitment_need SET number_ok='$numberOk' WHERE id=".$rowDb['id'];
	  			$db->execute($strUpdateMRF);
	  			$mrfUpdated[] = $rowDb;
	  		}
  		}
  	}
  	return $mrfUpdated;
  }
  function getMasterSalaryByYearGlobal($db = null, $intYear = null, $intCompany = null, $isManagerial = 'FALSE'){
  	$arraySalaryMasterId = array();
  	if (!empty($db) && !empty($intYear) && $db->connect()){
	  	$strSQL = "SELECT id FROM hrd_salary_master WHERE ";
	  	$strSQL .= "EXTRACT (YEAR FROM salary_date) = $intYear AND status >= ".REQUEST_STATUS_APPROVED_2." ";
	  	if (!empty($intCompany)){
	  		$strSQL .= "AND id_company = $intCompany ";
	  	}
	  	$strSQL .= "AND is_overtime_only IS FALSE AND is_managerial IS $isManagerial ";
	  	$strSQL .= "ORDER BY salary_date";
	    $res = $db->execute($strSQL);
	    while ($rowDb = $db->fetchrow($resDb)){
	    	$arraySalaryMasterId[] = $rowDb['id'];
	    }
	  }
    return $arraySalaryMasterId;
  }
  function getFKRSalaryList($db = null, $idEmployee = null, $idCandidate = null){
  	if (!empty($db) && (!empty($idEmployee) || !empty($idCandidate)) && $db->connect()){
  		$strSQL = "SELECT t1.*, t2.code AS allowance_code, t2.name AS allowance_name
      FROM hrd_fkr_detail AS t1 INNER JOIN hrd_allowance_type AS t2 ON t1.id_allowance_type = t2.id
      WHERE t1.id_fkr IN ";
      if (!empty($idCandidate)){
      	$strSQL .= "( SELECT id FROM hrd_fkr WHERE id_candidate = '$idCandidate' )";
      }else if(!empty($idEmployee)){
      	$strSQL .= "( SELECT id FROM hrd_fkr WHERE id_employee = '$idEmployee' )";
      }
	    $resDb = $db->execute($strSQL);
	    $arrayAllowance = array();
	    while ($rowDb = $db->fetchrow($resDb)){
	    	$allowanceData = array();
	    	$fltAmount = ($rowDb['amount_next'] == "" || $rowDb['amount_next'] == 0) ? $rowDb['amount_start'] : $rowDb['amount_next'];
      	$strCode = $rowDb['allowance_code'];
      	$strName = $rowDb['allowance_name'];
      	if ($fltAmount > 0){
      		$allowanceData['code'] = $strCode;
      		$allowanceData['name'] = $strName;
      		$allowanceData['amount'] = $fltAmount;
      	}
      	if (count($allowanceData)){
      		$arrayAllowance[] = $allowanceData;
      	}
	    }
  	}
  	return $arrayAllowance;
  }
  function exportXLSX($arrayData = null, $headers1 = null, $headers2 = null, $objectName = null, $title = null, $subtitle = null, $filenamereq = null){
  	$filename = '';
  	if (!is_null($arrayData) && count($arrayData) && !is_null($headers1) && !is_null($objectName)){
  		$alphas = createAlphasArray();
  		define('EOL',(PHP_SAPI == 'cli') ? PHP_EOL : '<br />');
			date_default_timezone_set('Asia/Jakarta');
			/** Include PHPExcel_IOFactory */
			require_once '../includes/phpexcel/Classes/PHPExcel/IOFactory.php';	
			$objPHPExcel = new PHPExcel();
			$objPHPExcel->setActiveSheetIndex(0);
			$headerStyleArray = array(
			  'borders' => array(
			    'allborders' => array(
			      'style' => PHPExcel_Style_Border::BORDER_THIN
			    )
			  ),
			  'fill' => array(
         	'type' => PHPExcel_Style_Fill::FILL_SOLID,
          'color' => array('rgb' => 'CED8F6')
        ),
        'alignment' => array(
            'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
            'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
        )
			);
			$minusValueStyleArray = array(
	    	'font'  => array(
	      	'color' => array('rgb' => 'FF0000'),
	    	)
	    );
			$startRow = 1;
			$totalCols = empty($headers2) ? count($headers1) : count($headers2);
			if (!is_null($title)){
				$startRow = 4;
				$objPHPExcel->getActiveSheet()->setCellValue('A2', $title);
				$objPHPExcel->getActiveSheet()->getStyle('A2')->getFont()->setSize(18)->setBold(true);
				$objPHPExcel->getActiveSheet()->mergeCells('A2:'.$alphas[$totalCols].'2');
			}
			if (!is_null($subtitle)){
				$objPHPExcel->getActiveSheet()->setCellValue('A3', $subtitle);
				$objPHPExcel->getActiveSheet()->getStyle('A3')->getFont()->setBold(true)->setItalic(true);
				$objPHPExcel->getActiveSheet()->mergeCells('A3:'.$alphas[$totalCols].'3');
			}
			//Simpel Headers
			/*for ($i = 0;$i < count($headers);$i++){
				$objPHPExcel->getActiveSheet()->setCellValue($alphas[$i].$startRow, $headers[$i]);
			}*/
			//Complex Headers 2 rows
			$startRows1 = $startRow;
			if (!empty($objPHPExcel) && !empty($headers1) && is_array($headers1) && count($headers1) && !empty($alphas)){
				$colpos = 0;
				for ($i = 0;$i < count($headers1);$i++){
					if (!is_null($headers1[$i]['value'])){
						$objPHPExcel->getActiveSheet()->setCellValue($alphas[$colpos].$startRow, $headers1[$i]['value']);
						if (isset($headers1[$i]['rowspan']) && $headers1[$i]['rowspan'] > 1){
							$objPHPExcel->getActiveSheet()->mergeCells($alphas[$colpos].$startRow.':'.$alphas[$colpos].($startRow + $headers1[$i]['rowspan'] - 1));
							$colpos++;
						}else if(isset($headers1[$i]['colspan']) && $headers1[$i]['colspan'] > 1){
							$objPHPExcel->getActiveSheet()->mergeCells($alphas[$colpos].$startRow.':'.$alphas[($colpos + $headers1[$i]['colspan'] - 1)].$startRow);
							$colpos = $colpos + $headers1[$i]['colspan'];
						}else{
							$colpos++;	
						}
					}
				}
			}
			$startRows2 = $startRow;
			if (!empty($objPHPExcel) && !empty($headers2) && is_array($headers2) && count($headers2) && !empty($alphas)){
				$startRow++;
				$startRows2 = $startRow;
				$colpos = 0;
				for ($i = 0;$i < count($headers2);$i++){
					if (!is_null($headers2[$i]['value'])){
						$objPHPExcel->getActiveSheet()->setCellValue($alphas[$colpos].$startRow, $headers2[$i]['value']);
						if (isset($headers2[$i]['rowspan']) && $headers2[$i]['rowspan'] > 1){
							$objPHPExcel->getActiveSheet()->mergeCells($alphas[$colpos].$startRow.':'.$alphas[$colpos].($startRow + $headers2[$i]['rowspan'] - 1));
							$colpos++;
						}else if(isset($headers2[$i]['colspan']) && $headers2[$i]['colspan'] > 1){
							$objPHPExcel->getActiveSheet()->mergeCells($alphas[$colpos].$startRow.':'.$alphas[($colpos + $headers2[$i]['colspan'])].$startRow);
							$colpos = $colpos + $headers2[$i]['colspan'];
						}else{
							$colpos++;	
						}
					}else{
						$colpos++;
					}
				}
			}
			//createHeadersXLS($objPHPExcel, $headers, $startRow, $alphas);
			$objPHPExcel->getActiveSheet()->getStyle('A'.$startRows1.':'.$alphas[($i-1)].$startRows2)->applyFromArray($headerStyleArray);
			$objPHPExcel->getActiveSheet()->getStyle('A'.$startRows1.':'.$alphas[($i-1)].$startRows2)->getFont()->setBold(true);
			$startRow++;
			if (empty($headers2)) $headers2 = $headers1;
			for ($i = 0;$i < count($headers2);$i++){
				for($j = 0;$j < count($arrayData);$j++){
					$rowNumber = $startRow + $j;
					$objPHPExcel->getActiveSheet()->setCellValue($alphas[$i].$rowNumber, $arrayData[$j]->$objectName[$i]);
					if (is_numeric($arrayData[$j]->$objectName[$i])){
						if ($arrayData[$j]->$objectName[$i] < 0){
							$objPHPExcel->getActiveSheet()->getStyle($alphas[$i].$rowNumber)->applyFromArray($minusValueStyleArray);
						}
						$objPHPExcel->getActiveSheet()->getStyle($alphas[$i].$rowNumber)->getNumberFormat()->setFormatCode('#,##0.00');
					}
				}
			}
			for ($i = 0;$i < count($headers2);$i++){
				$objPHPExcel->getActiveSheet()->getColumnDimension($alphas[$i])->setAutoSize(true);
				if (isset($headers2[$i]['wraptext']) && $headers2[$i]['wraptext']){
					$objPHPExcel->getActiveSheet()->getStyle($alphas[$i].$startRow.':'.$alphas[$i].($rowNumber + 1))->getAlignment()->setWrapText(true);
				}
				if (isset($headers2[$i]['align']) && !empty($headers2[$i]['align'])){
					if ($headers2[$i]['align'] == 'right'){
						$objPHPExcel->getActiveSheet()->getStyle($alphas[$i].$startRow.':'.$alphas[$i].($rowNumber + 1))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
					}else if($headers2[$i]['align'] == 'left'){
						$objPHPExcel->getActiveSheet()->getStyle($alphas[$i].$startRow.':'.$alphas[$i].($rowNumber + 1))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
					}else if($headers2[$i]['align'] == 'center'){
						$objPHPExcel->getActiveSheet()->getStyle($alphas[$i].$startRow.':'.$alphas[$i].($rowNumber + 1))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
					}
				}
				$objPHPExcel->getActiveSheet()->getStyle($alphas[$i].$startRow.':'.$alphas[$i].($rowNumber + 1))->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_TOP);
			}
			$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
			$filename = $filenamereq.'-'.time().'.xls';
			$objWriter->save($filename);
		}
		return $filename;
  }
  function deleteExcelDownloadedFile(){
  	global $db;
		if ($db->connect()){
			$oldTime = date("Y-m-d H:i:s", strtotime("-30 minutes"));
			$strSQL = "SELECT filename FROM hrd_temporary_file WHERE
			(filename LIKE '%.xlsx' OR filename LIKE '%.xls') 
			AND created <= '$oldTime'";
			$res = $db->execute($strSQL);
      while ($rowDb = $db->fetchrow($res)){
      	if (file_exists($rowDb['filename'])){
      		unlink($rowDb['filename']);
      	}
      }
      $strSQLDelete = "DELETE FROM hrd_temporary_file WHERE
      (filename LIKE '%.xlsx' OR filename LIKE '%.xls') 
      AND created <= '$oldTime'";
      $db->execute($strSQLDelete);
      //DELETE txt file
      $oldTime = date("Y-m-d H:i:s", strtotime("-60 minutes"));
			$strSQL = "SELECT filename FROM hrd_temporary_file WHERE
			filename LIKE '%.txt'	AND created <= '$oldTime'";
			$res = $db->execute($strSQL);
      while ($rowDb = $db->fetchrow($res)){
      	if (file_exists($rowDb['filename'])){
      		unlink($rowDb['filename']);
      	}
      }
      $strSQLDelete = "DELETE FROM hrd_temporary_file WHERE
      filename LIKE '%.txt' AND created <= '$oldTime'";
      $db->execute($strSQLDelete);
		}
	}
	function createAlphasArray(){
		$arrayAlphas = array();
		$alphas = range('A', 'Z');
		for ($i = 0;$i < count($alphas);$i++){
			$arrayAlphas[] = $alphas[$i];
		}
		for ($i = 0;$i < count($alphas);$i++){
			for ($j = 0;$j < count($alphas);$j++){
				$arrayAlphas[] = $alphas[$i].$alphas[$j];
			}
		}
		return $arrayAlphas;
	}
	function getEmployeePositionData($db = null, $employeeId = null, $fieldName = null){
        $positionData = null;
        if (!empty($db) && $db->connect() && !empty($employeeId)){
            if (!empty($fieldName) && !is_array($fieldName)){
                $strSQL = 'SELECT pos.'.$fieldName.' FROM hrd_employee emp LEFT JOIN ';
                $strSQL .= 'hrd_position pos ON emp.position_code = pos.position_code ';
                $strSQL .= 'WHERE emp.id = '.$employeeId;
                $resDb = $db->execute($strSQL);
                while ($rowDb = $db->fetchrow($resDb)){
                    $positionData  = $rowDb;
                }
            }else if (is_array($fieldName)){
                $strSQL = 'SELECT '.implode(',',$fieldName).' FROM hrd_employee emp LEFT JOIN ';
                $strSQL .= 'hrd_position pos ON emp.position_code = pos.position_code ';
                $strSQL .= 'WHERE emp.id = '.$employeeId;
                $resDb = $db->execute($strSQL);
                while ($rowDb = $db->fetchrow($resDb)){
                    $positionData  = $rowDb;
                }
            }else{
                $strSQL = 'SELECT pos.* FROM hrd_employee emp LEFT JOIN ';
                $strSQL .= 'hrd_position pos ON emp.position_code = pos.position_code ';
                $strSQL .= 'WHERE emp.id = '.$employeeId;
                $resDb = $db->execute($strSQL);
                while ($rowDb = $db->fetchrow($resDb)){
                    $positionData  = $rowDb;
                }
            }
        }
        return $positionData;
    }
?>
