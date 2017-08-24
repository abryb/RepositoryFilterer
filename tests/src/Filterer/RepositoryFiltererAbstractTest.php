<?php
/**
 * Created by PhpStorm.
 * User: blazej
 * Date: 24.08.17
 * Time: 13:49
 */

namespace Tests\Abryb\RepositoryFilterer\Filterer;

use PHPUnit\Framework\TestCase;

class RepositoryFiltererAbstractTest extends TestCase
{
    protected $filterer;

    public function setUp()
    {
        $em = $this->createMock(\Doctrine\ORM\EntityManager::class);
        $repo = $this->createMock(\Doctrine\ORM\EntityRepository::class);
        $mapping = $this->createMock(\Doctrine\ORM\Mapping\ClassMetadata::class);
        $helper = $this->createMock(\Abryb\RepositoryFilter\ArrayHelper\ArrayHelper::class);

        $this->filterer = new RepositoryFiltererAbstractClass($em, $repo, $mapping, array(), $helper);
    }

    public function testConstructor()
    {

    }
}
