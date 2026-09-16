# Render Blueprint V2

Use this file if you want to create a fresh Render Blueprint:

```text
render-blueprint-v2.yaml
```

It creates:

- Web service: `rblz-capstone-v2`
- Postgres database: `rblz-capstone-v2-db`

When Render asks for secret values, enter:

```dotenv
APP_KEY=base64:kRKA1KIcpVvFxCOKBiFto2DbX11/HrierHQI/djzo5A=
APP_URL=https://rblz-capstone-v2.onrender.com
```

After the first deploy, open the web service settings and check the actual Render URL. If Render gives a slightly different URL, update `APP_URL` to match it and redeploy.

For Gmail email sending, replace `MAIL_MAILER=log` with these service environment variables:

```dotenv
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=yourgmail@gmail.com
MAIL_PASSWORD=your_google_app_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=yourgmail@gmail.com
MAIL_FROM_NAME="${APP_NAME}"
```
