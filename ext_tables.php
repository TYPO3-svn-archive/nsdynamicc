<?php
if (!defined('TYPO3_MODE')) 	die('Access denied.');

if(TYPO3_MODE == 'BE') {
	$opendocsPath = t3lib_extMgm::extPath('nsdynamicc');
	// register AJAX calls
	///** Register your unique ajaxID for dynamic column Ajax function */
	$GLOBALS['TYPO3_CONF_VARS']['BE']['AJAX']['tx_nsdynamicc::createAjaxSelector'] = $opendocsPath.'classes/class.tx_nsdynamicc_display.php:tx_nsdynamicc_display->createAjaxSelector';
	debug($opendocsPath);

}

?>