<?php
/***************************************************************
 *  Copyright notice
 *
*  (c) 2010 Nico de Haen
 *  All rights reserved
 *
 *  This class is a backport of the corresponding class of FLOW3.
 *  All credits go to the v5 team.
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

require_once('BaseTestCase.php');

class Tx_ExtbaseKickstarter_ImportTool_testcase extends Tx_ExtbaseKickstarter_BaseTestCase {


	public function setUp() {
		$this->objectSchemaBuilder = $this->getMock($this->buildAccessibleProxy('Tx_ExtbaseKickstarter_ObjectSchemaBuilder'), array('dummy'));
	}
	
	/**
	 * Import a basic class from a file 
	 * @test
	 */
	public function TestBasicClassImport(){
		require_once(t3lib_extmgm::extPath('extbase_kickstarter') . 'Tests/Examples/BasicClass.php');
		$this->importClass('Tx_ExtbaseKickstarter_Tests_Examples_BasicClass');
	}
	
	/**
	 * Import a complex class from a file 
	 * @test
	 */
	public function TestComplexClassImport(){
		require_once(t3lib_extmgm::extPath('extbase_kickstarter') . 'Tests/Examples/ComplexClass.php');
		$classObject = $this->importClass('Tx_ExtbaseKickstarter_Tests_Examples_ComplexClass');
		$getters = $classObject->getGetters();
		$this->assertEquals(1, count($getters));
		$firstGetter = array_pop($getters);
		$this->assertEquals('getName', $firstGetter->getName());
		/**  here we could include some more tests
		$p = $classObject->getMethod('methodWithStrangePrecedingBlock')->getPrecedingBlock();
		$a = $classObject->getAppendedBlock();
		*/
	}
	
	/**
	 * Import a basic class from a file 
	 * @test
	 */
	public function TestExtendedClassImport(){
		$this->importClass('Tx_ExtbaseKickstarter_Controller_KickstarterModuleController');
	}
	
	/**
	 * Import a complex class from a file 
	 * @test
	 */
	public function TestAnotherComplexClassImport(){
		require_once(t3lib_extmgm::extPath('extbase_kickstarter') . 'Tests/Examples/AnotherComplexClass.php');
		$classObject = $this->importClass('Tx_ExtbaseKickstarter_Tests_Examples_AnotherComplexClass');
		
		/**  here we could include some more tests
		$p = $classObject->getMethod('methodWithStrangePrecedingBlock')->getPrecedingBlock();
		$a = $classObject->getAppendedBlock();
		*/
	}
	
	/**
	 * Import a big class from a file  
	 * @test
	 */
	public function Test_t3lib_div_ClassImport(){
		//require_once(t3lib_extmgm::extPath('extbase_kickstarter') . 'Tests/Examples/BasicClass.php');
		$this->importClass('t3lib_div');
	}
	
	/**
	 * 
	 * @param $className
	 * @return unknown_type
	 */
	protected function importClass($className){
		$importTool = new Tx_ExtbaseKickstarter_Utility_Import();
		$importTool->debugMode = true;
		$classObject = $importTool->importClassObjectFromFile($className);
		$this->assertTrue($classObject instanceof Tx_ExtbaseKickstarter_Domain_Model_Class);
		$classReflection = new Tx_ExtbaseKickstarter_Reflection_ClassReflection($className);
		$this->ImportFindsAllMethods($classObject,$classReflection);
		$this->ImportFindsAllProperties($classObject,$classReflection);
		
		return $classObject;
	}
	
	/**
	 * compares the number of methods found by parsing with those retrieved from the reflection class
	 * @param Tx_ExtbaseKickstarter_Domain_Model_Class $classObject
	 * @param Tx_ExtbaseKickstarter_Reflection_ClassReflection $classReflection
	 * @return void
	 */
	public function ImportFindsAllMethods($classObject,$classReflection){
		$reflectionMethodCount = count($classReflection->getNotInheritedMethods());
		$classObjectMethodCount = count($classObject->getMethods());
		$this->assertEquals($classObjectMethodCount, $reflectionMethodCount, 'Not all Methods were imported!');
	}
	
	/**
	 * compares the number of properties found by parsing with those retrieved from the reflection class
	 * @param Tx_ExtbaseKickstarter_Domain_Model_Class $classObject
	 * @param Tx_ExtbaseKickstarter_Reflection_ClassReflection $classReflection
	 * @return void
	 */
	public function ImportFindsAllProperties($classObject,$classReflection){
		$reflectionPropertyCount = count($classReflection->getNotInheritedProperties());
		$classObjectPropertCount = count($classObject->getProperties());
		$this->assertEquals($classObjectPropertCount, $reflectionPropertyCount, 'Not all Properties were imported!');
		
	}

}