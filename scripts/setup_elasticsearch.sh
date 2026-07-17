#!/usr/bin/env bash
set -euo pipefail

# Script to run inside the application container to verify Elasticsearch and perform reindex.
# Usage: copy into container and run: bash /path/to/setup_elasticsearch.sh

ES_HOST=${ELASTICSEARCH_HOST:-http://elasticsearch:9200}

echo "Checking Elasticsearch at ${ES_HOST}"
if ! curl -sS "${ES_HOST}/_cluster/health" >/dev/null; then
  echo "Elasticsearch is not reachable at ${ES_HOST}" >&2
  exit 2
fi

echo "Clearing config cache and optimizing..."
php artisan config:cache
php artisan optimize:clear

echo "Running migrations..."
php artisan migrate --force

echo "Setting search engine to elastic (if not set)"
php -r "require 'vendor/autoload.php';\nuse Illuminate\Support\Facades\DB;\nif (DB::table('core_config_data')->where('code', 'catalog.products.search.engine')->exists()) { DB::table('core_config_data')->updateOrInsert(['code'=>'catalog.products.search.engine'], ['value'=>'elastic']); }"

echo "Starting full reindex for elastic..."
php artisan indexer:index --type=elastic --mode=full

echo "Reindex of all indexers (inventory, price, flat, elastic)"
php artisan indexer:index --type=inventory,price,flat,elastic --mode=full || true

echo "Restarting queue workers"
php artisan queue:restart || true

echo "Done. Check indices with: curl ${ES_HOST}/_cat/indices?v"
