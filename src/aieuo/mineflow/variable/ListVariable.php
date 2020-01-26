<?php

namespace aieuo\mineflow\variable;

class ListVariable extends Variable implements \JsonSerializable {

    public $type = Variable::LIST;

    /** @var string */
    private $showString = "";

    /**
     * @return Variable[]
     */
    public function getValue() {
        return parent::getValue();
    }

    /**
     * @param Variable[] $value
     * @param string $name
     * @param string $str
     */
    public function __construct(array $value, string $name = "", ?string $str = "") {
        parent::__construct($value, $name);
        $this->showString = $str;
    }

    public function addValue(Variable $value) {
        $this->value[] = $value;
    }

    public function removeValue(Variable $value) {
        $index = array_search($value, $this->value, true);
        if ($index === false) return;
        unset($this->value[$index]);
        $this->value = array_merge($this->value);
    }

    public function getValueFromIndex($index): ?Variable {
        if (!isset($this->value[$index])) return null;
        return $this->value[$index];
    }

    public function getCount() {
        return count($this->value);
    }

    public function toStringVariable(): StringVariable {
        return new StringVariable($this->__toString(), $this->getName());
    }

    public function __toString() {
        if (!empty($this->getShowString())) return $this->getShowString();
        $values = [];
        foreach ($this->getValue() as $value) {
            $values[] = $value->__toString();
        }
        return json_encode($values);
    }

    public function getShowString(): string {
        return $this->showString;
    }

    public function jsonSerialize() {
        return [
            "name" => $this->getName(),
            "type" => $this->getType(),
            "value" => $this->getValue(),
        ];
    }

    public static function fromArray(array $data): ?Variable {
        if (!isset($data["value"])) return null;
        $values = [];
        foreach ($data["value"] as $value) {
            if (!isset($value["type"])) return null;
            $values[] = Variable::create($value["value"], $value["name"] ?? "", $value["type"]);
        }
        return new self($values, $data["name"] ?? "");
    }
}