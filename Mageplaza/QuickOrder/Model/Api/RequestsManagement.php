<?php
/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Mageplaza.com license that is
 * available through the world-wide-web at this URL:
 * https://www.mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Mageplaza
 * @package     Mageplaza_QuickOrder
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\QuickOrder\Model\Api;

use Magento\Framework\DataObject;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\HTTP\PhpEnvironment\RemoteAddress;
use Magento\Framework\Json\Helper\Data as JsonHelper;
use Magento\Framework\Session\SessionManagerInterface;
use Magento\Framework\Stdlib\Cookie\CookieMetadataFactory;
use Magento\Framework\Stdlib\Cookie\CookieSizeLimitReachedException;
use Magento\Framework\Stdlib\Cookie\FailureToSendException;
use Magento\Framework\Stdlib\CookieManagerInterface;
use Magento\Setup\Console\InputValidationException;
use Mageplaza\QuickOrder\Api\Data\FileRequestsInterface;
use Mageplaza\QuickOrder\Api\Data\RequestsInterface;
use Mageplaza\QuickOrder\Api\Data\UpdateRequestInterface;
use Mageplaza\QuickOrder\Api\RequestsManagementInterface;
use Mageplaza\QuickOrder\Controller\Items\Preitem;
use Mageplaza\QuickOrder\Helper\AddToCart as HelperAddToCart;
use Mageplaza\QuickOrder\Helper\Data as HelperData;
use Mageplaza\QuickOrder\Helper\Search as HelperSearch;

/**
 * Class RequestsManagement
 * @package Mageplaza\QuickOrder\Model\Api
 */
class RequestsManagement implements RequestsManagementInterface
{
    const COOKIE_DURATION = 86400;

    /**
     * @var HelperData
     */
    protected $helperData;

    /**
     * @var Preitem
     */
    protected $preItem;

    /**
     * @var JsonHelper
     */
    protected $jsonHelper;

    /**
     * @var CookieManagerInterface
     */
    protected $cookieManager;

    /**
     * @var CookieMetadataFactory
     */
    protected $cookieMetadataFactory;

    /**
     * @var RemoteAddress
     */
    protected $remoteAddress;

    /**
     * @var SessionManagerInterface
     */
    protected $sessionManager;

    /**
     * @var HelperAddToCart
     */
    protected $helperAddToCart;

    /**
     * @var HelperSearch
     */
    protected $helperSearch;

    /**
     * RequestsManagement constructor.
     *
     * @param HelperData $helperData
     * @param Preitem $preItem
     * @param JsonHelper $jsonHelper
     * @param CookieManagerInterface $cookieManager
     * @param CookieMetadataFactory $cookieMetadataFactory
     * @param RemoteAddress $remoteAddress
     * @param SessionManagerInterface $sessionManager
     * @param HelperAddToCart $helperAddToCart
     * @param HelperSearch $helperSearch
     */
    public function __construct(
        HelperData $helperData,
        Preitem $preItem,
        JsonHelper $jsonHelper,
        CookieManagerInterface $cookieManager,
        CookieMetadataFactory $cookieMetadataFactory,
        RemoteAddress $remoteAddress,
        SessionManagerInterface $sessionManager,
        HelperAddToCart $helperAddToCart,
        HelperSearch $helperSearch
    ) {
        $this->helperData = $helperData;
        $this->preItem = $preItem;
        $this->jsonHelper = $jsonHelper;
        $this->cookieManager = $cookieManager;
        $this->cookieMetadataFactory = $cookieMetadataFactory;
        $this->remoteAddress = $remoteAddress;
        $this->sessionManager = $sessionManager;
        $this->helperAddToCart = $helperAddToCart;
        $this->helperSearch = $helperSearch;
    }

    /**
     * @inheritdoc
     */
    public function addBySku(RequestsInterface $requests)
    {
        $this->checkEnabled();
        $preItem = $this->preItem->prepareData($requests->getSku());
        $this->setQodItemCookie($this->processSaveData($requests->getSku(), $preItem));

        return $preItem;
    }

    /**
     * @inheritdoc
     */
    public function update(UpdateRequestInterface $update)
    {
        $currentCookie = $this->getQodItemCookie();

        foreach ($update->getItems() as $key => $item) {
            if (!isset($currentCookie[$item->getItemId()])) {
                throw new InputValidationException(__('The Item ID does not exist.'));
            }

            $currentCookie[$item->getItemId()] = $item->getValue();
        }

        if (count($this->preItem->prepareData($currentCookie)) !== count($currentCookie)) {
            throw new InputValidationException(__('The value to update is not valid, please check again.'));
        }

        $this->clear();
        $this->setQodItemCookie($currentCookie);

        return $this->formatData($currentCookie);
    }

    /**
     * @inheritdoc
     */
    public function addByFile(FileRequestsInterface $request)
    {
        $this->checkEnabled();
        $file = explode("\n", base64_decode($request->getFile()));
        array_splice($file, 0, 1);
        $preItem = $this->preItem->prepareData($file);
        $this->setQodItemCookie($this->processSaveData($file, $preItem));

        return $preItem;
    }

    /**
     * @inheritdoc
     */
    public function getList()
    {
        $this->checkEnabled();

        return $this->formatData($this->getQodItemCookie());
    }

    /**
     * @inheritdoc
     */
    public function clear()
    {
        $this->checkEnabled();
        $this->cookieManager->deleteCookie(
            $this->getRemoteAddress(),
            $this->cookieMetadataFactory
                ->createCookieMetadata()
                ->setPath($this->sessionManager->getCookiePath())
                ->setDomain($this->sessionManager->getCookieDomain())
        );

        return true;
    }

    /**
     * @inheritdoc
     *
     * @param string $id
     *
     * @return bool
     * @throws CookieSizeLimitReachedException
     * @throws FailureToSendException
     * @throws InputException
     * @throws LocalizedException
     */
    public function delete($id)
    {
        $this->checkEnabled();
        $items = $this->getQodItemCookie();

        if (!isset($items[$id])) {
            throw new InputException(__('item_id is not exist, please try again.'));
        }

        unset($items[$id]);
        $this->clear();
        $this->setQodItemCookie($items);

        return true;
    }

    /**
     * @inheritdoc
     */
    public function addToCart()
    {
        if (!$this->getList()) {
            throw new InputValidationException(__('Cannot add to cart as quick order list is empty'));
        }

        $addToCart = $this->helperAddToCart->addToCart($this->processAddToCart($this->getList()));
        // $this->clear();

        return $addToCart;
    }

    /**
     * @return array|mixed
     */
    public function getQodItemCookie()
    {
        return HelperData::jsonDecode($this->cookieManager->getCookie($this->getRemoteAddress()));
    }

    /**
     * @param $data
     *
     * @throws InputException
     * @throws CookieSizeLimitReachedException
     * @throws FailureToSendException
     */
    public function setQodItemCookie($data)
    {
        $metadata = $this->cookieMetadataFactory
            ->createPublicCookieMetadata()
            ->setDuration(self::COOKIE_DURATION)
            ->setPath($this->sessionManager->getCookiePath())
            ->setDomain($this->sessionManager->getCookieDomain());

        $this->cookieManager->setPublicCookie(
            $this->getRemoteAddress(),
            $this->jsonHelper->jsonEncode($data),
            $metadata
        );
    }

    /**
     * @return bool|string
     */
    public function getRemoteAddress()
    {
        return $this->remoteAddress->getRemoteAddress() !== '127.0.0.1' ?: 'localhost';
    }

    /**
     * @param array $data
     * @param array $preItem
     *
     * @return array
     */
    public function processSaveData($data, $preItem)
    {
        $newData = [];

        foreach ($data as $key => $item) {
            if (!$item) {
                continue;
            }

            $newData[$preItem[$key]['item_id']] = $item;
        }

        return $newData;
    }

    /**
     * @param array $items
     *
     * @return array
     */
    public function formatData($items)
    {
        $preItem = $this->preItem->prepareData($items);
        $count = 0;

        foreach ($items as $key => $item) {
            $preItem[$count]['item_id'] = $key;
            $count++;
        }

        return $preItem;
    }

    /**
     * @throws LocalizedException
     */
    public function checkEnabled()
    {
        if (!$this->helperData->isEnabled()) {
            throw new LocalizedException(__('Module is disabled.'));
        }
    }

    /**
     * @param array $items
     *
     * @return array
     */
    public function processAddToCart($items)
    {
        $data = [];

        foreach ($items as $key => $item) {
            $data[$key]['type_id'] = $item['type_id'];
            $data[$key]['product_id'] = $item['product_id'];
            $data[$key]['qty'] = $item['qty'];

            switch ($item['type_id']) {
                case 'configurable':
                    $data[$key]['optionIds'] = $item['optionIds'];
                    break;
                case 'grouped':
                    foreach ($item['childProduct'] as $keyChild => $child) {
                        $data[$key]['childProduct'][$keyChild]['product_id'] = $child['product_id'];
                        $data[$key]['childProduct'][$keyChild]['qty'] = $child['qty'];
                        $data[$key]['childProduct'][$keyChild]['name'] = $child['name'];
                    }
                    break;
                case 'bundle':
                    foreach ($item['bundleOption'] as $keyOption => $option) {
                        $data[$key]['bundle_option'][$keyOption]['option_id'] = $option['option_id'];
                        $data[$key]['bundle_option'][$keyOption]['type'] = $option['type'];
                        $data[$key]['bundle_option'][$keyOption]['required'] = $option['required'];

                        $data[$key]['select_product'][$keyOption]['option_id'] = $item['bundleProduct'][$keyOption]['option_id'];
                        $data[$key]['select_product'][$keyOption]['selection_id'] = $item['bundleProduct'][$keyOption]['selection_id'];
                        $data[$key]['select_product'][$keyOption]['selection_can_change_qty'] = $item['bundleProduct'][$keyOption]['selection_can_change_qty'];
                        $data[$key]['select_product'][$keyOption]['selection_qty'] = $item['bundleProduct'][$keyOption]['selection_qty'];
                    }
                    break;
            }
        }

        return $data;
    }

    /**
     * @inheritdoc
     */
    public function getConfigs()
    {
        $data = $this->helperData;
        $search = $this->helperSearch;

        return new DataObject([
            'general' => new DataObject([
                'enabled' => $data->isEnabled(),
                'route_name' => $data->getUrlSuffix(),
                'page_title' => $data->getPageTitle(),
                'allow_customer_group' => $data->getCustomerGroupAllowAccess(),
                'show_quickorder_button' => $data->getShowLinkPosition(),
                'quickorder_label' => $data->getQuickOrderLabel()
            ]),
            'search' => new DataObject([
                'minimum_character' => $search->getMinCharacterToQuery(),
                'limit_search_results' => $search->getMaxResultAllowShow(),
                'display_product_image' => $search->getAllowDisplayImageConfig(),
            ]),
            'design' => new DataObject([
                'heading_background_color' => $data->getHeadingBackgroundColor(),
                'heading_text_color' => $data->getHeadingTextColor(),
                'heading_background_button' => $data->getHeadingBackgroundButton()
            ])
        ]);
    }
}
