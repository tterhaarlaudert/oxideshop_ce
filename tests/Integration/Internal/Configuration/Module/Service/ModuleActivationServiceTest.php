<?php
declare(strict_types=1);

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\EshopCommunity\Tests\Integration\Internal\Configuration\Module\Service;

use OxidEsales\Eshop\Core\Module\ModuleList;
use OxidEsales\EshopCommunity\Internal\Application\ContainerBuilder;
use OxidEsales\EshopCommunity\Internal\Common\Configuration\ShopConfigurationSettingDaoInterface;
use OxidEsales\EshopCommunity\Internal\Configuration\Module\Dao\ProjectConfigurationDaoInterface;
use OxidEsales\EshopCommunity\Internal\Configuration\Module\DataObject\EnvironmentConfiguration;
use OxidEsales\EshopCommunity\Internal\Configuration\Module\DataObject\ModuleConfiguration;
use OxidEsales\EshopCommunity\Internal\Configuration\Module\DataObject\ProjectConfiguration;
use OxidEsales\EshopCommunity\Internal\Configuration\Module\DataObject\ShopConfiguration;
use OxidEsales\EshopCommunity\Internal\Configuration\Module\Service\ModuleActivationServiceInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder as SymfonyContainerBuilder;
use OxidEsales\TestingLibrary\UnitTestCase;

/**
 * @internal
 */
class ModuleActivationServiceTest extends UnitTestCase
{
    public function testDataTransferToShopConfigurationSettingsOnActivation()
    {
        $projectConfigurationDao = $this->get(ProjectConfigurationDaoInterface::class);
        $projectConfigurationDao->persistConfiguration($this->getTestProjectConfiguration());

        $moduleActivationService = $this->get(ModuleActivationServiceInterface::class);
        $moduleActivationService->activate('testModuleConfiguration', 1);

        $shopConfigurationSettingDao = $this->get(ShopConfigurationSettingDaoInterface::class);
        $modulePaths = $shopConfigurationSettingDao->get('aModulePaths', 1);

        $this->assertArraySubset(
            [
                'testModuleConfiguration' => 'testModuleConfigurationPath',
            ],
            $modulePaths
        );
    }

    private function get(string $serviceId)
    {
        $containerBuilder = new ContainerBuilder();
        $container = $containerBuilder->getContainer();

        $this->setContainerDefinitionToPublic($container, $serviceId);

        $container->compile();

        return $container->get($serviceId);
    }

    private function setContainerDefinitionToPublic(SymfonyContainerBuilder $container, string $definitionId)
    {
        $definition = $container->getDefinition($definitionId);
        $definition->setPublic(true);

        $container->setDefinition($definitionId, $definition);
    }

    private function getTestProjectConfiguration()
    {
        $moduleConfiguration = new ModuleConfiguration();
        $moduleConfiguration
            ->setId('testModuleConfiguration')
            ->setPath('testModuleConfigurationPath')
            ->setVersion('v2.0')
            ->setState('active');

        $shopConfiguration = new ShopConfiguration();
        $shopConfiguration->setModuleConfiguration('testModuleConfiguration', $moduleConfiguration);

        $environmentConfiguration = new EnvironmentConfiguration();
        $environmentConfiguration->setShopConfiguration(1, $shopConfiguration);

        $projectConfiguration = new ProjectConfiguration();
        $projectConfiguration->setProjectName('testProject');
        $projectConfiguration->setEnvironmentConfiguration('dev', $environmentConfiguration);

        return $projectConfiguration;
    }
}
