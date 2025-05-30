<?php
include_once('global.php');
  function generateSelect($name, $data, $default = "", $attribute = "", $event = "")
  {
    $strResult = "<select name=\"".$name."\" id=\"".$name."\" ".$attribute." ".$event.">\n";
    foreach ($data as $row)
    {
        if ($row['value'] == $default)
          $strResult .= "  <option value=\"".$row['value']."\" selected>".$row['text']."</option>\n";
        else
          $strResult .= "  <option value=\"".$row['value']."\">".$row['text']."</option>\n";
    }
    $strResult .= "</select>";
    return $strResult;
  }

  function generateOption($data, $default = "")
  {
    $strResult = "";
    foreach ($data as $row)
    {
        if ($row['value'] == $default)
          $strResult .= "  <option value=\"".$row['value']."\" selected>".$row['text']."</option>\n";
        else
          $strResult .= "  <option value=\"".$row['value']."\">".$row['text']."</option>\n";
    }
    return $strResult;
  }

  function generateInput($name, $value, $attribute = "", $event = "")
  {
    $strResult = "<input type=\"text\" name=\"".$name."\" id=\"".$name."\" ".$attribute." ".$event." value=\"".$value."\" />";
    return $strResult;
  }

  function generateLabel($name, $value, $attribute = "", $event = "")
  {
    $strResult = "<label name=\"".$name."\" id=\"".$name."\" ".$attribute." ".$event." value=\"".$value."\" />";
    return $strResult;
  }

  function generateButton($name, $value, $attribute = "", $event = "")
  {
    $strResult = "<input type=\"button\" name=\"".$name."\" id=\"".$name."\" ".$attribute." ".$event." value=\"".$value."\" />";
    return $strResult;
  }

  function generateSubmit($name, $value, $attribute = "", $event = "")
  {
    $strResult = "<input type=\"submit\" name=\"".$name."\" id=\"".$name."\" ".$attribute." ".$event." value=\"".$value."\" />";
    return $strResult;
  }
  function generateRadio($name, $value, $attribute = "", $event = "")
  {
    $strResult = "<input type=\"radio\" name=\"".$name."\" id=\"".$name."\" ".$attribute." ".$event." value=\"".$value."\" />";
    return $strResult;
  }
  function generateCheckBox($name, $value, $attribute = "", $event = "")
  {
    if ($value == 't')
      $strResult = "<input type=\"checkbox\" name=\"".$name."\" id=\"".$name."\" ".$attribute." ".$event." value=\"t\" checked />";
    else
      $strResult = "<input type=\"checkbox\" name=\"".$name."\" id=\"".$name."\" ".$attribute." ".$event." value=\"f\" />";
    return $strResult;
  }
  //Above function is weird
	function generateCheckBox2($name, $value, $attribute = "", $event = "")
  {
    $strResult = "<input type=\"checkbox\" name=\"".$name."\" id=\"".$name."\" ".$attribute." ".$event." value=\"$value\" />";
    return $strResult;
  }	
  function generateHidden($name, $value, $strAttribute = "")
  {
    $strResult = "<input type=\"hidden\" name=\"".$name."\" id=\"".$name."\" value=\"".$value."\" ".$strAttribute." />";
    return $strResult;
  }

  function generateTextArea($name, $value, $attribute = "", $event = "")
  {
    $strResult = "<textarea name=\"".$name."\" id=\"".$name."\" ".$attribute." ".$event.">".$value."</textarea>";
    return $strResult;
  }

  function normalizeBoolean($boleanVar = null){
    if (!is_null($boleanVar)){
      if ($boleanVar == 't'){
        $boleanVar = true;
      }else if($boleanVar == 'f'){
        $boleanVar = false;
      }
    }
    return $boleanVar;
  }
  function generateRoleButtons($bolCanEdit, $bolCanDelete, $bolCanCheck, $bolCanApprove, $bolCanApprove2, $bolDatagridClass = false, &$objDatagrid = null)
  {
    global $words;
    $bolCanEdit = normalizeBoolean($bolCanEdit);
    $bolCanDelete = normalizeBoolean($bolCanDelete);
    $bolCanCheck = normalizeBoolean($bolCanCheck);
    $bolCanApprove = normalizeBoolean($bolCanApprove);
    $bolCanApprove2 = normalizeBoolean($bolCanApprove2);
    if ($bolDatagridClass)
    {
      include_once('../includes/datagrid2/datagrid.php');

      if ($bolCanDelete)
        $objDatagrid->addSpecialButton("btnDelete", "btnDelete", "submit", getWords('delete'), "onClick=\"javascript:return myClient.confirmDelete();\"", "deleteData()");
      if ($bolCanCheck)
      {
        $objDatagrid->addSpecialButton("btnChecked", "btnChecked", "submit", getWords('checked'), "onClick=\"javascript:return myClient.confirmChangeStatus();\"", "callChangeStatus()");
        //$objDatagrid->addSpecialButton("btnDenied", "btnDenied", "submit", $words['denied'], "onClick=\"javascript:return myClient.confirmChangeStatus();\"", "callChangeStatus()");
      }
      if ($bolCanApprove)
      {

        $objDatagrid->addSpecialButton("btnApproved", "btnApproved", "submit", getWords('approved'), "onClick=\"javascript:return myClient.confirmChangeStatus();\"", "callChangeStatus()");
        $objDatagrid->addSpecialButton("btnDenied", "btnDenied", "submit", getWords('denied'), "onClick=\"javascript:return myClient.confirmChangeStatus();\"", "callChangeStatus()");
      }
      if ($bolCanApprove2)
      {

        $objDatagrid->addSpecialButton("btnApproved2", "btnApproved2", "submit", getWords('approved 2'), "onClick=\"javascript:return myClient.confirmChangeStatus();\"", "callChangeStatus()");
        //$objDatagrid->addSpecialButton("btnClose", "btnClose", "submit", $words['close'], "onClick=\"javascript:return myClient.confirmChangeStatus();\"", "callChangeStatus()");
      }
    }
    else
    {
      $strButtons = "";
     /* if ($bolCanEdit)
      {
        $strButtons .= "&nbsp;";
        $strButtons .= generateSubmit("btnVerified", $words['verified'], "", " onClick=\"return confirmStatusChanges(false)\"");
      }*/
      if ($bolCanDelete)
      {

        $strButtons .= "&nbsp;";
        $strButtons .= generateSubmit("btnDelete", getWords('delete'), "", " onClick=\"return confirmDelete()\"");
      }
      if ($bolCanCheck)
      {
        $strButtons .= "&nbsp;";
        $strButtons .= generateSubmit("btnChecked", getWords('check'), "", " onClick=\"return confirmStatusChanges(false)\"");
        //$strButtons .= "&nbsp;";
        //$strButtons .= generateSubmit("btnDenied", $words['denied'], "", " onClick=\"return confirmStatusChanges(false)\"");
      }
      if ($bolCanApprove)
      {
        $strButtons .= "&nbsp;";
        $strButtons .= generateSubmit("btnApproved", getWords('approved'), "", " onClick=\"return confirmStatusChanges(false)\"");
        $strButtons .= "&nbsp;";
        $strButtons .= generateSubmit("btnDenied", getWords('denied'), "", " onClick=\"return confirmStatusChanges(false)\"");
        $strButtons .= "&nbsp;";
      }
      if ($bolCanAcknowledge)
      {
        $strButtons .= "&nbsp;";
        $strButtons .= generateSubmit("btnAcknowledged", getWords('acknowledged'), "", " onClick=\"return confirmStatusChanges(false)\"");
      }
      if ($bolCanApprove2)
      {
        $strButtons .= "&nbsp;";
        $strButtons .= generateSubmit("btnApproved2", getWords('approved 2'), "", " onClick=\"return confirmStatusChanges(false)\"");
      }
      return $strButtons;
    }
  }
  function generateSelectMonth($name, $default = "", $attribute = "", $event = "", $hasEmptyData = false, $emptyData = null)
  {
    $arrMonth = array(
                  "1" => "Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec");
    $strResult = "<select name=\"".$name."\" id=\"".$name."\" ".$attribute." ".$event.">\n";
    if ($default == "") $default = date('m');
    foreach ($arrMonth as $key => $val)
    {
        if ($key == $default)
          $strResult .= "  <option value=\"".$key."\" selected>".$val."</option>\n";
        else
          $strResult .= "  <option value=\"".$key."\">".$val."</option>\n";
    }
    $strResult .= "</select>";
    return $strResult;
  }

  function generateSelectYear($name, $default = "", $attribute = "", $event = "", $hasEmptyData = false, $emptyData = null, $intLimit = 50, $isAscending = false)
  {
    $strResult = "<select name=\"".$name."\" id=\"".$name."\" ".$attribute." ".$event.">\n";
    if ($hasEmptyData && $emptyData !== null)
    {
      $strResult .= "  <option value=\"\">".$emptyData."</option>\n";
    }
    $intYear = intval(date("Y"));
    if ($isAscending)
    {
      $intYear -= $intLimit;
      for($i = 0; $i < $intLimit; $i++)
      {
        if ($intYear == $default)
          $strResult .= "  <option value=\"".$intYear."\" selected>".$intYear."</option>\n";
        else
          $strResult .= "  <option value=\"".$intYear."\">".$intYear."</option>\n";
        $intYear++;
      }
    }
    else
    {
      for($i = 0; $i < $intLimit; $i++)
      {
        if ($intYear == $default)
          $strResult .= "  <option value=\"".$intYear."\" selected>".$intYear."</option>\n";
        else
          $strResult .= "  <option value=\"".$intYear."\">".$intYear."</option>\n";
        $intYear--;
      }
    }
    $strResult .= "</select>";
    return $strResult;
  }

  function generateSpan($name, $value, $style = "")
  {
    return "<span id=\"$name\" name=\"$name\" $style>".$value."</span>";
  }

  function generateDiv($name, $value, $style)
  {
    return "<div id=\"$name\" name=\"$name\" ".$style.">".$value."</div>";
  }

  function generateFile($name, $value = null, $attribute = "", $event = "")
  {
    $strResult = "<input type=\"file\" name=\"".$name."\" id=\"".$name."\" ".$attribute." ".$event." />";
    return $strResult;
  }

  function getMonth($m = 0)
  {
    return (($m == 0 ) ? date("F") : date("F", mktime(0,0,0,$m)));
  }



  /*
    Daftar fungsi-fungsi (super) global
      Author: Yudi K.
  */

  // fungsi untuk meredirect ke suatu page
  // $strDestPage = tujuan, $strSrcPage = asal, atau yang lama, $bolSave = simpan ke session
  function redirectPage($strDestPage = "login.php", $strSrcPage = "", $bolSave = true) {
    global $_SESSION;

    if ($strDestPage != "") {
      if ($bolSave) {
        if ($strSrcPage == "") $strSrcPage = $_SERVER['PHP_SELF'];
        $_SESSION['sessionLastPage'] = $strSrcPage;
      }
      header("location:$strDestPage");
    }
  }


  // fungsi untuk menentukan apakah yang login adalah employee biasa (staff, bukan admin sistem terkait)
  // pokokny ayang bukan super sys admin, admin, manager
  function isMe($strIDEmployee) {
    global $arrUserInfo;
    $bolResult = ($arrUserInfo['id_employee'] == $strIDEmployee);
    return $bolResult;
  }
  // fungsi untuk menentukan apakah yang login adalah employee biasa (staff, bukan admin sistem terkait)
  // pokokny ayang bukan super sys admin, admin, manager
  function isUserEmployee() {
    global $_SESSION;

    // employee adalah yang role = 2 (user), dan ada gak punya posisi (jabatan)
    $bolResult = ($_SESSION['sessionUserRole'] == ROLE_EMPLOYEE);
    return $bolResult;
  }
  function thisUserIs($strRole) {
    global $_SESSION;

    // employee adalah yang role = 2 (user), dan ada gak punya posisi (jabatan)
    $bolResult = ($_SESSION['sessionUserRole'] == $strRole);
    return $bolResult;
  }

  // fungsi untuk menentukan apakah yang login adalah employee biasa,
  // tapi posisinya sebagai department_head
  function isUserDeptHead($db) {
    global $_SESSION;
    global $arrUserInfo;

    $bolResult = false;
    $strDeptHead = getSetting("department_head");
    $bolResult = (strtoupper($strDeptHead) == strtoupper($arrUserInfo['position_code']));

    return $bolResult;
  } // isUserDeptHead

  // fungsi untuk menentukan apakah yang login adalah employee biasa,
  // tapi posisinya sebagai department_head
  function isUserGroupHead($db) {
    global $_SESSION;
    global $arrUserInfo;

    $bolResult = false;
    $strGroupHead = getSetting("group_head");
    $bolResult = (strtoupper($strGroupHead) == strtoupper($arrUserInfo['position_code']));

    return $bolResult;
  } // bolUserGroupHead


  // fungsi untuk menentukan apakah yang login adalah employee, tapi DEPT. HEAD (yang berhak approve punya employee)
  function isUserManager() {
    global $_SESSION;
    global $arrUserInfo;
    global $db;

    $bolResult = false;

    $strManagerCode = getSetting("department_head");
    if ($strManagerCode != "" && $arrUserInfo['idEmployee']) {
      // manager adalah user yang jabatannya sesuai
      $strSQL  = "SELECT position_code FROM hrd_employee ";
      $strSQL .= "WHERE id = '" .$arrUserInfo['idEmployee']."' ";
      $resDb = $db->execute($strSQL);
      if ($rowDb = $db->fetchrow($resDb)) {
        $bolResult = (strtoupper($strManagerCode) == strtoupper($rowDb['position_code']));
      }
    }
    return $bolResult;
  }// isUserManager


  // fungsi untuk mengambil informasi dari karyawan, berdasar userID, disimpan dalm array (global)
  function getUserEmployeeInfo() {
    global $db;
    global $arrUserInfo;
    global $ARRAY_DISABLE_GROUP;
    global $_SESSION;
    global $strEmpReadonly;
    global $strNonCbReadonly;

    if (isset($_SESSION['sessionUserID'])) {
      $strUserID = $_SESSION['sessionUserID'];
      if ($strUserID == "") return 0;
    } else {
      return 0;
    }

    $strSQL  = "SELECT t1.id, t1.employee_id, t1.employee_name, t1.position_code, t1.employee_status, t1.management_code, ";
    $strSQL .= "t1.division_code, t1.department_code, t1.section_code, t1.sub_section_code, t1.id_company, t1.branch_code ";
    $strSQL .= "FROM adm_user AS t2 LEFT JOIN hrd_employee AS t1 ON TRIM(t1.employee_id) = TRIM(t2.employee_id)  ";
    $strSQL .= "WHERE t2.id_adm_user = '$strUserID' ";
    //$strSQL .= "AND t1.active = 1 ";
    $resDb = $db->execute($strSQL);
    if ($rowDb = $db->fetchrow($resDb)) {
      $arrUserInfo['id_employee'] = $rowDb['id'];
      $arrUserInfo['employee_id'] = $rowDb['employee_id'];
      $arrUserInfo['employee_name'] = $rowDb['employee_name'];
      $arrUserInfo['employee_status'] = $rowDb['employee_status'];
      $arrUserInfo['division_code'] = $rowDb['division_code'];
      $arrUserInfo['department_code'] = $rowDb['department_code'];
      $arrUserInfo['section_code'] = $rowDb['section_code'];
      $arrUserInfo['sub_section_code'] = $rowDb['sub_section_code'];
      $arrUserInfo['position_code'] = $rowDb['position_code'];
      $arrUserInfo['id_company'] = $rowDb['id_company'];
      $arrUserInfo['branch_code'] = $rowDb['branch_code'];
      $arrUserInfo['management_code'] = $rowDb['management_code'];
      if ($rowDb['id_company'] != "")
      {
        $strSQL  = "SELECT * FROM hrd_company WHERE id = ".$rowDb['id_company'];
        $resDb = $db->execute($strSQL);
        $arrUserInfo['company_code'] = ($rowDb = $db->fetchrow($resDb)) ? $rowDb['company_code'] : "";
      }
      else
        $arrUserInfo['company_code'] = "";
    }
    if ($_SESSION['sessionUserRole'] == ROLE_EMPLOYEE )
    {
      $strEmpReadonly = "readonly";
      $ARRAY_DISABLE_GROUP['division'] = $ARRAY_DISABLE_GROUP['department'] = $ARRAY_DISABLE_GROUP['section'] =
      $ARRAY_DISABLE_GROUP['sub_section'] = "disabled";
    }
    else if ($_SESSION['sessionUserRole'] == ROLE_SUPERVISOR)
    {
      if($arrUserInfo['division_code'] != "") $ARRAY_DISABLE_GROUP['division'] = "disabled";
      if($arrUserInfo['department_code'] != "") $ARRAY_DISABLE_GROUP['department'] = "disabled";
      if($arrUserInfo['section_code'] != "") $ARRAY_DISABLE_GROUP['section'] = "disabled";
      if($arrUserInfo['sub_section_code'] != "") $ARRAY_DISABLE_GROUP['sub_section'] = "disabled";
    }
    else if ($_SESSION['sessionUserRole'] == ROLE_SUPERVISOR_BRANCH)
    {
      if($arrUserInfo['division_code'] != "") $ARRAY_DISABLE_GROUP['division'] = "disabled";
      if($arrUserInfo['department_code'] != "") $ARRAY_DISABLE_GROUP['department'] = "disabled";
      if($arrUserInfo['section_code'] != "") $ARRAY_DISABLE_GROUP['section'] = "disabled";
      if($arrUserInfo['sub_section_code'] != "") $ARRAY_DISABLE_GROUP['sub_section'] = "disabled";
      if($arrUserInfo['branch_code'] != "") $ARRAY_DISABLE_GROUP['branch'] = "disabled";
    }
    else if ($_SESSION['sessionUserRole'] == ROLE_BRANCH_ADMIN)
    {
      if($arrUserInfo['branch_code'] != "") $ARRAY_DISABLE_GROUP['branch'] = "disabled";
    }
    else if ($_SESSION['sessionUserRole'] == ROLE_DIVISION_BRANCH_ADMIN)
    {
      if($arrUserInfo['division_code'] != "") $ARRAY_DISABLE_GROUP['division'] = "disabled";
      if($arrUserInfo['branch_code'] != "") $ARRAY_DISABLE_GROUP['branch'] = "disabled";
    }
    if ($_SESSION['sessionUserRole'] != ROLE_SUPER) $strNonCbReadonly = "readonly";
  } //getUserEmployeeInfo

  // fungsi untuk mengambil informasi user
  function getAllUserInfo($db)
  {
    $arrResult = array();
    $strSQL  = "SELECT * FROM adm_user ";
    $resDb = $db->execute($strSQL);
    while ($rowDb = $db->fetchrow($resDb))
    {
      $arrResult[$rowDb['id_adm_user']]['name'] = ($rowDb['name'] == "") ? $rowDb['login_name'] : $rowDb['name'];
      $arrResult[$rowDb['id_adm_user']]['login'] = $rowDb['login_name'];
      $arrResult[$rowDb['id_adm_user']]['car'] = $rowDb['id_adm_company'];
    }
    return $arrResult;
  } //getAllUserInfo

  // fungsi untuk menampilkan error, berupa alert, di javascript
  function showError($str) {
    global $error;
    $strMsg = "<script>alert(\"" .$error[$str]. "\")</script>";
    echo $strMsg;
  }//showError


  //fungsi untuk memeriksa apakah sebuah nilai adalah numeric
  //input: sembarang
  //output: benar jika input adalah numerik
  function isNumeric($value) {
    return is_float($value);
  } //end of isNumeric



  // fungsi untuk menulis data log file
  // activity= tipe aktifitas yang dikerjakan, module=kode module, page=url, note=keterangan tambahan
  function writeLog($intActivity = 0, $intModule = 0, $strNote = "", $intStatus = 0)
  {
    global $dataPrivilege;
    /*
    global $bolSaveLog;

    $bolSaveLog = (getSetting("user_log") == 't');
    if (!$bolSaveLog)
    {
      return 0;
    }*/
    $intUserID = (isset($_SESSION['sessionUserID'])) ? $_SESSION['sessionUserID'] : -1;
    $intUserType = (isset($_SESSION['sessionUserType'])) ? $_SESSION['sessionUserType'] : -1;
    $strUserLogin = (isset($_SESSION['sessionUser'])) ? substr($_SESSION['sessionUser'],0,30) : "";
    $arrPage = explode("/", $_SERVER['PHP_SELF']);
    $strPage = (count($arrPage) > 0) ? substr($arrPage[count($arrPage) - 1], 0, 50) : "";
    $strNote = substr($strNote,0,255);
    $strIP = $_SERVER['REMOTE_ADDR'];
    $strMenuID = $dataPrivilege['id_adm_menu'];
    $arrData = array("id_adm_user" => $intUserID,
                       "action_type" => $intActivity,
                       "id_adm_menu" => $strMenuID,
                       "page" => $strPage,
                       "message" => $strNote,
                       "ip_address" => $strIP,
                       "php_file" => $strPage,
                       "status" => $intStatus);
    $tbl = new cModel("adm_userlog");
    $tbl->insert($arrData);
    return 0;
  }

  //fungsi untuk mengetahui apakah suatu data ada atau tidak
  //criteria dalam format "AND ...."
  function isDataExists($db, $table, $field, $data, $criteria = "") {
    $bolResult = false;

    // dibuat incase-sensitif aja
    $data = strtolower($data);
    $strSQL  = "SELECT \"$field\" FROM \"$table\" ";
    $strSQL .= "WHERE lower(\"$field\") = '$data' $criteria ";
    $resTmp = $db->execute($strSQL);
    if ($db->numrows($resTmp) > 0) {
      $bolResult = true;
    }

    return $bolResult;
  } //isDataExists

  //========================================================================================================================================
  //Edit : 8 Agustus 2008
  //fungsi untuk mengetahui apakah suatu data ada atau tidak(digunakan khusus untuk form Overtime Application untuk casting type Bigint)
  //criteria dalam format "AND ...."
  function isDataExists_($db, $table, $field, $data, $criteria = "") {
    $bolResult = false;

    // dibuat incase-sensitif aja
    $data = strtolower($data);
    $strSQL  = "SELECT \"$field\" FROM \"$table\" ";
    $strSQL .= "WHERE (\"$field\") = '$data' $criteria ";
    $resTmp = $db->execute($strSQL);
    if ($db->numrows($resTmp) > 0) {
      $bolResult = true;
    }

    return $bolResult;
  } //isDataExists


  //fungsi untuk mengambil data ID dari modified_by (pembuat data)
  //input: class DB, nama tabel, nama field (biasanya ID), nilai dari field,
  //output: id dari modified_by (user pembuat)
  function getmodified_byID($strTable, $strColumn, $strValue)
  {
    $tbl = new cModel($strTable);
    if ($row = $tbl->find("$strColumn = '$strValue'", "modified_by"))
    {
      return $row['modified_by'];
    }
    return "";
  } // end of getmodified_byID


  // fungsi untuk menghasilkan daftar page (pada page info)
  // input: total record, record per page, current page, link target
  // output: daftar halaman (lengkap dengan linknya)
  function generatePages($intTotal,$intPerPage,$intCurrPage,$strLink) {
    global $words;
    $strHasil = "";

    if ($intPerPage > 0) {
      if (($intTotal % $intPerPage) > 0) {
        $intTotalPage = $intTotal/$intPerPage + 1;
      } else {
        $intTotalPage = $intTotal/$intPerPage;
      }
      $intTotalPage = (int)$intTotalPage;
      if ($intTotalPage > 1) { // hanya diproses jika jumlah halaman > 1
        //prev link
        if ($intCurrPage == 1) {
          $strHasil .= "&laquo; ";
        } else {
          $strHasil .= "<a href=\"$strLink?page=" .($intCurrPage-1). "\">&laquo;</a> ";
        }

        for ($i=1;$i<=$intTotalPage;$i++) {
          if ($i == $intCurrPage) {
            $strHasil .= "<b>$i</b> ";
          } else {
            $strHasil .= "<a href=\"$strLink?page=$i\">$i</a> ";
          }
        }

        //next link
        if ($intTotalPage == $intCurrPage) {
          $strHasil .= "&raquo; ";
        } else {
          $strHasil .= "<a href=\"$strLink?page=" .($intCurrPage+1). "\">&raquo;</a> ";
        }

        //all
        $strHasil .= " &nbsp;<a href=\"$strLink?all=1\">[all]</a> &nbsp; ";
      }
    }
    return $strHasil;
  } //generatePages

  //fungsi untuk mengambil data konfigurasi'
  //data = database, code adalah kode konfigurasi
  function getSetting($code, $bolGeneral = false)
  {
    $tbl = ($bolGeneral) ? new cModel("all_setting_general") : new cModel("all_setting");
    if ($arrHasil = $tbl->findByCode($code))
      return $arrHasil['value'];
    else return false;
  }  //getSetting


  //fungsi untuk menyimpan data konfigurasi'
  //data = database, code adalah kode konfigurasi, $value = nilainya
  function saveSetting($code, $value, $note = null, $module = null, $bolGeneral = false)
  {
    $tbl = ($bolGeneral) ? new cModel("all_setting_general") : new cModel("all_setting_template");
    // cek dulu apakah ada settingnya
    $jml = $tbl->findCount("code = '$code'");
    $isSuccess = false;

    if ($note === null) $note = "";
    if ($module === null) $module = (isset($GLOBALS['strModule'])) ? $GLOBALS['strModule'] : 0;
    $data = array("code" => $code,
                  "value" => $value,
                  "note" => $note,
                  "module" => $module);
    if ($jml > 0)
    {
      $isSuccess = $tbl->update(array("code" => $code), $data);
    }
    else
    { // buat baru
      $isSuccess = $tbl->insert($data);
    }
    return $isSuccess;
  }  //saveSetting

  // fungsi untuk memformat angka ribuan, dengan format English
  // standard memakai 2 decimal, jika 0, diabaikan
  // Misal 11111111.11 jadi 11,111,111.11
  // iqnoreZero => 0 diabaikan
  // $intDec = jumlah angka dibelakang koma
  function standardFormat($fltX = 0, $ignoreZero = false, $intDec = 0)
  {
    if (!is_numeric($intDec)) $intDec = 0;
    if ($ignoreZero && $fltX == 0) {
      return 0;
    } else {
      if (is_numeric($fltX) == false) $fltX = 0;
      return number_format($fltX, $intDec, ',', '.');
    }
  }

  // fungsi untuk menentukan apakah sebuah hari itu hari libur atau tidak
  function isHoliday($strDate, $bolAllHoliday = true, $bolIsAllDay = 'f', $bolIsSatIn = 'f')
  {
       global $db;
       $bolResult = false;

       if ($strDate == "") return false;
       // cari hari dan tanggalnya

       list($tahun,$bulan,$tanggal) = explode("-",$strDate);
       $tsTanggal = mktime(0,0,0,$bulan,$tanggal,$tahun);
       $dtTanggal = getdate($tsTanggal);

       // cari di calendar
       $tbl = new cModel("hrd_calendar");
       if ($rowDb = $tbl->findByHoliday($strDate, "id, status"))
       {
         $bolResult = ($rowDb['status'] == 't'); // bisa saja hari libur, atau hari libur tapi dianggap masuk (pengganti)
       }
       else if($bolAllHoliday)
       {
         // tidak ada catatann hari libur
         if ($dtTanggal['wday'] == 0)
         {
           # If is_all_day is not checked, then Sunday is considered as off day.
           if ($bolIsAllDay == 'f') {
             $bolResult = true;
           }
         }
         else if ($dtTanggal['wday'] == 6)
         { // hari sabtu
           if (getSetting("saturday") == "t")
           {
             # If is_all_day and is_sat_in is not checked, then Saturday is considered as off day.
             if ($bolIsAllDay == 'f' && $bolIsSatIn == 'f') {
               $bolResult = true;
             }
           }
         }
       }

       return $bolResult;
  }//isHoliday


  // fungsi untuk mmembuat paging dari data
  function getPaging($intPage = 1, $intTotal = 1, $strLink = "") {
    global $intPageLimit; // jumlah link page maksimal yang ditampilkan
    global $intRowsLimit; // jumlah baris yang ditampilkan satu page

    $strResult = "";

    // cari jumlah halaman
    $intTotalPage = ceil($intTotal / $intRowsLimit);
    //if (($intTotal % $intRowsLimit) == 0) {
    //  $intTotalPage++;
    //}

    // cari start page dan finish page yang akan ditambilkan
    if (($intPage % $intPageLimit) == 0) {
      $intPageStart = ((($intPage / $intPageLimit) - 1) * $intPageLimit) + 1; // + ($intPage % $intPageLimit);
    } else {
      $intPageStart = ((floor($intPage / $intPageLimit)) * $intPageLimit) + 1; // + ($intPage % $intPageLimit);
    }
    $intPageFinish = $intPageStart + $intPageLimit - 1;
    if ($intPageStart < 1) { $intPageStart = 1;};
    if ($intPageFinish > $intTotalPage) { $intPageFinish = $intTotalPage; };

    // tambahkan link untuk prev dan first page

    if ($intPage > 1) {
      $strResult .= " <a href=\"" .str_replace("[PAGE]","1",$strLink). "\">&laquo;&laquo;</a>";

      $strResult .= " <a href=\"" .str_replace("[PAGE]",($intPage-1),$strLink). "\">&laquo;</a>";
    }

    for ($i = $intPageStart; $i <= $intPageFinish; $i++) {
      if ($i == $intPage) {
        $strResult .= " <strong>$i</strong> ";
      } else {
        $strResult .= " <a href=\"" .str_replace("[PAGE]",$i,$strLink). "\">$i</a> ";
      }
    }

    // tambahkan link next dan last page
    if ($intPage < $intTotalPage) {
      $strResult .= " <a href=\"" .str_replace("[PAGE]",($intPage+1),$strLink). "\">&raquo;</a>";
      $strResult .= " <a href=\"" .str_replace("[PAGE]",$intTotalPage,$strLink). "\">&raquo;&raquo;</a>";
    }

    return $strResult;
  }//getPaging

  // fungsi untuk menghitung selisih hari kerja
  // Misal : tanggal 1 s.d. 2, berarti ada 2 hari
  // Selisih hari adalah hari kerja, berarti selisih juga dikurangi dengan hari minggu dan libur, juga hari sabtu jika libur
  // $db adalah kelas database, $strFrom dan $strThru dalam format standard date PG, "YYYY-MM-DD"
  // output : total selisih hari
  function totalWorkDay($db, $strFrom, $strThru)
  {
    include_once("activity.php");
    global $arrWorkDay; // untuk menampung total workday, datefrom-datethru, agar menghemat pencarian, jika sudah pernah ada

    //if (isset($arrWorkDay["$strFrom.$strThru"])) return $arrWorkDay["$strFrom.$strThru"]; // langsung dibalikin

    if ($strFrom == "" || $strThru == "") return 0;

    $intResult = 0;
    $strFrom = pgDateFormat($strFrom, "Y-m-d");
    $strThru = pgDateFormat($strThru, "Y-m-d");
    if ($strFrom > $strThru) {
      $intResult = 0;
    } else if ($strFrom == $strThru) {
      $intResult = 1;
    } else {
      // ubah format dalam timestamp
      list($intYear, $intMonth, $intDay) = explode("-",$strFrom);
      $tsFrom = mktime(0,0,0,(int)$intMonth,(int)$intDay, (int)$intYear);
      $dtFrom = getdate($tsFrom);
      list($intYear, $intMonth, $intDay) = explode("-",$strThru);
      $tsThru = mktime(0,0,0,(int)$intMonth,(int)$intDay, (int)$intYear);
      $dtThru = getdate($tsThru);

      $intSelisih = round(($tsThru - $tsFrom) / 86400); // selisih dalam hari
      $intSelisihMinggu = floor($intSelisih / 7); // selisih dalam minggu
      $intResult = $intSelisih + 1; // karena hari awal dihitung juga
      $intModMinggu = ($intSelisih % 7);
      // dikurangi dengan hari minggu
      $intResult -= $intSelisihMinggu;
      if ($intModMinggu > 0) {
        if ($dtFrom['wday'] == 0 || $dtThru['wday'] == 0) {
          $intResult--;
        } else if ($dtFrom['wday'] > $dtThru['wday']) {
          $intResult--;
        }
      }

      // cek jika ada hari minggu yang ditngah-tengahnya
      /*
      if (($dtFrom['wday'] != 0) && ($dtThru['wday'] != 0) && ($dtFrom['wday'] > $dtThru['wday'])) {
        $intResult--;
      }
      */

      // dikurangi hari sabtu jika, sabtu libur
      $bolSaturday = (getSetting("saturday") == 't');
      if ($bolSaturday) {
        /*
        if ($intSelisihMinggu == 0) {
          // cari apakah hari minggu atau bukan
          if ($dtFrom['wday'] == 6 || $dtThru['wday'] == 6) {
            $intResult--;
          } else if ($dtThru['wday'] == 0) {
            $intResult--;
          }
        } else {
          $intResult -= $intSelisihMinggu;
          if ($dtFrom['wday'] == 6 || $dtThru['wday'] == 6) {
            $intResult--;
            echo " ada ";
          }
        }
        echo "-Sbt:$intResult x[$intSelisihMinggu] ";
        // cek jika ada hari sabtu yang ditngah-tengahnya
        if (($dtFrom['wday'] != 6) && ($dtThru['wday'] != 6) && ($dtFrom['wday'] > $dtThru['wday']) && ($dtFrom['wday'] != 0) && ($dtThru['wday'] != 0)) {
          $intResult--;
        }
        */
        $intResult -= $intSelisihMinggu;
        if ($intModMinggu > 0) {
          if ($dtFrom['wday'] == 6 || $dtThru['wday'] == 6) {
            $intResult--;
          } else if ($dtFrom['wday'] > $dtThru['wday']) {
            $intResult--;
          }
        }

      }

      // cari data hari libur
      // selain hari minggu
      $strSQL  = "SElECT COUNT(id) AS total FROM hrd_calendar ";
      $strSQL .= "WHERE holiday BETWEEN '$strFrom' AND '$strThru' ";
      $strSQL .= "AND EXTRACT(dow FROM holiday) <> 0 ";
      if ($bolSaturday) {
        $strSQL .= "AND EXTRACT(dow FROM holiday) <> 6 ";
      }
      $strSQL .= "AND status = 't' "; // cari yang libur saja
      $resDb = $db->execute($strSQL);
      if ($rowDb = $db->fetchrow($resDb)) {
        if (is_numeric($rowDb['total'])) {
          $intResult -= $rowDb['total'];
        }
      }

      // cari data pengganti libur
      $strSQL  = "SElECT COUNT(id) AS total FROM hrd_calendar ";
      $strSQL .= "WHERE holiday BETWEEN '$strFrom' AND '$strThru' ";
      $strSQL .= "AND status = 'f' "; // cari yang libur saja
      $resDb = $db->execute($strSQL);
      if ($rowDb = $db->fetchrow($resDb)) {
        if (is_numeric($rowDb['total'])) {
          $intResult += $rowDb['total'];
        }
      }
    }
//echo $intResult;

    // simpan ke var global
    $arrWorkDay["$strFrom.$strThru"] = $intResult;
    $intResult = round($intResult);
    return round($intResult);
  } //totalOffDay
function totalWorkDayEmployee($db, $strIDEmployee, $strFrom, $strThru)
{
  $intWorkday = $intTotal = getIntervalDate($strFrom, $strThru) + 1;
  $arrShift = getShiftSchedule($db, getNextDate($strFrom, -1), $strThru, $strIDEmployee);
  $strCurDate = $strFrom;
  while($strCurDate <= $strThru)
  {
    if(isEmployeeHoliday($db, $strCurDate, $strIDEmployee))
    {
      $intWorkday--;
    }

    $strCurDate = getNextDate($strCurDate);
  }
  return $intWorkday;
}
/*
function totalWorkDayEmployee($db, $strIDEmployee, $strFrom, $strThru)
  {
    include_once(dirname(dirname(__FILE__))."/includes/date/date.php");
    include_once("activity.php");


    if ($strFrom == "" || $strThru == "") return 0;

    $intResult = 0;
    $strFrom = pgDateFormat($strFrom, "Y-m-d");
    $strThru = pgDateFormat($strThru, "Y-m-d");

    if ($strFrom > $strThru)
    {
      swap($strFrom, $strThru);
    }

    // ubah format dalam timestamp
    list($intYear, $intMonth, $intDay) = explode("-",$strFrom);
    $dt1 = new clsDate(intval($intYear), intval($intMonth), intval($intDay));
    $tsFrom = mktime(10,0,0,(int)$intMonth,(int)$intDay, (int)$intYear);
    $dtFrom = getdate($tsFrom);
    list($intYear, $intMonth, $intDay) = explode("-",$strThru);
    $dt2 = new clsDate(intval($intYear), intval($intMonth), intval($intDay));
    $tsThru = mktime(10,0,0,(int)$intMonth,(int)$intDay, (int)$intYear);
    $dtThru = getdate($tsThru);


    //ambil workschedule berdasarkan jadwal per susunan organisasi
    $tblWorkingSchedule = new cModel("hrd_work_schedule");
    $arrScheduleRaw = $tblWorkingSchedule->findAll(null, null, "workday ASC");
    $arrSchedule = array();
    foreach($arrScheduleRaw as $rowDb)
    {
      if ($rowDb['workday'] == -1)
      {
        //all days
        for($i = 0; $i <= 6; $i++)
        {
          $arrSchedule[$rowDb['table_name']][$rowDb['link_code']][$i] = $rowDb['day_off'];
        }
      }
      else
        $arrSchedule[$rowDb['table_name']][$rowDb['link_code']][$rowDb['workday']] = $rowDb['day_off'];
    }

    if (getSetting("saturday") != 'f')
      $isSaturdayOff = 't';
    else
      $isSaturdayOff = 'f';
    $tblEmployee = new cModel("hrd_employee");
    if ($arrData = $tblEmployee->findById($strIDEmployee))
    {
      for($i = 0; $i <= 6; $i++)
      {
        $arrResult[$strIDEmployee][$i] = getScheduleDetail($i, $arrData, $arrSchedule);
        //jika tidak ada working schedule, maka baca dari general setting
        if ($arrResult[$strIDEmployee][$i] == false)
        {
          //sabtu
          $isDayOff = 'f';
          if ($i == 6)
            $isDayOff = $isSaturdayOff;
          //minggu
          else if ($i == 0) $isDayOff = 't';
          $arrResult[$strIDEmployee][$i] = array("day_off"     => $isDayOff);
        }
      }
    }
    print_r($arrResult);
    // cari data hari libur
    // selain hari minggu
    $arrHoliday = getListHoliday($strFrom, $strThru);
    $intResult = 0;
    while ($dt1->format("Y-m-d") <= $dt2->format("Y-m-d"))
    {
      $intDayWeek = $dt1->DayofWeek();
      if (isset($arrResult[$strIDEmployee][$intDayWeek]))
        if ($arrResult[$strIDEmployee][$intDayWeek]['day_off'] != 't')
        {
          if (!isset($arrHoliday[$dt1->format("Y-m-d")]))
            $intResult++;
        }

      $dt1->addDays(1);
    }

    return $intResult;
  } //totalWorkDayEmployee*/
  //output: array of date
  function getListWorkDayEmployee($db, $strIDEmployee, $strFrom, $strThru)
  {
    include_once(dirname(dirname(__FILE__))."/includes/date/date.php");

    if ($strFrom == "" || $strThru == "") return 0;

    $intResult = 0;
    $strFrom = pgDateFormat($strFrom, "Y-m-d");
    $strThru = pgDateFormat($strThru, "Y-m-d");

    $arrResultDate = array();

    if ($strFrom > $strThru)
    {
      swap($strFrom, $strThru);
    }
    // ubah format dalam timestamp
    list($intYear, $intMonth, $intDay) = explode("-",$strFrom);
    $dt1 = new clsDate(intval($intYear), intval($intMonth), intval($intDay));
    $tsFrom = mktime(0,0,0,(int)$intMonth,(int)$intDay, (int)$intYear);
    $dtFrom = getdate($tsFrom);
    list($intYear, $intMonth, $intDay) = explode("-",$strThru);
    $dt2 = new clsDate(intval($intYear), intval($intMonth), intval($intDay));
    $tsThru = mktime(0,0,0,(int)$intMonth,(int)$intDay, (int)$intYear);
    $dtThru = getdate($tsThru);


    //ambil workschedule berdasarkan jadwal per susunan organisasi
    $tblWorkingSchedule = new cModel("hrd_work_schedule");
    $arrScheduleRaw = $tblWorkingSchedule->findAll(null, null, "workday ASC");
    $arrSchedule = array();
    foreach($arrScheduleRaw as $rowDb)
    {
      if ($rowDb['workday'] == -1)
      {
        //all days
        for($i = 0; $i <= 6; $i++)
        {
          $arrSchedule[$rowDb['table_name']][$rowDb['link_code']][$i] = $rowDb['day_off'];
        }
      }
      else
        $arrSchedule[$rowDb['table_name']][$rowDb['link_code']][$rowDb['workday']] = $rowDb['day_off'];
    }
    //sabtu
    if (getSetting("saturday") != 'f')
      $isSaturdayOff = 't';
    else
      $isSaturdayOff = 'f';

    $tblEmployee = new cModel("hrd_employee");
    if ($arrData = $tblEmployee->findById($strIDEmployee))
    {
      for($i = 0; $i <= 6; $i++)
      {
        $arrResult[$strIDEmployee][$i] = getScheduleDetail($i, $arrData, $arrSchedule);
        //jika tidak ada working schedule, maka baca dari general setting
        if ($arrResult[$strIDEmployee][$i] == false)
        {
          $isDayOff = 'f';
          if ($i == 6)
            $isDayOff = $isSaturdayOff;
          //minggu
          else if ($i == 0) $isDayOff = 't';
          $arrResult[$strIDEmployee][$i] = array("day_off"     => $isDayOff);
        }
      }
    }
    // cari data hari libur
    // selain hari minggu
    $arrHoliday = getListHoliday($strFrom, $strThru);

    $intResult = 0;
    while ($dt1->format("Y-m-d") <= $dt2->format("Y-m-d"))
    {
      $intDayWeek = $dt1->DayofWeek();
      if (isset($arrResult[$strIDEmployee][$intDayWeek]))
        if ($arrResult[$strIDEmployee][$intDayWeek]['day_off'] != 't')
        {
          if (!isset($arrHoliday[$dt1->format("Y-m-d")]))
            $arrResultDate[$dt1->format("Y-m-d")] = 1;
        }

      $dt1->addDays(1);
    }

    return $arrResultDate;
  } //getListWorkDay

  function getListHoliday($strFrom, $strThru)
  {
    $tbl = new cModel("hrd_calendar");
    $arrHoliday = $tbl->findAll("(holiday BETWEEN '$strFrom' AND '$strThru') AND status = 't' ",
                                  null, null, null, null,
                                  "holiday"); // cari yang libur saja
    return $arrHoliday;
  }
  // fungsi untuk membulatkan nilai uang, dengan pembulatan tertentu
  // tipe = jennis pembulatan, 1 (ke atas), 0=tergantung, -1 (ke bawah)
  function roundMoney($fltAmount, $fltRoundFactor = 100, $type = 1) {
    $fltResult = 0;

    if (is_numeric($fltAmount) && is_numeric($fltRoundFactor) && ($fltRoundFactor != 0)) {
      if (($fltAmount % $fltRoundFactor) == 0) { // gak perlu dibulatkan
        $fltResult = round($fltAmount);
      } else {
        $fltTmp = floor($fltAmount / $fltRoundFactor);
        $fltFloor = ($fltTmp * $fltRoundFactor);
        if ($type == 1) { // bulatkan ke atas
          $fltResult = $fltFloor + $fltRoundFactor;
        } else if ($type == -1) { // bulatkan ke bawah
          $fltResult = $fltFloor;
        } else {
          // lihat komposisi
          $fltSelisih = $fltAmount - $fltFloor;
          $fltResult = ($fltSelisih < ($fltRoundFactor / 2)) ? $fltFloor : ($fltFloor + $fltRoundFactor);
        }
      }
    }

    return $fltResult;

  }// roundMoney

  // fungsi untuk mengubah angka ke angka romawi
  function getRomans($intNumber) {
    // array menampung angka
    $arrRomans = array(0, "I", "II", "III", "IV", "V", "VI", "VII", "VIII", "IX", "X", "XI", "XII"); // sampai 12 dulu

    // cari aja di array, jika gak ada, kembalikan nilai semula
    $strResult = (isset($arrRomans[$intNumber])) ? $arrRomans[$intNumber] : $intNumber;

    return $strResult;
  }//getRomans

  // fungsi untuk nambahin karakter 0 didepan angka
  // intLen = panjang string yang diinginkan
  function addPrevZero($strTeks, $intLen) {
    $strResult = "";
    $intDiff = ($intLen - strlen($strTeks)); // cari selisih

    for ($i = 1;$i <= $intDiff;$i++) {
      $strResult .= "0";
    }

    return $strResult.$strTeks;

  }// addPrevZero

  // fungsi untuk mengambil data, siapa sih atasan dalam suatu department
  // berdasar department dan jabatannya
  function getDepartmentLeaderID($db, $strDepartmentCode = "") {
    $strResult = "";

    $strManCode = getSetting("department_head");
    if ($strManCode != "" && $strDepartmentCode != "") {
      // ambil jabatan dari employee
      $strSQL  = "SELECT id FROM hrd_employee WHERE active = 1 AND flag = 0 ";
      $strSQL .= "AND department_code = '$strDepartmentCode' ";
      $strSQL .= "AND position_code = '$strManCode' ";
      $resDb = $db->execute($strSQL);
      if ($rowDb = $db->fetchrow($resDb)) {
        $strResult = $rowDb['id'];
      }
    }
    return $strResult;
  } // getUserManager


  // fungsi untuk menampilkan header file excel
  function headeringExcel($strFileName) {
    header("Content-type: application/vnd.ms-excel");
    header("Content-Disposition: attachment; filename=$strFileName" );
    header("Expires: 0");
    header("Cache-Control: must-revalidate, post-check=0,pre-check=0");
    header("Pragma: public");
  }

  // Fungsi terbilang dari : http://daunsalam.net/artikel/terbilang.htm
  function terbilang($bilangan) {

    $kalimat = "";

    $angka = array('0','0','0','0','0','0','0','0','0','0',
                   '0','0','0','0','0','0');
    $kata = array('','satu','dua','tiga','empat','lima',
                  'enam','tujuh','delapan','sembilan');
    $tingkat = array('','ribu','juta','milyar','triliun');

    $bolMinus = ($bilangan < 0);
    if ($bolMinus) $bilangan = abs($bilangan);

    $panjang_bilangan = strlen($bilangan);

    /* pengujian panjang bilangan */
    if (!is_numeric($bilangan)) {
      return "";
    } else if ($panjang_bilangan > 15) {
      $kalimat = "to large ";
      return $kalimat;
    }

    /* mengambil angka-angka yang ada dalam bilangan,
       dimasukkan ke dalam array */
    for ($i = 1; $i <= $panjang_bilangan; $i++) {
      $angka[$i] = substr($bilangan,-($i),1);
    }

    $i = 1;
    $j = 0;

    /* mulai proses iterasi terhadap array angka */
    while ($i <= $panjang_bilangan) {

      $subkalimat = "";
      $kata1 = "";
      $kata2 = "";
      $kata3 = "";

      /* untuk ratusan */
      if ($angka[$i+2] != "0") {
        if ($angka[$i+2] == "1") {
          $kata1 = "seratus";
        } else {
          $kata1 = $kata[$angka[$i+2]] . " ratus";
        }
      }

      /* untuk puluhan atau belasan */
      if ($angka[$i+1] != "0") {
        if ($angka[$i+1] == "1") {
          if ($angka[$i] == "0") {
            $kata2 = "sepuluh";
          } elseif ($angka[$i] == "1") {
            $kata2 = "sebelas";
          } else {
            $kata2 = $kata[$angka[$i]] . " belas";
          }
        } else {
          $kata2 = $kata[$angka[$i+1]] . " puluh";
        }
      }

      /* untuk satuan */
      if ($angka[$i] != "0") {
        if ($angka[$i+1] != "1") {
          $kata3 = $kata[$angka[$i]];
        }
      }

      /* pengujian angka apakah tidak nol semua,
         lalu ditambahkan tingkat */
      if (($angka[$i] != "0") OR ($angka[$i+1] != "0") OR
          ($angka[$i+2] != "0")) {
        $subkalimat = "$kata1 $kata2 $kata3 " . $tingkat[$j] . " ";
      }

      /* gabungkan variabe sub kalimat (untuk satu blok 3 angka)
         ke variabel kalimat */
      $kalimat = $subkalimat . $kalimat;
      $i = $i + 3;
      $j = $j + 1;

    }

    /* mengganti satu ribu jadi seribu jika diperlukan */
    if (($angka[5] == "0") AND ($angka[6] == "0")) {
      $kalimat = str_replace("satu ribu","seribu",$kalimat);
    }

    if ($bolMinus) $kalimat = "minus ".$kalimat;

    return trim($kalimat);

  }

  // fungsi untuk mengambil kelas CSS dari suatu status dari permintaan / Request
  function getCssClass($intStatus = 0) {
    $strClass = "";
    switch ($intStatus) {
      case 0 :
        $strClass = "bgNewData";
        break;
      case 1 :
        $strClass = "bgCheckedData";
        break;
      case 2 :
        $strClass = "bgApprovedData";
        break;
      case -1 :
        $strClass = "bgDenied";
        break;
      case 6 :
        $strClass = "bgApproved2";
        break;
      default :
        $strClass = "";
        break;
    }
    return $strClass;
  }//getCssClass

  // fungsi untuk mengambil kelas CSS dari suatu status dari permintaan / Request
  function getStatusRemovalAsset($intStatus = 0) {
    $strStatus = "";
    switch ($intStatus)
    {
      case 0 :
        $strStatus = "New";
        break;
      case 1 :
        $strStatus = "Approved";
        break;
      case 2 :
        $strStatus = "being Checked";
        break;
      case 3 :
        $strStatus = "Rejected";
        break;
    }
    return $strStatus;
  }//getCssClass

  function totalDay($db, $strFrom, $strThru) {
  global $arrDay; // untuk menampung totalday, datefrom-datethru, agar menghemat pencarian, jika sudah pernah ada

  if (isset($arrDay["$strFrom.$strThru"])) return $arrDay["$strFrom.$strThru"]; // langsung dibalikin

  if ($strFrom == "" || $strThru == "") return 0;

    $intResult = 0;
    $strFrom = pgDateFormat($strFrom, "Y-m-d");
    $strThru = pgDateFormat($strThru, "Y-m-d");
    if ($strFrom > $strThru) {
      $intResult = 0;
    } else if ($strFrom == $strThru) {
      $intResult = 1;
    } else {
      // ubah format dalam timestamp
      list($intYear, $intMonth, $intDay) = explode("-",$strFrom);
      $tsFrom = mktime(0,0,0,(int)$intMonth,(int)$intDay, (int)$intYear);
      $dtFrom = getdate($tsFrom);
      list($intYear, $intMonth, $intDay) = explode("-",$strThru);
      $tsThru = mktime(0,0,0,(int)$intMonth,(int)$intDay, (int)$intYear);
      $dtThru = getdate($tsThru);

      $intSelisih = ($tsThru - $tsFrom) / 86400; // selisih dalam hari
      $intSelisihMinggu = floor($intSelisih / 7); // selisih dalam minggu
      $intResult = $intSelisih + 1; // karena hari awal dihitung juga
      $intModMinggu = ($intSelisih % 7);

      // cari data pengganti libur
      $strSQL  = "SElECT COUNT(id) AS total FROM hrd_calendar ";
      $strSQL .= "WHERE holiday BETWEEN '$strFrom' AND '$strThru' ";
      $strSQL .= "AND status = 'f' "; // cari yang libur saja
      $resDb = $db->execute($strSQL);
      if ($rowDb = $db->fetchrow($resDb)) {
        if (is_numeric($rowDb['total'])) {
          $intResult += $rowDb['total'];
        }
      }
    }

    return $intResult;
  } //etotalDay

  //fungsi cek apakah isi field tertentu sudah ada di suatu tabel
  //return 1 jika sudah ada, 0 jika tidak ditemukan datanya
  //DW
  function cek_field($db,$field,$table,$isi,$kriteria="")
  {
    //inisialisasi
    $bol = 0;

    //cek ada kriteria atau tidak
    if ($kriteria == "")
      $kriteria = " 1=1 ";

    //cek di database
    $strSQL = "SELECT ".$field." FROM ".$table." WHERE ".$field." = '".$isi."' AND $kriteria ";
    $res = $db->execute($strSQL);
    if ($row = $db->fetchrow($res))
    {
      $bol = 1;
    }
    return $bol;
  }

  function isEmployeeHoliday($db, $strDate, $strIDEmployee, $arrShift = null)
  {
     if (!isset($arrShift))
        $arrShift = getShiftSchedule($db, $strDate, $strDate, $strIDEmployee);

     // 1. cek dari shift schedule
     if (isset($arrShift[$strDate][$strIDEmployee]))
     {
        $arrTemp = $arrShift[$strDate][$strIDEmployee];
        $strShiftType = $arrTemp['shift_code'];
        if ($arrTemp['shift_off'] != "t")
        {
           // jika prioritas libur general lebih tinggi dari jadwal shift
           // return isHoliday($strDate);
           // jika prioritas jadwal shift lebih tinggi dari pada libur general
           return false;
        }
        else
        {
           return true;
        }
     }
     // 2. cek dari work schedule
     else
     {
       $arrWorkSchedule = getWorkSchedule($db, $strDate , $strIDEmployee);
       if (isset($arrWorkSchedule[$strIDEmployee]))
       {
          if ($arrWorkSchedule[$strIDEmployee]['day_off'] != "t")
          {
             // cek hari libur
             return isHoliday($strDate);
             // karena prioritas libur general lebih tinggi dari setting work schedule employee
             // jika prioritas setting work schedule employee lebih tinggi dari pada libur general, langsung panggil nilai false
          }
          else
          {
             return true;
          }
       }
       else
       {
          return isHoliday($strDate);
       }
     }
  }
  function scopeData(&$strDataEmployee, &$strDataSubSection, &$strDataSection, &$strDataDepartment, &$strDataDivision, $strDataUserRole, $arrUserInfo, &$strDataBranch, &$strDataManagement)
  {
  	$bolScoped = true;
		if ($strDataUserRole == ROLE_SUPERVISOR)
    {
    	if ($arrUserInfo['management_code'] != "") $strDataManagement = $arrUserInfo['management_code'];
    	if ($arrUserInfo['division_code'] != "")    $strDataDivision =    $arrUserInfo['division_code'];
      if ($arrUserInfo['department_code'] != "")  $strDataDepartment =  $arrUserInfo['department_code'];
      if ($arrUserInfo['section_code'] != "")     $strDataSection =     $arrUserInfo['section_code'];
      if ($arrUserInfo['sub_section_code'] != "") $strDataSubSection =  $arrUserInfo['sub_section_code'];
    }
    else if ($strDataUserRole == ROLE_SUPERVISOR_BRANCH)
    {
    	if ($arrUserInfo['management_code'] != "") $strDataManagement = $arrUserInfo['management_code'];
      if ($arrUserInfo['division_code'] != "")    $strDataDivision =    $arrUserInfo['division_code'];
      if ($arrUserInfo['department_code'] != "")  $strDataDepartment =  $arrUserInfo['department_code'];
      if ($arrUserInfo['section_code'] != "")     $strDataSection =     $arrUserInfo['section_code'];
      if ($arrUserInfo['sub_section_code'] != "") $strDataSubSection =  $arrUserInfo['sub_section_code'];
      if ($arrUserInfo['branch_code'] != "")      $strDataBranch =  $arrUserInfo['branch_code'];
    }
    else if ($strDataUserRole == ROLE_EMPLOYEE)
    {
      $strDataEmployee = $arrUserInfo['employee_id'];
      if ($arrUserInfo['management_code'] != "") $strDataManagement = $arrUserInfo['management_code'];
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
    	if ($arrUserInfo['management_code'] != "") $strDataManagement = $arrUserInfo['management_code'];
      if ($arrUserInfo['division_code'] != "")      $strDataDivision =  $arrUserInfo['division_code'];
      if ($arrUserInfo['branch_code'] != "")      $strDataBranch =  $arrUserInfo['branch_code'];
    }
		
    else $bolScoped = false;
    return $bolScoped;

  }

  //fungsi untuk membatasi data entry untuk page general (diluar salary, compensation, dan benefit)
  //hanya role admin dan super yang dapat melakukan entry data untuk seluruh karyawan, selebihnya hanya entri data sendiri
  //kecuali supervisor, bisa mengedit data bawahannya
  /*function scopeGeneralDataEntry(&$strDataEmployee, $strDataUserRole, $arrUserInfo, $bolIsNew = false)
  {
    $bolScoped = true;
    if ($strDataUserRole == ROLE_EMPLOYEE || ($strDataUserRole == ROLE_SUPERVISOR && $bolIsNew))
      $strDataEmployee = $arrUserInfo['employee_id'];
    else if ($strDataUserRole == ROLE_ADMIN || $strDataUserRole == ROLE_SUPER)
    {
      if ($strDataEmployee == "") $strDataEmployee = $arrUserInfo['employee_id'];
      $bolScoped = false;
    }
    return $bolScoped;
  }  //fungsi untuk membatasi data entry untuk page general (diluar salary, compensation, dan benefit)
  */
  //edit by Ajeng
  function scopeGeneralDataEntry(&$strDataEmployee, $strDataUserRole, $arrUserInfo, $bolIsNew = false, &$strDataBranch)
  {
    $bolScoped = true;
    if ($strDataUserRole == ROLE_EMPLOYEE)// || ($strDataUserRole == ROLE_SUPERVISOR && $bolIsNew))
    {
      if ($strDataEmployee == "") $strDataEmployee = $arrUserInfo['employee_id'];
      if ($strDataBranch == "") $strDataBranch = $arrUserInfo['branch_code'];
    }
    else if ($strDataUserRole == ROLE_ADMIN || $strDataUserRole == ROLE_SUPER)
    {
      $bolScoped = false;
    }
    else if ($strDataUserRole == ROLE_SUPERVISOR)
    {
      //if ($strDataEmployee == "") $strDataEmployee = $arrUserInfo['employee_id'];
      //if ($strDataBranch == "") $strDataBranch = $arrUserInfo['branch_code'];
      $bolScoped = false;
    }
    else if ($strDataUserRole == ROLE_SUPERVISOR_BRANCH)
    {
      //if ($strDataEmployee == "") $strDataEmployee = $arrUserInfo['employee_id'];
      if ($strDataBranch == "") $strDataBranch = $arrUserInfo['branch_code'];
      $bolScoped = false;
    }
    else if ($strDataUserRole == ROLE_BRANCH_ADMIN)
    {
      if ($strDataBranch == "") $strDataBranch = $arrUserInfo['branch_code'];
      $bolScoped = false;
    }
    else if ($strDataUserRole == ROLE_DIVISION_BRANCH_ADMIN)
    {
      if ($strDataDivision == "") $strDataDivision = $arrUserInfo['division_code'];
      if ($strDataBranch == "") $strDataBranch = $arrUserInfo['branch_code'];
      $bolScoped = false;
    }
    return $bolScoped;
  }

  //hanya role admin dan super yang dapat melakukan entry data untuk seluruh karyawan, selebihnya hanya entri data sendiri
  function scopeCBDataEntry(&$strDataEmployee, $strDataUserRole, $arrUserInfo)
  {
    $bolScoped = true;
    if ($strDataUserRole != ROLE_SUPER && $strDataUserRole != ROLE_ADMIN)
      $strDataEmployee = $arrUserInfo['employee_id'];
    else $bolScoped = false;
    return $bolScoped;
  }

  function getRequestStatusClass($intStatus)
  {
      switch ($intStatus)
      {
        case 0 :
          return "class=bgNewData";
        case 1 :
          return "class=bgCheckedData";
        case 2 :
          return "class=bgApprovedData";
        case 6 :
          return "class=bgApprovedData2";
        case -1 :
          return "class=bgDenied";
        default :
          return "";
      }
  }
  function updateNote($db, $strTable, $strDataID, $strModifier, $strNote1, $strNote2, $strDataStatus, $intActivity, $intModule = MODULE_OTHER)
  {
    $strSQL  = "UPDATE $strTable ";
    $strSQL .= "SET modified_by = '$strModifier', ";
    $strSQL .= "note = '$strNote2' ";
    $strSQL .= "WHERE id = '$strDataID' ";
    $resExec = $db->execute($strSQL);
    writeLog($intActivity, $intModule, $strNote1.$strNote2, $strDataStatus);
  }

  function getNIK($db, $id_employee)
  {
  	if (is_integer($id_employee)){
	    $strSQL  = "SELECT employee_id FROM hrd_employee WHERE id = $id_employee; ";
	    $resExec = $db->execute($strSQL);
	    if ($rowDb = $db->fetchrow($resExec)) {
	      $result = $rowDb['employee_id'];
	    }else{ 
	    	$result = $id_employee;
	    }
	  }else{
	  	$result = $id_employee;
	  }
    return $result;
  }


  function accessDenied($strReferer)
  { return "<div align=center style=\"font-family:verdana;color:red\"><br><br><br>".getWords('access denied')." | <a href=\"".$strReferer."\">Back to previous page</a></div>";
  }

// remove by key:
function array_remove_key ()
{
  $args  = func_get_args();
  return array_diff_key($args[0],array_flip(array_slice($args,1)));
}
// remove by value:
function array_remove_value ()
{
  $args = func_get_args();
  return array_diff($args[0],array_slice($args,1));
}
function getCompanyCode()
{
  global $arrUserInfo;
  return $arrUserInfo['company_code'];
}


  // fungsi untuk get post atau session dan set session
  // $strPostSufix = nama object (kata setelah 'data', misalnya untuk dataEmployee => 'Employee', untuk dataDivision => 'Division'),
  // $defaultValue = nilai default jika tidak ada nilai yang di-post atau tidak ada nilai pada session,
  // $priorityValue = nilai yang ditentukan dan digunakan walaupun ada nilai yang di-post atau ada nilai session (misalnya restriksi),
  // $strSessionSufix = nama variable session (perlakuannya sama dengan strPostSufix, jika nilainya null, maka disamakan dengan strPostSufix,
  // $bolSetSession = set nilai session setelah mendapatkan nilai yang akan digunakan,
  // $strPostPrefix = kata depan pada object misalnya "data",
  // $strSessionPrefix = kata depan pada var session misalnya "sessiondata"
  function getInitialValue($strPostSufix, $defaultValue = "", $priorityValue = "", $strSessionSufix = null, $bolSetSession = true, $strPostPrefix = "data", $strSessionPrefix = "sessiondata", $bolAlertCall = false)
  {
    global $_SESSION;
    global $_POST;
    if ($strSessionSufix == null) $strSessionSufix = $strPostSufix;

    if ($bolAlertCall)
      $value = $priorityValue;
    else
    {
      //set nilai: urutan prioritas: priority, post, session, default
      if (!($priorityValue === "")) $value = $priorityValue;
      else
      {
        if (isset($_POST[$strPostPrefix.$strPostSufix]))
          $value = $_REQUEST[$strPostPrefix.$strPostSufix];
        else
          $value    = (isset($_SESSION[$strSessionPrefix.$strSessionSufix])) ? $_SESSION[$strSessionPrefix.$strSessionSufix] : $defaultValue;
      }
    }
    //echo "<br>".$strPostSufix."|".$_SESSION[$strSessionPrefix.$strSessionSufix]."|".$value;
    if ($bolSetSession) $_SESSION[$strSessionPrefix.$strSessionSufix] = $value;
    return $value;
  }

  //fungsi yang memanggil getInitialValue dengan menyelipkan nilai true pada $bolAlertCall
  //menyiapkan data initial untuk form yang diload dari link notifikasi home page
  //parameter bolAlertCall diset true (fix)
  //default untuk bolSetSession diubah menjadi false
  function getInitialValueAlert($strPostSufix, $defaultValue = "", $priorityValue = "", $strSessionSufix = null, $bolSetSession = false, $strPostPrefix = "data", $strSessionPrefix = "sessiondata")
  {
    getInitialValue($strPostSufix, $defaultValue, $priorityValue, $strSessionSufix, $bolSetSession, $strPostPrefix, $strSessionPrefix, true);
  }

  //belum lengkap
  function buidCriteria($arrData, $strKriteriaCompany, $strPrefix = "data")
  {
    foreach($arrData as $strKey => $strValue)
    {
      if ($strValue != "")
        $strKriteria .= "AND ". _underscore($strKey, $strPrefix)." = '".$strValue."'";
    }
  }

  function _underscore($strKey, $strPrefix)
  {
    strtolower(preg_replace('/(?<=\\w)([A-Z])/', '_\\1', preg_replace('/$strPrefix/i', '', $strKey)));
  }

  function generateSelectDay($name, $default = "", $attribute = "", $event = "", $hasEmptyData = false, $emptyData = null)
  {
    $strResult = "<select name=\"".$name."\" id=\"".$name."\" ".$attribute." ".$event.">\n";
    if ($hasEmptyData && $emptyData !== null)
    {
      $strResult .= "  <option value=\"\">".$emptyData."</option>\n";
    }
    for ($i=1; $i<=31; $i++)
    {
      if ($i == $default)
        $strResult .= "  <option value=\"".$i."\" selected>".$i."</option>\n";
      else
        $strResult .= "  <option value=\"".$i."\">".$i."</option>\n";
    }
    $strResult .= "</select>";
    return $strResult;
  }


  /*
  ======================================================================================================================
  ----------------------------------------------------------------------------------------------------------------------
  DESK   : Fungsi-Fungsi dibawah  ini adlaah fungsi yang ditambahkaan untuk keperluar penambahan recruitment modul
  ----------------------------------------------------------------------------------------------------------------------
  ======================================================================================================================
  */

  //======= fungsi untuk menambahkan 0 di depan angka sampai karakter berukuran tertentu ===============================
  function leadingZero($num, $leading)
  {
    return sprintf("%0".$leading."d", $num);
  }
  //============== END ================================================================================================

  //====================== fungsi untuk mengecek apakah user berhak mengakses data band tertentu ======================
  function isBandAccess($strBand)
  {
    if (!$_SESSION['sessionIsSpecifyBand']) return true;

    if (in_array($strBand, $_SESSION['sessionBandList']))
      return true;
    else return false;
  }
  //============================END=====================================================================================

  //============= fungsi untuk mengirim daftar band yang boleh diakses oleh user,=======================================
  // dalam string (untuk QUERY IN (xxxx))
  function getBandAccessCriteria()
  {
    $strResult = "";
    if (isset ($_SESSION['sessionBandList']))
    {
      foreach ($_SESSION['sessionBandList'] AS $strBand)
      {
        if ($strResult != "") $strResult .= ", ";
        $strResult .= "'$strBand'";
      }
    }
    else
    {
      $strResult = "all"; // artinya semua berhak
    }
    return $strResult;
  }
  //======================= END ==========================================================================================

  function callChangeStatus() {

    global $_REQUEST;
    global $db;
    global $intStatus;

    if (isset($_REQUEST['btnChecked'])) $intStatus = REQUEST_STATUS_CHECKED;
    else if (isset($_REQUEST['btnApproved'])) $intStatus = REQUEST_STATUS_APPROVED;
    else if (isset($_REQUEST['btnApproved2'])) $intStatus = REQUEST_STATUS_APPROVED_2;
    else if (isset($_REQUEST['btnDenied'])) $intStatus = REQUEST_STATUS_DENIED;
    else if (isset($_REQUEST['btnAcknowledged'])) $intStatus = REQUEST_STATUS_ACKNOWLEDGED;
    else if (isset($_REQUEST['btnClosed'])) $intStatus = REQUEST_STATUS_CLOSED;
    changeStatus($db, $intStatus);

  }
  function getStatusUpdateString($intStatus)
  {
    if ($intStatus == REQUEST_STATUS_CHECKED)
      $strUpdate = "checked_by = '" .$_SESSION['sessionUserID']."', checked_time = now(), ";
    else if ($intStatus == REQUEST_STATUS_APPROVED)
      $strUpdate = "approved_by = '" .$_SESSION['sessionUserID']."', approved_time = now(), ";
    else if ($intStatus == REQUEST_STATUS_DENIED)
      $strUpdate = "denied_by = '" .$_SESSION['sessionUserID']."', denied_time = now(), ";
    else if ($intStatus == REQUEST_STATUS_ACKNOWLEDGED)
      $strUpdate = "acknowledged_by = '" .$_SESSION['sessionUserID']."', acknowledged_time = now(), ";
    else if ($intStatus == REQUEST_STATUS_CLOSED)
      $strUpdate = "closed_by = '" .$_SESSION['sessionUserID']."', closed_time = now(), ";
    else if ($intStatus == REQUEST_STATUS_APPROVED_2)
      $strUpdate = "approved2_by = '" .$_SESSION['sessionUserID']."', approved2_time = now(), ";
    return $strUpdate;

  }

  function getEditFile($strFileName)
  {
    return str_replace("list","edit", $strFileName);
  }
  function isProcessable($intOldStatus, $intNewStatus)
  {
    // Apakah statusnya naik?
    $bolProcess1 = ($intOldStatus < $intNewStatus && $intOldStatus != REQUEST_STATUS_DENIED) || (($intOldStatus != REQUEST_STATUS_APPROVED||$intOldStatus != REQUEST_STATUS_APPROVED_2) && $intNewStatus == REQUEST_STATUS_DENIED);
    // Apakah statusnya tidak loncat?
//    $bolProcess2 = ($intNewStatus - $intOldStatus == 1 || $intNewStatus == REQUEST_STATUS_DENIED);
    if ($bolProcess1) return true;
    else
    {
      showNotification();
      return false;
    }

  }


  function showNotification()
    {
      echo "<script>
         alert(\"Status change is missmatched, only change the data status in following order: New-Check-Approved-Approved 2\");
         </script>";
    }




  /*
    countAdditionalAbsence
    returns an array: --> Key = id_employee, Value = floor((late_num + early_num)/2)
  */
  function countAdditionalAbsence($db, $strDateFrom = "now()", $strDateThru = "now()", $tolerance = 10){
    $arrAdditionalAbsence = array();
    $strSQL = "
      SELECT t1.id, t1.employee_id, COUNT(t2.late_duration) AS late_num FROM hrd_employee AS t1
      LEFT JOIN hrd_attendance AS t2 ON t1.id = t2.id_employee
      WHERE
        (attendance_date BETWEEN '$strDateFrom' AND '$strDateThru')
        AND late_duration > $tolerance
      GROUP BY t1.id, t2.id_employee, t1.employee_id
      ORDER BY t1.id
      ";
    $resDb = $db->execute($strSQL);
    //key = id_employee, value = # of late
    while($rowDb = $db->fetchrow($resDb)) $arrAdditionalAbsence[$rowDb['id']] = $rowDb['late_num'];

    $strSQL = "
      SELECT t1.id, t1.employee_id, COUNT(t2.early_duration) AS early_num FROM hrd_employee AS t1
      LEFT JOIN hrd_attendance AS t2 ON t1.id = t2.id_employee
      WHERE
        (attendance_date BETWEEN '$strDateFrom' AND '$strDateThru')
        AND early_duration > $tolerance
      GROUP BY t1.id, t2.id_employee, t1.employee_id
      ORDER BY t1.id
      ";
    $resDb = $db->execute($strSQL);
    //key = id_employee, value = # of late
    while($rowDb = $db->fetchrow($resDb)){
      if(isset($arrAdditionalAbsence[$rowDb['id']])) $arrAdditionalAbsence[$rowDb['id']] += $rowDb['early_num'];
      else $arrAdditionalAbsence[$rowDb['id']] = $rowDb['early_num'];
    }

    foreach($arrAdditionalAbsence as &$tempAddAbs) $tempAddAbs = floor($tempAddAbs/2);

    /*
      SELECT t1.id, t1.employee_id, COUNT(t2.late_duration) AS late_num FROM hrd_employee AS t1
      LEFT JOIN hrd_attendance AS t2 ON t1.id = t2.id_employee
      WHERE
        (attendance_date BETWEEN $strDateFrom AND $strDateThru)
        AND late_duration > $tolerance
      GROUP BY t1.id, t2.id_employee, t1.employee_id
      ORDER BY t1.id

      SELECT t1.id, t1.employee_id, COUNT(t2.early_duration) AS early_num FROM hrd_employee AS t1
      LEFT JOIN hrd_attendance AS t2 ON t1.id = t2.id_employee
      WHERE
        (attendance_date BETWEEN $strDateFrom AND $strDateThru)
        AND early_duration > $tolerance
      GROUP BY t1.id, t2.id_employee, t1.employee_id
      ORDER BY t1.id
    */
    return $arrAdditionalAbsence;
  }
	// ============= END ================================================

  function _debug_array($arr){
    echo "<pre>";
    print_r($arr);
    echo "</pre>";
  }

  // fungsi untuk mencari employee di salary calculation
  function getEmployeeInSalary($db, $strID)
  {
    global $db;

    $arrResult = array();

    $strSQL = "SELECT id_employee FROM hrd_salary_detail WHERE id_salary_master = $strID;";
    $resDb = $db->execute($strSQL);
    while ($rowDb = $db->fetchrow($resDb)) {
        $arrResult[$rowDb['id_employee']] = $rowDb['id_employee'];
    }

    return $arrResult;
  }// getEmployeeInSalary

  // fungsi untuk mengupdate status transfer salary default menjadi release
    function setEmployeeTransferRelease($db, $strID, $arrData)
    {
      global $db;

      $strSQL = "";
      //hardcode
      foreach ($arrData as $key => $index){
        $strSQL  = "INSERT INTO hrd_salary_transfer_status (id_salary_master, id_employee, transfer_code) VALUES ($strID, $index, '0');";
        $resDb = $db->execute($strSQL);
      }

      $resDb = $db->execute($strSQL);
    }// setEmployeeTransferRelease

  // fungsi untuk mencari employee yang status EC nya masih OPEN di periode salary
  function getEmployeeECOpen($db, $strDateFrom, $strDateThru)
  {
    global $db;

    $arrResult = array();

    $strSQL = "SELECT id FROM hrd_employee WHERE resign_date between '$strDateFrom' AND '$strDateThru' AND (ec_status <= 0 OR ec_status IS NULL)";
    $resDb = $db->execute($strSQL);
    while ($rowDb = $db->fetchrow($resDb)) {
        $arrResult[$rowDb['id']] = $rowDb['id'];
    }

    return $arrResult;
  }// getEmployeeECOpen

 // fungsi untuk mengupdate status employee yang status EC nya masih OPEN di periode salary
  function setEmployeeECOpentoHold($db, $strID, $arrData)
  {
    global $db;

    $strSQL = "";
    //hardcode
    foreach ($arrData as $key => $index){
      $strSQL  = "UPDATE hrd_salary_transfer_status SET transfer_code = '2' WHERE id_salary_master = $strID AND id_employee = $index;";
      $resDb = $db->execute($strSQL);
    }

    $resDb = $db->execute($strSQL);
  }// setEmployeeECOpentoHold

  // fungsi untuk mencari employee yang status Alpha > 2 hari di periode salary
  function getEmployeeAlpha($db, $strDateFrom, $strDateThru)
  {
    global $db;

    $arrResult = array();

    $strSQL = "SELECT id_employee, count(id) as total FROM hrd_absence_detail WHERE absence_date between '$strDateFrom' AND '$strDateThru' AND absence_type = 'A' GROUP BY id_employee";
    $resDb = $db->execute($strSQL);
    while ($rowDb = $db->fetchrow($resDb)) {
        if($rowDb['total'] >= 2)
            $arrResult[$rowDb['id_employee']] = $rowDb['id_employee'];
    }

    return $arrResult;
  }// getEmployeeAlpha

 // fungsi untuk mengupdate status employee yang Alpha > 2 to hold
  function setEmployeeAlphatoHold($db, $strID, $arrData)
  {
    global $db;

    $strSQL = "";
    //hardcode
    foreach ($arrData as $key => $index){
      $strSQL  = "UPDATE hrd_salary_transfer_status SET transfer_code = '1' WHERE id_salary_master = $strID AND id_employee = $index;";
      $resDb = $db->execute($strSQL);
    }

    $resDb = $db->execute($strSQL);
  }// setEmployeeAlphatoHold

  // fungsi untuk mencari employee yang belum punya no rekening
  function getEmployeeNoAccount($db, $strID)
  {
    global $db;

    $arrResult = array();

    $strSQL = "SELECT id_employee FROM hrd_salary_detail AS t0
                LEFT JOIN hrd_employee AS t1 ON t0.id_employee = t1.id
                WHERE (bank_account = '' OR bank_account = '0' OR bank_account is null) AND 
                id_salary_master = $strID ;";
    $resDb = $db->execute($strSQL);
    while ($rowDb = $db->fetchrow($resDb)) {
        $arrResult[$rowDb['id_employee']] = $rowDb['id_employee'];
    }

    return $arrResult;
  }// getEmployeeNoAccount

 // fungsi untuk mengupdate status employee yang belum punya no rekening to hold
  function setEmployeeNoAccounttoHold($db, $strID, $arrData)
  {
    global $db;

    $strSQL = "";
    //hardcode
    foreach ($arrData as $key => $index){
      $strSQL  = "UPDATE hrd_salary_transfer_status SET transfer_code = '5' WHERE id_salary_master = $strID AND id_employee = $index;";
      $resDb = $db->execute($strSQL);
    }

    $resDb = $db->execute($strSQL);
  }// setEmployeeNoAccounttoHold

  // fungsi untuk mencari employee yang belum punya no rekening
  function getEmployeeOverdue($db, $strDate)
  {
    global $db;

    $arrResult = array();

    $strSQL = "SELECT id FROM hrd_employee
                WHERE due_date < '$strDate' AND resign_date is NULL ;";
    $resDb = $db->execute($strSQL);
    while ($rowDb = $db->fetchrow($resDb)) {
        $arrResult[$rowDb['id']] = $rowDb['id'];
    }

    return $arrResult;
  }// getEmployeeOverdue

 // fungsi untuk mengupdate status employee yang belum punya no rekening to hold
  function setEmployeeOverduetoHold($db, $strID, $arrData)
  {
    global $db;

    $strSQL = "";
    //hardcode
    foreach ($arrData as $key => $index){
      $strSQL  = "UPDATE hrd_salary_transfer_status SET transfer_code = '3' WHERE id_salary_master = $strID AND id_employee = $index;";
      $resDb = $db->execute($strSQL);
    }

    $resDb = $db->execute($strSQL);
  }// setEmployeeOverduetoHold

  function isEligibleApprove2($db){
    global $_SESSION;

    $strGroupCode = $_SESSION['sessionIdGroup'];
    $strSQL = "SELECT eligible_approve2 FROM adm_group WHERE id_adm_group = $strGroupCode";
//    echo $strSQL;
    $resDb = $db->execute($strSQL);
    while($rowDb = $db->fetchrow($resDb)){
//      echo $rowDb['eligible_approve2'];
      if($rowDb['eligible_approve2'] == 't')
      {
        return true;
      }
      else
      {
        return false;
      }
    }
  }
  //Add By Heri
  function getAllUserInfoForList($db)
  {
    $arrResult = array();
    $strSQL  = "SELECT id_adm_user, name FROM adm_user";
    $resDb = $db->execute($strSQL);
    while ($rowDb = $db->fetchrow($resDb))
    {
      $arrResult[$rowDb['id_adm_user']] = ($rowDb['name'] == "") ? $rowDb['login_name'] : $rowDb['name'];
    }
    return $arrResult;
  } //getAllUserInfo
	function getDataListUser($arrUserList = null, $default = null, $isHasEmpty = false, $emptyData = null)
  {
    global $strDataCompany;
    global $db;
    if (empty($arrUserList)){
    	$arrData = getAllUserInfoForList($db);
    }else{
    	$arrData = $arrUserList;
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
  }//getDataListUser
  function check_plain($text) {
	  return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
	}
	function getDataYearGlobal(){
    $currYear = intval(date("Y"));
    $arrResult = array();
    for($i = $currYear; $i > $currYear - 10; $i--){
      if ($i == $currYear){
        $arrResult[] = array("value" => $i, "text" => $i, "selected" => true);
      }else{
        $arrResult[] = array("value" => $i, "text" => $i, "selected" => false);
      }
		}
    return $arrResult;
  }
  function getDataLevelGlobal(){
    global $bolCanViewManagerial;
    $arrResult = array();
    if (!$bolCanViewManagerial){
      $arrResult[] = array("value" => 1, "text" => "Staff Only", "selected" => true);
    }else{
      $arrResult[] = array("value" => 0, "text" => "All Employee");
      $arrResult[] = array("value" => 1, "text" => "Staff Only");
      $arrResult[] = array("value" => 2, "text" => "Managerial Only");
    }
    return $arrResult;
  }
  
  function newArrayAllowance($arrayMA = null){
  	$newArrayAllowance = array();
  	if (!empty($arrayMA)){
  		//Create Benefit With Tax Allowance
  		$benefitTaxAllowance = array();
  		$benefitNonTaxAllowance = array();
  		$defaultAllowance = array();
  		$otherAllowance = array();
  		$firstViewAllowance = array();
  		$managerialAllowance = array();
  		if (count($arrayMA)){
  			foreach ($arrayMA as $strCode => $maData){
  				$splitCode = explode('_', $maData['allowance_code']);
  				$isManagerial = false;
  				if (strtolower($splitCode[count($splitCode) - 1]) == 'managerial'){
  					$isManagerial = true;
  				}
  				if ($isManagerial){
  					$managerialAllowance[] = $maData;
  				}else{
	  				if ($maData['benefit'] == 't' && $maData['tax'] == 't'){
		  					$benefitTaxAllowance[] = $maData;
		  					$firstViewAllowance[] = $maData;	  				
	  				}else if ($maData['benefit'] == 't' && $maData['tax'] == 'f'){
	  						$benefitNonTaxAllowance[] = $maData;  					
	  				}else if($maData['benefit'] == 'f' && $maData['is_default'] == 't'){
	  					$defaultAllowance[] = $maData;
	  					$firstViewAllowance[] = $maData;
	  				}else if($maData['benefit'] == 'f' && $maData['is_default'] == 'f'){
	  					$otherAllowance[] = $maData;
	  					$firstViewAllowance[] = $maData;
	  				}
	  			}
  			}
  		}
  		$newArrayAllowance['benefit_tax'] = $benefitTaxAllowance;
  		$newArrayAllowance['benefit_non_tax'] = $benefitNonTaxAllowance;
  		$newArrayAllowance['default_allowance'] = $defaultAllowance;
  		$newArrayAllowance['other_allowance'] = $otherAllowance;
  		$newArrayAllowance['first_view_allowance'] = $firstViewAllowance;
  		$newArrayAllowance['managerial_allowance'] = $managerialAllowance;
  	}
  	return $newArrayAllowance;
  }
  
  function newArrayDeduction($arrayMD = null){
  	$newArrayDeduction = array();
  	if (!empty($arrayMD)){
  		//Create Benefit With Tax Allowance
  		$defaultDeduction = array();
  		$otherDeduction = array();
  		$managerialDeduction = array();
  		if (count($arrayMD)){
  			foreach ($arrayMD as $strCode => $mdData){
  				$splitCode = explode('_', $mdData['deduction_code']);
  				$isManagerial = false;
  				if (strtolower($splitCode[count($splitCode) - 1]) == 'managerial'){
  					$isManagerial = true;
  				}
  				if ($isManagerial){
  					$managerialDeduction[] = $mdData;
  				}else{
  					if($mdData['is_default'] == 't'){
	  					$defaultDeduction[] = $mdData;
	  				}else if($mdData['is_default'] == 'f'){
	  					$otherDeduction[] = $mdData;
	  				}
	  			}
  			}
  		}
  		$newArrayDeduction['default_deduction'] = $defaultDeduction;
  		$newArrayDeduction['other_deduction'] = $otherDeduction;
  		$newArrayDeduction['managerial_deduction'] = $managerialDeduction;
  	}
  	return $newArrayDeduction;
  }
?>
