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
	echo $serialize_act === $serialize_exp ? 'OK' : 'ERROR', "\n";

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
	
	if (!isset($a[0]) || count($a) != 1) {
		printf("Unexpected keys: %s\n", array_keys($a));
		return;
	} else if (!is_array($a)) {
		printf("\$a[0] is not an array, it is %s", gettype($a));
		return;
	}
	$a[0]['test'] = 'foo';
	if ($a[0][0][0]['test'] !== 'foo') {
		echo "Expected the unserialized array to be cyclic\n";
	}
	if (isset($a[0][0]) && $a[0][0]['test'] === 'foo') {
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
