<?php
/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\EshopCommunity\Tests\Unit\Internal\Twig\Extension;

use OxidEsales\EshopCommunity\Internal\Adapter\TemplateLogic\CollectStyleSheetsLogic;
use OxidEsales\EshopCommunity\Internal\Twig\Extensions\CollectStyleSheetsExtension;
use \PHPUnit\Framework\TestCase;

class CollectStyleSheetsExtensionTest extends TestCase
{

    /**
     * @covers       \OxidEsales\EshopCommunity\Internal\Adapter\TemplateLogic\CollectStyleSheetsLogic::collectStyleSheets
     * @dataProvider dataProvider
     *
     * @param $params
     * @param $isDynamic
     */
    public function testCollectStyleSheets($params, $isDynamic)
    {
        $collectStyleSheetsExtension = $this->getCollectStyleSheetsExtensionMock($params, $isDynamic);
        $collectStyleSheetsExtension->collectStyleSheets($params);
    }

    public function dataProvider()
    {
        return [
            [['foo' => 'bar'], true],
            [['foo' => 'bar'], false]
        ];
    }

    /**
     * @param array $params
     * @param bool  $isDynamic
     *
     * @return CollectStyleSheetsExtension
     */
    private function getCollectStyleSheetsExtensionMock($params, $isDynamic)
    {
        /** @var CollectStyleSheetsLogic $collectStyleSheetsLogic */
        $collectStyleSheetsLogic = $this->getMockBuilder('OxidEsales\EshopCommunity\Internal\Adapter\TemplateLogic\CollectStyleSheetsLogic')->disableOriginalConstructor()->getMock();
        $collectStyleSheetsLogic->method('collectStyleSheets')->willReturn([]);
        $collectStyleSheetsLogic->expects($this->once())->method('collectStyleSheets')->with($params, $isDynamic);
        $collectStyleSheetsExtension = new CollectStyleSheetsExtension($collectStyleSheetsLogic, $isDynamic);

        return $collectStyleSheetsExtension;
    }
}
