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

# Branch to initially install from (Will later be copied over by sync_shipper)
readonly BRANCH="feature-multi-ver"
## }}} Vars

## Functional Tests {{{
test_version() {
  local VERSION="$1"
  local VERSION_DIR="${FUNC_TEST_DIR}/${VERSION}"

  cd "${VERSION_DIR}"
  test_artisan_commands_present
  test_artisan_check_fail_incorrect_db
}

test_artisan_commands_present() {
  local OUTPUT="$($ARTISAN_BIN)"

  echo "# Test that shipper artisan commands exist"
  echo -n " - Commands present... "
  if echo $OUTPUT | grep 'shipper:check' > /dev/null; then
    echo_pass
    return 0
  else
    echo_fail
    echo "-- Output: --"
    echo "$OUTPUT"
    return 1
  fi
}

test_artisan_check_fail_incorrect_db() {
  local COMMAND="$ARTISAN_BIN shipper:check"

  echo "# Test artisan shipper:check"

  echo -n " - Show error message on incorrect DB... "
  (echo $($COMMAND) | grep "Host not set to 'db'" > /dev/null && echo_pass) || (echo_fail && return 1)

  echo -n " - Exit code is 1 on fail... "
  set +e
  ($COMMAND > /dev/null && echo_fail && return 1) || (echo_pass && return 0)
  set -e
}

## }}} Functional Tests

main() {
  local COMMAND="${1:-default}"
  local LARAVEL_VERSIONS="$(get_versions_to_test "${PHP_VERSION}")"

  case "$COMMAND" in
    prepare)
      for VERSION in $LARAVEL_VERSIONS; do
        create_version "${VERSION}"
      done
      ;;
    cleanup)
      cleanup
      ;;
    test)
      cd "${PROJECT_DIR}" && vendor/bin/phpunit

      for VERSION in $LARAVEL_VERSIONS; do
        echo "Running tests for laravel-shipper on Laravel ${VERSION}, PHP ${PHP_VERSION}..."
        test_version "${VERSION}"
      done
      ;;
    run)
      main "prepare"
      main "test"
      ;;
    *)
      echo "run.sh (prepare|test|cleanup|run)"
      exit 64
  esac
}

main "$@"
