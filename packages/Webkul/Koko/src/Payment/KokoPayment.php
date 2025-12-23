<?php

namespace Webkul\Koko\Payment;

use Illuminate\Support\Facades\Storage;
use Webkul\Payment\Payment\Payment;

class KokoPayment extends Payment
{
    protected $code = 'koko';

    public function getRedirectUrl()
    {
        return route('koko.redirect');
    }

    public function getTitle()
    {
        return $this->getConfigData('title') ?: 'Koko: Buy Now Pay Later';
    }

    public function getDescription()
    {
        return $this->getConfigData('description') ?: 'Pay in 3 interest free instalments with Koko.';
    }

    public function getImage()
    {
        $image = $this->getConfigData('image');

        if ($image) {
            return Storage::url($image);
        }

        return 'https://paykoko.com/img/logo1.7ff549c0.png';
    }
}
