<?php

namespace marmelab\NgAdminGeneratorBundle\Test\Transformer;

use JMS\Serializer\Serializer;
use marmelab\NgAdminGeneratorBundle\Transformer\EntityToEntityWithIconTransformer;

class EntityToEntityWithIconTransformerTest extends \PHPUnit_Framework_TestCase
{
    /** @var EntityToEntityWithIconTransformer */
    private $transformer;

    public function setUp()
    {
        $icons = [
            'pencil' => ['post', 'topic'],
            'comment' => ['comment'],
        ];

        $this->transformer = new EntityToEntityWithIconTransformer($icons);
    }

    public function testTransformShouldAddIconBasedOnEntityNameIfAvailable()
    {
        $entity = ['name' => 'topic'];
        $transformedEntity = $this->transformer->transform($entity);
        $this->assertEquals('pencil', $transformedEntity['icon']);

        $entity = ['name' => 'comment'];
        $transformedEntity = $this->transformer->transform($entity);
        $this->assertEquals('comment', $transformedEntity['icon']);
    }

    public function testTransformShouldAddDefaultIconIfNoneFound()
    {
        $entity = ['name' => 'extremely-precise-domain-name'];
        $transformedEntity = $this->transformer->transform($entity);
        $this->assertEquals('cog', $transformedEntity['icon']);
    }

    public function testReverseTransformEntityShouldRemoveItsIconField()
    {
        $entity = [
            'name' => 'post',
            'icon' => 'pencil',
        ];

        $transformedEntity = $this->transformer->reverseTransform($entity);
        $this->assertFalse(isset($transformedEntity['icon']));
    }
}
