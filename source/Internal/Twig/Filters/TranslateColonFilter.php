<?php
/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\EshopCommunity\Internal\Twig\Filters;

use Twig\Extension\AbstractExtension;

/**
 * Class TranslateColon
 *
 * @package OxidEsales\EshopCommunity\Internal\Twig\Extensions
 */
class TranslateColonFilter extends AbstractExtension
{

    /**
     * @return array|\Twig_Filter[]
     */
    public function getFilters()
    {
        return [new \Twig_Filter('getTranslatedColon', [$this, 'getTranslatedColon'])];
    }

    /**
     * Adds colon for selected language
     *
     * @param string $string
     *
     * @return mixed
     */
    public function getTranslatedColon($string)
    {
        $colon = \OxidEsales\Eshop\Core\Registry::getLang()->translateString('COLON');

        return $string . $colon;
    }
}
