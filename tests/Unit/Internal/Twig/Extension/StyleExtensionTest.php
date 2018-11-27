<?php
/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\EshopCommunity\Tests\Unit\Internal\Twig\Extension;

use OxidEsales\EshopCommunity\Internal\Adapter\TemplateLogic\StyleLogic;
use OxidEsales\EshopCommunity\Internal\Twig\Extensions\StyleExtension;
use \PHPUnit\Framework\TestCase;

class StyleExtensionTest extends TestCase
{

    /**
     * @covers       \OxidEsales\EshopCommunity\Internal\Adapter\TemplateLogic\StyleLogic::collectStyleSheets
     * @dataProvider dataProvider
     *
     * @param $params
     * @param $isDynamic
     */
    public function testCollectStyleSheets($params, $isDynamic)
    {
        $styleExtension = $this->getCollectStyleSheetsExtensionMock($params, $isDynamic);
        $styleExtension->style($params);
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
     * @return StyleExtension
     */
    private function getCollectStyleSheetsExtensionMock($params, $isDynamic)
    {
        /** @var StyleLogic $styleLogic */
        $styleLogic = $this->getMockBuilder('OxidEsales\EshopCommunity\Internal\Adapter\TemplateLogic\StyleLogic')->disableOriginalConstructor()->getMock();
        $styleLogic->method('collectStyleSheets')->willReturn([]);
        $styleLogic->expects($this->once())->method('collectStyleSheets')->with($params, $isDynamic);
        $styleExtension = new StyleExtension($styleLogic, $isDynamic);

        return $styleExtension;
    }
}
