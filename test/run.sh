#!/bin/bash
set -e

(cd "test/functional" && ./run.sh prepare)
vendor/bin/phpunit
(cd "test/functional" && ./run.sh test)
