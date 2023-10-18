<?php
	ini_set("max_execution_time", 136000);
	ini_set('memory_limit', '2048M');
	include_once('../global/session.php');
	include_once("global.php");
	include_once("../includes/model/model.php");
  include_once("../global/common_variable.php");
 	include_once("../global/date_function.php");
 	
	if (isset($_REQUEST['exported_data'])){
		$exportedData = $_REQUEST['exported_data'];
		$arrayData = array();
		for($i = 0;$i < count($exportedData);$i++){
			$detailData = new stdClass();
			foreach($exportedData[$i] as $key => $value){
				$detailData->$key = $value;
			}
			$arrayData[] = $detailData;
		}
		$headers = $_REQUEST['headers'];
		$objectName = $_REQUEST['object_name'];
		$title = 'LOAN REPORT';
		if (isset($_POST['title'])){
			$title = $_POST['title'];
		}
		$subtitle = null;
		if (isset($_POST['subtitle'])){
			$subtitle = $_POST['subtitle'];
		}
		$xlsfilename = exportXLSX($arrayData,$headers,$objectName,$title,$subtitle);
		$tblTempFile = new cModel("hrd_temporary_file", "Temporary File");
		$data['filename'] = $xlsfilename;
		$data['created'] = date('Y-m-d H:i:s');
		$tblTempFile->insert($data);
		print $xlsfilename;
	}
	exit();

?>