<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\script;

use aieuo\mineflow\exception\InvalidFlowValueException;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\FlowItemContainer;
use aieuo\mineflow\flowItem\FlowItemContainerTrait;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\formAPI\element\Button;
use aieuo\mineflow\ui\FlowItemContainerForm;
use aieuo\mineflow\utils\Category;
use pocketmine\player\Player;

class ElseAction extends FlowItem implements FlowItemContainer {
    use FlowItemContainerTrait;

    protected string $id = self::ACTION_ELSE;

    protected string $name = "action.else.name";
    protected string $detail = "action.else.description";

    protected string $category = Category::SCRIPT;

    public function __construct(array $actions = [], ?string $customName = null) {
        $this->setItems($actions, FlowItemContainer::ACTION);
        $this->setCustomName($customName);
    }

    public function getDetail(): string {
        $details = ["§7=============§f else §7=============§f"];
        foreach ($this->getActions() as $action) {
            $details[] = $action->getDetail();
        }
        $details[] = "§7================================§f";
        return implode("\n", $details);
    }

    public function getContainerName(): string {
        return empty($this->getCustomName()) ? $this->getName() : $this->getCustomName();
    }

    public function execute(FlowItemExecutor $source): \Generator {
        $lastResult = $source->getLastResult();
        if (!is_bool($lastResult)) throw new InvalidFlowValueException();
        if ($lastResult) return;

        yield from (new FlowItemExecutor($this->getActions(), $source->getTarget(), [], $source))->executeGenerator();
    }

    public function hasCustomMenu(): bool {
        return true;
    }

    public function getCustomMenuButtons(): array {
        return [
            new Button("@action.edit", fn(Player $player) => (new FlowItemContainerForm)->sendActionList($player, $this, FlowItemContainer::ACTION)),
        ];
    }

    public function loadSaveData(array $contents): FlowItem {
        foreach ($contents as $content) {
            $action = FlowItem::loadEachSaveData($content);
            $this->addItem($action, FlowItemContainer::ACTION);
        }
        return $this;
    }

    public function serializeContents(): array {
        return $this->getActions();
    }

    public function isDataValid(): bool {
        return true;
    }

    public function allowDirectCall(): bool {
        return false;
    }

    public function __clone() {
        $actions = [];
        foreach ($this->getActions() as $k => $action) {
            $actions[$k] = clone $action;
        }
        $this->setItems($actions, FlowItemContainer::ACTION);
    }
}