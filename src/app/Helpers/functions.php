<?php
/**
 * Laravel reCAPTCHA
 * Author: ZanySoft
 * Web: www.zanysoft.net
 */

use ZanySoft\ReCaptcha\Facades\ReCaptcha;

if (!function_exists('recaptcha')) {
    /**
     * @return ZanySoft\ReCaptcha\ReCaptcha
     */
    function recaptcha()
    {
        return app('recaptcha');
    }
}

if (!function_exists('recaptchaApiJsScriptTag')) {

    /**
     * Call ReCaptcha::apiJsScriptTag()
     * Write script HTML tag in you HTML code
     * Insert before </head> tag
     *
     * @param string|array|null $formId form id is required if you are using invisible ReCaptcha
     * @param array|null $configuration
     * @return string
     */
    function recaptchaApiJsScriptTag($formId = '', ?array $configuration = []): string
    {
        if (is_array($formId)) {
            $configuration = $formId;
            $formId = $configuration['formId'] ?? $configuration['form_id'] ?? $configuration['id'] ?? '';
        }
        return ReCaptcha::apiJsScriptTag($formId, $configuration);
    }
}

if (!function_exists('recaptchaApiV3JsScriptTag')) {

    /**
     * Call ReCaptcha::apiV3JsScriptTag()
     * Write script HTML tag in you HTML code
     * Insert before </head> tag
     *
     * @param array $config
     * @return string
     */
    function recaptchaApiV3JsScriptTag(?array $config = []): string
    {
        return ReCaptcha::apiV3JsScriptTag($config);
    }
}

if (!function_exists('recaptchaHtmlFormButton')) {

    /**
     * Call ReCaptcha::htmlFormButton()
     * Write HTML <button> tag in your HTML code
     * Insert before </form> tag
     *
     * Warning! Using only with ReCAPTCHA INVISIBLE
     *
     * @param string $buttonInnerHTML What you want to write on the submit button
     * @return string
     */
    function recaptchaHtmlFormButton(?string $buttonInnerHTML = 'Submit'): string
    {
        return ReCaptcha::htmlFormButton($buttonInnerHTML);
    }
}

if (!function_exists('recaptchaHtmlFormSnippet')) {

    /**
     * Call ReCaptcha::htmlFormSnippet()
     * Write ReCAPTCHA HTML tag in your FORM
     * Insert before </form> tag
     *
     *
     * @param string|null $id required if you are using v3 ReCaptcha
     * @return string
     */
    function recaptchaHtmlFormSnippet(?string $id = null): string
    {
        return ReCaptcha::htmlFormSnippet($id);
    }
}