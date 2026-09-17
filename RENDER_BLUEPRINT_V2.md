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

Render Free blocks outbound SMTP ports. Use the Brevo HTTPS API for registration OTP email:

```dotenv
MAIL_MAILER=log
MAIL_FROM_ADDRESS=yourgmail@gmail.com
MAIL_FROM_NAME=PARDS
BREVO_API_KEY=your_brevo_api_key
```

Register and verify `MAIL_FROM_ADDRESS` as a sender in Brevo before deploying the API key.
