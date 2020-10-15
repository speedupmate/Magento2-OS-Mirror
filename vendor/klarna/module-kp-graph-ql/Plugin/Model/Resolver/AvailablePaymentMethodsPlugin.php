<?php
/**
 * This file is part of the Klarna KpGraphQl module
 *
 * (c) Klarna Bank AB (publ)
 *
 * For the full copyright and license information, please view the NOTICE
 * and LICENSE files that were distributed with this source code.
 */
declare(strict_types=1);

namespace Klarna\KpGraphQl\Plugin\Model\Resolver;

use GraphQL\Language\AST\ArgumentNode;
use GraphQL\Language\AST\FieldNode;
use GraphQL\Language\AST\OperationDefinitionNode;
use Klarna\Core\Helper\ConfigHelper;
use Klarna\Kp\Api\QuoteInterface;
use Klarna\Kp\Api\QuoteRepositoryInterface;
use Magento\Framework\App\Request\Http;
use Magento\Quote\Model\MaskedQuoteIdToQuoteIdInterface;
use Magento\QuoteGraphQl\Model\Resolver\AvailablePaymentMethods;
use Magento\Store\Model\StoreManagerInterface;

class AvailablePaymentMethodsPlugin
{
    /**
     * @var ConfigHelper
     */
    private $configHelper;
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;
    /**
     * @var MaskedQuoteIdToQuoteIdInterface
     */
    private $maskedQuoteIdToQuoteId;
    /**
     * @var QuoteRepositoryInterface
     */
    private $quoteRepository;
    /**
     * @var Http
     */
    private $http;

    /**
     * @param ConfigHelper                    $configHelper
     * @param StoreManagerInterface           $storeManager
     * @param MaskedQuoteIdToQuoteIdInterface $maskedQuoteIdToQuoteId
     * @param QuoteRepositoryInterface        $quoteRepository
     * @param Http                            $http
     * @codeCoverageIgnore
     */
    public function __construct(
        ConfigHelper $configHelper,
        StoreManagerInterface $storeManager,
        MaskedQuoteIdToQuoteIdInterface $maskedQuoteIdToQuoteId,
        QuoteRepositoryInterface $quoteRepository,
        Http $http
    ) {
        $this->configHelper           = $configHelper;
        $this->storeManager           = $storeManager;
        $this->maskedQuoteIdToQuoteId = $maskedQuoteIdToQuoteId;
        $this->quoteRepository        = $quoteRepository;
        $this->http                   = $http;
    }

    /**
     * Modify results of resolve() call to apply the dynamic title for Klarna methods returned by API
     *
     * @param AvailablePaymentMethods $subject
     * @param array $list
     * @return array
     *
     * @SuppressWarnings(PMD.UnusedFormalParameter)
     */
    public function afterResolve(
        AvailablePaymentMethods $subject,
        array $list
    ): array {
        $store = $this->storeManager->getStore();
        if (!$this->configHelper->isPaymentConfigFlag('active', $store->getId())) {
            return $list;
        }

        $maskedCartId = $this->getMaskedQuoteId();
        if ($maskedCartId === null) {
            return $list;
        }

        $klarnaQuote = $this->getKlarnaQuote($maskedCartId);
        $paymentCategories = json_decode(json_encode($klarnaQuote->getPaymentMethodInfo()), true);

        return $this->getNewList($paymentCategories, $list);
    }

    /**
     * Getting back the masked quote id.
     * Parts of the logic was copied from \GraphQL\GraphQL::promiseToExecute().
     *
     * @return string
     * @throws \GraphQL\Error\SyntaxError
     *
     * @SuppressWarnings(PMD.StaticAccess)
     */
    private function getMaskedQuoteId(): ?string
    {
        $documentNode = \GraphQl\Language\Parser::parse(json_decode($this->http->getContent(), true)['query']);

        /** @var OperationDefinitionNode $definition */
        foreach ($documentNode->definitions as $definition) {
            /** @var FieldNode $selection */
            foreach ($definition->selectionSet->selections as $selection) {
                /** @var ArgumentNode $argument */
                foreach ($selection->arguments as $argument) {
                    if ($argument->name->value === 'cart_id') {
                        return $argument->value->value;
                    }
                }
            }
        }

        return null;
    }

    /**
     * Getting back the Klarna quote based on the masked cart id
     *
     * @param string $maskedCartId
     * @return QuoteInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function getKlarnaQuote(string $maskedCartId): QuoteInterface
    {
        $quoteId = $this->maskedQuoteIdToQuoteId->execute($maskedCartId);
        return $this->quoteRepository->getActiveByQuoteId($quoteId);
    }

    /**
     * Getting back the original list with Klarna entries
     *
     * @param array $paymentCategories
     * @param array $list
     * @return array
     */
    private function getNewList(array $paymentCategories, array $list): array
    {
        $paymentCategories = array_map(function ($paymentCategory) {
            return [
                'title' => $paymentCategory['name'],
                'code'  => 'klarna_' . $paymentCategory['identifier']
            ];
        }, $paymentCategories);
        $list = array_reverse(array_merge($list, $paymentCategories));
        $newList = [];
        foreach ($list as $method) {
            if (!in_array($method['code'], array_column($newList, 'code'))) {
                $newList[] = $method;
            }
        }
        return $newList;
    }
}
