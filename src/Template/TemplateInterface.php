<?php

namespace Console\Commands\Make\Template;

interface TemplateInterface
{
    public function render(): string;
}