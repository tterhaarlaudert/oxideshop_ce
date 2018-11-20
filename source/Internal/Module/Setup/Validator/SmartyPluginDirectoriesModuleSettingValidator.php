<?php declare(strict_types=1);
/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\EshopCommunity\Internal\Module\Setup\Validator;

use OxidEsales\EshopCommunity\Internal\Module\Configuration\DataObject\ModuleSetting;
use OxidEsales\EshopCommunity\Internal\Adapter\ShopAdapterInterface;
use OxidEsales\EshopCommunity\Internal\Module\Setup\Exception\ModuleSettingNotValidException;
use OxidEsales\EshopCommunity\Internal\Common\Exception\DirectoryNotExistentException;
use OxidEsales\EshopCommunity\Internal\Common\Exception\DirectoryNotReadableException;
use OxidEsales\EshopCommunity\Internal\Module\Setup\Exception\ModuleSetupValidationException;

/**
 * Class SmartyPluginDirectoriesModuleSettingValidator
 *
 * @internal Do not make a module extension for this class.
 * @see      https://oxidforge.org/en/core-oxid-eshop-classes-must-not-be-extended.html
 *
 * @ignore   This class will not be included in documentation.
 *
 */
class SmartyPluginDirectoriesModuleSettingValidator implements ModuleSettingValidatorInterface
{

    /**
     * @var ShopAdapterInterface
     */
    private $shopAdapter;

    /**
     * ControllersModuleSettingValidator constructor.
     * @param ShopAdapterInterface $shopAdapter
     */
    public function __construct(ShopAdapterInterface $shopAdapter)
    {
        $this->shopAdapter = $shopAdapter;
    }

    /**
     * @param ModuleSetting $moduleSetting
     * @param string        $moduleId
     * @param int           $shopId
     *
     * @throws ModuleSettingNotValidException
     * @throws DirectoryNotExistentException
     * @throws DirectoryNotReadableException
     * @throws ModuleSetupValidationException
     */
    public function validate(ModuleSetting $moduleSetting, string $moduleId, int $shopId)
    {
        if (!$this->canValidate($moduleSetting)) {
            throw new ModuleSetupValidationException('Setting ' . $moduleSetting->getName() . ' can not be validated by this class.');
        }

        $directories = $moduleSetting->getValue();
        if (!is_array($directories)) {
            throw new ModuleSettingNotValidException(
                'Module setting ' .
                $moduleSetting->getName() .
                ' must be of type array but ' .
                gettype($directories) .
                ' given'
            );
        }

        $fullPathToModule = $this->shopAdapter->getModuleFullPath($moduleId);

        foreach ($directories as $directory) {
            $fullPathSmartyPluginDirectory = $fullPathToModule . DIRECTORY_SEPARATOR . $directory;
            if (!is_dir($fullPathSmartyPluginDirectory)) {
                throw new DirectoryNotExistentException(
                    'Directory ' . $fullPathSmartyPluginDirectory . ' does not exist.'
                );
            }
            if (!is_readable($fullPathSmartyPluginDirectory)) {
                throw new DirectoryNotReadableException(
                    'Directory ' . $fullPathSmartyPluginDirectory . ' not readable.'
                );
            }
        }
    }

    /**
     * @param ModuleSetting $moduleSetting
     * @return bool
     */
    public function canValidate(ModuleSetting $moduleSetting): bool
    {
        return $moduleSetting->getName() === ModuleSetting::SMARTY_PLUGIN_DIRECTORIES;
    }
}
