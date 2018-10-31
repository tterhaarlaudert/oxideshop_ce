<?php

namespace OxidEsales\EshopCommunity\Internal\Adapter\TemplateLogic;

/**
 * Class InputHelpLogic
 *
 * @package OxidEsales\EshopCommunity\Internal\Adapter\TemplateLogic
 */
class InputHelpLogic
{

    /**
     * @param array $params
     *
     * @return array
     */
    public function getInputHelpParameters($params)
    {
        $sIdent = $this->getIdent($params);
        $sTranslation = $this->getTranslation($sIdent);
        $helpInputParameters = $this->formatHelpInputParameters($sIdent, $sTranslation);

        return $helpInputParameters;
    }

    /**
     * @param array $params
     *
     * @return null
     */
    private function getIdent($params)
    {
        return isset($params['ident']) ? $params['ident'] : null;
    }

    /**
     * @param string $sIdent
     *
     * @return mixed
     */
    private function getTranslation($sIdent)
    {
        $sTranslation = null;
        $oLang = \OxidEsales\Eshop\Core\Registry::getLang();
        $iLang = $oLang->getTplLanguage();
        $myConfig = \OxidEsales\Eshop\Core\Registry::getConfig();
        $blAdmin = $myConfig->isAdmin();
        try {
            $sTranslation = $oLang->translateString($sIdent, $iLang, $blAdmin);
        } catch (\OxidEsales\Eshop\Core\Exception\LanguageException $oEx) {
            // is thrown in debug mode and has to be caught here, as smarty hangs otherwise!
        }

        return $sTranslation;
    }

    /**
     * @param string $sIdent
     * @param string $sTranslation
     *
     * @return array
     */
    private function formatHelpInputParameters($sIdent, $sTranslation)
    {
        return ['sIdent' => $sIdent, 'sTranslation' => $sTranslation];
    }
}
