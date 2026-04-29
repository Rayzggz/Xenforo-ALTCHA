<?php

namespace Roi\ALTCHACAPTCHA\Captcha;

use AltchaOrg\Altcha\Altcha;
use AltchaOrg\Altcha\Challenge;
use AltchaOrg\Altcha\ChallengeParameters;
use AltchaOrg\Altcha\CreateChallengeOptions;
use AltchaOrg\Altcha\ServerSignature;
use AltchaOrg\Altcha\Solution;
use AltchaOrg\Altcha\VerifySolutionOptions;
use AltchaOrg\Altcha\Payload;
use AltchaOrg\Altcha\Algorithm\Pbkdf2;

use XF\Captcha\AbstractCaptcha;
use XF\Template\Templater;
use XF\App;

class ALTCHACAPTCHA extends AbstractCaptcha
{
    /**
     * ALTCHA HMAC Key
     *
     * @var null|string
     */
    protected $altchaHmacKey = null;

    protected $altchajsurl = "https://cdn.jsdelivr.net/gh/altcha-org/altcha@v3.0.2/dist/main/altcha.min.js";

    protected $altchai18njsurl = null;

    protected $altchaCost = 50000;

    protected $hideLogo = 'false';

    protected $hideFooter = 'false';

    protected $styleType = 'checkbox';

    public function __construct(App $app)
    {
        parent::__construct($app);
        $options = $app->options();
        $extraKeys = $options->extraCaptchaKeys;
        if (!empty($extraKeys['altchaHmacKey']))
        {
            $this->altchaHmacKey = $extraKeys['altchaHmacKey'];
        }

        if (!empty($options->roi_altchacaptcha_cdn_widget_js)) {
            $this->altchajsurl = $options->roi_altchacaptcha_cdn_widget_js;
        }

        if (!empty($options->roi_altchacaptcha_setting_cost)) {
            $this->altchaCost = $options->roi_altchacaptcha_setting_cost;
        }

        if (!empty($options->roi_altchacaptcha_cdn_i18n_js)) {
            $this->altchai18njsurl = $options->roi_altchacaptcha_cdn_i18n_js;
        }

        $this->hideLogo = $options->roi_altchacaptcha_style_hideLogo ? 'true' : 'false';

        $this->hideFooter = $options->roi_altchacaptcha_style_hideFooter ? 'true' : 'false';


        if (!empty($options->roi_altchacaptcha_style_type)) {
            $this->styleType = $options->roi_altchacaptcha_style_type;
        }
    }

    public function renderInternal(Templater $templater)
    {
        if (!$this->altchaHmacKey)
        {
            return '';
        }

        $pbkdf2 = new Pbkdf2();

        $altcha = new Altcha(
            hmacSignatureSecret: $this->altchaHmacKey,
            //hmacKeySignatureSecret: 'key-secret', //TODO
        );

        $challenge = $altcha->createChallenge(new CreateChallengeOptions(
            algorithm: $pbkdf2,
            cost: $this->altchaCost,
            expiresAt: new \DateTimeImmutable('+5 minutes'),
        ));


        $challenge = json_encode($challenge);


        return $templater->renderTemplate('public:roi_altchacaptcha_captcha', [
            'type' => $this->styleType,
            'altchajsurl' => $this->altchajsurl,
            'altchai18njsurl' => $this->altchai18njsurl,
            'challenge' => $challenge,
            'configuration' => $this->buildConfig(),
        ]);
    }

    public function isValid()
    {
        if (!$this->altchaHmacKey)
        {
            return true; // if not configured, always pass
        }

        $request = $this->app->request();

        $rawCaptchaResponse = $request->filter('altcha', 'str');
        if (!$rawCaptchaResponse)
        {
            return false;
        }

        $decoded = base64_decode($rawCaptchaResponse, true);
        if ($decoded === false)
        {
            return false;
        }

        try
        {
            $captchaResponse = json_decode($decoded, true, 512, JSON_THROW_ON_ERROR);
        }
        catch (\JsonException $e)
        {
            return false;
        }

        if (!is_array($captchaResponse))
        {
            return false;
        }

        if (
            !isset(
                $captchaResponse['challenge']['parameters'],
                $captchaResponse['challenge']['signature'],
                $captchaResponse['solution']['counter'],
                $captchaResponse['solution']['derivedKey'],
                $captchaResponse['solution']['time']
            )
        )
        {
            return false;
        }

        if (
            !is_array($captchaResponse['challenge']['parameters'])
            || !is_string($captchaResponse['challenge']['signature'])
            || !is_string($captchaResponse['solution']['derivedKey'])
        )
        {
            return false;
        }

        try
        {
            $altcha = new Altcha(
                hmacSignatureSecret: $this->altchaHmacKey,
            //hmacKeySignatureSecret: 'key-secret', //TODO
            );

            $pbkdf2 = new Pbkdf2();

            $challengeParam =  ChallengeParameters::fromArray($captchaResponse['challenge']['parameters']);
            $challenge = new Challenge($challengeParam, $captchaResponse['challenge']['signature']);


            $solution = new Solution($captchaResponse['solution']['counter'], $captchaResponse['solution']['derivedKey'], $captchaResponse['solution']['time']);


            $payload = new Payload($challenge, $solution);
            $result = $altcha->verifySolution(new VerifySolutionOptions(
                payload: $payload,
                algorithm: $pbkdf2,
            ));

            return $result->verified && !$result->expired;


        }
        catch (\Exception $e)
        {
            // this is an exception with the underlying request, so let it go through
            \XF::logException($e, false, 'ALTCHA CAPTCHA error: ');
            return true;
        }
    }

    private function buildConfig()
    {
        return json_encode([
            'hideFooter' => $this->hideFooter === 'true',
            'hideLogo' => $this->hideLogo === 'true',
        ], JSON_UNESCAPED_SLASHES);
    }

}
