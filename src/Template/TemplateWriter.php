<?php

namespace Console\Commands\Make\Template;

use Bermuda\Stdlib\ClsHelper;
use Webimpress\SafeWriter\FileWriter;

class TemplateWriter
{
    public function __construct(private ?string $path = null)
    {
        if (!$this->path) $this->path = getcwd() . '/src';
    }

    public function write(string $cls, TemplateInterface $template): void
    {
        $path = $this->path;
        if (($segments = ClsHelper::namespace($cls)) !== null) {
            foreach (explode('\\', $segments) as $dir) {
                if (!is_dir($path .= "\\$dir")) {
                    if (!mkdir($path)) throw new \RuntimeException("Unable to create directory: $path");
                }
            }
        }

        FileWriter::writeFile("$path\\".ClsHelper::basename($cls).".php", $template->render());
    }
}