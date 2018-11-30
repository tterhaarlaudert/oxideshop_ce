<?php
declare(strict_types=1);

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\EshopCommunity\Tests\Integration\Internal\Module\Configuration\Dao;

use OxidEsales\EshopCommunity\Internal\Application\ContainerBuilder;
use OxidEsales\EshopCommunity\Internal\Module\Configuration\Dao\ProjectConfigurationDaoInterface;
use OxidEsales\EshopCommunity\Internal\Module\Configuration\DataObject\Chain;
use OxidEsales\EshopCommunity\Internal\Module\Configuration\DataObject\EnvironmentConfiguration;
use OxidEsales\EshopCommunity\Internal\Module\Configuration\DataObject\ModuleConfiguration;
use OxidEsales\EshopCommunity\Internal\Module\Configuration\DataObject\ModuleSetting;
use OxidEsales\EshopCommunity\Internal\Module\Configuration\DataObject\ProjectConfiguration;
use OxidEsales\TestingLibrary\VfsStreamWrapper;
use PHPUnit\Framework\TestCase;
use OxidEsales\EshopCommunity\Internal\Common\Storage\ArrayStorageInterface;
use OxidEsales\EshopCommunity\Internal\Module\Configuration\Dao\ProjectConfigurationDao;
use OxidEsales\EshopCommunity\Internal\Module\Configuration\DataMapper\ProjectConfigurationDataMapper;
use OxidEsales\EshopCommunity\Internal\Module\Configuration\DataMapper\ProjectConfigurationDataMapperInterface;
use OxidEsales\EshopCommunity\Internal\Module\Configuration\DataMapper\ShopConfigurationDataMapperInterface;
use OxidEsales\EshopCommunity\Internal\Module\Configuration\DataObject\ShopConfiguration;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;

/**
 * @internal
 */
class ProjectConfigurationDaoTest extends TestCase
{
    public function testProjectConfigurationSaving()
    {
        $projectConfigurationDao = $this
            ->getContainer()
            ->get(ProjectConfigurationDaoInterface::class);

        $projectConfiguration = $this->getTestProjectConfiguration();

        $projectConfigurationDao->persistConfiguration($projectConfiguration);

        $this->assertEquals(
            $projectConfiguration,
            $projectConfigurationDao->getConfiguration()
        );
    }

    private function getTestProjectConfiguration(): ProjectConfiguration
    {
        $moduleConfiguration = new ModuleConfiguration();
        $moduleConfiguration
            ->setId('testModuleConfiguration')
            ->setState('active');

        $moduleConfiguration->addSetting(
            new ModuleSetting('path', 'somePath')
        )
            ->addSetting(
                new ModuleSetting('version', 'v2.1')
            )
            ->addSetting(new ModuleSetting(
                'controllers',
                [
                    'originalClassNamespace' => 'moduleClassNamespace',
                    'otherOriginalClassNamespace' => 'moduleClassNamespace',
                ]
            ))
            ->addSetting(new ModuleSetting(
                'templates',
                [
                    'originalTemplate' => 'moduleTemplate',
                    'otherOriginalTemplate' => 'moduleTemplate',
                ]
            ))
            ->addSetting(new ModuleSetting(
                'smartyPluginDirectories',
                [
                    'firstSmartyDirectory',
                    'secondSmartyDirectory',
                ]
            ))
            ->addSetting(new ModuleSetting(
                'blocks',
                [
                    [
                        'block'     => 'testBlock',
                        'position'  => '3',
                        'theme'     => 'flow_theme',
                        'template'  => 'extendedTemplatePath',
                        'file'      => 'filePath',
                    ],
                ]
            ))
            ->addSetting(new ModuleSetting(
                'extend',
                [
                    'originalClassNamespace' => 'moduleClassNamespace',
                    'otherOriginalClassNamespace' => 'moduleClassNamespace',
                ]
            ))
            ->addSetting(new ModuleSetting(
                ModuleSetting::SHOP_MODULE_SETTING,
                [
                    [
                        'group' => 'frontend',
                        'name'  => 'sGridRow',
                        'type'  => 'str',
                        'value' => 'row',
                    ],
                ]
            ))
            /**
            ->setSetting(new ModuleSetting(
            'events',
            [
            'onActivate' => 'ModuleClass::onActivate',
            'onDeactivate' => 'ModuleClass::onDeactivate',
            ]
            ))
             */;

        $classExtensionChain = new Chain();
        $classExtensionChain->setName('classExtensions');
        $classExtensionChain->setChain([
            'shopClassNamespace' => [
                'activeModule2ExtensionClass',
                'activeModuleExtensionClass',
                'notActiveModuleExtensionClass',
            ],
            'anotherShopClassNamespace' => [
                'activeModuleExtensionClass',
                'notActiveModuleExtensionClass',
                'activeModule2ExtensionClass',
            ],
        ]);

        $shopConfiguration = new ShopConfiguration();
        $shopConfiguration->addModuleConfiguration($moduleConfiguration);
        $shopConfiguration->addChain($classExtensionChain);

        $environmentConfiguration = new EnvironmentConfiguration();
        $environmentConfiguration->addShopConfiguration(1, $shopConfiguration);

        $projectConfiguration = new ProjectConfiguration();
        $projectConfiguration->addEnvironmentConfiguration('dev', $environmentConfiguration);

        return $projectConfiguration;
    }

    public function testWithCorrectNode()
    {
        $projectConfigurationData = ['environments' => []];

        $projectConfigurationDataMapper = $this->getProjectConfigurationDataMapper();

        $arrayStorage = $this
            ->getMockBuilder(ArrayStorageInterface::class)
            ->getMock();

        $arrayStorage
            ->method('get')
            ->willReturn($projectConfigurationData);

        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('projectConfiguration');
        $rootNode
            ->children()
            ->arrayNode('environments')
            ->end()
            ->end();

        $node = $treeBuilder->buildTree();

        $projectConfigurationDao = new ProjectConfigurationDao(
            $arrayStorage,
            $projectConfigurationDataMapper,
            $node
        );

        $this->assertEquals(
            $projectConfigurationDataMapper->fromData($projectConfigurationData),
            $projectConfigurationDao->getConfiguration()
        );
    }

    /**
     * @expectedException \Symfony\Component\Config\Definition\Exception\InvalidConfigurationException
     */
    public function testWithIncorrectNode()
    {
        $projectConfigurationData = [
            'environments' => [],
            'incorrectKey' => [],
        ];

        $projectConfigurationDataMapper = $this->getProjectConfigurationDataMapper();

        $arrayStorage = $this
            ->getMockBuilder(ArrayStorageInterface::class)
            ->getMock();

        $arrayStorage
            ->method('get')
            ->willReturn($projectConfigurationData);

        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('projectConfiguration');
        $rootNode
            ->children()
            ->arrayNode('environments')
            ->end()
            ->end();

        $node = $treeBuilder->buildTree();

        $projectConfigurationDao = new ProjectConfigurationDao(
            $arrayStorage,
            $projectConfigurationDataMapper,
            $node
        );

        $this->assertEquals(
            $projectConfigurationDataMapper->fromData($projectConfigurationData),
            $projectConfigurationDao->getConfiguration()
        );
    }

    private function getContainer()
    {
        $containerBuilder = new ContainerBuilder();

        $container = $containerBuilder->getContainer();

        $yamlFileStorageDefinition = $container->getDefinition('oxid_esales.module.configuration.project_configuration_yaml_file_storage');
        $yamlFileStorageDefinition->setArgument('$filePath', $this->getTestConfigurationFilePath());

        $container->setDefinition(
            'oxid_esales.module.configuration.project_configuration_yaml_file_storage',
            $yamlFileStorageDefinition
        );

        $projectConfigurationDaoDefinition = $container->getDefinition(ProjectConfigurationDaoInterface::class);
        $projectConfigurationDaoDefinition->setPublic(true);

        $container->setDefinition(
            ProjectConfigurationDaoInterface::class,
            $projectConfigurationDaoDefinition
        );

        $container->compile();

        return $container;
    }

    /**
     * @return string
     */
    private function getTestConfigurationFilePath(): string
    {
        $vfsStreamWrapper = new VfsStreamWrapper();
        $relativePath = 'test/testProjectConfigurationDao.yaml';
        $path = $vfsStreamWrapper->getRootPath() . $relativePath;

        if (!is_file($path)) {
            $vfsStreamWrapper->createFile($relativePath);
        }

        return $path;
    }

    private function getProjectConfigurationDataMapper(): ProjectConfigurationDataMapperInterface
    {
        $shopConfigurationDataMapper = $this
            ->getMockBuilder(ShopConfigurationDataMapperInterface::class)
            ->getMock();

        $shopConfigurationDataMapper
            ->method('fromData')
            ->willReturn(new ShopConfiguration());

        return new ProjectConfigurationDataMapper($shopConfigurationDataMapper);
    }
}
