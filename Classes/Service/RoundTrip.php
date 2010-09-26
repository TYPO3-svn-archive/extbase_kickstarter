<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2010 Nico de Haen <mail@ndh-websolutions.de>
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
 * Manages roundtrip functions and settings
 *
 * @package ExtbaseKickstarter
 * @version $ID:$
 */
class Tx_ExtbaseKickstarter_Service_RoundTrip implements t3lib_Singleton {
	
	
	protected $oldExtension = NULL;
	
	public function __construct() {
		$config = Tx_Extbase_Dispatcher::getExtbaseFrameworkConfiguration();
		$this->inflector = t3lib_div::makeInstance('Tx_ExtbaseKickstarter_Utility_Inflector');
		$this->config = $config['settings']['roundtrip'];
		//PATH_typo3conf . 'ext/' . $this->extension->getExtensionKey()
	}
	
	/**
	 * 
	 * @param Tx_ExtbaseKickstarter_Domain_Model_Extension $extension
	 * @return void
	 */
	public function injectExtension(Tx_ExtbaseKickstarter_Domain_Model_Extension $extension){
		$this->extension = $extension;
		$this->extensionDirectory = PATH_typo3conf . 'ext/' . $this->extension->getExtensionKey().'/';
		if(file_exists($this->extensionDirectory . '/kickstarter.json')){
			$objectSchemaBuilder = t3lib_div::makeInstance('Tx_ExtbaseKickstarter_ObjectSchemaBuilder');
			$jsonConfig =  json_decode(file_get_contents($this->extensionDirectory . '/kickstarter.json'),true);
			
			$this->oldExtension = $objectSchemaBuilder->build($jsonConfig);
			
		}
	}
	
	/**
	 * Find the deleted properties and remove them and their getter/setter methods from the classObject
	 * @param Tx_ExtbaseKickstarter_Domain_Model_Class $classObject
	 * @param Tx_ExtbaseKickstarter_Domain_Model_DomainObject $domainObject
	 */
	public function removeDeletedProperties(Tx_ExtbaseKickstarter_Domain_Model_Class $classObject,Tx_ExtbaseKickstarter_Domain_Model_DomainObject $domainObject){
		if(!$this->oldExtension){
			t3lib_div::devLog('No old Extension', 'extbase_kickstarter');
			return;
		}
		
		$newPropertyNames = array();
		foreach($domainObject->getProperties() as $newProperty){
			$newPropertyNames[] = $newProperty->getName();
		}
		t3lib_div::devLog('$newPropertyNames: ', 'extbase_kickstarter',0,$newPropertyNames);
		
		$oldDomainObject = $this->oldExtension->getDomainObjectByName($domainObject->getName());
		
		if($oldDomainObject){
			foreach($oldDomainObject->getProperties() as $oldProperty){
				$oldPropertyName = $oldProperty->getName();
				$newProperty = $domainObject->getPropertyByName($oldPropertyName);
				if(!$newProperty || ($oldProperty->getTypeHint() != $newProperty->getTypeHint())){
					// the property was removed or the relation type changed 
					$classObject->removeProperty($oldPropertyName);
					if (is_subclass_of($oldProperty, 'Tx_ExtbaseKickstarter_Domain_Model_Property_Relation_AnyToManyRelation')) {
						$classObject->removeMethod( 'add'.ucfirst($this->inflector->singularize($oldPropertyName)));
						$classObject->removeMethod( 'remove'.ucfirst($this->inflector->singularize($oldPropertyName)));
						t3lib_div::devLog('Methods removed: '.'add'.ucfirst($this->inflector->singularize($oldPropertyName)), 'extbase_kickstarter');
					}
					else {
						$classObject->removeMethod('get'.ucfirst($oldPropertyName));
						$classObject->removeMethod('set'.ucfirst($oldPropertyName));
						t3lib_div::devLog('Methods removed: '.'get'.ucfirst($oldPropertyName), 'extbase_kickstarter');
					}
				}
			}
		}
		else t3lib_div::devLog('No old Domainobject: '.$domainObject->getName(), 'extbase_kickstarter');
		
		return $classObject;
		//$addMethodName = 'add'.ucfirst($this->inflector->singularize($propertyName));
	}
	
	/**
	 * finds a related typoscript setting to a path
	 * and returns the overWrite setting
	 * 0 for overwrite
	 * 1 for merge (if possible)
	 * 2 for keep existing file
	 * 
	 * @param string $path Example: Configuration/TpoScript/setup.txt
	 * @return int overWriteSetting
	 */
	public function getOverWriteSetting($path){
		$pathParts = explode('/',$path);
		$settings = $this->config['overWriteSettings'];
		if($pathParts[0] == 'Classes'){
			if($pathParts[1] == 'Controller' && isset($settings['Classes']['Controller'])){
				return $settings['Classes']['Controller'];
			}
			else if($pathParts[2] == 'Model' && isset($settings['Classes']['Model'])){
				return $settings['Classes']['Model'];
			}
			else if($pathParts[2] == 'Repository' && isset($settings['Classes']['Repository'])){
				return $settings['Classes']['Repository'];
			}
		}
		else {
			//'Configuration/TypoScript/setup.txt'
			foreach($pathParts as $pathPart){
				if(strpos($pathPart,'.')>-1){
					$fileNameParts = explode('.',$pathPart);
					if(isset($settings[$fileNameParts[0]][$fileNameParts[1]])){
						return $settings[$fileNameParts[0]][$fileNameParts[1]];
					}
				}
				if(isset($settings[$pathPart])){
					$settings = $settings[$pathPart];
				}
			}
		}
		
		return 0;
	}
	
}
?>