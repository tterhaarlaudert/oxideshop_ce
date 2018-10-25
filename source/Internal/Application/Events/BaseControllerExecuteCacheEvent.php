<?php
declare(strict_types=1);

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\EshopCommunity\Internal\Application\Events;

use Symfony\Component\EventDispatcher\Event;

/**
 * Class BaseControllerExecuteCacheEvent
 *
 * @package OxidEsales\EshopCommunity\Internal\Application\Events
 */
class BaseControllerExecuteCacheEvent extends ExecuteCacheEvent
{
    const NAME = 'oxidesales.listcomponentajax.executeCache';

    /**
     * Handle event.
     *
     * @return null
     */
    public function handleEvent()
    {

    }
}
