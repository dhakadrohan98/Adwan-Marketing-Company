<?php
/**
 * @category  Sigma
 * @package   Sigma_CareersGraphQl
 * @author    SigmaInfo Team
 * @copyright 2021 Sigma (https://www.sigmainfo.net/)
 */

declare(strict_types=1);
namespace Sigma\CareersGraphQl\Model\Resolver;


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
class AddCareerRequestOutput implements ResolverInterface
{
    /**
     * @var \Sigma\CareersGraphQl\Model\Resolver\DataProvider\AddCareerRequest
     */
    private $addcareerRequestDataProvider;

    /**
     * @param addcareerRequestDataProvider $addcareerRequestDataProvider
     */
    public function __construct(
        \Sigma\CareersGraphQl\Model\Resolver\DataProvider\AddCareerRequest $addcareerRequestDataProvider,
        Filesystem $fileSystem,
        File $fileDriver
    ) {
        $this->addcareerRequestDataProvider = $addcareerRequestDataProvider;
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
        $name = $args['input']['name'];
        $email = $args['input']['email'];
        $mobile = $args['input']['mobile'];
        $specialization = $args['input']['specialization'];
        $createdAt = $args['input']['created_at'];
        $cv = $args['input']['cv'];
        $fileData = $args['input']['base_64_encoded'];
        $uploadedFileName = $this->uploadFile($cv, $fileData);

        $success_message = $this->addcareerRequestDataProvider->addData(
            $name,
            $email,
            $mobile,
            $specialization,
            $createdAt,
            $cv,
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
        $fileName = $customer_file;
        // if (isset($customer_file)) {

        // } else {
        //     $fileName = rand() . time();
        // }
        if (isset($fileData)) {
            $mediaFullPath = $this->fileSystem->getDirectoryRead(DirectoryList::MEDIA)->getAbsolutePath('cv/');

            // $originalPath = 'cv/';

            // $mediaFullPath = $mediaPath . $originalPath;

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
            $savedFile = fopen($mediaFullPath . $fileName, "wb");
            fwrite($savedFile, $fileContent);
            fclose($savedFile);
            $uploadedFileName = "/" . $fileName ;
        }
        return $uploadedFileName;
    }
}
