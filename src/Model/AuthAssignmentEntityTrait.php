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
     * @ManyToOne(targetEntity="Amylian\Yii\Doctrine\Rbac\Model\AuthItemEntityInterface", 
     *          cascade={"persist"}, fetch="EAGER", inversedBy="authAssignments")
     * @JoinColumn(name="auth_item_name", referencedColumnName="name")
     */
    protected $authItem = null;

    /**
     *
     * @var \DateTime
     * @Column(type="datetime", nullable=false)
     */
    protected $createdAt = null;

    /**
     * Handler for Doctrine prePersist and preUpdate events
     * 
     * @internal Do not call this method directly. It's called by the Doctrine ORM
     *           when the Entity is inserted/updated. Enable evens by adding
     *           the annotation <code>@HasLifecycleCallbacks</code> to your class
     *           annotations in order to enable lifecycle ballbacks.
     * @param \Doctrine\ORM\Event\LifecycleEventArgs $args
     * 
     * @PrePersist
     * @PreUpdate
     */
    public function handlePersistAndUpdateEvent(\Doctrine\ORM\Event\LifecycleEventArgs $args)
    {
        if (!isset($this->createdAt)) {
            $this->createdAt = new \DateTime();
        }
    }

    /**
     * {@inheritDoc}
     */
    public function setAuthItem(?AuthItemEntityInterface $role)
    {
        if ($this->authItem !== $role) {
            $prevRole = $this->authItem;
            if ($prevRole) {
                $prevRole->getAuthAssignments()->removeElement($this);
            }
            $this->authItem = $role;
            if ($this->authItem) {
                if (!$this->authItem->getAuthAssignments()->contains($this)) {
                    $this->authItem->getAuthAssignments()->add($this);
                }
            }
        }
    }

    /**
     * {@inheritDoc}
     */
    public function getAuthItem(): ?AuthItemEntityInterface
    {
        return $this->authItem;
    }

    /**
     * {@inheritDoc}
     */
    public function setCreatedAt($datetime)
    {
        if ($datetime instanceof \DateTime) {
            $this->createdAt = $datetime;
        } else {
            $datetime = $datetime ?? 'now';
            $this->createdAt = new \DateTime(is_numeric($datetime) ? '@' . $datetime : $datetime);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function getCreatedAt(): \DateTime
    {
        if (!$this->createdAt) {
            $this->createdAt = new \DateTime();
        }
        return $this->createdAt;
    }

    /**
     * {@inheritDoc}
     */
    public function getAssignment(): \yii\rbac\Assignment
    {
        $result = new \yii\rbac\Assignment();
        $result->createdAt = $this->getCreatedAt()->getTimestamp();
        $result->userId = $this->getUserId();
        $result->roleName = $this->getAuthItem()->getName();
        return $result;
    }

}
