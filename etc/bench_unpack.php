<?php
require_once(dirname(__FILE__) . '/bmark.php');

function unpack_mod(string $str)
{
    return hexdec(strrev(unpack('h*', $str)[1]));
}

function unpack_48_mod2(string $str)
{
    $ar = unpack("C*", $str);
    return ($ar[6] << 40) + ($ar[5] << 32) + ($ar[4] << 24) + ($ar[3] << 16) + ($ar[2] << 8) + ($ar[1]);
}

function unpack_24_mod2(string $str)
{
    $ar = unpack("C*", $str);
    return ($ar[3] << 16) + ($ar[2] << 8) + ($ar[1]);
}

function unpack_24_mod3(string $str)
{
    return (ord($str[2]) << 16) + (ord($str[1]) << 8) + ord($str[0]);
}


function unpack_48_mod3(string $str)
{
    return (ord($str[5]) << 40) + (ord($str[4]) << 32) + (ord($str[3]) << 24) + (ord($str[2]) << 16) + (ord($str[1]) << 8) + ord($str[0]);
}


function unpack_48(string $str)
{
    return unpack('P', str_pad($str, 8, "\0"))[1];
}

function pack_48(int $i)
{
    return substr(pack('P', $i), 0, 6);
}

function pack_24(int $i)
{
    return substr(pack('V', $i), 0, 3);
}

function pack_24_mod(int $i)
{
    return chr($i & 0xFF) . chr(($i >> 8) & 0xFF) . chr(($i >> 16) & 0xFF);
}

function pack_48_mod(int $i)
{
    return chr($i & 0xFF) . chr(($i >> 8) & 0xFF) . chr(($i >> 16) & 0xFF) . chr(($i >> 24) & 0xFF) . chr(($i >> 32) & 0xFF) . chr(($i >> 48) & 0xFF);
}

function unpack_24(string $str)
{
    return unpack('V', str_pad($str, 4, "\0"))[1];
}


$i6 = 1234567890;
$i3 = 15599999;
$str6 = pack_48($i6);
$str3 = pack_24($i3);
$str3_ = pack_24_mod($i3);

// int 3 bytes
$init['times'] = $times = 100000;
$init['int3']['int3'] = $i3;
$init['int3']['str3'] = $str3;
$init['int3']['str3_'] = $str3_;
$init['int3']['pack_24_hex'] = bin2hex(pack_24($i3));
$init['int3']['pack_24_hex_'] = bin2hex(pack_24_mod($i3));
$init['int3']['unpack_24'] = unpack_24($str3);
$init['int3']['unpack_mod'] = unpack_mod($str3);
$init['int3']['unpack_24_mod2'] = unpack_24_mod2($str3);
$init['int3']['unpack_24_mod3'] = unpack_24_mod3($str3);

//int 6 bytes
$init['int6']['int6'] = $i6;
$init['int6']['str6'] = $str6;
$init['int6']['pack_48_hex'] = bin2hex(pack_48($i6));
$init['int6']['unpack_48'] = unpack_48($str6);
$init['int6']['unpack_mod'] = unpack_mod($str6);
$init['int6']['unpack_48_mod2'] = unpack_48_mod2($str6);
$init['int6']['unpack_48_mod3'] = unpack_48_mod3($str6);
print_r($init);


//benchmark
$res['int6']['unpack_48'] = bmark($times, 'unpack_48', $str6);
$res['int6']['unpack_mod'] = bmark($times, 'unpack_mod', $str6);
$res['int6']['unpack_48_mod2'] = bmark($times, 'unpack_48_mod2', $str6);
$res['int6']['unpack_48_mod3'] = bmark($times, 'unpack_48_mod3', $str6);
$res['int6']['pack_48'] = bmark($times, 'pack_48', $i6);
$res['int6']['pack_48_mod'] = bmark($times, 'pack_48_mod', $i6);

$res['int3']['unpack_24'] = bmark($times, 'unpack_24', $str3);
$res['int3']['unpack_mod'] = bmark($times, 'unpack_mod', $str3);
$res['int3']['unpack_24_mod2'] = bmark($times, 'unpack_24_mod2', $str3);
$res['int3']['unpack_24_mod3'] = bmark($times, 'unpack_24_mod3', $str3);
$res['int3']['pack_24'] = bmark($times, 'pack_24', $i3);
$res['int3']['pack_24_mod'] = bmark($times, 'pack_24_mod', $i3);
print_r($res);
