<?php
declare(strict_types=1);

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\EshopCommunity\Internal\Module\ShopModuleSetting;

use function is_string;

use OxidEsales\EshopCommunity\Internal\Adapter\Configuration\Service\ShopSettingEncoderInterface;
use OxidEsales\EshopCommunity\Internal\Adapter\ShopAdapterInterface;
use OxidEsales\EshopCommunity\Internal\Common\Database\QueryBuilderFactoryInterface;
use OxidEsales\EshopCommunity\Internal\Common\Exception\EntryDoesNotExistDaoException;
use OxidEsales\EshopCommunity\Internal\Utility\ContextInterface;

/**
 * @internal
 */
class ShopModuleSettingDao implements ShopModuleSettingDaoInterface
{
    /**
     * @var QueryBuilderFactoryInterface
     */
    private $queryBuilderFactory;

    /**
     * @var ContextInterface
     */
    private $context;

    /**
     * @var ShopSettingEncoderInterface
     */
    private $shopSettingEncoder;

    /**
     * @var ShopAdapterInterface
     */
    private $shopAdapter;

    /**
     * ShopModuleSettingDao constructor.
     * @param QueryBuilderFactoryInterface $queryBuilderFactory
     * @param ContextInterface             $context
     * @param ShopSettingEncoderInterface  $shopSettingEncoder
     * @param ShopAdapterInterface         $shopAdapter
     */
    public function __construct(
        QueryBuilderFactoryInterface    $queryBuilderFactory,
        ContextInterface                $context,
        ShopSettingEncoderInterface     $shopSettingEncoder,
        ShopAdapterInterface            $shopAdapter
    ) {
        $this->queryBuilderFactory = $queryBuilderFactory;
        $this->context = $context;
        $this->shopSettingEncoder = $shopSettingEncoder;
        $this->shopAdapter = $shopAdapter;
    }

    /**
     * @param ShopModuleSetting $shopModuleSetting
     */
    public function save(ShopModuleSetting $shopModuleSetting)
    {
        /**
         * The same entity was splitted between two tables.
         * Till we can't refactor tables we have to save data in both.
         */
        $this->deleteFromOxConfigTable($shopModuleSetting);
        $this->deleteFromOxConfigDisplayTable($shopModuleSetting);

        $this->saveDataToOxConfigTable($shopModuleSetting);
        $this->saveDataToOxConfigDisplayTable($shopModuleSetting);
    }

    /**
     * @param ShopModuleSetting $shopModuleSetting
     */
    public function delete(ShopModuleSetting $shopModuleSetting)
    {
        $this->deleteFromOxConfigTable($shopModuleSetting);
    }

    /**
     * @param string $name
     * @param string $moduleId
     * @param int    $shopId
     *
     * @return ShopModuleSetting
     * @throws EntryDoesNotExistDaoException
     */
    public function get(string $name, string $moduleId, int $shopId): ShopModuleSetting
    {
        /**
         * The same entity was splitted between two tables.
         * Till we can't refactor tables we have to get data from both.
         */
        $settingsData = array_merge(
            $this->getDataFromOxConfigTable($name, $moduleId, $shopId),
            $this->getDataFromOxConfigDisplayTable($name, $moduleId)
        );

        $setting = new ShopModuleSetting();
        $setting
            ->setName($name)
            ->setValue($this->shopSettingEncoder->decode($settingsData['type'], $settingsData['value']))
            ->setShopId($shopId)
            ->setModuleId($moduleId)
            ->setType($settingsData['type']);

        if (isset($settingsData['oxvarconstraint'])
            && is_string($settingsData['oxvarconstraint'])
            && $settingsData['oxvarconstraint'] !== ''
        ) {
            $setting->setConstraints(
                explode('|', $settingsData['oxvarconstraint'])
            );
        }

        if (isset($settingsData['oxgrouping'])) {
            $setting->setGroupName($settingsData['oxgrouping']);
        }

        if (isset($settingsData['oxpos'])) {
            $setting->setPositionInGroup(
                (int) $settingsData['oxpos']
            );
        }

        return $setting;
    }

    /**
     * @param ShopModuleSetting $shopModuleSetting
     */
    private function saveDataToOxConfigTable(ShopModuleSetting $shopModuleSetting)
    {
        $queryBuilder = $this->queryBuilderFactory->create();
        $queryBuilder
            ->insert('oxconfig')
            ->values([
                'oxid'          => ':id',
                'oxmodule'      => ':moduleId',
                'oxshopid'      => ':shopId',
                'oxvarname'     => ':name',
                'oxvartype'     => ':type',
                'oxvarvalue'    => 'encode(:value, :key)',
            ])
            ->setParameters([
                'id'        => $this->shopAdapter->generateUniqueId(),
                'moduleId'  => $shopModuleSetting->getModuleId(),
                'shopId'    => $shopModuleSetting->getShopId(),
                'name'      => $shopModuleSetting->getName(),
                'type'      => $shopModuleSetting->getType(),
                'value'     => $this->shopSettingEncoder->encode(
                    $shopModuleSetting->getType(),
                    $shopModuleSetting->getValue()
                ),
                'key'       => $this->context->getConfigurationEncryptionKey(),
            ]);

        $queryBuilder->execute();
    }

    /**
     * @param ShopModuleSetting $shopModuleSetting
     */
    private function saveDataToOxConfigDisplayTable(ShopModuleSetting $shopModuleSetting)
    {
        $queryBuilder = $this->queryBuilderFactory->create();
        $queryBuilder
            ->insert('oxconfigdisplay')
            ->values([
                'oxid'              => ':id',
                'oxcfgmodule'       => ':moduleId',
                'oxcfgvarname'      => ':name',
                'oxgrouping'        => ':groupName',
                'oxpos'             => ':position',
                'oxvarconstraint'   => ':constraints',
            ])
            ->setParameters([
                'id'            => $this->shopAdapter->generateUniqueId(),
                'moduleId'      => 'module:' . $shopModuleSetting->getModuleId(),
                'name'          => $shopModuleSetting->getName(),
                'groupName'     => $shopModuleSetting->getGroupName(),
                'position'      => $shopModuleSetting->getPositionInGroup(),
                'constraints'   => implode('|', $shopModuleSetting->getConstraints()),
            ]);

        $queryBuilder->execute();
    }

    /**
     * @param string $name
     * @param string $moduleId
     * @param int    $shopId
     *
     * @return array
     * @throws EntryDoesNotExistDaoException
     */
    private function getDataFromOxConfigTable(string $name, string $moduleId, int $shopId): array
    {
        $queryBuilder = $this->queryBuilderFactory->create();
        $queryBuilder
            ->select('decode(oxvarvalue, :key) as value, oxvartype as type, oxvarname as name')
            ->from('oxconfig')
            ->where('oxshopid = :shopId')
            ->where('oxmodule = :moduleId')
            ->andWhere('oxvarname = :name')
            ->setParameters([
                'shopId'    => $shopId,
                'moduleId'  => $moduleId,
                'name'      => $name,
                'key'       => $this->context->getConfigurationEncryptionKey(),
            ]);

        $result = $queryBuilder->execute()->fetch();

        if (false === $result) {
            throw new EntryDoesNotExistDaoException();
        }

        return $result;
    }

    /**
     * @param string $name
     * @param string $moduleId
     * @return array
     */
    private function getDataFromOxConfigDisplayTable(string $name, string $moduleId): array
    {
        $queryBuilder = $this->queryBuilderFactory->create();
        $queryBuilder
            ->select('oxgrouping, oxpos, oxvarconstraint')
            ->from('oxconfigdisplay')
            ->where('oxcfgmodule = :moduleId')
            ->andWhere('oxcfgvarname = :name')
            ->setParameters([
                'moduleId'  => 'module:' . $moduleId,
                'name'      => $name,
            ]);

        $result = $queryBuilder->execute()->fetch();

        return $result ?? [];
    }

    /**
     * @param ShopModuleSetting $shopModuleSetting
     */
    private function deleteFromOxConfigTable(ShopModuleSetting $shopModuleSetting)
    {
        $queryBuilder = $this->queryBuilderFactory->create();
        $queryBuilder
            ->delete('oxconfig')
            ->where('oxshopid = :shopId')
            ->andWhere('oxvarname = :name')
            ->where('oxmodule = :moduleId')
            ->setParameters([
                'shopId'    => $shopModuleSetting->getShopId(),
                'name'      => $shopModuleSetting->getName(),
                'moduleId'  => $shopModuleSetting->getModuleId(),
            ]);

        $queryBuilder->execute();
    }

    /**
     * @param ShopModuleSetting $shopModuleSetting
     */
    private function deleteFromOxConfigDisplayTable(ShopModuleSetting $shopModuleSetting)
    {
        $queryBuilder = $this->queryBuilderFactory->create();
        $queryBuilder
            ->delete('oxconfigdisplay')
            ->where('oxcfgmodule = :moduleId')
            ->andWhere('oxcfgvarname = :name')
            ->setParameters([
                'moduleId'  => 'module:' . $shopModuleSetting->getModuleId(),
                'name'      => $shopModuleSetting->getName(),
            ]);

        $queryBuilder->execute();
    }
}
