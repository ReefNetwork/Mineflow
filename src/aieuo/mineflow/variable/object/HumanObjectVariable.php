<?php

namespace aieuo\mineflow\variable\object;

use aieuo\mineflow\variable\DummyVariable;
use aieuo\mineflow\variable\NumberVariable;
use aieuo\mineflow\variable\Variable;
use pocketmine\entity\Human;

class HumanObjectVariable extends LivingObjectVariable {

    public function __construct(Human $value, ?string $str = null) {
        parent::__construct($value, $str ?? $value->getName());
    }

    public function getProperty(string $name): ?Variable {
        $human = $this->getHuman();
        return match ($name) {
            "hand" => new ItemObjectVariable($human->getInventory()->getItemInHand()),
            "food" => new NumberVariable($human->getHungerManager()->getFood()),
            "xp" => new NumberVariable($human->getXpManager()->getCurrentTotalXp()),
            "xp_level" => new NumberVariable($human->getXpManager()->getXpLevel()),
            "xp_progress" => new NumberVariable($human->getXpManager()->getXpProgress()),
            "inventory" => new InventoryObjectVariable($human->getInventory()),
            default => parent::getProperty($name),
        };
    }

    /** @noinspection PhpIncompatibleReturnTypeInspection */
    public function getHuman(): Human {
        return $this->getEntity();
    }

    public static function getTypeName(): string {
        return "human";
    }

    public static function getValuesDummy(): array {
        return array_merge(parent::getValuesDummy(), [
            "hand" => new DummyVariable(ItemObjectVariable::class),
            "food" => new DummyVariable(NumberVariable::class),
            "xp" => new DummyVariable(NumberVariable::class),
            "xp_level" => new DummyVariable(NumberVariable::class),
            "xp_progress" => new DummyVariable(NumberVariable::class),
            "inventory" => new DummyVariable(InventoryObjectVariable::class),
        ]);
    }
}