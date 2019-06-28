<?php

namespace IndeedHat\TokenSearch\Sorter;

use IndeedHat\TokenSearch\Database\StorageAdapterInterface;
use IndeedHat\TokenSearch\ResultsCollection;

class DocIdSorter implements SorterInterface
{
    public $partialWords;

    public function run(StorageAdapterInterface $storage, array $tokens, array $fields = []): ResultsCollection
    {
        $docs = !empty($fields) 
            ? $this->withFields($storage, $tokens, $fields)
            : $this->withoutFields($storage, $tokens);

        $docs = array_map(function($id) {
            return new Result($id, 1);
        }, $docs);

        $collection = new ResultsCollection();
        $collection->reorder(ResultsCollection::ORDER_ASC, ResultsCollection::ORDER_BY_ID);

        return $collection;
    }

    private function withFields(StorageAdapterInterface $storage, array $tokens, array $fields): array
    {
        $docs = [];

        foreach ($tokens as $token) {
            if ($this->partialWords) {
                $docs = array_merge($storage->fieldsForPartialToken($token));
            } else {
                $docs = array_merge($storage->fieldsForToken($token));
            }
        }

        $docs = array_filter($docs, function(array $val) use ($fields) {
            return $fields[$val["word"]] ?? false;
        });

        $docs = array_map(function(array $val) {
            return $val["doc_id"];
        }, $docs);

        return array_unique($docs);

    }

    private function withoutFields(StorageAdapterInterface $storage, array $tokens): array
    {
        $docs = [];

        foreach ($tokens as $token) {
            if ($this->partialWords) {
                $docs = array_merge($storage->docsForPartialToken($token));
            } else {
                $docs = array_merge($storage->docsForToken($token));
            }
        }

        $docs = array_map(function (array $val) {
            return $val["doc_id"];
        }, $docs);

        return array_unique($docs);
    }
}
