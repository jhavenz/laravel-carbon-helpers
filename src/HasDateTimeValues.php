<?php

namespace Jhavenz\CarbonHelpers;

use Carbon\Carbon;
use Carbon\CarbonImmutable;
use DateTimeInterface;
use Jhavenz\CarbonHelpers\Exceptions\InvalidDateTimeException;
use Throwable;

trait HasDateTimeValues
{
	protected function assertDatetimeValue(mixed $value): void
	{
		if (! $this->isADatetimeValue($value)) {
			throw InvalidDateTimeException::invalidType($value);
		}
	}

	protected function isADatetimeValue (mixed $value): bool
	{
		try {
			return match (TRUE) {
				$value instanceof DateTimeInterface => true,
				is_string($value), is_int($value) => false !== strtotime($value),
				is_object($value) && method_exists($value, '__toString') => $this->isADatetimeValue($value->__toString()),
				is_object($value) && method_exists($value, 'toString') => $this->isADatetimeValue($value->toString),
				default => false
			};
		} catch (Throwable) {
			return false;
		}
	}

	protected function toCarbonImmutable (mixed $value): null|CarbonImmutable|CarbonCollection
	{
		if (! is_iterable($value)) {
			return $this->isADatetimeValue($value) ? carbonImmutable($value) : null;
		}

		return new CarbonCollection($value);
	}

	protected function toCarbonMutable (mixed $value): null|Carbon|CarbonCollection
	{
		if (! is_iterable($value)) {
			return $this->isADatetimeValue($value) ? carbon($value) : null;
		}

		return new CarbonCollection($value);
	}

	protected function withoutInvalidDatetimeValues ($values = []): array
	{
		return array_filter($values, fn ($v) => $this->isADatetimeValue($v));
	}
}
