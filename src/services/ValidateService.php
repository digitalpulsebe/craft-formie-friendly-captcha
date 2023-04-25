<?php
namespace digitalpulsebe\formiefriendlycaptcha\services;

use digitalpulsebe\formiefriendlycaptcha\formie\integrations\FriendlyCaptcha;
use digitalpulsebe\friendlycaptcha\services\ValidateService as BaseValidateService;


class ValidateService extends BaseValidateService
{
    public function getEndpointUrl(string $endpoint, string $service): string
    {
        if ($endpoint == 'custom') {
            $integration = \verbb\formie\Formie::getInstance()->getIntegrations()->getCaptchaByHandle('formieFriendlyCaptcha');
            if ($integration instanceof FriendlyCaptcha) {
                $baseUrl = $integration->getCustomEndpoint();
                if (substr($baseUrl, -1) !== '/') {
                    // append trailing slash if needed
                    $baseUrl = $baseUrl.'/';
                }
                return $baseUrl.$service;
            }
        }

        return parent::getEndpointUrl($endpoint, $service);
    }
}
