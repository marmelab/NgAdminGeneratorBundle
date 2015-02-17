<?php

namespace marmelab\NgAdminGeneratorBundle\Transformer;

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
        $transformedFields = [];
        $joinColumns = $this->getJoinColumns($doctrineMetadata);

        foreach ($doctrineMetadata->fieldMappings as $fieldMapping) {
            $field = [
                'name' => $fieldMapping['fieldName'],
            ];

            // if field is not in any relationship...
            if (!in_array($field['name'], array_keys($joinColumns))) {
                $field['type'] = self::$typeMapping[$fieldMapping['type']];
                $transformedFields[] = $field;
                continue;
            }

            $field['type'] = 'reference';
            $field = array_merge($field, $joinColumns[$field['name']]);

            $transformedFields[] = $field;
        }

        // check for inversed relationships
        $inversedRelationships = $this->getInversedRelationships($doctrineMetadata);
        if (isset($inversedRelationships[$doctrineMetadata->name])) {
            $transformedFields[] = array_merge([
                'type' => 'referenced_list',
            ], $inversedRelationships[$doctrineMetadata->name]);
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
                    'name' => $column['name'],
                    'referencedEntity' => $mappedEntity,
                    'referencedField' => $column['referencedColumnName']
                ];
            }

            // many-to-many relationship, through a joinTable
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

            // single relationship, through joinColumns
            $inversedRelationships[$mapping['sourceEntity']] = [
                'name' => $mappedEntity,
                'referencedEntity' => 'comment',
                'referencedField'=> 'post_id',
                'mappedBy' => $mapping['mappedBy']
            ];
        }

        return $inversedRelationships;
    }
}
