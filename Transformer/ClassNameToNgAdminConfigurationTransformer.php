<?php

namespace marmelab\NgAdminGeneratorBundle\Transformer;

use Doctrine\Common\Inflector\Inflector;
use JMS\Serializer\Serializer;
use marmelab\NgAdminGeneratorBundle\Guesser\ReferencedFieldGuesser;

class ClassNameToNgAdminConfigurationTransformer implements TransformerInterface
{
    private $metadataFactory;
    private $guesser;

    public function __construct(Serializer $serializer, ReferencedFieldGuesser $guesser)
    {
        $this->metadataFactory = $serializer->getMetadataFactory();
        $this->guesser = $guesser;
    }

    public function transform($className)
    {
        $metadata = $this->metadataFactory->getMetadataForClass($className);

        $entity = [
            'class' => $className,
            'name' => $this->getEntityName($className),
            'fields' => [],
        ];

        foreach ($metadata->propertyMetadata as $fieldName => $jmsField) {
            $field = ['name' => $fieldName];
            $field = array_merge($field, $this->getExtraDataBasedOnType($jmsField));

            $entity['fields'][] = $field;
        }

        return $entity;
    }

    public function reverseTransform($data)
    {
        throw new \DomainException("You shouldn't need to turn a ng-admin collection into JMS metadata.");
    }

    private function getExtraDataBasedOnType($field)
    {
        $type = $field->type['name'];

        switch ($field->type['name']) {
            case 'integer':
                return ['type' => 'number'];

            case 'string':
                if (in_array($field->reflection->name, [
                    'body',
                    'content',
                    'details',
                ])) {
                    return ['type' => 'text'];
                }

                return ['type' => 'string'];

            case 'ArrayCollection':
                return [
                    'type' => 'referenced_list',
                    'referencedEntity' => [
                        'class' => $field->type['params'][0]['name'],
                        'name' => $this->getEntityName($field->type['params'][0]['name'])
                    ],
                    'referencedField' => $this->guesser->guess($field->type['params'][0]['name']),
                ];

            case 'Lemon\RestBundle\Serializer\IdCollection':
                return [
                    'type' => 'reference_many',
                    'referencedEntity' => [
                        'class' => $field->type['params'][0]['name'],
                        'name' => $this->getEntityName($field->type['params'][0]['name'])
                    ],
                    'referencedField' => $this->guesser->guess($field->type['params'][0]['name']),
                ];

            case 'DateTime':
                return [
                    'type' => 'date',
                ];
        }

        return ['type' => $type];
    }

    private function getEntityName($className)
    {
        $classParts = explode('\\', $className);
        $entityName = end($classParts);

        return Inflector::tableize($entityName);
    }
}
