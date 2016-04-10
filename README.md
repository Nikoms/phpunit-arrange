[![SensioLabsInsight](https://insight.sensiolabs.com/projects/772fac18-f9cd-47dd-a6e0-420acfa1d815/mini.png)](https://insight.sensiolabs.com/projects/772fac18-f9cd-47dd-a6e0-420acfa1d815)
[![Scrutinizer Quality Score](https://scrutinizer-ci.com/g/Nikoms/phpunit-arrange/badges/quality-score.png)](https://scrutinizer-ci.com/g/Nikoms/phpunit-arrange/)
[![Code Coverage](https://scrutinizer-ci.com/g/Nikoms/phpunit-arrange/badges/coverage.png)](https://scrutinizer-ci.com/g/Nikoms/phpunit-arrange/)


# phpunit-arrange

## When to use it?

* You want to reduce your messy "setUp" methods that init objects for ALL tests. But most of the time, they are not required all together.
* You want to have explicit setup/arrange for each tests... Or reuse them!
* You want to have tests easy to read
* You want to put forward real important setup/expectations/fixtures and put the rest as annotations

It's time to refactor your ugly "setUp"!

## Compatibility

* PHP: Compatible with PHP 5.3 to the last version (7.1) and HHVM
* PHPUnit: Compatible with version 3.*, 4.*, 5.*

See build [here](https://travis-ci.org/Nikoms/phpunit-arrange). Some builds fail because some version of PHPUnit are not compatible with some PHP versions.

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

## Usage

"Use" the plugin on the top of your test:

```php
use Nikoms\PhpUnit\Annotation\Arrange;
```

And then, let the magic begin...


### Call an arrange method without argument

It's possible to easily call a custom "setUp" method before the test.

```php
use Nikoms\PhpUnit\Annotation\Arrange;

class ExampleTest extends \PHPUnit_Framework_TestCase
{

    public function initConnectedUser()
    {
        $this->user = new User();
        $this->user->isConnected = true;
    }

    /**
     * @Arrange("initConnectedUser")
     */
    public function testSomething(User $user)
    {
        $this->assertSame('Nicolas', $this->user->name);
        // Do something with your $this->user..
    }
}
```

### Call an arrange method with an argument

It's possible to give an argument to the arrange method. It works with a string, an array, a constant, etc...

```php
use Nikoms\PhpUnit\Annotation\Arrange;

class ExampleTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @param string $name
     * @return User
     */
    public function iAmConnectedWithTheName($name)
    {
        $this->user = new User();
        $this->user->isConnected = true;
        $this->user->name = $name;
    }

    /**
     * @Arrange(iAmConnectedWithTheName="Nicolas")
     */
    public function testSomething()
    {
        $this->assertSame('Nicolas', $this->user->name);
        // Do something with your $this->user..
    }
}
```

### Get something from the arrange method

If the arrange method returns something, then it is put as argument in the test method

```php
use Nikoms\PhpUnit\Annotation\Arrange;

class ExampleTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @param string $name
     * @return User
     */
    public function iAmConnectedWithTheName($name)
    {
        $user = new User();
        $user->isConnected = true;
        $user->name = $name;

        return $user;
    }

    /**
     * @Arrange(iAmConnectedWithTheName="Nicolas")
     */
    public function testSomething(User $user)
    {
        $this->assertSame('Nicolas', $user->name);
        // Do something with your user..
    }
}
```

### Chain calls of arrange methods

If the arrange method returns something, then it is put as argument in the test method

```php
use Nikoms\PhpUnit\Annotation\Arrange;

class ExampleTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @param string $name
     * @return User
     */
    public function iAmConnectedWithTheName($name)
    {
        $user = new User();
        $user->isConnected = true;
        $user->name = $name;

        return $user;
    }

    /**
     * @param User $user
     * @param string $group
     * @return User
     */
    public function memberOfGroup($user, $group)
    {
        $user->group = $group;

        return $user;
    }

    /**
     * @Arrange(iAmConnectedWithTheName="Nicolas", memberOfGroup=User::GROUP_ADMIN)
     * @param User $user
     */
    public function testSomething(User $user)
    {
        $this->assertSame(User::GROUP_ADMIN, $user->group);
        $this->assertSame('Nicolas', $user->name);
        // Do something with your user..
    }
}
```

### Use as many @Arrange as you want

Of course, it's possible to have multiple arrange for the same test method

```php
use Nikoms\PhpUnit\Annotation\Arrange;

class ExampleTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @param string $name
     * @return User
     */
    public function iAmConnectedWithTheName($name)
    {
        $user = new User();
        $user->isConnected = true;
        $user->name = $name;

        return $user;
    }
    /**
     * @Arrange(iAmConnectedWithTheName="Nicolas")
     * @Arrange(iAmConnectedWithTheName="Laura")
     * @param User $user
     */
    public function testSomething(User $nicolas, User $laura)
    {
        $this->assertSame('Nicolas', $nicolas->name);
        $this->assertSame('Laura', $laura->name);
        // Do something with your user..
    }
}
```

### Combine it with @dataProvider

*@dataProvider* can be used to fill arrange methods.


```php
use Nikoms\PhpUnit\Annotation\Arrange;

class ExampleTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @return array
     */
    public function provideNames()
    {
        return array(
            array('Nicolas'),
            array('Laura'),
        );
    }

    /**
     * @param string $name
     * @return User
     */
    public function iAmConnectedWithTheName($name)
    {
        $user = new User();
        $user->isConnected = true;
        $user->name = $name;

        return $user;
    }

    /**
     * @dataProvider provideNames
     * @Arrange("iAmConnectedWithTheName")
     *
     * @param string $dataProviderValue
     * @param User $user
     */
    public function testSomething($dataProviderValue, User $user)
    {
        // The user has the name given by the "@dataProvider":
        // First "Nicolas", then "Laura".
        $this->assertSame($dataProviderValue, $user->name);

        // Do something with your user..
    }
}
```

## Why calling it arrange?

Because it's the name of a step in tests [as explained here](http://integralpath.blogs.com/thinkingoutloud/2005/09/principles_of_t.html).
> **Follow the "3-As" pattern for test methods**: Arrange, Act, Assert.
Specifically, use separate code paragraphs (groups of lines of code separated by a blank line) for each of the As.
* ***Arrange*** is variable declaration and initialization.
* Act is invoking the code under test.
* Assert is using the Assert.* methods to verify that expectations were met.
Following this pattern consistently makes it easy to revisit test code.


## TO DO

* Try to integrate it in "--testdox"
