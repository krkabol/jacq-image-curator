#!/usr/bin/env bash
ARGS="$@"

if [ $# -eq 0 ]; then
	COMMAND="run build"
	else
	COMMAND=$ARGS
	fi

docker run --rm --interactive --tty \
        --volume ${PWD}:/data \
        --user=$(id -u):$(id -g) \
        -w="/data" \
        --entrypoint "npm" \
        node $COMMAND  --no-bin-link