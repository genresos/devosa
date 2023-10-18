<?php
  include_once('../global/session.php');
  include_once('global.php');
  include_once('activity.php');
  include_once('form_object.php');
  include_once('../global/employee_function.php');
  include_once('cls_annual_leave.php');


    $db = new CdbClass;
    $db->connect();
    $strSQL = "SELECT * FROM hrd_cost_center_member";
    $resExec = $db->execute($strSQL);
    echo "kucing";

    while ($row = $db->fetchrow($resExec))
    {
      $arrData = unserialize($row['attribute_value']);
      $arrDataNew = json_encode($arrData);

      $strSQL = "UPDATE hrd_cost_center_member SET attribute_value = '$arrDataNew' WHERE id = ".$row['id']."; ";
      $res = $db->execute($strSQL);
      }
echo "kambing";
?>
