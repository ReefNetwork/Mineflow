<?php

namespace aieuo\mineflow\utils;

use function preg_match;
use function preg_quote;
use function preg_replace;

class Utils {

    public static function isValidFileName(string $name): bool {
        return !preg_match("#[.¥/:?<>|*\"]#u", preg_quote($name, "/@#~"));
    }

    public static function getValidFileName(string $name): string {
        return preg_replace("#[.¥/:?<>|*\"]#u", "", preg_quote($name, "/@#~"));
    }

}