<?php declare(strict_types=1);

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\EshopCommunity\Tests\Unit\Internal\Module\MetaData;

use OxidEsales\EshopCommunity\Internal\Module\Configuration\DataObject\ModuleConfiguration;
use OxidEsales\EshopCommunity\Internal\Module\MetaData\MetaDataDataMapper;
use OxidEsales\EshopCommunity\Internal\Module\MetaData\Validator\MetaDataValidatorInterface;
use PHPUnit\Framework\TestCase;

class MetaDataDataMapperTest extends TestCase
{
    private $metaDataValidatorStub;

    protected function setUp()
    {
        parent::setUp();

        $this->metaDataValidatorStub = $this->getMockBuilder(MetaDataValidatorInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->metaDataValidatorStub->method('validate');
    }

    /**
     * @expectedException \DomainException
     */
    public function testToDataThrowsException()
    {
        $metaDataDataMapper = new MetaDataDataMapper($this->metaDataValidatorStub);

        $moduleConfiguration = $this->getMockBuilder(ModuleConfiguration::class)
            ->disableOriginalConstructor()
            ->getMock();
        $metaDataDataMapper->toData($moduleConfiguration);
    }

    /**
     * @dataProvider dataProviderInvalidData
     *
     * @expectedException \InvalidArgumentException
     *
     * @param $invalidData
     */
    public function testFromDataWillThrowExceptionOnInvalidParameterFormat($invalidData)
    {
        $metaDataDataMapper = new MetaDataDataMapper($this->metaDataValidatorStub);
        $metaDataDataMapper->fromData($invalidData);
    }

    public function dataProviderInvalidData(): array
    {
        return [
            'all mandatory keys are missing'    => [[]],
            'key metaDataVersion is missing'    => [['moduleData' => '']],
            'key moduleData version is missing' => [['metaDataVersion' => '']],
        ];
    }
}
