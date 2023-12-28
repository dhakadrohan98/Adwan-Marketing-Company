<?php
/**
 * Magedelight
 * Copyright (C) 2018 Magedelight <info@magedelight.com>
 *
 * NOTICE OF LICENSE
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see http://opensource.org/licenses/gpl-3.0.html.
 *
 * @category Magedelight
 * @package Magedelight_Payfort
 * @copyright Copyright (c) 2018 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */
namespace Magedelight\Payfort\Controller\Addcard;

use Magento\Framework\App\RequestInterface;

class Edit extends \Magento\Framework\App\Action\Action
{

    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    private $resultPageFactory;

    /**
     * @var \Magento\Customer\Model\Session
     */
    private $_customerSession;

    /**
     * @var \Magento\Vault\Model\PaymentTokenFactory
     */
    private $paymentCardSaveTokenFactory;

    /**
     * Edit constructor.
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Vault\Model\PaymentTokenFactory $paymentCardSaveTokenFactory
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Vault\Model\PaymentTokenFactory $paymentCardSaveTokenFactory,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory
    ) {
        $this->_customerSession = $customerSession;
        $this->paymentCardSaveTokenFactory = $paymentCardSaveTokenFactory;
        $this->resultPageFactory = $resultPageFactory;
        parent::__construct($context);
    }

    protected function _getSession()
    {
        return $this->_customerSession;
    }

    public function dispatch(RequestInterface $request)
    {
        if (!$this->_getSession()->authenticate()) {
            $this->_actionFlag->set('', 'no-dispatch', true);
        }
        return parent::dispatch($request);
    }
    public function execute()
    {
        try {
            $resultPage = $this->resultPageFactory->create();
            $hasKey = $this->getRequest()->getPostValue('public_hash');
            $customerId = $this->_customerSession->getCustomerId();
            if ($customerId) {
                $cardDetails =  $this->paymentCardSaveTokenFactory->create()->getCollection()
                                ->addFieldToFilter('public_hash', ["eq" => $hasKey])
                                ->addFieldToFilter('customer_id', ["eq" => $customerId]);
                if (!$cardDetails->getSize()) {
                    $this->messageManager->addError('Customer Card not found.');
                    return $this->_redirect('vault/cards/listaction/');
                }
                return $resultPage;
            }
            {
                $this->messageManager->addError("Please Try Again.");
                return $this->_redirect('vault/cards/listaction/');
            }
        } catch (\Exception $e) {
            $this->messageManager->addException($e, __($e->getMessage()));
             return $this->_redirect('vault/cards/listaction/');
        }
    }
}
