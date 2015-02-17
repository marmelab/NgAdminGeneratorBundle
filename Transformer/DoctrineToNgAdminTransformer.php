<?php

namespace marmelab\NgAdminGeneratorBundle\Transformer;

use Doctrine\Common\Util\Inflector;
use Symfony\Component\Form\DataTransformerInterface;

class DoctrineToNgAdminTransformer implements DataTransformerInterface
{
    /**
     * @see http://doctrine-dbal.readthedocs.org/en/latest/reference/types.html
     */
    private static $typeMapping = [
        'smallint' => 'number',
        'integer' => 'number',
        'bigint' => 'number',
        'decimal' => 'number',
        'float' => 'number',
        'string' => 'string',
        'guid' => 'string',
        'datetime' => 'string',
        'datetimez' => 'string',
        'time' => 'string',
        'text' => 'text',
        'boolean' => 'boolean',
        'date' => 'date',
    ];

    public function transform($doctrineMetadata)
    {
        $joinColumns = $this->getJoinColumns($doctrineMetadata);

        $transformedFields = [];
        foreach ($doctrineMetadata->fieldMappings as $fieldMapping) {
            $field = [
                'name' => $fieldMapping['fieldName'],
            ];

            // if field is in relationship, we'll deal it later
            if (in_array($field['name'], array_keys($joinColumns))) {
                continue;
            }

            $field['type'] = self::$typeMapping[$fieldMapping['type']];
            $transformedFields[] = $field;
        }

        // Deal with all relationships
        $transformedFields = array_merge($transformedFields, $joinColumns);

        // check for inversed relationships
        $inversedRelationships = $this->getInversedRelationships($doctrineMetadata);
        if (isset($inversedRelationships[$doctrineMetadata->name])) {
            $transformedFields[] = $inversedRelationships[$doctrineMetadata->name];
        }

        return $transformedFields;
    }

    public function reverseTransform($ngAdminConfiguration)
    {
        throw new \DomainException("You shouldn't need to transform a ng-admin configuration into a Doctrine mapping.");
    }

    private function getJoinColumns($metadata)
    {
        $joinColumns = [];
        foreach ($metadata->associationMappings as $mappedEntity => $mapping) {
            // should own property, otherwise it's inversed relationship
            if (!$mapping['isOwningSide']) {
                continue;
            }

            // single relationship, through joinColumns
            if (isset($mapping['joinColumns'])) {
                $column = $mapping['joinColumns'][0];
                $joinColumns[$column['name']] = [
                    'type' => 'reference',
                    'name' => $column['name'],
                    'referencedEntity' => $mappedEntity,
                    'referencedField' => $column['referencedColumnName']
                ];
            }

            // many-to-many relationship, through a joinTable
            if (isset($mapping['joinTable'])) {
                $joinColumns[$mapping['fieldName']] = [
                    'type' => 'reference_many',
                    'name' => $mapping['fieldName'],
                    'referencedEntity' => $this->getEntityName($mapping['targetEntity']),
                    'referencedField' => $mapping['joinTable']['inverseJoinColumns'][0]['referencedColumnName'],
                ];
            }
        }

        return $joinColumns;
    }

    private function getInversedRelationships($metadata)
    {
        $inversedRelationships = [];
        foreach ($metadata->associationMappings as $mappedEntity => $mapping) {
            // should own property, otherwise it's direct relationship
            if ($mapping['isOwningSide']) {
                continue;
            }

            $inversedRelationships[$mapping['sourceEntity']] = [
                'type' => 'referenced_list',
                'name' => $mappedEntity,
                'referencedEntity' => 'comment',
                'referencedField'=> 'post_id'
            ];
        }

        return $inversedRelationships;
    }

    private function getEntityName($className)
    {
        $classParts = explode('\\', $className);
        $entityName = end($classParts);

        return Inflector::tableize($entityName);
    }
}
