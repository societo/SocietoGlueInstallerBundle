<?php

/**
 * SocietoGlueInstallerBundle
 * Copyright (C) 2011 Kousuke Ebihara
 *
 * This program is under the EPL/GPL/LGPL triple license.
 * Please see the Resources/meta/LICENSE file that was distributed with this file.
 */

namespace Societo\Glue\InstallerBundle\DependencyInjection;

use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\Config\FileLocator;

use Symfony\Component\Config\Definition\Processor;
use Sensio\Bundle\DistributionBundle\DependencyInjection\SensioDistributionExtension;

class SocietoGlueInstallerExtension extends SensioDistributionExtension
{
    public function load(array $configs, ContainerBuilder $container)
    {
        parent::load($configs, $container);

        $loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('config.xml');
    }

    public function getNamespace()
    {
        return 'http://schema.societo.org/glue/installer';
    }

    public function getAlias()
    {
        return 'societo_glue_installer';
    }
}
