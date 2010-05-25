<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2009 Ingmar Schlecht <ingmar@typo3.org>
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
include_once "Text/Diff.php";
include_once "Text/Diff/Engine/native.php";
include_once "Text/Diff/ThreeWay.php";
include_once "Text/Diff/Renderer.php";
include_once "Text/Diff/Renderer/inline.php";
include_once "Text/Diff3.php";
/**
 * Backend Module of the Extbase Kickstarter extension
 *
 * @category    Controller
 * @package     TYPO3
 * @subpackage  tx_mvcextjssamples
 * @author      Ingmar Schlecht <ingmar@typo3.org>
 * @license     http://www.gnu.org/copyleft/gpl.html
 * @version     SVN: $Id$
 */
class Tx_ExtbaseKickstarter_Controller_KickstarterModuleController extends Tx_Extbase_MVC_Controller_ActionController {

	/**
	 * Holds reference to the template class
	 *
	 * @var template
	 */
	protected $doc;

	/**
	 * Holds reference to t3lib_SCbase
	 *
	 * @var t3lib_SCbase
	 */
	protected $scBase;


	protected $objectSchemaBuilder;

	protected $codeGenerator;

	public function initializeAction() {
		$instance = t3lib_div::makeInstance('Tx_ExtbaseKickstarter_ObjectSchemaBuilder');
		/* @var $instance Tx_ExtbaseKickstarter_ObjectSchemaBuilder */
       	$this->objectSchemaBuilder = $instance;
		$instance = t3lib_div::makeInstance('Tx_ExtbaseKickstarter_Service_CodeGenerator');
		/* @var $instance Tx_ExtbaseKickstarter_Service_CodeGenerator */
		$this->codeGenerator = $instance;
	}

	/**
	 * Index action for this controller.
	 *
	 * @return string The rendered view
	 */
	public function indexAction() {


	}

	public function domainmodellingAction() {
		
	}

	/**
	 * Main entry point for the buttons in the frontend
	 * @return unknown_type
	 * @todo rename this action
	 */
	public function generateCodeAction() {
		$jsonString = file_get_contents('php://input');
		$request = json_decode($jsonString, true);
		switch ($request['method']) {

			case 'saveWiring':
				$extensionConfigurationFromJson = json_decode($request['params']['working'], true);
				//t3lib_div::devLog("msg1", 'tx_extbasekickstarter', 0, $extensionConfigurationFromJson);
				$extensionSchema = $this->objectSchemaBuilder->build($extensionConfigurationFromJson);

				$extensionDirectory = PATH_typo3conf . 'ext/' . $extensionSchema->getExtensionKey().'/';
				
				// if the files where already created in a previous save, start the three way diff and merging algorithm
				if (file_exists($extensionDirectory . '/kickstarter.json')) {
					$extensionDirectory2 = PATH_typo3conf . 'ext/' . $extensionSchema->getExtensionKey().'_base/';
					t3lib_div::rmdir($extensionDirectory2);
                   	t3lib_div::mkdir($extensionDirectory2);
					$extensionConfigurationFromJson2 = json_decode(file_get_contents($extensionDirectory . '/kickstarter.json'), true);
					$extensionSchema2 = $this->objectSchemaBuilder->build($extensionConfigurationFromJson2);
					$this->codeGenerator->setExtensionDirectory($extensionDirectory2);
                   	$this->codeGenerator->build($extensionSchema2);

					$extensionDirectory3 = PATH_typo3conf . 'ext/' . $extensionSchema->getExtensionKey().'_generated/';
					t3lib_div::rmdir($extensionDirectory3);
                   	t3lib_div::mkdir($extensionDirectory3);
					$extensionConfigurationFromJson3 = json_decode(file_get_contents($extensionDirectory . '/kickstarter.json'), true);
					$this->codeGenerator->setExtensionDirectory($extensionDirectory3);
                   	$this->codeGenerator->build($extensionSchema);



					// define files to compare
					$base = $extensionDirectory2.'Classes/Domain/Model/box.php';
					$file2 = $extensionDirectory3.'Classes/Domain/Model/box.php';
					$file3 = $extensionDirectory.'Classes/Domain/Model/box.php';
					
					$merger = new Text_Diff_ThreeWay(file($base), file($file2),file($file3));
					print_r($merger->mergedOutput());
					//$diff3 = new Text_Diff3($base,$file2,$file3);
					//print_r($diff3->mergedOutput());
					// perform diff, print output
					//$diff = &new Text_Diff(file($file1), file($file2));
					
					//$renderer = &new Text_Diff_Renderer_inline();
					//echo $renderer->render($diff);

				}else{
					t3lib_div::mkdir($extensionDirectory);
					$this->codeGenerator->build($extensionSchema);
					t3lib_div::writeFile($extensionDirectory . 'kickstarter.json', $request['params']['working']);
				}
				
				
				return json_encode(array('saved'));
			break;
			case 'listWirings':
				$result = $this->getWirings();

				$response = array ('id' => $request['id'],'result' => $result,'error' => NULL);
				header('content-type: text/javascript');
				echo json_encode($response);
				exit();
		}
	}

	protected function getWirings() {
		$result = array();

		$extensionDirectoryHandle = opendir(PATH_typo3conf . 'ext/');
		while (false !== ($singleExtensionDirectory = readdir($extensionDirectoryHandle))) {
			if ($singleExtensionDirectory[0] == '.') continue;
			if (file_exists(PATH_typo3conf . 'ext/' . $singleExtensionDirectory . '/kickstarter.json')) {
				$result[] = array(
					'name' => $singleExtensionDirectory,
					'working' => file_get_contents(PATH_typo3conf . 'ext/' . $singleExtensionDirectory . '/kickstarter.json')
				);
			}
		}
		closedir($extensionDirectoryHandle);

		return $result;
	}

	/**
	 * Adds items to the ->MOD_MENU array. Used for the function menu selector.
	 *
	 * @return	void
	 */
	function menuConfig()	{
		$this->scBase->MOD_MENU = Array (
			'function' => Array (
				'1' => 'Menu 1',
				'2' => 'Menu 2',
				'3' => 'Menu 3',
			)
		);
		$this->scBase->menuConfig();
	}

}
?>