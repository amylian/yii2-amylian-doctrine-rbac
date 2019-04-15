<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Amylian\Tests\Yii\Doctrine\Rbac\Unit;

use \Yii;

/**
 * Description of ManagerTest
 *
 * @author Andreas Prucha, Abexto - Helicon Software Development <andreas.prucha@gmail.com>
 */
class ManagerTest extends \yiiunit\framework\rbac\ManagerTestCase
{

    /**
     * @var \Amylian\Yii\Doctrine\Rbac\Manager 
     */
    protected $auth = null;

    protected function mockApplication($config = array(), $appClass = '\yii\console\Application')
    {
        $defaultConfig = [
            'basePath' => __DIR__ . '/../',
            'components' => [
                'authManager' => [
                    'class' => \Amylian\Yii\Doctrine\Rbac\Manager::class,
                    'defaultRoles' => ['myDefaultRole']
                ],
            ],
            'container' => [
                'definitions' => [
                    \Doctrine\ORM\EntityManager::class => function ($container, $params, $config) {
                        // Create Cache
                        $cache = new \Doctrine\Common\Cache\FilesystemCache(\Yii::getAlias('@app/runtime/doctrine/cache'));
                        // Create the configuration
                        $config = \Doctrine\ORM\Tools\Setup::createAnnotationMetadataConfiguration(
                                        [\Yii::getAlias('@app/Misc')], true, \Yii::getAlias('@app/runtime/proxy'), $cache);
//                        $config->setSQLLogger(new \Doctrine\DBAL\Logging\EchoSQLLogger());
                        // Initialize and configure EventManager
                        $eventManager = new \Doctrine\Common\EventManager();
                        $resolver = new \Doctrine\ORM\Tools\ResolveTargetEntityListener();
                        $resolver->addResolveTargetEntity(\Amylian\Yii\Doctrine\Rbac\Model\AuthItemEntityInterface::class,
                                \Amylian\Tests\Yii\Doctrine\Rbac\Misc\AuthItem::class, []);
                        $resolver->addResolveTargetEntity(\Amylian\Yii\Doctrine\Rbac\Model\AuthRuleEntityInterface::class,
                                \Amylian\Tests\Yii\Doctrine\Rbac\Misc\AuthRule::class, []);
                        $resolver->addResolveTargetEntity(\Amylian\Yii\Doctrine\Rbac\Model\AuthRelationEntityInterface::class,
                                \Amylian\Tests\Yii\Doctrine\Rbac\Misc\AuthRelation::class, []);
                        $resolver->addResolveTargetEntity(\Amylian\Yii\Doctrine\Rbac\Model\AuthAssignmentEntityInterface::class,
                                \Amylian\Tests\Yii\Doctrine\Rbac\Misc\AuthAssignment::class, []);
                        $eventManager->addEventSubscriber($resolver);
                        // Initialize the Entity manager and open the connection 
                        return \Doctrine\ORM\EntityManager::create(
                                        [
                                            'dbname' => $GLOBALS['db_name'],
                                            'user' => $GLOBALS['db_username'],
                                            'password' => $GLOBALS['db_password'],
                                            'host' => $GLOBALS['db_host'],
                                            'driver' => $GLOBALS['db_type'],
                                        ], $config, $eventManager);
                    }
                ]
            ]
        ];
        $config = \yii\helpers\ArrayHelper::merge($defaultConfig, $config);
        parent::mockApplication($config, $appClass);

        // Update Schema from Entities 

        $em = \yii\di\Instance::of(\Doctrine\ORM\EntityManager::class)->get(); /* @var $em \Doctrine\ORM\EntityManager  */
        $metadatas = $em->getMetadataFactory()->getAllMetadata();
        $schemaValidator = new \Doctrine\ORM\Tools\SchemaValidator($em);
        $errors = $schemaValidator->validateMapping();
        if (!empty($errors)) {
            foreach ($errors as $className => $classErrors) {
                echo "Errors in Class $className:\n";
                foreach ($classErrors as $classError) {
                    echo "$classError\n";
                }
            }
        }
        $schemaTool = new \Doctrine\ORM\Tools\SchemaTool($em);
        $sqls = $schemaTool->getUpdateSchemaSql($metadatas, true);
        $schemaTool->updateSchema($metadatas, true);
    }

    protected function createManager(): \yii\rbac\ManagerInterface
    {
        if (!\Yii::$app) {
            $this->mockApplication();
        }
        return Yii::$app->get('authManager');
    }

    protected function setUp()
    {
        parent::setUp();
        $this->auth = $this->createManager();
        // $this->auth->em->getConfiguration()->setSQLLogger(new \Doctrine\DBAL\Logging\EchoSQLLogger());
    }

    protected function tearDown()
    {
        // $this->auth->em->getConfiguration()->setSQLLogger(null);
        parent::tearDown();
        $this->auth->removeAll();
    }

    protected function massRenameAndBack()
    {
        $auth = $this->auth;
        foreach (array_merge($auth->getRoles(), $auth->getPermissions()) as $item) {
            $oldName = $item->name;
            $item->name = $item->name . '__changed';
            $auth->update($oldName, $item);
        }
        foreach (array_merge($auth->getRoles(), $auth->getPermissions()) as $item) {
            $oldName = $item->name;
            $item->name = str_replace('__changed', '', $item->name);
            $auth->update($oldName, $item);
        }
    }

    protected function prepareData()
    {
        parent::prepareData();
        // Renaming is a tricky task - thus we test it heavily in each test
        $this->massRenameAndBack();
    }

}
