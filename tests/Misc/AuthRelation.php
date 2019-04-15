<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Amylian\Tests\Yii\Doctrine\Rbac\Misc;

/**
 * Description of AuthItemRelation
 *
 * @author Andreas Prucha, Abexto - Helicon Software Development <andreas.prucha@gmail.com>
 * 
 * @Entity
 * @HasLifecycleCallbacks
 */
class AuthRelation extends \yii\base\Model implements \Amylian\Yii\Doctrine\Rbac\Model\AuthRelationEntityInterface
{
    use \Amylian\Yii\Doctrine\Rbac\Model\AuthRelationEntityTrait;
}
