<?php

namespace TestsCodeGenerator;

use CodeGenerator\DocBlock;

class DocBlockTest extends \PHPUnit_Framework_TestCase
{
    protected $exampleToParseOne = <<<HERE
/**
 * Get specific types of annotations only.
 *
 * If none exist, we're returning an empty array.
 *
 * @param string|string[] \$types
 *
 * @return Annotation[]
 */
HERE;
    protected $exampleToParseTwo = '/** @var array */';
    protected $exampleToParseTwoExpected = <<<HERE
/**
 * @var array
 */
HERE;

    public function testSimpleDump()
    {
        $block = new DocBlock();

        $block->addText('Class MockDocBlock');
        $block->addTag('package', 'CodeGeneratormocks');
        $block->addTag('author', 'Christian Burgas');

        $expected = file_get_contents(__DIR__ . '/../../mocks/MockDocBlock.php');

        self::assertEquals($expected, $block->dump());
    }

    public function testFromMultiLineString()
    {
        $docBlock = DocBlock::createDocBlockFromCommentString($this->exampleToParseOne);

        self::assertEquals($this->exampleToParseOne, $docBlock->dump());
    }

    public function testFromString()
    {
        $docBlock = DocBlock::createDocBlockFromCommentString($this->exampleToParseTwo);

        self::assertEquals($this->exampleToParseTwoExpected, $docBlock->dump());
    }


}
