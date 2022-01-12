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

class EntityObjectVariable extends PositionObjectVariable {

    public static function fromObject(Entity $entity, ?string $str = null): EntityObjectVariable|LivingObjectVariable|HumanObjectVariable|PlayerObjectVariable {
        return match (true) {
            $entity instanceof Player => new PlayerObjectVariable($entity, $str ?? $entity->getName()),
            $entity instanceof Human => new HumanObjectVariable($entity, $str ?? $entity->getNameTag()),
            $entity instanceof Living => new LivingObjectVariable($entity, $str ?? $entity->getNameTag()),
            default => new EntityObjectVariable($entity, $str ?? $entity->getNameTag()),
        };
    }

    public function __construct(private Entity $entity, ?string $str = null) {
        parent::__construct($this->entity->getPosition(), $str);
    }

    public function getProperty(string $name): ?Variable {
        $entity = $this->getEntity();
        switch ($name) {
            case "id":
                return new NumberVariable($entity->getId());
            case "saveId":
                try {
                    return new StringVariable(EntityFactory::getInstance()->getSaveId($entity::class));
                } catch (\InvalidArgumentException) {
                    return new StringVariable("");
                }
            case "nameTag":
                return new StringVariable($entity->getNameTag());
            case "health":
                return new NumberVariable($entity->getHealth());
            case "maxHealth":
                return new NumberVariable($entity->getMaxHealth());
            case "yaw":
                return new NumberVariable($entity->getLocation()->getYaw());
            case "pitch":
                return new NumberVariable($entity->getLocation()->getPitch());
            case "direction":
                return new NumberVariable($entity->getHorizontalFacing());
            case "onGround":
                return new BooleanVariable($entity->isOnGround());
            case "aabb":
                return new AxisAlignedBBObjectVariable($entity->getBoundingBox());
            default:
                return parent::getProperty($name);
        }
    }

    public function getEntity(): Entity {
        return $this->entity;
    }

    public static function getTypeName(): string {
        return "entity";
    }
    
    public static function getValuesDummy(): array {
        return array_merge(parent::getValuesDummy(), [
            "id" => new DummyVariable(NumberVariable::class),
            "saveId" => new DummyVariable(StringVariable::class),
            "nameTag" => new DummyVariable(StringVariable::class),
            "health" => new DummyVariable(NumberVariable::class),
            "maxHealth" => new DummyVariable(NumberVariable::class),
            "yaw" => new DummyVariable(NumberVariable::class),
            "pitch" => new DummyVariable(NumberVariable::class),
            "direction" => new DummyVariable(NumberVariable::class),
            "onGround" => new DummyVariable(BooleanVariable::class),
            "aabb" => new DummyVariable(AxisAlignedBBObjectVariable::class),
        ]);
    }

    public function __toString(): string {
        return $this->getEntity()->getNameTag();
    }
}