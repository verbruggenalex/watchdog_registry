# Watchdog Registry

## Introduction

The Watchdog Registry module is intended for developers who rely on checking the
watchdog table to detect PHP errors. This module contains a configuration entity
that can be used to register known PHP errors in the watchdog table. These
registered PHP errors can then be allowed to exist in the project for follow up
purposes.

This project will provide both a drush command and a behat subcontext with which
you can detect unregistered or new PHP errors. It is pretty handy if you need to
take over maintainership of a certain project that already has some PHP errors
that are being logged in the watchdog table. Such as you can whitelist those and
start working without having to worry to introduce new PHP errors.

 * For a full description of the module, visit the project page:
   https://github.com/verbruggenalex/watchdog_registry

 * To submit bug reports and feature suggestions, or track changes:
   https://github.com/verbruggenalex/watchdog_registry/issues

## Requirements

   This module requires the following modules:

 * Dblog (https://www.drupal.org/docs/8/core/modules/dblog/overview)

## Installation

Install the Watchdog Registry module as you would normally install a contributed
Drupal module. Visit https://www.drupal.org/node/1897420#s-add-a-module-with-composer
for further information.

## Configuration

The module has a Watchdog Registry configuration entity overview page that you
can find at: /admin/reports/dblog/watchdog_registry. This page is usually only
used to view and/or delete your configuration entities.

The module provides links (Register/Edit registry) for PHP errors on the page
/admin/reports/dblog. Clicking such a link will automatically populate the
fields on the form at /admin/reports/dblog/watchdog_registry/add. You will have
to provide a machine name under which the Watchdog Registry configuration entity
will be exported.

@todo:
Once exported this module will provide you with both a drush command and/or a
behat subcontext that you can use to run during your tests to see if any new
PHP errors have been logged in your watchdog table.

## Testing

### Behat

Declare the subcontexts path that includes the Watchdog Registry behat steps
folder. Then when you run your behat tests after each scenario it will check if
there are any PHP errors that have not been registered. And after the suite it
will report them.

```yaml
default:
  extensions:
    Drupal\DrupalExtension:
      subcontexts:
        paths:
          - modules/contrib/watchdog_registry/behat/steps
```

### Drush

## Maintainers

Current maintainers:
 * Alex Verbruggen (alexverb) - https://www.drupal.org/user/1129948
