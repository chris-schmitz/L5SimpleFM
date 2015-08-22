<?php

namespace L5SimpleFM;

/**
 * A base class that abstracts the imperative logic for passing data to the SimpleFM bundle.
 */
abstract class L5SimpleFMBase
{

    /**
     * \Soliant\SimpleFM\Adapter
     *
     * @var object An instance of the SimpleFM Adapter class.
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
            throw new \Exception('Layout name is missing.');
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
     * Performs the steps necessary to execute a SimpleFM query.
     * @return object The SimpleFM result Object
     */
    public function executeCommand()
    {
        $this->adapter->setCommandarray($this->commandArray);
        $result = $this->adapter->execute();
        $this->checkResultForError($result);
        return $result;
    }

    /**
     * Parses the SimpleFM result and determines if a FileMaker error was thrown.
     *
     * @param  object A SimpleFM result object
     * @return none
     */
    protected function checkResultForError($result)
    {
        if (empty($result)) {
            throw new \Exception('Error retrieving database result.');
        }
        if ($result->getErrorCode() !== 0) {
            throw new \Exception('An error was thrown: ' . $result->getErrorMessage());
        }
    }
}
