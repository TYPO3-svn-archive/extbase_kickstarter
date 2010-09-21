<?php

class Tx_ExtbaseKickstarter_ViewHelpers_Be_ConfigurationViewHelper extends Tx_Fluid_ViewHelpers_Be_AbstractBackendViewHelper {

	/**
	 * @var t3lib_PageRenderer
	 */
	private $pageRenderer;
	
	public function __construct() {
		/* @var $this->pageRenderer t3lib_PageRenderer */
		$this->pageRenderer = $this->getDocInstance()->getPageRenderer();
		
	}
	
	public function render() {

		/** @todo This line should be disabled before publication of the extension */
		$this->pageRenderer->disableCompressJavascript();

		$this->pageRenderer->addInlineSetting('extbase_kickstarter', 'baseUrl', '../' . t3lib_extMgm::siteRelPath('extbase_kickstarter'));
		$this->setLocallangSettings();
		$this->setUrlSettings();

		//$this->pageRenderer->addExtDirectCode();
		
		// SECTION: JAVASCRIPT FILES
		$this->pageRenderer->addJsFile(t3lib_extMgm::extRelPath('extbase_kickstarter') . 'Resources/Public/JavaScript/Application.js');
		$this->pageRenderer->addJsFile(t3lib_extMgm::extRelPath('extbase_kickstarter') . 'Resources/Public/JavaScript/Application/AbstractBootstrap.js');

		// now the loading order does not matter anymore
		
		$this->pageRenderer->addJsFile(t3lib_extMgm::extRelPath('extbase_kickstarter') . 'Resources/Public/JavaScript/UserInterface/Bootstrap.js');
		$this->pageRenderer->addJsFile(t3lib_extMgm::extRelPath('extbase_kickstarter') . 'Resources/Public/JavaScript/UserInterface/Layout.js');
		$this->pageRenderer->addJsFile(t3lib_extMgm::extRelPath('extbase_kickstarter') . 'Resources/Public/JavaScript/UserInterface/TabLayout.js');

		$this->addJSPackageFiles();
		
		// SECTION: CSS FILES
		$this->pageRenderer->addCssFile(t3lib_extMgm::extRelPath('extbase_kickstarter') . 'Resources/Public/CSS/style.css');

	}

	/**
	 * Add package files based on JSPackageConfiguration
	 */
	private function addJSPackageFiles() {
		$jsFiles = array();

		$packages = $this->getJSPackageConfiguration();
		foreach($packages as $extKey => $extPackageConfig) {

			// Find the package base directory
			if (isset($extConfig['packagesBaseDirectory'])) {
				$packagesBaseDirectory = t3lib_div::getFileAbsFileName($extConfig['packagesBaseDirectory']);
			} else {
				$packagesBaseDirectory = t3lib_div::getFileAbsFileName('EXT:' . $extKey . '/Resources/Public/JavaScript/Packages/');
			}

			foreach ($extPackageConfig as $packageFolder => $packageConfig) {
				if (is_file($packagesBaseDirectory . $packageFolder . '/Bootstrap.js')) {
					$jsFiles[] = $packagesBaseDirectory . $packageFolder . '/Bootstrap.js';
				}
				

				$iterator = new RecursiveDirectoryIterator($packagesBaseDirectory . $packageFolder);

				foreach (new RecursiveIteratorIterator($iterator) as $file) {
					$filePathAndName = str_replace('\\', '/', $file->getPathName()); // Clean potential Windows file paths
					if ($file->isFile() && preg_match("/\.js$/i", $filePathAndName) && !in_array($filePathAndName, $jsFiles)) {
						$jsFiles[] = $filePathAndName;
					}
				}
			}
		}

		// Add the javascript files to the page
		foreach ($jsFiles as $key => $file) {
			$this->pageRenderer->addJsFile(str_replace(t3lib_extMgm::extPath($extKey), t3lib_extMgm::extRelPath($extKey), $file));
		}
	}
	
	private function getJSPackageConfiguration() {
		// TODO: This array has to be configurable from outside
		$packages = array(
			'extbase_kickstarter' => array(
				'Welcome' => array(),
				'General' => array(),
				'DomainModelling' => array()
			),
			/*
			 * Including packages from EXT:myext/Resources/Public/JavaScript/ExtbaseKickstarterPackages/MyPackage/ would work like this:
			'MyExtension' => array(
				'packagesBaseDirectory' => 'EXT:myext/Resources/Public/JavaScript/ExtbaseKickstarterPackages/',
			    array(
					'Package' => array()
				)
			),
			*/
		);
		return $packages;
	}

	private function setLocallangSettings() {
		$LL = t3lib_div::readLLfile('EXT:extbase_kickstarter/Resources/Private/Language/locallang.xml', 'default');

		if (!empty($LL['default']) && is_array($LL['default'])) {
			foreach ($LL['default'] as $key => $value) {
				$this->pageRenderer->addInlineSetting(
					'extbase_kickstarter._LOCAL_LANG',
					str_replace('.', '_', $key),
					Tx_Extbase_Utility_Localization::translate($key, 'extbase_kickstarter')
				);
			}
		}
	}

	private function setUrlSettings() {
		$controllerActionArray = array();

		if (is_array($GLOBALS['TBE_MODULES']['_configuration']['tools_ExtbaseKickstarterKickstarter']['controllerActions'])) {
			foreach ($GLOBALS['TBE_MODULES']['_configuration']['tools_ExtbaseKickstarterKickstarter']['controllerActions'] as $controller => $actionList) {
				$actions = explode(',', $actionList);
				foreach ($actions as $action) {
					$controllerActionArray[$controller][$action] = $this->controllerContext->getUriBuilder()->reset()->uriFor($action, array(), $controller);
				}
			}
		}

		$this->pageRenderer->addInlineSettingArray('extbase_kickstarter', array(
			'controllers' => $controllerActionArray
		));
	}
}