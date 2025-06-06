<?php
  /* Fungsi-fungsi khusus untuk masalah kehadiran
    By, Yudi K.
    2008-07-18
  */

  // dianggap sudah ada pengambilan include global

  // kelas untuk mengelola data kehadiran karyawan, per hari tertentu
  class clsAttendanceClass
  {
    var $db; // kelas database, sudah terkoneksi

    // atribut sebagai filter, jika ada
    var $strEmployeeID; 
    var $strIDEmployee; // nik, sebagai filter
    var $strKriteria; // kriteria, sebagai filter

    // atribut sebagai data pendukung, dalam array
    var $arrShift;       // daftar jadwal shift
    var $arrSchedule;       // daftar jadwal shift
    var $arrAttendance;  // daftar kehadiran
    var $arrEmployee;    // daftar info karyawan
    var $arrBarcode;     // daftar kode barcode karyawan, untuk nyari ID karyawan
    //var $arrBranch;      // daftar branch, terkait dengan data kehadiran (berupa text, bisa lebih dari satu)
    var $arrSection;     // daftar section, terkait dengan data kehadiran
    var $arrSubSection;  // daftar subsection, terkait dengan data kehadiran
    var $arrBreakTime;   // daftar jam istirahat
    var $arrOutOffice;   // daftar karyawan yang terjadwal tidak hadir (absen, cuti, trip, dsb)
    var $arrOT;          // daftar SPL, jadwal OT

    // atribut pendukung proses
    var $bolPublicHoliday;// apakah public holiday atau tidak (karena minggu belum tentu libur)
    var $bolSaturday;     // apakah tanggal itu, sabtu dianggap libur atau tidak -- general setting
    var $intWeekDay;      // kode tanggal 0-6
    var $strNormalStart;  // jam normal masuk,  standard perusahaan : hh:mm:ss
    var $strNormalFinish; // jam normal pulang, standard perusahaan : hh:mm:ss
    var $strNormalFinishFriday; // jam normal pulang, standard perusahaan : hh:mm:ss

    // konstuktor
    function clsAttendanceClass($db)
    { 
      $this->db = $db;
      $this->resetAttendance();
    }
    
    // reset data
    function resetAttendance()
    {
      // inisialisasi
      $this->strDateFrom  = "";
      $this->strDateThru  = "";
      $this->strEmployeeID      = "";
      $this->strKriteria    = "";

      $this->arrShift       = array();
      $this->arrSchedule    = array();
      $this->arrAttendance  = array();
      $this->arrEmployee    = array();
      $this->arrBarcode     = array();
      // $this->arrBranch      = array();
      $this->arrSection     = array();
      $this->arrSubSection  = array();
      $this->arrBreakTime   = array();
      $this->arrOutOffice   = array();
      $this->arrOT          = array();
    }
    
    // mengisi data filter
    function setFilter($strDateFrom, $strDateThru, $strIDEmployee = "", $strKriteria = "")
    {
       $this->strDateFrom        = $strDateFrom;
       $this->strDateThru        = $strDateThru;
       $this->strIDEmployee      = $strIDEmployee;
       $this->strKriteria        = $strKriteria;
    }

    // bolGetData: perintah untuk mengambil informasi penting di tanggal tersebut, untuk disimpan di array dulu
    function getAttendanceResource()
    {
        $this->getDefaultNormalTime();
        $this->getBreakTime();
        $this->getShiftSchedule();
        $this->getWorkSchedule();
        $this->getOutOfOffice();
        $this->getEmployeeInfo();
        $this->getAttendanceData();
        $this->getOvertimeData();
    }
    // fungsi untuk mengambil data jadwal shift di
    // default sesuai tanggal yang ada
    function getShiftSchedule()
    {
       $this->arrShift = getShiftSchedule($this->db, $this->strDateFrom, $this->strDateThru, $this->strIDEmployee);
           ;

      // activity.php
    }

    // fungsi untuk mengambil data jadwal kerja sesuai hari kerja dan keterangan di data department
    function getWorkSchedule()
    {
       $strTempDate = $this->strDateFrom;
       while(dateCompare($strTempDate, $this->strDateThru) <= 0)
       {
          $arrTempSchedule = getWorkSchedule($this->db, $strTempDate, $this->strIDEmployee);
          if (count($arrTempSchedule) > 0)
            $this->arrSchedule[$strTempDate] = $arrTempSchedule;
          $strTempDate = getNextDate($strTempDate);
       }
       //print_r($this->arrSchedule);
      // activity.php
    }

    // fungsi untuk mengambil info data ketidak hadiran
    function getOutOfOffice()
    {
      $this->arrOutOffice = getOutOfficeInfo($this->db, $this->strDateFrom, $this->strDateThru, $this->strIDEmployee); // activity.php
    }

    // fungsi untuk mengambil info data jam masuk keluar standard
    // mengambil data dari general setting
    // mengambil data dari cabang, simpan di arrBranch
    // mengambil data dari section dan subsection
    function getDefaultNormalTime()
    {
      // ambil data dari general setting
      if (($this->strNormalStart = substr(getSetting("start_time"),0,5)) == "")
        $this->strNormalStart = "08:00";
      if (($this->strNormalFinish = substr(getSetting("finish_time"),0,5)) == "")
        $this->strNormalFinish = "17:00";
      if (($this->strNormalFinishFriday = substr(getSetting("friday_finish_time"),0,5)) == "")
        $this->strNormalFinishFriday = $this->strNormalFinish ;
    }

    // fungsi untuk mengambil informasi karyawan, simpan ke arrEmployee
    function getEmployeeInfo()
    {
      $strTempKriteria = str_replace("sub_section_code", "t0.sub_section_code", $this->strKriteria);
      $strTempKriteria = str_replace(" section_code", " t0.section_code", $strTempKriteria);
      $strTempKriteria = str_replace("department_code", "t0.department_code", $strTempKriteria);
      $strTempKriteria = str_replace("division_code", "t0.division_code", $strTempKriteria);

      $strSQL = "
        SELECT t0.*, t1.get_ot, t1.get_auto_ot, t2.division_name, t3.department_name, t4.section_name
        FROM hrd_employee AS t0
        LEFT JOIN hrd_position AS t1 ON t0.position_code = t1.position_code
        LEFT JOIN hrd_division AS t2 ON t0.division_code = t2.division_code
        LEFT JOIN hrd_department AS t3 ON t0.department_code = t3.department_code
        LEFT JOIN hrd_section AS t4 ON t0.section_code = t4.section_code
        WHERE 1=1 ".$strTempKriteria." ";

      if ($this->strIDEmployee != "")     $strSQL .= "AND t0.id = '".$this->strIDEmployee."' ";
      $resDb = $this->db->execute($strSQL);
      while ($rowDb = $this->db->fetchrow($resDb))
      {
        $this->arrEmployee[$rowDb['id']]['employee_id'] = $rowDb['employee_id'];
        $this->arrEmployee[$rowDb['id']]['employee_id_2'] = $rowDb['employee_id_2'];
        $this->arrEmployee[$rowDb['id']]['employee_name'] = $rowDb['employee_name'];
        $this->arrEmployee[$rowDb['id']]['is_overtime'] = ($rowDb['get_ot'] == 0) ? "f" : "t";
        $this->arrEmployee[$rowDb['id']]['is_auto_overtime'] = ($rowDb['get_auto_ot'] == "t");
        $this->arrEmployee[$rowDb['id']]['division_code'] = $rowDb['division_code'];
        $this->arrEmployee[$rowDb['id']]['division_name'] = $rowDb['division_name'];
        $this->arrEmployee[$rowDb['id']]['department_code'] = $rowDb['department_code'];
        $this->arrEmployee[$rowDb['id']]['department_name'] = $rowDb['department_name'];
        $this->arrEmployee[$rowDb['id']]['section_code'] = $rowDb['section_code'];
        $this->arrEmployee[$rowDb['id']]['section_name'] = $rowDb['section_name'];
        $this->arrEmployee[$rowDb['id']]['sub_section_code'] = $rowDb['sub_section_code'];
        //$this->arrEmployee[$rowDb['id']]['local_time_difference'] = $rowDb['local_time_difference'];
        $this->arrEmployee[$rowDb['id']]['gender'] = $rowDb['gender'];
        $this->arrEmployee[$rowDb['id']]['barcode'] = $rowDb['barcode'];
        
        $this->arrBarcode[$rowDb['barcode']] = $rowDb['id'];
      }
    }

    // fungsi untuk mengambil daftar jam istirahat
    // dikelompokkan per cabang (termasuk default), per jenis hari
    function getBreakTime()
    {
      $strSQL  = "
        SELECT bt.*
        FROM hrd_break_time AS bt
      ";
      $resDb = $this->db->execute($strSQL);
      while ($rowDb = $this->db->fetchrow($resDb))
      {
        if ($rowDb['start_time'] != "" && $rowDb['duration'] > 0)
        {
          $tmp = substr($rowDb['start_time'],0,5);
          $this->arrBreakTime[$rowDb['type']] = getNextMinute($rowDb['start_time'],$rowDb['duration']);
        }
      }
    }

    // fungsi untuk mengambil data kehadiran seluruh karyawan yang pernah disimpan, di tanggal tersebut
    // disimpan di array, index adalah idEmployee
    function getAttendanceData()
    {
      $strSQL  = "SELECT t0.* FROM hrd_attendance AS t0 ";
      $strSQL .= "LEFT JOIN hrd_employee AS t1 ON t0.id_employee = t1.id ";
      //$strSQL .= "LEFT JOIN hrd_overtime_application_employee as t2 ON  t0.id_employee = t2.id_employee AND t0.attendance_date = t2.overtime_date ";
      $strSQL .= "WHERE (attendance_date = '" .$this->strDateFrom."' ";
      $strSQL .= "   OR attendance_date BETWEEN '" .$this->strDateFrom."' AND '" .$this->strDateThru."') ";
      $strSQL .= "AND (attendance_date <= resign_date ";
      $strSQL .= "   OR (active = 1)) "; 
      $strSQL .= $this->strKriteria;
      $strSQL .= " ORDER BY attendance_date"; 
      $resDb = $this->db->execute($strSQL);
      while ($rowDb = $this->db->fetchrow($resDb))
      {
        $this->arrAttendance[$rowDb['attendance_date']][$rowDb['id_employee']] = $rowDb;
      }
    }

    
    // fungsi untuk mengambil data SPL seluruh karyawan yang pernah disimpan, di tanggal tersebut
    // disimpan di array, index adalah idEmployee
    function getOvertimeData()
    {
       $this->arrOT = getEmployeeOvertimeApplicationDetail($this->db, $this->strDateFrom, $this->strDateThru, $this->strIDEmployee);
    }

    // fungsi mengambil info apakah jadwal cuti sedang OFF, untuk karyawan tertentu - idEmployee
    function isShiftOFF($strID)
    {
      $bolResult = false;
      if (isset($this->arrShift[$strID]))
      {
        $bolResult = ($this->arrShift[$strID]['shift_off'] == 't');
      }
      return $bolResult;
    }

    // fungsi untuk mengambil apakah ada jadwal shift untuk karyawan tersebut
    function isShift($strID)
    {
      return (isset($this->arrShift[$strID]));
    }


  } // class

  // kelas khusus untuk data kehadiran karyawan tertentu, sekedar menyimpan datanya saja
  class clsAttendanceInfo
  {
    var $db;
    var $strIDEmployee;
    var $strAttendanceDate;
    var $intWeekDay;

    var $strBranchCode;
    
    var $strAttendanceID;             // id dari data kehadiran, jika sudah ada
    var $strNormalStart;              // jam masuk normal, hh:mm:ss
    var $strNormalFinish;             // jam pulang normal, hh:mm:ss
    var $strAttendanceStart;          // jam hadir aktual
    var $strAttendanceFinish;         // jam pulang aktual
    var $strNormalBreak;              // jam selesai istirahat (siang) yang normal
    var $strActualBreak;              // jam kembali dari istirahat siang, aktual
    var $strOvertimeStartEarly;       // jam mulai early ot aktual, hh:mm:ss
    var $strOvertimeFinishEarly;      // jam selesai early ot aktual, hh:mm:ss
    var $strOvertimeStart;            // jam mulai ot aktual
    var $strOvertimeFinish;           // jam selesai ot aktual

    var $intTotalDuration;            // total durasi jam masuk dan pulang, dalam menit (yang dianggap aktual jam kerja)
    var $intTotalDurationFull;        // total durasi jam masuk dan pulang, full

    var $bolNeedCalculateLate;        // apakah perlu hitung keterlambatan
    var $bolLate;                     // apakah terlambat
    var $intLate;                     // jumlah menit keterlambatan
    var $intEarly;                    // jumlah menit pulang awal
    var $intBreakLate;                // jumlah menit keterlambatan setelah jam istirahat
    var $intLateDeduction;            // potongan keterlambatan, yang akan mengurangi lembur
    
    var $bolGetOT;                    // apakah perlu dihitung OTnya atau diabaikan
    var $fltOT1;
    var $fltOT2;
    var $fltOT3;
    var $fltOT4;
    var $fltOT5;                      // andai ada
    var $fltTotalOT;                  // total OT, dalam menit
    var $fltOTCalculated;                  // total OT, dalam menit

    var $bolAbsence;      // apakah sedang dianggap absen atau tidak
    var $bolHoliday;      // apakah termasuk hari libur, hari libur nasional
    var $bolShiftOff;     // apakah status shift adalah OFF
    var $bolShiftNight;   // apakah termasuk shift malam
    var $intAbsenceType;  // jenis absen, jika ada
    var $strAbsenceCode;  // kode  absen, jika ada
    var $strShiftCode;    // kode shift, jika ada -- untuk menentukan kerja siang atau malam
    var $bolYesterday;    // apakah ini data kemarin
    var $strNote;
    var $strDataSource;
    
    function clsAttendanceInfo($db)
    {
       $this->db = $db;
      // reset
    }

    // fungsi mengambil data karyawan dengan idEmployee, jika ada
    // disimpan dalam array, jika tidak ada, dikirim array kosong
    function getEmployeeAttendance($strID, $strDate, $objAttendanceClass)
    {
      return ((isset($objAttendanceClass->arrAttendance[$strDate][$strID])) ? $objAttendanceClass->arrAttendance[$strDate][$strID] : array());
    }

    // fungsi mereset atribut, untuk ID Employee tertentu
    function newInfo($strIDEmployee, $strDate)
    {
      $this->strIDEmployee     = $strIDEmployee;
      $this->strAttendanceDate = $strDate;
      $this->intWeekDay        = getWDay($strDate);
      $this->isFriday          = (getWDay($strDate) == 5);

      $this->strBranchCode = "";
      // reset
      $this->strAttendanceID      = "";
      $this->strNormalStart       = "";
      $this->strNormalFinish      = "";
      $this->strAttendanceStart   = "";
      $this->strAttendanceFinish  = "";
      $this->strOvertimeStartEarly   = "";
      $this->strOvertimeFinishEarly  = "";
      $this->strOvertimeStart   = "";
      $this->strOvertimeFinish  = "";
      $this->strOvertimeStartEarlyAuto   = "";
      $this->strOvertimeFinishEarlyAuto  = "";
      $this->strOvertimeStartAuto   = "";
      $this->strOvertimeFinishAuto  = "";
      $this->strNormalBreak       = "";
      $this->strActualBreak       = "";
      $this->intLocalTimeDifference = 0;

      $this->intTotalDuration     = 0;
      $this->intTotalDurationFull = 0;

      $this->bolNeedCalculateLate = false;
      $this->bolLate              = false;
      $this->intLate              = 0;
      $this->intEarly             = 0;
      $this->intBreakLate         = 0;
      $this->intLateDeduction     = 0;
      
      $this->bolGetOT             = false;
      $this->fltOT1               = 0;
      $this->fltOT2               = 0;
      $this->fltOT3               = 0;
      $this->fltOT4               = 0;
      $this->fltOT5               = 0;
	  $this->cfltOT1               = 0;
      $this->cfltOT2               = 0;
      $this->cfltOT3               = 0;
      $this->cfltOT4               = 0;
      $this->cfltOT5               = 0;
      $this->fltTotalOT           = 0;
      $this->fltOTCalculated = 0;
	  $this->totOTCalculated = 0;

      $this->bolAbsence           = false;
      $this->bolHoliday           = false;
      $this->bolShiftOff          = false;
      $this->bolYesterday         = false;
      $this->bolShiftNight         = false;
      $this->intAbsenceType       = -1;
      $this->strAbsenceCode       = "";
      $this->strShiftCode         = "";
      $this->strIsOvertime        = "f";
      //$this->strIsAutoOvertime    = "f";
      //$this->strNotOvertime       = "f";

      $this->strNote              = "";
      $this->arrOTApplication     = array();
    }

    // melakukan inisialisasi data objAttendance, yang merupakan data awal untuk karyawan dengan idEmployee tertentu
    // mengisi informasi di dalam objek tersebut dengan nilai default
    function initAttendanceInfo($objAttendanceClass)
    {
       $strTmpIDEmployee = $this->strIDEmployee;
       $strTmpAttendanceDate = $this->strAttendanceDate;
      if ((isset($objAttendanceClass->arrEmployee[$strTmpIDEmployee])))
      {
         $strTmpSubSection   = $objAttendanceClass->arrEmployee[$strTmpIDEmployee]['sub_section_code'];
         $strTmpSection      = $objAttendanceClass->arrEmployee[$strTmpIDEmployee]['section_code'];
         $this->bolGetOT     = ($objAttendanceClass->arrEmployee[$strTmpIDEmployee]['is_overtime'] == 't')          ; // dapat OT
         $this->bolGetAutoOT = ($objAttendanceClass->arrEmployee[$strTmpIDEmployee]['is_auto_overtime'] == 't')     ; // dapat auto OT
      }
      else
      {
        $strTmpSection    = "";
        $strTmpSubSection = "";
      }
      // cek absen atau tidak
      if (isset($objAttendanceClass->arrOutOffice[$strTmpAttendanceDate][$strTmpIDEmployee]))
      {
        $this->intAbsenceType = (isset($objAttendanceClass->arrOutOffice[$strTmpAttendanceDate][$strTmpIDEmployee]['type'])) ? $objAttendanceClass->arrOutOffice[$strTmpAttendanceDate][$strTmpIDEmployee]['type'] : "" ;
        $this->strAbsenceCode = $objAttendanceClass->arrOutOffice[$strTmpAttendanceDate][$strTmpIDEmployee]['code'];
        $this->bolAbsence = ($this->intAbsenceType == OUTOFFICE_ABSENT || $this->intAbsenceType == OUTOFFICE_LEAVE) ? true : false;
        //echo $this->strAttendanceDate."-".$this->intAbsenceType."-".(($this->bolAbsence) ? "true": "false")."\n";
      }

      // cek apakah pernah ada data kehadiran, jika ada, ambil dari data tersebut
      $arrTmp = $this->getEmployeeAttendance($strTmpIDEmployee, $strTmpAttendanceDate, $objAttendanceClass);
      if (count($arrTmp) > 0)
      {
        $this->strAttendanceID            = $arrTmp['id'];
        $this->strNormalStart             = substr($arrTmp['normal_start'], 0, 5);
        $this->strNormalFinish            = substr($arrTmp['normal_finish'], 0, 5);
        $this->strAttendanceStart         = substr($arrTmp['attendance_start'], 0, 5);
        $this->strAttendanceFinish        = substr($arrTmp['attendance_finish'], 0, 5);
        $this->strOvertimeStart           = substr($arrTmp['overtime_start'], 0, 5);
        $this->strOvertimeFinish          = substr($arrTmp['overtime_finish'], 0, 5);
        $this->strOvertimeStartEarly      = substr($arrTmp['overtime_start_early'], 0, 5);
        $this->strOvertimeFinishEarly     = substr($arrTmp['overtime_finish_early'], 0, 5);
        $this->strOvertimeStartAuto       = substr($arrTmp['overtime_start_auto'], 0, 5);
        $this->strOvertimeFinishAuto      = substr($arrTmp['overtime_finish_auto'], 0, 5);
        $this->strOvertimeStartEarlyAuto  = substr($arrTmp['overtime_start_early_auto'], 0, 5);
        $this->strOvertimeFinishEarlyAuto = substr($arrTmp['overtime_finish_early_auto'], 0, 5);
       if (isset($arrTmp['normal_break']))
           $this->strNormalBreak = substr($arrTmp['normal_break'], 0, 5);
        if (isset($arrTmp['actual_break']))
           $this->strActualBreak = substr($arrTmp['actual_break'], 0, 5);
        $this->bolLate                    = ($arrTmp['not_late'] != 't');
        $this->strNote                    = $arrTmp['note'];
        $this->strDataSource              = $arrTmp['data_source'];
        $this->bolAbsence                 = ($arrTmp['is_absence'] == 't');
        $this->bolHoliday                 = ($arrTmp['holiday'] == 1);
        $this->bolShiftNight              = (timeCompare($arrTmp['normal_start'], $arrTmp['normal_finish']) == 1);
        $this->strShiftCode               = $arrTmp['code_shift_type'];
        $this->intLate = $arrTmp['late_duration'];
        if (isset($arrTmp['late_deduction']))
           $this->intLateDeduction = $arrTmp['late_deduction'];
        $this->intEarly = $arrTmp['early_duration'];
        if (isset($arrTmp['break_late_deduction']))
           $this->intBreakLate = $arrTmp['break_late_duration'];
        $this->fltTotalOT                 = $arrTmp['overtime'];
        $this->fltOTCalculated            = $arrTmp['overtime_calculated'];
        $this->strIsOvertime              = $arrTmp['is_overtime'];
        //$this->strIsAutoOvertime          = $arrTmp['is_auto_overtime'];
        //$this->strNotOvertime             = $arrTmp['not_overtime'];
        $this->fltOT1                     = $arrTmp['l1'];
        $this->fltOT2                     = $arrTmp['l2'];
        $this->fltOT3                     = $arrTmp['l3'];
        $this->fltOT4                     = $arrTmp['l4'];
		$this->cfltOT1                     = $this->fltOT1*1.5;
        $this->cfltOT2                     = $this->fltOT2*2;
        $this->cfltOT3                     = $this->fltOT3*3;
        $this->cfltOT4                     = $this->fltOT4*4;
		$this->totOTCalculated = $this->cfltOT1+$this->cfltOT2+$this->cfltOT3+$this->cfltOT4;

        // jika ga dapat OT, kosongkan
        if (!$this->bolGetOT)
        {
          $this->strIsOvertime = false;
          //$this->strIsAutoOvertime = false;
          //$this->strNotOvertime = true;
          $this->fltOT1 = 0;
          $this->fltOT2 = 0;
          $this->fltOT3 = 0;
          $this->fltOT4 = 0;
          $this->fltTotalOT = 0;
          $this->fltOTCalculated = 0;
          // jika sudah ada data, tidak perlu menghitung ulang perbedaan waktu
          $this->intLocalTimeDifference = 0;
        }
      }
      else
      {
        $this->strNormalBreak = '12:00';
        //jika belum ada data kehadiran, perhitungkan perbedaan waktu
        $this->intLocalTimeDifference = 0;//($objAttendanceClass->arrEmployee[$strTmpIDEmployee]['local_time_difference'] == "" || !is_numeric($objAttendanceClass->arrEmployee[$strTmpIDEmployee]['local_time_difference'])) ? 0 : $objAttendanceClass->arrEmployee[$strTmpIDEmployee]['local_time_difference'] ; 

        // jika masih kosong juga, gunakan dari general setting

        // 1. cek dari shift
        if (isset($objAttendanceClass->arrShift[$strTmpAttendanceDate][$strTmpIDEmployee]))
        {
           $arrTemp = $objAttendanceClass->arrShift[$strTmpAttendanceDate][$strTmpIDEmployee];
           $this->strShiftCode = $arrTemp['shift_code'];
           if ($arrTemp['shift_off'] == "t" || isEmployeeHoliday($this->db, $strTmpAttendanceDate, $strTmpIDEmployee, $objAttendanceClass->arrShift))/*isHoliday($strTmpAttendanceDate, true /*if false means only checks public holiday))*/
           {
              $this->strNormalStart  = "";
              $this->strNormalFinish = "";
              $this->bolHoliday = true;
              $this->bolShiftOff = true;
              $this->bolShiftNight = true;
           }
           else
           {
              $this->strNormalStart  = substr($arrTemp['start_time'],0,5);
              $this->strNormalFinish = substr($arrTemp['finish_time'],0,5);
              $this->bolHoliday = false;
              $this->bolShiftOff = false;
              $this->bolShiftNight = (timeCompare($arrTemp['start_time'], $arrTemp['finish_time']) == 1);
          }
        }
        // 2. cek dari work schedule
        else if (isset($objAttendanceClass->arrSchedule[$strTmpAttendanceDate][$strTmpIDEmployee]))
        {
           $arrTemp = $objAttendanceClass->arrSchedule[$strTmpAttendanceDate][$strTmpIDEmployee];
           if ($arrTemp['day_off'] == "t" || isHoliday($strTmpAttendanceDate, true /*if false means only checks public holiday*/))
           {
              $this->strNormalStart  = "";
              $this->strNormalFinish = "";
              $this->bolHoliday = true;
              $this->bolShiftNight = false;
           }
           else 
           {
              $this->strNormalStart  = substr($arrTemp['start_time'],0,5);
              $this->strNormalFinish = substr($arrTemp['finish_time'],0,5);
              $this->bolHoliday = false;
              $this->bolShiftNight = (timeCompare($arrTemp['start_time'], $arrTemp['finish_time']) == 1);
           }
        }
        else
        {
           // cek hari libur
           $this->bolHoliday = isHoliday($strTmpAttendanceDate, true /*true means check public holiday and weekend holiday*/);
           if (!$this->bolHoliday )
           {
              // 3. bukan hari libur, tidak ada jadwal shift, tidak ada workschedule khusus, baru ambil default

              if ($this->strNormalStart == "")
                 $this->strNormalStart = $objAttendanceClass->strNormalStart;
              if ($this->strNormalFinish == "")
              {
                 if ($this->isFriday)
                    $this->strNormalFinish = $objAttendanceClass->strNormalFinishFriday;
                 else
                    $this->strNormalFinish = $objAttendanceClass->strNormalFinish;
              }
           }
        }
      }
    }

    // fungsi mengambil data spl karyawan dengan idEmployee, jika ada
    // disimpan dalam array, jika tidak ada, dikirim array kosong
    function getEmployeeOvertimeApplication($objAttendanceClass)
    {
      return ((isset($objAttendanceClass->arrOT[$this->strAttendanceDate][$this->strIDEmployee])) ? $objAttendanceClass->arrOT[$this->strAttendanceDate][$this->strIDEmployee] : array());
    }

    // fungsi untuk menyimpan data attendance dari objAttendance yang sedang diproses
    function saveCurrentAttendance($objAttendanceClass, $strDataSource = "")
    {
      //periksa perbedaan waktu
      $strAttendanceStart   = ($this->strAttendanceStart  == "") ? "" : $this->strAttendanceStart; 
      $strAttendanceFinish  = ($this->strAttendanceFinish == "") ? "" : $this->strAttendanceFinish; 
      /*
      $strAttendanceStart   = ($this->strAttendanceStart  == "") ? "" : ($this->intLocalTimeDifference != 0) ? getNextMinute($this->strAttendanceStart, $this->intLocalTimeDifference) : $this->strAttendanceStart; 
      $strAttendanceFinish  = ($this->strAttendanceFinish == "") ? "" : ($this->intLocalTimeDifference != 0) ? getNextMinute($this->strAttendanceFinish, $this->intLocalTimeDifference) : $this->strAttendanceFinish; 
      */
      // verifikasi data dulu
      $strNormalStart       = ($this->strNormalStart == "") ? "NULL" : "'" .$this->strNormalStart."'";
      $strNormalFinish      = ($this->strNormalFinish == "") ? "NULL" : "'" .$this->strNormalFinish."'";
      $strAttendanceStart   = ($this->strAttendanceStart == "") ? "NULL" : "'" .$strAttendanceStart."'";
      $strAttendanceFinish  = ($this->strAttendanceFinish == "") ? "NULL" : "'" .$strAttendanceFinish."'";
      $strNormalBreak       = ($this->strNormalBreak == "") ? "NULL" : "'" .$this->strNormalBreak."'";
      $strActualBreak       = ($this->strActualBreak == "") ? "NULL" : "'" .$this->strActualBreak."'";
      $strNotLate           = (timeCompare($this->strNormalStart, $this->strAttendanceStart) >= 0 && $this->bolHoliday == 0) ? "'t'" : "'f'";
      $strHoliday           = ($this->bolHoliday) ? "'1'" : "'0'";
      $strShiftType         = ($this->bolShiftNight) ? 1 : 0;
      $strShiftCode         = $this->strShiftCode;
      $strIsAbsence         = ($this->bolAbsence) ? "'t'" : "'f'";
      $bolYesterday         = ($this->bolYesterday);
      $strStatus    = 0; // sementara langsung OK

      if ($this->strAttendanceStart == "" && $this->strAttendanceFinish == "") // anggap data gak ada, hapus
      {
        $strSQL = "
          DELETE FROM hrd_attendance WHERE id_employee = '" .$this->strIDEmployee. "'
            AND attendance_date = '" .$this->strAttendanceDate."';
        ";
      }
      else if ($this->strAttendanceID == "") // baru
      {
        // hapus dulu, menghindari duplikasi
        $strSQL = "
          DELETE FROM hrd_attendance WHERE id_employee = '" .$this->strIDEmployee. "'
            AND attendance_date = '" .$this->strAttendanceDate."';
        ";
        $strSQL .= "
          INSERT INTO hrd_attendance (
              id_employee, attendance_date,
              attendance_start, attendance_finish, normal_start, normal_finish,
              not_late, note, total_duration,  late_duration,                
              early_duration, l1, l2, l3, l4, overtime, overtime_start, overtime_finish,
              shift_type, code_shift_type, status, is_absence, holiday, data_source
            )
            VALUES(
              '" .$this->strIDEmployee."', '" .$this->strAttendanceDate."', $strAttendanceStart, $strAttendanceFinish,
              $strNormalStart, $strNormalFinish, $strNotLate, '" .$this->strNote. "', '" .$this->intTotalDuration."',
               '" .$this->intLate."', '" .$this->intEarly. "',
              '" .$this->fltOT1. "', '" .$this->fltOT2."', '" .$this->fltOT3. "', '" .$this->fltOT4. "',
              '" .$this->fltTotalOT."', NULL, NULL,  '$strShiftType', '$strShiftCode',
              $strStatus, $strIsAbsence, $strHoliday, '$strDataSource'
            );
        ";
      }
      else if ($bolYesterday)// anggap update
      {
        // hapus dulu, cegah duplikasi
        $strSQL = "
          DELETE FROM hrd_attendance WHERE id_employee = '" .$this->strIDEmployee. "'
            AND attendance_date = '" .$this->strAttendanceDate."'
            AND id <> '" .$this->strAttendanceID."';
        ";
        $strSQL .= "
          UPDATE hrd_attendance SET created=now(),
            modified_by = '" .$_SESSION['sessionUserID']. "',
            attendance_start = $strAttendanceStart, attendance_finish = $strAttendanceFinish,
            normal_start = $strNormalStart, normal_finish = $strNormalFinish,
            not_late = $strNotLate, note = '".$this->strNote."', total_duration = '" .$this->intTotalDuration."',
            late_duration = '" .$this->intLate. "', early_duration = '" .$this->intEarly. "',
            l1 = '" .$this->fltOT1. "', l2 = '" .$this->fltOT2."',
            l3 = '" .$this->fltOT3. "', l4 = '" .$this->fltOT4."',
            overtime = '" .$this->fltTotalOT. "', ";
			//shift_type = '$strShiftType',
            $strSQL .= "status = '$strStatus', is_absence = $strIsAbsence, holiday = $strHoliday, data_source = '$strDataSource'
          WHERE id_employee = '" .$this->strIDEmployee. "'
            AND attendance_date = '" .$this->strAttendanceDate."';
        ";
        
      }

      if (isset($strSQL))
      {
         $resExec = $this->db->execute($strSQL);
         return ($resExec != false);
      }
    }
    // fungsi untuk menghitung total waktu kehadiran
    function calculateDuration()
    {

      if ($this->strAttendanceStart != "" && $this->strAttendanceFinish != "")
      {
        $this->intTotalDurationFull = getTotalHour($this->strAttendanceStart, $this->strAttendanceFinish);
        if ($this->strNormalStart == "")
          $this->intTotalDuration = $this->intTotalDurationFull;
        else
          $this->intTotalDuration = getTotalHour($this->strNormalStart, $this->strAttendanceFinish); // jam kerja dihitung dari normal
      }
    }

    // fungsi untuk menghitung keterlambatan dan pulang cepat
    function calculateLate()
    {
      //cek perbedaan waktu
      $strAttendanceStart   = ($this->strAttendanceStart  == "") ? "" : $this->strAttendanceStart; 
      $strAttendanceFinish  = ($this->strAttendanceFinish == "") ? "" : $this->strAttendanceFinish; 


      /*

      $strAttendanceStart   = ($this->strAttendanceStart  == "") ? "" : ($this->intLocalTimeDifference != 0) ? getNextMinute($this->strAttendanceStart, $this->intLocalTimeDifference) : $this->strAttendanceStart; 
      $strAttendanceFinish  = ($this->strAttendanceFinish == "") ? "" : ($this->intLocalTimeDifference != 0) ? getNextMinute($this->strAttendanceFinish, $this->intLocalTimeDifference) : $this->strAttendanceFinish; 
      */
      // masih ada kemungkinan bugs, jika telat atau pulang cepat terlalu jauh, melewati tengah malam
      if ($strAttendanceStart != "" && $this->strNormalStart != "")
      {
        if ($strAttendanceStart > $this->strNormalStart)
          $this->intLate = getTotalHour($this->strNormalStart, $strAttendanceStart);
      }
      if ($strAttendanceFinish != "" && $this->strNormalFinish != "")
      {
        if ($strAttendanceFinish < $this->strNormalFinish)
          $this->intEarly = getTotalHour($strAttendanceFinish, $this->strNormalFinish);
      }
      
      /*
      // hitung kemungkinan telat jam istirahat
      if ($this->strNormalBreak != "" && $this->strActualBreak != "" && ($this->strNormalBreak < $this->strActualBreak) )
      {
        $this->intBreakLate = getTotalHour($this->strNormalBreak, $this->strActualBreak);
      }

      // jika pulang < 12, hitung pengurang gaji
      if ($this->intEarly > 0)
      {
        // lakukan pembulatan pemotongan gaji, dibulatkan ke atas 30menit ke atas
        // aturannya, seharusnya yang dibayar hanya waktu kerja, dibulatkan 30menit ke bawah
        $this->intLateDeduction = ceil($this->intEarly/30) * 30;
      }
      */
    }

    // fungsi untuk menghitung total lembur
    function calculateOvertime()
    {
      if ($this->bolGetOT) // hanya yang berhak lembur yang dapat
      {
        // reset dulu
        $this->fltOT1 = $this->fltOT2 = $this->fltOT3 = $this->fltOT4 = $fltTotalOT = 0;
        // hitung
        
        $intTotal = ($this->intTotalDurationFull > $this->intTotalDuration) ? $this->intTotalDuration : $this->intTotalDurationFull;
        // ambil yang terkecil, dengan asumsi, kalau full lebih kecil, berarti telat
        $this->calculateOvertimeDetail($intTotal);
      
      }
    }

    // fungsi private, yang secara menghitung lembur lebih detail
    // intTotal adalah total jam kerja yang dianggap kerja aktual
    function calculateOvertimeDetail($intTotal)
    {
      // dibulatkan 30 menit ke bawah
      $intTotal = $intTotal - ($intTotal % 30);
      // cari total hari kerja normal
      // perlu cek apakah 5 atau 6 hari kerja
      /*if ($this->bolShortestDay)
      {
        $intWork = FRIDAY_WORK_HOUR * 60;
      }
      else
      {
        $intWork = ($this->strShiftCode == NIGHT_SHIFT_TYPE) ? (FULL_WORK_HOUR * 60) : (FULL_NIGHT_WORK_HOUR * 60);
      }
      */
      $intWork = 7 * 60; // default 7 jam dulu
        
      if ($this->bolHoliday || $this->bolShiftOff) // sedang OFF atau hari libur
      {
        if ($intTotal <= $intWork) // tidak sampai sepenuh hari, atau pas sehari
          $this->fltOT2 = $intTotal;
        else
        {
          $this->fltOT2 = $intWork;
          $intTmp = ($intTotal - $intWork); // ambil sisa
          if ($intTmp <= 60)
            $this->fltOT3 = $intTmp;
          else
          {
            $this->fltOT3 = 60;
            $this->fltOT4 = $intTotal - 60;
          }
        }
      }
      else // hari biasa
      {
        $intTotalOT = ($intTotal > $intWork) ? ($intTotal - $intWork) : 0; // hitung sisa untuk lembur
        if ($intTotalOT <= 60)
          $this->fltOT1 = $intTotalOT;
        else
        {
          $this->fltOT1 = 60;
          $this->fltOT2 = $intTotalOT - 60;
        }
      }
      $this->fltTotalOT = $this->fltOT1 + $this->fltOT2 + $this->fltOT3 + $this->fltOT4 + $this->fltOT5;
	  $this->fltOTCalculated = ($this->fltOT1*1.5) + ($this->fltOT2*2) + ($this->fltOT3*3) + ($this->fltOT4*4) + ($this->fltOT5*5);
    }

  }
  
?>