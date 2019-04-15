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
