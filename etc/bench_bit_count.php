<?php
require_once(dirname(__FILE__) . '/bmark.php');

function bit_count_hard(int $v, int $length = null)
{
    if ($length > 0) {
        $v &= (2 << $length - 1) - 1;
    }
    $S = [1, 2, 4, 8, 16]; // Magic Binary Numbers
    $B = [0x55555555, 0x33333333, 0x0F0F0F0F, 0x00FF00FF, 0x0000FFFF];
    $c = $v - (($v >> 1) & $B[0]);
    $c = (($c >> $S[1]) & $B[1]) + ($c & $B[1]);
    $c = (($c >> $S[2]) + $c) & $B[2];
    $c = (($c >> $S[3]) + $c) & $B[3];
    $c = (($c >> $S[4]) + $c) & $B[4];
    return $c;
}

function bit_count_hard2(int $v, int $length = null)
{
    if ($length > 0) {
        $v &= (2 << $length - 1) - 1;
    }
    $c = $v - (($v >> 1) & 0x55555555);
    $c = (($c >> 2) & 0x33333333) + ($c & 0x33333333);
    $c = (($c >> 4) + $c) & 0x0F0F0F0F;
    $c = (($c >> 8) + $c) & 0x00FF00FF;
    $c = (($c >> 16) + $c) & 0x0000FFFF;
    return $c;
}

function bit_count_hard3(int $i, int $length = null)
{
    if ($length > 0) {
        $i &= (2 << $length - 1) - 1;
    }
    $S = [1, 2, 4, 8, 16, 32];
    $B = [0x5555555555555555, 0x3333333333333333, 0x0F0F0F0F0F0F0F0F, 0x00FF00FF00FF00FF, 0x0000FFFF0000FFFF, 0x000000FFFFFF];

    $c = $i - (($i >> 1) & $B[0]);
    $c = (($c >> $S[1]) & $B[1]) + ($c & $B[1]);
    $c = (($c >> $S[2]) + $c) & $B[2];
    $c = (($c >> $S[3]) + $c) & $B[3];
    $c = (($c >> $S[4]) + $c) & $B[4];
    $c = (($c >> $S[5]) + $c) & $B[5];

    return $c;
}


function bit_count_pow(int $bmask, int $length = null)
{
    if ($length !== null) {
        $bmask &= pow(2, $length) - 1;
    }
    $cnt = 0;
    while ($bmask != 0) {
        $cnt++;
        $bmask &= $bmask - 1;
    }
    return $cnt;
}


function bit_count_shift(int $bmask, int $length = null)
{
    if ($length > 0) {
        $bmask &= (2 << $length - 1) - 1;
    }
    $cnt = 0;
    while ($bmask != 0) {
        $cnt++;
        $bmask &= $bmask - 1;
    }
    return $cnt;
}


$s = '11111111111111111111111111111111111111111111110';
$i = bindec($s);
$length = 48;
$times = 1000000;

$res['bit_count_pow'][] = bit_count_pow($i, $length);
$res['bit_count_shift'][] = bit_count_shift($i, $length);
$res['bit_count_hard'][] = bit_count_hard($i, $length);
$res['bit_count_hard2'][] = bit_count_hard2($i, $length);
$res['bit_count_hard3'][] = bit_count_hard3($i, $length);

$res['bit_count_pow'][] = bmark($times, 'bit_count_pow', $i, $length);
$res['bit_count_shift'][] = bmark($times, 'bit_count_shift', $i, $length);
$res['bit_count_hard'][] = bmark($times, 'bit_count_hard', $i, $length);
$res['bit_count_hard2'][] = bmark($times, 'bit_count_hard2', $i, $length);
$res['bit_count_hard3'][] = bmark($times, 'bit_count_hard3', $i, $length);

print_r($res);
