#!/bin/bash

# Check if branch argument is provided
if [ -z "$1" ]; then
    echo "Error: Please provide a branch name as an argument."
    exit 1
fi

# Create a regular directory
dir_name="delayjs_temp_directory"
echo "Creating directory: $dir_name"
mkdir "$dir_name"

# Clone repository into the regular directory
echo "Cloning repository to $dir_name"
if git clone https://github.com/wp-media/delay-javascript-loading.git "$dir_name"; then
    echo "Git clone successful"
else
    echo "Error: Git clone failed."
    rm -rf "$dir_name"  # Remove the directory if the clone fails
    exit 1
fi

# Change to the directory
cd "$dir_name" || exit

# Switch to the specified branch
branch_name=$1
git switch "$branch_name"

echo "Copying delayjs content to plugin"
cp delay-js.js ../assets/js/lazyload-scripts.min.js

# Come up one level out of the temp dir and run gulp
cd "./"
echo "Running gulp task"
gulp run:build-delayjs

# Delete the temporary directory
echo "Deleting temporary directory"
cd ..
rm -rf "$dir_name"

echo "Script completed successfully"