<?php

declare(strict_types=1);

namespace Flexzt\PredictionRecommendation\Components\Struct\Config;

use Shopware\Core\Framework\Struct\Struct;

class ConfigStruct extends Struct
{
    protected string $accessKey = '';
    protected ?string $salesChannelId = '';
    protected string $serviceHost = '';
    protected string $serviceImportHost = '';

    public function getAccessKey(): string
    {
        return $this->accessKey;
    }

    public function setAccessKey(string $accessKey): void
    {
        $this->accessKey = $accessKey;
    }

    public function getServiceHost(): string
    {
        return $this->serviceHost;
    }

    public function setServiceHost(string $serviceHost): void
    {
        $this->serviceHost = $serviceHost;
    }

    public function getSalesChannelId(): ?string
    {
        return $this->salesChannelId;
    }

    public function setSalesChannelId(?string $salesChannelId): void
    {
        $this->salesChannelId = $salesChannelId;
    }

    public function getServiceImportHost(): string
    {
        return $this->serviceImportHost;
    }

    public function setServiceImportHost(string $serviceImportHost): void
    {
        $this->serviceImportHost = $serviceImportHost;
    }
}
