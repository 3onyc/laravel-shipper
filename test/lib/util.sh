#!/bin/bash
echo_fail() {
  echo -e "\e[31mFAIL\e[0m"
}

echo_pass() {
  echo -e "\e[32mPASS\e[0m"
}

get_versions_to_test() {
  local phpVersion="$1"

  case "$phpVersion" in
    5.3.*)
      echo "4.0 4.1"
      ;;
    5.4.*)
      echo "4.0 4.1 4.2 5.0"
      ;;
    5.*|HHVM)
      echo "4.0 4.1 4.2 5.0 5.1"
  esac
}

get_cache_conf_dir() {
  _get_conf_dir "$FUNC_TEST_CACHE_DIR" "$1"
}
get_conf_dir() {
  _get_conf_dir "$FUNC_TEST_DIR" "$1"
}

_get_conf_dir() {
  local prefix="$1"
  local version="$2"
  local versionDir="${prefix}/${version}"

  case "$version" in
    4.*)
      echo "${versionDir}/app/config"
      ;;
    5.*)
      echo "${versionDir}/config"
      ;;
  esac
}

get_cache_conf_file() {
  _get_conf_file "$FUNC_TEST_CACHE_DIR" "$1" "$2"
}
get_conf_file() {
  _get_conf_file "$FUNC_TEST_DIR" "$1" "$2"
}

_get_conf_file() {
  local prefix="$1"
  local version="$2"
  local fileName="$3"

  echo "$(_get_conf_dir "${prefix}" "${version}")/${fileName}"
}

do_test() {
  local version="$1"
  local versionDir="${FUNC_TEST_DIR}/${version}"
  local testFn="$2"

  reset_install "${version}"
  cd "${versionDir}"

  $testFn "${version}"
}

fix_db_conf() {
  local version="$1"

  if [ -f ".env" ]; then
    sed -i "s/=localhost/=db/g" ".env"
  else
    sed -i "s/=> 'localhost'/=> 'db'/g" "$(get_conf_file "${version}" "database.php")"
  fi
}
