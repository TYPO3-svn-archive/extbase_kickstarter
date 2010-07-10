<?php 

class Tx_ExtbaseKickstarter_Tests_Examples_BasicClass {
	
	protected $name;
	
	/**
	 * 
	 * @return string $name
	 */
	public function getName(){
		return $this->name;
	}
	
	/**
	 * 
	 * @param string $name
	 * @return void
	 */
	public function setName($name){
		$this->name = $name;
	}
}
?>