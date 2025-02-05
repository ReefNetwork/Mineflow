<?php

namespace aieuo\mineflow\formAPI\element\mineflow;

use aieuo\mineflow\flowItem\FlowItemIds;
use aieuo\mineflow\variable\DummyVariable;

class PlayerVariableDropdown extends VariableDropdown {

    protected string $variableType = DummyVariable::PLAYER;

    protected array $actions = [
        FlowItemIds::GET_PLAYER
    ];

    /**
     * @param array<string, DummyVariable> $variables
     * @param string $default
     * @param string|null $text
     * @param bool $optional
     */
    public function __construct(array $variables = [], string $default = "", ?string $text = null, bool $optional = false) {
        parent::__construct($text ?? "@action.form.target.player", $variables, [
            DummyVariable::PLAYER,
        ], $default, $optional);
    }
}