<?php

namespace aieuo\mineflow\trigger\event;

use aieuo\mineflow\variable\DefaultVariables;
use aieuo\mineflow\variable\DummyVariable;
use aieuo\mineflow\variable\object\BlockObjectVariable;
use aieuo\mineflow\variable\object\PlayerObjectVariable;
use pocketmine\event\player\PlayerBedEnterEvent;

class PlayerBedEnterEventTrigger extends EventTrigger {
    public function __construct(string $subKey = "") {
        parent::__construct(PlayerBedEnterEvent::class, $subKey);
    }

    public function getVariables($event): array {
        /** @var PlayerBedEnterEvent $event */
        $target = $event->getPlayer();
        $block = $event->getBed();
        return array_merge(DefaultVariables::getPlayerVariables($target), DefaultVariables::getBlockVariables($block));
    }

    public function getVariablesDummy(): array {
        return [
            "target" => new DummyVariable(PlayerObjectVariable::class),
            "block" => new DummyVariable(BlockObjectVariable::class),
        ];
    }
}