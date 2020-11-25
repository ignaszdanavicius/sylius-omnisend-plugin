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

namespace NFQ\SyliusOmnisendPlugin\EventSubscriber;

use NFQ\SyliusOmnisendPlugin\Message\Command\DeleteCart;
use NFQ\SyliusOmnisendPlugin\Message\Command\UpdateCart;
use NFQ\SyliusOmnisendPlugin\Model\OrderDetails;
use NFQ\SyliusOmnisendPlugin\Resolver\ContactIdResolverInterface;
use Sylius\Bundle\ResourceBundle\Event\ResourceControllerEvent;
use Sylius\Component\Core\Model\ChannelInterface;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Order\Context\CartContextInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;

class CartSubscriber implements EventSubscriberInterface
{
    /** @var MessageBusInterface */
    private $messageBus;

    /** @var ContactIdResolverInterface */
    private $contactIdResolver;

    /** @var CartContextInterface */
    private $cartContext;

    public function __construct(
        MessageBusInterface $messageBus,
        CartContextInterface $cartContext,
        ContactIdResolverInterface $contactIdResolver
    ) {
        $this->messageBus = $messageBus;
        $this->cartContext = $cartContext;
        $this->contactIdResolver = $contactIdResolver;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            'sylius.order_item.post_add' => 'onOrderItemChange',
            'sylius.order_item.post_remove' => 'onOrderItemChange',
            'sylius.order.post_update' => 'onUpdate',
            'sylius.cart_change' => 'onCartChange',
            'sylius.carts.post_remove' => 'onCartsRemove',
            'sylius.order.pre_delete' => 'onCartRemove',
        ];
    }

    public function onOrderItemChange(ResourceControllerEvent $event): void
    {
        /** @var \NFQ\SyliusOmnisendPlugin\Model\OrderInterface $cart */
        $cart = $this->cartContext->getCart();
        /** @var ChannelInterface $channel */
        $channel = $cart->getChannel();

        $this->messageBus->dispatch(
            new Envelope(
                new UpdateCart(
                    $cart->getId(),
                    $this->contactIdResolver->resolve($cart),
                    $channel->getCode()
                )
            )
        );
    }

    public function updateOrder(OrderInterface $order): void
    {
        /** @var ChannelInterface $channel */
        $channel = $order->getChannel();

        if ($order->getId()) {
            $this->messageBus->dispatch(
                new Envelope(
                    new UpdateCart(
                        $order->getId(),
                        $this->contactIdResolver->resolve($order),
                        $channel->getCode()
                    )
                )
            );
        }
    }

    public function onUpdate(ResourceControllerEvent $event): void
    {
        /** @var \NFQ\SyliusOmnisendPlugin\Model\OrderInterface $order */
        $order = $event->getSubject();
        /** @var ChannelInterface $channel */
        $channel = $order->getChannel();

        if ($order->getState() === OrderInterface::STATE_CART) {
            $this->messageBus->dispatch(
                new Envelope(
                    new UpdateCart(
                        $order->getId(),
                        $this->contactIdResolver->resolve($order),
                        $channel->getCode()
                    )
                )
            );
        }
    }

    public function onCartsRemove(GenericEvent $event): void
    {
        /** @var OrderInterface[] $carts */
        $carts = $event->getSubject();

        foreach ($carts as $cart) {
            /** @var ChannelInterface $channel */
            $channel = $cart->getChannel();
            /** @var OrderDetails $details */
            $details = $cart->getOmnisendOrderDetails();

            if (null !== $details->getCartId()) {
                $this->messageBus->dispatch(
                    new Envelope(
                        new DeleteCart(
                            $details->getCartId(),
                            $channel->getCode()
                        )
                    )
                );
            }
        }
    }

    public function onCartChange(GenericEvent $event): void
    {
        /** @var \NFQ\SyliusOmnisendPlugin\Model\OrderInterface $order */
        $order = $event->getSubject();
        /** @var ChannelInterface $channel */
        $channel = $order->getChannel();

        $this->messageBus->dispatch(
            new Envelope(
                new UpdateCart(
                    $order->getId(),
                    $this->contactIdResolver->resolve($order),
                    $channel->getCode()
                )
            )
        );
    }

    public function onCartRemove(ResourceControllerEvent $event): void
    {
        /** @var OrderInterface $cart */
        $cart = $event->getSubject();
        /** @var ChannelInterface $channel */
        $channel = $cart->getChannel();
        /** @var OrderDetails $details */
        $details = $cart->getOmnisendOrderDetails();

        if ($cart->getState() === OrderInterface::STATE_CART) {
            if (null !== $details->getCartId()) {
                $this->messageBus->dispatch(
                    new Envelope(
                        new DeleteCart(
                            $details->getCartId(),
                            $channel->getCode()
                        )
                    )
                );
            }
        }
    }
}
