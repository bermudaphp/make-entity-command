<?php

namespace Console\Commands\Make\Template;

class EntityDtoTemplate extends Template
{
    public const properties = '%PROPERTIES%';
    public const constants = '%CONSTANTS%';
    public const vars = '%VARS%';

    protected function getBaseTemplate(): string
    {
        return <<<'EOD'
<?php
%NAMESPACE%;

use Bermuda\Dto\DtoInterface;

class %ENTITY_NAME%Dto implements DtoInterface
{
%CONSTANTS%
    public function __construct(%PROPERTIES%
    ) {
    }
    
    public function toArray(): array
    {
        return filter_null([
            %VARS%
        ]);
    }
}
EOD;
    }
}