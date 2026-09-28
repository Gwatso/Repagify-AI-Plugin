# Contributing to Repagify

Thanks for taking an interest. This document covers getting a local
environment running and the standards a patch is expected to meet.

## Local environment

You need a WordPress install running **WordPress 6.0 or newer** on
**PHP 7.4 or newer**. Any of these will do:

- **[Local](https://localwp.com/)** — simplest on Windows and macOS. Create a
  site, then clone this repository into
  `app/public/wp-content/plugins/repagify-plugin`.
- **[wp-env](https://developer.wordpress.org/block-editor/reference-guides/packages/packages-env/)** —
  `npx wp-env start` from the repository root.
- **[LocalWP alternatives](https://make.wordpress.org/core/handbook/tutorials/installing-a-local-server/)** —
  MAMP, XAMPP, Docker, or a plain LAMP stack all work.

Then activate the plugin from **Plugins → Installed Plugins**.

The archive scanner works immediately with no account and no network access.
To work on the generation flow you need a Repagify API key, which you add
under **Repagify → Settings**.

### Testing without spending generations

Generation calls cost against your plan's quota. When working on anything that
is not the API client itself, intercept the request rather than making it:

```php
add_filter( 'pre_http_request', function ( $pre, $args, $url ) {
    if ( false === strpos( $url, '/generate' ) ) {
        return $pre;
    }

    return array(
        'headers'  => array(),
        'body'     => wp_json_encode( array(
            'content'    => 'Mocked output.',
            'word_count' => 2,
        ) ),
        'response' => array( 'code' => 200, 'message' => 'OK' ),
        'cookies'  => array(),
        'filename' => null,
    );
}, 10, 3 );
```

Note that every filter on `pre_http_request` runs, so pass a non-`false`
`$pre` straight through or you will clobber another mock.

## Coding standards

This project follows the **[WordPress PHP Coding Standards](https://developer.wordpress.org/coding-standards/wordpress-coding-standards/php/)** (WPCS), not PSR-12.
In particular:

- Tabs for indentation, spaces for alignment
- Yoda conditions: `if ( 'publish' === $status )`
- Spaces inside parentheses: `foo( $bar )`
- `snake_case` for functions and variables, `Class_Name` for classes
- A docblock on every class, method and function

Install the sniffs and run them before opening a pull request:

```
composer global require wp-coding-standards/wpcs --dev
phpcs --standard=WordPress .
```

### Non-negotiables

These are WordPress.org review rejection reasons. A patch that breaks one will
not be merged:

- **Prefixing.** Every function, class, constant, option, hook and transient
  starts with `repagify_` / `Repagify_` / `REPAGIFY_`.
- **Nonces.** Every form POST and every AJAX handler verifies one.
- **Capabilities.** Every admin action checks `current_user_can()`.
- **Sanitize on input, escape on output.** No exceptions, including for values
  you believe are safe.
- **`ABSPATH` guard** at the top of every PHP file.
- **The API key never leaves the HTTP client.** It is never echoed, logged,
  put in a page attribute, or included in an error message.
- **The WordPress HTTP API only.** `wp_remote_get()` / `wp_remote_post()`,
  never cURL directly, and every call goes through
  `includes/class-repagify-api.php`.
- **No new dependencies.** No Composer packages shipped, no npm, no React, no
  bundled fonts or icon libraries. Dashicons ship with core and are the only
  icons used.

### Things this plugin deliberately does not do

Please do not send patches adding these:

- **Bulk generation.** Each generation takes 20 to 45 seconds and costs one of
  the user's quota. A "repurpose everything" button would spend a free
  account's whole allowance in a single click.
- **Telemetry, analytics or version pings.** The plugin makes no outbound
  request except the two the user explicitly triggers.
- **Modifying the user's posts.** The plugin reads content and writes one post
  meta key recording what has been repurposed. It never edits or publishes.

## Internationalisation

Text domain is `repagify`, loaded from `/languages`. Wrap every user-facing
string, use `printf`-style placeholders rather than concatenation, and add a
`translators:` comment wherever a placeholder's meaning is not obvious:

```php
printf(
    /* translators: %s: formatted word count. */
    esc_html__( '%s words will be sent.', 'repagify' ),
    esc_html( number_format_i18n( $words ) )
);
```

Strings needed in JavaScript are passed through `wp_localize_script()`. Never
hardcode user-facing English in a `.js` file.

Regenerate the translation template after changing any string:

```
wp i18n make-pot . languages/repagify.pot
```

## Pull requests

- One concern per pull request.
- Say what you changed and why, and how you verified it.
- If you touched anything that talks to the API, say whether you tested
  against the live service or a mock.
- New user-facing strings need to be translatable and need to be in the
  regenerated `.pot`.

## Reporting a security issue

Please do not open a public issue for a security problem. Email
**hello@repagify.afriflare.com** with the details and give us a reasonable
window to ship a fix before disclosing.
