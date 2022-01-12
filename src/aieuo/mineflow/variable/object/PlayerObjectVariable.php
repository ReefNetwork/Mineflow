<?php

declare(strict_types=1);

namespace aieuo\mineflow\variable\object;

use aieuo\mineflow\variable\BooleanVariable;
use aieuo\mineflow\variable\DummyVariable;
use aieuo\mineflow\variable\NumberVariable;
use aieuo\mineflow\variable\StringVariable;
use aieuo\mineflow\variable\Variable;
use pocketmine\player\Player;

class PlayerObjectVariable extends HumanObjectVariable {

    public function __construct(Player $value, ?string $str = null) {
        parent::__construct($value, $str ?? $value->getName());
    }

    public function getProperty(string $name): ?Variable {
        $player = $this->getPlayer();
        return match ($name) {
            "name" => new StringVariable($player->getName()),
            "display_name" => new StringVariable($player->getDisplayName()),
            "locale" => new StringVariable($player->getLocale()),
            "ping" => new NumberVariable($player->getNetworkSession()->getPing()),
            "ip" => new StringVariable($player->getNetworkSession()->getIp()),
            "port" => new NumberVariable($player->getNetworkSession()->getPort()),
            "uuid" => new StringVariable($player->getUniqueId()->toString()),
            "spawn_point" => new PositionObjectVariable($player->getSpawn()),
            "flying" => new BooleanVariable($player->isFlying()),
            default => parent::getProperty($name),
        };
    }

    /** @noinspection PhpIncompatibleReturnTypeInspection */
    public function getPlayer(): Player {
        return $this->getEntity();
    }

    public static function getTypeName(): string {
        return "player";
    }

    public static function getValuesDummy(): array {
        return array_merge(parent::getValuesDummy(), [
            "name" => new DummyVariable(StringVariable::class),
            "display_name" => new DummyVariable(StringVariable::class),
            "locale" => new DummyVariable(StringVariable::class),
            "ping" => new DummyVariable(NumberVariable::class),
            "ip" => new DummyVariable(StringVariable::class),
            "port" => new DummyVariable(NumberVariable::class),
            "uuid" => new DummyVariable(StringVariable::class),
            "spawn_point" => new DummyVariable(PositionObjectVariable::class),
            "flying" => new DummyVariable(BooleanVariable::class),
        ]);
    }

    public function __toString(): string {
        $value = $this->getPlayer();
        return $value->getName();
    }
}