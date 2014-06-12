PadBundle
=========

A Claroline Connect plugin

Installation
============

This plugin requires a pad manager to be installed : https://github.com/IFE-ENSL/PadManager.
This pad manager requires a etherpad-lite server.

First edit the composer.json file of your claroline application:

```yml
"repositories": [
...,
   {
       "type": "vcs",
       "url": "https://github.com/iCAPLyon1/PadBundle.git"
   }
]
```

Update the database

```sh
php app/console doctrine:schema:update --force
```

Then install the plugin

```sh
php app/console claroline:plugin:install IcapPadBundle
```

Finally, you have to indicate the pad manager location. At http://my-claroline-app/admin/open/platform_plugins, click on the icappad link
and edit the url (ex: http://www.my-pad-manager).
