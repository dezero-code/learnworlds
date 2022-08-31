# Learnworlds module for Dz Framework

This module provides an easy way to integrate with Learnworlds API v2. See documentation on [https://learnworlds.dev/](https://learnworlds.dev/)

## Installation

Add these lines to composer.json file:

```shell
"require": {
    ...
    "dezero/learnworlds": "dev-main"
    ...
},
"repositories":[
    ...
    {
        "type": "vcs",
        "url" : "git@github.com:dezero-code/learnworlds.git"
    }
    ...
]
```

## Configuration

1) Define the module in the configuration file `/app/config/common/modules.php`
```shell

    // Learnworlds module
    'learnworlds' => [
        // 'class' => '\dzlab\learnworlds\Module',
        'class' => '\learnworlds\Module',
    ],
```

2) Add a new alias path in `/app/config/common/aliases.php`
```shell
    'dzlab.learnworlds' => DZ_BASE_PATH . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'dezero' . DIRECTORY_SEPARATOR . 'learnworlds',
    'learnworlds'       => DZ_BASE_PATH . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'modules' . DIRECTORY_SEPARATOR . 'learnworlds',
```

3) Set new component in `/app/config/common/components.php`
```shell
    // Learnworlds components
    'learnworlds' => [
        'class' => '\dzlab\learnworlds\components\LearnworldsComponent',
        // 'class' => '\learnworlds\components\LearnworldsComponent'
    ],
    'learnworldsApi' => [
        'class' => '\dzlab\learnworlds\components\LearnworldsApi',
        // 'class' => '\learnworlds\components\LearnworldsApi'
    ],
```

4) Register a log file for Learnworlds module in `/app/config/components/logs.php`
```shell
    // Logs for Learnworlds module
    [
        'class' => '\dz\log\FileLogRoute',
        'logFile' => 'learnworlds.log',
        'categories' => 'learnworlds',
    ],
    [
        'class' => '\dz\log\FileLogRoute',
        'logFile' => 'learnworlds_error.log',
        'categories' => 'learnworlds_error',
    ],
    [
        'class' => '\dz\log\FileLogRoute',
        'logFile' => 'learnworlds_warning.log',
        'categories' => 'learnworlds_warning',
    ],
    [
        'class' => '\dz\log\FileLogRoute',
        'logFile' => 'learnworlds_dev.log',
        'categories' => 'learnworlds_dev',
    ],
```

5) Configurate Learnworlds API via environment variables from the `.env` file.
```shell
# The API key for PRODUCTION environment
LEARNWORLDS_API_CLIENT_ID="REPLACE_ME"
LEARNWORLDS_API_CLIENT_SECRET="REPLACE_ME"
LEARNWORLDS_API_URL="REPLACE_ME"
LEARNWORLDS_SCHOOL_URL="REPLACE_ME"

# The API key for TEST (SANDBOX) environment
LEARNWORLDS_SANDBOX_API_CLIENT_ID="REPLACE_ME"
LEARNWORLDS_SANDBOX_API_CLIENT_SECRET="REPLACE_ME"
LEARNWORLDS_SANDBOX_API_URL="REPLACE_ME"
LEARNWORLDS_SANDBOX_SCHOOL_URL="REPLACE_ME"
```

6) Copy file `config/learnworlds.php` to your project directory `/app/config/components/learnworlds.php`. Open the copied file and add your config values, if needed.

7) Optional. If it exists, copy the translation file from `messages/<language_id>/learnworlds.php` to `/app/<language_id>/learnworlds.php`

8) Create a new directory in /app/modules/learnworlds path for this module and create a new Module.php file with this content:
```shell
<?php
/**
 * Module to integrate with Learnworlds API for DZ Framework
 */

namespace learnworlds;

class Module extends \dzlab\learnworlds\Module
{
    /**
     * User default view path
     */
    public $viewPath = null;


    /**
     * Default controller
     */
    // public $defaultController = 'sso';


    /**
     * Load specific CSS or JS files for this module
     */
    public $cssFiles = null;    // ['learnworlds.css'];
    public $jsFiles = null;     // ['learnworlds.css'];


    /**
     * This method is called when the module is being created
     * you may place code here to customize the module or the application
     */
    public function init()
    {
        parent::init();
    }
}
```
