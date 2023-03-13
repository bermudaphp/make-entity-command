<?php

namespace Console\Commands\Make\Template;

class EntityRepositoryTemplate extends Template
{
    public const entityVarName = '%ENTITY_VAR_NAME%';
    protected function getBaseTemplate(): string
    {
        return <<<'EOD'
<?php
%NAMESPACE%;

use Bermuda\Cycle\Repository;

class %ENTITY_NAME%Repository extends Repository implements %ENTITY_NAME%RepositoryInterface, %ENTITY_NAME%FactoryInterface
{
    /**
     * @inerhitDoc
     */
    public function make%ENTITY_NAME%(%ENTITY_NAME%Dto $dto): %ENTITY_NAME%
    {
        return $this->orm->make(%ENTITY_NAME%::class, $dto->toArray());
    }

    /**
     * @inerhitDoc
     */
    public function persist(%ENTITY_NAME% $%ENTITY_VAR_NAME%, bool $cascade = true): %ENTITY_NAME%
    {
        $this->entityManager->persist($%ENTITY_VAR_NAME%, $cascade)->run();
        return $%ENTITY_VAR_NAME%;
    }

    /**
     * @inerhitDoc
     */
    public function delete(%ENTITY_NAME% $%ENTITY_VAR_NAME%, bool $cascade = true): void
    {
        $this->entityManager->delete($%ENTITY_VAR_NAME%, $cascade)->run();
    }
}
EOD;
    }
}