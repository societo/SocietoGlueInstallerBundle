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
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Societo\Glue\InstallerBundle\Form\AdminType;
use Societo\Glue\InstallerBundle\Form\AdminUser;

use Societo\AuthenticationBundle\RegistrationHandler;

use Societo\BaseBundle\Entity\Member;
use Societo\BaseBundle\Entity\Account;
use Societo\BaseBundle\Entity\MemberConfig;
use Societo\PageBundle\Entity\Page;
use Societo\PageBundle\Entity\PageGadget;

/**
 * InstallerController.
 *
 * @author Kousuke Ebihara <ebihara@php.net>
 */
class InstallerController extends Controller
{
    protected function checkMember()
    {
        $em = $this->getDoctrine()->getEntityManager();
        $member = null;

        try {
            $member = $em->getRepository('SocietoBaseBundle:Member')->findOneBy(array(
                'isAdmin' => true,
            ));
        } catch (\Exception $e) {
        }

        if ($member) {
            throw $this->createNotFoundException();
        }
    }

    protected function checkParameterFile()
    {
        $url = $this->generateUrl('_configurator_step', array('index' => 0));
        $path = $this->container->getParameter('kernel.root_dir').'/config/parameters.ini';
        if (!is_readable($path)) {
            return $this->render('SocietoGlueInstallerBundle:Installer:readable_ini_error.html.twig', array(
                'ini_path' => $path,
                'url' => $url,
            ));
        }

        $params = parse_ini_file($path);
        if ('ThisTokenIsNotSoSecretChangeIt' === $params['secret']) {
            $this->get('session')->setFlash('notice',
                sprintf('You should change "secret" value in %s. <a href="%s">Retry to configure parameters</a> is recommended.',
                $path, $url
            ));
        }
    }

    protected function checkDatabase()
    {
        $conn = $this->getDoctrine()->getConnection();
        try {
            $conn->connect();
        } catch (\PDOException $e) {
            return $this->render('SocietoGlueInstallerBundle:Installer:db_error.html.twig', array(
                'message' => $e->getMessage(),
            ));
        }
    }

    public function checkSchema()
    {
        $conn = $this->getDoctrine()->getConnection();
        $manager = $conn->getSchemaManager();

        $em = $this->getDoctrine()->getEntityManager();
        $metadatas = $em->getMetadataFactory()->getAllMetadata();

        $missings = array();

        foreach ($metadatas as $metadata) {
            if ($metadata->isMappedSuperclass) {
                continue;
            }

            $table = $metadata->getTableName();
            if (!$manager->tablesExist($table)) {
                $missings[] = $table;
            }
        }

        if ($missings) {
            return $this->render('SocietoGlueInstallerBundle:Installer:missing_table_error.html.twig', array(
                'missings' => $missings,
            ));
        }

        return false;
    }

    public function startAction()
    {
        // clear cache
        $cacheDir = $this->container->getParameter('kernel.cache_dir');
        $oldCacheDir  = $cacheDir.'_old';
        $filesystem = $this->get('filesystem');

        try {
            $filesystem->rename($cacheDir, $oldCacheDir);
            $filesystem->remove($cacheDir);
            $filesystem->remove($oldCacheDir);

            $filesystem->mkdir($cacheDir.'/annotations');
        } catch (\Exception $e) {
        }

        return $this->redirect($this->generateUrl('_installer_assets'));
    }

    public function installAssetsAction()
    {
        $filesystem = $this->get('filesystem');
        $path = $this->get('kernel')->getRootDir().'/../web/bundles/';

        try {
            foreach ($this->get('kernel')->getBundles() as $bundle) {
                if (is_dir($originDir = $bundle->getPath().'/Resources/public')) {
                    $targetDir = $path.preg_replace('/bundle$/', '', strtolower($bundle->getName()));

                    $filesystem->remove($targetDir);
                    $filesystem->mkdir($targetDir, 0777);
                    $filesystem->mirror($originDir, $targetDir);
                }
            }
        } catch (\Exception $e) {
        }

        return $this->redirect($this->generateUrl('_installer_table'));
    }

    public function createTableAction()
    {
        if ($response = $this->checkParameterFile()) {
            return $response;
        }

        if ($response = $this->checkDatabase()) {
            return $response;
        }

        $this->checkMember();

        $sqls = $this->createSchemaSql();
        $hasError = false;
        $message = '';

        $conn = $this->getDoctrine()->getConnection();
        try {
            $conn->beginTransaction();
            foreach ($sqls as $sql) {
                $conn->executeQuery($sql);
            }
            $conn->commit();
        } catch (\Exception $e) {
            $conn->rollback();

            $hasError = true;
            $message = $e->getMessage();
        }

        return $this->render('SocietoGlueInstallerBundle:Installer:start.html.twig', array(
            'sqls' => $sqls,
            'has_error' => $hasError,
            'message' => $message,
        ));
    }

    public function adminAction()
    {
        if ($response = $this->checkParameterFile()) {
            return $response;
        }

        if ($response = $this->checkDatabase()) {
            return $response;
        }

        if ($response = $this->checkSchema()) {
            return $response;
        }

        $this->checkMember();

        $form = $this->get('form.factory')->create(new AdminType());

        return $this->render('SocietoGlueInstallerBundle:Installer:admin.html.twig', array(
            'form' => $form->createView(),
        ));
    }

    public function createAction()
    {
        $this->checkMember();

        if ($response = $this->checkParameterFile()) {
            return $response;
        }

        $request = $this->get('request');

        $form = $this->get('form.factory')->create(new AdminType(), new AdminUser());
        $form->bindRequest($request);
        if ($form->isValid()) {
            $this->createData($form->getData());

            $this->get('session')->setFlash('success', 'Installation was successful. Log-in and start to configure this site as you please. Enjoy!');
            $this->get('session')->set('_security.target_path', $this->generateUrl('_backend', array(), true));

            return $this->redirect($this->generateUrl('_security_login'));
        }

        return $this->render('SocietoGlueInstallerBundle:Installer:start.html.twig', array(
            'form' => $form->createView(),
        ));
    }

    protected function createSchemaSql()
    {
        $em = $this->getDoctrine()->getEntityManager();
        $metadatas = $em->getMetadataFactory()->getAllMetadata();
        if (empty($metadatas)) {
            throw new \LogicException();
        }

        $tool = new \Doctrine\ORM\Tools\SchemaTool($em);

        return $tool->getCreateSchemaSql($metadatas);
    }

    // TODO
    protected function createData($input)
    {
        $em = $this->getDoctrine()->getEntityManager();
        $dispatcher = $this->get('event_dispatcher');

        $namespace = 'SocietoUsernameAuthPlugin';
        $signUp = new \Societo\BaseBundle\Entity\SignUp($namespace, $input->username, array(
            'password' => $input->password,
            'profile' => array(),
        ));

        $handler = new RegistrationHandler($em, $dispatcher, $this->container->getParameter('kernel.secret'));
        $handler->setSignUp($signUp);
        $member = $handler->register();

        $member->setIsAdmin(true);
        $em->persist($member);

        $page = new Page('insecure_default', 'login');
        $em->persist($page);

        $gadget = new PageGadget($page, 'head', 'SocietoUsernameAuthPlugin:LoginFormGadget');
        $em->persist($gadget);

        $page = new Page('secure_default');
        $em->persist($page);

        $em->flush();
    }
}
