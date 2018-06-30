<?php
require(dirname(__FILE__) . '/Yatrie.php');
require(dirname(__FILE__) . '/bmark.php');

$dic = dirname(__FILE__) . '/dic_tiny.txt';
$trie = new Yatrie($dic);


if (empty($argv[1])) {
    exit("Please enter a word to search!");
}


switch ($argv[1]) {
    case 'bmark':
        $cnt = count($argv);
        $cnt < 3 ?? exit("3 arguments expected\n");

        $res = bmark(['times'=> $argv[2], 'global'=>'$trie'], '$trie->trie_check', $argv[3]);
        echo "elapsed time: $res\n";
        break;


    default:
        $res = $trie->trie_check($argv[1]) ? " found word: " . $argv[1] : "not found: " . $argv[1];
        echo "$res\n";
}
