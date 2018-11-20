<?php
declare(strict_types=1);

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\EshopCommunity\Internal\Module\Configuration\DataMapper;

use OxidEsales\EshopCommunity\Internal\Module\Configuration\DataObject\Chain;
use OxidEsales\EshopCommunity\Internal\Module\Configuration\DataObject\ShopConfiguration;

/**
 * @internal
 */
class ShopConfigurationDataMapper implements ShopConfigurationDataMapperInterface
{
    /**
     * @var ModuleConfigurationDataMapperInterface
     */
    private $moduleConfigurationDataMapper;

    /**
     * ProjectConfigurationDataMapper constructor.
     * @param ModuleConfigurationDataMapperInterface $moduleConfigurationDataMapper
     */
    public function __construct(ModuleConfigurationDataMapperInterface $moduleConfigurationDataMapper)
    {
        $this->moduleConfigurationDataMapper = $moduleConfigurationDataMapper;
    }

    /**
     * @param ShopConfiguration $configuration
     * @return array
     */
    public function toData(ShopConfiguration $configuration): array
    {
        $data = [];

        $data['modules'] = $this->getModulesConfigurationData($configuration);
        $data['moduleChains'] = $this->getModuleChainData($configuration);

        return $data;
    }

    /**
     * @param array $data
     * @return ShopConfiguration
     */
    public function fromData(array $data): ShopConfiguration
    {
        $shopConfiguration = new ShopConfiguration();

        if (isset($data['modules'])) {
            $this->setModulesConfiguration($shopConfiguration, $data['modules']);
        }

        if (isset($data['moduleChains'])) {
            $this->setModuleChains($shopConfiguration, $data['moduleChains']);
        }

        return $shopConfiguration;
    }

    /**
     * @param ShopConfiguration $shopConfiguration
     * @param array             $modulesData
     */
    private function setModulesConfiguration(ShopConfiguration $shopConfiguration, array $modulesData)
    {
        foreach ($modulesData as $moduleId => $moduleData) {
            $moduleData['id'] = $moduleId;

            $shopConfiguration->setModuleConfiguration(
                $moduleId,
                $this->moduleConfigurationDataMapper->fromData($moduleData)
            );
        }
    }

    /**
     * @param ShopConfiguration $shopConfiguration
     * @return array
     */
    private function getModulesConfigurationData(ShopConfiguration $shopConfiguration): array
    {
        $data = [];

        foreach ($shopConfiguration->getModuleConfigurations() as $moduleId => $moduleConfiguration) {
            $data[$moduleId] = $this->moduleConfigurationDataMapper->toData($moduleConfiguration);
        }

        return $data;
    }

    /**
     * @param ShopConfiguration $shopConfiguration
     * @param array             $chainsData
     */
    private function setModuleChains(ShopConfiguration $shopConfiguration, array $chainsData)
    {
        foreach ($chainsData as $chainName => $chainData) {
            $chain = new Chain();
            $chain
                ->setName($chainName)
                ->setChain($chainData);

            $shopConfiguration->setChain($chainName, $chain);
        }
    }

    /**
     * @param ShopConfiguration $shopConfiguration
     * @return array
     */
    private function getModuleChainData(ShopConfiguration $shopConfiguration): array
    {
        $data = [];

        foreach ($shopConfiguration->getChains() as $chain) {
            $data[$chain->getName()] = $chain->getChain();
        }

        return $data;
    }
}
