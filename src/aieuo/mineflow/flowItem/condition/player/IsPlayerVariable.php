<?php

namespace aieuo\mineflow\flowItem\condition\player;

use aieuo\mineflow\flowItem\condition\entity\IsActiveEntityVariable;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\utils\Category;
use pocketmine\player\Player;

class IsPlayerVariable extends IsActiveEntityVariable {

    protected string $id = self::IS_PLAYER_VARIABLE;

    protected string $name = "condition.isPlayerVariable.name";
    protected string $detail = "condition.isPlayerVariable.detail";

    protected string $category = Category::PLAYER;

    public function execute(FlowItemExecutor $source): \Generator {
        $this->throwIfCannotExecute();

        $entity = $this->getOnlineEntity($source);

        yield FlowItemExecutor::CONTINUE;
        return $entity instanceof Player;
    }
}