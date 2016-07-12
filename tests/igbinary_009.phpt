--TEST--
Check for reference serialisation
--SKIPIF--
<?php
if(!extension_loaded('igbinary')) {
	echo "skip no igbinary";
}
--FILE--
<?php 

function test_cyclic2_serialization($type, $variable) {
	
	$serialized = igbinary_serialize($variable);
	$unserialized = igbinary_unserialize($serialized);
	if ($unserialized !== $unserialized[0][0]) {
		echo "$type Not cyclic\n"
	} else if ($unserialized === $unserialized[])
}
function test($type, $variable, $normalize = false) {
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

	if ($normalize) {
		$dump_act = preg_replace('/&array/', 'array', $dump_act);
		$dump_exp = preg_replace('/&array/', 'array', $dump_exp);
	}

	if ($dump_act !== $dump_exp) {
		echo "But var dump differs:\nActual:\n", $dump_act, "\nExpected\n", $dump_exp, "\n";
		if ($normalize) {
			echo "(Was normalized)\n";
		}
	}
	
	$serialize_act = serialize($unserialized);
	$serialize_exp = serialize($variable);
	if ($serialize_act !== $serialize_exp) {
		echo "But serialize differs:\nActual:\n", $serialize_act, "\nExpected:\n", $serialize_exp, "\n";
	}
}

$a = array('foo');

test('array($a, $a)', [$a, $a]);
test('array(&$a, &$a)', [&$a, &$a]);

$a = array(null);
$b = array(&$a);
$a[0] = &$b;
unset($b);
$a = [[&$a]];

test('cyclic $a = array(&array(&$a))', $a, true);
unset($a);
unset($b);
$a = null;
$a = [[&$a]];
test('cyclic $a = array(array(&$a))', $a);
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
cyclic $a = array(array(&$a))
140106001401060025140106000101
OK
