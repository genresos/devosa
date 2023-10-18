<?php
  DEFINED('VALID_APPLICATION') or die("Sorry, direct access to page <span style=\"color:red\">".$_SERVER['PHP_SELF']."</span> is prohibited!");
  $link = array(
            array("url" => "main.php", "name" => "home"),
            array("url" => "help.php", "name" => "help"),
            array("url" => "changepwd.php", "name" => "change password")
          );
  $strResult = "
        <div class=\"footer\">
      		<div class=\"footerPage\"> </div>
          <div align=\"right\">
            <table border=\"0\" cellpadding=\"0\" cellspacing=\"0\">
              <tr>";
  foreach($link as $val)
    $strResult .= "
                <td nowrap class=\"bottomSeparator\"></td>
                <td nowrap class=\"bottomMenu\" onMouseOver=\"this.className='bottomMenuHover'\" onMouseOut=\"this.className='bottomMenu'\" onClick=\"goMenu('".$globalRelativeFolder.$val['url']."')\">".strtoupper(getWords($val['name']))."</td>";
            
  $strResult .= "
                <td nowrap class=\"bottomSeparator\"></td>
                <td nowrap class=\"bottomMenu\" onMouseOver=\"this.className='bottomMenuHover'\" onMouseOut=\"this.className='bottomMenu'\" onClick=\"goMenu('".$globalRelativeFolder."logout.php','','".getWords("exit application message")."')\">".strtoupper(getWords("logout"))."</td>
              </tr>
            </table>
          </div>
        </div>";

        
  echo $strResult;
?>