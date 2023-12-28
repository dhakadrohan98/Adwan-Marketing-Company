<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Sigma\MirasvitRma\Rewrite\Mirasvit\RmaGraphQl\Service;
use Magento\Framework\App\State;
use Mirasvit\Rma\Api\Data\RmaInterface;
use Mirasvit\Rma\Api\Service\Attachment\AttachmentManagementInterface;
use Mirasvit\Rma\Api\Service\Message\MessageManagementInterface;
use Mirasvit\Rma\Api\Service\Message\MessageManagement\SearchInterface as MessageManagementSearchInterface;
use Mirasvit\Rma\Api\Service\Rma\RmaManagementInterface;
use Mirasvit\Rma\Api\Service\Rma\RmaManagement\SearchInterface as RmaManagementSearchInterface;
use Mirasvit\Rma\Helper\Attachment\Url;
use Mirasvit\Rma\Helper\Item\Html as ItemHtmlHelper;
use Mirasvit\Rma\Helper\Order\Html as OrderHtmlHelper;

class RmaService extends \Mirasvit\RmaGraphQl\Service\RmaService
{
    private $attachmentManagement;
    private $itemHtmlHelper;
    private $messageManagement;
    private $messageSearchManagement;
    private $rmaAttachmentUrl;
    private $rmaManagement;
    private $rmaOrderHtml;
    private $rmaSearchManagement;
    private $state;

    public function __construct(
        State $state,
        AttachmentManagementInterface $attachmentManagement,
        MessageManagementInterface $messageManagement,
        MessageManagementSearchInterface $messageSearchManagement,
        RmaManagementInterface $rmaManagement,
        RmaManagementSearchInterface $rmaSearchManagement,
        Url $rmaAttachmentUrl,
        ItemHtmlHelper $itemHtmlHelper,
        OrderHtmlHelper $rmaOrderHtml
    ) {
        $this->attachmentManagement    = $attachmentManagement;
        $this->messageManagement       = $messageManagement;
        $this->messageSearchManagement = $messageSearchManagement;
        $this->rmaSearchManagement     = $rmaSearchManagement;
        $this->rmaAttachmentUrl        = $rmaAttachmentUrl;
        $this->rmaManagement           = $rmaManagement;
        $this->itemHtmlHelper          = $itemHtmlHelper;
        $this->rmaOrderHtml            = $rmaOrderHtml;
        $this->state                   = $state;
    }

    public function getRmaOrdersInfo(RmaInterface $rma): array
    {
        $orders = [];

        $rmaOrders = $this->rmaManagement->getOrders($rma);
        foreach ($rmaOrders as $order) {
            $orderInfo = [
                'order_number' => $this->rmaOrderHtml->getOrderLabel($order),
                'type'         => $order->getData('is_offline') ? 'offline' : 'regular',
            ];
            $items = $this->rmaSearchManagement->getRequestedItems($rma);
            foreach ($items as $item) {
                $item->setStoreId($rma->getStoreId());
                $imageUrl = $this->state->emulateAreaCode('frontend', function () use ($item) {
                    $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
                    /** @var \Mirasvit\Rma\Api\Service\Item\ItemManagement\ProductInterface $itemProductService */
                    $itemProductService = $objectManager->create('Mirasvit\Rma\Api\Service\Item\ItemManagement\ProductInterface');
                    return $itemProductService->getImage($item, 'product_page_image_small')
                        ->resize(150)->getUrl();
                });
                $item->setImageUrl($imageUrl);
                $item->setItemName($this->itemHtmlHelper->getItemLabel($item));
                $item->setItemSku($item->getProductSku());
                $item->setItemId($item->getOrderItemId());
            }
            $orderInfo['items'] = $items;

            $messages = $this->messageSearchManagement->getVisibleInFront($rma);
            foreach ($messages as $message) {
                $message->setType(trim($this->messageManagement->getFrontendType($message), '_'));
                $message->setAuthorName($this->messageManagement->getAuthorName($message));
                $message->setMessage($this->messageManagement->getTextHtml($message));
                $message->setDate($message->getCreatedAt());
                $attachments = $this->attachmentManagement->getAttachmentsByMessage($message);
                foreach ($attachments as $attachment) {
                    $attachment->setUrl($this->rmaAttachmentUrl->getUrl($attachment));
                }
            }

            $orders[] = $orderInfo;
        }

        return $orders;
    }
}

