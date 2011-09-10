<?php

/**
 * This file is applied CC0 <http://creativecommons.org/publicdomain/zero/1.0/>
 */

namespace Societo\Glue\InstallerBundle\Tests\Controller;

use Societo\BaseBundle\Test\WebTestCase;

class ConfiguratorControllerTest extends WebTestCase
{
    public function testStepZeroAction()
    {
        $this->loadFixtures();

        $client = static::createClient();
        $client->request('GET', '/install/config/step/0');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
    }

    public function testMemberExists()
    {
        $this->loadFixtures(array(
            'Societo\BaseBundle\Tests\Fixtures\LoadAccountData',
        ));

        $client = static::createClient();
        $client->request('GET', '/install/config/step/0');

        $this->assertEquals(404, $client->getResponse()->getStatusCode());
    }
}
