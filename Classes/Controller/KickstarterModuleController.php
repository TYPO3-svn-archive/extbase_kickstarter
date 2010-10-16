<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2009 Ingmar Schlecht <ingmar@typo3.org>
*  All rights reserved
*
*  This script is part of the TYPO3 project. The TYPO3 project is
*  free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License as published by
*  the Free Software Foundation; either version 2 of the License, or
*  (at your option) any later version.
*
*  The GNU General Public License can be found at
*  http://www.gnu.org/copyleft/gpl.html.
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/

/**
 * Backend Module of the Extbase Kickstarter extension
 *
 * @category    Controller
 * @package     TYPO3
 * @subpackage  tx_mvcextjssamples
 * @author      Ingmar Schlecht <ingmar@typo3.org>
 * @license     http://www.gnu.org/copyleft/gpl.html
 * @version     SVN: $Id$
 */
class Tx_ExtbaseKickstarter_Controller_KickstarterModuleController extends Tx_Extbase_MVC_Controller_ActionController {

	/**
	 * Holds reference to the template class
	 *
	 * @var template
	 */
	protected $doc;

	/**
	 * Holds reference to t3lib_SCbase
	 *
	 * @var t3lib_SCbase
	 */
	protected $scBase;


	protected $objectSchemaBuilder;

	protected $codeGenerator;

	public function initializeAction() {
		$this->objectSchemaBuilder = t3lib_div::makeInstance('Tx_ExtbaseKickstarter_ObjectSchemaBuilder');
		$this->codeGenerator = t3lib_div::makeInstance('Tx_ExtbaseKickstarter_Service_CodeGenerator');
	}

	/**
	 * Index action for this controller.
	 *
	 * @return string The rendered view
	 */
	public function indexAction() {
	}

	public function domainmodellingAction() {
	}

	/**
	 * Main entry point for the buttons in the frontend
	 * @return unknown_type
	 * @todo rename this action
	 */
	public function generateCodeAction() {
		$this->config = Tx_Extbase_Dispatcher::getExtbaseFrameworkConfiguration();
		$jsonString = file_get_contents('php://input');
		$request = json_decode($jsonString, true);
		switch ($request['method']) {

			case 'saveWiring':
				$extensionConfigurationFromJson = json_decode($request['params']['working'], true);
				$extensionSchema = $this->objectSchemaBuilder->build($extensionConfigurationFromJson);
				$build = $this->codeGenerator->build($extensionSchema);
				if ($build === true) {
					return json_encode(array('saved'));
				} else {
					return json_encode(array($build));
				}
				
			break;
			case 'listWirings':
				$result = $this->getWirings();

				$response = array ('id' => $request['id'],'result' => $result,'error' => NULL);
				header('content-type: text/javascript');
				echo json_encode($response);
				exit();
		}
	}

	protected function getWirings() {
		$result = array();

		$extensionDirectoryHandle = opendir(PATH_typo3conf . 'ext/');
		while (false !== ($singleExtensionDirectory = readdir($extensionDirectoryHandle))) {
			if ($singleExtensionDirectory[0] == '.'){
				continue;
			}
			$jsonFile =  PATH_typo3conf . 'ext/' . $singleExtensionDirectory . '/kickstarter.json';
			if (file_exists($jsonFile)) {
				if($this->config['settings']['enableRoundtrip']){
					// generate unique IDs 
					$extensionConfigurationFromJson = json_decode(file_get_contents($jsonFile),true);
					$extensionConfigurationFromJson['modules'] = $this->generateUniqueIDs($extensionConfigurationFromJson['modules']);
					t3lib_div::writeFile($jsonFile, json_encode($extensionConfigurationFromJson));
				}
				
				$result[] = array(
					'name' => $singleExtensionDirectory,
					'working' => file_get_contents($jsonFile)
				);
			}
		}
		closedir($extensionDirectoryHandle);
		
		return $result;
	}

	/**
	 * Adds items to the ->MOD_MENU array. Used for the function menu selector.
	 *
	 * @return	void
	 */
	function menuConfig()	{
		$this->scBase->MOD_MENU = Array (
			'function' => Array (
				'1' => 'Menu 1',
				'2' => 'Menu 2',
				'3' => 'Menu 3',
			)
		);
		$this->scBase->menuConfig();
	}
	
	/**
	 * enable unique IDs to track modifications of properties and relations
	 * @param $jsonConfig
	 * @return array $jsonConfig with unique IDs
	 */
	protected function generateUniqueIDs($jsonConfig){
		//  generate unique IDs
		foreach($jsonConfig as &$module){
			
			if(empty($module['value']['objectsettings']['uid'])){
				$module['value']['objectsettings']['uid'] = md5(microtime().$module['propertyName']);
			}
		
			for($i=0;$i < count($module['value']['propertyGroup']['properties']);$i++){
				// don't save empty properties
				if(empty($module['value']['propertyGroup']['properties'][$i]['propertyName'])){
					unset($module['value']['propertyGroup']['properties'][$i]);
				}
				else if(empty($module['value']['propertyGroup']['properties'][$i]['uid'])){
					$module['value']['propertyGroup']['properties'][$i]['uid'] = md5(microtime().$module['value']['propertyGroup']['properties'][$i]['propertyName']);
				}
			}
			for($i=0;$i < count($module['value']['relationGroup']['relations']);$i++){
				// don't save empty relations
				if(empty($module['value']['relationGroup']['relations'][$i]['relationName'])){
					unset($module['value']['relationGroup']['relations'][$i]);
					t3lib_div::devlog('Unset called:'.$i,'extbase',0,$jsonConfig);
				}
				else if(empty($module['value']['relationGroup']['relations'][$i]['uid'])){
					$module['value']['relationGroup']['relations'][$i]['uid'] = md5(microtime().$module['value']['relationGroup']['relations'][$i]['relationName']);
				}
			}
		}
		return $jsonConfig;
	}

}
?>