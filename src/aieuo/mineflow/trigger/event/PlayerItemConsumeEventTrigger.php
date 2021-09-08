<?php

namespace aieuo\mineflow\trigger\event;

use aieuo\mineflow\variable\DefaultVariables;
use aieuo\mineflow\variable\DummyVariable;
use aieuo\mineflow\variable\object\ItemObjectVariable;
use aieuo\mineflow\variable\object\PlayerObjectVariable;
use pocketmine\event\player\PlayerItemConsumeEvent;

class PlayerItemConsumeEventTrigger extends EventTrigger {
    public function __construct(string $subKey = "") {
        parent::__construct(PlayerItemConsumeEvent::class, $subKey);
    }

    public function getVariables($event): array {
        /** @var PlayerItemConsumeEvent $event */
        $target = $event->getPlayer();
        $item = $event->getItem();
        return array_merge(DefaultVariables::getPlayerVariables($target), [
            "item" => new ItemObjectVariable($item),
        ]);
    }

    public function getVariablesDummy(): array {
        return [
            "target" => new DummyVariable(PlayerObjectVariable::class),
            "item" => new DummyVariable(ItemObjectVariable::class),
        ];
    }
}