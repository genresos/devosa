<?php
  include_once('../global/session.php');
  include_once('global.php');
  include_once('form_object.php');
  include_once('../classes/hrd/hrd_company.php');
  include_once('../global/excelReader/excel_reader.php');
  include_once('overtime_func.php');
  include_once('activity.php');


  $dataPrivilege = getDataPrivileges(basename($_SERVER['PHP_SELF']), $bolCanView, $bolCanEdit, $bolCanDelete, $bolCanApprove, $bolCanCheck, $bolCanAcknowledge, $bolCanApprove2);
  if (!$bolCanView) die(accessDenied($_SERVER['HTTP_REFERER']));


  //---- INISIALISASI ----------------------------------------------------
  $strWordsAttendanceData       = getWords("attendance data");
  $strWordsEntryAttendance      = getWords("entry attendance");
  $strWordsImportAttendance     = getWords("import attendance");
  $strWordsAttendanceList       = getWords("attendance list");
  $strWordsAttendanceReport     = getWords("attendance report");
  $strWordsAttendanceFile       = getWords("attendance file");
  $strWordsAttendanceDate       = getWords("attendance date");
  $strWordsAttendanceFile       = getWords("attendance file to import");
  $strWordsDownloadTemplate     = getWords("download templates format");
  $strWordsCompany              = getWords("company");
  $strWordsImport               = getWords("import");
  $strWordsRESULT               = getWords("result");
  $strDataDetail                = "";
  $strHidden                    = "";
  $intTotalData                 = 0;
  $strAttendanceDate            = "";
  $strInputDate                 = "";
  $bolShowResult                = false;
  $strResultInfo                = "";
  $strWordsImportData = getWords("import data");
  $strMessage					="Import Message Status";

  $strDownloadFiles             = "<a href='../hrd/attendance_list.xls'>Download</a>"; // lokasi donwnload untuk file contoh
  $strParameterName             = 'fileImport'; // Nama parameter yang untuk input type file
  $data                         = ""; //new Spreadsheet_Excel_Reader($lokasiFile);  // Class excel reader
  $strMessage					="";

  //----MAIN PROGRAM -----------------------------------------------------
  $db = new CdbClass;
  if ($db->connect())
  {
    if (!$bolCanView) echo "<script>alert(\"".getWords("view_denied")."\")</script>";
    $strDateFrom = (isset($_REQUEST['dataDateFrom'])) ? $_REQUEST['dataDateFrom'] : getNextDate(date("Y-m-d"), -7);
    $strDateThru = (isset($_REQUEST['dataDateThru'])) ? $_REQUEST['dataDateThru'] : date("Y-m-d");

    if (isset($_POST['btnImport']) && $bolCanEdit)
    {

        //Get Information Of Files and Parameter
  		$lokasiFile    = $_FILES[$strParameterName]['tmp_name'];
  		$tipeFile      = $_FILES[$strParameterName]['type'];

	    //d($strDateThru);
	    //Panggil fungsi importproses
	    $strMessage= importProcess($db);

    }
  }


  $strInputFile  = "<input type='file' name='".$strParameterName."' id='".$strParameterName."' size='50'> ";
  $strInputDate  = "<input type=text name='dataDateFrom' id='dataDateFrom' value='$strDateFrom' size=13>&nbsp;";
  $strInputDate .= "<input type=button name='btnDateFrom' id='btnDateFrom' value='..'>&nbsp;".getWords("until")."&nbsp;";
  $strInputDate .= "<input type=text name='dataDateThru' id='dataDateThru' value='$strDateThru' size=13>&nbsp;";
  $strInputDate .= "<input type=button name='btnDateThru' id='btnDateThru' value='..'>&nbsp;";
  $strInputCompany = getCompanyList($db, "dataCompany",$strDataCompany, $strEmptyOption2, $strKriteria2, "style=\"width:258\" ");


  $tbsPage = new clsTinyButStrong ;
  //write this variable in every page
  $strPageTitle = getWords($dataPrivilege['menu_name']);
  if (trim($dataPrivilege['icon_file']) == "") $pageIcon = "../images/icons/blank.gif";
  else $pageIcon = "../images/icons/".$dataPrivilege['icon_file'];
  $strTemplateFile = getTemplate(str_replace(".php", ".html", basename($_SERVER['PHP_SELF'])));
  //------------------------------------------------
  //Load Master Template
  $tbsPage->LoadTemplate($strMainTemplate) ;
  $tbsPage->Show() ;


  // Fungsi untuk proses insert data kedalam database
  // Untuk melakukan Import Data silahkan siapkan perintah Array dan querynya didalam fungsi ini

  function importProcess($db)
  {

  	global $data, $lokasiFile, $tipeFile, $intNumberOfField, $strNormalStartTime, $strNormalFinishTime,$strInputCompany,$strDateFrom,$strDateThru;
  	//global $data,$lokasiFile,$tipeFile,$intNumberOfField;
  	$data = new Spreadsheet_Excel_Reader($lokasiFile);  // Class excel reader
  	//d($data);
  	// membaca jumlah baris dari data excel yang diupload
  	$baris = $data->rowcount($sheet_index=0);

  	// variabel awal counter untuk jumlah data yang sukses dan yang gagal diimport
  	$dataPost = $_POST;
  	//$dateFrom = $dataPost['dataDateFrom'];
  	//$dateThru = $dataPost['dataDateThru'];

  	// import data excel mulai baris ke-2 (karena baris pertama adalah nama kolom)
  	for ($i=9; $i<=$baris; $i++)
  	{
            $strFingerID  = $data->val($i,1); // Finger ID
            $strNIK       = $data->val($i,2); // NIK
            $strDate      = $data->val($i,3); // Date 
            $strIN        = $data->val($i,4); // Time IN
            $strOUT       = $data->val($i,5); // Time Out
		  	// Variabelisasi ambil dari template
		  	$strNIK       		 = $data->val($i,2); // NIK
		  	$strDATE      		 = date("Y-m-d",strtotime($data->val($i,3))); // Date substr($data->val($i,5)), 0, -2);
		  	$strIN        		 = ($data->val($i,4) != "") ? date("G:i",strtotime($data->val($i,5))) : 'f'; // Time IN   date("h:i",strtotime($data->val($i,4)));  $data->val($i,4);
		  	$strOUT       		 = ($data->val($i,8) != "") ? date("G:i",strtotime($data->val($i,9))) : 'f';  // Time Out

		  	//------------------------------------

		  	$strIdEmployee  = getIdEmployee2($strNIK); // Ambil informasi ID dengan Select dari NIK

		  	if(!is_null($strIdEmployee) AND !empty($strIdEmployee))
			{
  				$arrData[]=array("nik"=>$strNIK,"date"=>$strDATE,"in"=>$strIN,"out"=>$strOUT);
			}

  	}

  			$strMessage=saveDataAttendance($arrData,$strInputCompany,$strDATE,$strDATE);

  	    return  $strMessage;
  }


  //  Simpan data attendance
  function saveDataAttendance($arrData,$strInputCompany,$strDateFrom,$strDateThru){
  	global $db;
  	$intssucces=0;
  	$intGagal=0;
  	$strMessage = "";

  	foreach ($arrData  AS $key => $value){

  		$strIdEmployee=getIdEmployee2($value['nik']);
  		$strDate = $value['date'];
  		$strIn = ($value['in'] == 'f') ? 'null' : "'".$value['in']."'";
  		$strOut = ($value['out'] == 'f') ? 'null' : "'".$value['out']."'";

  		if ($strIdEmployee!=""){
  		   // Jika tidak ada simpan sebagai data baru

  			$isExistAttendance1=isExistAttendance($strIdEmployee,$strDate);
  			if ($isExistAttendance1){
  				//Jika ada  update nilai amountnya
  				$strSQL="UPDATE hrd_attendance SET attendance_date='$strDate',attendance_start=$strIn,attendance_finish=$strOut WHERE id_employee='$strIdEmployee' AND attendance_date ='$strDate';";
  				//d($strSQL);
  			}
  			else{
  				// Jika tidak ada simpan sebagai data baru
  				$strSQL="INSERT INTO hrd_attendance (id_employee,attendance_date,attendance_start,attendance_finish) Values ('".$strIdEmployee."','".$strDate."',".$strIn.",".$strOut.");";
  				//d($strSQL);
  			}

  			$res = $db->execute($strSQL);
  			if ($res)$intssucces++;
  			else $intGagal++;

            syncOvertimeApplication($db, $strDate, $strDate, $strIdEmployee, "");
            syncLateEarly($db, $strDate, $strDate, $strIdEmployee, "");
            syncShiftAttendance($db, $strDate, $strDate," AND id_employee =  $strIdEmployee ", "");

  		}
  	}

  	$strMessage.="<h3>Data sucess Saved =".$intssucces." </br> Data Failed= ".$intGagal."</h3>";
  	return $strMessage;
  }

	function  isExistAttendance($strIdEmployee,$attendance_date)
	{
		global $db;
		$bolExist=true;
		$strSQL="SELECT id FROM hrd_attendance WHERE id_employee='".$strIdEmployee."' AND attendance_date ='".$attendance_date."';";
		$res = $db->execute($strSQL);
		$arrData = $db->fetchrow($res);
		$strID=$arrData['id'];
		if ($strID=="") $bolExist=false;
		return $bolExist;
	}

  		// Funsi untuk cek apakah data yang di upload kosong dan sesuai dengan format yang benar
  		function validateFile($parameterName){
  		// Get Information Of Files
  		$lokasiFile    = $_FILES[$parameterName]['tmp_name'];
  		$tipeFile      = $_FILES[$parameterName]['type'];

  			$bolValidate=false;
  			// jika kosong file ksoong
  			if($lokasiFile==""){
  			$bolValidate=false;
  		}
  		elseif($tipeFile!="xls")
  		{
  		$bolValidate=false;
  		}
  		else
  		{
  		$bolValidate=true;
  		}
  		return $bolValidate;
  		}
  		// End FUNGSI ----------------------------------


  		// Fungsi untuk mendapatkan informasi id pada sistem dengan parameter nomer NIK
  		function getIdEmployee2 ($strNIK){
  			global $db;
  			if ($db->connect())
  			{
  				$strSQL = "SELECT id From hrd_employee WHERE employee_id= '".$strNIK."' ";
  				$res = $db->execute($strSQL);
  				$arrData = $db->fetchrow($res);
  				$strID=$arrData['id'];
  			}
  			return $strID;
  		}

  		//  Simpan data allowancc
  		function saveDataSalaryAllowance ($arrData ,$strSalaryType,$strNameTabel,$strCodeSalary,$strSalarySET){
  			global $db;
  			$intssucces=0;
  			$intGagal=0;
  			$strMessage = "";

  			foreach ($arrData  AS $key => $value){
  				$strIdEmployee=getIdEmployee2($value['nik']);
  				$strAmount=$value['amount'];

  				if ($strIdEmployee!=""){
  					$isExistSalary=isExistsalary($strIdEmployee,$strNameTabel,$strSalaryType,$strCodeSalary,$strSalarySET);
  					if ($isExistSalary){
  						//Jika ada  update nilai amountnya
  						$strSQL="UPDATE $strNameTabel SET amount='$strAmount' WHERE id_employee='$strIdEmployee' AND $strCodeSalary ='$strSalaryType' AND id_salary_set='$strSalarySET';";
  					}
  					else{
  					// Jika tidak ada simpan sebagai data baru
  						$strSQL="INSERT INTO $strNameTabel (id_employee,$strCodeSalary,amount,id_salary_set) Values ('".$strIdEmployee."','".$strSalaryType."','".$strAmount."','".$strSalarySET."');";
  					}
  							$res = $db->execute($strSQL);
  							if ($res)$intssucces++;
  							else $intGagal++;
  				}
  				}
  				$strMessage.="<h3>Data sucess Saved =".$intssucces." </br> Data Failed= ".$intGagal."</h3>";
    			return $strMessage;
  		}





  //--- DAFTAR FUNGSI------------------------------------------------------

  //fungsi untuk memeriksa data yang diimport mengandung tanggal berapa saja
  function checkDateImport() {
  	global $HTTP_POST_FILES;
  	global $bolShowResult;

  	$strResult = "";

  	$arrDate = array(); // menampung data daftar tanggal yang ada

  	if (is_uploaded_file($HTTP_POST_FILES["fileAttendance"]['tmp_name'])) {
  		$dbf = dbase_open($HTTP_POST_FILES["fileAttendance"]['tmp_name'], 0);
  		if ($dbf) {

  			$intLen = dbase_numrecords($dbf);
  			for ($i = 1; $i <= $intLen;$i++) {

  				$arrTmp = dbase_get_record_with_names($dbf,$i);

  				$strStatus = trim($arrTmp['FCSTATUS']);
  				$strAttendanceDate = timestampDate2Date($arrTmp['FDDATE']);

  				$strTime = timestampTime2Time($arrTmp['FCTIME']);
  				list($tahun,$bulan,$tanggal) = explode("-",$strAttendanceDate);
  				$tsTmp = mktime(0,0,0,(int)$bulan,(int)$tanggal,$tahun);

  				$dtTmp = getdate($tsTmp);
  				$strAttendanceDate = $dtTmp['year']."-".$dtTmp['mon']."-".$dtTmp['mday']; // biar seragam
  				$arrDate[$strAttendanceDate] = 1;

  			}

  			foreach($arrDate AS $strDate => $tmp) {
  				$strResult .= "<a href=\"javascript:insertDate('$strDate')\">" .pgDateFormat($strDate,"d M Y")."</a><br>";
  			}

  		}
  		dbase_close($dbf);

  	}
  	return $strResult;
  } //checkDateImport

  // fungsi untuk menghitung overtime dari data attendance per tanggal tertentu
  function calculateOT($db, $strDataDate) {
  	global $_SESSION;

  	$strmodified_byID = $_SESSION['sessionUserID'];

  	if ($strDataDate == "") return "";

  	$strSQL  = "SELECT id,id_employee FROM hrd_attendance WHERE attendance_date = '$strDataDate' ";
  	$resDb = $db->execute($strSQL);
  	$i = 0;
  	while ($rowDb = $db->fetchrow($resDb)) {
  		reCalculateOvertimeData($db, $strDataDate, $rowDb['id_employee']);
  		$i++;
  	}

  	return "$i data";
  };// calculateOT



  //fungsi untuk memproses import data
  function importData_Asli($db, &$intTotal) {
  global $HTTP_POST_FILES;
  global $_FILES;
  global $_REQUEST;
  global $_SESSION;
  global $words;

  $strmodified_by = $_SESSION['sessionUserID'];
  $strResult = "";
  foreach($_FILES AS $kode => $value){
  if (is_uploaded_file($_FILES[$kode]['tmp_name']))
  	{
  	$strFileName = $_FILES[$kode]['tmp_name'];
  	$strResult = processAttendance($db, $strFileName, false);
  }
  }
  /*
  if (is_uploaded_file($HTTP_POST_FILES["fileData"]['tmp_name'])) {
  $strFileName = $HTTP_POST_FILES["fileData"]['tmp_name'];
  $strResult = processAttendance($db, $strFileName, false);

  }
  */
  return $strResult;
  } //importData

  function importData($db, &$intTotal)
  {

  global $strDataCompany;
  global $strDateFrom, $strDateThru;
  $strResult = processAttendance($db, $prb, $strDateFrom, $strDateThru, false, $strDataCompany);
  return $strResult;
  }
  /// Fungsi get ID employee By barcode
  function getIDEmployeeByBarcode($db, $code) {
  $strResult = "";

  if ($code != "") {
  $strSQL  = "SELECT id FROM hrd_employee WHERE barcode= '$code' ";
  $resDb = $db->execute($strSQL);
  if ($rowDb = $db->fetchrow($resDb)) {
  $strResult = $rowDb['id'];
  }
  }
  return $strResult;
  }
  //----------------------------------------------------------------------
  function getFingerIdByEmployeeId($strDataEmployee)
  {
  	global $db;
  	$strSQL = "SELECT barcode FROM \"hrdEmployee\" WHERE \"employeeID\" = '$strDataEmployee' ";
  	$res = $db->execute($strSQL);
  	if ($rowDb = $db->fetchrow($res))$strFingerID = $rowDb['pin'];
  		else $strFingerID = "";
  		return $strFingerID;
  }



?>
