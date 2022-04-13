<?php

declare (strict_types=1);
namespace RectorPrefix20211221\Helmich\TypoScriptParser\Tokenizer\Printer;

use RectorPrefix20211221\Helmich\TypoScriptParser\Tokenizer\TokenInterface;
/**
 * Interface definition for a class that prints token streams
 *
 * @package    Helmich\TypoScriptParser
 * @subpackage Tokenizer\Printer
 */
interface TokenPrinterInterface
{
    /**
     * @param TokenInterface[] $tokens
     * @return string
     */
    public function printTokenStream(array $tokens) : string;
}
