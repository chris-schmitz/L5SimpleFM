<?php

namespace L5SimpleFM\FileMakerModels;

use L5SimpleFM\FileMakerModels\BaseModel;

/**
 * This is an example of how to create a model class using L5SimpleFM.
 * In your Laravel project you would:
 * - Create a new model using the name of your entity, in this case "Example".
 *     - You can do this either at the root of the `app` folder or in a subfolder (I use `app/FileMakerModels`).
 * - Add the `use L5SimpleFM\FileMakerModels\BaseModel;` path to import the BaseModel class.
 * - Create a `$layoutName` property to override the default property in the BaseModel class.
 * - Set the `$layoutName` property to the name of the *layout* in FileMaker that you want to interact with, *not* the table name.
 *     - In this example, my layout name is `example`.
 *
 * Note that you can extend the BaseModel class independently of using the `FileMakerInterface`.
 * The base class is just a specific application of the interface specific to one tableL5SimpleFM\FileMakerModels\BaseModel;.
 */
class Example extends BaseModel
{

    protected $layoutName = "example";

}
