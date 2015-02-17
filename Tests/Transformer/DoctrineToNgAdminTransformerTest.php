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
        $doctrineMetadata->associationMappings = [];
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

    /** @dataProvider nonReferenceTypeProvider */
    public function testShouldTransformDoctrineNonReferentialTypesIntoCorrectNgAdminTypes($doctrineType, $expectedNgAdminType)
    {
        $doctrineMetadata = new \StdClass();
        $doctrineMetadata->associationMappings = [];
        $doctrineMetadata->fieldMappings = [['fieldName' => 'myField', 'type' => $doctrineType]];

        $ngAdminConfiguration = $this->transformer->transform($doctrineMetadata);

        $this->assertEquals($expectedNgAdminType, $ngAdminConfiguration[0]['type']);
    }

    public function nonReferenceTypeProvider()
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

    public function testShouldTransformDoctrineReferentialFieldsIntoCorrectNgAdminType()
    {
        $doctrineMetadata = new \StdClass();
        $doctrineMetadata->fieldMappings = [['fieldName' => 'post_id']];
        $doctrineMetadata->associationMappings = [
            'post' => [
                'fieldName' => 'post_id',
                'isOwningSide' => true,
                'joinColumns' => [
                    ['name' => 'post_id', 'referencedColumnName' => 'id'],
                ]
            ],
        ];

        $ngAdminConfiguration = $this->transformer->transform($doctrineMetadata);

        $this->assertEquals([
            ['name' => 'post_id', 'type' => 'reference', 'referencedField' => 'id', 'referencedEntity' => 'post'],
        ], $ngAdminConfiguration);
    }

    public function testShouldNotTransformNotOwnedRelationshipToReferenceField()
    {
        $doctrineMetadata = new \StdClass();
        $doctrineMetadata->fieldMappings = [['fieldName' => 'post_id', 'type' => 'integer']];
        $doctrineMetadata->associationMappings = [
            'comments' => [ 'isOwningSide' => false ],
        ];

        $ngAdminConfiguration = $this->transformer->transform($doctrineMetadata);

        $this->assertEquals([
            ['name' => 'post_id', 'type' => 'number'],
        ], $ngAdminConfiguration);
    }
}
