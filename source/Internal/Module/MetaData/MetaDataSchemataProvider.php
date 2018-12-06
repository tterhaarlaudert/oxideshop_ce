<?php declare(strict_types=1);

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\EshopCommunity\Internal\Module\MetaData;

use OxidEsales\EshopCommunity\Internal\Module\MetaData\Validator\UnsupportedMetaDataVersionException;

/**
 * Class MetaDataDefinition
 *
 * @internal
 *
 * @package OxidEsales\EshopCommunity\Internal\Module\MetaData
 */
class MetaDataSchemataProvider implements MetaDataSchemataProviderInterface
{
    /**
     * @var array
     */
    private $metaDataDefinitions;

    /**
     * MetaDataDefinition constructor.
     *
     * @param array $metaDataDefinitions
     */
    public function __construct(array $metaDataDefinitions)
    {
        $this->metaDataDefinitions = $metaDataDefinitions;
    }

    /**
     * @return array
     */
    public function getMetaDataSchemata(): array
    {
        return $this->metaDataDefinitions;
    }

    /**
     * @param string $metaDataVersion
     *
     * @throws UnsupportedMetaDataVersionException
     *
     * @return array
     */
    public function getMetaDataSchemaForVersion(string $metaDataVersion): array
    {
        if (false === array_key_exists($metaDataVersion, $this->metaDataDefinitions)) {
            throw new UnsupportedMetaDataVersionException("Metadata version $metaDataVersion is not supported");
        }

        return $this->metaDataDefinitions[$metaDataVersion];
    }

    /**
     * @param string $metaDataVersion
     *
     * @throws UnsupportedMetaDataVersionException
     *
     * @return array
     */
    public function getFlippedMetaDataSchemaForVersion(string $metaDataVersion): array
    {
        if (false === array_key_exists($metaDataVersion, $this->metaDataDefinitions)) {
            throw new UnsupportedMetaDataVersionException("Metadata version $metaDataVersion is not supported");
        }

        return $this->arrayFlipRecursive($this->metaDataDefinitions[$metaDataVersion]);
    }

    /**
     * Recursively exchange keys and values for a given array
     *
     * @param array $metaDataVersion
     *
     * @return array
     */
    private function arrayFlipRecursive(array $metaDataVersion): array
    {
        $transposedArray = [];

        foreach ($metaDataVersion as $key => $item) {
            if (is_numeric($key) && \is_string($item)) {
                $transposedArray[$this->convertKeyToLowerCase($item)] = $key;
            } elseif (\is_string($key) && \is_array($item)) {
                $transposedArray[$this->convertKeyToLowerCase($key)] = $this->arrayFlipRecursive($item);
            }
        }

        return $transposedArray;
    }

    /**
     * @param string $key
     *
     * @return string
     */
    private function convertKeyToLowerCase(string $key): string
    {
        return strtolower($key);
    }
}
