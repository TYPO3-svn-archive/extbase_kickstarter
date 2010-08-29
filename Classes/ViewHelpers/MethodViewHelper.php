<?php


class Tx_ExtbaseKickstarter_ViewHelpers_MethodViewHelper extends Tx_Fluid_Core_ViewHelper_AbstractViewHelper {
	
	/**
	 * 
	 * @param object $methodSchemaObject
	 * @param string $renderElement
	 * @return 
	 */
	public function render($methodSchemaObject,$renderElement) {
		$content = '';
		//t3lib_div::devLog(serialize($methodSchemaObject), $renderElement);
		switch($renderElement){
			case 'parameter'		:	$content = $this->renderMethodParameter($methodSchemaObject);

		}
		return $content;
	}
	
	private function renderMethodParameter($methodSchemaObject){
		$content = '';
		$parameters = array();
		
		foreach($methodSchemaObject->getParameters()  as $parameter){
			$parameters[] = strtolower($parameter->getVarType()).' $'.$parameter->getName();
			t3lib_div::devLog($methodSchemaObject->getName().':'.$parameter->getName(), 'parameter debug');			
		}
		return implode(', ',$parameters);
	}
	

}
?>
