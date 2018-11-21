<?php
declare(strict_types=1);

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\EshopCommunity\Internal\Application;

use OxidEsales\Eshop\Core\Registry;
use Psr\Container\ContainerInterface;
use Symfony\Component\DependencyInjection\Dumper\PhpDumper;

/**
  *
 * @internal
 */
class ContainerFactory
{
    /**
     * @var self
     */
    private static $instance = null;

    /**
     * @var ContainerInterface
     */
    private $symfonyContainer = null;

    /**
     * ContainerFactory constructor.
     *
     * Make the constructor private
     */
    private function __construct()
    {
    }

    /**
     * @return ContainerInterface
     */
    public function getContainer()
    {
        if ($this->symfonyContainer === null) {
            $this->initializeContainer();
        }

        return $this->symfonyContainer;
    }

    /**
     * Reads the container definition again and writes
     * new cache file
     */
    public function resetContainer()
    {
        $cacheFilePath = $this->getCacheFilePath();
        $this->getCompiledSymfonyContainer();
        $this->saveContainerToCache($cacheFilePath);
    }

    /**
     * Loads container from cache if available, otherwise
     * create the container from scratch.
     */
    private function initializeContainer()
    {
        $cacheFilePath = $this->getCacheFilePath();

        if (file_exists($cacheFilePath)) {
            $this->loadContainerFromCache($cacheFilePath);
        } else {
            $this->getCompiledSymfonyContainer();
            $this->saveContainerToCache($cacheFilePath);
        }
    }

    /**
     * @param string $cachefile
     */
    private function loadContainerFromCache($cachefile)
    {
        include_once $cachefile;
        $this->symfonyContainer = new \ProjectServiceContainer();
    }

    /**
     * Returns compiled Container
     */
    private function getCompiledSymfonyContainer()
    {
        $containerBuilder = new ContainerBuilder();
        $this->symfonyContainer = $containerBuilder->getContainer();
        $this->symfonyContainer->compile();
    }

    /**
     * Dumps the compiled container to the cachefile.
     *
     * @param string $cachefile
     */
    private function saveContainerToCache($cachefile)
    {
        $dumper = new PhpDumper($this->symfonyContainer);
        file_put_contents($cachefile, $dumper->dump(), LOCK_EX);
    }

    /**
     * @todo: move it to another place.
     *
     * @return string
     */
    private function getCacheFilePath()
    {
        $compileDir = Registry::getConfig()->getConfigParam('sCompileDir');

        return $compileDir . '/containercache.php';
    }

    /**
     * @return ContainerFactory
     */
    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new ContainerFactory();
        }
        return self::$instance;
    }
}
