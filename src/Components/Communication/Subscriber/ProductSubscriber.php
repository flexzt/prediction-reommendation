<?php

declare(strict_types=1);

namespace Flexzt\PredictionRecommendation\Components\Communication\Subscriber;

use Flexzt\PredictionRecommendation\Components\Business\Recommendation\PredictionEventModelInterface;
use Flexzt\PredictionRecommendation\Components\Business\Recommendation\PredictionModelInterface;
use Shopware\Core\Content\Product\Events\ProductIndexerEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ProductSubscriber implements EventSubscriberInterface
{
    public function __construct(
        protected PredictionEventModelInterface $predictionEventModel,
        protected PredictionModelInterface $predictionModel
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            ProductIndexerEvent::class => 'onProductIndexing'
        ];
    }

    public function onProductIndexing(ProductIndexerEvent $event): void
    {
        $eventClient = $this->predictionEventModel->getEventClient();

        foreach ($event->getIds() as $id) {
//            $eventClient->deleteItem($id);
            $eventClient->setItem(
                $id,
                [
                    'itemType' => 'product',
                ]
            );
        }
    }
}
