<?php

namespace aieuo\mineflow\flowItem\condition;

use pocketmine\entity\Entity;
use aieuo\mineflow\recipe\Recipe;

class NandScript extends AndScript {

    protected $id = self::CONDITION_NAND;

    protected $name = "condition.nand.name";
    protected $detail = "condition.nand.description";

    public function getDetail(): string {
        $details = ["-----------nand-----------"];
        foreach ($this->getConditions() as $condition) {
            $details[] = $condition->getDetail();
        }
        $details[] = "------------------------";
        return implode("\n", $details);
    }

    public function execute(Recipe $origin): bool {
        return !parent::execute($origin);
    }
}