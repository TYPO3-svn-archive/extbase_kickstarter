<?php
class Tx_ExtbaseKickstarter_Utility_Diff3 {
	/**
	 * merges 2 files based on a base file (also called "Three Way Merge")
	 * @param <type> $fileBase The file base
	 * @param <type> $file1 The first file with edits
	 * @param <type> $file2 The second file with edits
	 */
	public function merge($fileBase,$file1,$file2){
		if (stristr(PHP_OS, 'WIN')) {
			$output = shell_exec("diff3.exe -m $file1 $fileBase $file2");
		}else {
			$output = shell_exec("diff3 -m $file1 $fileBase $file2");
		}
		
		
		return $output;
	}
}


?>