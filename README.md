# ALTCHA CAPTCHA for XenForo

A XenForo add-on that integrates [ALTCHA](https://altcha.org/) as a privacy-friendly CAPTCHA alternative.

ALTCHA uses a proof-of-work challenge instead of image selection or third-party tracking. This add-on uses ALTCHA server-side verification without ALTCHA Sentinel, so it can run independently and does not require a paid ALTCHA Sentinel account.

## 🚀 Installation

Download and install the add-on from the XenForo Resource Manager:

[https://xenforo.com/community/resources/roi-altacha-captcha.10127/](https://xenforo.com/community/resources/roi-altacha-captcha.10127/)

After installing the add-on, enable it from the XenForo Admin Control Panel.

## ⚙️ Basic Configuration

Go to:

`Admin Control Panel -> Setup -> Options -> User registration -> Enable CAPTCHA for guests`

Select ALTCHA CAPTCHA and configure the required key.

### Private Secret Key

The Private Secret Key is the ALTCHA HMAC secret used by your server to sign and verify CAPTCHA challenges.

Use a long, random string here. This value is not provided by ALTCHA and does not need to come from an external account. You can generate your own random secret and paste it into the field.

Keep this key private. Do not publish it, commit it to a public repository, or share it with users. If the key is leaked, generate a new random key and replace the old one.

Example format:

```text
0378b0f84c4310279918d71a5647ba5d
```

## ⚙️ Advanced Configuration

Go to:

`Admin Control Panel -> Setup -> Options -> [Roi] ALTCHA CAPTCHA`

Available options:

- `Self-Hosted Widget JS URL`: Optional. Use this if you want to load the ALTCHA widget JavaScript from your own server or CDN.
- `Internationalization JS URL`: Optional. Use this if you want to load a custom ALTCHA language file.
- `Cost`: Controls the proof-of-work difficulty. A higher value makes bots work harder, but also increases the time needed by real users' browsers.
- `Checkbox & Switch`: Controls the ALTCHA widget display type.
- `Hides the ALTCHA logo icon`: Hides the ALTCHA logo in the widget.
- `Hides the ALTCHA attribution link`: Hides the ALTCHA footer attribution link.

The default cost is `50000`, which is a reasonable starting point for most forums. If users report that CAPTCHA takes too long, lower the value. If spam protection is too weak, raise it gradually.

## 🧩 Notes

This add-on uses ALTCHA server integration in "verifying without Sentinel" mode.

Because of this:

- It works independently.
- It is free to use.
- It does not require ALTCHA Sentinel.
- It does not support advanced ALTCHA Sentinel features.

## 🧰 Compatibility

Developed and tested with:

- XenForo 2.3.3
- PHP 8.3.14

Expected compatibility:

- XenForo 2.2+
- PHP 8.2+

## ✨ Acknowledgements

- [ALTCHA](https://altcha.org/)
- [altcha-lib-php](https://github.com/altcha-org/altcha-lib-php)

## License
This add-on is licensed under the MIT License. See the LICENSE file for more details.