<?php

namespace IndeedHat\TokenSearch\Indexer;

use IndeedHat\TokenSearch\Tokenizer\WhiteSpaceTokenizer;

class FieldIndexer extends AbstractIndexer
{
    public function index(): void
    {
        if (!$this->tokenizer) {
            $this->tokenizer = new WhiteSpaceTokenizer();
        }

        if (empty($this->data[0])) {
            return;
        }

        $wordList    = $this->tokenizer->tokenize($this->data[0]);
        $this->words = array_count_values($wordList);
    }
}
