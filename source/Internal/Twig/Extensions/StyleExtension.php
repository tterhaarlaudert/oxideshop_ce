<?php
/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\EshopCommunity\Internal\Twig\Extensions;

use OxidEsales\EshopCommunity\Internal\Adapter\TemplateLogic\StyleLogic;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * Class AssignAdvancedExtension
 *
 * @package OxidEsales\EshopCommunity\Internal\Twig\Extensions
 */
class StyleExtension extends AbstractExtension
{

    /**
     * @var StyleLogic
     */
    private $styleLogic;
    private $isDynamic;

    /**
     * StyleExtension constructor.
     *
     * @param StyleLogic $styleLogic
     * @param bool       $isDynamic
     */
    public function __construct(StyleLogic $styleLogic, $isDynamic)
    {
        $this->styleLogic = $styleLogic;
        $this->isDynamic = $isDynamic;
    }

    /**
     * @return array|\Twig_Function[]
     */
    public function getFunctions()
    {
        return [new TwigFunction('style', [$this, 'style'])];
    }

    /**
     * @param array $params
     *
     * @return mixed
     */
    public function style($params)
    {
        $output = $this->styleLogic->collectStyleSheets($params, $this->isDynamic);

        return $output;
    }
}
