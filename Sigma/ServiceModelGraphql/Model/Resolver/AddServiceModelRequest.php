<?php
/**
 * @category  Sigma
 * @package   Sigma_ServiceModelGraphql
 * @author    SigmaInfo Team
 * @copyright 2021 Sigma (https://www.sigmainfo.net/)
 */

declare(strict_types=1);
namespace Sigma\ServiceModelGraphql\Model\Resolver;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Driver\File;
/**
 * Resolver forAdd Service Model Request
 */
class AddServiceModelRequest implements ResolverInterface
{
    /**
     * @var \Sigma\ServiceModelGraphql\Model\Resolver\DataProvider\AddServiceModel
     */
    private $serviceRequestDataProvider;

    /**
     * @param recentlyViewedDataProvider $recentlyViewedDataProvider
     */
    public function __construct(
        \Sigma\ServiceModelGraphql\Model\Resolver\DataProvider\AddServiceModel $serviceRequestDataProvider,
        Filesystem $fileSystem,
        File $fileDriver
    ) {
        $this->serviceRequestDataProvider = $serviceRequestDataProvider;
        $this->fileSystem = $fileSystem;
        $this->fileDriver = $fileDriver;
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
        $customerId = $context->getUserId();
        $createdAt = $args['input']['created_at'];
        $customer_file = $args['input']['customer_file'];
        $fileData = $args['input']['base_64_encoded'];


        $uploadedFileName = $this->uploadFile($customer_file, $fileData);

        $success_message = $this->serviceRequestDataProvider->addData(
            $customerId,
            $createdAt,
            $customer_file,
            $uploadedFileName
        );
        return $success_message;
    }
    /**
     * Convert the file and save it
     *
     * @param [String] $customer_file
     * @param [String] $fileData
     * @return void
     */
    public function uploadFile($customer_file, $fileData)
    {
        if (isset($customer_file)) {
            $fileName = $customer_file;
        } else {
            $fileName = rand() . time();
        }
        if (isset($fileData)) {

            $mediaFullPath = $this->fileSystem->getDirectoryRead(DirectoryList::MEDIA)->getAbsolutePath('allrequests/');

           // $originalPath = 'allrequests/';

          //  $mediaFullPath = $mediaPath . $originalPath;


            if (!file_exists($mediaFullPath)) {
                mkdir($mediaFullPath, 0775, true);
            }

            /* Check File is exist or not */

            $fullFilepath = $mediaFullPath . $fileName;


            if (file_exists($fullFilepath)) {
                $fileName = rand() . time() . $fileName;
            }

            $base64FileArray = explode( ',', $fileData );

            $fileContent = base64_decode($base64FileArray[1]);

            $savedFile = fopen($fullFilepath, "wb");
            fwrite($savedFile, $fileContent);
            fclose($savedFile);
            $uploadedFileName = "/" . $fileName ;
            //
        }
        return $uploadedFileName;
    }
}
