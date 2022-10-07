<?php
/**
 * Friendly Captcha plugin for Craft CMS 3.x
 *
 * Integrate Friendly Captcha to fight spam in your Craft CMS forms
 *
 * @link      https://www.digitalpulse.be/
 * @copyright Copyright (c) 2022 Digital Pulse
 */

namespace digitalpulsebe\formiefriendlycaptcha;

use verbb\formie\services\Integrations;
use verbb\formie\events\RegisterIntegrationsEvent;
use digitalpulsebe\formiefriendlycaptcha\services\ValidateService as ValidateService;

use Craft;
use craft\base\Plugin;

use yii\base\Event;

/**
 *
 * @author    Digital Pulse
 * @package   FormieFriendlyCaptcha
 * @since     1.0.0
 *
 * @property  ValidateService $validate
 */
class FormieFriendlyCaptcha extends Plugin
{
    /**
     * Static property that is an instance of this plugin class so that it can be accessed via
     * FormieFriendlyCaptcha::$plugin
     *
     * @var FormieFriendlyCaptcha
     */
    public static $plugin;

    /**
     * To execute your plugin’s migrations, you’ll need to increase its schema version.
     *
     * @var string
     */
    public $schemaVersion = '1.0.0';

    public $hasCpSettings = true;

    public function init()
    {
        parent::init();
        self::$plugin = $this;

        // register formie custom captcha
        if (class_exists(Integrations::class)) {
            Event::on(Integrations::class, Integrations::EVENT_REGISTER_INTEGRATIONS, function(RegisterIntegrationsEvent $event) {
                $event->captchas[] = \digitalpulsebe\formiefriendlycaptcha\formie\integrations\FriendlyCaptcha::class;
            });
        }

        Craft::info(
            Craft::t(
                'formie-friendly-captcha',
                '{name} plugin loaded',
                ['name' => $this->name]
            ),
            __METHOD__
        );
    }

    protected function settingsHtml(): string
    {
        return Craft::$app->view->renderTemplate(
            'formie-friendly-captcha/settings'
        );
    }

}
