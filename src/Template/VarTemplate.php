<?php

namespace Console\Commands\Make\Template;

final class VarTemplate implements TemplateInterface
{
    public function __construct(public readonly string $name)
    {
    }

    public function render(): string
    {
        return "\$$this->name";
    }
}