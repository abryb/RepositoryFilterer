<?php

namespace Abryb\RepositoryFilter\ArrayHelper;

interface ArrayHelperInterface
{
    public function convertArrayKeys(array $array) : array;

    public function convertArrayValues(array $array) : array;

    public function isSequentialArray($array) : bool;

    public function isAssociationArray($array) : bool;
}