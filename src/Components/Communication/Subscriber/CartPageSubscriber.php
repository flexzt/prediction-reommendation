<?php

declare(strict_types=1);

namespace Flexzt\PredictionRecommendation\Components\Communication\Subscriber;


use Flexzt\PredictionRecommendation\Components\Business\Recommendation\Event\PredictionIOAPIError;
use Flexzt\PredictionRecommendation\Components\Business\Recommendation\PredictionEventModelInterface;
use Flexzt\PredictionRecommendation\Components\Business\Recommendation\PredictionModelInterface;
use Flexzt\PredictionRecommendation\Components\Shared\CartPredictionProductsConstants;
use Flexzt\PredictionRecommendation\Components\Shared\Struct\IdsCollection;
use Shopware\Storefront\Page\Checkout\Cart\CheckoutCartPageLoadedEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class CartPageSubscriber implements EventSubscriberInterface
{
    public function __construct(
        protected PredictionEventModelInterface $predictionEventModel,
        protected PredictionModelInterface $predictionModel
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            CheckoutCartPageLoadedEvent::class => 'onCheckoutCartPageLoaded'
        ];
    }

    public function onCheckoutCartPageLoaded(CheckoutCartPageLoadedEvent $event): void
    {
        $customer = $event->getSalesChannelContext()->getCustomer();

        if (!$customer) {
            return;
        }

        $config = $this->predictionModel->getConfig($event->getSalesChannelContext()->getSalesChannelId());

        try {
            $predictedScope = $this->predictionEventModel->getEngineClient()
                ->sendQuery(
                    ['user' => $customer->getId(), 'num' => $config->getSearchQueryLimit()]
                );
        } catch (PredictionIOAPIError $e) {
            $predictedScope = [];
        }

        if (!$predictedScope) {
            return;
        }

        $predictedProducts = $this->predictionModel->getRelatedProducts(
            IdsCollection::fetchPredictedScope($predictedScope),
            $config->getSearchQueryLimit(),
            $event->getSalesChannelContext()
        );

        $event->getPage()
            ->assign([CartPredictionProductsConstants::EXTENSION_PREDICTED_PRODUCTS => $predictedProducts]);
    }
}
