<?php declare(strict_types=1);
/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\EshopCommunity\Internal\Module\State;

/**
 * @internal
 */
interface ModuleStateServiceInterface
{
    /**
     * @param string $moduleName
     * @param int    $shopId
     * @return string
     */
    public function getState(string $moduleName, int $shopId): string;

    /**
     * @param string $moduleName
     */
    public function setDeleted(string $moduleName);
}
