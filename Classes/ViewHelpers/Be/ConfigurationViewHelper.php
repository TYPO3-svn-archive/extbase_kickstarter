<?php

class Tx_ExtbaseKickstarter_ViewHelpers_Be_ConfigurationViewHelper extends Tx_Fluid_ViewHelpers_Be_AbstractBackendViewHelper {
	public function render() {
		$pageRenderer = $this->getDocInstance()->getPageRenderer();
		
		// SECTION: JAVASCRIPT FILES
		$pageRenderer->addJsFile(t3lib_extMgm::extRelPath('extbase_kickstarter') . 'Resources/Public/JavaScript/Application.js');
		$pageRenderer->addJsFile(t3lib_extMgm::extRelPath('extbase_kickstarter') . 'Resources/Public/JavaScript/Application/AbstractBootstrap.js');

		// now the loading order does not matter anymore
		$pageRenderer->addJsFile(t3lib_extMgm::extRelPath('extbase_kickstarter') . 'Resources/Public/JavaScript/Packages/Welcome/Bootstrap.js');
		$pageRenderer->addJsFile(t3lib_extMgm::extRelPath('extbase_kickstarter') . 'Resources/Public/JavaScript/Packages/Welcome/WelcomeScreen.js');
		$pageRenderer->addJsFile(t3lib_extMgm::extRelPath('extbase_kickstarter') . 'Resources/Public/JavaScript/Packages/General/Bootstrap.js');
		$pageRenderer->addJsFile(t3lib_extMgm::extRelPath('extbase_kickstarter') . 'Resources/Public/JavaScript/Packages/General/General.js');
		$pageRenderer->addJsFile(t3lib_extMgm::extRelPath('extbase_kickstarter') . 'Resources/Public/JavaScript/Packages/DomainModelling/Bootstrap.js');
		$pageRenderer->addJsFile(t3lib_extMgm::extRelPath('extbase_kickstarter') . 'Resources/Public/JavaScript/Packages/DomainModelling/DomainModeller.js');
		$pageRenderer->addJsFile(t3lib_extMgm::extRelPath('extbase_kickstarter') . 'Resources/Public/JavaScript/UserInterface/Bootstrap.js');
		$pageRenderer->addJsFile(t3lib_extMgm::extRelPath('extbase_kickstarter') . 'Resources/Public/JavaScript/UserInterface/Layout.js');
		$pageRenderer->addJsFile(t3lib_extMgm::extRelPath('extbase_kickstarter') . 'Resources/Public/JavaScript/UserInterface/TabLayout.js');
		
		// SECTION: CSS FILES
		$pageRenderer->addCssFile(t3lib_extMgm::extRelPath('extbase_kickstarter') . 'Resources/Public/css/sprites.css');
		$pageRenderer->addCssFile(t3lib_extMgm::extRelPath('extbase_kickstarter') . 'Resources/Public/css/style.css');

	}
}

?>