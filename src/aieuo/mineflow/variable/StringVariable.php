<?php

namespace aieuo\mineflow\variable;

use aieuo\mineflow\exception\UnsupportedCalculationException;

class StringVariable extends Variable implements \JsonSerializable {

    public static function getTypeName(): string {
        return "string";
    }

    public function __construct(private string $value) {
    }

    public function getValue(): string {
        return $this->value;
    }

    public function add(Variable $target): StringVariable {
        return new StringVariable($this->getValue().$target);
    }

    public function sub(Variable $target): StringVariable {
        return new StringVariable(str_replace((string)$target, "", $this->getValue()));
    }

    public function mul(Variable $target): StringVariable {
        if ($target instanceof NumberVariable) return new StringVariable(str_repeat($this->getValue(), $target->getValue()));

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
            "type" => static::getTypeName(),
            "value" => $this->getValue(),
        ];
    }
}