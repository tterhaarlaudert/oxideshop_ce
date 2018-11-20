<?php
declare(strict_types=1);

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\EshopCommunity\Internal\Adapter;

use OxidEsales\Eshop\Core\MailValidator;
use OxidEsales\Eshop\Core\Module\Module;
use OxidEsales\Eshop\Core\Module\ModuleCache;
use OxidEsales\Eshop\Core\NamespaceInformationProvider;
use OxidEsales\Eshop\Core\Registry;
use OxidEsales\Eshop\Core\Routing\ShopControllerMapProvider;
use OxidEsales\EshopCommunity\Internal\Adapter\Exception\ModuleNotLoadableException;
use \Exception;

/**
 * @internal
 */
class ShopAdapter implements ShopAdapterInterface
{
    /**
     * @param string $email
     *
     * @return bool
     */
    public function isValidEmail($email): bool
    {
        $emailValidator = oxNew(MailValidator::class);

        return $emailValidator->isValidEmail($email);
    }

    /**
     * @param string $string
     *
     * @return string
     */
    public function translateString($string): string
    {
        $lang = Registry::getLang();

        return $lang->translateString($string);
    }

    /**
     * @param string $moduleId
     *
     * @throws ModuleNotLoadableException
     */
    public function invalidateModuleCache(string $moduleId)
    {
        $module = oxNew(Module::class);
        if (!$module->load($moduleId)) {
            throw new ModuleNotLoadableException('The following module could not be loaded. ModuleId: ' . $moduleId);
        }

        $moduleCache = oxNew(ModuleCache::class, $module);
        $moduleCache->resetCache();
    }

    /**
     * @param string $moduleId
     *
     * @return string
     *
     * @throws Exception
     */
    public function getModuleFullPath(string $moduleId) : string
    {
        $module = oxNew(Module::class);
        $fullPath = $module->getModuleFullPath($moduleId);

        if ($fullPath === false) {
            throw new Exception('Path to module with id ' . $moduleId . ' not found.');
        }

        return $fullPath;
    }

    /**
     * @return string
     */
    public function generateUniqueId(): string
    {
        return Registry::getUtilsObject()->generateUId();
    }

    /**
     * @return array
     */
    public function getShopControllerClassMap(): array
    {
        $shopControllerMapProvider = oxNew(ShopControllerMapProvider::class);

        return $shopControllerMapProvider->getControllerMap();
    }

    /**
     * @param string $namespace
     * @return bool
     */
    public function isNamespace(string $namespace): bool
    {
        return NamespaceInformationProvider::isNamespacedClass($namespace);
    }

    /**
     * @param string $namespace
     * @return bool
     */
    public function isShopUnifiedNamespace(string $namespace): bool
    {
        return NamespaceInformationProvider::classBelongsToShopUnifiedNamespace($namespace);
    }

    /**
     * @param string $namespace
     * @return bool
     */
    public function isShopEditionNamespace(string $namespace): bool
    {
        return NamespaceInformationProvider::classBelongsToShopEditionNamespace($namespace);
    }
}
