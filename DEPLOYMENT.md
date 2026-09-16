# Deployment

This project is prepared for GitHub-based deployment to Render and Railway.

## Required production variables

Generate `APP_KEY` locally with:

```bash
php artisan key:generate --show
```

Use these variables on Render or Railway:

```dotenv
APP_NAME=RBLZCapstone
APP_ENV=production
APP_DEBUG=false
APP_KEY=base64:...
APP_URL=https://your-production-url
DB_CONNECTION=pgsql
DB_URL=your-postgres-connection-url
DATABASE_URL=your-postgres-connection-url
SESSION_DRIVER=database
QUEUE_CONNECTION=database
CACHE_STORE=database
LOG_CHANNEL=stderr
RUN_MIGRATIONS=true
```

## Render

1. Push the repo to GitHub.
2. In Render, create a Blueprint from `render.yaml`, or create a new Docker Web Service from the repo.
3. Create/attach a Render Postgres database.
4. Add `APP_KEY` when Render asks for secret values.
5. Deploy.

Render's Laravel guide uses Docker plus Postgres. This repo includes a `Dockerfile` and `render.yaml` for that path.

## Railway

1. Push the repo to GitHub.
2. In Railway, create a new project and deploy from the GitHub repo.
3. Add a Postgres database service.
4. Set:

```dotenv
APP_KEY=base64:...
DB_CONNECTION=pgsql
DB_URL=${{Postgres.DATABASE_URL}}
DATABASE_URL=${{Postgres.DATABASE_URL}}
LOG_CHANNEL=stderr
```

5. Generate a public domain from the service Networking settings.

Railway can use the Dockerfile automatically. The `railway/` scripts are included if you later split workers or cron into separate services.
