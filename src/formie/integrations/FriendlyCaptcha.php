<?php

namespace digitalpulsebe\formiefriendlycaptcha\formie\integrations;

use Craft;
use craft\helpers\App;
use craft\helpers\Html;
use digitalpulsebe\formiefriendlycaptcha\FormieFriendlyCaptcha;
use verbb\formie\base\Captcha;
use verbb\formie\elements\Form;
use verbb\formie\elements\Submission;

class FriendlyCaptcha extends Captcha
{
    public $handle = 'formieFriendlyCaptcha';
    public string $siteKey = '';
    public string $apiKey = '';
    public string $startEvent = 'focus';
    public string $endpoint = 'global';
    public ?string $customEndpoint = null;
    public bool $darkMode = false;

    public function getName(): string
    {
        return Craft::t('formie', 'Friendly Captcha');
    }

    public function getIconUrl(): string
    {
        return Craft::$app->getAssetManager()->getPublishedUrl('@digitalpulsebe/formiefriendlycaptcha/icon.svg', true);
    }

    public function getDescription(): string
    {
        return $this->getName();
    }

    public function getSettingsHtml(): string
    {
        return Craft::$app->getView()->renderTemplate('formie-friendly-captcha/formie/settings', [
            'integration' => $this,
        ]);
    }

    public function getFrontEndHtml(Form $form, $page = null): string
    {
        Craft::$app->view->registerJsFile('https://unpkg.com/friendly-challenge@0.9.7/widget.module.min.js', ['async' => true, 'defer' => true]);
        Craft::$app->view->registerJsFile('https://unpkg.com/friendly-challenge@0.9.7/widget.min.js', ['async' => true, 'defer' => true]);

        $attributes = [
            'class' => 'frc-captcha',
            'data-sitekey' => $this->getSiteKey(),
            'data-lang' => substr(Craft::$app->language ?? 'en', 0, 2),
            'data-start' => $this->startEvent,
        ];

        if ($this->endpoint != 'global') {
            $attributes['data-puzzle-endpoint'] = FormieFriendlyCaptcha::$plugin->validate->getEndpointUrl($this->endpoint, 'puzzle');
        }

        if ($this->darkMode) {
            $attributes['class'] = 'frc-captcha dark';
        }

        return Html::tag('div','', $attributes);
    }

    public function getFrontEndJsVariables(Form $form, $page = null)
    {
        return [];
    }

    public function validateSubmission(Submission $submission): bool
    {
        $solution = Craft::$app->getRequest()->getParam('frc-captcha-solution');
        $siteKey = $this->getSiteKey();
        $apiKey = $this->getApiKey();

        if (empty($solution)) {
            $this->spamReason = "Empty Friendly Captcha solution 'frc-captcha-solution' in request";
            return false;
        }

        if (empty($siteKey)) {
            $this->spamReason = "Empty Site Key in Friendly Captcha settings";
            return false;
        }

        if (empty($apiKey)) {
            $this->spamReason = "Empty API Key in Friendly Captcha settings";
            return false;
        }

        try {
            $valid = \digitalpulsebe\formiefriendlycaptcha\FormieFriendlyCaptcha::$plugin->validate->validateSolution($solution, $siteKey, $apiKey, $this->endpoint);
        } catch (\Throwable $exception) {
            $valid = false;
            $this->spamReason = $exception->getMessage();
        }

        return $valid;
    }

    /**
     * @return string
     */
    public function getSiteKey(): string
    {
        return App::parseEnv($this->siteKey);
    }

    /**
     * @return string
     */
    public function getApiKey(): string
    {
        return App::parseEnv($this->apiKey);
    }

    /**
     * @return ?string
     */
    public function getCustomEndpoint(): ?string
    {
        if ($this->customEndpoint) {
            return App::parseEnv($this->customEndpoint);
        }

        return null;
    }

    public function rules()
    {
        return [
            ['siteKey', 'string'],
            ['apiKey', 'string'],
            ['darkMode', 'boolean'],
            ['startEvent', 'in', 'range' => ['auto', 'focus', 'none']],
            ['endpoint', 'in', 'range' => ['global', 'eu']],
            [['siteKey', 'apiKey', 'startEvent', 'endpoint'], 'required'],
        ];
    }
}