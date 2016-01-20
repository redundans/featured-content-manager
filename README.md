# Featured Content Manager

This README is for development info only, will be removed during plugin build. Public info should go in readme.txt!

## Install
1. Clone project
2. cd to project folder
3. $ npm install
4. $ grunt
5. that's it

## Tests
Tests are runned with PHPUnit and with mock objects by WP_Mock (https://github.com/10up/wp_mock).

1. Install WP_Mock with Composer in a terminal: ```composer require --dev 10up/wp_mock:dev-master```

* To run the test locally, install PHPUnit.
* To run the tests on a Docker container, use the provided Dockunit.json with Dockunit (https://github.com/tlovett1/dockunit). Install with npm: ```npm install -g dockunit```

## Release
1. Install the build script 'plugin-build' in your path
2. cd to /build
3. $ plugin-build [version number, eg 0.3]
4. login to plugins.klandestino.se and upload the zip-file created in the /build folder to the product (https://plugins.klandestino.se/wp-admin/post.php?post=24&action=edit) in wp-admin. Do this by using "add new file" because then we keep the version history with old zip-files.
5. Select the latest version "Choose the source file to be used for automatic updates" for the product in wp-admin.
6. Update the current version number of the product in wp-admin
7. that's it
