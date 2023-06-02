<?php
/**
 * Laravel reCAPTCHA
 * Author: ZanySoft
 * Web: www.zanysoft.net
 */

namespace ZanySoft\ReCaptcha;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class ReCaptcha
{

    /**
     * The configuration
     */
    protected $config = null;
    /**
     * The Site key
     * please visit https://developers.google.com/recaptcha/docs/start
     * @var string
     */
    protected $siteKey;

    /**
     * The Secret key
     * please visit https://developers.google.com/recaptcha/docs/start
     * @var string
     */
    protected $secretKey;

    /**
     * The Language Code
     */
    protected $lang = null;

    /**
     * The chosen ReCAPTCHA version
     * please visit https://developers.google.com/recaptcha/docs/start
     * @var string
     */
    protected $version;

    /**
     * Whether is true the ReCAPTCHA is inactive
     * @var boolean
     */
    protected $skipByIp = false;

    /**
     * The API request URI
     */
    protected $apiUrl = 'https://www.google.com/recaptcha/api/siteverify';

    /**
     * ReCaptchaBuilder constructor.
     *
     * @param $siteKey
     * @param $secretKey
     * @param $lang
     * @param string $version
     */
    public function __construct()
    {
        $this->config = config('recaptcha');

        $version = Arr::get($this->config, 'version');
        $site_key = Arr::get($this->config, 'site_key');
        $secret_key = Arr::get($this->config, 'secret_key');
        $lang = Arr::get($this->config, 'lang');

        if (!$lang) {
            $lang = config('app.locale', 'en');
        }

        $this->setSiteKey($site_key);
        $this->setSecretKey($secret_key);
        $this->setLanguage($lang);
        $this->setVersion($version);
        $this->setSkipByIp($this->skipByIp());
    }

    /**
     * @param string $siteKey
     * @return ReCaptcha
     */
    public function setSiteKey(string $siteKey): ReCaptcha
    {
        $this->siteKey = $siteKey;

        return $this;
    }

    /**
     * @param string $secretKey
     * @return ReCaptcha
     */
    public function setSecretKey(string $secretKey): ReCaptcha
    {
        $this->secretKey = $secretKey;

        return $this;
    }

    /**
     * @param string $lang
     * @return ReCaptcha
     */
    public function setLanguage(string $lang): ReCaptcha
    {
        $this->lang = $lang;

        return $this;
    }

    /**
     * @param string $version
     * @return ReCaptcha
     */
    public function setVersion(string $version): ReCaptcha
    {
        $this->version = $version;

        return $this;
    }

    /**
     * @param bool $skipByIp
     * @return ReCaptcha
     */
    public function setSkipByIp(bool $skipByIp): ReCaptcha
    {
        $this->skipByIp = $skipByIp;

        return $this;
    }

    /**
     * @return string
     */
    public function getApiUrl()
    {
        return $this->apiUrl;
    }

    /**
     * @return string
     */
    public function getSecretKey()
    {
        return $this->secretKey;
    }

    /**
     * @return string
     */
    public function getVersion(): string
    {
        return $this->version;
    }

    /**
     * @return array|\Illuminate\Config\Repository|mixed
     */
    public function getIpWhitelist()
    {
        $whitelist = Arr::get($this->config, 'skip_ip', []);

        if (!is_array($whitelist)) {
            $whitelist = explode(',', $whitelist);
        }

        return $whitelist;
    }

    /**
     * Checks whether the user IP address is among IPs "to be skipped"
     *
     * @return bool
     */
    public function skipByIp(): bool
    {
        return (in_array(request()->ip(), $this->getIpWhitelist()));
    }

    /**
     * Write ReCAPTCHA HTML tag in your FORM
     * Insert before </form> tag
     * @return string
     */
    public function htmlFormSnippet($id = null): string
    {
        if ($this->skipByIp) {
            return '';
        }

        if (Str::startsWith($id, '#')) {
            $id = ltrim($id, '#');
        }

        if ($this->version == 'v2') {
            $theme = Arr::get($this->config, 'theme', 'light');
            return '<div class="g-recaptcha" data-sitekey="' . $this->siteKey . '" data-theme="' . $theme . '"></div>';
        }

        if ($this->version == 'v3' && $id) {
            return '<input type="hidden" name="g-recaptcha-response" id="' . $id . '">';
        }

        return '';
    }

    /**
     * Write HTML <button> tag in your HTML code
     * Insert before </form> tag
     *
     * @param string $buttonInnerHTML
     *
     * @return string
     */
    public function htmlFormButton($buttonInnerHTML = 'Submit'): string
    {
        if ($this->skipByIp) {
            return '';
        }

        return ($this->version == 'invisible')
            ? '<button class="g-recaptcha" data-sitekey="' . $this->siteKey . '" data-callback="laraReCaptcha">' . $buttonInnerHTML . '</button>'
            : '';
    }

    /**
     * Write script HTML tag in you HTML code
     * Insert before </head> tag
     *
     * @param string|null $formId
     * @param array|null $configuration
     * @return string
     * @throws \Exception
     */
    public function apiJsScriptTag(?string $formId = '', ?array $configuration = []): string
    {
        if ($this->skipByIp) {
            return '';
        }

        // Get language code
        $this->lang = Arr::get($configuration, 'lang', $this->lang);
        $this->lang = $this->ietfLangTag($this->lang);


        switch ($this->version) {
            case 'v3':
                $langParam = (!empty($this->lang) ? '&hl=' . $this->lang : '');
                $html = "<script src=\"https://www.google.com/recaptcha/api.js?render={$this->siteKey}{$langParam}\"></script>";
                break;
            default:
                $onload_callback = Arr::get($configuration, 'onload_callback', '');
                $render = Arr::get($configuration, 'render', '');

                $langParam = (!empty($this->lang) ? '?hl=' . $this->lang : '');
                if ($onload_callback) {
                    $langParam .= ($langParam ? '&' : '?') . 'onload=' . $onload_callback;
                }

                if ($render) {
                    $langParam .= ($langParam ? '&' : '?') . 'render=' . $render;
                }

                $html = "<script src=\"https://www.google.com/recaptcha/api.js{$langParam}\" async defer></script>";
        }

        if ($this->version == 'invisible') {
            if (!$formId) {
                $formId = $configuration['formId'] ?? $configuration['form_id'] ?? $configuration['id'] ?? '';
            }

            if (!$formId) {
                throw new \Exception("formId required", 1);
            }
            $html .= '<script>
			function laraReCaptcha(token) {
				document.getElementById("' . $formId . '").submit();
			}
			</script>';

        } else if ($this->version == 'v3') {

            $action = Arr::get($configuration, 'action', 'homepage');


            $jsCustomValidation = Arr::get($configuration, 'custom_validation', '');

            // Check if set custom_validation. That function will override default fetch validation function
            if ($jsCustomValidation) {
                $validateFunction = ($jsCustomValidation) ? "{$jsCustomValidation}(token);" : '';
            } else {

                $jsThenCallback = Arr::get($configuration, 'callback_then', '');
                $jsCallbackCatch = Arr::get($configuration, 'callback_catch', '');

                $jsThenCallback = ($jsThenCallback) ? "{$jsThenCallback}(response)" : '';
                $jsCallbackCatch = ($jsCallbackCatch) ? "{$jsCallbackCatch}(err)" : '';

                $validation_route = Arr::get($this->config, 'validation_route', 'laravel-recaptcha/validate');
                $token_parameter_name = Arr::get($this->config, 'token_parameter_name', 'token');

                $validateFunction = "
                fetch('/" . $validation_route . "?" . $token_parameter_name . "=' + token, {
                    headers: {
                        \"X-Requested-With\": \"XMLHttpRequest\",
                        \"X-CSRF-TOKEN\": csrfToken.content
                    }
                })
                .then(function(response) {
                   	{$jsThenCallback}
                })
                .catch(function(err) {
                    {$jsCallbackCatch}
                });";
            }

            // Fixing invalid action name in recaptcha v3
            $action = str_replace(['-', '.'], '', $action);

            $html .= "<script>
			var csrfToken = document.head.querySelector('meta[name=\"csrf-token\"]');
			grecaptcha.ready(function() {
				grecaptcha.execute('{$this->siteKey}', {action: '{$action}'}).then(function(token) {
					{$validateFunction}
				});
			});
			</script>";
        }

        return $html;
    }

    /**
     * @param array|null $configuration
     * @return string
     * @throws \Exception
     */
    public function apiV3JsScriptTag(?array $configuration = []): string
    {
        return $this->apiJsScriptTag('', $configuration);
    }

    public function validateToken($token)
    {
        $params = http_build_query([
            'secret' => $this->getSecretKey(),
            'remoteip' => request()->getClientIp(),
            'response' => $token,
        ]);

        $url = $this->getApiUrl() . '?' . $params;

        if (function_exists('curl_version')) {
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_HEADER, false);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 1);
            if (strpos(strtolower($url), 'https://') !== false) {
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
            }
            $buffer = curl_exec($ch);
            $error = curl_error($ch);
            curl_close($ch);
        } else {
            $error = null;
            try {
                $buffer = file_get_contents($url);
                if (is_null($buffer) || empty($buffer)) {
                    $error = 'cURL response empty';
                }
            } catch (\Exception $e) {
                $buffer = null;
                $error = $e->getMessage();
            }
        }

        return ['buffer' => $buffer, 'error' => $error];
    }

    /**
     * @param null $locale
     * @return mixed
     */
    protected function ietfLangTag($locale = null)
    {
        if (empty($locale)) {
            $locale = config('app.locale');
        }

        return str_replace('_', '-', $locale);
    }
}
