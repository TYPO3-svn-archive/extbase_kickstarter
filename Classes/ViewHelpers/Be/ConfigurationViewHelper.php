<?php

class Tx_ExtbaseKickstarter_ViewHelpers_Be_ConfigurationViewHelper extends Tx_Fluid_ViewHelpers_Be_AbstractBackendViewHelper {
	public function render() {
		$doc = $this->getDocInstance();
		$doc->bodyTagAdditions .= 'class="yui-skin-sam"';

		$pageRenderer = $doc->getPageRenderer();
		
		// SECTION: JAVASCRIPT FILES
		$pageRenderer->addJsFile(t3lib_extMgm::extRelPath('extbase_kickstarter') . 'Resources/Public/js/toolbar.js');
		$pageRenderer->addJsFile(t3lib_extMgm::extRelPath('extbase_kickstarter') . 'Resources/Public/js/layout.js');
		$pageRenderer->addJsFile(t3lib_extMgm::extRelPath('extbase_kickstarter') . 'Resources/Public/js/extensionTree.js');
		
		// ExtJS Application
		//$pageRenderer->addJsFile(t3lib_extMgm::extRelPath('extbase_kickstarter') . 'Resources/Public/Application.js');
		
		// SECTION: CSS FILES
		$pageRenderer->addCssFile(t3lib_extMgm::extRelPath('extbase_kickstarter') . 'Resources/Public/css/sprites.css');
		$pageRenderer->addCssFile(t3lib_extMgm::extRelPath('extbase_kickstarter') . 'Resources/Public/css/style.css');

	}
}

?>