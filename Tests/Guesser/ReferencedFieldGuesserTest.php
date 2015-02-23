<?php

namespace marmelab\NgAdminGeneratorBundle\Tests\Guesser;

use marmelab\NgAdminGeneratorBundle\Guesser\ReferencedFieldGuesser;

class ReferencedFieldGuesserTest extends \PHPUnit_Framework_TestCase
{
    public function testGuessingBestReferencedFieldShouldReturnFirstMatchedField()
    {
        $bestChoices = ['title', 'name', 'slug'];
        $serializer = $this->getSerializerMock(['id', 'name', 'created_at']);
        $guesser = new ReferencedFieldGuesser($serializer, $bestChoices);

        $this->assertEquals('name', $guesser->guess('Acme\FooBundle\Entity\Post'));
    }

    public function testGuessingBestReferencedFieldShouldReturnNullIfNoFieldFound()
    {
        $bestChoices = ['title', 'slug'];
        $serializer = $this->getSerializerMock(['id', 'name', 'created_at']);
        $guesser = new ReferencedFieldGuesser($serializer, $bestChoices);

        $this->assertEquals(null, $guesser->guess('Acme\FooBundle\Entity\Post'));
    }

    private function getSerializerMock(array $fieldNames, $metadataForClassExpectedCalls = 1)
    {
        $metadataFactory = $this->getMockBuilder('Metadata\MetadataFactory')
            ->disableOriginalConstructor()
            ->getMock();

        $metadataFactory->expects($this->exactly($metadataForClassExpectedCalls))
            ->method('getMetadataForClass')
            ->willReturn((object) [
                'propertyMetadata' => array_combine($fieldNames, $fieldNames),
            ]);

        $serializer = $this->getMockBuilder('JMS\Serializer\Serializer')
            ->disableOriginalConstructor()
            ->getMock();

        $serializer->expects($this->once())
            ->method('getMetadataFactory')
            ->willReturn($metadataFactory);

        return $serializer;
    }
}
