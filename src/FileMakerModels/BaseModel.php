<?php

namespace L5SimpleFM\FileMakerModels;

use L5SimpleFM\Contracts\FileMakerInterface;

/**
 * Note, FileMaker models are just implementations of the L5SimpleFM class
 * with the table pre-set. The methods found in the base class are just
 * maps to the L5SimpleFM class methods so that when the methods are
 * called, they can be called directly from the model, e.g.
 *
 *     $people->findByFields($fieldValues)->executeCommand();
 * vs
 *     $people->connection->findByFields($fieldValues)->executeCommand();
 *
 * This means that using the model does not require an indepth understanding
 * of the L5SimpleFM class.
 *
 * To create a new model, extend this class and override the `$layoutName`
 * property and set it to the fileMaker layout name to use.
 */
abstract class BaseModel
{

    protected $layoutName;
    protected $connection;

    public function __construct(FileMakerInterface $fm)
    {
        $this->connection = $fm->setLayout($this->layoutName);
    }

    public function findByFields($fieldValues)
    {
        return $this->connection->findByFields($fieldValues);
    }

    public function findAll($max = null, $skip = null)
    {
        return $this->connection->findAll($max, $skip);
    }

    public function findByRecId($recId)
    {
        return $this->connection->findByRecId($recId);
    }

    public function createRecord($data)
    {
        return $this->connection->createRecord($data);
    }

    public function updateRecord($recId, $data)
    {
        return $this->connection->updateRecord($recId, $data);
    }

    public function deleteRecord($recId)
    {
        return $this->connection->deleteRecord($recId);
    }

    public function callScript($scriptName, $scriptParameters = null)
    {
        return $this->connection->callScript($scriptName, $scriptParameters);
    }

    public function addCommandItems($commandArray)
    {
        return $this->connection->addCommandItems($commandArray);
    }

    public function max($count)
    {
        return $this->connection->max($count);
    }

    public function skip($count)
    {
        return $this->connection->skip($count);
    }

    public function sort($sortArray)
    {
        return $this->conection->sort($sortArray);
    }

    public function executeCommand()
    {
        return $this->connection->executeCommand();
    }
}
