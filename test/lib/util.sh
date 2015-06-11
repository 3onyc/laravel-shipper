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

do_test() {
  local version="$1"
  local versionDir="${FUNC_TEST_DIR}/${version}"
  local testFn="$2"

  reset_install "${version}"
  cd "${versionDir}"

  $testFn
}
