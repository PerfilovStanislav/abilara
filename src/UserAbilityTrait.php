<?php

namespace Abilara;

use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * @property AbilityModel $abilities
 */

trait UserAbilityTrait
{
    public function abilities(): HasOne
    {
        return $this->hasOne(AbilityModel::class, 'user_id', 'id');
    }

    public function addAbilities(Ability ...$abilities): bool
    {
        /** @var AbilityModel $abilityModel */
        $abilityModel = $this->abilities()->firstOrNew();

        return $abilityModel->add(...$abilities);
    }

    public function removeAbilities(Ability ...$abilities): bool
    {
        /** @var AbilityModel $abilityModel */
        $abilityModel = $this->abilities()->firstOrNew();

        return $abilityModel->remove(...$abilities);
    }

    public function hasAbility(Ability $ability): bool
    {
        return $this->abilities?->has($ability) ?? false;
    }

    public function hasAnyAbility(Ability ...$abilities): bool
    {
        return $this->abilities?->hasAny(...$abilities) ?? false;
    }

    public function hasAllAbilities(Ability ...$abilities): bool
    {
        return $this->abilities?->hasAll(...$abilities) ?? false;
    }
}