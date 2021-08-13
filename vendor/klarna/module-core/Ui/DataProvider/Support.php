<?php
/**
 * This file is part of the Klarna Core module
 *
 * (c) Klarna Bank AB (publ)
 *
 * For the full copyright and license information, please view the NOTICE
 * and LICENSE files that were distributed with this source code.
 */
declare(strict_types=1);

namespace Klarna\Core\Ui\DataProvider;

use Magento\Ui\DataProvider\AbstractDataProvider;
use Magento\Framework\Api\Filter;
use Magento\Backend\Model\Auth;

class Support extends AbstractDataProvider
{
    /**
     * @var Auth
     */
    private $auth;

    /**
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param Auth   $auth
     * @param array  $meta
     * @param array  $data
     * @codeCoverageIgnore
     */
    public function __construct(
        string $name,
        string $primaryFieldName,
        string $requestFieldName,
        Auth   $auth,
        array  $meta = [],
        array  $data = []
    ) {
        parent::__construct(
            $name,
            $primaryFieldName,
            $requestFieldName,
            $meta,
            $data
        );
        $this->auth = $auth;
    }

    /**
     * We can provide prefill data this way
     * @return array
     */
    public function getData(): array
    {
        $currentUser = $this->auth->getUser();
        return [
            'new' => [
                'contact_name' => $currentUser->getName(),
                'contact_email'   => $currentUser->getEmail(),
            ]
        ];
    }
    // phpcs:disable Magento2.CodeAnalysis.EmptyBlock
    /**
     * we need to override this method to disable it
     * @param Filter $filter
     * @SuppressWarnings(PMD.UnusedFormalParameter)
     */
    public function addFilter(Filter $filter)
    {
    }
}
