<?php
/**
 * Created by PhpStorm.
 * User: kharidas
 * Date: 21.01.20
 * Time: 17:03
 */

namespace Perspective\CustomerAvatar\Model\Image;

use Magento\Customer\Model\Customer;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\ValidatorException;
use Magento\Framework\File\Uploader;
use Perspective\CustomerAvatar\Api\AvatarManagerInterface;
use Perspective\CustomerAvatar\Api\ImageGeneratorInterface;
use Magento\Framework\View\Asset\Repository;
use Magento\Deploy\Package\Package;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Io\File;
use Perspective\CustomerAvatar\Exception\AvatarInvalidName;
use Magento\Customer\Model\Attribute as CustomerAttribute;
use Magento\Framework\Filesystem\Directory\Write;

class Generate implements ImageGeneratorInterface
{
    /**
     * Assets repository
     *
     * @var Repository
     */
    protected $_assetRepository;

    /**
     * @var Filesystem
     */
    protected $_fileSystem;

    /**
     * @var File
     */
    protected $_file;

    /**
     * @var Write
     */
    protected $_directory;

    /**
     * Generate constructor.
     * @param Repository $assetRepository
     * @param Filesystem $filesystem
     * @param File $file
     * @param Write $directory
     * @throws FileSystemException
     */
    public function __construct(
        Repository $assetRepository,
        Filesystem $filesystem,
        File $file
    )
    {
        $this->_directory = $filesystem->getDirectoryWrite(DirectoryList::MEDIA);
        $this->_file = $file;
        $this->_fileSystem = $filesystem;
        $this->_assetRepository = $assetRepository;
    }

    /**
     * Font family file path
     *
     * @var null|string
     */
    protected $_fontPath = null;

    /**
     * Generate png image and get saved location path
     *
     * @param string $text
     * @return string
     * @throws LocalizedException
     */
    public function generate(string $text): string
    {
        $image = $this->createImageResource();

        $this->addBackGround($image)->addText(
            $image,
            $text
        )->setThickness($image);

        return $this->saveAndDestroy($image, $text);
    }

    /**
     * Generate image by customer's data
     *
     * @param Customer $customer
     * @return string
     * @throws AvatarInvalidName
     * @throws LocalizedException
     */
    public function generateForCustomer(Customer $customer) : string
    {
        $text = $this->buildTextFromCustomer($customer);
        $path = $this->generate($text);
        $this->cutEntityType($customer, $path);
        return $path;
    }

    /**
     * Remove last generated
     *
     * @param Customer $customer
     * @param $path
     * @throws FileSystemException
     * @throws ValidatorException
     */
    public function removeGeneratedImage(Customer $customer, $path)
    {
        $this->_directory->delete($customer->getEntityType()->getEntityTypeCode().$path);
    }

    /**
     * Cut entity type from path
     *
     * @param Customer $customer
     * @param string $path
     */
    public function cutEntityType(Customer $customer, string &$path) : void
    {
        $code = $customer->getEntityType()->getEntityTypeCode();
        $result = str_replace($code, '', $path);
        $path = is_string($result) ? $result : '';
    }

    /**
     * Build text for avatar
     *
     * @param Customer $customer
     * @return string
     * @throws AvatarInvalidName
     */
    public function buildTextFromCustomer(Customer $customer) : string
    {
        $dataModel = $customer->getDataModel();
        $firstName = $dataModel->getFirstname();
        $lastName = $dataModel->getLastname();

        if(!$firstName || !$lastName) {
            throw new AvatarInvalidName(__('Invalid '.$firstName ? 'first name' : 'last name'.'!'));
        }

        return mb_strtoupper(mb_substr($firstName, 0, 1).mb_substr($lastName, 0, 1));
    }

    /**
     * Generate image resource
     *
     * @return false|resource
     */
    public function createImageResource()
    {
        return imagecreate(self::IMAGE_SIZE, self::IMAGE_SIZE);
    }

    /**
     * Add background color
     *
     * Hardcoded for purple
     *
     * @param $image
     * @return $this
     */
    public function addBackGround($image)
    {
        imagecolorallocate($image, 112, 27, 181);
        return $this;
    }

    /**
     * Add text to the image
     *
     * @param $image
     * @param string $text
     * @return $this
     * @throws LocalizedException
     */
    public function addText($image, string $text)
    {
        $color = imagecolorallocate($image, 255, 255, 255);
        list($x,$y) = $this->getCenteredValues($text);

        imagettftext(
            $image,
            self::TEXT_SIZE,
            0,
            $x,
            $y,
            $color,
            $this->getFontFamilyFilePath(),
            $text
        );

        return $this;
    }

    /**
     * Set image thickness
     *
     * @param $image
     * @param int $thick
     * @return $this
     */
    public function setThickness($image, $thick = 1)
    {
        imagesetthickness($image, $thick);
        return $this;
    }

    /**
     * Centered x & y for current text
     *
     * @param string $text
     * @return array
     * @throws LocalizedException
     */
    public function getCenteredValues(string $text) : array
    {
        $bbox = imagettfbbox(self::TEXT_SIZE, 0, $this->getFontFamilyFilePath(), $text);

        $xr = abs(max($bbox[2], $bbox[4]));
        $yr = abs(max($bbox[5], $bbox[7]));

        $x = intval((self::IMAGE_SIZE - $xr) / 2);
        $y = intval((self::IMAGE_SIZE + $yr) / 2);

        return [$x, $y];
    }

    /**
     * Get font family ttf file path
     *
     * @return string
     * @throws LocalizedException
     */
    public function getFontFamilyFilePath() : string
    {
        if($this->_fontPath === null) {
            $asset = $this->_assetRepository->createAsset(
                'Perspective_CustomerAvatar::font/'.self::FONT_FAMILY_FILE,
                ['area' => Package::BASE_AREA]
            );
            $this->_fontPath = $asset->getSourceFile();
        }
        return $this->_fontPath;
    }

    /**
     * Save and destroy image
     *
     * @param $image
     * @param string $text
     * @return string
     */
    public function saveAndDestroy($image, string $text) : string
    {
        $path = $this->getSavePath();
        imagepng($image, $path['absolute']);
        imagedestroy($image);

        return $path['relative'];
    }

    /**
     * Get media path to save
     *
     * @return array
     */
    public function getSavePath() : array
    {
        $this->checkDirectory();

        $name = 'image_g_'.uniqid().self::IMAGE_FORMAT;

        $mediaPath = $this->getMediaPath(self::FULL_MEDIA_DIRECTORY.$name);

        $newName = Uploader::getNewFileName($mediaPath);

        return [
            'absolute' => $this->getMediaPath(self::FULL_MEDIA_DIRECTORY.$newName),
            'relative' => $this->getRelativeMediaPath(self::FULL_MEDIA_DIRECTORY.$newName)
        ];
    }

    /**
     * Check and create media directory
     */
    public function checkDirectory() : void
    {
        $media = $this->_fileSystem->getDirectoryRead(DirectoryList::MEDIA)->getAbsolutePath();
        $this->_file->mkdir(
            $media.self::FULL_MEDIA_DIRECTORY
        );
    }

    /** Get media path
     * @param null $param
     * @return string
     */
    public function getMediaPath($param = null)
    {
        return $this->_fileSystem->getDirectoryRead(DirectoryList::MEDIA)->getAbsolutePath($param);
    }

    /**
     * Get relative image path
     *
     * @param null $param
     * @return string
     */
    public function getRelativeMediaPath($param = null)
    {
        return $this->_fileSystem->getDirectoryRead(DirectoryList::MEDIA)->getRelativePath($param);
    }
}
