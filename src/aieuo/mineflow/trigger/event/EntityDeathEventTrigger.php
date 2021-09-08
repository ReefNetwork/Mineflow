<?php

namespace aieuo\mineflow\trigger\event;

use aieuo\mineflow\variable\DefaultVariables;
use aieuo\mineflow\variable\DummyVariable;
use aieuo\mineflow\variable\object\PlayerObjectVariable;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDeathEvent;
use pocketmine\Player;

class EntityDeathEventTrigger extends EntityEventTrigger {
    public function __construct(string $subKey = "") {
        parent::__construct(EntityDeathEvent::class, $subKey);
    }

    public function getVariables($event): array {
        /** @var EntityDeathEvent $event */
        $target = $event->getEntity();
        $variables = DefaultVariables::getEntityVariables($target);
        $cause = $target->getLastDamageCause();
        if ($cause instanceof EntityDamageByEntityEvent) {
            $killer = $cause->getDamager();
            if ($killer instanceof Player) {
                $variables = array_merge($variables, DefaultVariables::getPlayerVariables($killer, "killer"));
            }
        }
        return $variables;
    }

    public function getVariablesDummy(): array {
        return [
            "target" => new DummyVariable(PlayerObjectVariable::class),
            "killer" => new DummyVariable(PlayerObjectVariable::class),
        ];
    }
}