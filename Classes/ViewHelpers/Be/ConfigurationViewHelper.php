<?php

class Tx_ExtbaseKickstarter_ViewHelpers_Be_ConfigurationViewHelper extends Tx_Fluid_ViewHelpers_Be_AbstractBackendViewHelper {
	
	/**
	 * @var string Absolute Path to the folder where the Editor Packages reside
	 */
	private $defaultEditorPackagePath;
	
	/**
	 * @var t3lib_PageRenderer
	 */
	private $pageRenderer;
	
	public function __construct() {
		$this->defaultEditorPackagePath = t3lib_extMgm::extPath('extbase_kickstarter') . 'Resources/Public/JavaScript/Packages/';
		$this->pageRenderer = $this->getDocInstance()->getPageRenderer();
	}
	
	public function render() {
		
		// SECTION: JAVASCRIPT FILES
		$this->pageRenderer->addJsFile(t3lib_extMgm::extRelPath('extbase_kickstarter') . 'Resources/Public/JavaScript/Application.js');
		$this->pageRenderer->addJsFile(t3lib_extMgm::extRelPath('extbase_kickstarter') . 'Resources/Public/JavaScript/Application/AbstractBootstrap.js');

		// now the loading order does not matter anymore
		$this->addJSPackageFiles();
		$this->pageRenderer->addJsFile(t3lib_extMgm::extRelPath('extbase_kickstarter') . 'Resources/Public/JavaScript/UserInterface/Bootstrap.js');
		$this->pageRenderer->addJsFile(t3lib_extMgm::extRelPath('extbase_kickstarter') . 'Resources/Public/JavaScript/UserInterface/Layout.js');
		$this->pageRenderer->addJsFile(t3lib_extMgm::extRelPath('extbase_kickstarter') . 'Resources/Public/JavaScript/UserInterface/TabLayout.js');
		
		// SECTION: CSS FILES
		$this->pageRenderer->addCssFile(t3lib_extMgm::extRelPath('extbase_kickstarter') . 'Resources/Public/css/sprites.css');
		$this->pageRenderer->addCssFile(t3lib_extMgm::extRelPath('extbase_kickstarter') . 'Resources/Public/css/style.css');

	}
	
	private function addJSPackageFiles() {
		$packages = $this->getJSPackageConfiguration();
		foreach($packages as $package => $config) {
			if(isset($config['packagesBaseDirectory'])) {
				$baseDirectory = $config['packagesBaseDirectory'] . $package . '/';
			} else {
				$baseDirectory = $this->defaultEditorPackagePath . $package . '/';
			}
			try {
				$iterator = $this->iterateDirectoryRecursively($baseDirectory);
			} catch(Exception $e) {
				// Directory was not found/accessible or something..
				continue;
			}
			foreach ($iterator as $file) {
				if($this->isJSFile($file->getFileName())) {
					$this->pageRenderer->addJsFile($file->getPathname());
				}
			}
		}
	}
	
	private function isJSFile($filepath) {
		$parts = explode('.', $filepath);
		if($parts[count($parts)-1] === 'js') {
			return TRUE;
		} else {
			return FALSE;
		}
	}
	
	/**
	 * Iterate over a directory with all subdirectories
	 *
	 * (Taken from TYPO3 Phoenix)
	 *
	 * @param string $directory The directory to iterate over
	 * @return Iterator An iterator
	 * @author Christopher Hlubek
	 */
	protected function iterateDirectoryRecursively($directory) {
		return new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directory));
	}
	
	private function getJSPackageConfiguration() {
		// TODO: This array has to be configurable from outside
		$packages = array(
			'Welcome' => array(),
			'General' => array(),
			'DomainModelling' => array(),
			/*
			 * Including packages from EXT:myext/Resources/Public/JavaScript/ExtbaseKickstarterPackages/MyPackage/ would work like this:
			'MyPackage' => array(
				'packagesBaseDirectory' => 'EXT:myext/Resources/Public/JavaScript/ExtbaseKickstarterPackages/'
			),
			*/
		);
		return $packages;
	}
}

?>