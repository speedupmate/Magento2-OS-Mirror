<?php

/*
 * This file is part of PHP CS Fixer.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *     Dariusz Rumiński <dariusz.ruminski@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace PhpCsFixer\Fixer\Comment;

use PhpCsFixer\AbstractFixer;
use PhpCsFixer\FixerDefinition\CodeSample;
use PhpCsFixer\FixerDefinition\FixerDefinition;
use PhpCsFixer\Tokenizer\Tokens;

/**
 * @author Dariusz Rumiński <dariusz.ruminski@gmail.com>
 */
final class NoTrailingWhitespaceInCommentFixer extends AbstractFixer
{
    /**
     * {@inheritdoc}
     */
    public function getDefinition()
    {
        return new FixerDefinition(
            'There MUST be no trailing spaces inside comments and phpdocs.',
            array(new CodeSample('<?php
// This is '.'
// a comment. '.'
'))
        );
    }

    /**
     * {@inheritdoc}
     */
    public function isCandidate(Tokens $tokens)
    {
        return $tokens->isAnyTokenKindsFound(array(T_COMMENT, T_DOC_COMMENT));
    }

    /**
     * {@inheritdoc}
     */
    protected function applyFix(\SplFileInfo $file, Tokens $tokens)
    {
        foreach ($tokens as $index => $token) {
            if ($token->isGivenKind(T_DOC_COMMENT)) {
                $token->setContent(
                    preg_replace('/[ \t]+$/m', '', $token->getContent())
                );

                continue;
            }

            if ($token->isGivenKind(T_COMMENT)) {
                if ('/*' === substr($token->getContent(), 0, 2)) {
                    $token->setContent(
                        preg_replace('/[ \t]+$/m', '', $token->getContent())
                    );
                } elseif (isset($tokens[$index + 1]) && $tokens[$index + 1]->isWhitespace()) {
                    $nextToken = $tokens[$index + 1];
                    $nextToken->setContent(
                        ltrim($nextToken->getContent(), " \t")
                    );
                }
            }
        }
    }
}
