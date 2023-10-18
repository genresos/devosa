<?php
  include_once('../global/session.php');
  include_once('global.php');
  include_once('../global/employee_function.php');
  include_once('../global/common_data.php');
  include_once('../includes/datagrid2/datagrid.php');
  include_once('../includes/form2/form2.php');
  include_once('../classes/hrd/hrd_employee.php');
  include_once('../classes/hrd/hrd_medical_quota_primary.php');
  include_once('../classes/hrd/hrd_medical_quota_secondary.php');


  $dataPrivilege = getDataPrivileges("medical_quota.php", $bolCanView, $bolCanEdit, $bolCanDelete, $bolCanApprove, $bolCanCheck, $bolCanAcknowledge, $bolCanApprove2);
  if (!$bolCanView) die(accessDenied($_SERVER['HTTP_REFERER']));

  $db = new CdbClass;
  $strWordsMedicalData = getWords("medical data");
  $strWordsEmployeeQuotaList  = getWords("employee quota list");
  $strWordsInputMedicalClaim = getWords("input claim");
  $strWordsMedicalClaimList = getWords("claim list");
  $strWordsEmployeeMedicalReport = getWords("employee medical report");

  //----MAIN PROGRAM -----------------------------------------------------

  if ($db->connect())
  {
    getUserEmployeeInfo();
    $arrUserList = getAllUserInfo($db);
    $strYear = (getPostValue('dataYear') == "") ? date("Y") : getPostValue('dataYear');
    $strDataCompany = (getPostValue('dataCompany') == "") ? 1 : getPostValue('dataCompany') ;

    scopeData($strDataEmployee, $strDataSubSection, $strDataSection, $strDataDepartment, $strDataDivision, $_SESSION['sessionUserRole'], $arrUserInfo);
    $strReadonly = (scopeCBDataEntry($strDataEmployee, $_SESSION['sessionUserRole'], $arrUserInfo)) ? "readonly" : "";
    $f = new clsForm("formFilter", 3, "1024px", "");
    $f->caption = strtoupper("filter data");

    $f->addSelect(getWords("year"), "dataYear", getDataListYear($strYear), array("style" => "width:$strDefaultWidthPx"), "", false);

    $f->addInputAutoComplete(getWords("employee ID"), "dataEmployeeID", getDataEmployee($strDataEmployee), "style=width:$strDefaultWidthPx ".$strReadonly, "string", false);
    $f->addLabelAutoComplete("", "dataEmployeeID", "");
    $f->addLiteral("","","");
    $f->addLiteral("","","");

    $f->addSelect(getWords("branch"), "dataBranch", getDataListBranch("", true), array("style" => "width:$strDefaultWidthPx"), "", false);

    $f->addSelect(getWords("level"), "dataPosition", getDataListPosition("", true), array("style" => "width:$strDefaultWidthPx"), "", false);
    $f->addSelect(getWords("grade"), "dataGrade", getDataListSalaryGrade("", true), array("style" => "width:$strDefaultWidthPx"), "", false);
    $f->addSelect(getWords("status"), "dataEmployeeStatus", getDataListEmployeeStatus("", true, array("value" => "", "text" => "", "selected" => true)), array("style" => "width:$strDefaultWidthPx"), "", false);

    $f->addSelect(getWords("active"), "dataActive", getDataListEmployeeActive("", true, array("value" => "", "text" => "", "selected" => true)), array("style" => "width:$strDefaultWidthPx"), "", false);
    $f->addSelect(getWords("company"), "dataCompany", getDataListCompany($strDataCompany, $bolCompanyEmptyOption, $arrCompanyEmptyData, $strKriteria2), array("style" => "width:$strDefaultWidthPx"), "", false);
    $f->addSelect(getWords("division"), "dataDivision", getDataListDivision($strDataDivision, true), array("style" => "width:$strDefaultWidthPx"), "", false, ($ARRAY_DISABLE_GROUP['division'] == ""));
    $f->addSelect(getWords("department"), "dataDepartment", getDataListDepartment($strDataDepartment, true), array("style" => "width:$strDefaultWidthPx"), "", false, ($ARRAY_DISABLE_GROUP['department'] == ""));
    $f->addSelect(getWords("section"), "dataSection", getDataListSection($strDataSection, true), array("style" => "width:$strDefaultWidthPx"), "", false, ($ARRAY_DISABLE_GROUP['section'] == ""));
    $f->addSelect(getWords("sub section"), "dataSubSection", getDataListSubSection($strDataSubSection, true), array("style" => "width:$strDefaultWidthPx"), "", false, ($ARRAY_DISABLE_GROUP['sub_section'] == ""));

    $f->addSubmit("btnShow", getWords("show"), "", true, true, "", "", "");
    $formFilter = $f->render();

    getData($db, $strDataCompany, $strYear);
  }

  function getData($db, $strDataCompany, $strYear)
  {
    global $dataPrivilege, $bolCanEdit, $bolCanDelete, $bolCanApprove, $bolCanCheck, $bolCanAcknowledge;
    global $f;
    global $myDataGrid;
    global $DataGrid;
    global $strKriteriaCompany;
    global $ARRAY_MEDICAL_TREATMENT_GROUP;

    // cari data master medical type untuk generate header tabel plafon
    //$strSQL  = "SELECT * FROM hrd_medical_type ";
    // uddin on takenaka: ubah query tanpa tipe outpatient
    $strSQL  = "SELECT * FROM hrd_medical_type where type >0";
    $resDb = $db->execute($strSQL);
    while ($rowDb = $db->fetchrow($resDb)) {
      $arrMedGroup[$rowDb['type']][$rowDb['id']] = $rowDb;
    }
    $formFilter = $f->render();
    $strKriteria = "";
    $strFormatter = (isset($_POST['btnExportXLS'])) ? "" : "formatNumber()";

  // GENERATE CRITERIA

    $arrData = $f->getObjectValues();
    if ($arrData['dataEmployeeID']!= "") {
      $strKriteria .= "AND employee_id = '".$arrData['dataEmployeeID']."' ";
    }
    if ($arrData['dataPosition']!= "") {
      $strKriteria .= "AND position_code = '".$arrData['dataPosition']."' ";
    }
    if ($arrData['dataBranch']!= "") {
      $strKriteria .= "AND branch_code = '".$arrData['dataBranch']."' ";
    }
    if ($arrData['dataGrade']!= "") {
      $strKriteria .= "AND t0.grade_code = '".$arrData['dataGrade']."' ";
    }
    if ($arrData['dataEmployeeStatus']!= "") {
      $strKriteria .= "AND t0.employee_status = '".$arrData['dataEmployeeStatus']."' ";
    }
    if ($arrData['dataActive']!= "") {
      $strKriteria .= "AND active = '".$arrData['dataActive']."' ";
    }
    if ($arrData['dataDivision']!= "") {
      $strKriteria .= "AND division_code = '".$arrData['dataDivision']."' ";
    }
    if ($arrData['dataDepartment']!= "") {
      $strKriteria .= "AND department_code = '".$arrData['dataDepartment']."' ";
    }
    if ($arrData['dataSection']!= "") {
      $strKriteria .= "AND section_code = '".$arrData['dataSection']."' ";
    }
    if ($arrData['dataSubSection']!= "") {
      $strKriteria .= "AND sub_section_code = '".$arrData['dataSubSection']."' ";
    }

    $strKriteria .= $strKriteriaCompany;



    $myDataGrid = new cDataGrid("formData","DataGrid1", "100%", "100%", false, false, false, false);
    $myDataGrid->caption = getWords(strtoupper(vsprintf(getWords("list of %s"), getWords($dataPrivilege['menu_name']))));
    $myDataGrid->pageSortBy = "employee_name";
    //$myDataGrid->setCriteria($strKriteria);

    $myDataGrid->setAJAXCallBackScript(basename($_SERVER['PHP_SELF']));
    $myDataGrid->setPageLimit("all");
	if (!isset($_REQUEST['btnExportXLS']))
    $myDataGrid->addColumnCheckbox(new DataGrid_Column("chkID", "id_employee", array("rowspan" => 2, 'width' => 30), array('align'=>'center', 'nowrap' => 'nowrap')));
    $myDataGrid->addColumnNumbering(new DataGrid_Column("No", "", array('rowspan' => '2', 'width'=>'30'), array('nowrap'=>'')));
    $myDataGrid->addColumn(new DataGrid_Column(getWords("employee id"), "employee_id", array('nowrap'=>'', 'rowspan' => '2', ), array('nowrap' => '')));
    $myDataGrid->addColumn(new DataGrid_Column(getWords("employee name"), "employee_name", array('nowrap'=>'', 'rowspan' => '2', 'style' => '250'), array('nowrap' => '')));
    $myDataGrid->addColumn(new DataGrid_Column(getWords("grade"), "grade_code", array('rowspan' => '2', 'width' => '25'), array('nowrap' => '')));
    $myDataGrid->addColumn(new DataGrid_Column(getWords("family status"), "family_status", array('rowspan' => '2', 'width' => '25'), array('nowrap' => '')));
    $myDataGrid->addColumn(new DataGrid_Column(getWords("#of Service"), "num_service", array('rowspan' => '2', 'width' => '25'), array('nowrap' => '')));
    //$myDataGrid->addColumn(new DataGrid_Column(getWords("department"), "department_code", array('rowspan' => '2', 'width' => '75'), array('align' => 'center'), true, true, "", "getDepartmentName()", "", true));

    $myDataGrid->addSpannedColumn(getWords("outpatient"), 6);

    $myDataGrid->addColumn(new DataGrid_Column(getWords("main quota"), "amount", array(), array('align' => 'center'), true, true, "", ($strFormatter), "", true));
    $myDataGrid->addColumn(new DataGrid_Column(getWords("add. quota"), "amount1", array(), array('align' => 'center'), true, true, "", ($strFormatter), "", true));
    $myDataGrid->addColumn(new DataGrid_Column(getWords("add. quota"), "amount2", array(), array('align' => 'center'), true, true, "", ($strFormatter), "", true));
    $myDataGrid->addColumn(new DataGrid_Column(getWords("total quota"), "total_quota", array(), array('align' => 'center'), true, true, "", ($strFormatter), "", true));
    $myDataGrid->addColumn(new DataGrid_Column(getWords("claimed"), "main_claimed", array(), array('align' => 'center'), true, true, "", ($strFormatter), "", true));
    $myDataGrid->addColumn(new DataGrid_Column(getWords("remaining"), "main_remaining", array(), array('align' => 'center'), true, true, "", ($strFormatter), "", true));


    foreach($arrMedGroup AS $idMedGroup => $arrMedType)
    {
        foreach($arrMedType AS $idMedType => $arrMedDetail)
        {
          $myDataGrid->addSpannedColumn(getWords($ARRAY_MEDICAL_TREATMENT_GROUP[$idMedGroup])."-".getWords($arrMedDetail['code']), 3);
          $myDataGrid->addColumn(new DataGrid_Column(getWords("quota"), "quota_".$idMedType, array(), array('align' => 'center'), true, true, "", ($strFormatter), "", true));
          if ($arrMedDetail['per_person']=="t"){
              //$myDataGrid->addColumn(new DataGrid_Column(getWords("claimed"), "claimed_".$idMedType, array(), array('align' => 'center'), true, true, "",($strFormatter), "", true));
              $myDataGrid->addColumn(new DataGrid_Column(getWords("claimed"), "claimed_".$idMedType, array(), array('align' => 'center'), true, true, "","", "", true));
              $myDataGrid->addColumn(new DataGrid_Column(getWords("remaining"), "remaining_".$idMedType, array(), array('align' => 'center'), true, true, "", "", "", true));
          }else{
              $myDataGrid->addColumn(new DataGrid_Column(getWords("claimed"), "claimed_".$idMedType, array(), array('align' => 'center'), true, true, "",($strFormatter), "", true));
              $myDataGrid->addColumn(new DataGrid_Column(getWords("remaining"), "remaining_".$idMedType, array(), array('align' => 'center'), true, true, "", ($strFormatter), "", true));
          }
      }
    }

    foreach($arrData AS $key => $value)
    {
      $myDataGrid->strAdditionalHtml .= generateHidden($key, $value, "");
    }

    if($bolCanEdit)
      $myDataGrid->addSpecialButton("btnSave", "btnSave", "submit", getWords("calculate"), "onClick=\"javascript:return myClient.confirmSave();\"", "saveData()");

    $myDataGrid->addButtonExportExcel("Export Excel", $dataPrivilege['menu_name'].".xls", getWords($dataPrivilege['menu_name']));

    $myDataGrid->getRequest();
    //--------------------------------



    //get Data and set to Datagrid's DataSource by set the data binding (bind method)



    $strSQL  = "SELECT t0.id as id_employee, t0.employee_id, t0.id_company, t0.employee_name, t0.gender,  date_part('year',age(t0.join_date)) as num_service,
                t1.inspouse, t1.grade_code, t1.employee_status, t1.family_status,
                t1.amount, t1.amount1, t1.amount2, t1.amount + t1.amount1 + t1.amount2 AS total_quota, t1.note,
                SUM(t4.approved_cost) as main_claimed
                FROM hrd_employee AS t0
                LEFT JOIN (SELECT * FROM hrd_medical_quota_primary WHERE quota_year = $strYear) AS t1 ON t0.id = t1.id_employee
                LEFT JOIN
                (SELECT id_employee, id_medical_type, approved_cost, claim_date, status
                FROM hrd_medical_claim AS t2
                LEFT JOIN hrd_medical_claim_master AS t3 ON t2.id_master = t3.id
                WHERE status <> ".REQUEST_STATUS_NEW." AND status <> ".REQUEST_STATUS_DENIED."
                AND EXTRACT(year FROM claim_date) = '$strYear' AND id_medical_type = -1)
                AS t4 ON t4.id_employee = t0.id
                WHERE 1=1 $strKriteria
                GROUP BY  t0.id , t0.employee_id, t0.id_company, t0.employee_name, t0.gender,
                t1.inspouse, t1.grade_code, t1.employee_status, t1.family_status,
                t1.amount, t1.amount1, t1.amount2, total_quota, t1.note
                ";

    $strSQLCOUNT  = "SELECT COUNT(*) AS total FROM hrd_employee AS t0
                LEFT JOIN (SELECT * FROM hrd_medical_quota_primary WHERE quota_year = $strYear) AS t2 ON t0.id = t2.id_employee
                WHERE 1=1 $strKriteria
                ";
    $myDataGrid->totalData = $myDataGrid->getTotalData($db, $strSQLCOUNT);
    $dataset = $myDataGrid->getData($db, $strSQL);

    $strSQL  = "SELECT t0.id AS id_employee, t1.id_medical_type, t1.amount, SUM(t4.approved_cost) as claimed
                FROM hrd_employee AS t0
                LEFT JOIN
                (SELECT id_employee, id_medical_type, amount FROM hrd_medical_quota_secondary  WHERE quota_year = $strYear) AS t1 ON t0.id = t1.id_employee
                LEFT JOIN
                (SELECT id_employee, id_medical_type, approved_cost, claim_date, status
                FROM hrd_medical_claim AS t2
                LEFT JOIN hrd_medical_claim_master AS t3 ON t2.id_master = t3.id
                WHERE status <> ".REQUEST_STATUS_NEW." AND status <> ".REQUEST_STATUS_CHECKED." AND status <> ".REQUEST_STATUS_DENIED." AND EXTRACT(year FROM claim_date) = '$strYear' )
                AS t4 ON t4.id_employee = t0.id AND t4.id_medical_type = t1.id_medical_type
                WHERE 1=1 $strKriteria
                GROUP BY t0.id, t1.id_medical_type, t1.amount";

  $strSQL  = "SELECT t0.id AS id_employee, t1.id_medical_type, t1.amount, SUM(t4.approved_cost) as claimed,t1.type,t1.per_person
                FROM hrd_employee AS t0
                LEFT JOIN
                (SELECT a.id_employee, a.id_medical_type, a.amount ,b.type,b.per_person
                      FROM hrd_medical_quota_secondary a, hrd_medical_type b
                       WHERE a.quota_year = $strYear and b.id=a.id_medical_type
                ) AS t1 ON t0.id = t1.id_employee
                LEFT JOIN
                (SELECT id_employee, id_medical_type, approved_cost, claim_date, status
                  FROM hrd_medical_claim AS t2
                  LEFT JOIN hrd_medical_claim_master AS t3 ON t2.id_master = t3.id
                  WHERE status <> ".REQUEST_STATUS_NEW." AND status <> ".REQUEST_STATUS_CHECKED." AND status <> ".REQUEST_STATUS_DENIED." AND EXTRACT(year FROM claim_date) = '$strYear' )
                  AS t4 ON t4.id_employee = t0.id AND t4.id_medical_type = t1.id_medical_type
                  WHERE 1=1 $strKriteria
                  GROUP BY t0.id, t1.id_medical_type, t1.amount,t1.type,t1.per_person";
    $resDb = $db->execute($strSQL);
    while ($rowDb = $db->fetchrow($resDb)) {
          $arrQuota2[$rowDb['id_employee']][$rowDb['id_medical_type']] = array("amount" => $rowDb['amount'], "claimed" => $rowDb['claimed'], "medical_type" => $rowDb['type'],"per_person" => $rowDb["per_person"]) ;
    }

    foreach($dataset AS $index => $rowDb)
    {
      $dataset[$index]['main_remaining'] = $rowDb['total_quota'] - $rowDb['main_claimed'];

      foreach($arrQuota2[$rowDb['id_employee']] AS $idMedType => $arrDetail)
      {
        if ($arrDetail['medical_type']==0){
          // uddin
          // akumulasi primary dan secondary yg inpatient
          $dataset[$index]['amount'] += $arrDetail['amount'];
          $dataset[$index]['total_quota'] +=$arrDetail['amount'];
          $dataset[$index]['main_remaining'] +=($arrDetail['amount']-$arrDetail['claimed']);
          $dataset[$index]['main_claimed'] +=$arrDetail['claimed'];
          //end
        }
        $dataset[$index]['quota_'.$idMedType] = $arrDetail['amount'];

        //uddin
        //if per person
        if ($arrDetail['per_person']=="t"){
          $strSQlClaim="select t2.id_family,t2.name,sum(t2.approved_cost) approval_cost_
                FROM hrd_medical_claim AS t2,hrd_medical_claim_master AS t3
                where t2.id_master = t3.id
                  AND t2.id_medical_type=".$idMedType."
                  AND t3.id_employee=".$rowDb['id_employee']."
                  AND t3.status <> ".REQUEST_STATUS_NEW." AND t3.status <> ".REQUEST_STATUS_CHECKED."
                  AND t3.status <> ".REQUEST_STATUS_DENIED." AND EXTRACT(year FROM t2.claim_date) = '$strYear'
                group by t2.id_family,t2.name
                ";
                $resDbClaim = $db->execute($strSQlClaim);
                $temStrClaim="<table>";
                $temStrRemain="<table>";
                while ($rowDbClaim = $db->fetchrow($resDbClaim)) {
                    $temStrClaim.="<tr><td>".$rowDbClaim["name"]."</td><td>".number_format($rowDbClaim["approval_cost_"])."</td></tr>";
                    $temStrRemain.="<tr><td>".$rowDbClaim["name"]."</td><td>".number_format($arrDetail['amount']-$rowDbClaim["approval_cost_"])."</td></tr>";
                }
                $temStrClaim.="</table>";
                $temStrRemain.="</table>";
          $dataset[$index]['claimed_'.$idMedType] = $temStrClaim;
          $dataset[$index]['remaining_'.$idMedType] = $temStrRemain;
        }else{
          $dataset[$index]['claimed_'.$idMedType] = $arrDetail['claimed'];
          $dataset[$index]['remaining_'.$idMedType] = $arrDetail['amount'] - $arrDetail['claimed'];
        }
      }
    }

    $myDataGrid->bind($dataset);
    $DataGrid = $myDataGrid->render();
  }
  // fungsi untuk menyimpan data
  function saveData()
  {
    global $myDataGrid;
    global $error;
    global $strKriteriaCompany;
    $strError = "";
    $strKriteria = "";
    $bolSuccess = true;
    $strModifiedByID = $_SESSION['sessionUserID'];
    $tblMedicalQuotaPrimary = new cHrdMedicalQuotaPrimary();
    $tblMedicalQuotaSecondary = new cHrdMedicalQuotaSecondary();


    $cekstatusgaji = array("branch1_allowance","branch2_allowance","branch3_allowance","grade1_allowance","grade2_allowance","grade3_allowance",
    "position1_allowance","position2_allowance","position3_allowance","family_status1_allowance","family_status2_allowance","family_status3_allowance",
    "functional1_allowance","functional2_allowance","family_status3_allowance");


    $arrData = $_POST;
    $strYear = $arrData['dataYear'];
    //$strDataCompany = $arrData['dataCompany'];
    if ($arrData['dataCompany']!= "") {
    $strKriteria .= "AND id_company = '".$arrData['dataCompany']."' ";
    }
    if ($arrData['dataEmployeeID']!= "") {
      $strKriteria .= "AND employee_id = '".$arrData['dataEmployeeID']."' ";
    }
    if ($arrData['dataPosition']!= "") {
      $strKriteria .= "AND position_code = '".$arrData['dataPosition']."' ";
    }
    if ($arrData['dataBranch']!= "") {
      $strKriteria .= "AND branch_code = '".$arrData['dataBranch']."' ";
    }
    if ($arrData['dataGrade']!= "") {
      $strKriteria .= "AND grade_code = '".$arrData['dataGrade']."' ";
    }
    if ($arrData['dataEmployeeStatus']!= "") {
      $strKriteria .= "AND employee_status = '".$arrData['dataEmployeeStatus']."' ";
    }
    if ($arrData['dataActive']!= "") {
      $strKriteria .= "AND active = '".$arrData['dataActive']."' ";
    }
    if ($arrData['dataDivision']!= "") {
      $strKriteria .= "AND division_code = '".$arrData['dataDivision']."' ";
    }
    if ($arrData['dataDepartment']!= "") {
      $strKriteria .= "AND department_code = '".$arrData['dataDepartment']."' ";
    }
    if ($arrData['dataSection']!= "") {
      $strKriteria .= "AND section_code = '".$arrData['dataSection']."' ";
    }
    if ($arrData['dataSubSection']!= "") {
      $strKriteria .= "AND sub_section_code = '".$arrData['dataSubSection']."' ";
    }
    $strKriteria .= $strKriteriaCompany;

    $arrKeys = array();
    foreach ($myDataGrid->checkboxes as $strValue)
    {
      $arrEmployee[] = $strValue;
    }
    $db = new CdbClass;
    if ($db->connect())
    {

      // cari data plafon outpatient
      $arrPlatform = array();
      $strSQL  = "SELECT * FROM hrd_medical_platform_primary";
      $resDb = $db->execute($strSQL);
      while ($rowDb = $db->fetchrow($resDb)) {
        $arrPlatform[$rowDb['family_status_code']][$rowDb['gender']][$rowDb['inspouse']] = $rowDb['amount'];
        //echo "[".$rowDb['family_status_code']." - ".$rowDb['gender']." - ".$rowDb['inspouse']." - ".$rowDb['amount']."]";
        //echo "[".$arrPlatform[$rowDb['family_status_code']][$rowDb['gender']][$rowDb['inspouse']]."]";
      }
      // cari data plafon outpatient tambahan
      $arrAddPlatform = array();
      $strSQL  = "SELECT id_employee, amount1, amount2, note FROM hrd_medical_quota_primary WHERE quota_year = $strYear;";
      $resDb = $db->execute($strSQL);
      while ($rowDb = $db->fetchrow($resDb)) {
          $arrAddPlatform[$rowDb['id_employee']]= $rowDb;
      }
      // cari data master medical type untuk generate header tabel plafon
      $strSQL  = "SELECT * FROM hrd_medical_type";
      $resDb = $db->execute($strSQL);
      while ($rowDb = $db->fetchrow($resDb)) {
        $arrMedType[$rowDb['id']] = $rowDb;
      }
      // cari data plafon lainnya
      $strSQL  = "SELECT * FROM hrd_medical_platform_secondary";
      $resDb = $db->execute($strSQL);
      while ($rowDb = $db->fetchrow($resDb)) {
        $arrPlatformSecondary[$rowDb['id_medical_type']][$rowDb['grade_code']] = $rowDb['amount'];
      }
      // cari data code basic salary
      $strSQL  = "SELECT * FROM all_setting WHERE code = 'basic_salary_code';";
      $resDb = $db->execute($strSQL);
      while ($rowDb = $db->fetchrow($resDb)) {
        //$basic_salary_code = $rowDb['value'];
        $basic_salary_code    = (isset($rowDb['value']) && !empty($rowDb['value'])) ? $rowDb['value'] : "basic_salary";
      }
$sq=0;

for ($i = (count($cekstatusgaji)-1); $i >= 0; $i--) {
  if ($basic_salary_code == $cekstatusgaji[$i]){
$sq++;
  }}
//echo $sq;

if($sq==0){
      $strSQL  = "SELECT t1.id as id_employee, employee_id, employee_name, gender, inspouse,date_part('year',age(t1.join_date)) as num_service,
                  medical_quota_status, t1.grade_code, employee_status, join_date, resign_date, due_date,
                  EXTRACT(year FROM join_date) AS join_year, EXTRACT(year FROM due_date) AS due_year, EXTRACT(year FROM resign_date) AS resign_year, basic_salary
                  FROM
                  hrd_employee AS t1 LEFT JOIN
                  (SELECT id_employee, amount as basic_salary FROM hrd_employee_allowance WHERE allowance_code = '$basic_salary_code' AND id_salary_set = (SELECT id FROM hrd_basic_salary_set WHERE 1=1 AND EXTRACT(year FROM start_date) = '$strYear' ORDER BY start_date DESC LIMIT 1)) AS t2 ON t1.id = t2.id_employee
                  LEFT JOIN hrd_position AS t3 ON t1.position_code = t3.position_code
                  WHERE 1=1 $strKriteria";
}else{
      $strSQL  = "SELECT t1.id as id_employee, employee_id, employee_name, gender, inspouse,date_part('year',age(t1.join_date)) as num_service,
                  medical_quota_status, t1.grade_code, employee_status, join_date, resign_date, due_date,
                  EXTRACT(year FROM join_date) AS join_year, EXTRACT(year FROM due_date) AS due_year, EXTRACT(year FROM resign_date) AS resign_year, basic_salary
                  FROM
                  hrd_employee AS t1 LEFT JOIN
                  (SELECT id_employee, $basic_salary_code as basic_salary FROM hrd_employee_basic_salary WHERE id_salary_set = (SELECT id FROM hrd_basic_salary_set WHERE 1=1 AND EXTRACT(year FROM start_date) = '$strYear' ORDER BY start_date DESC LIMIT 1)) AS t2 ON t1.id = t2.id_employee
                  LEFT JOIN hrd_position AS t3 ON t1.position_code = t3.position_code
                  WHERE 1=1 $strKriteria";
}
      $resDb = $db->execute($strSQL);
      while ($rowDb = $db->fetchrow($resDb))
      {
        $arrParam[$rowDb['id_employee']] = $rowDb;
      }
//echo $strSQL;
  foreach($arrEmployee AS $strIDEmployee)
      {
      	if(isset($arrParam[$strIDEmployee])){
        $rowDb = $arrParam[$strIDEmployee];

        $strMedicalQuotaStatus  = $rowDb['medical_quota_status'];
        $strGender         = $rowDb['gender'];
        $strInspouse       = $rowDb['inspouse'];
        $strGrade          = $rowDb['grade_code'];
        $strEmployeeStatus = $rowDb['employee_status'];
        $intNumService = $rowDb['num_service'];
        //$fltBasicSalary    = $rowDb['basic_salary'];
        $fltBasicSalary    = (isset($rowDb['basic_salary']) && !empty($rowDb['basic_salary'])) ? $rowDb['basic_salary'] : 0;
        //echo "[".$fltBasicSalary."]";
        $varMin  = 0;
        //Sesuaikan dengan tahun berjalan, jika karyawan baru prorata
        if ($rowDb['resign_year'] < $strYear && validStandardDate($rowDb['resign_date'])) $varMin = 12;
        else
        {
           if ($rowDb['join_year'] == $strYear)
           {
              //$varMin += $arrJoin['month'] - 1;
              //$varMin += ($arrJoin['day'] - 1)/lastDay($arrJoin['month'], $arrJoin['year']);
              $strStartDate = $rowDb['join_date'];
           }
           else if ($rowDb['join_year'] < $strYear)
           {
              $strStartDate = date("Y")."-01-01";
           }

           if (isset($rowDb['due_year']) && $rowDb['due_year'] != "" && $rowDb['due_year'] == $strYear && $rowDb['employee_status'] != 1 )
           {
              //$varMin += 12 - $arrDue['month'];
              //$varMin += (lastday($arrDue['month'], $arrDue['year']) - $arrDue['day'])/lastday($arrDue['month'], $arrDue['year']);
              $strEndDate = $rowDb['due_date'];
           }
           else  if (isset($rowDb['resign_year']) && $rowDb['resign_year'] != "" && $rowDb['resign_year'] == $strYear && $rowDb['employee_status'] == 1 )
           {
              //$varMin += 12 - $arrDue['month'];
              //$varMin += (lastday($arrDue['month'], $arrDue['year']) - $arrDue['day'])/lastday($arrDue['month'], $arrDue['year']);
              $strEndDate = $rowDb['resign_date'];
           }
           else
           {
              $strEndDate = date("Y")."-12-31";
           }
        }

        //$varMin = (12 - $varMin)/12;
        $varMin = (getIntervalDate($strStartDate,$strEndDate)+1)/(getIntervalDate(date("Y")."-01-01", date("Y")."-12-31") +1);
        $intPlatform = (isset($arrPlatform[$strMedicalQuotaStatus][$strGender][$strInspouse]) && !empty($arrPlatform[$strMedicalQuotaStatus][$strGender][$strInspouse])) ? $arrPlatform[$strMedicalQuotaStatus][$strGender][$strInspouse] : 0;
        //echo "[".$arrPlatform[$strMedicalQuotaStatus][$strGender][$strInspouse]."]";
        //echo "[".$intPlatform."]";
        $data = array();
        $data['id_employee'] = $strIDEmployee;
        $data['family_status'] = $strMedicalQuotaStatus;
        $data['grade_code'] = $strGrade;
        $data['inspouse'] = $strInspouse;
        $data['employee_status'] = $strEmployeeStatus;
        $data['basic_salary'] = $fltBasicSalary;
        $data['quota_year'] = $strYear;
        $data['amount'] = $varMin * ($intPlatform/100) * $fltBasicSalary;
        //echo "[".$varMin." - ".($intPlatform/100)." - ".$fltBasicSalary."]";
        $data['amount1']  = (isset($arrAddPlatform[$strIDEmployee]['amount1'])) ? $arrAddPlatform[$strIDEmployee]['amount1'] : 0;
        $data['amount2'] = (isset($arrAddPlatform[$strIDEmployee]['amount2'])) ? $arrAddPlatform[$strIDEmployee]['amount2'] : 0;
        $data['note'] = (isset($arrAddPlatform[$strIDEmployee]['note'])) ? $arrAddPlatform[$strIDEmployee]['note'] : "";
//print_r($data);
        $tblMedicalQuotaPrimary->delete(array("id_employee" => $strIDEmployee, "quota_year" => $strYear));
        $tblMedicalQuotaPrimary->insert($data);

        foreach($arrMedType AS $idType => $arrDetail)
        {
          //cek permanent_only, apakah hanya untuk karyawan tetap
          if ($arrDetail['permanent_only'] != "t" || $strEmployeeStatus == STATUS_PERMANENT)
            $intPlatform  = (isset($arrPlatformSecondary[$idType][$strGrade])) ? $arrPlatformSecondary[$idType][$strGrade] : 0;
          else
            $intPlatform  = 0;

            //uddin 20150118
            //cek apakah ada additional platform role masa kerja
            $strSQL = "SELECT role_value,amount,id_medical_type,grade_code FROM hrd_medical_platform_additional
            WHERE role_type='masa_kerja' and role_value <= ".$intNumService." and (role_value2 is null or role_value2 > ".$intNumService.")
            and grade_code='" .$strGrade. "' and id_medical_type=".$idType;
            //echo $strSQL;
            $resDb2 = $db->execute($strSQL);

            $addPlatform=0;
            while ($rowDb2 = $db->fetchrow($resDb2)) {
              $addPlatform  = $rowDb2['amount'];
            }
            $intPlatform  = $intPlatform+$addPlatform;
            //end

            //uddin 20150118
            //cek apakah nominal atau prosentase
            if ($arrDetail['value_type'] == "percen")
              $intPlatform=($intPlatform/100)*$fltBasicSalary;
            //end

          //cek apakah perlu di prorata
          if ($arrDetail['prorate'] == "t") $intPlatform *= $varMin;
          $data = array();
          $data['id_employee'] = $strIDEmployee;
          $data['id_medical_type'] = $idType;
          $data['quota_year'] = $strYear;
          $data['amount']  = $intPlatform;
          $tblMedicalQuotaSecondary->delete(array("id_employee" => $strIDEmployee, "id_medical_type" => $idType, "quota_year" => $strYear));
          $tblMedicalQuotaSecondary->insert($data);
        }
		}
      }
    }

    if($bolSuccess)
      $myDataGrid->message = $tblMedicalQuotaPrimary->strMessage;
    else
      $myDataGrid->errorMessage = $strError;


  } // saveData



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
