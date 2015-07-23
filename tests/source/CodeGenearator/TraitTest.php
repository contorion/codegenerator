<?php

namespace TestsCodeGenerator;

use CodeGenerator\FileBlock;
use CodeGenerator\MethodBlock;
use CodeGenerator\TraitBlock;

class TraitTest extends \PHPUnit_Framework_TestCase
{
    public function testDump()
    {
        $file = new FileBlock();

        $reflectionClass = new \ReflectionClass('\\CodeGeneratorMocks\\MockTrait');

        $reflectedClass = TraitBlock::buildFromReflection($reflectionClass);
        $file->addBlock($reflectedClass);

        $actual = $file->dump();
        $expected = file_get_contents($reflectionClass->getFileName());
        $this->assertSame($expected, $actual);
    }

    public function testGetName()
    {
        $className = 'Foo';
        $class = new TraitBlock($className);
        $this->assertSame($className, $class->getName());
    }

    public function testByHand()
    {
        $file = new FileBlock();

        $trait = new TraitBlock('TestTrait');
        $trait->addUse('\\CodeGeneratorMocks\\MockCompositeTrait');
        $trait->addMethod(new MethodBlock('testMethod', 'echo 1;'));

        $file->addBlock($trait);

        $expected = <<<TEST
<?php

trait TestTrait {

    use \CodeGeneratorMocks\MockCompositeTrait;

    public function testMethod() {
        echo 1;
    }
}

TEST;
        $this->assertSame($expected, $file->dump());
    }
}
