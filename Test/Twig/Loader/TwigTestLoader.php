<?php

namespace marmelab\NgAdminGeneratorBundle\Test\Twig\Loader;

class TwigTestLoader implements \Twig_LoaderInterface
{
    private static $templates;

    public function __construct()
    {
        self::$templates = [
            'marmelabNgAdminGeneratorBundle:Configuration:config.js.twig' => __DIR__.'/../../../Resources/views/Configuration/config.js.twig',
        ];
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
