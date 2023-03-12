<?php
/**
 * BSS Commerce Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://bsscommerce.com/Bss-Commerce-License.txt
 *
 * @category   BSS
 * @package    Bss_ProductStockAlertGraphQl
 * @author     Extension Team
 * @copyright  Copyright (c) 2020-2021 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
declare(strict_types=1);

namespace Bss\ProductStockAlertGraphQl\Model\Resolver;

use Bss\ProductStockAlert\Model\ResourceModel\Stock;
use Bss\ProductStockAlert\Model\StockFactory;
use Exception;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlAuthorizationException;
use Magento\Framework\GraphQl\Query\Resolver\Value;
use Magento\Framework\GraphQl\Query\Resolver\ValueFactory;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\GraphQl\Model\Query\ContextInterface;
use Magento\Store\Model\StoreManagerInterface;

class UnSubscribeAll implements ResolverInterface
{
    /**
     * @var StockFactory
     */
    protected $stockFactory;

    /**
     * @var Stock
     */
    protected $stockResource;

    /**
     * @var CustomerRepositoryInterface
     */
    protected $customerRepository;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var ValueFactory
     */
    protected $valueFactory;

    /**
     * UnSubscribeAll constructor.
     * @param StockFactory $stockFactory
     * @param CustomerRepositoryInterface $customerRepository
     * @param StoreManagerInterface $storeManager
     * @param ValueFactory $valueFactory
     */
    public function __construct(
        StockFactory $stockFactory,
        CustomerRepositoryInterface $customerRepository,
        StoreManagerInterface $storeManager,
        ValueFactory $valueFactory
    ) {
        $this->stockFactory = $stockFactory;
        $this->customerRepository = $customerRepository;
        $this->storeManager = $storeManager;
        $this->valueFactory = $valueFactory;
    }

    /**
     * @param Field $field
     * @param \Magento\Framework\GraphQl\Query\Resolver\ContextInterface $context
     * @param ResolveInfo $info
     * @param array|null $value
     * @param array|null $args
     * @return Value
     * @throws GraphQlAuthorizationException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ): Value {
        $resultData = [];
        $currentUserId = $context->getUserId();
        /**
         * @var ContextInterface $context
         */
        if (false === $context->getExtensionAttributes()->getIsCustomer()) {
            throw new GraphQlAuthorizationException(__('The request is allowed for logged in customer'));
        }
        
        $store = $context->getExtensionAttributes()->getStore();
        $websiteId = (int) $store->getWebsiteId();
        $dataRender = $this->unsubscribeAllStockNotice(
            $websiteId,
            $currentUserId,
            $resultData
        );
        return $this->valueFactory->create(
            function () use ($dataRender) {
                return $dataRender;
            }
        );
    }

    /**
     * @param int $websiteId
     * @param int $customerId
     * @param array $resultData
     * @return array
     */
    public function unsubscribeAllStockNotice(
        int $websiteId,
        int $customerId,
        array $resultData
    ): array {
        $resultData = [];
        try {
            $customer = $this->customerRepository->getById($customerId);
            $website = $this->storeManager->getWebsite($websiteId);
            $this->stockFactory->create()
                ->deleteCustomer(
                    $customer->getId(),
                    $website->getId()
                );
        } catch (Exception $exception) {
            $resultData[] = [
                'message' => $exception->getMessage(),
                'params' => ''
            ];
        }
        return $resultData;
    }
}
