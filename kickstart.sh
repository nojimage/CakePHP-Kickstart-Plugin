#!/bin/bash

##
# CakePHP2.1 kickstart script
#
# usage:
#   kickstart.sh <PROJECT_NAME>
##
set -o nounset
set -o errexit

CAKEPHP_URL="https://github.com/cakephp/cakephp.git"
CAKEPHP_BRANCH=master
KICKSTART_PLUGIN_URL="https://github.com/nojimage/CakePHP-Kickstart-Plugin.git"

# get options
while getopts b: OPT
do
  case $OPT in
    "b" ) CAKEPHP_BRANCH="$OPTARG"; shift `expr $OPTIND - 1` ;;
  esac
done

# help print
function print_usage {
	echo ""
	echo "$0 [-b <cakephp_branch>] <command> <project_name>"
	echo ""
	echo "Available commands are:"
	echo " - init (fetch CakePHP from git. and install kickstart plugin.)"
	echo " - cakeupdate (copies latest CakePHP core into project)"
}

if [ $# != 2 ]; then
	print_usage
	exit 1
fi

# Vars
COMMAND=$1
PROJECT=$2
PROJECT_DIR=$(pwd)/${PROJECT}
TMP_DIR=/tmp/cakeproject/${PROJECT}

# Functions
function prepare_tmp_dir {
	echo "preparing temporary directory '${TMP_DIR}'..."
	rm -rf $TMP_DIR
	mkdir -p $TMP_DIR
}

function fetch_cake {
    echo ""
    echo "Fetching lastest CakePHP sources from git"
    prepare_tmp_dir
    cd $TMP_DIR
    git init
    git remote add origin ${CAKEPHP_URL}
    git pull origin ${CAKEPHP_BRANCH}
    echo ""
    cat lib/Cake/VERSION.txt
    echo ""
}

case $COMMAND in
	init)
		echo ""
        if [ -d ${PROJECT_DIR} ]; then
            echo "${PROJECT_DIR} already exists."
            exit 1
        fi
        echo "Setup CakePHP 2.1"
        # fetch cakephp from git
		fetch_cake
		cp -r $TMP_DIR ${PROJECT_DIR}
		rm -rf $TMP_DIR
        cd ${PROJECT_DIR}
        # create git repo
        rm -rf .git
        git init
        git add . ; git add -f app/tmp; git add -f  app/Config;
        git commit -m 'first commit'
        echo ""
        echo "CakePHP initialization successs."
        ## add kickstart plugin
        git submodule add -f -b master ${KICKSTART_PLUGIN_URL} plugins/Kickstart
        echo "" >> app/Config/bootstrap.php
        echo "CakePlugin::load('Kickstart'); // load Kickstart Plugin" >> app/Config/bootstrap.php
        echo "" >> app/Config/bootstrap.php
        git au app/Config/bootstrap.php
        git commit -m 'add Kickstart Plugin'
        echo ""
        echo "Next run:"
        echo ""
        echo " cd ${PROJECT}; app/Console/cake Kickstart.kickstart"
        echo ""
		;;

	cakeupdate)
		echo ""
		echo "Update CakePHP core"
		fetch_cake
        if [ ! -d ${PROJECT_DIR}/lib/Cake ] ; then
            mkdir -p ${PROJECT_DIR}/lib/Cake
        fi
		rm -rf ${PROJECT_DIR}/lib/Cake
		cp -r $TMP_DIR/lib/Cake ${PROJECT_DIR}/lib/Cake
		rm -rf $TMP_DIR
		cd ${PROJECT_DIR}
		;;

	*)
		echo "Unkown <command> '${COMMAND}'."
        print_usage
		exit 1
		;;
esac;

exit 0
