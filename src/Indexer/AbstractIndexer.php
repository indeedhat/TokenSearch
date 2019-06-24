<?php

namespace IndeedHat\TokenSearch\Indexer;

abstract class AbstractIndexer
{
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

    function __construct(array $data, string $id = "")
    {
        $this->data = $data;
        $this->id = $id;
    }

    public abstract function index(): void;
}
