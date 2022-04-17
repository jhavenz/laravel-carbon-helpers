<?php

namespace Sourcefli\CarbonHelpers;

use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Carbon\CarbonPeriod;
use Illuminate\Support\Collection;
use Sourcefli\CarbonHelpers\Exceptions\InvalidDateTimeException;

class CarbonCollection extends Collection
{
	use HasDateTimeValues;

	public function current (): CarbonInterface
	{
		return current($this->items);
	}

	/** removes non-datetime values without throwing an exception */
	public function asImmutables (): static
	{
		return $this->toCarbonImmutable($this);
	}

	/** removes non-datetime values without throwing an exception */
	public function asMutables (): static
	{
		return $this->toCarbonMutable($this);
	}

	public static function fromPeriod (CarbonPeriod $period, bool $toImmutables = true): CarbonCollection
	{
		return static::make($period)->when($toImmutables,
			fn ($self) => $self->asImmutables(),
			fn ($self) => $self->asMutables(),
		);
	}

	public function getClosestFromToday (): CarbonImmutable
	{
		return $this->sortByTimestamp()->first();
	}

	public function getFarthestFromToday (): CarbonImmutable
	{
		return $this->sortByTimestamp()->last();
	}

	/**
	 * {@see \Carbon\Traits\Converter::getTimeFormatByPrecision} for precision options.
	 *  Leave null to compare by date only.
	 */
	public function hasDate (mixed $date, ?string $precision = null): bool
	{
		if (! $this->isADatetimeValue($date)) {
			return false;
		}

		if (is_null($precision)) {
			return $this->toDateString()->contains(carbonImmutable($date)->toDateString());
		}

		return $this->toDateTimeLocalString($precision)->contains(carbonImmutable($date)->toDateTimeLocalString($precision));
	}

	public function remove (mixed $datetime, string $comparatorMethod = 'isSameDay'): static
	{
		if (! $this->isADatetimeValue($datetime)) {
			return $this;
		}

		return $this->asImmutables()->filter(
			fn (CarbonImmutable $date) => carbonImmutable($datetime)->{$comparatorMethod}($date)
		);
	}

	public function removeAllByDate (CarbonCollection $exclusions): static
	{
		return $this->toDateString()->diff($exclusions->toDateString())->asImmutables();
	}

	public function removeAllByDateTime (CarbonCollection $exclusions, string $precision = 'second'): static
	{
		return $this
			->toDateTimeLocalString($precision)
			->diff($exclusions->toDateTimeLocalString($precision))
			->asImmutables();
	}

	public function sortByTimestamp (): static
	{
		return $this->asImmutables()->sortBy(
			fn (CarbonImmutable $date) => $date->getTimestamp()
		);
	}

	/** @throws InvalidDateTimeException */
	public function toDateString (): static
	{
		return $this->map(fn ($value) => tap($this->toCarbonImmutable($value)?->toDateString(),
			fn ($dt) => $this->assertDatetimeValue($dt)
		));
	}

	/** @throws InvalidDateTimeException */
	public function toDateTimeLocalString (string $timePrecision = 'second'): static
	{
		return $this->map(fn ($value) => tap($this->toCarbonImmutable($value)?->toDateTimeLocalString($timePrecision),
			fn ($dt) => $this->assertDatetimeValue($dt)
		));
	}
}
