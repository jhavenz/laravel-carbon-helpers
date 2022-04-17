<?php

namespace Sourcefli\CarbonHelpers\Exceptions;

use InvalidArgumentException;

class InvalidDateTimeException extends InvalidArgumentException
{
	public static function invalidType(mixed $value): static
	{
		return new static(
			'Invalid datetime value given of type ['.gettype($value).']'
		);
	}
}
