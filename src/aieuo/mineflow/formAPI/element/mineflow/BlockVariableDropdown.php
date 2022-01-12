<?php

namespace aieuo\mineflow\formAPI\element\mineflow;

use aieuo\mineflow\flowItem\FlowItemIds;
use aieuo\mineflow\variable\DummyVariable;
use aieuo\mineflow\variable\object\BlockObjectVariable;

class BlockVariableDropdown extends VariableDropdown {

    protected string $variableClass = BlockObjectVariable::class;

    protected array $actions = [
        FlowItemIds::CREATE_BLOCK_VARIABLE,
        FlowItemIds::GET_BLOCK,
        FlowItemIds::GET_TARGET_BLOCK,
    ];

    /**
     * @param array<string, DummyVariable> $variables
     * @param string $default
     * @param string|null $text
     * @param bool $optional
     */
    public function __construct(array $variables = [], string $default = "", ?string $text = null, bool $optional = false) {
        parent::__construct($text ?? "@action.form.target.block", $variables, [
            BlockObjectVariable::class,
        ], $default, $optional);
    }
}