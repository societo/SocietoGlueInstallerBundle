<?php

/**
 * SocietoGlueInstallerBundle
 * Copyright (C) 2011 Kousuke Ebihara
 *
 * This program is under the EPL/GPL/LGPL triple license.
 * Please see the Resources/meta/LICENSE file that was distributed with this file.
 */

namespace Societo\Glue\InstallerBundle\Form;

use Symfony\Component\Validator\Constraints as Assert;

class AdminUser
{
    /**
     * @Assert\NotBlank
     */
    public $username;

    /**
     * @Assert\NotBlank
     */
    public $password;
}
