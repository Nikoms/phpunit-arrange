<?php

namespace Nikoms\PhpUnit\Annotation;

/**
 * @Annotation
 */
class Arrange
{
    /**
     * @var array
     */
    private $methods;

    /**
     * Arrange constructor.
     * @param array $values
     */
    public function __construct(array $values)
    {
        if (isset($values['value'])) {
            $this->methods[$values['value']] = null;
        } else {
            $this->methods = $values;
        }
    }

    /**
     * @return array
     */
    public function getMethods()
    {
        return $this->methods;
    }
}
