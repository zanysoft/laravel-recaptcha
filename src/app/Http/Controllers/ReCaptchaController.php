<?php
/**
 * Laravel reCAPTCHA
 * Author: ZanySoft
 * Web: www.zanysoft.net
 */

namespace ZanySoft\ReCaptcha\app\Http\Controllers;

use Illuminate\Routing\Controller;
use ZanySoft\ReCaptcha\ReCaptcha;

class ReCaptchaController extends Controller
{
    /**
     * @return array
     */
    public function validateV3(): array
    {
        $token = request()->input(config('recaptcha.token_parameter_name', 'token'), '');

        if (config('recaptcha.version') != 'v3') {
            //...
        }

        $recaptcha = new ReCaptcha();

        if ($recaptcha->skipByIp()) {
            // Add 'skip_by_ip' field to response
            return [
                'skip_by_ip' => true,
                'score' => 0.9,
                'success' => true,
            ];
        }

        if (!$token) {
            return [
                'error' => 'Invalid token',
                'score' => 0.1,
                'success' => false,
            ];
        }

        $response = $recaptcha->validateToken($token);

        $buffer = $response['buffer'] ?? null;

        if (!$buffer) {
            return [
                'error' => $response['error'] ?? 'cURL response empty',
                'score' => 0.1,
                'success' => false,
            ];
        }

        $response = json_decode(trim($buffer), true);

        return $response;
    }
}
