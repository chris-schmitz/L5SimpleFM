<?php

namespace L5SimpleFM;

use L5SimpleFM\Contracts\FileMakerInterface;
use L5SimpleFM\L5SimpleFMBase;
use Soliant\SimpleFM\Adapter;

/**
 * A class that outlines the different kinds of queries you may send to
 * FileMaker via SimpleFM.
 */
class L5SimpleFM extends L5SimpleFMBase implements FileMakerInterface
{

    /**
     * The name of the layout in FileMaker to perform the query against.
     * @var string The name of the layout
     */
    protected $layout;

    /**
     * An array of the url parameters to send via SimpleUrl.
     * @var array An associative array of url parameters
     *            This could be commands (e.g. ['-find' => null]) or fieldName => fieldValue
     */
    protected $commandArray;

    public function __construct(Adapter $adapter)
    {
        parent::__construct($adapter);
    }

    public function findAll()
    {
        $this->primeCommandArray();
        $this->addToCommandArray(['-findall' => null]);
        return $this;
    }

    /**
     * @param string Name of the layout to perform the query on.
     */
    public function setLayout($layoutName)
    {
        if (empty($layoutName)) {
            throw new \Exception('No layout specified.');
        }
        $this->layoutName = $layoutName;

        // Note: I don't know why we need to fire this in addition to setting the '-lay' parameter
        // in the request url. I'm sure there's a good reason and maybe a way to not do both, but
        // for now we can just do both
        $this->adapter->setLayoutName($layoutName);
        return $this;
    }

    /**
     * Find record(s) based on field data
     * @param  array An associative array of FieldNames => SearchValues
     * @return object This class
     */
    public function findByFields($fieldValues)
    {
        if (empty($fieldValues)) {
            throw new \Exception('No field values specified');
        }
        $this->primeCommandArray();
        $this->addToCommandArray($fieldValues);
        $this->addToCommandArray(['-find' => null]);
        return $this;
    }

    /**
     * Find a record based on it's internal FileMaker record ID
     * @param  integer The internal FileMaker Record ID
     * @return object This object
     */
    public function findByRecId($recId)
    {
        if (empty($recId)) {
            throw new \Exception('No record ID specified');
        }
        $this->primeCommandArray();
        $this->addToCommandArray(['-recid' => $recId]);
        $this->addToCommandArray(['-find' => null]);
        return $this;
    }

    /**
     * Create a new record and populate it with data.
     * @param  array An associative array of FileName => Values to populate the record with
     * @return object This object
     */
    public function createRecord($data)
    {
        $this->primeCommandArray();
        $this->addToCommandArray($data);
        $this->addToCommandArray(['-new' => null]);
        return $this;
    }

    /**
     * Update data in an existing record per it's internal FileMaker record ID.
     * @param  integer The internal FileMaker record ID.
     * @param  array An associative array of FileName => Values to overwrite the record with
     *         Data in fields not included will not be overwritten.
     * @return object This object
     */
    public function updateRecord($recId, $data)
    {
        if (empty($recId)) {
            throw new \Exception('No record ID specified');
        }
        $this->primeCommandArray();
        $this->addToCommandArray($data);
        $this->addToCommandArray(['-recid' => $recId]);
        $this->addToCommandArray(['-edit' => null]);
        return $this;
    }

    /**
     * Delete a record per it's internal FileMaker record ID.
     * @param  integer The internal FileMaker record ID.
     * @return object This object
     */
    public function deleteRecord($recId)
    {
        if (empty($recId)) {
            throw new \Exception('No record ID specified');
        }
        $this->primeCommandArray();
        $this->addToCommandArray(['-recid' => $recId]);
        $this->addToCommandArray(['-delete' => null]);
        return $this;
    }

    /**
     * @param  string The name of the FileMaker scipt to fire.
     * @param  array  An associative array
     * @return object This object
     */
    public function callScript($scriptName, $scriptParameters = null)
    {

        if (empty($scriptName)) {
            throw new \Exception('No script name specified');
        }

        // This is needed if we're only performing a script
        if ($this->commandArray['-db'] == null) {
            $this->primeCommandArray();
        }
        $this->addToCommandArray([
            '-script' => $scriptName,
            '-script.param' => $scriptParameters,
        ]);
        return $this;
    }

    public function addCommandItems($commandArray)
    {
        $this->addToCommandArray($commandArray);
        return $this;
    }

}
