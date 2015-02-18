<?php

namespace marmelab\NgAdminGeneratorBundle\Transformer;

// We redefine transformer interface to avoid adding dependency to symfony/Form component.
interface TransformerInterface
{
    public function transform($data);

    public function reverseTransform($data);
}
