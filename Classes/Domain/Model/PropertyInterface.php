<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2010 Nico de Haen <typo3@ndh-websolutions.de>
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

Interface Tx_ExtbaseKickstarter_Domain_Model_PropertyInterface {
	
	/**
	 * Get SQL Definition to be used inside CREATE TABLE.
	 *
	 * @retrun string the SQL definition
	 */
	abstract public function getSqlDefinition();
	
		/**
	 * Template Method which should return the type hinting information
	 * being used in PHPDoc Comments.
	 * Examples: integer, string, Tx_FooBar_Something, Tx_Extbase_Persistence_ObjectStorage<Tx_FooBar_Something>
	 *
	 * @return string
	 */
	abstract public function getTypeForComment();

	/**
	 * Template method which should return the PHP type hint
	 * Example: Tx_Extbase_Persistence_ObjectStorage, array, Tx_FooBar_Something
	 *
	 * @return string
	 */
	abstract public function getTypeHint();
}


?>