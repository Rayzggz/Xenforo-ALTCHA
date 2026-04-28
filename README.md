# ALTCHA for XenForo
A XenForo add-on that integrates the [ALTCHA](https://altcha.org/) human verification system, providing a modern, privacy-friendly CAPTCHA alternative that offers better protection against bots with a smooth user experience.

# 🚀 Installation
Download and install the add-on from the XenForo Resource page:
[https://xenforo.com/community/resources/roi-altacha-captcha.10127/](https://xenforo.com/community/resources/roi-altacha-captcha.10127/)


# 🧩 Notes
This add-on uses ALTCHA Server Integration with the “Verifying without Sentinel” mode.
Thanks to this, it works completely independently and it is completely free to use.
It does not depend on, require, or support ALTCHA Sentinel.

Hence, this add-on does not support the advanced features provided by ALTCHA Sentinel.

# ⚙️ Configuration

Admin Control Panel -> Setup -> Options -> User registration -> Enable CAPTCHA for guests

Private Secret Key: Your ALTCHA private secret key. This key used to HMAC sign the verification requests. Make sure to keep it secure and do not share it publicly.

Advanced Options:

Admin Control Panel -> Setup -> Options -> [Roi] ALTCHA CAPTCHA

# 🧰 Compatibility

Developed and tested for XenForo 2.3.3 and PHP 8.3.14

Technically compatible with XenForo 2.2+ and PHP 8.2+ but not officially tested.

# ✨ Acknowledgements
- [ALTCHA](https://altcha.org/)
- [altcha-lib-php](https://github.com/altcha-org/altcha-lib-php)

# 📄 License
This add-on is licensed under the MIT License. See the LICENSE file for more details.