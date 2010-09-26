<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2010 Nico de Haen <mail@ndh-websolutions.de>
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
 * Manages roundtrip functions and settings
 *
 * @package ExtbaseKickstarter
 * @version $ID:$
 */
class Tx_ExtbaseKickstarter_Service_RoundTrip implements t3lib_Singleton {
	
	public function __construct() {
		$config = Tx_Extbase_Dispatcher::getExtbaseFrameworkConfiguration();
		$this->config = $config['settings']['roundtrip'];
	}
	
	/**
	 * finds a related typoscript setting to a path
	 * and returns the overWrite setting
	 * 0 for overwrite
	 * 1 for merge (if possible)
	 * 2 for keep existing file
	 * 
	 * @param string $path Example: Configuration/TpoScript/setup.txt
	 * @return int overWriteSetting
	 */
	public function getOverWriteSetting($path){
		$pathParts = explode('/',$path);
		$settings = $this->config['overWriteSettings'];
		if($pathParts[0] == 'Classes'){
			if($pathParts[1] == 'Controller' && isset($settings['Classes']['Controller'])){
				return $settings['Classes']['Controller'];
			}
			else if($pathParts[2] == 'Model' && isset($settings['Classes']['Model'])){
				return $settings['Classes']['Model'];
			}
			else if($pathParts[2] == 'Repository' && isset($settings['Classes']['Repository'])){
				return $settings['Classes']['Repository'];
			}
		}
		else {
			//'Configuration/TypoScript/setup.txt'
			foreach($pathParts as $pathPart){
				if(strpos($pathPart,'.')>-1){
					$fileNameParts = explode('.',$pathPart);
					if(isset($settings[$fileNameParts[0]][$fileNameParts[1]])){
						return $settings[$fileNameParts[0]][$fileNameParts[1]];
					}
				}
				if(isset($settings[$pathPart])){
					$settings = $settings[$pathPart];
				}
			}
		}
		
		return 0;
	}
	
}
?>