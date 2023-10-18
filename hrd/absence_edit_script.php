<?php
  include_once('../global/session.php');
  include_once('global.php');
  include_once('activity.php');
  include_once('form_object.php');
  include_once('../global/employee_function.php');
  include_once('cls_annual_leave.php');

    $db = new CdbClass;
    $db->connect();
    $strSQL = "SELECT * FROM hrd_absence WHERE ((date_from, date_thru) OVERLAPS ('2017-04-24', '2017-04-24') OR date_thru = '2017-04-24' OR date_from = '2017-04-24') order by id";
    $resExec = $db->execute($strSQL);
    echo "kucing";

    while ($row = $db->fetchrow($resExec))
    {
      $strCurrDate = $row['date_from'];
      while (dateCompare($strCurrDate, $row['date_thru']) <= 0)
      {
          $arrShift = getShiftScheduleByDate($db, $strCurrDate, "", "", $row['id_employee']);
          $arrWorkSchedule = getWorkSchedule($db, $strCurrDate, $row['id_employee']);
          $bolIsAllDay = getEmployeeIsAllDay($db, $row['id_employee']);
          $bolIsSatIn = getEmployeeIsSatIn($db, $row['id_employee']);
          // 1. cek dari shift schedule
          if (isset($arrShift[$row['id_employee']]['shift_off']) && $arrShift[$row['id_employee']]['shift_off'] == 't') {
              $bolHoliday = isHoliday($strCurrDate, true, $bolIsAllDay, $bolIsSatIn);
          }
          // 2. cek dari work schedule
          else if (isset($arrWorkSchedule[$row['id_employee']]['day_off']) && $arrWorkSchedule[$row['id_employee']]['day_off'] == 't') {
              $bolHoliday = isHoliday($strCurrDate, true, $bolIsAllDay, $bolIsSatIn);
          }
          // 2. cek general setting
          else
            $bolHoliday = false;

          $dateDay = date("w", strtotime($strCurrDate));

          $strSQL2 .= "DELETE FROM hrd_absence_detail WHERE id_absence = '".$row['id']."' AND absence_date = '$strCurrDate' ;";
          //$res = $db->execute($strSQL);
          if ($bolHoliday == false) //jika bukan hari libur, masukkan datanya
          {
            $strSQL2 .= "INSERT INTO hrd_absence_detail (created,modified_by,created_by, id_absence, id_employee, absence_date, absence_type) ";
            $strSQL2 .= "VALUES ( now(),'" .$_SESSION['sessionUserID']. "','" .$_SESSION['sessionUserID']."', '".$row['id']."', '".$row['id_employee']."', '$strCurrDate','".$row['absence_type_code']."'); ";
            //$res = $db->execute($strSQL);
          }

          $strCurrDate = getNextDate($strCurrDate);
        }
      }
$res = $db->execute($strSQL2);
echo "kambing";
?>