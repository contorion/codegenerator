<?php

namespace TestsCodeGenerator;

use CodeGenerator\DocBlock;

class DocBlockTest extends \PHPUnit_Framework_TestCase
{
    public function testSimpleDump()
    {
        $block = new DocBlock();

        $block->addText('Class MockDocBlock');
        $block->addTag('package', 'CodeGeneratormocks');
        $block->addTag('author', 'Christian Burgas');

        $expected = file_get_contents(__DIR__ . '/../../mocks/MockDocBlock.php');

        self::assertEquals($expected, $block->dump());
    }
}
