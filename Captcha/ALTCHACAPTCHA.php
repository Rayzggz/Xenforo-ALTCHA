<?php

namespace Roi\ALTCHACAPTCHA\Captcha;

use AltchaOrg\Altcha\ChallengeOptions;
use AltchaOrg\Altcha\Altcha;
use AltchaOrg\Altcha\Hasher\Algorithm;
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
    public function __construct(App $app)
    {
        parent::__construct($app);
        $extraKeys = $app->options()->extraCaptchaKeys;
        if (!empty($extraKeys['altchaHmacKey']))
        {
            $this->altchaHmacKey = $extraKeys['altchaHmacKey'];
        }
    }

    public function renderInternal(Templater $templater)
    {
        if (!$this->altchaHmacKey)
        {
            return '';
        }

        $altcha = new Altcha($this->altchaHmacKey);

        $options = new ChallengeOptions(
            algorithm: Algorithm::SHA512,
            maxNumber: 3000000, // the maximum random number
            expires: (new \DateTimeImmutable())->add(new \DateInterval('PT5M')),
            saltLength: 32, // Length of the salt in bytes
        );

        $challenge = json_encode($altcha->createChallenge($options));


        return $templater->renderTemplate('public:roi_altchacaptcha_captcha', [
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
            $altcha = new Altcha($this->altchaHmacKey);

            $isValid = $altcha->verifySolution($captchaResponse);

            if ($isValid) {
                return true;
            } else {
                return false;
            }
        }
        catch (\Exception $e)
        {
            // this is an exception with the underlying request, so let it go through
            \XF::logException($e, false, 'ALTCHA CAPTCHA error: ');
            return true;
        }
    }

}
