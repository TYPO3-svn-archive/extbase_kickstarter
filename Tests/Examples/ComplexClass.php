<?php 
/**
 * multiline comment test
 * @author Nico de Haen
 * 

	empty line in multiline comment
	
	// single comment in multiline
	 * 
	some keywords: $property  function
	static 
	
	

 *
 */



final class Tx_ExtbaseKickstarter_Tests_Examples_ComplexClass {
	
	protected $name; private $propertiesInOneLine;
	
	const testConstant = "123"; const testConstant2 = 0.56;
	
	/**
	 * 
	 * @return string $name
	 */
	public function getName(){
		return $this->name;
	}
	//startPrecedingBlock
	
	/***********************************************************/
	
	
	/*********/ //some  strange comments /*/ test \*\*\*
	//  include_once('typo3conf/ext/extbase_kickstarter/Tests/Examples/ComplexClass.php'); // test
	
	/**
	 * 
	 * @param string $name
	 * @return void
	 */
	public function methodWithStrangePrecedingBlock(string $name){
		/**
		 * multi line comment in a method
		 * @var unknown_type
		 */
		$this->name = $name;
	}
	
	// single line comment
}


require_once(t3lib_extmgm::extPath('extbase_kickstarter') . 'Tests/Examples/BasicClass.php');   include('typo3conf/ext/extbase_kickstarter/Tests/Examples/ComplexClass.php'); // test

 include_once('typo3conf/ext/extbase_kickstarter/Tests/Examples/ComplexClass.php'); // test
?>