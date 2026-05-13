#!/usr/bin/env bash
set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
BUILD_DIR="${ROOT_DIR}/build"
PACKAGE_DIR="${BUILD_DIR}/wp-rocket"
ZIP_NAME="${1:-wp-rocket.zip}"
ZIP_PATH="${BUILD_DIR}/${ZIP_NAME}"

if ! command -v composer >/dev/null 2>&1; then
	echo "composer is required to install production dependencies." >&2
	exit 1
fi

if ! command -v zip >/dev/null 2>&1; then
	echo "zip is required to create the plugin archive." >&2
	exit 1
fi

rm -rf "${PACKAGE_DIR}" "${ZIP_PATH}"
mkdir -p "${PACKAGE_DIR}"

(
	cd "${ROOT_DIR}"
	tar \
		--exclude='./.agents' \
		--exclude='./.babelrc' \
		--exclude='./.codex' \
		--exclude='./.editorconfig' \
		--exclude='./.git' \
		--exclude='./.gitattributes' \
		--exclude='./.github' \
		--exclude='./.gitignore' \
		--exclude='./.travis.yml' \
		--exclude='./.tx' \
		--exclude='./AGENTS.md' \
		--exclude='./CONTRIBUTING.md' \
		--exclude='./LICENSE' \
		--exclude='./README.md' \
		--exclude='./bin' \
		--exclude='./build' \
		--exclude='./composer.lock' \
		--exclude='./docs' \
		--exclude='./gulpfile.js' \
		--exclude='./node_modules' \
		--exclude='./package-lock.json' \
		--exclude='./package.json' \
		--exclude='./phpcs.xml' \
		--exclude='./phpstan.neon.dist' \
		--exclude='./phpunit.xml.dist' \
		--exclude='./src' \
		--exclude='./tests' \
		--exclude='./vendor' \
		-cf - .
) | tar -x -C "${PACKAGE_DIR}"

composer install \
	--working-dir="${PACKAGE_DIR}" \
	--no-dev \
	--no-interaction \
	--no-progress \
	--prefer-dist \
	--no-scripts

composer dump-autoload \
	--working-dir="${PACKAGE_DIR}" \
	--optimize \
	--no-interaction \
	--no-dev

(
	cd "${BUILD_DIR}"
	zip -qr "${ZIP_NAME}" wp-rocket
)

echo "${ZIP_PATH}"
