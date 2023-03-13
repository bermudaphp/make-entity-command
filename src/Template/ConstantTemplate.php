<?php

namespace Console\Commands\Make\Template;

class ConstantTemplate implements TemplateInterface
{
    public function __construct(
        public readonly string $name,
        public readonly string $value
    ) {
    }

    public function render(): string
    {
        return "    public const $this->name = '$this->value';";
    }
}