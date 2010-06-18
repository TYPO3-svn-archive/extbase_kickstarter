<?php

$PATH_typo3conf = '/Applications/MAMP/htdocs/typo3_svn/typo3conf/';
require_once $PATH_typo3conf . 'ext/extbase_kickstarter/lib/pear/' . "Text/Diff/Engine/native.php";
require_once $PATH_typo3conf . 'ext/extbase_kickstarter/lib/pear/' . "Text/Diff/Engine/shell.php";
require_once $PATH_typo3conf . 'ext/extbase_kickstarter/lib/pear/' . "Text/Diff3.php";
 
$base =  $PATH_typo3conf .'ext/extbase_kickstarter/testmerge/Box_base.php';
$file2 = $PATH_typo3conf .'ext/extbase_kickstarter/testmerge/Box_generated.php';
$file3 = $PATH_typo3conf .'ext/extbase_kickstarter/testmerge/Box.php';
					
$merger = new Text_Diff3(file($base), file($file2),file($file3));
echo "<h1>merged</h1><pre>";
print_r($merger);
echo "<hr>";
print_r($merger->mergedOutput());
echo "</pre>";
 
 
?>