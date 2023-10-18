<?php
include_once('../global/session.php');
include_once('global.php');
include_once('form_object.php');
include_once('salary_func.php');
include_once('../classes/hrd/hrd_basic_salary_set.php');
include_once('../classes/hrd/hrd_employee_allowance.php');
include_once('../classes/hrd/hrd_allowance_type.php');
//include_once("../includes/krumo/class.krumo.php");
$dataPrivilege = getDataPrivileges(
    basename($_SERVER['PHP_SELF']),
    $bolCanView,
    $bolCanEdit,
    $bolCanDelete,
    $bolCanApprove,
    $bolCanCheck,
    $bolCanAcknowledge,
    $bolCanApprove2
);
if (!$bolCanView) {
    die(accessDenied($_SERVER['HTTP_REFERER']));
}
//---- INISIALISASI ----------------------------------------------------
$strDataDetail = "";
$strDataColumn = "";
$strHidden = "";
$intTotalData = 0;
$strPaging = "";
$arrColumns = [];
$strAllowanceList = "";
$strMessage = $strMsgClass = "";
$bolError = true;
$strDisable = "";
$strReadonly = "";
$strWordsGeneralSetting = getWords("general setting");
$strWordsSalarySet = getWords("salary set");
$strWordsEmployeeAllowance = getWords("employee allowance");
$strWordsManagerialAllowance = getWords("managerial allowance");
$strWordsEmployeeDeduction = getWords("employee deduction");
$strWordsManagerialDeduction = getWords("managerial deduction");
$strWordStartDate = getWords("Start date");
$strWordEmployeeID = getwords("n i k");
$strWordEmployeeID2 = getwords("n i k corporate");
$strWordActive = getWords("active");
$strWordFamilyStatus = getWords("family status");
$strWordCurrency = getWords("currency");
$strWordEmployeeStatus = getWords("employee status");
// $strWordIDEmp               = getWords("id employee");
$strWordEmployeeID = getwords("n i k");
$strWordBranch = getWords("branch");
$strWordBranchContract = getWords("branch contract");
$strWordPosition = getWords("position");
$strWordFunctional = getWords("functional");
$strWordCompany = getWords("company");
$strWordDepartment = getWords("department");
$strWordSection = getWords("section");
$strWordSubSection = getWords("sub section");
$strWordDivision = getWords("division");
$strWordCostCenter = getWords("cost center");
$strWordID = getWords("id");
$strWordEmployeeName = getWords("employee name");
$strWordJobGrade = getWords("job grade");
$strWordAllowance = getWords("allowance");
$strWordSalarySet = getWords("salary set");
$allID = "";
//----------------------------------------------------------------------
//--- DAFTAR FUNSI------------------------------------------------------
// fungsi untuk menampilkan data
// $db = kelas database, $intRows = jumlah baris (return)
// $strKriteria = query kriteria, $strOrder = query ORder by
function getData(
    $db,
    &$intRows,
    $strIDSalarySet = "",
    $strIDSalarySetSource = "",
    $strKriteria = "",
    $intPage = 1,
    $strDefault = "",
    $strDefault2 = "",
    $strDefault3 = "",
    $bolLimit = true
) {
    global $strDisable;
    global $words;
    global $ARRAY_EMPLOYEE_STATUS;
    global $intTotalData;
    global $arrColumns;
    global $_SESSION;
    global $arrMultivalFixAllowance;
    global $arrMultivalDailyAllowance;
    global $arrTempVal;
    //PAGING ---------------------------------------------------------------------------------------
    global $intRowsLimit;
    global $strPaging;
    global $strIDCompany;
    //echo "D"; print_r($arrMultivalDailyAllowance); echo "D";
    $intRowsLimit = getSetting("rows_per_page");
    if (!is_numeric($intRowsLimit)) {
        $intRowsLimit = 50000;
    }
    // cari total data
    $intTotal = 0;
    $strSQL = "SELECT count(id) AS total FROM hrd_employee as t1 WHERE id_company = $strIDCompany ";
    $strSQL .= $strKriteria;
    $resDb = $db->execute($strSQL);
    if ($rowDb = $db->fetchrow($resDb)) {
        if (is_numeric($rowDb['total'])) {
            $intTotal = $rowDb['total'];
        }
    }
    $strPaging = getPaging($intPage, $intTotal, "javascript:goPage('[PAGE]')");
    if ($strPaging == "") {
        $strPaging = "1&nbsp;";
    }
    $intStart = (($intPage - 1) * $intRowsLimit);
    //echo $intStart;
    //--------- ---------------------------------------------------------------------------------------
    $intmodified_byID = $_SESSION['sessionUserID'];
    $intTextWidth = 10;
    $strResult = "";
    $tblAllowanceType = new cHrdAllowanceType;
    $arrNonFixAllowanceType = $tblAllowanceType->findall("active = TRUE", "code, active", "", null, 1, "code");
    // ambil data tunjangan tidak fix (hrd_employee_allowance) dari edisi terkait jika ada data
    $tblEmployeeAllowance = new cHrdEmployeeAllowance;
    //    echo $strIDSalarySet;
    if ($tblEmployeeAllowance->findCount("id_salary_set = '$strIDSalarySet'") > 0) {
        $strSQL = "SELECT t1.id AS id_employee_key, t4.* FROM hrd_employee AS t1 ";
        $strSQL .= "LEFT JOIN ((SELECT id_employee, allowance_code, amount FROM hrd_employee_allowance WHERE id_salary_set = $strIDSalarySet) AS t2 ";
        $strSQL .= " LEFT JOIN (SELECT code, active FROM hrd_allowance_type WHERE active = 't') AS t3 ON t2.allowance_code = t3.code) AS t4 ";
        $strSQL .= " ON t1.id = t4.id_employee ";
        $strSQL .= "WHERE id_company = $strIDCompany $strKriteria ";
        $resTmp = $db->execute($strSQL);
        while ($rowTmp = $db->fetchRow($resTmp)) {
            $arrNonFix1[$rowTmp['id_employee_key']][$rowTmp['allowance_code']] = $rowTmp['amount'];
        }
    }
    if ($strIDSalarySetSource != "" && $tblEmployeeAllowance->findCount("id_salary_set = $strIDSalarySetSource") > 0) {
        // ambil data tunjangan tidak fix (hrd_employee_allowance) dari edisi sebelumnya (atau edisi yang dipilih sebaai acuan)
        $strSQL = "SELECT t1.id AS id_employee_key, t4.* FROM hrd_employee AS t1 ";
        $strSQL .= "LEFT JOIN ((SELECT id_employee, allowance_code, amount FROM hrd_employee_allowance WHERE id_salary_set = $strIDSalarySetSource) AS t2 ";
        $strSQL .= " LEFT JOIN (SELECT code, active FROM hrd_allowance_type WHERE active = 't') AS t3 ON t2.allowance_code = t3.code) AS t4 ";
        $strSQL .= " ON t1.id = t4.id_employee ";
        $strSQL .= "WHERE id_company = $strIDCompany $strKriteria";
        $resTmp = $db->execute($strSQL);
        while ($rowTmp = $db->fetchRow($resTmp)) {
            $arrNonFix2[$rowTmp['id_employee_key']][$rowTmp['allowance_code']] = $rowTmp['amount'];
        }
    }
    // ambil data yang ada gaji pokok dan tunjangan fix
    $strSQL = "SELECT t1.id AS id_employee_key, employee_name, t2.* FROM hrd_employee AS t1 ";
    $strSQL .= "LEFT JOIN (SELECT * FROM hrd_employee_basic_salary WHERE id_salary_set = $strIDSalarySet) AS t2 ON t2.id_employee = t1.id ";
    $strSQL .= "WHERE id_company = $strIDCompany $strKriteria ";
    $resTmp = $db->execute($strSQL);
    while ($rowTmp = $db->fetchRow($resTmp)) {
        if ($rowTmp['id_employee'] == "") {
            getDefaultFix(
                $db,
                "basic_salary",
                $arrFix,
                "AND t1.id = " . $rowTmp['id_employee_key'] . "",
                $strIDSalarySetSource
            );
            foreach ($arrMultivalFixAllowance as $strCode => $strName) {
                getDefaultFix($db, $strCode, $arrFix, "AND t1.id = '" . $rowTmp['id_employee_key'] . "'");
            }
            foreach ($arrMultivalDailyAllowance as $strCode => $strName) {
                //saat pertama kali set dibuat, belum ada record, perlu cek apakah pada source set recordnya available, jika tidak, baca pada referensi
                //setelah ada idset, getdefault mengambil data dari referensi master, bukan tabel source
                //getDefaultFix($db, $strCode, $arrFix, "AND t1.id = '".$rowTmp['id_employee_key']."'");
                $arrFix[$rowTmp['id_employee_key']][$strCode] = $rowTmp[$strCode];
            }
            foreach ($arrNonFixAllowanceType AS $strCode => $arrNonFixAllowanceDetail) {
                $arrFix[$rowTmp['id_employee_key']][$strCode] = (isset($arrNonFix2[$rowTmp['id_employee_key']][$strCode])) ? $arrNonFix2[$rowTmp['id_employee_key']][$strCode] : 0;
            }
        } else {
            $arrFix[$rowTmp['id_employee_key']]['basic_salary'] = $rowTmp['basic_salary'];
            foreach ($arrMultivalFixAllowance as $strCode => $strName) {
                $arrFix[$rowTmp['id_employee_key']][$strCode] = $rowTmp[$strCode];
            }
            foreach ($arrMultivalDailyAllowance as $strCode => $strName) {
                $arrFix[$rowTmp['id_employee_key']][$strCode] = $rowTmp[$strCode];
            }
            foreach ($arrNonFixAllowanceType AS $strCode => $arrNonFixAllowanceDetail) {
                $arrFix[$rowTmp['id_employee_key']][$strCode] = (isset($arrNonFix1[$rowTmp['id_employee_key']][$strCode])) ? $arrNonFix1[$rowTmp['id_employee_key']][$strCode] : 0;
            }
        }
        foreach ($arrColumns AS $strKey => $strValue) {
            if (!isset($arrFix[$rowTmp['id_employee_key']][$strKey])) {
                $arrFix[$rowTmp['id_employee_key']][$strKey] = $strValue;
                $arrtest[$rowTmp['id_employee_key']][$strKey] = $strValue;
            }
        }
    }
    if ($strDefault != "") {
        getDefaultFix($db, $strDefault, $arrFix, "");
    } else if ($strDefault2 != "") {
        foreach ($arrFix AS $strEmp => $arrEmp) {
            $arrFix[$strEmp][$strDefault2] = $arrColumns[$strDefault2];
        }
    } else if ($strDefault3 != "") {
        $arrTempVal = $arrFix;
        //echo $strDefault3."|". getSetting($strDefault3);
        foreach ($arrFix AS $strEmp => $arrEmp) {
            $strFormula = (getSetting($strDefault3) == null) ? $arrColumns[$strDefault3] : getSetting($strDefault3);
            $arrParam = preg_split("/[\+\-\*\/]/", $strFormula);
            preg_match_all("/[\+\-\*\/]/", $strFormula, $arrOpr);
            $arrFix[$strEmp][$strDefault3] = getRecValue($arrParam, $arrOpr[0], $strEmp);
        }
    }
    $intRows = 0;
    $strSQL = "SELECT t1.id,t1.employee_id, t1.employee_name, t1.gender, t1.join_date, t1.resign_date,";
    $strSQL .= "t1.employee_status, t1.position_code, t1.grade_code, t1.family_status_code , t1.functional_code, ";//, t2.position_allowance ";
    $strSQL .= "t1.division_code, t1.branch_cost_center_code, t1.branch_penugasan_code, ";
    $strSQL .= "t1.position_code,t1.employee_id_2 ";
    $strSQL .= "FROM hrd_employee AS t1 ";
    $strSQL .= "LEFT JOIN hrd_department AS t4 ON t1.department_code = t4.department_code ";
    $strSQL .= "LEFT JOIN hrd_section AS t5 ON t1.section_code = t5.section_code ";
    $strSQL .= "LEFT JOIN hrd_position AS t6 ON t1.position_code = t6.position_code ";
    $strSQL .= "WHERE t1.id_company = $strIDCompany AND position_group >= 2 $strKriteria ";
    $strSQL .= "ORDER BY  join_date DESC ";
    if ($bolLimit) {
        $strSQL .= "LIMIT $intRowsLimit OFFSET $intStart ";
    }
    $resDb = $db->execute($strSQL);
    while ($rowDb = $db->fetchrow($resDb)) {
        $intRows++;
        ($rowDb['gender'] == 0) ? $strGender = "F" : $strGender = "M";
        // ----- TAMPILKAN DATA ---------------------------------------
        $strResult .= "<tr valign=top id=detailData$intRows title=\"" . $rowDb['employee_id'] . "-" . $rowDb['employee_name'] . "\">\n";
        $strResult .= "  <td nowrap>" . ($intStart + $intRows) . "&nbsp;</td>";
        $strResult .= "  <td nowrap><input type=hidden name=detailID$intRows value=\"" . $rowDb['id'] . "\">" . $rowDb['employee_id'] . "&nbsp;</td>";
        $strResult .= "  <td nowrap>" . $rowDb['employee_id_2'] . "&nbsp;</td>";
        $strResult .= "  <td nowrap>" . $rowDb['employee_name'] . "&nbsp;</td>";
        $strResult .= "  <td nowrap>" . $rowDb['division_code'] . "&nbsp;</td>";
        $strResult .= "  <td nowrap>" . $rowDb['branch_cost_center_code'] . "&nbsp;</td>";
        $strResult .= "  <td nowrap>" . $rowDb['branch_penugasan_code'] . "&nbsp;</td>";
        $strResult .= "  <td nowrap>" . $rowDb['position_code'] . "&nbsp;</td>";
        $strResult .= "  <td nowrap>" . $rowDb['functional_code'] . "&nbsp;</td>";
        $strResult .= "  <td>" . $rowDb['grade_code'] . "&nbsp;</td>";
        $strResult .= "  <td>" . $rowDb['join_date'] . "&nbsp;</td>";
        $strResult .= "  <td>" . $rowDb['resign_date'] . "&nbsp;</td>";
        //$strResult .= "  <td nowrap>" .$rowDb['family_status_code']. "&nbsp;</td>";
        //$strResult .= "  <td nowrap>$strGender &nbsp;</td>";
        //$strResult .= "<td align=center><input type=\"text\" name=\"dataBasic$intRows\" size=$intTextWidth maxlength=20 value=\"".$arrFix[$rowDb['id']]['basic_salary']."\" class=numeric $strDisable></td>\n";
        foreach ($arrMultivalFixAllowance as $strCode => $strName) {
            $strObjectName = "data" . getWords(str_replace("_allowance", "", $strCode));
            $strResult .= "<td><input type=\"text\" name=\"" . $strObjectName . $intRows . "\" size=$intTextWidth maxlength=20 value=\"" . $arrFix[$rowDb['id']][$strCode] . "\" class=numeric $strDisable></td>\n";
        }
        foreach ($arrMultivalDailyAllowance as $strCode => $strName) {
            $strObjectName = "data" . getWords(str_replace("_allowance", "", $strCode));
            $strResult .= "<td align=center><input type=\"text\" name=\"" . $strObjectName . $intRows . "\" size=$intTextWidth maxlength=20 value=\"" . $arrFix[$rowDb['id']][$strCode] . "\" class=numeric $strDisable></td>\n";
        }
        // ambil dta tunjangan yang dimiliki employee, jika ada
        $i = 0;
        foreach ($arrColumns AS $strKode => $strVal) {
            $strAmount = $arrFix[$rowDb['id']][$strKode];
            $i++;
            // cari dulu, apakah sudah ada di data atau belum
            $strResult .= "<td align=center><input type=\"text\" name=\"dataAllowance$i" . "_$intRows\" size=$intTextWidth maxlength=20 value=\"$strAmount\" class=numeric $strDisable></td>\n";
        }
        $strResult .= "</tr>\n";
    }
    if ($intRows > 0) {
        writeLog(ACTIVITY_VIEW, MODULE_PAYROLL, "", 0);
    }
    return $strResult;
} // showData
function getRecValue($arrParam, $arrOpr, $strEmp)
{
    global $arrColumns;
    global $arrTempVal;
    if (!is_array($arrParam)) {
        if (is_numeric($arrParam)) {
            return $arrParam;
        } else {
            return ($arrTempVal[$strEmp][$arrParam] == null) ? 0 : $arrTempVal[$strEmp][$arrParam];
        }
    } else {
        if (count($arrParam) == 1) {
            return getRecValue($arrParam[0], $arrOpr, $strEmp);
        } else {
            $strSlice = $arrParam[count($arrParam) - 1];
            $arrParam2 = array_slice($arrParam, 0, -1);
            return computeArithmatic(
                getRecValue($arrParam2, $arrOpr, $strEmp),
                getRecValue($strSlice, $arrOpr, $strEmp),
                $arrOpr[count($arrParam2) - 1]
            );
        }
    }
}

function computeArithmatic($a, $b, $opr)
{
    switch ($opr) {
        case "+":
            return $a + $b;
        case "-":
            return $a - $b;
        case "*":
            return $a * $b;
        case "/":
            return $a / $b;
            return 0;
    }
}

function getDefaultFix($db, $strDefault, &$arrFix, $strCondition = "", $strIDSource = "")
{
    global $strIDCompany, $ARRAY_ALLOWANCE_SET;
    global $arrSystemFixAllowance;
    /*if($strIDSource != "")
    {
      $strSQL  = "SELECT t1.id as t1_id, t2.* FROM hrd_employee as t1 LEFT JOIN (SELECT * FROM hrd_employee_basic_salary WHERE id_salary_set = $strIDSource) AS t2 ON t1.id = t2.id_employee WHERE 1=1 ";
      $strSQL .= $strCondition;
      $resTmp = $db->execute($strSQL);
      while ($rowTmp = $db->fetchrow($resTmp))
      {
        $arrFix[$rowTmp['t1_id']][$strDefault] = (isset($rowTmp[$strDefault]) && is_numeric($rowTmp[$strDefault])) ? $rowTmp[$strDefault] : 0;
      }
    }
    else */
    if ($strDefault == "basic_salary") {
        $strSQL = "SELECT t1.id as t1_id, t2.basic_salary ";
        $strSQL .= "FROM hrd_employee as t1 ";
        $strSQL .= "LEFT JOIN hrd_salary_grade AS t2 ON t1.grade_code = t2.grade_code ";
        $strSQL .= "WHERE 1=1 " . $strCondition;
        $resTmp = $db->execute($strSQL);
        while ($rowTmp = $db->fetchrow($resTmp)) {
            $arrFix[$rowTmp['t1_id']][$strDefault] = is_numeric($rowTmp[$strDefault]) ? $rowTmp[$strDefault] : 0;
        }
    } else {
        $strKey = substr($strDefault, 0, -11); //dikurangi string "i_allowance" 11 karakter
        $strIndex = substr(
            $strDefault,
            -11,
            1
        ); //dikurangi string "xxxi_allowance" 11 karakter dari belakang, sebanya 1 karakter
        $strSQL = "SELECT t1.id as t1_id,  ";
        $strSQL .= "t2." . $ARRAY_ALLOWANCE_SET[$strKey]['field_name'] . $strIndex . " ";
        $strSQL .= "FROM hrd_employee as t1 ";
        $strSQL .= "LEFT JOIN " . $ARRAY_ALLOWANCE_SET[$strKey]['table_name'] . " AS t2 ";
        $strSQL .= "ON t1.$strKey" . "_code = t2.$strKey" . "_code ";
        $strSQL .= "WHERE 1=1 " . $strCondition;
        $resTmp = $db->execute($strSQL);
        while ($rowTmp = $db->fetchrow($resTmp)) {
            $arrFix[$rowTmp['t1_id']][$strDefault] = is_numeric(
                $rowTmp[$ARRAY_ALLOWANCE_SET[$strKey]['field_name'] . $strIndex]
            ) ? $rowTmp[$ARRAY_ALLOWANCE_SET[$strKey]['field_name'] . $strIndex] : 0;
        }
    }
}

// fungsi untuk menyimpan data
function saveData($db)
{
    global $arrMultivalFixAllowance;
    global $arrMultivalDailyAllowance;
    global $strIDSalarySet;
    # Intialize all variables.
    $strSQLExec = '';
    $intTotalData = 0;
    $intColumn = 0;
    $arrColumn = [];
    $arrDataID = [];
    $arrFixAllowanceFields = [];
    $arrDailyAllowanceFields = [];
    $arrayAllowanceData = [];
    $strModifiedByID = $_SESSION['sessionUserID'];
    if (array_key_exists('totalData', $_REQUEST) === true and is_numeric($_REQUEST['totalData']) === true) {
        $intTotalData = $_REQUEST['totalData'];
    }
    if (array_key_exists('totalColumn', $_REQUEST) === true and is_numeric($_REQUEST['totalColumn']) === true) {
        $intColumn = $_REQUEST['totalColumn'];
    }
    # Get all allowance array data list.
    for ($i = 1; $i <= $intColumn; $i++) {
        $arrColumn[$i] = '';
        if (array_key_exists('dataAllowanceType' . $i, $_REQUEST) === true) {
            $arrColumn[$i] = $_REQUEST['dataAllowanceType' . $i];
        }
    }
    $parseAllowanceFields = function ($source) {
        foreach ($source as $strCode => $strName) {
            $fields[$strCode] = str_replace(
                'flt',
                'data',
                'flt' . getWords(str_replace('_allowance', '', $strCode))
            );
        }
        return $fields;
    };
    # Parse all allowance fields.
    $arrFixAllowanceFields = $parseAllowanceFields($arrMultivalFixAllowance);
    $arrDailyAllowanceFields = $parseAllowanceFields($arrMultivalDailyAllowance);
    $allAllowanceFields = array_merge($arrFixAllowanceFields, $arrDailyAllowanceFields);
    # Predefine generate allowance query closure anonymous function.
    $generateAllowanceQuery = function ($values, $fields, $sqlUpdate = '', $sqlInsertField = '', $sqlInsertValue = 0) {
        foreach ($fields as $codeName => $fieldName) {
            $fieldValue = 0;
            if (array_key_exists($fieldName . $i, $values) === true and is_numeric($values[$fieldName . $i]) === true) {
                $fieldValue = $values[$fieldName . $i];
            }
            $sqlUpdate .= $codeName . ' = ' . $fieldValue . ', ';
            $sqlInsertField .= $codeName . ', ';
            $sqlInsertValue .= $fieldValue . ', ';
        }
        return [
            'sqlUpdate'      => $sqlUpdate,
            'sqlInsertField' => $sqlInsertField,
            'sqlInsertValue' => $sqlInsertValue
        ];
    };
    for ($i = 1; $i <= $intTotalData; $i++) {
        # Initialize required local variables.
        $strDataID = '';
        if (array_key_exists('detailID' . $i, $_REQUEST) === true) {
            $strDataID = $_REQUEST['detailID' . $i];
        }
        if ($strDataID !== '') {
            # Save all basic salary and position allowance first.
            $fltBasic = 0;
            if (array_key_exists('dataAllowance2_' . $i, $_REQUEST) === true and
                is_numeric($_REQUEST['dataAllowance2_' . $i]) === true
            ) {
                $fltBasic = $_REQUEST['dataAllowance2_' . $i];
            }
            # Save all others allowance data.
            for ($j = 1; $j <= $intColumn; $j++) {
                $arrayAllowanceData[$strDataID][$arrColumn[$j]] = $_REQUEST['dataAllowance' . $j . "_" . $i];
            }
            $allowanceQuery[$strDataID] = $generateAllowanceQuery($_REQUEST, $allAllowanceFields);
            $arrDataID[] = $strDataID;
        }
    }
    # Create query for all other allowance data.
    foreach ($arrayAllowanceData as $idEmployee => $arrayAllowanceColumnAmount) {
        $strSQLExec .= 'DELETE FROM hrd_employee_allowance
                          WHERE id_salary_set = ' . $strIDSalarySet . ' AND id_employee = ' . $idEmployee . ';';
        foreach ($arrayAllowanceColumnAmount as $strColumnName => $fltAllowanceAmount) {
            $strSQLExec .= 'INSERT INTO hrd_employee_allowance (
                                created,
                                created_by,
                                id_employee,
                                allowance_code,
                                amount,
                                id_salary_set
                            ) VALUES (
                                now(),
                                ' . $strModifiedByID . ',
                                ' . $idEmployee . ',
                                \'' . $strColumnName . '\',
                                ' . $fltAllowanceAmount . ',
                                ' . $strIDSalarySet . '
                            );';
            #writeLog(ACTIVITY_ADD, MODULE_PAYROLL, "$strDataID", 0);
        }
    }
    # Build select query on one time.
    $sqlWhereDataID = 'id_employee in (' . implode(',', $arrDataID) . ')';
    $strSQL = 'SELECT id, id_employee FROM hrd_employee_basic_salary 
                WHERE ' . $sqlWhereDataID . ' AND id_salary_set = ' . $strIDSalarySet;
    $rows = $db->getRecordSet($strSQL, 'ASSOC');
    $existingEmployeeOnSalarySet = [];
    foreach ($rows as $row) {
        $existingEmployeeOnSalarySet[] = $row['id_employee'];
        $strSQLUpdateAllowance = $allowanceQuery[$row['id_employee']]['sqlUpdate'];
        $strSQLExec .= 'UPDATE hrd_employee_basic_salary
                        SET 
                            modified_by = ' . $strModifiedByID . ',' . $strSQLUpdateAllowance . 'basic_salary = 0
                        WHERE
                            id = ' . $row['id'] . ';';
        #writeLog(ACTIVITY_ADD, MODULE_PAYROLL, $row['id_employee'], 0);
    }
    $unExistsEmployeeOnSalarySet = array_diff($arrDataID, $existingEmployeeOnSalarySet);
    foreach ($unExistsEmployeeOnSalarySet as $employeeID) {
        $strSQLInsertAllowanceField = $allowanceQuery[$employeeID]['sqlInsertField'];
        $strSQLInsertAllowanceValue = $allowanceQuery[$employeeID]['sqlInsertValue'];
        $strSQLExec .= 'INSERT INTO hrd_employee_basic_salary (
                            created,
                            created_by,
                            id_employee,
                            ' . $strSQLInsertAllowanceField . ' basic_salary,
                            id_salary_set
                        ) VALUES(
                            now(),
                            \'' . $strModifiedByID . '\',
                            \'' . $employeeID . '\',
                            \'' . $strSQLInsertAllowanceValue . '\',
                            \'' . $strIDSalarySet . '\'
                        );';
        #writeLog(ACTIVITY_ADD, MODULE_PAYROLL, $employeeID, 0);
    }
    $resExec = $db->execute($strSQLExec);
}

function getHeaderString()
{
    global $strDisable;
    global $arrMultivalFixAllowance;
    global $arrMultivalDailyAllowance;
    $strResult = "";
    foreach ($arrMultivalFixAllowance as $strCode => $strName) {
        $strCaption = strtoupper(getSetting($strCode . "_name"));
        $strResult .= "<td width=\"65px\" nowrap valign=\"bottom\">$strCaption<br>&nbsp;
              <input type=submit name=\"btnDefault\" value=\"Get Default\" style=\"width:65px;font-size:7pt\" onClick=\"document.formData.dataDefault.value = '$strCode'\" $strDisable></td>";
    }
    foreach ($arrMultivalDailyAllowance as $strCode => $strName) {
        $strCaption = strtoupper(getSetting($strCode . "_name"));
        $strResult .= "<td width=\"65px\" nowrap valign=\"bottom\">$strCaption<br>&nbsp;
              <input type=submit name=\"btnDefault\" value=\"Get Default\" style=\"width:65px;font-size:7pt\" onClick=\"document.formData.dataDefault.value = '$strCode'\" $strDisable></td>";
    }
    return $strResult;
}

// fungsi untuk mengambil daftar tunjangan, selain tunjangan jabatan, simpan ke kolom array
function getAllowanceData($db)
{
    global $strDisable;
    global $arrColumns;
    global $strDataColumn;
    global $strSpan1;
    global $strSpan2;
    global $strAllowanceList;
    global $strDefaultWidthPx;
    global $arrMultivalFixAllowance;
    global $arrMultivalDailyAllowance;
    $strAllowanceList = "<select name=dataAllowanceImport style=\"width:$strDefaultWidthPx\">\n";
    //$strAllowanceList .= "<option value='BASIC'>".strtoupper(getWords("basic salary"))."</option>\n";
    $strSQL = "SELECT code,name,amount FROM hrd_allowance_type WHERE active = 't' order by seq";
    $resDb = $db->execute($strSQL);
    $i = 0;
    while ($rowDb = $db->fetchrow($resDb)) {
        $i++;
        $strDataColumn .= "<td align=center nowrap valign=\"bottom\"><input type=hidden name=dataAllowanceType$i value=\"" . $rowDb['code'] . "\"> ";
        $arrTemp = split("_", $rowDb['code']);
        if (count($arrTemp) > 1) {
            $strDataColumn .= str_replace(["_", " "], "<br>", strtoupper($rowDb['name'])) . "<br>";
        } else {
            $strDataColumn .= strtoupper($rowDb['name']) . "<br>&nbsp;<br>";
        }
        if (is_numeric($rowDb['amount'])) {
            $strDataColumn .= "&nbsp;<br><input type=\"submit\" name=\"btnDefault" . $rowDb['code'] . "\" value=\"Get Default\"  style=\"width:75px;font-size:7pt\" onclick=\"document.formData.dataDefault2.value = '" . $rowDb['code'] . "'\" $strDisable></td>\n";
        } else {
            $strDataColumn .= "&nbsp;<br><input type=\"submit\" name=\"btnDefault" . $rowDb['code'] . "\" value=\"Get Default\"  style=\"width:75px;font-size:7pt\" onclick=\"document.formData.dataDefault3.value = '" . $rowDb['code'] . "'\" $strDisable></td>\n";
        }
        $arrColumns[$rowDb['code']] = $rowDb['amount'];
        $strAllowanceList .= "<option value='" . $rowDb['code'] . "'>" . strtoupper($rowDb['code']) . "</option>\n";
    }
    $strSpan1 += $i;
    $strSpan2 += $i;
    $strAllowanceList .= "</select>\n";
    return true;
} // getAllowanceData
//----------------------------------------------------------------------
//----MAIN PROGRAM -----------------------------------------------------
$strInfo = "";
$db = new CdbClass;
if ($db->connect()) {
    getUserEmployeeInfo($db);
    // ------ AMBIL DATA KRITERIA -------------------------
    $strIDSalarySet = (isset($_SESSION['sessionFilterIDSalarySet'])) ? $_SESSION['sessionFilterIDSalarySet'] : "";
    $strDataEmployee = (isset($_SESSION['sessionFilterEmployee'])) ? $_SESSION['sessionFilterEmployee'] : "";
    $strDataPosition = (isset($_SESSION['sessionFilterPosition'])) ? $_SESSION['sessionFilterPosition'] : "";
    $strDataCurrency = (isset($_SESSION['sessionFilterCurrency   '])) ? $_SESSION['sessionFilterCurrency'] : "";
    $strDataGrade = (isset($_SESSION['sessionFilterGrade   '])) ? $_SESSION['sessionFilterGrade'] : "";
    $strDataFamilyStatus = (isset($_SESSION['sessionFilterFamilyStatus'])) ? $_SESSION['sessionFilterFamilyStatus'] : "";
    $strDataBranch = (isset($_SESSION['sessionFilterBranch'])) ? $_SESSION['sessionFilterBranch'] : "";
    $strDataDivision = (isset($_SESSION['sessionFilterDivision'])) ? $_SESSION['sessionFilterDivision'] : "";
    $strDataDepartment = (isset($_SESSION['sessionFilterDepartment'])) ? $_SESSION['sessionFilterDepartment'] : "";
    $strDataSection = (isset($_SESSION['sessionFilterSection'])) ? $_SESSION['sessionFilterSection'] : "";
    $strDataSubSection = (isset($_SESSION['sessionFilterSubSection'])) ? $_SESSION['sessionFilterSubSection'] : "";
    $strDataCostCenter = (isset($_SESSION['sessionFilterCostCenter'])) ? $_SESSION['sessionFilterCostCenter'] : "";
    $strDataStatus = (isset($_SESSION['sessionFilterEmployeeStatus'])) ? $_SESSION['sessionFilterEmployeeStatus'] : "";
    $strDataActive = (isset($_SESSION['sessionFilterActive'])) ? $_SESSION['sessionFilterActive'] : "";
    if (isset($_REQUEST['dataIDSalarySet'])) {
        $strIDSalarySet = $_REQUEST['dataIDSalarySet'];
    }
    if (isset($_REQUEST['dataEmployee'])) {
        $strDataEmployee = $_REQUEST['dataEmployee'];
    }
    if (isset($_REQUEST['dataPosition'])) {
        $strDataPosition = $_REQUEST['dataPosition'];
    }
    if (isset($_REQUEST['dataGrade'])) {
        $strDataGrade = $_REQUEST['dataGrade'];
    }
    if (isset($_REQUEST['dataCurrency'])) {
        $strDataCurrency = $_REQUEST['dataCurrency'];
    }
    if (isset($_REQUEST['dataFamilyStatus'])) {
        $strDataFamilyStatus = $_REQUEST['dataFamilyStatus'];
    }
    if (isset($_REQUEST['dataDivision'])) {
        $strDataDivision = $_REQUEST['dataDivision'];
    }
    if (isset($_REQUEST['dataDepartment'])) {
        $strDataDepartment = $_REQUEST['dataDepartment'];
    }
    if (isset($_REQUEST['dataBranch'])) {
        $strDataBranch = $_REQUEST['dataBranch'];
    }
    if (isset($_REQUEST['dataSection'])) {
        $strDataSection = $_REQUEST['dataSection'];
    }
    if (isset($_REQUEST['dataSubSection'])) {
        $strDataSubSection = $_REQUEST['dataSubSection'];
    }
    if (isset($_REQUEST['dataCostCenter'])) {
        $strDataCostCenter = $_REQUEST['dataCostCenter'];
    }
    if (isset($_REQUEST['dataStatus'])) {
        $strDataStatus = $_REQUEST['dataStatus'];
    }
    if (isset($_REQUEST['dataActive'])) {
        $strDataActive = $_REQUEST['dataActive'];
    }
    // default selalu ambil yang aktif saja
    //if($strDataActive == "") $strDataActive = 1;
    // simpan dalam session
    $_SESSION['sessionFilterIDSalarySet'] = $strIDSalarySet;
    $_SESSION['sessionFilterEmployee'] = $strDataEmployee;
    $_SESSION['sessionFilterPosition'] = $strDataPosition;
    $_SESSION['sessionFilterGrade'] = $strDataGrade;
    $_SESSION['sessionFilterCurrency'] = $strDataCurrency;
    $_SESSION['sessionFilterFamilyStatus'] = $strDataFamilyStatus;
    $_SESSION['sessionFilterDivision'] = $strDataDivision;
    $_SESSION['sessionFilterBranch'] = $strDataBranch;
    $_SESSION['sessionFilterDepartment'] = $strDataDepartment;
    $_SESSION['sessionFilterSection'] = $strDataSection;
    $_SESSION['sessionFilterSubSection'] = $strDataSubSection;
    $_SESSION['sessionFilterCostCenter'] = $strDataCostCenter;
    $_SESSION['sessionFilterEmployeeStatus'] = $strDataStatus;
    $_SESSION['sessionFilterActive'] = $strDataActive;
    $intCurrPage = (isset($_REQUEST['dataPage'])) ? $_REQUEST['dataPage'] : 1;
    if (!is_numeric($intCurrPage)) {
        $intCurrPage = 1;
    }
    scopeData(
        $strDataEmployee,
        $strDataSubSection,
        $strDataSection,
        $strDataDepartment,
        $strDataDivision,
        $_SESSION['sessionUserRole'],
        $arrUserInfo
    );
    //    $strReadonly = "";
    $strReadonly = (scopeCBDataEntry($strDataEmployee, $_SESSION['sessionUserRole'], $arrUserInfo)) ? "readonly" : "";
    $strDisable = (scopeCBDataEntry($strDataEmployee, $_SESSION['sessionUserRole'], $arrUserInfo)) ? "disabled" : "";
    //    $strDisable  = "";
    // ------------ GENERATE KRITERIA QUERY,JIKA ADA -------------
    $strKriteria = "";
    if ($strDataEmployee != "") {
        $strKriteria .= "AND t1.employee_id = '$strDataEmployee' ";
    }
    if ($strDataSection != "") {
        $strKriteria .= "AND t1.section_code = '$strDataSection' ";
    }
    if ($strDataSubSection != "") {
        $strKriteria .= "AND t1.sub_section_code = '$strDataSubSection' ";
    }
    if ($strDataCostCenter != "") {
        $strKriteria .= "AND t1.branch_cost_center_code = '$strDataCostCenter' ";
    }
    if ($strDataActive != "") {
        $strKriteria .= "AND t1.active = '$strDataActive' ";
    }
    if ($strDataDepartment != "") {
        $strKriteria .= "AND t1.department_code = '$strDataDepartment' ";
    }
    if ($strDataDivision != "") {
        $strKriteria .= "AND t1.division_code = '$strDataDivision' ";
    }
    if ($strDataPosition != "") {
        $strKriteria .= "AND t1.position_code = '$strDataPosition' ";
    }
    if ($strDataCurrency != "") {
        $strKriteria .= "AND t1.salary_currency = '$strDataCurrency' ";
    }
    if ($strDataGrade != "") {
        $strKriteria .= "AND t1.grade_code = '$strDataGrade' ";
    }
    if ($strDataStatus != "") {
        $strKriteria .= "AND t1.employee_status = '$strDataStatus' ";
    }
    if ($strDataFamilyStatus != "") {
        $strKriteria .= "AND t1.family_status_code = '$strDataFamilyStatus' ";
    }
    if ($strDataBranch != "") {
        $strKriteria .= "AND t1.branch_code = '$strDataBranch' ";
    }
    $strKriteria .= $strKriteriaCompany;
    $arrMultivalFixAllowance = getFixAllowance($db, "t" /*multival*/, "f" /*daily*/); //salary_func.php
    $arrMultivalDailyAllowance = getFixAllowance($db, "t" /*multival*/, "t" /*daily*/); //salary_func.php
    // colspan untuk colum allowance
    $strSpan1 = count($arrMultivalFixAllowance) + count($arrMultivalDailyAllowance);
    // colspan untk bagian paging
    $strSpan2 = $strSpan1 + 10;
    //ambil header untuk tunjangan fix
    $strHeader = getHeaderString();
    //ambil header untuk tunjangan lain2
    getAllowanceData($db);
    // hapus data jika ada perintah
    if (isset($_REQUEST['btnSave'])) {
        if ($bolCanEdit) {
            saveData($db);
            $strError = "data saved";
            $bolError = false;
        } else {
            $bolError = true;
            $strError = getWords("Sorry, you don't have authotrity to modify any data on this page");
        }
        $strMessage = $strError;
        $strMsgClass = ($bolError) ? "class=bgError" : "class=bgOK";
    } else if (isset($_REQUEST['btnImport'])) {
        if ($bolCanEdit) {
            importData($db);
            $_REQUEST['btnShow'] = "Show";
        }
    }
    $bolShow = (isset($_REQUEST['btnShow']) || isset($_REQUEST['btnSave']) || isset($_REQUEST['dataDefault']));
    $tblBasicSalarySet = new cHrdBasicSalarySet();
    $arrBasicSalarySet = $tblBasicSalarySet->findAll(
        $strKriteriaCompany,
        "id, start_date, note, id_company, id_salary_set_source",
        "",
        null,
        1,
        "id"
    );
    foreach ($arrBasicSalarySet AS $keySet => $arrSet) {
        $arrSetSource[$keySet] = $arrSet['start_date'] . " - " . printCompanyName(
                $arrSet['id_company']
            ) . " - " . $arrSet['note'];
    }
    if (isset($arrSetSource[$strIDSalarySet])) {
        $strDataStartDate = $arrBasicSalarySet[$strIDSalarySet]['start_date'];
        $strDataNote = $arrBasicSalarySet[$strIDSalarySet]['note'];
        $strIDCompany = $arrBasicSalarySet[$strIDSalarySet]['id_company'];
        $strIDSalarySetSource = $arrBasicSalarySet[$strIDSalarySet]['id_salary_set_source'];
    } else {
        $strDataNote = $strDataStartDate = "";
        $bolShow = false;
    }
    krsort($arrSetSource);
    $strInputStartDate = getComboFromArray(
        $arrSetSource,
        "dataIDSalarySet",
        $strIDSalarySet,
        "style=\"width:$strDefaultWidthPx\""
    );
    if ($bolCanView) {
        if ($bolShow) {
            // tampilkan hanya jika ada permintaan dan data tanggalnya tepat
            if (isset($_REQUEST['dataDefault'])) {
                $strDataDetail = getData(
                    $db,
                    $intTotalData,
                    $strIDSalarySet,
                    $strIDSalarySetSource,
                    $strKriteria,
                    $intCurrPage,
                    $_REQUEST['dataDefault'],
                    $_REQUEST['dataDefault2'],
                    $_REQUEST['dataDefault3']
                );
            } else {
                $strDataDetail = getData(
                    $db,
                    $intTotalData,
                    $strIDSalarySet,
                    $strIDSalarySetSource,
                    $strKriteria,
                    $intCurrPage
                );
            }
        } else {
            $strDataDetail = "";
        }
    } else {
        showError("view_denied");
        $strDataDetail = "";
    }
    // generate data hidden input dan element form input
    $strInputEmployee = "<input type=text name=dataEmployee id=dataEmployee maxlength=30 value=\"" . $strDataEmployee . "\" style=\"width:$strDefaultWidthPx\" $strReadonly>";
    $strInputSection = getSectionList(
        $db,
        "dataSection",
        $strDataSection,
        $strEmptyOption,
        "",
        "style=\"width:$strDefaultWidthPx\" $strDisable"
    );
    $strInputSubSection = getSubSectionList(
        $db,
        "dataSubSection",
        $strDataSubSection,
        $strEmptyOption,
        "",
        "style=\"width:$strDefaultWidthPx\" $strDisable"
    );
    $strInputCostCenter = getCostCenterList(
        $db,
        "dataCostCenter",
        $strDataCostCenter,
        $strEmptyOption,
        "",
        "style=\"width:$strDefaultWidthPx\" $strDisable"
    );
    $strInputDepartment = getDepartmentList(
        $db,
        "dataDepartment",
        $strDataDepartment,
        $strEmptyOption,
        "",
        "style=\"width:$strDefaultWidthPx\" $strDisable"
    );
    $strInputBranch = getBranchList(
        $db,
        "dataBranch",
        $strDataBranch,
        $strEmptyOption,
        "",
        "style=\"width:$strDefaultWidthPx\" "
    );
    $strInputDivision = getDivisionList(
        $db,
        "dataDivision",
        $strDataDivision,
        $strEmptyOption,
        "",
        "style=\"width:$strDefaultWidthPx\" $strDisable"
    );
    $strInputActive = getEmployeeActiveList("dataActive", $strDataActive, $strEmptyOption, $strDisable);
    //handle user company-access-right
    $strInputCompany = getCompanyList(
        $db,
        "dataCompany",
        $strDataCompany,
        $strEmptyOption2,
        $strKriteria2,
        "style=\"width:$strDefaultWidthPx\" $strDisable"
    );
    $strInputPosition = getPositionList(
        $db,
        "dataPosition",
        $strDataPosition,
        $strEmptyOption,
        "",
        "style=\"width:$strDefaultWidthPx\" $strDisable"
    );
    $strInputCurrency = getComboFromArray(
        $ARRAY_CURRENCY,
        "dataCurrency",
        $strDataCurrency,
        $strEmptyOption,
        " style=\"width:$strDefaultWidthPx\""
    );
    $strInputGrade = getSalaryGradeList(
        $db,
        "dataGrade",
        $strDataGrade,
        $strEmptyOption,
        "",
        "style=\"width:$strDefaultWidthPx\" $strDisable"
    );
    $strInputEmployeeStatus = getEmployeeStatusList(
        "dataStatus",
        $strDataStatus,
        $strEmptyOption,
        "style=\"width:$strDefaultWidthPx\" $strDisable"
    );
    $strInputFamilyStatus = getFamilyStatusList(
        $db,
        "dataFamilyStatus",
        $strDataFamilyStatus,
        $strEmptyOption,
        "",
        "style=\"width:$strDefaultWidthPx\" $strDisable"
    );
    $strBtnSave = ($bolCanEdit) ? "<input type=submit name=\"btnSave\" value=\"Save\">" : "";
    $strHidden .= "<input type=hidden name=dataIDSalarySet value=\"$strIDSalarySet\">";
    $strHidden .= "<input type=hidden name=dataPosition value=\"$strDataPosition\">";
    $strHidden .= "<input type=hidden name=dataGrade value=\"$strDataGrade\">";
    $strHidden .= "<input type=hidden name=dataFamilyStatus value=\"$strDataFamilyStatus\">";
    $strHidden .= "<input type=hidden name=dataSection value=\"$strDataSection\">";
    $strHidden .= "<input type=hidden name=dataSubSection value=\"$strDataSubSection\">";
    $strHidden .= "<input type=hidden name=dataDepartment value=\"$strDataDepartment\">";
    $strHidden .= "<input type=hidden name=dataDivision value=\"$strDataDivision\">";
    $strHidden .= "<input type=hidden name=dataBranch value=\"$strDataBranch\">";
    $strHidden .= "<input type=hidden name=\"dataStatus\" value=\"$strDataStatus\">";
    $strHidden .= "<input type=hidden name=dataActive value=\"$strDataActive\">";
    $strHidden .= "<input type=hidden name=dataEmployee value=\"$strDataEmployee\">";
    $strHidden .= "<input type=hidden name=dataCompany value=\"$strDataCompany\">";
    $strHidden .= "<input type=hidden name=dataPage value=\"$intCurrPage\">";
}
$tbsPage = new clsTinyButStrong;
//write this variable in every page
$strPageTitle = $dataPrivilege['menu_name'];
if (trim($dataPrivilege['icon_file']) == "") {
    $pageIcon = "../images/icons/blank.gif";
} else {
    $pageIcon = "../images/icons/" . $dataPrivilege['icon_file'];
}
$strTemplateFile = getTemplate(str_replace(".php", ".html", basename($_SERVER['PHP_SELF'])));
//------------------------------------------------
//Load Master Template
$tbsPage->LoadTemplate("../templates/master.html");
$tbsPage->Show();
?>
