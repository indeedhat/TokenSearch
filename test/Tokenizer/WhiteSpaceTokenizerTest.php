<?php

namespace IndeedHat\TokenSearch\Test\Tokenizer;

use IndeedHat\TokenSearch\Tokenizer\TokenizerInterface;
use IndeedHat\TokenSearch\Tokenizer\WhiteSpaceTokenizer;
use PHPUnit\Framework\TestCase;

class WhiteSpaceTokenizerTest extends TestCase
{
    public function dataProvider(): array
    {
        return [
            [
                "this is a simple string",
                [
                    "this", "is", "a", "simple", "string"
                ]
            ],
            [
                " this one has extra  whitespace ",
                [
                    "this", "one", "has", "extra", "whitespace",
                ]
            ],
            [
                "this\tone \rhas \n other \r\n whitespace characters",
                [
                    "this", "one", "has", "other", "whitespace", "characters"
                ]
            ],
            [
                "this one has duplicate words has this one it has",
                [
                    "this", "one", "has", "duplicate", "words", "has", "this", "one", "it", "has"
                ]
            ],
            [
                "this, one has none word \$$@*",
                [
                    "this,", "one", "has", "none", "word", "\$$@*"
                ]
            ]
        ];
    }

    /**
     * @test
     */
    public function implementsTokenizerInterface(): void
    {
        $tokeinzer = new WhiteSpaceTokenizer;

        $this->assertInstanceOf(TokenizerInterface::class, $tokeinzer);
    }

    /**
     * @test
     * @dataProvider dataProvider
     */
    public function providesValidTokens(string $text, $expectedTokens)
    {
        $tokenizer = new WhiteSpaceTokenizer;
        $wordList = $tokenizer->tokenize($text);

        $this->assertEquals($expectedTokens, $wordList, "Tokenizer did not produce the expected word list");
    }
}
