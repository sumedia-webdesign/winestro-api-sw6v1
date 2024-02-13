<?php

/**
 * @copyright Sven Ullmann <kontakt@sumedia-webdesign.de>
 */

declare(strict_types=1);

namespace Sumedia\Wbo\Service\Wbo\Command\SetArticles;

use DateTime;
use Monolog\Logger;
use Shopware\Core\Content\Media\Aggregate\MediaFolder\MediaFolderEntity;
use Shopware\Core\Content\Media\File\FileFetcher;
use Shopware\Core\Content\Media\File\FileSaver;
use Shopware\Core\Content\Media\File\MediaFile;
use Shopware\Core\Content\Media\MediaEntity;
use Shopware\Core\Content\Media\MediaService;
use Shopware\Core\Content\Media\MediaType\ImageType;
use Shopware\Core\Content\Media\Pathname\UrlGenerator;
use Shopware\Core\Content\Media\Pathname\UrlGeneratorInterface;
use Shopware\Core\Content\Product\Aggregate\ProductMedia\ProductMediaCollection;
use Shopware\Core\Content\Product\Aggregate\ProductMedia\ProductMediaEntity;
use Shopware\Core\Content\Product\ProductEntity;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\Uuid\Uuid;
use Sumedia\Wbo\Config\WboConfig;
use Sumedia\Wbo\Service\Wbo\Response\GetArticle\Article;
use Symfony\Component\HttpFoundation\Request;

class Images
{
    /** @var Logger */
    protected $errorLogger;

    /** @var WboConfig */
    protected $wboConfig;

    /** @var EntityRepository */
    protected $productRepository;

    /** @var EntityRepository */
    protected $mediaRepository;

    /** @var EntityRepository */
    protected $mediaFolderRepository;

    /** @var EntityRepository */
    protected $productMediaRepository;

    /** @var FileFetcher */
    protected $fileFetcher;

    /** @var FileSaver */
    protected $fileSaver;

    /** @var MediaService */
    protected $mediaService;

    /** @var UrlGeneratorInterface */
    protected $urlGenerator;

    /** @var Context */
    protected $context;

    /** @var string */
    protected $tempDirPath;

    public function __construct(
        Logger $errorLogger,
        WboConfig $wboConfig,
        EntityRepository $productRepository,
        $mediaRepository,
        $mediaFolderRepository,
        EntityRepository $productMediaRepository,
        FileFetcher $fileFetcher,
        FileSaver $fileSaver,
        MediaService $mediaService,
        UrlGeneratorInterface $urlGenerator,
        Context $context
    )
    {
        $this->errorLogger = $errorLogger;
        $this->wboConfig = $wboConfig;
        $this->productRepository = $productRepository;
        $this->mediaRepository = $mediaRepository;
        $this->mediaFolderRepository = $mediaFolderRepository;
        $this->productMediaRepository = $productMediaRepository;
        $this->fileFetcher = $fileFetcher;
        $this->fileSaver = $fileSaver;
        $this->mediaService = $mediaService;
        $this->urlGenerator = $urlGenerator;
        $this->context = $context;
    }

    public function execute(Article $article, array &$productData)
    {
        $mediaFolder = $this->getMediaFolderEntity();
        if (null === $mediaFolder) {
            return;
        }

        $product = $this->getProductEntity($productData['id']);
        $productMedia = $product ? $product->getMedia() : null;

        $wboMediaData = $this->getWboMediaData($article, $product, $mediaFolder);
        $customMediaData = $this->getCustomMediaData($productMedia);
        $deleteMediaData = $this->getDeleteMediaData($productMedia, $wboMediaData);

        $this->deleteMedia($deleteMediaData);
        $this->upsertMedia($wboMediaData, $mediaFolder);

        $productMediaData = $this->buildProductMediaData($wboMediaData, $customMediaData);

        $productData['media'] = $productMediaData;
        $productData['coverId'] = $productMediaData[0]['id'] ?? null;
    }

    protected function getWboMediaData(Article $article, ?ProductEntity $product, MediaFolderEntity $mediaFolder): array
    {
        $wboMediaData = [];
        foreach (range(1, 4) as $index) {
            $imageUrl = $article->getArticleImages()->getBigImage($index)
                ?: $article->getArticleImages()->getImage($index);
            if (!$imageUrl || !$this->isImage($imageUrl)) {
                continue;
            }

            $modifiedDate = $this->getFileModifiedDateFromImageUrl($imageUrl);
            $imageFileName = $this->getImageFileNameFromImageUrl($imageUrl);
            $mediaFileName = $this->getMediaFileName($imageFileName);
            $media = $this->getMediaEntityByFileName($mediaFileName, $mediaFolder->getId());
            $productMedia = $this->getProductMedia($mediaFileName, $product);

            $wboMediaData[$index] = [
                'productMediaId' => $productMedia ? $productMedia->getId() : null,
                'mediaId' => $media ? $media->getId() : null,
                'media' => $media,
                'modifiedDate' => $modifiedDate,
                'mediaFileName' => $mediaFileName,
                'imageUrl' => $imageUrl
            ];
        }
        return $wboMediaData;
    }

    protected function getCustomMediaData(?ProductMediaCollection $productMediaCollection): array
    {
        if (null === $productMediaCollection) {
            return [];
        }

        $customMedia = [];
        /** @var ProductMediaEntity $productMedia */
        foreach ($productMediaCollection as $productMedia) {
            if (!$productMedia->getMedia()) {
                continue;
            }
            $fileName = $productMedia->getMedia()->getFileName();
            if (!$this->isWboMediaFileName($fileName)) {
                $customMedia[] = [
                    'productMediaId' => $productMedia->getId(),
                    'mediaId' => $productMedia->getMedia()->getId()
                ];
            }
        }
        return $customMedia;
    }

    protected function getDeleteMediaData(?ProductMediaCollection $productMedia, array $wboMediaData): array
    {
        if (null === $productMedia) {
            return [];
        }

        $deleteMediaData = [];

        $wboMediaFileNames = array_map(function($elem){
            return $elem['mediaFileName'];
        }, $wboMediaData);

        foreach ($productMedia as $productMediaElement) {
            $media = $productMediaElement->getMedia();
            if (!$media) {
                continue;
            }
            $mediaFileName = $media->getFileName();
            if ($this->isWboMediaFileName($mediaFileName)) {
                if (false === array_search($mediaFileName, $wboMediaFileNames)) {
                    $deleteMediaData[] = [
                        'mediaId' => $media->getId(),
                        'mediaVersionId' => $media->getVersionId(),
                        'productMediaId' => $productMediaElement->getId(),
                        'productMediaVersionId' => $productMediaElement->getVersionId()
                    ];
                }
            }
        }

        return $deleteMediaData;
    }

    protected function deleteMedia(array $deleteMediaData): void
    {
        $productMediaIds = array_map(function($elem){
            return [
                'id' => $elem['productMediaId'],
                'versionId' => $elem['productMediaVersionId']
            ];
        }, $deleteMediaData);
        $mediaIds = array_map(function($elem){
            return [
                'id' => $elem['mediaId']
            ];
        }, $deleteMediaData);
        if (count($productMediaIds)) {
            $this->productMediaRepository->delete($productMediaIds, $this->context);
        }
        if (count($mediaIds)) {
            $this->mediaRepository->delete($mediaIds, $this->context);
        }
    }

    protected function upsertMedia(array &$wboMediaData, MediaFolderEntity $mediaFolder): void
    {
        foreach ($wboMediaData as &$wboMediaElement) {
            if (!$this->needUpdate($wboMediaElement['media'], $wboMediaElement['modifiedDate'])) {
                continue;
            }
            $tempFilePath = $this->createTempFileFromUrl($wboMediaElement['imageUrl']);
            $this->resizeImage($tempFilePath);
            $media = $this->saveMedia(
                $tempFilePath,
                $wboMediaElement['mediaId'],
                $mediaFolder
            );
            $wboMediaElement['media'] = $media;
            $wboMediaElement['mediaId'] = $media->getId();
        }
    }

    protected function buildProductMediaData(array $wboMediaData, array $customMediaData): array
    {
        $productMediaData = [];
        $position = 1;
        foreach ($wboMediaData as $wboMediaElement) {
            $productMediaData[] = [
                'id' => $wboMediaElement['productMediaId'] ?: Uuid::randomHex(),
                'mediaId' => $wboMediaElement['mediaId'],
                'position' => $position++
            ];
        }
        foreach ($customMediaData as $customMediaElement) {
            $productMediaData[] = [
                'id' => $customMediaElement['productMediaId'] ?: Uuid::randomHex(),
                'mediaId' => $customMediaElement['mediaId'],
                'position' => $position++
            ];
        }
        return $productMediaData;
    }

    protected function getTempDirPath(): string
    {
        if ($this->tempDirPath == null) {
            $tempDirPath = 'var/tmp/wbo_temp_dir_' . md5(uniqid((string) time()));
            if (!is_dir(dirname($tempDirPath, 2))) {
                mkdir(dirname($tempDirPath, 2));
            }
            if (!is_dir(dirname($tempDirPath))) {
                mkdir(dirname($tempDirPath));
            }
            if (!is_dir($tempDirPath)) {
                mkdir($tempDirPath);
            }
            $this->tempDirPath = $tempDirPath;
        }
        return $this->tempDirPath;
    }

    protected function hasNewSize(MediaEntity $media): bool
    {
        $maxWidth = $this->wboConfig->get(WboConfig::MEDIA_MAX_WIDTH);
        $maxHeight = $this->wboConfig->get(WboConfig::MEDIA_MAX_HEIGHT);
        $metaData = $media->getMetaData();
        $width = $metaData['width'] ?? 0;
        $height = $metaData['height'] ?? 0;
        return $width > $height ? $width != $maxWidth : $height != $maxHeight;
    }

    protected function needUpdate(?MediaEntity $media, string $modifiedDate): bool
    {
        if (!$media) {
            return true;
        }

        $mediaDate = strtotime($media->getUploadedAt()->format('Y-m-d H:i:s'));
        $modifiedDate = strtotime($modifiedDate);
        $mediaHasNewSize = $this->hasNewSize($media);
        if ($modifiedDate < $mediaDate && !$mediaHasNewSize) {
            return false;
        }

        return true;
    }

    protected function getProductEntity(string $id): ?ProductEntity
    {
        $search = $this->productRepository->search(
            (new Criteria([$id]))->addAssociation('media'),
            Context::createDefaultContext()
        );
        if ($search->count()) {
            return $search->get($id);
        }
        return null;
    }

    protected function getProductMedia(string $mediaFileName, ?ProductEntity $product): ?ProductMediaEntity
    {
        if (!$product) {
            return null;
        }

        /** @var ProductMediaEntity $productMedia */
        foreach ($product->getMedia()->getElements() as $productMedia)
        {
            if ($productMedia->getMedia() && $productMedia->getMedia()->getFileName() == $mediaFileName) {
                return $productMedia;
            }
        }
        return null;
    }

    protected function saveMedia(
        string $tempFilePath,
        $mediaId,
        MediaFolderEntity $mediaFolder
    ): MediaEntity {
        $mediaFileName = $this->getMediaFileName(basename($tempFilePath));
        $mediaFile = new MediaFile(
            $tempFilePath,
            $this->getMimeType(basename($tempFilePath)),
            $this->getExtension(basename($tempFilePath)),
            filesize($tempFilePath),
            md5_file($tempFilePath)
        );
        if (!$mediaId) {
            $mediaId = $this->createMediaInFolder($mediaFolder);
        }
        $mediaId = $this->mediaService->saveMediaFile(
            $mediaFile,
            $mediaFileName,
            $this->context,
            $mediaFolder->getId(),
            $mediaId
        );

        return $this->mediaRepository->search(new Criteria([$mediaId]), $this->context)->first();
    }

    protected function createMediaInFolder(MediaFolderEntity $mediaFolder): string
    {
        $mediaId = Uuid::randomHex();
        $this->mediaRepository->create(
            [
                [
                    'id' => $mediaId,
                    'private' => false,
                    'mediaFolderId' => $mediaFolder->getId()
                ]
            ],
            $this->context
        );

        return $mediaId;
    }

    protected function getMediaFolderEntity() : ?MediaFolderEntity
    {
        $mediaFolderId = $this->wboConfig->get(WboConfig::MEDIA_DIRECTORY);
        $folderCollection = $this->mediaFolderRepository->search(
            new Criteria([$mediaFolderId]),
            $this->context
        );

        if (!$folderCollection->count()) {
            $this->errorLogger->error("Could not fetch media directory");
            return null;
        }

        return $folderCollection->first();
    }

    protected function getImageFileNameFromImageUrl(string $imageUrl): string
    {
        $prefix     = 'wbo_';
        if (basename(basename($imageUrl)) == 'big') {
            $prefix .= 'big_';
        }
        return  $prefix . basename($imageUrl);
    }

    protected function isImage(string $image): bool
    {
        $extension = substr($image,strrpos($image,'.')+1);
        return in_array(strtolower($extension),['jpg','gif','png','tiff']);
    }

    protected function getMediaEntityByFileName(string $mediaFileName, string $mediaFolderId) : ?MediaEntity
    {
        $searchCriteria = new Criteria();
        $searchCriteria->addAssociation('mediaFolder');
        $searchCriteria->addFilter(new EqualsFilter('fileName', $mediaFileName));
        $searchCriteria->addFilter(new EqualsFilter('mediaFolderId', $mediaFolderId));
        $searchMedia = $this->mediaRepository->search($searchCriteria, $this->context);
        if (!$searchMedia->count()) {
            return null;
        }

        return $searchMedia->first();
    }

    protected function getFileModifiedDateFromImageUrl(string $imageUrl): string
    {
        $context = stream_context_create([
            'http' => [
                'method' => 'HEAD',
                'follow_location' => true,
                'ignore_errors' => true
            ]
        ]);
        $headers = get_headers($imageUrl, PHP_MAJOR_VERSION == 8 ? true : 1, $context);

        $fmt = 'D, d M Y H:i:s O+';
        $datetime = DateTime::createFromFormat($fmt, $headers['Last-Modified']);
        return date('Y-m-d H:i:s', $datetime->getTimestamp());
    }

    protected function createTempFileFromUrl(string $imageUrl): ?string
    {
        $ext        = pathinfo($imageUrl, PATHINFO_EXTENSION);
        $request    = new Request(['extension' => $ext], ['url' => $imageUrl]);
        $tempFile   = $this->getTempDirPath() . DIRECTORY_SEPARATOR . $this->getImageFileNameFromImageUrl($imageUrl);

        try {
            $this->fileFetcher->fetchFileFromURL($request, $tempFile);
            return $tempFile;
        } catch(\Exception $e) {
            return null;
        }
    }

    protected function getMetaData(string $filepath): array
    {
        $imagesizeData = getimagesize($filepath);
        return [
            'type'      => $imagesizeData[2],
            'width'     => $imagesizeData[0],
            'height'    => $imagesizeData[1]
        ];
    }

    protected function getMediaType(string $filepath): ImageType
    {
        $metaData   = $this->getMetaData($filepath);
        $mediaType  = new ImageType();

        if (in_array($metaData['type'], [1, 3])) {
            $mediaType->addFlag('transparent');
        }

        return $mediaType;
    }

    protected function getMediaFileName(string $fileName): string
    {
        return substr($fileName, 0, strrpos($fileName, '.'));
    }

    protected function getExtension(string $fileName): string
    {
        return substr($fileName, strrpos($fileName, '.')+1);
    }

    protected function resizeImage(string $imageFilePath): void
    {
        $maxWidth = $this->wboConfig->get(WboConfig::MEDIA_MAX_WIDTH) ?: 980;
        $maxHeight = $this->wboConfig->get(WboConfig::MEDIA_MAX_HEIGHT) ?: 980;

        list($originalWidth, $originalHeight, $imageType) = getimagesize($imageFilePath);

        if ($originalWidth <= $maxWidth && $originalHeight <= $maxHeight) {
            return;
        }

        if ($originalWidth >= $originalHeight) {
            $scaleRatio = $originalWidth / $maxHeight;
        } else {
            $scaleRatio = $originalHeight / $maxWidth;
        }

        $newWidth = (int) round($originalWidth / $scaleRatio);
        $newHeight = (int) round($originalHeight / $scaleRatio);

        $imageCreateMethod = $this->getImageCreateMethodByImageType($imageType);
        if (null === $imageCreateMethod) {
            $this->errorLogger->warning('Could not GD create image');
            return;
        }

        $isTransparent = $this->getImageIsTransparentFromImageType($imageType);
        $originalImage = $imageCreateMethod($imageFilePath);
        $newImage = imagecreatetruecolor($newWidth, $newHeight);
        if ($isTransparent) {
            imagealphablending($newImage, true);
            $transparency = imagecolorallocatealpha($newImage, 0, 0, 0, 127);
            imagefill($newImage, 0, 0, $transparency);
            imagesavealpha($newImage, true);
        }
        imagecopyresized($newImage, $originalImage, 0, 0, 0, 0, $newWidth, $newHeight, $originalWidth, $originalHeight);

        $imageSaveMethod = $this->getImageSaveMethodByImageType($imageType);
        $imageSaveMethod($newImage, $imageFilePath);
    }

    protected function getImageCreateMethodByImageType(int $imageType): ?string
    {
        switch ($imageType) {
            case 1: return 'imagecreatefromgif';
            case 2: return 'imagecreatefromjpeg';
            case 3: return 'imagecreatefrompng';
            case 6: return 'imagecreatefrombmp';
            case 15: return 'imagecreatefromwbmp';
        }
        return null;
    }

    protected function getImageIsTransparentFromImageType(int $imageType): bool
    {
        return in_array($imageType, [1, 3]);
    }

    protected function getImageSaveMethodByImageType(int $imageType): ?string
    {
        switch ($imageType) {
            case 1: return 'imagegif';
            case 2: return 'imagejpeg';
            case 3: return 'imagepng';
            case 6: return 'imagejpeg';
            case 15: return 'imagejpeg';
        }
        return null;
    }

    protected function getMimeType(string $fileName): ?string
    {
        $ext = substr($fileName, strrpos($fileName, '.')+1);
        switch ($ext) {
            case 'jpg':
            case 'jpeg':
                return 'image/jpeg';
            case 'gif':
                return 'image/gif';
            case'png':
                return 'image/png';
        }
        return null;
    }

    protected function isWboMediaFileName(string $mediaFileName): bool
    {
        return substr($mediaFileName, 0, 4) == 'wbo_';
    }

    protected function removeTempDir($dir): void
    {
        clearstatcache();
        if (!is_dir($dir)) {
            return;
        }
        $dh = opendir($dir);
        if (!$dh) {
            return;
        }
        while(false !== $file = readdir($dh)) {
            if ('.' == $file || '..' == $file) {
                continue;
            }
            $filePath = $dir . DIRECTORY_SEPARATOR . $file;
            if (is_file($filePath)) {
                unlink($filePath);
            } elseif (is_dir($filePath)) {
                $this->removeTempDir($filePath);
            }
        }
        closedir($dh);
        rmdir($dir);
    }

    public function __destruct()
    {
        $this->removeTempDir($this->getTempDirPath());
    }

}
