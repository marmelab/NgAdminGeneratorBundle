<?php

namespace marmelab\NgAdminGeneratorBundle\Transformer;

class DoctrineToNgAdminTransformer implements TransformerInterface
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
            // add link from owner entity, not for inverse relationship
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
}
