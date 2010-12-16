{namespace k=Tx_ExtbaseKickstarter_ViewHelpers}<?php
if (!defined ('TYPO3_MODE')) {
	die ('Access denied.');
}
<f:for each="{extension.plugins}" as="plugin">
Tx_Extbase_Utility_Extension::configurePlugin(
	$_EXTKEY,
	'<k:uppercaseFirst>{plugin.key}</k:uppercaseFirst>',
	array(
		<f:for each="{extension.domainObjectsForWhichAControllerShouldBeBuilt}" as="domainObject">'{domainObject.name}' => '<f:for each="{domainObject.actions}" as="action">{action.name},</f:for>'</f:for>
	),
	array(
		<f:for each="{extension.domainObjectsForWhichAControllerShouldBeBuilt}" as="domainObject">'{domainObject.name}' => '<f:for each="{domainObject.actions}" as="action">{action.name},</f:for>',</f:for>
	)
);
</f:for>
?>