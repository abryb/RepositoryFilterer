<?php

class ArrayHelper extends AbstractArrayHelper
{
    public function convertArrayKeys(array $array) : array
    {
        $result = [];
        foreach ($array as $k => $v) {
            if (is_array($v)) {
                $result
            }
            if (array_key_exists($k, $this->keysTranslation)) {
                $result[$this->keysTranslation[$k]] = $v;
            }
        }
        return $result;
    }

    public function convertArrayValues(array $array) : array
    {
        return array();
    }

    protected function arrayRecursiveHelper(array $array)
    {
       
    }
}