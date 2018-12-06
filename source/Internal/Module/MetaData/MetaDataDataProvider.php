<?php declare(strict_types=1);

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\EshopCommunity\Internal\Module\MetaData;

use OxidEsales\EshopCommunity\Internal\Adapter\ShopAdapterInterface;
use OxidEsales\EshopCommunity\Internal\Module\MetaData\Event\InvalidMetaDataEvent;
use OxidEsales\EshopCommunity\Internal\Module\MetaData\Validator\InvalidMetaDataException;
use Psr\Log\LogLevel;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Class MetaDataDataProvider
 *
 * @internal
 *
 * @package OxidEsales\EshopCommunity\Internal\Module\MetaData
 */
class MetaDataDataProvider
{
    const METADATA_ID = 'id';
    const METADATA_METADATA_VERSION = 'metaDataVersion';
    const METADATA_MODULE_DATA = 'moduleData';
    const METADATA_TITLE = 'title';
    const METADATA_DESCRIPTION = 'description';
    const METADATA_LANG = 'lang';
    const METADATA_THUMBNAIL = 'thumbnail';
    const METADATA_AUTHOR = 'author';
    const METADATA_URL = 'url';
    const METADATA_EMAIL = 'email';
    const METADATA_PATH = 'path';
    const METADATA_VERSION = 'version';
    const METADATA_EXTEND = 'extend';
    const METADATA_BLOCKS = 'blocks';
    const METADATA_CONTROLLERS = 'controllers';
    const METADATA_EVENTS = 'events';
    const METADATA_TEMPLATES = 'templates';
    const METADATA_SETTINGS = 'settings';
    const METADATA_SMARTY_PLUGIN_DIRECTORIES = 'smartyplugindirectories';

    /**
     * @var string
     */
    private $filePath;

    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * @var MetaDataNormalizerInterface
     */
    private $metaDataNormalizer;

    /**
     * @var ShopAdapterInterface
     */
    private $shopAdapter;


    /**
     * MetaDataDataProvider constructor.
     *
     * @param EventDispatcherInterface    $eventDispatcher
     * @param MetaDataNormalizerInterface $metaDataNormalizer
     * @param ShopAdapterInterface        $shopAdapter
     */
    public function __construct(EventDispatcherInterface $eventDispatcher, MetaDataNormalizerInterface $metaDataNormalizer, ShopAdapterInterface $shopAdapter)
    {
        $this->eventDispatcher = $eventDispatcher;
        $this->metaDataNormalizer = $metaDataNormalizer;
        $this->shopAdapter = $shopAdapter;
    }

    /**
     * @param string $filePath
     *
     * @return array
     * @throws InvalidMetaDataException
     */
    public function getData(string $filePath): array
    {
        if (!is_readable($filePath) || is_dir($filePath)) {
            throw new \InvalidArgumentException('File ' . $filePath . ' is not a readable or not even a file');
        }
        $this->filePath = $filePath;
        $normalizedMetaData = $this->getNormalizedMetaDataFileContent();

        return $this->addPathToData($normalizedMetaData);
    }

    /**
     * @return array
     * @throws InvalidMetaDataException
     */
    private function getNormalizedMetaDataFileContent(): array
    {
        /**
         * The following variables will be overwritten when the metadata file is included.
         */
        $sMetadataVersion = null;
        $aModule = null;
        include_once $this->filePath;

        $this->validateMetaDataFileVariables($sMetadataVersion, $aModule);

        $normalizedMetaData = $this->metaDataNormalizer->normalizeData($aModule);
        $normalizedMetaData[static::METADATA_ID] = $this->sanitizeMetaDataId($normalizedMetaData);
        if (isset($normalizedMetaData[static::METADATA_EXTEND])) {
            $normalizedMetaData[static::METADATA_EXTEND] = $this->sanitizeExtendedClasses($normalizedMetaData);
        }

        return [
            static::METADATA_METADATA_VERSION => $sMetadataVersion,
            static::METADATA_MODULE_DATA      => $normalizedMetaData
        ];
    }

    /**
     * @param array $normalizedMetaData
     *
     * @return array
     */
    private function addPathToData(array $normalizedMetaData): array
    {
        /**
         * TODO Define how the path should be and implement this.
         * if meta data file path is /var/www/eshop/source/modules/MyModule/metadata.php,
         * the path should be something like /modules/MyModule/ or modules/MyModule/ or MyModule/
         */
        $normalizedMetaData[static::METADATA_PATH] = $this->getModuleDirectoryName() . DIRECTORY_SEPARATOR;

        return $normalizedMetaData;
    }

    /**
     * @return string
     */
    private function getModuleDirectoryName(): string
    {
        return trim(basename(\dirname($this->filePath)), DIRECTORY_SEPARATOR);
    }

    /**
     * @param array $metaData
     *
     * @return string
     */
    private function sanitizeMetaDataId(array $metaData): string
    {
        $metaDataId = $metaData[static::METADATA_ID] ?? '';
        if ('' === $metaDataId) {
            $level = LogLevel::ERROR;
            $message = 'No metadata key "id" was not found in ' . $this->filePath;

            $event = new InvalidMetaDataEvent($level, $message);
            $this->eventDispatcher->dispatch($event::NAME, $event);

            $metaDataId = trim(basename($this->getModuleDirectoryName()), DIRECTORY_SEPARATOR);
        }

        return $metaDataId;
    }

    /**
     * @param $sMetadataVersion
     * @param $aModule
     *
     * @throws InvalidMetaDataException
     */
    private function validateMetaDataFileVariables($sMetadataVersion, $aModule)
    {
        if ($sMetadataVersion === null || !is_scalar($sMetadataVersion)) {
            throw new InvalidMetaDataException('The variable $sMetadataVersion must be present in ' . $this->filePath . ' and it must be a scalar');
        }
        if ($aModule === null || !is_array($aModule)) {
            throw new InvalidMetaDataException('The variable $aModule must be present in ' . $this->filePath . ' and it must be an array');
        }
    }

    /**
     * @param array $normalizedMetaData
     *
     * @return array
     */
    private function sanitizeExtendedClasses(array $normalizedMetaData): array
    {
        $sanitizedExtendedClasses = [];
        $extendedClasses = $normalizedMetaData[static::METADATA_EXTEND] ?? [];
        foreach ($extendedClasses as $shopClass => $moduleClass) {
            if ($this->isBackwardsCompatibleClass($shopClass)) {
                $sanitizedShopClass = $this->getBackwardsCompatibilityClassMap()[strtolower($shopClass)];
            } else {
                $sanitizedShopClass = $shopClass;
            }
            $sanitizedExtendedClasses[$sanitizedShopClass] = $moduleClass;
        }

        return $sanitizedExtendedClasses;
    }

    /**
     * @param string $className
     *
     * @return bool
     */
    private function isBackwardsCompatibleClass(string $className): bool
    {
        return array_key_exists(strtolower($className), $this->getBackwardsCompatibilityClassMap());
    }

    /**
     * @return array
     */
    private function getBackwardsCompatibilityClassMap(): array
    {
        return $this->shopAdapter->getBackwardsCompatibilityClassMap();
    }
}
