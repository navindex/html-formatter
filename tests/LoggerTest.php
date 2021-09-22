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
     * @param mixed   $rule
     * @param mixed   $pattern
     * @param mixed   $subject
     * @param mixed   $matches
     * @param mixed[] $expected
     *
     * @return void
     */
    public function testPushSingle($rule, $pattern, $subject, $matches, array $expected)
    {
        $logger = new Logger();
        $logger->push($rule, $pattern, $subject, $matches);

        $this->assertSame($expected, $logger->get());
    }

    /**
     * @dataProvider providerPushMulti
     *
     * @param array[] $data
     * @param array[] $expected
     *
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
     * @param mixed   $rule
     * @param mixed   $pattern
     * @param mixed   $subject
     * @param mixed   $matches
     * @param mixed[] $expected
     *
     * @return void
     */
    public function testClearSingle($rule, $pattern, $subject, $matches, array $expected)
    {
        $logger = new Logger();
        $logger->push($rule, $pattern, $subject, $matches);
        $logger->clearLog();

        $this->assertSame([], $logger->get());
    }

    /**
     * @dataProvider providerPushMulti
     *
     * @param array[] $data
     * @param array[] $expected
     *
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
     * @return \Iterator <int, mixed>
     */
    public function providerPushSingle(): Iterator
    {
        yield [
            'my_rule', 'my_pattern', 'my_subject', 'my_matches', [
                [
                    'rule'    => 'my_rule',
                    'pattern' => 'my_pattern',
                    'subject' => 'my_subject',
                    'matches' => 'my_matches'
                ]
            ]
        ];
        yield [
            false, -12, null, ['something', 6, null, true], [
                [
                    'rule'    => false,
                    'pattern' => -12,
                    'subject' => null,
                    'matches' => ['something', 6, null, true]
                ]
            ]
        ];
    }

    /**
     * Data provider.
     *
     * @return \Iterator <int, array>
     */
    public function providerPushMulti(): Iterator
    {
        yield [
            [
                ['my_rule', 'my_pattern', 'my_subject', 'my_matches'],
                [false, -12, null, ['something', 6, null, true]],
                [[], true, 0, ''],
            ],
            [
                [
                    'rule'    => 'my_rule',
                    'pattern' => 'my_pattern',
                    'subject' => 'my_subject',
                    'matches' => 'my_matches'
                ],
                [
                    'rule'    => false,
                    'pattern' => -12,
                    'subject' => null,
                    'matches' => ['something', 6, null, true]
                ],
                [
                    'rule'    => [],
                    'pattern' => true,
                    'subject' => 0,
                    'matches' => ''
                ]

            ]
        ];
    }
}
