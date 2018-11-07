<?php
/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\EshopCommunity\Internal\Twig\Extensions;

use OxidEsales\EshopCommunity\Internal\Adapter\TemplateLogic\CollectStyleSheetsLogic;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * Class AssignAdvancedExtension
 *
 * @package OxidEsales\EshopCommunity\Internal\Twig\Extensions
 */
class CollectStyleSheetsExtension extends AbstractExtension
{

    /**
     * @var CollectStyleSheetsLogic
     */
    private $collectStyleSheetsLogic;
    private $isDynamic;

    /**
     * CollectStyleSheetsExtension constructor.
     *
     * @param CollectStyleSheetsLogic $collectStyleSheetsLogic
     * @param bool                    $isDynamic
     */
    public function __construct(CollectStyleSheetsLogic $collectStyleSheetsLogic, $isDynamic)
    {
        $this->collectStyleSheetsLogic = $collectStyleSheetsLogic;
        $this->isDynamic = $isDynamic;
    }

    /**
     * @return array|\Twig_Function[]
     */
    public function getFunctions()
    {
        return [new TwigFunction('collectStyleSheets', [$this, 'collectStyleSheets'])];
    }

    /**
     * @param array $params
     *
     * @return mixed
     */
    public function collectStyleSheets($params)
    {
        $output = $this->collectStyleSheetsLogic->collectStyleSheets($params, $this->isDynamic);

        return $output;
    }
}
