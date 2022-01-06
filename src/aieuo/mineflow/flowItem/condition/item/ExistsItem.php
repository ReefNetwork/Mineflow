<?php

namespace aieuo\mineflow\flowItem\condition\item;

use aieuo\mineflow\flowItem\FlowItemExecutor;

class ExistsItem extends TypeItem {

    protected string $id = self::EXISTS_ITEM;

    protected string $name = "condition.existsItem.name";
    protected string $detail = "condition.existsItem.detail";

    public function execute(FlowItemExecutor $source): \Generator {
        $this->throwIfCannotExecute();

        $item = $this->getItem($source);
        $player = $this->getOnlinePlayer($source);

        yield FlowItemExecutor::CONTINUE;
        return $player->getInventory()->contains($item);
    }
}