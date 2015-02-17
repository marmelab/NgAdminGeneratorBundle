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
        return array_map(function($fieldMapping) {
            return [
                'name' => $fieldMapping['fieldName'],
                'type' => self::$typeMapping[$fieldMapping['type']],
            ];
        }, $doctrineMetadata->fieldMappings);
    }

    public function reverseTransform($ngAdminConfiguration)
    {
        throw new \DomainException("You shouldn't need to transform a ng-admin configuration into a Doctrine mapping.");
    }
}
