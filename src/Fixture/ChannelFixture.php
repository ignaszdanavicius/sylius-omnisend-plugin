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

namespace NFQ\SyliusOmnisendPlugin\Fixture;

use NFQ\SyliusOmnisendPlugin\Model\ChannelOmnisendTrackingAwareInterface;
use Sylius\Bundle\FixturesBundle\Fixture\AbstractFixture;
use Sylius\Bundle\FixturesBundle\Fixture\FixtureInterface;
use Sylius\Component\Channel\Model\ChannelInterface;
use Sylius\Component\Channel\Repository\ChannelRepositoryInterface;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;

class ChannelFixture extends AbstractFixture implements FixtureInterface
{
    /** @var ChannelRepositoryInterface */
    private $channelRepository;

    public function __construct(ChannelRepositoryInterface $channelRepository)
    {
        $this->channelRepository = $channelRepository;
    }

    public function getName(): string
    {
        return 'omnisend_channel';
    }

    public function load(array $options): void
    {
        foreach ($options['custom'] as $item) {
            if (isset($item['channel_code'])) {
                /** @var ChannelInterface|ChannelOmnisendTrackingAwareInterface|null $channel */
                $channel = $this->channelRepository->findOneByCode($item['channel_code']);

                if (null !== $channel) {
                    $channel->setOmnisendApiKey($item['api_key']);
                    $channel->setOmnisendTrackingKey($item['tracking_key']);
                    $this->channelRepository->add($channel);
                }
            }
        }
    }

    protected function configureOptionsNode(ArrayNodeDefinition $optionsNode): void
    {
        $optionsNode
            ->children()
                ->arrayNode('custom')
                    ->requiresAtLeastOneElement()
                    ->arrayPrototype()
                        ->children()
                            ->scalarNode('channel_code')->cannotBeEmpty()->end()
                            ->scalarNode('api_key')->cannotBeEmpty()->end()
                            ->scalarNode('tracking_key')->cannotBeEmpty()->end()
                        ->end()
                    ->end()
                ->end()
            ->end();
    }
}
