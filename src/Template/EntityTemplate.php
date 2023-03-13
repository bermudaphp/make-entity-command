<?php

namespace Console\Commands\Make\Template;

class EntityTemplate extends Template
{
    public const properties = '%PROPERTIES%';
    public const vars = '%PROPERTIES%';
    protected function getBaseTemplate(): string
    {
        return <<<'EOD'
<?php 
%NAMESPACE%;

use Bermuda\Stdlib\Arrayable;

class %ENTITY_NAME% implements Arrayable
{
%PROPERTIES%

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