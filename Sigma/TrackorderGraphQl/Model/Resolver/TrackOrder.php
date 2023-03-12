<?php
/**
 * @category  Sigma
 * @package   Sigma_TrackorderGraphQl
 * @author    SigmaInfo Team
 * @copyright 2021 Sigma (https://www.sigmainfo.net/)
 */

declare(strict_types=1);

namespace Sigma\TrackorderGraphQl\Model\Resolver;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

/**
 * Track Order data reslover
 */
class TrackOrder implements ResolverInterface
{
    /**
     * @var orderRepository
     */
    private $orderRepository;

    /**
     * @param OrderRepositoryInterface $orderRepository
     */
    public function __construct(
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository
    ) {
        $this->orderRepository = $orderRepository;
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
        
        $salesId = $this->getSalesId($args);
        $salesData = $this->getSalesData($salesId, $args['emailid']);

        return $salesData;
    }

    /**
     * Get Sales Id
     *
     * @param array $args
     * @return int
     * @throws GraphQlInputException
     */
    private function getSalesId(array $args): int
    {
        if (!isset($args['id'])) {
            throw new GraphQlInputException(__('"order id should be specified'));
        }

        return (int)$args['id'];
    }

    /**
     * Get Sales Data
     *
     * @param int $orderId
     * @param string $emailid
     * @return array
     * @throws GraphQlNoSuchEntityException
     */
    private function getSalesData(int $orderId, String $emailid): array
    {
        try {
            $order = $this->orderRepository->get($orderId);
            $billigAddress = $order->getBillingAddress()->getData();
            $shippingAddress = $order->getShippingAddress()->getData();
            foreach ($order->getAllVisibleItems() as $_item) {
                $itemsData[] = $_item->getData();
            }
            $orderData = [
                'increment_id' => $order->getIncrementId(),
                'grand_total' => $order->getGrandTotal(),
                'customer_name' => $order->getCustomerFirstname().' '.$order->getCustomerLastname(),
                'customer_email' => $order->getCustomerEmail(),
                'created_at' => $order->getCreatedAt(),
                'is_guest_customer' => !empty($order->getCustomerIsGuest()) ? 1 : 0,
                'shipping_method' => $order->getShippingMethod(),
                'shipping_address' => $shippingAddress,
                'billing_address' => $billigAddress,
                'items' => $itemsData
            ];
        } catch (NoSuchEntityException $e) {
            throw new GraphQlNoSuchEntityException(__($e->getMessage()), $e);
        }
        return $orderData;
    }
}
