<?php

class Tx_ExtbaseKickstarter_ViewHelpers_Be_ConfigurationViewHelper extends Tx_Fluid_ViewHelpers_Be_AbstractBackendViewHelper {
	public function render() {
		$doc = $this->getDocInstance();
		$doc->bodyTagAdditions .= 'class="yui-skin-sam"';

		$pageRenderer = $doc->getPageRenderer();

		// SECTION: JAVASCRIPT FILES
		
		// ExtJS Application
		$pageRenderer->addJsFile(t3lib_extMgm::extRelPath('extbase_kickstarter') . 'Resources/Public/Application.js');

	}
}

?>