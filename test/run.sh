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
readonly BRANCH="feature-multi-ver"
## }}} Vars

## Functional Tests {{{
test_versions() {
  local laravelVersions="$(get_versions_to_test "${PHP_VERSION}")"
  for version in $laravelVersions; do
    echo "[${version} Running tests for laravel-shipper on PHP ${PHP_VERSION}..."
    test_version "${version}"
  done
}
test_version() {
  local version="$1"

  do_test "${version}" test_artisan_commands_present
  do_test "${version}" test_artisan_check_fail_incorrect_db
  do_test "${version}" test_artisan_check_success
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

  if [ -f ".env" ]; then
    sed -i "s/=localhost/=db/g" ".env"
  else
    sed -i "s/=> 'localhost'/=> 'db'/g" "$(get_conf_file "${version}" "database.php")"
  fi

  echo -n " - Exit code is 0... "
  set +e
  ($checkCommand > /dev/null && echo_pass && return 0) || (echo_fail && return 1)
  set -e
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
      echo "run.sh (prepare|test|func-test|unit-test|cleanup|run)"
      exit 64
  esac
}

main "$@"
