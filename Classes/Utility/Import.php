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
 * provides methods to import a class schema 
 *
 * @package ExtbaseKickstarter
 * @version $ID:$
 */
class Tx_ExtbaseKickstarter_Utility_Import {
	  
	public $debugMode = true;
	
	public $methodRegex = "/\s*function\s*([a-zA-Z0-9_]*)/";

	public $propertyRegex = "/\s*\\$([a-zA-Z0-9]*)/";
	
	//TODO this regex still needs some improvement
	// the second round brackets should find all kind of strings, i.e. " 'test' " and ' "test" '
	public $constantRegex = "/\s*const\s*([a-zA-Z0-9]*)\s*\=\s*\'*\"*([^;\"']*)'*\"*;/";
	
	// TODO parse definitions of namespaces
	public $namespaceRegex = "/^namespace|^use|^declare/";
	
	public $includeRegex = "/(require_once|require|include_once|include)+\s*\(([^;]*)\)/";
	
	// TODO parse definitions of constants
	public $defineRegex = "/define+\s*\(([a-zA-Z0-9_-,\\'\"\s]*)/";

	/**
	 * builds a classSchema from a className, you have to require_once before importing the class
	 * @param string $className
	 * @return Tx_ExtbaseKickstarter_Domain_Model_Class_Schema 
	 */
	public function importClassSchemaFromFile($className){
		
		$this->starttime = microtime(true); // for profiling
		
		if(!class_exists($className)){
			throw new Exception('Class not exists: '.$className);
		} 
		
		$classSchema = new Tx_ExtbaseKickstarter_Domain_Model_Class_Schema($className);
		
		$classReflection = new Tx_ExtbaseKickstarter_Reflection_ClassReflection($className);
		
		$propertiesToMap = array('FileName','Modifiers','Tags');
		
		foreach($propertiesToMap as $propertyToMap){
			// these are all "value objects" so there is no need to parse them
			$getterMethod = 'get'.$propertyToMap;
			$setterMethod = 'set'.$propertyToMap;
			
			$classSchema->$setterMethod($classReflection->$getterMethod());
		}
		
		$file = $classReflection->getFileName();
		$fileHandler = fopen($file,'r');
		
		if(!$fileHandler){
			throw new Exception('Could not open file: '.$file);
		} 
		
		/**
		 *  tokenizer might be useful in case of errors during parsing lines
		 *  but slows down the whole process by a factor between 5 to 20 ...
		 *  

		$fContent = fread($fileHandler,filesize($file));
		$tokenArr = array();
		$token = token_get_all($fContent);
		foreach($token as $t){
			if(count($t)==3){
				if(!isset($tokenArr[$t[2]]))$tokenArr[$t[2]] = array();
				$t['name'] = token_name($t[0]);
				$tokenArr[$t[2]][] = $t; 
			}
			
		}
		*/
		
		/**
		 * various flags used during parsing process
		 */
		
		$isSingleLineComment = false; 
		$isMultilineComment = false;
		$isMethodBody = false;
		
		// the Tx_ExtbaseKickstarter_Reflection_MethodReflection returned from ClassReflection
		$currentMethodReflection = NULL; 
		
		 // the new created Tx_ExtbaseKickstarter_Domain_Model_ClassMethod
		$currentClassMethod = NULL;
		
		// remember the last line that matched either a property or a method end
		$lastMatchedLine = 0; 
		$currentMethodEndLine = 0;
		
		$lines = array();
		$lineCount = 1;
		// keep all lines above a property or a method and save it in the precedingBlock property
		while(!feof($fileHandler)){
			$line = fgets($fileHandler);
			
			//$token = token_get_all($line);
			//$token['name'] = token_name($token[0]);
			//t3lib_div::print_array($token);
			//$line = str_replace('<?php','',$line);
			$trimmedLine = trim($line);
			
			if($lineCount == $classReflection->getStartLine()){
				$classPreComment = '';
				foreach($lines as $lN => $lContent){
					if(strlen(trim($lContent))>0){
						$classPreComment .= $lContent;
					}
				}
				$classSchema->setPrecedingBlock($classPreComment);
				
				$lastMatchedLine = $lineCount;
			}
			
			if(!empty($trimmedLine) && !$isMethodBody){
				
				// end of multiline comment found (maybe this part could be better solved with tokenizer?
				if(strrpos($line,'*/')>-1){
					if(strrpos($line,'/**')>-1){
						// if a multiline comment starts in the same line after a multiline comment end
						$isMultilineComment = (strrpos($line,'/**') > strrpos($line,'*/'));
					}
					else $isMultilineComment = false;
				}
				else if(strrpos($line,'/**')>-1){
					// multiline comment start
					$isMultilineComment = true;
				}
			
				// single comment line
				if(!$isMultilineComment && preg_match("/^\s*\/\\//",$line)){
					$isSingleLineComment = true;
				}
				else {
					$isSingleLineComment = false;
				}
				
				if(!$isSingleLineComment && !$isMultilineComment && !empty($trimmedLine)){
					// if not in a comment we look for a method or property
					//
					$methodMatches = array();
					$propertyMatches = array();
					$constantMatches = array();
					
					if(preg_match_all($this->methodRegex,$trimmedLine,$methodMatches)){
						// a method was found
						$isMethodBody = true;
						$methodName = $methodMatches[1][0];
						
						try{
							// the method has to exist in the classReflection
							$currentMethodReflection = $classReflection->getMethod($methodMatches[1][0]);
							if($currentMethodReflection){
								
								$precedingBlock = $this->concatLinesFromArray($lines,$lastMatchedLine);
								
								$currentClassMethod = new Tx_ExtbaseKickstarter_Domain_Model_Class_Method($methodName,$currentMethodReflection);
								$currentClassMethod->setPrecedingBlock($precedingBlock);
								$currentClassMethod->setTags($currentMethodReflection->getTags());
								
								$currentMethodEndLine = $currentMethodReflection->getEndline();
							}
							else {
								throw new Tx_ExtbaseKickstarter_Exception_ParseError(
										'Method '. $methodName . ' does not exist. Parsed from line '.$lineCount . 'in '. $classReflection->getFileName()
									);
							}
						}
						catch(ReflectionException $e){
							// ReflectionClass throws an exception if a method was not found
							t3lib_div::print_array('Exception: '.$e->getMessage());
						}
						
					} // end of pregmatch "function"
					
					if(!$isMethodBody){
						if(preg_match_all($this->constantRegex,$trimmedLine,$constantMatches)){
						//if(preg_match_all($this->constantRegex,$trimmedLine,$constantMatches)){
							
							preg_match_all($this->constantRegex,$trimmedLine,$constantMatches);
							for($i = 0;$i< count($constantMatches[0]);$i++){
								try{
									$constantName = $constantMatches[1][$i];
									// the constant has to exist in the classReflection
									$reflectionConstantValue = $classReflection->getConstant($constantName);
									//var_dump($reflectionConstant);
									$classSchema->setConstant($constantName,$reflectionConstantValue);
								}
								catch(ReflectionException $e){
									// ReflectionClass throws an exception if a property was not found
									t3lib_div::print_array('Exception in line : '.$e->getMessage().' Constant '.$propertyName.' found in line '.$lineCount);
								}
							}
						}
						if(preg_match_all($this->propertyRegex,$trimmedLine,$propertyMatches)){
							// a property (or multiple) was found
							$propertyNames = $propertyMatches[1];
							$isFirstProperty = true;
							foreach($propertyNames as $propertyName){
								try{
									// the property has to exist in the classReflection
									$reflectionProperty = $classReflection->getProperty($propertyName);
									if($reflectionProperty){

										//TODO we need to create the right property here (for each type: e.g. BooleanProperty...), how could this be done?
										$classProperty = new Tx_ExtbaseKickstarter_Domain_Model_AbstractGenericProperty($propertyName);
										$classProperty->mapToReflectionProperty($reflectionProperty);
										
										if($isFirstProperty){
											// only the first property will get the preceding block assigned
											$precedingBlock = $this->concatLinesFromArray($lines,$lastMatchedLine);
											$classProperty->setPrecedingBlock($precedingBlock);
											$isFirstProperty = false;
										}
										
										$classSchema->addProperty($classProperty);
										$lastMatchedLine = $lineCount;
									}
									else {
										throw new Tx_ExtbaseKickstarter_Exception_ParseError(
												'Property '. $propertyName . ' does not exist. Parsed from line '.$lineCount . 'in '. $classReflection->getFileName()
											);
									}
								}
								catch(ReflectionException $e){
									// ReflectionClass throws an exception if a property was not found
									t3lib_div::print_array('Exception in line : '.$e->getMessage().'Property '.$propertyName.' found in line '.$lineCount);
								}
							}
						} // end of pregmatch "property"
						
						
						
						
					} // end of not in method body
					$includeMatches = array();
					if( preg_match_all($this->includeRegex,$line,$includeMatches)){
						//preg_match_all($this->includeRegex,$trimmedLine,$includeMatches);
						foreach($includeMatches[2] as $include){
							$classSchema->addInclude($include);
						}
					}
				} // end of not in comment
				
			} // end of not empty and not in method body
			else if($isMethodBody && $lineCount == ($currentMethodEndLine -1)){
				
				$methodBodyStartLine = $currentMethodReflection->getStartLine();
				$methodBody = $this->concatLinesFromArray($lines,$methodBodyStartLine);
				$methodBody .= $line;
				
				$currentClassMethod->setBody($methodBody);
				$classSchema->addMethod($currentClassMethod);
				$currentMethodEndLine = 0;
				// end of a method body
				$isMethodBody = false;
				$lastMatchedLine = $lineCount;
				//TODO what if a method is defined in the same line as the preceding method ends? Should be checked with tokenizer?	
				
			}
			
			$lines[$lineCount] = $line;
			$lineCount++;
			
			
		} // end while feof

		if($lineCount > $classReflection->getEndLine()){
			$appendedBlock = $this->concatLinesFromArray($lines,$classReflection->getEndLine());
			$appendedBlock = str_replace('?>','',$appendedBlock);
			$classSchema->setAppendedBlock($appendedBlock);
		}
		if($this->debugMode){
			if(count($classSchema->getMethods()) != count($classReflection->getNotInheritedMethods())){
				debug('Errorr: method count does not match: '.count($classSchema->getMethods()).' methods found, should be '.count($classReflection->getNotInheritedMethods()));
				debug($classSchema->getMethods());
				debug($classReflection->getNotInheritedMethods());
			}
			if(count($classSchema->getProperties()) != count($classReflection->getNotInheritedProperties())){
				debug('Error: property count does not match:'.count($classSchema->getProperties()).' properties found, should be '.count($classReflection->getNotInheritedProperties()));
				debug($classSchema->getProperties());
				debug($classReflection->getNotInheritedProperties());
			}
			
			$info = $classSchema->getInfo();
			
			$this->endtime = microtime(true);
	    	$totaltime = $this->endtime - $this->starttime;
	    	$totaltime = round($totaltime,5);
	    	
	    	$info['Parsetime:'] = $totaltime.' s';
			
	    	debug($classSchema->getInfo());
		}

		return $classSchema;
	}

		/**
	 * 
	 * @param array $lines
	 * @param int $start
	 * @param int $end (optional)
	 * @return string concatenated lines
	 */
	public function concatLinesFromArray($lines,$start,$end = NULL){
		$result = '';
		foreach($lines as $lineNumber => $lineContent){
			if($end && $lineNumber == $end){
				return $result;
			}
			if($lineNumber > $start){
				$result .= $lineContent;
			}
		}
		return $result;
	}
}

?>