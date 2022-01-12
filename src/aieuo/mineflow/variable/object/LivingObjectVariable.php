<?php

namespace aieuo\mineflow\variable\object;

use aieuo\mineflow\variable\BooleanVariable;
use aieuo\mineflow\variable\DummyVariable;
use aieuo\mineflow\variable\NumberVariable;
use aieuo\mineflow\variable\StringVariable;
use aieuo\mineflow\variable\Variable;
use pocketmine\entity\Entity;
use pocketmine\entity\EntityFactory;
use pocketmine\entity\Human;
use pocketmine\entity\Living;
use pocketmine\player\Player;

class LivingObjectVariable extends EntityObjectVariable {

    public function __construct(Living $entity, ?string $str = null) {
        parent::__construct($entity, $str);
    }

    public function getProperty(string $name): ?Variable {
        $living = $this->getLiving();
        return match ($name) {
            "armor" => new InventoryObjectVariable($living->getArmorInventory()),
            "sprinting" => new BooleanVariable($living->isSprinting()),
            "sneaking" => new BooleanVariable($living->isSneaking()),
            "gliding" => new BooleanVariable($living->isGliding()),
            "swimming" => new BooleanVariable($living->isSwimming()),
            default => parent::getProperty($name),
        };
    }

    /** @noinspection PhpIncompatibleReturnTypeInspection */
    public function getLiving(): Living {
        return $this->getEntity();
    }

    public static function getTypeName(): string {
        return "living";
    }

    public static function getValuesDummy(): array {
        return array_merge(parent::getValuesDummy(), [
            "armor" => new DummyVariable(InventoryObjectVariable::class),
            "sprinting" => new DummyVariable(BooleanVariable::class),
            "sneaking" => new DummyVariable(BooleanVariable::class),
            "gliding" => new DummyVariable(BooleanVariable::class),
            "swimming" => new DummyVariable(BooleanVariable::class),
        ]);
    }
}