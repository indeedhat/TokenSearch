<?php

namespace IndeedHat\TokenSearch\Tokenizer;

class WhiteSpaceTokenizer implements TokenizerInterface
{
    public function tokenize(string $subject): array
    {
        return preg_split("/\s+/", $subject, 0, PREG_SPLIT_NO_EMPTY);
    }
}
