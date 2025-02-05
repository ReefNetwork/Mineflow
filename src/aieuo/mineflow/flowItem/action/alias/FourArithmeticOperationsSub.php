<?php

declare(strict_types=1);

namespace aieuo\mineflow\flowItem\action\alias;

use aieuo\mineflow\flowItem\action\math\FourArithmeticOperations;

class FourArithmeticOperationsSub extends FourArithmeticOperations implements FlowItemAlias {

    protected string $id = self::FOUR_ARITHMETIC_OPERATIONS_SUB;

    public function __construct(string $value1 = "", string $value2 = "", string $resultName = "result") {
        parent::__construct($value1, self::SUBTRACTION, $value2, $resultName);
    }
}