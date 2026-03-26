#!/usr/bin/env bash

set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
DEFAULT_ARCHIVE="/Users/mikebrennan/Downloads/site-archive-mpmav2-live-1774301609-3CeMD9wdB71QY4Rxgdip1FpvUPmGU8tvDOsa.zip"
ARCHIVE_PATH="${1:-$DEFAULT_ARCHIVE}"
SYNC_CODE="${SYNC_CODE:-0}"
KEEP_EXTRACTED="${KEEP_EXTRACTED:-0}"

cd "$ROOT_DIR"

if [[ ! -f "$ARCHIVE_PATH" ]]; then
  echo "Archive not found: $ARCHIVE_PATH" >&2
  exit 1
fi

if ! command -v wp >/dev/null 2>&1; then
  echo "wp-cli is required but was not found in PATH." >&2
  exit 1
fi

if ! command -v unzip >/dev/null 2>&1; then
  echo "unzip is required but was not found in PATH." >&2
  exit 1
fi

if ! command -v rsync >/dev/null 2>&1; then
  echo "rsync is required but was not found in PATH." >&2
  exit 1
fi

timestamp="$(date +%Y%m%d-%H%M%S)"
extract_dir="$ROOT_DIR/tmp/staging-import-$timestamp"
backup_file="$ROOT_DIR/tmp/mpma_poc-before-staging-import-$timestamp.sql"
sql_path="$extract_dir/wp-content/mysql.sql"
uploads_path="$extract_dir/wp-content/uploads"

mkdir -p "$ROOT_DIR/tmp" "$extract_dir"

db_name="$(wp config get DB_NAME --type=constant)"
db_user="$(wp config get DB_USER --type=constant)"
db_password="$(wp config get DB_PASSWORD --type=constant)"
db_host="$(wp config get DB_HOST --type=constant)"
local_home="$(wp config get WP_HOME --type=constant)"

mysql_bin="/Applications/XAMPP/xamppfiles/bin/mysql"
mysqldump_bin="/Applications/XAMPP/xamppfiles/bin/mysqldump"

if [[ ! -x "$mysql_bin" ]] || [[ ! -x "$mysqldump_bin" ]]; then
  echo "Expected XAMPP mysql binaries were not found." >&2
  exit 1
fi

mysql_args=()
mysqldump_args=()

if [[ "$db_host" == *:*/* ]]; then
  socket_path="${db_host#*:}"
  mysql_args+=(--socket="$socket_path")
  mysqldump_args+=(--socket="$socket_path")
else
  mysql_args+=(-h "$db_host")
  mysqldump_args+=(-h "$db_host")
fi

mysql_args+=(-u "$db_user")
mysqldump_args+=(-u "$db_user")

if [[ -n "$db_password" ]]; then
  mysql_args+=(-p"$db_password")
  mysqldump_args+=(-p"$db_password")
fi

echo "Backing up local database to $backup_file"
"$mysqldump_bin" "${mysqldump_args[@]}" "$db_name" > "$backup_file"

echo "Extracting database dump from archive"
unzip -q "$ARCHIVE_PATH" "wp-content/mysql.sql" -d "$extract_dir"

if [[ ! -f "$sql_path" ]]; then
  echo "Could not find wp-content/mysql.sql inside archive." >&2
  exit 1
fi

echo "Importing staging database into local database '$db_name'"
"$mysql_bin" "${mysql_args[@]}" "$db_name" < "$sql_path"

echo "Replacing staging URLs with local URL: $local_home"
wp search-replace 'https://mpmav2.wpenginepowered.com' "$local_home" --all-tables --precise
wp search-replace 'http://mpma-poc.local:8080' "$local_home" --all-tables --precise
wp search-replace 'http://mpma-poc.local' "$local_home" --all-tables --precise

if unzip -Z1 "$ARCHIVE_PATH" | grep -q '^wp-content/uploads/'; then
  echo "Extracting uploads from archive"
  unzip -q "$ARCHIVE_PATH" "wp-content/uploads/*" -d "$extract_dir"

  if [[ -d "$uploads_path" ]]; then
    echo "Syncing uploads into local wp-content/uploads"
    mkdir -p "$ROOT_DIR/wp-content/uploads"
    rsync -a --delete "$uploads_path"/ "$ROOT_DIR/wp-content/uploads"/
  fi
else
  echo "No uploads directory found in archive. Skipping uploads sync."
fi

if [[ "$SYNC_CODE" == "1" ]]; then
  echo "Extracting and syncing plugins, themes, mu-plugins, and drop-ins"
  unzip -q "$ARCHIVE_PATH" "wp-content/plugins/*" "wp-content/themes/*" "wp-content/mu-plugins/*" "wp-content/drop-ins/*" -d "$extract_dir"

  for path in plugins themes mu-plugins drop-ins; do
    if [[ -e "$extract_dir/wp-content/$path" ]]; then
      mkdir -p "$ROOT_DIR/wp-content/$path"
      rsync -a --delete "$extract_dir/wp-content/$path"/ "$ROOT_DIR/wp-content/$path"/
    fi
  done
fi

echo "Flushing rewrite rules and cache"
wp rewrite flush --hard
wp cache flush || true

echo "Import complete."
echo "DB backup: $backup_file"
if [[ "$KEEP_EXTRACTED" != "1" ]]; then
  rm -rf "$extract_dir"
  echo "Removed extracted temp files."
else
  echo "Kept extracted temp files at $extract_dir"
fi
