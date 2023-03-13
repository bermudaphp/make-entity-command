<?php

namespace Console\Commands\Make\Template;

use Bermuda\Stdlib\StrHelper;
use Bermuda\Stdlib\StrWrp;

class PropertyTemplate implements TemplateInterface
{
    public const VISIBILITY_PUBLIC = 'public';
    public const VISIBILITY_PROTECTED = 'protected';
    public const VISIBILITY_PRIVATE = 'private';

    public function __construct(
        private readonly string $name,
        private string $visibility = self::VISIBILITY_PRIVATE,
        private array $types = [],
        private bool $readonly = false,
        public readonly bool $allowsNull = false,
        private bool $isConstructorProperty = false,
    ) {
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function visibility(string $visibility = null): string
    {
        if ($visibility !== null) {
            $visibility = strtolower($visibility);
            if (!StrHelper::equals($visibility, [
                self::VISIBILITY_PRIVATE,
                self::VISIBILITY_PROTECTED,
                self::VISIBILITY_PUBLIC
            ])) return $this->visibility;

            $old = $this->visibility;
            $this->visibility = $visibility;
        }

        return $old ?? $this->visibility;
    }

    public function types(array $types = null): array
    {
        if ($types !== null) {
            $old = $this->types;
            $this->types = $types;
        }

        return $old ?? $this->types;
    }

    public function constructorProperty(bool $mode = null): bool
    {
        if ($mode !== null) {
            $old = $this->isConstructorProperty;
            $this->isConstructorProperty = $mode;
        }

        return $old ?? $this->isConstructorProperty;
    }

    public function readonly(bool $mode = null): bool
    {
        if ($mode !== null) {
            $old = $this->readonly;
            $this->readonly = $mode;
        }

        return $old ?? $this->readonly;
    }

    public function render(): string
    {
        $end = ';';
        $str = '    ' . $this->visibility;

        if ($this->isConstructorProperty) {
            $str = '    '.$str;
            $end = ',';
        }

        if (($count = count($this->types)) === 1 && $this->types[0] === null) {
            $this->types = array_slice($this->types, 1);
        } else if ($this->allowsNull && $count === 1) {
            $str .= ' ?';
            $this->types = array_filter($this->types, static fn($v) => $v != 'null');
        } else if (count($this->types) == 0) {
            $str .= ' mixed ';
        }
        $str .= implode('|', $this->types);
        $str = rtrim($str, ' ');
        $str .= " \$$this->name";

        if ($this->allowsNull) {
            $str .= ' = null';
        }

        return $str . $end;
    }

    public static function parse(string $string): self
    {

        $prop = new StrWrp($string);
        $allowsNull = false;

        if ($prop->startsWith('_')) {
            $visibility = self::VISIBILITY_PRIVATE;
            $prop = $prop->ltrim('_');
        } elseif ($prop->startsWith('#')) {
            $visibility = self::VISIBILITY_PROTECTED;
            $prop = $prop->ltrim('#');
        } else {
            $visibility = self::VISIBILITY_PUBLIC;
        }

        if ($prop->contains(':')) {
            list($name, $types) = $prop->explode(':', 2);
            $typesNormalized = [];
            foreach ($types->explode('|') as $type) {
                $type = self::normalizeType($type, $allowsNull);
                if ($type) $typesNormalized[] = $type;
            }
        }

        return new static($name, $visibility, $typesNormalized ?? [], allowsNull: $allowsNull);
    }

    private static function normalizeType(StrWrp $type, &$allowsNull):? string
    {
        if ($type->startsWith('?')) {
            $allowsNull = true;
            $type = $type->ltrim('?');
        }

        if (
            $type->equals([
                'int', 'bool', 'null',
                'string', 'object', 'array'
            ])
        ) {
            return $type->toString();
        } elseif ($type->equals('datetime')) {
            return '\DateTimeInterface';
        } elseif ($type->equals('carbon')) {
            return '\Carbon\CarbonInterface';
        } else {
            return class_exists($type->toString()) ? $type->toString() : null;
        }
    }
}