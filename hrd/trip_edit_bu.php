<?php
  include_once('../global/session.php');
  include_once('global.php');
  include_once('../global/common_data.php');
  include_once('../global/employee_function.php');
  include_once('../includes/datagrid2/datagrid.php');
  include_once('../includes/form2/form2.php');
  include_once('../classes/hrd/hrd_trip_type.php');
  include_once('../classes/hrd/hrd_trip_type_cost_setting.php');
  include_once('../classes/hrd/hrd_trip_cost_type.php');
  include_once('../classes/hrd/hrd_trip_cost_platform.php');
  include_once('../classes/hrd/hrd_trip.php');
  include_once('../classes/hrd/hrd_trip_detail.php');

  
  $dataPrivilege = getDataPrivileges(basename($_SERVER['PHP_SELF']), $bolCanView, $bolCanEdit, $bolCanDelete, $bolCanApprove, $bolCanCheck, $bolCanAcknowledge, $bolCanApprove2);
  if (!$bolCanView) die(accessDenied($_SERVER['HTTP_REFERER']));



 
  $db = new CdbClass;
  if ($db->connect())
  {
    $strWordsTripAllowanceQuota = getWords("trip allowance quota");
    $strWordsDataEntry          = getWords("data entry");
    $strWordsBusinessTripList   = getWords("business trip list");
    $strWordsBusinessTripReport = getWords("business trip report");
    $strWordsDispositionForm    = getWords("disposition form");

    getUserEmployeeInfo();

    //INISIALISASI------------------------------------------------------------------------------------------------------------------

    //ambil semua jenis trip
    $tblTripType = new cHrdTripType();
    $arrTripType = $tblTripType->findAll("", "id, trip_type_code, trip_type_name, daily_allowance", "", null, 1, "id");
    //ambil semua jenis trip cost
    $tblTripCostType = new cHrdTripCostType();
    $arrTripCostType = $tblTripCostType->findAll("", "id, trip_cost_type_name, currency, '' as note", "trip_cost_type_name", null, 1, "id");
    //ambil setting cost untuk trip sesuai dengan trip type yang dipilih
    $tblTripTypeCostSetting = new cHrdTripTypeCostSetting();
    foreach($arrTripType AS $strTripID => $arrTripDetail)
    {
      $arrTripCostSetting = $tblTripTypeCostSetting->findAll("id_trip_type = $strTripID", "id_trip_cost_type, include", "", null, 1, "id_trip_cost_type");
      $arrTripCost[$strTripID] = array();
      foreach($arrTripCostType AS $strCostID => $arrCostDetail)
      {
        if(isset($arrTripCostSetting[$strCostID]) && $arrTripCostSetting[$strCostID]['include'] == 't')
          $arrTripCost[$strTripID][] = $strCostID;
      }
    }
    $tblTripCostPlatform = new cHrdTripCostPlatform();
    $strDataID          = getRequestValue('dataID');
    $isNew              = ($strDataID == "");
    if($strDataID != "" ) 
    {
      $arrData = getDataByID($strDataID);
      $arrTripCost = array();
      $arrTripCostType = array();
      $arrTripType[$arrData['dataTripType']]['trip_type_name'] = $arrData['dataTripTypeName'];

      $tblTripDetail = new cHrdTripDetail();
      $arrTripDetail = $tblTripDetail->findAll("id_trip = $strDataID", "", "", null, 1, "id_trip_cost_type");
      foreach($arrTripDetail AS $strCostID => $arrDetail)
      {
        $arrTripCost[$arrData['dataTripType']][] = $strCostID;
        $arrTripCostType[$strCostID]['trip_cost_type_name'] = $arrDetail['trip_cost_type'];
        $arrTripCostType[$strCostID]['currency'] = $arrDetail['currency'];
        $arrTripCostType[$strCostID]['amount'] = $arrDetail['amount'];
        $arrTripCostType[$strCostID]['note'] = $arrDetail['note'];
      }
      if (!isset($_REQUEST['btnSave']) && (isset($_REQUEST['cash']))) $strDataID = "";
    }
    else
    {
      $_REQUEST['cash'] = 't';
      $arrData['dataTempFormCode'] = $arrData['dataFormCode']  = (getPostValue('dataFormCode') != "") ? getPostValue('dataFormCode') : getFormCode($db, "HR.BT-", date(".m.y"), "hrd_trip");
      $arrData['dataTripType']  = getPostValue('dataTripType');
      $arrData['dataEmployee']  = getPostValue('dataEmployee');
      $arrData['dataDateFrom']  = (getPostValue('dataDateFrom') != "") ? getPostValue('dataDateFrom') : date("Y-m-d");
      $arrData['dataDateThru']  = (getPostValue('dataDateThru') != "") ? getPostValue('dataDateThru') : date("Y-m-d");
      $arrData['dataProposalDate']  = (getPostValue('dataProposalDate') != "") ? getPostValue('dataProposalDate') : date("Y-m-d");
      $arrData['dataDestination']   = getPostValue('dataDestination');
      $arrData['dataPurpose']       = getPostValue('dataPurpose');
      $arrData['dataTask']          = getPostValue('dataTask');
      $arrData['dataNote']          = getPostValue('dataNote');
    }
    $strDataTempFormCode  = $arrData['dataTempFormCode'];
    $strDataFormCode      = $arrData['dataFormCode'];
    $strDataTripTypeID    = $arrData['dataTripType'];
    $strDataEmployee      = $arrData['dataEmployee'];
    $strDataDateFrom      = $arrData['dataDateFrom'];
    $strDataDateThru      = $arrData['dataDateThru'];
    $strDataProposalDate  = $arrData['dataProposalDate'];
    $strDataDestination   = $arrData['dataDestination'];
    $strDataPurpose       = $arrData['dataPurpose'];
    $strDataTask          = $arrData['dataTask'];
    $strDataNote          = $arrData['dataNote'];

    $tblTrip = new cHrdTrip();
  //  $dataTrip = $tblTrip->find("
    $strReadonly = (scopeGeneralDataEntry($strDataEmployee, $_SESSION['sessionUserRole'], $arrUserInfo)) ? "readonly" : "" ;
  // ------------------------------------------------------------------------------------------------------------------------------
    $strReadonly=true;
    if ($bolCanEdit)
    {
      $f = new clsForm("formInput", 2, "100%", "");
      $f->caption = strtoupper($strWordsINPUTDATA);

      $f->addHidden("dataID", $strDataID);
      $f->addHidden("dataFormCode", $strDataFormCode);
      $f->addInput(getWords("form code"), "dataTempFormCode", $strDataFormCode, array("size" => 45, "readonly"=>"readonly", "maxlength" => 30), "string", true, (!isset($_REQUEST['cash'])), true);  
      $f->addSelect(getWords("trip type"), "dataTripType", getDataListTripType($strDataTripTypeID), "style='width:250px'", "", true);
      
	  if($_SESSION['sessionUserRole'] == ROLE_EMPLOYEE)
	  {
		$strDataEmployee = $_SESSION["sessionEmployeeID"];
		$strReadonly=readonly;
	  }else{
		$strDataEmployee = $arrData['dataEmployee'];
	  }
	   
      //----------------------------------------------------

      $f->addInputAutoComplete(getWords("employee ID"), "dataEmployee", getDataEmployee($strDataEmployee), "style='width:250px' ". $strReadonly, "string", true);
      $f->addLabelAutoComplete("", "dataEmployee", "");
	//$f->addInputAutoComplete(getWords("employee ID"), "dataEmployee", value=$arrData['dataEmployee']. "\", "style='width:250px' ". $strReadonly, "string", true);
      //$f->addLabelAutoComplete("", "dataEmployee", "");
      
      $f->addLabel(getWords("proposal date"), "dataProposalDate", $strDataProposalDate, array("style" => "width:$strDateWidth"), "string", true, true, true);
      //$f->addHidden("dataProposalDate", $strDataFormCode);

      $f->addInput(getWords("date from"), "dataDateFrom", $strDataDateFrom, array("style" => "width:$strDateWidth"), "date", true, true, true);
      $f->addInput(getWords("date thru"), "dataDateThru", $strDataDateThru, array("style" => "width:$strDateWidth"), "date", true, true, true);
      $f->addSelect(getWords("destination"), "dataDestination", getDataListDestination($strDataDestination), "", "", true);
      $f->addTextArea(getWords("purpose"), "dataPurpose", $strDataPurpose, array("cols"=>76, "rows"=>3), "string", true, true, true);
      $f->addTextArea(getWords("task detail"), "dataTask", $strDataTask, array("cols"=>76, "rows"=>3), "string", false, true, true);
      $f->addTextArea(getWords("note"), "dataNote", $strDataNote, array("cols"=>76, "rows"=>3), "string", false, true, true);


      if ($strDataTripTypeID != "" || $strDataDateFrom == "")
      { 
        $bolDaily     = ($arrTripType[$strDataTripTypeID]['daily_allowance'] == "t");
        //hitung berapa malam jika trip typenya di set sebagai daily allowance
        $intDuration  = ($bolDaily) ? getIntervalDate($strDataDateFrom, $strDataDateThru) + 1 : 1;
        $arrEmployee  = getEmployeeInfoByCode($db, $strDataEmployee, "grade_code");
        $strGradeCode = $arrEmployee['grade_code'];
        $arrQuota = $tblTripCostPlatform->findAll("id_trip_type = $strDataTripTypeID AND grade_code = '$strGradeCode'", "id_trip_cost_type, amount", "", null, 1, "id_trip_cost_type");
        $f->addHidden("dataTripTypeName", $arrTripType[$strDataTripTypeID]['trip_type_name']);

        //tambah baris blank supaya semua field allowance terletak di kolom kanan
        if ((count($arrTripCost[$strDataTripTypeID])*2) > 11)
        {
          for($i = 11; $i <= (count($arrTripCost[$strDataTripTypeID])*2); $i++)
            $f->addLiteral("", "", "");
        }

	$m=0;
        foreach($arrTripCost[$strDataTripTypeID] AS $strCostID)
        {
		
          $fltQuota = (isset($arrQuota[$strCostID])) ? $intDuration * $arrQuota[$strCostID]['amount'] : 0;

          $fltAmount = (isset($arrTripCostType[$strCostID]['amount'])) ? $arrTripCostType[$strCostID]['amount'] : $fltQuota;
          //generate button untuk alat bantu mengubah nilai allowance menjadi 0, 50%, 75%, dan nilai default.

          $strBtnHelper = "
          <input type=\"button\" name=\"btn".$strCostID."_0\" value=\"0%\" 
          onClick=\"(document.formInput.dataTripCost_".$strCostID.".value = 0)\">&nbsp;
          <input type=\"button\" name=\"btn".$strCostID."_50\" value=\"50%\" 
          onClick=\"\$(dataTripCost_".$strCostID.").value = (\$(dataTripCost_".$strCostID.").value /2);\">&nbsp;
          <input type=\"button\" name=\"btn".$strCostID."_75\" value=\"75%\" 
          onClick=\"\$(dataTripCost_".$strCostID.").value = (\$(dataTripCost_".$strCostID.").value / 4 * 3);\">&nbsp;
          <input type=\"button\" name=\"btn".$strCostID."_add1day\" value=\"-1day\" 
          onClick=\"\$(dataTripCost_".$strCostID.").value = (\$(dataTripCostPlus1Day_".$strCostID.").value);\">&nbsp;
          <input type=\"button\" name=\"btn".$strCostID."_Reset\" value=\"".getWords("reset")."\" 
          onClick=\"\$(dataTripCost_".$strCostID.").value = (\$(dataTripCostDefault_".$strCostID.").value);\">&nbsp;";
          //generate field allowance untuk jenis trip yang dipilih 
	
	if($m==0)
       
	   $f->addInput(getWords($arrTripCostType[$strCostID]['trip_cost_type_name'])." (".$arrTripCostType[$strCostID]['currency'].")", "dataTripCost_".$strCostID, $fltAmount , array("size" => 30, "maxlength" => 12, ), "numeric", true, true, true, "", $strBtnHelper); // edit by yuda
      
	 else
	  
          $f->addInput(getWords($arrTripCostType[$strCostID]['trip_cost_type_name'])." (".$arrTripCostType[$strCostID]['currency'].")", "dataTripCost_".$strCostID, $fltAmount , array("size" => 30, "maxlength" => 12, ), "numeric", true, false, true, "", $strBtnHelper); // edit by yuda
         

	 $f->addInput("", "dataTripCostNote_".$strCostID, $arrTripCostType[$strCostID]['note'], array("size" => 70, "maxlength" => 255), "string", false, true, true, "");
          //$f->addTextArea("", "dataTripCostNote_".$strCostID, $arrTripCostType[$strCostID]['note'], array("cols"=>69, "rows"=>1), "string", false, true, true);
          //tampung data allowance real (sebelum di potong jadi 50% atau 75%)
          $f->addHidden("dataTripCostDefault_".$strCostID, $fltQuota);
          //tampung data allowance real plus 1 hari (misalnya untuk uang makan, karena $intDuration menghitung jumlah malam)
          if ($intDuration == 0)
            $f->addHidden("dataTripCostPlus1Day_".$strCostID, $fltQuota / 1) ;
          else
            $f->addHidden("dataTripCostPlus1Day_".$strCostID, $fltQuota / $intDuration * ($intDuration - 1)) ;
		$m++;
        }
        if ((count($arrTripCost[$strDataTripTypeID])*2) < 11)
        {
          //tambah baris blank supaya semua field allowance terletak di kolom kanan
          for($i = 1; $i <= (11 - (count($arrTripCost[$strDataTripTypeID])*2)) ; $i++)
            $f->addLiteral("", "", "");
        }
		
		$f->addLiteral("", "buttonAllowance", generateSubmit("btnAllowance", getWords("set allowance"),  array("onClick" => "javascript:myClient.checkTripDate();"), ""));
        //tambah baris blank supaya semua field allowance terletak di kolom kanan
        for($i = 1; $i <= 11; $i++)
          $f->addLiteral("", "", "");
		  
        $f->addSubmit("btnSave", getWords("save"), array("onClick" => "javascript:myClient.confirmSave();"), true, true, "", "", "saveData()");
        $f->addButton("btnAdd", getWords("add new"), array("onClick" => "location.href='".basename($_SERVER['PHP_SELF'])."'"));
      }
      else
      {
        $f->addLiteral("", "buttonAllowance", generateSubmit("btnAllowance", getWords("set allowance"),  array("onClick" => "javascript:myClient.checkTripDate();"), ""));
        //tambah baris blank supaya semua field allowance terletak di kolom kanan
        for($i = 1; $i <= 11; $i++)
          $f->addLiteral("", "", "");
      }
      
      $formInput = $f->render();
    }
    else
      $formInput = "";
  }
    
  
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


  function getDataByID($strDataID)
  {
    global $db;
    $tblTrip = new cHrdTrip();
    $arrTrip = $tblTrip->findAll("id = $strDataID", "", "", null, 1, "id");
    $arrTemp = getEmployeeInfoByID($db, $arrTrip[$strDataID]['id_employee'], "employee_id");
    $arrResult['dataEmployee'] = $arrTemp['employee_id'];
    $arrResult['dataTempFormCode'] = $arrTrip[$strDataID]['form_code'];
    $arrResult['dataFormCode'] = $arrTrip[$strDataID]['form_code'];
    $arrResult['dataTripType'] = $arrTrip[$strDataID]['id_trip_type'];
    $arrResult['dataTripTypeName'] = $arrTrip[$strDataID]['trip_type'];
    $arrResult['dataProposalDate'] = $arrTrip[$strDataID]['proposal_date'];
    $arrResult['dataDateFrom'] = $arrTrip[$strDataID]['date_from'];
    $arrResult['dataDateThru'] = $arrTrip[$strDataID]['date_thru'];
    $arrResult['dataDestination'] = $arrTrip[$strDataID]['destination'];
    $arrResult['dataPurpose'] = $arrTrip[$strDataID]['purpose'];
    $arrResult['dataTask'] = $arrTrip[$strDataID]['task'];
    $arrResult['dataNote'] = $arrTrip[$strDataID]['note'];

    //foreach($arrTripCost[$arrTrip['trip_type']
    //g78
    return $arrResult;
  }
    
  // fungsi untuk menyimpan data
  function saveData() 
  {
    global $f;
    global $db;
    global $isNew;
    global $arrTripCost;
    global $arrTripCostType;
    if ($db->connect())
    {
      $strmodified_byID = $_SESSION['sessionUserID'];
      $strIDEmployee = getIDEmployee($db, $f->getValue('dataEmployee'));
      $tblHrdTrip = new cHrdTrip();
      $tblHrdTripTypeCostSetting = new cHrdTripTypeCostSetting();
      $data = array("id_trip_type" => $f->getValue('dataTripType'),
                    "form_code" => $f->getValue('dataFormCode'),
                    "trip_type" => $f->getValue('dataTripTypeName'),
                    "id_employee" => ($strIDEmployee),
                    "proposal_date" => $f->getValue('dataProposalDate'),
                    "date_from" => $f->getValue('dataDateFrom'),
                    "date_thru" => $f->getValue('dataDateThru'),
                    "destination" => $f->getValue('dataDestination'),
                    "purpose" => $f->getValue('dataPurpose'),
                    "task" => $f->getValue('dataTask'),
                    "note" => $f->getValue('dataNote'));


      // simpan data trip type
      $bolSuccess = false;
      if ($isNew)
      {
        // data baru
        $bolSuccess = $tblHrdTrip->insert($data);
      } 
      else 
      {
        $bolSuccess = $tblHrdTrip->update("id='".$f->getValue('dataID')."'", $data);
      }
      if ($bolSuccess)
      {
        if ($isNew)
          $f->setValue('dataID', $tblHrdTrip->getLastInsertId());
        else
          $f->setValue('dataID', $f->getValue('dataID'));
      }
      // simpan data trip type cost setting
      $tblHrdTripDetail = new cHrdTripDetail();
      $tblHrdTripDetail->delete("id_trip = ".$f->getValue('dataID'));
      $data2 = array("id_trip" => $f->getValue('dataID'));
      foreach($arrTripCost[$f->getValue('dataTripType')] AS $strCostID)
      {
        $data2['id_trip_cost_type'] = $strCostID;
        $data2['trip_cost_type']    = $arrTripCostType[$strCostID]['trip_cost_type_name'];
        $data2['amount']            = ($f->getValue('dataTripCost_'.$strCostID) != "") ? $f->getValue('dataTripCost_'.$strCostID) : 0;
        $data2['note']              = $f->getValue('dataTripCostNote_'.$strCostID);
        $data2['currency']          = ($arrTripCostType[$strCostID]['currency'] != "") ? $arrTripCostType[$strCostID]['currency'] : "IDR";
        // hapus data lama, insert data baru
        $tblHrdTripDetail->insert($data2);
      }
    }

    $f->message = $tblHrdTrip->strMessage;
  } // saveData
  


?>