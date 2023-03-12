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

namespace Mageplaza\QuickOrder\Controller\Index;

use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\Response\Http\FileFactory;
use Magento\Framework\Component\ComponentRegistrar;
use Magento\Framework\Controller\Result\Raw;
use Magento\Framework\Controller\Result\RawFactory;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\ValidatorException;
use Magento\Framework\Filesystem\Directory\ReadFactory;

/**
 * Class Downloadxml
 * @package Mageplaza\QuickOrder\Controller\Index
 */
class Downloadxml extends Action
{
    /**
     * @var RawFactory
     */
    private $resultRawFactory;

    /**
     * @var FileFactory
     */
    private $fileFactory;

    /**
     * @var ComponentRegistrar
     */
    private $componentRegistrar;

    /**
     * @var ReadFactory
     */
    private $readFactory;

    /**
     * Downloadxml constructor.
     *
     * @param Context $context
     * @param FileFactory $fileFactory
     * @param RawFactory $resultRawFactory
     * @param ComponentRegistrar $componentRegistrar
     * @param ReadFactory $readFactory
     */
    public function __construct(
        Context $context,
        FileFactory $fileFactory,
        RawFactory $resultRawFactory,
        ComponentRegistrar $componentRegistrar,
        ReadFactory $readFactory
    ) {
        $this->fileFactory = $fileFactory;
        $this->resultRawFactory = $resultRawFactory;
        $this->componentRegistrar = $componentRegistrar;
        $this->readFactory = $readFactory;

        parent::__construct($context);
    }

    /**
     * @return Raw
     * @throws NoSuchEntityException
     * @throws FileSystemException
     * @throws ValidatorException
     */
    public function execute()
    {
        $fileName = 'mageplaza_qod_sample_xml.xml';
        $moduleDir = $this->componentRegistrar->getPath(ComponentRegistrar::MODULE, 'Mageplaza_QuickOrder');
        $fileAbsolutePath = $moduleDir . '/Files/Sample/' . $fileName;
        $directoryRead = $this->readFactory->create($moduleDir);
        $filePath = $directoryRead->getRelativePath($fileAbsolutePath);

        if (!$directoryRead->isFile($filePath)) {
            throw new NoSuchEntityException(__('There is no file: %file', ['file' => $filePath]));
        }

        $fileSize = isset($directoryRead->stat($filePath)['size']) ? $directoryRead->stat($filePath)['size'] : null;
        $this->fileFactory->create(
            $fileName,
            null,
            DirectoryList::VAR_DIR,
            'application/octet-stream',
            $fileSize
        );

        /** @var Raw $resultRaw */
        $resultRaw = $this->resultRawFactory->create();
        $resultRaw->setContents($directoryRead->readFile($filePath));

        return $resultRaw;
    }
}
