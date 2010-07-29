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
 * method representing a "method" in the context of software development
 *
 * @package ExtbaseKickstarter
 * @version $ID:$
 */
class Tx_ExtbaseKickstarter_Domain_Model_Class_Method extends Tx_ExtbaseKickstarter_Domain_Model_AbstractGenericSchema{
	
	/**
	 * body
	 * @var string
	 */
	protected $body;
	
	/**
	 * 
	 * @var array
	 */
	protected $parameters;
	
	
	public function __construct($methodName,$methodReflection = NULL){
		$this->name = $methodName;
		if($methodReflection instanceof Tx_ExtbaseKickstarter_Reflection_MethodReflection){
			foreach($this as $key => $value) {
				$setterMethodName = 'set'.t3lib_div::underscoredToUpperCamelCase($key);
				$getterMethodName = 'get'.t3lib_div::underscoredToUpperCamelCase($key);
	    		// map properties of reflection class to this class
				if(method_exists($methodReflection,$getterMethodName) && method_exists($this,$setterMethodName) ){
	    			
					$this->$setterMethodName($methodReflection->$getterMethodName());
	    			//t3lib_div::print_array($getterMethodName);
	    			
	    		}
			}
		}
		
	}
	
	/**
	 * Setter for body
	 *
	 * @param string $body body
	 * @return void
	 */
	public function setBody($body) {
		$this->body = $body;
	}

	/**
	 * Getter for body
	 *
	 * @return string body
	 */
	public function getBody() {
		return $this->body;
	}
	
	/**
	 * getter for parameters
	 * @return array parameters
	 */
	public function getParameters(){
		return $this->parameters;
	}
	
	/**
	 * setter for parameters
	 * @param array $parameters
	 * @return void
	 */
	public function setParameters($parameters){
		foreach($parameters as $parameter){
			$methodParameter = new Tx_ExtbaseKickstarter_Domain_Model_Class_MethodParameter($parameter->getName(),$parameter);
			$this->parameters[] = $methodParameter;
		}
		
	}
	
}

?>
