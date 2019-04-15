<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Amylian\Tests\Yii\Doctrine\Rbac\Misc;

/**
 * Description of AuthRule
 *
 * @author Andreas Prucha, Abexto - Helicon Software Development <andreas.prucha@gmail.com>
 *  
 * @Entity
 * @HasLifecycleCallbacks;
 */
class AuthRule extends \yii\base\Model implements \Amylian\Yii\Doctrine\Rbac\Model\AuthRuleEntityInterface
{
    use \Amylian\Yii\Doctrine\Rbac\Model\AuthRuleEntityTrait;
}
