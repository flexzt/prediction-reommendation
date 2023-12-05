<?php

declare(strict_types=1);

namespace Flexzt\PredictionRecommendation\Components\Business\Recommendation;

use Flexzt\PredictionRecommendation\Components\Business\Config\ConfigModel;
use Flexzt\PredictionRecommendation\Components\Shared\Struct\IdsCollection;
use Flexzt\PredictionRecommendation\Components\Struct\Config\ConfigStruct;
use Shopware\Core\Checkout\Cart\Cart;
use Shopware\Core\Checkout\Cart\LineItem\LineItem;
use Shopware\Core\Content\Product\Aggregate\ProductConfiguratorSetting\ProductConfiguratorSettingEntity;
use Shopware\Core\Content\Product\Cart\ProductGateway;
use Shopware\Core\Content\Product\ProductCollection;
use Shopware\Core\Content\Property\Aggregate\PropertyGroupOption\PropertyGroupOptionCollection;
use Shopware\Core\Content\Property\Aggregate\PropertyGroupOption\PropertyGroupOptionEntity;
use Shopware\Core\Content\Property\PropertyGroupCollection;
use Shopware\Core\Content\Property\PropertyGroupDefinition;
use Shopware\Core\Content\Property\PropertyGroupEntity;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Exception\InconsistentCriteriaIdsException;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\System\SalesChannel\SalesChannelContext;


class RecommendationModel implements PredictionModelInterface
{
    public function __construct(
        protected ConfigModel $configModel,
        private readonly EntityRepositoryInterface $configuratorRepository,
        private readonly ProductGateway $productGateway
    ) {
    }

    /**
     * @return array<string, PropertyGroupEntity>|null
     * @throws InconsistentCriteriaIdsException
     *
     */
    public function loadProductSettings(string $productId, Context $context): PropertyGroupCollection
    {
        return $this->sortSettings($this->loadSettings($productId, $context));
    }


    private function loadSettings(string $productId, Context $context): ?array
    {
        $criteria = (new Criteria())->addFilter(
            new EqualsFilter('productId', $productId)
        );

        $criteria->addAssociation('option.group')
            ->addAssociation('option.media')
            ->addAssociation('media');

        $settings = $this->configuratorRepository->search($criteria, $context)->getEntities();

        if ($settings->count() <= 0) {
            return null;
        }
        $groups = [];

        /** @var ProductConfiguratorSettingEntity $setting */
        foreach ($settings as $setting) {
            $option = $setting->getOption();
            if ($option === null) {
                continue;
            }

            $group = $option->getGroup();
            if ($group === null) {
                continue;
            }

            $groupId = $group->getId();

            if (isset($groups[$groupId])) {
                $group = $groups[$groupId];
            }

            $groups[$groupId] = $group;

            $options = $group->getOptions();
            if ($options === null) {
                $options = new PropertyGroupOptionCollection();
                $group->setOptions($options);
            }
            $options->add($option);

            $option->setConfiguratorSetting($setting);
        }

        return $groups;
    }

    /**
     * @param array<string, PropertyGroupEntity>|null $groups
     */
    private function sortSettings(?array $groups): PropertyGroupCollection
    {
        if (!$groups) {
            return new PropertyGroupCollection();
        }

        $sorted = [];
        foreach ($groups as $group) {
            if (!$group) {
                continue;
            }

            if (!$group->getOptions()) {
                $group->setOptions(new PropertyGroupOptionCollection());
            }

            $sorted[$group->getId()] = $group;
        }

        /** @var PropertyGroupEntity $group */
        foreach ($sorted as $group) {
            $options = $group->getOptions();
            if ($options === null) {
                continue;
            }
            $options->sort(
                static function (PropertyGroupOptionEntity $a, PropertyGroupOptionEntity $b) use ($group) {
                    $configuratorSettingA = $a->getConfiguratorSetting();
                    $configuratorSettingB = $b->getConfiguratorSetting();

                    if ($configuratorSettingA !== null && $configuratorSettingB !== null
                        && $configuratorSettingA->getPosition() !== $configuratorSettingB->getPosition()) {
                        return $configuratorSettingA->getPosition() <=> $configuratorSettingB->getPosition();
                    }

                    if ($group->getSortingType() === PropertyGroupDefinition::SORTING_TYPE_ALPHANUMERIC) {
                        return strnatcmp($a->getTranslation('name'), $b->getTranslation('name'));
                    }

                    return ($a->getTranslation('position') ?? $a->getPosition() ?? 0) <=> ($b->getTranslation(
                            'position'
                        ) ?? $b->getPosition() ?? 0);
                }
            );
        }

        return new PropertyGroupCollection($sorted);
    }

    public function getConfig(?string $salesChannelId = null): ConfigStruct
    {
        return $this->configModel->getConfig($salesChannelId);
    }

    public function getConfigurationValues(PropertyGroupCollection $configuration): array
    {
        $optionValueArray = [];
        $optionValueArray['optionGroup'] = $configuration->getIds();

        foreach ($configuration->getElements() as $group) {
            foreach ($group->getOptions() as $option) {
                $optionValueArray['optionValues'][] = $option->getName();
            }
        }

        return $optionValueArray;
    }

    public function getCartLineItemsActiveReferenceIds(Cart $cart): IdsCollection
    {
        return new IdsCollection(
            $cart->getLineItems()->filterType(LineItem::PRODUCT_LINE_ITEM_TYPE)->getReferenceIds()
        );
    }

    public function getRelatedProducts(
        IdsCollection $idsCollection,
        int $limit,
        SalesChannelContext $salesChannelContext
    ): ProductCollection {
        $productCollection = new ProductCollection();

        if ($idsCollection->count() === 0) {
            return $productCollection;
        }

        $productCollection = $this->productGateway->get($idsCollection->getElements(), $salesChannelContext);

        if ($productCollection->count() < $limit) {
            return $productCollection;
        }

        return $productCollection->slice(0, $limit);
    }
}
