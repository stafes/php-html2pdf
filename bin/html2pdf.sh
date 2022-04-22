#!/bin/sh
# html2pdf.sh
#
# -----------------------------------------------------------------------------
# Purpose : Html to PDF
# -----------------------------------------------------------------------------
#
# Description :
#
# Usage :
#   $ html2pdf.sh --url="http://example.co.jp/" --savepath="s3://example/example.pdf" --option=""
#
# -----------------------------------------------------------------------------
# Set options
set -eu

APP_NAME="html2pdf"
SCRIPT_DIR=`dirname $0`
ROOT_DIR="$SCRIPT_DIR/.."
TMP_DIR="/tmp/${APP_NAME}"
NOW_STR=`date '+%Y%m%d%H%M%S'`
UUID=$(uuidgen)
TMP_FILE="${TMP_DIR}/${NOW_STR}_${UUID}.pdf"

# mkdir if TMP_DIR not exists.
[ ! -e $TMP_DIR ] && mkdir -p $TMP_DIR

DRY_RUN=0
ARG_URL=""
ARG_SAVE_PATH=""
ARG_OPTION=""

for STR in "$@"
do
  opt=(`echo ${STR} | tr -s '=' ' '`)
  case "${opt[0]}" in
    '--url' )
        # Required
        if [[ -z "${opt[1]}" ]] || [[ "${opt[1]}" =~ ^-+ ]]; then
            echo "${APP_NAME} failed: option ${opt[0]} requires an argument." 1>&2
            exit 1
        fi
        ARG_URL="${opt[1]}"
        ;;
    '--savepath' )
        # Required
        if [[ -z "${opt[1]}" ]] || [[ "${opt[1]}" =~ ^-+ ]]; then
            echo "${APP_NAME} failed: option ${opt[0]} requires an argument." 1>&2
            exit 1
        fi
        ARG_SAVE_PATH="${opt[1]}"
        ;;
    '--option' )
        ARG_OPTION="${opt[1]}"
        ;;
    '--dry-run' )
        DRY_RUN=1
        ;;
    -*)
        echo "${APP_NAME} failed: illegal option -- '$(echo ${opt[0]} | sed 's/^-*//')'" 1>&2
        exit 1
        ;;
    esac
done

GET_SCRIPT="${SCRIPT_DIR}/application.php ${APP_NAME}:gethtml --url=${ARG_URL}"
SCRIPT="${SCRIPT_DIR}/application.php ${APP_NAME}:run" 

if [ ! -z "${ARG_OPTION}" ]; then
  SCRIPT="${SCRIPT} --option \"${ARG_OPTION}\""
fi

php ${GET_SCRIPT} | php ${SCRIPT} > "${TMP_FILE}"

if [ $? != "0" ]; then
  echo "ERROR: ${APP_NAME} failed." 1>&2
  exit 1
fi

# exit if dry-run
if [ $DRY_RUN = 1 ]; then
  exit 0
fi

# cp to s3
aws s3 cp "${TMP_FILE}" "${ARG_SAVE_PATH}"

if [ $? != "0" ]; then
  echo "ERROR: ${APP_NAME} failed : s3 cp ${TMP_FILE} to ${ARG_SAVE_PATH} failed." 1>&2
  exit 1
fi

# remove tmp file if success
rm -r ${TMP_FILE}

exit 0