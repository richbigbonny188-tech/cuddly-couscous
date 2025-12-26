# AutoUpdater

This repository contains the shop files for the Gambio AutoUpdater.

In general the development of the AutoUpdater happens in the main_develop and compatibility_develop branch and update packages are created from the release branches.
But it should be mention, that the Gambio shop repository embedded this repository and copies the AutoUpdater files of a certain version into the shop.

There is a `build-package` script, which can be used to build an update package.
There is also a `composer create-docs` command, which can be used to create the PHP documentation of the AutoUpdater files.