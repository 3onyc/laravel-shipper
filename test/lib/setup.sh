#!/bin/bash
set -euo pipefail
[ -n "${DEBUG:-}" ] && set -x

cleanup() {
  rm -rf "${FUNC_TEST_DIR}"
}

create_version() {
  local VERSION="$1"
  local VERSION_DIR="${FUNC_TEST_DIR}/${VERSION}"

  echo "Creating test env for Laravel ${VERSION}..."

  create_project "${VERSION}"
  install_shipper "${VERSION}"
  sync_shipper "${VERSION}"
  add_provider "${VERSION}"
}

create_project() {
  local VERSION="$1"
  local VERSION_DIR="${FUNC_TEST_DIR}/${VERSION}"

  if [ ! -d "${VERSION_DIR}/vendor" ]; then
    $COMPOSER_BIN create-project laravel/laravel "${VERSION_DIR}" "${VERSION}" #--prefer-source --keep-vcs
  fi
}

install_shipper() {
  local VERSION="$1"
  local VERSION_DIR="${FUNC_TEST_DIR}/${VERSION}"

  cd "${VERSION_DIR}"
  if [ ! -d vendor/x3tech/laravel-shipper ]; then
    $COMPOSER_BIN require --prefer-source "x3tech/laravel-shipper dev-${BRANCH}"
  fi
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
      add_provider_4 "${VERSION}"
      ;;
    5.0)
      add_provider_50 "${VERSION}"
      ;;
    5.1)
      add_provider_51 "${VERSION}"
      ;;
  esac
}

add_provider_4() {
  local VERSION="$1"
  local VERSION_DIR="${FUNC_TEST_DIR}/${VERSION}"

  local CONFIG_FILE="${VERSION_DIR}/app/config/app.php"
  if ! grep 'ShipperProvider' "${CONFIG_FILE}" > /dev/null; then
    sed -i \
      "s/WorkbenchServiceProvider',/WorkbenchServiceProvider', 'x3tech\\\\LaravelShipper\\\\Provider\\\\ShipperProvider',/g" \
      "${CONFIG_FILE}"
  fi
}

add_provider_50() {
  local VERSION="$1"
  local VERSION_DIR="${FUNC_TEST_DIR}/${VERSION}"

  local CONFIG_FILE="${VERSION_DIR}/config/app.php"
  if ! grep 'ShipperProvider' "${CONFIG_FILE}" > /dev/null; then
    sed -i \
      "s/RouteServiceProvider',/RouteServiceProvider', 'x3tech\\\\LaravelShipper\\\\Provider\\\\ShipperProvider',/g" \
      "${CONFIG_FILE}"
  fi
}

add_provider_51() {
  local VERSION="$1"
  local VERSION_DIR="${FUNC_TEST_DIR}/${VERSION}"

  local CONFIG_FILE="${VERSION_DIR}/config/app.php"
  if ! grep 'ShipperProvider' "${CONFIG_FILE}" > /dev/null; then
    sed -i \
      "s/RouteServiceProvider::class,/RouteServiceProvider::class, 'x3tech\\\\LaravelShipper\\\\Provider\\\\ShipperProvider',/g" \
      "${CONFIG_FILE}"
  fi
}
