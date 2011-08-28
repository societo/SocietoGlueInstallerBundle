<?php

/**
 * SocietoGlueInstallerBundle
 * Copyright (C) 2011 Kousuke Ebihara
 *
 * This program is under the EPL/GPL/LGPL triple license.
 * Please see the Resources/meta/LICENSE file that was distributed with this file.
 */

namespace Societo\Glue\InstallerBundle\Configurator\Form;

use Symfony\Component\Form\FormBuilder;
use Sensio\Bundle\DistributionBundle\Configurator\Form\DoctrineStepType as BaseDoctrineStepType;

class DoctrineStepType extends BaseDoctrineStepType
{
    public function buildForm(FormBuilder $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $builder->add('password', 'password', array('required' => false));
        $builder->add('driver', 'hidden');
    }
}
