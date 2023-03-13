<?php

namespace Console\Commands\Make\Template;

abstract class Template implements TemplateInterface
{
    protected array $tokens = [];

    public const namespace = '%NAMESPACE%';
    public const entityName = '%ENTITY_NAME%';


    public function setTokens(array $tokens): void
    {
        $this->tokens = $tokens;
    }

    public function render(): string
    {
        if ($this->tokens === []) throw new \RuntimeException('Empty tokens!');
        if ($this->tokens[self::namespace] !== null) {
            $this->tokens[self::namespace] = PHP_EOL . 'namespace ' . $this->tokens[self::namespace] . ';';
        } else $this->tokens[self::namespace] = '';

        $replace = array_map(
            static fn($token) => $token instanceof TemplateInterface ? $token->render() : (string) $token,
            array_values($this->tokens)
        );

        return str_replace(array_keys($this->tokens), $replace, $this->getBaseTemplate());
    }

    abstract protected function getBaseTemplate(): string ;
}