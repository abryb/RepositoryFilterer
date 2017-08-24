<?php

/*
 * This file is part of RepositoryFilterer project.
 *
 * (c) B?a?ej Rybarkiewicz <andrzej.blazej.rybarkiewicz@gmail.com>
 */

namespace Abryb\RepositoryFilterer\Filterer;

use Doctrine\ORM\QueryBuilder;

interface RepositoryFilterableInterface
{
    public function filterBy(array $filters, QueryBuilder $qb = null, $joinedAlias = null);
}
