<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\script;

use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\FlowItemContainer;
use aieuo\mineflow\flowItem\FlowItemContainerTrait;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\formAPI\CustomForm;
use aieuo\mineflow\formAPI\element\Button;
use aieuo\mineflow\formAPI\element\CancelToggle;
use aieuo\mineflow\formAPI\element\mineflow\ExampleNumberInput;
use aieuo\mineflow\ui\FlowItemContainerForm;
use aieuo\mineflow\ui\FlowItemForm;
use aieuo\mineflow\utils\Category;
use aieuo\mineflow\variable\DummyVariable;
use aieuo\mineflow\variable\NumberVariable;
use pocketmine\player\Player;

class RepeatAction extends FlowItem implements FlowItemContainer {
    use FlowItemContainerTrait;

    protected string $id = self::ACTION_REPEAT;

    protected string $name = "action.repeat.name";
    protected string $detail = "action.repeat.description";

    protected string $category = Category::SCRIPT;

    protected int $permission = self::PERMISSION_LEVEL_1;

    private string $repeatCount;

    private string $startIndex = "0";
    private string $counterName = "i";

    public function __construct(array $actions = [], int $count = 1, ?string $customName = null) {
        $this->setItems($actions, FlowItemContainer::ACTION);
        $this->repeatCount = (string)$count;
        $this->setCustomName($customName);
    }

    public function setRepeatCount(string $count): void {
        $this->repeatCount = $count;
    }

    public function getRepeatCount(): string {
        return $this->repeatCount;
    }

    public function setStartIndex(string $startIndex): self {
        $this->startIndex = $startIndex;
        return $this;
    }

    public function getStartIndex(): string {
        return $this->startIndex;
    }

    public function setCounterName(string $counterName): self {
        $this->counterName = $counterName;
        return $this;
    }

    public function getCounterName(): string {
        return $this->counterName;
    }

    public function getDetail(): string {
        $repeat = $this->getRepeatCount();
        $length = strlen($repeat) - 1;
        $left = (int)ceil($length / 2);
        $right = $length - $left;
        $details = ["", "§7".str_repeat("=", 12 - $left)."§frepeat(".$repeat.")§7".str_repeat("=", 12 - $right)."§f"];
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
        $count = $source->replaceVariables($this->repeatCount);
        $this->throwIfInvalidNumber($count, 1);

        $start = $source->replaceVariables($this->startIndex);
        $this->throwIfInvalidNumber($start);

        $name = $this->counterName;
        $end = (int)$start + (int)$count;

        for ($i = (int)$start; $i < $end; $i++) {
            yield from (new FlowItemExecutor($this->getActions(), $source->getTarget(), [
                $name => new NumberVariable($i)
            ], $source))->executeGenerator();
        }
        $source->resume();
        return true;
    }

    public function hasCustomMenu(): bool {
        return true;
    }

    public function getCustomMenuButtons(): array {
        return [
            new Button("@action.edit", fn(Player $player) => (new FlowItemContainerForm)->sendActionList($player, $this, FlowItemContainer::ACTION)),
            new Button("@action.for.setting", fn(Player $player) => $this->sendSetRepeatCountForm($player)),
        ];
    }

    public function sendSetRepeatCountForm(Player $player): void {
        (new CustomForm("@action.repeat.editCount"))
            ->setContents([
                new ExampleNumberInput("@action.repeat.repeatCount", "10", $this->getRepeatCount(), true, 1),
                new CancelToggle()
            ])->onReceive(function (Player $player, array $data) {
                if ($data[1]) {
                    (new FlowItemForm)->sendFlowItemCustomMenu($player, $this, FlowItemContainer::ACTION, ["@form.cancelled"]);
                    return;
                }

                $this->setRepeatCount($data[0]);
                (new FlowItemForm)->sendFlowItemCustomMenu($player, $this, FlowItemContainer::ACTION, ["@form.changed"]);
            })->show($player);
    }

    public function loadSaveData(array $contents): FlowItem {
        $this->setRepeatCount((string)$contents[0]);

        foreach ($contents[1] as $content) {
            $action = FlowItem::loadEachSaveData($content);
            $this->addItem($action, FlowItemContainer::ACTION);
        }

        if (isset($contents[2])) $this->startIndex = (string)$contents[2];
        if (isset($contents[3])) $this->counterName = $contents[3];
        return $this;
    }

    public function serializeContents(): array {
        return [
            $this->repeatCount,
            $this->getActions(),
            $this->startIndex,
            $this->counterName
        ];
    }

    public function getAddingVariables(): array {
        return [
            $this->getCounterName() => new DummyVariable(DummyVariable::NUMBER)
        ];
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