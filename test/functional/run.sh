#!/bin/bash
set -euo pipefail
[ -n "${DEBUG:-}" ] && set -x

readonly PROJECT_DIR="$(pwd)/../../"
readonly TEST_DIR="$(pwd)/_test"

cleanup() {
  rm -rf "${TEST_DIR}"
} 

create_version() {
  local VERSION="$1"
  local VERSION_DIR="${TEST_DIR}/${VERSION}"

  echo "Creating ${VERSION}..."
  [ -d "${VERSION_DIR}/vendor" ] && echo "${VERSION} Already created" && return 0

  composer create-project laravel/laravel "${TEST_DIR}/${VERSION}" "${VERSION}" --prefer-dist

  cd "${VERSION_DIR}"
  if [ ! -d vendor/x3tech/laravel-shipper ]; then
    composer require --prefer-source "x3tech/laravel-shipper dev-feature-multi-ver"
  fi
  sync_shipper "${VERSION}"
  add_provider "${VERSION}"
}

sync_shipper() {
  local VERSION="$1"
  local VERSION_DIR="${TEST_DIR}/${VERSION}"

  cd "${VERSION_DIR}"
  rsync \
    --checksum \
    --archive \
    --exclude=vendor/ \
    --exclude=.git/ \
    --exclude=test/functional \
    "$PROJECT_DIR" \
    vendor/x3tech/laravel-shipper/
}

add_provider() {
  local VERSION="$1"
  local VERSION_DIR="${TEST_DIR}/${VERSION}"

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

test_version() {
  local VERSION="$1"
  local VERSION_DIR="${TEST_DIR}/${VERSION}"

  echo "Testing Laravel ${VERSION}"
  cd "${VERSION_DIR}"
  test_artisan_commands
}

test_artisan_commands() {
  if ./artisan | grep 'shipper:check' > /dev/null; then
    return 0
  else
    ./artisan | head -n2
    return 1
  fi
}

echo_fail() {
  echo -e "\e[31mFAIL\e[0m"
}

echo_pass() {
  echo -e "\e[32mPASS\e[0m"
}

main() {
  local COMMAND="${1:-default}"

  case "$COMMAND" in
    prepare)
      create_version "4.0"
      create_version "4.1"
      create_version "4.2"
      create_version "5.0"
      create_version "5.1"
      ;;
    cleanup)
      cleanup
      ;;
    test)
      test_version "4.0" && echo_pass || echo_fail
      test_version "4.1" && echo_pass || echo_fail
      test_version "4.2" && echo_pass || echo_fail
      test_version "5.0" && echo_pass || echo_fail
      test_version "5.1" && echo_pass || echo_fail
      ;;
    *)
      echo "run.sh (prepare|test|cleanup)"
      exit 64
  esac
}

main "$@"
