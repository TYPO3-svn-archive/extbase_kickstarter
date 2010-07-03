<?php

/***************************************************************
*  Copyright notice
*
*  (c) 2010 Stephan Petzl <stephan.petzl@ajado.com>, ajado
*  			Christian Kartnig <office@hahnepeter.de>
*  			Sebastian Kurfürst <sebastian@typo3.org>
*  			
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
 * Box
 *
 * @version $Id$
 * @copyright Copyright belongs to the respective authors
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class Tx_TestExtension_Domain_Model_Box extends Tx_Extbase_DomainObject_AbstractEntity {
	
	/**
	 * width
	 * @validate NotEmpty
	 * @var integer
	 */
	protected $width;
	
	/**
	 * height
	 * @var integer
	 */
	protected $height;
	
	
	
	/**
	 * Setter for width
	 *
	 * @param integer $width width
	 * @return void
	 */
	public function setWidth($width) {
		$this->width = $width;
	}

	/**
	 * Getter for width
	 *
	 * @return integer width
	 */
	public function getWidth() {
		return $this->width;
	}
	
	/**
	 * Setter for height
	 *
	 * @param integer $height height
	 * @return void
	 */
	public function setHeight($height) {
		$this->height = $height+50;
	}

	/**
	 * Getter for height
	 *
	 * @return integer height
	 */
	public function getHeight() {
		return $this->height;
	}

	public function myMethod() {
		return "hello my own method!";
	}
	
}
?>