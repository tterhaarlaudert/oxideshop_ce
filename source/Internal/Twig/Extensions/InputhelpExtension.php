<?php
/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\EshopCommunity\Internal\Twig\Extensions;

use OxidEsales\EshopCommunity\Internal\Adapter\TemplateLogic\InputHelpLogic;
use OxidEsales\EshopCommunity\Internal\Twig\TwigEngine;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * Class OxidExtension
 */
class InputhelpExtension extends AbstractExtension
{

    /**
     * @var InputHelpLogic
     */
    private $inputHelpLogic;

    /**
     * InputhelpExtension constructor.
     *
     * @param InputHelpLogic $inputHelpLogic
     */
    public function __construct(InputHelpLogic $inputHelpLogic)
    {
        $this->inputHelpLogic = $inputHelpLogic;
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
        $getInputhelpParameters = $this->inputHelpLogic->getInputHelpParameters(['ident' => $sIdent]);

        return $getInputhelpParameters['sIdent'];
    }

    /**
     * @param string $sIdent
     *
     * @return mixed
     */
    public function getSHelpText($sIdent)
    {
        $getInputHelpParameters = $this->inputHelpLogic->getInputHelpParameters(['ident' => $sIdent]);

        return $getInputHelpParameters['sTranslation'];
    }
}
