<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Amylian\Yii\Doctrine\Rbac;

use Amylian\Yii\Doctrine\Rbac\Common\InvalidConfigException;
use Amylian\Yii\Doctrine\Rbac\Common\UnknownAuthAssignmentException;
use Amylian\Yii\Doctrine\Rbac\Common\UnknownAuthItemException;
use Amylian\Yii\Doctrine\Rbac\Common\UnknownAuthRuleException;
use Amylian\Yii\Doctrine\Rbac\Model\AuthAssignmentEntityInterface;
use Amylian\Yii\Doctrine\Rbac\Model\AuthItemEntityInterface;
use Amylian\Yii\Doctrine\Rbac\Model\AuthRelationEntityInterface;
use Amylian\Yii\Doctrine\Rbac\Model\AuthRuleEntityInterface;
use Doctrine\DBAL\Logging\EchoSQLLogger;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query;
use Exception;
use yii\base\InvalidArgumentException;
use yii\base\InvalidCallException;
use yii\di\Instance;
use yii\rbac\Assignment;
use yii\rbac\BaseManager;
use yii\rbac\Item;
use yii\rbac\Permission;
use yii\rbac\Role;
use yii\rbac\Rule;

/**
 * AuthManager for Doctrine
 * 
 * This AuthManager requires Doctrine to be available and initialized in the application.
 * 
 * The following steps are necessary to use it:
 * 
 * - Initialize a {@see EntityManager}
 * - Implement Entities for the required Interfaces 
 *   {@see AuthItemEntityInterface} (default implementation {@see AuthItemEntityTrait}), 
 *   {@see AuthRelationEntityInterface} (default implementation (@see AuthRelationEntityTrait)}
 *   {@see AuthRuleEntityInterface} (default implementation (@see AuthRuleEntityTrait)} and
 *   {@see AuthAssignmentEntityInterface}. {@see AuthAssignmentEntityInterface} should not be 
 *   used directly but either by using {@see Model\AuthAssignmentEntityWithIdInterface} which just stores
 *   a ID of the assigned user (default implementation {@see AuthAssignmentEntityWithIdTrait}) or
 *   {@see Model\AuthAssignmentEntityWithRelationInterface} which uses a relation for references
 *   to users (default implementation: {@see AuthAssignmentEntityWithRelationTrait}}
 * - The interfaces need to be mapped to the concrete entity class implementing the interfaces mentioned above.
 *
 * @author Andreas Prucha, Abexto - Helicon Software Development <andreas.prucha@gmail.com>
 */
class Manager extends BaseManager
{

    const EVENT_INVALIDATE_CACHE = 'invalidateCache';

    /**
     * @var EntityManagerInterface|string Instance of entity manger
     * or string Identifying the entity manager 
     */
    public $em = EntityManager::class;

    /**
     * @var bool Specifies if flush() is called on the EntityManager
     * when changes were made. 
     */
    public $autoFlush = true;

    /**
     * @var string|null Class implementing the entity {@see Model\AuthItemInterface}
     */
    public $authItemEntityClass = AuthItemEntityInterface::class;

    /**
     * @var string|null Class implementing the entity {@see Model\AuthRuleInterface} (Auto-detected if not specified)
     */
    public $authRuleEntityClass = AuthRuleEntityInterface::class;

    /**
     * @var string|null Class implementing the entity {@see Model\AuthRuleInterface} (Auto-detected if not specified)
     */
    public $authRelationEntityClass = AuthRelationEntityInterface::class;

    /**
     * @var string|null Class implementing the entity {@see Model\BaseAuthAssignmentEntityInterface} (Auto-detected if not specified)
     */
    public $authAssignmentEntityClass = AuthAssignmentEntityInterface::class;

    /**
     * @var bool Controls wheter Entity objects are pre-fetched.   
     */
    public $enablePreloading = true;

    /**
     * @var bool Uses default Doctrine Cache. If not set it's ignored.
     */
    public $enableCaching = true;

    /**
     * @var int Lifetime of cached query results, 
     */
    public $cacheLifetime = 86400;

    /**
     * @var int Current transaction depth. Increased in {@see beginTransaction()} and decreased in
     *      {@see commitTransaction()} and {@see rollbackTransaction()}
     */
    protected $transactionDepth = 0;

    /**
     * @var bool Indicates if clear is necessary after end of transaction 
     */
    protected $inWritingTransaction = false;

    /**
     * @var bool Indicates that final commit will perform a rollback. This flag is set by a nested
     *      {@see rollbackTransaction()} 
     */
    protected $inRolledBackTransaction = false;

    /**
     * @var bool Indicates whether data has been pre-fetched or fetching is necessary.
     * @see $preFetch
     */
    protected $needPreloading = true;

    /**
     * @var Array UserID => array of AuthAssignmentEntityInterface[] 
     */
    protected $cachedAuthAssignments = [];

    protected function resolveEntityClassOfInterface($configuredClassNameOrInterface, $requiredInterface)
    {
        $managerClass = __CLASS__;
        $resolvedClassName = $this->em->getClassMetadata($requiredInterface)->getName();
        if ($resolvedClassName == $requiredInterface) {
            throw new InvalidConfigException("Entity '$requiredInterface' does not have a valid entity class defined. Use Doctrine\\ORM\\Tools\\ResolveTargetEntityListener to map this interface to an actual entity class which implements this interface");
        }
        if (($configuredClassNameOrInterface != $resolvedClassName) && ($configuredClassNameOrInterface != $requiredInterface)) {
            throw new InvalidConfigException("Class '$className' is configured in '$managerClass', but is interface '$requiredInterface' is mapped to '$resolvedClassName' in \\ORM\\Tools\\ResolveTargetEntityListener");
        }
        if (!is_subclass_of($resolvedClassName, $requiredInterface, true)) {
            throw new InvalidConfigException("Entity Class '$className' does not implement '$requiredInterface'");
        }
        return $resolvedClassName;
    }

    public function init()
    {
        parent::init();

        if (is_string($this->em)) {
            $this->em = Instance::of($this->em)->get();
        }

        $this->authItemEntityClass = $this->resolveEntityClassOfInterface($this->authItemEntityClass, AuthItemEntityInterface::class);
        $this->authRuleEntityClass = $this->resolveEntityClassOfInterface($this->authRuleEntityClass, AuthRuleEntityInterface::class);
        $this->authRelationEntityClass = $this->resolveEntityClassOfInterface($this->authRelationEntityClass, AuthRelationEntityInterface::class);
        $this->authAssignmentEntityClass = $this->resolveEntityClassOfInterface($this->authAssignmentEntityClass, AuthAssignmentEntityInterface::class);
    }

    /**
     * Starts a transaction
     * 
     * If no transaction in context of this class is active, a transaction is started 
     * by calling {@see EntityManager::beginTransaction()}. Otherwise the internal
     * counter is increased
     * 
     * If <code>$writingTransaction</code> is set to <code>true</code> the entity manager
     * is cleared in after commit or rollback
     * 
     * @param type $writingTransaction Indicates if transaction makes updates
     */
    protected function beginTransaction($writingTransaction = true)
    {
        if (!$this->transactionDepth) {
            $this->em->beginTransaction();
            $this->inRolledBackTransaction = false;
            $this->inWritingTransaction = $writingTransaction;
        }
        $this->transactionDepth++;
        if ($writingTransaction) {
            $this->inWritingTransaction = true;
        }
    }

    /**
     * Flushes changes and commits
     * 
     * This method always calls {@see EntityManager::flush()} if in a writing transaction
     * and calls {@see EntityManager::commit()} if not in a nested transaction
     * 
     * NOTE: If a nested transaction was rolled back, a rollback will be done instead of a commit
     * as just commit-everything is allowed
     * 
     */
    protected function commitTransaction()
    {
        if ($this->inWritingTransaction) {
            $this->em->flush();
        }
        if ($this->transactionDepth <= 0) {
            throw new InvalidCallException('No active transaction in context of RBAC-Manager - cannot commit.');
        }
        if ($this->transactionDepth == 1) {
            if (!$this->inRolledBackTransaction) {
                $this->em->commit();
            } else {
                $this->em->rollback();
            }
            $this->transactionDepth = 0;
            if ($this->inWritingTransaction) {
                $this->invalidateCache();
            }
        } else {
            $this->transactionDepth--;
        }
    }

    /**
     * Rolls back the transaction
     * @throws InvalidCallException
     */
    protected function rollbackTransaction()
    {
        if ($this->transactionDepth <= 0) {
            throw new InvalidCallException('No active transaction in context of RBAC-Manager - cannot rollback.');
        }
        if ($this->transactionDepth == 1) {
            try {
                $this->em->rollback();
                if ($this->inWritingTransaction) {
                    $this->invalidateCache();
                }
            } finally {
                $this->transactionDepth = 0;
            }
        } else {
            $this->transactionDepth--;
        }
    }

    protected function rollbackTransactionAndThrow(\Exception $exception)
    {
        $this->rollbackTransaction();
        throw $exception;
    }

    /**
     * Creates a query object
     * 
     * Internally {@see EntityManager::createQuery()} is called and caching is configured
     * 
     * @param type $dql
     */
    protected function createQuery($dql)
    {
        $result = $this->em->createQuery($dql);
        $result->expireResultCache(true);
        /**
          $result->useResultCache($this->enableCaching, $this->cacheLifetime);
          $result->setCacheable($this->cacheLifetime);
          $result->setLifetime($this->cacheLifetime);
          $result->setCacheMode(\Doctrine\ORM\Cache::MODE_NORMAL);
         */
        return $result;
    }

    /**
     * Preloads entites with DQL queries.
     * NOTE: This method does nothing if {@see $enablePreloading} is <code>false</false> or
     * no entities have been already preloaded.
     * @param mixed userId Preload Assignment Entities for given user (If  null, assignments are not preloaded)
     */
    protected function preload($userId = null)
    {
        // Invalidate the internal caching arrays
        if ($this->needPreloading) {
            $this->cachedAuthAssignments = [];
        }
        // Load stuff if necessary 
        if ($this->enablePreloading && $this->needPreloading) {
            $this->beginTransaction(false); // Read Only Transaction!
            try {
                $preloadAuthItemsQuery = $this->createQuery('
                    select authItem, authRules 
                    from 
                        ' . AuthItemEntityInterface::class . ' as authItem  
                    left join 
                        authItem.rule authRules');
                $items = (array) $preloadAuthItemsQuery->getResult();
                $preloadAuthReleations = $this->createQuery(
                        'select partial authItem.{name}, parentAuthRelations, childAuthRelations
                     from 
                        ' . AuthItemEntityInterface::class . ' as authItem 
                     left join
                        authItem.childAuthRelations childAuthRelations
                     left join
                        authItem.parentAuthRelations parentAuthRelations');
                $preloadAuthReleations->getResult();
                // Preload Assignments
                // Unfortunately it seems not be possible to use index by on a relation
                // thus we populate the cache array in a foreach
                if ($userId) {
                    $preloadAuthAssignmentsQuery = $this->createQuery(
                            'select authAssignments, authItems
                     from 
                        ' . AuthAssignmentEntityInterface::class . ' as authAssignments
                     left join
                        authAssignments.authItem authItems
                     where
                        authAssignments.userId = :userId');
                    $preloadAuthAssignmentsQuery->setParameter('userId', $userId);
                    $this->setCachedAuthAssignments($preloadAuthAssignmentsQuery->getResult(), false);
                }
                // Done
                $this->commitTransaction();
                $this->needPreloading = false;
            } catch (Exception $ex) {
                $this->rollbackTransactionAndThrow($ex);
            }
        }
    }

    /**
     * Sets the items in {@see $cacheAssignments}
     * @param AuthAssignmentEntityInterface[] $items Array of Assignments
     * @param bool $clearAll Remove all cached assignments before adding the new ones
     */
    protected function setCachedAuthAssignments(array $items, $clearAll = false)
    {
        if ($clearAll) {
            $this->cachedAuthAssignments = [];
        }
        $resettedUserItems = [];
        foreach ($items as $authAssignment) {
            if (!isset($this->cachedAuthAssignments[$authAssignment->getUserId()])) {
                if (!isset($resettedUserItems[$authAssignment->getUserId()])) {
                    $this->cachedAuthAssignments[$authAssignment->getUserId()] = [];
                    $resettedUserItems[$authAssignment->getUserId()] = true;
                }
            }
            $this->cachedAuthAssignments[$authAssignment->getUserId()][$authAssignment->getAuthItem()->getName()] = $authAssignment;
        }
    }

    /**
     * Default implementation of cache cleaning
     */
    protected function defaultInvalidateCache()
    {
        $cache = $this->em->getCache();
        if ($cache) {
            $cache->evictCollectionRegions();
            $cache->evictEntityRegions();
            $cache->evictQueryRegions();
            $cache->getQueryCache()->clear();
        }
        if ($this->em->getConfiguration()->getResultCacheImpl() instanceof \Doctrine\Common\Cache\ClearableCache) {
            $this->em->getConfiguration()->getResultCacheImpl()->deleteAll();
        }
    }

    /**
     * Invalidates cache
     * 
     * This method performs the following steps: 
     * 1. unmanages entities by calling {@see EntityManager::clear()}
     * 2. {@see static::EVENT_INVALIDATE_CACHE} for custom cache cleaning.
     * 3. calls {@see defaultInvalidateCache()} if no event handler is attached or {@see Event::$handled} is not
     *    set to true
     */
    public function invalidateCache()
    {

        $this->needPreloading = true;
        $this->setCachedAuthAssignments([], true);
        $event = new \yii\base\Event();
        $this->trigger(static::EVENT_INVALIDATE_CACHE, $event);
        if (!$event->handled) {
            $this->defaultInvalidateCache();
        }
    }

    /**
     * Returns the Repository for authRuleEntityClass objects
     * @return \Doctrine\ORM\Repository
     */
    protected function getAuthRuleEntityRepository(): EntityRepository
    {
        return $this->em->getRepository($this->authRuleEntityClass);
    }

    /**
     * Finds and AuthItemEntity by name
     * @param string $name
     * @param bool $load Loads the entity if <code>true</code> - otherwise a reference is created
     * @param bool $throwException If set to <code>true</code> an exception is thrown if Rule is not found.
     *  Otherwise the function returns null. 
     * @return AuthRuleEntityInterface|null
     * @throws \Exception
     */
    protected function findAuthRuleEntity($name, $load = true, $throwException = true): ?AuthRuleEntityInterface
    {
        if ($load) {
            $result = $this->getAuthRuleEntityRepository()->find($name);
        } else {
            $result = $this->em->getReference($this->authRuleEntityClass, $name);
        }
        if (!$result && $throwException) {
            throw new UnknownAuthRuleException("Rule '$name' not found");
        }
        return $result;
    }

    /**
     * Returns the {@see AuthRuleEntityInterface} object associated with the given {@see Item}
     * 
     * @param Item $item
     * @return AuthRuleEntityInterface|null null if <code>$item->ruleName</code> is null,
     *         or the instance of the rule entity.
     * 
     */
    protected function findAuthRuleEntityOfItem(Item $item): ?AuthRuleEntityInterface
    {
        return isset($item->ruleName) ? $this->findAuthRuleEntity($item->ruleName) : null;
    }

    /**
     * Returns the Repository for {@see Model\AuthAssignmentEntityInterface}
     * @return \Doctrine\ORM\Repository
     */
    protected function getAuthAssignmentEntityRepository(): EntityRepository
    {
        return $this->em->getRepository($this->authAssignmentEntityClass);
    }

    /**
     * Finds the assignment entity
     * @param string|item $item
     * @param mixed $userId
     */
    protected function findAuthAssignmentEntity($item, $userId, $throwException = true): AuthAssignmentEntityInterface
    {
        $itemName = ($item instanceof Item) ? $item->name : $item;
        if (isset($this->cachedAuthAssignments[$userId]) && isset($this->cachedAuthAssignments[$userId][$itemName])) {
            $this->cachedAuthAssignments[$userId][$itemName];
        } else {
            $result = $this->getAuthAssignmentEntityRepository()->find(['userId' => $userId, 'authItem' => $itemName]);
        }
        if ($result === null && $throwException) {
            throw new UnknownAuthAssignmentException("AuthAssignment '$itemName' not found");
        }
        return $result;
    }

    /**
     * Returns all {@see Model\AuthAssignmentEntityInterface} Entities of a user
     * @param mixed $userId
     * @return AuthAssignmentEntityInterface[]|AuthAssignmentEntityInterface 
     */
    protected function findAuthAssignmentEntities($userId): array
    {
        if (isset($this->cachedAuthAssignments[$userId])) {
            $result = $this->cachedAuthAssignments[$userId];
        } else {
            $result = $this->getAuthAssignmentEntityRepository()->findBy(['userId' => $userId]);
            $this->setCachedAuthAssignments($result);
        }
        return $result;
    }

    /**
     * Returns the Repository for {@see AuthItemRelationInterface}
     * @return EntityRepository
     */
    protected function getAuthRelationEntityRepoistory(): EntityRepository
    {
        return $this->em->getRepository($this->authRelationEntityClass);
    }

    /**
     * Creates a new {@see AuthRelationEntityInterface} object
     * @param AuthItemEntityInterface|null $parent
     * @param AuthItemEntityInterface|null $child
     * @return AuthRelationEntityInterface
     */
    protected function newAuthRelationEntity(?AuthItemEntityInterface $parent, ?AuthItemEntityInterface $child): AuthRelationEntityInterface
    {
        $result = new $this->authRelationEntityClass();
        if ($parent) {
            $result->setParent($parent);
        }
        if ($child) {
            $result->setChild($child);
        }
        return $result;
    }

    /**
     * Returns a new AuthRuleEntity
     * @return AuthRuleEntityInterface
     */
    protected function newAuthRuleEntity(?Rule $rule): AuthRuleEntityInterface
    {
        $result = new $this->authRuleEntityClass ();
        if ($rule) {
            $result->setRule($rule);
        }
        return $result;
    }

    /**
     * Returns the Repository for {@see $authItemEntityClass}
     * @return EntityRepository
     */
    protected function getAuthItemEntityRepository(): EntityRepository
    {
        return $this->em->getRepository($this->authItemEntityClass);
    }

    /**
     * Finds and AuthItemEntity by name
     * @param string|Item $item
     * @param bool $load Loads the entity if <code>true</code> - otherwise a reference is created
     * @return AuthItemEntityInterface
     */
    protected function findAuthItemEntity($item, $throwException = true): ?AuthItemEntityInterface
    {
        $itemName = $item instanceof Item ? $item->name : $item;
        $result = $this->getAuthItemEntityRepository()->find($itemName);
        if ($result === null && $throwException) {
            throw new UnknownAuthItemException("AuthItem '$itemName' not found");
        }
        return $result;
    }

    /**
     * Removes the AuthItem
     * 
     * Removes the AuthItem and takes care of relations
     * 
     * 
     * 
     * @param AuthItemEntityInterface $item
     */
    protected function removeAuthItemEntity($item)
    {
        if ($item instanceof AuthItemEntityInterface) {
            $aie = $item;
        } else {
            $aie = $this->findAuthItemEntity($item);
        }
        if ($aie) {
            $this->beginTransaction(true);
            try {
                foreach ($aie->getParentAuthItemRelations() as $aire) {
                    $this->em->remove($aire);
                }
                foreach ($aie->getChildAuthItemRelations() as $aire) {
                    $this->em->remove($aire);
                }
                $this->em->remove($aie);
                $this->commitTransaction();
            } catch (\Exception $exc) {
                $this->rollbackTransactionAndThrow($exc);
            }
        }
    }

    /**
     * Creates a new AuthItem Entity
     * @param Item $item Assign data from Item
     * @return AuthItemEntityInterface
     */
    protected function newAuthItemEntity(?Item $item): AuthItemEntityInterface
    {
        $result = new $this->authItemEntityClass ();
        if ($item) {
            $result->setItem($item, $this->findAuthRuleEntityOfItem($item));
        }
        return $result;
    }

    /**
     * Creates a new AuthAssignment Entity
     * @return AuthItemEntityInterface
     */
    protected function newAuthAssignmentEntity(): AuthAssignmentEntityInterface
    {
        return new $this->authAssignmentEntityClass ();
    }

    /**
     * Used to remove all relations between items
     */
    protected function removeAllItemRelations()
    {
        try {
            $this->beginTransaction();
            $itemRelations = $this->getAuthRelationEntityRepoistory()->findAll();
            foreach ($itemRelations as $itemRelation) {
                /* @var $itemRelation AuthRelationEntityInterface */
                $this->em->remove($itemRelation);
            }
            $this->commitTransaction();
        } catch (Exception $exc) {
            $this->rollbackTransactionAndThrow($exc);
        }
    }

    /**
     * Removes all Items
     * @param int $type Type of Item to be removed 
     *                  ({@see AuthItemEntityInterface::TYPE_PERMISSION} or {@see AuthItemEntityInterface::TYPE_ROLE}).
     *                  If <code>null</code>, all items will be removed.
     */
    protected function removeAllAuthItems($type)
    {
        try {
            $this->beginTransaction();
            if ($type) {
                $authItems = $this->em->getRepository($this->authItemEntityClass)->findByType($type);
            } else {
                $authItems = $this->em->getRepository($this->authItemEntityClass)->findAll();
            }
            foreach ($authItems as $authItem) {
                if ($this->em->contains($authItem)) {
                    $this->em->remove($authItem);
                }
            }
            $this->commitTransaction();
        } catch (Exception $ex) {
            $this->rollbackTransactionAndThrow($ex);
        }
    }

    /**
     * {@inheritDoc}
     * @param Item $item
     */
    protected function addItem($item)
    {
        $this->beginTransaction(true);
        try {
            $newItemEntity = $this->newAuthItemEntity($item);
            $this->em->persist($newItemEntity);
            $this->commitTransaction();
        } catch (\Exception $exc) {
            $this->rollbackTransaction();
            return false;
        }
        return true;
    }

    /**
     * {@inheritDoc}
     */
    protected function addRule($rule)
    {
        $this->beginTransaction(true);
        try {
            $newAuthRule = $this->newAuthRuleEntity($rule);
            $this->em->persist($newAuthRule);
            $this->commitTransaction();
        } catch (Exception $ex) {
            $this->rollbackTransactionAndThrow($ex);
        }
        return true;
    }

    /**
     * {@inheritDoc}
     * @param type $name
     * @return Item
     */
    protected function getItem($name): ?Item
    {
        $foundItem = $this->findAuthItemEntity($name, false);
        return $foundItem ? $foundItem->getItem() : null;
    }

    /**
     * {@inheritDoc}
     * @param int $type
     * @return Item[]
     */
    protected function getItems($type)
    {
        $result = [];
        foreach ($this->em->getRepository(AuthItemEntityInterface::class)->findByType($type) as $authItemEntity) {
            $result[] = $authItemEntity->getItem();
        }
        return $result;
    }

    /**
     * {@inheritDoc}
     */
    protected function removeItem($item): bool
    {
        $aie = $this->findAuthItemEntity($item);
        if ($aie) {
            $this->beginTransaction(true);
            try {
                $this->removeAuthItemEntity($item);
                $this->commitTransaction();
            } catch (Exception $exc) {
                $this->rollbackTransactionAndThrow($exc);
            }
        }
        return true;
    }

    /**
     * {@inheritDoc}
     */
    protected function removeRule($rule): bool
    {
        if ($rule instanceof Rule) {
            $ruleEntity = $this->findAuthRuleEntity($rule->name);
        } else {
            $ruleEntity = $rule;
        }
        if (isset($ruleEntity)) {
            $this->beginTransaction(true);
            try {
                foreach ($ruleEntity->getUsedByAuthItems() as $aie) {
                    $aie->setRule(null);
                    $this->em->persist($aie);
                }
                $this->em->remove($ruleEntity);
                $this->commitTransaction();
            } catch (Exception $ex) {
                $this->rollbackTransactionAndThrow($ex);
            }
        }
        return true;
    }

    /**
     * {@inheritDoc}
     */
    protected function updateItem($name, $item): bool
    {
        $authItemEntity = $this->findAuthItemEntity($name);
        $this->em->getConfiguration()->setSQLLogger(new EchoSQLLogger());
        $this->beginTransaction(true);
        try {
            if ($authItemEntity->getName() !== $item->name) {
                // Special handling for renaming:
                // 1. Create new Item
                // 2. Update all assignments of old to new
                // 3. Remove old one
                // 4. Re-Attach saved Children, Parents and Assignments
                $newAuthItem = $this->newAuthItemEntity($item);
                foreach ($authItemEntity->getAuthAssignments() as $authAssignment) {
                    $authAssignment->setAuthItem($newAuthItem);
                    $this->em->persist($authAssignment);
                }
                foreach ($authItemEntity->getChildAuthItemRelations() as $authRelation) {
                    $newAuthItem->addChildAuthItem($authRelation->getChild());
                    $this->em->remove($authRelation);
                }
                foreach ($authItemEntity->getParentAuthItemRelations() as $authRelation) {
                    $newAuthItem->addParentAuthItem($authRelation->getParent());
                    $this->em->remove($authRelation);
                }
                $this->em->remove($authItemEntity);
                $this->em->flush();
                $this->em->persist($newAuthItem);
                $this->em->flush();
                $this->em->remove($authItemEntity);
            } else {
                $itemEntity->setItem($item, $this->findAuthRuleEntityOfItem($item));
                $this->em->persist($itemEntity);
            }
            $this->commitTransaction();
        } catch (Exception $exc) {
            $this->rollbackTransactionAndThrow($exc);
        }
        return true;
    }

    
    /**
     * {@inheritDoc}
     */
    protected function updateRule($name, $rule): bool
    {
        $this->beginTransaction();
        try {
            $ruleEntity = $this->findAuthRuleEntity($name);
            if ($ruleEntity->getName() !== $rule->name) {
                // Special handling for renaming:
                // 1. Create new rule
                // 2. Attach assignments to new rule
                // 3. Remove old rule
                $newRuleEntity = $this->newAuthRuleEntity($rule);
                foreach ($ruleEntity->getUsedByAuthItems() as $authItem) {
                    $authItem->setRule($newRuleEntity);
                }
                $this->em->persist($newRuleEntity);
                $this->em->flush();
                $this->em->remove($ruleEntity);
            } else {
                // Name has not changed - we can savely just assign and persist
                $ruleEntity->setRule($rule);
                $this->em->persist($ruleEntity);
            }
            $this->commitTransaction();
            return true; // ===> RETURN true
        } catch (\Exception $exc) {
            $this->rollbackTransaction();
            return false;
        }
        return true;
    }

    public function addChild($parent, $child): bool
    {
        $result = false;
        $parentItemEntity = $this->findAuthItemEntity($parent->name);
        $childItemEntity = $this->findAuthItemEntity($child->name);
        if ($parentItemEntity && $childItemEntity) {
            $this->beginTransaction();
            try {
                $this->em->persist($this->newAuthRelationEntity($parentItemEntity, $childItemEntity));
                $this->commitTransaction();
            } catch (Exception $exc) {
                $this->rollbackTransactionAndThrow($exc);
            }
        }
        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function assign($role, $userId): Assignment
    {
        $roleEntity = $this->findAuthItemEntity($role->name);
        if ($roleEntity) {
            $assignmentEntity = $this->newAuthAssignmentEntity();
            $assignmentEntity->setUser($userId);
            $assignmentEntity->setAuthItem($roleEntity);
            try {
                $this->beginTransaction();
                $this->em->persist($assignmentEntity);
                $this->commitTransaction();
                return $assignmentEntity->getAssignment();
            } catch (Exception $ex) {
                $this->rollbackTransactionAndThrow($ex);
            }
        } else {
            return false;
        }
    }

    /**
     * Checks whether there is a loop in the authorization item hierarchy.
     * @param Item $parent the parent item
     * @param Item $child the child item to be added to the hierarchy
     * @return bool whether a loop exists
     */
    protected function detectLoop($parent, $child)
    {
        if ($child->name === $parent->name) {
            return true;
        }
        foreach ($this->getChildren($child->name) as $grandchild) {
            if ($this->detectLoop($parent, $grandchild)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Checks if the child can be added to the parent.
     * 
     * {@see canAddChild()} for details. 
     * 
     * NOTE if parameter $throwException exception is set to true, this function does not
     * return true or false, but raises an exception if it's not possible to add the child
     * 
     * @param Item $parent
     * @param Item $child
     * @param bool $throwException Throw exception if child cannot be added
     * @throws InvalidArgumentException
     * @throws InvalidCallException
     */
    protected function isValidRelationship(Item $parent, Item $child, $throwException)
    {
        // Check if Child and Parent are the same
        if ($parent->name === $child->name) {
            if ($throwException) {
                throw new InvalidArgumentException("Cannot add '{$parent->name}' as a child of itself.");
            } else {
                return false; // ===> RETURN false;
            }
        }
        // Check if it's an attempt to assign a Role to a Permission
        if ($parent instanceof Permission && $child instanceof Role) {
            if ($throwException) {
                throw new InvalidArgumentException('Cannot add a role as a child of a permission.');
            } else {
                return false; // ===> RETURN false;
            }
        }
        // check if parent is a child of child
        $children = $this->getChildAuthItemEntitiesRecursive($this->findAuthItemEntity($parent), true, 0);
        if ($this->detectLoop($parent, $child)) {
            if ($throwException) {
                throw new InvalidCallException("Cannot add '{$child->name}' as a child of '{$parent->name}'. A loop has been detected.");
            } else {
                return false; // ===> RETURN false;
            }
        }
        // everything seems to be OK
        return true;
    }

    /**
     * {@inheritDoc}
     * <code>false</false> is returned if
     * - The child is the parent
     * - The parent is a {@see \yii\rbac\Permission}, but child is a {@see \yii\rbac\Role}
     * - The assignment would lead to a loop assignment
     */
    public function canAddChild($parent, $child): bool
    {
        return $this->isValidRelationship($parent, $child, false);
    }

    /**
     * Checks if the user has access to the requested item
     * 
     * @param type $userId
     * @param array $userAssignments
     * @param AuthItemEntityInterface $itemEntity
     * @param type $params
     * @return boolean
     */
    protected function internalCheckAccessRecursiveWithEntities($userId,
            array $userAssignments,
            AuthItemEntityInterface $itemEntity,
            $params)
    {
        // Check the rule of the item
        if (!$this->executeRule($userId, $itemEntity->getItem(), $params)) {
            return false;
        }
        // Check if this rule belongs to the user 
        if (isset($userAssignments[$itemEntity->getName()]) || in_array($itemEntity->getName(), $this->defaultRoles)) {
            return true;
        }
        // Check parents
        $parents = $itemEntity->getParentAuthItems();
        if (!empty($parents)) {
            foreach ($parents as $parentAuthItem) {
                if ($this->internalCheckAccessRecursiveWithEntities($userId, $userAssignments, $parentAuthItem, $params)) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * {@inheritDoc}
     */
    public function checkAccess($userId, $permissionName, $params = array())
    {
        $result = false;
        $this->preload($userId);
        $itemEntity = $this->findAuthItemEntity($permissionName, false);
        if ($itemEntity) {
            $userAssignments = $this->getAssignments($userId);
            $result = $this->internalCheckAccessRecursiveWithEntities($userId, $userAssignments, $itemEntity, $params);
        }
        return $result;
    }

    /**
     * {@inheritDoc}
     */
    public function getAssignment($roleName, $userId)
    {
        $authAssignmentEntity = $this->em->getRepository($this->authAssignmentEntityClass)->findOneBy(
                ['userId' => $userId, 'authItem' => $roleName]);
        if ($authAssignmentEntity instanceof AuthAssignmentEntityInterface) {
            return $authAssignmentEntity->getAssignment(); // ===> RETURN Assignment
        } else {
            return null; // Not Found ===> RETURN null;
        }
    }

    /**
     * {@inheritDoc}
     */
    public function getAssignments($userId)
    {
        $result = [];
        $assignmentEntities = $this->findAuthAssignmentEntities($userId);
        foreach ($assignmentEntities as $aa) {
            /* @var $aa AuthAssignmentEntityInterface */
            $a = $aa->getAssignment();
            $result[$a->roleName] = $a;
        }
        return $result;
    }

    /**
     * Returns all direct and indirect AuthItem-children 
     * 
     * @param AuthItemEntityInterface $rootAuthItem Root item to start with
     * @param bool $includeRoot specifies wether to include the item itself
     * @param int $onlyOfType If 0, all item type are added to the result list, otherwise just those matching
     * @result AuthItemEntityInterface[]|AuthItemEntityInterface|array 
     *         Array of children indexed by name
     */
    private function getChildAuthItemEntitiesRecursive(AuthItemEntityInterface $rootAuthItem, bool $includeRoot = false, $onlyOfType = 0)
    {
        $result = [];
        if ($includeRoot) {
            if ($onlyOfType && $rootAuthItem->getType() == $onlyOfType) {
                $result[$rootAuthItem->getName()] = $rootAuthItem;
            }
        }
        foreach ($rootAuthItem->getChildAuthItems() as $childAuthItem) {
            $result = array_merge($result, $this->getChildAuthItemEntitiesRecursive($childAuthItem, true, $onlyOfType));
        }
        return $result;
    }

    /**
     * Returns all direct and indirect Item-children
     * 
     * @param AuthItemEntityInterface $rootAuthItem Root item to start with
     * @param bool $includeRoot specifies wether to include the item itself
     * @param int $onlyOfType If 0, all item type are added to the result list, otherwise just those matching
     * @result Item[]|AuthItemEntityInterface|array 
     *         Array of children indexed by name
     */
    private function getChildItemsRecursive(AuthItemEntityInterface $rootAuthItem, bool $includeRoot = false, $onlyOfType = 0)
    {
        $result = [];
        if ($includeRoot) {
            if ($onlyOfType && $rootAuthItem->getType() == $onlyOfType) {
                $item = $rootAuthItem->getItem();
                $result[$item->name] = $item;
            }
        }
        foreach ($rootAuthItem->getChildAuthItems() as $childAuthItem) {
            $result = array_merge($result, $this->getChildItemsRecursive($childAuthItem, true, $onlyOfType));
        }
        return $result;
    }

    /**
     * {@inheritDoc}
     */
    public function getChildRoles($roleName)
    {
        return $this->getChildItemsRecursive($this->findAuthItemEntity($roleName), true, AuthItemEntityInterface::TYPE_ROLE);
    }

    /**
     * {@inheritDoc}
     */
    public function getChildren($name)
    {
        $result = [];
        $item = $this->findAuthItemEntity($name);
        if ($item) {
            foreach ($item->getChildAuthItems() as $childAuthItemEntity) {
                $ci = $childAuthItemEntity->getItem();
                $result[$ci->name] = $ci;
            }
        }
        return $result;
    }

    /**
     * {@inheritDoc}
     */
    public function getPermissionsByRole($roleName)
    {
        $rootAuthItem = $this->findAuthItemEntity($roleName);
        return $this->getChildItemsRecursive($rootAuthItem, true, AuthItemEntityInterface::TYPE_PERMISSION);
    }

    /**
     * {@inheritDoc}
     */
    public function getPermissionsByUser($userId)
    {
        $result = [];
        $authItems = [];
        $authAssignments = $this->findAuthAssignmentEntities($userId);
        foreach ($authAssignments as $authAssignment) {
            $result = array_merge($result, $this->getChildItemsRecursive($authAssignment->getAuthItem(), true, AuthItemEntityInterface::TYPE_PERMISSION));
        }
        return $result;
    }

    /**
     * {@inheritDoc}
     */
    public function getRolesByUser($userId)
    {
        $result = $this->getDefaultRoleInstances();
        $authAssignments = $this->findAuthAssignmentEntities($userId);
        foreach ($authAssignments as $authAssignment) {
            $result = array_merge($result, $this->getChildItemsRecursive($authAssignment->getAuthItem(), true, AuthItemEntityInterface::TYPE_ROLE));
        }
        return $result;
    }

    /**
     * {@inheritDoc}
     */
    public function getRule($name)
    {
        $ruleEntity = $this->findAuthRuleEntity($name, true, false);
        return isset($ruleEntity) ? $ruleEntity->getRule() : null;
    }

    /**
     * {@inheritDoc}
     */
    public function getRules()
    {
        $result = [];
        foreach ($this->getAuthRuleEntityRepository()->findAll() as $are) {
            $result[] = $are->getRule();
        }
        return $result;
    }

    /**
     * {@inheritDoc}
     */
    public function getUserIdsByRole($roleName): array
    {
        $result = [];
        $authItem = $this->findAuthItemEntity($roleName, false);
        if ($authItem) {
            foreach ($authItem->getAuthAssignments() as $aae) {
                $result[] = $aae->getUserId();
            }
        }
        return $result;
    }

    /**
     * {@inheritDoc}
     */
    public function hasChild($parent, $child): bool
    {
        $parentName = ($parent instanceof Item) ? $parent->name : $parent;
        $childName = ($child instanceof Item) ? $child->name : $child;
        return $this->getAuthRelationEntityRepoistory()->find(['parent' => $parentName, 'child' => $childName]) !== null;
    }

    /**
     * {@inheritDoc}
     * @throws \Exception
     */
    public function removeAll()
    {
        try {
            $this->beginTransaction();
            $this->removeAllAssignments();
            $this->removeAllItemRelations();
            $this->removeAllAuthItems(null); // Must be before Rules!
            $this->removeAllRules();
            $this->commitTransaction();
        } catch (Exception $ex) {
            $this->rollbackTransactionAndThrow($ex);
        }
    }

    /**
     * {@inheritDoc}
     * @throws \Exception
     */
    public function removeAllAssignments()
    {
        try {
            $this->beginTransaction();
            $authAssignments = $this->getAuthAssignmentEntityRepository()->findAll();
            foreach ($authAssignments as $authAssignment) {
                $this->em->remove($authAssignment);
            }
            $this->commitTransaction();
        } catch (Exception $ex) {
            $this->rollbackTransactionAndThrow($ex);
        }
        /*
          $this->beginTransaction();
          try {
          $removeAllAsignmentsDqlQuery = $this->createQuery('delete ' . AuthAssignmentEntityInterface::class . ' authAssignments');
          $removeAllAsignmentsDqlQuery->setHint(Query::HINT_CACHE_EVICT, true)->execute();
          $this->commitTransaction();
          } catch (Exception $ex) {
          $this->rollbackTransactionAndThrow($ex);
          }
         */
    }

    public function removeAllPermissions()
    {
        $this->removeAllAuthItems(AuthItemEntityInterface::TYPE_PERMISSION);
    }

    public function removeAllRoles()
    {
        $this->removeAllAuthItems(AuthItemEntityInterface::TYPE_ROLE);
    }

    /**
     * {@inheritDoc}
     */
    public function removeAllRules()
    {
        try {
            $this->beginTransaction();
            $authRules = $this->em->getRepository($this->authRuleEntityClass)->findAll();
            foreach ($authRules as $authRule) {
                /* @var $authRule AuthRuleEntityInterface */
                foreach ($authRule->getUsedByAuthItems() as $authItemEntity) {
                    $authItemEntity->setRule(null);
                    $this->em->persist($authItemEntity);
                }
                $this->em->remove($authRule);
            }
            $this->commitTransaction();
        } catch (Exception $ex) {
            $this->rollbackTransactionAndThrow($ex);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function removeChild($parent, $child): bool
    {
        try {
            $this->beginTransaction();
            $parentEntity = $this->findAuthItemEntity($parent);
            $childEntity = $this->findAuthItemEntity($child);
            if ($parentEntity) {
                $parentEntity->removeChildAuthItem($childEntity);
                $this->em->persist($parentEntity);
                $this->em->persist($childEntity);
            }
            $this->commitTransaction();
        } catch (Exception $ex) {
            $this->rollbackTransactionAndThrow($ex);
            return false;
        }
        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function removeChildren($parent): bool
    {
        try {
            $parentEntity = $this->findAuthItemEntity($parent);
            if ($parentEntity) {
                foreach ($parentEntity->getChildAuthItems() as $childEntity) {
                    $parentEntity->removeChildAuthItem($childEntity);
                    $this->em->persist($parentEntity);
                    $this->em->persist($childEntity);
                }
            }
        } catch (Exception $ex) {
            $this->rollbackTransaction();
            return false;
        }
        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function revoke($role, $userId): bool
    {
        $this->beginTransaction();
        try {
            $aae = $this->findAuthAssignmentEntity($role, $userId);
            if ($aae) {
                $aae->setAuthItem(null);
                $this->em->remove($aae);
            }
            $this->commitTransaction();
        } catch (Exception $ex) {
            $this->rollbackTransaction();
            return false; // Some exception ===> RETURN false;
        }
        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function revokeAll($userId): bool
    {
        $this->beginTransaction();
        try {
            $assignmentEntities = $this->findAuthAssignmentEntities($userId);
            foreach ($assignmentEntities as $aa) {
                /* @var $aa AuthAssignmentEntityInterface */
                $this->em->remove($aa);
            }
            $this->commitTransaction();
        } catch (Exception $ex) {
            $this->rollbackTransaction();
            return false; // Exception ===> RETURN false;
        }
        return true;
    }

}
