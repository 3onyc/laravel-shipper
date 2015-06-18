#!/bin/bash
set -euo pipefail
[ -n "${DEBUG:-}" ] && set -x

source test/lib/util.sh
source test/lib/setup.sh

## Vars {{{

# PHP version detection
if [ -z "${PHP_VERSION:-}" -a -z "${PHP_BIN:-}" ]; then
  readonly PHP_VERSION="$(php -r 'echo phpversion();')"
  readonly PHP_BIN="$(which php)"
fi

readonly COMPOSER_BIN="$(which composer)"
readonly ARTISAN_BIN="${PHP_BIN} ./artisan"

readonly PROJECT_DIR="$(pwd)"

# Directory to put installs in for functional tests
readonly FUNC_TEST_DIR="${PROJECT_DIR}/test/functional/_test"
readonly FUNC_TEST_CACHE_DIR="${PROJECT_DIR}/test/functional/_cache"

# Branch to initially install from (Will later be copied over by sync_shipper)
readonly BRANCH="develop"
## }}} Vars

## Functional Tests {{{
test_versions() {
  local laravelVersions="$(get_versions_to_test "${PHP_VERSION}")"
  for version in $laravelVersions; do
    echo "[${version}] Running tests for laravel-shipper on PHP ${PHP_VERSION}..."
    test_version "${version}"
  done
}
test_version() {
  local version="$1"

  do_test "${version}" test_artisan_commands_present
  do_test "${version}" test_artisan_check_fail_incorrect_db
  do_test "${version}" test_artisan_check_success
  do_test "${version}" test_artisan_create_docker_success
  do_test "${version}" test_artisan_create_docker_compose_success
  do_test "${version}" test_artisan_create_dirs_success
  do_test "${version}" test_artisan_config_publish_success

  if [ -z "${TRAVIS:-}"]; then
    # Only run these when not on Travis
    # TODO: Run Docker on travis
    do_test "${version}" test_production_returns_200
    do_test "${version}" test_local_returns_200
  fi
}

test_artisan_commands_present() {
  local artisanOutput="$($ARTISAN_BIN)"

  echo "# Test that shipper artisan commands exist"
  echo -n " - Commands present... "
  if echo "$artisanOutput" | grep 'shipper:check' > /dev/null; then
    echo_pass
    return 0
  else
    echo_fail
    echo "-- Output: --"
    echo "$artisanOutput"
    return 1
  fi
}

test_artisan_check_fail_incorrect_db() {
  local checkCommand="$ARTISAN_BIN shipper:check"

  echo "# artisan shipper:check fails on incorrect DB"

  echo -n " - Displays error message... "
  (echo $($checkCommand) | grep "Host not set to 'db'" > /dev/null && echo_pass) || (echo_fail && return 1)

  echo -n " - Exit code is 1... "
  set +e
  ($checkCommand > /dev/null && echo_fail && return 1) || (echo_pass && return 0)
  set -e
}

test_artisan_check_success() {
  local version="$1"
  local checkCommand="$ARTISAN_BIN shipper:check"

  echo "# artisan shipper:check succeeds when everything is correct"

  fix_db_conf "${version}"

  echo -n " - Exit code is 0... "
  set +e
  ($checkCommand > /dev/null && echo_pass && return 0) || (echo_fail && return 1)
  set -e
}

test_artisan_create_docker_success() {
  echo "# artisan shipper:create:docker produces a working Dockerfile"
  local createCommand="$ARTISAN_BIN shipper:create:docker"

  fix_db_conf "${version}"

  echo -n " - Exit code is 0... "
  set +e
  ($createCommand > /dev/null && echo_pass && return 0) || (echo_fail && return 1)
  set -e
}

test_artisan_create_docker_compose_success() {
  echo "# artisan shipper:create:docker produces a working docker-compose.yml"
  local createCommand="$ARTISAN_BIN shipper:create:docker-compose"

  fix_db_conf "${version}"
  echo -n " - Exit code is 0... "
  set +e
  ($createCommand > /dev/null && echo_pass && return 0) || (echo_fail && return 1)
  set -e
}

test_artisan_create_dirs_success() {
  echo "# artisan shipper:create:dirs creates the correct directories"
  local createCommand="$ARTISAN_BIN shipper:create:dirs"

  fix_db_conf "${version}"
  echo -n " - Exit code is 0... "
  set +e
  ($createCommand > /dev/null && echo_pass && return 0) || (echo_fail && return 1)
  set -e

  echo -n " - app/storage/logs/hhvm exists... "
  ([ -d 'app/storage/logs/hhvm' ] && echo_pass && return 0) || (echo_fail && return 1)

  echo -n " - app/storage/logs/nginx exists... "
  ([ -d 'app/storage/logs/nginx' ] && echo_pass && return 0) || (echo_fail && return 1)
}

test_artisan_config_publish_success() {
  local version="$1"

  echo "# artisan config:publish creates config for laravel-shipper"


  case "${version}" in
    4.*)
      local confPath="app/config/packages/x3tech/laravel-shipper/config.php"
      local publishCommand="$ARTISAN_BIN config:publish x3tech/laravel-shipper"
      ;;
    5.*)
      local confPath="config/shipper.php"
      local publishCommand="$ARTISAN_BIN vendor:publish"
      ;;
  esac

  set +e
  echo -n " - Exit code is 0... "
  ($publishCommand > /dev/null && echo_pass && return 0) || (echo_fail && return 1)

  echo -n " - ${confPath} exists... "
  ([ -f "${confPath}" ] && echo_pass && return 0) || (echo_fail && return 1)
  set -e
}

test_production_returns_200() {
  local version="$1"
  local projectName="test_${version}_prod"

  echo "# docker-compose start results in a working Laravel instance (production)"

  $ARTISAN_BIN --env=production shipper:create:all > /dev/null
  docker-compose -p "${projectName}" build > /dev/null
  docker-compose -p "${projectName}" up -d > /dev/null
  
  echo -n " - returns 200... "
  sleep 2

  set +e
  curl -ISs --fail http://localhost:8080 > /dev/null
  local exitCode="$?"
  set -e

  docker-compose -p "${projectName}" stop &> /dev/null
  ([ $exitCode -eq 0 ] && echo_pass && return 0) || (echo_fail && return 1)
}
test_local_returns_200() { local version="$1"
  local version="$1"
  local projectName="test_${version}_local"

  echo "# docker-compose start results in a working Laravel instance (local)"

  $ARTISAN_BIN --env=local shipper:create:all > /dev/null
  docker-compose -p "${projectName}" build > /dev/null
  docker-compose -p "${projectName}" up -d > /dev/null

  echo -n " - returns 200... "
  sleep 2

  set +e
  curl -ISs --fail http://localhost:8080 > /dev/null
  local exitCode="$?"
  set -e

  docker-compose -p "${projectName}" stop &> /dev/null
  ([ $exitCode -eq 0 ] && echo_pass && return 0) || (echo_fail && return 1)
}

## }}} Functional Tests

main() {
  local commandArg="${1:-default}"

  case "$commandArg" in
    prepare)
      create_versions
      ;;
    cleanup)
      cleanup
      ;;
    single)
      local laravelVersion="${LARAVEL_VERSION}"

      create_version "${laravelVersion}"
      test_version "${laravelVersion}"
      ;;
    test)
      main "unit-test"
      main "func-test"
      ;;
    unit-test)
      (cd "${PROJECT_DIR}" && vendor/bin/phpunit)
      ;;
    func-test)
      test_versions
      ;;
    run)
      main "prepare"
      main "test"
      ;;
    *)
      echo "run.sh (prepare|test|func-test|unit-test|cleanup|run|single)"
      exit 64
  esac
}

main "$@"
