<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Amylian\Yii\Doctrine\Rbac;

/**
 * Description of IdentityRepository
 *
 * @author Andreas Prucha, Abexto - Helicon Software Development <andreas.prucha@gmail.com>
 */
class IdentityRepository extends \Doctrine\ORM\EntityRepository implements IdentityRepositoryInterface
{
    
    public function findByAuthKey($authKey)
    {
        return $this->findOneBy(['authKey' => $authKey]);
    }

    public function findById($id)
    {
        return $this->findOneBy(['id' => $id]);
    }

    public function findByUserName($userName)
    {
        return $this->findOneBy(['id' => mb_strtolower($userName)]);
    }

}
