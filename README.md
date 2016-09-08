== BlueSpiceDistribution package loader / creator ==

* git version
* tarball version

BlueSpiceDistribution have been moved and can be initialized with the following command (git version):

	bash build/dist/init_bsdistribution_from_git.bash ../

replace "../" if your like to clone extensions into another dir

The init script will clone all needed extensions from github to your local extensions folder, before you can start, create a github account and setup your ssh key in profile settings. 

As alternative or for distribution you can create a zip file with all extensions (tarball version):

	bash build/dist/build_tarball.bash

This script will create the file "/tmp/BlueSpiceDistribution.zip", just extract the content into your extensions folder and copy/include LocalSettings config file as needed. 
