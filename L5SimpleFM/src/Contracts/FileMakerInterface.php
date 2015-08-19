<?php

namespace L5SimpleFM\Contracts;

interface FileMakerInterface
{

    public function setLayout($layoutName);

    public function findByFields($fieldValues);

    public function findByRecId($recId);

    public function createRecord($data);

    public function updateRecord($recId, $data);

    public function deleteRecord($recId);

    public function callScript($scirptName, $parameters);

    public function executeCommand();
}
