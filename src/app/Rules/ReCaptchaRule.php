<?php
/**
 * Laravel reCAPTCHA
 * Author: ZanySoft
 * Web: www.zanysoft.net
 */

namespace ZanySoft\ReCaptcha\app\Rules;

use Illuminate\Contracts\Validation\Rule;
use ZanySoft\ReCaptcha\ReCaptcha;

class ReCaptchaRule implements Rule
{
    /**
     * Determine if the validation rule passes.
     * Call out to reCAPTCHA and process the response.
     *
     * @param string $attribute
     * @param mixed $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        $value = strip_tags($value);

        $version = config('recaptcha.version');

        if (empty($version)) return false;

        $recaptcha = new ReCaptcha();

        if ($recaptcha->skipByIp()) {
            return true;
        }

        if (!$value) {
            return false;
        }

        $response = $recaptcha->validateToken($value);
        $buffer = $response['buffer'] ?? null;

        if (!$buffer || is_null($buffer) || empty($buffer)) {
            return false;
        }

        $response = json_decode(trim($buffer), true);

        return (isset($response['success'])) ? $response['success'] : false;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return trans('validation.recaptcha');
    }
}
