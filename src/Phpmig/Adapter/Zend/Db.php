<?php
/**
 * @package    Phpmig
 * @subpackage Phpmig\Adapter
 */
namespace Phpmig\Adapter\Zend;

use \Phpmig\Migration\Migration,
    \Phpmig\Adapter\AdapterInterface;

/**
 * This file is part of phpmig
 *
 * Copyright (c) 2011 Dave Marshall <dave.marshall@atstsolutuions.co.uk>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


/**
 * Phpmig adapter for Zend_Db
 *
 * @author      Wojtek Gancarczyk  <gancarczyk@gmail.com>
 */
class Db implements AdapterInterface
{
    /**
     * @var string
     */
    protected $tableName;

    /**
     * @var string
     */
    protected $createStatement;

    /**
     * @var string
     */
    protected $hasSchemaStatement;

    /**
     * @var \Zend_Db_Adapter_Abstract
     */
    protected $adapter;

    /**
     *
     *
     * @param \Zend_Db_Adapter_Abstract $adapter
     * @param \Zend_Config $configuration
     */
    public function __construct(\Zend_Db_Adapter_Abstract $adapter, \Zend_Config $configuration)
    {
        $this->adapter = $adapter;
        $this->tableName = $configuration->phpmig->tableName;
        $this->createStatement = $configuration->phpmig->createStatement;
        $this->schemaStatement = $configuration->phpmig->schemaStatement;
        $this->hasSchemaStatement = $configuration->phpmig->hasSchemaStatement;

    }

    /**
     * Get all migrated version numbers
     *
     * @return array
     */
    public function fetchAll()
    {
        $select = $this->adapter->select();
        $select->from($this->tableName, 'version');
        $select->order('version ASC');
        $all = $this->adapter->fetchAll($select);
        return array_map(function($v) {return $v['version'];}, $all);
    }

    /**
     * Up
     *
     * @param Migration $migration
     * @return AdapterInterface
     */
    public function up(Migration $migration)
    {
        $this->adapter->insert($this->tableName, array(
            'version' => $migration->getVersion(),
        ));

        return $this;
    }

    /**
     * Down
     *
     * @param Migration $migration
     * @return AdapterInterface
     */
    public function down(Migration $migration)
    {
        $this->adapter->delete($this->tableName, array(
            'version' => $migration->getVersion(),
        ));

        return $this;
    }

    /**
     * Is the schema ready?
     *
     * @return bool
     */
    public function hasSchema()
    {
        try {
            $statement = $this->adapter->query($this->hasSchemaStatement);
            $statement->execute();
            $results = $statement->fetchAll();
        } catch (\Zend_Db_Statement_Exception $exception) {
            return false;
        } catch (\PDOException $exception) {
            return false;
        }

        if (is_array($results) && !empty($results)) {
            return true;
        }

        return false;
    }

    /**
     * Create Schema
     *
     * @return AdapterInterface
     */
    public function createSchema()
    {
        $this->adapter->query($this->createStatement);
        return $this;
    }

}