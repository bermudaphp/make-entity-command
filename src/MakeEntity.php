<?php

namespace Console\Commands\Make;

use Bermuda\Stdlib\ClsHelper;
use Console\Commands\Command;
use Console\Commands\Make\Template\ConfigProviderTemplate;
use Console\Commands\Make\Template\ConstantTemplate;
use Console\Commands\Make\Template\EntityDtoTemplate;
use Console\Commands\Make\Template\EntityFactoryInterfaceTemplate;
use Console\Commands\Make\Template\EntityRepositoryInterfaceTemplate;
use Console\Commands\Make\Template\EntityRepositoryTemplate;
use Console\Commands\Make\Template\EntityTemplate;
use Console\Commands\Make\Template\PropertyTemplate;
use Console\Commands\Make\Template\TemplateCollection;
use Console\Commands\Make\Template\TemplateWriter;
use Console\Commands\Make\Template\VarTemplate;
use Console\Commands\Provider\Push;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Exception\InvalidArgumentException;

class MakeEntity extends Command
{
    private ?TemplateWriter $writer = null;
    public function getName(): string
    {
        return 'make:entity';
    }

    protected function configure()
    {
        $this->addArgument('name', InputArgument::REQUIRED);
        $this->addArgument('props', InputArgument::IS_ARRAY);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $entityName = ucfirst($input->getArgument('name'));
        if (!ClsHelper::isValidName($entityName)) {
            throw new InvalidArgumentException("Invalid Entity name [$entityName]");
        }

        $props = $input->getArgument('props');

        if ($props != []) {
            $tokens = [];

            $tokens['properties'] = $properties = new TemplateCollection;
            $tokens['constants'] = $constants = new TemplateCollection;
            $tokens['vars'] = $vars = new TemplateCollection;

            foreach ($props as $prop) {
                $properties->addTemplate($prop = PropertyTemplate::parse($prop));
                $constants->addTemplate(new ConstantTemplate($prop->getName(), $prop->getName()));
                $vars->addTemplate(new VarTemplate($prop->getName()));
            }
        }

        $this->writeEntityTemplate($entityName, $tokens ?? null, $output);
        $this->writeEntityDtoTemplate($entityName, $tokens ?? null, $output);
        $this->writeEntityRepositoryInterfaceTemplate($entityName, $output);
        $this->writeEntityFactoryInterfaceTemplate($entityName, $output);
        $this->writeEntityRepositoryTemplate($entityName, $output);
        $provider = $this->writeConfigProviderTemplate($entityName, $output);

        if (class_exists('Console\Commands\Provider\Push')) {
            $input = new ArrayInput([
                'command' => 'provider:push',
                'provider' => $provider
            ], new InputDefinition([
                    new InputArgument('command', InputArgument::REQUIRED),
                    new InputArgument('provider', InputArgument::REQUIRED)
                ])
            );

            return (new Push())->execute($input, $output);
        }

        return self::SUCCESS;
    }

    protected function getEntityTemplate(): EntityTemplate
    {
        return new EntityTemplate();
    }

    protected function getEntityDtoTemplate(): EntityDtoTemplate
    {
        return new EntityDtoTemplate();
    }

    protected function getEntityRepositoryTemplate(): EntityRepositoryTemplate
    {
        return new EntityRepositoryTemplate();
    }

    protected function getEntityRepositoryInterfaceTemplate(): EntityRepositoryInterfaceTemplate
    {
        return new EntityRepositoryInterfaceTemplate();
    }

    protected function getEntityFactoryInterfaceTemplate(): EntityFactoryInterfaceTemplate
    {
        return new EntityFactoryInterfaceTemplate();
    }

    protected function getConfigProviderTemplate(): ConfigProviderTemplate
    {
        return new ConfigProviderTemplate();
    }

    /**
     * @param string $entityName
     * @param TemplateCollection[]|null $tokens
     * @param OutputInterface $output
     * @return void
     */
    protected function writeEntityTemplate(string $entityName, ?array $tokens, OutputInterface $output): void
    {
        $template = $this->getEntityTemplate();
        $template->setTokens([
            $template::entityName => ClsHelper::basename($entityName),
            $template::properties => $tokens['properties'],
            $template::namespace => ClsHelper::namespace($entityName),
            $template::vars => $tokens['vars'],
        ]);

        $this->getWriter()->write($entityName, $template);

        $output->writeln("$entityName successfully created.");
    }

    /**
     * @param string $entityName
     * @param TemplateCollection[]|null $tokens
     * @param OutputInterface $output
     * @return void
     */
    protected function writeEntityDtoTemplate(string $entityName, ?array $tokens, OutputInterface $output): void
    {
        $template = $this->getEntityDtoTemplate();
        $template->setTokens([
            $template::entityName => ClsHelper::basename($entityName),
            $template::properties => $tokens['properties']->each(static function(PropertyTemplate $template) {
                $template->constructorProperty(true);
                $template->readonly(true);
            }),
            $template::constants => $tokens['constants'],
            $template::namespace => ClsHelper::namespace($entityName),
            $template::vars => $tokens['vars'],
        ]);

        $this->getWriter()->write($clsName = "{$entityName}Dto", $template);

        $output->writeln("$clsName successfully created.");
    }

    protected function writeConfigProviderTemplate(string $entityName, OutputInterface $output): string
    {
        $template = $this->getConfigProviderTemplate();
        $template->setTokens([
            $template::entityName => ClsHelper::basename($entityName),
            $template::namespace => ClsHelper::namespace($entityName)
        ]);

        $namespace = ClsHelper::namespace($entityName) ?? '';
        if ($namespace !== '') {
            $namespace .= '\\';
        }

        $this->getWriter()->write("{$namespace}ConfigProvider", $template);
        $output->writeln(
            sprintf(
                "%s\%s successfully created.",
                ClsHelper::namespace($entityName) ?? '',
                'ConfigProvider'
            )
        );

        return "{$namespace}ConfigProvider";
    }

    protected function writeEntityFactoryInterfaceTemplate(string $entityName, OutputInterface $output): void
    {
        $template = $this->getEntityFactoryInterfaceTemplate();
        $template->setTokens([
            $template::entityName => ClsHelper::basename($entityName),
            $template::namespace => ClsHelper::namespace($entityName)
        ]);

        $this->getWriter()->write($clsName = "{$entityName}FactoryInterface", $template);

        $output->writeln("{$clsName}FactoryInterface successfully created.");
    }
    
    protected function writeEntityRepositoryTemplate(string $entityName, OutputInterface $output): void
    {
        $template = $this->getEntityRepositoryTemplate();
        $template->setTokens([
            $template::entityName => ClsHelper::basename($entityName),
            $template::namespace => ClsHelper::namespace($entityName),
            $template::entityVarName => lcfirst(ClsHelper::basename($entityName)),
        ]);

        $this->getWriter()->write($clsName = "{$entityName}Repository", $template);

        $output->writeln("{$clsName}Repository successfully created.");
    }
    
    protected function writeEntityRepositoryInterfaceTemplate(string $entityName, OutputInterface $output): void
    {
        $template = $this->getEntityRepositoryInterfaceTemplate();
        $template->setTokens([
            $template::entityName => ClsHelper::basename($entityName),
            $template::namespace => ClsHelper::namespace($entityName),
            $template::entityVarName => lcfirst(ClsHelper::basename($entityName)),
        ]);

        $this->getWriter()->write($clsName = "{$entityName}RepositoryInterface", $template);

        $output->writeln("{$clsName}RepositoryInterface successfully created.");
    }

    protected function getWriter(): TemplateWriter
    {
        return $this->writer ?? $this->writer = new TemplateWriter;
    }
}
