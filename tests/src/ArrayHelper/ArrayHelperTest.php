<?php
declare(strict_types=1);

namespace Tests\Abryb\RepositoryFilter\ArrayHelper;

use PHPUnit\Framework\TestCase;

/**
 * @covers ArrayHelper
 */
class ArrayHelperTest extends TestCase
{
    protected $keysTranslation;

    protected $valuesTranslations;

    protected function setUp()
    {
        $this->keysTranslation = ['one' => 'three', 'two' => 'four'];
        $this->valuesTranslations = ['three' => 'one', 'four' => 'two'];
    }

    public function testConvertArrayKeys()
    {
        $testArray = [
            'one' => 'two',
            'two' => 'one',
            'array' => [
                    'one' => 'one'
                ]

        ];

        $expectedArray = [
            'three' => 'two',
            'four' => 'one',
            'array' => [
                'three' => 'one'
            ]

        ];

        $arrayHelper = new \Abryb\RepositoryFilter\ArrayHelper\ArrayHelper($this->keysTranslation, $this->valuesTranslations);

        $this->assertEquals($expectedArray, $arrayHelper->convertArrayKeys($testArray));
    }

    public function testConvertArrayValues()
    {
        $testArray = [
            'one' => 'three',
            'two' => 'four',
            'array' => [
                'one' => 'three'
            ]

        ];

        $expectedArray = [
            'one' => 'one',
            'two' => 'two',
            'array' => [
                'one' => 'one'
            ]

        ];

        $arrayHelper = new \Abryb\RepositoryFilter\ArrayHelper\ArrayHelper($this->keysTranslation, $this->valuesTranslations);

        $this->assertEquals($expectedArray, $arrayHelper->convertArrayValues($testArray));
    }
}
