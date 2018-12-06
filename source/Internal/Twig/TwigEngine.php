<?php
/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 *
 * @author Jędrzej Skoczek & Tomasz Kowalewski
 */

namespace OxidEsales\EshopCommunity\Internal\Twig;

use OxidEsales\EshopCommunity\Internal\Templating\BaseEngineInterface;

use Symfony\Component\Templating\TemplateNameParserInterface;
use Symfony\Component\Templating\TemplateReferenceInterface;
use Twig\Environment;

/**
 * Class TwigEngine
 */
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
     *
     * @param Environment                 $engine
     * @param TemplateNameParserInterface $parser
     */
    public function __construct(Environment $engine, TemplateNameParserInterface $parser)
    {
        $this->engine = $engine;
        $this->parser = $parser;

        if ($this->engine->isDebug()) {
            $this->engine->addExtension(new \Twig_Extension_Debug());
        }
    }

    /**
     * Renders a template.
     *
     * @param string $name       A template name or a TemplateReferenceInterface instance
     * @param array  $parameters An array of parameters to pass to the template
     *
     * @return string The evaluated template as a string
     */
    public function render($name, array $parameters = array()): string
    {
        return $this->engine->render($name, $parameters);
    }

    /**
     * Returns true if the template exists.
     *
     * @param string $name A template name or a TemplateReferenceInterface instance
     *
     * @return bool true if the template exists, false otherwise
     */
    public function exists($name): bool
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
    public function supports($name): bool
    {
        $template = $this->parser->parse($name);

        return 'twig' === $template->get('engine');
    }

    /**
     * @param string $cacheId
     *
     * @return void
     */
    public function setCacheId($cacheId): void
    {
    }

    /**
     * @param string $name
     * @param mixed  $value
     *
     * @return void
     */
    public function addGlobal($name, $value): void
    {
        $this->globals[$name] = $value;
    }

    /**
     * @return array
     */
    public function getGlobals(): array
    {
        return $this->globals;
    }
}
