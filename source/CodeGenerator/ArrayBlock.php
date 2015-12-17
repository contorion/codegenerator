<?php

namespace CodeGenerator;

class ArrayBlock extends Block
{
    /** @var array */
    private $value;

    /**
     * @param array $value
     */
    public function __construct(array $value = null)
    {
        $this->value = (array)$value;
    }

    /**
     * @return string
     */
    protected function dumpContent()
    {
        $entries = [];
        $isAssociative = $this->isAssociative();
        foreach ($this->value as $key => $value) {
            $line = '';
            if ($isAssociative) {
                    $line .= '\'' . $key . '\' => ';
            }
            $value = new ValueBlock($value);
            $line .= $value->dump();
            $entries[] = $line;
        }
        $content = implode(', ', $entries);
        if (strlen($content) < 100) {
            return '[' . $content . ']';
        } else {
            $content = implode(",\n", $entries);

            return $this->dumpLine(
                '[',
                $this->indent($content),
                ']'
            );
        }
    }

    public function isAssociative()
    {
        return (bool)count(array_filter(array_keys($this->value), 'is_string'));
    }
}
