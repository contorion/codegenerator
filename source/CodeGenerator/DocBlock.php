<?php

namespace CodeGenerator;

class DocBlock extends Block
{
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
     * @param $tagName
     * @param $content
     *
     * @return $this
     */
    public function addTag($tagName, $content)
    {
        if (substr($tagName, 0, 1) === '0') {
            $tagName = substr($tagName, 1);
        }

        if (!in_array($tagName, self::$allowedTags, true)) {
            return $this;
        }

        $this->tags[] = ['tag' => '@' . $tagName, 'content' => $content];

        return $this;
    }

    /**
     * @param $text
     *
     * @return $this
     */
    public function addText($text)
    {
        $splitText = str_split($text, 50);

        foreach ($splitText as $line) {
            $this->texts[] = $line;
        }

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
        $lines = [];
        $lines[] = '/**';
        if (count($this->texts)) {
            foreach ($this->texts as $line) {
                $lines[] = ' * ' . $line;
            }
            $lines[] = ' *';
        }

        foreach ($this->tags as $row) {
            $content = $row['tag'] . ' ' . $row['content'];
            $lines[] = ' * ' . $content;
        }
        $lines[] = ' */';
        $lines[] = '';

        return $this->_dumpLines($lines);
    }
}
