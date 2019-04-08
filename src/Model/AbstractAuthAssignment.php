<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Amylian\Yii\Doctrine\Rbac\Model;

/**
 * Description of BaseAuthAssignment
 *
 * @author Andreas Prucha, Abexto - Helicon Software Development <andreas.prucha@gmail.com>
 * 
 * @MappedSuperclass
 */
class AbstractAuthAssignment extends \yii\base\Model implements AuthAssignmentEntityInterface
{
    /**
     * @Column(type="datetime", name="created_at")
     */
    protected $_createdAt;
    
    /**
     * @var AuthItemEntityInterface
     * @ManyToOne(targetEntity="Amylian\Yii\Doctrine\Rbac\Model\AuthItemEntityInterface", 
     *            mappedBy="_name", 
     *            cascade={"all"}, 
     *            fetch="LAZY")
     * @JoinColumn(name="auth_item_name")
     */
    protected $_authItem;
    
    /**
     * @var \yii\web\IdentityInterface
     * @ManyToOne(targetEntity="Amylian\Yii\Doctrine\Rbac\Model\AuthItemEntityInterface", 
     *            mappedBy="_name", 
     *            cascade={"all"}, 
     *            fetch="LAZY")
     * @JoinColumn(name="user_id")
     */
    protected $_user;
    
    
    public function __construct($config = array())
    {
        parent::__construct($config);
        $this->setCreatedAt(null);
    }
    
    public function getAuthItem(): AuthItemEntityInterface
    {
        return $this->_authItem;
    }

    public function getCreatedAt()
    {
        return $this->_createdAt;
    }

    public function getUser(): \yii\web\IdentityInterface
    {
        return $this->_user;
    }

    public function setAuthItem(AuthItemEntityInterface $value)
    {
        return $this->_authItem;
    }

    public function setCreatedAt($value)
    {
        $this->_createdAt = $value ?? new \DateTime();
    }

    public function setUser(\yii\web\IdentityInterface $value)
    {
        $this->_user = $value;
    }

}
