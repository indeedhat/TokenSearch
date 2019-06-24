<?php

namespace IndeedHat\TokenSearch\Tokenizer;

interface TokenizerInterface
{
    public function tokenize(string $subject): array;
}
