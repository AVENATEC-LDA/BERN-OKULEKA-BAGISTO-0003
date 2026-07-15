# Dokploy Deployment for Bagisto

This repository includes a Dokploy deployment manifest that builds the Bagisto production image and deploys it with persistent storage.

## Files
- `docker-compose.dokploy.yml` — Dokploy-ready Docker Compose configuration
- `.env.dokploy.example` — sample environment file for Dokploy deployment

## Deployment strategy

- Uses the `docker/production/nginx/Dockerfile` build path.
- Deploys Bagisto with the built-in MySQL service inside the same container.
- Persists data with two named volumes:
  - `bagisto-mysql` → `/var/lib/mysql`
  - `bagisto-storage` → `/var/www/bagisto/storage`
- Uses `env_file: .env` so Dokploy environment variables are injected into the container.

## How to deploy

1. Create a new Dokploy application.
2. Select **Docker Compose** deployment.
3. Point Dokploy to this repository and set the compose file path to `docker-compose.dokploy.yml`.
4. Add environment variables in Dokploy or copy `.env.dokploy.example` to `.env` and edit values locally.
   - Be sure to set `APP_URL` to your deployed domain.
   - Keep `DB_HOST=127.0.0.1` to use the container's internal MySQL.
   - Set `APP_DEBUG=false` for production.
5. Deploy the application.

## Recommended Dokploy settings

- Use Docker Compose mode, not Docker Stack, because the image build relies on `build:` support.
- Configure a domain through Dokploy domains or use a generated `traefik.me` domain.
- If Dokploy creates a `.env` file automatically, the `env_file` setting in `docker-compose.dokploy.yml` will load those variables into the container.

## Notes

- This deployment uses the Bagisto production image build process, which installs Laravel, runs migrations, seeds data, and caches config during image build.
- The runtime container does not run database migrations automatically for an external database. To keep deployment simple, this manifest uses the built-in internal MySQL mode.
- For a prebuilt image deployment, you can replace `build:` with a `image:` stanza using `webkul/bagisto:<version>` or `webkul/bagisto:latest`.

## Example `docker-compose.dokploy.yml`

The manifest builds from `docker/production` and exposes port `80`.

```yaml
version: '3.9'

services:
  bagisto:
    build:
      context: ./docker/production
      dockerfile: nginx/Dockerfile
      args:
        BAGISTO_VERSION: ${BAGISTO_VERSION:-v2.4.7}
        PHP_VERSION: ${PHP_VERSION:-8.3}
    image: bagisto-dokploy:latest
    env_file:
      - .env
    ports:
      - '80:80'
    volumes:
      - bagisto-mysql:/var/lib/mysql
      - bagisto-storage:/var/www/bagisto/storage
    restart: unless-stopped

volumes:
  bagisto-mysql:
  bagisto-storage:
```
