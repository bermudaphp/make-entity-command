<?php

namespace Console\Commands\Make\Template;

final class TemplateCollection implements TemplateInterface
{
    /**
     * @var ConstantTemplate[]
     */
    private array $templates = [];
    public function __construct(array $templates = [], private readonly ?string $concreteTemplate = null)
    {
        foreach ($templates as $template) $this->addTemplate($template);
    }

    public function addTemplate(TemplateInterface $template): void
    {
        if ($this->concreteTemplate && !$template instanceof $this->concreteTemplate) {
            throw new \InvalidArgumentException('Argument #1 ($template) must be instance of '.$this->concreteTemplate);
        }

        $this->templates[] = $template;
    }

    public function render(): string
    {
        $str = '';
        foreach ($this->templates as $template) $str .= $template->render() . PHP_EOL;
        return $str;
    }

    public function __clone(): void
    {
        $templates = $this->templates;
        $this->templates = [];
        foreach ($templates as $template) $this->addTemplate(clone $template);
    }

    public function each(callable $callback): self
    {
        foreach ($this->templates as $template) $callback($template);
        return $this;
    }
}