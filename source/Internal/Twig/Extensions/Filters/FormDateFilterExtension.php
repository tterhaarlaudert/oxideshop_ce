<?php

namespace OxidEsales\EshopCommunity\Internal\Twig\Extensions\Filters;

use OxidEsales\EshopCommunity\Internal\Adapter\TemplateLogic\FormDateLogic;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

/**
 * Class FormDateFilterExtension
 */
class FormDateFilterExtension extends AbstractExtension
{

    /** @var FormDateLogic */
    private $formDateLogic;

    /**
     * FormDateFilterExtension constructor.
     *
     * @param FormDateLogic $formDateLogic
     */
    public function __construct(FormDateLogic $formDateLogic)
    {
        $this->formDateLogic = $formDateLogic;
    }

    /**
     * Returns a list of functions to add to the existing list.
     *
     * @return TwigFilter[]
     */
    public function getFilters()
    {
        return [
            new TwigFilter('form_date', [$this, 'formDate'], ['is_safe' => ['html']]),
        ];
    }

    /**
     * @param object $convObject
     * @param null   $fieldType
     * @param bool   $passedValue
     *
     * @return string
     */
    public function formDate($convObject, $fieldType = null, $passedValue = false)
    {
        return $this->formDateLogic->formdate($convObject, $fieldType, $passedValue);
    }
}
