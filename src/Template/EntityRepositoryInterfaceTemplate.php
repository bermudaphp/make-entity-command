<?php

namespace Console\Commands\Make\Template;

class EntityRepositoryInterfaceTemplate extends Template
{
    public const entityVarName = '%ENTITY_VAR_NAME%';

    protected function getBaseTemplate(): string
    {
        return <<<'EOD'
<?php
%NAMESPACE%;

interface %ENTITY_NAME%RepositoryInterface
{
    /**
     * @param %ENTITY_NAME% $%ENTITY_VAR_NAME%
     * @param bool $cascade
     * @return %ENTITY_NAME%
     * @throws \DomainException
     */
    public function persist(%ENTITY_NAME% $%ENTITY_VAR_NAME%, bool $cascade = true): %ENTITY_NAME% ;

    /**
     * @param %ENTITY_NAME% $%ENTITY_VAR_NAME%
     * @param bool $cascade
     * @return %ENTITY_NAME%
     * @throws \DomainException
     */
    public function delete(%ENTITY_NAME% $%ENTITY_VAR_NAME%, bool $cascade = true): void ;
}
EOD;
    }
}