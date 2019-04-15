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
 * Base Trait for {@AuthItemInterface}
 * 
 * Applications using this trait
 * 
 * *    MUST implment {@see AuthItemInterface}
 * *    MUST declare the class @Entity for Doctrine
 * *    MUST add the mapping AuthItemInterface => Concrete Class to 
 *      {@see \Doctrine\ORM\Tools\ResolveTargetEntityListener} 
 * *    MUST override {@see newAuthItemRelation()}
 * *    SHOULD add the anntoation <code>@HasLifecycleCallbacks</code> to the class annotation
 *      in order to enable lifecycle-callbacks
 *
 * @author Andreas Prucha, Abexto - Helicon Software Development <andreas.prucha@gmail.com>
 * 
 */
trait AuthItemEntityTrait
{

    /**
     * @var string 
     * @Id
     * @Column(type="string", length=64, unique=true, nullable=false);
     */
    protected $name = null;

    /**
     * @var int the type of the item. This should be either [[TYPE_ROLE]] or [[TYPE_PERMISSION]].
     * @Column(type="smallint");
     */
    protected $type;

    /**
     * @var \Doctrine\Common\Collections\ArrayCollection|AuthRelationEntityInterface[]
     * @OneToMany(targetEntity="Amylian\Yii\Doctrine\Rbac\Model\AuthRelationEntityInterface", 
     *            mappedBy="parent", 
     *            cascade={"all"}, 
     *            fetch="LAZY",
     *            orphanRemoval=true)
     */
    protected $childAuthRelations;

    /**
     * @var \Doctrine\Common\Collections\ArrayCollection|AuthRelationEntityInterface[]
     * @OneToMany(targetEntity="Amylian\Yii\Doctrine\Rbac\Model\AuthRelationEntityInterface", 
     *            mappedBy="child", 
     *            cascade={"all"}, 
     *            fetch="LAZY",
     *            orphanRemoval=true)
     */
    protected $parentAuthRelations;

    /**
     * @var AuthAssignmentEntityInterface[]|\Doctrine\Common\Collections\ArrayCollection 
     * @OneToMany(targetEntity="Amylian\Yii\Doctrine\Rbac\Model\AuthAssignmentEntityInterface", 
     *            mappedBy="authItem", 
     *            cascade={"all"},
     *            fetch="EXTRA_LAZY",
     *            orphanRemoval=true)
     */
    protected $authAssignments = null;

    /**
     * @var string 
     * @Column(type="string", length=1024, unique=false, nullable=true)
     */
    protected $description = null;

    /**
     * @var mixed 
     * @Column(type="text", unique=false, nullable=true)
     */
    protected $data = null;

    /**
     * @var \DateTime 
     * @Column(type="datetime", unique=false, nullable=false)
     */
    protected $createdAt = null;

    /**
     * @var \DateTime 
     * @Column(type="datetime", unique=false, nullable=false)
     */
    protected $updatedAt = null;

    /**
     * @var AuthRuleEntityInterface
     * @ManyToOne(targetEntity="Amylian\Yii\Doctrine\Rbac\Model\AuthRuleEntityInterface", cascade={"persist"}, 
     *           inversedBy="usedByAuthItems", fetch="EAGER")
     * @JoinColumn(name="rule_name", referencedColumnName="name")
     */
    protected $rule = null;

    /**
     * @var AuthItemEntityInterface[]|null
     */
    private $_childAuthItems = null;

    /**
     * @var AuthItemEntityInterface[]|null
     */
    private $_parentAuthItems = null;

    /**
     * Representation as {@see \yii\rbac\Item}
     * Created by {@see getItem()}. If a property changes it's set to null.
     * @var \yii\rbac\Item 
     */
    protected $_item = null;

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
            $this->updatedAt = $this->createdAt;
        } else {
            $this->updatedAt = new \DateTime();
        }
    }

    /**
     * Creates a new Instance of the entity class implementing {@see AuthItemRelationInterface}
     * 
     * The concrete implementation of this entity class MUST use this function to create a
     * new instance of the relation object 
     * 
     * @return AuthRelationEntityInterface
     */
    abstract protected function newAuthItemRelation(): AuthRelationEntityInterface;

    /**
     * {@inheritDoc}
     */
    public function getItem(): \yii\rbac\Item
    {
        if (!$this->_item) {
            $attribs = ['type' => $this->getType(),
                'name' => $this->getName(),
                'description' => $this->getDescription(),
                'data' => $this->getData(),
                'ruleName' => $this->getRuleName(),
                'createdAt' => $this->getCreatedAt()->getTimestamp(),
                'updatedAt' => $this->getUpdatedAt()->getTimestamp()];
            switch ($this->getType()) {
                case static::TYPE_PERMISSION: {
                        $this->_item = new \yii\rbac\Permission($attribs); // ===> RETURN
                        break;
                    }
                case static::TYPE_ROLE: {
                        $this->_item = new \yii\rbac\Role($attribs); // ===> RETURN
                        break;
                    }
                default: {
                        $this->_item = new \yii\rbac\Item($attribs); // ===> RETURN
                        break;
                    }
            }
        }
        return $this->_item;
    }
    
    /**
    {@inheritDoc}
    */
    public function setItem(\yii\rbac\Item $item, ?AuthRuleEntityInterface $authRuleEntity = null)
    {
        if ($item->ruleName) {
            if (!isset($authRuleEntity)) {
            throw new InvalidArgumentException("parameter \$item has rule name set to '$item->ruleName', but parameter \$authRuleEntity is null");
            } else {
                if ($item->ruleName !== $authRuleEntity->getName()) {
                    throw new InvalidArgumentException("parameter \$item has rule name set to '$item->ruleName', but entity passed in \$authRuleEntity has the name '".$authRuleEntity->getName()."'");
                }
            }
        }
        $this->setName($item->name);
        $this->setType($item->type);
        $this->setDescription($item->description);
        $this->setData($item->data);
        $this->setCreatedAt($item->createdAt);
        $this->setUpdatedAt($item->updatedAt);
        $this->setRule($authRuleEntity);
    }
    

    /**
     * {@inheritDoc}
     */
    public function getChildAuthItemRelations()
    {
        if (!isset($this->childAuthRelations)) {
            $this->childAuthRelations = new \Doctrine\Common\Collections\ArrayCollection();
        }
        return $this->childAuthRelations;
    }

    /**
     * {@inheritDoc}
     */
    public function getChildAuthItems()
    {
        if ($this->_childAuthItems === null) {
            $this->_childAuthItems = [];
            foreach ($this->getChildAuthItemRelations() as $rel) {
                $this->_childAuthItems[$rel->getChild()->getName()] = $rel->getChild();
            }
        }
        return $this->_childAuthItems;
    }

    /**
     * {@inheritDoc}
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * {@inheritDoc}
     */
    public function getData()
    {
        return isset($this->data) ? unserialize((string) $this->data) : null;
    }

    /**
     * {@inheritDoc}
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * {@inheritDoc}
     */
    public function getParentAuthItemRelations()
    {
        if (!isset($this->parentAuthRelations)) {
            $this->parentAuthRelations = new \Doctrine\Common\Collections\ArrayCollection();
        }
        return $this->parentAuthRelations;
    }

    /**
     * {@inheritDoc}
     */
    public function getParentAuthItems()
    {
        if ($this->_parentAuthItems === null) {
            $this->_parentAuthItems = [];
            foreach ($this->getParentAuthItemRelations() as $rel) {
                $this->_parentAuthItems[$rel->getParent()->getName()] = $rel->getParent();
            }
        }
        return $this->_parentAuthItems;
    }

    /**
     * {@inheritDoc}
     */
    public function getRuleName()
    {
        return isset($this->rule) ? $this->rule->getName() : null;
    }

    /**
     * {@inheritDoc}
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * {@inheritDoc}
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * {@inheritDoc}
     */
    public function setCreatedAt($value)
    {
        if ($value instanceof \DateTime) {
            $this->createdAt = $value;
        } else {
            $value = $value ?? 'now';
            $this->createdAt = new \DateTime(is_numeric($value) ? '@' . $value : $value);
        }
        $this->_item = null;
    }

    /**
     * {@inheritDoc}
     */
    public function setData($value)
    {
        $this->data = isset($value) ? serialize($value) : null;
        $this->_item = null;
    }

    /**
     * {@inheritDoc}
     */
    public function setDescription($value)
    {
        $this->description = $value;
        $this->_item = null;
    }

    /**
     * {@inheritDoc}
     */
    public function setName($value)
    {
        $this->name = $value;
        $this->_item = null;
    }

    /**
     * {@inheritDoc}
     */
    public function setType($value)
    {
        $this->type = $value;
        $this->_item = null;
    }

    /**
     * {@inheritDoc}
     */
    public function setUpdatedAt($value)
    {
        if ($value instanceof \DateTime) {
            $this->updatedAt = $value;
        } else {
            $value = $value ?? 'now';
            $this->updatedAt = new \DateTime(is_numeric($value) ? '@' . $value : $value);
        }
        $this->_item = null;
    }

    /**
     * {@inheritDoc}
     */
    public function getRule(): ?AuthRuleEntityInterface
    {
        return $this->rule;
    }

    /**
     * {@inheritDoc}
     */
    public function setRule(?AuthRuleEntityInterface $value)
    {
        if ($value !== $this->rule) {
            if (isset($this->rule) && $value === null) {
                $this->rule->getUsedByAuthItems()->removeElement($this);
            }
            $this->rule = $value;
            if ($this->rule) {
                if (!$this->rule->getUsedByAuthItems()->contains($this)) {
                    $this->rule->getUsedByAuthItems()->add($this);
                }
            }
        }
        $this->_item = null;
    }

    /**
     * {@inheritDoc}
     */
    public function addChildAuthItem(AuthItemEntityInterface $child)
    {
        $this->_childAuthItems = null;
        $rel = $this->newAuthItemRelation(); /* @var Amylian\Yii\Doctrine\Rbac\Model\AuthItemRelationInterface $rel */
        $rel->setParent($this);
        $rel->setChild($child);
        $this->addChildAuthItemRelation($rel);
        return $rel;
    }

    /**
     * {@inheritDoc}
     */
    public function addParentAuthItem(AuthItemEntityInterface $parent)
    {
        $this->_parentAuthItems = null;
        $rel = $this->newAuthItemRelation(); /* @var Amylian\Yii\Doctrine\Rbac\Model\AuthItemRelationInterface $rel */
        $rel->setParent($parent);
        $rel->setChild($this);
        $this->addParentAuthItemRelation($rel);
        return $rel;
    }

    /**
     * {@inheritDoc}
     */
    public function removeChildAuthItem(AuthItemEntityInterface $child)
    {
        $foundRel = null;
        foreach ($this->getChildAuthItemRelations() as $rel) {
            if ($rel->getChild() === $child) {
                $foundRel = $rel;
                break; // found ===> break foreach
            }
        }
        if ($foundRel) {
            $this->childAuthRelations->removeElement($foundRel);
            $foundRel->setParent(null);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function removeParentAuthItem(AuthItemEntityInterface $parent)
    {
        $foundRel = null;
        foreach ($this->getParentAuthItemRelations() as $rel) {
            if ($rel->getParent() === $parent) {
                $foundRel = $rel;
                break; // found ===> break foreach
            }
        }
        if ($foundRel) {
            $this->parentAuthRelations->removeElement($foundRel);
            $foundRel->setChild(null);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function addChildAuthItemRelation(AuthRelationEntityInterface $relation)
    {
        if (!$this->getChildAuthItemRelations()->contains($relation)) {
            $this->getChildAuthItemRelations()->add($relation);
        }
        $this->_childAuthItems = null;
    }

    /**
     * {@inheritDoc}
     */
    public function addParentAuthItemRelation(AuthRelationEntityInterface $relation)
    {
        if (!$this->getParentAuthItemRelations()->contains($relation)) {
            $this->getParentAuthItemRelations()->add($relation);
        }
        $this->_parentAuthItems = null;
    }

    /**
     * {@inheritDoc}
     */
    public function removeChildAuthItemRelation(AuthRelationEntityInterface $relation)
    {
        $this->childAuthRelations->removeElement($relation);
        if ($relation->getParent()) {
            $relation->setParent(null);
            $relation->setChild(null);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function removeParentAuthItemRelation(AuthRelationEntityInterface $relation)
    {
        $this->childAuthRelations->removeElement($relation);
        if ($relation->getChild()) {
            $relation->setParent(null);
            $relation->setChild(null);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function getAuthAssignments()
    {
        if (!$this->authAssignments) {
            $this->authAssignments = new \Doctrine\Common\Collections\ArrayCollection();
        }
        return $this->authAssignments;
    }

}
