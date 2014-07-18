Maestro
=======

Next-generation MVC Framework for PHP5.3+
Mimics ExpressJS/LocomotiveJS's API for PHP.

## Components

- Maestro\Router\Router - @nikic's FastRoute made compatible with PHP5.3 and wrapped inside a friendlier syntax.
- Maestro\Renderer\Renderer - Dependency-less extensible rendering engine.
- Maestro\HTTP\Request - HTTP Request abstraction object
- Maestro\HTTP\Response - HTTP Response abstraction object, multiple content-type sending available.
- Maestro\Controller - Controller base class.
- Maestro\Maestro - Self-contained app with all of the above components.

## Installation

### Composer

As usual, put this in your `composer.json` file.

```
	"require": {
        "otak/maestro": "1.0.x"
    }

```

## Usage

Create the following file structure at the root of your project

```

/app
--	/controllers
--	/models
--	/views
/config
--	/initializers
--	routes.php
/helpers
index.php

```

Then fill out your index.php like this

```

<?php

    use Maestro\Maestro;
    use Maestro\HTTP\Request;
    use Maestro\HTTP\Response;

    Maestro::gi()
        ->set('app path', __DIR__.'/app/')
        ->set('env', 'development') // change it to production when you need
        ->set('controller namespace', 'YourCompany\\Namespace')
        ->loadRoutes();

    Maestro::gi()->conduct();


```

# More to come
