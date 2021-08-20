<?php

namespace aieuo\mineflow\variable;

use aieuo\mineflow\exception\UnsupportedCalculationException;

class StringVariable extends Variable implements \JsonSerializable {

    public int $type = Variable::STRING;

    public function getValue(): string {
        return (string)parent::getValue();
    }

    public function add($target): StringVariable {
        return new StringVariable($this->getValue().$target);
    }

    public function sub($target): StringVariable {
        return new StringVariable(str_replace((string)$target, "", $this->getValue()));
    }

    public function mul($target): StringVariable {
        if ($target instanceof NumberVariable) $target = $target->getValue();
        if(is_numeric($target)) return new StringVariable(str_repeat($this->getValue(), (int)$target));

        throw new UnsupportedCalculationException();
    }

    public function callMethod(string $name, array $parameters = []): ?Variable {
        switch ($name) {
            case "length":
                return new NumberVariable(mb_strlen($this->getValue()));
            case "toLowerCase":
                return new StringVariable(mb_strtolower($this->getValue()));
            case "toUpperCase":
                return new StringVariable(mb_strtoupper($this->getValue()));
            case "substring":
                return new StringVariable(mb_substr($this->getValue(), $parameters[0], $parameters[1] ?? null));
        }
        return null;
    }

    public function jsonSerialize(): array {
        return [
            "type" => $this->getType(),
            "value" => $this->getValue(),
        ];
    }
}