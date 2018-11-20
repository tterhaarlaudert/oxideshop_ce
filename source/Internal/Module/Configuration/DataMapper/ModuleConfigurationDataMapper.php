<?php
declare(strict_types=1);

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\EshopCommunity\Internal\Module\Configuration\DataMapper;

use OxidEsales\EshopCommunity\Internal\Module\Configuration\Validator\SettingValidatorInterface;
use OxidEsales\EshopCommunity\Internal\Module\Configuration\DataObject\ModuleConfiguration;
use OxidEsales\EshopCommunity\Internal\Module\Configuration\DataObject\ModuleSetting;

/**
 * @internal
 */
class ModuleConfigurationDataMapper implements ModuleConfigurationDataMapperInterface
{
    /**
     * @var SettingValidatorInterface
     */
    private $settingValidator;

    /**
     * ModuleConfigurationDataMapper constructor.
     * @param SettingValidatorInterface $settingValidator
     */
    public function __construct(SettingValidatorInterface $settingValidator)
    {
        $this->settingValidator = $settingValidator;
    }

    /**
     * @param ModuleConfiguration $configuration
     * @return array
     */
    public function toData(ModuleConfiguration $configuration): array
    {
        $data['id']         = $configuration->getId();
        $data['version']    = $configuration->getVersion();
        $data['state']      = $configuration->getState();
        $data['path']       = $configuration->getPath();
        $data['settings']   = $this->getSettingsData($configuration);

        return $data;
    }

    /**
     * @param array $data
     * @return ModuleConfiguration
     */
    public function fromData(array $data): ModuleConfiguration
    {
        $moduleConfiguration = new ModuleConfiguration();
        $moduleConfiguration
            ->setId($data['id'])
            ->setVersion($data['version'])
            ->setState($data['state'])
            ->setPath($data['path']);

        if (isset($data['settings'])) {
            $this->setSettings($moduleConfiguration, $data['settings']);
        }

        return $moduleConfiguration;
    }

    /**
     * @param ModuleConfiguration $moduleConfiguration
     * @param array               $settingsData
     */
    private function setSettings(ModuleConfiguration $moduleConfiguration, array $settingsData)
    {
        $settings = $this->getMappedSettings($settingsData);

        $this->settingValidator->validate(
            $moduleConfiguration->getVersion(),
            $settings
        );

        foreach ($settings as $setting) {
            $moduleConfiguration->setModuleSetting(
                $setting->getName(),
                $setting
            );
        }
    }

    /**
     * @param ModuleConfiguration $moduleConfiguration
     * @return array
     */
    private function getSettingsData(ModuleConfiguration $moduleConfiguration): array
    {
        $data = [];

        foreach ($moduleConfiguration->getSettings() as $setting) {
            $data[$setting->getName()] = $setting->getValue();
        }

        return $data;
    }

    /**
     * @param array $settingsData
     * @return array
     */
    private function getMappedSettings(array $settingsData): array
    {
        $settings = [];
        foreach ($settingsData as $settingName => $settingValue) {
            $settings[] = new ModuleSetting($settingName, $settingValue);
        }

        return $settings;
    }
}
