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

namespace NFQ\SyliusOmnisendPlugin\Model;

use Sylius\Component\Core\Model\OrderInterface as BaseOrderInterface;
use DateTimeInterface;

interface OrderInterface extends BaseOrderInterface, OmnisendCartAwareInterface
{
    public function getCancelledAt(): ?DateTimeInterface;

    public function setCancelledAt(?DateTimeInterface $dateTime): void;
}
