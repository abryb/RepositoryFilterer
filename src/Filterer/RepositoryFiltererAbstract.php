<?php

/*
 * This file is part of RepositoryFilterer project.
 *
 * (c) B?a?ej Rybarkiewicz <andrzej.blazej.rybarkiewicz@gmail.com>
 */

namespace Abryb\DoctrineBehaviors\ORM\Filterable;

use Doctrine\DBAL\Types\Type;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\QueryBuilder;

abstract class RepositoryFiltererAbstract
{
    const JOIN_TYPE = 'join_type';
    const JOIN_LIMIT = 'join_limit';
    const JOIN_DEPTH = 'join_depth';
    const ALIAS = 'alias';
    const BLOCKED_FILTERS = 'blocked_filters';
    const DEFINED_FILTERS = 'defined_filters';
    const DEFAULT_NOT_NULL = 'default_not_null';
    const DEFAULT_NOT_LIKE = 'default_not_like';
    const STRING_TYPES_ALLOW = 'string_types_allow';
    const GLOBAL_STRING_ALLOW_MAX = 'global_string_allow_max';
    const GLOBAL_STRING_ALLOW_MIN = 'global_string_allow_min';

    const VALUE_LEFT_JOIN = 'leftJoin';
    const VALUE_INNER_JOIN = 'innerJoin';
    const VALUE_DEFAULT_ALIAS = 'e';
    const VALUE_BLOCKED_FILTERS = array();
    const VALUE_DEFINED_FILTERS = array();
    const VALUE_NOT_NULL_DEFAULT = 'not_null';
    const VALUE_NOT_LIKE_DEFAULT = 'not_like';

//    const SEARCH_LIKE_ALLOW_REGEX      = 4; // TODO
    const SEARCH_LIKE_ALLOW_CONTAINING = 3;
    const SEARCH_LIKE_ALLOW_ENDING = 2;
    const SEARCH_LIKE_ALLOW_BEGINNING = 1;
    const SEARCH_LIKE_ALLOW_EQUAL_ONLY = 0;

    const COMPARE_NEQ = 'neq';
    const COMPARE_EQ = 'eq';
    const COMPARE_GT = 'gt';
    const COMPARE_GTE = 'gte';
    const COMPARE_LT = 'lt';
    const COMPARE_LTE = 'lte';

    const TYPE_SMALLINT = Type::SMALLINT;
    const TYPE_BIGINT = Type::BIGINT;
    const TYPE_INTEGER = Type::INTEGER;
    const TYPE_DECIMAL = Type::DECIMAL;
    const TYPE_FLOAT = Type::FLOAT;
    const TYPE_STRING = Type::STRING;
    const TYPE_TEXT = Type::TEXT;
    const TYPE_GUID = Type::GUID;
    const TYPE_BINARY = Type::BINARY;
    const TYPE_BLOB = Type::BLOB;
    const TYPE_BOOLEAN = Type::BOOLEAN;
    const TYPE_DATE_IMMUTABLE = Type::DATE_IMMUTABLE ?? 'date_immutable';
    const TYPE_DATE = Type::DATE;
    const TYPE_DATETIME_IMMUTABLE = Type::DATETIME_IMMUTABLE ?? 'datetime_immutable';
    const TYPE_DATETIMETZ_IMMUTABLE = Type::DATETIMETZ_IMMUTABLE ?? 'datetimez_immutable';
    const TYPE_TIME_IMMUTABLE = Type::TIME_IMMUTABLE ?? 'time_immutable';
    const TYPE_DATETIME = Type::DATETIME;
    const TYPE_DATETIMETZ = Type::DATETIMETZ;
    const TYPE_TIME = Type::TIME;
    const TYPE_DATEINTERVAL = Type::DATEINTERVAL;
    const TYPE_JSON = Type::JSON ?? 'json';
    const TYPE_TARRAY = Type::TARRAY;
    const TYPE_SIMPLE_ARRAY = Type::SIMPLE_ARRAY;
    const TYPE_JSON_ARRAY = Type::JSON_ARRAY ?? 'json_array';
    const TYPE_OBJECT = Type::OBJECT;

    const FORMAT_DATE_INTERVAL = 'P%YY%MM%DDT%HH%IM%SS';
    const FORMAT_DATETIME = 'Y-m-d H:i:s';
    const FORMAT_DATETIMEZ = 'Y-m-d H:i:s';
    const FORMAT_TIME = 'Y-m-d H:i:s';
    const FORMAT_DATE = 'Y-m-d';

    /**
     * @var \ArrayHelperInterface
     */
    protected $arrayHelper;

    /**
     * @var ClassMetadata
     */
    protected $classMetadata;

    /**
     * @var EntityManager
     */
    protected $entityManager;

    /**
     * @var EntityRepository
     */
    protected $entityRepository;

    /**
     * @var array settings of filterable
     */
    protected $settings;

    /**
     * THE settings of filterable.
     *
     * @var array
     */
    protected static $defaultSettings = array(
        self::JOIN_TYPE => self::VALUE_LEFT_JOIN,
//        self::JOIN_TYPE => 10,
//        self::JOIN_DEPTH => 2,
        self::ALIAS => self::VALUE_DEFAULT_ALIAS,
        self::BLOCKED_FILTERS => array(),
        self::DEFINED_FILTERS => array(),
        self::DEFAULT_NOT_NULL => self::VALUE_NOT_NULL_DEFAULT,
        self::DEFAULT_NOT_LIKE => self::VALUE_NOT_LIKE_DEFAULT,
        self::GLOBAL_STRING_ALLOW_MIN => self::SEARCH_LIKE_ALLOW_EQUAL_ONLY,
        self::GLOBAL_STRING_ALLOW_MAX => self::SEARCH_LIKE_ALLOW_CONTAINING,
        self::STRING_TYPES_ALLOW => array(
            Type::STRING => self::SEARCH_LIKE_ALLOW_CONTAINING,
            Type::TEXT => self::SEARCH_LIKE_ALLOW_BEGINNING,
            Type::GUID => self::SEARCH_LIKE_ALLOW_EQUAL_ONLY,
            Type::BINARY => self::SEARCH_LIKE_ALLOW_BEGINNING,
            Type::BLOB => self::SEARCH_LIKE_ALLOW_BEGINNING,
        ),
    );

    public function __construct(
        EntityManager $entityManager,
        EntityRepository $entityRepository,
        ClassMetadata $classMetadata,
        array $settings = array(),
        \ArrayHelperInterface $arrayHelper = null
    )

    {
        $this->entityManager = $entityManager;
        $this->entityRepository = $entityRepository;
        $this->classMetadata = $classMetadata;
        $this->setSettings($settings);

        if (null === $arrayHelper) {
            $this->arrayHelper = new \ArrayHelper();
        }
    }

    /**
     * @param array             $filters
     * @param QueryBuilder|null $qb
     * @param null              $joinedAlias
     *
     * @return QueryBuilder
     */
    abstract public function filterBy(array $filters, QueryBuilder $qb = null, $joinedAlias = null): QueryBuilder;

    /**
     * @param array $settings
     */
    public function setSettings(array $settings)
    {
        $this->settings = $settings;
    }

    /**
     * @return array
     */
    public function getSettings(): array
    {
        return array_merge($this::$defaultSettings, $this->settings);
    }

    /**
     * @param string[] ...$keys
     *
     * @return mixed
     */
    public function getSetting(string ...$keys)
    {
        $settings = $this->getSettings();
        foreach ($keys as $key) {
            $settings = $settings[$key];
        }

        return $settings;
    }

    /**
     * @return array of compare types
     */
    protected function getCompareTypes(): array
    {
        return array(
            self::COMPARE_EQ,
            self::COMPARE_NEQ,
            self::COMPARE_GT,
            self::COMPARE_GTE,
            self::COMPARE_LT,
            self::COMPARE_LTE,
        );
    }

    protected function getDefinedFiltersNames()
    {
        return array_keys($this->getSetting(self::DEFINED_FILTERS));
    }
}
