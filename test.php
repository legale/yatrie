<?php
require_once(dirname(__FILE__) . '/src/Yatrie.php');
require_once(dirname(__FILE__) . '/etc/bmark.php');

$trie = new Yatrie('dic_tiny.txt');

$word = 'человек';
$word2 = 'человекф';

$last_id = $trie->trie_add($word);
var_dump( $last_id);

print "check $word ";
var_dump( $trie->trie_check($word));

print "check $word2 ";
var_dump( $trie->trie_check($word2));
$bin = strrev(decbin( $trie->node_get_children($last_id)));
print "$bin\n";
$len = strlen($bin);

for($i = 0; $i < $len; ++$i){
    if($bin[$i] === '1'){
        $index = $i+1;
        print "$index\n";
    }
}
