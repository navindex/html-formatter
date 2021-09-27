<?php

declare(strict_types=1);

namespace Navindex\HtmlFormatter\Tests;

use Iterator;
use Navindex\HtmlFormatter\Helper;
use Navindex\HtmlFormatter\Pattern;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Navindex\HtmlFormatter\Helper
 * @uses \Navindex\HtmlFormatter\Pattern
 */
final class HelperTest extends TestCase
{
    /**
     * @dataProvider providerPlaceholder
     *
     * @param string $word
     * @param string $expected
     *
     * @return void
     */
    public function testPlaceholder(string $word, string $expected)
    {
        $this->assertSame($expected, Helper::placeholder($word));
    }

    /**
     * @dataProvider providerStart
     *
     * @param string $line
     * @param string $prefix
     * @param string $expected
     *
     * @return void
     */
    public function testStart(string $line, string $prefix, string $expected)
    {
        $this->assertSame($expected, Helper::start($line, $prefix));
    }

    /**
     * @dataProvider providerFinish
     *
     * @param string $line
     * @param string $cap
     * @param string $expected
     *
     * @return void
     */
    public function testFinish(string $line, string $cap, string $expected)
    {
        $this->assertSame($expected, Helper::finish($line, $cap));
    }

    /**
     * @dataProvider providerWrap
     *
     * @param null|mixed $var
     * @param mixed[]    $expected
     *
     * @return void
     */
    public function testWrap($var, array $expected)
    {
        $this->assertSame($expected, Helper::wrap($var));
    }

    /**
     * @dataProvider providerIsAssoc
     *
     * @param mixed[] $array
     * @param bool    $expected
     *
     * @return void
     */
    public function testIsAssoc(array $array, bool $expected)
    {
        $this->assertSame($expected, Helper::isAssoc($array));
    }

    /**
     * Data provider.
     *
     * @return \Iterator <int, array <int, string>>
     */
    public function providerPlaceholder(): Iterator
    {
        $marker = Pattern::MARKER;

        yield ['name', "{$marker}name:%s:name{$marker}"];
        yield ['', "{$marker}:%s:{$marker}"];
        yield ['-1', "{$marker}-1:%s:-1{$marker}"];
        yield ["'", "{$marker}':%s:'{$marker}"];
    }

    /**
     * Data provider.
     *
     * @return \Iterator <int, array <int, string>>
     */
    public function providerStart(): Iterator
    {
        yield ['this line', 'extend ', 'extend this line'];
        yield [' this line', 'extend', 'extend this line'];
        yield [' this line', 'extend ', 'extend  this line'];
        yield ['extend this line', 'extend ', 'extend this line'];
        yield ['aaa', '', 'aaa'];
        yield ['', 'aaa', 'aaa'];
        yield ['', '', ''];
        yield ['-1', '-2', '-2-1'];
        yield ['123', '456', '456123'];
        yield ['456123', '456', '456123'];
    }

    /**
     * Data provider.
     *
     * @return \Iterator <int, array <int, string>>
     */
    public function providerFinish(): Iterator
    {
        yield ['this is a line', ' with extra', 'this is a line with extra'];
        yield ['this is a line ', 'with extra', 'this is a line with extra'];
        yield ['this is a line ', ' with extra', 'this is a line  with extra'];
        yield ['this is a line with extra', ' with extra', 'this is a line with extra'];
        yield ['aaa', '', 'aaa'];
        yield ['', 'aaa', 'aaa'];
        yield ['', '', ''];
        yield ['-1', '-2', '-1-2'];
        yield ['123', '456', '123456'];
        yield ['123456', '456', '123456'];
    }

    /**
     * Data provider.
     *
     * @return \Iterator <int, array <int, mixed>>
     */
    public function providerWrap(): Iterator
    {
        yield [null, []];
        yield [[], []];
        yield ['this is a string', ['this is a string']];
        yield [['this is a string'], ['this is a string']];
        yield [42, [42]];
        yield [[42], [42]];
        yield [['aaa' => ['bbb' => ['ccc' => 'value']]], ['aaa' => ['bbb' => ['ccc' => 'value']]]];
    }

    /**
     * Data provider.
     *
     * @return \Iterator <int, array <int, mixed[]|bool>>
     */
    public function providerIsAssoc(): Iterator
    {
        yield [[], false];
        yield [[0,1,2,3], false];
        yield [[1,2,3,4], false];
        yield [[2,3,4,6], false];
        yield [['aaa' => 'bbb'], true];
        yield [['xxx' => 'aaa', 'yyy' => 'aaa', 'zzz' => 'aaa'], true];
        yield [[0 => 'aaa', 1 => 'aaa', 2 => 'aaa'], false];
        yield [[0 => 'aaa', 1 => 'aaa', 3 => 'aaa'], false];
        yield [['0' => 'aaa', '1' => 'aaa', '2' => 'aaa'], false];
        yield [['0' => 'aaa', '1' => 'aaa', '3' => 'aaa'], false];
    }
}
