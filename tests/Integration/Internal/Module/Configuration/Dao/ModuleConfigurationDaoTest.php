<?php declare(strict_types=1);
/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\EshopCommunity\Tests\Integration\Internal\Module\Configuration\Dao;

use OxidEsales\EshopCommunity\Internal\Application\ContainerBuilder;
use OxidEsales\EshopCommunity\Internal\Module\Configuration\Dao\ModuleConfigurationDaoInterface;
use OxidEsales\EshopCommunity\Internal\Module\Configuration\Dao\ProjectConfigurationDaoInterface;
use OxidEsales\EshopCommunity\Internal\Module\Configuration\DataObject\EnvironmentConfiguration;
use OxidEsales\EshopCommunity\Internal\Module\Configuration\DataObject\ModuleConfiguration;
use OxidEsales\EshopCommunity\Internal\Module\Configuration\DataObject\ProjectConfiguration;
use OxidEsales\EshopCommunity\Internal\Module\Configuration\DataObject\ShopConfiguration;
use OxidEsales\EshopCommunity\Tests\Integration\Internal\ContainerTrait;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
class ModuleConfigurationDaoTest extends TestCase
{
    use ContainerTrait;

    /**
     * @var \Symfony\Component\DependencyInjection\ContainerBuilder
     */
    private $container;

    protected function setUp()
    {
        $this->container = $this->getContainer();

        $this->prepareProjectConfiguration();

        parent::setUp();
    }

    public function testSaving()
    {
        $moduleConfiguration = new ModuleConfiguration();
        $moduleConfiguration
            ->setId('testId')
            ->setState('deprecated');

        $dao = $this->container->get(ModuleConfigurationDaoInterface::class);
        $dao->save($moduleConfiguration, 1);

        $this->assertEquals(
            $moduleConfiguration,
            $dao->get('testId', 1)
        );
    }

    private function prepareProjectConfiguration()
    {
        $shopConfiguration = new ShopConfiguration();

        $environmentConfiguration = new EnvironmentConfiguration();
        $environmentConfiguration->addShopConfiguration(1, $shopConfiguration);

        $projectConfiguration = new ProjectConfiguration();
        $projectConfiguration->addEnvironmentConfiguration('prod', $environmentConfiguration);

        $dao = $this->container->get(ProjectConfigurationDaoInterface::class);

        $dao->persistConfiguration($projectConfiguration);
    }

    /**
     * We need to replace services in the container with a mock
     *
     * @return \Symfony\Component\DependencyInjection\ContainerBuilder
     */
    private function getContainer()
    {
        $containerBuilder = new ContainerBuilder();
        $container = $containerBuilder->getContainer();

        $projectConfigurationYmlStorageDefinition = $container->getDefinition('oxid_esales.module.configuration.project_configuration_yaml_file_storage');
        $projectConfigurationYmlStorageDefinition->setArgument(
            '$filePath',
            tempnam(sys_get_temp_dir() . '/test_project_configuration', 'test_')
        );
        $container->setDefinition(
            'oxid_esales.module.configuration.project_configuration_yaml_file_storage',
            $projectConfigurationYmlStorageDefinition
        );

        $this->setContainerDefinitionToPublic($container, ProjectConfigurationDaoInterface::class);
        $this->setContainerDefinitionToPublic($container, ModuleConfigurationDaoInterface::class);

        $container->compile();

        return $container;
    }
}
