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
 * class schema representing a "class" in the context of software development
 *
 * @package ExtbaseKickstarter
 * @version $ID:$
 */
class Tx_ExtbaseKickstarter_Domain_Model_Class_Schema extends Tx_ExtbaseKickstarter_Domain_Model_AbstractGenericSchema{
	
	/**
	 * constants
	 * @var array
	 */
	protected $constants;
	
	/**
	 * properties
	 * @var array
	 */
	protected $properties;
	
	/**
	 * propertieNames
	 * @var array
	 */
	protected $propertieNames;

	
	/**
	 * methods
	 * @var array
	 */
	protected $methods;
	
	/**
	 * methodNames
	 * @var array
	 */
	protected $methodNames;
	
	/**
	 * interfaceNames
	 * @var string
	 */
	protected $interfaceNames;
	
	/**
	 * all lines that were found below the class declaration
	 * @var string
	 */
	protected $appendedBlock;
	
	/**
	 * all includes (filenames) that were found in a file 
	 * currently not used
	 * includes should be preserved by writing the blocks (preceding, appended) into the new file
	 * @var array
	 */
	protected $includes;
	
	/**
	 * parentClass
	 * @var string
	 */
	//protected $parent_class;
	
	
	/**
	 * isFileBased
	 * @var boolean
	 */
	protected $isFileBased = false;
	
	
	/**
	 * the path to the file this class was defined in
	 * @var string
	 */
	protected $fileName;
	
	/**
	 * is instantiated only if the class is imported from a file
	 * @var Tx_ExtbaseKickstarter_Reflection_ClassReflection
	 */
	protected $classReflection = NULL;
	
	/**
	 * constructor of this class
	 * @param string $className
	 * @return unknown_type
	 */
	public function __construct($className){
		$this->name = $className;
	}
	
	/**
	 * Setter for a single constant
	 *
	 * @param string $constant constant
	 * @return void
	 */
	public function setConstant($constantName,$constantValue) {
		$this->constants[$constantName] = $constantValue;
	}
	
	/**
	 * Setter for constants
	 *
	 * @param string $constants constants
	 * @return void
	 */
	public function setConstants($constants) {
		$this->constants = $constants;
	}

	/**
	 * Getter for constants
	 *
	 * @return string constants
	 */
	public function getConstants() {
		return $this->constants;
	}
	
	/**
	 * Getter for a single constant
	 *
	 * @return mixed constant value
	 */
	public function getConstant($constantName) {
		if(isset($this->constants[$constantName])){
			return $this->constants[$constantName];
		}
		else return NULL;
	}
	
	
	
	/**
	 * 
	 * @return boolean 
	 */
	public function methodExists($methodName){
		if(is_array($this->methodNames) && in_array($methodName,$this->methodNames)){
			return true;
		}
		else return false;
	}
	
	/**
	 * Setter for methods
	 *
	 * @param string $methods methods
	 * @return void
	 */
	public function setMethods($methods) {
		$this->methods = $methods;
	}
	
	/**
	 * Setter for a single method (allows to override an existing method)
	 *
	 * @param Tx_ExtensionEditor_Domain_Model_ClassMethod $method
	 * @return void
	 */
	public function setMethod(Tx_ExtensionEditor_Domain_Model_ClassMethod $classMethod) {
		$this->methods[$classMethod->getName()] = $classMethod;
	}

	/**
	 * Getter for methods
	 *
	 * @return string methods
	 */
	public function getMethods() {
		return $this->methods;
	}
	
	/**
	 * Getter for method
	 *
	 * @return string methods
	 */
	public function getMethod($methodName) {
		if($this->methodExists($methodName)){
			return $this->methods[$methodName];
		}
		else return NULL;
	}
	
	/**
	 * Add a method
	 *
	 * @param Tx_ExtensionEditor_Domain_Model_ClassMethod $methods
	 * @return void
	 */
	public function addMethod($classMethod) {
		if(!$this->methodExists($classMethod->getName())){
			$this->methodNames[] = $classMethod->getName();
			$this->methods[$classMethod->getName()] = $classMethod;
		}
		
	}
	
	
	
	
	/**
	 * 
	 * @return array
	 */
	public function getGetter(){
		$allMethods = $this->getMethods();
		$getterMethods = array();
		foreach($allMethods as $method){
			$methodName = $method->getName();
			if(strpos($methodName,'get')===0){
				if($this->hasProperty(substr($methodName,3))){
					$getterMethods[] = new Tx_ExtbaseKickstarter_Domain_Model_ClassMethod($method);
				}
			}
		}
		return $getterMethods;
	}
	
	/**
	 * 
	 * @return array
	 */
	public function getSetter(){
		$allMethods = $this->getMethods();
		$setterMethods = array();
		foreach($allMethods as $method){
			$methodName = $method->getName();
			if(strpos($methodName,'set')===0){
				if($this->hasProperty(substr($methodName,3))){
					$setterMethods[] = new Tx_ExtbaseKickstarter_Domain_Model_ClassMethod($method);
				}
			}
		}
		return $setterMethods;
	}
	
	/**
	 * Getter for property
	 *
	 * @return string methods
	 */
	public function getProperty($propertyName) {
		if($this->propertyExists($propertyName)){
			return $this->properties[$propertyName];
		}
		else return NULL;
	}
	
	/**
	 * Setter for properties
	 *
	 * @param select $properties properties
	 * @return void
	 */
	public function setProperties($properties) {
		$this->properties = $properties;
	}

	/**
	 * Getter for properties
	 *
	 * @return select properties
	 */
	public function getProperties() {
		return $this->properties;
	}
	
	/**
	 * Setter for staticProperties
	 *
	 * @param string $staticProperties staticProperties
	 * @return void
	 */
	public function setStaticProperties($staticProperties) {
		$this->staticProperties = $staticProperties;
	}

	/**
	 * Getter for staticProperties
	 *
	 * @return string staticProperties
	 */
	public function getStaticProperties() {
		return $this->staticProperties;
	}
	

	
	/**
	 * 
	 * @return boolean 
	 */
	public function propertyExists($propertyName){
		if(is_array($this->propertyNames) && in_array($propertyName,$this->propertyNames)){
			return true;
		}
		else return false;
	}
	
	/**
	 * add a property (returns true if successfull added)
	 *
	 * @param Tx_ExtbaseKickstarter_Reflection_PropertyReflection
	 * @return boolean success
	 */
	public function addProperty($classProperty) {
		if(!$this->propertyExists($classProperty->getName())){
			$this->propertyNames[] = $classProperty->getName();
			$this->properties[$classProperty->getName()] = $classProperty;
		}
		else return false;
	}
	
	/**
	 * Setter for property
	 *
	 * @param Tx_ExtbaseKickstarter_Reflection_PropertyReflection
	 * @return boolean success
	 */
	public function setProperty($classProperty) {
		$this->propertyNames[] = $classProperty->getName();
		$this->properties[$classProperty->getName()] = $classProperty;
	}
	
	
	/**
	 * Setter for interfaceNames
	 *
	 * @param string $interfaceNames interfaceNames
	 * @return void
	 */
	public function setInterfaceNames($interfaceNames) {
		$this->interfaceNames = $interfaceNames;
	}

	/**
	 * Getter for interfaceNames
	 *
	 * @return string interfaceNames
	 */
	public function getInterfaceNames() {
		return $this->interfaceNames;
	}
	
	
	/**
	 * Setter for parentClass
	 *
	 * @param string $parentClass parentClass
	 * @return void
	 */
	public function setParentClass($parentClass) {
		$this->parentClass = $parentClass;
	}

	/**
	 * Getter for parentClass
	 *
	 * @return string parentClass
	 */
	public function getParentClass() {
		return $this->parentClass;
	}
	

	/**
	 * Setter for includes
	 *
	 * @param array $includes
	 * @return void
	 */
	public function setIncludes($includes) {
		$this->includes = $includes;
	}

	/**
	 * Getter for includes
	 *
	 * @return array includes
	 */
	public function getIncludes() {
		return $this->includes;
	}
	
	/**
	 * 
	 * @param $fileName
	 * @return void
	 */
	public function addInclude($fileName){
		//TODO make some checks... allowed file?
		$this->includes[] = $fileName;
	}
	
	/**
	 * Getter for fileName
	 *
	 * @return string fileName
	 */
	public function getFileName() {
		return $this->fileName;
	}
	
	/**
	 * Setter for fileName
	 * @param string $fileName
	 * @return void
	 */
	public function setFileName($fileName) {
		$this->fileName = $fileName;
	}
	
	/**
	 * getter for appendedBlock
	 * @return string $appendedBlock
	 */
	public function getAppendedBlock(){
		return $this->appendedBlock;
	}
	
	/**
	 * setter for appendedBlock
	 * @param string $appendedBlock
	 * @return void
	 */
	public function setAppendedBlock($appendedBlock){
		$this->appendedBlock = $appendedBlock;
	}
	
	public function getInfo(){
		$infoArray = array();
		$infoArray['className'] = $this->getName();
		$infoArray['fileName'] = $this->getFileName();
		$infoArray['Methods'] = $this->getMethods();
		//$infoArray['Inherited Methods'] = count($this->getInheritedMethods());
		//$infoArray['Not inherited Methods'] = count($this->getNotInheritedMethods());
		$infoArray['Properties'] = $this->getProperties();
		//$infoArray['Inherited Properties'] = count($this->getInheritedProperties());
		//$infoArray['Not inherited Properties'] = count($this->getNotInheritedProperties());
		$infoArray['Constants'] = $this->getConstants();
		$infoArray['Includes'] = $this->getIncludes();
		$infoArray['Modifiers'] = $this->getModifierNames();
		$infoArray['Tags'] = $this->getTags();
		//$infoArray['Methods'] = count($this->getMethods());
		return $infoArray;
	}

}

?>