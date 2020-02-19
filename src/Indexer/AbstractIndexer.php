<?php

namespace IndeedHat\TokenSearch\Indexer;

use IndeedHat\TokenSearch\Tokenizer\TokenizerInterface;

abstract class AbstractIndexer
{
    /**
     * @var TokenizerInterface
     */
    protected $tokenizer;

    /**
     * @var array
     */
    public $words = [];

    /**
     * @var string
     */
    public $id;

    /**
     * @var array
     */
    protected $data;

    public function __construct(array $data, string $id = "")
    {
        $this->data = $data;
        $this->id   = $id;
    }

    abstract public function index(): void;

    public function withTokenizer(?TokenizerInterface $tokenizer): void
    {
        $this->tokenizer = $tokenizer;
    }
}
