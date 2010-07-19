<?php
if (!defined('TYPO3_MODE')) 	die('Access denied.');

if(TYPO3_MODE == 'BE') {
$opendocsPath = t3lib_extMgm::extPath('nsdynamicc');

/** Register your unique ajaxID for dynamic column Ajax function */
	$GLOBALS['TYPO3_CONF_VARS']['BE']['AJAX']['tx_nsdynamicc::createAjaxSelector'] = $opendocsPath.'classes/class.tx_nsdynamicc_display.php:tx_nsdynamicc_display->createAjaxSelector';
/** register your unique ajaxID for collapse record table Ajax functionality */
	$GLOBALS['TYPO3_CONF_VARS']['BE']['AJAX']['tx_nsdynamicc::updateAjaxCollapse'] = $opendocsPath.'classes/class.tx_nsdynamicc_collapse.php:tx_nsdynamicc_collapse->updateAjaxCollapse';
/** register helptip new class to change the help Tips appearance and Ajax functionality */
	//$GLOBALS['TYPO3_CONF_VARS']['BE']['AJAX']['tx_nsdynamicc::updateAjaxCollapse'] = $opendocsPath.'classes/class.tx_nsdynamicc_collapse.php:tx_nsdynamicc_collapse->updateAjaxCollapse';
}

?>