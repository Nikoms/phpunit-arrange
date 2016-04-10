[![SensioLabsInsight](https://insight.sensiolabs.com/projects/772fac18-f9cd-47dd-a6e0-420acfa1d815/mini.png)](https://insight.sensiolabs.com/projects/772fac18-f9cd-47dd-a6e0-420acfa1d815)
[![Build Status](https://api.travis-ci.org/Nikoms/phpunit-arrange.png)](https://api.travis-ci.org/Nikoms/phpunit-arrange)
[![Scrutinizer Quality Score](https://scrutinizer-ci.com/g/Nikoms/phpunit-arrange/badges/quality-score.png)](https://scrutinizer-ci.com/g/Nikoms/phpunit-arrange/)
[![Code Coverage](https://scrutinizer-ci.com/g/Nikoms/phpunit-arrange/badges/coverage.png)](https://scrutinizer-ci.com/g/Nikoms/phpunit-arrange/)


# phpunit-arrange

* Do "Arrange" (Arrange-Act-Assert style) in annotations
* Make your tests easier to read
* Dramatically reduce your messy "setUp" method!
* Only put the real important mock/expect in your test method, the rest is the annotation


## Installation

### Composer

Simply add this to your `composer.json` file:
```js
"require": {
    "nikoms/phpunit-arrange": "dev-master"
}
```

Then run `php composer.phar install`

### PhpUnit configuration

To activate the plugin. Add the listener to your phpunit.xml(.dist) file:

```xml
<?xml version="1.0" encoding="UTF-8"?>
<phpunit>
    ...
    <listeners>
        <listener class="Nikoms\PhpUnit\Listener\ArrangeListener" file="vendor/nikoms/phpunit-arrange/src/ArrangeListener.php" />
    </listeners>
</phpunit>
```


## TO DO

* Write the doc (basically, it's just adding a listener)
* Try to integrate it in "--testdox"
