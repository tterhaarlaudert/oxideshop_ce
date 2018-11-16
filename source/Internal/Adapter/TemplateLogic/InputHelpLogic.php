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
     * @return null
     */
    public function getIdent($params)
    {
        return isset($params['ident']) ? $params['ident'] : null;
    }

    /**
     * @param array $params
     *
     * @return mixed
     */
    public function getTranslation($params)
    {
        $sIdent = $this->getIdent($params);
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
}
