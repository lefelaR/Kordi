#!/bin/sh

set -eu

if ! docker info >/dev/null 2>&1; then
	echo "Docker is not running. Starting it..."
	if ! docker desktop start --timeout 180; then
		echo "Could not start Docker." >&2
		exit 1
	fi
	if ! docker info >/dev/null 2>&1; then
		echo "Docker did not become ready." >&2
		exit 1
	fi
fi

docker compose down --remove-orphans
docker compose up --build --force-recreate --remove-orphans