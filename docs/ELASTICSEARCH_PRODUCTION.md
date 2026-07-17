## Elasticsearch — Produção

Este documento descreve os passos necessários para habilitar e configurar Elasticsearch em produção (Dokploy / Docker Compose).

Passos resumidos:

1. Ajustar variáveis de ambiente (Dokploy):
   - `ELASTICSEARCH_HOST` → `http://elasticsearch:9200` (quando usando o serviço no compose)
   - `ELASTICSEARCH_USER`, `ELASTICSEARCH_PASS` (se aplicável)
   - `ELASTICSEARCH_API_KEY` (se usar API Key)

2. Garantir que o `docker-compose.dokploy.yml` inclua o serviço `elasticsearch` (o repositório já contém uma configuração de exemplo).

3. Deploy da aplicação via Dokploy (ou equivalente):
   - Ao finalizar o deploy, abra um terminal/console no container da aplicação para executar os comandos abaixo.

4. Comandos de inicialização (executar dentro do container da aplicação):

```bash
# verificar saúde do Elasticsearch (substitua a URL se necessário)
curl -sS ${ELASTICSEARCH_HOST:-http://elasticsearch:9200}/_cluster/health | jq

# limpar cache/config
php artisan config:cache
php artisan optimize:clear

# rodar migrations se necessário
php artisan migrate --force

# definir engine de busca para elastic (opcional via SQL se preferir)
# UPDATE core_config_data SET value = 'elastic' WHERE code = 'catalog.products.search.engine';

# executar reindex completo dos índices dependentes (produto/price/inventory/flat/elastic)
php artisan indexer:index --type=elastic --mode=full

# opcional: reindexar todos os indexers
php artisan indexer:index --type=inventory,price,flat,elastic --mode=full

# reiniciar workers/queues conforme configuração de produção (Supervisor / systemd)
php artisan queue:restart

```

5. Verificações pós-indexação:
   - Verifique que índices foram criados: `curl ${ELASTICSEARCH_HOST}/_cat/indices?v`
   - Teste buscas na loja e sugestões no frontend.

Dicas / notas:
- Em produção, configure `ES_JAVA_OPTS` e memória conforme disponibilidade do servidor.
- Habilite autenticação/SSL se o cluster for acessível publicamente.
- Para catálogos grandes, execute reindex em janela de baixa carga e monitore uso de CPU/RAM.
