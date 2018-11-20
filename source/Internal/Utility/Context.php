<?php declare(strict_types=1);
/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\EshopCommunity\Internal\Utility;

use OxidEsales\Eshop\Core\Config;
use OxidEsales\Facts\Facts;

/**
 * @internal
 */
class Context implements ContextInterface
{
    /**
     * @var Config
     */
    private $config;

    /**
     * @var
     */
    private $facts;

    /**
     * Context constructor.
     * @param Config $config
     */
    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    /**
     * @return string
     */
    public function getEnvironment(): string
    {
        return 'prod';
    }

    /**
     * @return string
     */
    public function getLogLevel()
    {
        return $this->getConfigParameter('sLogLevel');
    }

    /**
     * @return string
     */
    public function getLogFilePath(): string
    {
        return $this->config->getLogsDir() . 'oxideshop.log';
    }

    /**
     * @return array
     */
    public function getRequiredContactFormFields(): array
    {
        $contactFormRequiredFields = $this->getConfigParameter('contactFormRequiredFields');

        return $contactFormRequiredFields === null ? [] : $contactFormRequiredFields;
    }

    /**
     * @return int
     */
    public function getCurrentShopId(): int
    {
        return $this->config->getShopId();
    }

    /**
     * @return string
     */
    public function getShopDir(): string
    {
        return $this->getFacts()->getSourcePath();
    }

    /**
     * @return string
     */
    public function getContainerCacheFile()
    {
        return $this->getConfigParameter('sCompileDir') . DIRECTORY_SEPARATOR . 'containercache.php';
    }

    /**
     * @return string
     */
    public function getConfigurationEncryptionKey(): string
    {
        return $this->getConfigParameter('sConfigKey');
    }

    /**
     * @param string $name
     * @return mixed
     */
    private function getConfigParameter($name)
    {
        return $this->config->getConfigParam($name);
    }

    /**
     * @return Facts
     */
    private function getFacts()
    {
        if ($this->facts == null) {
            $this->facts = new Facts();
        }
        return $this->facts;
    }
}
