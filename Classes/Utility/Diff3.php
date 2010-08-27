<?php
class Tx_ExtbaseKickstarter_Utility_Diff3 {

	public function mergeStrings($stringBase,$string1,$string2){
		
	}
	/**
	 * merges 2 files based on a base file (also called "Three Way Merge")
	 * @param <type> $fileBase The file base
	 * @param <type> $file1 The first file with edits
	 * @param <type> $file2 The second file with edits
	 */
	public function mergeFiles($fileBase,$file1,$file2){
		//print_r($_SERVER); die();
		if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
			$path = implode('/', array_slice(explode('/', $_SERVER['SCRIPT_FILENAME']),0,-2)) . '/typo3conf/ext/extbase_kickstarter/Resources/Private/Binaries/Windows/';
			chdir($path);
			$output = shell_exec('"' . $path . 'diff3.exe"' . ' -m "' . $file1 . '" "' . $fileBase . '" "' . $file2 . '" > out.txt');
			// writing temporary file, because otherwise newlines get lost
			$output = file_get_contents('out.txt');
			unlink('out.txt');
		}else {
			$output = shell_exec("diff3 -m $file1 $fileBase $file2");
		}
		

		return $output;
	}
}


?>