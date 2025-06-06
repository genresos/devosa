<?php

/* OpenTBS version 1.5.0
Author  : Skrol29 (email: http://www.tinybutstrong.com/onlyyou.html)
Licence : LGPL
This class can open a zip file, read the central directory, and retrieve the content of a zipped file which is not compressed.
Site: http://www.tinybutstrong.com/plugins.php
*/

// Constants to drive the plugin.
define('OPENTBS_PLUGIN','clsOpenTBS');
define('OPENTBS_DOWNLOAD',1);   // download (default) = TBS_OUTPUT
define('OPENTBS_NOHEADER',4);   // option to use with DOWNLOAD: no header is sent
define('OPENTBS_FILE',8);       // output to file
define('OPENTBS_DEBUG_XML',16); // display the result of the current subfile
define('OPENTBS_STRING',32);    // output to string
define('OPENTBS_DEBUG_AVOIDAUTOFIELDS',64); // avoit auto field merging during the Show() method
define('OPENTBS_INFO',1);       // command to display the archive info
define('OPENTBS_RESET',2);      // command to reset the changes in the current archive
define('OPENTBS_ADDFILE',4);    // command to add a new file in the archive
define('OPENTBS_DELETEFILE',8); // command to delete a file in the archive
define('OPENTBS_DEFAULT','');   // Charset
define('OPENTBS_ALREADY_XML',false);
define('OPENTBS_ALREADY_UTF8','already_utf8');

class clsOpenTBS extends clsTbsZip {

	function OnInstall() {
		$TBS =& $this->TBS;

		if (!isset($TBS->OtbsAutoLoad)) $TBS->OtbsAutoLoad = true; // TBS will load the subfile regarding to the extension of the archive
		if (!isset($TBS->OtbsConvBr))   $TBS->OtbsConvBr = false;  // string for NewLine conversion
		if (!isset($TBS->OtbsAutoUncompress)) $TBS->OtbsAutoUncompress = $this->Meth8Ok;
		$this->Version = '1.5.0'; // Version can be displayed using [onshow..tbs_info] since TBS 3.2.0
		$this->DebugLst = false; // deactivate the debug mode
		$this->ExtMode = '';
		return array('BeforeLoadTemplate','BeforeShow', 'OnCommand', 'OnOperation', 'OnCacheField');
	}

	function BeforeLoadTemplate(&$File,&$Charset) {

		$TBS =& $this->TBS;
		if ($TBS->_Mode!=0) return; // If we are in subtemplate mode, the we use the TBS default process

		// Decompose the file path. The syntaxe is 'Archive.ext#subfile', or 'Archive.ext', or '#subfile'
		$p = strpos($File, '#');
		if ($p===false) {
			$FilePath = $File;
			$SubFileLst = false;
		} else {
			$FilePath = substr($File,0,$p);
			$SubFileLst = substr($File,$p+1);
		}

		// Open the archive
		if ($FilePath!=='') {
			$this->Open($FilePath);  // Open the archive
			$this->PrepareExtInfo(); // Set extension information
			if ($TBS->OtbsAutoLoad && ($this->ArchExtInfo!==false) && ($SubFileLst===false)) {
				// auto load files from the archive
				$SubFileLst = $this->ArchExtInfo['load'];
				$TBS->OtbsConvBr = $this->ArchExtInfo['br'];
			}
			$TBS->OtbsCurrFile = false;
			$TBS->OtbsSubFileLst = $SubFileLst;
			$this->TbsSrcParking = array();
			$this->TbsCurrIdx = false;
		} elseif ($this->ArchFile==='') {
			$this->RaiseError('Cannot read file(s) "'.$SubFileLst.'" because no archive is opened.');
		}

		// Change the Charset if a new archive is opended, or if LoadTemplate is called explicitely for that
		if (($FilePath!=='') || ($File==='')) {
			if ($Charset===OPENTBS_ALREADY_XML) {
				$TBS->LoadTemplate('', false);                       // Define the function for string conversion
			} elseif ($Charset===OPENTBS_ALREADY_UTF8) {
				$TBS->LoadTemplate('', array(&$this,'ConvXmlOnly')); // Define the function for string conversion
			} else {
				$TBS->LoadTemplate('', array(&$this,'ConvXmlUtf8')); // Define the function for string conversion
			}
		}

		$TbsLoadTemplate = (($TBS->Render & OPENTBS_DEBUG_AVOIDAUTOFIELDS)!=OPENTBS_DEBUG_AVOIDAUTOFIELDS);		
		
		// Load the subfile(s)
		if (($SubFileLst!=='') && ($SubFileLst!==false)) {

			if (is_string($SubFileLst)) $SubFileLst = explode(';',$SubFileLst);

			$ModeSave = $TBS->_Mode;
			$TBS->_Mode++;    // deactivate TplVars[] reset and Charset reset.
			$TBS->Plugin(-4); // deactivate other plugins

			foreach ($SubFileLst as $SubFile) {

				$idx = $this->FileGetIdx($SubFile);
				if ($idx===false) {
					$this->RaiseError('The file "'.$SubFile.'" is not found in the archive "'.$this->ArchFile.'".');
				} else {
					// Save the current loaded subfile if any
					$this->TbsSrcPark();
					// load the subfile
					if (isset($this->TbsSrcParking[$idx])) {
						$TBS->Source = $this->TbsSrcParking[$idx]; // Load from parking
						$ok = true;
					} else {
						$TBS->Source = $this->FileRead($idx, $TBS->OtbsAutoUncompress); // Load from the archive
						$ok = ($this->LastReadComp<=0); // the contents is not compressed
						if ($ok && ($this->ArchExtInfo!==false)) {
							if (isset($this->ArchExtInfo['rpl_what'])) $TBS->Source = str_replace($this->ArchExtInfo['rpl_what'],$this->ArchExtInfo['rpl_with'],$TBS->Source); // auto replace strings in the loaded file
							if (($this->ArchExt==='docx') && isset($TBS->OtbsClearMsWord) && $TBS->OtbsClearMsWord) $this->MsWord_Clean($TBS->Source);
						}
					}

					// apply default TBS behaviors on the uncompressed content: other plug-ins + [onload] fields
					if ($ok & $TbsLoadTemplate) $TBS->LoadTemplate(null,'+');

					$TBS->OtbsCurrFile = $SubFile;
					$this->TbsCurrIdx = $idx;

				}

			}

			// Reactivate default configuration
			$TBS->_Mode = $ModeSave;
			$TBS->Plugin(-10); // reactivate other plugins

		}

		if ($FilePath!=='') $TBS->_LastFile = $FilePath;

		return false; // default LoadTemplate() process is not executed

	}

	function BeforeShow(&$Render, $File='') {

		$TBS =& $this->TBS;

		if ($TBS->_Mode!=0) return; // If we are in subtemplate mode, the we use the TBS default process

		$this->TbsSrcPark(); // Save the current loaded subfile if any

		$TBS->Plugin(-4); // deactivate other plugins

		$Debug = (($Render & OPENTBS_DEBUG_XML)==OPENTBS_DEBUG_XML);
		if ($Debug) $this->DebugLst = array();

		$TbsShow = (($Render & OPENTBS_DEBUG_AVOIDAUTOFIELDS)!=OPENTBS_DEBUG_AVOIDAUTOFIELDS);
		
		// Merges all modified subfiles
		$idx_lst = array_keys($this->TbsSrcParking);
		foreach ($idx_lst as $idx) {
			$TBS->Source = $this->TbsSrcParking[$idx];
			unset($this->TbsSrcParking[$idx]); // save memory space
			$this->TbsCurrIdx = $idx; // usefull for debug mode
			if ($TbsShow) $TBS->Show(TBS_NOTHING);
			if ($Debug) $this->DebugLst[$this->CdFileLst[$idx]['v_name']] = $TBS->Source;
			$this->FileReplace($idx, $TBS->Source, TBSZIP_STRING, $TBS->OtbsAutoUncompress);
		}
		$TBS->Plugin(-10); // reactivate other plugins
		$this->TbsCurrIdx = false;
		
		if (isset($this->OpenXmlRid))    $this->OpenXML_RidCommit($Debug);      // Commit special OpenXML features if any
		if (isset($this->OpenXmlCTypes)) $this->OpenXML_CTypesCommit($Debug);   // Commit special OpenXML features if any
		if (isset($this->OpenDocManif))  $this->OpenDoc_ManifestCommit($Debug); // Commit special OpenDocument features if any

		if ( ($TBS->ErrCount>0) && (!$TBS->NoErr) && (!$Debug)) {
			$TBS->meth_Misc_Alert('Show() Method', 'The output is cancelled by the OpenTBS plugin because at least one error has occured.');
			exit;
		}

		if ($Debug) {
			// Do the debug even if other options are used
			$this->TbsDebug(true);
		} elseif (($Render & TBS_OUTPUT)==TBS_OUTPUT) { // notice that TBS_OUTPUT = OPENTBS_DOWNLOAD
			// download
			$ContentType = (isset($this->ArchExtInfo['ctype'])) ? $this->ArchExtInfo['ctype'] : '';
			$this->Flush($Render, $File, $ContentType); // $Render is used because it can contain options OPENTBS_DOWNLOAD and OPENTBS_NOHEADER.
			$Render = $Render - TBS_OUTPUT; //prevent TBS from an extra output.
		} elseif(($Render & OPENTBS_FILE)==OPENTBS_FILE) {
			// to file
			$this->Flush(TBSZIP_FILE, $File);
		} elseif(($Render & OPENTBS_STRING)==OPENTBS_STRING) {
			// to string
			$this->Flush(TBSZIP_STRING);
			$TBS->Source = $this->OutputSrc;
			$this->OutputSrc = '';
		}

		if (($Render & TBS_EXIT)==TBS_EXIT) {
			$this->Close();
			exit;
		}

		return false; // cancel the default Show() process

	}

	function OnCacheField($BlockName,&$Loc,&$Txt,$PrmProc) {

		if (isset($Loc->PrmLst['ope'])) {
			$ope_lst = explode(',', $Loc->PrmLst['ope']); // in this event, ope is not exploded
			if (in_array('changepic', $ope_lst)) {
				$this->TbsPicChg($Txt, $Loc); // add parameter "att" which will be processed just after this event, when the field is cached
				$PrmLst['pic_change'] = true;
			}
			if (strpos(','.$Loc->PrmLst['ope'],',ods')!==false) {
				foreach($ope_lst as $ope) {
					if (substr($ope,0,3)==='ods') {
						$x = '';
						$this->OpenDoc_ChangeCellType($Txt, $Loc, $ope, false, $x);
						return; // only once
					}
				}
			}
		}

	}

	function OnOperation($FieldName,&$Value,&$PrmLst,&$Txt,$PosBeg,$PosEnd,&$Loc) {
    // in this event, ope is exploded, there is one function call for each ope command
		$ope = $PrmLst['ope'];
		if ($ope==='addpic') {
			$this->TbsPicAdd($Value, $PrmLst, $Loc, 'ope=addpic');
		} elseif ($ope==='changepic') {
			if (!isset($PrmLst['pic_change'])) {
				$this->TbsPicChg($Txt, $Loc);
				$PrmLst['pic_change'] = true;
			}
			$this->TbsPicAdd($Value, $PrmLst, $Loc, 'ope=changepic'); // add parameter "att" which will be processed just before the value is merged
		} elseif(substr($ope,0,3)==='ods') {
			if (!isset($Loc->PrmLst['odsok'])) $this->OpenDoc_ChangeCellType($Txt, $Loc, $ope, true, $Value);
		}
	}

	function OnCommand($Cmd, $Name=false, $Data=false, $DataType=TBSZIP_STRING, $Compress=true) {

		if ($Cmd==OPENTBS_INFO) {
			// Display debug information
			echo "<strong>OpenTBS plugin Information</strong><br>\r\n";
			return $this->Debug();
		} elseif ($Cmd==OPENTBS_RESET) {
			// Reset all mergings
			$this->ArchCancelModif();
			$this->TbsSrcParking = array();
			$TBS =& $this->TBS;
			$TBS->Source = '';
			$TBS->OtbsCurrFile = false;
			if (is_string($TBS->OtbsSubFileLst)) {
				$f = '#'.$TBS->OtbsSubFileLst;
				$h = '';
				$this->BeforeLoadTemplate($f,$h);
			}
			return true;
		} elseif ($Cmd==OPENTBS_ADDFILE) {
			// Add a new file or cancel a previous add
			return $this->FileAdd($Name, $Data, $DataType, $Compress);
		} elseif ($Cmd==OPENTBS_DELETEFILE) {
			// Delete an existing file in the archive
			$this->FileCancelModif($Name, false);    // cancel added files
			return $this->FileReplace($Name, false); // mark the file as to be deleted
		}

	}

	function TbsSrcPark() {
		// save the last opened subfile
		if ($this->TbsCurrIdx!==false) {
			$this->TbsSrcParking[$this->TbsCurrIdx] = $this->TBS->Source;
			$this->TBS->Source = '';
			$this->TbsCurrIdx = false;
		}
	}

	function TbsDebug($XmlFormat = true) {
		// display modified and added files

		if (!headers_sent()) header('Content-Type: text/plain; charset="UTF-8"');

		$nl = "\n";
		$sep = str_repeat('-',30);
		$bull = $nl.'  - ';

		echo "* OPENTBS DEBUG MODE: if the star, (*) on the left before the word OPENTBS, is not the very first character of this page, then your
merged Document will be corrupted when you use the OPENTBS_DOWNLOAD option. If there is a PHP error message, then you have to fix it.
If they are blank spaces, line beaks, or other unexpected characters, then you have to check your code in order to avoid them.";
		echo $nl;
		echo $nl.$sep.$nl.'INFORMATION'.$nl.$sep;
		echo $nl.'* OpenTBS version: '.$this->Version;
		echo $nl.'* TinyButStrong version: '.$this->TBS->Version;
		echo $nl.'* PHP version: '.PHP_VERSION;
		echo $nl.'* Opened archive: '.$this->ArchFile;

		// scann files for collecting information
		$mod_lst = ''; // id of modified files
		$del_lst = ''; // id of deleted  files
		$add_lst = ''; // id of added    files

		$idx_lst = array_keys($this->ReplInfo);
		foreach ($idx_lst as $idx) {
			$name = $this->CdFileLst[$idx]['v_name'];
			if ($this->ReplInfo[$idx]===false) {
				$del_lst .= $bull.$name;
			} else {
				$mod_lst .= $bull.$name;
			}
		}
		$idx_lst = array_keys($this->AddInfo);
		foreach ($idx_lst as $idx) {
			$name = $this->AddInfo[$idx]['name'];
			$add_lst .= $bull.$name;
		}

		if ($mod_lst==='')  $mod_lst = ' none';
		if ($del_lst==='')  $del_lst = ' none';
		if ($add_lst==='')  $add_lst = ' none';

		echo $nl.'* Deleted files in the archive:'.$del_lst;
		echo $nl.'* Added files in the archive:'.$add_lst;
		echo $nl.'* Modified files in the archive:'.$mod_lst;
		echo $nl;

		// display contents merged with OpenTBS
		foreach ($this->DebugLst as $name=>$src) {
			$x = trim($src);
			$info = '';
			$xml = ((strlen($x)>0) && $x[0]==='<');
			if ($XmlFormat && $xml) {
				$info = ' (XML reformated for debuging only)';
				$src = $this->XmlFormat($src);
			}
			echo $nl.$sep;
			echo $nl.'File merged with OpenTBS'.$info.': '.$name;
			echo $nl.$sep;
			echo $nl.$src;
		}

	}

	function ConvXmlOnly($Txt, $ConvBr) {
	// Used by TBS to convert special chars and new lines.
	  $x = htmlspecialchars($Txt);
	  if ($ConvBr) $this->ConvBr($x);
	  return $x;
	}

	function ConvXmlUtf8($Txt, $ConvBr) {
	// Used by TBS to convert special chars and new lines.
	  $x = htmlspecialchars(utf8_encode($Txt));
	  if ($ConvBr) $this->ConvBr($x);
	  return $x;
	}

	function ConvBr(&$x) {
  	$z = $this->TBS->OtbsConvBr;
  	if ($z===false) return;
    $x = nl2br($x); // Convert any type of line break
    $x = str_replace('<br />',$z ,$x);
	}

	function XmlFormat($Txt) {
	// format an XML source the be nicely aligned

		// delete line breaks
		$Txt = str_replace("\r",'',$Txt);
		$Txt = str_replace("\n",'',$Txt);

		// init values
		$p = 0;
		$lev = 0;
		$Res = '';

		$to = true;
		while ($to!==false) {
			$to = strpos($Txt,'<',$p);
			if ($to!==false) {
				$tc = strpos($Txt,'>',$to);
				if ($to===false) {
					$to = false; // anomaly
				} else {
					// get text between the tags
					$x = trim(substr($Txt, $p, $to-$p),' ');
					if ($x!=='') $Res .= "\n".str_repeat(' ',max($lev,0)).$x;
					// get the tag
					$x = substr($Txt, $to, $tc-$to+1);
					if ($Txt[$to+1]==='/') $lev--;
					$Res .= "\n".str_repeat(' ',max($lev,0)).$x;
					// change the level
					if (($Txt[$to+1]!=='?') && ($Txt[$to+1]!=='/') && ($Txt[$tc-1]!=='/')) $lev++;
					// next position
					$p = $tc + 1;
				}
			}
		}

		$Res = substr($Res, 1); // delete the first line break
		if ($p<strlen($Txt)) $Res .= trim(substr($Txt, $p), ' '); // complete the end

		return $Res;

	}

	function RaiseError($Msg, $NoErrMsg=false) {
		// Overwrite the parent RaiseError() method.
		$exit = (!$this->TBS->NoErr);
		if ($exit) $Msg .= ' The process is ending, unless you set NoErr property to true.';
		$this->TBS->meth_Misc_Alert('OpenTBS Plugin', $Msg, $NoErrMsg);
		if ($exit) {
			if ($this->DebugLst!==false) {
				if ($this->TbsCurrIdx!==false) $this->DebugLst[$this->CdFileLst[$this->TbsCurrIdx]['v_name']] = $this->TBS->Source;
				$this->TbsDebug(true);
			}
			exit;
		}
		return false;
	}

	function TbsPicChg($Txt, &$Loc) {

		$att = false;
		if (isset($this->ArchExtInfo['frm'])) {
			if ($this->ArchExtInfo['frm']==='odf') {
				$att = 'draw:image#xlink:href';
			} elseif ($this->ArchExtInfo['frm']==='openxml') {
				$att = $this->OpenXML_FirstPicAtt($Txt, $Loc->PosBeg, true);
				if ($att===false) return $this->RaiseError('Parameter ope=changepic used in the field ['.$Loc->FullName.'] has failed to found the picture.');
			}
		} else {
			return $this->RaiseError('Parameter ope=changepic used in the field ['.$Loc->FullName.'] is not supported with the current document type.');
		}

		if ($att!==false) {
			if (isset($Loc->PrmLst['att'])) {
				return $this->RaiseError('Parameter att is used with parameter ope=changepic in the field ['.$Loc->FullName.']. changepic will be ignored');
			} else {
				$Loc->PrmLst['att'] = $att;
			}
		}

		return true;

	}

	function TbsPicAdd(&$Value, &$PrmLst, $Loc, $Prm) {
	// add a picture inside the archive, use parameters 'from' and 'as'.
	// Arguments $Loc and $Prm are only used for error messages.

		$TBS = &$this->TBS;

		// set the path where files should be taken
		if (isset($PrmLst['from'])) {
			if (!isset($PrmLst['pic_prepared'])) $TBS->meth_Merge_AutoVar($PrmLst['from'],true); // merge automatic TBS fields in the path
			$FullPath = str_replace($TBS->_ChrVal,$Value,$PrmLst['from']); // merge [val] fields in the path
		} else {
			$FullPath = $Value;
		}

    if ( (!isset($PrmLst['pic_prepared'])) && isset($PrmLst['default']) ) $TBS->meth_Merge_AutoVar($PrmLst['default'],true); // merge automatic TBS fields in the path

		$ok = true; // true if the picture file is actually inserted and ready to be changed

		// check if the picture exists, and eventually use the default picture
    if (!file_exists($FullPath)) {
    	if (isset($PrmLst['default'])) {
    		$x = $PrmLst['default'];
    		if ($x==='current') {
    			$ok = false;
    		} elseif (file_exists($x)) {
    			$FullPath = $x;
    		} else {
    			$ok = $this->RaiseError('The default picture "'.$x.'" defined by parameter "default" of the field ['.$Loc->FullName.'] is not found.');
    		}
    	} else {
   			$ok = $this->RaiseError('The picture "'.$FullPath.'" that is supposed to be added because of parameter "'.$Prm.'" of the field ['.$Loc->FullName.'] is not found. You can use parameter default=current to cancel this message');
    	}
    }

		// set the name of the new files
		if (isset($PrmLst['as'])) {
			if (!isset($PrmLst['pic_prepared'])) $TBS->meth_Merge_AutoVar($PrmLst['as'],true); // merge automatic TBS fields in the path
			$InternalPath = str_replace($TBS->_ChrVal,$Value,$PrmLst['as']); // merge [val] fields in the path
		} else {
			$InternalPath = basename($FullPath);
		}

		if ($ok) {

			// the value of the current TBS fields becomes the full path
			if (isset($this->ArchExtInfo['pic_path'])) $InternalPath = $this->ArchExtInfo['pic_path'].$InternalPath;

			// actually add the picture inside the archive
			if ($this->FileGetIdxAdd($InternalPath)===false) $this->FileAdd($InternalPath, $FullPath, TBSZIP_FILE, true);

			// preparation for others file in the archive
			$Rid = false;
			if ($this->ArchExtInfo!==false) {
				if ($this->ArchExtInfo['frm']==='odf') {
					// OpenOffice document
					$this->OpenDoc_ManifestChange($InternalPath,'');
				} elseif ($this->ArchExtInfo['frm']==='openxml') {
					// Microsoft Office document
					$this->OpenXML_CTypesPrepare($InternalPath, '');
					$Rid = $this->OpenXml_RidPrepare($TBS->OtbsCurrFile, basename($InternalPath));
				}
			}

			// change the value of the fields for the merging process
			if ($Rid===false) {
				$Value = $InternalPath;
			} else {
				$Value = $Rid; // the Rid is used instead of the file name for the merging
			}

		} else {

			$Value = '';

		}

		$PrmLst['pic_prepared'] = true; // mark the locator as Picture prepared

		return $ok;

	}

	function PrepareExtInfo() {
/* Extension Info must be an array with keys 'load', 'br', 'ctype' and 'pic_path'. Keys 'rpl_what' and 'rpl_with' are optional.
 load:     files in the archive to be automatically loaded by OpenTBS when the archive is loaded. Separate files with comma ';'.
 br:       string that replace break-lines in the values merged by TBS, set to false if no conversion.
 frm:      format of the file ('odf' or 'openxml'), for now it is used only to activate a special feature for openxml files
 ctype:    (optional) the Content-Type header name that should be use for HTTP download. Omit or set to '' if not specified.
 pic_path: (optional) the folder nale in the archive where to place pictures
 rpl_what: (optional) string to replace automatically in the files when they are loaded. Can be a string or an array.
 rpl_with: (optional) to be used with 'rpl_what',  Can be a string or an array.

User can define his own Extension Information, they are taken in acount if saved int the global variable $_OPENTBS_AutoExt.
*/

		$Ext = basename($this->ArchFile);
		$p = strrpos($Ext, '.');
		$Ext = ($p===false) ? ''  : strtolower(substr($Ext,$p+1));

		$i = false;
		if (isset($GLOBAL['_OPENTBS_AutoExt'][$Ext])) {
			$i = $GLOBAL['_OPENTBS_AutoExt'][$Ext];
		} elseif (strpos(',odt,ods,odg,odf,odp,odm,ott,ots,otg,otp,', ','.$Ext.',')!==false) {
			// OpenOffice & LibreOffice documents
			$i = array('load'=>'content.xml', 'br'=>'<text:line-break/>', 'frm'=>'odf', 'ctype'=>'application/vnd.oasis.opendocument.', 'pic_path'=>'Pictures/', 'rpl_what'=>'&apos;', 'rpl_with'=>'\'');
			if ($this->FileExists('styles.xml')) $i['load'] = 'styles.xml;'.$i['load']; // styles.xml may contain header/footer contents
			if ($Ext==='odf') $i['br'] = false;
			$ctype = array('t'=>'text', 's'=>'spreadsheet', 'g'=>'graphics', 'f'=>'formula', 'p'=>'presentation', 'm'=>'text-master');
			$i['ctype'] .= $ctype[($Ext[2])];
			$i['pic_ext'] = array('png'=>'png', 'bmp'=>'bmp', 'gif'=>'gif', 'jpg'=>'jpeg', 'jpeg'=>'jpeg', 'jpe'=>'jpeg', 'jfif'=>'jpeg', 'tif'=>'tiff', 'tiff'=>'tiff');
		} elseif (strpos(',docx,xlsx,pptx,', ','.$Ext.',')!==false) {
			// Microsoft Office documents
			if (!isset($this->TBS->OtbsClearMsWord)) $this->TBS->OtbsClearMsWord = true;
			$this->OpenXML_MapInit();
			$x = array(chr(226).chr(128).chr(152) , chr(226).chr(128).chr(153));
			$ctype = 'application/vnd.openxmlformats-officedocument.';
			if ($Ext==='docx') {
				$i = array('load'=>'word/document.xml', 'br'=>'<w:br/>', 'frm'=>'openxml', 'ctype'=>$ctype.'wordprocessingml.document', 'pic_path'=>'word/media/','rpl_what'=>$x,'rpl_with'=>'\'');
				$i['load'] = $this->OpenXML_MapGetVal(array('wordprocessingml.header+xml', 'wordprocessingml.footer+xml', 'wordprocessingml.document.main+xml'), $i['load'], ';', true); // footnotes and endnotes omitted for perf
			} elseif ($Ext==='xlsx') {
				$i = array('load'=>'xl/worksheets/sheet1.xml;xl/sharedStrings.xml', 'br'=>false, 'frm'=>'openxml', 'ctype'=>$ctype.'spreadsheetml.sheet', 'pic_path'=>'xl/media/');
				$i['load'] = $this->OpenXML_MapGetVal(array('spreadsheetml.worksheet+xml', 'spreadsheetml.sharedStrings+xml'), $i['load'], ';', true);
			} elseif($Ext==='pptx') {
				$i = array('load'=>'ppt/slides/slide1.xml', 'br'=>false, 'frm'=>'openxml', 'ctype'=>$ctype.'presentationml.presentation', 'pic_path'=>'ppt/media/' ,'rpl_what'=>$x,'rpl_with'=>'\'');
				$i['load'] = $this->OpenXML_MapGetVal(array('presentationml.notesSlide+xml'), $i['load'], ';', true); // masternotes and slidenotes omitted for perf 
			}
			$i['pic_ext'] = array('png'=>'png', 'bmp'=>'bmp', 'gif'=>'gif', 'jpg'=>'jpeg', 'jpeg'=>'jpeg', 'jpe'=>'jpeg', 'tif'=>'tiff', 'tiff'=>'tiff', 'ico'=>'x-icon', 'svg'=>'svg+xml');
		}

		$this->ArchExt = $Ext;
		$this->ArchExtInfo = $i;

	}

	function OpenXML_RidPrepare($DocPath, $ImageName) {
/* Return the RelationId if the image if it's already referenced in the Relation file in the archive.
Otherwise, OpenTBS prepares info to add this information at the end of the merging.
$ImageName must be the name of the image, wihtout path. This is because OpenXML needs links to be relatif to the active document. In our case, image files are always stored into subfolder 'media'.
*/

		if (!isset($this->OpenXmlRid[$DocPath])) {
			$o = (object) null;
			$o->RidLst = array();
			$o->RidNew = array();
			$DocName = basename($DocPath);
			$o->FicPath = str_replace($DocName,'_rels/'.$DocName.'.rels',$DocPath);
			$o->FicType = false; // false = to check, 0 = exist in the archive, 1 = to add in the archive
			$o->FicIdx = false; // in case of FicType=0
			$this->OpenXmlRid[$DocPath] = &$o;
		} else {
			$o = &$this->OpenXmlRid[$DocPath];
		}

		if ($o->FicType===false) {
			$FicIdx = $this->FileGetIdx($o->FicPath);
			if ($FicIdx===false) {
				$o->FicType = 1;
				$o->FicTxt = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"></Relationships>';
			} else {
				$o->FicIdx = $FicIdx;
				$o->FicType = 0;
				$Txt = $this->FileRead($FicIdx, true);
				$o->FicTxt = $Txt;
				// read existing Rid in the file
				$zImg = ' Target="media/';
				$zId  = ' Id="';
				$p = -1;
				while (($p = strpos($Txt, $zImg, $p+1))!==false) {
					// Get the image name
					$p1 = $p + strlen($zImg);
					$p2 = strpos($Txt, '"', $p1);
					if ($p2===false) return $this->RaiseError("(OpenXML) end of attribute Target not found in position ".$p1." of subfile ".$o->FicPath);
					$Img = substr($Txt, $p1, $p2 -$p1 -1);
					// Get the Id
					$p1 = strrpos(substr($Txt,0,$p), '<');
					if ($p1===false) return $this->RaiseError("(OpenXML) begining of tag not found in position ".$p." of subfile ".$o->FicPath);
					$p1 = strpos($Txt, $zId, $p1);
					if ($p1!==false) {
						$p1 = $p1 + strlen($zId);
						$p2 = strpos($Txt, '"', $p1);
						if ($p2===false) return $this->RaiseError("(OpenXML) end of attribute Id not found in position ".$p1." of subfile ".$o->FicPath);
						$Rid = substr($Txt, $p1, $p2 -$p1 -1);
						$o->RidLst[$Img] = $Rid;
					}
				}
			}
		}

		if (isset($o->RidLst[$ImageName])) return $o->RidLst[$ImageName];

		// Add the Rid in the information
		$NewRid = 'opentbs'.(1+count($o->RidNew));
		$o->RidLst[$ImageName] = $NewRid;
		$o->RidNew[$ImageName] = $NewRid;

		return $NewRid;

	}

	function OpenXML_RidCommit ($Debug) {

		foreach ($this->OpenXmlRid as $o) {
			// search position for insertion
			$p = strpos($o->FicTxt, '</Relationships>');
			if ($p===false) return $this->RaiseError("(OpenXML) closing tag </Relationships> not found in subfile ".$o->FicPath);
			// build the string to instert
			$x = '';
			foreach ($o->RidNew as $img=>$rid) {
				$x .= '<Relationship Id="'.$rid.'" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/image" Target="media/'.$img.'"/>';
			}
			// insert
			$o->FicTxt = substr_replace($o->FicTxt, $x, $p, 0);
			if ($o->FicType==1) {
				$this->FileAdd($o->FicPath, $o->FicTxt);
			} else {
				$this->FileReplace($o->FicIdx, $o->FicTxt);
			}
			// debug mode
			if ($Debug) $this->DebugLst[$o->FicPath] = $o->FicTxt;
		}

	}

	function OpenXML_CTypesPrepare($FileOrExt, $ct='') {
/* this function prepare information for editing the '[Content_Types].xml' file.
It needs to be completed when a new picture file extension is added in the document.
*/

		$p = strrpos($FileOrExt, '.');
		$ext = ($p===false) ? $FileOrExt : substr($FileOrExt, $p+1);
		$ext = strtolower($ext);

		if (isset($this->OpenXmlCTypes[$ext]) && ($this->OpenXmlCTypes[$ext]!=='') ) return;

		if (($ct==='') && isset($this->ArchExtInfo['pic_ext'][$ext])) $ct = 'image/'.$this->ArchExtInfo['pic_ext'][$ext];

		$this->OpenXmlCTypes[$ext] = $ct;

	}

	function OpenXML_CTypesCommit($Debug) {

		$file = '[Content_Types].xml';
		$idx = $this->FileGetIdx($file);
		if ($idx===false) {
			$Txt = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types"></Types>';
		} else {
			$Txt = $this->FileRead($idx, true);
		}

		$x = '';
		foreach ($this->OpenXmlCTypes as $ext=>$ct) {
			$p = strpos($Txt, ' Extension="'.$ext.'"');
			if ($p===false) {
				if ($ct==='') {
					$this->RaiseError("(OpenXML) '"+$ext+"' is not an picture's extension recognize by OpenTBS.");
				} else {
					$x .= '<Default Extension="'.$ext.'" ContentType="'.$ct.'"/>';
				}
			}
		}


		if ($x!=='') {

			$p = strpos($Txt, '</Types>'); // search position for insertion
			if ($p===false) return $this->RaiseError("(OpenXML) closing tag </Types> not found in subfile ".$file);
			$Txt = substr_replace($Txt, $x, $p ,0);

			// debug mode
			if ($Debug) $this->DebugLst[$file] = $Txt;

			if ($idx===false) {
				$this->FileAdd($file, $Txt);
			} else {
				$this->FileReplace($idx, $Txt);
			}

		}

	}

	function OpenXML_FirstPicAtt($Txt, $Pos, $Backward) {
	// search the first image element in the given direction. Two types of igame can be found. Return the value required for "att" parameter.
		$TypeVml = '<v:imagedata ';
		$TypeDml = '<a:blip ';

		if ($Backward) {
			// search the last image position this code is compatible with PHP 4
			$p = -1;
			$pMax = -1;
			$t_curr = $TypeVml;
			$t = '';
			do {
				$p = strpos($Txt, $t_curr, $p+1);
				if ( ($p===false) || ($p>=$Pos) ) {
					if ($t_curr===$TypeVml) {
						// we take a new search for the next type of image
						$t_curr = $TypeDml;
						$p = -1;
					} else {
						$p = false;
					}
				} elseif ($p>$pMax) {
					$pMax = $p;
					$t = $t_curr;
				}
			} while ($p!==false);
		} else {
			$p1 = strpos($Txt, $TypeVml, $Pos);
			$p2 = strpos($Txt, $TypeDml, $Pos);
			if (($p1===false) && ($p2===false)) {
				$t = '';
			} elseif ($p1===false) {
				$t = $TypeDml;
			} elseif ($p2===false) {
				$t = $TypeVml;
			} else {
				$t = ($p1<$p2) ? $TypeVml : $TypeDml;
			}
		}

		if ($t===$TypeVml) {
			return 'v:imagedata#r:id';
		} elseif ($t===$TypeDml) {
			return 'a:blip#r:embed';
		} else {
			return false;
		}

	}

	function OpenXML_MapInit() {
	// read the Content_Type XML file and save a sumup in the OpenXmlMap property.

		$this->OpenXmlMap = array();
		$Map =& $this->OpenXmlMap;

		$file = '[Content_Types].xml';
		$idx = $this->FileGetIdx($file);
		if ($idx===false) return;

		$Txt = $this->FileRead($idx, true);

		$type = ' ContentType="application/vnd.openxmlformats-officedocument.';
		$type_l = strlen($type);
		$name = ' PartName="';
		$name_l = strlen($name);

		$p = -1;
		while ( ($p=strpos($Txt, '<', $p+1))!==false) {
			$pe = strpos($Txt, '>', $p);
			if ($pe===false) return; // syntax error in the XML
			$x = substr($Txt, $p+1, $pe-$p-1);
			$pi = strpos($x, $type);
			if ($pi!==false) {
				$pi = $pi + $type_l;
				$pc = strpos($x, '"', $pi);
				if ($pc===false) return; // syntax error in the XML
				$ShortType = substr($x, $pi, $pc-$pi); // content type's short value
				$pi = strpos($x, $name);
				if ($pi!==false) {
					$pi = $pi + $name_l;
					$pc = strpos($x, '"', $pi);
					if ($pc===false) return; // syntax error in the XML
					$Name = substr($x, $pi, $pc-$pi); // name
					if (!isset($Map[$ShortType])) $Map[$ShortType] = array();
					$Map[$ShortType][] = $Name;
				}
			}
			$p = $pe;
		}

	}

	function OpenXML_MapGetVal($ShortType, $Default, $Glue=false, $CheckFiles=false) {
	// Return all values for a given type (or array of types) in the map. If $CheckFiles=true, then fix pathes and return only files that do exist in the archive.

		if (is_string($ShortType)) $ShortType = array($ShortType);

		$res = array();
		foreach ($ShortType as $type) {
			if (isset($this->OpenXmlMap[$type])) {
				$val = $this->OpenXmlMap[$type];
				if ($CheckFiles) {
					foreach ($val as $file) {
						if ($file[0]=='/') $file = substr($file,1); // fix the file path
						if ($this->FileExists($file)) $res[] = $file;
					}
				} else {
					$res = array_merge($res, $val);
				}
			}
		}
		
		if (count($res)==0) {
			$res = $Default;
		} elseif ($Glue!==false) {
			$res = implode($Glue, $res);
		}

		return $res;
	}
	
	// Cleaning tags in MsWord

	function MsWord_Clean(&$Txt) {
		$Txt = str_replace('<w:lastRenderedPageBreak/>', '', $Txt); // faster
		$this->MsWord_CleanTag($Txt, array('<w:proofErr', '<w:noProof', '<w:lang', '<w:lastRenderedPageBreak'));
		$this->MsWord_CleanSystemBookmarks($Txt);
		$this->MsWord_CleanRsID($Txt);
		$this->MsWord_CleanDuplicatedLayout($Txt);
	}

	function MsWord_FoundTag($Txt, $Tag, $PosBeg) {
	// Found the next tag of the asked type. (Not specific to MsWord, works for any XML)
		$len = strlen($Tag);
		$p = $PosBeg;
		while ($p!==false) {
			$p = strpos($Txt, $Tag, $p);
			if ($p===false) return false;
			$x = substr($Txt, $p+$len, 1);
			if (($x===' ') || ($x==='/') || ($x==='>') ) {
				return $p;
			} else {
				$p = $p+$len;
			}
		}
		return false;
	}

	function MsWord_CleanTag(&$Txt, $TagLst) {
	// Delete all tags of the types listed in the list. (Not specific to MsWord, works for any XML)
		$nbr_del = 0;
		foreach ($TagLst as $tag) {
			$p = 0;
			while (($p=$this->MsWord_FoundTag($Txt, $tag, $p))!==false) {
				// get the end of the tag
				$pe = strpos($Txt, '>', $p);
				if ($pe===false) return false; // error in the XML formating
				// delete the tag
				$Txt = substr_replace($Txt, '', $p, $pe-$p+1);
			} 
		}
		return $nbr_del;
	}

	function MsWord_CleanSystemBookmarks(&$Txt) {
	// Delete GoBack hidden bookmarks that appear since Office 2010. Example: <w:bookmarkStart w:id="0" w:name="_GoBack"/><w:bookmarkEnd w:id="0"/>

		$x = ' w:name="_GoBack"/><w:bookmarkEnd ';
		$x_len = strlen($x);
		
		$b = '<w:bookmarkStart ';
		$b_len = strlen($b);
		
		$nbr_del = 0;
		
		$p = 0;
		while ( ($p=strpos($Txt, $x, $p))!==false ) {
			$pe = strpos($Txt, '>', $p + $x_len);
			if ($pe===false) return false;
			$pb = strrpos(substr($Txt,0,$p) , '<');
			if ($pb===false) return false;
			if (substr($Txt, $pb, $b_len)===$b) {
				$Txt = substr_replace($Txt, '', $pb, $pe - $pb + 1); 
				$p = $pb;
				$nbr_del++;
			} else {
				$p = $pe +1;
			}
		}

		return $nbr_del;
		
	}
	
	function MsWord_CleanRsID(&$Txt) {
	/* Delete XML attributes relative to log of user modifications. Returns the number of deleted attributes.
	In order to insert such information, MsWord do split TBS tags with XML elements.
	After such attributes are deleted, we can concatenate duplicated XML elements. */

		$rs_lst = array('w:rsidR', 'w:rsidRPr');

		$nbr_del = 0;
		foreach ($rs_lst as $rs) {

			$rs_att = ' '.$rs.'="';
			$rs_len = strlen($rs_att);

			$p = 0;
			while ($p!==false) {
				// search the attribute
				$ok = false;
				$p = strpos($Txt, $rs_att, $p);
				if ($p!==false) {
					// attribute found, now seach tag bounds
					$po = strpos($Txt, '<', $p);
					$pc = strpos($Txt, '>', $p);
					if ( ($pc!==false) && ($po!==false) && ($pc<$po) ) { // means that the attribute is actually inside a tag
						$p2 = strpos($Txt, '"', $p+$rs_len); // position of the delimiter that closes the attribute's value
						if ( ($p2!==false) && ($p2<$pc) ) {
							// delete the attribute
							$Txt = substr_replace($Txt, '', $p, $p2 -$p +1);
							$ok = true;
							$nbr_del++;
						}
					}
					if (!$ok) $p = $p + $rs_len;
				}
			}

		}

		// delete empty tags
		$Txt = str_replace('<w:rPr></w:rPr>', '', $Txt);
		$Txt = str_replace('<w:pPr></w:pPr>', '', $Txt);

		return $nbr_del;

	}

	function MsWord_CleanDuplicatedLayout(&$Txt) {
	// Return the number of deleted dublicates
	
		$wro = '<w:r';
		$wro_len = strlen($wro);

		$wrc = '</w:r';
		$wrc_len = strlen($wrc);

		$wto = '<w:t';
		$wto_len = strlen($wto);

		$wtc = '</w:t';
		$wtc_len = strlen($wtc);

		$nbr = 0;
		$wro_p = 0;
		while ( ($wro_p=$this->MsWord_FoundTag($Txt, $wro, $wro_p))!==false ) {
			$wto_p = $this->MsWord_FoundTag($Txt,$wto,$wro_p); if ($wto_p===false) return false; // error in the structure of the <w:r> element
			$first = true;
			do {
				$ok = false;
				$wtc_p = $this->MsWord_FoundTag($Txt,$wtc,$wto_p); if ($wtc_p===false) return false; // error in the structure of the <w:r> element
				$wrc_p = $this->MsWord_FoundTag($Txt,$wrc,$wro_p); if ($wrc_p===false) return false; // error in the structure of the <w:r> element
				if ( ($wto_p<$wrc_p) && ($wtc_p<$wrc_p) ) { // if the found <w:t> is actually included in the <w:r> element
					if ($first) {
						$superflous = '</w:t></w:r>'.substr($Txt, $wro_p, ($wto_p+$wto_len)-$wro_p); // should be like: '</w:t></w:r><w:r>....<w:t'
						$superflous_len = strlen($superflous);
						$first = false;
					}
					$x = substr($Txt, $wtc_p+$superflous_len,1);
					if ( (substr($Txt, $wtc_p, $superflous_len)===$superflous) && (($x===' ') || ($x==='>')) ) {
						// if the <w:r> layout is the same same the next <w:r>, then we join it
						$p_end = strpos($Txt, '>', $wtc_p+$superflous_len); //
						if ($p_end===false) return false; // error in the structure of the <w:t> tag
						$Txt = substr_replace($Txt, '', $wtc_p, $p_end-$wtc_p+1);
						$nbr++;
						$ok = true;
					}
				}
			} while ($ok);

			$wro_p = $wro_p + $wro_len;

		}

		return $nbr; // number of replacements

	}

	// OpenOffice documents

	function OpenDoc_ManifestChange($Path, $Type) {
	// Set $Type=false in order to mark the the manifest entry to be deleted.
	// Video and sound files are not to be registered in the manifest since the contents is not saved in the document.

		// Initialization
		if (!isset($this->OpenDocManif)) $this->OpenDocManif = array();

		// We try to found the type of image
		if (($Type==='') && (substr($Path,0,9)==='Pictures/')) {
			$ext = basename($Path);
			$p = strrpos($ext, '.');
			if ($p!==false) {
				$ext = strtolower(substr($ext,$p+1));
				if (isset($this->ArchExtInfo['pic_ext'][$ext])) $Type = 'image/'.$this->ArchExtInfo['pic_ext'][$ext];
			}
		}

		$this->OpenDocManif[$Path] = $Type;

	}

	function OpenDoc_ManifestCommit($Debug) {

		// Retrieve the content of the manifest
		$name = 'META-INF/manifest.xml';
		$idx = $this->FileGetIdx($name);
		if ($idx===false) return;

		if (isset($this->TbsSrcParking[$idx])) {
			$Txt = $this->TbsSrcParking[$idx];
		} else {
			$Txt = $this->FileRead($idx, true);
			if ($this->LastReadComp>0) return $this->RaiseError("(OpenDocumentFormat) unable to uncompress 'META-INF/manifest.xml'.");
		}

		// Perform all changes
		foreach ($this->OpenDocManif as $Path => $Type) {
			$x = 'manifest:full-path="'.$Path.'"';
			$p = strpos($Txt,$x);
			if ($Type===false) {
				// the entry should be deleted
				if ($p!==false) {
					$p1 = strrpos(substr($Txt,0,$p), '<');
					$p2 = strpos($Txt,'>',$p);
					if (($p1!==false) && ($p2!==false)) $Txt = substr($Txt,0,$p1).substr($Txt,$p2+1);
				}
			} else {
				// the entry should be added
				if ($p===false) {
					$p = strpos($Txt,'</manifest:manifest>');
					if ($p!==false) {
						$x = ' <manifest:file-entry manifest:media-type="'.$Type.'" '.$x.'/>'."\n";
						$Txt = substr_replace($Txt, $x, $p, 0);
					}
				}
			}
		}

		// Save changes
		$this->FileReplace($idx, $Txt);

		if ($Debug) $this->DebugLst[$name] = $Txt;

	}

	function OpenDoc_ChangeCellType(&$Txt, &$Loc, $Ope, $IsMerging, &$Value) {
	// change the type of a cell in an ODS file
	
		$Loc->PrmLst['odsok'] = true; // avoid the field to be processed twice

		if ($Ope==='odsStr') return true;

		static $OpeLst = array('odsNum'=>'float', 'odsPercent'=>'percentage', 'odsCurr'=>'currency', 'odsBool'=>'boolean', 'odsDate'=>'date', 'odsTime'=>'time');
		$AttStr = 'office:value-type="string"';
		$AttStr_len = strlen($AttStr);

		if (!isset($OpeLst[$Ope])) return false;
		
		$t0 = clsTinyButStrong370::f_Xml_FindTagStart($Txt, 'table:table-cell', true, $Loc->PosBeg, false, true);
		if ($t0===false) return false; // error in the XML structure

		$te = strpos($Txt, '>', $t0);
		if ( ($te===false) || ($te>$Loc->PosBeg) ) return false; // error in the XML structure

		$len = $te - $t0 + 1;
		$tag = substr($Txt, $t0, $len);

		$p = strpos($tag, $AttStr);
		if ($p===false) return false; // error: the cell was expected to have a string contents since it contains a TBS tag.

		// replace the current string with blanck chars
		$len = $Loc->PosEnd - $Loc->PosBeg + 1;
		$Txt = substr_replace($Txt, str_repeat(' ',$len), $Loc->PosBeg, $len);

		// prepare special formating for the value
		$type = $OpeLst[$Ope];
		$att_new = 'office:value-type="'.$type.'"';
		$newfrm = false;
		switch ($type) {
		case 'float':      $att_new .= ' office:value="[]"'; break;
		case 'percentage': $att_new .= ' office:value="[]"'; break;
		case 'currency':   $att_new .= ' office:value="[]"'; if (isset($Loc->PrmLst['currency'])) $att_new .= ' office:currency="'.$Loc->PrmLst['currency'].'"'; break;
		case 'boolean':    $att_new .= ' office:boolean-value="[]"'; break;
		case 'date':       $att_new .= ' office:date-value="[]"'; $newfrm = 'yyyy-mm-ddThh:nn:ss'; break;
		case 'time';       $att_new .= ' office:time-value="[]"'; $newfrm = '"PT"hh"H"nn"M"ss"S"'; break;
		}

		// replace the sring attribute with the new attribute
		//$diff = strlen($att_new) - $AttStr_len;
		$p_att = $t0 + $p;
		$p_fld = $p_att + strpos($att_new, '['); // new position of the fields in $Txt
		$Txt = substr_replace($Txt, $att_new, $p_att, $AttStr_len);
		
		// move the TBS field
		$Loc->PosBeg = $p_fld;
		$Loc->PosEnd = $p_fld +1;

		if ($IsMerging) {
			// the field is currently beeing merged
			if ($type==='boolean') {
				if ($Value) {
					$Value = 'true';
				} else {
					$Value = 'false';
				}
			} elseif ($newfrm!==false) {
				$prm = array('frm'=>$newfrm);
				$Value = $this->TBS->meth_Misc_Format($Value,$prm);
			}
			$Loc->ConvStr = false;
			$Loc->ConvProtect = false;
		} else {
			if ($newfrm!==false) $Loc->PrmLst['frm'] = $newfrm;
		}

	}
	
}

/*
TbsZip version 2.3 (2010-11-29)
Author  : Skrol29 (email: http://www.tinybutstrong.com/onlyyou.html)
Licence : LGPL
This class is independent from any other classes and has been originally created for the OpenTbs plug-in
for TinyButStrong Template Engine (TBS). OpenTbs makes TBS able to merge OpenOffice and Ms Office documents.
Visit http://www.tinybutstrong.com
*/

define('TBSZIP_DOWNLOAD',1);   // download (default)
define('TBSZIP_NOHEADER',4);   // option to use with DOWNLOAD: no header is sent
define('TBSZIP_FILE',8);       // output to file  , or add from file
define('TBSZIP_STRING',32);    // output to string, or add from string

class clsTbsZip {

	function __construct() {
		$this->Meth8Ok = extension_loaded('zlib'); // check if Zlib extension is available. This is need for compress and uncompress with method 8.
		$this->DisplayError = false;
		$this->ArchFile = '';
		$this->Error = false;
	}

	function clsTbsZip() {$this->__construct();} // for PHP 4 compatibility

	function CreateNew($ArchName='new.zip') {
	// Create a new virtual empty archive, the name will be the default name when the archive is flushed.
		$this->Close(); // note that $this->ArchHnd is set to false here
		$this->Error = false;
		$this->ArchFile = $ArchName;
		$this->ArchIsNew = true;
		$bin = 'PK'.chr(05).chr(06).str_repeat(chr(0), 18);
		$this->CdEndPos = strlen($bin) - 4;
		$this->CdInfo = array('disk_num_curr'=>0, 'disk_num_cd'=>0, 'file_nbr_curr'=>0, 'file_nbr_tot'=>0, 'l_cd'=>0, 'p_cd'=>0, 'l_comm'=>0, 'v_comm'=>'', 'bin'=>$bin);
		$this->CdPos = $this->CdInfo['p_cd'];
	}

	function Open($ArchFile) {
	// Open the zip archive
		$this->Close(); // close handle and init info
		$this->Error = false;
		$this->ArchFile = $ArchFile;
		$this->ArchIsNew = false;
		// open the file
		$this->ArchHnd = fopen($ArchFile, 'rb');
		$ok = !($this->ArchHnd===false);
		if ($ok) $ok = $this->CentralDirRead();
		return $ok;
	}

	function Close() {
		if (isset($this->ArchHnd) and ($this->ArchHnd!==false)) fclose($this->ArchHnd);
		$this->ArchFile = '';
		$this->ArchHnd = false;
		$this->CdInfo = array();
		$this->CdFileLst = array();
		$this->CdFileNbr = 0;
		$this->CdFileByName = array();
		$this->VisFileLst = array();
		$this->ArchCancelModif();
	}	

	function ArchCancelModif() {
		$this->LastReadComp = false; // compression of the last read file (1=compressed, 0=stored not compressed, -1= stored compressed but read uncompressed)
		$this->LastReadIdx = false;  // index of the last file read
		$this->ReplInfo = array();
		$this->ReplByPos = array();
		$this->AddInfo = array();
	}

	function FileAdd($Name, $Data, $DataType=TBSZIP_STRING, $Compress=true) {

		if ($Data===false) return $this->FileCancelModif($Name, false); // Cancel a previously added file

		// Save information for adding a new file into the archive
		$Diff = 30 + 46 + 2*strlen($Name); // size of the header + cd info
		$Ref = $this->_DataCreateNewRef($Data, $DataType, $Compress, $Diff, $Name);
		if ($Ref===false) return false;
		$Ref['name'] = $Name;
		$this->AddInfo[] = $Ref;
		return $Ref['res'];

	}

	function CentralDirRead() {
		$cd_info = 'PK'.chr(05).chr(06); // signature of the Central Directory
		$cd_pos = -22;
		$this->_MoveTo($cd_pos, SEEK_END);
		$b = $this->_ReadData(4);
		if ($b!==$cd_info) return $this->RaiseError('The footer of the Central Directory is not found.');

		$this->CdEndPos = ftell($this->ArchHnd) - 4;
		$this->CdInfo = $this->CentralDirRead_End($cd_info);
		$this->CdFileLst = array();
		$this->CdFileNbr = $this->CdInfo['file_nbr_curr'];
		$this->CdPos = $this->CdInfo['p_cd'];

		if ($this->CdFileNbr<=0) return $this->RaiseError('No file found in the Central Directory.');
		if ($this->CdPos<=0) return $this->RaiseError('No position found for the Central Directory listing.');

		$this->_MoveTo($this->CdPos);
		for ($i=0;$i<$this->CdFileNbr;$i++) {
			$x = $this->CentralDirRead_File($i);
			if ($x!==false) {
				$this->CdFileLst[$i] = $x;
				$this->CdFileByName[$x['v_name']] = $i;
			}
		}
		return true;
	}

	function CentralDirRead_End($cd_info) {
		$b = $cd_info.$this->_ReadData(18);
		$x = array();
		$x['disk_num_curr'] = $this->_GetDec($b,4,2); // number of this disk
		$x['disk_num_cd'] = $this->_GetDec($b,6,2);   // number of the disk with the start of the central directory
		$x['file_nbr_curr'] = $this->_GetDec($b,8,2); // total number of entries in the central directory on this disk
		$x['file_nbr_tot'] = $this->_GetDec($b,10,2);  // total number of entries in the central directory
		$x['l_cd'] = $this->_GetDec($b,12,4);          // size of the central directory
		$x['p_cd'] = $this->_GetDec($b,16,4);         // offset of start of central directory with respect to the starting disk number
		$x['l_comm'] = $this->_GetDec($b,20,2);       // .ZIP file comment length
		$x['v_comm'] = $this->_ReadData($x['l_comm']); // .ZIP file comment
		$x['bin'] = $b.$x['v_comm'];
		return $x;
	}

	function CentralDirRead_File($idx) {

		$b = $this->_ReadData(46);

		$x = $this->_GetHex($b,0,4);
		if ($x!=='h:02014b50') return $this->RaiseError('Signature of file information not found in the Central Directory in position '.(ftell($this->ArchHnd)-46).' for file #'.$idx.'.');

		$x = array();
		$x['vers_used'] = $this->_GetDec($b,4,2);
		$x['vers_necess'] = $this->_GetDec($b,6,2);
		$x['purp'] = $this->_GetBin($b,8,2);
		$x['meth'] = $this->_GetDec($b,10,2);
		$x['time'] = $this->_GetDec($b,12,2);
		$x['date'] = $this->_GetDec($b,14,2);
		$x['crc32'] = $this->_GetDec($b,16,4);
		$x['l_data_c'] = $this->_GetDec($b,20,4);
		$x['l_data_u'] = $this->_GetDec($b,24,4);
		$x['l_name'] = $this->_GetDec($b,28,2);
		$x['l_fields'] = $this->_GetDec($b,30,2);
		$x['l_comm'] = $this->_GetDec($b,32,2);
		$x['disk_num'] = $this->_GetDec($b,34,2);
		$x['int_file_att'] = $this->_GetDec($b,36,2);
		$x['ext_file_att'] = $this->_GetDec($b,38,4);
		$x['p_loc'] = $this->_GetDec($b,42,4);
		$x['v_name'] = $this->_ReadData($x['l_name']);
		$x['v_fields'] = $this->_ReadData($x['l_fields']);
		$x['v_comm'] = $this->_ReadData($x['l_comm']);

		$x['bin'] = $b.$x['v_name'].$x['v_fields'].$x['v_comm'];

		return $x;
	}

	function RaiseError($Msg) {
		if ($this->DisplayError) echo '<strong>'.get_class($this).' ERROR : '.$Msg.'</strong><br>'."\r\n";
		$this->Error = $Msg;
		return false;
	}

	function Debug($FileHeaders=false) {

		$this->DisplayError = true;
		
		echo "<br />\r\n";
		echo "------------------<br/>\r\n";
		echo "Central Directory:<br/>\r\n";
		echo "------------------<br/>\r\n";
		print_r($this->CdInfo);

		echo "<br />\r\n";
		echo "-----------------------------------<br/>\r\n";
		echo "File List in the Central Directory:<br/>\r\n";
		echo "-----------------------------------<br/>\r\n";
		print_r($this->CdFileLst);			
		
		if ($FileHeaders) {
			echo "<br/>\r\n";
			echo "------------------------------<br/>\r\n";
			echo "File List in the Data Section:<br/>\r\n";
			echo "------------------------------<br/>\r\n";
			$idx = 0;
			$pos = 0;
			$this->_MoveTo($pos);
			while ($ok = $this->_ReadFile($idx,false)) {
				$this->VisFileLst[$idx]['debug_pos'] = $pos;
				$pos = ftell($this->ArchHnd);
				$idx++;
			}
			print_r($this->VisFileLst);
		}
		
	}

	function FileExists($NameOrIdx) {
		return ($this->FileGetIdx($NameOrIdx)!==false);
	}
	
	function FileGetIdx($NameOrIdx) {
	// Check if a file name, or a file index exists in the Central Directory, and return its index
		if (is_string($NameOrIdx)) {
			if (isset($this->CdFileByName[$NameOrIdx])) {
				return $this->CdFileByName[$NameOrIdx];
			} else {
				return false;
			}
		} else {
			if (isset($this->CdFileLst[$NameOrIdx])) {
				return $NameOrIdx;
			} else {
				return false;
			}
		}
	}

	function FileGetIdxAdd($Name) {
	// Check if a file name exists in the list of file to add, and return its index
		if (!is_string($Name)) return false;
		$idx_lst = array_keys($this->AddInfo);
		foreach ($idx_lst as $idx) {
			if ($this->AddInfo[$idx]['name']===$Name) return $idx;
		}
		return false;
	}
	
	function FileRead($NameOrIdx, $Uncompress=true) {
		
		$this->LastReadComp = false; // means the file is not found
		$this->LastReadIdx - false;

		$idx = $this->FileGetIdx($NameOrIdx);
		if ($idx===false) return $this->RaiseError('File "'.$NameOrIdx.'" is not found in the Central Directory.');

		$pos = $this->CdFileLst[$idx]['p_loc'];
		$this->_MoveTo($pos);

		$this->LastReadIdx = $idx; // Can be usefull to get the idx

		$Data = $this->_ReadFile($idx, true);

		// Manage uncompression
		$Comp = 1; // means the contents stays compressed
		$meth = $this->CdFileLst[$idx]['meth'];
		if ($meth==8) {
			if ($Uncompress) {
				if ($this->Meth8Ok) {
					$Data = gzinflate($Data);
					$Comp = -1; // means uncompressed
				} else {
					$this->RaiseError('Unable to uncompress file "'.$NameOrIdx.'" because extension Zlib is not installed.');
				}
			}
		} elseif($meth==0) {
			$Comp = 0; // means stored without compression
		} else {
			if ($Uncompress) $this->RaiseError('Unable to uncompress file "'.$NameOrIdx.'" because it is compressed with method '.$meth.'.');
		}
		$this->LastReadComp = $Comp;

		return $Data;

	}

	function _ReadFile($idx, $ReadData) {
	// read the file header (and maybe the data ) in the archive, assuming the cursor in at a new file position
	
		$b = $this->_ReadData(30);
		
		$x = $this->_GetHex($b,0,4);
		if ($x!=='h:04034b50') return $this->RaiseError('Signature of file information not found in the Data Section in position '.(ftell($this->ArchHnd)-30).' for file #'.$idx.'.');

		$x = array();
		$x['vers'] = $this->_GetDec($b,4,2);
		$x['purp'] = $this->_GetBin($b,6,2);
		$x['meth'] = $this->_GetDec($b,8,2);
		$x['time'] = $this->_GetDec($b,10,2);
		$x['date'] = $this->_GetDec($b,12,2);
		$x['crc32'] = $this->_GetDec($b,14,4);
		$x['l_data_c'] = $this->_GetDec($b,18,4);
		$x['l_data_u'] = $this->_GetDec($b,22,4);
		$x['l_name'] = $this->_GetDec($b,26,2);
		$x['l_fields'] = $this->_GetDec($b,28,2);
		$x['v_name'] = $this->_ReadData($x['l_name']);
		$x['v_fields'] = $this->_ReadData($x['l_fields']);

		$x['bin'] = $b.$x['v_name'].$x['v_fields'];

		// Read Data
		$len_cd = $this->CdFileLst[$idx]['l_data_c'];
		if ($x['l_data_c']==0) {
			// Sometimes, the size is not specified in the local information.
			$len = $len_cd;
		} else {
			$len = $x['l_data_c'];
			if ($len!=$len_cd) {
				//echo "TbsZip Warning: Local information for file #".$idx." says len=".$len.", while Central Directory says len=".$len_cd.".";
			}
		}

		if ($ReadData) {
			$Data = $this->_ReadData($len);
		} else {
			$this->_MoveTo($len, SEEK_CUR);
		}
		
		// Description information
		$desc_ok = ($x['purp'][2+3]=='1');
		if ($desc_ok) {
			$b = $this->_ReadData(16);
			$x['desc_bin'] = $b;
			$x['desc_sign'] = $this->_GetHex($b,0,4); // not specified in the documentation sign=h:08074b50
			$x['desc_crc32'] = $this->_GetDec($b,4,4);
			$x['desc_l_data_c'] = $this->_GetDec($b,8,4);
			$x['desc_l_data_u'] = $this->_GetDec($b,12,4);
		}

		// Save file info without the data
		$this->VisFileLst[$idx] = $x;

		// Return the info
		if ($ReadData) {
			return $Data;
		} else {
			return true;
		}
		
	}

	function FileReplace($NameOrIdx, $Data, $DataType=TBSZIP_STRING, $Compress=true) {
	// Store replacement information.

		$idx = $this->FileGetIdx($NameOrIdx);
		if ($idx===false) return $this->RaiseError('File "'.$NameOrIdx.'" is not found in the Central Directory.');

		$pos = $this->CdFileLst[$idx]['p_loc'];

		if ($Data===false) {
			// file to delete
			$this->ReplInfo[$idx] = false;
			$Result = true;
		} else {
			// file to replace
			$Diff = - $this->CdFileLst[$idx]['l_data_c'];
			$Ref = $this->_DataCreateNewRef($Data, $DataType, $Compress, $Diff, $NameOrIdx);
			if ($Ref===false) return false;
			$this->ReplInfo[$idx] = $Ref;
			$Result = $Ref['res'];
		}

		$this->ReplByPos[$pos] = $idx;

		return $Result;

	}

	function FileCancelModif($NameOrIdx, $ReplacedAndDeleted=true) {
	// cancel added, modified or deleted modifications on a file in the archive
	// return the number of cancels
	
		$nbr = 0;

		if ($ReplacedAndDeleted) {
			// replaced or deleted files
			$idx = $this->FileGetIdx($NameOrIdx);
			if ($idx!==false) {
				if (isset($this->ReplInfo[$idx])) {
					$pos = $this->CdFileLst[$idx]['p_loc'];
					unset($this->ReplByPos[$pos]);
					unset($this->ReplInfo[$idx]);
					$nbr++;
				}
			}
		}
		
		// added files		
		$idx = $this->FileGetIdxAdd($NameOrIdx);
		if ($idx!==false) {
			unset($this->InfoAdd[$idx]);
			$nbr++;
		}
		
		return $nbr;
		
	}

	function Flush($Render=TBSZIP_DOWNLOAD, $File='', $ContentType='') {

		$ArchPos = 0;
		$Delta = 0;
		$FicNewPos = array();
		$DelLst = array(); // idx of deleted files
		$DeltaCdLen = 0; // delta of the CD's size

		$now = time();
		$date  = $this->_MsDos_Date($now);
		$time  = $this->_MsDos_Time($now);

		$this->OutputOpen($Render, $File, $ContentType);
		
		// output modified zipped files and unmodified zipped files that are beetween them
		ksort($this->ReplByPos);
		foreach ($this->ReplByPos as $ReplPos => $ReplIdx) {
			// output data from the zip archive which is before the data to replace
			$this->OutputFromArch($ArchPos, $ReplPos);
			// get current file information
			if (!isset($this->VisFileLst[$ReplIdx])) $this->_ReadFile($ReplIdx, false);
			$FileInfo =& $this->VisFileLst[$ReplIdx];
			$b1 = $FileInfo['bin'];
			if (isset($FileInfo['desc_bin'])) {
				$b2 = $FileInfo['desc_bin'];
			} else {
				$b2 = '';
			}
			$info_old_len = strlen($b1) + $this->CdFileLst[$ReplIdx]['l_data_c'] + strlen($b2); // $FileInfo['l_data_c'] may have a 0 value in some archives
			// get replacement information
			$ReplInfo =& $this->ReplInfo[$ReplIdx];
			if ($ReplInfo===false) {
				// The file is to be deleted
				$Delta = $Delta - $info_old_len; // headers and footers are also deleted
				$DelLst[$ReplIdx] = true;
			} else {
				// prepare the header of the current file
				$this->_DataPrepare($ReplInfo); // get data from external file if necessary
				$this->_PutDec($b1, $time, 10, 2); // time
				$this->_PutDec($b1, $date, 12, 2); // date
				$this->_PutDec($b1, $ReplInfo['crc32'], 14, 4); // crc32
				$this->_PutDec($b1, $ReplInfo['len_c'], 18, 4); // l_data_c
				$this->_PutDec($b1, $ReplInfo['len_u'], 22, 4); // l_data_u
				if ($ReplInfo['meth']!==false) $this->_PutDec($b1, $ReplInfo['meth'], 8, 2); // meth
				// prepare the bottom description if the zipped file, if any
				if ($b2!=='') {
					$this->_PutDec($b2, $ReplInfo['crc32'], 4, 4);  // crc32
					$this->_PutDec($b2, $ReplInfo['len_c'], 8, 4);  // l_data_c
					$this->_PutDec($b2, $ReplInfo['len_u'], 12, 4); // l_data_u
				}
				// output data
				$this->OutputFromString($b1.$ReplInfo['data'].$b2);
				unset($ReplInfo['data']); // save PHP memory
				$Delta = $Delta + $ReplInfo['diff'] + $ReplInfo['len_c'];
			}
			// Update the delta of positions for zipped files which are physically after the currently replaced one
			for ($i=0;$i<$this->CdFileNbr;$i++) {
				if ($this->CdFileLst[$i]['p_loc']>$ReplPos) {
					$FicNewPos[$i] = $this->CdFileLst[$i]['p_loc'] + $Delta;
				}
			}
			// Update the current pos in the archive
			$ArchPos = $ReplPos + $info_old_len;
		}
		
		// Ouput all the zipped files that remain before the Central Directory listing
		if ($this->ArchHnd!==false) $this->OutputFromArch($ArchPos, $this->CdPos); // ArchHnd is false if CreateNew() has been called
		$ArchPos = $this->CdPos;

		// Output file to add
		$AddNbr = count($this->AddInfo);
		if ($AddNbr>0) {
			$AddDataLen = 0; // total len of added data (inlcuding file headers)
			$AddPos = $ArchPos + $Delta; // position of the start
			$AddLst = array_keys($this->AddInfo);
			foreach ($AddLst as $idx) {
				$n = $this->_DataOuputAddedFile($idx, $AddPos);
				$AddPos += $n;
				$AddDataLen += $n;
			}
		}
				
		// Modifiy file information in the Central Directory for replaced files
		$b2 = '';
		$old_cd_len = 0;
		for ($i=0;$i<$this->CdFileNbr;$i++) {
			$b1 = $this->CdFileLst[$i]['bin'];
			$old_cd_len += strlen($b1);
			if (!isset($DelLst[$i])) {
				if (isset($FicNewPos[$i])) $this->_PutDec($b1, $FicNewPos[$i], 42, 4);   // p_loc
				if (isset($this->ReplInfo[$i])) {
					$ReplInfo =& $this->ReplInfo[$i];
					$this->_PutDec($b1, $time, 12, 2); // time
					$this->_PutDec($b1, $date, 14, 2); // date
					$this->_PutDec($b1, $ReplInfo['crc32'], 16, 4); // crc32
					$this->_PutDec($b1, $ReplInfo['len_c'], 20, 4); // l_data_c
					$this->_PutDec($b1, $ReplInfo['len_u'], 24, 4); // l_data_u
					if ($ReplInfo['meth']!==false) $this->_PutDec($b1, $ReplInfo['meth'], 10, 2); // meth
				}
				$b2 .= $b1;
			}
		}
		$this->OutputFromString($b2);
		$ArchPos += $old_cd_len;
 		$DeltaCdLen =  $DeltaCdLen + strlen($b2) - $old_cd_len;
 		
		// Output until Central Directory footer
		if ($this->ArchHnd!==false) $this->OutputFromArch($ArchPos, $this->CdEndPos); // ArchHnd is false if CreateNew() has been called

		// Output file information of the Central Directory for added files
		if ($AddNbr>0) {
			$b2 = '';
			foreach ($AddLst as $idx) {
				$b2 .= $this->AddInfo[$idx]['bin'];
			}
			$this->OutputFromString($b2);
			$DeltaCdLen += strlen($b2);
		}
		
		// Output Central Directory footer
		$b2 = $this->CdInfo['bin'];
		$DelNbr = count($DelLst);
		if ( ($AddNbr>0) or ($DelNbr>0) ) {
			// total number of entries in the central directory on this disk
			$n = $this->_GetDec($b2, 8, 2);
			$this->_PutDec($b2, $n + $AddNbr - $DelNbr,  8, 2);
			// total number of entries in the central directory
			$n = $this->_GetDec($b2, 10, 2);
			$this->_PutDec($b2, $n + $AddNbr - $DelNbr, 10, 2);
			// size of the central directory
			$n = $this->_GetDec($b2, 12, 4);
			$this->_PutDec($b2, $n + $DeltaCdLen, 12, 4);
			$Delta = $Delta + $AddDataLen;
		}
		$this->_PutDec($b2, $this->CdPos+$Delta , 16, 4); // p_cd  (offset of start of central directory with respect to the starting disk number)
		$this->OutputFromString($b2);
		
	}

	// ----------------
	// output functions
	// ----------------

	function OutputOpen($Render, $File, $ContentType) {

		if (($Render & TBSZIP_FILE)==TBSZIP_FILE) {
			if (''.$File=='') $File = basename($this->ArchFile).'.zip';
			$this->OutputHandle = fopen($File, 'w');
			$this->OutputMode = TBSZIP_FILE;
		} elseif (($Render & TBSZIP_STRING)==TBSZIP_STRING) {
			$this->OutputMode = TBSZIP_STRING;
			$this->OutputSrc = '';
		} elseif (($Render & TBSZIP_DOWNLOAD)==TBSZIP_DOWNLOAD) {
			$this->OutputMode = TBSZIP_DOWNLOAD;
			// Output the file
			if (''.$File=='') $File = basename($this->ArchFile);
			if (($Render & TBSZIP_NOHEADER)==TBSZIP_NOHEADER) {
			} else {
				header ('Pragma: no-cache');
				if ($ContentType!='') header ('Content-Type: '.$ContentType);
				header('Content-Disposition: attachment; filename="'.$File.'"');
				header('Expires: 0');
				header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
				header('Cache-Control: public');
				header('Content-Description: File Transfer'); 
				header('Content-Transfer-Encoding: binary');
				$Len = $this->_EstimateNewArchSize();
				if ($Len!==false) header('Content-Length: '.$Len); 
			}
		}
	}

	function OutputFromArch($pos, $pos_stop) {
		$len = $pos_stop - $pos;
		if ($len<0) return;
		$this->_MoveTo($pos);
		$block = 1024;
		while ($len>0) {
			$l = min($len, $block);
			$x = $this->_ReadData($l);
			$this->OutputFromString($x);
			$len = $len - $l;
		}
		unset($x);
	}

	function OutputFromString($data) {
		if ($this->OutputMode===TBSZIP_DOWNLOAD) {
			echo $data; // donwload
		} elseif ($this->OutputMode===TBSZIP_STRING) {
			$this->OutputSrc .= $data; // to string
		} elseif (TBSZIP_FILE) {
			fwrite($this->OutputHandle, $data); // to file
		}
	}

	function OutputClose() {
		if ($this->OutputHandle!==false) fclose($this->OutputHandle);
	}

	// ----------------
	// Reading functions
	// ----------------

	function _MoveTo($pos, $relative = SEEK_SET) {
		fseek($this->ArchHnd, $pos, $relative);
	}

	function _ReadData($len) {
		if ($len>0) {
			$x = fread($this->ArchHnd, $len);
			return $x;
		} else {
			return '';
		}
	}

	// ----------------
	// Take info from binary data
	// ----------------

	function _GetDec($txt, $pos, $len) {
		$x = substr($txt, $pos, $len);
		$z = 0;
		for ($i=0;$i<$len;$i++) {
			$asc = ord($x[$i]);
			if ($asc>0) $z = $z + $asc*pow(256,$i);
		}
		return $z;
	}

	function _GetHex($txt, $pos, $len) {
		$x = substr($txt, $pos, $len);
		return 'h:'.bin2hex(strrev($x));
	}

	function _GetBin($txt, $pos, $len) {
		$x = substr($txt, $pos, $len);
		$z = '';
		for ($i=0;$i<$len;$i++) {
			$asc = ord($x[$i]);
			if (isset($x[$i])) {
				for ($j=0;$j<8;$j++) {
					$z .= ($asc & pow(2,$j)) ? '1' : '0';
				}
			} else {
				$z .= '00000000';
			}
		}
		return 'b:'.$z;
	}

	// ----------------
	// Put info into binary data
	// ----------------

	function _PutDec(&$txt, $val, $pos, $len) {
		$x = '';
		for ($i=0;$i<$len;$i++) {
			if ($val==0) {
				$z = 0;
			} else {
				$z = intval($val % 256);
				if (($val<0) && ($z!=0)) { // ($z!=0) is very important, example: val=-420085702
					// special opration for negative value. If the number id too big, PHP stores it into a signed integer. For example: crc32('coucou') => -256185401 instead of  4038781895. NegVal = BigVal - (MaxVal+1) = BigVal - 256^4
					$val = ($val - $z)/256 -1;
					$z = 256 + $z;
				} else {
					$val = ($val - $z)/256;
				}
			}
			$x .= chr($z);
		}
		$txt = substr_replace($txt, $x, $pos, $len);
	}
	
	function _MsDos_Date($Timestamp = false) {
		// convert a date-time timstamp into the MS-Dos format
		$d = ($Timestamp===false) ? getdate() : getdate($Timestamp);
		return (($d['year']-1980)*512) + ($d['mon']*32) + $d['mday'];
	}
	function _MsDos_Time($Timestamp = false) {
		// convert a date-time timstamp into the MS-Dos format
		$d = ($Timestamp===false) ? getdate() : getdate($Timestamp);
		return ($d['hours']*2048) + ($d['minutes']*32) + intval($d['seconds']/2); // seconds are rounded to an even number in order to save 1 bit
	}

	function _MsDos_Debug($date, $time) {
		// Display the formated date and time. Just for debug purpose.
		// date end time are encoded on 16 bits (2 bytes) : date = yyyyyyymmmmddddd , time = hhhhhnnnnnssssss
		$y = ($date & 65024)/512 + 1980;
		$m = ($date & 480)/32;
		$d = ($date & 31);
		$h = ($time & 63488)/2048;
		$i = ($time & 1984)/32;
		$s = ($time & 31) * 2; // seconds have been rounded to an even number in order to save 1 bit
		return $y.'-'.str_pad($m,2,'0',STR_PAD_LEFT).'-'.str_pad($d,2,'0',STR_PAD_LEFT).' '.str_pad($h,2,'0',STR_PAD_LEFT).':'.str_pad($i,2,'0',STR_PAD_LEFT).':'.str_pad($s,2,'0',STR_PAD_LEFT);
	}
	
	function _DataOuputAddedFile($Idx, $PosLoc) {

		$Ref =& $this->AddInfo[$Idx];
		$this->_DataPrepare($Ref); // get data from external file if necessary

		// Other info
		$now = time();
		$date  = $this->_MsDos_Date($now);
		$time  = $this->_MsDos_Time($now);
		$len_n = strlen($Ref['name']);
		$purp  = 2048 ; // purpose // +8 to indicates that there is an extended local header 

		// Header for file in the data section 
		$b = 'PK'.chr(03).chr(04).str_repeat(' ',26); // signature
		$this->_PutDec($b,20,4,2); //vers = 20
		$this->_PutDec($b,$purp,6,2); // purp
		$this->_PutDec($b,$Ref['meth'],8,2);  // meth
		$this->_PutDec($b,$time,10,2); // time
		$this->_PutDec($b,$date,12,2); // date
		$this->_PutDec($b,$Ref['crc32'],14,4); // crc32
		$this->_PutDec($b,$Ref['len_c'],18,4); // l_data_c
		$this->_PutDec($b,$Ref['len_u'],22,4); // l_data_u
		$this->_PutDec($b,$len_n,26,2); // l_name
		$this->_PutDec($b,0,28,2); // l_fields
		$b .= $Ref['name']; // name
		$b .= ''; // fields

		// Output the data
		$this->OutputFromString($b.$Ref['data']);
		$OutputLen = strlen($b) + $Ref['len_c']; // new position of the cursor
		unset($Ref['data']); // save PHP memory
		
		// Information for file in the Central Directory
		$b = 'PK'.chr(01).chr(02).str_repeat(' ',42); // signature
		$this->_PutDec($b,20,4,2);  // vers_used = 20
		$this->_PutDec($b,20,6,2);  // vers_necess = 20
		$this->_PutDec($b,$purp,8,2);  // purp
		$this->_PutDec($b,$Ref['meth'],10,2); // meth
		$this->_PutDec($b,$time,12,2); // time
		$this->_PutDec($b,$date,14,2); // date
		$this->_PutDec($b,$Ref['crc32'],16,4); // crc32
		$this->_PutDec($b,$Ref['len_c'],20,4); // l_data_c
		$this->_PutDec($b,$Ref['len_u'],24,4); // l_data_u
		$this->_PutDec($b,$len_n,28,2); // l_name
		$this->_PutDec($b,0,30,2); // l_fields
		$this->_PutDec($b,0,32,2); // l_comm
		$this->_PutDec($b,0,34,2); // disk_num
		$this->_PutDec($b,0,36,2); // int_file_att
		$this->_PutDec($b,0,38,4); // ext_file_att
		$this->_PutDec($b,$PosLoc,42,4); // p_loc
		$b .= $Ref['name']; // v_name
		$b .= ''; // v_fields
		$b .= ''; // v_comm

		$Ref['bin'] = $b;

		return $OutputLen;

	}

	function _DataCreateNewRef($Data, $DataType, $Compress, $Diff, $NameOrIdx) {

		if (is_array($Compress)) {
			$result = 2;
			$meth = $Compress['meth'];
			$len_u = $Compress['len_u'];
			$crc32 = $Compress['crc32'];
			$Compress = false;
		} elseif ($Compress and ($this->Meth8Ok)) {
			$result = 1;
			$meth = 8;
			$len_u = false; // means unknown
			$crc32 = false;
		} else {
			$result = ($Compress) ? -1 : 0;
			$meth = 0;
			$len_u = false;
			$crc32 = false;
			$Compress = false;
		}

		if ($DataType==TBSZIP_STRING) {
			$path = false;
			if ($Compress) {
				// we compress now in order to save PHP memory
				$len_u = strlen($Data);
				$crc32 = crc32($Data);
				$Data = gzdeflate($Data);
				$len_c = strlen($Data);
			} else {
				$len_c = strlen($Data);
				if ($len_u===false) {
					$len_u = $len_c;
					$crc32 = crc32($Data);
				}
			}
		} else {
			$path = $Data;
			$Data = false;
			if (file_exists($path)) {
				$fz = filesize($path);
				if ($len_u===false) $len_u = $fz;
				$len_c = ($Compress) ? false : $fz;
			} else {
				return $this->RaiseError("Cannot add the file '".$path."' because it is not found.");
			}
		}

		// at this step $Data and $crc32 can be false only in case of external file, and $len_c is false only in case of external file to compress
		return array('data'=>$Data, 'path'=>$path, 'meth'=>$meth, 'len_u'=>$len_u, 'len_c'=>$len_c, 'crc32'=>$crc32, 'diff'=>$Diff, 'res'=>$result);

	}

	function _DataPrepare(&$Ref) {
	// returns the real size of data
		if ($Ref['path']!==false) {
			$Ref['data'] = file_get_contents($Ref['path']);
			if ($Ref['crc32']===false) $Ref['crc32'] = crc32($Ref['data']);
			if ($Ref['len_c']===false) {
				// means the data must be compressed
				$Ref['data'] = gzdeflate($Ref['data']);
				$Ref['len_c'] = strlen($Ref['data']);
			}
		}
	}

	function _EstimateNewArchSize($Optim=true) {
	// Return the size of the new archive, or false if it cannot be calculated (because of external file that must be compressed before to be insered)

		if ($this->ArchIsNew) {
			$Len = strlen($this->CdInfo['bin']);
		} else {
			$Len = filesize($this->ArchFile);
		}

		// files to replace or delete
		foreach ($this->ReplByPos as $i) {
			$Ref =& $this->ReplInfo[$i];
			if ($Ref===false) {
				// file to delete
				$Info =& $this->CdFileLst[$i];
				if (!isset($this->VisFileLst[$i])) {
					if ($Optim) return false; // if $Optimization is set to true, then we d'ont rewind to read information
					$this->_MoveTo($Info['p_loc']);
					$this->_ReadFile($i, false);
				}
				$Vis =& $this->VisFileLst[$i];
				$Len += -strlen($Vis['bin']) -strlen($Info['bin']) - $Info['l_data_c'];
				if (isset($Vis['desc_bin'])) $Len += -strlen($Vis['desc_bin']);
			} elseif ($Ref['len_c']===false) {
				return false; // information not yet known
			} else {
				// file to replace
				$Len += $Ref['len_c'] + $Ref['diff'];
			}
		}
		
		// files to add
		$i_lst = array_keys($this->AddInfo);
		foreach ($i_lst as $i) {
			$Ref =& $this->AddInfo[$i];
			if ($Ref['len_c']===false) {
				return false; // information not yet known
			} else {
				$Len += $Ref['len_c'] + $Ref['diff'];
			}
		}
		
		return $Len;
		
	}
	
}
