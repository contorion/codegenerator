<?php

namespace CodeGenerator;

class DocBlock extends Block
{
    const DOC_BLOCK_START = '/**';
    const DOC_BLOCK_END = '*/';
    const DOC_BLOCK_INDENT = '*';

    /**
     * @var array
     */
    protected static $allowedTags = [
        'api',
        'author',
        'category',
        'copyright',
        'deprecated',
        'example',
        'filesource',
        'global',
        'ignore',
        'internal',
        'license',
        'link',
        'method',
        'package',
        'param',
        'property',
        'property-read',
        'property-write',
        'return',
        'see',
        'since',
        'source',
        'subpackage',
        'throws',
        'todo',
        'uses',
        'used-by',
        'var',
        'version',
    ];


    /**
     * @var array
     */
    protected $texts = [];

    /**
     * @var array
     */
    protected $tags = [];

    /**
     * @param \Reflector $reflection
     *
     * @return DocBlock|null
     */
    public static function createMethodDocBlockFromReflection(\Reflector $reflection)
    {
        $isClass = $reflection instanceof \ReflectionClass;
        $isFunction = $reflection instanceof \ReflectionFunctionAbstract;
        $isProperty = $reflection instanceof \ReflectionProperty;

        if (!$isClass && !$isFunction && !$isProperty) {
            return null;
        }
        /** @var \ReflectionClass $reflection */
        $string = $reflection->getDocComment();
        if (!$string) {
            return null;
        }

        return self::createDocBlockFromCommentString($string);
    }

    /**
     * @param $string
     *
     * @return DocBlock
     */
    public static function createDocBlockFromCommentString($string)
    {

        // cleanup
        $string = trim($string);
        if (substr($string, 0, 3) === self::DOC_BLOCK_START) {
            $string = substr($string, 3);
        }
        if (substr($string, -2) === self::DOC_BLOCK_END) {
            $string = substr($string, 0, strlen($string) - strlen(self::DOC_BLOCK_END));
        }
        $string = rtrim($string);
        $string = trim($string, "\n");

        $block = new self();
        $lines = explode(PHP_EOL, $string);

        foreach ($lines as $line) {
            if (self::isTag($line) && preg_match('/(\s?\*\s?)*(@([\w]+))(?=\s|$)/', $line, $matches)) {
                $tag = $matches[3];
                $content = trim(str_replace($matches[0], '', $line));
                $block->addText('@' . $tag . ' ' . $content);
            } else {
                if (!preg_match('/(\s?\*\s?)(.*)/', $line, $matches)) {
                    continue;
                }
                $block->addText($matches[2]);
            }
        }

        return $block;
    }

    /**
     * @param $tagName
     *
     * @return bool
     */
    protected static function isTag($tagName)
    {
        return 0 !== preg_match('/(^[\\*\s*]*@)/', $tagName);
    }

    /**
     * @param $text
     *
     * @return $this
     */
    public function addText($text)
    {
        $splitText = str_split($text, 72);

        foreach ($splitText as $line) {
            $this->texts[] = $line;
        }

        return $this;
    }

    /**
     * @param $tagName
     * @param $content
     *
     * @return $this
     */
    public function addTag($tagName, $content)
    {
        if (substr($tagName, 0, 1) === '@') {
            $tagName = substr($tagName, 1);
        }
        if (!in_array($tagName, self::$allowedTags, true)) {
            return $this;
        }

        $this->tags[] = ['tag' => '@' . $tagName, 'content' => $content];

        return $this;
    }

    /**
     * @return string
     */
    public function dump()
    {
        return $this->dumpContent();
    }

    /**
     * @return string
     */
    protected function dumpContent()
    {
        if (!count($this->texts) && !count($this->tags)) {
            return '';
        }

        $lines = [];
        $lines[] = self::DOC_BLOCK_START;
        if (count($this->texts)) {
            $line = '';
            foreach ($this->texts as $line) {
                $lines[] = rtrim(' * ' . $line);
            }
            if ($line !== '' && count($this->tags)) {
                // if last line was already empty, do not add another one
                $lines[] = ' ' . self::DOC_BLOCK_INDENT;
            }
        }

        foreach ($this->tags as $row) {
            $content = ' ' . $row['tag'] . ' ' . $row['content'];
            $lines[] = ' ' . self::DOC_BLOCK_INDENT . $content;
        }
        $lines[] = ' ' . self::DOC_BLOCK_END;

        return $this->dumpLines($lines);
    }
}
