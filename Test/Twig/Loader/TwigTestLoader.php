<?php

namespace marmelab\NgAdminGeneratorBundle\Test\Twig\Loader;

class TwigTestLoader implements \Twig_LoaderInterface
{
    private static $templates = [];

    public function __construct()
    {
        foreach([
            'config.js.twig',
            'field.js.twig',
            'fields.js.twig',
            'reference.js.twig',
            'reference_many.js.twig',
            'referenced_list.js.twig'
        ] as $template) {
            self::$templates['marmelabNgAdminGeneratorBundle:Configuration:' . $template] = __DIR__ . '/../../../Resources/views/Configuration/'.$template;
        }
    }

    public function isFresh($name, $timestamp)
    {
        return true;
    }

    public function getCacheKey($file)
    {
        return $file;
    }

    public function getSource($name)
    {
        return file_get_contents(self::$templates[$name]);
    }
}
