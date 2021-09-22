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
     * @dataProvider providerFinish
     *
     * @param string $line
     * @param string $expected
     *
     * @return void
     */
    public function testFinish(string $line, string $cap, string $expected)
    {
        $this->assertSame($expected, Helper::finish($line, $cap));
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
}
