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
	
	public $defaultIndent = "\t\t";
	
	/**
	 * 
	 * @var array
	 */
	protected $parameters;
	
	
	public function __construct($methodName,$methodReflection = NULL){
		$this->setName($methodName);
		if($methodReflection instanceof Tx_ExtbaseKickstarter_Reflection_MethodReflection){
			$methodReflection->getTagsValues(); // just to initialize the docCommentParser
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
		// keep or set the indent 
		if(strpos($body,$this->defaultIndent)!==0){
			$lines = explode("\n",$body);
			$newLines = array();
			foreach($lines as $line){
				$newLines[] = $this->defaultIndent.$line;
			}
			$body = implode("\n",$newLines);
		}
		$this->body = rtrim($body);
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
	 * getter for parameter names
	 * @return array parameter names
	 */
	public function getParameterNames(){
		$parameterNames = array();
		foreach($this->parameters as $parameter){
			$parameterNames[] = $parameter->getName();
		}
		return $parameterNames;
	}

	/**
	 * adder for parameters
	 * @param array $parameters
	 * @return void
	 */
	public function setParameters($parameters){
		foreach($parameters as $parameter){
			$methodParameter = new Tx_ExtbaseKickstarter_Domain_Model_Class_MethodParameter($parameter->getName(),$parameter);
			$this->parameters[] = $methodParameter;
		}

	}

	/**
	 * setter for a single parameter
	 * @param array $parameter
	 * @return void
	 */
	public function setParameter($parameter){
		$this->parameters[] = $parameter;
	}
	
	
	public function getAnnotations(){
		$annotations = parent::getAnnotations();
		if(count($this->parameters > 0) && !$this->isTaggedWith('param')){
			foreach($this->parameters as $parameter){
				$annotations[] = 'param '.strtolower($parameter->getVarType()).' $'.$parameter->getName();
			}
		}
		if(!$this->isTaggedWith('return')){
			$annotations[] = 'return';
		}
		return $annotations;
	}
	
}

?>
