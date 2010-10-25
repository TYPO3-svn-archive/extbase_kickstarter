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
	
	protected $renamedDomainObjects = array();
	
	
	/**
	 * If a JSON file is found in the extensions directory the previous version
	 * of the extension is build to compare it with the new configuration coming 
	 * from the kickstarter input
	 * 
	 * @param Tx_ExtbaseKickstarter_Domain_Model_Extension $extension
	 */
	public function __construct(Tx_ExtbaseKickstarter_Domain_Model_Extension $extension) {
		$config = Tx_Extbase_Dispatcher::getExtbaseFrameworkConfiguration();
		$this->settings = $config['settings']['roundtrip'];
		$this->classParser = t3lib_div::makeInstance('Tx_ExtbaseKickstarter_Utility_ClassParser');
		$this->extension = $extension;
		$this->extensionDirectory =  $this->extension->getExtensionDir();
		$this->extClassPrefix = 'Tx_' . Tx_Extbase_Utility_Extension::convertLowerUnderscoreToUpperCamelCase($this->extension->getExtensionKey());
		if(file_exists($this->extensionDirectory . '/kickstarter.json')){
			$objectSchemaBuilder = t3lib_div::makeInstance('Tx_ExtbaseKickstarter_ObjectSchemaBuilder');
			$jsonConfig =  json_decode(file_get_contents($this->extensionDirectory . 'kickstarter.json'),true);
			//t3lib_div::devlog('old JSON:'.$this->extensionDirectory . 'kickstarter.json','extbase_kickstarter',0,$jsonConfig);
			$this->previousExtension = $objectSchemaBuilder->build($jsonConfig);
			
			$oldDomainObjects = $this->previousExtension->getDomainObjects();
			foreach($oldDomainObjects as $oldDomainObject){
				$this->oldDomainObjects[$oldDomainObject->getUniqueIdentifier()] = $oldDomainObject;
			}
			
			// now we store all renamed domainObjects in an array to enable detection of renaming in 
			// relationProperties (property->getForeignClass)
			foreach($this->extension->getDomainObjects() as $domainObject){
				if(isset($this->oldDomainObjects[$domainObject->getUniqueIdentifier()])){
					if($this->oldDomainObjects[$domainObject->getUniqueIdentifier()]->getName() != $domainObject->getName()){
						$renamedDomainObjects[$domainObject->getUniqueIdentifier()] = $domainObject;
					}
				}
			}
		}
		spl_autoload_register('Tx_ExtbaseKickstarter_Utility_ClassLoader::loadClass',false,true);
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
			t3lib_div::devlog('domainObjectIdentified:'.$currentDomainObject->getName(),'extbase_kickstarter');
			$oldDomainObject = $this->oldDomainObjects[$currentDomainObject->getUniqueIdentifier()];
			$fileName = Tx_ExtbaseKickstarter_Service_CodeGenerator::getFolderForClassFile($this->extensionDirectory,'Model').$oldDomainObject->getName().'.php';
			if(file_exists($fileName)){
				t3lib_div::devLog('Filename of existing class:'.$fileName, 'extbase_kickstarter');
				// import the classObject from the existing file
				include_once($fileName);
				$className = $oldDomainObject->getClassName();
				$this->classObject  = $this->classParser->parse($className);
				t3lib_div::devlog('Model class methods','extbase_kickstarter',0,$this->classObject->getMethods());
				if($oldDomainObject->getName() != $currentDomainObject->getName()){
					t3lib_div::devlog('domainObject renamed. old: '.$oldDomainObject->getName().' new: '.$currentDomainObject->getName(),'extbase_kickstarter');
					$newClassName = $currentDomainObject->getClassName();
					$this->classObject->setName($newClassName);
					$this->classObject->setFileName($currentDomainObject->getName().'.php');
					$this->cleanUp( Tx_ExtbaseKickstarter_Service_CodeGenerator::getFolderForClassFile($this->extensionDirectory,'Model'),$oldDomainObject->getName().'.php');
				}
				
				$this->updateModelClassProperties($oldDomainObject,$currentDomainObject);

				$newActions = array();
				foreach($currentDomainObject->getActions() as $newAction){
					$newActions[$newAction->getName()] = $newAction;
				}
				$oldActions = $oldDomainObject->getActions();
				
				if((empty($newActions) && !$currentDomainObject->isAggregateRoot()) && (!empty($oldActions) || $oldDomainObject->isAggregateRoot())){
					// remove the controller
					$this->cleanUp(Tx_ExtbaseKickstarter_Service_CodeGenerator::getFolderForClassFile($this->extensionDirectory,'Controller'),$oldDomainObject->getName().'Controller.php');
				}
				return $this->classObject;
			}
			else {
				t3lib_div::devLog('class file didn\'t exist:'.$fileName, 'extbase_kickstarter');
			}
		}
		else {
			t3lib_div::devlog('domainObject not identified:'.$currentDomainObject->getUniqueIdentifier(),'extbase_kickstarter',0,$this->oldDomainObjects);
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
			$fileName =  Tx_ExtbaseKickstarter_Service_CodeGenerator::getFolderForClassFile($this->extensionDirectory,'Controller').$oldDomainObject->getName().'Controller.php';
			if(file_exists($fileName)){
				t3lib_div::devlog('existing controller class:'.$fileName,'extbase_kickstarter');
				include_once($fileName);
				$className = $oldDomainObject->getControllerName();
				$this->classObject  = $this->classParser->parse($className);
				t3lib_div::devlog('Controller class methods','extbase_kickstarter',0,$this->classObject->getMethods());
				if($oldDomainObject->getName() != $currentDomainObject->getName()){
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
							$this->cleanUp(Tx_ExtbaseKickstarter_Service_CodeGenerator::getFolderForClassFile($this->extensionDirectory,'Repository'),$oldDomainObject->getName().'Repository.php');
						}
					}
					
					$this->classObject->setFileName($currentDomainObject->getName().'Controller.php');
					$this->cleanUp( Tx_ExtbaseKickstarter_Service_CodeGenerator::getFolderForClassFile($this->extensionDirectory,'Controller'),$oldDomainObject->getName().'Controller.php');
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
				t3lib_div::devLog('class file didn\'t exist:'.$fileName, 'extbase_kickstarter');
			}
		}
		t3lib_div::devlog('No existing controller class:'.$currentDomainObject->getName(),'extbase_kickstarter');
		return NULL;
	}
	
	/**
	 * 
	 * @param Tx_ExtbaseKickstarter_Domain_Model_DomainObject $domainObject
	 */
	public function getRepositoryClass(Tx_ExtbaseKickstarter_Domain_Model_DomainObject $currentDomainObject){
		if(isset($this->oldDomainObjects[$currentDomainObject->getUniqueIdentifier()])){
			$oldDomainObject = $this->oldDomainObjects[$currentDomainObject->getUniqueIdentifier()];
			$fileName =  Tx_ExtbaseKickstarter_Service_CodeGenerator::getFolderForClassFile($this->extensionDirectory,'Repository').$oldDomainObject->getName().'Repository.php';
			if(file_exists($fileName)){
				t3lib_div::devlog('existing Repository class:'.$fileName,'extbase_kickstarter');
				include_once($fileName);
				$className = $oldDomainObject->getDomainRepositoryClassName();
				$this->classObject  = $this->classParser->parse($className);
				t3lib_div::devlog('Repository class methods','extbase_kickstarter',0,$this->classObject->getMethods());
				if($oldDomainObject->getName() != $currentDomainObject->getName()){
					$newClassName = $currentDomainObject->getDomainRepositoryClassName();
					$this->classObject->setName($newClassName);
					$this->classObject->setFileName($currentDomainObject->getName().'_Repository.php');
					$this->cleanUp( Tx_ExtbaseKickstarter_Service_CodeGenerator::getFolderForClassFile($this->extensionDirectory,'Repository'),$oldDomainObject->getName().'Repository.php');
				}
				return $this->classObject;
			}
			else {
				t3lib_div::devLog('class file didn\'t exist:'.$fileName, 'extbase_kickstarter');
			}
		}
		t3lib_div::devlog('No existing Repository class:'.$currentDomainObject->getName(),'extbase_kickstarter');
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
		t3lib_div::devlog('properties new:','extbase_kickstarter',0,$newProperties);
		
		// compare all old properties with new ones
		foreach($oldDomainObject->getProperties() as $oldProperty){
			t3lib_div::devlog('properties old:'.$oldProperty->getUniqueIdentifier(),'extbase_kickstarter',0,$oldDomainObject->getProperties());
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
							$this->removeMethod('is'.ucfirst(Tx_ExtbaseKickstarter_Utility_Inflector::singularize($oldProperty->getName())));
						}
					}
					$this->classObject->removeProperty($oldProperty->getName());
					t3lib_div::devlog('property type changed => removed:'.$oldProperty->getName(),'extbase_kickstarter');
				}
				else {
					$this->updateProperty($oldProperty,$newProperty);
				}
			}
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
		if($newProperty->getName() != $oldProperty->getName()){
			t3lib_div::devlog('property renamed:'.$oldProperty->getName().' '.$newProperty->getName(),'extbase_kickstarter',0,$this->classObject->getProperties());
			return true;
		}
		if($newProperty->getTypeForComment() != $oldProperty->getTypeForComment()){
			//TODO: 
			t3lib_div::devlog('property type changed from '.$oldProperty->getTypeForComment().' to '.$newProperty->getTypeForComment(),'extbase_kickstarter',1);
			return true;
		}
		if($newProperty->isRelation()){
			// if only the related domain object was renamed
			if($oldProperty->getForeignClass()->getClassName() != $this->getForeignClass($newProperty)->getName()){
				t3lib_div::devlog('related domainObject was renamed:'.$oldProperty->getForeignClass()->getClassName() .' ->' .$this->getForeignClass($newProperty)->getClassName(),'extbase_kickstarter');
				return true;
			}
		}
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
		if($newProperty->getTypeForComment() != $oldProperty->getTypeForComment()){
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
					$methodParameter->setName($newParameterName);
					//  we have to replace the old parameter name with the new one in method body 
					$newMethodBody = $this->replacePropertyNameInString($newMethodBody,$oldParameterName,$newParameterName);
					$mergedMethod->setBody($newMethodBody);
				}
				$typeHint = $methodParameter->getTypeHint();
				if($typeHint){
					if($oldProperty->isRelation() && $typeHint == $oldProperty->getForeignClass()->getClassName()){
						$methodParameter->setTypeHint($this->getForeignClass($newProperty)->getClassName());
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
						$newValues[] = $v;
					}
					$mergedMethod->setTag('param',$newValues);
				}
				else {
					// TODO: str_replace is insufficient in certain cases 
					if(method_exists($oldProperty,'getForeignClass')){
						$v = str_replace($oldProperty->getForeignClass()->getName(),$this->getForeignClass($newProperty)->getName(),$v);
					}
					$tagValue = str_replace($oldProperty->getName(),$newProperty->getName(),$tagValue);
					$tagValue = str_replace(ucfirst($oldProperty->getName()),ucfirst($newProperty->getName()),$tagValue);  
					$tagValue = str_replace($oldProperty->getTypeForComment(),$newProperty->getTypeForComment(),$tagValue);
					$mergedMethod->setTag('param',$tagValue);
				}
			}
			if($tagKey == 'return'){
				$mergedMethod->removeTag('return');
				$tagValue = str_replace($oldProperty->getName(),$newProperty->getName(),$tagValue);
				$tagValue = str_replace($oldProperty->getTypeForComment(),$newProperty->getTypeForComment(),$tagValue);
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
	 * remove class files that are not required any more, due to renaming of ModelObjects or changed types
	 * @param string $path
	 * @param string $file
	 * @return unknown_type
	 */
	public function cleanUp($path,$fileName){
		t3lib_div::devLog('cleanUp calledd: '.$path.$fileName, 'extbase_kickstarter',0);
		if(!is_file($path.$fileName)){
			t3lib_div::devLog('cleanUp File not found: '.$path.$fileName, 'extbase_kickstarter',0);
			return;
		}
		if($this->settings['backupFiles']){
			$backupDir = t3lib_div::mkdir($this->extensionDirectory.$this->settings['backupDir']);
			if(t3lib_div::validPathStr($backupDir)){
				if(!is_dir($backupDir)){
					t3lib_div::mkdir($backupDir);
				}
				if(copy($path.$fileName,$backupDir.$fileName)){
					t3lib_div::fixPermissions($backupDir.$fileName);
					t3lib_div::devLog('File moved to backup: '.$backupDir.$fileName, 'extbase_kickstarter');
				}
				else {
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
	public function getOverWriteSetting($path){
		$pathParts = explode('/',$path);
		$settings = $this->settings['overWriteSettings'];
		if($pathParts[0] == 'Classes'){
			if($pathParts[1] == 'Controller' && isset($settings['Classes']['Controller'])){
				//t3lib_div::devLog('Overwrite setting for File: '.$path.'->'.$settings['Classes']['Controller'], 'extbase_kickstarter',0,$settings);
				return $settings['Classes']['Controller'];
			}
			else if($pathParts[2] == 'Model' && isset($settings['Classes']['Model'])){
				//t3lib_div::devLog('Overwrite setting for File: '.$path.'->'.$settings['Classes']['Model'], 'extbase_kickstarter',0,$settings);
				return $settings['Classes']['Model'];
			}
			else if($pathParts[2] == 'Repository' && isset($settings['Classes']['Repository'])){
				//t3lib_div::devLog('Overwrite setting for File: '.$path.'->'.$settings['Classes']['Repository'], 'extbase_kickstarter',0,$settings);
				return $settings['Classes']['Repository'];
			}
		}
		else {
			foreach($pathParts as $pathPart){
				if(strpos($pathPart,'.')>-1){
					$fileNameParts = explode('.',$pathPart);
					if(isset($settings[$fileNameParts[0]][$fileNameParts[1]])){
						//t3lib_div::devLog('Overwrite setting for File: '.$path.'->'.$settings[$fileNameParts[0]][$fileNameParts[1]], 'extbase_kickstarter',0,$settings);
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