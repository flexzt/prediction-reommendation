<?php

declare(strict_types=1);

namespace Flexzt\PredictionRecommendation\Components\Business\Config;


use Flexzt\PredictionRecommendation\Components\Struct\Config\ConfigStruct;

interface ConfigModelInterface
{
    public const CONFIG_PATH = 'PredictionRecommendation.config';

    public function getConfig(?string $salesChannelId = null): ConfigStruct;
}
