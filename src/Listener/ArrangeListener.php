<?php

namespace Nikoms\PhpUnit\Listener;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\AnnotationRegistry;
use Doctrine\Common\Annotations\DocParser;
use Nikoms\PhpUnit\Annotation\Arrange;
use PHPUnit_Framework_Test;

class ArrangeListener extends \PHPUnit_Framework_BaseTestListener
{
    public function __construct()
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

        $parser = new DocParser();
        $parser->setIgnoreNotImportedAnnotations(true);
        $annotationReader = new AnnotationReader($parser);

        $annotations = $annotationReader->getMethodAnnotations($reflectionMethod);
        foreach ($annotations as $annotation) {
            if ($annotation instanceof Arrange) {
                $arrangeOutput = $this->runAnnotations($testCase, $annotation);
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
     * @return mixed
     */
    private function runAnnotations(\PHPUnit_Framework_TestCase $testCase, Arrange $annotation)
    {
        $dataProviderArguments = $testCase->readAttribute($testCase, 'data');
        $arrangeOutput = null;
        foreach ($annotation->getMethods() as $method => $annotationArguments) {
            if (method_exists($testCase, $method)) {
                $givenArgument = array();

                if ($arrangeOutput !== null) {
                    $givenArgument[] = $arrangeOutput;
                }

                if (!empty($dataProviderArguments)) {
                    $givenArgument = array_merge($givenArgument, $dataProviderArguments);
                }

                if($annotationArguments !== null){
                    $givenArgument[] = $annotationArguments;
                }
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
