<?php 
/***************************************************************
*  Copyright notice
*
*  (c) 2010 Nico de Haen
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
 * Builds the required class objects for extbase extensions
 *
 * @package ExtbaseKickstarter
 */

class Tx_ExtbaseKickstarter_ClassBuilder  implements t3lib_Singleton {
	
	/**
	 * 
	 * @return void
	 */
	public function __construct(){
		$this->inflector = t3lib_div::makeInstance('Tx_ExtbaseKickstarter_Utility_Inflector');
		$this->classParser = t3lib_div::makeInstance('Tx_ExtbaseKickstarter_Utility_ClassParser');
	}
	
	/**
	 * 
	 * @param Tx_ExtbaseKickstarter_Domain_Model_Extension $extension
	 * @return void
	 */
	public function injectExtension(Tx_ExtbaseKickstarter_Domain_Model_Extension $extension){
		$this->extension = $extension;
		$this->extensionDirectory = PATH_typo3conf . 'ext/' . $this->extension->getExtensionKey().'/';
	}
	
	//TODO the methods below need to reflect changing or deleting of elements in the kickstarter
	// currently the json provides no information about that, so it can't be processed yet
	
	/**
	 * This method generates the class schema object, which is passed to the template
	 * it keeps all methods and properties including user modified method bodies and comments 
	 * needed to create a domain object class file
	 * 
	 * @param Tx_ExtbaseKickstarter_Domain_Model_DomainObject $domainObject
	 * @return 
	 */
	public function generateModelClassObject($domainObject, $oldDomainObject= NULL){
		
		$domainObjectClassFile = $this->extensionDirectory.'Classes/Domain/Model/' . $domainObject->getName() . '.php';
		$className = 'Tx_' . Tx_Extbase_Utility_Extension::convertLowerUnderscoreToUpperCamelCase($this->extension->getExtensionKey()) . '_Domain_Model_' . $domainObject->getName();
		
		// is there already a class file? 
		if(file_exists( $domainObjectClassFile) &&  $this->extension->isModified($domainObjectClassFile)){
			t3lib_div::devLog('Class '.$className.' was modified', 'extbase_kickstarter');
			include_once($domainObjectClassFile);
			try {
				// import the classObject from the existing file
				$classObject = $this->classParser->parse($className);
			}
			catch(Exception $e){
				t3lib_div::devLog('Class '.$className.' could not be imported: '.$e->getError(), 'extbase_kickstarter');		
			}			
		}
		// 
		else {
			// otherwise instantiate a new one and set the required attributes
			$classObject = new Tx_ExtbaseKickstarter_Domain_Model_Class($className);
			$classObject->setFileName($domainObjectClassFile);
			if($domainObject->isEntity()){
				$classObject->setParentClass('Tx_Extbase_DomainObject_AbstractEntity');
			}
			else {
				$classObject->setParentClass('Tx_Extbase_DomainObject_AbstractValueObject');
			}
		}
		
		if(!$classObject->hasDescription()){
			$classObject->setDescription($domainObject->getDescription());
		}
		
		if(!$classObject->methodExists('__constructor')){
			$constructorMethod = new Tx_ExtbaseKickstarter_Domain_Model_Class_Method('__constructor');
			$constructorMethod->setDescription('The constructor of this '.$domainObject->getName());
			$constructorMethod->setBody("//Do not remove this line: It would break the functionality\n\$this->initStorageObjects();");
			$constructorMethod->addModifier('public');
			$constructorMethod->setTag('return','void');
			$classObject->addMethod($constructorMethod);
		}
		$anyToManyRelationProperties = $domainObject->getAnyToManyRelationProperties();
		if(count($anyToManyRelationProperties) > 0){
			$initStorageObjectsMethod = new Tx_ExtbaseKickstarter_Domain_Model_Class_Method('initStorageObjects');
			$initStorageObjectsMethod->setDescription('Initializes all Tx_Extbase_Persistence_ObjectStorage properties.');
			$methodBody = "/**\n* Do not modify this method!\n* It will be rewritten on each save in the kickstarter\n* You may modify the constructor of this class instead\n*/\n";
			foreach($anyToManyRelationProperties as $relationProperty){
				$methodBody .= "\$this->".$relationProperty->getName()." = new Tx_Extbase_Persistence_ObjectStorage();\n";
			}
			$initStorageObjectsMethod->setBody($methodBody);
			$initStorageObjectsMethod->addModifier('protected');
			$initStorageObjectsMethod->setTag('return','void');
			$classObject->setMethod($initStorageObjectsMethod);
		}
		//TODO the following part still needs some enhancement: 
		//what should be obligatory in existing properties and methods
		foreach ($domainObject->getProperties() as $domainProperty) {
			$propertyName = $domainProperty->getName();
					
			// add the property to class Object (or update an existing class Object property)
			if($classObject->propertyExists($propertyName)){
				$classProperty = $classObject->getProperty($propertyName);
				$classPropertyTags = $classProperty->getTags();
			}
			else {
				$classProperty = new Tx_ExtbaseKickstarter_Domain_Model_Class_Property($propertyName);
				$classProperty->setTag('var',$domainProperty->getTypeForComment());
				$classProperty->addModifier('protected');
			}
			
			$classProperty->setAssociatedDomainObjectProperty($domainProperty);
			
			$classObject->setProperty($classProperty);
			
			if (is_subclass_of($domainProperty, 'Tx_ExtbaseKickstarter_Domain_Model_Property_Relation_AnyToManyRelation')) {
				//TODO relation properties need add/remove methods	
				// add (or update) a getter method
				$addMethodName = 'add'.ucfirst($this->inflector->singularize($propertyName));
				
				if($classObject->methodExists($addMethodName)){
					$addMethod = $classObject->getMethod($addMethodName);
				}
				else {
					$addMethod = new Tx_ExtbaseKickstarter_Domain_Model_Class_Method($addMethodName);
					// default method body
					$addMethod->setBody('$this->'.$propertyName.'->attach($'.$this->inflector->singularize($propertyName).');');
					$addMethod->setTag('param',$domainProperty->getForeignClass()->getClassName().' $'.$this->inflector->singularize($propertyName));
					$addMethod->setTag('return','void');
					$addMethod->addModifier('public');
				}
				$addParameters = $addMethod->getParameterNames();
				
				if(!in_array($this->inflector->singularize($propertyName),$addParameters)){
					t3lib_div::devlog(serialize($addParameters).'--'.$this->inflector->singularize($propertyName),'extbase_kickstarter');
					$addParameter = new Tx_ExtbaseKickstarter_Domain_Model_Class_MethodParameter($this->inflector->singularize($propertyName));
					$addParameter->setVarType($domainProperty->getForeignClass()->getClassName());
					$addParameter->setTypeHint($domainProperty->getForeignClass()->getClassName());
					$addMethod->setParameter($addParameter);
				}
				if(!$addMethod->hasDescription()){
					$addMethod->setDescription('Adds a '.ucfirst($domainProperty->getForeignClass()->getName()));
				}
				$classObject->setMethod($addMethod);
				
				$removeMethodName = 'remove'.ucfirst($this->inflector->singularize($propertyName));
				
				if($classObject->methodExists($removeMethodName)){
					$removeMethod = $classObject->getMethod($removeMethodName);
				}
				else {
					$removeMethod = new Tx_ExtbaseKickstarter_Domain_Model_Class_Method($removeMethodName);
					// default method body
					$removeMethod->setBody('$this->'.$propertyName.'->detach($'.$this->inflector->singularize($propertyName).'ToRemove);');
					$removeMethod->setTag('param',$domainProperty->getForeignClass()->getClassName().' $'.$this->inflector->singularize($propertyName).'ToRemove The '.$domainProperty->getForeignClass()->getName().' to be removed');
					$removeMethod->setTag('return','void');
					$removeMethod->addModifier('public');
				}
				$removeParameters = $removeMethod->getParameterNames();
				if(!in_array($this->inflector->singularize($propertyName),$removeParameters)){
					$removeParameter = new Tx_ExtbaseKickstarter_Domain_Model_Class_MethodParameter($this->inflector->singularize($propertyName).'ToRemove');
					$removeParameter->setVarType($domainProperty->getForeignClass()->getClassName());
					$removeParameter->setTypeHint($domainProperty->getForeignClass()->getClassName());
					$removeMethod->setParameter($removeParameter);
				}
				if(!$removeMethod->hasDescription()){
					$removeMethod->setDescription('Removes a '.ucfirst($domainProperty->getForeignClass()->getName()));
				}
				$classObject->setMethod($removeMethod);
				
			}
			else {
				// add (or update) a getter method
				$getterMethodName = 'get'.ucfirst($propertyName);
				
				if($classObject->methodExists($getterMethodName)){
					$getterMethod = $classObject->getMethod($getterMethodName);
					$getterMethodTags = $getterMethod->getTags();
				}
				else {
					$getterMethod = new Tx_ExtbaseKickstarter_Domain_Model_Class_Method($getterMethodName);
					// default method body
					$getterMethod->setBody('return $this->'.$propertyName.';');
					$getterMethod->setTag('return',strtolower($domainProperty->getTypeForComment()).' $'.$propertyName);
					$getterMethod->addModifier('public');
				}
				if(!$getterMethod->hasDescription()){
					$getterMethod->setDescription('Returns the '.$propertyName);
				}
				$classObject->setMethod($getterMethod);
				
				// add (or update) a setter method
				$setterMethodName = 'set'.ucfirst($propertyName);
				
				if($classObject->methodExists($setterMethodName)){
					$setterMethod = $classObject->getMethod($setterMethodName);
					$setterMethodTags = $setterMethod->getTags();
				}
				else {
					$setterMethod = new Tx_ExtbaseKickstarter_Domain_Model_Class_Method($setterMethodName);
					// default method body
					$setterMethod->setBody('$this->'.$propertyName.' = $'.$propertyName.';');
					$setterMethod->setTag('param',strtolower($domainProperty->getTypeForComment()).' $'.$propertyName);
					$setterMethod->setTag('return','void');
					$setterMethod->addModifier('public');
				}
				if(!$setterMethod->hasDescription()){
					$setterMethod->setDescription('Sets the '.$propertyName);
				}
				$setterParameters = $setterMethod->getParameterNames();
				if(!in_array($propertyName,$setterParameters)){
					$setterParameter = new Tx_ExtbaseKickstarter_Domain_Model_Class_MethodParameter($propertyName);
					$setterParameter->setVarType($domainProperty->getTypeForComment());
					$setterMethod->setParameter($setterParameter);
				}
	
				$classObject->setMethod($setterMethod);	
			}
			
			
		
			
		}
		
		return $classObject;
	}
	
	/**
	 * This method generates the class object, which is passed to the template
	 * it keeps all methods and properties including user modified method bodies and comments 
	 * needed to create a controller class file
	 * 
	 * @param Tx_ExtbaseKickstarter_Domain_Model_DomainObject $domainObject
	 * @return 
	 */
	public function generateControllerClassObject($domainObject){
		$controllerClassFile = $this->extensionDirectory . 'Classes/Controller/' . $domainObject->getName() . 'Controller.php';
		
		$className = 'Tx_' . Tx_Extbase_Utility_Extension::convertLowerUnderscoreToUpperCamelCase($this->extension->getExtensionKey()) . '_Controller_' . $domainObject->getName().'Controller';
	
		if(file_exists( $controllerClassFile) &&  $this->extension->isModified($controllerClassFile)){
			if(!class_exists($className)){
				include_once($controllerClassFile);
			}
			
			try {
				$classObject = $this->classParser->parse($className);
			}
			catch(Exception $e){
				t3lib_div::devLog('Class '.$className.' could not be imported: '.$e->getError(), 'extbase_kickstarter');		
			}				
		}
		else {
			$classObject = new Tx_ExtbaseKickstarter_Domain_Model_Class($className);
			$classObject->setFileName($controllerClassFile);
			$classObject->setParentClass('Tx_Extbase_MVC_Controller_ActionController');
			$classObject->setDescription('Controller for '.$domainObject->getName());
		}
		
		if($domainObject->isAggregateRoot()){
			$propertyName = t3lib_div::lcfirst($domainObject->getName()).'Repository';
			//$domainObject->getDomainRepositoryClassName();
			// now add the property to class Object (or update an existing class Object property)
			if(!$classObject->propertyExists($propertyName)){
				$classProperty = new Tx_ExtbaseKickstarter_Domain_Model_Class_Property($propertyName);
				$classProperty->setTag('var',$domainObject->getDomainRepositoryClassName());
				$classProperty->addModifier('protected');
				$classObject->setProperty($classProperty);
			}
			$initializeMethodName = 'initializeAction';
			if(!$classObject->methodExists($initializeMethodName)){
				$initializeMethod = new Tx_ExtbaseKickstarter_Domain_Model_Class_Method($initializeMethodName);
				$initializeMethod->setDescription('Initializes the current action');
				$initializeMethod->setBody('$this->'.t3lib_div::lcfirst($domainObject->getName()).'Repository = t3lib_div::makeInstance('.$domainObject->getDomainRepositoryClassName().');');
				$initializeMethod->setTag('return','void');
				$initializeMethod->addModifier('public');
				$classObject->addMethod($initializeMethod);
			}
		}
		
		foreach($domainObject->getActions() as $action){
			$actionMethodName = $action->getName().'Action';
			$actionMethod = new Tx_ExtbaseKickstarter_Domain_Model_Class_Method($actionMethodName);
			$actionMethod->setDescription('action '.$action->getName());
			$actionMethod->setBody('');
			$actionMethod->setTag('return','string The rendered ' . $action->getName() .' action');
			$actionMethod->addModifier('public');
			
			$classObject->addMethod($actionMethod);
		}
		return $classObject;
	}
	
	/**
	 * This method generates the class object, which is passed to the template
	 * it keeps all methods and properties including user modified method bodies and comments 
	 * needed to create a repository class file
	 * 
	 * @param Tx_ExtbaseKickstarter_Domain_Model_DomainObject $domainObject
	 * @return 
	 */
	public function generateRepositoryClassObject($domainObject){
		$repositoryClassFile = $this->extensionDirectory . 'Classes/Domain/Repository/' . $domainObject->getName() . 'Repository.php';
		
		$className = 'Tx_' . Tx_Extbase_Utility_Extension::convertLowerUnderscoreToUpperCamelCase($this->extension->getExtensionKey()) . '_Domain_Repository_' . $domainObject->getName().'Repository';
	
		if(file_exists( $repositoryClassFile) &&  $this->extension->isModified($repositoryClassFile)){
			include_once($repositoryClassFile);
			try {
				$classObject = $this->classParser->parse($className);
			}
			catch(Exception $e){
				t3lib_div::devLog('Class '.$className.' could not be imported: '.$e->getError(), 'extbase_kickstarter');		
			}		
		}
		else {
			$classObject = new Tx_ExtbaseKickstarter_Domain_Model_Class($className);
			$classObject->setFileName($repositoryClassFile);
			$classObject->setParentClass('Tx_Extbase_Persistence_Repository');
			$classObject->setDescription('Repository for '.$domainObject->getName());
		}
		
		
		return $classObject;
	}
	
}
?>
