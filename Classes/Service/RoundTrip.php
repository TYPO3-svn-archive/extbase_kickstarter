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
class Tx_ExtbaseKickstarter_Service_RoundTrip implements t3lib_singleton {
	
	
	protected $previousExtension = NULL;
	
	/**
	 * if an extension was renamed this property keeps the original extensionDirectory
	 * otherwise it is set to the current extensionDir
	 * 
	 * @var string path
	 */
	protected $previousExtensionDirectory;
	
	/**
	 * the directory of the current extension
	 * @var string path
	 */
	protected $extensionDirectory;
	
	
	/**
	 * if an extension was renamed this property keeps the old key
	 * otherwise it is set to the current extensionKey
	 * 
	 * @var string
	 */
	protected $previousExtensionKey;
	
	protected $oldDomainObjects = array();
	
	protected $renamedDomainObjects = array();
	
	/**
	 * @var Tx_ExtbaseKickstarter_Utility_ClassParser
	 */
	protected $classParser;

	
	/**
	 * was the extension renamed?
	 * 
	 * @var boolean
	 */
	protected $extensionRenamed = false;
	
	
	/**
	 * If a JSON file is found in the extensions directory the previous version
	 * of the extension is build to compare it with the new configuration coming 
	 * from the kickstarter input
	 * 
	 * @param Tx_ExtbaseKickstarter_Domain_Model_Extension $extension
	 */
	public function initialize(Tx_ExtbaseKickstarter_Domain_Model_Extension $extension) {
		$this->extension = $extension;
		$this->extensionDirectory =  $this->extension->getExtensionDir();
		$this->extClassPrefix = 'Tx_' . Tx_Extbase_Utility_Extension::convertLowerUnderscoreToUpperCamelCase($this->extension->getExtensionKey());
		
		if (Tx_ExtbaseKickstarter_Utility_Compatibility::compareFluidVersion('1.2.0', '<')) {
			$this->classParser = t3lib_div::makeInstance('Tx_ExtbaseKickstarter_Utility_ClassParser');
			$this->settings = Tx_Extbase_Dispatcher::getExtbaseFrameworkConfiguration();
		}
		
		// defaults
		$this->previousExtensionDirectory = $this->extensionDirectory;
		$this->previousExtensionKey = $this->extension->getExtensionKey();
		
		$originalExtensionKey = $extension->getOriginalExtensionKey();
		
		if(!empty($originalExtensionKey) && $originalExtensionKey != $this->extension->getExtensionKey()){
			$this->previousExtensionDirectory = PATH_typo3conf.'ext/'.$originalExtensionKey.'/';
			$this->previousExtensionKey = $originalExtensionKey;
			$this->extensionRenamed = true;
			t3lib_div::devlog('Extension renamed: ' . $originalExtensionKey .' => ' . $this->extension->getExtensionKey() ,'extbase_kickstarter',1);
		}
		
		if(file_exists($this->previousExtensionDirectory . 'kickstarter.json')){
			$objectSchemaBuilder = t3lib_div::makeInstance('Tx_ExtbaseKickstarter_ObjectSchemaBuilder');
			$jsonConfig =  json_decode(file_get_contents($this->previousExtensionDirectory . 'kickstarter.json'),true);
			//t3lib_div::devlog('old JSON:'.$this->previousExtensionDirectory . 'kickstarter.json','extbase_kickstarter',0,$jsonConfig);
			$this->previousExtension = $objectSchemaBuilder->build($jsonConfig);
			$oldDomainObjects = $this->previousExtension->getDomainObjects();
			foreach($oldDomainObjects as $oldDomainObject){
				$this->oldDomainObjects[$oldDomainObject->getUniqueIdentifier()] = $oldDomainObject;
			}
			
			// now we store all renamed domainObjects in an array to enable detection of renaming in 
			// relationProperties (property->getForeignClass)
			// we also build an array with the new unique identifiers to detect deleting of domainObjects
			$currentDomainsObjects = array();
			foreach($this->extension->getDomainObjects() as $domainObject){
				if(isset($this->oldDomainObjects[$domainObject->getUniqueIdentifier()])){
					if($this->updateExtensionKey($this->oldDomainObjects[$domainObject->getUniqueIdentifier()]->getName()) != $domainObject->getName()){
						$renamedDomainObjects[$domainObject->getUniqueIdentifier()] = $domainObject;
					}
				}
				$currentDomainsObjects[$domainObject->getUniqueIdentifier()] = $domainObject;
			}
			// remove deleted objects
			foreach($oldDomainObjects as $oldDomainObject){
				if(!isset($currentDomainsObjects[$oldDomainObject->getUniqueIdentifier()])){
					$this->removeDomainObjectFiles($oldDomainObject);
				}
			}
		}
		spl_autoload_register('Tx_ExtbaseKickstarter_Utility_ClassLoader::loadClass',false,true);
	}
	

	/**
	 * @param Tx_ExtbaseKickstarter_Utility_ClassParser $classParser
	 * @return void
	 */
	public function injectClassParser(Tx_ExtbaseKickstarter_Utility_ClassParser $classParser) {
		$this->classParser = $classParser;
	}
	
	/**
	 * @param Tx_Extbase_Configuration_ConfigurationManager $configurationManager
	 * @return void
	 */
	public function injectConfigurationManager(Tx_Extbase_Configuration_ConfigurationManager $configurationManager) {
		$this->configurationManager = $configurationManager;
		$this->settings = $this->configurationManager->getConfiguration(Tx_Extbase_Configuration_ConfigurationManagerInterface::CONFIGURATION_TYPE_SETTINGS);
	}
	
	/**
	 * @param Tx_Extbase_Object_ObjectManagerInterface $objectManager
	 * @return void
	 */
	public function injectObjectManager(Tx_Extbase_Object_ObjectManagerInterface $objectManager) {
		$this->objectManager = $objectManager;
	}
	
	/**
	 * This method is the main part of the roundtrip functionality
	 * It looks for a previous version of the current domain object and 
	 * parses the existing class file for that domain model
	 * compares all properties and methods with the previous version.
	 * 
	 * Methods are either removed/added or updated according to the new property names
	 * 
	 * 
	 * @param Tx_ExtbaseKickstarter_Domain_Model_Class $classObject The class object parsed from an existing class
	 * @param Tx_ExtbaseKickstarter_Domain_Model_DomainObject $domainObject The new domain object
	 * 
	 * @return Tx_ExtbaseKickstarter_Domain_Model_Class OR NULL
	 */
	public function getDomainModelClass(Tx_ExtbaseKickstarter_Domain_Model_DomainObject $currentDomainObject){
		if(isset($this->oldDomainObjects[$currentDomainObject->getUniqueIdentifier()])){
			t3lib_div::devlog('domainObject identified:'.$currentDomainObject->getName(),'extbase_kickstarter',0);
			$oldDomainObject = $this->oldDomainObjects[$currentDomainObject->getUniqueIdentifier()];
			$extensionDir = $this->previousExtensionDirectory;
			$fileName = Tx_ExtbaseKickstarter_Service_CodeGenerator::getFolderForClassFile($extensionDir,'Model',false).$oldDomainObject->getName().'.php';
			if(file_exists($fileName)){
				t3lib_div::devLog('Filename of existing class:'.$fileName, 'extbase_kickstarter',0);
				// import the classObject from the existing file
				include_once($fileName);
				$className = $oldDomainObject->getClassName();
				$this->classObject  = $this->classParser->parse($className);
				//t3lib_div::devlog('Model class methods','extbase_kickstarter',0,$this->classObject->getMethods());
				if($oldDomainObject->getName() != $currentDomainObject->getName() || $this->extensionRenamed){
					if(!$this->extensionRenamed)t3lib_div::devlog('domainObject renamed. old: '.$oldDomainObject->getName().' new: '.$currentDomainObject->getName(),'extbase_kickstarter');
					
					$newClassName = $currentDomainObject->getClassName();
					$this->classObject->setName($newClassName);
					$this->classObject->setFileName($currentDomainObject->getName().'.php');
					$this->cleanUp( Tx_ExtbaseKickstarter_Service_CodeGenerator::getFolderForClassFile($extensionDir,'Model'),$oldDomainObject->getName().'.php');
					$this->cleanUp( $extensionDir.'Configuration/TCA/',$oldDomainObject->getName().'.php');
					
				}
				
				$this->updateModelClassProperties($oldDomainObject,$currentDomainObject);

				$newActions = array();
				foreach($currentDomainObject->getActions() as $newAction){
					$newActions[$newAction->getName()] = $newAction;
				}
				$oldActions = $oldDomainObject->getActions();
				
				if((empty($newActions) && !$currentDomainObject->isAggregateRoot()) && (!empty($oldActions) || $oldDomainObject->isAggregateRoot())){
					// remove the controller
					$this->cleanUp(Tx_ExtbaseKickstarter_Service_CodeGenerator::getFolderForClassFile($extensionDir,'Controller'),$oldDomainObject->getName().'Controller.php');
				}
				return $this->classObject;
			}
			else {
				t3lib_div::devLog('class file didn\'t exist:'.$fileName, 'extbase_kickstarter',2);
			}
		}
		else {
			t3lib_div::devlog('domainObject not identified:'.$currentDomainObject->getName() . '(' . $currentDomainObject->getUniqueIdentifier() . ')','extbase_kickstarter',2,$this->oldDomainObjects);
		}
		return NULL;
	}
	
	/**
	 * 
	 * @param Tx_ExtbaseKickstarter_Domain_Model_DomainObject $domainObject
	 */
	public function getControllerClass(Tx_ExtbaseKickstarter_Domain_Model_DomainObject $currentDomainObject){
		if(isset($this->oldDomainObjects[$currentDomainObject->getUniqueIdentifier()])){
			$oldDomainObject = $this->oldDomainObjects[$currentDomainObject->getUniqueIdentifier()];
			$extensionDir = $this->previousExtensionDirectory;
			$fileName =  Tx_ExtbaseKickstarter_Service_CodeGenerator::getFolderForClassFile($extensionDir ,'Controller',false).$oldDomainObject->getName().'Controller.php';
			if(file_exists($fileName)){
				t3lib_div::devlog('existing controller class:'.$fileName,'extbase_kickstarter',0);
				include_once($fileName);
				$className = $oldDomainObject->getControllerName();
				$this->classObject  = $this->classParser->parse($className);
				//t3lib_div::devlog('Controller class methods','extbase_kickstarter',0,$this->classObject->getMethods());
				if($oldDomainObject->getName() != $currentDomainObject->getName() || $this->extensionRenamed){
					$newClassName = $currentDomainObject->getControllerName();
					$this->classObject->setName($newClassName);
					if($oldDomainObject->isAggregateRoot()){
						// should we keep the old properties comments and tags?
						$this->classObject->removeProperty(t3lib_div::lcfirst($oldDomainObject->getName()).'Repository');
						
						if($currentDomainObject->isAggregateRoot()){
							// update the initializeAction method body
							$initializeMethod = $this->classObject->getMethod('initializeAction');
							if($initializeMethod != NULL){
								$initializeMethodBody = $initializeMethod->getBody();
								if($currentDomainObject->isAggregateRoot()){
									$newMethodBody = str_replace($oldDomainObject->getDomainRepositoryClassName(), $currentDomainObject->getDomainRepositoryClassName(),$initializeMethodBody);
									$newMethodBody = str_replace(t3lib_div::lcfirst($oldDomainObject->getName()).'Repository', t3lib_div::lcfirst($currentDomainObject->getName()).'Repository',$newMethodBody);
									$initializeMethod->setBody($newMethodBody);
									$this->classObject->setMethod($initializeMethod);
								}
								else {
									$this->classObject->removeMethod('initializeAction');
								}
							}
						}
						else {
							$this->cleanUp(Tx_ExtbaseKickstarter_Service_CodeGenerator::getFolderForClassFile($extensionDir ,'Repository'),$oldDomainObject->getName().'Repository.php');
						}
					}
					
					$this->classObject->setFileName($currentDomainObject->getName().'Controller.php');
					$this->cleanUp( Tx_ExtbaseKickstarter_Service_CodeGenerator::getFolderForClassFile($extensionDir ,'Controller'),$oldDomainObject->getName().'Controller.php');
				}
				
				$newActions = array();
				foreach($currentDomainObject->getActions() as $newAction){
					$newActions[$newAction->getName()] = $newAction;
				}
				$oldActions = $oldDomainObject->getActions();
				if(isset($this->oldDomainObjects[$currentDomainObject->getUniqueIdentifier()])){
					// now we remove old action methods
					foreach($oldActions as $oldAction){
						if(!isset($newActions[$oldAction->getName()])){
							// an action was removed
							$this->classObject->removeMethod($oldAction->getName().'Action');
							t3lib_div::devlog('Action method removed:'.$oldAction->getName(),'extbase_kickstarter',0,$this->classObject->getMethods());
						}
					}
					// we don't have to add new ones, this will be done automatically by the class builder
				}
				
				return $this->classObject;
			}
			else {
				t3lib_div::devLog('class file didn\'t exist:'.$fileName, 'extbase_kickstarter',2);
			}
		}
		t3lib_div::devlog('No existing controller class:'.$currentDomainObject->getName(),'extbase_kickstarter',2);
		return NULL;
	}
	
	/**
	 * 
	 * @param Tx_ExtbaseKickstarter_Domain_Model_DomainObject $domainObject
	 */
	public function getRepositoryClass(Tx_ExtbaseKickstarter_Domain_Model_DomainObject $currentDomainObject){
		if(isset($this->oldDomainObjects[$currentDomainObject->getUniqueIdentifier()])){
			$oldDomainObject = $this->oldDomainObjects[$currentDomainObject->getUniqueIdentifier()];
			$extensionDir = $this->previousExtensionDirectory;
			$fileName =  Tx_ExtbaseKickstarter_Service_CodeGenerator::getFolderForClassFile($extensionDir ,'Repository',false).$oldDomainObject->getName().'Repository.php';
			if(file_exists($fileName)){
				t3lib_div::devlog('existing Repository class:'.$fileName,'extbase_kickstarter',0);
				include_once($fileName);
				$className = $oldDomainObject->getDomainRepositoryClassName();
				$this->classObject  = $this->classParser->parse($className);
				//t3lib_div::devlog('Repository class methods','extbase_kickstarter',0,$this->classObject->getMethods());
				if($oldDomainObject->getName() != $currentDomainObject->getName() || $this->extensionRenamed){
					$newClassName = $currentDomainObject->getDomainRepositoryClassName();
					$this->classObject->setName($newClassName);
					$this->classObject->setFileName($currentDomainObject->getName().'_Repository.php');
					$this->cleanUp( Tx_ExtbaseKickstarter_Service_CodeGenerator::getFolderForClassFile($extensionDir ,'Repository'),$oldDomainObject->getName().'Repository.php');
				}
				return $this->classObject;
			}
			else {
				t3lib_div::devLog('class file didn\'t exist:'.$fileName, 'extbase_kickstarter',2);
			}
		}
		t3lib_div::devlog('No existing Repository class:'.$currentDomainObject->getName(),'extbase_kickstarter',2);
		return NULL;
	}
	
	/**
	 * Compare the properties of each object and remove/update 
	 * the properties and the related methods
	 * 
	 * @param Tx_ExtbaseKickstarter_Domain_Model_DomainObject $oldDomainObject
	 * @param Tx_ExtbaseKickstarter_Domain_Model_DomainObject $newDomainObject
	 * 
	 * return void (all actions are performed on $this->classObject
	 */
	protected function updateModelClassProperties($oldDomainObject,$newDomainObject){
		$newProperties = array();
		foreach($newDomainObject->getProperties() as $property){
			$newProperties[$property->getUniqueIdentifier()] = $property;
		}
		//t3lib_div::devlog('properties new:','extbase_kickstarter',0,$newProperties);
		
		// compare all old properties with new ones
		foreach($oldDomainObject->getProperties() as $oldProperty){
			if(isset($newProperties[$oldProperty->getUniqueIdentifier()])){
				
				$newProperty = $newProperties[$oldProperty->getUniqueIdentifier()];
				
				// relation type changed
				if($oldProperty->isAnyToManyRelation() != $newProperty->isAnyToManyRelation()){
					t3lib_div::devlog('property type changed:'.$oldProperty->getName().' '.$newProperty->getName(),'extbase_kickstarter',0,$newProperties);
					// remove old methods since we won't convert getter and setter methods to add/remove methods
					if($oldProperty->isAnyToManyRelation()){
						$this->classObject->removeMethod('add'.ucfirst(Tx_ExtbaseKickstarter_Utility_Inflector::singularize($oldProperty->getName())));
						$this->classObject->removeMethod('remove'.ucfirst(Tx_ExtbaseKickstarter_Utility_Inflector::singularize($oldProperty->getName())));
					}
					else{
						$this->classObject->removeMethod('get'.ucfirst(Tx_ExtbaseKickstarter_Utility_Inflector::singularize($oldProperty->getName())));
						$this->classObject->removeMethod('set'.ucfirst(Tx_ExtbaseKickstarter_Utility_Inflector::singularize($oldProperty->getName())));
						if ($oldProperty->isBoolean()){
							$this->classObject->removeMethod('is'.ucfirst(Tx_ExtbaseKickstarter_Utility_Inflector::singularize($oldProperty->getName())));
						}
					}
					$this->classObject->removeProperty($oldProperty->getName());
					t3lib_div::devlog('property type changed => removed old property:'.$oldProperty->getName(),'extbase_kickstarter',1);
				}
				else {
					$this->updateProperty($oldProperty,$newProperty);
				}
			}
			else {
				$this->removePropertyAndRelatedMethods($oldProperty);
			}
		}
	}
	
	protected function removePropertyAndRelatedMethods($propertyToRemove){
		$propertyName = $propertyToRemove->getName();
		$this->classObject->removeProperty($propertyName);
		if ($propertyToRemove->isAnyToManyRelation()) {
			$this->classObject->removeMethod( 'add'.ucfirst(Tx_ExtbaseKickstarter_Utility_Inflector::singularize($propertyName)));
			$this->classObject->removeMethod( 'remove'.ucfirst(Tx_ExtbaseKickstarter_Utility_Inflector::singularize($propertyName)));
			t3lib_div::devLog('Methods removed: '.'add'.ucfirst(Tx_ExtbaseKickstarter_Utility_Inflector::singularize($propertyName)), 'extbase_kickstarter');
		}
		else {
			$this->classObject->removeMethod('get'.ucfirst($propertyName));
			$this->classObject->removeMethod('set'.ucfirst($propertyName));
			if ($propertyToRemove->isBoolean()){
				$this->classObject->removeMethod('is'.ucfirst(Tx_ExtbaseKickstarter_Utility_Inflector::singularize($propertyName)));
			}
			t3lib_div::devLog('Methods removed: '.'get'.ucfirst($propertyName), 'extbase_kickstarter');
		}
	}
	
	/**
	 * Rename a property and update comment (var tag and description)
	 * @param Tx_ExtbaseKickstarter_Domain_Model_AbstractDomainObjectProperty $oldProperty
	 * @param Tx_ExtbaseKickstarter_Domain_Model_AbstractDomainObjectProperty $newProperty
	 * 
	 * @return void
	 */
	protected function updateProperty($oldProperty,$newProperty){
		$classProperty = $this->classObject->getProperty($oldProperty->getName());
		if($classProperty){
			$classProperty->setName($newProperty->getName());
			$classProperty->setTag('var',$newProperty->getTypeForComment().' $'.$newProperty->getName());
			$newDescription = $newProperty->getDescription();
			if(empty($newDescription) || $newDescription == $newProperty->getName()){
				$newDescription = str_replace($oldProperty->getName(),$newProperty->getName(),$classProperty->getDescription());
			}
			$classProperty->setDescription($newDescription);
			$this->classObject->removeProperty($oldProperty->getName());
			$this->classObject->setProperty($classProperty);
			if($this->relatedMethodsNeedUpdate($oldProperty,$newProperty)){
				$this->updatePropertyRelatedMethods($oldProperty,$newProperty);
			}
		}
	}
	
	/**
	 * 
	 * @param Tx_ExtbaseKickstarter_Domain_Model_AbstractDomainObjectProperty $oldProperty
	 * @param Tx_ExtbaseKickstarter_Domain_Model_AbstractDomainObjectProperty $newProperty
	 * 
	 * @return boolean
	 */
	protected function relatedMethodsNeedUpdate($oldProperty,$newProperty){
		if($this->extensionRenamed){
			return true;
		}
		if($newProperty->getName() != $this->updateExtensionKey($oldProperty->getName())){
			t3lib_div::devlog('property renamed:'.$this->updateExtensionKey($oldProperty->getName()).' '.$newProperty->getName(),'extbase_kickstarter',0,$this->classObject->getProperties());
			return true;
		}
		if($newProperty->getTypeForComment() != $this->updateExtensionKey($oldProperty->getTypeForComment())){
			//TODO: 
			t3lib_div::devlog('property type changed from '.$this->updateExtensionKey($oldProperty->getTypeForComment()).' to '.$newProperty->getTypeForComment(),'extbase_kickstarter',0);
			return true;
		}
		if($newProperty->isRelation()){
			// if only the related domain object was renamed
			if($this->getForeignClass($newProperty)->getClassName() != $this->updateExtensionKey($oldProperty->getForeignClass()->getClassName())){
				t3lib_div::devlog('related domainObject was renamed:'.$this->updateExtensionKey($oldProperty->getForeignClass()->getClassName()) .' ->' .$this->getForeignClass($newProperty)->getClassName(),'extbase_kickstarter');
				return true;
			}
		}
	}
	
	/**
	 * replace occurences of the old extension key with the new one
	 * used to compare classNames
	 * @param $stringToParse
	 * @return unknown_type
	 */
	protected function updateExtensionKey($stringToParse){
		if(!$this->extensionRenamed){
			return $stringToParse;
		}
		return str_replace(ucfirst($this->previousExtensionKey),ucfirst($this->extension->getExtensionKey()),$stringToParse);
	}
	
	/**
	 * 
	 * @param Tx_ExtbaseKickstarter_Domain_Model_AbstractDomainObjectProperty $oldProperty
	 * @param Tx_ExtbaseKickstarter_Domain_Model_AbstractDomainObjectProperty $newProperty
	 */
	protected function updatePropertyRelatedMethods($oldProperty,$newProperty){
		if($newProperty->isAnyToManyRelation()){
			$this->updateMethod($oldProperty,$newProperty,'add');
			$this->updateMethod($oldProperty,$newProperty,'remove');
		}
		else {
			$this->updateMethod($oldProperty,$newProperty,'get');
			$this->updateMethod($oldProperty,$newProperty,'set');
			if ($newProperty->isBoolean()){
				$this->updateMethod($oldProperty,$newProperty,'is');
			}
		}
		if($newProperty->getTypeForComment() != $this->updateExtensionKey($oldProperty->getTypeForComment())){
			if($oldProperty->isBoolean() && !$newProperty->isBoolean()){
				$this->classObject->removeMethod(Tx_ExtbaseKickstarter_ClassBuilder::getMethodName($oldProperty,'is'));
				t3lib_div::devlog('Method removed:'.Tx_ExtbaseKickstarter_ClassBuilder::getMethodName($oldProperty,'is'),'extbase_kickstarter',1,$this->classObject->getMethods());
			}
		}
	}
	
	/**
	 * update means renaming of method name, parameter and replacing parameter names in method body
	 * 
	 * @param Tx_ExtbaseKickstarter_Domain_Model_AbstractDomainObjectProperty $oldProperty
	 * @param Tx_ExtbaseKickstarter_Domain_Model_AbstractDomainObjectProperty $newProperty
	 * @param string $methodType get,set,add,remove,is
	 */
	protected function updateMethod($oldProperty,$newProperty,$methodType){
		
		$oldMethodName = Tx_ExtbaseKickstarter_ClassBuilder::getMethodName($oldProperty,$methodType);
		$mergedMethod = $this->classObject->getMethod($oldMethodName);
		if(!$mergedMethod){
			// no previous version of the method exists
			return;
		}
		$newMethodName = Tx_ExtbaseKickstarter_ClassBuilder::getMethodName($newProperty,$methodType);
		t3lib_div::devlog('updateMethod:'.$oldMethodName.'=>'.$newMethodName,'extbase_kickstarter');
		
		if($oldProperty->getName() != $newProperty->getName()){
			$mergedMethod->setName($newMethodName);
			$oldMethodBody = $mergedMethod->getBody();
			
			if(trim($oldMethodBody) ==  trim(Tx_ExtbaseKickstarter_ClassBuilder::getDefaultMethodBody($oldProperty, $methodType))){
				// this means the method was not modified so we can remove it and it will be regenerated from ClassBuilder
				$this->classObject->removeMethod($oldMethodName);
				return;
			}
			$newMethodBody = $this->replacePropertyNameInString($oldMethodBody,$oldProperty->getName(),$newProperty->getName());
			$mergedMethod->setBody($newMethodBody);
		}
		
		// update the method parameters
		$methodParameters = $mergedMethod->getParameters();
		if(!empty($methodParameters)){
			foreach($methodParameters as $methodParameter){
				$oldParameterName = $methodParameter->getName();
				if($oldParameterName == $oldProperty->getName()){
					$methodParameter->setName(Tx_ExtbaseKickstarter_Utility_Inflector::singularize($newProperty->getName()));
				}
				else {
					$newParameterName = str_replace(Tx_ExtbaseKickstarter_Utility_Inflector::singularize($oldProperty->getName()),Tx_ExtbaseKickstarter_Utility_Inflector::singularize($newProperty->getName()),$oldParameterName); // TODO: str_replace is insufficient in certain cases 
					// if the extension was renamed
					$newParameterName = $this->updateExtensionKey($newParameterName);
					$methodParameter->setName($newParameterName);
					//  we have to replace the old parameter name with the new one in method body 
					$newMethodBody = $this->replacePropertyNameInString($mergedMethod->getBody(),$oldParameterName,$newParameterName);
					$mergedMethod->setBody($newMethodBody);
				}
				$typeHint = $methodParameter->getTypeHint();
				if($typeHint){
					if($oldProperty->isRelation() && $typeHint == $oldProperty->getForeignClass()->getClassName()){
						$methodParameter->setTypeHint($this->updateExtensionKey($this->getForeignClass($newProperty)->getClassName()));
					}
					t3lib_div::devlog('new typeHint:'.$this->getForeignClass($newProperty)->getClassName(),'extbase_kickstarter');
				}
				$mergedMethod->replaceParameter($methodParameter);
			}
		}
		// update the tags
		$tags = $mergedMethod->getTags();
		foreach($tags as $tagKey => $tagValue){
			//  we need to update the param tag
			if($tagKey == 'param'){
				$mergedMethod->removeTag('param');
				if(is_array($tagValue)){
					$newValues = array();
					foreach($tagValue as $v){
						if(method_exists($oldProperty,'getForeignClass')){
							$v = str_replace($oldProperty->getForeignClass()->getClassName(),$this->getForeignClass($newProperty)->getClassName(),$v);
						}
						$v = str_replace(ucfirst($oldProperty->getName()),ucfirst($newProperty->getName()),$v);
						$v = str_replace(Tx_ExtbaseKickstarter_Utility_Inflector::singularize($oldProperty->getName()),Tx_ExtbaseKickstarter_Utility_Inflector::singularize($newProperty->getName()),$v);
						$v = str_replace($oldProperty->getTypeForComment(),$newProperty->getTypeForComment(),$v);
						// replace old extensionKey in ClassNames
						$v = $this->updateExtensionKey($v);
						// replace old extensionKey in propertyNames
						$v = str_replace($this->previousExtensionKey,$this->extension->getExtensionKey(),$v);
						$newValues[] = $v;
					}
					$mergedMethod->setTag('param',$newValues);
				}
				else {
					// TODO: str_replace is insufficient in certain cases 
					if(method_exists($oldProperty,'getForeignClass')){
						$v = str_replace($oldProperty->getForeignClass()->getClassName(),$this->getForeignClass($newProperty)->getClassName(),$v);
					}
					$tagValue = str_replace($oldProperty->getName(),$newProperty->getName(),$tagValue);
					$tagValue = str_replace(ucfirst($oldProperty->getName()),ucfirst($newProperty->getName()),$tagValue);  
					$tagValue = str_replace($oldProperty->getTypeForComment(),$newProperty->getTypeForComment(),$tagValue);
					$tagValue = $this->updateExtensionKey($tagValue);
					$mergedMethod->setTag('param',$tagValue);
				}
			}
			if($tagKey == 'return'){
				$mergedMethod->removeTag('return');
				$tagValue = str_replace($oldProperty->getName(),$newProperty->getName(),$tagValue);
				$tagValue = str_replace($oldProperty->getTypeForComment(),$newProperty->getTypeForComment(),$tagValue);
				// replace old extensionKey in ClassNames
				$tagValue = $this->updateExtensionKey($tagValue);
				// replace old extensionKey in propertyNames
				$tagValue = str_replace($this->previousExtensionKey,$this->extension->getExtensionKey(),$tagValue);
				$mergedMethod->setTag('return',$tagValue);
			}
		}
		$mergedMethod->setDescription(str_replace($oldProperty->getName(),$newProperty->getName(),$mergedMethod->getDescription()));
		$this->classObject->removeMethod($oldMethodName);
		$this->classObject->addMethod($mergedMethod);
	}
	
	
	/**
	 * Replace all occurences of the old property name with the new name
	 * 
	 * @param string $string
	 * @param string $oldName
	 * @param string $newName
	 */
	protected function replacePropertyNameInString($string,$oldName,$newName){
		$regex = '/([\$|>])'.$oldName.'([^a-zA-Z0-9_])/';
		$result = preg_replace($regex, '$1'.$newName.'$2', $string);
		return $result;
	}
	
	/**
	 * if the foreign DomainObject was renamed, the relation has to be updated also
	 * 
	 * @param Tx_ExtbaseKickstarter_Domain_Model_Property_Relation_AbstractRelation $relation
	 * @return string className of foreign class
	 */
	public function getForeignClass($relation){
		if(isset($this->renamedDomainObjects[$relation->getForeignClass()->getUniqueIdentifier()])){
			$renamedObject = $this->renamedDomainObjects[$relation->getForeignClass()->getUniqueIdentifier()];
			return $renamedObject;
		}
		else return $relation->getForeignClass();
	}
	
	/**
	 * remove domainObject related files if a domainObject was deleted
	 *
	 */
	protected function removeDomainObjectFiles($domainObject){
		t3lib_div::devlog('Remove domainObject '.$domainObject->getName(),'extbase_kickstarter',0);
		$this->cleanUp(Tx_ExtbaseKickstarter_Service_CodeGenerator::getFolderForClassFile($this->previousExtensionDirectory,'Model',false),$domainObject->getName().'.php');
		$this->cleanUp( $this->previousExtensionDirectory.'Configuration/TCA/',$domainObject->getName().'.php');
		if($domainObject->isAggregateRoot()){
			$this->cleanUp(Tx_ExtbaseKickstarter_Service_CodeGenerator::getFolderForClassFile($this->previousExtensionDirectory,'Controller',false),$domainObject->getName().'Controller.php');
			$this->cleanUp(Tx_ExtbaseKickstarter_Service_CodeGenerator::getFolderForClassFile($this->previousExtensionDirectory,'Repository',false),$domainObject->getName().'Repository.php');
		}
		if(count($domainObject->getActions()) > 0){
			$this->cleanUp(Tx_ExtbaseKickstarter_Service_CodeGenerator::getFolderForClassFile($this->previousExtensionDirectory,'Controller',false),$domainObject->getName().'Controller.php');
		}
	}
	
	/**
	 * remove class files that are not required any more, due to renaming of ModelObjects or changed types
	 * @param string $path
	 * @param string $file
	 * @return unknown_type
	 */
	public function cleanUp($path,$fileName){
		if($this->extensionRenamed){
			// wo won't delete the old extension!
			return;
		}
		if(!is_file($path.$fileName)){
			t3lib_div::devLog('cleanUp File not found: '.$path.$fileName, 'extbase_kickstarter',1);
			return;
		}
		if($this->settings['roundtrip']['backupFiles']){
			if(empty($this->settings['roundtrip']['backupDir'])){
				$this->settings['roundtrip']['backupDir'] = '_bak';
			}
			t3lib_div::mkdir($this->extensionDirectory.$this->settings['roundtrip']['backupDir']);
			$backupDir = $this->extensionDirectory.$this->settings['roundtrip']['backupDir'].'/';
			if(t3lib_div::validPathStr($backupDir)){
				if(!is_dir($backupDir)){
					t3lib_div::mkdir($backupDir);
				}
				
				if(copy($path.$fileName,$backupDir.$fileName)){
					t3lib_div::fixPermissions($backupDir.$fileName);
					t3lib_div::devLog('File moved to backup: '.$backupDir.$fileName, 'extbase_kickstarter');
				}
				else {
					t3lib_div::devLog('File could not be copied to backup: '.$backupDir.$fileName, 'extbase_kickstarter',0,$this->settings);
					throw new Exception('File could not be copied to backup: '.$backupDir.$fileName);
				}
			}
			else {
				throw new Exception('Backup dir not allowed: '.$backupDir);
			}
		}
		unlink($path.$fileName);
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
	public static function getOverWriteSetting($path,$settings){
		$pathParts = explode('/',$path);
		$overWriteSettings = $settings['roundtrip']['overWriteSettings'];
		if($pathParts[0] == 'Classes'){
			if($pathParts[1] == 'Controller' && isset($overWriteSettings['Classes']['Controller'])){
				//t3lib_div::devLog('Overwrite setting for File: '.$path.'->'.$settings['Classes']['Controller'], 'extbase_kickstarter',0,$settings);
				return $overWriteSettings['Classes']['Controller'];
			}
			else if($pathParts[2] == 'Model' && isset($overWriteSettings['Classes']['Model'])){
				//t3lib_div::devLog('Overwrite setting for File: '.$path.'->'.$settings['Classes']['Model'], 'extbase_kickstarter',0,$settings);
				return $overWriteSettings['Classes']['Model'];
			}
			else if($pathParts[2] == 'Repository' && isset($overWriteSettings['Classes']['Repository'])){
				//t3lib_div::devLog('Overwrite setting for File: '.$path.'->'.$settings['Classes']['Repository'], 'extbase_kickstarter',0,$settings);
				return $overWriteSettings['Classes']['Repository'];
			}
		}
		else {
			foreach($pathParts as $pathPart){
				if(strpos($pathPart,'.')>-1){
					$fileNameParts = explode('.',$pathPart);
					if(isset($overWriteSettings[$fileNameParts[0]][$fileNameParts[1]])){
						//t3lib_div::devLog('Overwrite setting for File: '.$path.'->'.$settings[$fileNameParts[0]][$fileNameParts[1]], 'extbase_kickstarter',0,$settings);
						return $overWriteSettings[$fileNameParts[0]][$fileNameParts[1]];
					}
				}
				if(isset($overWriteSettings[$pathPart])){
					$overWriteSettings = $overWriteSettings[$pathPart];
				}
			}
		}
		
		return 0;
	}
}
?>