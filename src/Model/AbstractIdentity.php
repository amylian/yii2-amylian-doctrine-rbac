<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Amylian\Yii\Doctrine\Rbac\Model;

/**
 * Description of AbstractIdentity
 *
 * @author Andreas Prucha, Abexto - Helicon Software Development <andreas.prucha@gmail.com>
 * 
 * @MappedSuperclass
 */
class AbstractIdentity extends \yii\base\Model implements IdentityInterface
{
    
    public function getAuthKey(): string
    {
        
    }

    public function getId()
    {
        
    }

    public function validateAuthKey($authKey): bool
    {
        
    }

    public static function findIdentity($id): \yii\web\IdentityInterface
    {
        
    }

    public static function findIdentityByAccessToken($token, $type = null): \yii\web\IdentityInterface
    {
        
    }

}
