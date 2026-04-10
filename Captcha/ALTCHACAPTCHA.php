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

    protected $altchajsurl = "https://cdn.jsdelivr.net/gh/altcha-org/altcha@v3.0.0/dist/main/altcha.min.js";

    protected $altchai18njsurl = null;

    protected $altchaCost = 50000;
    public function __construct(App $app)
    {
        parent::__construct($app);
        $extraKeys = $app->options()->extraCaptchaKeys;
        if (!empty($extraKeys['altchaHmacKey']))
        {
            $this->altchaHmacKey = $extraKeys['altchaHmacKey'];
        }

        if (!empty($extraKeys['altchajsurl'])) {
            $this->altchajsurl = $extraKeys['altchajsurl'];
        }

        if (!empty($extraKeys['altchaCost'])) {
            $this->altchaCost = $extraKeys['altchaCost'];
        }

        if (!empty($extraKeys['altchai18njsurl'])) {
            $this->altchai18njsurl = $extraKeys['altchai18njsurl'];
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
            'altchajsurl' => $this->altchajsurl,
            'altchai18njsurl' => $this->altchai18njsurl,
            'challenge' => $challenge,
        ]);
    }

    public function isValid()
    {
        if (!$this->altchaHmacKey)
        {
            return true; // if not configured, always pass
        }

        $request = $this->app->request();

        $captchaResponse = $request->filter('altcha', 'str');
        if (!$captchaResponse)
        {
            return false;
        }

        try
        {
            $altcha = new Altcha(
                hmacSignatureSecret: $this->altchaHmacKey,
            //hmacKeySignatureSecret: 'key-secret', //TODO
            );

            $captchaResponse = json_decode(base64_decode($captchaResponse), true);
            $pbkdf2 = new Pbkdf2();

            $challengeParam =  ChallengeParameters::fromArray($captchaResponse['challenge']['parameters']);
            $challenge = new Challenge($challengeParam, $captchaResponse['challenge']['signature']);


            $solution = new Solution($captchaResponse['solution']['counter'], $captchaResponse['solution']['derivedKey'], $captchaResponse['solution']['time']);


            $payload = new Payload($challenge, $solution);
            $result = $altcha->verifySolution(new VerifySolutionOptions(
                payload: $payload,
                algorithm: $pbkdf2,
            ));

            var_dump($result);

            return $result->verified && !$result->expired;


        }
        catch (\Exception $e)
        {
            // this is an exception with the underlying request, so let it go through
            \XF::logException($e, false, 'ALTCHA CAPTCHA error: ');
            return true;
        }
    }

}
