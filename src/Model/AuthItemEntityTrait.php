<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
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
     * @var \Doctrine\Common\Collections\ArrayCollection|AuthItemRelationInterface[]
     * @OneToMany(targetEntity="Amylian\Yii\Doctrine\Rbac\Model\AuthItemRelationInterface", 
     *            mappedBy="parent", 
     *            cascade={"all"}, 
     *            orphanRemoval=true)
     */
    protected $childAuthItemRelations;

    /**
     * @var \Doctrine\Common\Collections\ArrayCollection|AuthItemRelationInterface[]
     * @OneToMany(targetEntity="Amylian\Yii\Doctrine\Rbac\Model\AuthItemRelationInterface", 
     *            mappedBy="child", 
     *            cascade={"all"}, 
     *            orphanRemoval=true)
     */
    protected $parentAuthItemRelations;

    /**
     * @var string 
     * @Column(type="string", length=1024, unique=false, nullable=true)
     */
    protected $description = null;

    /**
     * @var mixed 
     * @Column(type="blob", unique=false, nullable=true)
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
     * @var AuthRuleInterface
     * @ManyToOne(targetEntity="Amylian\Yii\Doctrine\Rbac\Model\AuthRuleInterface", cascade={"persist"}, 
     *           fetch="EAGER")
     * @JoinColumn(referencedColumnName="name")
     */
    protected $rule = null;

    /**
     * @var string Invalid In this context 
     */
    private $ruleName = null;

    /**
     * @var AuthItemEntityInterface[]|null
     */
    private $_childAuthItems = null;

    /**
     * @var AuthItemEntityInterface[]|null
     */
    private $_parentAuthItems = null;

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
    public function handleOnPersistAndUpdateEvent(\Doctrine\ORM\Event\LifecycleEventArgs $args)
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
     * @return AuthItemRelationInterface
     */
    abstract protected function newAuthItemRelation(): AuthItemRelationInterface;

    /**
     * Returns the RBAC Item (@see \yii\rbac\Item) reflected by this entity object
     * @return \yii\rbac\Role|\yii\rbac\Permission|\yii\rbac\Item
     */
    public function getItem(): \yii\rbac\Item
    {
        $attribs = ['type' => $this->getType(),
            'name' => $this->getName(),
            'description' => $this->getDescription(),
            'data' => $this->getData(),
            'ruleName' => $this->getRuleName(),
            'createdAt' => $this->getCreatedAt()->getTimestamp(),
            'updatedAt' => $this->getUpdatedAt()->getTimestamp()];
        switch ($this->getType()) {
            case static::TYPE_PERMISSION: {
                    return new \yii\rbac\Permission($attribs); // ===> RETURN
                }
            case static::TYPE_ROLE: {
                    return new \yii\rbac\Role($attribs); // ===> RETURN
                }
            default: {
                    return new \yii\rbac\Item($attribs); // ===> RETURN
                }
        }
    }

    public function getChildAuthItemRelations()
    {
        if (!isset($this->childAuthItemRelations)) {
            $this->childAuthItemRelations = new \Doctrine\Common\Collections\ArrayCollection();
        }
        return $this->childAuthItemRelations;
    }

    public function getChildAuthItems()
    {
        if ($this->_childAuthItems === null) {
            $this->_childAuthItems = [];
            foreach ($this->getChildAuthItemRelations() as $rel) {
                $this->_childAuthItems[] = $rel->getChild();
            }
        }
        return $this->_childAuthItems;
    }

    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    public function getData()
    {
        return isset($this->data) ? unserialize($this->data) : null;
    }

    public function getDescription()
    {
        return $this->description;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getParentAuthItemRelations()
    {
        if (!isset($this->parentAuthItemRelations)) {
            $this->parentAuthItemRelations = new \Doctrine\Common\Collections\ArrayCollection();
        }
        return $this->parentAuthItemRelations;
    }

    public function getParentAuthItems()
    {
        if ($this->_parentAuthItems === null) {
            $this->_parentAuthItems = [];
            foreach ($this->getParentAuthItemRelations() as $rel) {
                $this->_parentAuthItems[] = $rel->getParent();
            }
        }
        return $this->_parentAuthItems;
    }

    public function getRuleName()
    {
        return $this->getRule() ? $this->getRule->getName() : null;
    }

    public function getType()
    {
        return $this->type;
    }

    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    public function setCreatedAt($value)
    {
        $this->createdAt = $value instanceof \DateTime ? $value : new \DateTime($value);
    }

    public function setData($value)
    {
        $this->data = isset($value) ? serialize($value) : null;
    }

    public function setDescription($value)
    {
        $this->description = $value;
    }

    public function setName($value)
    {
        $this->name = $value;
    }

    public function setType($value)
    {
        $this->type = $value;
    }

    public function setUpdatedAt($value)
    {
        $this->updatedAt = $value instanceof \DateTime ? $value : new \DateTime($value);
    }

    public function getRule(): ?AuthRuleInterface
    {
        return $this->rule;
    }

    public function setRule(?AuthRuleInterface $value)
    {
        if ($value !== $this->rule) {
            $this->rule = $value;
        }
    }

    public function addChildAuthItem(AuthItemEntityInterface $child)
    {
        $this->_childAuthItems = null;
        $rel = $this->newAuthItemRelation(); /* @var Amylian\Yii\Doctrine\Rbac\Model\AuthItemRelationInterface $rel */
        $rel->setParent($this);
        $rel->setChild($child);
        $this->addChildAuthItemRelation($rel);
    }

    public function addParentAuthItem(AuthItemEntityInterface $parent)
    {
        $this->_parentAuthItems = null;
        $rel = $this->newAuthItemRelation(); /* @var Amylian\Yii\Doctrine\Rbac\Model\AuthItemRelationInterface $rel */
        $rel->setParent($parent);
        $rel->setChild($this);
        $this->addParentAuthItemRelation($rel);
    }

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
            $this->childAuthItemRelations->removeElement($foundRel);
        }
    }

    public function removeParentAuthItem(AuthItemEntityInterface $parent)
    {
        
    }

    public function addChildAuthItemRelation(AuthItemRelationInterface $relation)
    {
        if (!$this->getChildAuthItemRelations()->contains($relation)) {
            $this->getChildAuthItemRelations()->add($relation);
        }
        $this->_childAuthItems = null;
    }

    public function addParentAuthItemRelation(AuthItemRelationInterface $relation)
    {
        if (!$this->getParentAuthItemRelations()->contains($relation)) {
            $this->getParentAuthItemRelations()->add($relation);
        }
        $this->_parentAuthItems = null;
    }

    public function removeChildAuthItemRelation(AuthItemRelationInterface $relation)
    {
        $this->childAuthItemRelations->removeElement($relation);
        if ($relation->getParent()) {
            $relation->setParent(null);
            $relation->setChild(null);
        }
    }

    public function removeParentAuthItemRelation(AuthItemRelationInterface $relation)
    {
        $this->childAuthItemRelations->removeElement($relation);
        if ($relation->getChild()) {
            $relation->setParent(null);
            $relation->setChild(null);
        }
    }

}
