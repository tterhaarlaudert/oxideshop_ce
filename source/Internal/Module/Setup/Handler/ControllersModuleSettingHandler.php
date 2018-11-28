<?php declare(strict_types=1);
/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\EshopCommunity\Internal\Module\Setup\Handler;

use OxidEsales\EshopCommunity\Internal\Adapter\Configuration\Dao\ShopConfigurationSettingDaoInterface;
use OxidEsales\EshopCommunity\Internal\Adapter\Configuration\DataObject\ShopConfigurationSetting;
use OxidEsales\EshopCommunity\Internal\Adapter\Configuration\DataObject\ShopSettingType;
use OxidEsales\EshopCommunity\Internal\Module\Configuration\DataObject\ModuleSetting;
use OxidEsales\EshopCommunity\Internal\Module\Setup\Exception\WrongModuleSettingException;
use OxidEsales\EshopCommunity\Internal\Common\Exception\EntryDoesNotExistDaoException;

/**
 * @internal
 */
class ControllersModuleSettingHandler implements ModuleSettingHandlerInterface
{
    /**
     * @var ShopConfigurationSettingDaoInterface
     */
    private $shopConfigurationSettingDao;

    /**
     * ShopConfigurationModuleSettingHandler constructor
     *
     * @param ShopConfigurationSettingDaoInterface $shopConfigurationSettingDao
     */
    public function __construct(
        ShopConfigurationSettingDaoInterface    $shopConfigurationSettingDao
    ) {
        $this->shopConfigurationSettingDao = $shopConfigurationSettingDao;
    }

    /**
     * @param ModuleSetting $moduleSetting
     * @param string        $moduleId
     * @param int           $shopId
     *
     * @throws WrongModuleSettingException
     */
    public function handleOnModuleActivation(ModuleSetting $moduleSetting, string $moduleId, int $shopId)
    {
        if (!$this->canHandle($moduleSetting)) {
            throw new WrongModuleSettingException($moduleSetting, self::class);
        }

        $shopControllers = $this->getShopControllers($shopId);

        $shopSettingValue = array_merge(
            $shopControllers->getValue(),
            [
                strtolower($moduleId) => $this->controllerKeysToLowercase($moduleSetting->getValue()),
            ]
        );

        $shopControllers->setValue($shopSettingValue);

        $this->shopConfigurationSettingDao->save($shopControllers);
    }

    /**
     * @param ModuleSetting $moduleSetting
     * @param string        $moduleId
     * @param int           $shopId
     *
     * @throws WrongModuleSettingException
     */
    public function handleOnModuleDeactivation(ModuleSetting $moduleSetting, string $moduleId, int $shopId)
    {
        if (!$this->canHandle($moduleSetting)) {
            throw new WrongModuleSettingException($moduleSetting, self::class);
        }

        $shopControllers = $this->getShopControllers($shopId);

        $shopSettingValue = $shopControllers->getValue();
        unset($shopSettingValue[$moduleId]);

        $shopControllers->setValue($shopSettingValue);

        $this->shopConfigurationSettingDao->save($shopControllers);
    }

    /**
     * @param ModuleSetting $moduleSetting
     * @return bool
     */
    public function canHandle(ModuleSetting $moduleSetting): bool
    {
        return $moduleSetting->getName() === ModuleSetting::CONTROLLERS;
    }

    /**
     * @param int $shopId
     * @return ShopConfigurationSetting
     */
    private function getShopControllers(int $shopId): ShopConfigurationSetting
    {
        try {
            $shopConfigurationSetting = $this->shopConfigurationSettingDao->get(
                ShopConfigurationSetting::MODULE_CONTROLLERS,
                $shopId
            );
        } catch (EntryDoesNotExistDaoException $exception) {
            $shopConfigurationSetting = new ShopConfigurationSetting();
            $shopConfigurationSetting
                ->setShopId($shopId)
                ->setName(ShopConfigurationSetting::MODULE_CONTROLLERS)
                ->setType(ShopSettingType::ARRAY)
                ->setValue([]);
        }

        return $shopConfigurationSetting;
    }

    /**
     * Change the controller keys to lower case.
     *
     * @param array $controllers The controllers array of one module.
     *
     * @return array The given controllers array with the controller keys in lower case.
     */
    private function controllerKeysToLowercase(array $controllers) : array
    {
        $result = [];

        foreach ($controllers as $controllerKey => $controllerClass) {
            $result[strtolower($controllerKey)] = $controllerClass;
        }

        return $result;
    }
}
