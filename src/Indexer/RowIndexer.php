<?php

namespace IndeedHat\TokenSearch\Indexer;

class RowIndexer extends AbstractIndexer
{
    /**
     * @var array
     */
    public $fields = [];

    public function index(): void
    {
        foreach ($this->data as $id => $field) {
            $fieldIndex = new FieldIndexer([$field], $id);
            $fieldIndex->withTokenizer($this->tokenizer);
            $fieldIndex->index();

            if (!$fieldIndex->words) {
                continue;
            }

            $this->addToWordList($fieldIndex->words);
            $this->fields[$id] = $fieldIndex;
        }
    }

    private function addToWordList(array $words): void
    {
        foreach ($words as $word => $count) {
            $this->words[$word] = ($this->words[$word] ?? 0) + $count;
        }
    }
}
