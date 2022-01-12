<?php

namespace aieuo\mineflow\variable\object;

use aieuo\mineflow\variable\ListVariable;
use aieuo\mineflow\variable\NumberVariable;
use aieuo\mineflow\variable\ObjectVariable;
use aieuo\mineflow\variable\Variable;
use pocketmine\inventory\Inventory;
use pocketmine\item\Item;
use function array_key_last;
use function explode;
use function array_map;

class InventoryObjectVariable extends ObjectVariable {

    public function __construct(Inventory $value, ?string $str = null) {
        parent::__construct($value, $str);
    }

    public function getProperty(string $name): ?Variable {
        $inventory = $this->getInventory();
        return match ($name) {
            "all" => new ListVariable(array_values(array_map(fn(Item $item) => new ItemObjectVariable($item), $inventory->getContents()))),
            "size" => new NumberVariable($inventory->getSize()),
            default => new ItemObjectVariable($inventory->getItem((int)$name)),
        };
    }

    /** @noinspection PhpIncompatibleReturnTypeInspection */
    public function getInventory(): Inventory {
        return $this->getValue();
    }

    public static function getTypeName(): string {
        return "inventory";
    }

    public static function getValuesDummy(): array {
        return [];
    }

    public function __toString(): string {
        $value = $this->getInventory();
        $names = explode("\\", $value::class);
        return $names[array_key_last($names)];
    }
}
