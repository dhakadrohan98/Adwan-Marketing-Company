<?php
namespace Sigma\MirasvitHelpdesk\Plugin;

use Mirasvit\Helpdesk\Model\Ticket;
use Mirasvit\HelpdeskGraphQl\Service\BuildTicketOutput;

class AppendOrderDataBuildTicketOutput
{
    /**
     * @param BuildTicketOutput $subject
     * @param $result
     * @param Ticket $ticket
     * @return string
     */
    public function afterConvert(BuildTicketOutput $subject, $result, Ticket $ticket)
    {
        $orderData = [];
        if ($ticket->getOrder()) {
            $orderData = $ticket->getOrder()->getData();
            $orderData['order_number'] = $ticket->getOrder()->getIncrementId();
            $orderData['order_id'] = $ticket->getOrder()->getEntityId();
        }
        $result['order'] = $orderData;
        return $result;
    }
}
