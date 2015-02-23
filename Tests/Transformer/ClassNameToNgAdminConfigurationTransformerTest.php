<?php

namespace marmelab\NgAdminGeneratorBundle\Transformer;

use JMS\Serializer\Serializer;

class ClassNameToNgAdminConfigurationTransformerTest extends \PHPUnit_Framework_TestCase
{
    private $className = 'Acme\FooBundle\Entity\Post';

    public function testTransformShouldAddClassFqdnAndEntityName()
    {
        $serializer = $this->getSerializerMock([[]]);
        $guesser = $this->getReferencedFieldGuesserMock();
        $transformer = new ClassNameToNgAdminConfigurationTransformer($serializer, $guesser);

        $transformedData = $transformer->transform($this->className);
        $this->assertEquals([
            'name' => 'post',
            'class' => 'Acme\FooBundle\Entity\Post',
            'fields' => [],
        ], $transformedData);
    }

    public function testShouldTransformIntegerFieldIntoNumber()
    {
        $serializer = $this->getSerializerMock([[
            'order' => (object) [
                'type' => [
                    'name' => 'integer',
                ],
            ],
        ]]);
        $guesser = $this->getReferencedFieldGuesserMock();

        $transformer = new ClassNameToNgAdminConfigurationTransformer($serializer, $guesser);

        $transformedData = $transformer->transform($this->className);
        $this->assertEquals([
            ['name' => 'order', 'type' => 'number'],
        ], $transformedData['fields']);
    }

    /** @dataProvider stringFieldsProvider */
    public function testShouldTransformStringFieldIntoStringOrTextDependingFieldName($fieldName, $expectedType)
    {
        $serializer = $this->getSerializerMock([[
            $fieldName => (object) [
                'type' => [
                    'name' => 'string',
                ],
                'reflection' => (object) [
                    'name' => $fieldName,
                ]
            ],
        ]]);
        $guesser = $this->getReferencedFieldGuesserMock();
        $transformer = new ClassNameToNgAdminConfigurationTransformer($serializer, $guesser);

        $transformedData = $transformer->transform($this->className);
        $this->assertEquals([
            ['name' => $fieldName, 'type' => $expectedType],
        ], $transformedData['fields']);
    }

    public function stringFieldsProvider()
    {
        return [
            ['title', 'string'],
            ['name', 'string'],
            ['body', 'text'],
            ['content', 'text'],
            ['details', 'text'],
        ];
    }

    public function testShouldTransformArrayCollectionIntoReferencedListFieldWithRelationshipColumn()
    {
        $serializer = $this->getSerializerMock([
            $this->className => [
                'comments' => (object) [
                    'type' => [
                        'name' => 'ArrayCollection',
                        'params' => [
                            ['name' => 'Acme\FooBundle\Entity\Comment'],
                        ]
                    ],
                    'reflection' => (object) [
                        'name' => 'post',
                    ]
                ],
            ],
        ]);
        $guesser = $this->getReferencedFieldGuesserMock('title');

        $transformer = new ClassNameToNgAdminConfigurationTransformer($serializer, $guesser);

        $transformedData = $transformer->transform($this->className);
        $this->assertEquals([[
            'name' => 'comments',
            'type' => 'referenced_list',
            'referencedEntity' => [
                'name' => 'comment',
                'class' => 'Acme\FooBundle\Entity\Comment',
            ],
            'referencedField' => 'title',
        ]], $transformedData['fields']);
    }


    private function getSerializerMock(array $propertyMetadatas)
    {
        $metadataFactory = $this->getMockBuilder('Metadata\MetadataFactory')
            ->disableOriginalConstructor()
            ->getMock();

        $metadataFactory->expects($this->exactly(count($propertyMetadatas)))
            ->method('getMetadataForClass')
            ->will($this->returnCallback(function($className) use($propertyMetadatas) {
                if (count($propertyMetadatas) <= 1) {
                    return (object) [
                        'propertyMetadata' => current($propertyMetadatas),
                    ];
                }

                return (object) [
                    'propertyMetadata' => $propertyMetadatas[$className],
                ];
            }));

        $serializer = $this->getMockBuilder('JMS\Serializer\Serializer')
            ->disableOriginalConstructor()
            ->getMock();

        $serializer->expects($this->once())
            ->method('getMetadataFactory')
            ->willReturn($metadataFactory);

        return $serializer;
    }

    private function getReferencedFieldGuesserMock($guessedField = null)
    {
        $guesser = $this->getMockBuilder('marmelab\NgAdminGeneratorBundle\Guesser\ReferencedFieldGuesser')
            ->disableOriginalConstructor()
            ->getMock();

        $guesser->expects($this->any())
            ->method('guess')
            ->willReturn($guessedField);

        return $guesser;
    }
}
