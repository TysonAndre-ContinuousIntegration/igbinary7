--TEST--
Correctly unserialize scalar refs.
--SKIPIF--
--INI--
igbinary.compact_strings = On
--FILE--
<?php
$a = array(13);
$a[1] = &$a[0];
$a[2] = &$a[1];
$a[3] = &$a[2];

printf("%s\n", serialize($a));
$ig_ser = igbinary_serialize($a);
printf("%s\n", bin2hex($ig_ser));
$ig = igbinary_unserialize($ig_ser);
printf("%s\n", serialize($ig));
$f = &$ig[3];
$f = 'V';
var_dump($ig);
--EXPECT--
a:4:{i:0;i:13;i:1;R:2;i:2;R:2;i:3;R:2;}
000000021404060025060d060125010106022501010603250101
a:4:{i:0;i:13;i:1;R:2;i:2;R:2;i:3;R:2;}
array(4) {
  [0]=>
  &string(1) "V"
  [1]=>
  &string(1) "V"
  [2]=>
  &string(1) "V"
  [3]=>
  &string(1) "V"
}
