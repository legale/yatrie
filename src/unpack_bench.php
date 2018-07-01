<?php
require ('bmark.php');

function unpack_mod(string $str)
{
    return hexdec(strrev(unpack('h*', $str)[1]));
}

function unpack_48(string $str)
{
    return unpack('P', str_pad($str, 8, "\0"))[1];
}

function pack_48(int $i)
{
    return substr(pack('P', $i), 0, 6);
}


$i = 1234567890;
$str = pack_48($i);

$init['int'] = $i;
$init['str'] = $str;
$init['pack_48_hex'] = bin2hex(pack_48($i));
$init['unpack_48'] = unpack_48($str);
$init['unpack_mod'] = unpack_mod($str);
print_r($init);


//benchmark
$times = 100000;
$res['unpack_48'] = bmark($times, 'unpack_48', $str);
$res['unpack_mod'] = bmark($times, 'unpack_mod', $str);
print_r($res);
