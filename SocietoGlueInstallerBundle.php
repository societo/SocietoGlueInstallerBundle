<?php

/**
 * SocietoGlueInstallerBundle
 * Copyright (C) 2011 Kousuke Ebihara
 *
 * This program is under the EPL/GPL/LGPL triple license.
 * Please see the Resources/meta/LICENSE file that was distributed with this file.
 */

namespace Societo\Glue\InstallerBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Sensio\Bundle\DistributionBundle\SensioDistributionBundle;

use Societo\Glue\InstallerBundle\Configurator\Step\DoctrineStep;
use Societo\Glue\InstallerBundle\Configurator\Step\SecretStep;

use Societo\Glue\InstallerBundle\Controller\ConfiguratorController;
use Societo\Glue\InstallerBundle\Controller\InstallerController;

use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * SocietoGlueInstallerBundle
 *
 * @author Kousuke Ebihara <ebihara@php.net>
 */
class SocietoGlueInstallerBundle extends SensioDistributionBundle
{
    public function boot()
    {
        $configurator = $this->container->get('sensio.distribution.webconfigurator');
        $configurator->addStep(new DoctrineStep($configurator->getParameters()));
        $configurator->addStep(new SecretStep($configurator->getParameters()));

        $dbname = $this->container->getParameter('database_name');
        $dispatcher = $this->container->get('event_dispatcher');
        $dispatcher->addListener(KernelEvents::REQUEST, function ($event) use ($dbname) {
            if ($event->getRequestType() != HttpKernelInterface::MASTER_REQUEST) {
                return null;
            }

            if ($dbname) {
                return null;
            }

            if (0 !== strpos($event->getRequest()->getPathInfo(), '/install')) {
                $url = $event->getRequest()->getUriForPath('/install');
                $event->setResponse(new RedirectResponse($url));
            }
        }, 96);
    }
}
