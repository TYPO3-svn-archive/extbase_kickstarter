<?php


class Tx_ExtbaseKickstarter_ViewHelpers_ClassViewHelper extends Tx_Fluid_Core_ViewHelper_AbstractViewHelper {
	
	/**
	 * 
	 * @param object $classSchemaObject
	 * @param string $renderElement
	 * @return 
	 */
	public function render($classSchemaObject,$renderElement) {
		$content = '';
		//t3lib_div::devLog(serialize($classSchemaObject), $renderElement);
		switch($renderElement){
			case 'extend'		:	$content = $this->renderExtendClassDeclaration($classSchemaObject);
									break;
		}
		return $content;
	}
	
	private function renderExtendClassDeclaration($classSchema){
		$parentClass = $classSchema->getParentClass();
		if(is_object($parentClass)){
			$parentClass = $parentClass->getName();
		}
		if(!empty($parentClass)){
			return ' extends '.$parentClass;
		}
		else return '';
	}
	

}
?>
