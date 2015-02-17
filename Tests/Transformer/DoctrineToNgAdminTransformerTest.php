<?php

namespace marmelab\NgAdminGeneratorBundle\Tests\Transformer;

use marmelab\NgAdminGeneratorBundle\Transformer\DoctrineToNgAdminTransformer;

class DoctrineToNgAdminTransformerTest extends \PHPUnit_Framework_TestCase
{
    /** @var DoctrineToNgAdminTransformer */
    private $transformer;

    private $doctrineMetadataMock;

    public function setUp()
    {
        $this->transformer = new DoctrineToNgAdminTransformer();
        $this->doctrineMetadataMock = new \StdClass();
        $this->doctrineMetadataMock->name = 'Acme\FooBundle\Entity\Comment';
        $this->doctrineMetadataMock->associationMappings = [];
        $this->doctrineMetadataMock->fieldMappings = [];
    }

    public function testShouldTransformInputDataIntoExpectedFormat()
    {
        $this->doctrineMetadataMock->fieldMappings = [
            ['fieldName' => 'title', 'type' => 'string'],
            ['fieldName' => 'body', 'type' => 'text']
        ];

        $ngAdminConfiguration = $this->transformer->transform($this->doctrineMetadataMock);

        $this->assertEquals([
            ['name' => 'title', 'type' => 'string'],
            ['name' => 'body', 'type' => 'text'],
        ], $ngAdminConfiguration);
    }

    /** @dataProvider nonReferenceTypeProvider */
    public function testShouldTransformDoctrineNonReferentialTypesIntoCorrectNgAdminTypes($doctrineType, $expectedNgAdminType)
    {
        $this->doctrineMetadataMock->fieldMappings = [['fieldName' => 'myField', 'type' => $doctrineType]];
        $ngAdminConfiguration = $this->transformer->transform($this->doctrineMetadataMock);

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
        $this->doctrineMetadataMock->fieldMappings = [['fieldName' => 'post_id']];
        $this->doctrineMetadataMock->associationMappings = [
            'post' => [
                'fieldName' => 'post_id',
                'isOwningSide' => true,
                'joinColumns' => [
                    ['name' => 'post_id', 'referencedColumnName' => 'id'],
                ]
            ],
        ];

        $ngAdminConfiguration = $this->transformer->transform($this->doctrineMetadataMock);

        $this->assertEquals([
            ['name' => 'post_id', 'type' => 'reference', 'referencedField' => 'id', 'referencedEntity' => 'post'],
        ], $ngAdminConfiguration);
    }

    public function testShouldTransformNotOwnedRelationshipToReferencedListField()
    {
        $this->doctrineMetadataMock->associationMappings = [
            'comments' => [
                'isOwningSide' => false,
                'sourceEntity' => 'Acme\FooBundle\Entity\Comment',
                'mappedBy' => 'comments',
            ],
        ];

        $ngAdminConfiguration = $this->transformer->transform($this->doctrineMetadataMock);

        $this->assertEquals([[
            'name' => 'comments',
            'referencedEntity' => 'comment',
            'referencedField'=> 'post_id',
            'mappedBy' => 'comments',
            'type' => 'referenced_list',
        ]], $ngAdminConfiguration);
    }
}
