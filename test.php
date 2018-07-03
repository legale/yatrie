<?php
require_once(dirname(__FILE__) . '/src/Yatrie.php');
require_once(dirname(__FILE__) . '/etc/bmark.php');

$trie = new Yatrie();

$trie->trie_add('аа');
$trie->trie_add('а');
var_dump(decbin( $trie->node_get_children(0)));
