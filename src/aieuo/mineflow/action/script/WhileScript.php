<?php

namespace aieuo\mineflow\action\script;

use pocketmine\entity\Entity;
use pocketmine\Player;
use aieuo\mineflow\utils\Session;
use aieuo\mineflow\utils\Categories;
use aieuo\mineflow\ui\ScriptForm;
use aieuo\mineflow\ui\ConditionContainerForm;
use aieuo\mineflow\ui\ActionForm;
use aieuo\mineflow\ui\ActionContainerForm;
use aieuo\mineflow\script\Script;
use aieuo\mineflow\recipe\Recipe;
use aieuo\mineflow\formAPI\ListForm;
use aieuo\mineflow\condition\Conditionable;
use aieuo\mineflow\condition\ConditionContainer;
use aieuo\mineflow\condition\Condition;
use aieuo\mineflow\action\process\Process;
use aieuo\mineflow\action\ActionContainer;
use aieuo\mineflow\action\Action;
use aieuo\mineflow\formAPI\element\Button;
use aieuo\mineflow\Main;
use aieuo\mineflow\task\WhileScriptTask;

class WhileScript extends ActionScript implements ActionContainer, ConditionContainer {

    protected $id = self::SCRIPT_WHILE;

    protected $name = "@script.while.name";
    protected $description = "@script.while.description";

    protected $category = Categories::CATEGORY_ACTION_SCRIPT;

    /** @var Conditionable[] */
    private $conditions = [];
    /** @var Action[] */
    private $actions = [];
    /** @var int */
    private $interval = 20;
    /** @var int */
    private $limit = -1;
    /** @var int */
    private $taskId;

    public function __construct(array $conditions = [], array $actions = [], int $interval = 20, ?string $customName = null) {
        $this->conditions = $conditions;
        $this->actions = $actions;
        $this->interval = $interval;
        $this->setCustomName($customName);
    }

    public function addCondition(Conditionable $condition): void {
        $this->conditions[] = $condition;
    }

    public function setConditions(array $conditions): void {
        $this->conditions = $conditions;
    }

    public function setActions(array $actions): void {
        $this->actions = $actions;
    }

    public function getCondition(int $index): ?Conditionable {
        return $this->conditions[$index] ?? null;
    }

    public function getConditions(): array {
        return $this->conditions;
    }

    public function removeCondition(int $index): void {
        unset($this->conditions[$index]);
        $this->conditions = array_merge($this->conditions);
    }

    public function addAction(Action $action): void {
        $this->actions[] = $action;
    }

    public function getAction(int $index): ?Action {
        return $this->actions[$index] ?? null;
    }

    public function getActions(): array {
        return $this->actions;
    }

    public function removeAction(int $index): void {
        unset($this->actions[$index]);
        $this->actions = array_merge($this->actions);
    }

    public function setLimit(int $limit): void {
        $this->limit = $limit;
    }

    public function getLimit(): int {
        return $this->limit;
    }

    public function setInterval(int $interval): void {
        $this->interval = $interval;
    }

    public function getInterval(): int {
        return $this->interval;
    }

    public function setTaskId(int $id) {
        $this->taskId = $id;
    }

    public function getDetail(): string {
        $details = ["", "=============while============="];
        foreach ($this->conditions as $condition) {
            $details[] = $condition->getDetail();
        }
        $details[] = "~~~~~~~~~~~~~~~~~~~~~~~~~~~";
        foreach ($this->actions as $action) {
            $details[] = $action->getDetail();
        }
        $details[] = "================================";
        return implode("\n", $details);
    }

    public function execute(?Entity $target, ?Recipe $origin = null): ?bool {
        $script = clone $this;
        if ($origin instanceof Recipe) {
            $origin->wait();
        }
        $handler = Main::getInstance()->getScheduler()->scheduleRepeatingTask(new WhileScriptTask($script, $target, $origin), $this->interval);
        $script->setTaskId($handler->getTaskId());
        return true;
    }

    public function check(?Entity $target, ?Recipe $origin) {
        foreach ($this->conditions as $condition) {
            $result = $condition->execute($target, $origin);

            if ($result !== true) {
                Main::getInstance()->getScheduler()->cancelTask($this->taskId);
                if ($origin instanceof Recipe) $origin->resume();
                return;
            }
        }

        foreach ($this->actions as $action) {
            $result = $action->execute($target, $origin);
            if ($result === null) return null;
        }
    }

    public function sendEditForm(Player $player, array $messages = []) {
        $detail = trim($this->getDetail());
        (new ListForm($this->getName()))
            ->setContent(empty($detail) ? "@recipe.noActions" : $detail)
            ->addButtons([
                new Button("@form.back"),
                new Button("@condition.edit"),
                new Button("@action.edit"),
                new Button("@script.while.editInterval"),
                new Button("@form.delete"),
            ])->onReceive(function (Player $player, ?int $data) {
                $session = Session::getSession($player);
                if ($data === null) {
                    $session->removeAll();
                    return;
                }
                $parents = $session->get("parents");
                $parent = end($parents);
                switch ($data) {
                    case 0:
                        array_pop($parents);
                        $session->set("parents", $parents);
                        (new ActionContainerForm)->sendActionList($player, $parent);
                        break;
                    case 1:
                        (new ConditionContainerForm)->sendConditionList($player, $this);
                        break;
                    case 2:
                        (new ActionContainerForm)->sendActionList($player, $this);
                        break;
                    case 3:
                        (new ScriptForm)->sendSetWhileInterval($player, $this);
                        break;
                    case 4:
                        (new ActionForm)->sendConfirmDelete($player, $this, $parent);
                        break;
                }
            })->addMessages($messages)->show($player);
    }

    public function parseFromSaveData(array $contents): ?Script {
        if (!isset($contents[1])) return null;
        foreach ($contents[0] as $content) {
            switch ($content["type"]) {
                case Recipe::CONTENT_TYPE_CONDITION:
                    $condition = Condition::parseFromSaveDataStatic($content);
                    break;
                case Recipe::CONTENT_TYPE_SCRIPT:
                    $condition = Script::parseFromSaveDataStatic($content);
                    break;
                default:
                    return null;
            }
            if ($condition === null) return null;
            $this->addCondition($condition);
        }

        foreach ($contents[1] as $content) {
            switch ($content["type"]) {
                case Recipe::CONTENT_TYPE_PROCESS:
                    $action = Process::parseFromSaveDataStatic($content);
                    break;
                case Recipe::CONTENT_TYPE_SCRIPT:
                    $action = Script::parseFromSaveDataStatic($content);
                    break;
                default:
                    return null;
            }
            if ($action === null) return null;
            $this->addAction($action);
        }

        $this->setInterval($contents[2] ?? 20);
        $this->setLimit($contents[3] ?? -1);
        return $this;
    }

    public function serializeContents(): array {
        return  [
            $this->conditions,
            $this->actions,
            $this->interval,
            $this->limit,
        ];
    }
}