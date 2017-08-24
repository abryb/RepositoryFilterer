<?php

namespace Abryb\RepositoryFilter\ArrayHelper;

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
    public function convertArrayKeys(array $array) : array
    {
        $result = [];
        foreach ($array as $k => $v) {
            if (is_array($v)) {
                $v = $this->convertArrayKeys($v);
            }
            if (array_key_exists($k, $this->keysTranslation)) {
                $result[$this->keysTranslation[$k]] = $v;
            } else {
                $result[$k] = $v;
            }
        }
        return $result;
    }

    /**
     * @param array $array
     * @return array
     */
    public function convertArrayValues(array $array) : array
    {
        $result = [];
        foreach ($array as $k => $v) {
            if (is_array($v)) {
                $v = $this->convertArrayValues($v);
            }
            if ((is_string($v) || is_int($v)) && array_key_exists($v, $this->valuesTranslations)) {
                $result[$k] = $this->valuesTranslations[$v];
            } else {
                $result[$k] = $v;
            }
        }
        return $result;
    }

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