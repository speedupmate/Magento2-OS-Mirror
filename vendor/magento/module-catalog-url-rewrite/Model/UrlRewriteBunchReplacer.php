<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogUrlRewrite\Model;

use Magento\UrlRewrite\Model\UrlPersistInterface;

/**
 * Url Rewrite Replacer based on bunches.
 */
class UrlRewriteBunchReplacer
{
    /**
     * Url Persist Interface.
     *
     * @var UrlPersistInterface
     */
    private $urlPersist;

    /**
     * @param UrlPersistInterface $urlPersist
     */
    public function __construct(UrlPersistInterface $urlPersist)
    {
        $this->urlPersist = $urlPersist;
    }

    /**
     * Do Bunch Replace, with default bunch value = 10000.
     *
     * @param array $urls
     * @param int $bunchSize
     * @return void
     */
    public function doBunchReplace(array $urls, $bunchSize = 10000)
    {
        foreach (array_chunk($urls, $bunchSize) as $urlsBunch) {
            $this->urlPersist->replace($urlsBunch);
        }
    }
}
