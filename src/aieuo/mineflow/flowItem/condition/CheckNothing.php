<?php

namespace aieuo\mineflow\flowItem\condition;

use pocketmine\entity\Entity;
use aieuo\mineflow\utils\Categories;
use aieuo\mineflow\recipe\Recipe;

class CheckNothing extends Condition {

    protected $id = self::CHECK_NOTHING;

    protected $name = "@condition.noCheck.name";
    protected $description = "@condition.noCheck.description";
    protected $detail = "@condition.noCheck.detail";

    protected $category = Categories::CATEGORY_CONDITION_COMMON;

    protected $targetRequired = Recipe::TARGET_REQUIRED_NONE;

    public function execute(?Entity $target, Recipe $origin): ?bool {
        return true;
    }

    public function isDataValid(): bool {
        return true;
    }

    public function loadSaveData(array $content): Condition {
        return $this;
    }

    public function serializeContents(): array {
        return [];
    }
}