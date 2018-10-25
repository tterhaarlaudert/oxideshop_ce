<?php
/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\EshopCommunity\Internal\Twig\Extensions;

use OxidEsales\EshopCommunity\Internal\Adapter\TemplateLogic\InputhelpLogic;
use OxidEsales\EshopCommunity\Internal\Twig\TwigEngine;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * Class OxidExtension
 */
class InputhelpExtension extends AbstractExtension
{

    /**
     * @var InputhelpLogic
     */
    private $inputhelpLogic;

    /**
     * InputhelpExtension constructor.
     *
     * @param InputhelpLogic $inputhelpLogic
     */
    public function __construct(InputhelpLogic $inputhelpLogic)
    {
        $this->inputhelpLogic = $inputhelpLogic;
    }

    /**
     * @return array|\Twig_Function[]
     */
    public function getFunctions()
    {
        return [
            new TwigFunction('getSHelpId', [$this, 'getSHelpId']),
            new TwigFunction('getSHelpText', [$this, 'getSHelpText'])
        ];
    }

    /**
     * @param string $sIdent
     *
     * @return mixed
     */
    public function getSHelpId($sIdent)
    {
        $getInputhelpParameters = $this->inputhelpLogic->getInputhelpParameters(['ident' => $sIdent]);

        return $getInputhelpParameters['sIdent'];
    }

    /**
     * @param string $sIdent
     *
     * @return mixed
     */
    public function getSHelpText($sIdent)
    {
        $getInputhelpParameters = $this->inputhelpLogic->getInputhelpParameters(['ident' => $sIdent]);

        return $getInputhelpParameters['sTranslation'];
    }
}
