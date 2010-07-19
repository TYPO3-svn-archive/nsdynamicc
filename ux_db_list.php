<?php

/**  
 * 
 * Dummy document - displays nothing but background color. *  
 ** @author    Nuwan Sameera <nuwan28@gmail.com> 
 * Revised for TYPO3 3.6 2/2003 by Kasper Skårhøj 
 * XHTML compliant content */
//require ('init.php');
//require ('template.php');// ***************************

// Script Classes 
// ***************************
class ux_SC_db_list extends  SC_db_list {
	
	function main()    
	{            
		global $BE_USER,$LANG,$BACK_PATH,$CLIENT;
		
			// Start document template object:
		$this->doc = t3lib_div::makeInstance('template');
		$this->doc->backPath = $BACK_PATH;
		$this->doc->setModuleTemplate('templates/db_list.html');
		//path to Nuwan extention
		$path_dc = t3lib_extMgm::extRelPath("nsdynamicc");		

		//add custom CSS files
		$this->doc->addStyleSheet('tx_nsdynamicc', t3lib_extMgm::extRelPath('nsdynamicc').'res/column_styles.css');

			//load the extJS javascript fremwork by Nuwan		
		$this->doc->getPageRenderer()->loadExtJS();		
		//load the sortable js files in extenstion
		$this->content .= $this->doc->loadJavascriptLib($path_dc.'scripts/sortable.js');	
		//Get the path to js script by Nuwan		
		$this->content .= $this->doc->loadJavascriptLib($path_dc.'scripts/dynamic_column.js');
		
		// Loading current page record and checking access:
		$this->pageinfo = t3lib_BEfunc::readPageAccess($this->id,$this->perms_clause);
		$access = is_array($this->pageinfo) ? 1 : 0;

			// Initialize the dblist object:
		$dblist = t3lib_div::makeInstance('localRecordList');				 
		
		$dblist->backPath = $BACK_PATH;
		$dblist->calcPerms = $BE_USER->calcPerms($this->pageinfo);
		$dblist->thumbs = $BE_USER->uc['thumbnailsByDefault'];
		$dblist->returnUrl=$this->returnUrl;
		$dblist->allFields = ($this->MOD_SETTINGS['bigControlPanel'] || $this->table) ? 1 : 0;
		$dblist->localizationView = $this->MOD_SETTINGS['localization'];
		$dblist->showClipboard = 1;
		$dblist->disableSingleTableView = $this->modTSconfig['properties']['disableSingleTableView'];
		$dblist->listOnlyInSingleTableMode = $this->modTSconfig['properties']['listOnlyInSingleTableView'];
		$dblist->hideTables = $this->modTSconfig['properties']['hideTables'];
		$dblist->tableTSconfigOverTCA = $this->modTSconfig['properties']['table.'];
		$dblist->clickTitleMode = $this->modTSconfig['properties']['clickTitleMode'];
		$dblist->alternateBgColors=$this->modTSconfig['properties']['alternateBgColors']?1:0;
		$dblist->allowedNewTables = t3lib_div::trimExplode(',', $this->modTSconfig['properties']['allowedNewTables'], 1);
		$dblist->deniedNewTables = t3lib_div::trimExplode(',', $this->modTSconfig['properties']['deniedNewTables'], 1);
		$dblist->newWizards=$this->modTSconfig['properties']['newWizards']?1:0;
		$dblist->pageRow = $this->pageinfo;
		$dblist->counter++;
		$dblist->MOD_MENU = array('bigControlPanel' => '', 'clipBoard' => '', 'localization' => '');
		$dblist->modTSconfig = $this->modTSconfig;

			// Clipboard is initialized:
		$dblist->clipObj = t3lib_div::makeInstance('t3lib_clipboard');		// Start clipboard
		$dblist->clipObj->initializeClipboard();	// Initialize - reads the clipboard content from the user session

			// Clipboard actions are handled:
		$CB = t3lib_div::_GET('CB');	// CB is the clipboard command array
		if ($this->cmd=='setCB') {
				// CBH is all the fields selected for the clipboard, CBC is the checkbox fields which were checked. By merging we get a full array of checked/unchecked elements
				// This is set to the 'el' array of the CB after being parsed so only the table in question is registered.
			$CB['el'] = $dblist->clipObj->cleanUpCBC(array_merge((array)t3lib_div::_POST('CBH'),(array)t3lib_div::_POST('CBC')),$this->cmd_table);
		}
		if (!$this->MOD_SETTINGS['clipBoard'])	$CB['setP']='normal';	// If the clipboard is NOT shown, set the pad to 'normal'.
		$dblist->clipObj->setCmd($CB);		// Execute commands.
		$dblist->clipObj->cleanCurrent();	// Clean up pad
		$dblist->clipObj->endClipboard();	// Save the clipboard content

			// This flag will prevent the clipboard panel in being shown.
			// It is set, if the clickmenu-layer is active AND the extended view is not enabled.
		$dblist->dontShowClipControlPanels = $CLIENT['FORMSTYLE'] && !$this->MOD_SETTINGS['bigControlPanel'] && $dblist->clipObj->current=='normal' && !$BE_USER->uc['disableCMlayers'] && !$this->modTSconfig['properties']['showClipControlPanelsDespiteOfCMlayers'];



			// If there is access to the page, then render the list contents and set up the document template object:
		if ($access)	{
				// Deleting records...:
				// Has not to do with the clipboard but is simply the delete action. The clipboard object is used to clean up the submitted entries to only the selected table.
			if ($this->cmd=='delete')	{
				$items = $dblist->clipObj->cleanUpCBC(t3lib_div::_POST('CBC'),$this->cmd_table,1);
				if (count($items))	{
					$cmd=array();
					foreach ($items as $iK => $value) {
						$iKParts = explode('|',$iK);
						$cmd[$iKParts[0]][$iKParts[1]]['delete']=1;
					}
					$tce = t3lib_div::makeInstance('t3lib_TCEmain');
					$tce->stripslashes_values=0;
					$tce->start(array(),$cmd);
					$tce->process_cmdmap();

					if (isset($cmd['pages']))	{
						t3lib_BEfunc::setUpdateSignal('updatePageTree');
					}

					$tce->printLogErrorMessages(t3lib_div::getIndpEnv('REQUEST_URI'));
				}
			}

				// Initialize the listing object, dblist, for rendering the list:
			$this->pointer = t3lib_div::intInRange($this->pointer,0,100000);
			$dblist->start($this->id,$this->table,$this->pointer,$this->search_field,$this->search_levels,$this->showLimit);
			$dblist->setDispFields();

				// Render versioning selector:
			if (t3lib_extMgm::isLoaded('version')) {
				$dblist->HTMLcode .= $this->doc->getVersionSelector($this->id);
			}

				// Render the list of tables:
			$dblist->generateList();

				// Write the bottom of the page:
			$dblist->writeBottom();

				// Add JavaScript functions to the page:
			$this->doc->JScode = $this->doc->wrapScriptTags('
				function jumpToUrl(URL)	{	//
					window.location.href = URL;
					return false;
				}
				function jumpExt(URL,anchor)	{	//
					var anc = anchor?anchor:"";
					window.location.href = URL+(T3_THIS_LOCATION?"&returnUrl="+T3_THIS_LOCATION:"")+anc;
					return false;
				}
				function jumpSelf(URL)	{	//
					window.location.href = URL+(T3_RETURN_URL?"&returnUrl="+T3_RETURN_URL:"");
					return false;
				}

				function setHighlight(id)	{	//
					top.fsMod.recentIds["web"]=id;
					top.fsMod.navFrameHighlightedID["web"]="pages"+id+"_"+top.fsMod.currentBank;	// For highlighting

					if (top.content && top.content.nav_frame && top.content.nav_frame.refresh_nav)	{
						top.content.nav_frame.refresh_nav();
					}
				}
				'.$this->doc->redirectUrls($dblist->listURL()).'
				'.$dblist->CBfunctions().'
				function editRecords(table,idList,addParams,CBflag)	{	//
					window.location.href="'.$BACK_PATH.'alt_doc.php?returnUrl='.rawurlencode(t3lib_div::getIndpEnv('REQUEST_URI')).
						'&edit["+table+"]["+idList+"]=edit"+addParams;
				}
				function editList(table,idList)	{	//
					var list="";

						// Checking how many is checked, how many is not
					var pointer=0;
					var pos = idList.indexOf(",");
					while (pos!=-1)	{
						if (cbValue(table+"|"+idList.substr(pointer,pos-pointer))) {
							list+=idList.substr(pointer,pos-pointer)+",";
						}
						pointer=pos+1;
						pos = idList.indexOf(",",pointer);
					}
					if (cbValue(table+"|"+idList.substr(pointer))) {
						list+=idList.substr(pointer)+",";
					}

					return list ? list : idList;
				}

				if (top.fsMod) top.fsMod.recentIds["web"] = '.intval($this->id).';						
			');

				// Setting up the context sensitive menu:
			$this->doc->getContextMenuCode();
		} // access

			// Begin to compile the whole page, starting out with page header:
		$this->body='';
		$this->body.= '<form action="'.htmlspecialchars($dblist->listURL()).'" method="post" name="dblistForm" class="sameera">';
		$this->body.= $dblist->HTMLcode;
		$this->body.= '<input type="hidden" name="cmd_table" /><input type="hidden" name="cmd" /></form>';

			// If a listing was produced, create the page footer with search form etc:
		if ($dblist->HTMLcode)	{
				// Making field select box (when extended view for a single table is enabled):
			if ($dblist->table)	{										
				$this->body.=$dblist->fieldSelectBox($dblist->table);				
			}

				// Adding checkbox options for extended listing and clipboard display:
			$this->body.='
					<!--
						Listing options for clipboard and thumbnails
					-->
					<div id="typo3-listOptions">
						<form action="" method="post">';

			$this->body.=t3lib_BEfunc::getFuncCheck($this->id,'SET[bigControlPanel]',$this->MOD_SETTINGS['bigControlPanel'],'db_list.php',($this->table?'&table='.$this->table:''),'id="checkLargeControl"').' <label for="checkLargeControl">'.$LANG->getLL('largeControl',1).'</label><br />';
			if ($dblist->showClipboard)	{
				$this->body.=t3lib_BEfunc::getFuncCheck($this->id,'SET[clipBoard]',$this->MOD_SETTINGS['clipBoard'],'db_list.php',($this->table?'&table='.$this->table:''),'id="checkShowClipBoard"').' <label for="checkShowClipBoard">'.$LANG->getLL('showClipBoard',1).'</label><br />';
			}
			$this->body.=t3lib_BEfunc::getFuncCheck($this->id,'SET[localization]',$this->MOD_SETTINGS['localization'],'db_list.php',($this->table?'&table='.$this->table:''),'id="checkLocalization"').' <label for="checkLocalization">'.$LANG->getLL('localization',1).'</label><br />';
			$this->body.='
						</form>
					</div>';
			$this->body.= t3lib_BEfunc::cshItem('xMOD_csh_corebe', 'list_options', $GLOBALS['BACK_PATH']);

				// Printing clipboard if enabled:
			if ($this->MOD_SETTINGS['clipBoard'] && $dblist->showClipboard)	{
				$this->body.= $dblist->clipObj->printClipboard();
				$this->body.= t3lib_BEfunc::cshItem('xMOD_csh_corebe', 'list_clipboard', $GLOBALS['BACK_PATH']);
			}

				// Search box:
			$this->body.=$dblist->getSearchBox();

				// Display sys-notes, if any are found:
			$this->body.=$dblist->showSysNotesForPage();
		}

			// Setting up the buttons and markers for docheader
		$docHeaderButtons = $dblist->getButtons();
		$markers = array(
			'CSH' => $docHeaderButtons['csh'],
			'CONTENT' => $this->body
		);

		// Build the <body> for the module
		$this->content = $this->doc->startPage('DB list');
		$this->content.= $this->doc->moduleBody($this->pageinfo, $docHeaderButtons, $markers);
		$this->content.= $this->doc->endPage();
		$this->content = $this->doc->insertStylesAndJS($this->content);		
	}	
	
}

// Include extension?	
if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/nsdynamicc/ux_db_list.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/nsdynamicc/ux_db_list.php']);
}	

?>
