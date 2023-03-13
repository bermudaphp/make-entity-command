<?php

namespace Console\Commands\Make\Template;

class EntityFactoryInterfaceTemplate extends Template
{
    protected function getBaseTemplate(): string
    {
        return <<<'EOD'
<?php 
%NAMESPACE%

interface %ENTITY_NAME%FactoryInterface
{
    /**
     * @param %ENTITY_NAME%Dto $dto
     * @return %ENTITY_NAME%
     * @throws EntityFactoryException
     */
    public function make%ENTITY_NAME%(%ENTITY_NAME%Dto $dto): %ENTITY_NAME% ;
}
EOD;
    }
}