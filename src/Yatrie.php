<?php

class Yatrie
{
    public $dic, $char_count, $size_refs, $size_node;
    //this is node id variable increment when creating new node by node_make() function
    //minus 1 start value because first id is 0
    public $id = -1;
    public $size_block = 3000; //number of nodes in 1 dictionary block
    public $size_mask = 6; //6 bytes are 48 bits. Children bitmask and "last word letter flag"
    public $size_ref = 3; // reference size. Each node has 6 bytes mask + references * char qty

//this is sample codepage used for tests1
//    public $codepage = array('а' => 1, 'б' => 2, 'в' => 3, 'г' => 4, 'д' => 5, 'flag' => 6);
//    public $codepage_index = array('а' => 0, 'б' => 1, 'в' => 2, 'г' => 3, 'д' => 4);


    public $codepage = array('а' => 1, 'б' => 2, 'в' => 3, 'г' => 4,
        'д' => 5, 'е' => 6, 'ё' => 7, 'ж' => 8, 'з' => 9, 'и' => 10, 'й' => 11, 'к' => 12, 'л' => 13, 'м' => 14,
        'н' => 15, 'о' => 16, 'п' => 17, 'р' => 18, 'с' => 19, 'т' => 20, 'у' => 21, 'ф' => 22, 'х' => 23, 'ц' => 24,
        'ч' => 25, 'ш' => 26, 'щ' => 27, 'ъ' => 28, 'ы' => 29, 'ь' => 30, 'э' => 31, 'ю' => 32, 'я' => 33, 0 => 34,
        1 => 35, 2 => 36, 3 => 37, 4 => 38, 5 => 39, 6 => 40, 7 => 41, 8 => 42, 9 => 43, '-' => 44,
        '\'' => 45, '’' => 46, 'flag' => 47);

    public $codepage_index = array('а' => 0, 'б' => 1, 'в' => 2, 'г' => 3, 'д' => 4, 'е' => 5, 'ё' => 6, 'ж' => 7,
        'з' => 8, 'и' => 9, 'й' => 10, 'к' => 11, 'л' => 12, 'м' => 13, 'н' => 14, 'о' => 15, 'п' => 16, 'р' => 17,
        'с' => 18, 'т' => 19, 'у' => 20, 'ф' => 21, 'х' => 22, 'ц' => 23, 'ч' => 24, 'ш' => 25, 'щ' => 26, 'ъ' => 27,
        'ы' => 28, 'ь' => 29, 'э' => 30, 'ю' => 31, 'я' => 32, 0 => 33, 1 => 34, 2 => 35, 3 => 36, 4 => 37, 5 => 38,
        6 => 39, 7 => 40, 8 => 41, 9 => 42, '-' => 43, '\'' => 44, '’' => 45,);


    public function __construct(string $dic = '')
    {
        $this->char_count = count($this->codepage_index);    //codepage
        //each node are 6 bytes "children mask"  + 2 bytes * chars qty "node refs"
        $this->size_refs = $this->size_ref * $this->char_count;
        $this->size_node = $this->size_mask + $this->size_refs;
        $this->init_trie($dic);

        return;
    }

    public function init_trie(string $dic = null)
    {
        if (empty($dic)) {
            $this->layer_make_empty();
        } else {
            $fp = gzopen($dic, 'r');
            $i = 0;
            $size = $this->size_block * $this->size_node;
            while (!feof($fp)) {
                $this->dic[$i] = gzread($fp, $size);
                ++$i;
            }
            gzclose($fp);
            $this->id = $this->node_get_last_id();
        }

    }

    public function node_get_last_id()
    {
        if (!is_array($this->dic)) {
            $len = strlen($this->dic);
            $cnt = $len / $this->size_node - 1;
            return $cnt;
        }
        $block = reset($this->dic);
        $block_last = key($this->dic);
        $len = strlen($block);
        $cnt = $len / $this->size_node - 1;
        return $block_last * $this->size_block + $cnt;
    }

    public function layer_make_empty()
    {
        //create empty dic
        $this->dic = array(0 => '');
        for ($i = 0; $i < $this->char_count; ++$i) {
            $this->node_make();
        }
//        print " layer make trie len: ".strlen($this->trie)."\n";
        return true;
    }

    public function str_pad_null(int $size)
    {
        return str_pad('', $size, "\0");
    }

    //this method return trie dictionary block
    public function &trie(int $id = null)
    {
        if (!is_array($this->dic)) {
            return $this->dic;
        } else if (isset($id)) {
            $block = (int)floor($this->id / $this->size_block);
        } else {
            end($this->dic);
            $block = key($this->dic);
        }
        return $this->dic[$block];
    }

    public function node_make(string $mask = null, string $refs = null)
    {
        $trie = &$this->trie(++$this->id);
        $trie .= $mask ?? $this->str_pad_null($this->size_mask);
        $trie .= $refs ?? $this->str_pad_null($this->size_refs);
//        if ($this->id % 30000 === 0) {
//            print "created node id: " . $this->id . "\n";
//        }

        return $this->id;
    }

    public function node_save_ref(int $id, int $char_index, int $ref)
    {
        $trie = &$this->trie($id);
        //node id relative to block
        $id_rel = $id % $this->size_block;
        $offset = $id_rel * $this->size_node + $this->size_mask + $this->size_ref * $char_index;
//         print __METHOD__." id:$id  id_rel:$id_rel char_index:$char_index offset:$offset ref:$ref\n";
        $sub = $this->pack_24($ref);
        return $trie = substr($trie, 0, $offset) . $sub . substr($trie, $offset + $this->size_ref);
    }

    public function node_get_ref(int $id, int $char_index)
    {
        $trie = &$this->trie($id);
        //node id relative to block
        $id_rel = $id % $this->size_block;
//        print "id:$id id_rel:$id_rel char_index:$char_index\n";

        $offset = $id_rel * $this->size_node + $this->size_mask + $this->size_ref * $char_index;
//        print __METHOD__ . " id:$id id_rel:$id_rel char_index:$char_index offset:$offset\n";

        $res = $this->unpack_24(substr($trie, $offset, $this->size_ref));
        return $res;
    }

    public function node_save_children(int $id, int $mask)
    {
        $trie = &$this->trie($id);
        //node id relative to block
        $id_rel = $id % $this->size_block;
        $offset = $id_rel * $this->size_node;
        return $trie = substr($trie, 0, $offset) . $this->pack_48($mask) . substr($trie, $offset + $this->size_mask);
    }


    public function node_get_children(int $id)
    {
        $trie = &$this->trie($id);
        //node id relative to block
        $id_rel = $id % $this->size_block;
        $offset = $id_rel * $this->size_node;
//        print "len: " .strlen($trie)."\n";
//        print __METHOD__." id:$id id_rel: $id_rel offset:$offset \n";
        return $this->unpack_48(substr($trie, $offset, $this->size_mask));
    }

    public function pack_16(int $i)
    {
        return pack('v', $i);
    }

    public function unpack_16(string $str)
    {
        return unpack('v', $str)[1];
    }

    public function pack_32(int $i)
    {
        return pack('V', $i);
    }

    public function unpack_32(string $str)
    {
        return unpack('V', $str)[1];
    }

    public function pack_64(int $i)
    {
        return pack('P', $i);
    }

    public function unpack_64(string $str)
    {
        return unpack('P', $str)[1];
    }

    public function pack_48(int $i)
    {
        return substr(pack('P', $i), 0, 6);
    }

    public function unpack_48(string $str)
    {
        return unpack('P', str_pad($str, 8, "\0"))[1];
    }

    public function pack_24(int $i)
    {
        return substr(pack('V', $i), 0, 3);
    }

    public function unpack_24(string $str)
    {
        return unpack('V', str_pad($str, 4, "\0"))[1];
    }


    public function node_clear_char_flag(int $id)
    {
        $mask = $this->node_get_children($id);
        $this->bit_clear($mask, $this->char_number('flag'));
        return $this->node_save_children($id, $mask);
    }

    public function node_get_char_flag(int $id)
    {
        $mask = $this->node_get_children($id);
        return $this->bit_check($mask, $this->char_number('flag'));
    }

    public function node_set_char_flag(int $id)
    {
//        print "add flag id:$id\n";
        $mask = $this->node_get_children($id);
        $this->bit_set($mask, $this->char_number('flag'));
        return $this->node_save_children($id, $mask);
    }

    public function trie_list(string $word)
    {
        $abc = $this->str_split_rus($word);
        $cnt = count($abc);

        //this is the first letter
        $id = $this->codepage_index[$abc[0]];

        for ($i = 1; $i < $cnt; ++$i) {
            $id = $this->trie_get_char($id, $abc[$i]);
        }

        $mask = $this->node_get_children($id);
        return decbin($mask);
    }

    public function node_foreach(int $id)
    {
        $res = array();
        $mask = $this->node_get_children($id);
        $cnt = count($this->codepage_index) + 1;
        for ($i = 1; $i < $cnt; ++$i) {
            if($this->bit_check($mask,$i)){
                $res[] = $i;
            }
        }
        return $res;
    }

    public function trie_add(string $word)
    {
//        print "word: $word\n";
        $abc = $this->str_split_rus($word);
        $cnt = count($abc);

        //this is the first letter
        $id = $this->codepage_index[$abc[0]];
//print "first letter index:$id\n";

        //we save second char to the first letter node etc
        for ($i = 1; $i < $cnt; ++$i) {
            $id = $this->trie_add_char($id, $abc[$i]);
        }
        //add last char flag for the last char
//print "last char $id\n";
        return $this->node_set_char_flag($id);
    }


    public function trie_get_char(int $parent_id, string $char)
    {
//        print "parent:$parent_id char:$char\n";

        $mask = $this->node_get_children($parent_id);

        if ($this->bit_check($mask, $this->char_number($char))) {
            return $this->node_get_ref($parent_id, $this->char_index($char));
        } else {
            return false;
        }
    }

    public function trie_add_char(int $parent_id, string $char)
    {
//print "parent:$parent_id char:$char\n";

        $mask = $this->node_get_children($parent_id);
        $str = decbin($mask);
//print "char: $char parent_id: $parent_id  mask: $str\n";

        if ($this->bit_check($mask, $this->char_number($char))) {
            $id = $this->node_get_ref($parent_id, $this->char_index($char));
//            print "ref:$id\n";
        } else {
            $this->bit_set($mask, $this->char_number($char));
            $this->node_save_children($parent_id, $mask);
            $str = decbin($mask);
//print "saved char: $char mask: $str\n";
            $mask = $this->node_get_children($parent_id);
            $str = decbin($mask);
//print "saved char: $char mask: $str\n";

            $this->node_make();
            $id = $this->id;
            $this->node_save_ref($parent_id, $this->char_index($char), $this->id);

//            $ref = $this->node_get_ref($parent_id, $this->char_index($char));
//            print "after create: $id ref:$ref\n";
        }

        return $id;
    }


    public function trie_remove(string $word)
    {
        $abc = $this->str_split_rus($word);
        $cnt = count($abc);

        $id = $this->codepage_index[$abc[0]];

        for ($i = 1; $i < $cnt; ++$i) {
            //get children
            $mask = $this->node_get_children($id);
            //count children
            //if children are more than one we can delete this node
            if ($this->bit_count($mask) > 1) {
                $this->node_save_children($id, 0);
            }

            $id = $this->trie_get_char($id, $abc[$i]);
        }
        return $this->node_clear_char_flag($id);
    }

    public function trie_check(string $word)
    {
        $abc = $this->str_split_rus($word);
        $cnt = count($abc);

        $id = $this->codepage_index[$abc[0]];

        for ($i = 1; $i < $cnt; ++$i) {
            $id = $this->trie_get_char($id, $abc[$i]);
        }

        return $this->node_get_char_flag($id);
    }


    function char_index(string $char)
    {
        return isset($this->codepage[$char]) ? $this->codepage_index[$char] : false;
    }


    function char_number(string $char)
    {
        return isset($this->codepage[$char]) ? $this->codepage[$char] : false;
    }

    function bit_set(int &$bitmap, int $bit)
    {
        $bitmap |= 1 << $bit - 1;
        return $bitmap;
    }

    function bit_clear(int &$bitmap, int $bit)
    {
        $bitmap &= ~(1 << $bit - 1);
        return $bitmap;
    }

    function bit_check(int &$bitmap, int $bit)
    {
        return (bool)(($bitmap >> $bit - 1) & 1);
    }


    function bit_count(int $bmask)
    {
        $cnt = 0;
        while ($bmask != 0) {
            $cnt++;
            $bmask &= $bmask - 1;
        }
        return $cnt;
    }

    public function str_split_rus(string $word)
    {
        $byte3 = array('e28099' => '’');

        $byte2 = array('d0b0' => 'а', 'd0b1' => 'б', 'd0b2' => 'в', 'd0b3' => 'г', 'd0b4' => 'д',
            'd0b5' => 'е', 'd191' => 'ё', 'd0b6' => 'ж', 'd0b7' => 'з', 'd0b8' => 'и', 'd0b9' => 'й',
            'd0ba' => 'к', 'd0bb' => 'л', 'd0bc' => 'м', 'd0bd' => 'н', 'd0be' => 'о', 'd0bf' => 'п',
            'd180' => 'р', 'd181' => 'с', 'd182' => 'т', 'd183' => 'у', 'd184' => 'ф', 'd185' => 'х',
            'd186' => 'ц', 'd187' => 'ч', 'd188' => 'ш', 'd189' => 'щ', 'd18a' => 'ъ', 'd18b' => 'ы',
            'd18c' => 'ь', 'd18d' => 'э', 'd18e' => 'ю', 'd18f' => 'я',);

        $byte1 = array(
            '2d' => '-',
            27 => '\'',
            30 => 0,
            31 => 1,
            32 => 2,
            33 => 3,
            34 => 4,
            35 => 5,
            36 => 6,
            37 => 7,
            38 => 8,
            39 => 9,
        );

        $res = array();
        for ($i = 0, $len = strlen($word); $i < $len;) {
            $hex = bin2hex(substr($word, $i, 2));
            if (isset($byte2[$hex])) {
                $res[] = $byte2[$hex];
                $i += 2;
                continue;
            }

            $hex = bin2hex(substr($word, $i, 1));
            if (isset($byte1[$hex])) {
                $res[] = (string)$byte1[$hex];
                $i += 1;
                continue;
            }

            $hex = bin2hex(substr($word, $i, 3));
            if (isset($byte3[$hex])) {
                $res[] = $byte3[$hex];
                $i += 3;
                continue;
            }
            return false; //if we are here then unknown symbol detected
        }
        return $res;
    }


}