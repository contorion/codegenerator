<?php

namespace CodeGenerator;

abstract class Block implements GeneratorConstants
{
    /** @var string */
    protected static $indentation = '    ';
    /** @var  DocBlock */
    protected $docBlock;

    /**
     * @param string $indentation
     */
    public static function setIndentation($indentation)
    {
        self::$indentation = (string)$indentation;
    }

    /**
     * @param string $className
     *
     * @return string
     */
    protected static function normalizeClassName($className)
    {
        if (strpos($className, '\\') !== 0) {
            $className = '\\' . $className;
        }

        return $className;
    }

    /**
     * @param DocBlock $docBlock
     *
     * @return $this
     */
    public function setDocBlock($docBlock)
    {
        $this->docBlock = $docBlock;

        return $this;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->dump();
    }

    /**
     * @return string
     */
    public function dump()
    {
        if ($this->docBlock) {
            $docBlockText = $this->docBlock->dump();
            if ($docBlockText) {
                $docBlockText .= PHP_EOL;
            }

            return $docBlockText . '' . $this->dumpContent();
        }

        return $this->dumpContent();
    }

    /**
     * @return string
     */
    abstract protected function dumpContent();

    /**
     * @param string $content
     *
     * @return string
     */
    protected function indent($content)
    {
        return preg_replace('/(:?^|[\n])/', '$1' . self::$indentation, $content);
    }

    /**
     * @param string $content
     * @param bool|null $untilUnsafe
     *
     * @return string
     */
    protected function outdent($content, $untilUnsafe = null)
    {
        $indentation = self::$indentation;
        if (!$indentation) {
            return $content;
        }
        $lines = explode(PHP_EOL, $content);
        if ($untilUnsafe) {
            $nonemptyLines = array_filter(
                $lines,
                function ($line) {
                    return (bool)trim($line);
                }
            );
            $unsafeLines = array_filter(
                $nonemptyLines,
                function ($line) use ($indentation) {
                    return strpos($line, $indentation) !== 0;
                }
            );
            if (count($unsafeLines) || !count($nonemptyLines)) {
                return $content;
            }
        }
        foreach ($lines as $key => $line) {
            $lines[$key] = preg_replace('/^' . preg_quote(self::$indentation) . '/', '$1', $line);
        }
        $content = implode(PHP_EOL, $lines);
        if ($untilUnsafe) {
            $content = $this->outdent($content, $untilUnsafe);
        }

        return $content;
    }

    /**
     * @param string $line , $line, $line
     *
     * @return string
     */
    protected function dumpLine($line)
    {
        $lines = func_get_args();

        return $this->dumpLines($lines);
    }

    /**
     * @param \Reflector $reflection
     */
    protected function setDocBlockFromReflection(\Reflector $reflection)
    {
        $block = DocBlock::createMethodDocBlockFromReflection($reflection);
        if ($block) {
            $this->setDocBlock($block);
        }
    }

    /**
     * @param string[] $lines
     *
     * @return string
     */
    protected function dumpLines(array $lines)
    {
        return implode(
            PHP_EOL,
            array_filter(
                $lines,
                function ($element) {
                    return !is_null($element);
                }
            )
        );
    }
}
