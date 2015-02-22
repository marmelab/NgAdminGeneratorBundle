<?php

namespace marmelab\NgAdminGeneratorBundle\Transformer;

use Doctrine\Common\Inflector\Inflector;
use JMS\Serializer\Serializer;

class ClassNameToNgAdminConfigurationTransformer implements TransformerInterface
{
    private $metadataFactory;
    private $bestReferencedFieldChoices = [];

    public function __construct(Serializer $serializer, array $bestReferencedFieldChoices = [])
    {
        $this->metadataFactory = $serializer->getMetadataFactory();
        $this->bestReferencedFieldChoices = $bestReferencedFieldChoices;
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
                    'referencedEntity' => $this->getEntityName($field->type['params'][0]['name']),
                    'referencedField' => $field->reflection->name.'_id',
                ];

            case 'Lemon\RestBundle\Serializer\IdCollection':
                return [
                    'type' => 'reference_many',
                    'referencedEntity' => $this->getEntityName($field->type['params'][0]['name']),
                    'referencedField' => $this->getBestReferencedField($field),
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

    private function getBestReferencedField($field)
    {
        if (!in_array($field->type['name'], ['ArrayCollection', 'Lemon\RestBundle\Serializer\IdCollection'])) {
            throw new \DomainException("Can't get a referenced field best match of a non reference field.");
        }

        $referencedClass = $field->type['params'][0]['name'];
        $metadata = $this->metadataFactory->getMetadataForClass($referencedClass);

        $fieldNames = array_keys($metadata->propertyMetadata);
        $bestFields = array_intersect($this->bestReferencedFieldChoices, $fieldNames);
        if (!count($bestFields)) {
            return 'id';
        }

        return current($bestFields);
    }
}
