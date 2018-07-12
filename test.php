<?php
require_once(dirname(__FILE__) . '/src/Yatrie.php');
require_once(dirname(__FILE__) . '/etc/bmark.php');

$t = new Yatrie();
$ww[] = 'аа';
$ww[] = 'аб';
$ww[] = 'ав';
$ww[] = 'аг';
$ww[] = 'ад';
$ww[] = 'ае';
$ww[] = 'аё';
$ww[] = 'аж';
$ww[] = 'аз';
$ww[] = 'аи';
$ww[] = 'ак';
$ww[] = 'ал';
$ww[] = 'ам';
$ww[] = 'ан';
$ww[] = 'ао';
$ww[] = 'ап';
$ww[] = 'ар';
$ww[] = 'ас';
$ww[] = 'ат';
$ww[] = 'аф';
$ww[] = 'ааа';
$ww[] = 'ааб';
$ww[] = 'аав';
$ww[] = 'ааг';
$ww[] = 'аад';
$ww[] = 'аае';
$ww[] = 'ааё';
$ww[] = 'ааж';
$ww[] = 'ааз';
$ww[] = 'ааи';

foreach ($ww as $w) {
//    list($mask,$ref_id) = $t->node_get(0);
//    $m = decbin($mask);
//    print "$m $ref_id\n";
    $t->trie_add($w);
}
foreach ($ww as $w) {
    $res = $t->trie_check($w);
    if(false === $res){
        print "$w failed\n";
    }
}

//var_dump($t->nodes);
//var_dump($t->refs);