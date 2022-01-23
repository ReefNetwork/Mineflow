<?php

namespace aieuo\mineflow\variable;

use aieuo\mineflow\variable\object\AxisAlignedBBObjectVariable;
use aieuo\mineflow\variable\object\BlockObjectVariable;
use aieuo\mineflow\variable\object\ConfigObjectVariable;
use aieuo\mineflow\variable\object\EntityObjectVariable;
use aieuo\mineflow\variable\object\EventObjectVariable;
use aieuo\mineflow\variable\object\HumanObjectVariable;
use aieuo\mineflow\variable\object\InventoryObjectVariable;
use aieuo\mineflow\variable\object\ItemObjectVariable;
use aieuo\mineflow\variable\object\LivingObjectVariable;
use aieuo\mineflow\variable\object\RecipeObjectVariable;
use aieuo\mineflow\variable\object\WorldObjectVariable;
use aieuo\mineflow\variable\object\LocationObjectVariable;
use aieuo\mineflow\variable\object\PlayerObjectVariable;
use aieuo\mineflow\variable\object\PositionObjectVariable;
use aieuo\mineflow\variable\object\ScoreboardObjectVariable;
use aieuo\mineflow\variable\object\Vector3ObjectVariable;

class DummyVariable extends Variable {

    public int $type = Variable::DUMMY;

    private string $description;
    private string $valueType;

    public const UNKNOWN = "unknown";
    public const STRING = "string";
    public const NUMBER = "number";
    public const BOOLEAN = "boolean";
    public const LIST = "list";
    public const MAP = "map";
    public const BLOCK = "block";
    public const CONFIG = "config";
    public const ENTITY = "entity";
    public const EVENT = "event";
    public const HUMAN = "human";
    public const LIVING = "living";
    public const ITEM = "item";
    public const WORLD = "world";
    public const LOCATION = "location";
    public const PLAYER = "player";
    public const POSITION = "position";
    public const VECTOR3 = "vector3";
    public const SCOREBOARD = "scoreboard";
    public const INVENTORY = "inventory";
    public const AXIS_ALIGNED_BB = "axisAlignedBB";
    public const RECIPE = "recipe";

    public function __construct(string $valueType = "", string $description = "") {
        $this->valueType = $valueType;
        $this->description = $description;
        parent::__construct("");
    }

    public function getDescription(): string {
        return $this->description;
    }

    public function getValueType(): string {
        return $this->valueType;
    }

    public function toStringVariable(): StringVariable {
        return new StringVariable($this->getValue());
    }

    public function getValueFromIndex(string $index): ?Variable {
        $variables = $this->getObjectValuesDummy();
        return $variables[$index] ?? null;
    }

    /**
     * @return array<string, DummyVariable>
     */
    public function getObjectValuesDummy(): array {
        return match ($this->getValueType()) {
            self::BLOCK => BlockObjectVariable::getValuesDummy(),
            self::CONFIG => ConfigObjectVariable::getValuesDummy(),
            self::ENTITY => EntityObjectVariable::getValuesDummy(),
            self::EVENT => EventObjectVariable::getValuesDummy(),
            self::HUMAN => HumanObjectVariable::getValuesDummy(),
            self::LIVING => LivingObjectVariable::getValuesDummy(),
            self::ITEM => ItemObjectVariable::getValuesDummy(),
            self::WORLD => WorldObjectVariable::getValuesDummy(),
            self::LOCATION => LocationObjectVariable::getValuesDummy(),
            self::PLAYER => PlayerObjectVariable::getValuesDummy(),
            self::POSITION => PositionObjectVariable::getValuesDummy(),
            self::VECTOR3 => Vector3ObjectVariable::getValuesDummy(),
            self::SCOREBOARD => ScoreboardObjectVariable::getValuesDummy(),
            self::INVENTORY => InventoryObjectVariable::getValuesDummy(),
            self::AXIS_ALIGNED_BB => AxisAlignedBBObjectVariable::getValuesDummy(),
            self::RECIPE => RecipeObjectVariable::getValuesDummy(),
            default => [],
        };
    }

    public function isObjectVariableType(): bool {
        return in_array($this->getValueType(), [
            self::BLOCK,
            self::CONFIG,
            self::ENTITY,
            self::EVENT,
            self::HUMAN,
            self::LIVING,
            self::ITEM,
            self::WORLD,
            self::LOCATION,
            self::PLAYER,
            self::POSITION,
            self::VECTOR3,
            self::SCOREBOARD,
            self::INVENTORY,
            self::AXIS_ALIGNED_BB,
            self::RECIPE,
        ], true);
    }
}