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
class ux_localRecordList extends  localRecordList {
	
	function getTable($table,$id,$rowlist)	{		
		
		global $TCA, $TYPO3_CONF_VARS;

		// Loading all TCA details for this table:
		t3lib_div::loadTCA($table);

    	//Init
		$addWhere = '';
		$titleCol = $TCA[$table]['ctrl']['label'];
		$thumbsCol = $TCA[$table]['ctrl']['thumbnail'];
		$l10nEnabled = $TCA[$table]['ctrl']['languageField'] && $TCA[$table]['ctrl']['transOrigPointerField'] && !$TCA[$table]['ctrl']['transOrigPointerTable'];
		$tableCollapsed = (!$this->tablesCollapsed[$table]) ? false : true;

		// prepare space icon
		$this->spaceIcon = t3lib_iconWorks::getSpriteIcon('empty-empty', array('style' => 'background-position: 0 10px;'));

			// Cleaning rowlist for duplicates and place the $titleCol as the first column always!
		$this->fieldArray=array();
			// title Column
		$this->fieldArray[] = $titleCol;	// Add title column

			// Control-Panel
		if (!t3lib_div::inList($rowlist,'_CONTROL_'))	{
			$this->fieldArray[] = '_CONTROL_';
			$this->fieldArray[] = '_AFTERCONTROL_';
		}
			// Clipboard
		if ($this->showClipboard)	{
			$this->fieldArray[] = '_CLIPBOARD_';
		}
			// Ref
		if (!$this->dontShowClipControlPanels)	{
			$this->fieldArray[]='_REF_';
			$this->fieldArray[]='_AFTERREF_';
		}
			// Path
		if ($this->searchLevels)	{
			$this->fieldArray[]='_PATH_';
		}
			// Localization
		if ($this->localizationView && $l10nEnabled)	{
			$this->fieldArray[] = '_LOCALIZATION_';
			$this->fieldArray[] = '_LOCALIZATION_b';
			$addWhere.=' AND (
				'.$TCA[$table]['ctrl']['languageField'].'<=0
				OR
				'.$TCA[$table]['ctrl']['transOrigPointerField'].' = 0
			)';
		}		
		//sameera added
		//$this->fieldArray[]='_DCOLUMN_';						
			// Cleaning up:
		$this->fieldArray=array_unique(array_merge($this->fieldArray,t3lib_div::trimExplode(',',$rowlist,1)));
		if ($this->noControlPanels)	{
			$tempArray = array_flip($this->fieldArray);
			unset($tempArray['_CONTROL_']);
			unset($tempArray['_CLIPBOARD_']);
			$this->fieldArray = array_keys($tempArray);
		}

			// Creating the list of fields to include in the SQL query:
		$selectFields = $this->fieldArray;
		
		$selectFields[] = 'uid';
		$selectFields[] = 'pid';
		if ($thumbsCol)	$selectFields[] = $thumbsCol;	// adding column for thumbnails
		if ($table=='pages')	{
			if (t3lib_extMgm::isLoaded('cms'))	{
				$selectFields[] = 'module';
				$selectFields[] = 'extendToSubpages';
				$selectFields[] = 'nav_hide';
			}
			$selectFields[] = 'doktype';
		}
		if (is_array($TCA[$table]['ctrl']['enablecolumns']))	{
			$selectFields = array_merge($selectFields,$TCA[$table]['ctrl']['enablecolumns']);
		}				
		
		if ($TCA[$table]['ctrl']['type'])	{
			$selectFields[] = $TCA[$table]['ctrl']['type'];
		}
		if ($TCA[$table]['ctrl']['typeicon_column'])	{
			$selectFields[] = $TCA[$table]['ctrl']['typeicon_column'];
		}
		if ($TCA[$table]['ctrl']['versioningWS'])	{
			$selectFields[] = 't3ver_id';
			$selectFields[] = 't3ver_state';
			$selectFields[] = 't3ver_wsid';
			$selectFields[] = 't3ver_swapmode';		// Filtered out when pages in makeFieldList()
		}
		if ($l10nEnabled)	{
			$selectFields[] = $TCA[$table]['ctrl']['languageField'];
			$selectFields[] = $TCA[$table]['ctrl']['transOrigPointerField'];
		}
		if ($TCA[$table]['ctrl']['label_alt'])	{
			$selectFields = array_merge($selectFields,t3lib_div::trimExplode(',',$TCA[$table]['ctrl']['label_alt'],1));
		}		
		$selectFields = array_unique($selectFields);		// Unique list!
		$selectFields = array_intersect($selectFields,$this->makeFieldList($table,1));		// Making sure that the fields in the field-list ARE in the field-list from TCA!
		$selFieldList = implode(',',$selectFields);		// implode it into a list of fields for the SQL-statement.
		$this->selFieldList = $selFieldList;
		
		/**
		 * @hook			DB-List getTable
		 * @date			2007-11-16
		 * @request		Malte Jansen  <mail@maltejansen.de>
		 */
		if(is_array($TYPO3_CONF_VARS['SC_OPTIONS']['typo3/class.db_list_extra.inc']['getTable'])) {
			foreach($TYPO3_CONF_VARS['SC_OPTIONS']['typo3/class.db_list_extra.inc']['getTable'] as $classData) {
				$hookObject = t3lib_div::getUserObj($classData);

				if(!($hookObject instanceof t3lib_localRecordListGetTableHook)) {
					throw new UnexpectedValueException('$hookObject must implement interface t3lib_localRecordListGetTableHook', 1195114460);
				}

				$hookObject->getDBlistQuery($table, $id, $addWhere, $selFieldList, $this);
			}
		}

			// Create the SQL query for selecting the elements in the listing:
		if ($this->csvOutput) {	// do not do paging when outputting as CSV
			$this->iLimit = 0;
		}

		if ($this->firstElementNumber > 2 && $this->iLimit > 0) {
				// Get the two previous rows for sorting if displaying page > 1
			$this->firstElementNumber = $this->firstElementNumber - 2;
			$this->iLimit = $this->iLimit + 2;
			$queryParts = $this->makeQueryArray($table, $id,$addWhere,$selFieldList);	// (API function from class.db_list.inc)
			$this->firstElementNumber = $this->firstElementNumber + 2;
			$this->iLimit = $this->iLimit - 2;
		} else {
			$queryParts = $this->makeQueryArray($table, $id,$addWhere,$selFieldList);	// (API function from class.db_list.inc)
		}

		$this->setTotalItems($queryParts);		// Finding the total amount of records on the page (API function from class.db_list.inc)

		// Init:
		$dbCount = 0;
		$out = '';
		$listOnlyInSingleTableMode = $this->listOnlyInSingleTableMode && !$this->table;

		// If the count query returned any number of records, we perform the real query, selecting records.
		if ($this->totalItems){
			// Fetch records only if not in single table mode or if in multi table mode and not collapsed
			if ($listOnlyInSingleTableMode || (!$this->table && $tableCollapsed)) {
				$dbCount = $this->totalItems;
			} else {
					// set the showLimit to the number of records when outputting as CSV
				if ($this->csvOutput) {
					$this->showLimit = $this->totalItems;
					$this->iLimit = $this->totalItems;
				}
				$result = $GLOBALS['TYPO3_DB']->exec_SELECT_queryArray($queryParts);
				$dbCount = $GLOBALS['TYPO3_DB']->sql_num_rows($result);
			}
		}

		// If any records was selected, render the list:
		if ($dbCount){
				// Half line is drawn between tables:
			if (!$listOnlyInSingleTableMode)	{
				$theData = Array();
				if (!$this->table && !$rowlist)	{
					$theData[$titleCol] = '<img src="clear.gif" width="'.($GLOBALS['SOBE']->MOD_SETTINGS['bigControlPanel']?'230':'350').'" height="1" alt="" />';
					if (in_array('_CONTROL_',$this->fieldArray))	$theData['_CONTROL_']='';
					if (in_array('_CLIPBOARD_',$this->fieldArray))	$theData['_CLIPBOARD_']='';
				}
				$out.=$this->addelement(0,'',$theData,'class="c-table-row-spacer"',$this->leftMargin);
			}

			// Header line is drawn
			$theData = Array();
			if ($this->disableSingleTableView)	{
				$theData[$titleCol] = '<span class="c-table">'.$GLOBALS['LANG']->sL($TCA[$table]['ctrl']['title'],1).'</span> ('.$this->totalItems.')';
			} else {				
				$theData[$titleCol] = $this->linkWrapTable($table, '<span class="c-table">' . $GLOBALS['LANG']->sL($TCA[$table]['ctrl']['title'], TRUE) . '</span> (' . $this->totalItems . ') ' . 	($this->table ? t3lib_iconWorks::getSpriteIcon('actions-view-table-collapse', array('title' => $GLOBALS['LANG']->getLL('contractView', TRUE))) : t3lib_iconWorks::getSpriteIcon('actions-view-table-expand', array('title' => $GLOBALS['LANG']->getLL('expandView', TRUE)))));			
			}

				// CSH:
			$theData[$titleCol].= t3lib_BEfunc::cshItem($table,'',$this->backPath,'',FALSE,'margin-bottom:0px; white-space: normal;');
			//$theData['_DCOLUMN_'].= '<span><img src="'.t3lib_extMgm::extRelPath("nsdynamicc").'icon/icon-column.png" /></span>';			
//			debug ($a, 'Output of variable',__FUNCTION__, __LINE__, __FILE__);			
			if ($listOnlyInSingleTableMode)	{
				$out.='
					<tr>
						<td class="t3-row-header" style="width:95%;">' . $theData[$titleCol] . '</td>
					</tr>';

				if ($GLOBALS['BE_USER']->uc["edit_showFieldHelp"])	{
					$GLOBALS['LANG']->loadSingleTableDescription($table);
					if (isset($GLOBALS['TCA_DESCR'][$table]['columns']['']))	{
						$onClick = 'vHWin=window.open(\'view_help.php?tfID='.$table.'.\',\'viewFieldHelp\',\'height=400,width=600,status=0,menubar=0,scrollbars=1\');vHWin.focus();return false;';
						$out.='
					<tr>
						<td class="c-tableDescription">'.t3lib_BEfunc::helpTextIcon($table,'',$this->backPath,TRUE).$GLOBALS['TCA_DESCR'][$table]['columns']['']['description'].'</td>
					</tr>';
					}
				}
			} else {
				// Render collapse button if in multi table mode
				$collapseIcon = '';
				if (!$this->table) {
					$collapseIcon = '<a href="' . htmlspecialchars($this->listURL()) . '&collapse[' . $table . ']=' . ($tableCollapsed ? '0' : '1') . '" title="' . ($tableCollapsed ? $GLOBALS['LANG']->sL('LLL:EXT:lang/locallang_core.php:labels.expandTable', TRUE) : $GLOBALS['LANG']->sL('LLL:EXT:lang/locallang_core.php:labels.collapseTable', TRUE)) . '">' .
							($tableCollapsed ? t3lib_iconWorks::getSpriteIcon('actions-view-list-expand', array('class' => 'collapseIcon')) : t3lib_iconWorks::getSpriteIcon('actions-view-list-collapse', array('class' => 'collapseIcon'))) .
						'</a>';
				}				
				//relative path for dynamic column icon
				$dIcon = $this->dIcon($id, $table); 				
				//debug ($theData, 'Output of variable',__FUNCTION__, __LINE__, __FILE__);
				$out .= $this->addElement(1, $collapseIcon, $theData, ' class="t3-row-header"', '', '', $dIcon);
			}

			// Render table rows only if in multi table view and not collapsed or if in single table view
			if (!$listOnlyInSingleTableMode && (!$tableCollapsed || $this->table)) {
					// Fixing a order table for sortby tables
				$this->currentTable = array();
				$currentIdList = array();
				$doSort = ($TCA[$table]['ctrl']['sortby'] && !$this->sortField);

				$prevUid = 0;
				$prevPrevUid = 0;

					// Get first two rows and initialize prevPrevUid and prevUid if on page > 1
				if ($this->firstElementNumber > 2 && $this->iLimit > 0) {
					$row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result);
					$prevPrevUid = -(int) $row['uid'];
					$row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result);
					$prevUid = $row['uid'];
				}

				$accRows = array();	// Accumulate rows here
				while($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result))	{

						// In offline workspace, look for alternative record:
					t3lib_BEfunc::workspaceOL($table, $row, $GLOBALS['BE_USER']->workspace, TRUE);

					if (is_array($row))	{
						$accRows[] = $row;
						$currentIdList[] = $row['uid'];
						if ($doSort)	{
							if ($prevUid)	{
								$this->currentTable['prev'][$row['uid']] = $prevPrevUid;
								$this->currentTable['next'][$prevUid] = '-'.$row['uid'];
								$this->currentTable['prevUid'][$row['uid']] = $prevUid;
							}
							$prevPrevUid = isset($this->currentTable['prev'][$row['uid']]) ? -$prevUid : $row['pid'];
							$prevUid=$row['uid'];
						}
					}
				}
				$GLOBALS['TYPO3_DB']->sql_free_result($result);

				$this->totalRowCount = count($accRows);

					// CSV initiated
				if ($this->csvOutput) $this->initCSV();

					// Render items:
				$this->CBnames=array();
				$this->duplicateStack=array();
				$this->eCounter=$this->firstElementNumber;

				$iOut = '';
				$cc = 0;

				foreach($accRows as $row)	{
					// Render item row if counter < limit
					if ($cc < $this->iLimit) {
						$cc++;
						$this->translations = FALSE;
						$iOut.= $this->renderListRow($table,$row,$cc,$titleCol,$thumbsCol);

							// If localization view is enabled it means that the selected records are either default or All language and here we will not select translations which point to the main record:
						if ($this->localizationView && $l10nEnabled)	{
								// For each available translation, render the record:
							if (is_array($this->translations)) {
								foreach ($this->translations as $lRow) {
										// $lRow isn't always what we want - if record was moved we've to work with the placeholder records otherwise the list is messed up a bit
									if ($row['_MOVE_PLH_uid'] && $row['_MOVE_PLH_pid']) {
										$tmpRow = t3lib_BEfunc::getRecordRaw($table, 't3ver_move_id="'.intval($lRow['uid']) . '" AND pid="' . $row['_MOVE_PLH_pid'] . '" AND t3ver_wsid=' . $row['t3ver_wsid'] . t3lib_beFunc::deleteClause($table), $selFieldList);
										$lRow = is_array($tmpRow)?$tmpRow:$lRow;
									}
										// In offline workspace, look for alternative record:
									t3lib_BEfunc::workspaceOL($table, $lRow, $GLOBALS['BE_USER']->workspace, true);
									if (is_array($lRow) && $GLOBALS['BE_USER']->checkLanguageAccess($lRow[$TCA[$table]['ctrl']['languageField']]))	{
										$currentIdList[] = $lRow['uid'];
										$iOut.=$this->renderListRow($table,$lRow,$cc,$titleCol,$thumbsCol,18);
									}
								}
							}
						}
					}

						// Counter of total rows incremented:
					$this->eCounter++;
				}

					// Record navigation is added to the beginning and end of the table if in single table mode
				if ($this->table) {
					$pageNavigation = $this->renderListNavigation();
					$iOut = $pageNavigation . $iOut . $pageNavigation;
				} else {
						// show that there are more records than shown
					if ($this->totalItems > $this->itemsLimitPerTable) {
						$countOnFirstPage = $this->totalItems > $this->itemsLimitSingleTable ? $this->itemsLimitSingleTable : $this->totalItems;
						$hasMore = ($this->totalItems > $this->itemsLimitSingleTable);
						$iOut .= '<tr><td colspan="' . count($this->fieldArray) . '" style="padding:5px;">
								<a href="'.htmlspecialchars($this->listURL() . '&table=' . rawurlencode($table)) . '">' .
								'<img' . t3lib_iconWorks::skinImg($this->backPath,'gfx/pildown.gif', 'width="14" height="14"') .' alt="" />'.
								' <i>[1 - ' . $countOnFirstPage . ($hasMore ? '+' : '') . ']</i></a>
								</td></tr>';
						}

				}

					// The header row for the table is now created:
				$out .= $this->renderListHeader($table,$currentIdList);
			}

				// The list of records is added after the header:
			$out .= $iOut;
			unset($iOut);
			// ... and it is all wrapped in a table:
			$out='
			<!--
				DB listing of elements:	"'.htmlspecialchars($table).'"
			-->
				<table border="0" cellpadding="0" cellspacing="0" class="typo3-dblist'.($listOnlyInSingleTableMode?' typo3-dblist-overview':'').'">
					'.$out.'
				</table>';

			// Output csv if...
			if ($this->csvOutput)	$this->outputCSV($table);	// This ends the page with exit.
		}
			// Return content:			
		return $out;
	}
	


	//addElement method overwrite here and 	
	function addElement($h, $icon, $data, $trParams = '', $lMargin = '', $altLine = '', $dcolumn =''){

	$noWrap = ($this->no_noWrap) ? '' : ' nowrap="nowrap"';
	// Start up:
	$out='
	<!-- Element, begin: -->
	<tr '.$trParams.'>';
		// Show icon and lines
	if ($this->showIcon)	{
		$out.='
		<td nowrap="nowrap" class="col-icon">';

		if (!$h)	{			
			$out.='<img src="clear.gif" width="1" height="8" alt="" />';
		} else {
			for ($a=0;$a<$h;$a++)	{
				if (!$a)	{
					if ($icon)	$out.= $icon;
				} else {
				}
			}
		}
		$out.='</td>
		';
	}

	// Init rendering.
	$colsp='';
	$lastKey='';
	$c=0;
	$ccount=0;
 				 	
	// Traverse field array which contains the data to present:			
	foreach ($this->fieldArray as $vKey) {			
		if (isset($data[$vKey]))	{
			if ($lastKey)	{
				$cssClass = $this->addElement_tdCssClass[$lastKey];
				if($this->oddColumnsCssClass && $ccount % 2 == 0) {
					$cssClass = implode(' ', array($this->addElement_tdCssClass[$lastKey], $this->oddColumnsCssClass));
				}
	
				$out.='
					<td'.
					$noWrap.
					' class="' . $cssClass . '"'.
					$colsp.
					$this->addElement_tdParams[$lastKey].
					'>'.$data[$lastKey].'</td>';
			}
			$lastKey=$vKey;
			$c=1;
			$ccount++;
		} else {
			if (!$lastKey) {$lastKey=$vKey;}
			$c++;
		}
		if ($c>1)	{$colsp=' colspan="'.$c.'"';} else {$colsp='';}
	}
	
	if ($lastKey) {
		$cssClass = $this->addElement_tdCssClass[$lastKey];
		if($this->oddColumnsCssClass) {
			$cssClass = implode(' ', array($this->addElement_tdCssClass[$lastKey], $this->oddColumnsCssClass));
		}
		
		$out.='
			<td'.$noWrap.' class="' . $cssClass . '"' . $colsp.$this->addElement_tdParams[$lastKey].'>'.$data[$lastKey].'</td>';	
			}
	//add dynamic column a small icon at the top-right corner of the record table
	if($dcolumn){				
		$out .= '<td style="padding-right: 3px; float: right;">'.$dcolumn.'</td>';
	}
	else{
		$out .= '<td></td>';
	}	
	// End row
	$out.='
	</tr>';
	// Return row.
	return $out;
  }
	
		/**
	 * Genarate the dynamic column icon with its attributes
	 *
	 * @param	 string		Table name
	 * @param	 integer		Page id
	 * @return string		dynamic column icon HTML
	 */
	function dIcon($id,$table)	{
		//get the path of dynamic column icon
		$dpath = t3lib_extMgm::extRelPath("nsdynamicc").'icon/icon-column.png';
		$dHTML = '<a class="dcolumn" data-dinfo="'.$id.','.$table.'" href="#"><img  src="'.$dpath.'" alt="dcolumn" /></a>';
		return $dHTML; 
	}

	
}
// Include extension?
	
if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/nsdynamicc/class.ux_db_list_extra.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/nsdynamicc/class.ux_db_list_extra.php']);
}	
// Make instance:
/*$SOBE = t3lib_div::makeInstance('dynamic_column');
$SOBE->main();
$SOBE->printContent();*/

?>
