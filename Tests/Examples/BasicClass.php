<?php 

class Tx_ExtbaseKickstarter_Tests_Examples_BasicClass {
	
	protected $names;
	
	/**
	 * 
	 * @return array $names
	 */
	public function getNames(){
		return $this->names;
	}
	
	/**
	 * 
	 * @param array $name
	 * @return void
	 */
	public function setNames(array $names){
		$this->names = $names;
	}
}
?>