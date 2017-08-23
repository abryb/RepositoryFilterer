<?php
/**
 * Created by PhpStorm.
 * User: bazej
 * Date: 23.08.17
 * Time: 22:55
 */

class ArrayHelper extends AbstractArrayHelper
{
    public function convertArrayKeys(array $array) : array
    {
        return array();
    }

    public function convertArrayValues(array $array) : array
    {
        return array();
    }

    public function isSequentialArray(array $array) : bool
    {
        if (!is_array($array)) {
            return false;
        }
        return array_keys($array) === range(0, count($array) - 1);
    }


    public function isAssociationArray(array $array) : bool
    {
        if (!(is_array($array) && $array === array())) {
            return false;
        }
        return array_keys($array) !== range(0, count($array) - 1);
    }
}