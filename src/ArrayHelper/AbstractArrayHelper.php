<?php
/**
 * Created by PhpStorm.
 * User: bazej
 * Date: 23.08.17
 * Time: 22:51
 */

abstract class AbstractArrayHelper implements ArrayHelperInterface
{
    protected $keysTranslation;

    protected $valuesTranslations;

    public function __construct(array $keysTranslation = array(), array $valuesTranslations = array())
    {
        $this->keysTranslation = $keysTranslation;
        $this->valuesTranslations = $valuesTranslations;
    }

    abstract public function convertArrayKeys(array $array) : array;

    abstract public function convertArrayValues(array $array) : array;

    abstract public function isSequentialArray(array $array) : bool;

    abstract public function isAssociationArray(array $array) : bool;
}