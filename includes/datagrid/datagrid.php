<?php
 /*
	Dedy's class DataGrid
	version 1.0
	PT. Invosa Systems
	All right reserved.
  Last Update : 21 April 2008
 */
require_once("datagrid.config.php");
require_once("excel/Workbook.php");
require_once("excel/Worksheet.php");

DEFINE("DATAGRID_RENDER_NORMAL", 0);
DEFINE("DATAGRID_RENDER_EXCEL_HTML", 1);
DEFINE("DATAGRID_RENDER_EXCEL_BIFF", 2);

class cDataGrid
{
  //set privileges of datagrid to delete, edit or view
  var $message;
  var $caption = "";
  var $strTitle1 = "";
  var $strTitle2 = "";
  var $strTitle3 = "";
  var $DATAGRID_RENDER_OUTPUT = null;
  
  //these properties must be called from method setAJAXCallBackScript()
  //do not set directly
  var $scriptFileName = "";
  var $useAJAXTechnology = false;
  //-------------------------------------------------------------
  
  //autoscroll property,
  //if set true, then datagrid will have auto scroll box (if the width column > table width)
  var $autoScroll = false;
  
  //autoscroll property,
  //if set true, then datagrid will have own form tag <form id=....
  //if set false, then datagrid must be placed inside other form tag from your own HTML,
  //call method disableFormTag to set this variable to false
  var $hasFormTag = true;

  var $isShowSearch;
  //set $isShowPageNumbering = true, to show paging
  var $isShowPageNumbering;
  //set $isShowPageLimit = true, to show view limit page size 
  //(have relation with variable $isShowPageNumbering) 
  var $isShowPageLimit;
  //set $isShowSort = true, to show sort by column
  var $isShowSort;
  
  var $hasCheckbox = false;
  var $checkboxItemID = "";
  var $checkboxes;
  
  //the datagrid object name
  var $gridName;
  //the datagrid width
  var $width;
  //the datagrid height
  var $height;

  //the grid dataset
  var $dataset = array();
  
  //current page number
  var $pageNumber = 1;
  var $jumpPage = 0;
  //total number of page
  var $pageCount;
  //current page limit
  //set "all" to make unlimited page size, otherwise set to positive integer e.g : 15
  var $pageLimit;
  //current sort key
  var $pageSortBy;
  //current search key
  var $pageSearchBy;
  //current search criteria value
  var $pageSearchCriteria;

  //total numbers of data (use for paging)
  //must be set before calling method render
  var $totalData = "0";
  
  //column set, consist of datagrid column object
  var $columnSet = array();
  //datagrid buttons collections
  var $buttons;

  //sort order ASC (Ascending) or DESC (Descending)
  var $sortOrder;
  //sort order key field
  var $sortName;

  var $blankSpace;
  
  var $defaultPageLimit;
  var $defaultPagingSize = "10";

  //set CSS file name
  var $CSSFileName;
  var $JSFileName;
  //CSS class name
	//FIXED!!! don't change this value, unless you change datagrid.css too
  var $gridClass = "dataGrid";
  //------------------------------------------------------------------------------
  
  //FIXED!!!  dont change value below, unless you change JAVASCRIPT datagrid_.js too
  //------------------------------------------------------------------------------
  var $buttonSearchName = "btnSearch";
  var $buttonSpecialName = "";
  var $imageName;   //should be ASC or DESC
  var $imageList; 
  var $formNameId = "form1";
  //repeater function
  var $repeaterFunction = "";
  
  var $strFileNameXLS = "";
  var $CSSFileNameExcel = "";
  //export excel default font-size
  var $fontSize = 9;
  
  var $strGroupByField = "";
  var $hasGroupBy = false;
  var $hasGrandTotal = false;
  var $summaryData = array();
  var $wkb = null;
  var $sheet = null;
  var $intExcelRows = 0;
  //---------------------------------------

  // Class constructor
  function cDataGrid($formName = "form1", 
                     $parGridName = "DataGrid1", 
                     $gridWidth = "100%", 
                     $gridHeight = "100%", 
                     $showPageLimit = true, /*true = showing datagrid page limit control*/
                     $showSearch = true, /*true = showing datagrid search feature*/
                     $showSort = true, /*true = showing datagrid sort feature*/
                     $showPageNumbering = true, /*true = showing datagrid page numbering*/
                     $path = null /*force path to*/ 
                    )
  {    
    global $CLASSDATAGRIDPATH;
    global $DEFAULTPAGELIMIT;
    global $DEFAULTPAGINGSIZE;
    global $DATAGRID_CSS;
    global $globalLanguage;
    
    $this->DATAGRID_RENDER_OUTPUT = DATAGRID_RENDER_NORMAL;
    $this->formNameId = $formName;
    $this->isShowPageNumbering = $showPageNumbering;
    $this->isShowSearch = $showSearch;
    $this->isShowPageLimit = $showPageLimit;
    $this->isShowSort = $showSort;
    $this->width = $gridWidth;
    $this->height = $gridHeight;
    $this->gridName = $parGridName;
    //don't change value below, because it is connected with Javascript function
    $this->buttonSearchName = "btnSearch".$parGridName;
    $this->buttonSpecialName = "";
    $this->defaultPageLimit = $DEFAULTPAGELIMIT;
    $this->defaultPagingSize = $DEFAULTPAGINGSIZE;
    if ($path != null) $CLASSDATAGRIDPATH = $path;
    
    if ($globalLanguage != "en" && $globalLanguage != "id") $dataLanguage = "en"; 
    else $dataLanguage = $globalLanguage;

    $this->CSSFileName = ereg_replace('/+', '/', $CLASSDATAGRIDPATH."/css/".$DATAGRID_CSS.".css");
    $this->CSSFileNameExcel = ereg_replace('/+', '/', $CLASSDATAGRIDPATH."/css/".$DATAGRID_CSS.".excel.css");
    $this->JSFileName = ereg_replace('/+', '/', $CLASSDATAGRIDPATH."/scripts/datagrid_".$dataLanguage.".js");
    
    if (!is_file($this->CSSFileName))
      die("SKIN/CSS for datagrid was not found, please check your SETTING below in datagrid.config.php:<br />
- \$CLASSDATAGRIDPATH<br />
- \$DATAGRID_CSS");
    if (!is_file($this->CSSFileNameExcel))
      $this->CSSFileNameExcel = $this->CSSFileName;
    
    if (!is_file($this->JSFileName))
      die("Javascript for datagrid was not found, please check your SETTING below in datagrid.config.php:<br />
- \$CLASSDATAGRIDPATH");

    $imagePath = ereg_replace('/+', '/', $CLASSDATAGRIDPATH."/css/".$DATAGRID_CSS."/");
    $this->imageList = array("sort_desc" => $imagePath."sortDesc.gif",
                             "sort_asc" => $imagePath."sortAsc.gif",
                             "indicator" => $imagePath."indicator.gif",
                             "left_arrow" => $imagePath."2leftarrow.png",
                             "right_arrow" => $imagePath."2rightarrow.png"
                            );

    $this->buttons = array();
    $this->blankSpace = "";
  }
  
  function setPermission($a, $b, $c)
  {
    //obsolete function;
  }
  
  function setCaption($caption)
  {
    $this->caption = $caption;
  }
  
  //merubah setting default paging size, default 15
  //merubah setting page limit, pilihan "all",100,50,25,20,15, ...
  function setPageLimit($limit)
  {
    $this->defaultPageLimit = $limit;
  }  
  
  //merubah setting page limit, defaultnya 10
  function setPagingSize($size)
  {
    $this->defaultPagingSize = $size;
  }
  
  //jika fungsi ini dipanggil maka datagrid tidak akan me-render tag <form....
  //pastikan jika anda memanggil fungsi ini, anda telah menyiapkan tag <form anda sendiri
  //kalo tidak semua fungsi sort, search, jump page, dll tidak berfungsi.
  function disableFormTag()
  {
    $this->hasFormTag = false;
  }
  
  //masukkan CSS file anda disini
  function setCSSFile($fileName)
  {
    if (is_file($fileName))
      $this->CSSFileName = $fileName;
    else 
      $this->CSSFileName = "";
  }
  
  //fungsi ini digunakan jika anda ingin menambahkan sesuatu untuk direpeat setiap row (TR)
  //disisipkan dibawah dari row 1 dan seterusnya
  //misal anda ingin menambahkan TR dengan display none bisa anda lakukan disini juga
  function addRepeaterFunction($fungsiRowGenerator)
  {
    //$fungsiRowGenerator = function yang ada di file PHP pemanggil datagrid ini
    //contoh fungsi berikut dapat ditambahkan di file PHP
    //function drawHiddenRow($params) //$params harus ada
    //{
    //  extract($params);
    //  $strResult = "<tr valign=top style=\"display:none;background-color:#eeeeee\" id=\"detail$counter\">\n";
    //  $strResult .= "  <td colspan=2 align=\"center\"><strong>Members List</strong></td>\n";
    //  $strResult .= "  <td colspan=5><div id=\"detailData$counter\"></div></td>\n";
    //  $strResult .= "</tr>\n";	
    //  return $strResult;
    //}
    //perhatikan $counter adalah reserved word => menghasilkan counter secara urut row by row
    //jika anda ingin mengakses record gunakan reserved word $record
    $this->repeaterFunction = $fungsiRowGenerator;
  }

  //this function only draw colspan header
  function addSpannedColumn($strHeader, $intColSpan)
  {
    $column = new DataGrid_Column($strHeader, "", 
                           array("colspan" => $intColSpan), null,
                           false, false, null, null, 
                           "string", true, 0, false, "");
    
    $this->columnSet = array_merge($this->columnSet, array($column));    
    return true;
  }

  function addColumn($column)
  {
    if (is_a($column, 'datagrid_column'))
    {
      $this->columnSet = array_merge($this->columnSet, array($column));
    }
    else 
      die("Wrong parameter on addColumn function");
    return true;
  }

  function addColumnNumbering($column)
  {
    if (is_a($column, 'datagrid_column'))
    {
	  	$column->item_formatter = "showItemNumber";
      $column->fieldName = "";
      $column->sortable = false;
      $column->searchable = false;
      $column->dataType = "integer";
      $column->isDatabaseField = false;
      if (!isset($column->attribs['align']))
        $column->attribs['align'] = 'center';
      
      $column->dataType = "integer";
      
      if ($column->xlsColumnWidth == 0) $column->xlsColumnWidth = 4;
      $this->columnSet = array_merge($this->columnSet, array($column));   
    }
    else 
      die("Wrong parameter on addColumnNumbering function");
    return true;
  }
  
  function addColumnCheckbox($column)
  {
    if ($this->hasCheckbox) die("Cannot add 2 column checkbox on 1 datagrid");
    if (is_a($column, 'datagrid_column'))
    {
      $this->checkboxItemID = $this->gridName."_".$column->columnName;
	  	$column->title_formatter = "_printCheckboxAll";
	  	$column->item_formatter = "_printCheckbox";
      $column->sortable = false;
      $column->searchable = false;
      $column->isDatabaseField = false;
      $this->columnSet = array_merge($this->columnSet, array($column));      
      $this->hasCheckbox = true;
    }
    else 
      die("Wrong parameter on addColumnCheckbox function");

    return true;
  }
  
  
  function addButton($id, $name, $type, $value, $clientAction="", $serverAction="", $class = "")
  {
		$this->buttons[] = array("special" => false, "id"=>$id, "name"=>$name, "type"=>$type, "value"=>$value, "class"=> "button", "clientAction"=>$clientAction, "serverAction"=>$serverAction);
  }

  function addButtonExportExcel($value = "Export to Excel", $strFileName = "exportData.xls", 
                              $strTitle1 = "", $strTitle2 = "", $strTitle3 = "")
  {
		$this->buttons[] = array("special" => false, "id"=>"btnExportXLS" , "name"=> "btnExportXLS", 
                             "type"=> "submit", "value"=>$value, 
                             "class"=> "button", 
                             "clientAction"=> "", 
                             "serverAction"=> "");
    $this->strFileNameXLS = $strFileName;
    $this->strTitle1 = $strTitle1;
    $this->strTitle2 = $strTitle2;
    $this->strTitle3 = $strTitle3;
  }
  
  function addButtonExportExcelHTML($value = "Export to Excel", $strFileName = "exportData.xls", 
                              $strTitle1 = "", $strTitle2 = "", $strTitle3 = "")
  {
		$this->buttons[] = array("special" => false, "id"=>"btnExportXLSHTML" , "name"=> "btnExportXLSHTML", 
                             "type"=> "submit", "value"=>$value, 
                             "class"=> "button", 
                             "clientAction"=> "", 
                             "serverAction"=> "");
    $this->strFileNameXLS = $strFileName;
    $this->strTitle1 = $strTitle1;
    $this->strTitle2 = $strTitle2;
    $this->strTitle3 = $strTitle3;
  }
  
  function addSpecialButton($id, $name, $type, $value, $clientAction="", $serverAction="")
  {
    $this->buttons[] = array("special" => true, "id"=>$id, "name"=>$name, "type"=>$type, "value"=>$value, "class"=> "buttonSpecial", "clientAction"=>$clientAction, "serverAction"=>$serverAction);
  }
  
  function _printOpeningTableContent()
  {
    //skip if output is to excel BIFF WRITER
    if ($this->DATAGRID_RENDER_OUTPUT == DATAGRID_RENDER_EXCEL_BIFF) return "";
    return "
    <table class=\"contentGrid\" cellspacing=\"0\" cellpadding=\"0\" border=\"0\" width=\"" . $this->width . "\">";
  }

  function _printClosingTableContent()
  {
    if ($this->DATAGRID_RENDER_OUTPUT == DATAGRID_RENDER_EXCEL_BIFF) return "";
    return "
    </table>";
  }
  
  function _printOpeningTableDataGrid()
  {
    //skip if output is to excel BIFF WRITER
    if ($this->DATAGRID_RENDER_OUTPUT == DATAGRID_RENDER_EXCEL_BIFF) return "";
    return "
      <tr>
        <td valign=\"top\">
          <table id=\"" . $this->gridName . "\" width=\"100%\" border=\"0\" cellpadding=\"0\" cellspacing=\"0\" class=\"" . $this->gridClass . "\">";
  }

  function _printClosingTableDataGrid()
  {
    if ($this->DATAGRID_RENDER_OUTPUT == DATAGRID_RENDER_EXCEL_BIFF) return "";
    return "
          </table>
        </td>
      </tr>";
  }
   
    /*
  -----------------------------------------------------------------------------------------------------------------------
  Fungsi dibawah ini digunakaan untuk keperlua migrasi aplikasi recruitmetn SMART U ke DEVOSA
  -----------------------------------------------------------------------------------------------------------------------
  */
  
  //======fungsi2 tambahan ===============================================================================================
  function getCriteria()
  {
    $strResult = "";
    //handle search dari datagrid
    //property pageSearchCriteria adalah: data kriteria yang dimasukkan pada saat tombol SEARCH di click
    //property pageSearchBy adalah: kriteria yang dipilih (dari drop-down list) pada saat tombol SEARCH di click, 
    //      jika berisi data kosong berarti pencarian adalah untuk semua field
    if ($this->isShowSearch)
    {
      if ($this->pageSearchCriteria!="")
        if ($this->pageSearchBy == "")
        {
          //any search field, maka looping untuk setial column dari datagrid yang ditampilkan
          foreach($this->columnSet as $col)
            if ($col->fieldName!='' && $col->searchable)
            {
              if ($strResult == "")
                $strResult = " lower(" . $this->_formatFieldName($col->fieldName) . ") LIKE '%" . strtolower($this->pageSearchCriteria) . "%'";
              else
                $strResult .= " OR lower(" . $this->_formatFieldName($col->fieldName) . ") LIKE '%" . strtolower($this->pageSearchCriteria) . "%'";
            }
          $strResult = "( ".$strResult. " )";
          
        }
        else
        {
          //specific search field
          $strResult = " lower(" . $this->_formatFieldName($this->pageSearchBy) . ") LIKE '%" . strtolower($this->pageSearchCriteria) . "%'";
        }
      return $strResult;
    }
    else
      return "";  
  }
  //==================== END ====================================================================================================================
  
  //======================= BEGIN GET SORT BY====================================================================================================
  function getSortBy()
  {
    //handle sort	
    if ($this->isShowSort) 
    {
      if ($this->pageSortBy!="") return $this->_formatFieldName($this->sortName) . " " . $this->sortOrder;
    }
    else
      if ($this->pageSortBy!="") return $this->_formatFieldName($this->pageSortBy). " ";
    
    return "";
  }
  //======================================================== END ================================================================================
   
   
   
   
  //draw/render html code
  function render($DATAGRID_RENDER_OUTPUT = null)
  {
    //force to render the output to yours.
    if ($DATAGRID_RENDER_OUTPUT !== null) $this->DATAGRID_RENDER_OUTPUT = $DATAGRID_RENDER_OUTPUT;
    
    //print header to download excel, only if the render output is to excel    
    if ($this->DATAGRID_RENDER_OUTPUT == DATAGRID_RENDER_EXCEL_HTML || $this->DATAGRID_RENDER_OUTPUT == DATAGRID_RENDER_EXCEL_BIFF)
    {
      $this->_showHttpHeader();
      //if render to BIFF WRITER the create workbook class
      if ($this->DATAGRID_RENDER_OUTPUT == DATAGRID_RENDER_EXCEL_BIFF)
        $this->_initWorkBook();
    }
    $strResult = "";
    //print CSS (stylesheet)
		$strResult .= $this->_printCSS();
    //render all datagrid input hidden
    $strResult .= $this->_createInputHidden();
    //print opening table contentGrid
    $strResult .= $this->_printOpeningTableContent();
    //print any datagrid message if exist
    $strResult .= $this->_printMessageRow();
    //render search bar
    //print page, captionm and searchbar on contentGrid
    $strResult .= $this->_printSearchBarAndCaptionRow();
    //print opening table of datagrid with id =   $thid->gridName
    $strResult .= $this->_printOpeningTableDataGrid();
    //print datagrid header row
    $strResult.= $this->_printGridHeaderRow();
    //print datagrid item row
    $strResult.= $this->_printGridItemRow();
    //print datagrid buttons
    $strResult.= $this->_printGridButtons();
    //print closing table datagrid 
    $strResult .= $this->_printClosingTableDataGrid();
    //print paging on contentGrid
    $strResult.= $this->_printPaging($this->jumpPage, $this->pageNumber, $this->totalData);
    //print closing table contentGrid
    $strResult .= $this->_printClosingTableContent();
    
    //print DIV loading/process indicator
    $strResult.= $this->_drawProgressBarIndicator();
    
    if ($this->DATAGRID_RENDER_OUTPUT == DATAGRID_RENDER_NORMAL)
    {
      if ($this->hasFormTag)
        $strResult = "
  <form id=\"". $this->formNameId ."\" name=\"". $this->formNameId . "\" method=\"post\">".$strResult."
  </form>";
  
      if ($this->autoScroll) $strResult = "<div style='overflow:auto'>".$strResult."</div>";
      $strResult .= $this->_drawJavascript();
      
  	  //print datagrid REMARKs 
      //PLEASE DO NOT EDIT THIS LINE BELOW
      //WARNING: DO NOT CHANGE any of this LINE BELOW because it used in AJAX request datagrid    
      $strResult = "
<!-- START OF DATAGRID: ".$this->gridName.", generated by Datagrid Class, by Dedy Sukandar -->
<div class=\"divDataGrid\" id=\"div".$this->gridName."\">".
"<!-- START OF DATAGRID CONTENT: ".$this->gridName." FOR AJAX -->".
$strResult;

      $strResult .= $this->_writeContextMenu();
      $strResult .= $this->_writeContextMenuJS();
      $strResult .= "
</div>
<!-- END OF DATAGRID CONTENT: ".$this->gridName." FOR AJAX -->
<!-- END OF DATAGRID: ".$this->gridName.", generated by Datagrid Class, by Dedy Sukandar -->";
    }
    
    if ($this->DATAGRID_RENDER_OUTPUT == DATAGRID_RENDER_EXCEL_HTML)
    {
      echo $strResult."</body></html>";
      exit();
    }
    
    if ($this->DATAGRID_RENDER_OUTPUT == DATAGRID_RENDER_EXCEL_BIFF)
    {
      $this->_disposeWorkBook();
      exit();
	  }
    return $strResult;
  }

  function _printDataset() 
  {
		$strResult = "";
				
		$intRows = 0;
    if (is_numeric($this->pageLimit) && $this->pageNumber>0) $intRows += $this->pageLimit * ($this->pageNumber-1);
    $currGroupBy = "--UNDEFINED--";
    $intNomor = 0;
    $isFirst = true;
    foreach ($this->dataset as $rowDb)
    {
      $intRows++;
      $this->intExcelRows++;
      //print TR
      if ($this->DATAGRID_RENDER_OUTPUT == DATAGRID_RENDER_NORMAL || $this->DATAGRID_RENDER_OUTPUT == DATAGRID_RENDER_EXCEL_HTML)
      {
        if (($intRows % 2) == 0)
          $strResult .= "
              <tr class=a valign=\"top\">";
        else
          $strResult .= "
              <tr valign=\"top\">";
      }
      //manage group by accumulation data
      if ($this->hasGroupBy)
      {
        $lastGroupBy = $currGroupBy;
        $currGroupBy = $rowDb[$this->strGroupByField];
        if (!$isFirst)
        {
          if ($lastGroupBy != $currGroupBy)
          {
            //accumulate sub total to grand total
            $this->_grandTotalAccumulation($lastGroupBy);
            //draw group by row
            $strResult .= $this->_renderGroupByRow($lastGroupBy);
            $intNomor = 0;
          }
        }
        else $isFirst = false;
      }
      $intNomor++;

      $counter = 0;
      foreach($this->columnSet as $idx => $value)
      {
        //do not print data if it is spanned column and no fieldName and no item _formatter
        if (($value->fieldName==="" || $value->fieldName==null) && 
            ($value->intColSpan > 1) && 
            ($value->item_formatter==="" || $value->item_formatter==null)) 
          continue;

        if ($this->DATAGRID_RENDER_OUTPUT == DATAGRID_RENDER_EXCEL_BIFF || $this->DATAGRID_RENDER_OUTPUT == DATAGRID_RENDER_EXCEL_HTML)
          if (!$value->showInExcel) continue;
        
        if ($value->item_formatter!="")
        {
          switch ($value->item_formatter)
          {
            case "showItemNumber" :
              //print auto numbering
              $strValue = $intNomor;
              $strValueSummary = 1;
              break;
            case "_printCheckbox" :
              $strValue = $this->_printCheckbox($value->columnName, $rowDb[$value->fieldName], $intRows);
              $strValueSummary = $strValue;
              break;
            default:
              $strValue = $this->_formatter($value->item_formatter, $rowDb, $intRows, $value->fieldName);
              $strValueSummary = $strValue;
          }
        }
        else
        {
          if ($value->fieldName != "") 
            $strValue = $rowDb[$value->fieldName];
          else 
            $strValue = "";
          
          $strValueSummary = $strValue;
        }

        if ($this->hasGroupBy)
        {
          if ($value->grouped)
          {
            $this->_subTotalAccumulation($currGroupBy, $value->groupField, $strValueSummary);
          }
          else if ($value->isTotalInformation)
          {
            //masukkan string pada parameter group field sebagai string/word total
            $this->_subTotalAccumulation($currGroupBy, $value->groupField, $value->groupField);
          }
        }
        
        $tdAttrib = $this->_serializeAttribute($value->attribs);
        
        if ($this->DATAGRID_RENDER_OUTPUT == DATAGRID_RENDER_NORMAL || $this->DATAGRID_RENDER_OUTPUT == DATAGRID_RENDER_EXCEL_HTML)
        {
          //print to HTML output
          if ($strValue === "" || $strValue === null) 
            $strValue="&nbsp;";
          $strResult .= "
              <td ".$tdAttrib.">" .$strValue. "</td>";
        }
        else
        {
          //print to excel output
          $strGeneratedFormatName = $this->_getCellFormat($value);
          
          if ($value->dataType == "numeric" || $value->dataType == "currency") 
            $this->sheet->write_number($this->intExcelRows, $counter, $strValue, $strGeneratedFormatName);
          if ($value->dataType == "integer")
            $this->sheet->write_number($this->intExcelRows, $counter, $strValue, $strGeneratedFormatName);
          else if ($value->dataType == "date")
            $this->sheet->write_string($this->intExcelRows, $counter, $strValue, $strGeneratedFormatName);
          else if ($value->dataType == "string")
            $this->sheet->write_string($this->intExcelRows, $counter, $strValue, $strGeneratedFormatName);
          else
            $this->sheet->write($this->intExcelRows, $counter, $strValue, $strGeneratedFormatName);

          $counter++;
        }        
      }//end of foreach($this->columnSet as $idx => &$value)
      if ($this->DATAGRID_RENDER_OUTPUT == DATAGRID_RENDER_NORMAL || $this->DATAGRID_RENDER_OUTPUT == DATAGRID_RENDER_EXCEL_HTML)
        $strResult .= "
            </tr>";
            
      
      //check is it have $repeaterFunction to draw?
      if ($this->DATAGRID_RENDER_OUTPUT == DATAGRID_RENDER_NORMAL || $this->DATAGRID_RENDER_OUTPUT == DATAGRID_RENDER_EXCEL_HTML)
        if ($this->repeaterFunction != "")
          //call to repeater function defined on client
          $strResult .= $this->_formatter($this->repeaterFunction, $rowDb, $intRows, $value->fieldName);
    } //foreach ($this->dataset as &$rowDb)
    if ($this->hasGroupBy)
    {
      //if not yet rendered for the subtotal 
      if (!$isFirst)
      {
        $lastGroupBy = $currGroupBy;
        //accumulate sub total to grand total
        $this->_grandTotalAccumulation($lastGroupBy);
        $strResult .= $this->_renderGroupByRow($lastGroupBy);
        //render grand total
        $strResult .= $this->_renderGrandTotal();
      }
    }
    
    if ($this->DATAGRID_RENDER_OUTPUT == DATAGRID_RENDER_NORMAL || $this->DATAGRID_RENDER_OUTPUT == DATAGRID_RENDER_EXCEL_HTML)
    {
      $strResult = "
            <!-- grid data -->
            <tbody>".$strResult."
            </tbody>
            <!-- end of grid data -->";
      return $strResult;
    }
    else
      return "";
    
	} // showData
  
  //accumulate all sub total to GRAND TOTAL
  function _grandTotalAccumulation($lastGroupBy)
  {
    //accumulate sub total to grand total
    if ($this->hasGrandTotal)
    {
      foreach($this->columnSet as $value)
      {
        if ($value->grouped)
        {
          $strValueSubTotal = $this->summaryData[$lastGroupBy][$value->groupField];
          if (!isset($this->summaryData['_GRANDTOTAL_'][$value->groupField]))
          {
            if (is_numeric($strValueSubTotal))
              $this->summaryData['_GRANDTOTAL_'][$value->groupField] = 0;
            else
              $this->summaryData['_GRANDTOTAL_'][$value->groupField] = "";
          }
          if (is_numeric($strValueSubTotal))
            $this->summaryData['_GRANDTOTAL_'][$value->groupField] += $strValueSubTotal;
        }
        else
        {
          if ($value->isTotalInformation)
          {
            $this->summaryData['_GRANDTOTAL_'][$value->groupField] = strtoupper("grand total");
          }
        }
      }
    }
  }
  
  //paramater:
  //1.  Group by name, 2. Group field, 3. value to be calculated
  function _subTotalAccumulation($currGroupBy, $groupField, $strValueSummary)
  {
    if (!isset($this->summaryData[$currGroupBy][$groupField]))
      $this->summaryData[$currGroupBy][$groupField] = $strValueSummary;
    else
    {
      if (is_numeric($strValueSummary))
        $this->summaryData[$currGroupBy][$groupField] += $strValueSummary;
      else if (!isset($this->summaryData[$currGroupBy][$groupField]))
        $this->summaryData[$currGroupBy][$groupField] = $strValueSummary;
    }
  }

  function getPageNumber()
  {
	  return (isset($_REQUEST["pageNumber".$this->gridName])) ? $_REQUEST["pageNumber".$this->gridName] : 1;
  }
  
  function getPageJump()
  {
	  return (isset($_REQUEST["pageJump".$this->gridName])) ? $_REQUEST["pageJump".$this->gridName] : 0;
  }

  function getPageLimit()
  {
	  $pageLimit =  (isset($_REQUEST["pageLimit".$this->gridName])) ? $_REQUEST["pageLimit".$this->gridName] : $this->defaultPageLimit;
	  if (!is_numeric($pageLimit)) $pageLimit = (isset($GLOBALS['ARRAY_PAGE_LIMIT'][0])) ? $GLOBALS['ARRAY_PAGE_LIMIT'][0] : "all";
    return $pageLimit;
  }
  
  function getPageSearchBy($defaultSearch = "")
  {
	  return (isset($_REQUEST["pageSearchBy".$this->gridName])) ? $_REQUEST["pageSearchBy".$this->gridName] : $defaultSearch;
  }
  
  function getPageSearchCriteria()
  {
	  return (isset($_REQUEST["pageSearchCriteria".$this->gridName])) ? $_REQUEST["pageSearchCriteria".$this->gridName] : "";
  }
  
  function getPageSortBy()
  {
	  if (isset($_REQUEST["pageSortBy".$this->gridName])) 
		  $pageSortBy = $_REQUEST["pageSortBy".$this->gridName];
	  else
		  //if no sort before then get default
		  if (count($this->columnSet)>0) 
        if ($this->pageSortBy == "" && $this->isShowSort)
          $pageSortBy = $this->_getDefaultField();
        else
          $pageSortBy = $this->pageSortBy;
    return $pageSortBy;
  }
  
  //input any pageSortBy string form
  //eg : string: "id DESC, name, dept DESC"
  function _formatOrderBy($pageSortBy, $escapeChar = "\"")
  {
    $arrOrder = explode(",", $pageSortBy);
    $arrResult = array();
    foreach ($arrOrder as $strOrder)
    {
      $arrSingleOrder = explode(" ", trim($strOrder));
      if (strpos($arrSingleOrder[0], ".") === false)
        $strResult = $escapeChar.str_replace($escapeChar, "", $strOrder).$escapeChar;
      else
      {
        //jika ada $escapeChar
        $arrSingleOrderFields = explode(".", $arrSingleOrder[0]);
        if (count($arrSingleOrderFields) == 2)
          $strResult = $arrSingleOrderFields[0].".".$escapeChar.str_replace($escapeChar, "", $strOrder).$escapeChar;
        else
          $strResult = $arrSingleOrder[0];
      }
      if (count($arrSingleOrder) > 1)
        $strResult .= " ".$arrSingleOrder[1];
      
      $arrResult[] = $strResult;
    }
    if (count($arrResult)>0)
      return implode(", ", $arrResult);
    else
      return "";
  }
  
  function _setSortData()
  {
    $this->sortName = "";
	  $this->sortOrder = "";    
	  if ($this->isShowSort)
	  {
		  //get current Sort Field and get the Sort Order
		  if ($this->pageSortBy!='') 
		  {
  			$strOrders = explode(" ",$this->pageSortBy,2);
  			if (count($strOrders)>0)
  			  if (count($strOrders)==2) 
  			  {
            $this->sortName = $strOrders[0];
            $this->sortOrder = " DESC";
            if (is_file($this->imageList['sort_desc']))
              $this->imageName = "<img src=\"".$this->imageList['sort_desc']."\" border=\"0\" style=\"vertical-align:middle\" height=\"15\" width=\"15\">";
            else
              $this->imageName = "<span>&nbsp;&#9650;</span>";
          }
          else 
          {
            $this->sortName = $strOrders[0];

            if (is_file($this->imageList['sort_asc']))
              $this->imageName = "<img src=\"".$this->imageList['sort_asc']."\" border=\"0\" style=\"vertical-align:middle\" height=\"15\" width=\"15\">";
            else
              $this->imageName = "<span>&nbsp;&#9660;</span>";
          }
		  }
		  else $this->imageName = "";
	  }
  }
  
  //this function will get last $_REQUEST and action that call before
  function getRequest()
  {
	  $this->pageNumber = $this->getPageNumber();
	  $this->pageJump = $this->getPageJump();
    $this->pageLimit = $this->getPageLimit();
    $this->pageSearchBy = $this->getPageSearchBy();
    $this->pageSearchCriteria = $this->getPageSearchCriteria();
    $this->pageSortBy = $this->getPageSortBy();
    
    $this->_setSortData();

    $lengthText = strlen($this->checkboxItemID);
    $this->checkboxes = array();
    foreach ($_POST as $strIndex => $strValue)
      if (substr($strIndex,0,$lengthText) == $this->checkboxItemID)
        $this->checkboxes[$strIndex] = $strValue;

	  if (count($this->buttons)>0)
	  {
      foreach($this->buttons as $button)
      {
        if (isset($_POST[$button['name']]) && $button['type'] == 'submit') 
        {
          if ($button['name'] == 'btnExportXLS')
          {
            $this->DATAGRID_RENDER_OUTPUT = DATAGRID_RENDER_EXCEL_BIFF;
          }
          else if ($button['name'] == 'btnExportXLSHTML')
          {
            $this->DATAGRID_RENDER_OUTPUT = DATAGRID_RENDER_EXCEL_HTML;
          }
 
          $funcName = str_replace("(","",$button['serverAction']);
          $funcName = str_replace(")","",$funcName);
          if (is_callable($funcName)) 
            call_user_func($funcName);
          else
          {
            $this->message = "
            Button \"".$button['value']."\" is supposed to call function \"".$funcName."()\" when it submitted to server,
            <br />
            but the PROGRAMMER/SOFTWARE DEVELOPER forgets to add that function.
            <br />
            Contact SOFTWARE VENDOR if this error happened
            <br />
            PROGRAMMER Hint: You have to add function \"".$funcName."()\" in your coding.";
          }
        }
      }
	  }
  }
  
  function getOffsetStart()
  {
    if ((is_numeric($this->pageLimit)) && ($this->pageLimit > 0))
      $intTotalPage = ceil($this->totalData / $this->pageLimit);
    else 
      $intTotalPage = 1;
    if ($this->pageNumber > $intTotalPage) $this->pageNumber = $intTotalPage;
	  $offsetStart = (($this->pageNumber -1) * $this->pageLimit);
    if ($offsetStart < 0) $offsetStart = 0;
    return $offsetStart;
  }

  function bind($ar)
  {
    if (is_array($ar)) 
    {
      $this->dataset = $ar;
      return true;
    } 
    else
      return false;
  }


  //parameter: nama file script yang akan dipanggil ketika AJAX request ke server terjadi
  function setAJAXCallBackScript($scriptFileName)
  {
    $this->scriptFileName = $scriptFileName;
    $this->useAJAXTechnology = true;
  }
  
  function _formatFieldName($fieldName)
  {
    if (defined("DB_TYPE"))
    {
      if (DB_TYPE == "mssql")
      {
        if (strpos($fieldName, ".") === false)
          return "[".str_replace(array("[", "]"), "", $fieldName)."]";
        else
        {
          $arrFields = explode(".", $fieldName);
          if (count($arrFields) == 2)
            return $arrFields[0]."."."[".str_replace(array("[", "]"), "", $arrFields[1])."]";
          else
            return $fieldName;
        }
      }
      else if (DB_TYPE == "mysql")
      {
        if (strpos($fieldName, ".") === false)
          return "`".str_replace("`", "", $fieldName)."`";
        else
        {
          $arrFields = explode(".", $fieldName);
          if (count($arrFields) == 2)
            return $arrFields[0]."."."`".str_replace("`", "", $arrFields[1])."`";
          else
            return $fieldName;
        }
      }
    }
    //DEFAULT POSTGRES;
    
    if (strpos($fieldName, ".") === false)
      return "\"".str_replace("\"", "", $fieldName)."\"";
    else
    {
      $arrFields = explode(".", $fieldName);
      if (count($arrFields) == 2)
        return $arrFields[0]."."."\"".str_replace("\"", "", $arrFields[1])."\"";
      else
        return $fieldName;
    }
  }
  
  //fungsi2 tambahan 
  function getKriteriaString()
  {
    $strResult = "";
    //handle search dari datagrid
    //property pageSearchCriteria adalah: data kriteria yang dimasukkan pada saat tombol SEARCH di click
    //property pageSearchBy adalah: kriteria yang dipilih (dari drop-down list) pada saat tombol SEARCH di click, 
    //      jika berisi data kosong berarti pencarian adalah untuk semua field
    if ($this->isShowSearch)
    {
      if ($this->pageSearchCriteria!="")
        if ($this->pageSearchBy == "")
        {
          //any search field, maka looping untuk setial column dari datagrid yang ditampilkan
          foreach($this->columnSet as $col)
            if ($col->fieldName!='' && $col->searchable)
            {
              if ($strResult == "")
                $strResult = " lower(" . $this->_formatFieldName($col->fieldName) . ") LIKE '%" . strtolower($this->pageSearchCriteria) . "%'";
              else
                $strResult .= " OR lower(" . $this->_formatFieldName($col->fieldName) . ") LIKE '%" . strtolower($this->pageSearchCriteria) . "%'";
            }
          $strResult = "( ".$strResult. " )";
          
        }
        else
        {
          //specific search field
          $strResult = " lower(" . $this->_formatFieldName($this->pageSearchBy) . ") LIKE '%" . strtolower($this->pageSearchCriteria) . "%'";
        }
      return $strResult;
    }
    else
      return "";  
  }
  
  function getTotalData($db, &$strSQLCOUNT)
  {
    if ($this->DATAGRID_RENDER_OUTPUT == DATAGRID_RENDER_EXCEL_BIFF) return 0;

    $totalData = 0;    
    if ($db->connect())
    {
      //get Data and set to Datagrid's DataSource by set the data binding (bind method)
      $strKriteria = $this->getKriteriaString();
      if ($strKriteria != "")
        if (strpos(strtolower($strSQLCOUNT), "where "))
          $strSQLCOUNT .= " AND ".$strKriteria;
        else
          $strSQLCOUNT .= " WHERE ".$strKriteria;
            
      $res = $db->execute($strSQLCOUNT);
      if ($rowDb = $db->fetchrow($res, "BOTH")) $totalData = $rowDb[0]; else $totalData = 0;
    }
   
    return $totalData;    
  }
  

  function getData($db, &$strSQL)
  {
    $arrData = array();
    
    if ($db->connect())
    {
      //get Data and set to Datagrid's DataSource by set the data binding (bind method)
      $strKriteria = $this->getKriteriaString();
      if ($strKriteria != "")
        if (strpos(strtolower($strSQL), "where "))
          $strSQL .= " AND ".$strKriteria;
        else
          $strSQL .= " WHERE ".$strKriteria;
        
      //handle sort	
      if ($this->isShowSort) 
      {
        if ($this->pageSortBy!="") $strSQL .= " ORDER BY " . $this->_formatFieldName($this->sortName) . " " . $this->sortOrder;
      }
      else
        if ($this->pageSortBy!="") $strSQL .= " ORDER BY " . $this->pageSortBy. " ";

      //handle page limit
      //since MSSQL do not have OFFSET (except for MSSQL 2005), we use dataSeek function to make offset and limit
    	if (defined("DB_TYPE"))
      {
        if (DB_TYPE == "mssql")
        {
          //handle page limit
          $res = $db->execute($strSQL);
          $pageLimit = 0;
          if ($this->DATAGRID_RENDER_OUTPUT == DATAGRID_RENDER_NORMAL)
          {
          	if ($this->isShowPageLimit)
          		if (is_numeric($this->pageLimit) && $this->pageLimit > 0)
              {
                $pageLimit = $this->pageLimit;
                //jump to offset 
                if ($db->numrows() > 0)
                  $db->dataSeek($this->getOffsetStart());
              }
          }
            
          $counter = 0;
          while ($rowDb = $db->fetchrow($res, "ASSOC"))
          {
            $counter++;
            $arrData[] = $rowDb;
            if ($pageLimit > 0 && $counter == $pageLimit)
              //stop after limit reach
              break;
          }
          return $arrData;
          //end of getData()
        }
      }
      //else if not MSSQL then...
      if ($this->isShowPageLimit && $this->DATAGRID_RENDER_OUTPUT == DATAGRID_RENDER_NORMAL)
    		if (is_numeric($this->pageLimit) && $this->pageLimit > 0)
    			$strSQL .= " LIMIT $this->pageLimit OFFSET ".$this->getOffsetStart();
    
      $arrData = $db->getRecordSet($strSQL);

    }
    //echo $strSQL;
   
    return $arrData;
  }  
  
  function groupBy($strGroup)
  {
    $this->strGroupByField = $strGroup;
    $this->hasGroupBy = true;
  }
  
  function _getRecordSet($db, $strSQL)
  {
    $res = $db->execute($strSQL);
    $arrResult = array();
    while ($rowDb = $db->fetchrow($res)) 
      $arrResult[] = $rowDb;

    return $arrResult;
  }
  
  function _renderGroupByRow($groupBy)
  {
    if (!$this->hasGroupBy) return "";
    if ($this->DATAGRID_RENDER_OUTPUT == DATAGRID_RENDER_EXCEL_BIFF)
    {
      $this->_renderGroupByRowToExcel($groupBy);
      return "";
    }
    if ($groupBy == "_GRANDTOTAL_")
      $strResult = "<tr class=gt>";
    else
      $strResult = "<tr class=gs>";
    $intSkip = 0;
    $intSpanned = 0;
    foreach($this->columnSet as $idx => $value)
    {
      if ($intSkip > 0) 
      {
        $intSkip--;
        if ($value->intColSpan <= 1) $intSpanned++;
        continue;
      }
      if ($value->grouped)
      {
        if ($intSpanned > 0)
          $strResult = str_replace("[var.colspan]", "colspan=\"".($intSpanned)."\"", $strResult);
        else
          $strResult = str_replace("[var.colspan]", "", $strResult);
        
        $tdAttrib = $this->_serializeAttribute($value->attribs);
        $intSpanned = 0;
        if ($value->intColSpan > 1) $intSkip = $value->intColSpan;
        else $intSkip = 0;
        $strValue = $this->summaryData[$groupBy][$value->groupField];

        $strResult .= "<td $tdAttrib>".$strValue."</td>";
      }
      else
      {
        if ($value->isTotalInformation)
        {
          $strValue = $this->summaryData[$groupBy][$value->groupField];
          //$strValue = $value->groupField;
          if ($intSpanned > 0)
            $strResult = str_replace("[var.colspan]", "colspan=\"".($intSpanned)."\"", $strResult);
          else
          {
            $strResult = str_replace("[var.colspan]", "", $strResult);
            $tdAttrib = "[var.colspan]";
            $strResult .= "<td $tdAttrib>".$strValue."</td>";
          }
        }
        else
        {
          if ($intSpanned == 0 && $value->intColSpan <= 1)
          {
            $tdAttrib = "[var.colspan]";
            $strResult .= "<td $tdAttrib>&nbsp;</td>";
          }
        }
        if ($value->intColSpan <= 1) $intSpanned++;
        //$strValue = $value->groupField;
      }
    }
    if ($intSpanned > 0)
    {
      if (strpos($strResult, "[var.colspan]") === false)
        $strResult .= "<td colspan=$intSpanned>&nbsp;</td>";
      else
        $strResult = str_replace("[var.colspan]", "colspan=\"".($intSpanned)."\"", $strResult);
    }
    else
    {
      $strResult = str_replace("[var.colspan]", "", $strResult);
    }
    $strResult .= "</tr>";
    return $strResult;
  }  

  function _renderGrandTotal()
  {
    if (!$this->hasGrandTotal) return "";
    return $this->_renderGroupByRow("_GRANDTOTAL_");
  }  
  
  function _renderGroupByRowToExcel($groupBy)
  {
    if (!$this->hasGroupBy) return;
    $intSkip = 0;
    $intSpanned = 0;
    $intLastColumnPosition = 0;
    $counter = 0;
    $this->intExcelRows++;
    foreach($this->columnSet as $idx => $value)
    {
      if ($intSkip > 0) 
      {
        $intSkip--;
        if ($value->intColSpan <= 1) $intSpanned++;
        continue;
      }

      if ($value->grouped)
      {
        if ($intSpanned > 0)
        {
          // merge cell sebanyak intSpanned
          if ( $intLastColumnPosition < $value->intColumnPosition )
            $this->sheet->merge_cells($this->intExcelRows, $intLastColumnPosition, $this->intExcelRows, ($value->intColumnPosition - 1) );
            
        }
        $intLastColumnPosition = $value->intColumnPosition + 1;
        $intSpanned = 0;
        if ($value->intColSpan > 1) $intSkip = $value->intColSpan;
        else $intSkip = 0;
        $strValue = $this->summaryData[$groupBy][$value->groupField];

        //print cell
        $strGeneratedFormatName = $this->_getCellFormat($value, "silver");
        if ($value->dataType == "numeric" || $value->dataType == "currency") 
          $this->sheet->write_number($this->intExcelRows, $value->intColumnPosition, $strValue, $strGeneratedFormatName);
        if ($value->dataType == "integer")
          $this->sheet->write_number($this->intExcelRows, $value->intColumnPosition, $strValue, $strGeneratedFormatName);
        else if ($value->dataType == "date")
          $this->sheet->write_string($this->intExcelRows, $value->intColumnPosition, $strValue, $strGeneratedFormatName);
        else if ($value->dataType == "string")
          $this->sheet->write_string($this->intExcelRows, $value->intColumnPosition, $strValue, $strGeneratedFormatName);
        else
          $this->sheet->write($this->intExcelRows, $value->intColumnPosition, $strValue, $strGeneratedFormatName);
        
        $counter++;
      }
      else
      {
        if ($value->isTotalInformation)
        {
          $strValue = $this->summaryData[$groupBy][$value->groupField];
          if ($intSpanned > 0)
          {
            //merge cell
            if ( $intLastColumnPosition < $value->intColumnPosition )
              $this->sheet->merge_cells($this->intExcelRows, $intLastColumnPosition, $this->intExcelRows, ($value->intColumnPosition - 1) );

          }
          else
          {
            //write cell
            $value->dataType = "string";
            $value->attribs['align'] = "left";
            $strGeneratedFormatName = $this->_getCellFormat($value, "silver");
            $this->sheet->write_string($this->intExcelRows, $value->intColumnPosition, $strValue, $strGeneratedFormatName);
          }
          $intLastColumnPosition = $value->intColumnPosition;
        }
        else
        {
          //write blank cell
          $strGeneratedFormatName = $this->_getCellFormat($value, "silver");
          $this->sheet->write_blank($this->intExcelRows, $value->intColumnPosition, $strGeneratedFormatName);
        }
        if ($value->intColSpan <= 1) $intSpanned++;
      }
    }
    if ($intSpanned > 0)
    {
      //merge last found spanned cell
      if ( $intLastColumnPosition < $value->intColumnPosition )
        $this->sheet->merge_cells($this->intExcelRows, $intLastColumnPosition, $this->intExcelRows, $value->intColumnPosition );
    }
  }  
  
  //Membuat hidden2 yang diperlukan untuk paging dan sorting
  function _createInputHidden()
  {
    //only print if render is normal
    if ($this->DATAGRID_RENDER_OUTPUT == DATAGRID_RENDER_EXCEL_HTML || $this->DATAGRID_RENDER_OUTPUT == DATAGRID_RENDER_EXCEL_BIFF) return "";
    
    $strResult= "";
	  if ($this->isShowPageNumbering)
		  $strResult.= "
    <input type=\"hidden\" id=\"" . "pageNumber".$this->gridName . "\" name=\"" . "pageNumber".$this->gridName . "\" value=\"" . $this->pageNumber . "\">
    <input type=\"hidden\" id=\"" . "pageJump".$this->gridName . "\" name=\"" . "pageJump".$this->gridName . "\" value=\"" . $this->jumpPage . "\">
    <input type=\"hidden\" id=\"" . "pageCount".$this->gridName . "\" name=\"" . "pageCount".$this->gridName . "\" value=\"" . $this->pageCount . "\">";

	  if ($this->isShowSort)
		  $strResult.= "
    <input type=\"hidden\" id=\"" . "pageSortBy".$this->gridName . "\" name=\"" . "pageSortBy".$this->gridName . "\" value=\"". $this->pageSortBy. "\">";
    return $strResult;
  }

  //Fungsi untuk mencetak search bar
	function _drawJavascript()
	{
    $strResult = "";
    if (!$GLOBALS['PROTOTYPE_LOADED'])
    {
      $GLOBALS['PROTOTYPE_LOADED'] = true;
      $strResult .= "
  <script type=\"text/javascript\" src=\"".$GLOBALS['CLASSDATAGRIDPATH']."scripts/datagrid_prototype.js"."\"></script>";
    }
    
    $strResult .= "
  <script type=\"text/javascript\" src=\"".$this->JSFileName."\"></script>
  <script type=\"text/javascript\">
    var specialButton_".$this->gridName." = new Array();";
    $counter = 0;
    foreach($this->buttons as $button)
    {
      if ($button['special'])
      {
          $strResult .= "
    specialButton_".$this->gridName."[".$counter."] = '".$button['id']."';";

        $counter++;
      }
    }
    
    $useAJAXTechnology = ($this->useAJAXTechnology) ? "true" : "false";
    $strResult .= "
    o".$this->gridName." = new myDatagrid('".$this->scriptFileName."', '".$this->formNameId."', '".$this->gridName."', ".count($this->dataset).", specialButton_".$this->gridName.", ".$useAJAXTechnology.");
    o".$this->gridName.".loadGrid();
    
    if ($('".$this->gridName."').tBodies[0])
    {
      var aRows = $('".$this->gridName."').tBodies[0].rows;
      var nRows = aRows.length;
      var currClassName = '';
      for(var i=0;i<nRows;i++)
      {
        currClassName = aRows[i].className;
        aRows[i].onmouseover = function() { this.className = this.className + 'Hover'; };
        aRows[i].onmouseout  = function() { this.className = this.className.replace('Hover', ''); };
      }
    }";
    if (count($this->buttons)>0)
    {
      foreach($this->buttons as $button)
      {
        if ($button['special'] && (count($this->dataset) == 0)) continue;

        $strResult.= "$('".$button['name']."').onmouseover = function() { this.className = this.className + 'Hover' };";
        $strResult.= "$('".$button['name']."').onmouseout = function() { this.className = this.className.replace('Hover', '') };";
      }
    }
    
    $strResult .= "
  </script>";
		return $strResult;
	}
  
  function _printDatagridCaptionExcel()
  {
    if ($this->strTitle1 != '') 
    {
      $formatCaption1 =& $this->wkb->add_format();
      $formatCaption1->set_size($this->fontSize + 2);
      $formatCaption1->set_bold(1);
      $formatCaption1->set_align('vcenter');
      $this->sheet->write_string($this->intExcelRows++, 0, $this->strTitle1, $formatCaption1);
    }
    if ($this->strTitle2 != '') 
    {
      $formatCaption2 =& $this->wkb->add_format();
      $formatCaption2->set_size($this->fontSize + 1);
      $formatCaption2->set_bold(1);
      $formatCaption2->set_align('vcenter');
      $this->sheet->write_string($this->intExcelRows++, 0, $this->strTitle2, $formatCaption2);
    }
    if ($this->strTitle3 != '') 
    {
      $formatCaption3 =& $this->wkb->add_format();
      $formatCaption3->set_size($this->fontSize);
      $formatCaption3->set_bold(1);
      $formatCaption3->set_align('vcenter');
      $this->sheet->write_string($this->intExcelRows++, 0, $this->strTitle3, $formatCaption3);
    }
  }
  
  function _printSearchBarAndCaptionRow()
  {
    if ( $this->DATAGRID_RENDER_OUTPUT == DATAGRID_RENDER_EXCEL_BIFF )
    {
      $this->_printDatagridCaptionExcel();
      return "";
    }
    $strResult = "
      <tr>
        <td height=\"10\" nowrap>
          <table class=\"pageLimitGrid\" width=\"100%\" border=\"0\" cellpadding=\"1\" cellspacing=\"0\">
            <tr>";
    
    if ($this->DATAGRID_RENDER_OUTPUT == DATAGRID_RENDER_NORMAL)
    {
  	  if ($this->isShowPageLimit)
  	  {
  		  $strResult.= "
              <td width=280>&nbsp;
                <input type=\"hidden\" id=\"" . "pageLimit".$this->gridName ."\" name=\"" . "pageLimit".$this->gridName . "\" value=\"". $this->pageLimit . "\">";
  		  //render page limit
        $pageLink = "";
        //set default page limit to all if no pageLimit defined
        if ($this->pageLimit == "") $this->pageLimit = $GLOBALS['ARRAY_PAGE_LIMIT'][0];//"all";
        foreach($GLOBALS['ARRAY_PAGE_LIMIT'] as $pageLimitValue)
        {
          if ($pageLink == "")
            if ($this->pageLimit == $pageLimitValue)
              $pageLink .= $GLOBALS['DATAGRIDWORDS']["view"].": $pageLimitValue";
            else
              $pageLink .= $GLOBALS['DATAGRIDWORDS']["view"].": <a href=\"javascript:o".$this->gridName.".setPageSize('".$pageLimitValue."')\">".$pageLimitValue."</a>";
          else
            if ($this->pageLimit == $pageLimitValue)
              $pageLink .= " | $pageLimitValue";
            else
              $pageLink .= " | <a href=\"javascript:o".$this->gridName.".setPageSize('".$pageLimitValue."')\">".$pageLimitValue."</a>";
        }
        $strResult.= $pageLink."</td>";
      }
    }
    //print datagrid caption
    $strResult.= "
              <td class=\"dataGridTitle\" nowrap>".$this->caption."</td>";

    if ($this->DATAGRID_RENDER_OUTPUT == DATAGRID_RENDER_NORMAL)
  	{
      if ($this->isShowSearch)
  	  {
  		  $strResult.= "
              <td width=300 align=right>
                <select id=\"" . "pageSearchBy".$this->gridName . "\" name=\"" . "pageSearchBy".$this->gridName . "\">
                  <option value=\"\">".$GLOBALS['DATAGRIDWORDS']['any part of field']."</option>" ;

  		  foreach($this->columnSet as $col)
  		    if ($col->fieldName!='' && $col->searchable)
  					if ($this->pageSearchBy == $col->fieldName)
  						$strResult .= "
                  <option value=\"". $col->fieldName. "\" selected>". $col->columnName. "</option>";
  				  else 
  						$strResult .= "
                  <option value=\"". $col->fieldName. "\">". $col->columnName. "</option>";
		
        $strResult .= "
                </select>
                <input class=\"textbox\" type=\"text\" size=\"20\" id=\"" . "pageSearchCriteria".$this->gridName . "\" name=\"" . "pageSearchCriteria".$this->gridName . "\" value=\"" . $this->pageSearchCriteria . "\" />
                <input class=\"buttonSearch\" id=\"" . $this->buttonSearchName . "\" name=\"" . $this->buttonSearchName . "\" type=\"button\" value=\"\" onClick=\"javascript:o".$this->gridName.".search()\">
              </td>";
      }
    }

    $strResult.= "
            </tr>
          </table>
        </td>
      </tr>";
    return $strResult;
  }
  
  function _createExcelFormat($color = null, $bgcolor = null, $font_size = null, $is_bold = false, $border = null, $h_align = null, $v_align = null)
  {
    // buat format excel untuk header row
    $format =& $this->wkb->add_format();
    
    if ($color != null) $format->set_color('white');
    if ($bgcolor != null)
    {
      $format->set_pattern();
      $format->set_fg_color($bgcolor);
    }
    if ($font_size == null) $font_size = $this->fontSize;
    $format->set_size($this->fontSize);
    if ($is_bold) $format->set_bold(1);
    if (intval($border) > 0) $format->set_border($border);
    if ($h_align != null) $format->set_align($h_align);
    if ($v_align != null) $format->set_align($v_align);
    
    return $format;
  }

  function _printHeaderColumnExcel($col, $intRowPos)
  {
    if ($col->xlsColumnWidth > 0) 
      $this->sheet->set_column($col->intColumnPosition, $col->intColumnPosition, $col->xlsColumnWidth);
    if ($col->showInExcel) 
    {
      $this->sheet->write_string($intRowPos, $col->intColumnPosition, $col->columnName, $this->formatTableHeader);
    }
    //merged cell if any...
    if ($col->intRowSpan > 1 || $col->intColSpan > 1)
    {
      if ($col->intRowSpan == 0) $col->intRowSpan = 1;        
      if ($col->intColSpan == 0) $col->intColSpan = 1;
      for($i = 0; $i < $col->intRowSpan; $i++)
        for($j = 0; $j < $col->intColSpan; $j++)
          if ($i != 0 || $j != 0)
            $this->sheet->write_blank($intRowPos + $i, $col->intColumnPosition + $j, $this->formatTableHeader);
      $this->sheet->merge_cells($intRowPos, $col->intColumnPosition, ($intRowPos + $col->intRowSpan - 1) , ($col->intColumnPosition + $col->intColSpan -1) );
    }
  }
  
  function _printHeaderColumn($col, $intRowPos)
  {
    if ($this->DATAGRID_RENDER_OUTPUT == DATAGRID_RENDER_EXCEL_BIFF) 
    {
      $this->_printHeaderColumnExcel($col, $intRowPos);
      return "";
    }
    
    $strEvents = "";
    if ($this->DATAGRID_RENDER_OUTPUT == DATAGRID_RENDER_NORMAL)
    {
      if (($this->isShowSort) && ($col->fieldName!='') && ($col->sortable))
      {
        $strEvents .= " onClick=\"javascript:o".$this->gridName.".goSort('".$col->fieldName."')\" title=\"Sort by " . str_replace(array("<br />", "<br>", "<br/>")," ",$col->columnName) ."\" ";
        $strEvents .= " onMouseOver=\"this.className='Hover'\" onMouseOut=\"this.className=''\" ";
      }
      $strValue = $col->columnName;      
    }
    else
      $strValue = $col->columnName;
      
    //serialize attribute
    $strResult = "";
    $tdAttrib = $this->_serializeAttribute( $col->titleAttribs );
    $strImage = "";
    if (($this->isShowSort) && ($col->fieldName!='') && ($col->sortable))
    {
      if ($this->sortName == $col->fieldName)
        $strImage = $this->imageName;
    }
    if ($col->intColSpan > 1)
    {
      $strResult.= "
          <th class='spannedCol' ".$tdAttrib.">" . nl2br($strValue) .$strImage. "</th>";
    }
    else
    {
      if ($col->isDatabaseField)
      {
        $strResult.= "
          <th id='hdrGrid".$col->fieldName."' ".$tdAttrib.$strEvents.">" . nl2br($strValue) . $strImage. "</th>";
      }
      else
        $strResult.= "
          <th ".$tdAttrib.$strEvents.">" . nl2br($strValue) . $strImage ."</th>";
      
    }
    return $strResult;
  }
  
  //fungsi untuk mencetak header dari datagrid
  //anda dapat meng-overload fungsi ini untuk mencetak header grid sesuai keinginan anda
  //hubungi Dedy Sukandar untuk mengetahui cara meng-overload fungsi dalam class, atau cari di google deh....
  function _printGridHeaderRow()
  {
    $strResult  = "";
    $arrTempColumn = array();

    //set deep level, get maximum deep level, and set positioning of column
    $maxDeepLevel = $this->_setHeaderDeepLevel();

    if ($this->DATAGRID_RENDER_OUTPUT == DATAGRID_RENDER_EXCEL_BIFF)
    {
      // buat format excel untuk header row
      $this->formatTableHeader =& $this->_createExcelFormat(null, 50, null, true, 1, 'center', 'vcenter');
    }
    
    if ($this->DATAGRID_RENDER_OUTPUT == DATAGRID_RENDER_NORMAL || $this->DATAGRID_RENDER_OUTPUT == DATAGRID_RENDER_EXCEL_HTML)
      $strResult .= "
        <!-- grid header -->
        <thead>";
    
    for ($level = 1; $level <= $maxDeepLevel; $level++)
    {
      if ($this->DATAGRID_RENDER_OUTPUT == DATAGRID_RENDER_NORMAL || $this->DATAGRID_RENDER_OUTPUT == DATAGRID_RENDER_EXCEL_HTML)
        $strResult .= "
          <tr align=\"center\">";

      foreach($this->columnSet as $col)
      {
        //only print at the same level
        if ($col->intDeepLevel != $level) continue;

        $intRowPos = $this->intExcelRows + $level - 1;
        
        if ($this->DATAGRID_RENDER_OUTPUT == DATAGRID_RENDER_EXCEL_HTML || $this->DATAGRID_RENDER_OUTPUT == DATAGRID_RENDER_EXCEL_BIFF)
          if (!$col->showInExcel) continue;
        
        //if column has title _formatter then do this....
        if ($col->title_formatter!="")
        {
          switch ($col->title_formatter)
          {
            case "_printCheckboxAll" :
              $strValue = $this->_printCheckboxAll();
              break;
            default :
              $strValue = $this->_formatter($col->title_formatter, null, null, null);
          }
          //sorry, not sorting capability for header of type formatter
          $col->sortable = false;
          $col->columnName = $strValue;
          $col->isDatabaseField = false;
          $strResult .= $this->_printHeaderColumn($col, $intRowPos);
        }
        else
        {
          //just print the title/header
          //----------------------------------
          $strResult .= $this->_printHeaderColumn($col, $intRowPos);
        }
      }
      if ($this->DATAGRID_RENDER_OUTPUT == DATAGRID_RENDER_NORMAL || $this->DATAGRID_RENDER_OUTPUT == DATAGRID_RENDER_EXCEL_HTML)
      $strResult.= "
          </tr>";
    }
    if ($this->DATAGRID_RENDER_OUTPUT == DATAGRID_RENDER_NORMAL || $this->DATAGRID_RENDER_OUTPUT == DATAGRID_RENDER_EXCEL_HTML)
    $strResult.= "
          </thead>
          <!-- end of grid header -->";

    $this->intExcelRows += $maxDeepLevel - 1;

    return $strResult;
  }

  function _printGridButtons()
  {
    $strResult = "";
    if ($this->DATAGRID_RENDER_OUTPUT == DATAGRID_RENDER_NORMAL)
    {
      $colSpan = count($this->columnSet);
      if ($this->hasCheckbox && (count($this->dataset) > 0))
        //have checkbox
        $strResult.= "
            <!-- grid footer -->
            <tfoot>
            <tr>
              <td align=\"center\">".$this->_printCheckboxAllBottom()."</td>
              <td colspan=\"" . ($colSpan-1). "\">";
      else
        //don't have checkbox
        $strResult.= "
            <!-- grid footer -->
            <tfoot>
            <tr>
              <td colspan=\"" . $colSpan . "\">";

      if (count($this->buttons)>0)
      {
        foreach($this->buttons as $button)
        {
          if ($button['special'] && (count($this->dataset) == 0)) continue;
          if ($button['class']=="")
            $className = "";
          else
            $className = "class=\"". $button['class'] . "\"";

          $strResult.= "
              <input ".$className." name=\"" . $button['name'] . "\" type=\"" . $button['type'] . "\" id=\"" . $button['id'] . "\" value=\"" . $button['value'] . "\" " . $button['clientAction'] . ">&nbsp;";
        }
      }
      $strResult.= "&nbsp;</td>
            </tr>
            </tfoot>
            <!-- end of grid footer -->";
    }
    return $strResult;
  }  
  
  function _printNoItem()
  {
    //if no data
    $strResult = "";
    $colSpan = count($this->columnSet);
    if ($this->DATAGRID_RENDER_OUTPUT == DATAGRID_RENDER_NORMAL || $this->DATAGRID_RENDER_OUTPUT == DATAGRID_RENDER_EXCEL_HTML)
      $strResult .= "
          <!-- grid data -->
          <tbody>
          <tr valign=\"top\">
            <td colspan=\"".$colSpan."\">" .$GLOBALS['DATAGRIDWORDS']['no data']. "</td>
          </tr>
          </tbody>
          <!-- end of grid data -->";
    return $strResult;
  }
  
  function _printGridItemRow()
  {
    $colSpan = count($this->columnSet);
    $strResult = "";
    if (count($this->dataset) == 0)
    {
      $strResult .= $this->_printNoItem();
    }
    else
    {
      $strResult.= $this->_printDataset();	
    }
    return $strResult;
  }

  function _drawProgressBarIndicator()
  {
    if ($this->DATAGRID_RENDER_OUTPUT == DATAGRID_RENDER_EXCEL_HTML || $this->DATAGRID_RENDER_OUTPUT == DATAGRID_RENDER_EXCEL_BIFF)
      return "";

    global $DATAGRIDWORDS;
    return "
    <div id=\"divIndicatorGrid\" class=\"indicator\" style=\"display : none\">
      <img src=\"".$this->imageList['indicator']."\" align=\"left\" width=\"16\" height=\"16\" border=\"0\">
      <span id=\"textGrid\">".$DATAGRIDWORDS['please wait']."<br />".$DATAGRIDWORDS['loading grid']."...</span>
    </div>";
  }
  
  function _getCellFormat($col, $backgroundColor = "")
  {
    $strGeneratedFormatName = "";
    if (isset($col->attribs['align']))
    {
      $strAlign = strtolower($col->attribs['align']);
      if ($strAlign != 'center' && $strAlign != 'left' && $strAlign != 'right')
        $strAlign = '';
        
      $strGeneratedFormatName .= "format".$col->dataType.$strAlign.$backgroundColor;
    }
    else
    {
      $strAlign = '';
      $strGeneratedFormatName .= "format".$col->dataType.$backgroundColor;
    }
    if (!isset($GLOBALS[$strGeneratedFormatName]))
    {
      $GLOBALS[$strGeneratedFormatName] =& $this->wkb->add_format();
      $GLOBALS[$strGeneratedFormatName]->set_size($this->fontSize);
      $GLOBALS[$strGeneratedFormatName]->set_border(1);
      if ($backgroundColor != '')
      {
        $GLOBALS[$strGeneratedFormatName]->set_pattern();
        $GLOBALS[$strGeneratedFormatName]->set_fg_color($backgroundColor);
      }
      if ($col->dataType == "numeric" || $col->dataType == "currency") 
      {
        $GLOBALS[$strGeneratedFormatName]->set_align('right');
      }
      if ($col->dataType == "integer")
      {
        $GLOBALS[$strGeneratedFormatName]->set_align('right');
      }
      else if ($col->dataType == "date")
        $GLOBALS[$strGeneratedFormatName]->set_align('center');
        
      if ($strAlign != '')
        $GLOBALS[$strGeneratedFormatName]->set_align($strAlign);
    }
    return $GLOBALS[$strGeneratedFormatName];
  }
  
  function _printCSS()
	{
    $strResult  = "";
    if ($this->DATAGRID_RENDER_OUTPUT == DATAGRID_RENDER_NORMAL)
    {
      if ($this->height != "100%" && $this->height!= "")
      {
        $strResult .= "
<style>
#".$this->gridName.">tbody 
{ 
  overflow: auto; 
  height: ".str_replace("px", "", $this->height)."px; 
  overflow-x: hidden; 
}
</style>";
      }
      if ($this->CSSFileName !== "")
        if (is_file($this->CSSFileName)) 
          $strResult .= "\n<link href=\"".$this->CSSFileName."\" rel=\"stylesheet\" type=\"text/css\">";
	  }
    else if ($this->DATAGRID_RENDER_OUTPUT == DATAGRID_RENDER_EXCEL_HTML)
    {
      $strResult = "<html><head><style>".file_get_contents($this->CSSFileNameExcel)."</style></head><body>";
    }
    return $strResult;
	}
  
  function _initWorkBook()
  {
    $this->wkb =& new workbook("-");
    //create custom color index 50
    $this->wkb->set_custom_color(50, 200, 200, 200);
    // Add a worksheet to the file, returning an object to add data to
    $this->sheet =& $this->wkb->add_worksheet('Data'); 
    // buat objek workbook dan worksheet
    $this->intExcelRows = 0;
  }
  
  function _disposeWorkBook()
  {
    $this->wkb->close();
    $this->wkb = null;
    $this->sheet = null;
    $this->intExcelRows = 0;
  }

  function _formatAttribute($key, $value)
  {
		$attribute = '';
		$attributeFormat = '%s="%s"';
		$minimizedAttributes = array('compact', 'checked', 'declare', 'readonly', 'disabled', 'selected', 'defer', 'ismap', 'nohref', 'noshade', 'nowrap', 'multiple', 'noresize');

		if (in_array($key, $minimizedAttributes)) 
    {
			if ($value === 1 || $value === true || $value === 'true' || $value == $key) 
      {
				$attribute = sprintf($attributeFormat, $key, $key);
			}
		} 
    else 
    {
			$attribute = sprintf($attributeFormat, $key, $value);
		}
		return $attribute;
	}
  
	function _formatter($par, $record, $counter, $fieldName)
  {
    // Define the Parameter list
    $paramList = array();
    //set reserved word untuk $record dan $counter
    //$record digunakan untuk mengakses record tiap row
    //$counter adalah auto nomor urut
    $paramList['record'] = $record;    
    $paramList['counter'] = $counter;
    $paramList['value'] = (isset($record[$fieldName])) ? $record[$fieldName] : "";
    
    // Determine callback and additional parameters
    if ($size = strpos($par, '(')) 
    {
      // Retrieve the name of the function to call
      $_formatter = substr($par, 0, $size);
      if (strstr($_formatter, '->')) 
          $_formatter = explode('->', $_formatter);
      elseif (strstr($_formatter, '::'))
          $_formatter = explode('::', $_formatter);

      // Build the list of parameters
      $length = strlen($par) - $size - 2;
      $parameters = substr($par, $size + 1, $length);
      $parameters = ($parameters === '') ? array() : split(',', $parameters);
      // Process the parameters
      foreach($parameters as $param)
        if ($param != '') 
        {
          $param = str_replace('$', '', $param);
          if (strpos($param, '=') != false) 
          {
            $vars = split('=', $param);
            $paramList[trim($vars[0])] = trim($vars[1]);
          }
          else
            $paramList[$param] = $$param;
        }
      //end of foreach
    } 
    else
      $_formatter = $par;

    // Call the _formatter
    if (is_callable($_formatter)) $result = call_user_func($_formatter, $paramList);
    
    else $result = false;

    return $result;
  }
  
  //serialize array into 2-pair value  
  function _serializeAttribute($arrAttribute)
  {
    $strAttribute = "";
    if ($arrAttribute != null)
    {
      if (is_array($arrAttribute))
      {
        $arrResult = array();
        foreach ($arrAttribute as $key => $value)
        {
          $arrResult[] = $this->_formatAttribute($key, $value);
        }
        $strAttribute = implode(' ', $arrResult);
      }
      else
        $strAttribute = $arrAttribute;
    }

    return $strAttribute;
  }  

  function _getDefaultField()
  {
    $strResult = "";
    if (count($this->columnSet)>0) 
      foreach($this->columnSet as $col)
      if ($col->fieldName!='' && $col->sortable)
      {
        $strResult = $col->fieldName;
        break;
      }
    return $strResult;
  }
  
  //set header deep level for colspan and rowspan feature
  //purpose: to set deep level, get maximum deep level, and set positioning of column
  //return value: maximum deep level
  function _setHeaderDeepLevel($excelOutput = false)
  {
    //define all deep level...
    $maxDeepLevel = 1;
    $numOfCol = count($this->columnSet);
    
    $colCounter = -1;
    
    foreach($this->columnSet as $key => $col)
    {
      //do not process if this will be printed on excel and the column is hided
      if ($excelOutput && !$col->showInExcel) continue;
      
      if ($this->columnSet[$key]->intColumnPosition == null)
      {
        $colCounter++;
        $this->columnSet[$key]->intColumnPosition = $colCounter;
      }
      
      if ($col->intColSpan >= 1)
      {
        $intCurrentColSpan = $col->intColSpan;
        if ($col->intRowSpan <= 0) 
          $intCurrentRowSpan = 1;
        else
          $intCurrentRowSpan = $col->intRowSpan;
          
        $deepLevel = $col->intDeepLevel + $intCurrentRowSpan;
        $intLastColumnPosition = $this->columnSet[$key]->intColumnPosition;
        
        if ($maxDeepLevel < $deepLevel) $maxDeepLevel = $deepLevel;
        
        $i = $key + 1;
        
        while (($i < $numOfCol) && ($intCurrentColSpan > 0))
        {
          //if tested deep level below then current deep level then do this...
          if (($this->columnSet[$i]->intDeepLevel < $col->intDeepLevel) || 
              ($this->columnSet[$i]->intDeepLevel == 1))
          {
            $this->columnSet[$i]->intDeepLevel = $deepLevel;
            $this->columnSet[$i]->intColumnPosition = $intLastColumnPosition;

            if ($this->columnSet[$i]->intColSpan > 1)
              $intLastColumnPosition += $this->columnSet[$i]->intColSpan;
            else
              $intLastColumnPosition++;
            
            if ($this->columnSet[$i]->intColSpan > 0)
            {
              $i = $i + $this->columnSet[$i]->intColSpan;
              $intCurrentColSpan = $intCurrentColSpan - $this->columnSet[$i]->intColSpan;
            }
            else
              $intCurrentColSpan--;
          }
          $i++;
        }
        $colCounter += ($col->intColSpan - 1);
      }
    }
    return $maxDeepLevel;
  }
  
  function _printMessageRow()
  {
    //skip if the output is to EXCEL
    if ($this->DATAGRID_RENDER_OUTPUT == DATAGRID_RENDER_EXCEL_HTML || $this->DATAGRID_RENDER_OUTPUT == DATAGRID_RENDER_EXCEL_BIFF)
      return "";
    
    if (trim($this->message) == "")
      return "
    <tr>
      <td align=\"center\" id=\"message".$this->gridName."\"></td>
    </tr>";
    else
      return "
    <tr>
      <td align=\"center\" class=\"dataGridMessage\" id=\"message".$this->gridName."\">".$this->message."</td>
    </tr>";
  }

  function _printCheckboxAll()
  {
    if ($this->DATAGRID_RENDER_OUTPUT == DATAGRID_RENDER_NORMAL)
      return "<input class=c type=\"checkbox\" name=\"".$this->gridName."_chkAll\" id=\"".$this->gridName."_chkAll\"  onClick=\"o".$this->gridName.".checkAllClicked(this)\">";
    else
      return "";
  }

  function _printCheckboxAllBottom()
  {
    if ($this->DATAGRID_RENDER_OUTPUT == DATAGRID_RENDER_NORMAL)
      return "<input class=c type=\"checkbox\" name=\"".$this->gridName."_chkAllBottom\" id=\"".$this->gridName."_chkAllBottom\"  onClick=\"o".$this->gridName.".checkAllClicked(this)\">";
    else
      return "";
  }

  function _printCheckbox($name, $data, $counter)
  {
    if ($this->DATAGRID_RENDER_OUTPUT == DATAGRID_RENDER_NORMAL)
      return "<input class=c type=\"checkbox\" name=\"".$this->checkboxItemID.$counter."\" id=\"".$this->checkboxItemID.$counter."\"  value=\"".$data."\" onClick=\"o".$this->gridName.".itemListClicked(this)\">";
    else
      return "";
  }
  
  
  function _printPaging($jumpPageType = 0, $intPage = 1, $intTotal = 1)
  {
    if ($this->DATAGRID_RENDER_OUTPUT == DATAGRID_RENDER_EXCEL_HTML || $this->DATAGRID_RENDER_OUTPUT == DATAGRID_RENDER_EXCEL_BIFF)
      return "";

    global $DATAGRIDWORDS;
    
    //if no paging exit this function
    if (!$this->isShowPageNumbering) return "";
    
    if ((is_numeric($this->pageLimit)) && ($this->pageLimit>0) && $this->isShowPageLimit)
    {
      $intRowsLimit=$this->pageLimit;
      if ($intRowsLimit==0) $intRowsLimit=1;
      
      $strResult = "
          <table class=\"pagingGrid\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\" width=\"100%\">
            <tr>
              <td nowrap width=\"32\"><strong>&nbsp;".$DATAGRIDWORDS['page']."&nbsp;&nbsp;</strong></td>";
      // cari jumlah halaman
      $intTotalPage = ceil($intTotal / $intRowsLimit);
  
      //set start page numbering
      if ($intPage <= 0) $intPage = 1;
      if ($jumpPageType != '0') 
        //click from jump page not from normal navigation
        // (jump 10 page default, customizable, call setPagingSize method to set other than 10 page jump)
        $intPageStart = $intPage;
      else if (($intPage % $this->defaultPagingSize) == 0)
        $intPageStart = ((($intPage / $this->defaultPagingSize) - 1) * $this->defaultPagingSize) + 1; // + ($intPage % $this->defaultPagingSize);
      else
        $intPageStart = ((floor($intPage / $this->defaultPagingSize)) * $this->defaultPagingSize) + 1; // + ($intPage % $this->defaultPagingSize);
          //$intPageStart = floor($intPageStart / $this->defaultPagingSize) * $this->defaultPagingSize + 1;

      if ($intPageStart>$this->defaultPagingSize)
      {
        //draw previous jump button 
        $intPageStartPrev = $intPageStart - $this->defaultPagingSize;
        if ($intPageStartPrev<0) $intPageStartPrev = 1;
        if (is_file($this->imageList['left_arrow']))
          $pagingPrev = "
                <td align=\"center\" width=30><a href=\"javascript:o".$this->gridName.".goPageStart('".$intPageStartPrev."')". "\" title='". vsprintf($DATAGRIDWORDS['jump to %d of previous pages'],$this->defaultPagingSize)."'><img src='".$this->imageList['left_arrow']."' border=0 width=16 height=16></a></td>";
        else
          $pagingPrev = "
                <td align=\"center\" width=30><a href=\"javascript:o".$this->gridName.".goPageStart('".$intPageStartPrev."')". "\" title='". vsprintf($DATAGRIDWORDS['jump to %d of previous pages'],$this->defaultPagingSize)."'>&lt;&lt;</a></td>";
      }
      else
        $pagingPrev = "
                <td width=30 valign=middle>&nbsp;</td>";
      
      //set finish page numbering
      $intPageFinish = $intTotalPage;//$intPageStart + $this->defaultPagingSize - 1;
      if (($intPageFinish-$intPageStart)>$this->defaultPagingSize) 
      {
        //draw next jump button
        $intPageFinish = $intPageStart + $this->defaultPagingSize - 1;
        if (is_file($this->imageList['right_arrow']))
          $pagingNext = "
                <td align=\"center\" width=30>
                  <a href=\"javascript:o".$this->gridName.".goPageStart('".($intPageFinish+1)."')". "\" title='". vsprintf($DATAGRIDWORDS['jump to %d of next pages'],$this->defaultPagingSize)."'><img src='".$this->imageList['right_arrow']."' border=0 width=16 height=16></a>
                </td>";
        else
          $pagingNext = "
                <td align=\"center\" width=30><a href=\"javascript:o".$this->gridName.".goPageStart('".($intPageFinish+1)."')". "\" title='". vsprintf($DATAGRIDWORDS['jump to %d of next pages'],$this->defaultPagingSize)."'>&gt;&gt;</a></td>";
      }
      else
        $pagingNext = "
                <td width=30 valign=middle>&nbsp;</td>";

      if ($intPageStart < 1) $intPageStart = 1;

      //this one is to adjust paging number
      //misal; dari page 10 pada size 15 per page dengan total data 10 page,  akan otomatis menjadi page 8 pada size 20 per page
      if ($intPageFinish > $intTotalPage)
        $intPageFinish = $intTotalPage; 
      if ($intPage>$intPageFinish) 
        $intPage=$intPageFinish;
      
      // tambahkan link untuk prev dan first page
      $strLink = "javascript:o".$this->gridName.".goPage('[PAGE]')";
      $strResult .= $pagingPrev;

      for ($i = $intPageStart; $i <= $intPageFinish; $i++)
        if ($i == $intPage) 
          $strResult .= "
              <td align=\"center\" width=\"20\" class=\"currentPage\">$i</td>";
        else
          $strResult .= "
              <td align=\"center\" width=\"20\"><a href=\"" .str_replace("[PAGE]",$i,$strLink). "\">$i</a></td>";
  
      $strResult .= $pagingNext;
      $strResult.= "
              <td align=\"center\">[ ".$DATAGRIDWORDS['page']." ".number_format($intPage, 0, '', ',')." ".$DATAGRIDWORDS['of']." ".number_format($intTotalPage, 0, '', ',')." ]&nbsp;&nbsp;&nbsp;Total : ".number_format($this->totalData, 0, '', ',')." ".$DATAGRIDWORDS['records']."</td>";
      
      if ($intPage > 1) 
        $strResult .= "
              <td nowrap align=\"center\" width=\"20\">
                &nbsp;<a href=\"" . str_replace("[PAGE]","1",$strLink). "\" title=\"".$DATAGRIDWORDS['go to']." ".$DATAGRIDWORDS['first page']."\">".$DATAGRIDWORDS['first']."</a>&nbsp;
              </td>
              <td nowrap align=\"center\" width=\"20\">
                &nbsp;<a href=\"" . str_replace("[PAGE]",($intPage-1),$strLink) . "\" title=\"".$DATAGRIDWORDS['go to']." ".$DATAGRIDWORDS['previous page']."\">".$DATAGRIDWORDS['previous']."</a>&nbsp;";
      else
        $strResult .= "
              <td nowrap align=\"center\" width=\"20\" class=\"disabledNavigation\">
                &nbsp;".$DATAGRIDWORDS['first']."&nbsp;
              </td>
              <td nowrap align=\"center\" width=\"20\" class=\"disabledNavigation\">
                &nbsp;".$DATAGRIDWORDS['previous']."&nbsp;";
  
      // tambahkan link next dan last page
      if ($intPage < $intTotalPage)
        $strResult .= "
              </td>
              <td nowrap align=\"center\" width=\"20\">
                &nbsp;<a href=\"" .str_replace("[PAGE]",($intPage+1),$strLink). "\" title=\"".$DATAGRIDWORDS['go to']." ".$DATAGRIDWORDS['next page']."\">".$DATAGRIDWORDS['next']."</a>&nbsp;
              </td>
              <td nowrap align=\"center\" width=\"20\">
                &nbsp;<a href=\"" .str_replace("[PAGE]",$intTotalPage,$strLink). "\" title=\"".$DATAGRIDWORDS['go to']." ".$DATAGRIDWORDS['last page']."\">".$DATAGRIDWORDS['last']."</a>&nbsp;";
      else
        $strResult .= "
              </td>
              <td nowrap align=\"center\" width=\"20\" class=\"disabledNavigation\">
                &nbsp;".$DATAGRIDWORDS['next']."&nbsp;
              </td>
              <td nowrap align=\"center\" width=\"20\" class=\"disabledNavigation\">
                &nbsp;".$DATAGRIDWORDS['last']."&nbsp;";

      $strResult.= "
              </td>
            </tr>
          </table>";
       
      if ($strResult=="") $strResult = "          &nbsp;".$DATAGRIDWORDS['page']." 1&nbsp;";
      $strResult2 = "
      <tr>
        <td valign=\"top\" height=\"10\">";
      $strResult2 .= $strResult;
      $strResult2.= "
        </td>
      </tr>";
      return $strResult2;
    }
    else
    {
      $strResult = "
      <tr>
        <td valign=\"top\" height=\"10\">
          <table class=\"pagingGrid\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\" width=\"100%\">
            <tr>
              <td nowrap align=\"left\">
                &nbsp;Total : ".number_format($this->totalData, 0, '', ',')." ".$DATAGRIDWORDS['records']."
              </td>
            </tr>
          </table>
        </td>
      </tr>";
      return $strResult;
    }
  }//_printPaging

  
  // fungsi untuk mengirim header xls ke browser
  function _showHttpHeader() 
  {
    header("Content-type: application/vnd.ms-excel");
    header("Content-Disposition: attachment; filename=".$this->strFileNameXLS );
    header("Expires: 0");
    header("Cache-Control: must-revalidate, post-check=0,pre-check=0");
    header("Pragma: public");
  }//_showHttpHeader
  
  
  function _writeContextMenu()
  {
    $strResult = "
<div class='contextMenu' id=\"divContext".$this->gridName."\">
  <ul>
    <li style='text-decoration: underline'>Show/Hide Columns</li>";
    $counter = 0;
    foreach ($this->columnSet as $val)
    {
      if ($val->isDatabaseField)
      {
        $strResult .= "
    <li><input class=c type=\"checkbox\" name=\"dataHeader".$val->fieldName."\" id=\"dataHeader".$val->fieldName."\" checked onClick=\"javascript:showHideField($counter, '".$val->fieldName."')\" />".$val->columnName."</li>";
      }
      if ($val->intColSpan<=0)
        $counter++;
    }
    $strResult .= "
    <li><input type=button onClick=\"javascript:CloseContext()\" value=\"Close\" /></li>
  </ul>
</div>";
    return $strResult;
  }
  
  function _writeContextMenuJS()
  {
    $strResult  = "
  <script type=\"text/javascript\" src=\"".$GLOBALS['CLASSDATAGRIDPATH']."scripts/datagrid_context.js"."\"></script>";
    $strResult .= "
  <script type=\"text/javascript\">InitContext('".$this->formNameId."', '".$this->gridName."');</script>";
    return $strResult;
  }

}
//end of class DataGrid

class DataGrid_Column
{
  var $columnName;
  var $fieldName;
  var $titleAttribs;
  var $attribs;
  var $title_formatter;
  var $item_formatter;
  var $sortable;
  var $searchable;
  var $dataType; /* string, numeric, date, currency, link*/
  var $showInExcel; /* default true*/
  var $xlsColumnWidth; /* default 0*/
  var $grouped = false; /* default false*/
  var $groupField = ""; /* default ""*/

  //will be set from titleAttribs
  var $intColSpan = 0;
  var $intRowSpan = 0;
  
  //reserved variable
  //will be re-calculate again when you use rowspan/colspan attribute
  var $intDeepLevel = 1;
  //will be set for EXCEL merge cell
  //1st column position is 0
  var $intColumnPosition = null;
  
  var $isDatabaseField = true;
  /**
  * Constructor
  *
  * Creates default table style settings
  *
  * @param   string    $columnName       The name of the column to be printed
  * @param   string    $fieldName        The name of the field for the column
  *                                      to be mapped to
  * @param   string    $orderBy          The field to order the data by
  * @param   string    $titleAttribs          The HTML attributes for the header (TR-TH tag)
  * @param   string    $attribs          The HTML attributes for the item/data (TR-TD tag)
  * @param   boolean   $sortable         Whether or not the column is sortable
  * @param   boolean   $searchable         Whether or not the column is searchable
  * @param   string    $title_formatter   A defined function to call upon the header/title  print
  *                                      rendering to allow for special
  *                                      formatting ON TITLE HEADER.  This allows for
  *                                      call-back function to print out a 
  *                                      link or a form element, or whatever 
  *                                      you can possibly think of.
  * @param   string    $item_formatter    A defined function to call upon
  *                                      rendering to allow for special
  *                                      formatting ON ITEM.  This allows for
  *                                      call-back function to print out a 
  *                                      link or a form element, or whatever 
  *                                      you can possibly think of.

  * @param   string  $dataType         the type of data e.g: string, numeric, date, currency, link (url, email, etc)
  * @param   boolean  $showInExcel         this column will be shown in excel if this set to true
  * @param   double  $xlsColumnWidth         the column width of excel (be careful this is not same with width in HTML)

  * @access  public
  */
  function DataGrid_Column($columnName, $fieldName = null,
                           $titleAttribs = array(), $attribs = array(), 
                           $sortable = true, $searchable = true, $title_formatter = null, 
                           $item_formatter = null, 
                           $dataType = "string", $showInExcel = true, $xlsColumnWidth = 0,
                           $grouped = false, $groupField = "", $isTotalInformation = false)
  {
    $this->columnName = $columnName;
    if ($fieldName==null) $this->fieldName = ""; else $this->fieldName = $fieldName;
  
    if ($titleAttribs==null) 
      $this->titleAttribs = array(); 
    else 
    {
      if (isset($titleAttribs['rowspan']))
      {
        $this->intRowSpan = intval($titleAttribs['rowspan']);
      }
      if (isset($titleAttribs['colspan']))
      {
        $this->isDatabaseField = false;
        $this->intColSpan = intval($titleAttribs['colspan']);
      }
      $this->titleAttribs  = $titleAttribs;
    }
    if ($attribs==null) $this->attribs = array(); else $this->attribs  = $attribs;
    $this->sortable = $sortable;
    $this->searchable = $searchable;
    if ($title_formatter==null) $this->title_formatter= ""; else $this->title_formatter=$title_formatter;
    if ($item_formatter==null) $this->item_formatter= ""; else $this->item_formatter=$item_formatter;
    $this->dataType = $dataType;
    $this->showInExcel = $showInExcel;
    $this->xlsColumnWidth = $xlsColumnWidth;
    if ($isTotalInformation)
    {
      $this->grouped = false;
    }
    else
    {
      $this->grouped = $grouped;
    }
    $this->groupField = $groupField;
    $this->isTotalInformation = $isTotalInformation;
  }

} // end of class DataGrid_Column


?>
