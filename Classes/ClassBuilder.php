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
	 * The current class object 
	 * @var Tx_ExtbaseKickstarter_Domain_Model_Class
	 */
	protected $classObject = NULL;
	
	/**
	 * This line is added to the constructor if there are storage objects to initialize
	 * @var string
	 */
	protected $initStorageObjectCall = "//Do not remove this line: It would break the functionality\n\$this->initStorageObjects();";
	
	/**
	 * 
	 * @param Tx_ExtbaseKickstarter_Domain_Model_Extension $extension
	 * @return void
	 */
	public function __construct(Tx_ExtbaseKickstarter_Domain_Model_Extension $extension){
		$this->extension = $extension;
		$this->extensionDirectory = PATH_typo3conf . 'ext/' . $this->extension->getExtensionKey().'/';
		
		$this->classParser = t3lib_div::makeInstance('Tx_ExtbaseKickstarter_Utility_ClassParser');
		$this->roundTripService =  t3lib_div::makeInstance('Tx_ExtbaseKickstarter_Service_RoundTrip',$extension);
		
		$config = Tx_Extbase_Dispatcher::getExtbaseFrameworkConfiguration();
		$this->settings = $config['settings']['classBuilder'];
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
	public function generateModelClassObject($domainObject){
		
		//t3lib_div::devLog(serialize($this->settings), 'extbase_kickstarter');
		$domainObjectClassFile = $this->extensionDirectory.'Classes/Domain/Model/' . $domainObject->getName() . '.php';
		$className = 'Tx_' . Tx_Extbase_Utility_Extension::convertLowerUnderscoreToUpperCamelCase($this->extension->getExtensionKey()) . '_Domain_Model_' . $domainObject->getName();
		
		// is there already a class file? 
		if( $this->roundTripService->getOverWriteSetting('Classes/Domain/Model/' . $domainObject->getName() . '.php') != 0 && $this->roundTripService->modelClassExists($domainObject)){
			t3lib_div::devLog('Class '.$className.' was modified', 'extbase_kickstarter',0,array($domainObjectClassFile));
			include_once($domainObjectClassFile);
			try {
				// import the classObject from the existing file
				$this->classObject = $this->classParser->parse($className);
				$this->classObject = $this->roundTripService->refactorClass($this->classObject,$domainObject);
			}
			catch(Exception $e){
				t3lib_div::devLog('Class '.$className.' could not be imported: '.$e->getError(), 'extbase_kickstarter');		
			}
				
		}
		// 
		else {
			// otherwise instantiate a new one and set the required attributes
			$this->classObject = new Tx_ExtbaseKickstarter_Domain_Model_Class($className);
			$this->classObject->setFileName($domainObjectClassFile);
			if($domainObject->isEntity()){
				// set the parent class from TS configuration
				if(!empty($this->settings['Model']['entityParentClass'])){
					if(strpos($this->settings['Model']['entityParentClass'],'Tx_')!==0){
						$parentClass = 'Tx_'.Tx_Extbase_Utility_Extension::convertLowerUnderscoreToUpperCamelCase($this->extension->getExtensionKey()) . '_Model_'. $this->settings['Model']['entityParentClass'];
					}
					else {
						$parentClass = $this->settings['Model']['entityParentClass'];
					}
				}
				else $parentClass = 'Tx_Extbase_DomainObject_AbstractEntity';
				$this->classObject->setParentClass($parentClass);
			}
			else {
				if(!empty($this->settings['Model']['valueObjectParentClass'])){
					if(strpos($this->settings['Model']['valueObjectParentClass'],'Tx_')!==0){
						$parentClass = 'Tx_'.Tx_Extbase_Utility_Extension::convertLowerUnderscoreToUpperCamelCase($this->extension->getExtensionKey()) . '_Model_'. $this->settings['Model']['valueObjectParentClass'];
					}
					else {
						$parentClass = $this->settings['Model']['valueObjectParentClass'];
					}
				}
				else $parentClass = 'Tx_Extbase_DomainObject_AbstractValueObject';
				$this->classObject->setParentClass($parentClass);
			}
		}
		
		if(!$this->classObject->hasDescription()){
			$this->classObject->setDescription($domainObject->getDescription());
		}
		
		
		
		$anyToManyRelationProperties = $domainObject->getAnyToManyRelationProperties();
		
		if(!$this->classObject->methodExists('__constructor')){
			$constructorMethod = new Tx_ExtbaseKickstarter_Domain_Model_Class_Method('__constructor');
			$constructorMethod->setDescription('The constructor of this '.$domainObject->getName());
			if(count($anyToManyRelationProperties) > 0){
				$constructorMethod->setBody($this->initStorageObjectCall);
			}
			$constructorMethod->addModifier('public');
			$constructorMethod->setTag('return','void');
			$this->classObject->addMethod($constructorMethod);
		}
		
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
			$this->classObject->setMethod($initStorageObjectsMethod);
		}
		else if($this->classObject->methodExists('initStorageObjects')){
			$this->classObject->getMethod('initStorageObjects')->setBody('// empty');
		}
		//TODO the following part still needs some enhancement: 
		//what should be obligatory in existing properties and methods
		foreach ($domainObject->getProperties() as $domainProperty) {
			$propertyName = $domainProperty->getName();
			// add the property to class Object (or update an existing class Object property)
			if($this->classObject->propertyExists($propertyName)){
				$classProperty = $this->classObject->getProperty($propertyName);
				$classPropertyTags = $classProperty->getTags();
			}
			else {
				$classProperty = new Tx_ExtbaseKickstarter_Domain_Model_Class_Property($propertyName);
				$classProperty->setTag('var',$domainProperty->getTypeForComment());
				$classProperty->addModifier('protected');
			}
			
			$classProperty->setAssociatedDomainObjectProperty($domainProperty);
			
			$this->classObject->setProperty($classProperty);
			
			$this->setPropertyRelatedMethods($domainProperty);
		}
		
		return $this->classObject;
	}
	
	/**
	 * add all setter/getter/adder etc. methods
	 * @param Tx_ExtbaseKickstarter_Domain_Model_AbstractDomainObjectProperty $domainProperty
	 */
	protected function setPropertyRelatedMethods($domainProperty){
		if (is_subclass_of($domainProperty, 'Tx_ExtbaseKickstarter_Domain_Model_Property_Relation_AnyToManyRelation')) {
			$addMethod = $this->buildAddMethod($domainProperty);
			$removeMethod = $this->buildRemoveMethod($domainProperty);
			$this->classObject->setMethod($addMethod);
			$this->classObject->setMethod($removeMethod);
		}
		else {
			$getMethod = $this->buildGetterMethod($domainProperty);
			$setMethod = $this->buildSetterMethod($domainProperty);
			$this->classObject->setMethod($getMethod);
			$this->classObject->setMethod($setMethod);
		}
	}
	
	
	/**
	 * 
	 * @param Tx_ExtbaseKickstarter_Domain_Model_AbstractDomainObjectProperty $domainProperty
	 */
	protected function buildGetterMethod($domainProperty){
		
		// add (or update) a getter method
		$getterMethodName = $this->getMethodName($domainProperty,'get');
		if($this->classObject->methodExists($getterMethodName)){
			$getterMethod = $this->classObject->getMethod($getterMethodName);
			$getterMethodTags = $getterMethod->getTags();
		}
		else {
			$getterMethod = new Tx_ExtbaseKickstarter_Domain_Model_Class_Method($getterMethodName);
			// default method body
			$getterMethod->setBody($this->getDefaultMethodBody($domainProperty,'get'));
			$getterMethod->setTag('return',strtolower($domainProperty->getTypeForComment()).' $'.$domainProperty->getName());
			$getterMethod->addModifier('public');
		}
		if(!$getterMethod->hasDescription()){
			$getterMethod->setDescription('Returns the '.$domainProperty->getName());
		}
		return $getterMethod;
	}
	
	/**
	 * 
	 * @param Tx_ExtbaseKickstarter_Domain_Model_AbstractDomainObjectProperty $domainProperty
	 */
	protected function buildSetterMethod($domainProperty){
		
		$propertyName = $domainProperty->getName();
		// add (or update) a setter method
		$setterMethodName = $this->getMethodName($domainProperty,'set');
		if($this->classObject->methodExists($setterMethodName)){
			$setterMethod = $this->classObject->getMethod($setterMethodName);
			$setterMethodTags = $setterMethod->getTags();
		}
		else {
			$setterMethod = new Tx_ExtbaseKickstarter_Domain_Model_Class_Method($setterMethodName);
			// default method body
			$setterMethod->setBody($this->getDefaultMethodBody($domainProperty,'set'));
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
		return $setterMethod;
	}
	
	/**
	 * 
	 * @param Tx_ExtbaseKickstarter_Domain_Model_AbstractDomainObjectProperty $domainProperty
	 */
	protected function buildAddMethod($domainProperty){

		$propertyName = $domainProperty->getName();
		
		$addMethodName = $this->getMethodName($domainProperty,'add');
		
		if($this->classObject->methodExists($addMethodName)){
			$addMethod = $this->classObject->getMethod($addMethodName);
		}
		else {
			$addMethod = new Tx_ExtbaseKickstarter_Domain_Model_Class_Method($addMethodName);
			// default method body
			$addMethod->setBody($this->getDefaultMethodBody($domainProperty,'add'));
			$addMethod->setTag('param',$domainProperty->getForeignClass()->getClassName().' $'.Tx_ExtbaseKickstarter_Utility_Inflector::singularize($propertyName));
			$addMethod->setTag('return','void');
			$addMethod->addModifier('public');
		}
		$addParameters = $addMethod->getParameterNames();
		
		if(!in_array(Tx_ExtbaseKickstarter_Utility_Inflector::singularize($propertyName),$addParameters)){
			$addParameter = new Tx_ExtbaseKickstarter_Domain_Model_Class_MethodParameter(Tx_ExtbaseKickstarter_Utility_Inflector::singularize($propertyName));
			$addParameter->setVarType($domainProperty->getForeignClass()->getClassName());
			$addParameter->setTypeHint($domainProperty->getForeignClass()->getClassName());
			$addMethod->setParameter($addParameter);
		}
		if(!$addMethod->hasDescription()){
			$addMethod->setDescription('Adds a '.ucfirst($domainProperty->getForeignClass()->getName()));
		}
		return $addMethod;
	}
	
	/**
	 * 
	 * @param Tx_ExtbaseKickstarter_Domain_Model_AbstractDomainObjectProperty $domainProperty
	 */
	protected function buildRemoveMethod($domainProperty){
		
		$propertyName = $domainProperty->getName();
		
		$removeMethodName = $this->getMethodName($domainProperty,'remove');
		
		if($this->classObject->methodExists($removeMethodName)){
			$removeMethod = $this->classObject->getMethod($removeMethodName);
		}
		else {
			$removeMethod = new Tx_ExtbaseKickstarter_Domain_Model_Class_Method($removeMethodName);
			// default method body
			$removeMethod->setBody($this->getDefaultMethodBody($domainProperty,'remove'));
			$removeMethod->setTag('param',$domainProperty->getForeignClass()->getClassName().' $'.Tx_ExtbaseKickstarter_Utility_Inflector::singularize($propertyName).'ToRemove The '.$domainProperty->getForeignClass()->getName().' to be removed');
			$removeMethod->setTag('return','void');
			$removeMethod->addModifier('public');
		}
		
		$removeParameters = $removeMethod->getParameterNames();
		
		if(!in_array(Tx_ExtbaseKickstarter_Utility_Inflector::singularize($propertyName),$removeParameters)){
			$removeParameter = new Tx_ExtbaseKickstarter_Domain_Model_Class_MethodParameter(Tx_ExtbaseKickstarter_Utility_Inflector::singularize($propertyName).'ToRemove');
			$removeParameter->setVarType($domainProperty->getForeignClass()->getClassName());
			$removeParameter->setTypeHint($domainProperty->getForeignClass()->getClassName());
			$removeMethod->setParameter($removeParameter);
		}
		
		if(!$removeMethod->hasDescription()){
			$removeMethod->setDescription('Removes a '.ucfirst($domainProperty->getForeignClass()->getName()));
		}
		return $removeMethod;
	}
	
	/**
	 * Builds a method that checks the current boolean state of a property
	 * 
	 * @param Tx_ExtbaseKickstarter_Domain_Model_AbstractDomainObjectProperty $domainProperty
	 */
	protected function buildIsMethod($domainProperty){
		
		$isMethodName = $this->getMethodName($domainProperty,'is');
		
		if($this->classObject->methodExists($isMethodName)){
			$isMethod = $this->classObject->getMethod($isMethodName);
		}
		else {
			$isMethod = new Tx_ExtbaseKickstarter_Domain_Model_Class_Method($isMethodName);
			// default method body
			$isMethod->setBody($this->getDefaultMethodBody($domainProperty,'is'));
			$isMethod->setTag('return','boolean');
			$isMethod->addModifier('public');
		}
		
		if(!$isMethod->hasDescription()){
			$isMethod->setDescription('Returns the boolean state of '.$property->getName());
		}
		return $isMethod;
	}
	
	/**
	 * 
	 * @param Tx_ExtbaseKickstarter_Domain_Model_AbstractDomainObjectProperty $property
	 * @param string $methodPrefix (get,set,add,remove,is)
	 * @return string method name
	 */
	public static function getMethodName($domainProperty, $methodPrefix){
		$propertyName = $domainProperty->getName();
		switch($methodPrefix){
			case 'set'		: return 'set'.ucfirst($propertyName);
			
			case 'get'		: return 'get'.ucfirst($propertyName);
			
			case 'add'		: return 'add'.ucfirst(Tx_ExtbaseKickstarter_Utility_Inflector::singularize($propertyName));
			
			case 'remove'	: return 'remove'.ucfirst(Tx_ExtbaseKickstarter_Utility_Inflector::singularize($propertyName));
			
			case 'is'		: return 'is'.ucfirst($propertyName);
		}
	}
	
	/**
	 * 
	 * @param Tx_ExtbaseKickstarter_Domain_Model_AbstractDomainObjectProperty $property
	 * @param string $methodType (get,set,add,remove,is)
	 * @return string method body
	 */
	public static function getDefaultMethodBody($domainProperty, $methodType){
		
		$propertyName = $domainProperty->getName();
		
		switch($methodType){
			
			case 'set'		: return "\t\$this->".$propertyName." = \$".$propertyName.";\n";
			
			case 'get'		: return "\treturn \$this->".$propertyName.";\n";
			
			case 'add'		: return "\t\$this->".$propertyName."->attach(\$".Tx_ExtbaseKickstarter_Utility_Inflector::singularize($propertyName).");\n";
			
			case 'remove'	: return "\t\$this->".$propertyName."->detach(\$".Tx_ExtbaseKickstarter_Utility_Inflector::singularize($propertyName)."ToRemove);";
			
			case 'is'		: return "\treturn \$this->get".ucfirst($propertyName)."();\n";
		}
	}
	
	/**
	 * This method generates the class object, which is passed to the template
	 * it keeps all methods and properties including user modified method bodies and 
	 * comments that are required to create a controller class file
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
				$this->classObject = $this->classParser->parse($className);
			}
			catch(Exception $e){
				t3lib_div::devLog('Class '.$className.' could not be imported: '.$e->getError(), 'extbase_kickstarter');		
			}				
		}
		else {
			$this->classObject = new Tx_ExtbaseKickstarter_Domain_Model_Class($className);
			$this->classObject->setFileName($controllerClassFile);
			// get parent class from config
			if(!empty($this->settings['Controller']['parentClass'])){
				if(strpos($this->settings['Controller']['parentClass'],'Tx_')!==0){
					$parentClass = 'Tx_'.Tx_Extbase_Utility_Extension::convertLowerUnderscoreToUpperCamelCase($this->extension->getExtensionKey()) . '_Controller_'. $this->settings['Controller']['parentClass'];
				}
				else {
					$parentClass = $this->settings['Controller']['parentClass'];
				}
			}
			else $parentClass = 'Tx_Extbase_MVC_Controller_ActionController';
			$this->classObject->setParentClass($parentClass);
			$this->classObject->setDescription('Controller for '.$domainObject->getName());
		}
		
		if($domainObject->isAggregateRoot()){
			$propertyName = t3lib_div::lcfirst($domainObject->getName()).'Repository';
			//$domainObject->getDomainRepositoryClassName();
			// now add the property to class Object (or update an existing class Object property)
			if(!$this->classObject->propertyExists($propertyName)){
				$classProperty = new Tx_ExtbaseKickstarter_Domain_Model_Class_Property($propertyName);
				$classProperty->setTag('var',$domainObject->getDomainRepositoryClassName());
				$classProperty->addModifier('protected');
				$this->classObject->setProperty($classProperty);
			}
			$initializeMethodName = 'initializeAction';
			if(!$this->classObject->methodExists($initializeMethodName)){
				$initializeMethod = new Tx_ExtbaseKickstarter_Domain_Model_Class_Method($initializeMethodName);
				$initializeMethod->setDescription('Initializes the current action');
				$initializeMethod->setBody('$this->'.t3lib_div::lcfirst($domainObject->getName()).'Repository = t3lib_div::makeInstance('.$domainObject->getDomainRepositoryClassName().');');
				$initializeMethod->setTag('return','void');
				$initializeMethod->addModifier('public');
				$this->classObject->addMethod($initializeMethod);
			}
		}
		
		foreach($domainObject->getActions() as $action){
			$actionMethodName = $action->getName().'Action';
			$actionMethod = new Tx_ExtbaseKickstarter_Domain_Model_Class_Method($actionMethodName);
			$actionMethod->setDescription('action '.$action->getName());
			$actionMethod->setBody('');
			$actionMethod->setTag('return','string The rendered ' . $action->getName() .' action');
			$actionMethod->addModifier('public');
			
			$this->classObject->addMethod($actionMethod);
		}
		return $this->classObject;
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
				$this->classObject = $this->classParser->parse($className);
			}
			catch(Exception $e){
				t3lib_div::devLog('Class '.$className.' could not be imported: '.$e->getError(), 'extbase_kickstarter');		
			}		
		}
		else {
			$this->classObject = new Tx_ExtbaseKickstarter_Domain_Model_Class($className);
			$this->classObject->setFileName($repositoryClassFile);
			if(!empty($this->settings['Repository']['parentClass'])){
				$parentClass = $this->settings['Repository']['parentClass'];
			}
			else {
				$parentClass = 'Tx_Extbase_Persistence_Repository';
			}
			$this->classObject->setParentClass($parentClass);
			$this->classObject->setDescription('Repository for '.$domainObject->getName());
		}
		
		
		return $this->classObject;
	}
	
}
?>