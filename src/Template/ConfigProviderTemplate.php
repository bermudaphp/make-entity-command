<?php

namespace Console\Commands\Make\Template;

class ConfigProviderTemplate extends Template
{
    protected function getBaseTemplate(): string
    {
        return <<<'EOD'
<?php
%NAMESPACE%;

use Cycle\ORM\ORMInterface;
use Psr\Container\ContainerInterface;

class ConfigProvider extends \Bermuda\Config\ConfigProvider
{
    protected function getFactories(): array
    {
        return [
            %ENTITY_NAME%RepositoryInterface::class => static function(ContainerInterface $container) {
                return $container->get(ORMInterface::class)->getRepository(%ENTITY_NAME%::class);
            }
        ];
    }

    protected function getAliases(): array
    {
        return [
            %ENTITY_NAME%FactoryInterface::class => %ENTITY_NAME%RepositoryInterface::class
        ];
    }
}
EOD;
    }
}
