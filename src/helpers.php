<?php

namespace Jhavenz\CarbonHelpers;

use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Carbon\CarbonPeriod;
use DateTimeInterface;
use InvalidArgumentException;

function carbon(null|CarbonPeriod|DateTimeInterface|int|string $value, ?string $tz = null): Carbon
{
	$tz = $tz ?? config('app.timezone');

	return match (true) {
		func_num_args() === 0 || blank($value) => Carbon::now($tz),
		is_int($value) => Carbon::createFromTimestamp($value, $tz),
		is_string($value) || $value instanceof DateTimeInterface => Carbon::parse($value, $tz),
		$value instanceof CarbonPeriod => carbon($value->getStartDate(), $tz),
		default => throw new InvalidArgumentException('Can not instantiate carbon instance using type: '.gettype($value))
	};
}

function carbonImmutable(null|CarbonPeriod|DateTimeInterface|int|string $value, ?string $tz = null): CarbonImmutable
{
	$tz = $tz ?? config('app.timezone');

	return match (TRUE) {
		func_num_args() === 0 || is_null($value) => CarbonImmutable::now($tz),
		is_int($value) => CarbonImmutable::createFromTimestamp($value, $tz),
		is_string($value) || $value instanceof DateTimeInterface => CarbonImmutable::parse($value, $tz),
		$value instanceof CarbonPeriod => carbonImmutable($value->getStartDate(), $tz),
		default => throw new InvalidArgumentException("Can not instantiate carbon immutable instance using type: ". gettype($value))
	};
}
