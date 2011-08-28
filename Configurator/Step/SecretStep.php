<?php

/**
 * SocietoGlueInstallerBundle
 * Copyright (C) 2011 Kousuke Ebihara
 *
 * This program is under the EPL/GPL/LGPL triple license.
 * Please see the Resources/meta/LICENSE file that was distributed with this file.
 */

namespace Societo\Glue\InstallerBundle\Configurator\Step;

use Sensio\Bundle\DistributionBundle\Configurator\Step\SecretStep as BaseSecretStep;
use Societo\Glue\InstallerBundle\Configurator\Form\DoctrineStepType;

class SecretStep extends BaseSecretStep
{
    public function getTemplate()
    {
        return 'SocietoGlueInstallerBundle:Configurator/Step:secret.html.twig';
    }
}
