#!/bin/bash
set -e

vendor/bin/phpunit
(cd "test/functional" && ./run.sh)
