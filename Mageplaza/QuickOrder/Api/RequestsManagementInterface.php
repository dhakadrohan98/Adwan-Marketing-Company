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

namespace Mageplaza\QuickOrder\Api;

/**
 * Interface RequestsManagementInterface
 * @package Mageplaza\QuickOrder\Api
 */
interface RequestsManagementInterface
{
    /**
     * @return string[]
     */
    public function getList();

    /**
     * @param \Mageplaza\QuickOrder\Api\Data\RequestsInterface $request
     *
     * @return \Mageplaza\QuickOrder\Api\Data\RequestsInterface
     */
    public function addBySku(\Mageplaza\QuickOrder\Api\Data\RequestsInterface $request);

    /**
     * @param \Mageplaza\QuickOrder\Api\Data\FileRequestsInterface $request
     *
     * @return \Mageplaza\QuickOrder\Api\Data\FileRequestsInterface
     */
    public function addByFile(\Mageplaza\QuickOrder\Api\Data\FileRequestsInterface $request);

    /**
     * @return bool
     */
    public function clear();

    /**
     * @param string $id
     *
     * @return bool
     */
    public function delete($id);

    /**
     * @return string[]
     */
    public function addToCart();

    /**
     * @param \Mageplaza\QuickOrder\Api\Data\UpdateRequestInterface $update
     *
     * @return \Mageplaza\QuickOrder\Api\Data\UpdateRequestInterface
     */
    public function update(\Mageplaza\QuickOrder\Api\Data\UpdateRequestInterface $update);

    /**
     * @return \Mageplaza\QuickOrder\Api\Data\ConfigInterface
     */
    public function getConfigs();
}
