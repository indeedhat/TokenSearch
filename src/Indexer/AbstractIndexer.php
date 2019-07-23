<?php

namespace IndeedHat\TokenSearch\Indexer;

abstract class AbstractIndexer
{
    /**
     * @var TokenizerInterface
     */
    protected $tokenizer;

    /**
     * @var array
     */
    public $words;

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

    public function withTokenizer(TokenizerInterface $tokenizer): self
    {
        $this->tokenizer = $tokenizer;
    }
}
