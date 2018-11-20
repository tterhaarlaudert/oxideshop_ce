<?php
/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\EshopCommunity\Tests\Unit\Internal\Module\Configuration\Dao;

use OxidEsales\EshopCommunity\Internal\Common\Storage\ArrayStorageInterface;
use OxidEsales\EshopCommunity\Internal\Module\Configuration\Dao\ProjectConfigurationDao;
use OxidEsales\EshopCommunity\Internal\Module\Configuration\DataMapper\ProjectConfigurationDataMapper;
use OxidEsales\EshopCommunity\Internal\Module\Configuration\DataMapper\ProjectConfigurationDataMapperInterface;
use OxidEsales\EshopCommunity\Internal\Module\Configuration\DataMapper\ShopConfigurationDataMapperInterface;
use OxidEsales\EshopCommunity\Internal\Module\Configuration\DataObject\ProjectConfiguration;
use OxidEsales\EshopCommunity\Internal\Module\Configuration\DataObject\ShopConfiguration;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
class ProjectConfigurationDaoTest extends TestCase
{
    public function testProjectConfigurationGetter()
    {
        $projectConfigurationData = [
            'project_name'  => 'Module structure 2018',
            'environments'  => [
                'dev' => [
                    'shops' => [],
                ],
                'prod' => [
                    'shops' => [],
                ],
            ],
        ];

        $shopConfigurationDataMapper = $this
            ->getMockBuilder(ShopConfigurationDataMapperInterface::class)
            ->getMock();

        $shopConfigurationDataMapper
            ->method('fromData')
            ->willReturn(new ShopConfiguration());

        $projectConfigurationDataMapper = new ProjectConfigurationDataMapper($shopConfigurationDataMapper);

        $arrayStorage = $this
            ->getMockBuilder(ArrayStorageInterface::class)
            ->getMock();

        $arrayStorage
            ->method('get')
            ->willReturn($projectConfigurationData);

        $projectConfigurationDao = new ProjectConfigurationDao(
            $arrayStorage,
            $projectConfigurationDataMapper
        );

        $this->assertEquals(
            $projectConfigurationDataMapper->fromData($projectConfigurationData),
            $projectConfigurationDao->getConfiguration()
        );
    }

    public function testProjectConfigurationSaving()
    {
        $projectConfigurationDataMapper = $this
            ->getMockBuilder(ProjectConfigurationDataMapperInterface::class)
            ->getMock();

        $arrayStorage = $this
            ->getMockBuilder(ArrayStorageInterface::class)
            ->getMock();

        $arrayStorage
            ->expects($this->atLeastOnce())
            ->method('save');

        $projectConfigurationDao = new ProjectConfigurationDao(
            $arrayStorage,
            $projectConfigurationDataMapper
        );

        $projectConfigurationDao->persistConfiguration(new ProjectConfiguration());
    }
}
