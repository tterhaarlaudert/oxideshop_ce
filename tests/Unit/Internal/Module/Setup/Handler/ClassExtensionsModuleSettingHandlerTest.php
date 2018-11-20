<?php declare(strict_types=1);
/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\EshopCommunity\Tests\Unit\Internal\Module\Setup\Handler;

use OxidEsales\EshopCommunity\Internal\Adapter\Configuration\Dao\ShopConfigurationSettingDaoInterface;
use OxidEsales\EshopCommunity\Internal\Adapter\Configuration\DataObject\ShopConfigurationSetting;
use OxidEsales\EshopCommunity\Internal\Module\Configuration\DataObject\ModuleSetting;
use OxidEsales\EshopCommunity\Internal\Module\Setup\Handler\ClassExtensionsModuleSettingHandler;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
class ClassExtensionsModuleSettingHandlerTest extends TestCase
{
    public function testCanHandle()
    {
        $handler = new ClassExtensionsModuleSettingHandler(
            $this->getMockBuilder(ShopConfigurationSettingDaoInterface::class)->getMock()
        );

        $this->assertTrue(
            $handler->canHandle(
                new ModuleSetting(ModuleSetting::CLASS_EXTENSIONS, [])
            )
        );
    }

    public function testCanNotHandle()
    {
        $handler = new ClassExtensionsModuleSettingHandler(
            $this->getMockBuilder(ShopConfigurationSettingDaoInterface::class)->getMock()
        );

        $this->assertFalse(
            $handler->canHandle(
                new ModuleSetting('anotherSetting', [])
            )
        );
    }

    public function testHandling()
    {
        $shopConfigurationSettingBeforeHandling = new ShopConfigurationSetting();
        $shopConfigurationSettingBeforeHandling
            ->setValue([
                'alreadyExistentModuleId' => ['extensionClass'],
            ]);

        $shopConfigurationSettingDao = $this->getMockBuilder(ShopConfigurationSettingDaoInterface::class)->getMock();
        $shopConfigurationSettingDao
            ->method('get')
            ->willReturn($shopConfigurationSettingBeforeHandling);

        $shopConfigurationSettingAfterHandling = new ShopConfigurationSetting();
        $shopConfigurationSettingAfterHandling
            ->setValue([
                'alreadyExistentModuleId' => ['extensionClass'],
                'newModuleId'             => ['moduleExtensionClass', 'anotherModuleExtensionClass'],
            ]);

        $shopConfigurationSettingDao
            ->expects($this->once())
            ->method('save')
            ->with($shopConfigurationSettingAfterHandling);

        $moduleSetting = new ModuleSetting(ModuleSetting::CLASS_EXTENSIONS, [
            'originalClass'         => 'moduleExtensionClass',
            'anotherOriginalClass'  => 'anotherModuleExtensionClass',
        ]);

        $handler = new ClassExtensionsModuleSettingHandler($shopConfigurationSettingDao);
        $handler->handle($moduleSetting, 'newModuleId', 1);
    }

    /**
     * @expectedException \OxidEsales\EshopCommunity\Internal\Module\Setup\Exception\WrongSettingModuleSettingHandlerException
     */
    public function testHandleWrongSetting()
    {
        $shopConfigurationSettingDao = $this
            ->getMockBuilder(ShopConfigurationSettingDaoInterface::class)
            ->getMock();


        $settingHandler = new ClassExtensionsModuleSettingHandler($shopConfigurationSettingDao);
        $moduleSetting = new ModuleSetting('someSetting', []);

        $settingHandler->handle($moduleSetting, 'testModule', 1);
    }
}
