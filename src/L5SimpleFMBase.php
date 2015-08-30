<?php

namespace L5SimpleFM;

use L5SimpleFM\Exceptions\GeneralException;
use L5SimpleFM\Exceptions\LayoutNameIsMissingException;
use L5SimpleFM\Exceptions\NoResultReturnedException;
use L5SimpleFM\Exceptions\RecordsNotFoundException;

/**
 * A base class that abstracts the imperative logic for passing data to the SimpleFM bundle.
 */
abstract class L5SimpleFMBase
{

    /**
     * An array of the url parameters to send via SimpleUrl.
     * @var array An associative array of url parameters
     *            This could be commands (e.g. ['-find' => null]) or fieldName => fieldValue
     */
    protected $commandArray;

    /**
     * @var object An instance of the SimpleFM Adapter class.
     * @var \Soliant\SimpleFM\Adapter
     */
    protected $adapter;

    public function __construct($adapter)
    {
        $this->adapter = $adapter;
    }

    /**
     * Adds the default url parameters needed for any request.
     *
     * @return none
     */
    protected function primeCommandArray()
    {
        if (empty($this->layoutName)) {
            throw new LayoutNameIsMissingException('You must specify a layout name.');
        }
        $this->commandArray['-db'] = $this->adapter->getHostConnection()->getDbName();
        $this->commandArray['-lay'] = $this->layoutName;
    }

    /**
     * Adds additional Url parameters to the request.
     *
     * @param array An associative array of url keys and values.
     *        These are normally fields => values or additional FM XML flags => null
     */
    protected function addToCommandArray($values)
    {
        foreach ($values as $key => $value) {
            $this->commandArray[$key] = $value;
        }
    }

    /**
     * Removed all command items from a previous request
     */
    protected function clearCommandArray()
    {
        // Note: this is the default value used by SimpleFM, if you pass in an
        // empty array SimpleFM will not overwrite the previous command
        $this->commandArray = [];
        $this->adapter->setCommandArray($this->commandArray);
    }

    /**
     * Performs the steps necessary to execute a SimpleFM query.
     * @return object The SimpleFM result Object
     */
    public function executeCommand()
    {
        $this->adapter->setCommandArray($this->commandArray);
        $result = $this->adapter->execute();
        $commandArrayUsed = $this->adapter->getCommandArray();
        $this->clearCommandArray();
        $this->checkResultForError($result, $commandArrayUsed);
        return $result;
    }

    /**
     * Parses the SimpleFM result and determines if a FileMaker error was thrown.
     *
     * @param  object A SimpleFM result object
     * @return none
     */
    protected function checkResultForError($result, $commandArrayUsed)
    {
        if (empty($result)) {
            throw new NoResultReturnedException('The SimpleFM request did not return a result.');
        }
        if ($result->getErrorCode() == 401) {
            throw new RecordsNotFoundException($result->getErrorMessage(), $result->getErrorCode(), $result);
        }
        if ($result->getErrorCode() !== 0) {
            $message = $result->getErrorMessage() . ". Command used: " . json_encode($commandArrayUsed);
            throw new GeneralException($message, $result->getErrorCode(), $result);
        }
    }
}
