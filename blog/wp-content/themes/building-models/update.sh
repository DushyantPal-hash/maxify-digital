#!/bin/bash

base_path="$(pwd)"
branch="master"

# Generic plugin - one.com themes and plugins 
slug="option-tree"
remote_path="git@gitlab.one.com:wp-in/one.com-wp-plugin-${slug}.git"
git archive --format zip --remote=${remote_path} -o ${slug}.zip master
unzip -o ${slug}.zip -d ${slug}
rm -rf ${slug}.zip

exit;