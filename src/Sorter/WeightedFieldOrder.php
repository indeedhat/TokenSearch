<?php

namespace IndeedHat\TokenSearch\Sorter;

use IndeedHat\TokenSearch\Database\StorageAdapterInterface;
use IndeedHat\TokenSearch\ResultsCollection;
use IndeedHat\TokenSearch\Result;

class WeightedFieldOrder implements SorterInterface
{
    /**
     * @var bool
     */
    public $partialWords;

    public function run(StorageAdapterInterface $storage, string $key, array $tokens, array $fields = []): ResultsCollection
    {
        $docs = !empty($fields)
            ? $this->withFields($storage, $key, $tokens, $fields)
            : $this->withoutFields($storage, $key, $tokens);

        $results = [];

        foreach ($docs as $id => $weight) {
            $results[] = new Result($id, $weight);
        }

        $collection = new ResultsCollection($results, count($results));
        $collection->reorder(ResultsCollection::ORDER_ASC, ResultsCollection::ORDER_BY_ID);

        return $collection;
    }

    private function withoutFields(StorageAdapterInterface $storage, string $key, array $tokens): array
    {
        // TODO: decide if this is what i want to do when no fields are provided
        throw new Exception("searching without field weights is not supported on this sorter");
    }

    private function withFields(StorageAdapterInterface $storage, string $key, array $tokens, array $fields): array
    {
        $docs = [];

        foreach ($tokens as $token) {
            if ($this->partialWords) {
                $tmp = array_merge($docs, $storage->fieldsForPartialToken($key, $token));
            } else {
                $tmp = array_merge($docs, $storage->fieldsForToken($key, $token));
            }

            foreach ($tmp as $doc) {
                $weight = $this->calcDocWeight($token, $doc, $fields);
                if (!$weight) {
                    continue;
                }

                if (!empty($docs[$doc["doc_id"]])) {
                    $docs[$doc["doc_id"]] += $weight;
                } else {
                    $docs[$doc["doc_id"]] = $weight;
                }
            }
        }

        return $docs;
    }

    private function calcDocWeight(string $token, array $docField, array $fields): float
    {
        if (empty($fields[$docField["field"]])) {
            return 0;
        }

        $modifier = 1;
        if ($this->partialWords) {
            $modifier = 1 / strlen($docField["word"]) * strlen($token);
        }

        return $fields[$docField["field"]] * $modifier;
    }
}
