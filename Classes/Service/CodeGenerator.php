<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2009 Ingmar Schlecht, 2010 Nico de Haen
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
class Tx_ExtbaseKickstarter_Service_CodeGenerator implements t3lib_Singleton {
	
	/**
	 *
	 * @var Tx_Fluid_Core_Parser_TemplateParser
	 */
	protected $templateParser;

	/**
	 *
	 * @var Tx_Fluid_Compatibility_ObjectManager
	 */
	protected $objectManager;

	/**
	 *
	 * @var Tx_ExtbaseKickstarter_Domain_Model_Extension
	 */
	protected $extension;
	

	public function __construct() {
		$this->templateParser = Tx_Fluid_Compatibility_TemplateParserBuilder::build();
		$this->objectManager = new Tx_Fluid_Compatibility_ObjectManager();
	}

	public function build(Tx_ExtbaseKickstarter_Domain_Model_Extension $extension) {
		$this->extension = $extension;

		// Validate the extension
		$extensionValidator = t3lib_div::makeInstance('Tx_ExtbaseKickstarter_Domain_Validator_ExtensionValidator');
		try {
			$extensionValidator->isValid($this->extension);
		} catch (Exception $e) {
			return $e->getMessage();
		}
		
		$this->importTool = new Tx_ExtbaseKickstarter_Utility_Import();
		// Base directory already exists at this point
		$extensionDirectory = PATH_typo3conf . 'ext/' . $this->extension->getExtensionKey().'/';
		//t3lib_div::mkdir($extensionDirectory);

		// Generate ext_emconf.php, ext_tables.* and TCA definition
		$extensionFiles = array('ext_emconf.php','ext_tables.php','ext_tables.sql','ext_localconf.php');
		foreach($extensionFiles as  $extensionFile){
			try {
				$fileContents = $this->renderTemplate( Tx_Extbase_Utility_Extension::convertUnderscoredToLowerCamelCase($extensionFile).'t', array('extension' => $extension));
				t3lib_div::writeFile($extensionDirectory . $extensionFile, $fileContents);
			} 
			catch (Exception $e) {
				return 'Could not write '.$extensionFile.', error: ' . $e->getMessage();
			}
		}
		
		try {
			t3lib_div::upload_copy_move(t3lib_extMgm::extPath('extbase_kickstarter') . 'Resources/Private/Icons/ext_icon.gif', $extensionDirectory . 'ext_icon.gif');
		} catch (Exception $e) {
			return 'Could not copy ext_icon.gif, error: ' . $e->getMessage();
		}
		
		// Generate TCA
		try {
			t3lib_div::mkdir_deep($extensionDirectory, 'Configuration/TCA');
			$tcaDirectory = $extensionDirectory . 'Configuration/';
			$domainObjects = $extension->getDomainObjects();
			
			foreach ($domainObjects as $domainObject) {
				$fileContents = $this->generateTCA($extension, $domainObject);
				t3lib_div::writeFile($tcaDirectory . 'TCA/' . $domainObject->getName() . '.php', $fileContents);
			}

		} catch (Exception $e) {
			return 'Could not generate Tca.php, error: ' . $e->getMessage();
		}

		// Generate TypoScript setup
		try {
			t3lib_div::mkdir_deep($extensionDirectory, 'Configuration/TypoScript');
			$typoscriptDirectory = $extensionDirectory . 'Configuration/TypoScript/';
			$fileContents = $this->generateTyposcriptSetup($extension);
			t3lib_div::writeFile($typoscriptDirectory . 'setup.txt', $fileContents);
		} catch (Exception $e) {
			return 'Could not generate typoscript setup, error: ' . $e->getMessage();
		}

		// Generate Private Resources .htaccess
		try {
			t3lib_div::mkdir_deep($extensionDirectory, 'Resources/Private');
			$privateResourcesDirectory = $extensionDirectory . 'Resources/Private/';
			$fileContents = $this->generatePrivateResourcesHtaccess();
			t3lib_div::writeFile($privateResourcesDirectory . '.htaccess', $fileContents);
		} catch (Exception $e) {
			return 'Could not create private resources folder, error: ' . $e->getMessage();
		}
		
		// Generate locallang*.xml files
		try {
			t3lib_div::mkdir_deep($privateResourcesDirectory, 'Language');
			$languageDirectory = $privateResourcesDirectory . 'Language/';
			$fileContents = $this->generateLocallang($extension);
			t3lib_div::writeFile($languageDirectory . 'locallang.xml', $fileContents);
			$fileContents = $this->generateLocallangDB($extension);
			t3lib_div::writeFile($languageDirectory . 'locallang_db.xml', $fileContents);
		} catch (Exception $e) {
			return 'Could not generate locallang files, error: ' . $e->getMessage();
		}

		try {
			t3lib_div::mkdir_deep($extensionDirectory, 'Resources/Public');
			$publicResourcesDirectory = $extensionDirectory . 'Resources/Public/';
			t3lib_div::mkdir_deep($publicResourcesDirectory, 'Icons');
			$iconsDirectory = $publicResourcesDirectory . 'Icons/';
			t3lib_div::upload_copy_move(t3lib_extMgm::extPath('extbase_kickstarter') . 'Resources/Private/Icons/relation.gif', $iconsDirectory . 'relation.gif');
		} catch (Exception $e) {
			return 'Could not create public resources folder, error: ' . $e->getMessage();
		}
				
		if (count($this->extension->getDomainObjects())) {
			
			// Generate Domain Model
			try {
				t3lib_div::mkdir_deep($extensionDirectory, 'Classes/Domain/Model');
				$domainModelDirectory = $extensionDirectory . 'Classes/Domain/Model/';
				t3lib_div::mkdir_deep($extensionDirectory, 'Classes/Domain/Repository');
				$domainRepositoryDirectory = $extensionDirectory . 'Classes/Domain/Repository/';
				foreach ($this->extension->getDomainObjects() as $domainObject) {
					$fileContents = $this->generateDomainObjectCode($domainObject, $extension);
					t3lib_div::writeFile($domainModelDirectory . $domainObject->getName() . '.php', $fileContents);
					$this->extension->setMD5Hash($domainModelDirectory . $domainObject->getName() . '.php');
					if ($domainObject->isAggregateRoot()) {
						$iconFileName = 'aggregate_root.gif';
					} elseif ($domainObject->isEntity()) {
						$iconFileName = 'entity.gif';
					} else {
						$iconFileName = 'value_object.gif';
					}
					t3lib_div::upload_copy_move(t3lib_extMgm::extPath('extbase_kickstarter') . 'Resources/Private/Icons/' . $iconFileName, $iconsDirectory . $domainObject->getDatabaseTableName() . '.gif');

					$fileContents = $this->generateLocallangCsh($extension, $domainObject);
					t3lib_div::writeFile($languageDirectory . 'locallang_csh_' . $domainObject->getDatabaseTableName() . '.xml', $fileContents);

					if ($domainObject->isAggregateRoot()) {
						$fileContents = $this->generateDomainRepositoryCode($domainObject);
						t3lib_div::writeFile($domainRepositoryDirectory . $domainObject->getName() . 'Repository.php', $fileContents);
						$this->extension->setMD5Hash($domainRepositoryDirectory . $domainObject->getName() . 'Repository.php');
					}
				}
			} catch (Exception $e) {
				return 'Could not generate domain model, error: ' . $e->getMessage();
			}

			// Generate Action Controller
			try {
				t3lib_div::mkdir_deep($extensionDirectory, 'Classes/Controller');
				$controllerDirectory = $extensionDirectory . 'Classes/Controller/';
				foreach ($this->extension->getDomainObjectsForWhichAControllerShouldBeBuilt() as $domainObject) {
					$fileContents = $this->generateActionControllerCode($domainObject, $extension);
					t3lib_div::writeFile($controllerDirectory . $domainObject->getName() . 'Controller.php', $fileContents);
					$this->extension->setMD5Hash($controllerDirectory . $domainObject->getName() . 'Controller.php');
				}
			} catch (Exception $e) {
				return 'Could not generate action controller, error: ' . $e->getMessage();
			}
			
			// Generate Domain Templates
			try {
				foreach ($this->extension->getDomainObjects() as $domainObject) {
					// Do not generate anyting if $domainObject is not an Entity or has no actions defined
					if (!$domainObject->getEntity() || (count($domainObject->getActions()) == 0)) continue;

					t3lib_div::mkdir_deep($privateResourcesDirectory, 'Templates/' . $domainObject->getName());
					$domainTemplateDirectory = $privateResourcesDirectory . 'Templates/' . $domainObject->getName() . '/';
					foreach($domainObject->getActions() as $action) {
						$fileContents = $this->generateDomainTemplate($domainObject, $action);
						t3lib_div::writeFile($domainTemplateDirectory . $action->getName() . '.html', $fileContents);
					}
				}
			} catch (Exception $e) {
				return 'Could not generate domain templates, error: ' . $e->getMessage();
			}

			try {
				// Generate Partial directory
				t3lib_div::mkdir_deep($extensionDirectory, 'Resources/Private/Partials');

				// Generate Layouts directory
				t3lib_div::mkdir_deep($extensionDirectory, 'Resources/Private/Layouts');
				$layoutsDirectory = $extensionDirectory . 'Resources/Private/Layouts/';
				t3lib_div::writeFile($layoutsDirectory . 'default.html', $this->generateLayout($extension));
			} catch (Exception $e) {
				return 'Could not generate private template folders, error: ' . $e->getMessage();
			}
		}

		return true;
	}

	/**
	 * Build the rendering context
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	protected function buildRenderingContext($templateVariables) {
		$variableContainer = $this->objectManager->create('Tx_Fluid_Core_ViewHelper_TemplateVariableContainer', $templateVariables);

		$renderingContext = $this->objectManager->create('Tx_Fluid_Core_Rendering_RenderingContext');
		$renderingContext->injectTemplateVariableContainer($variableContainer);
		//$renderingContext->setControllerContext($this->controllerContext); 

		$viewHelperVariableContainer = $this->objectManager->create('Tx_Fluid_Core_ViewHelper_ViewHelperVariableContainer');
		$renderingContext->injectViewHelperVariableContainer($viewHelperVariableContainer);

		return $renderingContext;
	}

	protected function renderTemplate($filePath, $variables) {
		if(!is_file(t3lib_extMgm::extPath('extbase_kickstarter').'Resources/Private/CodeTemplates/' . $filePath)){
			throw(new Exception('TemplateFile '.t3lib_extMgm::extPath('extbase_kickstarter').'Resources/Private/CodeTemplates/' . $filePath.' not found'));
		}
		$parsedTemplate = $this->templateParser->parse(file_get_contents(t3lib_extMgm::extPath('extbase_kickstarter').'Resources/Private/CodeTemplates/' . $filePath));
		return trim($parsedTemplate->render($this->buildRenderingContext($variables)));
	}


	public function generateActionControllerCode(Tx_ExtbaseKickstarter_Domain_Model_DomainObject $domainObject, Tx_ExtbaseKickstarter_Domain_Model_Extension $extension) {
		$controllerClassSchema = $this->generateControllerClassSchema($domainObject);
		return $this->renderTemplate('Classes/Controller/actionController.phpt', array('domainObject' => $domainObject, 'extension' => $extension,'classSchema'=>$controllerClassSchema));
	}

	public function generateActionControllerCrudActions(Tx_ExtbaseKickstarter_Domain_Model_DomainObject $domainObject) {
		return $this->renderTemplate('Classes/Controller/actionControllerCrudActions.phpt', array('domainObject' => $domainObject));
	}
	
	public function generateDomainObjectCode(Tx_ExtbaseKickstarter_Domain_Model_DomainObject $domainObject, Tx_ExtbaseKickstarter_Domain_Model_Extension $extension) {
		$modelClassSchema = $this->generateModelClassSchema($domainObject);
		return $this->renderTemplate('Classes/Domain/Model/domainObject.phpt', array('domainObject' => $domainObject, 'extension' => $extension,'classSchema'=>$modelClassSchema));
	}

	public function generateDomainRepositoryCode(Tx_ExtbaseKickstarter_Domain_Model_DomainObject $domainObject) {
		$repositoryClassSchema = $this->generateRepositoryClassSchema($domainObject);
		return $this->renderTemplate('Classes/Domain/Repository/domainRepository.phpt', array('domainObject' => $domainObject,'classSchema' => $repositoryClassSchema));
	}
	
	/**
	 * Generates the content of an Action template
	 * For some Actions default templates are provided, other Action templates will just be created emtpy
	 *
	 * @param Tx_ExtbaseKickstarter_Domain_Model_DomainObject $domainObject
	 * @param Tx_ExtbaseKickstarter_Domain_Model_Action $action
	 * @return string The generated Template code (might be empty)
	 */
	public function generateDomainTemplate(Tx_ExtbaseKickstarter_Domain_Model_DomainObject $domainObject, Tx_ExtbaseKickstarter_Domain_Model_Action $action) {
		if (file_exists(t3lib_extMgm::extPath('extbase_kickstarter').'Resources/Private/CodeTemplates/Resources/Private/Templates/' . $action->getName() . '.htmlt')) {
			return $this->renderTemplate('Resources/Private/Templates/'. $action->getName() . '.htmlt', array('domainObject' => $domainObject, 'action' => $action));
		}
	}

	public function generateFormErrorsPartial() {
		return file_get_contents(t3lib_extMgm::extPath('extbase_kickstarter') . 'Resources/Private/CodeTemplates/Resources/Private/Partials/formErrors.htmlt');
	}

	public function generateLayout(Tx_ExtbaseKickstarter_Domain_Model_Extension $extension) {
		return $this->renderTemplate('Resources/Private/Layouts/default.htmlt', array('extension' => $extension));
	}

	
	public function generateLocallang(Tx_ExtbaseKickstarter_Domain_Model_Extension $extension) {
		return $this->renderTemplate('Resources/Private/Language/locallang.xmlt', array('extension' => $extension));
	}
	
	public function generateLocallangDB(Tx_ExtbaseKickstarter_Domain_Model_Extension $extension) {
		return $this->renderTemplate('Resources/Private/Language/locallang_db.xmlt', array('extension' => $extension));
	}
	
	public function generateLocallangCsh(Tx_ExtbaseKickstarter_Domain_Model_Extension $extension, Tx_ExtbaseKickstarter_Domain_Model_DomainObject $domainObject) {
		return $this->renderTemplate('Resources/Private/Language/locallang_csh.xmlt', array('extension' => $extension, 'domainObject' => $domainObject));
	}
	
	public function generatePrivateResourcesHtaccess() {
		return $this->renderTemplate('Resources/Private/htaccess.t', array());
	}

	public function generateTCA(Tx_ExtbaseKickstarter_Domain_Model_Extension $extension, Tx_ExtbaseKickstarter_Domain_Model_DomainObject $domainObject) {
		return $this->renderTemplate('Configuration/TCA/domainObject.phpt', array('extension' => $extension, 'domainObject' => $domainObject));
	#public function generateTCA(Tx_ExtbaseKickstarter_Domain_Model_Extension $extension) {
		#return $this->renderTemplate('Configuration/Tca.phpt', array('extension' => $extension));
	}

	public function generateTyposcriptSetup(Tx_ExtbaseKickstarter_Domain_Model_Extension $extension) {
		return $this->renderTemplate('Configuration/TypoScript/setup.txt', array('extension' => $extension));
	}
	
	/**
	 * 
	 * @param Tx_ExtbaseKickstarter_Domain_Model_DomainObject $domainObject
	 * @return 
	 */
	protected function generateModelClassSchema($domainObject){
		$this->extensionDirectory = PATH_typo3conf . 'ext/' . $this->extension->getExtensionKey().'/';
		$domainObjectClassFile = $this->extensionDirectory.'Classes/Domain/Model/' . $domainObject->getName() . '.php';
		$className = 'Tx_' . Tx_Extbase_Utility_Extension::convertLowerUnderscoreToUpperCamelCase($this->extension->getExtensionKey()) . '_Domain_Model_' . $domainObject->getName();
	
		if(file_exists( $domainObjectClassFile) &&  $this->extension->isModified($domainObjectClassFile)){
			t3lib_div::devLog('Class '.$className.' was modified', 'extbase_kickstarter');
			include_once($domainObjectClassFile);
			try {
				$classSchema = $this->importTool->importClassSchemaFromFile($className);
			}
			catch(Exception $e){
				t3lib_div::devLog('Class '.$className.' could not be imported: '.$e->getError(), 'extbase_kickstarter');		
			}			
		}
		else {
			$classSchema = new Tx_ExtbaseKickstarter_Domain_Model_Class_Schema($className);
			$classSchema->setFileName($domainObjectClassFile);
			if($domainObject->isEntity()){
				$classSchema->setParentClass('Tx_Extbase_DomainObject_AbstractEntity');
			}
			else {
				$classSchema->setParentClass('Tx_Extbase_DomainObject_AbstractValueObject');
			}
		}
		$classDocComment = $classSchema->getDocComment();
		if(empty($classDocComment)){
			$classDocComment = $this->renderTemplate('Partials/Classes/Domain/Model/classDocComment.phpt', array('domainObject' => $domainObject, 'extension' => $this->extension,'classSchema'=>$classSchema));
			$classSchema->setDocComment($classDocComment);
		}
		if(!$classSchema->hasDescription()){
			$classSchema->setDescription($domainObject->getDescription());
		}
		//TODO the following part still needs some enhancement: 
		//what should be obligatory in existing properties and methods
		foreach ($domainObject->getProperties() as $domainProperty) {
			$propertyName = $domainProperty->getName();
					
			// add the property to class schema (or update an existing class schema property)
			if($classSchema->propertyExists($propertyName)){
				$classProperty = $classSchema->getProperty($propertyName);
				$classPropertyTags = $classProperty->getTags();
			}
			else {
				$classProperty = new Tx_ExtbaseKickstarter_Domain_Model_Class_Property($propertyName);
				$classProperty->setTag('var',strtolower($domainProperty->getTypeForComment()));
				$classProperty->addModifier('private');
			}
			
			$classProperty->setAssociatedDomainObjectProperty($domainProperty);
			
			$classSchema->setProperty($classProperty);
			
			//TODO relation properties need add/remove methods
			
			// add (or update) a getter method
			$getterMethodName = 'get'.ucfirst($propertyName);
			
			if($classSchema->methodExists($getterMethodName)){
				$getterMethod = $classSchema->getMethod($getterMethodName);
				$getterMethodTags = $getterMethod->getTags();
			}
			else {
				$getterMethod = new Tx_ExtbaseKickstarter_Domain_Model_Class_Method($getterMethodName);
				// default method body
				$getterMethod->setBody('return $this->'.$propertyName.';');
				$getterMethod->setTag('return',strtolower($domainProperty->getTypeForComment()).' $'.$propertyName);
				$getterMethod->addModifier('public');
			}
			if(!$getterMethod->hasDescription()){
				$getterMethod->setDescription('Returns the '.$propertyName);
			}
			$classSchema->setMethod($getterMethod);
			
			// add (or update) a setter method
			$setterMethodName = 'set'.ucfirst($propertyName);
			
			if($classSchema->methodExists($setterMethodName)){
				$setterMethod = $classSchema->getMethod($setterMethodName);
				$setterMethodTags = $setterMethod->getTags();
			}
			else {
				$setterMethod = new Tx_ExtbaseKickstarter_Domain_Model_Class_Method($setterMethodName);
				// default method body
				$setterMethod->setBody('$this->'.$propertyName.' = $'.$propertyName.';');
				$setterMethod->setTag('param',strtolower($domainProperty->getTypeForComment()).' $'.$propertyName);
				$setterMethod->setTag('return','void');
				$setterMethod->addModifier('public');
			}
			if(!$setterMethod->hasDescription()){
				$setterMethod->setDescription('Sets the '.$propertyName);
			}
			$setterParameters = $setterMethod->getParameterNames();
			if(!in_array($propertyName,$setterParameters)){
				$setterParameter = new Tx_ExtbaseKickstarter_Domain_Model_Class_MethodParameter($propertyName);
				$setterParameter->setVarType($domainProperty->getTypeForComment());
				$setterMethod->setParameter($setterParameter);
			}

			$classSchema->setMethod($setterMethod);
		
			
		}
		if(!$classSchema->methodExists('__constructor')){
			$constructorMethod = new Tx_ExtbaseKickstarter_Domain_Model_Class_Method('__constructor');
			$constructorMethod->setDescription('The constructor of this '.$domainObject->getName());
			$constructorMethod->setBody('$this->initSplObjects();');
			$constructorMethod->addModifier('public');
			$classSchema->addMethod($constructorMethod);
		}
		
		if(!$classSchema->methodExists('initSplObjects')){
			$initSplObjectsMethod = new Tx_ExtbaseKickstarter_Domain_Model_Class_Method('initSplObjects');
			$initSplObjectsMethod->setDescription('Initializes all Tx_Extbase_Persistence_ObjectStorage instances.');
			//TODO set dynamic method body
			$initSplObjectsMethod->setBody('');
			$initSplObjectsMethod->addModifier('protected');
			$classSchema->addMethod($initSplObjectsMethod);
		}
		return $classSchema;
	}
	
	/**
	 * 
	 * @param Tx_ExtbaseKickstarter_Domain_Model_DomainObject $domainObject
	 * @return 
	 */
	protected function generateControllerClassSchema($domainObject){
		$this->extensionDirectory = PATH_typo3conf . 'ext/' . $this->extension->getExtensionKey().'/';
		$controllerClassFile = $this->extensionDirectory . 'Classes/Controller/' . $domainObject->getName() . 'Controller.php';
		
		$className = 'Tx_' . Tx_Extbase_Utility_Extension::convertLowerUnderscoreToUpperCamelCase($this->extension->getExtensionKey()) . '_Controller_' . $domainObject->getName().'Controller';
	
		if(file_exists( $controllerClassFile) &&  $this->extension->isModified($controllerClassFile)){
			if(!class_exists($className)){
				include_once($controllerClassFile);
			}
			
			try {
				$classSchema = $this->importTool->importClassSchemaFromFile($className);
			}
			catch(Exception $e){
				t3lib_div::devLog('Class '.$className.' could not be imported: '.$e->getError(), 'extbase_kickstarter');		
			}				
		}
		else {
			$classSchema = new Tx_ExtbaseKickstarter_Domain_Model_Class_Schema($className);
			$classSchema->setFileName($controllerClassFile);
			$classSchema->setParentClass('Tx_Extbase_MVC_Controller_ActionController');
			$classSchema->setDescription('Controller for '.$domainObject->getName());
		}
		if(empty($classDocComment)){
			$classDocComment = $this->renderTemplate('Partials/Classes/Domain/Model/classDocComment.phpt', array('domainObject' => $domainObject, 'extension' => $this->extension,'classSchema'=>$classSchema));
			$classSchema->setDocComment($classDocComment);
		}
		if($domainObject->isAggregateRoot()){
			$propertyName = t3lib_div::lcfirst($domainObject->getName()).'Repository';
			//$domainObject->getDomainRepositoryClassName();
			// now add the property to class schema (or update an existing class schema property)
			if(!$classSchema->propertyExists($propertyName)){
				$classProperty = new Tx_ExtbaseKickstarter_Domain_Model_Class_Property($propertyName);
				$classProperty->setTag('var',$domainObject->getDomainRepositoryClassName());
				$classProperty->addModifier('protected');
				$classSchema->setProperty($classProperty);
			}
			$initializeMethodName = 'initializeAction';
			if(!$classSchema->methodExists($initializeMethodName)){
				$initializeMethod = new Tx_ExtbaseKickstarter_Domain_Model_Class_Method($initializeMethodName);
				$initializeMethod->setDescription('Initializes the current action');
				$initializeMethod->setBody('$this->'.t3lib_div::lcfirst($domainObject->getName()).'Repository = t3lib_div::makeInstance('.$domainObject->getDomainRepositoryClassName().');');
				$initializeMethod->setTag('return','void');
				$initializeMethod->addModifier('public');
				$classSchema->addMethod($initializeMethod);
			}
		}
		
		foreach($domainObject->getActions() as $action){
			$actionMethodName = $action->getName().'Action';
			$actionMethod = new Tx_ExtbaseKickstarter_Domain_Model_Class_Method($actionMethodName);
			$actionMethod->setDescription('action '.$action->getName());
			$actionMethod->setBody('');
			$actionMethod->setTag('return','string The rendered ' . $action->getName() .' action');
			$actionMethod->addModifier('public');
			
			$classSchema->addMethod($actionMethod);
		}
		return $classSchema;
	}
	
		/**
	 * 
	 * @param Tx_ExtbaseKickstarter_Domain_Model_DomainObject $domainObject
	 * @return 
	 */
	protected function generateRepositoryClassSchema($domainObject){
		$this->extensionDirectory = PATH_typo3conf . 'ext/' . $this->extension->getExtensionKey().'/';
		$repositoryClassFile = $this->extensionDirectory . 'Classes/Domain/Repository/' . $domainObject->getName() . 'Repository.php';
		
		$className = 'Tx_' . Tx_Extbase_Utility_Extension::convertLowerUnderscoreToUpperCamelCase($this->extension->getExtensionKey()) . '_Domain_Repository_' . $domainObject->getName().'Repository';
	
		if(file_exists( $repositoryClassFile) &&  $this->extension->isModified($repositoryClassFile)){
			include_once($repositoryClassFile);
			try {
				$classSchema = $this->importTool->importClassSchemaFromFile($className);
			}
			catch(Exception $e){
				t3lib_div::devLog('Class '.$className.' could not be imported: '.$e->getError(), 'extbase_kickstarter');		
			}		
		}
		else {
			$classSchema = new Tx_ExtbaseKickstarter_Domain_Model_Class_Schema($className);
			$classSchema->setFileName($repositoryClassFile);
			$classSchema->setParentClass('Tx_Extbase_Persistence_Repository');
			$classSchema->setDescription('Repository for '.$domainObject->getName());
		}
		if(empty($classDocComment)){
			$classDocComment = $this->renderTemplate('Partials/Classes/Domain/Model/classDocComment.phpt', array('domainObject' => $domainObject, 'extension' => $this->extension,'classSchema'=>$classSchema));
			$classSchema->setDocComment($classDocComment);
		}
		
		return $classSchema;
	}
}
?>