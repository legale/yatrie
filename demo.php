<?php
require_once(dirname(__FILE__) . '/src/Yatrie.php');
require_once(dirname(__FILE__) . '/etc/bmark.php');

$dic = [
    dirname(__FILE__) . '/tiny_headers.txt',
    dirname(__FILE__) . '/tiny_nodes.txt.gz',
    dirname(__FILE__) . '/tiny_refs.txt.gz'
];
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
        $res = $trie->trie_check($argv[1]);
        $res = $res !== false ? " found word: " . $argv[1] . " node_id: $res " : "not found: " . $argv[1];
        echo "$res\n";
}
