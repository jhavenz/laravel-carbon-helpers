<?php

namespace Sourcefli\CarbonHelpers;

use Carbon\Carbon;
use Carbon\CarbonImmutable;
use DateTimeInterface;
use Illuminate\Support\Stringable;
use Sourcefli\CarbonHelpers\Exceptions\InvalidDateTimeException;
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
				$value instanceof Stringable => $this->isADatetimeValue($value->__toString()),
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

		return CarbonCollection::make($value)->withoutInvalidDatetimeValues();
	}

	protected function toCarbonMutable (mixed $value): null|Carbon|CarbonCollection
	{
		if (! is_iterable($value)) {
			return $this->isADatetimeValue($value) ? carbon($value) : null;
		}

		return CarbonCollection::make($value)->withoutInvalidDatetimeValues();
	}

	public function withoutInvalidDatetimeValues (): static
	{
		return $this->filter(fn ($v) => $this->isADatetimeValue($v));
	}
}
