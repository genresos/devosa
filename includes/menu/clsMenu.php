<?php
 /*
	Dedy's class DHTML Menu
	version 1.0
	PT. Invosa Systems
	All right reserved.
 */
class clsMenu
{
  var $menuItem;
  var $maxLevel;
  var $menuPath;
  var $iconPath;
  var $noscript = false;

  //IE only
  var $opacity = 100;
  // Class constructor
  function clsMenu($menuPath, /*$dataMenu, */$skin = "aqua", $iconPath = "")
  {    
    $this->menuPath = $menuPath;
    if ($iconPath == "") $iconPath = $menuPath;
    $this->iconPath = $iconPath;
    //$this->menuItem = $dataMenu;
    $this->skin = $skin;
  }

  function addBlankSpace($spaces)
  {
    if ($spaces > 0)
      return str_pad("",$spaces, " ", STR_PAD_LEFT);
    else
      return "";
  }
  
  function addMenuItem($menuItem)
  {
    if (is_a($menuItem, 'MenuItem'))
    {
      if ($menuItem->parentMenuId != null && $menuItem->parentMenuId != "")
        $this->menuItem[$menuItem->parentMenuId]->hasChild = true;

      $this->menuItem[$menuItem->menuId] = $menuItem;
    }
    else 
      return false;
    return true;
  }
  
  function replace_quote($val)
  {
    if (strpos($val, "\"") === false)
      return $val;
    else
    {
      if (strpos($val, "'") === false)
        return str_replace("\"","'",$val);
      else
      {
        $val = str_replace("'","\'",$val);
        return str_replace("\"","'",$val);
      }
    }
  }
  
  function recurseMenuItem($parentMenuId, $level)
  {
    $result = "";
    foreach($this->menuItem as $valueItem)
    {
      if (!isset($valueItem->menuLevel)) continue;
      if ($valueItem->menuLevel == $level)
        if ($valueItem->parentMenuId == $parentMenuId || ($parentMenuId == -1 && $level == 0 && $valueItem->parentMenuId == null))
        {
          if ($valueItem->hasChild)
          {
            $result .= $this->addBlankSpace($level*4+2)."<li>\n";
            if ($valueItem->hint != "")
              $title = "title='".$valueItem->hint."'" ;
            else
              $title = "";
              
            if ($valueItem->icon != "")
              $result .= $this->addBlankSpace($level*4+2)."  <img src=\"".$this->iconPath.$valueItem->icon."\" height=16 width=16 />\n";
            if ($level == 0)
              //main menu
              $result .= $this->addBlankSpace($level*4+2)."  <a><span $title class=\"downarrow\">".$valueItem->text."</span></a>\n";
            else
              //sub menu
              $result .= $this->addBlankSpace($level*4+2)."  <a><span $title>".$valueItem->text."</span></a>\n";
            $result .= $this->addBlankSpace($level*4+2)."  <ul>\n";
            $currlevel = $level + 1;
            $result .= $this->recurseMenuItem($valueItem->menuId, $currlevel);
            $result .= $this->addBlankSpace($level*4+2)."  </ul>\n";
            $result .= $this->addBlankSpace($level*4+2)."</li>\n";
          }
          else
          {
            $result .= $this->addBlankSpace($level*4+2)."<li>\n";
            if ($valueItem->hint != "")
              $title = "title='".$valueItem->hint."'" ;
            else
              $title = "";
            if ($valueItem->icon != "")
              $result .= $this->addBlankSpace($level*4+2)."  <img src=\"".$this->iconPath.$valueItem->icon."\" height=16 width=16 />\n";
            if ($valueItem->text != "" && $valueItem->text != "-")
              $result .= $this->addBlankSpace($level*4+2)."  <a $title href=\"".$this->replace_quote($valueItem->url)."\">".$valueItem->text."</a>\n";
              
            $result .= $this->addBlankSpace($level*4+2)."</li>\n";
          }
        }
    }
    return $result;
  }
    
  //draw/render html code
  function render()
  {
    
	  $strResult  = "
<!-- START OF MENU, generated by DHTML Menu Class, by Dedy Sukandar -->";

    $strResult .= "
<link rel=\"stylesheet\" href='".$this->menuPath."stylesheet/skin-".$this->skin.".css' />
<script type=\"text/javascript\">
  _dynarch_menu_url = \"".$this->menuPath."stylesheet/\";
</script>
<script type=\"text/javascript\" src=\"".$this->menuPath."scripts/hmenu.js\"></script>";
	  
    $strResult .= "
<ul id=\"page-menu\" style=\"display:none\">\n";
    if (count($this->menuItem) > 0)
      $strResult .= $this->recurseMenuItem(-1, 0);
	  
    $strResult .= "</ul>\n";
    if (!$this->noscript)
      $strResult .= "
<script type=\"text/javascript\">
  DynarchMenu.preloadImages();
  var mainmenu = DynarchMenu.setup('page-menu', { shadows: [-1, 0, 5, 5], scrolling: true, electric: false});
</script>";//<script type=\"text/javascript\" src=\"".$this->menuPath."scripts/PieNG.js\"></script>

	  $strResult .= "
<!-- END OF MENU, generated by DHTML Menu Class, by Dedy Sukandar -->";
	  return $strResult;
  }
}


class MenuItem
{
  var $menuId;
  var $menuLevel;
  var $parentMenuId;
  //even $hasChild can be set to false, this value will be set to true if item has child (when MenuItem being inserted to main class clsMenu)
  var $hasChild = false;
  var $text;
  var $url;
  var $icon;
  //HTML code before menu item
  var $htmlBefore;
  //HTML code after menu item
  var $htmlAfter;
  
  function MenuItem($menuId, $menuLevel = 0, $parentMenuId = null, $text = "", $url = "#", $icon = "", $hint = "", $hasChild = false, $htmlBefore = "", $htmlAfter = "")
  {
    $this->menuId = $menuId;
    $this->menuLevel = $menuLevel;
    $this->hasChild = $hasChild;
    if ($parentMenuId === "")
      $this->parentMenuId = null;
    else
      $this->parentMenuId = $parentMenuId;
    $this->text = $text;
    if ($url == null || $url == "") 
      $this->url = "#";
    else
      $this->url = $url;
    $this->icon = $icon;
    //remove <br /> if any
    $this->hint = preg_replace("/\<(B|b)(R|r)(\s)*(\/)?\>/", ", ", $hint);
    $this->htmlBefore = $htmlBefore;
    $this->htmlAfter = $htmlAfter;
  }
}
?>