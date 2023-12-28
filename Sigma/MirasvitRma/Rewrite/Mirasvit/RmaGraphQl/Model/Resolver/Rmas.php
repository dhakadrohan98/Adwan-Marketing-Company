<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Sigma\MirasvitRma\Rewrite\Mirasvit\RmaGraphQl\Model\Resolver;

use Magento\CustomerGraphQl\Model\Customer\GetCustomer;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlAuthorizationException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\GraphQl\Model\Query\ContextInterface;
use Mirasvit\Rma\Model\ResourceModel\Rma\CollectionFactory;
use Mirasvit\Rma\Helper\Controller\Rma\CustomerStrategy;
use Mirasvit\RmaGraphQl\Service\RmaService;
use Mirasvit\Rma\Api\Service\Message\MessageManagement\SearchInterface;

class Rmas extends \Mirasvit\RmaGraphQl\Model\Resolver\Rmas
{

    private $customerStrategy;

    private $collectionFactory;

    private $getCustomer;

    private $rmaService;

    private $rmaSearchInterface;

    /**
     * @param CollectionFactory $collectionFactory
     */
    public function __construct(
        GetCustomer $getCustomer,
        CustomerStrategy $customerStrategy,
        CollectionFactory $collectionFactory,
        RmaService $rmaService,
        SearchInterface $rmaSearchInterface
    ) {
        $this->customerStrategy   = $customerStrategy;
        $this->collectionFactory  = $collectionFactory;
        $this->getCustomer        = $getCustomer;
        $this->rmaService         = $rmaService;
        $this->rmaSearchInterface = $rmaSearchInterface;
    }

    /**
     * @inheritdoc
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        /** @var ContextInterface $context */
        if (false === $context->getExtensionAttributes()->getIsCustomer()) {
            throw new GraphQlAuthorizationException(__('The current customer isn\'t authorized.'));
        }

        $items = [];

        $customer = $this->getCustomer->execute($context);
        $strategy = $this->customerStrategy->setCustomer($customer);

        $rmas = $strategy->getRmaList();
        /** @var \Mirasvit\Rma\Model\Rma $rma */
        foreach ($rmas as $rma) {
            $rmaMessages = $this->rmaSearchInterface->getVisibleInFront($rma);

            $items[] = [
                'id'             => $rma->getGuestId(),
                'increment_id'   => $rma->getIncrementId(),
                'order_id'       => $rma->getId(),
                'created_at'     => $rma->getCreatedAt(),
                'status'         => $rma->getStatusId(),
                'return_address' => $rma->getReturnAddress(),
                'orders'         => $this->rmaService->getRmaOrdersInfo($rma),
                'messages'       => $rmaMessages
            ];
        }

        return ['items' => $items];
    }
}