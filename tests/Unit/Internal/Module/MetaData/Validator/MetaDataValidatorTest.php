<?php declare(strict_types=1);

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\EshopCommunity\Tests\Unit\Internal\Module\Configuration\Validator;

use OxidEsales\EshopCommunity\Internal\Module\MetaData\MetaDataDataProvider;
use OxidEsales\EshopCommunity\Internal\Module\MetaData\MetaDataSchemataProvider;
use OxidEsales\EshopCommunity\Internal\Module\MetaData\Validator\MetaDataValidator;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class MetaDataValidatorTest extends TestCase
{
    private $metaDataSchemata;
    private $metaDataSchemaVersion20;
    private $metaDataSchemaVersion21;

    protected function setUp()
    {
        parent::setUp();

        $this->metaDataSchemaVersion20 = [
            '20only',
            'section1' =>
                ['subKey1',
                 'subKey2',
                ],
            'extend',
            'templates',
        ];
        $this->metaDataSchemaVersion21 = [
            '21only',
            'section1' =>
                ['subKey1',
                 'subKey2',
                ],
            'extend',
            'templates',
        ];
        $this->metaDataSchemata = [
            '2.0' => $this->metaDataSchemaVersion20,
            '2.1' => $this->metaDataSchemaVersion21,
        ];
    }

    /**
     * @expectedException \OxidEsales\EshopCommunity\Internal\Module\MetaData\Validator\UnsupportedMetaDataVersionException
     */
    public function testValidateThrowsExceptionOnUnsupportedMetaDataVersion()
    {
        $metaDataToValidate = [];

        $metaDataSchemata = new MetaDataSchemataProvider($this->metaDataSchemata);
        $eventDispatcherStub = $this->getMockBuilder(EventDispatcherInterface::class)->getMock();
        $validator = new MetaDataValidator($metaDataSchemata, $eventDispatcherStub);

        $validator->validate('1.2', $metaDataToValidate);
    }

    public function testValidateDispatchesEventOnUnsupportedMetaDataKey()
    {
        $metaDataToValidate = [
            'smartyPluginDirectories' => [],
        ];

        $metaDataSchemata = new MetaDataSchemataProvider($this->metaDataSchemata);
        $eventDispatcherMock = $this->getMockBuilder(EventDispatcherInterface::class)->getMock();
        $eventDispatcherMock->expects($this->once())->method('dispatch');
        $validator = new MetaDataValidator($metaDataSchemata, $eventDispatcherMock);

        $validator->validate('2.0', $metaDataToValidate);
    }

    /**
     * This test covers metaData sections like 'blocks' or 'settings', which have their own well defined subKeys
     */
    public function testValidateDispatchesEventOnUnsupportedMetaDataSubKey()
    {
        $metaDataToValidate = [
            '20only'   => 'value',
            'section1' => [
                [
                    'subkey1' => 'value1',
                    'subkey2' => 'value1',
                ],
                [
                    'subkey1'        => 'value2',
                    'unsupportedKey' => 'value2',
                ],
            ]
        ];

        $metaDataSchemata = new MetaDataSchemataProvider($this->metaDataSchemata);
        $eventDispatcherMock = $this->getMockBuilder(EventDispatcherInterface::class)->getMock();
        $eventDispatcherMock->expects($this->once())->method('dispatch');
        $validator = new MetaDataValidator($metaDataSchemata, $eventDispatcherMock);

        $validator->validate('2.0', $metaDataToValidate);
    }

    /**
     * This test covers metaData sections like 'extend', or 'templates', which have their custom subKeys
     */
    public function testExcludedSectionItemValidation()
    {
        $metaDataToValidate = [
            '20only'    => 'value',
            'section1'  => [
                [
                    'subkey1' => 'value1',
                    'subkey2' => 'value1',
                ],
                [
                    'subkey1' => 'value2',
                    'subkey2' => 'value2',
                ],
            ],
            MetaDataDataProvider::METADATA_EXTEND    => [
                'excludedsubkey1' => 'value2',
                'excludedsubkey2' => 'value2',
            ],
            MetaDataDataProvider::METADATA_CONTROLLERS    => [
                'excludedsubkey1' => 'value2',
                'excludedsubkey2' => 'value2',
            ],
            MetaDataDataProvider::METADATA_EVENTS    => [
                'excludedsubkey1' => 'value2',
                'excludedsubkey2' => 'value2',
            ],
            MetaDataDataProvider::METADATA_SMARTY_PLUGIN_DIRECTORIES    => [
                'excludedsubkey1' => 'value2',
                'excludedsubkey2' => 'value2',
            ],
            MetaDataDataProvider::METADATA_TEMPLATES => [
                'excludedsectionkey1' => 'value1',
                'excludedsectionkey2' => [
                    'excludedsubkey1' => 'value2',
                    'excludedsubkey2' => 'value2',
                ]
            ]
        ];

        $metaDataSchemata = new MetaDataSchemataProvider($this->metaDataSchemata);
        $eventDispatcherStub = $this->getMockBuilder(EventDispatcherInterface::class)->getMock();
        $validator = new MetaDataValidator($metaDataSchemata, $eventDispatcherStub);

        $validator->validate('2.0', $metaDataToValidate);
    }

    public function testValidateIsCaseSensitive()
    {
        $metaDataToValidate = [
            '20ONLY'   => 'value', // This UPPERCASE key will not validate
            'section1' => [
                [
                    'subkey1' => 'value1',
                    'subkey2' => 'value1',
                ],
                [
                    'subkey1' => 'value2',
                    'subkey2' => 'value2',
                ],
            ]
        ];

        $metaDataSchemata = new MetaDataSchemataProvider($this->metaDataSchemata);
        $eventDispatcherMock = $this->getMockBuilder(EventDispatcherInterface::class)->getMock();
        $eventDispatcherMock->expects($this->once())->method('dispatch');
        $validator = new MetaDataValidator($metaDataSchemata, $eventDispatcherMock);

        $validator->validate('2.0', $metaDataToValidate);
    }

    /**
     * @expectedException  \OxidEsales\EshopCommunity\Internal\Module\MetaData\Validator\UnsupportedMetaDataValueTypeException
     */
    public function testValidateThrowsExceptionOnUnsupportedMetaDataValueType()
    {
        $metaDataToValidate = [
            '20only' => new \stdClass(),
        ];

        $metaDataSchemata = new MetaDataSchemataProvider($this->metaDataSchemata);
        $eventDispatcherStub = $this->getMockBuilder(EventDispatcherInterface::class)->getMock();
        $validator = new MetaDataValidator($metaDataSchemata, $eventDispatcherStub);

        $validator->validate('2.0', $metaDataToValidate);
    }

    public function testValidateThrowsNoExceptionOnIncompleteFirstLevel()
    {
        $metaDataToValidate = [
            // missing '20only'        => 'value',
            'section1' => [
                [
                    'subkey1' => 'value1',
                    'subkey2' => 'value1'
                ],
            ]
        ];

        $metaDataSchemata = new MetaDataSchemataProvider($this->metaDataSchemata);
        $eventDispatcherStub = $this->getMockBuilder(EventDispatcherInterface::class)->getMock();
        $validator = new MetaDataValidator($metaDataSchemata, $eventDispatcherStub);

        $validator->validate('2.0', $metaDataToValidate);
    }

    public function testValidateThrowsNoExceptionOnIncompleteSecondLevel()
    {
        $metaDataToValidate = [
            '20only'   => 'value',
            'section1' => [
                [
                    // missing 'subKey1' => 'value1',
                    'subkey2' => 'value1'
                ],
            ]
        ];

        $metaDataSchemata = new MetaDataSchemataProvider($this->metaDataSchemata);
        $eventDispatcherStub = $this->getMockBuilder(EventDispatcherInterface::class)->getMock();
        $validator = new MetaDataValidator($metaDataSchemata, $eventDispatcherStub);

        $validator->validate('2.0', $metaDataToValidate);
    }
}
