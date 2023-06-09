<?php

declare(strict_types=1);

namespace Flexzt\PredictionRecommendation\Components\Business\Config;

use Shopware\Core\System\SystemConfig\SystemConfigService;
use Flexzt\PredictionRecommendation\Components\Struct\Config\ConfigStruct;


class ConfigModel implements ConfigModelInterface
{
    /** @var array<ConfigStruct> $config */
    protected static array $config = [];

    public function __construct(private readonly SystemConfigService $systemConfigService)
    {
    }

    public function getConfig(?string $salesChannelId = null): ConfigStruct
    {
        if (!isset(static::$config[$salesChannelId])) {
            $configStruct = (new ConfigStruct())->assign(
                (array)$this->systemConfigService->get(self::CONFIG_PATH, $salesChannelId)
            );
            $configStruct->setSalesChannelId($salesChannelId);

            static::$config[$salesChannelId] = $configStruct;
        }

        return static::$config[$salesChannelId];
    }
}
