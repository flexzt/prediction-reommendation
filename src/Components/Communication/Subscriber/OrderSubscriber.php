<?php

declare(strict_types=1);

namespace Flexzt\PredictionRecommendation\Components\Communication\Subscriber;

use Flexzt\PredictionRecommendation\Components\Business\Recommendation\PredictionEventModelInterface;
use Flexzt\PredictionRecommendation\Components\Business\Recommendation\PredictionModelInterface;
use Shopware\Core\Checkout\Cart\Event\BeforeLineItemAddedEvent;
use Shopware\Core\Checkout\Cart\Event\CheckoutOrderPlacedEvent;
use Shopware\Core\Checkout\Cart\LineItem\LineItem;
use Shopware\Core\Checkout\Order\Aggregate\OrderCustomer\OrderCustomerEntity;
use Shopware\Core\Checkout\Order\Aggregate\OrderLineItem\OrderLineItemCollection;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class OrderSubscriber implements EventSubscriberInterface
{
    public function __construct(
        protected PredictionEventModelInterface $predictionEventModel,
        protected PredictionModelInterface $predictionModel
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            CheckoutOrderPlacedEvent::class => 'onOrderPlaced',
            BeforeLineItemAddedEvent::class => 'onLineItemAdded'
        ];
    }

    public function onOrderPlaced(CheckoutOrderPlacedEvent $event): void
    {
        /** @var OrderCustomerEntity $customer */
        $customer = $event->getOrder()->getOrderCustomer();
        /** @var OrderLineItemCollection $lineItems */
        $lineItems = $event->getOrder()->getLineItems();
        $eventClient = $this->predictionEventModel->getEventClient($event->getSalesChannelId());

        $products = $lineItems->filterByType(LineItem::PRODUCT_LINE_ITEM_TYPE);

        foreach ($products as $purchasedProduct) {
            $eventClient->recordUserActionOnItem(
                'purchase',
                $customer->getCustomerId(),
                $purchasedProduct->getReferencedId(),
                [
                    'itemType' => 'product',
                    'variant'  => $purchasedProduct->getPayload()['productNumber']
                ]
            );
        }
    }

    public function onLineItemAdded(BeforeLineItemAddedEvent $event): void
    {
        $salesChannelContext = $event->getSalesChannelContext();

        if (!$salesChannelContext->getCustomer()) {
            return;
        }

        $lineItem = $event->getLineItem();

        if ($lineItem->getType() !== LineItem::PRODUCT_LINE_ITEM_TYPE) {
            return;
        }

        $eventClient = $this->predictionEventModel->getEventClient(
            $salesChannelContext->getSalesChannelId()
        );

        $eventClient->recordUserActionOnItem(
            'add-to-cart',
            $salesChannelContext->getCustomerId(),
            $event->getLineItem()->getReferencedId(),
            [
                'itemType' => 'product',
            ]
        );
    }
}
