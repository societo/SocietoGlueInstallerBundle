<?php

/**
 * SocietoGlueInstallerBundle
 * Copyright (C) 2011 Kousuke Ebihara
 *
 * This program is under the EPL/GPL/LGPL triple license.
 * Please see the Resources/meta/LICENSE file that was distributed with this file.
 */

namespace Societo\Glue\InstallerBundle\Configurator;

use Sensio\Bundle\DistributionBundle\Configurator\Step\StepInterface;
use Sensio\Bundle\DistributionBundle\Configurator\Configurator as BaseConfigurator;

class Configurator extends BaseConfigurator
{
    private $defaultFilename = '';

    public function __construct($kernelDir)
    {
        $this->defaultFilename = $kernelDir.'/config/default/parameters.ini';

        parent::__construct($kernelDir);
    }
    

    protected function read()
    {
        try {
            return parent::read();
        } catch (\Exception $e) {
            $original = $this->filename;
            $this->filename = $this->defaultFilename;

            return parent::read();
        }
    }
}
