<?php

namespace CodeGenerator;

class FileBlock extends Block
{
    /** @var Block[] */
    private $blocks = [];

    /**
     * @param Block $block
     */
    public function addBlock(Block $block)
    {
        $this->blocks[] = $block;
    }

    protected function dumpContent()
    {
        $lines = [];
        $lines[] = '<?php';
        foreach ($this->blocks as $block) {
            $lines[] = '';
            $lines[] = $block->dump();
        }
        $lines[] = '';

        return $this->dumpLines($lines);
    }
}
