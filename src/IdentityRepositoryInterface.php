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
interface IdentityRepositoryInterface
{
    /**
     * Returns the Entity by Id
     * @param type $id
     */
    public function findById($id);
    /**
     * Returns the Entity by User Name
     * @param string $userName
     */
    public function findByUserName($userName);
    /**
     * Returns the Entity by Auth Key
     * @param string $authKey
     */
    public function findByAuthKey($authKey);
}
