<?php
/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 *
 * @author Jędrzej Skoczek & Tomasz Kowalewski
 */

namespace OxidEsales\EshopCommunity\Internal\Twig;

use OxidEsales\EshopCommunity\Internal\Templating\BaseEngineInterface;
use OxidEsales\EshopCommunity\Internal\Twig\Extensions\MathExtension;
use OxidEsales\EshopCommunity\Internal\Twig\Extensions\OxidExtension;
use OxidEsales\EshopCommunity\Internal\Twig\Extensions\OxidIncludeExtension;
use OxidEsales\EshopCommunity\Internal\Twig\Extensions\SmartyExtension;
use OxidEsales\EshopCommunity\Internal\Twig\Extensions\MailtoExtension;

use Symfony\Component\Templating\TemplateNameParserInterface;
use Symfony\Component\Templating\TemplateReferenceInterface;
use Twig\Environment;

class TwigEngine implements BaseEngineInterface
{
    /**
     * @var \Twig_Environment
     */
    private $engine;

    /**
     * @var TemplateNameParserInterface
     */
    protected $parser;

    /**
     * Array of global parameters
     *
     * @var array
     */
    private $globals = [];


    /**
     * TwigEngine constructor.
     * @param Environment $engine
     * @param TemplateNameParserInterface $parser
     */
    public function __construct(Environment $engine, TemplateNameParserInterface $parser)
    {
		$this->engine = $engine;
        $this->parser = $parser;

        if ($this->engine->isDebug()) {
            $this->engine->addExtension(new \Twig_Extension_Debug());
        }

        $this->engine->addExtension(new MathExtension());
        $this->engine->addExtension(new OxidExtension());
        $this->engine->addExtension(new OxidIncludeExtension());
        $this->engine->addExtension(new SmartyExtension());

        $this->engine->addExtension(new MailtoExtension());
    }

    /**
     * Renders a template.
     *
     * @param string|TemplateReferenceInterface $name A template name or a TemplateReferenceInterface instance
     * @param array $parameters An array of parameters to pass to the template
     *
     * @return string The evaluated template as a string
     */
    public function render($name, array $parameters = array())
    {
        return $this->engine->render($name, $parameters);
    }

    /**
     * Returns true if the template exists.
     *
     * @param string|TemplateReferenceInterface $name A template name or a TemplateReferenceInterface instance
     *
     * @return bool true if the template exists, false otherwise
     */
    public function exists($name)
    {
        return $this->engine->getLoader()->exists($name);
    }

    /**
     * Returns true if this class is able to render the given template.
     *
     * @param string|TemplateReferenceInterface $name A template name or a TemplateReferenceInterface instance
     *
     * @return bool true if this class supports the given template, false otherwise
     */
    public function supports($name)
    {
        $template = $this->parser->parse($name);

        return 'twig' === $template->get('engine');
    }

    /**
     * @param string $cacheId
     */
    public function setCacheId($cacheId)
    {
    }

    /**
     * @param string $name
     *
     * @param mixed  $value
     */
    public function addGlobal($name, $value)
    {
        $this->globals[$name] = $value;
    }

    /**
     * @return array
    public function getGlobals()
     */
    public function getGlobals()
    {
        return $this->globals;
    }
}
