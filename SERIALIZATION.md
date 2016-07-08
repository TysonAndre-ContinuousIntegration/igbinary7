`|` means choose from multiple options. Space (` `) means concatenation.

`'xy'` means a byte with leading hex digit of `x`, least significant hex digit of `y`

See `enum igbinary_type` in `igbinary.c`

```
;;;;;;;;;;;;;;;;;
; Scalars
;;;;;;;;;;;;;;;;;;

; single byte (signed or unsigned)
BYTE := '00'..'ff'
; 2 bytes (unsigned or signed)
BYTE2 := BYTE BYTE
; 4 bytes (unsigned or signed)
BYTE4 := BYTE BYTE BYTE BYTE
; 8 bytes
BYTE8 := BYTE BYTE BYTE BYTE BYTE BYTE BYTE BYTE

STRING := '11' LENGTH8 <LENGTH8 BYTE> | '12' LENGTH16 <LENGTH16 BYTE> | '13' LENGTH32 <LENGTH32 BYTE>

LENGTH8 := BYTE
LENGTH16 := BYTE2
LENGTH32 := BYTE4
LENGTH64 := BYTE8

ARRAY_OR_REF := ARRAY|ARRAY_REF

ARRAY := ARRAY8|ARRAY16|ARRAY32

ARRAY8 := '14' LENGTH8 <LENGTH8 ARRAY_ENTRY>

ARRAY_ENTRY := KEY ARRAY_VAL
KEY := LONG|STRING

ULONG := '06' BYTE | '08' BYTE2 | '0a' BYTE4 | '20' BYTE8
; BYTE* != '0....0' (i.e. no -0)
NEGATIVE_LONG := '07' BYTE | '09' BYTE2 | '0b' BYTE4 | '21' BYTE8
LONG := ULONG | NEGATIVE_LONG
ARRAY_VAL := SIMPLE_REF | STRING

; Reference to a previously declared array
ARRAY_REF := '01' BYTE | '02' BYTE2 | '03' BYTE4
; What
SIMPLE_REF := '25' ARRAY_REF
```

```
14
  02 (2 elements)
    0600 (0=>
    25 (is ref)
      17 (Object)
        03 (Name length)
          4f626a Bar
        14 (Array8)
          02 (2 elements)
            11 string key
              01 61 'a' =>
              06 01        1
            11 string key
              01 62 'b' =>
              06 02       2
   06 01 (1 =>
     25 (type_ref)
       22 (should be objref8)
         => 1 (The first object in the hash)

; cyclic $a = array(&array(&$a)). Note that the value $a was passed to the serializer, not the reference &$a, so there is a third array.
14 (array 8)
  01 (1 element)
    0600 0 =>
    25 (is ref)
      14 (array 8)
        01 (1 element)
          0600 (0 => )
          25 (is ref)
            14 (array 8)
              01 (size 1)
                0600 0 =>
                25 (is ref)
                  01 01 (The first array reference in the hash)
