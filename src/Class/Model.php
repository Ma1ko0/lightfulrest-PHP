<?php

declare(strict_types=1);

namespace App;

use JsonSerializable;

abstract class Model implements JsonSerializable
{
    protected array $hidden = [];

    protected function getHiddenProperties(): array
    {
        return array_merge(['hidden'], $this->hidden);
    }

    public function jsonSerialize(): array
    {
        $reflection = new \ReflectionClass($this);
        $properties = [];
        foreach ($reflection->getProperties() as $property) {
            if ($property->isStatic()) {
                continue;
            }
            if (in_array($property->getName(), $this->getHiddenProperties(), true)) {
                continue;
            }
            $properties[$property->getName()]
                = $property->getValue($this);
        }
        return $properties;
    }
}
