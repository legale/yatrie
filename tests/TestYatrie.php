<?php

use PHPUnit\Framework\TestCase;

require_once(dirname(__FILE__) . '/vendor/autoload.php'); //PHPUnit
require_once(dirname(__FILE__) . '/../src/Reflect.php'); //wrapper for the Reflection class to test non public methods
require_once(dirname(__FILE__) . '/../src/Yatrie.php'); //Test class


/**
 * Class TestYatrie
 */
class TestYatrie extends TestCase
{
    /**
     * @var
     */
    private $class;


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

    public function unpack_dataProvider()
    {

        $a = array_keys(array_fill(0, 10, 0));
        $a2 = array_map(function ($k, $i) {
            return array(pack('V', $i), $k);
        }, $a, array_keys($a));

        return $a2;
    }

    protected function setUp()
    {
        $c = new Yatrie();
        $this->class = new Reflect($c);
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
    public function testNothing()
    {
        $this->assertTrue(true);
    }


    /**
     * @dataProvider bit_set_dataProvider
     */
    public function test_bit_set(string $mask, int $bit, string $expected)
    {
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
        $t = &$this->class;
        $mask = bindec($mask);
        $res = $t->bit_count($mask);
        $this->assertEquals($res, $expected);
    }


    /**
     * @test
     */
    public function str_split_rus()
    {
        $word = 'абв';
        $t = &$this->class;
        $res = $t->str_split_rus($word);
        $this->assertEquals($res, ['а', 'б', 'в']);

    }

    /**
     * @test
     */
    public function str_split_rus_mod()
    {
        $word = 'кот-д’ивуару5';
        $t = &$this->class;
        $res = $t->str_split_rus_mod($word);
        $this->assertEquals($res, ['к', 'о', 'т', '-', 'д', '’', 'и', 'в', 'у', 'а', 'р', 'у', '5']);
    }

    /**
     * @test
     * @dataProvider pack_dataProvider
     */
    public function pack_24(int $int)
    {
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
    public function pack_48(int $int)
    {
        $t = &$this->class;
        $res = $t->pack_48($int);
        $i = $t->unpack_48($res);
        $this->assertEquals(strlen($res), 6);
        $this->assertEquals($int, $i);
    }

    /**
     * @test
     * @dataProvider unpack_dataProvider
     */
    public function unpack_48(string $str, int $expected)
    {
        $t = &$this->class;
        $i = $t->unpack_48($str);
        $this->assertEquals($i, $expected);
    }

    /**
     * @test
     * @dataProvider unpack_dataProvider
     */
    public function unpack_24(string $str, int $expected)
    {
        $t = &$this->class;
        $i = $t->unpack_24($str);
        $this->assertEquals($i, $expected);
    }

    /**
     * @test
     * @dataProvider unpack_dataProvider
     */
    public function unpack_mod(string $str, int $expected)
    {
        $t = &$this->class;
        $i = $t->unpack_mod($str);
        $this->assertEquals($i, $expected);
    }


}