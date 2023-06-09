<?php

declare(strict_types=1);

namespace Flexzt\PredictionRecommendation\Components\Business\Recommendation;

use Flexzt\PredictionRecommendation\Components\Business\Config\ConfigModel;


class RecommendationModel implements PredictionModelInterface
{
    public function __construct(protected ConfigModel $configModel) { }
}
