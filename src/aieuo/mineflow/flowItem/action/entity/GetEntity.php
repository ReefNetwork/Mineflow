<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\entity;

use aieuo\mineflow\exception\InvalidFlowValueException;
use aieuo\mineflow\flowItem\FlowItem;
use aieuo\mineflow\flowItem\FlowItemExecutor;
use aieuo\mineflow\formAPI\element\mineflow\ExampleInput;
use aieuo\mineflow\utils\Category;
use aieuo\mineflow\utils\EntityHolder;
use aieuo\mineflow\utils\Language;
use aieuo\mineflow\variable\DummyVariable;
use aieuo\mineflow\variable\object\EntityObjectVariable;
use aieuo\mineflow\variable\object\HumanObjectVariable;
use aieuo\mineflow\variable\object\PlayerObjectVariable;
use pocketmine\entity\Human;
use pocketmine\Player;

class GetEntity extends FlowItem {

    protected string $id = self::GET_ENTITY;

    protected string $name = "action.getEntity.name";
    protected string $detail = "action.getEntity.detail";
    protected array $detailDefaultReplace = ["id", "result"];

    protected string $category = Category::ENTITY;
    protected string $returnValueType = self::RETURN_VARIABLE_NAME;

    private string $entityId;
    private string $resultName;

    public function __construct(string $name = "", string $result = "entity") {
        $this->entityId = $name;
        $this->resultName = $result;
    }

    public function setKey(string $name): self {
        $this->entityId = $name;
        return $this;
    }

    public function getKey(): string {
        return $this->entityId;
    }

    public function setResultName(string $name): self {
        $this->resultName = $name;
        return $this;
    }

    public function getResultName(): string {
        return $this->resultName;
    }

    public function isDataValid(): bool {
        return $this->getKey() !== "" and !empty($this->getResultName());
    }

    public function getDetail(): string {
        return Language::get($this->detail, [$this->getKey(), $this->getResultName()]);
    }

    public function execute(FlowItemExecutor $source): \Generator {
        $this->throwIfCannotExecute();

        $id = $this->getInt($source->replaceVariables($this->getEntityId()), min: 0);
        $resultName = $source->replaceVariables($this->getReturnName());

        $entity = EntityHolder::findEntity($id);
        if ($entity === null) {
            throw new InvalidFlowValueException($this->getName(), Language::get("action.getEntity.notFound", [(string)$id]));
        }
        if ($entity instanceof Player) {
            $variable = new PlayerObjectVariable($entity, $entity->getName());
        } elseif ($entity instanceof Human) {
            $variable = new HumanObjectVariable($entity, $entity->getNameTag());
        } else {
            $variable = new EntityObjectVariable($entity, $entity->getNameTag());
        }
        $source->addVariable($resultName, $variable);
        yield FlowItemExecutor::CONTINUE;
        return $this->getResultName();
    }

    public function getEditFormElements(array $variables): array {
        return [
            new ExampleInput("@action.getEntity.form.target", "aieuo", $this->getKey(), true),
            new ExampleInput("@action.form.resultVariableName", "entity", $this->getResultName(), true),
        ];
    }

    public function loadSaveData(array $content): FlowItem {
        $this->setKey($content[0]);
        $this->setResultName($content[1]);
        return $this;
    }

    public function serializeContents(): array {
        return [$this->getKey(), $this->getResultName()];
    }

    public function getAddingVariables(): array {
        return [
            $this->getResultName() => new DummyVariable(PlayerObjectVariable::class, $this->getKey())
        ];
    }
}