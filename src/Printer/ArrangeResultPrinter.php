<?php

namespace Nikoms\PhpUnit\Printer;

use Doctrine\Common\Annotations\AnnotationReader;
use Nikoms\PhpUnit\Annotation\Arrange;
use Nikoms\PhpUnit\AnnotationReaderFactory;
use Nikoms\PhpUnit\Listener\ArrangeListener;
use PHPUnit_Framework_Test;

/**
 * Class ArrangeResultPrinter
 * @package Nikoms\PhpUnit\Printer
 */
class ArrangeResultPrinter extends \PHPUnit_Util_TestDox_ResultPrinter_Text
{
    /**
     * @param \PHPUnit_Framework_Test | \PHPUnit_Framework_TestCase $test
     */
    public function startTest(PHPUnit_Framework_Test $test)
    {
        parent::startTest($test);
        if ($this->currentTestMethodPrettified == null || empty(ArrangeListener::$inputs[$test->getName(true)])) {
            return;
        }

        $arranges = $this->getArrangeLines($test);
        $arrangeText = $this->getArrangeText($arranges);
        $this->currentTestMethodPrettified = $arrangeText.$this->currentTestMethodPrettified;
    }

    /**
     * @param array $arranges
     * @return string
     */
    private function getArrangeText(array $arranges)
    {
        $arrangeText = '';
        $tab = '     ';
        if (!empty($arranges)) {
            $arrangeText = 'When '
                .implode(PHP_EOL.$tab.'And ', $arranges)
                .PHP_EOL
                .$tab
                .'Then ';

            return $arrangeText;
        }

        return $arrangeText;
    }

    /**
     * @param \PHPUnit_Framework_TestCase $test
     * @return array
     */
    private function getArrangeLines(\PHPUnit_Framework_TestCase $test)
    {
        $arranges = [];
        $annotationReader = AnnotationReaderFactory::getAnnotationReader();

        foreach (ArrangeListener::$inputs[$test->getName(true)] as $i => $arrangeMethods) {
            foreach ($arrangeMethods as $arrangeMethod => $arguments) {
                try {
                    $describe = $this->getArrangeDescription($test, $annotationReader, $arrangeMethod);
                    $arranges[] = $this->getArrangeLine($describe, $arguments);
                } catch (\DomainException $ex) {
                    continue;
                }
            }
        }

        return $arranges;
    }

    /**
     * @param PHPUnit_Framework_Test $test
     * @param AnnotationReader $annotationReader
     * @param string $arrangeMethod
     * @return string
     */
    private function getArrangeDescription(
        PHPUnit_Framework_Test $test,
        AnnotationReader $annotationReader,
        $arrangeMethod
    ) {
        $arrangeMethodAnnotation = $annotationReader->getMethodAnnotation(
            new \ReflectionMethod($test, $arrangeMethod),
            Arrange::class
        );
        if ($arrangeMethodAnnotation === null) {
            throw new \DomainException('The arrange method does not have an annotation itself');
        }

        return $arrangeMethodAnnotation->getMethods()['describe'];
    }

    /**
     * @param string $describe
     * @param array $arguments
     * @return array
     */
    private function getArrangeLine($describe, array $arguments)
    {
        return call_user_func_array(
            'sprintf',
            array_merge(
                [$describe],
                $this->getArgumentsAsString($arguments)
            )
        )
        .',';
    }

    /**
     * @param array $arguments
     * @return array
     */
    private function getArgumentsAsString(array $arguments)
    {
        return array_map(
            function ($argument) {
                if (is_object($argument)) {
                    $fullClassName = get_class($argument);

                    return 'the '.strtolower(substr($fullClassName, strrpos($fullClassName, '\\') + 1));
                }

                return $argument;
            },
            $arguments
        );
    }
}