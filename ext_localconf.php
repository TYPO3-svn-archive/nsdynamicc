<?php    // Setting the Install Tool password to the default 'joh316'
/*$TYPO3_CONF_VARS['BE']['installToolPassword'] = 'bacb98acf97e0b6112b1d1b650b84971';    
// Setting the list of extensions to BLANK (by default there is a long list set)
$TYPO3_CONF_VARS['EXT']['extList'] = 'install';$TYPO3_CONF_VARS['EXT']['requiredExt'] = 'lang';    
// Setting up the database username, password and host$typo_db_username = 'root';
$typo_db_password = 'nuwr875';$typo_db_host = 'localhost';
## INSTALL SCRIPT EDIT POINT TOKEN - all lines after this points may be changed by the install script!
$typo_db = 't3_coreinstall';    
//  Modified or inserted by Typo3 Install Tool.
$TYPO3_CONF_VARS['SYS']['sitename'] = 'Core Install';    
//  Modified or inserted by Typo3 Install Tool.// Updated by Typo3 Install Tool 14-02-2003 15:20:04
$TYPO3_CONF_VARS['EXT']['extList'] = 'install,phpmyadmin,setup,info_pagetsconfig';       
// Modified or inserted by Typo3 Extension Manager. // Updated by Typo3 Extension Manager 19-02-2003 12:47:26
*/

//$TYPO3_CONF_VARS["BE"]["XCLASS"]["tslib/class.tslib_content.php"] = PATH_typo3conf."class.ux_tslib_content.php";

if (!defined('TYPO3_MODE')) {
	die ('Access denied.');
}
$TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['typo3/class.db_list_extra.inc'] = t3lib_extMgm::extPath('nsdynamicc') . 'class.ux_db_list_extra.php';
$TYPO3_CONF_VARS['BE']['XCLASS']['typo3/db_list.php'] = t3lib_extMgm::extPath('nsdynamicc') . 'ux_db_list.php';
?>

