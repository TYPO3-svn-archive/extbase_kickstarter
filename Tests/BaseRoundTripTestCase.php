<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2009 Jochen Rau <jochen.rau@typoplanet.de>
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

abstract class Tx_ExtbaseKickstarter_BaseRoundTripTestCase extends Tx_ExtbaseKickstarter_BaseTestCase {
	
	function setUp(){
		$this->extension = $this->getMock('Tx_ExtbaseKickstarter_Domain_Model_Extension',array('getExtensionDir'));
		$extensionKey = 'dummy';
		$dummyExtensionDir = PATH_typo3conf.'ext/extbase_kickstarter/Tests/Examples/'.$extensionKey.'/';
		if(!is_dir($dummyExtensionDir)){
			t3lib_div::mkdir_deep(PATH_typo3conf.'ext/extbase_kickstarter/Tests/Examples/',$extensionKey);
		}
		$this->extension->setExtensionKey($extensionKey);
		$this->extension->expects($this->any())
             ->method('getExtensionDir')
             ->will($this->returnValue($dummyExtensionDir));
             
        $this->roundTripService =  $this->getMock($this->buildAccessibleProxy('Tx_ExtbaseKickstarter_Service_RoundTrip'),array('dummy'),array($this->extension));
        $this->classBuilder = $this->getMock('Tx_ExtbaseKickstarter_ClassBuilder',array('dummy'),array($this->extension));
        
        $this->codeGenerator = $this->getMock($this->buildAccessibleProxy('Tx_ExtbaseKickstarter_Service_CodeGenerator'),array('dummy'));
        $this->codeGenerator->conf = array('settings'=>array('enableRoundtrip'=>'1'));
	}
	
	
	/**
	 * Helper function
	 * @param $name
	 * @param $entity
	 * @param $aggregateRoot
	 * @return object Tx_ExtbaseKickstarter_Domain_Model_DomainObject
	 */
	protected function buildDomainObject($name, $entity = false, $aggregateRoot = false){
		$domainObject = $this->getMock($this->buildAccessibleProxy('Tx_ExtbaseKickstarter_Domain_Model_DomainObject'),array('dummy'));
		$domainObject->setExtension($this->extension);
		$domainObject->setName($name);
		$domainObject->setEntity($entity);
		$domainObject->setAggregateRoot($aggregateRoot);
		return $domainObject;
	}

}




