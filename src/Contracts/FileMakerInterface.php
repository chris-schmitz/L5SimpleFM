<?php

namespace L5SimpleFM\Contracts;

interface FileMakerInterface
{
    /**
     * Sets the context for the request.
     * @param string $layoutName The name of the layout in FileMaker.
     * @return object The class implementing this interface.
     */
    public function setLayout($layoutName);

    /**
     * Find all records within a layout.
     * @return object The class implementing this interface.
     */
    public function findAll();

    /**
     * Find all records for a given layout using the field => search value pairs provided.
     * @param  array $fieldValues An associative array of field => search values to use for the find.
     * @return object The class implementing this interface.
     */
    public function findByFields($fieldValues);

    /**
     * Find a singular record in the given layout by using FileMaker's internal record id.
     * @param  integer $recId The integer record id for the record.
     * @return object The class implementing this interface.
     */
    public function findByRecId($recId);

    /**
     * Create a new in the given layout using the field => value pairs provided.
     * @param  array $data An associative array of field => values to use to populate the record.
     * @return object The class implementing this interface.
     */
    public function createRecord($data);

    /**
     * Update an existing record in the given layout indicated by it's internal FileMaker record id.
     * and using the field => value pairs provided for the data.
     * @param  integer $recId The integer record id for the record.
     * @param  array $data An associative array of field => values to use to populate the record.
     *                     Fields not indicated in the array should *not* be overwritten or emptied.
     * @return object The class implementing this interface.
     */
    public function updateRecord($recId, $data);

    /**
     * Delete a record in the given layout indicated by it's internal FileMaker record id.
     * @param  integer $recId The integer record id for the record.
     * @return object The class implementing this interface.
     */
    public function deleteRecord($recId);

    /**
     * Call a script after performing the command.
     * @param  string $scirptName The name of the script to perform.
     * @param  string $parameters Any parameters needed to run the script.
     * @return object The class implementing this interface.
     */
    public function callScript($scirptName, $parameters);

    /**
     * Add additional items to the command being sent.
     * Note that this should not clear the existing command items.
     * @param array $commandItems An associative array of command => value pairs.
     * @return object The class implementing this interface.
     */
    public function addCommandItems($commandItems);

    /**
     * Clear the existing command items.
     * @return object The class implementing this interface.
     */
    public function clearCommandItems();

    /**
     * Sets the maximum number of records to return from the request.
     * @param  integer $count The integer number of records to return.
     * @return object The class implementing this interface.
     */
    public function max($count);

    /**
     * Sets the number of records to skip with from the request.
     * @param  integer $count The integer number of records to skip.
     * @return object The class implementing this interface.
     */
    public function skip($count);

    /**
     * Specify the fields, direction and rank that the results should be sorted in.
     * @param  array $sortArray A multi-dimensional array where each subarray contains
     *                          - The field to sort.
     *                          - The rank in which it sort that field relative to the other sort fields.
     *                          - The direction to sort the particular field in.
     * @return object The class implementing this interface.
     */
    public function sort($sortArray);

    /**
     * Sending the command that has been constructed by the other methods to FileMaker.
     * @return  FmResultSet An instance of the SimpleFM FmResultSet.
     */
    public function executeCommand();
}
