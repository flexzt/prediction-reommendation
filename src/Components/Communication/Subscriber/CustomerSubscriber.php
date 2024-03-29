<?php

declare(strict_types=1);

namespace Flexzt\PredictionRecommendation\Components\Communication\Subscriber;

use Flexzt\PredictionRecommendation\Components\Business\Recommendation\PredictionEventModelInterface;
use Flexzt\PredictionRecommendation\Components\Business\Recommendation\PredictionModelInterface;
use Shopware\Core\Checkout\Customer\Event\CustomerLoginEvent;
use Shopware\Storefront\Page\Product\ProductPageLoadedEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class CustomerSubscriber implements EventSubscriberInterface
{

    public function __construct(
        protected PredictionEventModelInterface $predictionEventModel,
        protected PredictionModelInterface $predictionModel
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            CustomerLoginEvent::class => 'onCustomerLogin',
            ProductPageLoadedEvent::class => 'onProductPageLoaded',
        ];
    }

    public function onCustomerLogin(CustomerLoginEvent $event): void
    {
        $location = $event->getSalesChannelContext()->getShippingLocation();

        try {
            $this->predictionEventModel->getEventClient($event->getSalesChannelId())->setUser(
                $event->getCustomer()->getId(),
                [
                    'countryISO' => $location->getCountry()->getIso(),
                    'city'       => $location->getAddress()->getCity(),
                ]
            );
        } catch (\Exception $e) {
        }
    }

    public function onProductPageLoaded(ProductPageLoadedEvent $event): void
    {
        $salesChannelContext = $event->getSalesChannelContext();

        if (!$salesChannelContext->getCustomer()) {
            return;
        }

        $product = $event->getPage()->getProduct();

        $configuration = $this->predictionModel->loadProductSettings(
            $product->getId(),
            $event->getContext()
        );
        $optionValues = $this->predictionModel->getConfigurationValues($configuration);

        try {
            $this->predictionEventModel->getEventClient($salesChannelContext->getSalesChannelId())
                ->recordUserActionOnItem(
                    'view',
                    $salesChannelContext->getCustomerId(),
                    $product->getId(),
                    [
                        'itemType' => 'product',
                        'variant'  => $product->getProductNumber(),
                        ...$optionValues
                    ]
                );
        } catch (\Exception $e) {
        }
    }
}
