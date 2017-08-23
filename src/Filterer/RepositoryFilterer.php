<?php

/*
 * This file is part of RepositoryFilterer project.
 *
 * (c) B?a?ej Rybarkiewicz <andrzej.blazej.rybarkiewicz@gmail.com>
 */

namespace Abryb\RepositoryFilterer\Filterable;

use Doctrine\DBAL\Types\Type;
use Doctrine\ORM\QueryBuilder;

class RepositoryFilterer extends RepositoryFiltererAbstract
{
    /**
     * @var string root entity alias
     */
    protected $rootAlias = self::VALUE_DEFAULT_ALIAS;

    protected function getAliasedProperty($property)
    {
        return sprintf('%s.%s', $this->rootAlias, $property);
    }

    /**
     * @param array             $filters
     * @param QueryBuilder|null $qb
     * @param null              $joinedAlias
     *
     * @return QueryBuilder
     */
    public function filterBy(array $filters, QueryBuilder $qb = null, $joinedAlias = null): QueryBuilder
    {
        $alias = $this->getSetting(self::KEY_ALIAS);
        $entityName = $this->entityRepository->getClassName();

        // Remove blocked filters
        $filters = array_filter($filters, function ($f) {
            return !in_array($f, $this->getSetting(self::KEY_BLOCKED_FILTERS), true);
        }, ARRAY_FILTER_USE_KEY);

        // Check QueryBuilder and alias.
        if ($qb === null) {
            $qb = $this->entityRepository->createQueryBuilder($alias);
        } else {
            if ($joinedAlias) {
                $this->rootAlias = $joinedAlias;
            } elseif (null === $alias = $qb->getRootAliases()[0] ?? null) {
                $qb->select($alias)->from($entityName, $alias);
            } else {
                $isRootThisClass = array_filter($qb->getRootEntities(), function ($class) use ($entityName) {
                    return $class === $entityName;
                });
                if ($isRootThisClass) {
                    $this->rootAlias = $alias;
                }
            }
        }

        // Iterate through filters, but first apply defined filters
        $this->applyDefinedFilter($qb);

        foreach ($filters as $property => $value) {
            // If defined filter - continue
            if (in_array($property, $this->getDefinedFiltersNames(), true)) {
                continue;
            }
            // if value is null, just do it, and continue
            if ($value === null) {
                $this->filterValueIsNull($qb, $property);
                continue;
            }
            // if value is const VALUE_NOT_NULL, also do it and continue
            if ($value === $this->getSetting(self::KEY_DEFAULT_NOT_NULL)) {
                $this->filterValueIsNotNull($qb, $property);
                continue;
            }

            /*
             * Important part
             */

            // If entity has property
            if ($this->classMetadata->hasField($property)) {
                $this->filterField($qb, $property, $value);

                // If entity has association
            } elseif ($this->classMetadata->hasAssociation($property)) {
                $this->filterAssociation($qb, $property, $value);
            }
        }

        return $qb;
    }

    protected function applyDefinedFilter(QueryBuilder $qb)
    {
        /**
         * TODO
         */
    }

    protected function filterField(QueryBuilder $qb, $property, $value)
    {
        $aliasProperty = $this->getAliasedProperty($property);

        switch ($this->getFieldMappingType($property)) {
            // group numbers
            case self::TYPE_SMALLINT:
            case self::TYPE_BIGINT:
            case self::TYPE_INTEGER:
            case self::TYPE_DECIMAL:
            case self::TYPE_FLOAT:
                $this->filterNumber($qb, $aliasProperty, $value);
                break;
            case self::TYPE_STRING:
                $this->filterString($qb, $aliasProperty, $value, self::TYPE_STRING);
                break;
            case self::TYPE_TEXT:
                $this->filterString($qb, $aliasProperty, $value, self::TYPE_TEXT);
                break;
            case self::TYPE_GUID:
                $this->filterString($qb, $aliasProperty, $value, self::TYPE_GUID);
                break;
            case self::TYPE_BINARY:
                $this->filterString($qb, $aliasProperty, $value, self::TYPE_BINARY);
                break;
            case self::TYPE_BLOB:
                $this->filterString($qb, $aliasProperty, $value, self::TYPE_BLOB);
                break;
            case self::TYPE_BOOLEAN:
                $this->filterBoolean($qb, $aliasProperty, $value);
                break;
            case self::TYPE_DATE_IMMUTABLE:
            case self::TYPE_DATE:
                $this->filterDateTime($qb, $aliasProperty, $value, false);
                break;
            case self::TYPE_DATETIME_IMMUTABLE:
            case self::TYPE_DATETIMETZ_IMMUTABLE:
            case self::TYPE_TIME_IMMUTABLE:
            case self::TYPE_DATETIME:
            case self::TYPE_DATETIMETZ:
            case self::TYPE_TIME:
                $this->filterDateTime($qb, $aliasProperty, $value, true);
                break;
            case self::TYPE_DATEINTERVAL:
                $this->filterDateInterval($qb, $property, $value);
                break;
            case self::TYPE_JSON:
            case self::TYPE_TARRAY:
                $this->filterTArray($qb, $aliasProperty, $value);
                break;
            case self::TYPE_SIMPLE_ARRAY:
                $this->filterSimpleArray($qb, $aliasProperty, $value);
                break;
            case self::TYPE_JSON_ARRAY:
                $this->filterJsonArray($qb, $aliasProperty, $value);
                break;
            case self::TYPE_OBJECT:
                $this->filterObject($qb, $aliasProperty, $value);
                break;
            default:
                break;
        }
    }

    protected function filterAssociation(QueryBuilder $qb, $association, $value)
    {
        $aliasAssociation = $this->getAliasedProperty($association);

        $associatedClass = $this->classMetadata->getAssociationTargetClass($association);
        if ($association instanceof $associatedClass) {
            $field = $this->classMetadata->getSingleAssociationReferencedJoinColumnName($association);
            try {
                $qb->andWhere($qb->expr()->eq(
                    $aliasAssociation,
                    $qb->expr()->literal($value->{'get'.$field}())
                ));
            } catch (\Exception $e) {

            }
        }
        // If $value is not array do '='
        if (!is_array($value)) {
            $qb->andWhere($qb->expr()->eq(
                $aliasAssociation,
                $qb->expr()->literal($value)
            ));
            /**
             * TRY getId or something
             */
            // Else if value is Sequential array do 'in'
        } elseif ($this->isSequentialArray($value)) {
            $qb->andWhere($qb->expr()->in(
                $aliasAssociation,
                $qb->expr()->literal($value)
            ));
            return;
        }

        // Check JOIN_LIMIT
        if (count($qb->getDQLParts()['join'][$this->rootAlias]) >= $this->getSetting(self::KEY_JOIN_LIMIT)) {
            return;
        }

        // getRepository of associacion
        $associationMappings = $this->classMetadata->getAssociationMapping($association);
        $associatedRepository = $this->entityManager->getRepository($associationMappings['targetEntity']);

        // Check if associated repository is filterable.
        if ($associatedRepository instanceof RepositoryFilterableInterface) {
            // Join association
            $joinedAlias = $this->rootAlias.'_'.$association;
            $joinedType = $this->getSetting(self::KEY_JOIN_TYPE);

            $qb->$joinedType($aliasAssociation, $joinedAlias);

            // Run filterBy method of associated repository
            $associatedRepository->filterBy($value, $qb, $joinedAlias);
        }

    }

    protected function filterNumber(QueryBuilder $qb, $property, $value)
    {
        if (!is_array($value)) {
            $qb->andWhere($qb->expr()->eq(
                $property,
                $qb->expr()->literal($value)
            ));
            return;
        }
        if ($this->isSequentialArray($value)) {
            $qb->andWhere($qb->expr()->in($property, array_values($value)));

            return;
        }
        foreach ($this->getCompareTypes() as $cpt) {
            if (array_key_exists($cpt, $value)) {
                $qb->andWhere($qb->expr()->$cpt(
                    $property,
                    $qb->expr()->literal($value[$cpt])
                ));
            }
        }
    }

    protected function filterString(QueryBuilder $qb, $property, $needle, $type = self::TYPE_STRING)
    {
        $like = 'like';
        $not_like = $this->getSetting(self::KEY_DEFAULT_NOT_LIKE);

        // if string  - check if it begins with 'not_like' value, change for not_like mode and remove 'not_like' from beginning
        if (is_string($needle)) {
            $isNotLike = substr($needle, 0, count($not_like));
            if ($isNotLike === self::KEY_DEFAULT_NOT_LIKE) {
                $like = 'notLike';
                if (0 === strpos($needle, $isNotLike)) {
                    $needle = substr($needle, strlen($isNotLike));
                }
            }
        }
        // If array - search for 'not_like' key, if exists change 'like' for 'not_like' and remove array keys.
        if (is_array($needle) && array_key_exists($not_like, $needle)) {
            $like = 'notLike';
            $needle = $needle[self::KEY_DEFAULT_NOT_LIKE];
        }

        // Validate type of search
        $type = $this->validateSearchLikeType($type);

        // Get needle
        $needle = $this->getSearchNeedleBySearchType($needle, $type);

        $qb->andWhere(
            $qb->expr()->$like(
                $property,
                $qb->expr()->literal($needle)
            )
        );
    }

    protected function filterBoolean(QueryBuilder $qb, $property, $value)
    {
        $qb->andWhere($qb->expr()->eq(
            $property,
            $qb->expr()->literal($value)
        ));
    }

    protected function filterDateTime(QueryBuilder $qb, $property, $value, $withTime)
    {
        if (isset($value['from'])) {
            $value['gte'] = $value['from'];
            unset($value['from']);
        }
        if (isset($value['to'])) {
            $value['lte'] = $value['to'];
            unset($value['to']);
        }

        if (is_array($value)) {
            foreach ($this->getCompareTypes() as $compareType) {
                if (isset($value[$compareType]) && is_string($value[$compareType])) {
                    try {
                        $value[$compareType] = new \DateTime($value[$compareType]);
                    } catch (\Exception $e) {
                        unset($value[$compareType]);
                    }
                }
            }
        } elseif (is_string($value)) {
            try {
                $value = new \DateTime($value);
            } catch (\Exception $e) {
                $value = null;
            }
        }

        if ($value instanceof \DateTime) {
            $parameter = $withTime ? $value->format(self::FORMAT_DATETIME) : $value->format(self::FORMAT_DATE);
            $qb->andWhere($qb->expr()->eq(
                $property,
                $qb->expr()->literal($parameter)
            ));
        }

        if (is_array($value)) {
            foreach ($this->getCompareTypes() as $compareType) {
                if (isset($value[$compareType]) && $value[$compareType] instanceof \DateTime) {
                    $parameter = $withTime ? $value[$compareType]->format(self::FORMAT_DATETIME) : $value['from']->format(self::FORMAT_DATE);
                    $qb->andWhere($qb->expr()->$compareType(
                        $property,
                        $qb->expr()->literal($parameter)
                    ));
                }
            }
        }

        return $this;
    }

    protected function filterDateInterval(QueryBuilder $qb, $property, $value)
    {
        /**
         * TODO improve.
         */
        if (is_array($value)) {
            foreach ($this->getCompareTypes() as $compareType) {
                if (isset($value[$compareType]) && is_string($value[$compareType])) {
                    try {
                        $value[$compareType] = new \DateInterval($value[$compareType]);
                    } catch (\Exception $e) {
                        unset($value[$compareType]);
                    }
                }
            }
        } elseif (is_string($value)) {
            try {
                $value = new \DateInterval($value);
            } catch (\Exception $e) {
                $value = null;
            }
        }

        if ($value instanceof \DateInterval) {
            $parameter = $value->format(self::FORMAT_DATE_INTERVAL);
            $qb->andWhere($qb->expr()->eq(
                $property,
                $qb->expr()->literal($parameter)
            ));
        }

        if (is_array($value)) {
            foreach ($this->getCompareTypes() as $compareType) {
                if (isset($value[$compareType]) && $value[$compareType] instanceof \DateInterval) {
                    $parameter = $value->format(self::FORMAT_DATE_INTERVAL);
                    $qb->andWhere($qb->expr()->$compareType(
                        $property,
                        $qb->expr()->literal($parameter)
                    ));
                }
            }
        }
    }

    protected function filterSimpleArray(QueryBuilder $qb, $aliasProperty, $value)
    {
        /**
         * TODO change to regex or improve.
         */
        $search = null;
        try {
            if (is_array($value)) {
                $search = implode(',', $value);
            } else {
                $search = (string) $value;
            }
        } catch (\Exception $e) {
        }
        if ($search) {
            $this->filterString($qb, $aliasProperty, $search);
        }
    }

    protected function filterTArray(QueryBuilder $qb, $aliasProperty, $value)
    {
        /**
         * TODO change to regex or improve.
         */
        $search = null;
        try {
            if (is_array($value)) {
                $search = serialize($value);
            } else {
                $search = (string) $value;
            }
        } catch (\Exception $e) {
        }
        if ($search) {
            $this->filterString($qb, $aliasProperty, $search);
        }
    }

    protected function filterJsonArray(QueryBuilder $qb, $aliasProperty, $value)
    {
        /**
         * TODO change to regex or improve.
         */
        $search = null;
        try {
            if (is_array($value)) {
                $search = json_encode($value);
            } else {
                $search = (string) $value;
            }
        } catch (\Exception $e) {
        }
        if ($search) {
            $this->filterString($qb, $aliasProperty, $search);
        }
    }

    protected function filterObject(QueryBuilder $qb, $aliasProperty, $value)
    {
        /**
         * TODO change to regex or improve.
         */
        $search = null;
        try {
            if (is_object($value) || is_array($value)) {
                $search = serialize($value);
            } else {
                $search = (string) $value;
            }
        } catch (\Exception $e) {
        }
        if ($search) {
            $this->filterString($qb, $aliasProperty, $search);
        }
    }

    protected function filterValueIsNull(QueryBuilder $qb, $property)
    {
        $qb->andWhere($qb->expr()->isNull($property));
    }

    protected function filterValueIsNotNull(QueryBuilder $qb, $property)
    {
        $qb->andWhere($qb->expr()->isNotNull($property));
    }

    protected function getFieldMappingType($property)
    {
        return $this->classMetadata->getFieldMapping($property)['type'];
    }

    protected function isAssociationArray($array)
    {
        if (!(is_array($array) && $array === array())) {
            return false;
        }
        return array_keys($array) !== range(0, count($array) - 1);
    }

    protected function isSequentialArray($array)
    {
        if (!is_array($array)) {
            return false;
        }
        return array_keys($array) === range(0, count($array) - 1);
    }

    protected function validateSearchLikeType($type)
    {
        $search_allow_min = $this->getSetting(self::KEY_GLOBAL_STRING_ALLOW_MIN);
        $search_allow_max = $this->getSetting(self::KEY_GLOBAL_STRING_ALLOW_MAX);
        $search_allow_this_type = $this->getSetting(self::KEY_STRING_TYPES_ALLOW)[$type];
        return max($search_allow_min, min($search_allow_this_type, $search_allow_max));
    }

    protected function getSearchNeedleBySearchType($value, $type)
    {
        $value = trim($value, '%');

        switch ($type) {
            case self::SEARCH_LIKE_ALLOW_EQUAL_ONLY:
                break;
            case self::SEARCH_LIKE_ALLOW_BEGINNING:
                $value .= '%';
                break;
            case self::SEARCH_LIKE_ALLOW_ENDING:
                $value = '%'.$value;
                break;
            case self::SEARCH_LIKE_ALLOW_CONTAINING:
                $value = '%'.$value.'%';
                break;
            default:
                break;
        }

        return $value;
    }
}
