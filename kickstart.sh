#!/bin/bash

##
# CakePHP kickstart script
#
# usage:
#   kickstart.sh <PROJECT_NAME>
##
set -o nounset
set -o errexit

# help print
function print_usage {
	echo ""
	echo "$0 <project_name> <command>"
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
PROJECT=$1
COMMAND=$2
PROJECT_DIR=$(pwd)/${PROJECT}
TMP_DIR=/tmp/cakeproject/${PROJECT}
CAKEPHP_URL="https://github.com/cakephp/cakephp.git"
KICKSTART_PLUGIN_URL="https://github.com/nojimage/CakePHP-Kickstart-Plugin.git"

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
    git pull origin master
    echo ""
    cat cake/VERSION.txt
    echo ""
}

case $COMMAND in
	init)
		echo ""
        if [ -d ${PROJECT_DIR} ]; then
            echo "${PROJECT_DIR} already exists."
            exit 1
        fi
        echo "Setup CakePHP"
        # fetch cakephp from git
		fetch_cake
		cp -r $TMP_DIR ${PROJECT_DIR}
		rm -rf $TMP_DIR
        cd ${PROJECT_DIR}
        # create git repo
        rm -rf .git
        git init
        git add . ; git add -f app/tmp; git add -f  app/config;
        git commit -m 'first commit'
        # add kickstart plugin
        git submodule add -f ${KICKSTART_PLUGIN_URL} plugins/kickstart
        git commit -m 'add Kickstart Plugin'
        echo ""
        echo "CakePHP initialization successs. Next run:"
        echo ""
        echo " cd ${PROJECT}; cake/console/cake kickstart"
        echo ""
		;;

	cakeupdate)
		echo ""
		echo "Update CakePHP core"
		fetch_cake
		rm -rf ${PROJECT_DIR}/cake
		cp -r $TMP_DIR/cake ${PROJECT_DIR}/cake
		rm -rf $TMP_DIR
		cd ${PROJECT_DIR}
		;;

	*)
		echo "Unkown <command> '${COMMAND}'."
        print_usage
		exit 1
		;;
esac

exit 0
