<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Webkul\Core\Models\CoreConfig;
use Webkul\Core\Repositories\ChannelRepository;
use Webkul\Core\Repositories\LocaleRepository;

class OnepayConfigSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $channelRepository = app(ChannelRepository::class);
        $localeRepository = app(LocaleRepository::class);

        // Get default channel and locale
        $defaultChannel = $channelRepository->findWhere(['code' => 'default'])->first();
        $defaultLocale = $localeRepository->findWhere(['code' => 'en'])->first();

        $channelCode = $defaultChannel ? $defaultChannel->code : null;
        $localeCode = $defaultLocale ? $defaultLocale->code : null;

        // Onepay configuration values
        $configs = [
            // Enable Onepay
            [
                'code' => 'sales.payment_methods.onepay.active',
                'value' => '1',
                'channel_code' => null,
                'locale_code' => null,
            ],
            // Title (channel and locale based)
            [
                'code' => 'sales.payment_methods.onepay.title',
                'value' => 'Online Payment - Visa / Master Card / Amex by Onepay',
                'channel_code' => $channelCode,
                'locale_code' => $localeCode,
            ],
            // Description (channel and locale based)
            [
                'code' => 'sales.payment_methods.onepay.description',
                'value' => 'Pay securely with Onepay payment gateway.',
                'channel_code' => $channelCode,
                'locale_code' => $localeCode,
            ],
            // App ID
            [
                'code' => 'sales.payment_methods.onepay.app_id',
                'value' => '01V71190A73F0EE206718',
                'channel_code' => null,
                'locale_code' => null,
            ],
            // Hash Salt
            [
                'code' => 'sales.payment_methods.onepay.hash_salt',
                'value' => 'A79Q1190A73F0EE206742',
                'channel_code' => null,
                'locale_code' => null,
            ],
            // App Token
            [
                'code' => 'sales.payment_methods.onepay.app_token',
                'value' => '9dbaeed05cf236910fd740e2caab371f11a2121ee1ac8d590fab41bd51a02057e117fa84c0562e6b.JO361190A73F0EE206757',
                'channel_code' => null,
                'locale_code' => null,
            ],
        ];

        // Insert or update configurations
        foreach ($configs as $config) {
            $existing = CoreConfig::where('code', $config['code'])
                ->where('channel_code', $config['channel_code'])
                ->where('locale_code', $config['locale_code'])
                ->first();

            if ($existing) {
                $existing->update(['value' => $config['value']]);
            } else {
                CoreConfig::create($config);
            }
        }

        $this->command->info('Onepay configuration values have been set successfully!');
    }
}

