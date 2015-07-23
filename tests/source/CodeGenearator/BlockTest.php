<?php

namespace TestsCodeGenerator;

use CodeGenerator\Block;
use CodeGenerator\FileBlock;
use CodeGeneratorHelpers\TestHelper;

class BlockTest extends \PHPUnit_Framework_TestCase
{
    public function testOutdent()
    {
        $block = new FileBlock();
        $cases = [
            '    foo' => 'foo',
            'foo' => 'foo',
            "    foo\nbar" => "foo\nbar",
            '            foo' => '        foo',
        ];
        foreach ($cases as $input => $expected) {
            $output = TestHelper::invokeMethod($block, '_outdent', [$input]);
            $this->assertSame($expected, $output);
        }
    }

    public function testOutdentUntilSafe()
    {
        $block = new FileBlock();
        $cases = [
            "    foo\nbar" => "    foo\nbar",
            "        foo\n    bar" => "    foo\nbar",
            '            foo' => 'foo',
        ];
        foreach ($cases as $input => $expected) {
            $output = TestHelper::invokeMethod($block, '_outdent', [$input, true]);
            $this->assertSame($expected, $output);
        }
    }

    public function testIndent()
    {
        $block = new FileBlock();
        $cases = [
            "foo\nbar" => "    foo\n    bar",
            "    foo\n    bar" => "        foo\n        bar",
        ];
        foreach ($cases as $input => $expected) {
            $output = TestHelper::invokeMethod($block, '_indent', [$input, true]);
            $this->assertSame($expected, $output);
        }
    }

    public function testSetIndentation()
    {
        Block::setIndentation('  ');
        $block = new FileBlock();

        $output = TestHelper::invokeMethod($block, '_indent', ['foo', true]);
        $this->assertSame('  foo', $output);
        $output = TestHelper::invokeMethod($block, '_outdent', ["  foo\n    bar", true]);
        $this->assertSame("foo\n  bar", $output);

        Block::setIndentation('    ');
    }
}
