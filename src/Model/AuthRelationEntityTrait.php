<?php

/*
 * BSD 3-Clause License
 * 
 * Copyright (c) 2019, Andreas Prucha (Abexto - Helicon Software Development) 
 * Amylian Project
 *  
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 * 
 * * Redistributions of source code must retain the above copyright notice, this
 *   list of conditions and the following disclaimer.
 * 
 * * Redistributions in binary form must reproduce the above copyright notice,
 *   this list of conditions and the following disclaimer in the documentation
 *   and/or other materials provided with the distribution.
 * 
 * * Neither the name of the copyright holder nor the names of its
 *   contributors may be used to endorse or promote products derived from
 *   this software without specific prior written permission.
 * 
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
 * AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
 * IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
 * DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE
 * FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL
 * DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR
 * SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
 * CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY,
 * OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 * 
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
