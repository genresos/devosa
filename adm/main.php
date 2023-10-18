<?php
  include_once('../global/session.php');
  include_once("../global.php");

  $tbsPage = new clsTinyButStrong ;

  //write this variable in every page
  $strPageTitle = getWords('-- System Administration --') ;
  $pageIcon = "../images/icons/home.png";
  $strTemplateFile = "templates/main.html";
  
  if (!$GLOBALS['globalIsModuleLoaded'])
  {
    //jika daftar module belum ke load, maka load dahulu dari database
    //get Default Module, that is the first occurence module, order by sequence_no of table adm_module
    if ($GLOBALS['globalIdGroup'] != "")
    {
      $_SESSION['sessionModuleList'] = getDataModuleFromDatabase($GLOBALS['globalIdGroup']);
      if (count($_SESSION['sessionModuleList']) > 0)
      {
        $_SESSION['sessionModuleID'] = $_SESSION['sessionModuleList'][0]['id_adm_module'];
        $_SESSION['sessionModuleName'] = $_SESSION['sessionModuleList'][0]['name'];
      }
    }
  }
  
  if (!$GLOBALS['globalIsPrivilegesLoaded'])
    //jika data privileges user belum ke load, maka load dahulu dari database
    //get data privileges from database
    $_SESSION['sessionPrivileges'] = getDataPrivilegesFromDatabase($GLOBALS['globalIdGroup']);

 
  
  $strFavLink = generateFavLink();
  //------------------------------------------------
  $tbsPage->LoadTemplate("../templates/master.html");

  $tbsPage->Show() ;

  //recursively re-order the menu
  function reorderMenu($arrMenu, $id_menu = "", $menu_level, &$arrResult)
  {
    $next_menu_level = $menu_level+1;
    foreach($arrMenu as $key => $value)
    {
      if ($value['menu_level'] == $menu_level && $value['parent_id_adm_menu'] == $id_menu)
      {
        $arrResult[] = $value;
        reorderMenu($arrMenu, $value['id_adm_menu'], $next_menu_level, $arrResult);
      }
    }
    return $arrResult;
  }
  
  function generateFavLink()
  {
    $strResult = "";
    if (is_array($_SESSION['sessionPrivileges']))
    //pastikan bahwa data ini adalah array
    {
      $counter = 0;
      foreach ($_SESSION['sessionPrivileges'] as $data)
        if ($_SESSION['sessionModuleID'] == $data['id_adm_module'])
          $counter++;
      
      if ($counter <= 10) 
        $numOfColumn = 1;
      else if ($counter <=20)
        $numOfColumn = 2;
      else
        $numOfColumn = 3;
      
      $strResult = generateFavLinkTable($numOfColumn, $counter);

    }
    return $strResult;
  }
  
  function generateFavLinkTable($numOfColumn, $counter)
  {
    $strResult = "";
    $items = ceil($counter / $numOfColumn);
    $percentageColumn = (100 / $numOfColumn)."%";
    if (is_array($_SESSION['sessionPrivileges']))
    //pastikan bahwa data ini adalah array
    {
      reorderMenu($_SESSION['sessionPrivileges'], "", 0, $dataset);

      $counter = 0;
      
      $strNewTableBegin = "
          <td width=\"".$percentageColumn."\" valign=\"top\">
            <table width=\"100%\" cellpadding=\"1\" cellspacing=\"0\" border=\"0\">";
      $strNewTableEnd = "
            </table>
          </td>";
      $strRowMenu = "";
      foreach ($dataset as $data)
      {
        if ($_SESSION['sessionModuleID'] == $data['id_adm_module'])
        {
          if ($counter >= $items && $data['menu_level'] == 0)
          {
            $strResult .= $strNewTableBegin.$strRowMenu.$strNewTableEnd;
            $strRowMenu = "";
            $counter = 0;
          }
          $strRowMenu .=  generateFavLinkRow($data);

          $counter++;
        }
      }
      $strResult .= $strNewTableBegin.$strRowMenu.$strNewTableEnd;
      
      $strResult = "
      <table class=\"content\" border=\"0\" cellpadding=\"0\" cellspacing=\"0\">
        <tr>".$strResult."
        </tr>
      </table>";
    }
    return $strResult;
  }
  
  function generateFavLinkRow($data)
  {
    $strResult = "";
    if (!$data['icon_file']) $data['icon_file'] = 'blank.png';
    if ($data['menu_level'] == 0)
    {
      $strResult = "
              <tr valign=\"bottom\">
                <td height=\"40\" width=\"32\"><img src=\"../images/icons/".$data['icon_file']."\" width=\"32\" height=\"32\" border=\"0\" /></td>
                <td colspan=\"2\" class=\"pageHeader1\">".getWords($data['menu_name'])."</td>
              </tr>";
    }
    else if ($data['menu_level'] == 1)
    {
      $strResult = "
              <tr>
                <td>&nbsp;</td>
                <td colspan=\"2\" class=\"pageHeader4\"><a href=\"".$data['php_file']."\"><img src=\"../images/icons/".$data['icon_file']."\" width=\"16\" height=\"16\" border=\"0\" />".getWords($data['menu_name'])."</a></td>
              </tr>
              <tr>
                <td>&nbsp;</td>
                <td width=\"20\">&nbsp;</td>
                <td class=\"pageHeader5\">".getWords(nl2br($data['note']))."</td>
              </tr>";
    }
    return $strResult;
  }
?>