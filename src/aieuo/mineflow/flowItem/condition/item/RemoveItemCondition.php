<?php

namespace aieuo\mineflow\flowItem\condition\item;

use aieuo\mineflow\flowItem\FlowItemExecutor;

class RemoveItemCondition extends TypeItem {

    protected string $id = self::REMOVE_ITEM_CONDITION;

    protected string $name = "condition.removeItem.name";
    protected string $detail = "condition.removeItem.detail";

    public function execute(FlowItemExecutor $source): \Generator {
        $this->throwIfCannotExecute();

        $item = $this->getItem($source);
        $player = $this->getOnlinePlayer($source);

        if (!$player->getInventory()->contains($item)) return false;
        $player->getInventory()->removeItem($item);

        yield FlowItemExecutor::CONTINUE;
        return true;
    }
}