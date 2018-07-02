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


    public function bit_set_dataProvider()
    {
        return [
            ['100', 1, '101'], ['0', 2, '10'], ['0', 3, '100'], ['1', 3, '101'], ['111', 4, '1111'],
        ];
    }

    public function bit_clear_dataProvider()
    {
        return [
            ['1', 1, '0'], ['10', 2, '0'], ['111', 3, '11'], ['11', 3, '11'], ['1011', 4, '11'],
        ];
    }


    public function bit_check_dataProvider()
    {
        return [
            ['1', 1, true], ['1', 2, false], ['101011', 3, false], ['101011', 4, true]
        ];
    }

    public function bit_count_dataProvider()
    {
        return [
            ['1', 1], ['11', 2], ['101011', 4], ['11010000', 3]
        ];
    }

    public function pack_dataProvider()
    {
        return [[1], [2345], [3243242], [1234567]];
    }


    public function unpack_24_dataProvider()
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

    public function unpack_48_dataProvider()
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

    public function str_pad_null_dataProvider()
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

    public function node_make_dataProvider()
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


    public function trie_dataProvider()
    {
        return [[0], [1], [1000], [5000], [10000], [20000],];
    }

    public function node_get_children_dataProvider()
    {
        return [[0], [1], [2], [21], [24]];
    }


    protected function setUp()
    {

    }

    /**
     *
     */
    protected function tearDown()
    {
    }

    /**
     *
     */
    public function test_nothing()
    {
        $this->assertTrue(true);
    }


    /**
     * @dataProvider bit_set_dataProvider
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
     * @dataProvider bit_clear_dataProvider
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
     * @dataProvider bit_check_dataProvider
     */
    public function test_bit_check(string $mask, int $bit, string $expected)
    {
        $c = new Yatrie();
        $this->class = new Reflect($c);
        $t = &$this->class;
        $mask = bindec($mask);
        $res = $t->bit_check($mask, $bit);
        $this->assertEquals($res, $expected);
    }

    /**
     * @dataProvider bit_count_dataProvider
     */
    public function test_bit_count(string $mask, int $expected)
    {
        $c = new Yatrie();
        $this->class = new Reflect($c);
        $t = &$this->class;
        $mask = bindec($mask);
        $res = $t->bit_count($mask);
        $this->assertEquals($res, $expected);
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
     * @dataProvider pack_dataProvider
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
     * @dataProvider pack_dataProvider
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
     * @dataProvider unpack_48_dataProvider
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
     * @dataProvider unpack_24_dataProvider
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
     * @dataProvider unpack_48_dataProvider
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
     * @dataProvider str_pad_null_dataProvider
     * @param int $size
     * @param string $expected
     */
    public function test_str_pad_null(int $size, string $expected)
    {
        $t = $this->class_mock_create();
        $this->assertEquals($t->str_pad_null($size), $expected);
    }

    /**
     * @test
     */
    public function test_class_constructor()
    {
        $t = new Yatrie();
        //check class calculated variables
        $this->assertEquals(count($t->codepage_index), $t->char_count);
        $this->assertEquals(count($t->codepage_index) * $t->size_ref, $t->size_refs);
        $this->assertEquals(count($t->codepage_index) * $t->size_ref + $t->size_mask, $t->size_node);

    }

    public function test_node_get_last_id()
    {
        $t = new Yatrie();
        $id = $t->id;
        $t->node_make();
        $this->assertEquals($id + 1, $t->node_get_last_id());
        $t->node_make();
        $this->assertEquals($id + 2, $t->node_get_last_id());
    }

    public function test_layer_make_empty()
    {
        $t = $this->class_mock_create();

        //class variables
        $t->char_count = $chars = count($t->codepage_index);
        $t->size_refs = $chars * $t->size_ref;

        //calculate expected dic len
        $dic_len = ($chars * $t->size_ref + $t->size_mask) * $chars;


        $t->layer_make_empty();
        $this->assertEquals($dic_len, strlen($t->dic[0]));
    }

    /**
     * @test
     */
    public function test_init_trie()
    {
        $t = new Yatrie();
        //check class calculated variables
        $this->assertEquals(count($t->codepage_index), $t->char_count);
        $this->assertEquals(count($t->codepage_index) * $t->size_ref, $t->size_refs);
        $this->assertEquals(count($t->codepage_index) * $t->size_ref + $t->size_mask, $t->size_node);

    }

    /**
     * @test
     * @dataProvider node_make_dataProvider
     */
    public function test_node_make(string $mask = null, string $refs = null, string $expected)
    {
        $t = $this->class_mock_create();
        //calculate $size_refs
        $cnt = count($t->codepage_index);
        $t->size_refs = $t->size_ref * $cnt;
        $size_node = $t->size_mask + $t->size_refs;

        //create sample dic
        $t->dic = [0 => ''];

        //create first node
        $id = $t->node_make($mask, $refs);
        $this->assertEquals(0, $id); // node id increment check
        $this->assertEquals($size_node, strlen($t->dic[0]));

        //create second node
        $id = $t->node_make($mask, $refs);
        $this->assertEquals($id, 1); // node id increment check
        $this->assertEquals($size_node * 2, strlen($t->dic[0]));

    }


    public function class_mock_create()
    {
        // Get mock, without the constructor being called
        return $this->getMockBuilder('Yatrie')
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
    }


    /**
     * @param int $nodes
     * @param $expected
     *
     * @test
     * @dataProvider trie_dataProvider
     */
    public function test_trie(int $nodes)
    {
        $t = new Yatrie();


        for ($i = 0; $i < $nodes; ++$i) {
            $t->node_make();
        }

        //block expected
        $expected = (int)floor($t->id / $t->size_block);
        //write test value to the block
        $t->dic[$expected] = 'hit';

        //returned block
        $block = $t->trie($t->id);

        //check returned block nulber
        $this->assertEquals($t->dic[$expected], $block);

    }


    /**
     * @test
     * @dataProvider node_get_children_dataProvider
     * @param int $id
     */
    public function test_node_get_children(int $id)
    {
        $t = new Yatrie();
        $mask_check = bindec('111111111111111111111111111111111111111111111110');
        $mask_packed = $t->pack_48($mask_check);
        $dic = &$t->trie($id);
        $id_rel = $id % $t->size_block;
        $offset = $id_rel * $t->size_node;
        $dic = substr($dic, 0, $offset) . $mask_packed . substr($dic, $offset + 6);
        $mask = $t->node_get_children($id);
        $this->assertEquals($mask_check, $mask);
    }

    /**
     * @test
     * @dataProvider node_get_children_dataProvider
     * @param int $id
     */
    public function test_node_save_children(int $id)
    {
        $t = new Yatrie();
        $mask_check = bindec('111111111111111111111111111111111111111111111110');
        $t->node_save_children($id, $mask_check);
        $mask = $t->node_get_children($id);
        $this->assertEquals($mask_check, $mask);
    }

    /**
     * @test
     * @dataProvider node_get_children_dataProvider
     * @param int $id
     */
    public function test_node_get_ref(int $id)
    {
        $t = new Yatrie();
        $id_rel = $id % $t->size_block;

        $dic = &$t->trie($id);
        $i1 = 523456;
        $i2 = 223555;

        $mask_packed1 = $t->pack_24($i1);
        $mask_packed2 = $t->pack_24($i2);

        $index1 = $t->codepage_index['а'];
        $index2 = $t->codepage_index['г'];

        $offset1 = $id_rel * $t->size_node + $t->size_mask + $t->size_ref * $index1;
        $offset2 = $id_rel * $t->size_node + $t->size_mask + $t->size_ref * $index2;

        $dic = substr($dic, 0, $offset1) . $mask_packed1 . substr($dic, $offset1 + $t->size_ref);
        $dic = substr($dic, 0, $offset2) . $mask_packed2 . substr($dic, $offset2 + $t->size_ref);

        $mask1 = $t->node_get_ref($id, $index1);
        $mask2 = $t->node_get_ref($id, $index2);

        $this->assertEquals($i1, $mask1);
        $this->assertEquals($i2, $mask2);
    }

    /**
     * @test
     * @dataProvider node_get_children_dataProvider
     * @param int $id
     */
    public function test_node_save_ref(int $id)
    {
        $t = new Yatrie();
        $i1 = 123555;
        $i2 = 532343;
        $index1 = $t->codepage_index['а'];
        $index2 = $t->codepage_index['я'];
        $t->node_save_ref($id, $index1, $i1);
        $t->node_save_ref($id, $index2, $i2);

        $ref1 = $t->node_get_ref($id, $index1, $i1);
        $ref2 = $t->node_get_ref($id, $index2, $i2);

        $this->assertEquals($i1, $ref1);
        $this->assertEquals($i2, $ref2);
    }

    public function test_pack_16(){
        $t = new Yatrie();
        $i = bindec('111111111111111');
        $check = pack('v', $i);
        $this->assertEquals($check, $t->pack_16($i));
    }

    public function test_pack_32(){
        $t = new Yatrie();
        $i = bindec('111111111111111111111111111111');
        $check = pack('V', $i);
        $this->assertEquals($check, $t->pack_32($i));
    }

    public function test_unpack_32(){
        $t = new Yatrie();
        $i = bindec('111111111111111111111111111111');
        $packed = pack('V', $i);
        $check = unpack('V', $packed)[1];
        $this->assertEquals($check, $t->unpack_32($packed));
    }

    public function test_unpack_16(){
        $t = new Yatrie();
        $i = bindec('1111111111111111');
        $packed = pack('v', $i);
        $check = unpack('v', $packed)[1];
        $this->assertEquals($check, $t->unpack_16($packed));
    }

    public function test_pack_64(){
        $t = new Yatrie();
        $i = bindec('111111111111111111111111111111111111111111111111111111111111');
        $check = pack('P', $i);
        $this->assertEquals($check, $t->pack_64($i));
    }

    public function test_unpack_64(){
        $t = new Yatrie();
        $i = bindec('111111111111111111111111111111111111111111111111111111111111');
        $packed = pack('P',$i);
        $check = unpack('P', $packed)[1];
        $this->assertEquals($check, $t->unpack_64($packed));
    }

}