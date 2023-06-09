<?php

declare(strict_types=1);

namespace Flexzt\PredictionRecommendation\Components\Business\Recommendation;

use Flexzt\PredictionRecommendation\Components\Business\Recommendation\Event\EventClient;

interface PredictionEventModelInterface
{
    public function getEventClient(?string $salesChannelId = null): EventClient;
}
