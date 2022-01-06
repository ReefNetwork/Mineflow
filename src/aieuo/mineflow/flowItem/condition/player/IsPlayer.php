<?php

namespace aieuo\mineflow\flowItem\condition\player;

use aieuo\mineflow\flowItem\condition\entity\IsActiveEntity;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\utils\Category;
use aieuo\mineflow\utils\EntityHolder;

class IsPlayer extends IsActiveEntity {

    protected string $id = self::IS_PLAYER;

    protected string $name = "condition.isPlayer.name";
    protected string $detail = "condition.isPlayer.detail";

    protected string $category = Category::PLAYER;

    public function execute(FlowItemExecutor $source): \Generator {
        $this->throwIfCannotExecute();

        $id = $this->getInt($source->replaceVariables($this->getEntityId()));

        yield FlowItemExecutor::CONTINUE;
        return EntityHolder::isPlayer($id);
    }
}