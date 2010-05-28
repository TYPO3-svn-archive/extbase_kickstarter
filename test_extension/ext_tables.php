<?php
if (!defined ('TYPO3_MODE')) die ('Access denied.');

Tx_Extbase_Utility_Extension::registerPlugin(
	$_EXTKEY,
	'Pi1',
	'test extension'
);

t3lib_extMgm::addStaticFile($_EXTKEY, 'Configuration/TypoScript', 'test extension');

//$TCA['tt_content']['types']['list']['subtypes_addlist'][$_EXTKEY . '_pi1'] = 'pi_flexform';
//t3lib_extMgm::addPiFlexFormValue($_EXTKEY . '_pi1', 'FILE:EXT:' . $_EXTKEY . '/Configuration/FlexForms/flexform_list.xml');


t3lib_extMgm::addLLrefForTCAdescr('tx_testextension_domain_model_box','EXT:test_extension/Resources/Private/Language/locallang_csh_tx_testextension_domain_model_box.xml');
t3lib_extMgm::allowTableOnStandardPages('tx_testextension_domain_model_box');
$TCA['tx_testextension_domain_model_box'] = array (
	'ctrl' => array (
		'title'             => 'LLL:EXT:test_extension/Resources/Private/Language/locallang_db.xml:tx_testextension_domain_model_box',
		'label' 			=> 'width',
		'tstamp' 			=> 'tstamp',
		'crdate' 			=> 'crdate',
		'versioningWS' 		=> 2,
		'versioning_followPages'	=> TRUE,
		'origUid' 			=> 't3_origuid',
		'languageField' 	=> 'sys_language_uid',
		'transOrigPointerField' 	=> 'l18n_parent',
		'transOrigDiffSourceField' 	=> 'l18n_diffsource',
		'delete' 			=> 'deleted',
		'enablecolumns' 	=> array(
			'disabled' => 'hidden'
			),
		'dynamicConfigFile' => t3lib_extMgm::extPath($_EXTKEY) . 'Configuration/Tca.php',
		'iconfile' 			=> t3lib_extMgm::extRelPath($_EXTKEY) . 'Resources/Public/Icons/tx_testextension_domain_model_box.gif'
	)
);

?>