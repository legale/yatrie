<?php

use PHPUnit\Framework\TestCase;

require_once(dirname(__FILE__) . '/vendor/autoload.php');
require_once(dirname(__FILE__) . '/../src/Yatrie.php');


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
            ['0' , 1, '1'], ['0' , 2, '10'], ['0' , 3, '100'], ['1' , 3, '101'], ['111' , 4, '1111'],
        ];
    }

    public function bit_clear_dataProvider()
    {
        return [
            ['1' , 1, '0'], ['10' , 2, '0'], ['111' , 3, '11'], ['11' , 3, '11'], ['1011' , 4, '11'],
        ];
    }


    public function bit_check_dataProvider()
    {
        return [
            ['1' , 1, true], ['1' , 2, false], ['101011' , 3, false], ['101011' , 4, true]
        ];
    }

    protected function setUp()
    {
        $this->class = new Yatrie();
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
        $res = decbin( $t->bit_set($mask, $bit));
        $this->assertEquals($res, $expected);
    }

    /**
     * @dataProvider bit_clear_dataProvider
     */
    public function test_bit_clear(string $mask, int $bit, string $expected)
    {
        $t = &$this->class;
        $mask = bindec($mask);
        $res = decbin( $t->bit_clear($mask, $bit));
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


}