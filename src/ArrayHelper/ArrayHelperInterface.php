<?php

interface ArrayHelperInterface
{
    public function convertArrayKeys(array $array) : array;

    public function convertArrayValues(array $array) : array;

    public function isSequentialArray(array $array) : bool;

    public function isAssociationArray(array $array) : bool;
}