# Render Web-Only Blueprint

Use this Blueprint when your Render account already has an active free Postgres database.

Blueprint file:

```text
render-web-only.yaml
```

It creates only the web service:

```text
pards-capstone
```

Use the existing Render Postgres database connection string for both:

```dotenv
DB_URL=postgres://...
DATABASE_URL=postgres://...
```

Also set:

```dotenv
APP_KEY=base64:kRKA1KIcpVvFxCOKBiFto2DbX11/HrierHQI/djzo5A=
APP_URL=https://pards-capstone.onrender.com
```

If Render gives the service a different URL, update `APP_URL` to match the real URL and redeploy.
