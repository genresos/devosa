<?php
  include_once('../global/session.php');
  include_once('../global.php');
  include_once('../includes/datagrid/datagrid.php');
  include_once('../includes/form2/form2.php');
  include_once('../classes/adm/adm_user.php');
  include_once('../global/common_function.php');
  include_once('../global/common_data.php');

  $dataPrivilege = getDataPrivileges(basename($_SERVER['PHP_SELF']), $bolCanView, $bolCanEdit, $bolCanDelete, $bolCanApprove);
  if (!$bolCanView) die(getWords('view denied'));
 
  $db = new CdbClass;
  if ($db->connect())
  {
    $arrUser = getAllUserInfo($db);
    $strDataID = (isset($_REQUEST['dataID'])) ? $_REQUEST['dataID'] : "";

    if ($bolCanEdit)
    {
      $f = new clsForm("formInput",2, "100%", "");
      $f->caption = strtoupper(vsprintf(getWords("input data %s"), getWords("user")));
      //$f->addHelp(getWords("help for")." ".$dataPrivilege['menu_name'], getHelps("master user"), 8, 167, 400, 300);

      $f->addHidden("dataID", $strDataID);
      //$f->addFieldSet("Information");

      $f->addInput(getWords("login name"), "dataLogin", "", array("size"=>30), "string", true, true, true);
      $f->addPassword(getWords("password"), "dataPwd", "", array("size" => 20), "string", false, true, true, "", "&nbsp;".getWords("leave blank if no change was made"));
      $f->addInput(getWords("user name"), "dataName", "", array("size" => 50), "string", true, true, true);
      //$f->addInput(getWords("employee id"), "dataEmployee", "", array("size" => 10), "string", false, true, true, "", "&nbsp;(".getWords("if any").")");
      $f->addInputAutoComplete(getWords("employee ID"), "dataEmployee", getDataEmployee(), array("size" => 50), "string", false);

      $f->addSelect(getWords("company access right"), "dataCompany", getDataListCompany(-1, true, array("value" => -1, "text" => "ALL")));
      $f->addSelect(getWords("group"), "dataGroup", getDataGroup());
      $f->addCheckBox(getWords("active"), "dataActive", false);
      $f->addSelect(getWords("default module"), "dataIdAdmModule", getDataModule());
      
      $f->addSubmit("btnSave", getWords("save"), array("onClick" => "return confirm('".getWords('do you want to save this entry?')."');"), true, true, "", "", "saveData()");
      $f->addButton("btnAdd", getWords("add new user"), array("onClick" => "javascript:myClient.editData(0);"));
      
      //$f->validateEntryBeforeSubmit=false;
      $formInput = $f->render();
    }
    else
      $formInput = "";
    
    $myDataGrid = new cDataGrid("formData","DataGrid1");
    $myDataGrid->setAJAXCallBackScript(basename($_SERVER['PHP_SELF']));
    
    if ($bolCanDelete)
      $myDataGrid->addColumnCheckbox(new DataGrid_Column("chkID", "id_adm_user", array('width' => '30'), array('align'=>'center', 'nowrap' => '')));
    
    $myDataGrid->addColumnNumbering(new DataGrid_Column("No", "", array('width'=>'30'), array('nowrap'=>'')));
    $myDataGrid->addColumn(new DataGrid_Column(getWords("login name"), "login_name", array('width' => '130'),array('nowrap' => '')));
    $myDataGrid->addColumn(new DataGrid_Column(getWords("name"), "name", array('width' => ''), array('nowrap' => '')));
    $myDataGrid->addColumn(new DataGrid_Column(getWords("employee id"), "employee_id", array('width' => '110'), array('align'=>'center','nowrap' => '')));
    $myDataGrid->addColumn(new DataGrid_Column(getWords("company access right"), "company_name", array('width' => '110'), array('align'=>'center','nowrap' => '')));
    //$myDataGrid->addColumn(new DataGrid_Column(getWords("company"), "company_name", null, array('nowrap' => '')));
    $myDataGrid->addColumn(new DataGrid_Column(getWords("group name"), "group_name", null, array('nowrap' => '')));
    //$myDataGrid->addColumn(new DataGrid_Column(getWords("company"), "company_name", null, array('nowrap' => '')));
    $myDataGrid->addColumn(new DataGrid_Column(getWords("status"), "active", array('width' => '80'), array('align' => 'center', 'nowrap' => ''), true, false, "", "", "", false));
    if ($bolCanEdit)
      $myDataGrid->addColumn(new DataGrid_Column("", "", array('width' => '60'), array('align' => 'center', 'nowrap' => ''), false, false, "","printEditLink()", "", false));
    
    if ($bolCanDelete)
      $myDataGrid->addSpecialButton("btnDelete","btnDelete","submit","Delete","onClick=\"javascript:return myClient.confirmDelete();\"","deleteData()");
    $myDataGrid->addButtonExportExcel("Export Excel", $dataPrivilege['menu_name'].".xls", getWords($dataPrivilege['menu_name']));

    $myDataGrid->getRequest();
    //--------------------------------
    //get Data and set to Datagrid's DataSource by set the data binding (bind method)
    $strSQLCOUNT  = "
      SELECT COUNT(*) AS total 
      FROM
        (
          SELECT a.*, b.code, b.name as group_name, c.id as id_company, c.company_name  as company_name 
            FROM adm_user AS a INNER JOIN adm_group AS b ON a.id_adm_group = b.id_adm_group
            INNER JOIN hrd_company AS c on a.id_adm_company = c.id
        ) AS x ";
    $strSQL  = "
          SELECT a.*, b.code, b.name as group_name, c.id as id_company , c.company_name  as company_name
            FROM adm_user AS a INNER JOIN adm_group AS b ON a.id_adm_group = b.id_adm_group 
            INNER JOIN ((SELECT id, company_name FROM hrd_company) UNION (select -1 as id, 'ALL' as company_name)) AS c on a.id_adm_company = c.id ";

     if ($arrUser[$_SESSION['sessionUserID']]['car'] != -1) $strSQL .= " WHERE id_adm_company = ".$arrUser[$_SESSION['sessionUserID']]['car'];

    $myDataGrid->totalData = $myDataGrid->getTotalData($db, $strSQLCOUNT);
    $dataset = $myDataGrid->getData($db, $strSQL);
    $counter = 0;
    foreach ($dataset as &$rowDb)
    {
      $counter++;
      if ($rowDb['active']=='t') 
        $rowDb['active'] = "<input type=\"hidden\" name=\"detailActive$counter\" id=\"detailActive$counter\" value=\"t\" disabled>".getWords("active");
      else
        $rowDb['active'] = "<input type=\"hidden\" name=\"detailActive$counter\" id=\"detailActive$counter\" value=\"f\" disabled>".getWords("inactive");
    } 
    //bind Datagrid with array dataset
    $myDataGrid->bind($dataset);
    $DataGrid = $myDataGrid->render();
  }
  $tbsPage = new clsTinyButStrong ;
  
  $strWordListofUser = strtoupper(vsprintf(getWords("list of %s"), getWords("user")));
  //write this variable in every page
  $strPageTitle = $dataPrivilege['menu_name'];
  $pageIcon = "../images/icons/".$dataPrivilege['icon_file'];
  $strTemplateFile = getTemplate(str_replace(".php", ".html", basename($_SERVER['PHP_SELF'])));
  //------------------------------------------------
  //Load Master Template
  $tbsPage->LoadTemplate("../templates/master.html") ;
  $tbsPage->Show() ;
//--------------------------------------------------------------------------------


  function getDataGroup()
  {
    global $db;
    $result = array();
    if ($db->connect())
    {
      $strSQL  = "SELECT * FROM adm_group WHERE active='t' ORDER BY id_adm_group";
      $res = $db->execute($strSQL);
      while ($rowDb = $db->fetchrow($res)) 
        $result[] = array("value"=>$rowDb['id_adm_group'], "text"=>"[".$rowDb['code']."] " .$rowDb['name'], "selected" => false);
    }
    return $result;
  }
  function getAccessRight()
  {
    global $db;
    $result = array();
    if ($db->connect())
    {
      $strSQL  = "SELECT * FROM adm_group WHERE active='t' ORDER BY id_adm_group";
      $res = $db->execute($strSQL);
      while ($rowDb = $db->fetchrow($res)) 
        $result[] = array("value"=>$rowDb['id_adm_group'], "text"=>"[".$rowDb['code']."] " .$rowDb['name'], "selected" => false);
    }
    return $result;
  }
  
  function getDataCompany()
  {
    global $db;
    $result = array();
    if ($db->connect())
    {
      $strSQL  = "SELECT * FROM adm_company ORDER BY name";
      $res = $db->execute($strSQL);
      while ($rowDb = $db->fetchrow($res)) 
        $result[] = array("value"=>$rowDb['id_adm_company'], "text"=>$rowDb['name'], "selected" => false);
    }
    return $result;
  }
  
  
  function getDataModule()
  {
    global $db;
    $result = array();
    if ($db->connect())
    {
      $strSQL  = "SELECT * FROM adm_module ORDER BY id_adm_module";
      $res = $db->execute($strSQL);
      while ($rowDb = $db->fetchrow($res)) 
        $result[] = array("value"=>$rowDb['id_adm_module'], "text"=>$rowDb['name'], "selected" => false);
    }
    return $result;
  }

  function printEditLink($params)
  {
    extract($params);
    return "
      <input type=\"hidden\" name=\"detailID$counter\" id=\"detailID$counter\" value=\"" .$record['id_adm_user']. "\" disabled>
      <input type=\"hidden\" name=\"detailLogin$counter\" id=\"detailLogin$counter\" value=\"" .$record['login_name']. "\" disabled>
      <input type=\"hidden\" name=\"detailName$counter\" id=\"detailName$counter\" value=\"" .$record['name']. "\" disabled>
      <input type=\"hidden\" name=\"detailEmployee$counter\" id=\"detailEmployee$counter\" value=\"" .$record['employee_id']. "\" disabled>
      <input type=\"hidden\" name=\"detailCompany$counter\" id=\"detailCompany$counter\" value=\"" .$record['id_company']. "\" disabled>
      <input type=\"hidden\" name=\"detailGroup$counter\" id=\"detailGroup$counter\" value=\"" .$record['id_adm_group']. "\" disabled>
      <input type=\"hidden\" name=\"detailIdAdmModule$counter\" id=\"detailIdAdmModule$counter\" value=\"" .$record['id_adm_module']. "\" disabled>
      <a href=\"javascript:myClient.editData($counter)\">" .getWords('edit'). "</a>";
  }
  
  // fungsi untuk menyimpan data
  function saveData() 
  {
    global $f;
    
    $strDataActive = ($f->getValue('dataActive')) ? 't' : 'f';
    $dataUser = new cAdmUser();
    
    // simpan data -----------------------
    if ($f->getValue('dataID') == "") 
    {
      // data baru
      $data =  array(
                "login_name" => $f->getValue('dataLogin'),
                "pwd" => md5($f->getValue('dataPwd')),
                "name" => $f->getValue('dataName'),
                "id_adm_group" => $f->getValue('dataGroup'),
                "employee_id" => $f->getValue('dataEmployee'),
                "active" => $strDataActive,
                "id_adm_module" => $f->getValue('dataIdAdmModule'),
                "id_adm_company" => $f->getValue('dataCompany')
              );
      $dataUser->insert($data);
    } 
    else 
    {
      $data =  array(
                "login_name" => $f->getValue('dataLogin'),
                "name" => $f->getValue('dataName'),
                "id_adm_group" => $f->getValue('dataGroup'),
                "employee_id" => $f->getValue('dataEmployee'),
                "active" => $strDataActive,
                "id_adm_module" => $f->getValue('dataIdAdmModule'),
                "id_adm_company" => $f->getValue('dataCompany')
              );
      if ($f->getValue('dataPwd') != "") $data["pwd"] = md5($f->getValue('dataPwd'));
      
      $dataUser->update(array("id_adm_user" => $f->getValue('dataID')), $data);
    }
    $f->message = $dataUser->strMessage;
  } // saveData
  
  // fungsi untuk menghapus data
  function deleteData() 
  {
    global $myDataGrid;
  
    $arrKeys = array();
    foreach ($myDataGrid->checkboxes as $strValue)
      $arrKeys['id_adm_user'][] = $strValue;

    $dataUser = new cAdmUser();    
    $dataUser->deleteMultiple($arrKeys);

    $myDataGrid->message = $dataUser->strMessage;
  } //deleteData

?>