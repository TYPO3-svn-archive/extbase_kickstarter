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
 * abstract schema representing a class, method or property in the context of 
 * software development
 *
 * @package ExtbaseKickstarter
 * @version $ID:$
 */
abstract class Tx_ExtbaseKickstarter_Domain_Model_AbstractGenericSchema {
	
	/**
	 * name
	 * @var string
	 */
	protected $name;
	
	/**
	 * modifiers  (privat, static abstract etc. not to mix up with "isModified" )
	 * @var int
	 */
	protected $modifiers;
	
	/**
	 * @var array An array of tag names and their values (multiple values are possible)
	 */
	protected $tags = array();
	
	/**
	 * docComment
	 * @var string
	 */
	protected $docComment;
	
	/**
	 * precedingBlock
	 * all lines that were found above the declaration of the current element
	 * 
	 * @var string
	 */
	protected $precedingBlock;
	
	/**
	 * isModified (this flag is set to true, if a modification of a class was detected)
	 * @var string
	 */
	protected $isModified;	
		
	
	/**
	 * Setter for name
	 *
	 * @param string $name name
	 * @return void
	 */
	public function setName($name) {
		$this->name = $name;
	}

	/**
	 * Getter for name
	 *
	 * @return string name
	 */
	public function getName() {
		return $this->name;
	}
	
	/**
	 * Checks if the doc comment of this method is tagged with
	 * the specified tag
	 *
	 * @param  string $tag: Tag name to check for
	 * @return boolean TRUE if such a tag has been defined, otherwise FALSE
	 */
	public function isTaggedWith($tagName) {
		return in_array($tagName,array_keys($this->tags));
	}

	/**
	 * Returns an array of tags and their values
	 *
	 * @return array Tags and values
	 */
	public function getTags() {
		return $this->tags;
	}

	/**
	 * sets the array of tags 
	 *
	 * @return array Tags and values
	 */
	public function setTags($tags) {
		return $this->tags = $tags;
	}
	/**
	 * Returns the values of the specified tag
	 * @return array Values of the given tag
	 */
	public function getTagsValues($tagName) {
		return $this->tags($tagName);
	}
	
	/**
	 * Setter for modifiers
	 *
	 * @param string $modifiers modifiers
	 * @return void
	 */
	public function setModifiers($modifiers) {
		$this->modifiers = $modifiers;
	}

	/**
	 * Getter for modifiers
	 *
	 * @return int modifiers
	 */
	public function getModifiers() {
		return $this->modifiers;
	}
	
	public function getModifierNames(){
		$modifiers = $this->getModifiers();
		$modifierNames = array();
		if(is_array($modifiers)){
			foreach($modifiers as $modifier){
				$modifierNames[] = array_shift(Reflection::getModifierNames($modifier));
			}
		}
		else $modifierNames[] = array_shift(Reflection::getModifierNames($modifiers));
		return $modifierNames;
	}
	

	/**
	 * Setter for docComment
	 *
	 * @param string $docComment docComment
	 * @return void
	 */
	public function setDocComment($docComment) {
		$this->docComment = $docComment;
	}

	/**
	 * Getter for docComment
	 *
	 * @return string docComment
	 */
	public function getDocComment() {
		return $this->docComment;
	}
	
	/**
	 * Setter for precedingBlock
	 *
	 * @param string $precedingBlock precedingBlock
	 * @return void
	 */
	public function setPrecedingBlock($precedingBlock) {
		$this->precedingBlock = $precedingBlock;
	}

	/**
	 * Getter for precedingBlock
	 *
	 * @return string precedingBlock
	 */
	public function getPrecedingBlock() {
		return $this->precedingBlock;
	}
	
	/**
	 * Setter for isModified 
	 *
	 * @param string $isModified isModified
	 * @return void
	 */
	public function setIsModified($isModified) {
		$this->isModified = $isModified;
	}

	/**
	 * Getter for isModified
	 *
	 * @return string isModified
	 */
	public function getIsModified() {
		return $this->isModified;
	}
	

}

?>