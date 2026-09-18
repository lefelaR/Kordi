#!/bin/sh

set -eu

docker compose down --remove-orphans
docker compose up --build --force-recreate --remove-orphans