<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2009 Ingmar Schlecht
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
 * Creates a request an dispatches it to the controller which was specified
 * by TS Setup, Flexform and returns the content to the v4 framework.
 *
 * This class is the main entry point for extbase extensions in the frontend.
 *
 * @package ExtbaseKickstarter
 * @version $ID:$
 */
class Tx_ExtbaseKickstarter_Service_CodeGenerator {
	
	/**
	 *
	 * @var Tx_Fluid_Core_Parser_TemplateParser
	 */
	protected $templateParser;

	/**
	 *
	 * @var Tx_Fluid_Compatibility_ObjectFactory
	 */
	protected $objectFactory;

	/**
	 *
	 * @var Tx_ExtbaseKickstarter_Domain_Model_Extension
	 */
	protected $extension;

	public function __construct() {
		$this->templateParser = Tx_Fluid_Compatibility_TemplateParserBuilder::build();
		$this->objectFactory = new Tx_Fluid_Compatibility_ObjectFactory();
	}

	public function initialize(Tx_ExtbaseKickstarter_Domain_Model_Extension $extension) {
		$this->extension = $extension;
	}

	/**
	 * Build the rendering context
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	protected function buildRenderingContext($templateVariableContainer) {
		$variableContainer = $this->objectFactory->create('Tx_Fluid_Core_ViewHelper_TemplateVariableContainer', $templateVariableContainer);

		$renderingConfiguration = $this->objectFactory->create('Tx_Fluid_Core_Rendering_RenderingConfiguration');
		$renderingConfiguration->setObjectAccessorPostProcessor($this->objectFactory->create('Tx_Fluid_Core_Rendering_HTMLSpecialCharsPostProcessor'));

		$renderingContext = $this->objectFactory->create('Tx_Fluid_Core_Rendering_RenderingContext');
		$renderingContext->setTemplateVariableContainer($variableContainer);
		$renderingContext->setControllerContext($this->controllerContext);
		$renderingContext->setRenderingConfiguration($renderingConfiguration);

		$viewHelperVariableContainer = $this->objectFactory->create('Tx_Fluid_Core_ViewHelper_ViewHelperVariableContainer');
		$viewHelperVariableContainer->add('GLOBAL', 'extension', $this->extension);
		$renderingContext->setViewHelperVariableContainer($viewHelperVariableContainer);

		return $renderingContext;
	}

	protected function renderTemplate($filePath, $variables) {
		$parsedTemplate = $this->templateParser->parse(file_get_contents('Resources/Private/CodeTemplates/' . $filePath));
		return $parsedTemplate->render($this->buildRenderingContext($variables));
	}
	public function generateDomainObjectCode(Tx_ExtbaseKickstarter_Domain_Model_DomainObject $domainObject) {
		return $this->renderTemplate('domainObject.phpt', array('domainObject', $domainObject));
	}
}
?>