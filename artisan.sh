#!/bin/bash
args=("$@")
docker compose run -it --rm --user $(id -u):$(id -g) artisan $args
