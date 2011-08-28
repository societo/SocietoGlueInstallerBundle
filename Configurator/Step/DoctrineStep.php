<?php

/**
 * SocietoGlueInstallerBundle
 * Copyright (C) 2011 Kousuke Ebihara
 *
 * This program is under the EPL/GPL/LGPL triple license.
 * Please see the Resources/meta/LICENSE file that was distributed with this file.
 */

namespace Societo\Glue\InstallerBundle\Configurator\Step;

use Sensio\Bundle\DistributionBundle\Configurator\Step\DoctrineStep as BaseDoctrineStep;
use Societo\Glue\InstallerBundle\Configurator\Form\DoctrineStepType;

class DoctrineStep extends BaseDoctrineStep
{
    public function getTemplate()
    {
        return 'SocietoGlueInstallerBundle:Configurator/Step:doctrine.html.twig';
    }

    public function getFormType()
    {
        return new DoctrineStepType();
    }
}
