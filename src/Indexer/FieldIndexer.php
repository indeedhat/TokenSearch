<?php

namespace IndeedHat\TokenSearch\Indexer;

use IndeedHat\TokenSearch\Tokenizer\TokenizerInterface;
use IndeedHat\TokenSearch\Tokenizer\WhiteSpaceTokenizer;

class FieldIndexer extends AbstractIndexer
{
    /**
     * @var TokenizerInterface
     */
    private $tokenizer;

    public function index(): void
    {
        if (!$this->tokenizer) {
            $this->tokenizer = new WhiteSpaceTokenizer;
        }

        $wordList = $this->tokenizer->tokenize($this->data[0]);
        $this->words = array_count_values($wordList);
    }

    public function withTokenizer(TokenizerInterface $tokenizer): self
    {
        $this->tokenizer = $tokenizer;
    }

}
