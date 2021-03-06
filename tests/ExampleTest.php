<?php

namespace Nikoms\PhpUnit;

use Nikoms\PhpUnit\Annotation\Arrange;

class ExampleTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var User
     */
    private $user;
    static private $callCount = array();
    private $arrayStored;

    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();
        self::$callCount = array(
            'dataProvider' => 0,
            'provideSomeNames' => 0,
        );
    }


    public function doNothing()
    {

    }

    /**
     * @Arrange(describe="the user initialized is connected")
     */
    public function initConnectedUser()
    {
        $this->user = new User();
        $this->user->isConnected = true;
    }

    /**
     * @Arrange(describe="the user initialized is named '%s'")
     * @param string $name
     */
    public function initUserNamed($name)
    {
        $this->user = new User();
        $this->user->name = $name;
    }

    /**
     * @Arrange(describe="The user is connected")
     * @return User
     */
    public function theUserIsConnected()
    {
        $user = new User();
        $user->isConnected = true;

        return $user;
    }

    /**
     * @Arrange(describe="the connected user is named '%s'")
     * @param string $name
     * @return User
     */
    public function theConnectedUserIsNamed($name)
    {
        $user = new User();
        $user->isConnected = true;
        $user->name = $name;

        return $user;
    }

    /**
     * @Arrange(describe="%s is a member of the group '%s'")
     * @param User $user
     * @param string $group
     * @return User
     */
    public function memberOfGroup($user, $group = '')
    {
        $this->assertInstanceOf('Nikoms\PhpUnit\User', $user);
        $user->group = $group;

        return $user;
    }

    /**
     * @Arrange("methodDoesNotExist")
     */
    public function test_a_non_existing_method_should_only_trigger_a_notice()
    {
        //Nothing is broken when the method does not exist
        $this->assertTrue(true);
    }

    /**
     * @Arrange("initConnectedUser")
     */
    public function test_an_existing_method_is_called_before_the_test()
    {
        $this->assertInstanceOf('Nikoms\PhpUnit\User', $this->user);
        $this->assertTrue($this->user->isConnected);
    }

    /**
     * @Arrange(initUserNamed="Nicolas")
     */
    public function test_an_arrange_method_could_receive_an_argument()
    {
        $this->assertInstanceOf('Nikoms\PhpUnit\User', $this->user);
        $this->assertSame('Nicolas', $this->user->name);
    }

    /**
     * @testdox the user is passed to the test method
     * @Arrange(theConnectedUserIsNamed="Nicolas")
     */
    public function test_when_an_arrange_method_returns_something_then_it_is_passed_to_the_test_method()
    {
        $args = func_get_args();
        $this->assertCount(1, $args);
        $this->assertInstanceOf('Nikoms\PhpUnit\User', $args[0]);
        $this->assertTrue($args[0]->isConnected);
        $this->assertSame('Nicolas', $args[0]->name);
    }

    /**
     * @Arrange(theConnectedUserIsNamed="Nicolas")
     * @Arrange("doNothing")
     * @Arrange(theConnectedUserIsNamed="Laura")
     * @param User $nicolas
     * @param User $laura
     */
    public function test_it_does_not_pass_null_arguments(User $nicolas, User $laura)
    {
        $this->assertSame('Nicolas', $nicolas->name);
        $this->assertSame('Laura', $laura->name);
    }

    /**
     * @testdox the user has the given name and the given group
     * @Arrange(theConnectedUserIsNamed="Nicolas", memberOfGroup=User::GROUP_ADMIN)
     * @param User $user
     */
    public function test_the_output_of_an_arrange_method_is_used_as_first_argument_for_the_next_arrange_method($user)
    {
        $this->assertInstanceOf('Nikoms\PhpUnit\User', $user);
        $this->assertSame('Nicolas', $user->name);
        $this->assertTrue($user->isConnected);
        $this->assertSame(User::GROUP_ADMIN, $user->group);
    }

    /**
     * @return array
     */
    public function dataProvider()
    {
        return array(
            array('dataFromDataProvider1'),
            array('dataFromDataProvider2'),
        );
    }

    /**
     * @dataProvider dataProvider
     * @Arrange("theUserIsConnected")
     */
    public function test_data_from_arrange_method_comes_after_the_data_from_data_provider()
    {
        self::$callCount['dataProvider']++;
        $dataProviderValue = sprintf('dataFromDataProvider%d', self::$callCount['dataProvider']);
        $args = func_get_args();
        $this->assertCount(2, $args);

        //First argument is the data provided by "@dataProvider"
        $this->assertSame($dataProviderValue, $args[0]);

        //Second argument is a user connected
        $this->assertInstanceOf('Nikoms\PhpUnit\User', $args[1]);
        $this->assertTrue($args[1]->isConnected);
    }


    /**
     * @return array
     */
    public function provideNames()
    {
        return array(
            array('Nicolas 1'),
            array('Nicolas 2'),
        );
    }

    /**
     * @dataProvider provideNames
     * @Arrange("theConnectedUserIsNamed")
     *
     * @param string $dataProviderValue
     * @param User $user
     */
    public function test_arrange_method_receives_the_data_from_data_provider($dataProviderValue, User $user)
    {
        self::$callCount['provideSomeNames']++;
        $guessedDataProviderValue = sprintf('Nicolas %d', self::$callCount['provideSomeNames']);

        $this->assertSame($guessedDataProviderValue, $dataProviderValue);

        //The user has the name given by the "@dataProvider"
        $this->assertSame($dataProviderValue, $user->name);
    }


    /**
     */
    public function returnAllReceivesArguments()
    {
        return func_get_args();
    }

    /**
     * @return array
     */
    public function provideName()
    {
        return array(
            array('Nicolas', 'Laura'),
        );
    }

    /**
     * @Arrange(returnAllReceivesArguments="annotation argument")
     * @dataProvider provideName
     *
     * @param $firstDataProvider
     * @param $secondDataProvider
     * @param array $receivedArrangeArguments
     */
    public function test_arrange_method_receives_first_data_provider_values_then_annotation_argument(
        $firstDataProvider,
        $secondDataProvider,
        $receivedArrangeArguments
    ) {
        $this->assertSame(
            array(
                'Nicolas',
                'Laura',
                'annotation argument',
            ),
            $receivedArrangeArguments
        );
    }


    public function storeArray(
        $dataToStore
    ) {
        $this->arrayStored = $dataToStore;
    }

    /**
     * @Arrange(storeArray={"country"="BE","user"="Nicolas"})
     */
    public function test_arrange_method_receives_an_array_when_array_is_given()
    {
        $this->assertSame(
            array(
                'country' => 'BE',
                'user' => 'Nicolas',
            ),
            $this->arrayStored
        );
    }

}

class User
{
    const GROUP_ADMIN = 'ADMIN';
    const GROUP_USER = 'USER';
    public $isConnected;
    public $name;
    public $group;
}