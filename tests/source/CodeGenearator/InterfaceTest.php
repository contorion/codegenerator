<?php

namespace TestsCodeGenerator;

use CodeGenerator\ConstantBlock;
use CodeGenerator\FileBlock;
use CodeGenerator\InterfaceBlock;

class InterfaceTest extends \PHPUnit_Framework_TestCase
{
    public function testDump()
    {
        $file = new FileBlock();

        $reflectionClass = new \ReflectionClass('CodeGeneratormocks\\MockInterface');
        $reflectedClass = InterfaceBlock::buildFromReflection($reflectionClass);
        $file->addBlock($reflectedClass);

        $actual = $file->dump();
        $expected = file_get_contents($reflectionClass->getFileName());
        $this->assertSame($expected, $actual);
    }

    public function testDumpSmall()
    {
        $file = new FileBlock();

        $reflectionClass = new \ReflectionClass('CodeGeneratormocks\\MockInterfaceTwo');
        $reflectedClass = InterfaceBlock::buildFromReflection($reflectionClass);
        $file->addBlock($reflectedClass);

        $actual = $file->dump();
        $expected = file_get_contents($reflectionClass->getFileName());
        $this->assertSame($expected, $actual);
    }

    public function testDumpExtract()
    {
        $expected = <<<TEST
<?php

namespace CodeGeneratormocks;

interface MockInterfaceThree
{
    const FOO = 2;

    const BAR = 'test';
}

TEST;

        $file = new FileBlock();

        $interface = new InterfaceBlock('MockInterfaceThree');
        $file->addBlock($interface);
        $interface->setNamespace('CodeGeneratormocks');
        $interface->extractConstantsFromOtherClassOrInterface('CodeGeneratormocks\\MockInterfaceTwo');
        $interface->addConstant(new ConstantBlock('BAR', 'test'));

        $actual = $file->dump();
        $this->assertSame($expected, $actual);
    }

    public function testGetName()
    {
        $className = 'Foo';
        $class = new InterfaceBlock($className, ['Bar']);
        $this->assertSame($className, $class->getName());
    }
}
