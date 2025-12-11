<?php

namespace Webkul\Onepay\Payment;

use Illuminate\Support\Facades\Storage;
use Webkul\Payment\Payment\Payment;

class OnepayPayment extends Payment
{
    /**
     * Payment method code
     *
     * @var string
     */
    protected $code = 'onepay';

    /**
     * Get redirect url for redirect to payment page
     *
     * @return string
     */
    public function getRedirectUrl()
    {
        return route('onepay.redirect');
    }

    /**
     * Get payment method title.
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->getConfigData('title') ?: 'Onepay';
    }

    /**
     * Get payment method description.
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->getConfigData('description') ?: 'Pay securely with Onepay payment gateway.';
    }

    /**
     * Get payment method image.
     *
     * @return string|null
     */
    public function getImage()
    {
        $url = $this->getConfigData('image');

        if ($url) {
            return Storage::url($url);
        }

        // Return null if no image is configured
        // The frontend will handle null images gracefully
        return null;
    }
}

