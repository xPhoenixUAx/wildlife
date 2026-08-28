# Wildlife Match

Production-ready static/PHP website for an independent wildlife-service referral platform. The front end uses semantic HTML5, CSS3 and vanilla JavaScript. There is no build step.

## Publish configuration

Before publishing, replace the visibly marked values in:

- `config/site-config.js`: legal operator display name, corporate email, postal address, client-side destination-email reference and any footer link changes.
- `config/server-config.php`: the private `FORM_DESTINATION_EMAIL` used by the PHP mail handler. This must remain server-side and should not be committed with a private production address if the repository is public.

The legal policies intentionally read these operator-controlled values. Do not publish with a `CONFIGURE` marker or the example email address.

## Local testing

Serve the `starter/` directory from a PHP-capable web server; opening the HTML files directly cannot test sessions, CSRF protection or `handler.php`.

Example with PHP 8.1 or newer:

```sh
php -S 127.0.0.1:8080 -t starter
```

Then open `http://127.0.0.1:8080/index.html`.

For end-to-end form delivery, configure a working server mail transport. The handler accepts POST only, validates and randomizes up to three JPG/PNG/WebP attachments of 5 MB each, applies session/IP rate limits, and returns JSON to JavaScript or a safe `303` redirect without JavaScript.

## Site behavior

- All providers are independent; the website makes no guarantee of introduction, response or availability.
- No phone contact element or phone field is used.
- Cookie controls store only the user’s preference. No optional analytics or advertising scripts are currently loaded.
- The generated photography is local WebP. Asset provenance is listed in `IMAGE_CREDITS.md`.
