<?php
require_once(dirname(__FILE__) . '/src/Yatrie.php');
require_once(dirname(__FILE__) . '/etc/bmark.php');

$trie = new Yatrie();

pow('','')
var_dump( $trie->trie_add('а'));
var_dump( $trie->trie_add('аа'));
var_dump( $trie->trie_add('человекоподобный'));
var_dump(decbin( $trie->node_get_children(61)));
