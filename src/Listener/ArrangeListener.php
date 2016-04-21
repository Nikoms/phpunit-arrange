<?php

namespace Nikoms\PhpUnit\Listener;

use Doctrine\Common\Annotations\AnnotationRegistry;
use Nikoms\PhpUnit\Annotation\Arrange;
use Nikoms\PhpUnit\AnnotationReaderFactory;
use PHPUnit_Framework_Test;

/**
 * Class ArrangeListener
 * @package Nikoms\PhpUnit\Listener
 */
class ArrangeListener extends \PHPUnit_Framework_BaseTestListener
{
    /**
     * @var array
     */
    public static $inputs;

    /**
     * ArrangeListener constructor.
     * @param array $ignoredAnnotationNames
     */
    public function __construct(array $ignoredAnnotationNames = null)
    {
        AnnotationRegistry::registerFile(__DIR__.'/../Annotation/Arrange.php');
    }

    /**
     * @param  PHPUnit_Framework_Test $test
     */
    public function startTest(PHPUnit_Framework_Test $test)
    {
        if ($test instanceof \PHPUnit_Framework_TestCase) {
            $this->setUpContext($test);
        }
    }

    /**
     * @param \PHPUnit_Framework_TestCase $testCase
     */
    private function setUpContext(\PHPUnit_Framework_TestCase $testCase)
    {
        $testMethodArguments = array();
        $reflectionMethod = new \ReflectionMethod($testCase, $testCase->getName(false));

        $annotationReader = AnnotationReaderFactory::getAnnotationReader();
        $annotations = $annotationReader->getMethodAnnotations($reflectionMethod);
        foreach ($annotations as $i => $annotation) {
            if ($annotation instanceof Arrange) {
                $arrangeOutput = $this->runAnnotations($testCase, $annotation, $i);
                if ($arrangeOutput !== null) {
                    $testMethodArguments[] = $arrangeOutput;
                }
            }
        }
        $testCase->setDependencyInput($testMethodArguments);
    }

    /**
     * @param \PHPUnit_Framework_TestCase $testCase
     * @param Arrange $annotation
     * @param int $annotationId
     * @return mixed
     */
    private function runAnnotations(\PHPUnit_Framework_TestCase $testCase, Arrange $annotation, $annotationId)
    {
        $arrangeOutput = null;
        foreach ($annotation->getMethods() as $method => $annotationArguments) {
            if (method_exists($testCase, $method)) {
                $givenArgument = array();

                if ($arrangeOutput !== null) {
                    $givenArgument[] = $arrangeOutput;
                }

                $dataProviderArguments = $testCase->readAttribute($testCase, 'data');
                if (!empty($dataProviderArguments)) {
                    $givenArgument = array_merge($givenArgument, $dataProviderArguments);
                }

                if ($annotationArguments !== null) {
                    $givenArgument[] = $annotationArguments;
                }
                self::$inputs[$testCase->getName(true)][$annotationId][$method] = $givenArgument;
                $arrangeOutput = call_user_func_array(array($testCase, $method), $givenArgument);
            } else {
                trigger_error(
                    sprintf('Error on @Arrange annotation: Impossible to call "%s" method', $method),
                    E_USER_NOTICE
                );
            }
        }

        return $arrangeOutput;
    }
}
