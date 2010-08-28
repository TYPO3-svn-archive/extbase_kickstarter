<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2010 Stephan Petzl
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
 * A class for merging extension objects
 *
 * @package ExtbaseKickstarter
 */
class Tx_ExtbaseKickstarter_Utility_ExtensionMerger {

	function   __construct() {
		
	}

	/**
	 * merges 2 extensions
	 * @param Tx_ExtbaseKickstarter_Domain_Model_Extension $extension1
	 * @param Tx_ExtbaseKickstarter_Domain_Model_Extension $extension2
	 * @return Tx_ExtbaseKickstarter_Domain_Model_Extension the merged extension
	 */
	public function mergeExtensions(Tx_ExtbaseKickstarter_Domain_Model_Extension $extension1, Tx_ExtbaseKickstarter_Domain_Model_Extension $extension2){

		$extensionMerged = new Tx_ExtbaseKickstarter_Domain_Model_Extension();
		foreach($extension1->getDomainObjects() as $domainObject1){
			$domainObject2 = $extension2->getDomainObjectByName($domainObject1->getName());
			$domainObjectMerged = $this->mergeDomainObjects($domainObject1,$domainObject2);
			$extensionMerged->addDomainObject($domainObjectMerged);
		}
		return $extensionMerged;
	}

	/**
	 * merges 2 domain objects
	 * @param Tx_ExtbaseKickstarter_Domain_Model_DomainObject $domainObject1
	 * @param Tx_ExtbaseKickstarter_Domain_Model_DomainObject $domainObject2
	 * @return Tx_ExtbaseKickstarter_Domain_Model_DomainObject the merged domainObject
	 */
	private function mergeDomainObjects(Tx_ExtbaseKickstarter_Domain_Model_DomainObject $domainObject1, Tx_ExtbaseKickstarter_Domain_Model_DomainObject $domainObject2){
		$domainObjectMerged = new Tx_ExtbaseKickstarter_Domain_Model_DomainObject($domainObject1->getClassName());
		foreach($domainObject1->getMethods() as $method1){
			$method2 = $domainObject2->getMethod($method1->getName());
			$methodMerged = $this->mergeMethods($method1,$method2);
			$domainObjectMerged->addMethod($methodMerged);
		}
		return $domainObjectMerged;
	}

	/**
	 * merges 2 methods
	 * @param Tx_ExtbaseKickstarter_Domain_Model_Class_Method $method1
	 * @param Tx_ExtbaseKickstarter_Domain_Model_Class_Method $method2
	 * @return Tx_ExtbaseKickstarter_Domain_Model_Class_Method
	 */
	private function mergeMethods(Tx_ExtbaseKickstarter_Domain_Model_Class_Method $method1, Tx_ExtbaseKickstarter_Domain_Model_Class_Method $method2){
		$methodMerged = new Tx_ExtbaseKickstarter_Domain_Model_Class_Method($method1->getName());

		foreach($method1->getParameters() as $parameter1){
			$parameter2 = $method2->getParameter($parameter1);
			$parameterMerged = $this->mergeMethodParameters($parameter1, $parameter2);
			$methodMerged->addParameter($parameterMerged);
		}
		return $methodMerged;
	}

	/**
	 *
	 * @param Tx_ExtbaseKickstarter_Domain_Model_Class_MethodParameter $parameter1
	 * @param Tx_ExtbaseKickstarter_Domain_Model_Class_MethodParameter $parameter2
	 * @return Tx_ExtbaseKickstarter_Domain_Model_Class_MethodParameter
	 */
	private function mergeMethodParameters(Tx_ExtbaseKickstarter_Domain_Model_Class_MethodParameter $parameter1, Tx_ExtbaseKickstarter_Domain_Model_Class_MethodParameter $parameter2){
		$parameterMerged = new Tx_ExtbaseKickstarter_Domain_Model_Class_MethodParameter($parameter1->getName());

		if($parameter1 == null){
			return $parameter2;
		}else if($parameter2 == null){
			return $parameter1;
		}else{
			$parameterMerged->setDefaultValue($parameter1->getDefaultValue() ? $parameter1->getDefaultValue() : $parameter2->getDefaultValue());
			$parameterMerged->setPosition($parameter1->getPosition() ? $parameter1->getPosition() : $parameter2->getPosition());
			// ...
		}
		return $parameterMerged;
	}


}
?>
