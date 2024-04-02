#!/usr/bin/env bash
# Create .mo files from .po files.
# Twisted by WP-Translations.org, created by grappler.
for file in `find . -name "*.po"` ; do msgfmt -o ${file/.po/.mo} $file ; done
