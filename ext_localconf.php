<?php

if (!defined('TYPO3_MODE')) {
	die ('Access denied.');
}
$TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['typo3/class.db_list_extra.inc'] = t3lib_extMgm::extPath('nsdynamicc') . 'class.ux_db_list_extra.php';
$TYPO3_CONF_VARS['BE']['XCLASS']['typo3/db_list.php'] = t3lib_extMgm::extPath('nsdynamicc') . 'ux_db_list.php';

//$TYPO3_CONF_VARS['BE']['XCLASS']['t3lib/class.t3lib_befunc.php'] = t3lib_extMgm::extPath('nsdynamicc') . 'class.ux_t3lib_befunc.php';

if (TYPO3_MODE == 'BE') {
//register hook cshItemTempClass for helptip new functionality
$TYPO3_CONF_VARS['SC_OPTIONS']['t3lib/class.t3lib_befunc.php']['cshItemTempClass'][] = 'EXT:nsdynamicc/classes/class.tx_nsdynamicc_helptips.php:tx_nsdynamicc_helptips';

//template Hooks enabled to add js and css files
$TYPO3_CONF_VARS['SC_OPTIONS']['typo3/template.php']['preStartPageHook'][] = 'EXT:nsdynamicc/classes/class.tx_nsdynamicc_helptips.php:&tx_nsdynamicc_helptips->preStartPageHook';
}
?>

