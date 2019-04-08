<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Amylian\Yii\Doctrine\Rbac\Model;

/**
 * Base interface for AuthAssignment-Entities
 * 
 * @author Andreas Prucha, Abexto - Helicon Software Development <andreas.prucha@gmail.com>
 */
trait AuthAssignmentEntityTrait
{
    /**
     * @var AuthItemEntityInterface
     * @Id
     * @ManyToOne(targetEntity="Amylian\Yii\Doctrine\Rbac\Model\AuthItemEntityInterface", cascade={"persist"}, fetch="EAGER")
     * @JoinColumn(name="role_auth_item_name", referencedColumnName="name")
     */
    protected $role = null;
    
    /**
     *
     * @var \DateTime
     * @Column(type="datetime", nullable=false)
     */
    protected $createdAt = null;
    
    /**
     * Assigns the role item
     * @param \Amylian\Yii\Doctrine\Rbac\Model\AuthItemEntityInterface|null $role
     */
    public function setAuthItem(?AuthItemEntityInterface $role)
    {
        $this->role = $role;
    }
    
    public function getAuthItem(): ?AuthItemEntityInterface
    {
        return $this->role;
    }
    
    public function setCreatedAt($datetime)
    {
        if ($datetime instanceof \DateTime) {
            $this->createdAt = $datetime;
        } else {
            $datetime = $datetime ?? 'now';
            $this->createdAt = new \DateTime(is_numeric($datetime) ? '@'.$datetime : $datetime);
        }
    
    }
    
    public function getCreatedAt(): \DateTime
    {
        if (!$this->createdAt) {
            $this->createdAt = new \DateTime();
        }
        return $this->createdAt;
    }
    
    public function getAssignment(): \yii\rbac\Assignment
    {
        $result = new \yii\rbac\Assignment();
        $result->createdAt = $this->getCreatedAt()->getTimestamp();
        $result->userId = $this->getUserId();
        $result->roleName = $this->getAuthItem()->getName();
        return $result;
    }
}
