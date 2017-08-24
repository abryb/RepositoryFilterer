<?php

abstract class AbstractArrayHelper implements ArrayHelperInterface
{
    /**
     * @var array
     */
    protected $keysTranslation;

    /**
     * @var array
     */
    protected $valuesTranslations;

    /**
     * AbstractArrayHelper constructor.
     * @param array $keysTranslation
     * @param array $valuesTranslations
     */
    public function __construct(array $keysTranslation = array(), array $valuesTranslations = array())
    {
        $this->keysTranslation    = $keysTranslation;
        $this->valuesTranslations = $valuesTranslations;
    }

    /**
     * @param array $array
     * @return array
     */
    abstract public function convertArrayKeys(array $array) : array;

    /**
     * @param array $array
     * @return array
     */
    abstract public function convertArrayValues(array $array) : array;

    /**
     * @param $array
     * @return bool
     */
    public function isSequentialArray($array) : bool
    {
        if (!is_array($array)) {
            return false;
        }
        return array_keys($array) === range(0, count($array) - 1);
    }

    /**
     * @param $array
     * @return bool
     */
    public function isAssociationArray($array) : bool
    {
        if (!(is_array($array) && $array === array())) {
            return false;
        }
        return array_keys($array) !== range(0, count($array) - 1);
    }

    /**
     * @return array
     */
    public function getKeysTranslation(): array
    {
        return $this->keysTranslation;
    }

    /**
     * @param array $keysTranslation
     * @return AbstractArrayHelper
     */
    public function setKeysTranslation(array $keysTranslation): AbstractArrayHelper
    {
        $this->keysTranslation = $keysTranslation;
        return $this;
    }

    /**
     * @return array
     */
    public function getValuesTranslations(): ?array
    {
        return $this->valuesTranslations;
    }

    /**
     * @param array $valuesTranslations
     * @return AbstractArrayHelper
     */
    public function setValuesTranslations(array $valuesTranslations): AbstractArrayHelper
    {
        $this->valuesTranslations = $valuesTranslations;
        return $this;
    }
}