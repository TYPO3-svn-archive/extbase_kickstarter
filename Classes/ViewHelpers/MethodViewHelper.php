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
			$parameterName = $parameter->getName();
			$typeHint = $parameter->getTypeHint ();
			
			if($parameter->isOptional()){
				$defaultValue = $parameter->getDefaultValue();
				if(!empty($typeHint)){
					// typeHints of optional parameter have the format "typeHint or defaultValue"
					$typeHintParts = explode(' ',$typeHint);
					$typeHint = $typeHintParts[0];
				}
				
				if(is_array($defaultValue)){
					if(!empty($defaultValue)){
						$defaultValue = json_encode($defaultValue);
						if(strpos($defaultValue,'}')>-1){
							$defaultValue = str_replace('{','array(',$defaultValue);
							$defaultValue = str_replace('}',')',$defaultValue);
							$defaultValue = str_replace(':',' => ',$defaultValue);
						}
						if(strpos($defaultValue,']')>-1){
							$defaultValue = str_replace('[','array(',$defaultValue);
							$defaultValue = str_replace(']',')',$defaultValue);
						}
						//t3lib_div::devLog('default Value: '. $defaultValue, 'parameter debug');				
					}
					else $defaultValue = 'array()';
				}
				else {
					$defaultValue = json_encode($defaultValue);
				}
				$parameterName .= ' = '.$defaultValue;
			}
			$parameterName = '$'.$parameterName;
			if($parameter->isPassedByReference()){
				$parameterName = '&'.$parameterName;
			}
			$parameters[] = $typeHint.' '.$parameterName;
			//t3lib_div::devLog($methodSchemaObject->getName().':'.$parameter->getName(), 'parameter debug');			
		}
		return implode(', ',$parameters);
	}
	

}
?>
