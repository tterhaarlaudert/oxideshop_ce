<?php declare(strict_types=1);
/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\EshopCommunity\Internal\Module\Configuration\Dao;

use OxidEsales\EshopCommunity\Internal\Common\Storage\ArrayStorageInterface;
use OxidEsales\EshopCommunity\Internal\Module\Configuration\DataMapper\ProjectConfigurationDataMapperInterface;
use OxidEsales\EshopCommunity\Internal\Module\Configuration\DataObject\ProjectConfiguration;
use Symfony\Component\Config\Definition\NodeInterface;

/**
 * @internal
 */
class ProjectConfigurationDao implements ProjectConfigurationDaoInterface
{
    /**
     * @var ArrayStorageInterface
     */
    private $arrayStorage;

    /**
     * @var ProjectConfigurationDataMapperInterface
     */
    private $projectConfigurationDataMapper;

    /**
     * @var NodeInterface
     */
    private $node;

    /**
     * ProjectConfigurationDao constructor.
     * @param ArrayStorageInterface                   $arrayStorage
     * @param ProjectConfigurationDataMapperInterface $projectConfigurationDataMapper
     * @param NodeInterface                           $node
     */
    public function __construct(
        ArrayStorageInterface                   $arrayStorage,
        ProjectConfigurationDataMapperInterface $projectConfigurationDataMapper,
        NodeInterface                           $node
    ) {
        $this->arrayStorage = $arrayStorage;
        $this->projectConfigurationDataMapper = $projectConfigurationDataMapper;
        $this->node = $node;
    }


    /**
     * @return ProjectConfiguration
     */
    public function getConfiguration(): ProjectConfiguration
    {
        $data = $this->arrayStorage->get();

        return $this->projectConfigurationDataMapper->fromData(
            $this->node->normalize($data)
        );
    }

    /**
     * @param ProjectConfiguration $configuration
     */
    public function persistConfiguration(ProjectConfiguration $configuration)
    {
        $data = $this->projectConfigurationDataMapper->toData($configuration);

        $this->arrayStorage->save(
            $this->node->normalize($data)
        );
    }
}
