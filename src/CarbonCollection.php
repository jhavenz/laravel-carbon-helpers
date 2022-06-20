<?php

namespace Jhavenz\CarbonHelpers;

use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Carbon\CarbonPeriod;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Jhavenz\CarbonHelpers\Exceptions\InvalidDateTimeException;
use LogicException;

class CarbonCollection extends Collection
{
	use HasDateTimeValues;

	/** @noinspection PhpMissingParentConstructorInspection */
	public function __construct($items = [])
	{
		$this->items = $this->withoutInvalidDatetimeValues($this->getArrayableItems($items));
	}

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

	public static function fromRequest(?Request $request = null): static
	{
		$self = new static;

		foreach (($request ?? request())->all() as $value) {
			if (! $self->isADatetimeValue($value)) {
				continue;
			}

			$self->add($value);
		}

		return $self;
	}

	public static function fromPeriod (CarbonPeriod $period, bool $toImmutables = true): CarbonCollection
	{
		return static::make($period->toArray())->when($toImmutables,
			fn (CarbonCollection $self) => $self->asImmutables(),
			fn (CarbonCollection $self) => $self->asMutables(),
		);
	}

	public function getClosestFromNow (): CarbonImmutable
	{
		return $this->removeAllInThePast()->whenNotEmpty(
			fn (CarbonCollection $self) => $self->sortByTimestamp()->first(),
			fn () => throw new \RangeException('No dates in the future were found in this collection')
		);
	}

	public function getFarthestFromNow (): CarbonImmutable
	{
		return $this->removeAllInThePast()->whenNotEmpty(
			fn (CarbonCollection $self) => $self->sortByTimestamp()->last(),
			fn () => throw new \RangeException('No dates in the future were found in this collection')
		);
	}

	public function getClosestToNow (): CarbonImmutable
	{
		return $this->removeAllInTheFuture()->whenNotEmpty(
			fn (CarbonCollection $self) => $self->sortByTimestamp()->first(),
			fn () => throw new \RangeException("No dates from the past were found in this collection")
		);
	}

	public function getFarthestToNow (): CarbonImmutable
	{
		return $this->removeAllInTheFuture()->whenNotEmpty(
			fn (CarbonCollection $self) => $self->sortByTimestamp()->last(),
			fn () => throw new \RangeException('No dates from the past were found in this collection')
		);
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

	/**
	 * $comparatorMethod signature: function(CarbonInterface $existingValue, mixed $removalValue): bool {...}
	 * Returning true will remove the datetime value from this collection
	 */
	public function remove (mixed $datetime, string|Closure $comparatorMethod = 'is'): static
	{
		if (! is_iterable($datetime)) {
			if (! $this->isADatetimeValue($datetime)) {
				return $this;
			}

			foreach ($this as $key => $dtValue) {
				if (is_string($comparatorMethod)) {
					if (carbonImmutable($datetime)->{$comparatorMethod}($dtValue)) {
						unset($this[$key]);
					}
					continue;
				}

				$uCompare = $comparatorMethod($dtValue, $datetime);
				if (! is_bool($uCompare)) {
					throw new LogicException("comparator method must return a boolean that determines whether the existing datetime should be removed");
				}

				if (true === $uCompare) {
					unset($this[$key]);
				}
			}

			return $this;
		}

		foreach ($datetime as $removal) {
			$this->remove($removal);
		}

		return $this;
	}

	public function removeAllByDate (CarbonCollection $exclusions): static
	{
		return $this->remove($exclusions, 'isSameDay');
	}

	public function removeAllByDateTime (CarbonCollection $exclusions, string $precision = 'second'): static
	{
		return $this->remove(
			$exclusions,
			fn (CarbonInterface $existing, mixed $removal): bool => carbonImmutable($removal)->toDateTimeString($precision) === $existing->toDateTimeString($precision)
		);
	}

	public function removeAllInTheFuture(): static
	{
		$self = clone $this;

		$exclusions = $self->asImmutables()->filter(fn (CarbonInterface $value) => $value->isAfter(now()));

		return $this->removeAllByDate($exclusions);
	}

	public function removeAllInThePast(): static
	{
		$self = clone $this;

		$exclusions = $self->asImmutables()->filter(fn (CarbonInterface $value) => $value->isBefore(now()));

		return $this->removeAllByDateTime($exclusions);
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
