<?php

namespace Abilara;

enum Ability: int
{
    use AbilityTrait;

    case ROOT   = 1;
    case ADMIN  = 2;
    case USER   = 3;

    case ADD_ABILITIES = 4;
    case BAN_USERS     = 5;
    case COOK          = 6;

    public function include(): array
    {
        return match ($this) {
            self::ROOT  => [self::ADMIN, self::ADD_ABILITIES],  // Admin + Can add abilities
            self::ADMIN => [self::USER, self::BAN_USERS],       // Like user + Can ban users
            self::USER  => [self::COOK],                        // Users can cook
            default  => []
        };
    }
}
