<?php
/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 *
 * @author Jędrzej Skoczek & Tomasz Kowalewski
 */

namespace OxidEsales\EshopCommunity\Internal\Twig;

/**
 * Interface TwigContextInterface
 *
 * @package OxidEsales\EshopCommunity\Internal\Twig
 */
interface TwigContextInterface
{

    /**
     * @return array
     */
    public function getTemplateDirectories(): array;

    /**
     * @return boolean
     */
    public function getIsDebug(): bool;

    /**
     * @return string
     */
    public function getCacheDir(): string;
}
