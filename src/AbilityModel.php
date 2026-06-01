<?php

namespace Abilara;

use Illuminate\Database\Eloquent\Model;

class AbilityModel extends Model
{
    protected $table = 'abilities';

    protected $primaryKey = 'user_id';

    public $incrementing = false;

    protected $keyType = 'int';

    public $timestamps = false;

    protected $casts = [
        'abilities' => 'array',
    ];

    protected $attributes = [
        'abilities' => '[]',
    ];

    protected $fillable = [
        'user_id',
        'abilities',
    ];

    /**
     * @return int[]
     */
    protected function abilities(): array
    {
        return $this->abilities ?? [];
    }

    public function add(Ability ...$abilities): bool
    {
        $additionalAbilities = \array_column($abilities, 'value');

        $this->abilities = \array_keys(
            \array_flip($this->abilities) + \array_flip($additionalAbilities)
        );

        return $this->save();
    }

    public function set(Ability ...$abilities): bool
    {
        $this->abilities = \array_column($abilities, 'value');

        return $this->save();
    }

    public function remove(Ability ...$abilities): bool
    {
        $toRemove = \array_column($abilities, 'value');

        $this->abilities = \array_keys(
            \array_diff_key(
                \array_flip($this->abilities),
                \array_flip($toRemove)
            )
        );

        return $this->save();
    }

    public function has(Ability $ability): bool
    {
        foreach ($this->abilities() as $a) {
            if (Ability::tryFrom($a)?->has($ability) === true) {
                return true;
            }
        }

        return false;
    }

    public function hasAny(Ability ...$abilities): bool
    {
        foreach ($abilities as $ability) {
            if ($this->has($ability) === true) {
                return true;
            }
        }

        return false;
    }

    public function hasAll(Ability ...$abilities): bool
    {
        foreach ($abilities as $ability) {
            if ($this->has($ability) === false) {
                return false;
            }
        }

        return true;
    }
}