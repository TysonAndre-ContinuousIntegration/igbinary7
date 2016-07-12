--TEST--
Check for reference serialization (Original example, not using var_dump)
--SKIPIF--
<?php
if(!extension_loaded('igbinary')) {
	echo "skip no igbinary";
}
--FILE--
<?php 

// Verify that $type[0] is the same zval as $type[0][0][0], but different from $type[0]
function test_cyclic2($type, $variable) {
	$serialized = igbinary_serialize($variable);
	$unserialized = igbinary_unserialize($serialized);

	echo $type, "\n";
	echo substr(bin2hex($serialized), 8), "\n";
	echo $unserialized == $variable ? 'OK' : 'ERROR', "\n";

	ob_start();
	var_dump($variable);
	$dump_exp = ob_get_clean();
	ob_start();
	var_dump($unserialized);
	$dump_act = ob_get_clean();

	if (preg_replace('/&array/', 'array', $dump_act) !== preg_replace('/&array/', 'array', $dump_exp)) {
		echo "But var dump differs:\nActual:\n", $dump_act, "\nExpected\n", $dump_exp, "\n";
		echo "(Was normalized)\n";
	}
	
	if (!isset($unserialized[0]) || count($unserialized) != 1) {
		printf("Unexpected keys: %s\n", array_keys($unserialized));
		return;
	} else if (!is_array($unserialized)) {
		printf("\$a[0] is not an array, it is %s", gettype($unserialized));
		return;
	}
	// Set a key, check for the presense of the key 2 levels deeper (Should find it) and 1 level deeper (Should not find it)
	$unserialized[0]['test'] = 'foo';
	if ($unserialized[0][0][0]['test'] !== 'foo') {
		echo "Expected the unserialized array to be cyclic\n";
	}
	if (isset($unserialized[0][0]['test'])) {
		echo "Expected the unserialized array to be cyclic AND of cycle depth 2, but cycle depth is 1\n";
	}
}

$a = [null];
$b = [&$a];
$a[0] = &$b;
// 1401060025140106002514010600250101 could also be serialized as 14010600251401060014010600250101 if we normalized the references which only occurred once in the serialization
// (Replace middle &array(&$a) with array(&$array), i.e. second 2514 with 14)
test_cyclic2('cyclic $a = array(&array(&$a)) - testing functionality', $a);
unset($a);

$a = null;
$a = [[&$a]];
test_cyclic2('cyclic $a = array(array(&$a)) - testing functionality', $a);


--EXPECT--
cyclic $a = array(&array(&$a)) - testing functionality
1401060025140106002514010600250101
OK
cyclic $a = array(array(&$a)) - testing functionality
14010600251401060014010600250101
OK
