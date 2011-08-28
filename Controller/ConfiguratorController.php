<?php

/**
 * SocietoGlueInstallerBundle
 * Copyright (C) 2011 Kousuke Ebihara
 *
 * This program is under the EPL/GPL/LGPL triple license.
 * Please see the Resources/meta/LICENSE file that was distributed with this file.
 */

namespace Societo\Glue\InstallerBundle\Controller;

use Symfony\Component\DependencyInjection\ContainerAware;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Sensio\Bundle\DistributionBundle\Controller\ConfiguratorController as BaseConfiguratorController;

/**
 * ConfiguratorController.
 *
 * @author Kousuke Ebihara <ebihara@php.net>
 */
class ConfiguratorController extends BaseConfiguratorController
{
    // TODO
    protected function checkMember()
    {
        $member = null;

        try {
            $em = $this->container->get('doctrine.orm.entity_manager');
            $member = $em->getRepository('SocietoBaseBundle:Member')->findOneBy(array(
                'isAdmin' => true,
            ));
        } catch (\Exception $e) {
            return null;
        }

        if ($member) {
            throw new \Exception();
        }
    }

    public function stepAction($index = 0)
    {
        $this->checkMember();

        return parent::stepAction($index);
    }

    public function checkAction()
    {
        $this->checkMember();

        $configurator = $this->container->get('sensio.distribution.webconfigurator');

        $majors = $configurator->getRequirements();
        $minors = $configurator->getOptionalSettings();

        $url = $this->container->get('router')->generate('_configurator_step', array('index' => 0));

        if (empty($majors) && empty($minors)) {
            return new RedirectResponse($url);
        }

        return $this->container->get('templating')->renderResponse('SocietoGlueInstallerBundle::Configurator/check.html.twig', array(
            'majors'  => $majors,
            'minors'  => $minors,
            'url'     => $url,
        ));
    }

    public function finalAction()
    {
        $this->checkMember();

        $configurator = $this->container->get('sensio.distribution.webconfigurator');
        $configurator->clean();

        return $this->container->get('templating')->renderResponse('SocietoGlueInstallerBundle::Configurator/final.html.twig', array(
            'parameters'  => $configurator->render(),
            'ini_path'    => $this->container->getParameter('kernel.root_dir').'/config/parameters.ini',
            'is_writable' => $configurator->isFileWritable(),
        ));
    }
}
