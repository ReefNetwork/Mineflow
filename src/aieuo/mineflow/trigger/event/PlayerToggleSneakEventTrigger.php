<?php

namespace aieuo\mineflow\trigger\event;

use aieuo\mineflow\variable\BoolVariable;
use aieuo\mineflow\variable\DefaultVariables;
use aieuo\mineflow\variable\DummyVariable;
use pocketmine\event\player\PlayerToggleSneakEvent;

class PlayerToggleSneakEventTrigger extends EventTrigger {
    public function __construct(string $subKey = "") {
        parent::__construct("PlayerToggleSneakEvent", $subKey, PlayerToggleSneakEvent::class);
    }

    public function getVariables(mixed $event): array {
        /** @var PlayerToggleSneakEvent $event */
        $target = $event->getPlayer();
        $variables = DefaultVariables::getPlayerVariables($target);
        $variables["state"] = new BoolVariable($event->isSneaking());
        return $variables;
    }

    public function getVariablesDummy(): array {
        return [
            "target" => new DummyVariable(DummyVariable::PLAYER),
            "state" => new DummyVariable(DummyVariable::BOOLEAN),
        ];
    }
}