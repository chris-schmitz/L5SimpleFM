# L5SimpleFM

L5SimpleFM is a tool wrapped around the [Soliant Consulting's SimpleFM package](https://github.com/soliantconsulting/SimpleFM). L5SimpleFM allows you to make declarative queries against a hosted FileMaker database.


This tool has been made specifically for Laravel 5 integration and can be installed via composer from the [packagist repository](https://packagist.org/packages/cschmitz/l5simplefm).

Readme Contents:

- [Quick Examples](#quick-examples)
- [Required tools](#required-tools)
- [Installation](#installation)
- [Configuration](#configuration)
- [Important Notes](#important-notes)
- [Demo FileMaker Database](#demo-filemaker-database)
- [L5SimpleFM Models](#l5simplefm-model)
- [Commands](#commands)
- [Exceptions](#exceptions)

# Quick Examples

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
	
	namespace MyApp\Models;
	
	use L5SimleFM\FileMakerModels\BaseModel;
	
	class User extends BaseModel
	{
		protected $layoutName = "web_Users";
	}

Performing the find from the first example using the newly defined `User` model would look like this:

    <?php

    namespace App\Http\Controllers;

    use App\Http\Controllers\Controller;
    use App\Models\User;

    class UsersController extends Controller
    {
        protected $user;

        public function __construct(User $users)
        {
            $this->user = $users;
        }

        public function findUsers()
        {
            $searchFields = ['username' => 'chris.schmitz', 'status' => 'active'];
            $result = $this->user->findByFields($searchFields)->executeCommand();
            $records = $result->getRows();
            return compact('records');
        }
    }



## Required Tools

The following tools are required to run this project:

- <a href="http://php.net/manual/en/install.php" target="_blank">PHP version 5.5 or newer</a>
- <a href="https://getcomposer.org/" target="_blank">Composer</a>
    - If you're installing composer for the first time, make sure you <a href="https://getcomposer.org/doc/00-intro.md#globally" target="_blank">install composer globally</a>
- <a href="http://laravel.com/docs/5.1" target="_blank">Laravel 5.1</a>
- <a href="https://git-scm.com/" target="_blank">Git</a>
- <a href="http://store.filemaker.com/US/ENG/LIC/" target="_blank">FileMaker Server version 13 or newer</a>


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
    - Has a security account for the website user.
    - The privilege set that is set for the website user has the `fmxml` extended privilege enabled.

For the purposes of this readme, I'll be using and referring to the [Demo file for this project](https://github.com/chris-schmitz/L5SimpleFM/tree/master/SampleDatabase).

### Laravel
- Rename your `.env.example` file `.env`
- From the command line, cd into the root of your project (you should be able to see the `artisan` tool) and run the command to generate the application key:

        php artisan key:generate

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

---

&nbsp;

## L5SimpleFM Model

L5SimpleFM can be used just as a basic data access tool by accessing the L5SimpleFM class or the FileMakerInterface directly, but it can also be used as a data model. Really, the difference between the two is very minor. The basic idea creating an instance of the L5SimpleFM class that is meant to only be used to access a specific entity (in FileMaker's case, this would likely be a single table via a layout).

### Creating a L5SimpleFM model 

A L5SimpleFM model should extend the `L5SimpleFM\FileMakerModels\BaseModel` class:

    <?php

    namespace App\Models;

    use L5SimpleFM\FileMakerModels\BaseModel;

    class User extends BaseModel
    {

        protected $layoutName = "web_User";

    }

In the `Example` FileMaker model class above, the layout in our FileMaker file would be named `web_User`.

### A basic call

Once you have:
- Installed the bundle
- Hosted and configured your FileMaker database
- Configured your Laravel project
- Created a model

You can open the Laravel project's `app/Http/routes.php` file. Add the following route:

    <?php

    use App\Models\User;

    Route::get('users', function (User $user) {
        try {
            $user->findAll();
            $user->max(10);
            $user->sort([
                ['field' => 'company', 'rank' => 1, 'direction' => 'descend'],
                ['field' => 'status', 'rank' => 2, 'direction' => 'ascend'],
            ]);
            $result  = $user->executeCommand();
            $records = $result->getRows();

        } catch (\Exception $e) {
            return $e->getMessage();
        }
        return compact('records');
    });

Here's a breakdown on each step of the find:

- `$user->findAll();`
    - Tells your L5SimpleFM `User` model to get ready to find all records on the layout.
- `$user->max(10)`
    - Tells your model to only return up to `10` record when it executes the command.
- `$user->sort([ ['field' => 'company', 'rank' => 1, 'direction' => 'descend'], ['field' => 'status', 'rank' => 2, 'direction' => 'ascend'] ])`
    - Tells your model to sort first by the company field in descending order and then by the status field in ascending order after executing the command.
- `$result  = $user->executeCommand();`
    - Tells L5SimpleFM to execute your command.
    - The result of the command is a *SimpleFM* (not L5SimpleFM) object containing meta data on the request result as well as the resulting data.
- `$records = $result->getRows();`
    - Extracts the records from the SimpleFM object.

### Method Chaining

L5SimpleFM uses method chaining, so the same find all demo above can also be written like this:

    Route::get('users', function (User $user) {
        try {
            $sortFields = [
                ['field' => 'company', 'rank' => 1, 'direction' => 'descend'],
                ['field' => 'status', 'rank' => 2, 'direction' => 'ascend'],
            ];

            $result = $user->findAll()->max(10)->sort($sortFields)->executeCommand();
            $records = $result->getRows();

        } catch (\Exception $e) {
            return $e->getMessage();
        }
        return compact('records');
    });

This use of method chaining can mak complex requests a bit more readable. The rest of the demos in this readme will use method chaining.
From here, you will have access to all of the methods outlined in [the `BaseModel` class](https://github.com/chris-schmitz/L5SimpleFM/blob/master/src/FileMakerModels/BaseModel.php). These methods are actually maps to the `L5SimpleFM` classes public methods.

# Commands

- [executeCommand()](#executecommand)
- [findByFields($fieldValues)](#findallmax--null-skip--null)
- [findAll($max = null, $skip = null)](#findbyfieldsfieldvalues)
- [findByRecId($recId)](#findbyrecidrecid)
- [createRecord($data)](#createrecorddata)
- [updateRecord($recId, $data)](#updaterecordrecid-data)
- [deleteRecord($recId)](#deleterecordrecid)
- [callScript($scriptName, $scriptParameters = null)](#callscriptscriptname-scriptparameters--null)
- [addCommandItems($commandArray)](#addcommanditemscommandarray)
- [max($count)](#maxcount)
- [skip($count)](#skipcount)
- [sort($sortArray)](#sortsortarray)
- [resetLayout($layoutName)](#resetlayoutlayoutname)



## executeCommand()

For any of these commands to execute, you need to call or chain on the `executeCommand()` command. 

Any command chained before `executeCommand()` is just used to build up the request's form. This is what allows you to call the command methods separately or chained together. 

The following is an example of an index method on a controller that breaks up the method calls to build up an object that allows paging through a record set and fires `executeCommand()` once it's set up:


    namespace App\Http\Controllers;

    use App\Http\Controllers\Controller;
    use Illuminate\Http\Request;

    // The L5SimpleFM Model for User
    use App\Models\User;

    class UsersController extends Controller
    {
        protected $user;

        public function __construct(User $users)
        {
            $this->user = $users;
        }

    public function index(Request $request)
    {
        // capturing request headers passed in from the browser
        $max = $request->get('max');
        $skip = $request->get('skip');

        $sortArray = [
            ['field' => 'company', 'rank' => 1, 'direction' => 'descend'],
            ['field' => 'username', 'rank' => 2, 'direction' => 'ascend'],
        ];

        // note that we did not fire `executeCommand()` yet, we're still just building up the L5SimpleFM command
        $this->user->findAll()->sort($sortArray);

        // we don't want to specify a max value unless the browser actually asked for it
        if (!empty($max)) {
            $this->user->max($max);
        }

        // we don't want to specify a skip value unless the browser actually asked for it
        if (!empty($skip)) {
            $this->user->skip($skip);
        }

        // now that our command has been assembled, we fire it
        $result = $this->user->executeCommand();

        // getting the total number of records found (which may be larger than our max value)
        $total = $result->getCount();

        $records = $result->getRows();

        return compact('total', 'records');
    }


## findAll($max = null, $skip = null)

Find all returns all records for a given Entity(layout). The `max` and `skip` parameters allow you to limit the number of records and page through the data.

If we wanted to return all records from a layout a "page" at a time where:

- The page size was 10 records per page
- We are on page 3

We could perform a command like this:

    // in your controller, these values would be passed in by the request parameters
    $max = 10;
    $skip = 2;

    try {
        $result = $this->user->findAll()->max($max)->skip($skip)->executeCommand();
        $records = $result->getRows();
    } catch (\Exception $e) {
        return $e->getMessage();
    }
    return compact('records');

## findByFields($fieldValues)

L5SimpleFM accepts an associative array of `[field name => search value]`s for searching. 

For instance, if we wanted to find all records in the `web_Users` layout from the company Skeleton Key who have a status of Active, we could use this chain of commands:

     try {
        $searchFields = [
            'company' => 'Fake Company, INC',
            'status'  => 'Active',
        ];

        $result  = $this->user->findByFields($searchFields)->executeCommand();
        $records = $result->getRows();
    } catch (\Exception $e) {
        return $e->getMessage();
    }
    return compact('records');


## findByRecId($recId)

FileMaker uses an internal record id for every record you create, regardless of if you add a serial number field to your tables. You can see this record id in FileMaker by going to the layout you want to search on, opening the Data Viewer, and entering the function `Get(RecordId)`.

L5SimpleFM has a method specifically for searching by this record id. 

Ex;ample. To find the record in the `web_Users` table with a recid of 3, we could use the following chain of commands:

    try {
        $result = $this->user->findByRecId(3)->executeCommand();
        $record = $result->getRows();
    } catch (\Exception $e) {
        return $e->getMessage();
    }
    return compact('record');


## callScript($scriptName, $scriptParameters = null)

A script can be set to fire after L5SimpleFM executes a different command. 

Here's the same log script fired after a findByRecId command:

    try {
        $searchFields = ['username' => 'chris.schmitz'];
        $message      = sprintf("Creating a log record after performing a find for the user record with username %s.", $searchFields['username']);
        $result       = $this->user->findByFields($searchFields)->callScript('Create Log', $message)->executeCommand();
        $records      = $result->getRows();
    } catch (\Exception $e) {
        return $e->getMessage();
    }
    return compact('records');

## createRecord($data)

An associative array of `[field name => search value]`s can be used to create a new record.

    try {
        $recordValues = [
            'username' => 'new.person',
            'email'    => 'new.person@skeletonkey.com',
            'company'  => 'Skeleton Key'
        ];
        $result = $this->user->createRecord($recordValues)->executeCommand();
        $record = $result->getRows();
    } catch (\Exception $e) {
        return $e->getMessage();
    }
    return compact('record');

## updateRecord($recId, $data)

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
        $recid = 2;
        $message = sprintf('User %s no longer works for Skeleton Key', $updatedValues['username']);
        $result = $this->user->updateRecord($recid, $updatedValues)->callScript('Create Log', $message)->executeCommand();
        $record = $result->getRows();
    } catch (\Exception $e) {
        return $e->getMessage();
    }
    return compact('record');

## deleteRecord($recId)

To delete a record, specify the record id.

Note that we do not need to set a `$result` variable as there are no records to fetch when the record is deleted successfully. Any error in deleting the record will be caught by the exception `catch`.

    try {
        $recid = 10;
        $this->user->deleteRecord($recid)->executeCommand();
    } catch (\Exception $e) {
        return $e->getMessage();
    }
    return ['success' => 'Record Deleted'];


## addCommandItems($commandArray)

There are many other custom web publishing XML commands that you can send to the FileMaker Server via SimpleFM that what I have outlined here. I tried to cover some of the most common (and ones that I need for the project that I extracted this wrapper from). There are also additional commands you can pass in with a particular request.

The commands are sent via key/value pairs via the request url. You can see documentation for these in FileMaker Server's PDF "fmsXX_cwp_xml.pdf" where XX is the version number of the FileMaker Server you're accessing (e.g. fms13_cwp_xml.pdf).

If you want to send a command to FileMaker Server that is not defined by the L5SimpleFM class you can use the `customCommand` method. You can pass an associative array of [command => value] pairs to add to the request url.

E.g. If we wanted to set a max number of records to return with a `findAll` command, we can add the `-max` command in with the request:

    try {
        $maxRecordsToReturn = 3;
        $result = $this->user->findAll()->addCommandItems(['-max' => $maxRecordsToReturn])->executeCommand();
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
        $result = $this->user->addCommandItems($commandArray)->executeCommand();
        $records = $result->getRows();
    } catch (\Exception $e) {
        return $e->getMessage();
    }
    return compact('records');

## max($count)

For commands that return a variable number of records you can chain `max()` into the command to limit the number of records returned:

     try {
        $searchFields = [
            'company' => 'Skeleton Key',
            'status'  => 'Active',
        ];
        $count = 50;

        $result  = $this->user->findByFields($searchFields)->max($count)->executeCommand();
        $records = $result->getRows();
    } catch (\Exception $e) {
        return $e->getMessage();
    }
    return compact('records');

While the total number of records found may be larger than 50, only 50 records will be returned in the rows.

## skip($count)

Similar to the `max()` command, the `skip()` command can be added to commands that return a variable number of records to affect the records returned. Skip will determine what record to start with when returning a limited number of records. 

    try {
        $searchFields = [
            'company' => 'Fake Company, INC',
            'status' => 'Active',
        ];
        $count = 50;
        $skip = 2;

        $result = $this->user->findByFields($searchFields)->max($count)->skip($skip)->executeCommand();
        $records = $result->getRows();
    } catch (\Exception $e) {
        return $e->getMessage();
    }
    return compact('records');

In this example, we're only returning up to 50 records and we'll start with the 11th record in the found set (we skipped the first 10).

`skip()` and `max()` can be used in combination to facilitate paging through a found set of records.

## sort($sortArray)

L5SimpleFM accepts a multi-dimensional array of data to perform sorting. 

With sorting you **must** specify the:

- Field being used to sort
- The rank (or order) in which the field should be sorted

If we wanted to sort by `company` and then `username` we could use the following array structure:

    $sortOptions = [
        ['field' => 'company', 'rank' => 1],
        ['field' => 'username', 'rank' => 2]
    ];


You can also optionally specify the direction that the field can be sorted in.

    $sortOptions = [
        ['field' => 'company', 'rank' => 1, 'direction' => 'descend'],
        ['field' => 'username', 'rank' => 2, 'direction' => 'ascend']
    ];

Once you've built up your sort options array, you can pass them into the `sort()` command:

    try {
        $sortOptions = [
            ['field' => 'company', 'rank' => 1, 'direction' => 'descend'],
            ['field' => 'username', 'rank' => 2, 'direction' => 'ascend'],
        ];

        $result = $this->user->findAll()->sort($sortOptions)->executeCommand();
        $records = $result->getRows();
    } catch (\Exception $e) {
        return $e->getMessage();
    }
    return compact('records');

## resetLayout($layoutName)

One of the shortcomings (in my opinion) of FileMaker's custom web publishing is that you cannot specify the fields returned from a request; you always get data from *every field on the layout* that you're requesting from. From a sql viewpoint, requests to FileMaker Server via custom web publishing are always `SELECT * FROM mylayout ...` and not `SELECT field1,field3,fieldN FROM mylayout ...`.

Because of this I added the ability to reset the layout you're using at runtime. 

    try {
        $searchFields = [
            'company' => 'Fake Company, INC',
            'status' => 'Active',
        ];

        $result = $this->user->resetLayout('web_UserList')->findByFields($searchFields)->max($count)->skip($skip)->executeCommand();
        $records = $result->getRows();
    } catch (\Exception $e) {
        return $e->getMessage();
    }
    return compact('records');

This means you can have more than one layout that represents an entity. 

An actual example of this is if your web app has a list that uses a handful of columns from your entity to let the user identify each record. Clicking on a row opens a new window that shows all of the fields for the entity. If you're only using one layout to represent the entity then you're always returning all of the fields for the entity when you generate the list view even though you don't need them. 

With the `resetLayout` command, you can define two layouts, a list layout with only the fields you need for the list and a details layout which has all of the fields you need for the details window. When you need to use the alternate layout (the one not defined as the `$layoutName` property in the model) you can use the `resetLayout` command to fire the request against the alternate layout.

# Exceptions 

All of the exceptions that L5SimpleFM throws come from the class `L5SimpleFMBase`. The exceptions can be caught by their individual names, e.g.:

    try {
        $result = $this->user->findByFields(['company' => 'error co.'])->executeCommand();
        $records = $result->getRows();
    } catch (RecordsNotFoundException $e) {
        return $e->getMessage();
    }

Or by catching a generic php exception class (which all of the custom exceptions extend from):

    try {
        $result = $this->user->findByFields(['company' => 'error co.'])->executeCommand();
        $records = $result->getRows();
    } catch (\Exception $e) {
        return $e->getMessage();
    }

## Troubleshooting with getCommandResult

For the `RecordsNotFoundException` and `GeneralException` classes, the result object from the SimpleFM request is returned and can be accessed by the method `->getCommandResult()`. e.g.:

       try {
            $searchFields = [
                // there is no Error Company and the `RecordsNotFoundException` will be thrown
                'company' => 'Error Company', 
                'status' => 'Active',
            ];

            $result = $this->user->findByFields($searchFields)->executeCommand();
            $records = $result->getRows();
        } catch (\Exception $e) {
            $message = $e->getMessage();

            // You can use the `getCommandResult()` method to return 
            // the entire SimpleFM result object. 
            $result = $e->getCommandResult();
            dd($result);


            return $message;
        }
        return compact('records');
    }

This can be helpful because SimpleFM's FmResultSet object includes a debug url which is helpful in figuring out why the result failed:

    FmResultSet {#153 ▼
      #debugUrl: "http://web_user:[...]@127.0.0.1:80/fmi/xml/fmresultset.xml?-db=L5SimpleFMExample&-lay=web_Users&-db=L5SimpleFMExample&-lay=web_Users&company=Error+Co&status=Active&-find"
      #errorCode: 401
      #errorMessage: "No records match the request"
      #errorType: "FileMaker"
      #count: 0
      #fetchSize: 0
      #rows: []
    }

This also means you get access to the FmResultSet's other [result handling methods](https://github.com/soliantconsulting/SimpleFM#handle-the-result) like `getDebugUrl()`:

       try {
            $searchFields = [
                // there is no Error Company and the `RecordsNotFoundException` will be thrown
                'company' => 'Error Company', 
                'status' => 'Active',
            ];

            $result = $this->user->findByFields($searchFields)->executeCommand();
            $records = $result->getRows();
        } catch (\Exception $e) {
            $message = $e->getMessage();
            $result = $e->getCommandResult();

            // spits out the debug url itself
            dd($result->getDebugUrl());

            return $message;
        }
        return compact('records');
    }

**Note** that even though the FmResultSet and debug URL don't expose your password, it's still not a good idea to leave it in your project when you push to production, i.e. Use it for development debugging only.

## LayoutNameIsMissingException

This exception is thrown if you try to set a layout name without a value or with an empty string. 

If you're creating FileMaker models with L5SimpleFM, you would see this error if you did not specify the `protected $layoutName;` property.

This exception does not contain a result object.

## NoResultReturnedException

This exception is thrown if SimpleFM for some reason does not return a result object. 

This exception does not contain a result object.

## RecordsNotFoundException

This exception is returned if your find query does not return a result. 

I created a specific exception for this because it is an error that is thrown that you are likely to ignore. 

For example, if you're looking for an existing user record and creating a new record if an existing one isn't found, you could catch for the exception and flag to the rest of your app to create a new record:

    public function updateOrCreateNewUser(Request $request)
    {
        // email passed in from a POST request
        $email = $request->get('email');

        $userRecord = $this->checkForExistingUserRecord($email);
        
        if ($userRecord == false) {
            $record = $this->createNewUser($request->all());
        } else {
            $record = $this->updateExistingUser($userRecord['recid'], $request->all());
        }
        return compact('record');
    }

    protected function checkForExistingUserRecord($email)
    {
        try {
            $quotedEmail = sprintf('"%s"', $email);

            $result = $this->user
                ->findByFields(['email' => $quotedEmail])
                ->executeCommand();
            $record = $result->getRows()[0];
        } catch (RecordsNotFoundException $e) {
            return false;
        }
        return $record;
    }

In other cases you may want to treat a record not found exception as you would any other exception.

The RecordsNotFoundException **does** return a a command result.

## GeneralException

This is an exception that is thrown if the SimpleFM request:
- *Did* return a result
- The result *did* have an error
- The error was not the Records Not Found error.

These would be any other FileMaker XML custom web publishing errors.

The GeneralException **does** return a a command result.

In fact, the only reason I defined a general exception instead of throwing a regular PHP Exception is so that the command result can be passed back. 