<?php

use PHPUnit\Framework\TestCase;

require_once(dirname(__FILE__) . '/vendor/autoload.php'); //PHPUnit
require_once(dirname(__FILE__) . '/../etc/Reflect.php'); //wrapper for the Reflection class to test non public methods
require_once(dirname(__FILE__) . '/../src/Yatrie.php'); //Test class


/**
 * Class TestYatrie
 */
class TestYatrie extends TestCase
{


    /**
     * @return array
     */
    public function data_bit_set()
    {
        return [
            ['100', 1, '101'], ['0', 2, '10'], ['0', 3, '100'], ['1', 3, '101'], ['111', 4, '1111'],
        ];
    }

    /**
     * @return array
     */
    public function data_bit_clear()
    {
        return [
            ['1', 1, '0'], ['10', 2, '0'], ['111', 3, '11'], ['11', 3, '11'], ['1011', 4, '11'],
        ];
    }


    /**
     * @return array
     */
    public function data_bit_get()
    {
        return [
            ['1', 1, true], ['1', 2, false], ['101011', 3, false], ['101011', 4, true]
        ];
    }

    /**
     * @return array
     */
    public function data_bit_count()
    {
        return [
            ['1', 1, 1], ['11', 1, 1], ['101011', 2, 3], ['11010000', 3, 8], ['111111111111111111111111111111111111111111111110', 47, 48]
        ];
    }

    /**
     * @return array
     */
    public function data_pack()
    {
        return [[1], [2345], [3243242], [1234567]];
    }


    /**
     * @return array
     */
    public function data_unpack_24()
    {
        $max = bindec('111111111111111111111111');
        $half = $max / 2;
        $third = $max / 3;
        $fourth = $max / 4;
        $fifth = $max / 5;
        return [
            [substr(pack('P', 123456), 0, 3), 123456],
            [substr(pack('P', 0), 0, 3), 0], //min 24 bit unsigned integer
            [substr(pack('P', $max), 0, 3), $max],//max 24 bit unsigned integer
            [substr(pack('P', $half), 0, 3), $half],
            [substr(pack('P', $third), 0, 3), $third],
            [substr(pack('P', $fourth), 0, 3), $fourth],
            [substr(pack('P', $fifth), 0, 3), $fifth],
        ];
    }

    /**
     * @return array
     */
    public function data_unpack_48()
    {
        $max = bindec('111111111111111111111111111111111111111111111111');
        $half = $max / 2;
        $third = $max / 3;
        $fourth = $max / 4;
        $fifth = $max / 5;
        return [
            [substr(pack('P', 123456), 0, 6), 123456],
            [substr(pack('P', 0), 0, 6), 0], //min 48 bit unsigned integer
            [substr(pack('P', $max), 0, 6), $max], //max 48 bit unsigned integer
            [substr(pack('P', $half), 0, 6), $half],
            [substr(pack('P', $third), 0, 6), $third],
            [substr(pack('P', $fourth), 0, 6), $fourth],
            [substr(pack('P', $fifth), 0, 6), $fifth],
        ];
    }

    /**
     * @return array
     */
    public function data_str_pad_null()
    {
        return [
            [0, ''],
            [1, "\0"],
            [2, "\0\0"],
            [3, "\0\0\0"],
            [4, "\0\0\0\0"],
            [5, "\0\0\0\0\0"],
        ];
    }

    /**
     * @return array
     */
    public function data_node_make()
    {
        //children mask (string 6 bytes), refs(string 46 * 3 = 138 bytes), created node id (int)
        return [
            [null, null, 0],
            [null, null, 1],
            [null, str_repeat("\0", 138), 2],
            [str_repeat("\0", 6), null, 3],
            [str_repeat("\0", 6), str_repeat("\0", 138), 4],
            [str_repeat("\0", 6), str_repeat("\0", 138), 5],
        ];
    }


    /**
     * @return array
     */
    public function data_node_get()
    {
        return [[0], [2], [10], [100000], [200000]];
    }

    /**
     * @return array
     */
    public function data_block_number_get()
    {
        return [[0], [21], [24], [65535], [100000]];
    }


    /**
     *
     */
    public function test_nothing()
    {
        $this->assertTrue(true);
    }

    /**
     * @dataProvider data_bit_set
     */
    public function test_bit_set(string $mask, int $bit, string $expected)
    {
        $c = new Yatrie();
        $this->class = new Reflect($c);

        $t = &$this->class;
        $mask = bindec($mask);
        $res = decbin($t->bit_set($mask, $bit));
        $this->assertEquals($res, $expected);
    }

    /**
     * @dataProvider data_bit_clear
     */
    public function test_bit_clear(string $mask, int $bit, string $expected)
    {
        $c = new Yatrie();
        $this->class = new Reflect($c);
        $t = &$this->class;
        $mask = bindec($mask);
        $res = decbin($t->bit_clear($mask, $bit));
        $this->assertEquals($res, $expected);
    }

    /**
     * @dataProvider data_bit_get
     */
    public function test_bit_get(string $mask, int $bit, string $expected)
    {
        $c = new Yatrie();
        $this->class = new Reflect($c);
        $t = &$this->class;
        $mask = bindec($mask);
        $res = $t->bit_get($mask, $bit);
        $this->assertEquals($res, $expected);
    }

    /**
     * @dataProvider data_bit_count
     */
    public function test_bit_count(string $mask, int $expected, int $length)
    {
        $c = new Yatrie();
        $this->class = new Reflect($c);
        $t = &$this->class;
        $mask = bindec($mask);
        $res = $t->bit_count($mask, $length);
        $this->assertEquals($res, $expected);
    }

    /**
     * @return array
     */
    public function data_node_char_get_ref()
    {
        //parend node id, word
        return [
            ['а'],
            ['б'],
            ['я'],
            ['фрик'],
        ];
    }

    /**
     * @return array
     */
    public function data_trie_char_add()
    {
        //parend node id, words to add, returned reference id
        return [
            [['а'], 1],
            [['б'], 1],
            [['я'], 1],
            [['а', 'аб'], 2],
            [['яя'], 2],
            [['ааааа'], 5],
            [['бвгде'], 5],
            [['фрика'], 5],
            [['фрик', 'фрика'], 5],
            [['фри', 'фрик', 'фрика'], 5],
        ];
    }

    /**
     * @param string $id
     * @param string $char
     * @param string $expected
     * @test
     * @dataProvider data_trie_char_add
     */
    public function test_trie_char_add(array $words, int $expected)
    {
        $t = new Reflect(new Yatrie());
        $shift = $t->id_node; //already created nodes

        foreach ($words as $word) {
            $array = $t->str_split_rus_mod($word);
            $parent_id = $t->codepage_index[$array[0]];
            foreach ($array as $i => $char) {
                $parent_id = $t->trie_char_add($i, $parent_id, $char);
            }
        }
        $this->assertEquals($shift + $expected, $parent_id);
    }

    /**
     * @param string $word
     * @test
     * @dataProvider data_node_char_get_ref
     */
    public function test_node_char_get_ref(string $word)
    {
        $t = new Reflect(new Yatrie());
        $shift = $t->id_node;

        $array = $t->str_split_rus($word);
        $parent_id = $t->codepage_index[$array[0]];

        foreach ($array as $i => $char) {
            //dic is empty so false expected
            $this->assertFalse($t->node_char_get_ref($parent_id, $char));

            $ref_id = $t->trie_char_add($i, $parent_id, $char);

            $res = $t->node_char_get_ref($parent_id, $char);
            //now check if saved ref_id equals returned
            $this->assertEquals($ref_id, $res);
            $parent_id = $ref_id;
        }
    }

    /**
     * @test
     */
    public function test_str_split_rus()
    {
        $c = new Yatrie();
        $this->class = new Reflect($c);
        $t = &$this->class;
        $word = 'кот-д’ивуару5-абвгдеёжзиклмнопрстуфхцчшщъьэюя1234567890';
        $ar = preg_split('//u', $word, 0, PREG_SPLIT_NO_EMPTY);
        $res = $t->str_split_rus($word);
        $this->assertEquals($res, $ar);
        $this->assertFalse($t->str_split_rus('abc'));
    }

    /**
     * @test
     */
    public function test_str_split_rus_mod()
    {
        $c = new Yatrie();
        $this->class = new Reflect($c);
        $t = &$this->class;
        $word = 'кот-д’ивуару5-абвгдеёжзиклмнопрстуфхцчшщъьэюя1234567890';
        $ar = preg_split('//u', $word, 0, PREG_SPLIT_NO_EMPTY);
        $res = $t->str_split_rus_mod($word);
        $this->assertEquals($res, $ar);
        $this->assertFalse($t->str_split_rus_mod('abc'));
    }

    /**
     * @test
     * @dataProvider data_pack
     */
    public function test_pack_24(int $int)
    {
        $c = new Yatrie();
        $this->class = new Reflect($c);
        $t = &$this->class;
        $res = $t->pack_24($int);
        $i = $t->unpack_24($res);
        $this->assertEquals(strlen($res), 3);
        $this->assertEquals($int, $i);
    }

    /**
     * @test
     * @dataProvider data_pack
     */
    public function test_pack_48(int $int)
    {
        $c = new Yatrie();
        $this->class = new Reflect($c);
        $t = &$this->class;
        $res = $t->pack_48($int);
        $i = $t->unpack_48($res);
        $this->assertEquals(strlen($res), 6);
        $this->assertEquals($int, $i);
    }

    /**
     * @test
     * @dataProvider data_unpack_48
     */
    public function test_unpack_48(string $str, int $expected)
    {
        $c = new Yatrie();
        $this->class = new Reflect($c);
        $t = &$this->class;
        $i = $t->unpack_48($str);
        $this->assertEquals($i, $expected);
    }

    /**
     * @test
     * @dataProvider data_unpack_24
     */
    public function test_unpack_24(string $str, int $expected)
    {
        $c = new Yatrie();
        $this->class = new Reflect($c);
        $t = &$this->class;
        $i = $t->unpack_24($str);
        $this->assertEquals($i, $expected);
    }

    /**
     * @test
     * @dataProvider data_unpack_48
     */
    public function test_unpack_mod(string $str, int $expected)
    {
        $c = new Yatrie();
        $this->class = new Reflect($c);
        $t = &$this->class;
        $i = $t->unpack_mod($str);
        $this->assertEquals($i, $expected);
    }

    /**
     * @test
     * @dataProvider data_str_pad_null
     * @param int $size
     * @param string $expected
     */
    public function test_str_pad_null(int $size, string $expected)
    {
        $t = $this->class_mock_create();
        $this->assertEquals($t->str_pad_null($size), $expected);
    }

    /**
     * @return \PHPUnit\Framework\MockObject\MockObject
     */
    public function class_mock_create()
    {
        // Get mock, without the constructor being called
        return $this->getMockBuilder('Yatrie')
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
    }


    /**
     * @return array
     */
    public function init_trie_dataProvider()
    {
        return [
            [null],
            [[
                dirname(__FILE__) . '/test_headers.txt',
                dirname(__FILE__) . '/test_nodes.txt.gz',
                dirname(__FILE__) . '/test_refs.txt.gz'
            ]]
        ];
    }

    /**
     * @test
     */
    public function test_class_constructor()
    {
        $t = new Yatrie();
        //check class calculated variables
        $cnt_check = count($t->codepage_index);
        $this->assertEquals($cnt_check, $t->char_count);
        $this->assertEquals($t->size_ref_id + $t->size_mask, $t->size_node);

    }


    /**
     *
     */
    public function test_layer_make_empty()
    {
        $t = $this->class_mock_create();

        //class variables
        $t->char_count = $chars = count($t->codepage_index);
        $t->size_node = $t->size_ref_id + $t->size_mask;

        //calculate expected dic len
        $nodes_block_len = $chars * $t->size_node;


        $t->layer_make_empty();
        $this->assertEquals($nodes_block_len, strlen($t->nodes[0]));
    }

    /**
     * @test
     * @dataProvider init_trie_dataProvider
     */
    public function test_init_trie(array $dic = null)
    {
        $t = new Yatrie($dic);
        //check class calculated variables
        $this->assertEquals(count($t->codepage_index), $t->char_count);
        $this->assertEquals($t->size_mask + $t->size_ref_id, $t->size_node);

        if (!empty($dic)) {
            //headers
            list($id_node, $id_ref) = unserialize(file_get_contents($dic[0]));
            $this->assertEquals($id_node, $t->id_node);
            $this->assertEquals($id_ref, $t->id_ref);
            //nodes
            $string = '';
            $fp = gzopen($dic[1], 'r');
            while (!feof($fp)) {
                $string .= gzread($fp, 999999);
            }
            gzclose($fp);
            $nodes = unserialize($string);
            $this->assertEquals($nodes, $t->nodes);
            unset($string, $nodes);

            //refs
            $string = '';
            $fp = gzopen($dic[2], 'r');
            while (!feof($fp)) {
                $string .= gzread($fp, 999999);
            }
            gzclose($fp);
            $refs = unserialize($string);
            $this->assertEquals($refs, $t->refs);
            unset($string, $refs);

        } else {
            //empty dic init check only one alphabet nodes expected
            $this->assertEquals($t->size_node * count($t->codepage_index), strlen($t->nodes[0]));

            //expected last node id equals number of alphabet chars (+1 cause first node_id is 0)
            $this->assertEquals(count($t->codepage_index), $t->id_node + 1);
        }
    }

    /**
     * @test
     * @dataProvider data_node_make
     */
    public function test_node_make(string $mask = null, string $refs = null, string $expected)
    {
        $t = $this->class_mock_create();

        //class variables
        $t->size_node = $t->size_ref + $t->size_mask;


        //create sample dic
        $t->dic = [0 => ''];

        //create first node
        $id = $t->node_make();
        $this->assertEquals(0, $id); // node id increment check
        $this->assertEquals($t->size_node, strlen($t->nodes[0]));

        //create second node
        $id = $t->node_make();
        $this->assertEquals($id, 1); // node id increment check
        $this->assertEquals($t->size_node * 2, strlen($t->nodes[0]));

    }

    /**
     * @param int $nodes
     * @param $expected
     *
     * @test
     * @dataProvider data_block_number_get
     */
    public function test_block_number_get(int $id)
    {
        $t = new Yatrie();
        $block_num = $t->block_number_get($id);
        $this->assertEquals((int)floor($id / $t->size_block), $block_num);
    }

    /**
     * @test
     * @dataProvider data_node_get
     * @param int $id
     */
    public function test_node_get_raw(int $id)
    {
        $t = new Yatrie();
        //test data
        $mask = bindec('1010101010');
        $mask_str = $t->pack_48($mask);
        $ref_id = 123456;
        $ref_id_str = $t->pack_24($ref_id);

        //id relative to the block
        $rel_id = $id % $t->size_block;
        $offset = $rel_id * $t->size_node;

        $block = &$t->nodes_block($id);
        $block = str_repeat("\0", $t->size_block * $t->size_node);
        $block = substr($block, 0, $offset) . $mask_str . $ref_id_str . substr($block, $offset + $t->size_node);

        $res = $t->node_get_raw($block, $offset);
        $this->assertEquals($mask, $res[0]);
        $this->assertEquals($ref_id, $res[1]);
    }

    /**
     * @test
     * @dataProvider data_node_get
     * @param int $id
     */
    public function test_node_set_raw(int $id)
    {
        $t = new Yatrie();
        $mask_check = bindec('111111111111111111111111111111111111111111111110');
        $mask_str = $t->pack_48($mask_check);

        $ref_id_check = 444;
        $ref_id_str = $t->pack_24($ref_id_check);

        $node = $mask_str . $ref_id_str;

        $block = &$t->nodes_block($id);
        $block = str_repeat("\0", $t->size_block * $t->size_node);
        $offset = $t->node_offset($id);
        $t->node_set_raw($block, $offset, $node);
        list($mask, $ref_id) = $t->node_get_raw($block, $offset);
        $this->assertEquals($mask_check, $mask);
        $this->assertEquals($ref_id_check, $ref_id);
    }


    public function test_pack_16()
    {
        $t = new Yatrie();
        $i = bindec('111111111111111');
        $check = pack('v', $i);
        $this->assertEquals($check, $t->pack_16($i));
    }

    /**
     *
     */
    public function test_pack_32()
    {
        $t = new Yatrie();
        $i = bindec('111111111111111111111111111111');
        $check = pack('V', $i);
        $this->assertEquals($check, $t->pack_32($i));
    }

    /**
     *
     */
    public function test_unpack_32()
    {
        $t = new Yatrie();
        $i = bindec('111111111111111111111111111111');
        $packed = pack('V', $i);
        $check = unpack('V', $packed)[1];
        $this->assertEquals($check, $t->unpack_32($packed));
    }

    /**
     *
     */
    public function test_unpack_16()
    {
        $t = new Yatrie();
        $i = bindec('1111111111111111');
        $packed = pack('v', $i);
        $check = unpack('v', $packed)[1];
        $this->assertEquals($check, $t->unpack_16($packed));
    }

    /**
     *
     */
    public function test_pack_64()
    {
        $t = new Yatrie();
        $i = bindec('111111111111111111111111111111111111111111111111111111111111');
        $check = pack('P', $i);
        $this->assertEquals($check, $t->pack_64($i));
    }

    /**
     *
     */
    public function test_unpack_64()
    {
        $t = new Yatrie();
        $i = bindec('111111111111111111111111111111111111111111111111111111111111');
        $packed = pack('P', $i);
        $check = unpack('P', $packed)[1];
        $this->assertEquals($check, $t->unpack_64($packed));
    }


    /**
     * @param int $id
     * @param string $char
     * @test
     * @dataProvider data_node_set_char_flag
     */
    public function test_node_set_char_flag(int $id)
    {
        $t = new Reflect(new Yatrie());
        $t->node_set_char_flag($id);

        list($mask, $ref_id) = $t->node_get($id);
        //check flag bit
        $this->assertEquals($t->bit_set(0, $t->codepage['flag']), $mask);
    }

    /**
     * @param int $id
     * @param string $char
     * @test
     * @dataProvider data_node_set_char_flag
     */
    public function test_node_get_char_flag(int $id)
    {
        $t = new Reflect(new Yatrie());

        $this->assertFalse($t->node_get_char_flag($id));

        $t->node_set_char_flag($id);
        $this->assertTrue($t->node_get_char_flag($id));
    }

    /**
     * @param int $id
     * @param string $char
     * @test
     * @dataProvider data_node_set_char_flag
     */
    public function test_node_clear_char_flag(int $id)
    {
        $t = new Reflect(new Yatrie());
        $t->node_set_char_flag($id);
        $this->assertTrue($t->node_get_char_flag($id));
        $t->node_clear_char_flag($id);
        $this->assertFalse($t->node_get_char_flag($id));
    }

    /**
     * @return array
     */
    public function data_node_set_char_flag()
    {
        return [[0], [1], [2], [3], [4]];
    }


    /**
     * @param string $word
     * @test
     * @dataProvider data_trie_add
     */
    public function test_trie_add(array $words)
    {
        $t = new Reflect(new Yatrie());

        foreach ($words as $i => $word) {
            $last_id = $t->trie_add($word);

            $chars = $t->str_split_rus_mod($word);
            //first char node id
            $id = $t->codepage_index[$chars[0]];
            $len = count($chars);

            if ($i === 0) {
                for ($i = 1; $i < $len; ++$i) {
                    $id = $t->node_char_get_ref($id, $chars[$i]);
                    $this->assertNotFalse($id);
                }
                //check $last_id and last $id in chain
                $this->assertEquals($last_id, $id);
            }

            //check last char flag
            $this->assertTrue($t->node_get_char_flag($last_id));
        }

    }

    /**
     * @return array
     */
    public function data_trie_add()
    {
        return [
            [["ааааа", "аааа", "ааа", "аа", "а"]],
            [["а", "б"]],
            [["я"]],
            [["я", "он", "она", "оными", "один", "одна", "хозяин", "хозяйка"]],
            [["тысячатристашестьдесятвосемь"]]
        ];
    }

    /**
     * @param string $word
     * @test
     * @dataProvider data_trie_add
     */
    public function test_trie_remove(array $words)
    {
        $t = new Reflect(new Yatrie());

        foreach ($words as $i => $word) {
            $last_id[$i] = $t->trie_add($word);
        }
        foreach ($words as $i => $word) {

            $this->assertTrue($t->trie_remove($word));
            //check if char flag cleared
            $this->assertFalse($t->node_get_char_flag($last_id[$i]));
        }

        //check trie remove on words with the same nodes used
        $short_id = $t->trie_add($words[0]);
        $long_id = $t->trie_add($words[0] . $words[0]);

        //delete shorter word and check if longer word is still exists
        $t->trie_remove($words[0] . $words[0]);

        $this->assertFalse($t->trie_check($words[0] . $words[0]));
        $this->assertEquals($short_id, $t->trie_check($words[0]));

        //check return if word is not exists
        $this->assertFalse($t->trie_remove($word . $word . $word));
    }

    /**
     * @test
     */
    public function test_refs_allocate()
    {
        $t = new Yatrie();

        for ($i = 3; $i < 200; $i += 3) {

            $id = $t->id_ref;
            $ref_id = $t->refs_allocate($i);
            $this->assertEquals($id + 1, $ref_id);
        }

    }

    /**
     * @param string $word
     * @param string $check
     * @test
     * @dataProvider data_trie_check
     */
    public function test_trie_check(string $word, string $check)
    {
        $t = new Yatrie();
        $last_id = $t->trie_add($word);

        //check if the first word exist
        $this->assertEquals($last_id, $t->trie_check($word));

        //check if the second word is not exists
        $this->assertFalse($t->trie_check($check));
    }

    /**
     * @return array
     */
    public function data_trie_check()
    {
        return [
            ['а', 'б'],
            ['аа', 'аб'],
            ['человек', 'человека']
        ];
    }


    /**
     * @param int $ref
     * @param int $pos
     * @test
     * @dataProvider data_ref_insert
     */
    public function test_ref_get_raw(int $ref_id, int $pos, int $ref)
    {
        $t = new Yatrie();
        $refs = str_repeat("\0", $t->size_ref * $t->size_block + $t->size_ref * $t->char_count);
        $ref_id = $ref_id % $t->size_block;
        $offset = $ref_id * $t->size_ref + $pos * $t->size_ref;
        $refs = substr($refs, 0, $offset) . $t->pack_24($ref) . substr($refs, $offset + $t->size_ref);
        $res = $t->ref_get_raw($refs, $ref_id, $pos);
        $this->assertEquals($ref, $res);
    }


    /**
     * @test
     */
    public function test_ref_insert()
    {
        $class = new Yatrie();
        $t = new Reflect($class);
        $check = $refs = $t->str_pad_null($t->size_ref * $t->size_block);
        $size_check = $size_init = strlen($refs);

        for ($i = 1; $i < $t->size_block; $i <<= 1) {
            $size_check += $t->size_ref;
            $t->call('ref_insert', [&$refs, $i, $i]);

            $check = $t->unpack_24(substr($refs, $i * $t->size_ref, $t->size_ref));
            $this->assertEquals($check, $i);
            $this->assertEquals($size_check, strlen($refs));


        }
    }


    /**
     * @param int $ref_id
     * @param int $pos
     * @param int $ref
     * @test
     * @dataProvider data_ref_insert
     */
    public function test_refs_get(int $ref_id, int $pos, int $ref)
    {
        $t = new Yatrie();
        $size = $t->size_ref * count($t->codepage); //max refs size
        $block_num = (int)floor($ref_id / $t->size_block);
        $block = str_repeat("\0", $t->size_block * $t->size_ref); //empty block
        $refs = str_repeat("\0", $size); //empty refs
        $t->ref_insert($refs, $ref, $pos);
        $size += $t->size_ref; //increase size
        //manual save refs to the block
        $rel_id = $ref_id % $t->size_block;
        $offset = $rel_id * $t->size_ref;
        $block = substr($block, 0, $offset) . $refs . substr($block, $offset);
        $t->refs[$block_num] = $block;
        //refs_get and check
        $res = $t->refs_get($ref_id, $size);
        $this->assertEquals($refs, $res);

    }

    /**
     * @param int $ref_id
     * @param int $pos
     * @param int $ref
     * @test
     * @dataProvider data_ref_insert
     */
    public function test_refs_set(int $ref_id, int $pos, int $ref)
    {
        $t = new Yatrie();
        $size = $t->size_ref * count($t->codepage); //max refs size
        $check = $block = str_repeat("\0", $t->size_block * $t->size_ref); //empty block
        $refs = str_repeat("\0", $size); //empty refs
        $t->ref_insert($refs, $ref, $pos);
        //manual save refs to the block
        $rel_id = $ref_id % $t->size_block;
        $offset = $rel_id * $t->size_ref;
        $check = substr($block, 0, $offset) . $refs . substr($block, $offset + $size);
        //refs_get and check
        $t->refs_set($block, $refs, $offset, $size);
        $this->assertEquals($check, $block);
    }


    /**
     * @param int $ref_id
     * @param int $pos
     * @param int $ref
     * @test
     * @dataProvider data_ref_insert
     */
    public function test_refs_offset(int $ref_id, int $pos, int $ref)
    {
        $t = new Yatrie();
        $expected = $ref_id % $t->size_block * $t->size_ref;
        $res = $t->refs_offset($ref_id);
        $this->assertEquals($expected, $res);

    }

    /**
     * @return array
     */
    public function data_ref_insert()
    {
        //int $ref_id, int $pos, int $ref
        return [
            [0, 0, 0], [0, 32, 123], [10, 10, 555], [500, 22, 32123], [601, 1, 225222]
        ];
    }


    /**
     *
     * @test
     */
    public function test_refs_block()
    {
        $t = new Yatrie();
        $ref_one = 1234567;
        $ref_two = 2345678;
        $refs = $t->pack_24($ref_one) . $t->pack_24($ref_two);
        for ($ref_id = 1; $ref_id < 400000; $ref_id <<= 1) {
            $block = &$t->refs_block($ref_id);
            $block .= $refs;

            //check refs block saving
            $check_num = (int)floor($ref_id / $t->size_block);
            $this->assertEquals($t->refs[$check_num], $block);

            //check if refs_block correctly create new blocks
            $id_rel = $ref_id % $t->size_block;
            $offset = $id_rel * $t->size_ref;
            $sub_one = substr($block, $offset, $t->size_ref);
            $sub_two = substr($block, $offset + $t->size_ref, $t->size_ref);
            $this->assertEquals($ref_one, $t->unpack_24($sub_one));
            $this->assertEquals($ref_two, $t->unpack_24($sub_two));

            //clear refs
            $t->refs = [];
        }

    }

    /**
     *
     * @test
     */
    public function test_nodes_block()
    {
        $t = new Yatrie();
        for ($i = 1; $i < 400000; $i <<= 1) {
            $block = &$t->nodes_block($i);
            $check_num = (int)floor($i / $t->size_block);
            $this->assertEquals($t->nodes[$check_num], $block);
        }

    }

    public function test_id_relative()
    {
        $t = new Yatrie();
        for ($i = 1; $i < 400000; $i <<= 1) {
            $id = $t->id_relative($i);
            $check = $i % $t->size_block;
            $this->assertEquals($check, $id);
        }
    }

    public function test_refs_reallocate()
    {
        $t = $this->class_mock_create();
        //$t = new Reflect($class);
        //test missing size
        $this->assertFalse($t->refs_reallocate(55555));

        //main test
        for ($i = 1; $i < 20000; $i <<= 1) {
            $t->deal[$i] = [$i]; //testing deallocated block
            $t->deal[$i + $i] = [$i + $i]; //another deallocated block
            //test if correct block returned
            $this->assertEquals($i, $t->refs_reallocate($i));
            //test if second request failed
            $this->assertFalse($t->refs_reallocate($i));
        }

    }


    /**
     * @test
     * @dataProvider data_node_char_get_ref
     */
    public function test_trie_word_nodes(string $word)
    {
        $t = new Yatrie();
        $last_node_id = $t->trie_add($word);
        $array = $t->str_split_rus_mod($word);
        $expected = count($array);
        $nodes = $t->trie_word_nodes($word);

        //check calculated nodes amount and returned amount
        $this->assertEquals($expected, count($nodes));

        //check last node id and returned last node id
        $this->assertEquals($last_node_id, end($nodes)[0]);

    }

    /**
     * @test
     */
    public function test_ref_remove()
    {
        $t = new Reflect(new Yatrie());
        $empty = $refs = $t->str_pad_null($t->size_ref * $t->size_block * 10); //very big $refs string
        for ($i = 1; $i < 200; $i <<= 1) {
            $t->call('ref_insert', [&$refs, $i, $i]); //now we insert ref $i on $i position
            $check = $refs; //save changed $refs string
            $t->call('ref_remove', [&$refs, $i]); //remove ref from $i position
            $this->assertNotEquals($check, $refs); //expected not equals $refs and previously saved refs
            $this->assertEquals($empty, $refs); //expected equals initial refs and $refs
        }
    }

    /**
     *
     */
    protected function setUp(): void
    {

    }

    /**
     *
     */
    protected function tearDown(): void
    {
    }

}
