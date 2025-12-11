<?php

namespace Webkul\Koko\Payment;

use Illuminate\Support\Facades\Storage;
use Webkul\Payment\Payment\Payment;

class KokoPayment extends Payment
{
    /**
     * Payment method code
     *
     * @var string
     */
    protected $code = 'koko';

    /**
     * Get redirect url for redirect to payment page
     *
     * @return string
     */
    public function getRedirectUrl()
    {
        return route('koko.redirect');
    }

    /**
     * Get payment method title.
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->getConfigData('title') ?: 'Koko: Buy Now Pay Later';
    }

    /**
     * Get payment method description.
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->getConfigData('description') ?: 'Pay in 3 interest free instalments with Koko.';
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

        // Return default KOKO logo if no image is configured
        // Use a placeholder or null if file doesn't exist to avoid breaking the payment methods list
        try {
            $logoPath = bagisto_asset('images/koko-logo.png', 'shop');
            // Verify the path is valid (not empty and not an error)
            if (!empty($logoPath) && $logoPath !== 'images/koko-logo.png') {
                return $logoPath;
            }
        } catch (\Exception $e) {
            // Silently fail if asset doesn't exist
        }

        // Return null if no image is available
        // Frontend will handle null images gracefully (image tag won't render)
        return null;
    }
}

