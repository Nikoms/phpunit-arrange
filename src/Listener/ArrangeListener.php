<?php

namespace Nikoms\PhpUnit\Listener;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\AnnotationRegistry;
use Doctrine\Common\Annotations\DocParser;
use Nikoms\PhpUnit\Annotation\Arrange;
use PHPUnit_Framework_Test;

class ArrangeListener extends \PHPUnit_Framework_BaseTestListener
{
    /**
     * @var array
     */
    private $ignoredAnnotationNames;

    /**
     * ArrangeListener constructor.
     * @param array $ignoredAnnotationNames
     */
    public function __construct(array $ignoredAnnotationNames = null)
    {
        AnnotationRegistry::registerFile(__DIR__.'/../Annotation/Arrange.php');

        //For old AnnotationReader (<=1.2.7)
        if ($ignoredAnnotationNames === null) {
            $ignoredAnnotationNames = array(
                'author',
                'after',
                'afterClass',
                'backupGlobals',
                'backupStaticAttributes',
                'before',
                'beforeClass',
                'codeCoverageIgnore',
                'codeCoverageIgnoreStart',
                'codeCoverageIgnoreEnd',
                'covers',
                'coversDefaultClass',
                'coversNothing',
                'dataProvider',
                'depends',
                'expectedException',
                'expectedExceptionCode',
                'expectedExceptionMessage',
                'expectedExceptionMessageRegExp',
                'group',
                'large',
                'medium',
                'preserveGlobalState',
                'requires',
                'runTestsInSeparateProcesses',
                'runInSeparateProcess',
                'small',
                'test',
                'testdox',
                'ticket',
                'uses',
            );
        }
        $this->ignoredAnnotationNames = $ignoredAnnotationNames;
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

        $annotationReader = $this->getAnnotationReader();
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

                if ($annotationArguments !== null) {
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

    /**
     * @return AnnotationReader
     */
    private function getAnnotationReader()
    {
        //For new (>1.2.7) version of AnnotationReader, we can give a DocParser since a3c2928912eeb5dc5678352f22c378173def16b6
        $parser = new DocParser();
        $parser->setIgnoreNotImportedAnnotations(true);
        $annotationReader = new AnnotationReader($parser);

        //For old version of AnnotationReader (<=1.2.7) , we have to specify manually all ignored annotations
        foreach ($this->ignoredAnnotationNames as $ignoredAnnotationName) {
            $annotationReader->addGlobalIgnoredName($ignoredAnnotationName);
        }

        return $annotationReader;
    }
}
