<?php

declare(strict_types=1);

namespace Flexzt\PredictionRecommendation\Components\Shared\Struct;

use Shopware\Core\Framework\Struct\Collection;

class IdsCollection extends Collection
{
    public static function fetchPredictedScope(array $predictedScope): self
    {
        $ids = [];

        foreach ($predictedScope['itemScores'] as $element) {
            $ids[] = $element['item'];
        }

        return new self($ids);
    }
}
