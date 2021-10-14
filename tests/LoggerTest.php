<?php

declare(strict_types=1);

namespace Navindex\HtmlFormatter\Tests;

use Iterator;
use Navindex\HtmlFormatter\Logger;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Navindex\HtmlFormatter\Logger
 */
final class LoggerTest extends TestCase
{
    /**
     * @return void
     */
    public function testConstructor()
    {
        $logger = new Logger();
        $this->assertEquals([], $logger->get());
    }

    /**
     * @dataProvider providerPushSingle
     *
     * @param  mixed   $rule
     * @param  mixed   $subject
     * @param  mixed   $matches
     * @param  mixed[] $expected
     * @return void
     */
    public function testPushSingle($rule, $subject, $matches, array $expected)
    {
        $logger = new Logger();
        $logger->push($rule, $subject, $matches);

        $this->assertSame($expected, $logger->get());
    }

    /**
     * @dataProvider providerPushMulti
     *
     * @param  array[] $data
     * @param  array[] $expected
     * @return void
     */
    public function testPushMulti(array $data, array $expected)
    {
        $logger = new Logger();

        foreach ($data as $row) {
            $logger->push(...$row);
        }

        $this->assertSame($expected, $logger->get());
    }

    /**
     * @dataProvider providerPushSingle
     *
     * @param  mixed   $rule
     * @param  mixed   $subject
     * @param  mixed   $matches
     * @param  mixed[] $expected
     * @return void
     */
    public function testClearSingle($rule, $subject, $matches, array $expected)
    {
        $logger = new Logger();
        $logger->push($rule, $subject, $matches);
        $logger->clearLog();

        $this->assertSame([], $logger->get());
    }

    /**
     * @dataProvider providerPushMulti
     *
     * @param  array[] $data
     * @param  array[] $expected
     * @return void
     */
    public function testClearMulti(array $data, array $expected)
    {
        $logger = new Logger();

        foreach ($data as $row) {
            $logger->push(...$row);
        }
        $logger->clearLog();

        $this->assertSame([], $logger->get());
    }

    /**
     * Data provider.
     *
     * @return \Iterator <int, array<int, mixed>>
     */
    public function providerPushSingle(): Iterator
    {
        yield [
            'my_rule', 'my_subject', 'my_matches', [
                [
                    'rule'    => 'my_rule',
                    'subject' => 'my_subject',
                    'matches' => 'my_matches',
                ],
            ],
        ];
        yield [
            false, -12, ['something', 6, null, true], [
                [
                    'rule'    => false,
                    'subject' => -12,
                    'matches' => ['something', 6, null, true],
                ],
            ],
        ];
    }

    /**
     * Data provider.
     *
     * @return \Iterator <int, array[]>
     */
    public function providerPushMulti(): Iterator
    {
        yield [
            [
                ['my_rule', 'my_subject', 'my_matches'],
                [false, null, ['something', 6, null, true]],
                [[], 0, ''],
            ],
            [
                [
                    'rule'    => 'my_rule',
                    'subject' => 'my_subject',
                    'matches' => 'my_matches',
                ],
                [
                    'rule'    => false,
                    'subject' => null,
                    'matches' => ['something', 6, null, true],
                ],
                [
                    'rule'    => [],
                    'subject' => 0,
                    'matches' => '',
                ],

            ],
        ];
    }
}
