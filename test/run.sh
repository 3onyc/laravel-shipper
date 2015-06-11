#!/bin/bash
set -euo pipefail
[ -n "${DEBUG:-}" ] && set -x

## Vars {{{
if [ -n "${PHP_VERSION:-}" -a -n "${PHP_BIN:-}" ]; then
  # Skip
  echo > /dev/null
#elif which hhvm > /dev/null; then
#  readonly PHP_VERSION="HHVM"
#  readonly PHP_BIN="$(which hhvm)"
else
  readonly PHP_VERSION="$(php -r 'echo phpversion();')"
  readonly PHP_BIN="$(which php)"
fi

readonly COMPOSER_BIN="$(which composer)"
readonly ARTISAN_BIN="${PHP_BIN} ./artisan"

readonly PROJECT_DIR="$(pwd)"
readonly FUNC_TEST_DIR="${PROJECT_DIR}/test/functional/_test"

readonly BRANCH="feature-multi-ver"
## }}} Vars

## Setup/Teardown {{{
cleanup() {
  rm -rf "${FUNC_TEST_DIR}"
}

create_version() {
  local VERSION="$1"
  local VERSION_DIR="${FUNC_TEST_DIR}/${VERSION}"

  echo "Creating ${VERSION}..."
  if [ ! -d "${VERSION_DIR}/vendor" ]; then
    $COMPOSER_BIN create-project laravel/laravel "${VERSION_DIR}" "${VERSION}" #--prefer-source --keep-vcs
  fi

  cd "${VERSION_DIR}"
  if [ ! -d vendor/x3tech/laravel-shipper ]; then
    $COMPOSER_BIN require --prefer-source "x3tech/laravel-shipper dev-${BRANCH}"
  fi
  sync_shipper "${VERSION}"
  add_provider "${VERSION}"
}

sync_shipper() {
  local VERSION="$1"
  local VERSION_DIR="${FUNC_TEST_DIR}/${VERSION}"

  cd "${VERSION_DIR}"
  rsync \
    --checksum \
    --archive \
    --exclude=vendor/ \
    --exclude=.git/ \
    --exclude=test/functional \
    "$PROJECT_DIR/" \
    vendor/x3tech/laravel-shipper
}

add_provider() {
  local VERSION="$1"
  local VERSION_DIR="${FUNC_TEST_DIR}/${VERSION}"

  case "${VERSION}" in
    4.0|4.1|4.2)
      local CONFIG_FILE="${VERSION_DIR}/app/config/app.php"
      if ! grep 'ShipperProvider' "${CONFIG_FILE}" > /dev/null; then
        sed -i \
          "s/WorkbenchServiceProvider',/WorkbenchServiceProvider', 'x3tech\\\\LaravelShipper\\\\Provider\\\\ShipperProvider',/g" \
          "${CONFIG_FILE}"
      fi
      ;;
    5.0)
      local CONFIG_FILE="${VERSION_DIR}/config/app.php"
      if ! grep 'ShipperProvider' "${CONFIG_FILE}" > /dev/null; then
        sed -i \
          "s/RouteServiceProvider',/RouteServiceProvider', 'x3tech\\\\LaravelShipper\\\\Provider\\\\ShipperProvider',/g" \
          "${CONFIG_FILE}"
      fi
      ;;
    5.1)
      local CONFIG_FILE="${VERSION_DIR}/config/app.php"
      if ! grep 'ShipperProvider' "${CONFIG_FILE}" > /dev/null; then
        sed -i \
          "s/RouteServiceProvider::class,/RouteServiceProvider::class, 'x3tech\\\\LaravelShipper\\\\Provider\\\\ShipperProvider',/g" \
          "${CONFIG_FILE}"
      fi
      ;;
  esac
}
## }}} Setup/Teardown

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
  $COMMAND > /dev/null
  local EXIT_CODE="$?"
  set -e
  ([ "$EXIT_CODE" -eq 1 ] || return 1 && echo_pass) || (echo_fail && return 1)
}

## }}} Functional Tests

## Utility {{{
echo_fail() {
  echo -e "\e[31mFAIL\e[0m"
}

echo_pass() {
  echo -e "\e[32mPASS\e[0m"
}
## }}} Utility

main() {
  local COMMAND="${1:-default}"

  case "$PHP_VERSION" in
    5.3.*)
      local LARAVEL_VERSIONS="4.0 4.1"
      ;;
    5.4.*)
      local LARAVEL_VERSIONS="4.0 4.1 4.2 5.0"
      ;;
    5.*|HHVM)
      local LARAVEL_VERSIONS="4.0 4.1 4.2 5.0 5.1"
  esac

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
