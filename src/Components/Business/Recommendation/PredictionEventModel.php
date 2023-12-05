<?php

declare(strict_types=1);

namespace Flexzt\PredictionRecommendation\Components\Business\Recommendation;

use Flexzt\PredictionRecommendation\Components\Business\Config\ConfigModel;
use Flexzt\PredictionRecommendation\Components\Business\Recommendation\Event\EngineClient;
use Flexzt\PredictionRecommendation\Components\Business\Recommendation\Event\EventClient;

class PredictionEventModel implements PredictionEventModelInterface
{
    public function __construct(protected ConfigModel $configModel) { }


    public function getEventClient(?string $salesChannelId = null): EventClient
    {
        $pluginConfig = $this->configModel->getConfig($salesChannelId);

        return new EventClient($pluginConfig->getAccessKey(), $pluginConfig->getServiceImportHost());
    }

    public function getEngineClient(?string $salesChannelId = null): EngineClient
    {
        $pluginConfig = $this->configModel->getConfig($salesChannelId);

        return new EngineClient($pluginConfig->getServiceHost());
    }
}
