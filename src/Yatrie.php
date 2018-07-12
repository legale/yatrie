<?php

/**
 * Class Yatrie
 */
class Yatrie
{

    public $deal = []; //deallocated memory array to store deallocated memory blocks for the future reuse
    /**
     * @var array
     */
    public $refs = [];
    /**
     * @var array
     */
    public $nodes = [];
    /**
     * @var int
     */
    public $char_count;
    /**
     * @var int
     */
    public $size_refs;
    /**
     * @var int
     */
    public $size_node;
    /**
     * this is node id variable increment when creating new node by node_make() function
     * minus 1 start value because first id is 0
     * @var int
     */
    public $id_node = -1;
    public $id_ref = -1;
    /**
     * @var int
     */
    public $size_block = 65536; //number of nodes in 1 nodes array block
    public $power = 16; //power of two to get $size_block value
    public $mod = 65535; //value to get $i % $size_block. ($i & $mod === $i % $size_block but faster)

    public $size_block_refs = 131072; //number of refs in 1 refs array block
    public $power_refs = 17; //power of two to get $size_block_refs value
    public $mod_refs = 131071; //value to get $i % $size_block_refs. ($i & $mod_refs === $i % $size_block_refs but faster)

    /**
     * @var int
     */
    public $size_mask = 6; //6 bytes are 48 bits. Children bitmask and "last word letter flag"

    /**
     * @var int
     */
    public $size_ref = 3; // reference size. Each node has 6 bytes mask + references * char qty
    /**
     * @var int
     **/
    public $size_ref_id = 3; // node reference size.


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
    public function __construct(array $dic = null)
    {
        $this->char_count = count($this->codepage_index);
        $this->size_node = $this->size_mask + $this->size_ref_id; //each node is a node mask + node reference to refs
        $this->init_trie($dic);
    }

    /**
     * trie initialization method
     * @param string|null $dic
     */
    public function init_trie(array $dic = null)
    {
        if (empty($dic)) {
            $this->layer_make_empty();
        } else {

            //load last created ids from headers file
            list($this->id_node, $this->id_ref) = unserialize(file_get_contents($dic[0]));

            //load nodes
            $string = '';
            $fp = gzopen($dic[1], 'r');
            while (!feof($fp)) {
                $string .= gzread($fp, 999999);
            }
            gzclose($fp);
            $this->nodes = unserialize($string);

            //load refs
            $string = '';
            $fp = gzopen($dic[2], 'r');
            while (!feof($fp)) {
                $string .= gzread($fp, 999999);
            }
            gzclose($fp);
            $this->refs = unserialize($string);

        }

    }


    /**
     * create first node sequence nodes from 0 to 45
     * @return bool
     */
    public function layer_make_empty()
    {
        for ($i = 0; $i < $this->char_count; ++$i) {
            $this->node_make();
        }
        $this->nodes[0] = $this->str_pad_null($this->size_node * $this->char_count);
        return true;
    }

    /**
     * null byte string create method
     * @param int $size
     * @return string
     */
    public function str_pad_null(int $size = 0)
    {
        return str_repeat("\0", $size);
    }

    public function ref_get_raw(string &$block, int $ref_id, int $pos)
    {
        $offset = $this->refs_offset($ref_id);
        return $this->unpack_24(substr($block, $offset + $pos * $this->size_ref, $this->size_ref));
    }

    public function ref_get(int $ref_id, int $pos)
    {
        $block = &$this->refs_block($ref_id);
        return $this->ref_get_raw($block, $ref_id, $pos);
    }

    public function refs_get(int $ref_id, int $size)
    {
        $block = &$this->refs_block($ref_id);
        return substr($block, $this->refs_offset($ref_id), $size);
    }

    public function refs_offset(int $ref_id)
    {
        return ($ref_id & $this->mod) * $this->size_ref;
    }

    public function node_offset(int $id)
    {
        return ($id & $this->mod) * $this->size_node;
    }


    /**
     * this method returns trie dictionary block
     * @param int $id
     * @return mixed
     */
    public function block_number_get(int $id)
    {
        return $id >> $this->power;
    }


    public function &nodes_block(int $id)
    {
        $num = $this->block_number_get($id);
        if (!isset($this->nodes[$num])) {
            $this->nodes[$num] = '';
        }
        return $this->nodes[$num];
    }

    public function &refs_block(int $ref_id)
    {
        $num = $this->block_number_get($ref_id);
        if (!isset($this->refs[$num])) {
            $rel_id = $this->id_relative($ref_id);
            $this->refs[$num] = $this->str_pad_null($rel_id * $this->size_ref);
        }
        return $this->refs[$num];
    }

    /**
     * this method returns id relative to the block
     * @param int $id
     * @return int
     */
    public function id_relative(int $id)
    {
        return $id & $this->mod;
    }


    /**
     * Make new node method
     * @param string|null $mask
     * @param string|null $refs
     * @return int
     */
    public function node_make()
    {
        $block = &$this->nodes[$this->block_number_get(++$this->id_node)];
        $block .= $this->str_pad_null($this->size_node);
        return $this->id_node;
    }

    /**
     * Allocate new refs memory block
     * @param int $size
     * @return int
     */
    public function refs_allocate(int $size = 3)
    {
        $id = $this->id_ref;
        $this->id_ref += $size / $this->size_ref;
        return ++$id;
    }

    public function ref_insert(string &$refs, int $ref, int $pos)
    {
        if ($refs === '') {
            return $refs = $this->pack_24($ref);
        }
        $offset = $this->size_ref * $pos;
        $refs = substr($refs, 0, $offset) . $this->pack_24($ref) . substr($refs, $offset);
        return $refs;
    }

    public function refs_set(string &$block, string $refs, int $offset = null, int $size = null)
    {
        if ($offset === null) {
            $block .= $refs;
        } else {
            $block = substr($block, 0, $offset) . $refs . substr($block, $offset + $size);
        }
    }


    /**
     * @param int $parent_id
     * @param string $char
     * @return bool|int
     */
    public function node_char_get_ref(int $parent_id, string $char)
    {
//        print "parent:$parent_id char:$char\n";
        list($mask, $ref_id) = $this->node_get($parent_id);

        //number of bits before the current char position in the codepage
        $bits = $this->codepage[$char] === 0 ? 0 : $this->codepage[$char] - 1;

        //calculate reference position in the references sequence
        $pos = $this->bit_count($mask, $bits);

        if ($this->bit_get($mask, $this->codepage[$char])) {
            return $this->ref_get($ref_id, $pos);
        } else {
            return false;
        }
    }


    /**
     * this method for saving raw node data
     * @param int $id
     * @param int $mask
     * @return string
     */
    public function node_set_raw(string &$block, int $offset, string $node)
    {
        return $block = substr($block, 0, $offset) . $node . substr($block, $offset + $this->size_node);
    }


    /**
     * @param int $id
     * @return int
     */
    public function node_get(int $id)
    {
        $block = $this->nodes_block($id);
        return $this->node_get_raw($block, $this->node_offset($id));
    }

    /**
     * this method for getting raw node data
     * @param int $id
     * @return int
     */
    public function node_get_raw(string &$block, int $offset)
    {
        $mask = substr($block, $offset, $this->size_mask);
        $ref_id = substr($block, $offset + $this->size_mask, $this->size_ref_id);
        return [$this->unpack_48($mask), $this->unpack_24($ref_id)];
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
        $block = &$this->nodes_block($id);
        $offset = $this->node_offset($id);
        $node = $this->node_get_raw($block, $offset);
        $this->bit_clear($node[0], $this->codepage['flag']);
        return $this->node_set_raw($block, $offset, $this->pack_48($node[0]) . $this->pack_24($node[1]));
    }

    /**
     * @param int $id
     * @return bool
     */
    public function node_get_char_flag(int $id)
    {
        $node = $this->node_get($id);
        return $this->bit_get($node[0], $this->codepage['flag']);
    }

    /**
     * @param int $id
     * @return string
     */
    public function node_set_char_flag(int $id)
    {
        $block = &$this->nodes_block($id);
        $offset = $this->node_offset($id);
        $node = $this->node_get_raw($block, $offset);
        $this->bit_set($node[0], $this->codepage['flag']);
        return $this->node_set_raw($block, $offset, $this->pack_48($node[0]) . $this->pack_24($node[1]));
    }


    /**
     * @param string $word
     * @return string
     */
    public function trie_add(string $word)
    {
        $abc = $this->str_split_rus_mod($word);
        $cnt = count($abc);


        //this is the first letter
        $parent_id = $this->codepage_index[$abc[0]];

        //we save second char to the first letter node etc
        for ($i = 1; $i < $cnt; ++$i) {
            $parent_id = $this->trie_char_add($i, $parent_id, $abc[$i]);
        }
        //add last char flag for the last char
        $this->node_set_char_flag($parent_id);
        return $parent_id;
    }

    private function node_fill(string &$block, int $offset, int $mask, int $ref_id, int $pos, string $char)
    {
        //first we need to calculate allocated memory for the current node references
        $cnt = $this->bit_count($mask, 46);

        //if bitmask is empty we will simply create new refs block for the node
        if ($cnt === 0) {
            $size = 0;
            $refs = '';
        } else {
            $size = $this->size_ref * $cnt;
            //if there is some memory allocated save its id for reuse
            $this->deal[$size][] = $ref_id;

            //now we get allocated refs
            $refs = $this->refs_get($ref_id, $size);
        }

        //create new node and save its id
        $next_node_id = $this->node_make();

        //insert just created node id to the current node references array
        $this->ref_insert($refs, $next_node_id, $pos);

        //increase references size
        $size += $this->size_ref;

        //reallocate used or allocate new refs memory
        $last_ref_id = $this->id_ref;
        $ref_id = $this->refs_get_memory($size);
        $refs_block = &$this->refs_block($ref_id);

        //if new memory allocated
        if ($last_ref_id !== $this->id_ref) {
            $this->refs_set($refs_block, $refs);
            //otherwise used memory allocated
        } else {
            $this->refs_set($refs_block, $refs, $this->refs_offset($ref_id), $size);
        }

        //change node bitmask
        $this->bit_set($mask, $this->codepage[$char]);

        //save node
        $this->node_set_raw($block, $offset, $this->pack_48($mask) . $this->pack_24($ref_id));

        return $next_node_id;
    }


    /**
     * @param int $parent_id
     * @param string $char
     * @return int
     */
    private function trie_char_add(int $level, int $parent_id, string $char)
    {
        //print "level:$level parent:$parent_id char:$char\n";

        //get memory block number
        $block = &$this->nodes_block($parent_id);

        //$node[0] is node bitmask
        //$node[1] is a node offset in the references block $ref_id
        $offset = $this->node_offset($parent_id);
        list($mask, $ref_id) = $this->node_get_raw($block, $offset);

        //number of bits before the current char position in the codepage
        $bits = $this->codepage[$char] === 0 ? 0 : $this->codepage[$char] - 1;

        //calculate reference position in the references sequence
        $pos = $this->bit_count($mask, $bits);

        //if char exists in the bitmask we need to get next node id in the references block
        if ($this->bit_get($mask, $this->codepage[$char])) {
            $next_node_id = $this->ref_get($ref_id, $pos);
        } else {
            $next_node_id = $this->node_fill($block, $offset, $mask, $ref_id, $pos, $char);
        }

        return $next_node_id;
    }

    public function refs_reallocate(int $size)
    {
        if (!empty($this->deal[$size])) {
            return array_pop($this->deal[$size]);
        } else {
            return false;
        }
    }

    public function trie_word_nodes(string $word)
    {
        $abc = $this->str_split_rus_mod($word);
        $cnt = count($abc);
        $res = [];

        $parent_id = $this->codepage_index[$abc[0]];
        $res[] = [$parent_id, $abc[0]];

        for ($i = 1; $i < $cnt; ++$i) {
            $parent_id = $this->node_char_get_ref($parent_id, $abc[$i]);
            if ($parent_id === false) {
                return false;
            } else {
                $res[] = [$parent_id, $abc[$i]];
            }
        }
        return $this->node_get_char_flag($parent_id) === false ? false : $res;
    }


    /**
     * @param string $word
     * @return string
     */
    public function trie_remove(string $word)
    {
        $nodes = $this->trie_word_nodes($word);
        if ($nodes === false) {
            return false;
        }

        $last = count($nodes) - 1;
        //first we clear "last char" flag
        $this->node_clear_char_flag($nodes[$last][0]);

        //flag to store info if the previous node was deleted
        $deleted = false;

        //iterate nodes in reverse order
        for ($i = $last; $i > -1; --$i) {
            list($parent_id, $char) = $nodes[$i];

            //get node
            $block = &$this->nodes_block($parent_id);
            $offset = $this->node_offset($parent_id);
            list($mask, $ref_id) = $this->node_get_raw($block, $offset);

            //current node refs amount
            $refs_amount_initial = $refs_amount = $this->bit_count($mask, 46);
            //check last char flag
            $flag = $this->bit_get($mask, $this->codepage['flag']) ? true : false;

            if ($deleted) {
                $this->bit_clear($mask, $this->codepage[$nodes[$i + 1][1]]);
                //decrement $refs_amount
                --$refs_amount;
            }

            //if the node doesn't have references and last char flag is not set we can delete it
            if ($refs_amount === 0 && !$flag) {
                $this->node_remove($block, $offset, $parent_id, $ref_id, $refs_amount_initial);
                $deleted = true;
            } else if ($deleted) { //if the node was changed we need to change its references

                $size = $this->size_ref * $refs_amount_initial;
                $refs = $this->refs_get($ref_id, $size);

                //number of bits before the char position in the codepage
                $bits = $this->codepage[$nodes[$i + 1][1]] === 0 ? 0 : $this->codepage[$nodes[$i + 1][1]] - 1;
                $pos = $this->bit_count($mask, $bits);

                $this->ref_remove($refs, $pos);

                //new refs size
                $size_new = $this->size_ref * $refs_amount;

                //reallocate used or allocate new refs memory
                $last_ref_id = $this->id_ref;
                $ref_id = $this->refs_get_memory($size_new);

                $refs_block = &$this->refs_block($ref_id);

                //if new memory allocated
                if ($last_ref_id !== $this->id_ref) {
                    $this->refs_set($refs_block, $refs);
                    //otherwise used memory allocated
                } else {
                    $this->refs_set($refs_block, $refs, $this->refs_offset($ref_id), $size_new);
                }

                $deleted = false;
            } else {
                $deleted = false;
            }
        }

        return true;
    }

    private function refs_get_memory(int $size)
    {
        $ref_id = $this->refs_reallocate($size);

        //if reallocation failed
        if ($ref_id === false) {
            $ref_id = $this->refs_allocate($size);
        }
        return $ref_id;
    }

    private function ref_remove(string &$refs, int $ref_pos)
    {
        $offset = $this->size_ref * $ref_pos;
        $refs = substr($refs, 0, $offset) . substr($refs, $offset + $this->size_ref);
        return $refs;
    }

    private function node_remove(string &$block, int $offset, int $parent_id, int $ref_id, int $refs_amount)
    {
        //first deallocate refs memory
        $this->deal[$refs_amount * $this->size_ref][] = $ref_id;
        //clear node mask and reference
        return $this->node_set_raw($block, $offset, $this->str_pad_null($this->size_node));
    }

    /**
     * @param string $word
     * @return bool
     */
    public function trie_check(string $word)
    {
        $abc = $this->str_split_rus_mod($word);
        $cnt = count($abc);

        $parent_id = $this->codepage_index[$abc[0]];

        for ($i = 1; $i < $cnt; ++$i) {
            $parent_id = $this->node_char_get_ref($parent_id, $abc[$i]);
            if ($parent_id === false) {
                return false;
            }
        }

        return $this->node_get_char_flag($parent_id) === false ? false : $parent_id;
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
    private function bit_count(int $i, int $length = null)
    {
        if ($length > 0) {
            $i &= (2 << $length - 1) - 1; // 2 << $i === pow(2,$i-1) bit shift is about 3% faster than pow()
        } else if ($length === 0) {
            return 0;
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