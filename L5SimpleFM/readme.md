# L5SimpleFM

L5SimpleFM is a wrapper for the [Soliant Consulting's SimpleFM package](https://github.com/soliantconsulting/SimpleFM).

The wrapper has been made specifically for Laravel 5 integration. 

L5SimpleFM allows you to make declarative queries against a hosted FileMaker database via the SimpleFM bundle.

e.g. 

Performing a find on the `web_Users` layout in a FileMaker database for a user with the `web_Users::username` value of  **chris.schmitz** and the `web_Users::status` of **active** would look like this:

    


## Installation

- Create your Laravel project
- Add the package to your `composer.json` file:

    require: {
        "cschmitz/l5-simplefm": "dev-master"
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

A demo FileMaker database [can be found here]().

### Full Access Account
- Username: **Admin**
- Password: **admin!password**

NOTE: **If you're going to host this example file on a publicly accessible FileMaker server, CHANGE THE FULL ACCESS ACCOUNT PASSWORD!**

### Web Access Account
- Username: **web_user**
- Password: **webdemo!**


## Demo

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

## Performing a script

Performing a script can be done as a stand alone command or as part of another chain *after* the command has been fired. 

### Firing a stand alone script

In the example FileMaker database, I have a script that will create a log record with a passed in message:

    try {
        $scriptParameters = 'This stand alone log record was created by L5SimpleFM';
        $result = $fm->setLayout('web_User')->callScript('Create Log', $scriptParameters)->executeCommand();
        $record = $result->getRows();
    } catch (\Exception $e) {
        return $e->getMessage();
    }
    return $record;

## Creating a new record

## Updating an existing record

## Deleting a record