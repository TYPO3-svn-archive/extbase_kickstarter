<?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');

$TCA['tx_testextension_domain_model_box'] = array(
	'ctrl' => $TCA['tx_testextension_domain_model_box']['ctrl'],
	'interface' => array(
		'showRecordFieldList' => 'width,height'
	),
	'types' => array(
		'1' => array('showitem' => 'width,height')
	),
	'palettes' => array(
		'1' => array('showitem' => '')
	),
	'columns' => array(
		'sys_language_uid' => array(
			'exclude' => 1,
			'label' => 'LLL:EXT:lang/locallang_general.php:LGL.language',
			'config' => array(
				'type' => 'select',
				'foreign_table' => 'sys_language',
				'foreign_table_where' => 'ORDER BY sys_language.title',
				'items' => array(
					array('LLL:EXT:lang/locallang_general.php:LGL.allLanguages',-1),
					array('LLL:EXT:lang/locallang_general.php:LGL.default_value',0)
				)
			)
		),
		'l18n_parent' => array(
			'displayCond' => 'FIELD:sys_language_uid:>:0',
			'exclude' => 1,
			'label' => 'LLL:EXT:lang/locallang_general.php:LGL.l18n_parent',
			'config' => array(
				'type' => 'select',
				'items' => array(
					array('', 0),
				),
				'foreign_table' => 'tx_testextension_domain_model_box',
				'foreign_table_where' => 'AND tx_testextension_domain_model_box.uid=###REC_FIELD_l18n_parent### AND tx_testextension_domain_model_box.sys_language_uid IN (-1,0)',
			)
		),
		'l18n_diffsource' => array(
			'config'=>array(
				'type'=>'passthrough')
		),
		't3ver_label' => array(
			'displayCond' => 'FIELD:t3ver_label:REQ:true',
			'label' => 'LLL:EXT:lang/locallang_general.php:LGL.versionLabel',
			'config' => array(
				'type'=>'none',
				'cols' => 27
			)
		),
		'hidden' => array(
			'exclude' => 1,
			'label'   => 'LLL:EXT:lang/locallang_general.xml:LGL.hidden',
			'config'  => array(
				'type' => 'check'
			)
		),
		'width' => array(
			'exclude' => 0,
			'label'   => 'LLL:EXT:test_extension/Resources/Private/Language/locallang_db.xml:tx_testextension_domain_model_box.width',
			'config'  => array(
				'type' => 'input',
				'size' => 4,
				'eval' => 'int'
			)
		),
		'height' => array(
			'exclude' => 0,
			'label'   => 'LLL:EXT:test_extension/Resources/Private/Language/locallang_db.xml:tx_testextension_domain_model_box.height',
			'config'  => array(
				'type' => 'input',
				'size' => 4,
				'eval' => 'int'
			)
		),
	),
);

?>