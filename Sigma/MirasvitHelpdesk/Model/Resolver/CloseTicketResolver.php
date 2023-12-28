<?php

namespace Sigma\MirasvitHelpdesk\Model\Resolver;


use Magento\CustomerGraphQl\Model\Customer\GetCustomer;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Customer\Model\CustomerFactory;
use Mirasvit\Helpdesk\Helper\Customer as CustomerHelper;
use Mirasvit\Helpdesk\Model\TicketFactory;
use Mirasvit\Helpdesk\Model\ResourceModel\Ticket\CollectionFactory;
use Mirasvit\HelpdeskGraphQl\Service\BuildMessageOutput;

class CloseTicketResolver implements ResolverInterface
{
    protected $customer;
    protected $ticketFactory;
    protected $ticketCollectionFactory;
    protected $getCustomer;
    protected $buildMessageOutput;
    protected $messageManager;

    /**
     * @param CustomerFactory $customer
     * @param TicketFactory $ticketFactory
     * @param CollectionFactory $ticketCollectionFactory
     */
    public function __construct(
        GetCustomer        $getCustomer,
        TicketFactory      $ticketFactory,
        CollectionFactory  $ticketCollectionFactory,
        BuildMessageOutput $buildMessageOutput,
        CustomerHelper     $customerHelper)
    {
        $this->ticketFactory = $ticketFactory;
        $this->ticketCollectionFactory = $ticketCollectionFactory;
        $this->getCustomer = $getCustomer;
        $this->buildMessageOutput = $buildMessageOutput;
        $this->customerHelper = $customerHelper;
    }

    public function resolve(
        Field $field,
              $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null)
    {
        if (empty($args['ticketId'])) {
            throw new GraphQlInputException(__('Required parameter "ticketId" is missing'));
        }
        $success_message = [];
        if ($context->getExtensionAttributes()->getIsCustomer()) {
            $customer = $this->getCustomer->execute($context);
            $ticket = $this->ticketFactory->create()->load((int)$args['ticketId']);
            if(!$ticket->getTicketId()){
                throw new GraphQlInputException(__('Ticket is not found.'));
            }
            if (!$context->getExtensionAttributes()->getIsCustomer() && $ticket->getCustomerId() != $customer->getId()) {
                throw new GraphQlInputException(__('Unable to close ticket'));
            }
            $ticket->close();
            $success_message['status'] = "SUCCESS";
            $success_message['message'] = "Ticket was successfully closed.";
        } else {
            $success_message['status'] = "FAILED";
            $success_message['message'] = "Customer is not valid.";
        }
        return $success_message;
    }
}

