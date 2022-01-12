<?php

declare(strict_types=1);

namespace aieuo\mineflow\variable\object;

use aieuo\mineflow\variable\DummyVariable;
use aieuo\mineflow\variable\NumberVariable;
use aieuo\mineflow\variable\Variable;
use pocketmine\entity\Location;

class LocationObjectVariable extends PositionObjectVariable {

    public function __construct(Location $value, ?string $str = null) {
        parent::__construct($value, $str);
    }

    public function getProperty(string $name): ?Variable {
        $location = $this->getLocation();
        return match ($name) {
            "yaw" => new NumberVariable($location->yaw),
            "pitch" => new NumberVariable($location->pitch),
            "down" => new LocationObjectVariable(Location::fromObject($location->down(1), $location->world, $location->yaw, $location->pitch)),
            "up" => new LocationObjectVariable(Location::fromObject($location->up(1), $location->world, $location->yaw, $location->pitch)),
            "north" => new LocationObjectVariable(Location::fromObject($location->north(1), $location->world, $location->yaw, $location->pitch)),
            "south" => new LocationObjectVariable(Location::fromObject($location->south(1), $location->world, $location->yaw, $location->pitch)),
            "west" => new LocationObjectVariable(Location::fromObject($location->west(1), $location->world, $location->yaw, $location->pitch)),
            "east" => new LocationObjectVariable(Location::fromObject($location->east(1), $location->world, $location->yaw, $location->pitch)),
            default => parent::getProperty($name),
        };
    }

    /** @noinspection PhpIncompatibleReturnTypeInspection */
    public function getLocation(): Location {
        return $this->getValue();
    }

    public static function getTypeName(): string {
        return "location";
    }

    public static function getValuesDummy(): array {
        return array_merge(parent::getValuesDummy(), [
            "yaw" => new DummyVariable(NumberVariable::class),
            "pitch" => new DummyVariable(NumberVariable::class),
            "down" => new DummyVariable(LocationObjectVariable::class),
            "up" => new DummyVariable(LocationObjectVariable::class),
            "north" => new DummyVariable(LocationObjectVariable::class),
            "south" => new DummyVariable(LocationObjectVariable::class),
            "west" => new DummyVariable(LocationObjectVariable::class),
            "east" => new DummyVariable(LocationObjectVariable::class),
        ]);
    }

    public function __toString(): string {
        $value = $this->getLocation();
        return $value->x.",".$value->y.",".$value->z.",".$value->world->getFolderName()." (".$value->getYaw().",".$value->getPitch().")";
    }
}