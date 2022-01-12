<?php

declare(strict_types=1);

namespace aieuo\mineflow\variable\object;

use aieuo\mineflow\variable\DummyVariable;
use aieuo\mineflow\variable\ListVariable;
use aieuo\mineflow\variable\NumberVariable;
use aieuo\mineflow\variable\ObjectVariable;
use aieuo\mineflow\variable\StringVariable;
use aieuo\mineflow\variable\Variable;
use pocketmine\entity\Entity;
use pocketmine\entity\Human;
use pocketmine\entity\Living;
use pocketmine\world\World;
use pocketmine\player\Player;
use function array_filter;
use function array_map;

class WorldObjectVariable extends ObjectVariable {

    public function __construct(World $value, ?string $str = null) {
        parent::__construct($value, $str ?? $value->getFolderName());
    }

    public function getProperty(string $name): ?Variable {
        $level = $this->getWorld();
        return match ($name) {
            "name" => new StringVariable($level->getDisplayName()),
            "folderName" => new StringVariable($level->getFolderName()),
            "id" => new NumberVariable($level->getId()),
            "spawn" => new PositionObjectVariable($level->getSpawnLocation()),
            "safe_spawn" => new PositionObjectVariable($level->getSafeSpawn()),
            "time" => new NumberVariable($level->getTime()),
            "players" => new ListVariable(array_values(array_map(fn(Player $player) => new PlayerObjectVariable($player), $level->getPlayers()))),
            "entities" => new ListVariable(array_values(array_map(fn(Entity $entity) => EntityObjectVariable::fromObject($entity), $level->getEntities()))),
            "livings" => new ListVariable(array_values(array_map(fn(Living $living) => EntityObjectVariable::fromObject($living),
                array_filter($level->getEntities(), fn(Entity $entity) => $entity instanceof Living)
            ))),
            default => null,
        };
    }

    /** @noinspection PhpIncompatibleReturnTypeInspection */
    public function getWorld(): World {
        return $this->getValue();
    }

    public static function getTypeName(): string {
        return "world";
    }

    public static function getValuesDummy(): array {
        return [
            "name" => new DummyVariable(StringVariable::class),
            "folderName" => new DummyVariable(StringVariable::class),
            "id" => new DummyVariable(NumberVariable::class),
            "spawn" => new DummyVariable(PositionObjectVariable::class),
            "safe_spawn" => new DummyVariable(PositionObjectVariable::class),
            "time" => new DummyVariable(NumberVariable::class),
            "players" => new DummyVariable(ListVariable::class, PlayerObjectVariable::getTypeName()),
            "entities" => new DummyVariable(ListVariable::class, EntityObjectVariable::getTypeName()),
            "livings" => new DummyVariable(ListVariable::class, LivingObjectVariable::getTypeName()),
        ];
    }
}
