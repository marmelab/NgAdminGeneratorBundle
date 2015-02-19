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

        $transformedEntity = $this->transformer->transform($this->doctrineMetadataMock);

        $this->assertEquals([
            'class' => 'Acme\FooBundle\Entity\Comment',
            'name' => 'comment',
            'fields' => [
                'title' => [
                    'name' => 'title',
                    'type' => 'string',
                ],
                'body' => [
                    'name' => 'body',
                    'type' => 'text',
                ],
            ]
        ], $transformedEntity);
    }

    /** @dataProvider nonReferenceTypeProvider */
    public function testShouldTransformDoctrineNonReferentialTypesIntoCorrectNgAdminTypes($doctrineType, $expectedNgAdminType)
    {
        $this->doctrineMetadataMock->fieldMappings = [['fieldName' => 'myField', 'type' => $doctrineType]];
        $ngAdminConfiguration = $this->transformer->transform($this->doctrineMetadataMock);

        $this->assertEquals($expectedNgAdminType, current($ngAdminConfiguration['fields'])['type']);
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
                'targetEntity' => 'Acme\FooBundle\Entity\Post',
                'joinColumns' => [
                    ['name' => 'post_id', 'referencedColumnName' => 'id'],
                ]
            ],
        ];

        $transformedEntity = $this->transformer->transform($this->doctrineMetadataMock);

        $this->assertEquals([
            'class' => 'Acme\FooBundle\Entity\Comment',
            'name' => 'comment',
            'fields' => [
                'post_id' => [
                    'name' => 'post_id',
                    'type' => 'reference',
                    'referencedField' => 'id',
                    'referencedEntity' => [
                        'name' => 'post',
                        'class' => 'Acme\FooBundle\Entity\Post',
                    ],
                ],
            ],
        ], $transformedEntity);
    }

    public function testShouldTransformNotOwnedRelationshipToReferencedListField()
    {
        $this->doctrineMetadataMock->name = 'Acme\FooBundle\Entity\Post';
        $this->doctrineMetadataMock->associationMappings = [
            'comments' => [
                'isOwningSide' => false,
                'sourceEntity' => 'Acme\FooBundle\Entity\Post',
                'targetEntity' => 'Acme\FooBundle\Entity\Comment',
                'mappedBy' => 'post',
                'fieldName' => 'post',
            ],
        ];

        $ngAdminConfiguration = $this->transformer->transform($this->doctrineMetadataMock);

        $this->assertEquals([
            'class' => 'Acme\FooBundle\Entity\Post',
            'name' => 'post',
            'fields' => [
                'post' => [
                    'name' => 'comments',
                    'referencedEntity' => [
                        'name' => 'comment',
                        'class' => 'Acme\FooBundle\Entity\Comment',
                    ],
                    'referencedField'=> 'post_id',
                    'type' => 'referenced_list',
                ]
            ]
        ], $ngAdminConfiguration);
    }

    public function testShouldTransformManyToManyRelationshipToReferenceManyField()
    {
        $this->doctrineMetadataMock->associationMappings = [
            'tags' => [
                'isOwningSide' => true,
                'fieldName' => 'tags',
                'targetEntity' => 'Acme\FooBundle\Entity\Tag',
                'joinTable' => [
                    'inverseJoinColumns' => [
                        ['referencedColumnName' => 'id'],
                    ],
                ],
            ],
        ];

        $ngAdminConfiguration = $this->transformer->transform($this->doctrineMetadataMock);

        $this->assertEquals([
            'class' => 'Acme\FooBundle\Entity\Comment',
            'name' => 'comment',
            'fields' => [
                'tags' => [
                    'name' => 'tags',
                    'type' => 'reference_many',
                    'referencedEntity' => [
                        'name' => 'tag',
                        'class' => 'Acme\FooBundle\Entity\Tag',
                    ],
                    'referencedField' => 'id',
                ]
            ]
        ], $ngAdminConfiguration);
    }
}
