<?php

namespace marmelab\NgAdminGeneratorBundle\Transformer;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Common\Inflector\Inflector;
use marmelab\NgAdminGeneratorBundle\Guesser\ReferencedFieldGuesser;

class NgAdminWithRelationshipsTransformer implements TransformerInterface
{
    private $metadataFactory;
    private $referencedFieldGuesser;

    public function __construct(EntityManagerInterface $entityManager, ReferencedFieldGuesser $referencedFieldGuesser)
    {
        $this->metadataFactory = $entityManager->getMetadataFactory();
        $this->referencedFieldGuesser = $referencedFieldGuesser;
    }

    public function transform($configuration)
    {
        $transformedConfiguration = $this->addForeignKeyFieldToReferencedFields($configuration);
        $transformedConfiguration = $this->transformReferenceRelationships($transformedConfiguration);

        return $transformedConfiguration;
    }

    public function reverseTransform($configWithRelationships)
    {
        throw new \DomainException("You shouldn't have to remove relationships from a ng-admin configuration.");
    }

    private function addForeignKeyFieldToReferencedFields($configuration)
    {
        $referenceFields = array_filter($configuration['fields'], function($field) {
            return in_array($field['type'], ['referenced_list', 'reference_many']);
        });

        if (!count($referenceFields)) {
            return $configuration;
        }

        $transformedConfiguration = $configuration;
        foreach ($referenceFields as $index => $referenceField) {
            // if referenced field already found with JMS serializer, just skip it.
            if ($referenceField['referencedField']) {
                continue;
            }

            if ($referenceField['type'] === 'referenced_list') {
                $targetEntity = $configuration['class'];
                $sourceEntity = $referenceField['referencedEntity']['class'];
            }

            $referenceMetadata = $this->metadataFactory->getMetadataFor($referenceField['referencedEntity']['class']);
            foreach ($referenceMetadata->associationMappings as $mapping) {
                if ($mapping['sourceEntity'] !== $sourceEntity && $mapping['targetEntity'] !== $targetEntity) {
                    continue;
                }

                if ($referenceField['type'] === 'referenced_list') {
                    $transformedConfiguration['fields'][$index]['referencedField'] = $mapping['targetToSourceKeyColumns']['id'];
                }
            }
        }

        return $transformedConfiguration;
    }

    /**
     * Turns foreign key columns into Reference field instead of simple "number" one.
     */
    private function transformReferenceRelationships($configuration)
    {
        $associationMappings = $this->metadataFactory->getMetadataFor($configuration['class'])->associationMappings;
        if (!count($associationMappings)) {
            return $configuration;
        }

        $transformedConfiguration = $configuration;
        foreach ($configuration['fields'] as $fieldIndex => $field) {
            $matchingAssociation = array_filter($associationMappings, function($association) use($field) {
                return isset($association['joinColumns']) && $association['joinColumns'][0]['name'] === $field['name'];
            });

            if (!count($matchingAssociation)) {
                continue;
            }

            $matchingAssociation = current($matchingAssociation);

            $transformedField = [
                'name' => $field['name'],
                'type' => 'reference',
                'referencedEntity' => [
                    'name' => Inflector::pluralize($matchingAssociation['fieldName']),
                    'class' => $matchingAssociation['targetEntity'],
                ],
                'referencedField' => $this->referencedFieldGuesser->guess($matchingAssociation['targetEntity']),
            ];

            $transformedConfiguration['fields'][$fieldIndex] = $transformedField;
        }

        return $transformedConfiguration;
    }
}
