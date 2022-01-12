<?php

namespace aieuo\mineflow\variable\object;

use aieuo\mineflow\variable\DummyVariable;
use aieuo\mineflow\variable\NumberVariable;
use aieuo\mineflow\variable\ObjectVariable;
use aieuo\mineflow\variable\Variable;
use pocketmine\math\AxisAlignedBB;
use pocketmine\math\Vector3;

class AxisAlignedBBObjectVariable extends ObjectVariable {

    public function __construct(AxisAlignedBB $value, ?string $str = null) {
        parent::__construct($value, $str);
    }

    public function getProperty(string $name): ?Variable {
        $aabb = $this->getAxisAlignedBB();
        return match ($name) {
            "min_x" => new NumberVariable($aabb->minX),
            "min_y" => new NumberVariable($aabb->minY),
            "min_Z" => new NumberVariable($aabb->minZ),
            "max_x" => new NumberVariable($aabb->maxX),
            "max_y" => new NumberVariable($aabb->maxY),
            "max_Z" => new NumberVariable($aabb->maxZ),
            "min" => new Vector3($aabb->minX, $aabb->minY, $aabb->minZ),
            "max" => new Vector3($aabb->maxX, $aabb->maxY, $aabb->maxZ),
            default => null,
        };
    }

    /** @noinspection PhpIncompatibleReturnTypeInspection */
    public function getAxisAlignedBB(): AxisAlignedBB {
        return $this->getValue();
    }

    public static function getTypeName(): string {
        return "aabb";
    }

    public static function getValuesDummy(): array {
        return [
            "min_x" => new DummyVariable(NumberVariable::class),
            "min_y" => new DummyVariable(NumberVariable::class),
            "min_Z" => new DummyVariable(NumberVariable::class),
            "max_x" => new DummyVariable(NumberVariable::class),
            "max_y" => new DummyVariable(NumberVariable::class),
            "max_Z" => new DummyVariable(NumberVariable::class),
            "min" => new DummyVariable(Vector3ObjectVariable::class),
            "max" => new DummyVariable(Vector3ObjectVariable::class),
        ];
    }

    public function __toString(): string {
        return (string)$this->getAxisAlignedBB();
    }

}