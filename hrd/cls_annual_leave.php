<?php

  /*
    KELAS KHUSUS UNTUK MENANGANI CUTI TAHUNAN
    CREATED: 24 DES 2008 (YUDI)
  */
  include_once("cls_employee.php");
  define("ANNUAL_LEAVE_LIMIT", 12); // batas hak cuti tahunan

  class clsAnnualLeave
  {
    var $data; // database
    var $strIDEmployee; // id employee, jika khusus unutk satu karyawan tertentu
    var $strDate; // tanggal yang untuk menentukan batas cuti, default hari ini, format YYYY-MM-DD
    var $arrDate; // tanggal yang sudah diextract dalam array
    var $arrEmployee; // array data karyawan, khususnya data joindate
    var $arrData;   // data untuk menyimpan info cuti, per id employee
	//var $COBA1;
	//var $COBA2;
    /*
      curr -> tahun saat ini
      prev -> quota tahun ini
      next -> jumlah libur tahun ini
    */
    var $arrHistory; // ambil data history
    var $arrBlankDetail = array( // hanya untuk keperluan inisialisasi, strukturnya sama dengan data hak cuti
        "year"    => "", // tahun
        "quota"   => 0,  // jumlah jatah max
        "taken"   => 0,  // jumlah yang telah digunakan
        "holiday" => 0,  // jumlah hari libur yang memotong cuti
        "remain"  => 0,  // jumlah sisa hak cuti
        "remain_add"  => 0,  // jumlah sisa hak cuti
        "overdue" => 'f',  // sudah tidak berlaku lagi
        "start"   => "",  // tanggal awal periode
        "finish"  => "",  // tanggal akhir periode
        "expiry"  => "",  // tanggal expired cuti
      );

    // konstruktor
    // jika ID Employee disii, artiya hanya mencari info 1 karyawan saja, jika kosong, berarti mengambil semua data
    function clsAnnualLeave($db, $strIDEmployee = "")
    {
      $this->data = $db;
      $this->strIDEmployee = $strIDEmployee;
      $this->strDate = date("Y-m-d");
      $this->arrDate = extractDate($this->strDate); // date_function.php
      // langsung inisialisasi data, ambil data yang mungkin ada
      $this->getEmployeeData();
      $this->getLeaveHistory();
      $this->bolProrate = (getSetting("leave_method") == "1");
      $this->bolCutoff = (getSetting("leave_method") == "2");
    }

    /* setEmployeeID : fungsi untuk mengisi data id karyawan
       input: id employee
    */
    function setEmployeeID($strID)
    {
      $this->strIDEmployee = $strID;
    }
    /* setDate : fungsi untuk mengisi data id karyawan
       input : tanggal - format standard
    */
    function setDate($strDate)
    {
      $this->strDate = $strDate;
      $this->arrDate = extractDate($strDate);
    }

    /* getEmployeeData : ambil data karyawan yang perlu, tampung ke array (private)
    */
    function getEmployeeData()
    {
      $clsEmp = new clsEmployees($this->data);
      $strKrit = ($this->strIDEmployee != "") ? "AND id = '" .$this->strIDEmployee."' " : "";
      $clsEmp->loadData("*", $strKrit);
      $this->arrEmployee = $clsEmp->arrEmployee;
      unset($clsEmp);
    }

    /* getLeaveHistory : ambil data history, load ke memory untuk mempercepat proses
        history yang diambil adalah yang tahun ini, tahun lalu, dan 2 tahun lalu
    */
    function getLeaveHistory()
    {
      $strKrit  = ($this->strIDEmployee == "") ? "" : "AND \"hleave.id_employee\" = '" .$this->strIDEmployee."' ";
      //$strKrit .= " AND hleave.year IN (".($this->arrDate['year']-1).", ".($this->arrDate['year']-2).", ".($this->arrDate['year']-3).", ".($this->arrDate['year']).", ".($this->arrDate['year']+1)." ) ";
      $strKrit .= " AND hleave.year IN (".($this->arrDate['year']-1).", ".($this->arrDate['year']).", ".($this->arrDate['year']+1)." ) ";
      $strSQL = "SELECT * FROM hrd_leave_history as hleave
	  LEFT JOIN hrd_leave_additional AS hadd ON hadd.id_employee = hleave.id_employee AND hadd.year = hleave.year
	  WHERE 1=1 AND hleave.year is not Null $strKrit ";
      $res = $this->data->execute($strSQL);

      while($row = $this->data->fetchrow($res))
      {
        $this->arrHistory[$row['id_employee']][$row['year']] = $row;
      }
    }

    /* isEmployeeLeave : ambil info apakah karyawan tertentu sudah mendapat hak cuti
        input: id karyawan
        output : true jika karyawan sudah mendapat hak cuti saat ini
    */
    function isEmployeeLeave($strIDEmployee = "")
    {
      // jika kosong, artinya sesuai dengan atribut id employee
      if ($strIDEmployee == "") $strIDEmployee = $this->strIDEmployee;
      if ($strIDEmployee == "") return false;
      else
      {
        $strJoinDate = (isset($this->arrEmployee[$strIDEmployee])) ? $this->arrEmployee[$strIDEmployee]['join_date'] : "";
        if ($strJoinDate == "") return false;
        else
        {
          $arrSelisih = getDateInterval($strJoinDate, $this->strDate); // date_function.php
          return ($arrSelisih['year'] > 0); // lebih dari setahun
        }
      }
    }

    /* generateAnnualLeave : fungsi untuk menghitung jatah hak cuti per karyawan, langsung seluruh karyawan (yang ada dalam array)
        ambil dari data leave history, jika ada, jika tidak, hitung ulang
    */
    function generateAnnualLeave()
    {
      foreach ($this->arrEmployee AS $strID => $arrInfo)
      {
        $this->generateEmployeeAnnualLeave($strID);
      }
    }

    /* generateEmployeeAnnualLeave : fungsi untuk generate data cuti tahunan, tapi hanya per karyawan, public
        hasil disimpan dalam atribut arrData
       input : id karyawan
    */
    //by Brian - ditambah last grand leave data bila sudah bekerja selama X tahun.
    function generateEmployeeAnnualLeave($strID)
    {
      if (isset($this->arrEmployee[$strID]))
      {
        $arrInfo = $this->arrEmployee[$strID];
        $arrLeave = array(); // tampung sementara, biar ringkas
        $arrLeave['curr'] = $arrLeave['next'] = $arrLeave['prev'] = $this->arrBlankDetail;
        $arrDur = getDateInterval($arrInfo['join_date'], $this->strDate);

        // ambil tahun terlebih dahulu
        $strThisYear = $this->arrDate['year'];
        $strStartCurr = $this->getStartPeriod($this->arrDate["year"], $arrInfo['join_date']); // ambil periode awal untuk tahun sekarang
        // bandingkan, apakah sekarang sudah melewati masa join date
        if ($strStartCurr > $this->strDate) $strThisYear--;
        $arrLeave['prev']['year'] = $strThisYear - 1;
        $arrLeave['curr']['year'] = $strThisYear;
        $arrLeave['next']['year'] = $strThisYear + 1;
        /*
        if ($arrDur['year'] <= 0) // belum punya hak
        {
          // nothing
          $arrLeave['prev']['year'] = $arrLeave['curr']['year'] = $arrLeave['next']['year'] = "";
        }
        else */if ($arrDur['year'] <= 0) // satu tahun
        {
          $arrLeave['prev']['year'] = "";
        }
        $arrLeave['prev'] = $this->getAnnualLeaveByYear($strID, $arrInfo['join_date'], $arrLeave['prev']['year']);
        $arrLeave['curr'] = $this->getAnnualLeaveByYear($strID, $arrInfo['join_date'], $arrLeave['curr']['year']);
        $arrLeave['next'] = $this->getAnnualLeaveByYear($strID, $arrInfo['join_date'], $arrLeave['next']['year']);

        //tmbhn untuk cuti besar
        include_once("../global/common_function.php");
        $intPCB = getSetting("pcb"); //untuk mencari cuti besar
        if($arrDur['year'] < $intPCB) $arrLeave['grand']['year'] = ""; //belum pernah dapat hak cuti besar
        else //pernah dapat hak cuti besar; cari dari DB
        {
          if ($arrDur['year'] % $intPCB < 2) $arrLeave['grand']['year'] = ""; // 0 => thn ini; 1 => thn kemarin
          else $arrLeave['grand']['year'] = $strThisYear - ($arrDur['year'] % $intPCB);
        }
        $arrLeave['grand'] = $this->getAnnualLeaveByYear($strID, $arrInfo['join_date'], $arrLeave['grand']['year']);
        //tmbhn untuk cuti besar

        // cek kedaluwarsa atau gak
        $this->arrData[$strID] = $arrLeave;
        //print_r($arrLeave);
      }
    }

    /* getEmployeeLeaveRemain : fungsi untuk mengirimkan nilai total sisa hak cuti karyawan
        input: id karyawan
        output : jumlah sisa cuti
    */
    //by Brian - tambah last grand leave

// Remark By uddin, diduga tidak berguna
/*    function getEmployeeLeaveRemain($strID = "")
    {
      if ($strID == "") $strID = $this->strIDEmployee;

      $intResult = 0;
      if (isset($this->arrData[$strID]))
      {
        $arrLeave = $this->arrData[$strID];
        if ($arrLeave['prev']['year'] != "" && !$arrLeave['prev']['overdue']) $intResult += $arrLeave['prev']['remain'];
        if ($arrLeave['prev']['year'] != "" && $arrLeave['prev']['remain'] < 0) $intResult += $arrLeave['prev']['remain'];
        if ($arrLeave['curr']['year'] != "") $intResult += $arrLeave['curr']['remain'];
        if ($arrLeave['grand']['year'] != "" && !$arrLeave['grand']['overdue']) $intResult += $arrLeave['grand']['remain'];
      }

      return $intResult;
    }
*/
    /* getEmployeeLeaveInfo : fungsi untuk mengirim semua data cuti per karyawan,
        input : id karyawan
        output : array cuti
    */
    //by Brian - tambah grand leave
    function getEmployeeLeaveInfo($strID)
    {
      $arrResult['curr'] = $arrResult['prev'] = $arrResult['next'] = $arrResult['grand'] = $this->arrBlankDetail;

      if (isset($this->arrData[$strID])) $arrResult = $this->arrData[$strID];

      return $arrResult;

    }

    /* getAnnualLeaveByYear : ambil data hak cuti untuk tahun tertentu dan karyawan tertentu
        input: id employee, join date, tahun yang diinginkan
        output: array
    */
    //by Brian - perubahan cara menentukan overdue
    function getAnnualLeaveByYear($strID, $strJoinDate, $strYear)
    {
      //$intMBCN = getSetting("mbcn"); //masa berlaku cuti normal

      $arrResult = $this->arrBlankDetail;
      $arrResult['year'] = $strYear;
      if ($strYear == "" || $strID == "" || !validStandardDate($strJoinDate))
      {
        return $arrResult;
      }
      else
      {
        if (isset($this->arrHistory[$strID][$strYear]))
        {
          $arrResult['quota']   = $this->arrHistory[$strID][$strYear]['total'];
          $arrResult['holiday'] = $this->arrHistory[$strID][$strYear]['holiday'];
          $arrResult['taken']   = $this->arrHistory[$strID][$strYear]['used'];
          $arrResult['overdue'] = $this->arrHistory[$strID][$strYear]['overdue'];
          $arrResult['start']   = $this->arrHistory[$strID][$strYear]['start_date'];
          $arrResult['finish']  = $this->arrHistory[$strID][$strYear]['finish_date'];
          $arrResult['expiry']  = $this->arrHistory[$strID][$strYear]['expiration_date'];
        }
        else
        {
          // create data
          if ($this->saveLeaveHistory($strID, $strYear))
          {
            $arrResult['quota']   = $this->arrHistory[$strID][$strYear]['total'];
            $arrResult['holiday'] = $this->arrHistory[$strID][$strYear]['holiday'];
            $arrResult['taken']   = $this->arrHistory[$strID][$strYear]['used'];
            $arrResult['overdue'] = $this->arrHistory[$strID][$strYear]['overdue'];
            $arrResult['start']   = $this->arrHistory[$strID][$strYear]['start_date'];
            $arrResult['finish']  = $this->arrHistory[$strID][$strYear]['finish_date'];
            $arrResult['expiry']  = $this->arrHistory[$strID][$strYear]['expiration_date'];
          }
        }
        $arrResult['remain'] = $arrResult['quota'] - $arrResult['taken'] - $arrResult['holiday'];
        //echo $arrResult['remain'] ."--";
        $arrResult['remain_add'] =0; // hanya inisial remain additional

        // cek apakah sudah jatuh tempo atau gak
        if ($arrResult['overdue'] == 'f')
        {
          $strStart = $this->getStartPeriod($strYear, $strJoinDate);


          //diganti dengan membandingkan expiry
          /*
          $arrDur = getDateInterval($strStart, $this->strDate); // date_function.php
          $intMonthDur = intval($arrDur['year']) * 12 + intval($arrDur['month']);
          if ($intMonthDur >= $intMBCN)
          */
          include_once("../global/date_function.php");
          if(getIntervalDate($strStart, $arrResult['expiry']) <= 0) //bila start date <= expiry date, maka set expired
          {
            $arrResult['overdue'] = $this->arrHistory[$strID][$strYear]['overdue'] = 't';
            // update database
            $strSQL = "
              UPDATE \"hrd_leave_history\" SET overdue = 't'
              WHERE \"id_employee\" = '$strID' AND \"year\" = '$strYear'
            ";
            $resExec = $this->data->execute($strSQL);
          }
        }
        return $arrResult;

      }
    }

    /* getStartPeriod : fungsi untuk mengambil tanggal awal periode cuti di tahun tertentu, berdasar tanggal join date (private)
        input: tahun, join date (format YYYY-MM-DD)
        output: tanggal (format YYYY-MM-DD)
    */
    function getStartPeriod($strYear, $strJoinDate)
    {
      $strResult = "";
      if($this->bolCutoff){
        //bila tahun 1, maka cuti = join date
        //bila tahun 2+, cuti = 1 Jan
        $intTerm    = intval($strYear) - intval(substr($strJoinDate,0,4));
        if($intTerm > 1)
          $strResult = $strYear."-01-01";
        else{
          $strResult = "";
          if ($strYear == "" || $strJoinDate == "") return "";

          $arrTmp = extractDate($strJoinDate); //date_function.php
          $intLastDay = lastDay($arrTmp['month'], $arrTmp['year']); // date_function.php
          if ($arrTmp['day'] > $intLastDay) $arrTmp['day'] = $intLastDay; // untuk kasus 29 feb
          $strResult = $strYear."-".$arrTmp['month']."-".$arrTmp['day'];
          $strResult = pgDateFormat($strResult, "Y-m-d");// biar rapi saja
        }
      }else if ($this->bolProrate)
        $strResult = $strYear."-01-01";
      else
      {
        $strResult = "";
        if ($strYear == "" || $strJoinDate == "") return "";

        $arrTmp = extractDate($strJoinDate); //date_function.php
        $intLastDay = lastDay($arrTmp['month'], $arrTmp['year']); // date_function.php
        if ($arrTmp['day'] > $intLastDay) $arrTmp['day'] = $intLastDay; // untuk kasus 29 feb
        $strResult = $strYear."-".$arrTmp['month']."-".$arrTmp['day'];
        $strResult = pgDateFormat($strResult, "Y-m-d");// biar rapi saja
      }
      return $strResult;
    }

    /* saveLeaveHistory : method untuk menyimpan data ke leave history berdasar id employee dan tahun
        input: id employee, tahun
    */
    //by Brian - dibenarkan cara menghitung tanggal expired
    function saveLeaveHistory($strID, $strYear)
    {
      $intJCI = getSetting("jci"); //jatah cuti inisial
      $intPCB = getSetting("pcb"); //periode cuti besar
      $intJCB = getSetting("jcb"); //jatah cuti besar
      $intJCN = getSetting("jcn"); //jatah cuti normal
      $intMBCB = getSetting("mbcb"); //masa berlaku cuti besar
      $intMBCN = getSetting("mbcn"); //masa berlaku cuti normal
      $isprogresifGrandLeave = getSetting("progressive"); // apakah cuti besar berlaku progresif untuk tahun2 berikutnya

      if ($strID == "" || $strYear == "") return false;
      if (!isset($this->arrEmployee[$strID])) return false;
      if ($this->arrEmployee[$strID]['join_date'] == "") return false;
      $strJoinDate = $this->arrEmployee[$strID]['join_date'];

      // hapus dulu data lama, biar gak duplicate
      $strSQL = "
        DELETE FROM \"hrd_leave_history\"
        WHERE \"id_employee\" = '$strID' AND \"year\" = '$strYear'
      ";

      $res = $this->data->execute($strSQL);
      $strSQL = "
        DELETE FROM \"hrd_leave_additional\"
        WHERE \"id_employee\" = '$strID' AND \"year\" = '$strYear'
      ";
      $res = $this->data->execute($strSQL);
      //print_r($strSQL);
      // cari start dan finish date
      $strStart = $this->getStartPeriod($strYear, $strJoinDate);
      $strStart1 = $this->getStartPeriod($strYear, $strJoinDate);

      $strFinish  = getNextDate(getNextYear($strStart), -1);

      //inisiasi expiry date = cuti normal
      $intPeriod = $intMBCN;

      $intTerm    = intval($strYear) - intval(substr($strJoinDate,0,4));


      //Initial leave quota is taken from general setting Initial Leave Quota
      //Holiday Leave is ignored on the first year
      if ($intTerm == 0)
      {
           $intQuota = $intJCI;
      }
      else if ($intTerm == 1 && $this->bolCutoff)
      {
        //Tahun 1 = prorata dari 31 Des thn itu
        $strStart = substr($strJoinDate,0,4) . "-12-31";
        $arrDur = getDateInterval($strJoinDate, $strStart);
        $intQuota = $arrDur['month'] + 1;

      }
      else if ($intTerm == 1 && $this->bolProrate)
      {
        $arrDur = getDateInterval($strJoinDate, $strStart);
        $intQuota = $arrDur['month'] + 1;
      }
      //Normal & add posibility grand leave
      else if ($intTerm>1){
        $intQuota = $intJCN;

        // Perhitungan penambahan cuti besar
        //CEk Cuti besar apakah progresif
        // cuti progresif di insert ke additional cuti (Takenaka role)
        if ($isprogresifGrandLeave=="t"){
          $intMod5year =floor($intTerm/$intPCB);
          if ($intMod5year>6)
            $intMod5year=6;
        }else{
           // Non progresif maka hanya pada saat ulang tahun kelipatan intPCB cuti ditambah intJCB
          if (($intTerm % $intPCB == 0) && $intTerm > 0)
          {
            $intQuota =  $intQuota+$intJCB;
            $intPeriod = $intMBCB; //periode expired-nya menggunakan cuti besar , uddin ?????
          }
        }
      }else
      //Normal leave quota is taken from general setting Leave Quota.
      {
        $intQuota = $intJCN;
      }

      $intHoliday = $this->getLeaveHoliday($strStart1, $strFinish);


      //by Brian - mulai tambahan untuk masa berlaku cuti besar
      //hitung tanggal expired
      //include_once("../global/date_function.php");
      $strExpiry = getNextDateNextMonth($strStart,$intPeriod);


      //bila berubah, artinya ada cuti yg disimpan di masa cuti sebelumnya; if-nya meaningless, cuma untuk debug: toh kalau tdk berubah $strCurrentStart = $strStart
      if($strCurrentStart != $strStart) $strStart = $strCurrentStart;
      //by Brian - tambahan selesai
//                echo $strStart."-".$strFinish."|<br>";
      $intTaken   = $this->getEmployeeLeaveAnnualByYear($strID, $strStart1, $strFinish);
      $intTakenAdditional =$this->getEmployeeLeaveAnnualAdditionalByYear($strID, $strStart1, $strFinish);

      //uddin
      // cek startup cuti/cuti tanpa record absence
      $arrStartupTaken=$this->getStartupLeave($strID,$strYear);
      $intTaken+=$arrStartupTaken["taken"];
      $intTakenAdditional+=$arrStartupTaken["add_taken"];

      // INSERT LEAVE History & Quota
      $strSQL = "
        INSERT INTO \"hrd_leave_history\" (
          \"id_employee\", \"year\", \"start_date\", \"finish_date\",
            total, holiday, used, overdue, \"expiration_date\"
        )
        VALUES (
          '$strID', '$strYear', '$strStart1', '$strFinish',
          '$intQuota', '$intHoliday', '$intTaken', 't', '$strExpiry'
        )
      ";
      $res = $this->data->execute($strSQL);


      IF ($intMod5year>0){
        $strSQL ="INSERT INTO hrd_leave_additional (id_employee,\"year\",additional_quota,additional)
                  VALUES ('$strID', '$strYear','$intMod5year','$intTakenAdditional')";
        $res = $this->data->execute($strSQL);
      }


      if ($res == false) return false;
      else
      {
        $this->arrHistory[$strID][$strYear]['total'] = $intQuota;
        $this->arrHistory[$strID][$strYear]['used'] = $intTaken;
        $this->arrHistory[$strID][$strYear]['holiday'] = $intHoliday;
        $this->arrHistory[$strID][$strYear]['overdue'] = 't';
        $this->arrHistory[$strID][$strYear]['start_date']  = $strStart;
        $this->arrHistory[$strID][$strYear]['finish_date'] = $strFinish;
        $this->arrHistory[$strID][$strYear]['expiration_date'] = $strExpiry;
      }

      return true;
    }

    /* getEmployeeLeaveAnnualByYear : fungsi untuk mengambil informasi cuti tahunan yang sudah diambil karyawan, untuk tahun penerapan tertentu
        input: id employee, tahun penerapan (bukan tahun pengambilan cuti, karena bisa saja cuti sekarang untuk jatah tahun lalu)
        output : total cuti yang diambil
    */
    function getEmployeeLeaveAnnualByYear($strID, $strStart, $strFinish)
    {
      $intResult = 0;
      if ($strStart == "" || $strFinish == "" || $strID == "") return $intResult;


	  // query ini dicomment karena tidak mengitung jumlah cuti di hari sabtu dan minggu

	  $strSQL  = "
        SELECT SUM(1 * leave_weight) AS total
        FROM \"hrd_absence_detail\" AS t0
        LEFT JOIN hrd_absence_type AS t1 ON t0.absence_type = t1.code
        LEFT JOIN hrd_absence AS t2 ON t0.id_absence = t2.id
        WHERE absence_date BETWEEN '$strStart' AND '$strFinish'
          AND deduct_leave = TRUE
          AND t2.status >= ".REQUEST_STATUS_APPROVED."
          AND t0.id_employee = '$strID'
      ";
	 //print($strSQL);

      $resDb = $this->data->execute($strSQL);
      while ($rowDb = $this->data->fetchrow($resDb))
      {
        if ($rowDb['total'] != "") $intResult = $rowDb['total'];
      }
//echo $strStart."-".$strFinish."=".$intResult."<br>";
      return $intResult;
    } // getEmployeeLeaveAnnualByYear

    function getEmployeeLeaveAnnualAdditionalByYear($strID, $strStart, $strFinish)
    {
      $intResult = 0;
      if ($strStart == "" || $strFinish == "" || $strID == "") return $intResult;


    // query ini dicomment karena tidak mengitung jumlah cuti di hari sabtu dan minggu

    $strSQL  = "
        SELECT SUM(1 * leave_weight) AS total
        FROM \"hrd_absence_detail\" AS t0
        LEFT JOIN hrd_absence_type AS t1 ON t0.absence_type = t1.code
        LEFT JOIN hrd_absence AS t2 ON t0.id_absence = t2.id
        WHERE absence_date BETWEEN '$strStart' AND '$strFinish'
          AND deduct_additional_leave = TRUE
          AND t2.status >= ".REQUEST_STATUS_APPROVED."
          AND t0.id_employee = '$strID'
      ";
   //print($strSQL);

      $resDb = $this->data->execute($strSQL);
      while ($rowDb = $this->data->fetchrow($resDb))
      {
        if ($rowDb['total'] != "") $intResult = $rowDb['total'];
      }
//echo $strStart."-".$strFinish."=".$intResult."<br>";
      return $intResult;
    } // getEmployeeLeaveAnnualByYear

    /* getLeaveHoliday : fungsi untuk megnhitung libur yang terhitung sebagai cuti
        input: class db, tanggal awal dan tgl akhir (format: YYYY-MM-DD)
        output: berupa integer, jumlah hari liburnya
    */
    function getLeaveHoliday($strStartDate, $strFinishDate)
    {
      $intResult  = 0;

      if ($strStartDate == "" || $strFinishDate == "") return 0;

      $strSQL  = "SELECT count(id) AS total FROM \"hrd_calendar\" WHERE status='t' AND leave='t' ";
      $strSQL .= "AND holiday BETWEEN '$strStartDate' AND '$strFinishDate' ";
      $resTmp = $this->data->execute($strSQL);
      if ($rowTmp = $this->data->fetchrow($resTmp)) {
        if ($rowTmp['total'] != "") $intResult = $rowTmp['total'];
      }
//      echo $strStartDate."-".$strFinishDate."=".$intResult."<br>";

      return $intResult;

    } //getLeaveHoliday

    //uddin 20150122
    //untuk cek startup cuti/ bisa digunakan untuk cuti yg dispute
    function getStartupLeave($id_employee,$pyear){
      //echo $id_employee."---";
      $intResult  = array("taken"=>0,"add_taken" => 0);

      if ($id_employee == "" || $pyear == "") return $intResult;

//echo "HHH".$id_employee;
//$strSQL  = "select * from hrd_leave_taken_startup where year=";
      $strSQL  = "select * from hrd_leave_taken_startup where \"year\"=".$pyear." and id_employee=".$id_employee;
      //echo $strSQL."<br/>";
      $resTmp = $this->data->execute($strSQL);
      if ($rowTmp = $this->data->fetchrow($resTmp)) {
        $intResult  = array("taken"=>$rowTmp["annual_taken"],"add_taken" => $rowTmp["additional_taken"]);
      }
      return $intResult;


    }

  }

?>
