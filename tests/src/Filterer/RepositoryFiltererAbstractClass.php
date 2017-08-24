<?php
/**
 * Created by PhpStorm.
 * User: blazej
 * Date: 24.08.17
 * Time: 13:51
 */

namespace Tests\Abryb\RepositoryFilterer\Filterer;

use Abryb\RepositoryFilterer\Filterer\RepositoryFiltererAbstract;

class RepositoryFiltererAbstractClass extends RepositoryFiltererAbstract
{
    public function filterBy(array $filters, \Doctrine\ORM\QueryBuilder $qb = null, $joinedAlias = null): \Doctrine\ORM\QueryBuilder
    {
        return $qb;
    }
}