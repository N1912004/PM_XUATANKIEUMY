<?php

namespace {
    if (! extension_loaded('intl') && ! class_exists('NumberFormatter')) {
        class NumberFormatter
        {
            public const DECIMAL = 1;

            public const CURRENCY = 2;

            public const PERCENT = 3;

            public const SCIENTIFIC = 4;

            public const SPELLOUT = 5;

            public const ORDINAL = 6;

            public const DURATION = 7;

            public const PATTERN_DECIMAL = 8;

            public const INDEX = 9;

            public const IGNORE = 10;

            public const FRACTION_DIGITS = 1;

            public const MAX_FRACTION_DIGITS = 2;

            public function __construct(string $locale, int $style) {}

            public function setAttribute(int $attribute, int $value): bool
            {
                return true;
            }

            public function format(int|float $value): string
            {
                return number_format($value);
            }
        }
    }
}

namespace Illuminate\Support {
    if (! \extension_loaded('intl') && ! function_exists('Illuminate\Support\extension_loaded')) {
        function extension_loaded($name)
        {
            if ($name === 'intl') {
                return true;
            }

            return \extension_loaded($name);
        }
    }
}
