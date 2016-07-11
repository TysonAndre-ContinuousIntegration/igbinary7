--TEST--
Check for reference serialisation
--SKIPIF--
<?php
if(!extension_loaded('igbinary')) {
	echo "skip no igbinary";
}
--FILE--
<?php

function test($type, $variable, $test = true) {
	$serialized = igbinary_serialize($variable);
	$unserialized = igbinary_unserialize($serialized);

	echo $type, "\n";
	echo substr(bin2hex($serialized), 8), "\n";
	echo !$test || $unserialized == $variable ? 'OK' : 'ERROR', "\n";

	ob_start();
	var_dump($variable);
	$dump_exp = ob_get_clean();
	ob_start();
	var_dump($unserialized);
	$dump_act = ob_get_clean();
	debug_zval_dump($variable);
	debug_zval_dump($unserialized);


	if ($dump_act !== $dump_exp) {
		printf("But var dump differs:\nActual\n%sExpected\n%s", $dump_act, $dump_exp);
	}
}

$a = array('foo');

test('array($a, $a)', array($a, $a), true);
// TODO igbinary is unserializing this incorrectly as [$a, $a] instead of [&$a, &$a].
test('array(&$a, &$a)', array(&$a, &$a), true);

$a = array(null);
$b = array(&$a);
$a[0] = &$b;

// this might also be working properly. print_r() does something serializes as array() if the last reference to $b is removed.
test('cyclic $a = array(&array(&$a))', $a, false);
unset($b);
printf("a = ...\n");
debug_zval_dump($a);

--EXPECT--
array($a, $a)
14020600140106001103666f6f06010101
OK
array(&$a, &$a)
1402060025140106001103666f6f0601250101
OK
cyclic $a = array(&array(&$a))
1401060025140106002514010600250101
OK
