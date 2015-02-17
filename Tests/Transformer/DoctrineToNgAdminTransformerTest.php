<?php

namespace marmelab\NgAdminGeneratorBundle\Tests\Transformer;

use marmelab\NgAdminGeneratorBundle\Transformer\DoctrineToNgAdminTransformer;

class DoctrineToNgAdminTransformerTest extends \PHPUnit_Framework_TestCase
{
    private $transformer;

    public function setUp()
    {
        $this->transformer = new DoctrineToNgAdminTransformer();
    }

    public function testShouldTransformInputDataIntoExpectedFormat()
    {
        $doctrineMetadata = new \StdClass();
        $doctrineMetadata->fieldMappings = [
            ['fieldName' => 'title', 'type' => 'string'],
            ['fieldName' => 'body', 'type' => 'text']
        ];

        $ngAdminConfiguration = $this->transformer->transform($doctrineMetadata);

        $this->assertEquals([
            ['name' => 'title', 'type' => 'string'],
            ['name' => 'body', 'type' => 'text'],
        ], $ngAdminConfiguration);
    }

    /** @dataProvider typeProvider */
    public function testShouldDoctrineTypesIntoCorrectNgAdminTypes($doctrineType, $expectedNgAdminType)
    {
        $doctrineMetadata = new \StdClass();
        $doctrineMetadata->fieldMappings = [['fieldName' => 'myField', 'type' => $doctrineType]];

        $ngAdminConfiguration = $this->transformer->transform($doctrineMetadata);

        $this->assertEquals($expectedNgAdminType, $ngAdminConfiguration[0]['type']);
    }

    public function typeProvider()
    {
        return [
            ['smallint', 'number'],
            ['integer', 'number'],
            ['bigint', 'number'],
            ['decimal', 'number'],
            ['float', 'number'],
            ['string', 'string'],
            ['guid', 'string'],
            ['datetime', 'string'],
            ['datetimez', 'string'],
            ['time', 'string'],
            ['text', 'text'],
            ['boolean', 'boolean'],
            ['date', 'date'],
        ];
    }
}
