<?php
namespace Sigma\MirasvitRma\Rewrite\Mirasvit\RmaGraphQl\Model\Resolver;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Sales\Api\Data\OrderInterface;

class OrderItems implements ResolverInterface
{
     /**
     * @var OrderItemRepositoryInterface
     */
    protected $orderItemRepository;
    private $orderResource;
    private $orderFactory;
    public function __construct(
        \Magento\Sales\Api\Data\OrderInterface $order,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        \Magento\Sales\Model\Spi\OrderResourceInterface $orderResource,
        \Magento\Sales\Api\Data\OrderInterfaceFactory $orderFactory
    ) {

        $this->order = $order;
        $this->orderRepository = $orderRepository;

        $this->orderResource = $orderResource;
        $this->orderFactory = $orderFactory;
    }

    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        $incrementId = $this->getOrderId($args);
        $output = [];
        $order = $this->orderFactory->create();
        $this->orderResource->load($order, $incrementId, OrderInterface::INCREMENT_ID);
        $orderInfo = $this->orderRepository->get($order->getId());
        foreach ($orderInfo->getAllVisibleItems() as $item) {
            $output[] = [
                'id'=>$item->getOrderId(),
                'order_item_id'=>$item->getItemId(),
                'sku' => $item->getSku(),
                ];
        }
        return ['items' => $output];
    }
    /**
     * Passed Order Id from Graphql query
     *
     * @param array $args
    **/
    public function getOrderId(array $args)
    {
        if (!isset($args['order'])) {
            throw new GraphQlInputException(__('"Order Id should be specified'));
        }
        return $args['order'];
    }
    public function getOrderItem($itemId)
    {
        return $this->orderItemRepository->get($itemId);
    }
}
