<?php

namespace aieuo\mineflow\variable\object;

use aieuo\mineflow\variable\DummyVariable;
use aieuo\mineflow\variable\NumberVariable;
use aieuo\mineflow\variable\ObjectVariable;
use aieuo\mineflow\variable\StringVariable;
use aieuo\mineflow\variable\Variable;
use pocketmine\math\Vector3;

class Vector3ObjectVariable extends ObjectVariable {

    public function __construct(Vector3 $value, ?string $str = null) {
        parent::__construct($value, $str);
    }

    public function getProperty(string $name): ?Variable {
        $position = $this->getVector3();
        return match ($name) {
            "x" => new NumberVariable($position->x),
            "y" => new NumberVariable($position->y),
            "z" => new NumberVariable($position->z),
            "xyz" => new StringVariable($position->x.",".$position->y.",".$position->z),
            "down" => new Vector3ObjectVariable($position->down(1)),
            "up" => new Vector3ObjectVariable($position->up(1)),
            "north" => new Vector3ObjectVariable($position->north(1)),
            "south" => new Vector3ObjectVariable($position->south(1)),
            "west" => new Vector3ObjectVariable($position->west(1)),
            "east" => new Vector3ObjectVariable($position->east(1)),
            default => null,
        };
    }

    /** @noinspection PhpIncompatibleReturnTypeInspection */
    public function getVector3(): Vector3 {
        return $this->getValue();
    }

    public static function getTypeName(): string {
        return "vector3";
    }

    public static function getValuesDummy(): array {
        return [
            "x" => new DummyVariable(NumberVariable::class),
            "y" => new DummyVariable(NumberVariable::class),
            "z" => new DummyVariable(NumberVariable::class),
            "xyz" => new DummyVariable(StringVariable::class),
            "down" => new DummyVariable(Vector3ObjectVariable::class),
            "up" => new DummyVariable(Vector3ObjectVariable::class),
            "north" => new DummyVariable(Vector3ObjectVariable::class),
            "south" => new DummyVariable(Vector3ObjectVariable::class),
            "west" => new DummyVariable(Vector3ObjectVariable::class),
            "east" => new DummyVariable(Vector3ObjectVariable::class),
        ];
    }

    public function __toString(): string {
        $value = $this->getVector3();
        return $value->x.",".$value->y.",".$value->z;
    }
}