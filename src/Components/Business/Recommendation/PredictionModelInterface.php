<?php

declare(strict_types=1);

namespace Flexzt\PredictionRecommendation\Components\Business\Recommendation;


use Flexzt\PredictionRecommendation\Components\Shared\Struct\IdsCollection;
use Flexzt\PredictionRecommendation\Components\Struct\Config\ConfigStruct;
use Shopware\Core\Checkout\Cart\Cart;
use Shopware\Core\Content\Product\ProductCollection;
use Shopware\Core\Content\Property\PropertyGroupCollection;
use Shopware\Core\Framework\Context;
use Shopware\Core\System\SalesChannel\SalesChannelContext;

interface PredictionModelInterface
{
    public function loadProductSettings(string $productId, Context $context): PropertyGroupCollection;

    public function getConfig(?string $salesChannelId = null): ConfigStruct;

    public function getConfigurationValues(PropertyGroupCollection $configuration): array;

    public function getCartLineItemsActiveReferenceIds(Cart $cart): IdsCollection;

    public function getRelatedProducts(
        IdsCollection $idsCollection,
        int $limit,
        SalesChannelContext $salesChannelContext
    ): ProductCollection;
}
