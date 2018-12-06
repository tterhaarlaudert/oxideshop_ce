<?php
/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\EshopCommunity\Tests\Unit\Internal\Twig\Extensions\Filters;

use OxidEsales\EshopCommunity\Internal\Adapter\TemplateLogic\TranslateSalutationLogic;
use OxidEsales\EshopCommunity\Internal\Twig\Extensions\Filters\TranslateSalutationExtension;
use OxidEsales\EshopCommunity\Tests\Unit\Internal\Twig\Extensions\AbstractExtensionTest;

/**
 * Class TranslateSalutationExtensionTest
 *
 * @author Tomasz Kowalewski (t.kowalewski@createit.pl)
 */
class TranslateSalutationExtensionTest extends AbstractExtensionTest
{

    /** @var TranslateSalutationExtension */
    protected $extension;

    protected function setUp(): void
    {
        parent::setUp();
        $this->extension = new TranslateSalutationExtension(new TranslateSalutationLogic());
    }

    public function translateSalutationProvider(): array
    {
        return [
            ["{{ 'MR'|translate_salutation }}", 'Herr'],
            ["{{ 'MRS'|translate_salutation }}", 'Frau'],
        ];
    }

    /**
     * @param string $template
     * @param string $expected
     *
     * @dataProvider translateSalutationProvider
     */
    public function testTranslateSalutation(string $template, string $expected): void
    {
        $this->assertEquals($expected, $this->getTemplate($template)->render([]));
    }
}