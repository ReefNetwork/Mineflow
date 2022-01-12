<?php

namespace aieuo\mineflow\variable\object;

use aieuo\mineflow\variable\DummyVariable;
use aieuo\mineflow\variable\NumberVariable;
use aieuo\mineflow\variable\StringVariable;
use aieuo\mineflow\variable\Variable;
use pocketmine\block\Block;
use pocketmine\math\Facing;

class BlockObjectVariable extends PositionObjectVariable {

    public function __construct(private Block $block, ?string $str = null) {
        parent::__construct($block->getPosition(), $str);
    }

    public static function getTypeName(): string {
        return "block";
    }

    public function getProperty(string $name): ?Variable {
        $block = $this->getBlock();
        return match ($name) {
            "name" => new StringVariable($block->getName()),
            "id" => new NumberVariable($block->getId()),
            "damage", "meta" => new NumberVariable($block->getMeta()),
            "item" => new ItemObjectVariable($block->getPickedItem()),
            "down" => new BlockObjectVariable($block->getSide(Facing::DOWN)),
            "up" => new BlockObjectVariable($block->getSide(Facing::UP)),
            "north" => new BlockObjectVariable($block->getSide(Facing::NORTH)),
            "south" => new BlockObjectVariable($block->getSide(Facing::SOUTH)),
            "west" => new BlockObjectVariable($block->getSide(Facing::WEST)),
            "east" => new BlockObjectVariable($block->getSide(Facing::EAST)),
            default => parent::getProperty($name),
        };
    }

    public function getBlock(): Block {
        return $this->block;
    }

    public static function getValuesDummy(): array {
        return array_merge(parent::getValuesDummy(), [
            "name" => new DummyVariable(StringVariable::class),
            "id" => new DummyVariable(NumberVariable::class),
            "damage", "meta" => new DummyVariable(NumberVariable::class),
            "item" => new DummyVariable(ItemObjectVariable::class),
            "down" => new DummyVariable(BlockObjectVariable::class),
            "up" => new DummyVariable(BlockObjectVariable::class),
            "north" => new DummyVariable(BlockObjectVariable::class),
            "south" => new DummyVariable(BlockObjectVariable::class),
            "west" => new DummyVariable(BlockObjectVariable::class),
            "east" => new DummyVariable(BlockObjectVariable::class),
        ]);
    }

    public function __toString(): string {
        $value = $this->getBlock();
        return (string)$value;
    }
}