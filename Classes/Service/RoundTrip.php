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
	
	
	protected $previousExtension = NULL;
	
	protected $oldDomainObjects = array();
	
	/**
	 * 
	 * @param Tx_ExtbaseKickstarter_Domain_Model_Extension $extension
	 */
	public function __construct(Tx_ExtbaseKickstarter_Domain_Model_Extension $extension) {
		$config = Tx_Extbase_Dispatcher::getExtbaseFrameworkConfiguration();
		$this->settings = $config['settings']['roundtrip'];
		$this->classParser = t3lib_div::makeInstance('Tx_ExtbaseKickstarter_Utility_ClassParser');
		$this->extension = $extension;
		$this->extensionDirectory = PATH_typo3conf . 'ext/' . $this->extension->getExtensionKey().'/';
		if(file_exists($this->extensionDirectory . '/kickstarter.json')){
			$objectSchemaBuilder = t3lib_div::makeInstance('Tx_ExtbaseKickstarter_ObjectSchemaBuilder');
			$jsonConfig =  json_decode(file_get_contents($this->extensionDirectory . '/kickstarter.json'),true);
			
			$this->previousExtension = $objectSchemaBuilder->build($jsonConfig);
			
			$oldDomainObjects = $this->previousExtension->getDomainObjects();
			foreach($oldDomainObjects as $oldDomainObject){
				$this->domainObjects[$oldDomainObject->getUniqueIdentifier()] = $oldDomainObject;
			}
			
		}
	}
	
	public function getExistingClass($domainObject){
		if(isset($this->domainObjects[$domainObject->getUniqueIdentifier()])){
			$oldDomainObject = $this->domainObjects[$domainObject->getUniqueIdentifier()];
			$fileName = Tx_ExtbaseKickstarter_Service_CodeGenerator::getDomainModelPath().$oldDomainObject->getName().'.php';
			if(file_exists($fileName)){
				include_once($fileName);
				$className = 'Tx_' . Tx_Extbase_Utility_Extension::convertLowerUnderscoreToUpperCamelCase($this->extension->getExtensionKey()) . '_Domain_Model_' . $oldDomainObject->getName();
				$classObject = $this->classParser->parse($className);
				if($oldDomainObject->getName() != $domainObject->getName()){
					$newClassName = 'Tx_' . Tx_Extbase_Utility_Extension::convertLowerUnderscoreToUpperCamelCase($this->extension->getExtensionKey()) . '_Domain_Model_' . $domainObject->getName();
					$classObject->setName($newClassName);
				}
				$newProperties = array();
				foreach($domainObject->getProperties() as $property){
					$newProperties[$property->getUniqueIdentifier()] = $property;
				}
				foreach($oldDomainObject->getProperties() as $oldProperty){
					if(isset($newProperties[$oldProperty->getUniqueIdentifier()])){
						$newProperty = $newProperties[$oldProperty->getUniqueIdentifier()];
						
						if($oldProperty->isAnyToManyRelation() != $newProperty->isAnyToManyRelation()){
							// remove old methods since we won't convert modified getter and setter to add/remove methods
							if($oldProperty->isAnyToManyRelation()){
								$classObject->removeMethod('add'.ucfirst(Tx_ExtbaseKickstarter_Utility_Inflector::singularize($oldProperty->getName())));
								$classObject->removeMethod('remove'.ucfirst(Tx_ExtbaseKickstarter_Utility_Inflector::singularize($oldProperty->getName())));
							}
							else{
								$classObject->removeMethod('get'.ucfirst(Tx_ExtbaseKickstarter_Utility_Inflector::singularize($oldProperty->getName())));
								$classObject->removeMethod('set'.ucfirst(Tx_ExtbaseKickstarter_Utility_Inflector::singularize($oldProperty->getName())));
							}
						}
						
						else if($newProperty->getName() != $oldProperty->getName()){
							// rename methods
							if($newProperty->isAnyToManyRelation()){
								$oldMethodName = Tx_ExtbaseKickstarter_ClassBuilder::getMethodName($oldProperty.'add');
								$newMethodName = Tx_ExtbaseKickstarter_ClassBuilder::getMethodName($newProperty,'add');
								$mergedMethod = $classObject->getMethod($oldMethodName);
								$mergedMethod->setName($newMethodName);
								$oldMethodBody = $method->getBody();
								$newMethodBody = $this->replacePropertyNameInMethodBody($oldMethodBody,$oldProperty->getName(),$newProperty->getName());
								$mergedMethod->setBody($newMethodBody);
								$classObject->removeMethod($oldMethodName);
								$classObject->addMethod($mergedMethod);
							}
							
						}
					}
				}
				
				// TODO: check actions
				// TODO: check parent class
				
			}
		}
	}
	
	protected function updateMethod(){
		
	}
	
	/**
	 * Replace all occurences of the old property name with the new name
	 * 
	 * @param string $methodBody
	 * @param string $oldName
	 * @param string $newName
	 */
	protected function replacePropertyNameInMethodBody($methodBody,$oldName,$newName){
		$regex = '/([\$|>])'.$oldName.'([^a-zA-Z0-9_])/';
		$result = preg_replace($regex, '$1'.$newName.'$2', $methodBody);
		return $result;
	}
	
	/**
	 * Find the deleted properties and remove them and their getter/setter methods from the classObject
	 * @param Tx_ExtbaseKickstarter_Domain_Model_Class $classObject
	 * @param Tx_ExtbaseKickstarter_Domain_Model_DomainObject $domainObject
	 */
	public function refactorClass(Tx_ExtbaseKickstarter_Domain_Model_Class $classObject,Tx_ExtbaseKickstarter_Domain_Model_DomainObject $domainObject){
		//TODO implement Refactoring
		return $classObject;
		$this->classObject = $classObject;
		if(isset($this->domainObjects[$domainObject->getUniqueIdentifier()])){
			$oldDomainObject = $this->domainObjects[$domainObject->getUniqueIdentifier()];	
		}
		else {
			// a class with the same name was found but it was not build from the current model...
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
						$classObject->removeMethod( 'add'.ucfirst(Tx_ExtbaseKickstarter_Utility_Inflector::singularize($oldPropertyName)));
						$classObject->removeMethod( 'remove'.ucfirst(Tx_ExtbaseKickstarter_Utility_Inflector::singularize($oldPropertyName)));
						t3lib_div::devLog('Methods removed: '.'add'.ucfirst(Tx_ExtbaseKickstarter_Utility_Inflector::singularize($oldPropertyName)), 'extbase_kickstarter');
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
		//$addMethodName = 'add'.ucfirst(Tx_ExtbaseKickstarter_Utility_Inflector::singularize($propertyName));
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
		$settings = $this->settings['overWriteSettings'];
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