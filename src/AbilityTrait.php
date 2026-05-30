<?php

namespace Abilara;

trait AbilityTrait
{
    public function has(self $ability): bool
    {
        return isset($this->expanded()[$ability->value]);
    }

    private function expanded(): array
    {
        static $cache = [];

        return $cache[$this->value] ??= $this->buildExpanded();
    }

    private function buildExpanded(): array
    {
        $result = [$this->value => true];

        foreach ($this->include() as $ability) {
            $result += $ability->expanded();
        }

        return $result;
    }
}