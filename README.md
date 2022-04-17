# Carbon helpers and CarbonCollection class 

Has some basic helper functions to quickly instantiate \Carbon\Carbon or \Carbon\CarbonImmutable instances
```php
use function Sourcefli\CarbonHelpers\carbon;
use function Sourcefli\CarbonHelpers\carbonImmutable;

//=> now
carbon(); 
carbon('2 weeks ago');

//=> now
carbonImmutable() 
carbonImmutable('3 weeks ago')
```

Also has a helpful CarbonCollection class for common logic when dealing with a collection of datetime values
```php
use Sourcefli\CarbonHelpers\CarbonCollection;

$period = \Carbon\CarbonPeriod::dates('tomorrow', 'next year');

//=> has a carbon instance for every date in the Period
$carbonCollection = CarbonCollection::fromPeriod($period); 

//=> ['2022-01-10', '2022-01-11', '2022-01-12', etc.] 
$carbonCollection->toDateString(); 

//=> ['2022-01-10 12:00:00', '2022-01-11 12:15:30', '2022-01-12 15:45:45', etc.] -> seconds are acknowledged 
$carbonCollection->toDateTimeLocalString('second'); 

//=> ['2022-01-10 12:30:00', '2022-01-11 12:45:00', '2022-01-12 15:45:00', etc.] -> minutes are acknowledged (seconds aren't)
$carbonCollection->toDateTimeLocalString('minute'); 
```

## Installation
You can install the package via composer:

```bash
composer require sourcefli/laravel-carbon-helpers
```

## Usage
More examples:
```php
use Sourcefli\CarbonHelpers\CarbonCollection;

//=> Uses this package's `\Sourcefli\CarbonHelpers\HasDateTimeValues::isADatetimeValue` function to collect datetime values from the request
CarbonCollection::fromRequest();

//=> filters out everything but ['2020-09-10', '2021-09-11', '2023-10-08']
$carbonCollection = CarbonCollection::make([
    'foobar', '2020-09-10', '2021-09-11', new \stdClass, false, 123, '2023-10-08'
]); 

//=> converts all values to \Carbon\CarbonImmutable instances (filters any/all invalid datetime values in the process)
$carbonCollection->asImmutables(); 

//=> converts all values to \Carbon\Carbon instances (filters any/all invalid datetime values in the process)
$carbonCollection->asMutables(); 


//=> looks for the closest Carbon instance from today (seeking into the future)
$carbonCollection->getClosestFromNow(); 

//=> looks for the farthest Carbon instance from today (seeking into the future)
$carbonCollection->getFarthestFromNow(); 


//=> looks for the closest Carbon instance to today (seeking into the past)
$carbonCollection->getClosestToNow(); 

//=> looks for the farthest Carbon instance from today (seeking into the past)
$carbonCollection->getFarthestToNow(); 


$carbonCollection->removeAllByDate(
    //=> dates to be removed
    CarbonCollection::make(['2020-09-10', '2023-10-08']) 
);


$carbonCollection->removeAllByDateTime(
    //=> removes any datetimes on Jan 10th during the noon hour (different precisions can be used in the 2nd param)
    CarbonCollection::make(['2022-01-10 12:00:00']), 
    'hour' 
);

$carbonCollection->sortByTimestamp();

//=> ignored
$carbonCollection->remove('foobar') 


//=> removes any datetimes having this date
$carbonCollection->remove('2023-10-08') 


//=> Removes any values coming after the predicate. ('2023-10-08' is the predicate value used)
$carbonCollection->remove('2023-10-08', fn ($value, $predicate) => carbon($predicate)->isBefore($value)) 
```

## Testing
TODO

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing
PRs and ideas are welcome

## Security Vulnerabilities
Please email me at mail@jhavens.tech to report security vulnerabilities.

## Credits
- [Jonathan Havens](https://github.com/sourcefli)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
