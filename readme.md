# L5SimpleFM

L5SimpleFM is a wrapper for the [Soliant Consulting's SimpleFM package](https://github.com/soliantconsulting/SimpleFM).

The wrapper has been made specifically for Laravel 5 integration. 

L5SimpleFM allows you to make declarative queries against a hosted FileMaker database via the SimpleFM bundle.

e.g. 

Performing a find on the `web_Users` layout in a FileMaker database for a user with the `web_Users::username` value of  **chris.schmitz** and the `web_Users::status` of **active** would look like this:

	try {
		$searchFields = ['username' => 'chris.schmitz', 'status' => 'active'];
		$result = $fm->setLayout('web_Users')->findByFields($searchFields)->executeCommand();
		$records = $result->getRows();
	} catch (\Exception $e) {
		return $e->getMessage();
	}
	return compact('records');
    
L5SimpleFM also allows you to define Model classes for individual Entities within your FileMaker file. Using the same `web_Users` example above, defining a L5SimpleFM FileMaker model would look like this:

	<?php
	
	namespace MyApp\FileMakerModels;
	
	use L5SimleFM\FileMakerModels\BaseModel;
	
	class User extends BaseModel
	{
		protected $layoutName = "web_Users";
	}

Performing the find from the first example using the newly defined `User` model would look like this:

	<?php
	
	namespace MyApp\Http\Controllers;
	
	use MyApp\FileMakerModels\User;
	
	class UsersController {
	
		protected $user;

		public function __construct(User $users){
			$this->user = $user;
		}
		
		public function findUsers(){
			$searchFields = ['username' => 'chris.schmitz', 'status' => 'active'];
			$result = $this->user->findByFields($searchFields)->executeCommand();
			$records = $result->getRows();
			return compact('records');
		}
	}


Readme Contents:

- [Installation](#user-content-installation)
- [Configuration](#user-content-configuration)
- [Important Notes](#user-content-important-notes)
- [Demo FileMaker Database](#user-content-demo-filemaker-database)
- [L5SimpleFM Models](#user-content-creating-a-l5-simplefm-model)
- [Using the L5SimpleFM Class Directly](#user-content-using-the-l5simplefm-class-directly)
- [L5SimpleFM Class Commands](#user-content-l5simplefm-commands)


## Installation

- Create your Laravel project
- Add the package to your `composer.json` file:

	    require: {
	        "cschmitz/l5simplefm": "0.1.*"
	    }

- Run a `composer install` or `composer update` to pull in the package.
- Once the package is installed, add the L5SimpleFM service provider to the `providers` key in `config/app.php`:

        L5SimpleFM\L5SimpleFMServiceProvider::class,

## Configuration

### FileMaker
- Make sure that your FileMaker Database is:
    - Hosted on a FileMaker Server that is accessible by your web server.
    - Has a security account for the website user
    - The privilege set that is set for the website user has the `fmxml` extended privilege enabled

### Laravel
- In the Laravel project, update the `.env`
    - Add the following keys and values:
        - `FM_DATABASE=` 
            - The value should be the name of your database file without the extension.
        - `FM_USERNAME=`
            - The value should be the website security account name.
        - `FM_PASSWORD=`
            - The value should be the website security account password.
        - `FM_HOST=`
            - The value should be the IP address or domain name of your FileMaker Server.
    - The `FM_` entries should look similar to this:

            FM_DATABASE=L5SimpleFMExample
            FM_USERNAME=web_user
            FM_PASSWORD=webdemo!
            FM_HOST=127.0.0.1


## Important Notes

### When in *production*, **Never dump the L5SimpleFM object to the browser**!
SimpleFM uses FileMaker Server's XML web publishing to access FileMaker. This means your database credentials are passes in the request. 

You can see this if you die and var_dump the `L5SimpleFM->adapter->hostConnection` property.

Dumping the object is very helpful when debugging while developing, but dumping the object in production is a security risk.

## Demo FileMaker Database

A demo FileMaker database [can be found here](https://github.com/chris-schmitz/L5SimpleFM/tree/master/SampleDatabase).

### Full Access Account
- Username: **Admin**
- Password: **admin!password**

NOTE: **If you're going to host this example file on a publicly accessible FileMaker server, CHANGE THE FULL ACCESS ACCOUNT PASSWORD!**

### Web Access Account
- Username: **web_user**
- Password: **webdemo!**

## L5SimpleFM Model

L5SimpleFM can be used just as a [basic data access tool](#user-content-basic-l5simplefm-usage), but it can also be used as a data model. Really, the difference between the two is very minor. The basic idea creating an instance of the L5SimpleFM class that is meant to only be used to access a specific entity (in FileMaker's case, this would likely be a single table via a layout).

### Creating a L5SimpleFM model 

A L5SimpleFM model should extend the `L5SimpleFM\FileMakerModels\BaseModel` class:

    <?php

    namespace L5SimpleFM\FileMakerModels;

    use L5SimpleFM\FileMakerModels\BaseModel;

    class Example extends BaseModel
    {

        protected $layoutName = "example";

    }

In the `Example` FileMaker model class above, the layout in our FileMaker file would be named `example`.

From here, you will have access to all of the methods outlined in [the `BaseModel` class](). These methods are actually maps to the `L5SimpleFM` classes public methods. A quick reference for these methods:




## Using the L5SimpleFM class directly

These are the notes on how you can use the L5SimpleFM class via the FileMakerInterface directly. You would use this as a data access tool vs a formal piece of MVC structure. To see how to use L5SimpleFM as a Model, see the [L5SimpleFM Model](#user-content-l5simplefm-model) section of the readme.

### A basic call

Once you've installed the bundle, hosted and configured your FileMaker database, and configured your Laravel project, open the Laravel project's `app/Http/routes.php` file. Add the following route:

    Route::get('simplefmtest', function (FileMakerInterface $fm) {
        try {
            $fm->setLayout('web_Users');
            $fm->findAll();
            $result  = $fm->executeCommand();
            $records = $result->getRows();
        } catch (\Exception $e) {
            return $e->getMessage();
        }
        return compact('records');
    });

Here's a breakdown on each step of the find:

- `$fm->setLayout('web_Users');`
    - Sets the layout that you would like to perform the find against.
- `$fm->findAll();`
    - Tells L5SimpleFM to find all records on the layout.
- `$result  = $fm->executeCommand();`
    - Tells L5SimpleFM to execute your command.
    - The result of the command is a *SimpleFM* (not L5SimpleFM) object containing meta data on the request result as well as the resulting data.
- `$records = $result->getRows();`
    - Extracts the records from the SimpleFM object.

### Method Chaining

L5SimpleFM uses method chaining, so the same find all demo above can also be written like this:

	Route::get('simplefmtest', function (FileMakerInterface $fm) {
	    try {
			// The separate steps from the previous example are chained here:
	        $result = $fm->setLayout('web_Users')->findAll()->executeCommand();
	
	        $records = $result->getRows();
	    } catch (\Exception $e) {
	        return $e->getMessage();
	    }
	    return compact('records');
	});

This use of method chaining can mak complex requests a bit more readable. The rest of the demos in this readme will use method chaining.

## Interface vs Concrete class

L5SimpleFM has an optional interface called `FileMakerInterface` that can be injected into a constructor or controller method instead of injecting L5SimpleFM directly. This means that if you use the interface and ever want to switch out the L5SimpleFM implementation with something else the effect on your application's business logic should be minimal. 

That said, if you want to inject the L5SimpleFM concrete class directly you can do so by using the class name instead of the interface. review the `L5SimpleFMServiceProvider` register method for constructor details.

&nbsp;

# L5SimpleFM Commands

All of the commands outlined here are found in the public method list of the `L5SimpleFM` class. 

All examples expect that you have injected the `FileMakerInterface` as a dependency stored in the variable `$fm`.

## Finding by fields

L5SimpleFM accepts an associative array of `[field name => search value]`s for searching. 

For instance, if we wanted to find all records in the `web_Users` layout from the company Skeleton Key who have a status of Active, we could use this chain of commands:

     try {
        $searchFields = [
            'company' => 'Skeleton Key',
            'status'  => 'Active',
        ];

        $result  = $fm->setLayout('web_Users')->findByFields($searchFields)->executeCommand();
        $records = $result->getRows();
    } catch (\Exception $e) {
        return $e->getMessage();
    }
    return compact('records');


## Finding by recid

FileMaker uses an internal record id for every record you create, regardless of if you add a serial number field to your tables. You can see this record id in FileMaker by going to the layout you want to search on, opening the Data Viewer, and entering the function `Get(RecordId)`.

L5SimpleFM has a method specifically for searching by this record id. 

Example. To find the record in the `web_Users` table with a recid of 3, we could use the following chain of commands:

    try {
        $result = $fm->setLayout('web_Users')->findByRecId(3)->executeCommand();
        $record = $result->getRows();
    } catch (\Exception $e) {
        return $e->getMessage();
    }
    return compact('record');


## Firing a script after a command

A script can be set to fire after L5SimpleFM executes a different command. 

Here's the same log script fired after a findByRecId command:

    try {
        $searchFields = ['username' => 'chris.schmitz'];
        $message      = sprintf("Creating a log record after performing a find for the user record with username %s.", $searchFields['username']);
        $result       = $fm->setLayout('web_Users')->findByFields($searchFields)->callScript('Create Log', $message)->executeCommand();
        $records      = $result->getRows();
    } catch (\Exception $e) {
        return $e->getMessage();
    }
    return compact('records');

## Creating a new record

An associative array of `[field name => search value]`s can be used to create a new record.

    try {
        $recordValues = [
            'username' => 'new.person',
            'email'    => 'new.person@skeletonkey.com',
            'company'  => 'Skeleton Key'
        ];
        $result = $fm->setLayout('web_Users')->createRecord($recordValues)->executeCommand();
        $record = $result->getRows();
    } catch (\Exception $e) {
        return $e->getMessage();
    }
    return compact('record');

## Updating an existing record

Like creating a new record, an associative array of `[field name => search values]`s can be used to update a record.

Fields that are not included in the array will *not* be modified, so only specify what you want to change. If you need to clear a field, pass in an empty string.

To update the record, you will need the record id for the specific record.

    try {
        $updatedValues = [
            'username' => 'fired.person',
            'email' => '',
            'company' => '',
            'status' => 'Inactive'
        ];
        $recid = 8;
        $message = sprintf('User %s no longer works for Skeleton Key', $updatedValues['username']);
        $result = $fm->setLayout('web_Users')->updateRecord($recid, $updatedValues)->callScript('Create Log', $message)->executeCommand();
        $record = $result->getRows();
    } catch (\Exception $e) {
        return $e->getMessage();
    }
    return compact('record');

## Deleting a record

To delete a record, specify the record id.

Note that we do not need to set a `$result` variable as there are no records to fetch when the record is deleted successfully. Any error in deleting the record will be caught by the exception `catch`.

    try {
        $recid = 10;
        $fm->setLayout('web_Users')->deleteRecord($recid)->executeCommand();
    } catch (\Exception $e) {
        return $e->getMessage();
    }
    return ['success' => 'Record Deleted'];


## Add command items

There are many other custom web publishing XML commands that you can send to the FileMaker Server via SimpleFM that what I have outlined here. I tried to cover some of the most common (and ones that I need for the project that I extracted this wrapper from). There are also additional commands you can pass in with a particular request.

The commands are sent via key/value pairs via the request url. You can see documentation for these in FileMaker Server's PDF "fmsXX_cwp_xml.pdf" where XX is the version number of the FileMaker Server you're accessing (e.g. fms13_cwp_xml.pdf).

If you want to send a command to FileMaker Server that is not defined by the L5SimpleFM class you can use the `customCommand` method. You can pass an associative array of [command => value] pairs to add to the request url.

E.g. If we wanted to set a max number of records to return with a `findAll` command, we can add the `-max` command in with the request:

    try {
        $maxRecordsToReturn = 3;
        $result = $fm->setLayout('web_Users')->findAll()->addCommandItems(['-max' => $maxRecordsToReturn])->executeCommand();
        $records = $result->getRows();
    } catch (\Exception $e) {
        return $e->getMessage();
    }
    return $records;

You can also use this to construct any command to be sent via SimpleFM, including ones that are not included in the L5SimpleFM class methods. If we wanted to create a `findByFields` command by hand we could do it like this:

    try {
        $commandArray = [
            'status' => 'Active',
            '-max' => 3,
            '-find' => null
        ];
        $result = $fm->setLayout('web_Users')->addCommandItems($commandArray)->executeCommand();
        $records = $result->getRows();
    } catch (\Exception $e) {
        return $e->getMessage();
    }
    return compact('records');
