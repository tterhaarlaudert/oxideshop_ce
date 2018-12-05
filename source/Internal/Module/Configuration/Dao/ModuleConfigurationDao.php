<?php declare(strict_types=1);
/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\EshopCommunity\Internal\Module\Configuration\Dao;

use OxidEsales\EshopCommunity\Internal\Module\Configuration\DataObject\ModuleConfiguration;
use OxidEsales\EshopCommunity\Internal\Utility\ContextInterface;

/**
 * @internal
 */
class ModuleConfigurationDao implements ModuleConfigurationDaoInterface
{
    /**
     * @var ProjectConfigurationDaoInterface
     */
    private $projectConfigurationDao;

    /**
     * @var ContextInterface
     */
    private $context;

    /**
     * ModuleConfigurationDao constructor.
     * @param ProjectConfigurationDaoInterface $projectConfigurationDao
     * @param ContextInterface                 $context
     */
    public function __construct(ProjectConfigurationDaoInterface $projectConfigurationDao, ContextInterface $context)
    {
        $this->projectConfigurationDao = $projectConfigurationDao;
        $this->context = $context;
    }


    /**
     * @param string $moduleId
     * @param int    $shopId
     * @return ModuleConfiguration
     */
    public function get(string $moduleId, int $shopId): ModuleConfiguration
    {
        $projectConfiguration = $this->projectConfigurationDao->getConfiguration();

        return $projectConfiguration
            ->getEnvironmentConfiguration($this->context->getEnvironment())
            ->getShopConfiguration($shopId)
            ->getModuleConfiguration($moduleId);
    }

    /**
     * @param ModuleConfiguration $moduleConfiguration
     * @param int                 $shopId
     */
    public function save(ModuleConfiguration $moduleConfiguration, int $shopId)
    {
        $projectConfiguration = $this->projectConfigurationDao->getConfiguration();

        $shopConfiguration = $projectConfiguration
            ->getEnvironmentConfiguration($this->context->getEnvironment())
            ->getShopConfiguration($shopId);

        $shopConfiguration->addModuleConfiguration($moduleConfiguration);

        $this->projectConfigurationDao->persistConfiguration($projectConfiguration);
    }
}
