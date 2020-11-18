<?php

/*
 * @copyright C UAB NFQ Technologies
 *
 * This Software is the property of NFQ Technologies
 * and is protected by copyright law – it is NOT Freeware.
 *
 * Any unauthorized use of this software without a valid license key
 * is a violation of the license agreement and will be prosecuted by
 * civil and criminal law.
 *
 * Contact UAB NFQ Technologies:
 * E-mail: info@nfq.lt
 * http://www.nfq.lt
 */

declare(strict_types=1);

namespace Tests\NFQ\SyliusOmnisendPlugin\Mock;

use NFQ\SyliusOmnisendPlugin\Model\OrderInterface;
use NFQ\SyliusOmnisendPlugin\Model\OrderTrait;
use Sylius\Component\Core\Model\Order;

class OrderMock extends Order implements OrderInterface
{
    use OrderTrait;
}
