#!/bin/bash

FILES=$(git ls-files -om --exclude-standard);
if [ -n "$FILES" ]; then
	phpcs "$FILES"
fi
