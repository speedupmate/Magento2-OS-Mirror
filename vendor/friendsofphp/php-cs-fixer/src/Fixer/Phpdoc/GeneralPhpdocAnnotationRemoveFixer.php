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

namespace PhpCsFixer\Fixer\Phpdoc;

use PhpCsFixer\AbstractFixer;
use PhpCsFixer\DocBlock\DocBlock;
use PhpCsFixer\Fixer\ConfigurableFixerInterface;
use PhpCsFixer\FixerDefinition\CodeSample;
use PhpCsFixer\FixerDefinition\FixerDefinition;
use PhpCsFixer\Tokenizer\Tokens;

/**
 * @author Graham Campbell <graham@alt-three.com>
 * @author Dariusz Rumiński <dariusz.ruminski@gmail.com>
 */
final class GeneralPhpdocAnnotationRemoveFixer extends AbstractFixer implements ConfigurableFixerInterface
{
    /**
     * @var string[]
     */
    protected $configuration;

    /**
     * @var array
     */
    private static $defaultConfiguration = array();

    /**
     * {@inheritdoc}
     */
    public function configure(array $configuration = null)
    {
        if (null === $configuration) {
            $this->configuration = self::$defaultConfiguration;

            return;
        }

        $this->configuration = $configuration;
    }

    /**
     * {@inheritdoc}
     */
    public function getDefinition()
    {
        return new FixerDefinition(
            'Configured annotations should be omitted from phpdocs.',
            array(
                new CodeSample(
                    '<?php
/**
 * @internal
 * @author someone
 */
function foo() {}',
                    array('author')
                ),
            ),
            null,
            'Array of not wanted annotations could be configured, eg `[\'@author\']`.',
            self::$defaultConfiguration
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getPriority()
    {
        // must be run before the PhpdocSeparationFixer, PhpdocOrderFixer,
        // PhpdocTrimFixer and PhpdocNoEmptyReturnFixer.
        return 10;
    }

    /**
     * {@inheritdoc}
     */
    public function isCandidate(Tokens $tokens)
    {
        return $tokens->isTokenKindFound(T_DOC_COMMENT);
    }

    /**
     * {@inheritdoc}
     */
    protected function applyFix(\SplFileInfo $file, Tokens $tokens)
    {
        if (!count($this->configuration)) {
            return;
        }

        foreach ($tokens as $token) {
            if (!$token->isGivenKind(T_DOC_COMMENT)) {
                continue;
            }

            $doc = new DocBlock($token->getContent());
            $annotations = $doc->getAnnotationsOfType($this->configuration);

            // nothing to do if there are no annotations
            if (empty($annotations)) {
                continue;
            }

            foreach ($annotations as $annotation) {
                $annotation->remove();
            }

            $token->setContent($doc->getContent());
        }
    }
}
