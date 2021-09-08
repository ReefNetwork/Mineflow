<?php
declare(strict_types=1);

namespace aieuo\mineflow\exception;

use aieuo\mineflow\utils\Language;
use Throwable;

class InvalidFormValueException extends \RuntimeException {

    private string $errorMessage;
    private int $index;

    public function __construct(string $errorMessage, int $index, string $message = "", int $code = 0, Throwable $previous = null) {
        parent::__construct($message, $code, $previous);
        $this->errorMessage = Language::replace($errorMessage);
        $this->index = $index;
    }

    public function getErrorMessage(): string {
        return $this->errorMessage;
    }

    public function getIndex(): int {
        return $this->index;
    }
}