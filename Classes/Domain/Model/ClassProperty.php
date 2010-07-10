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
 * property representing a "property" in the context of software development
 *
 * @package ExtbaseKickstarter
 * @version $ID:$
 */
class Tx_ExtbaseKickstarter_Domain_Model_ClassProperty extends Tx_ExtbaseKickstarter_Domain_Model_AbstractSchema{
		
	
	
	public function __construct($propertyName,$propertyReflection = NULL){
		$this->name = $propertyName;
		
		if($propertyReflection instanceof Tx_ExtbaseKickstarter_Reflection_PropertyReflection){
			foreach($this as $key => $value) {
				$setterMethodName = 'set'.t3lib_div::underscoredToUpperCamelCase($key);
				$getterMethodName = 'get'.t3lib_div::underscoredToUpperCamelCase($key);
				
	    		// map properties of reflection class to this class
				if(method_exists($propertyReflection,$getterMethodName) && method_exists($this,$setterMethodName) ){
	    			$this->$setterMethodName($propertyReflection->$getterMethodName());
	    			//t3lib_div::print_array($getterMethodName);
	    		}
			}
		}
	}
	
}
	