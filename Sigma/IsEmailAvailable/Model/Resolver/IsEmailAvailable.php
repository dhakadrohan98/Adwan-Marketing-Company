<?php
/**
 * @category  Sigma
 * @package   Sigma_IsEmailAvailable
 * @author    SigmaInfo Team
 * @copyright 2021 Sigma (https://www.sigmainfo.net/)
 */

declare(strict_types=1);

namespace Sigma\IsEmailAvailable\Model\Resolver;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Catalog\Block\Product\Context;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Customer\Model\CustomerFactory;

/**
 * Resolver for Check Email Available
 */
class IsEmailAvailable implements ResolverInterface
{

    /**
     * Customer Interface
     *
     * @var \Magento\Customer\Model\CustomerFactory
     */
    protected $customer;

    /**
     * Store Interface
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @param Context $context
     * @param CustomerFactory $customer
     * @param StoreManagerInterface $storeManager
     * @param data $data
     */
    public function __construct(
        Context $context,
        CustomerFactory $customer,
        StoreManagerInterface $storeManager,
        array $data = []
    ) {
        $this->customer = $customer;
        $this->storeManager = $storeManager;
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

        /**
        * Get Email address from variable
        */
        $emailId = $this->getString($args);

        /**
        * Get Customer Data based on email
        */
        $customerModel = $this->getCustomerByEmail($emailId);

        /**
        * Get Customer Id from Customer Model
        */
        $customerId = $customerModel->getId();

        if ($customerId) {
            $message['message'] = "You already have an account with us. Sign in or continue as guest.";
            $message['status'] = "1";
        } else {
            $message['message'] = "Email address is not available";
            $message['status'] = "0";
        }
        return $message;
    }

    /**
     * Passed Email Id from graphql query
     *
     * @param args $args
     *
     * @throws GraphQlNoSuchEntityException
     */
    private function getString(array $args)
    {
        if (!isset($args['emailId'])) {
            throw new GraphQlInputException(__('"Email Id value should be specified'));
        }
        return $args['emailId'];
    }

    /**
     * Get customer collection
     *
     * @param emailId $emailId
     */
    public function getCustomerByEmail($emailId)
    {
        $websiteId = $this->storeManager->getStore()->getWebsiteId();
        $customer = $this->customer->create()->setWebsiteId($websiteId)->loadByEmail($emailId);

        return $customer;
    }
}
