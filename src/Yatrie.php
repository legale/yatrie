<?php

/**
 * Class Yatrie
 */
class Yatrie
{

    /**
     * @var
     */
    public $dic;
    /**
     * @var int
     */
    public $char_count;
    /**
     * @var float|int
     */
    public $size_refs;
    /**
     * @var float|int
     */
    public $size_node;
    /**
     * this is node id variable increment when creating new node by node_make() function
     * minus 1 start value because first id is 0
     * @var int
     */
    public $id = -1;
    /**
     * @var int
     */
    public $size_block = 4096; //number of nodes in 1 dictionary block
    public $power = 12; //power of two to get $size_block value
    public $mod = 4095; //value to get $i % $size_block. ($i & $mod === $i % $size_block)
    /**
     * @var int
     */
    public $size_mask = 6; //6 bytes are 48 bits. Children bitmask and "last word letter flag"
    /**
     * @var int
     */
    public $size_ref = 3; // reference size. Each node has 6 bytes mask + references * char qty


    /**
     * this is sample codepage used for tests
     * public $codepage = array('а' => 1, 'б' => 2, 'в' => 3, 'г' => 4, 'д' => 5, 'flag' => 6);
     * public $codepage_index = array('а' => 0, 'б' => 1, 'в' => 2, 'г' => 3, 'д' => 4);
     * @var array
     */
    public $codepage = array('а' => 1, 'б' => 2, 'в' => 3, 'г' => 4,
        'д' => 5, 'е' => 6, 'ё' => 7, 'ж' => 8, 'з' => 9, 'и' => 10, 'й' => 11, 'к' => 12, 'л' => 13, 'м' => 14,
        'н' => 15, 'о' => 16, 'п' => 17, 'р' => 18, 'с' => 19, 'т' => 20, 'у' => 21, 'ф' => 22, 'х' => 23, 'ц' => 24,
        'ч' => 25, 'ш' => 26, 'щ' => 27, 'ъ' => 28, 'ы' => 29, 'ь' => 30, 'э' => 31, 'ю' => 32, 'я' => 33, 0 => 34,
        1 => 35, 2 => 36, 3 => 37, 4 => 38, 5 => 39, 6 => 40, 7 => 41, 8 => 42, 9 => 43, '-' => 44,
        '\'' => 45, '’' => 46, 'flag' => 47);

    /**
     * @var array
     */
    public $codepage_index = array('а' => 0, 'б' => 1, 'в' => 2, 'г' => 3, 'д' => 4, 'е' => 5, 'ё' => 6, 'ж' => 7,
        'з' => 8, 'и' => 9, 'й' => 10, 'к' => 11, 'л' => 12, 'м' => 13, 'н' => 14, 'о' => 15, 'п' => 16, 'р' => 17,
        'с' => 18, 'т' => 19, 'у' => 20, 'ф' => 21, 'х' => 22, 'ц' => 23, 'ч' => 24, 'ш' => 25, 'щ' => 26, 'ъ' => 27,
        'ы' => 28, 'ь' => 29, 'э' => 30, 'ю' => 31, 'я' => 32, 0 => 33, 1 => 34, 2 => 35, 3 => 36, 4 => 37, 5 => 38,
        6 => 39, 7 => 40, 8 => 41, 9 => 42, '-' => 43, '\'' => 44, '’' => 45,);


    /**
     * Yatrie constructor.
     * @param string|null $dic
     */
    public function __construct(string $dic = null)
    {
        $this->char_count = count($this->codepage_index);    //codepage
        //each node are 6 bytes "children mask"  + 2 bytes * chars qty "node refs"
        $this->size_refs = $this->size_ref * $this->char_count;
        $this->size_node = $this->size_mask + $this->size_refs;
        $this->init_trie($dic);

        return;
    }

    /**
     * @param string|null $dic
     */
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

    /**
     * @return float|int
     */
    public function node_get_last_id()
    {

        $block = end($this->dic);
        $block_last = key($this->dic);
        $len = strlen($block);
        $cnt = $len / $this->size_node - 1;
        return $block_last * $this->size_block + $cnt;
    }

    /**
     * @return bool
     */
    public function layer_make_empty()
    {
        //create empty dic
        $this->dic = array(0 => '');
        for ($i = 0; $i < $this->char_count; ++$i) {
            $this->node_make();
        }
        return true;
    }

    /**
     * @param int $size
     * @return string
     */
    public function str_pad_null(int $size = 0)
    {
        return str_repeat("\0", $size);
    }


    /**
     * this method return trie dictionary block
     * @param int $id
     * @return mixed
     */
    public function &trie(int $id)
    {
        $block = $id >> $this->power;
        return $this->dic[$block];
    }

    /**
     * @param string|null $mask
     * @param string|null $refs
     * @return int
     */
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

    /**
     * @param int $id
     * @param int $char_index
     * @param int $ref
     * @return string
     */
    public function node_save_ref(int $id, int $char_index, int $ref)
    {
        $trie = &$this->trie($id);
        //node id relative to block
        $id_rel = $id & $this->mod;
        $offset = $id_rel * $this->size_node + $this->size_mask + $this->size_ref * $char_index;
//         print __METHOD__." id:$id  id_rel:$id_rel char_index:$char_index offset:$offset ref:$ref\n";
        $sub = $this->pack_24($ref);
        return $trie = substr($trie, 0, $offset) . $sub . substr($trie, $offset + $this->size_ref);
    }

    /**
     * @param int $parent_id
     * @param string $char
     * @return bool|int
     */
    public function node_char_get_ref(int $parent_id, string $char)
    {
//        print "parent:$parent_id char:$char\n";
        $mask = $this->node_get_children($parent_id);

        if ($this->bit_get($mask, $this->codepage[$char])) {
            return $this->node_get_ref($parent_id, $this->codepage_index[$char]);
        } else {
            return false;
        }
    }

    /**
     * @param int $id
     * @param int $char_index
     * @return int
     */
    public function node_get_ref(int $id, int $char_index)
    {
        $trie = &$this->trie($id);
        //node id relative to block
        $id_rel = $id & $this->mod;
//        print "id:$id id_rel:$id_rel char_index:$char_index\n";

        $offset = $id_rel * $this->size_node + $this->size_mask + $this->size_ref * $char_index;
//        print __METHOD__ . " id:$id id_rel:$id_rel char_index:$char_index offset:$offset\n";

        $res = $this->unpack_24(substr($trie, $offset, $this->size_ref));
        return $res;
    }

    /**
     * @param int $id
     * @param int $mask
     * @return string
     */
    public function node_save_children(int $id, int $mask)
    {
        $trie = &$this->trie($id);
        //node id relative to block
        $id_rel = $id & $this->mod;
        $offset = $id_rel * $this->size_node;
        return $trie = substr($trie, 0, $offset) . $this->pack_48($mask) . substr($trie, $offset + $this->size_mask);
    }


    /**
     * @param int $id
     * @return int
     */
    public function node_get_children(int $id)
    {
        $trie = &$this->trie($id);
        //node id relative to block
        $id_rel = $id & $this->mod;
        $offset = $id_rel * $this->size_node;
//        print "len: " .strlen($trie)."\n";
//        print __METHOD__." id:$id id_rel: $id_rel offset:$offset \n";
        return $this->unpack_48(substr($trie, $offset, $this->size_mask));
    }

    /**
     * @param int $i
     * @return string
     */
    public function pack_16(int $i)
    {
        return pack('v', $i);
    }

    /**
     * @param string $str
     * @return mixed
     */
    public function unpack_16(string $str)
    {
        return unpack('v', $str)[1];
    }

    /**
     * @param int $i
     * @return string
     */
    public function pack_32(int $i)
    {
        return pack('V', $i);
    }

    /**
     * @param string $str
     * @return mixed
     */
    public function unpack_32(string $str)
    {
        return unpack('V', $str)[1];
    }

    /**
     * @param int $i
     * @return string
     */
    public function pack_64(int $i)
    {
        return pack('P', $i);
    }

    /**
     * @param string $str
     * @return mixed
     */
    public function unpack_64(string $str)
    {
        return unpack('P', $str)[1];
    }

    /**
     * @param int $i
     * @return bool|string
     */
    public function pack_48(int $i)
    {
        return substr(pack('P', $i), 0, 6);
    }

    /**
     * @param string $str
     * @return float|int
     */
    public function unpack_mod(string $str)
    {
        return hexdec(strrev(unpack('h*', $str)[1]));
    }


    /**
     * @param int $i
     * @return bool|string
     */
    public function pack_24(int $i)
    {
        return substr(pack('V', $i), 0, 3);
    }


    /**
     * @param string $str
     * @return int
     */
    function unpack_24(string $str)
    {
        return (ord($str[2]) << 16) + (ord($str[1]) << 8) + ord($str[0]);
    }


    /**
     * @param string $str
     * @return int
     */
    function unpack_48(string $str)
    {
        return (ord($str[5]) << 40) + (ord($str[4]) << 32) + (ord($str[3]) << 24) + (ord($str[2]) << 16) + (ord($str[1]) << 8) + ord($str[0]);
    }

    /**
     * @param int $id
     * @return string
     */
    public function node_clear_char_flag(int $id)
    {
        $mask = $this->node_get_children($id);
        $this->bit_clear($mask, $this->codepage['flag']);
        return $this->node_save_children($id, $mask);
    }

    /**
     * @param int $id
     * @return bool
     */
    public function node_get_char_flag(int $id)
    {
        $mask = $this->node_get_children($id);
        return $this->bit_get($mask, $this->codepage['flag']);
    }

    /**
     * @param int $id
     * @return string
     */
    public function node_set_char_flag(int $id)
    {
//        print "add flag id:$id\n";
        $mask = $this->node_get_children($id);
        $this->bit_set($mask, $this->codepage['flag']);
        return $this->node_save_children($id, $mask);
    }

    /**
     * @param string $word
     * @return string
     */
    public function trie_list(string $word)
    {
        $abc = $this->str_split_rus_mod($word);
        $cnt = count($abc);

        //this is the first letter
        $id = $this->codepage_index[$abc[0]];

        for ($i = 1; $i < $cnt; ++$i) {
            $id = $this->node_char_get_ref($id, $abc[$i]);
        }

        $mask = $this->node_get_children($id);
        return decbin($mask);
    }


    /**
     * @param string $word
     * @return string
     */
    public function trie_add(string $word)
    {
//        print "word: $word\n";
        $abc = $this->str_split_rus_mod($word);
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
        $this->node_set_char_flag($id);
        return $id;
    }

    /**
     * @param int $parent_id
     * @param string $char
     * @return int
     */
    public function trie_add_char(int $parent_id, string $char)
    {
//print "parent:$parent_id char:$char\n";

        $mask = $this->node_get_children($parent_id);
        $str = decbin($mask);
//print "char: $char parent_id: $parent_id  mask: $str\n";

        if ($this->bit_get($mask, $this->codepage[$char])) {
            $ref_id = $this->node_get_ref($parent_id, $this->codepage_index[$char]);
//            print "ref:$id\n";
        } else {
            $this->bit_set($mask, $this->codepage[$char]);
            $this->node_save_children($parent_id, $mask);
            $str = decbin($mask);
//print "saved char: $char mask: $str\n";
            $mask = $this->node_get_children($parent_id);
            $str = decbin($mask);
//print "saved char: $char mask: $str\n";

            $this->node_make();
            $ref_id = $this->id;
            $this->node_save_ref($parent_id, $this->codepage_index[$char], $ref_id);

//            $ref = $this->node_get_ref($parent_id, $this->char_index($char));
//            print "after create: $id ref:$ref\n";
        }

        return $ref_id;
    }


    /**
     * @param string $word
     * @return string
     */
    public function trie_remove(string $word)
    {
        $abc = $this->str_split_rus_mod($word);
        $cnt = count($abc);

        $id = $this->codepage_index[$abc[0]];

        for ($i = 1; $i < $cnt; ++$i) {
            //get children
            $mask = $this->node_get_children($id);
            //var_dump($this->bit_count($mask));
            //count children
            //if children are less than 2 we can delete this node
            if ($this->bit_count($mask) < 2) {
                $this->node_save_children($id, 0);
            }

            $id = $this->node_get_ref($id, $this->codepage_index[$abc[$i]]);
        }
        return $this->node_clear_char_flag($id);
    }

    /**
     * @param string $word
     * @return bool
     */
    public function trie_check(string $word)
    {
        $abc = $this->str_split_rus_mod($word);
        $cnt = count($abc);

        $id = $this->codepage_index[$abc[0]];

        for ($i = 1; $i < $cnt; ++$i) {
            $id = $this->node_char_get_ref($id, $abc[$i]);
        }

        return $this->node_get_char_flag($id);
    }


    /**
     * @param int $bitmap
     * @param int $bit
     * @return int
     */
    private function bit_set(int &$bitmap, int $bit)
    {
        $bitmap |= 1 << $bit - 1;
        return $bitmap;
    }

    /**
     * @param int $bitmap
     * @param int $bit
     * @return bool|int
     */
    private function bit_clear(int &$bitmap, int $bit)
    {
        $bitmap &= ~(1 << $bit - 1);
        return $bitmap;
    }

    /**
     * @param int $bitmap
     * @param int $bit
     * @return bool
     */
    private function bit_get(int $bitmap, int $bit)
    {
        return (bool)(($bitmap >> $bit - 1) & 1);
    }


    /**
     * @param int $bmask
     * @return int
     */
    private function bit_count(int $bmask)
    {
        $cnt = 0;
        while ($bmask != 0) {
            $cnt++;
            $bmask &= $bmask - 1;
        }
        return $cnt;
    }

    /**
     * @param string $word
     * @return array|bool
     */
    public function str_split_rus_mod(string $word)
    {
        $byte3 = array('’' => 'e28099',);

        $byte2 = array('а' => 'd0b0', 'б' => 'd0b1', 'в' => 'd0b2', 'г' => 'd0b3', 'д' => 'd0b4',
            'е' => 'd0b5', 'ё' => 'd191', 'ж' => 'd0b6', 'з' => 'd0b7', 'и' => 'd0b8', 'й' => 'd0b9', 'к' => 'd0ba',
            'л' => 'd0bb', 'м' => 'd0bc', 'н' => 'd0bd', 'о' => 'd0be', 'п' => 'd0bf', 'р' => 'd180', 'с' => 'd181',
            'т' => 'd182', 'у' => 'd183', 'ф' => 'd184', 'х' => 'd185', 'ц' => 'd186', 'ч' => 'd187', 'ш' => 'd188',
            'щ' => 'd189', 'ъ' => 'd18a', 'ы' => 'd18b', 'ь' => 'd18c', 'э' => 'd18d', 'ю' => 'd18e', 'я' => 'd18f');

        $byte1 = array('-' => '2d', '\'' => 27, '0' => 30, '1' => 31, '2' => 32, '3' => 33, '4' => 34, '5' => 35,
            '6' => 36, '7' => 37, '8' => 38, '9' => 39,);

        $res = array();
        for ($i = 0, $len = strlen($word); $i < $len;) {
            $sub = substr($word, $i, 2);
            if (isset($byte2[$sub])) {
                $res[] = $sub;
                $i += 2;
                continue;
            }

            $sub = substr($word, $i, 1);
            if (isset($byte1[$sub])) {
                $res[] = $sub;
                $i += 1;
                continue;
            }

            $sub = substr($word, $i, 3);
            if (isset($byte3[$sub])) {
                $res[] = $sub;
                $i += 3;
                continue;
            }
            return false; //if we are here then unknown symbol detected
        }

        return $res;
    }


    /**
     * @param string $word
     * @return array|bool
     */
    private function str_split_rus(string $word)
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