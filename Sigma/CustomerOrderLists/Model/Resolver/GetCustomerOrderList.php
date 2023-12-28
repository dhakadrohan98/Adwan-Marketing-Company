<?php
/**
 * @category  Sigma
 * @package   Sigma_CustomerOrderLists
 * @author    SigmaInfo Team
 * @copyright 2021 Sigma (https://www.sigmainfo.net/)
**/

namespace Sigma\CustomerOrderLists\Model\Resolver;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;

class GetCustomerOrderList implements ResolverInterface
{
    /**
     * @param OrderRepositoryInterface $orderRepository,
     * @param SearchCriteriaBuilder $searchCriteriaBuilder,
     * @param PriceCurrencyInterface $priceCurrency,
     * @param Currency $currencyFormat
     */
    public function __construct(
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder,
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency,
        \Magento\Directory\Model\Currency $currencyFormat
    ) { 
        $this->orderRepository = $orderRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->priceCurrency = $priceCurrency;
        $this->currencyFormat =$currencyFormat;
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
        
        $output = [];

        /**
         *  id of customer data
         */
        $customerId = $this->getId($args);
        /* Filter fors customer order with status complete */
        $searchCriteria = $this->searchCriteriaBuilder->addFilter('customer_id', $customerId, 'eq')->addFilter('status', 'complete')->create();
        /*Gets the list of the orders */
        $orders = $this->orderRepository->getList($searchCriteria);
        foreach($orders as $order) {
            $output[] = [
                'order_id' => $order->getId(),
                'increment_id' => $order->getIncrementId(),
                'order_date' => $order->getCreatedAt(),
                'total_amount'=> $this->getCurrencyFormat($order->getGrandTotal())
            ];
        }
        return ['items' => $output];
    }
    /**
     * Passed Customer Id from graphql query
     *
     * @param array $args
    **/
    private function getId(array $args)
    {
        if (!isset($args['customer_id'])) {
            throw new GraphQlInputException(__('"Customer Id should be specified'));
        }
        return $args['customer_id'];
    }
    /**
     * Get current store currency symbol with price
     * $price price value
     * true includeContainer
     * Precision value 2
     */
    public function getCurrencyFormat($price)
    {
        return $this->getCurrencySymbol() . number_format((float)$price, \Magento\Framework\Pricing\PriceCurrencyInterface::DEFAULT_PRECISION);
    }

    /**
     * Get current store CurrencySymbol
     */
    public function getCurrencySymbol()
    {
        $symbol = $this->priceCurrency->getCurrencySymbol();
        return $symbol;
    }
}
