<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Amylian\Yii\Doctrine\Rbac\Model;

/**
 * Standard implementation Trait for {@see AuthRelationEntityInterface}
 *
 * @author Andreas Prucha, Abexto - Helicon Software Development <andreas.prucha@gmail.com>
 * 
 * @MappedSuperclass
 */
trait AuthRelationEntityTrait
{

    /**
     * @var AuthItemEntityInterface
     * @Id
     * @ManyToOne(targetEntity="Amylian\Yii\Doctrine\Rbac\Model\AuthItemEntityInterface", fetch="EAGER", cascade={"persist"}, inversedBy="parentAuthRelations")
     * @JoinColumn(name = "child_auth_item_name", referencedColumnName="name")
     */
    protected $child = null;

    /**
     * @var AuthItemEntityInterface 
     * @Id
     * @ManyToOne(targetEntity="Amylian\Yii\Doctrine\Rbac\Model\AuthItemEntityInterface", fetch="EAGER", cascade={"persist"}, inversedBy="childAuthRelations")
     * @JoinColumn(name = "parent_auth_item_name", referencedColumnName="name")
     */
    protected $parent = null;
    
    /**
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
        if (!isset($this->reatedAt)) {
            $this->createdAt = new \DateTime();
        }
    }
    

    public function getChild(): ?AuthItemEntityInterface
    {
        return $this->child;
    }

    public function getParent(): ?AuthItemEntityInterface
    {
        return $this->parent;
    }

    public function setChild(?AuthItemEntityInterface $authItem)
    {
        $oldAuthItem = $this->child;
        if ($authItem !== $this->child) {
            $this->child = $authItem;
            if ($authItem === null) {
                if ($oldAuthItem !== null) {
                    $oldAuthItem->removeParentAuthItemRelation($this);
                }
            } else {
                $authItem->addParentAuthItemRelation($this);
            }
        }
    }

    public function setParent(?AuthItemEntityInterface $authItem)
    {
        $oldAuthItem = $this->parent;
        if ($authItem !== $this->parent) {
            $this->parent = $authItem;
            if ($authItem === null) {
                if ($oldAuthItem !== null) {
                    $oldAuthItem->removeChildAuthItemRelation($this);
                }
            } else {
                $authItem->addChildAuthItemRelation($this);
            }
        }
    }

}
