<?php

namespace Nikoms\PhpUnit;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\DocParser;

/**
 * Class AnnotationReaderFactory
 * @package Nikoms\PhpUnit
 */
class AnnotationReaderFactory
{
    /**
     * @var AnnotationReader
     */
    private static $annotationReader;

    /**
     * @var array
     */
    private static $ignoredAnnotationNames = array(
        //All known phpunit annotations
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

    /**
     * @return AnnotationReader
     */
    public static function getAnnotationReader()
    {
        if (self::$annotationReader !== null) {
            return self::$annotationReader;
        }

        //For old AnnotationReader (<=1.2.7)
        //For new (>1.2.7) version of AnnotationReader, we can give a DocParser since a3c2928912eeb5dc5678352f22c378173def16b6
        $parser = new DocParser();
        $parser->setIgnoreNotImportedAnnotations(true);
        self::$annotationReader = new AnnotationReader($parser);

        //For old version of AnnotationReader (<=1.2.7) , we have to specify manually all ignored annotations
        foreach (self::$ignoredAnnotationNames as $ignoredAnnotationName) {
            self::$annotationReader->addGlobalIgnoredName($ignoredAnnotationName);
        }

        return self::$annotationReader;
    }
}