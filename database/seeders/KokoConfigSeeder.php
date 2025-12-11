<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Webkul\Core\Models\CoreConfig;
use Webkul\Core\Repositories\ChannelRepository;
use Webkul\Core\Repositories\LocaleRepository;

class KokoConfigSeeder extends Seeder
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

        // Koko configuration values
        $configs = [
            // Enable Koko
            [
                'code' => 'sales.payment_methods.koko.active',
                'value' => '1',
                'channel_code' => null,
                'locale_code' => null,
            ],
            // Title (channel and locale based)
            [
                'code' => 'sales.payment_methods.koko.title',
                'value' => 'Koko: Buy Now Pay Later',
                'channel_code' => $channelCode,
                'locale_code' => $localeCode,
            ],
            // Description (channel and locale based)
            [
                'code' => 'sales.payment_methods.koko.description',
                'value' => 'Pay in 3 interest free instalments with Koko.',
                'channel_code' => $channelCode,
                'locale_code' => $localeCode,
            ],
            // Merchant ID
            [
                'code' => 'sales.payment_methods.koko.merchant_id',
                'value' => 'c8cca514bdfa0582cdc40c9703c71e9d',
                'channel_code' => null,
                'locale_code' => null,
            ],
            // API Key
            [
                'code' => 'sales.payment_methods.koko.api_key',
                'value' => '83fA5n1xUaj8OKnX23YY5vlni5q39gBi',
                'channel_code' => null,
                'locale_code' => null,
            ],
            // Private Key
            [
                'code' => 'sales.payment_methods.koko.private_key',
                'value' => '-----BEGIN RSA PRIVATE KEY-----
MIICXAIBAAKBgQCnAPcpmvA3Iipb7Fn+eAmO/P4Xv8y+PVm8FrDhqOSeMqaUQmzf
iZ6xw+ejCmye46MMW5SaA03Hnm0WGDXqYhMR0TiWUgXRCeQImxSq+wXwd+0ufxW+
ANnvH9l/mxcPwlGr2BKJTUJy2NQt8FZ9R6NSfIlKzdyGStvzF3j0KdBnjQIDAQAB
AoGAVMjwsnaurc7yomiD5+UZNTbL6VK+p3aOMCd09ZvBNW+RkoOGspYzsxw6ZVPN
gX0gMg3si6RRwJ5101nHRY81DmysZ90kgJsknqxUuwKGU6k2Wk18JqJBLGLXilwR
Z5/NjdgohoZDrJbbr029LNLZ06pvpdXtvVRM9A1XZVzEnAECQQDQ02Wg7nGFvS4M
yRWMHNARLto19W/Q+BlCsWRCDYO5zns9BtaqzZ3CyOAaXObDs6ZWpCEY+3e84u3X
pvBpdOGtAkEAzLr15YBG9Y3hQgErwIUd0dSlYiDzaIM9DszIh+lzCIi/bUM6nXQi
IZ0zDJmLjwa0bMduO+ZDiUbxuCFlxhEZYQJAdpTEbhlYr4gYwTvil3i5EjjXwrJH
t5NazMts0jFYbsd4pdPfTIiMIFLvJylABTtbpnF3Nfd+K+10//OVK10q1QJBAMLU
qW3exaipfNTziE+OXvJxC3J3KS0st85909iDsZVNjd7NO9rbyh9zGkHDXayfFNTw
dVdLqrnZae9w2QnE/AECQF+cRPcQMA1wbmOBCyn/C1YAMji71DtplJF9fFOxlp9P
XdzBrBj9flrwjasEs3WKrepvZ9A0GT5HaG15ULd2/rc=
-----END RSA PRIVATE KEY-----',
                'channel_code' => null,
                'locale_code' => null,
            ],
            // Public Key
            [
                'code' => 'sales.payment_methods.koko.public_key',
                'value' => '-----BEGIN PUBLIC KEY-----
MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQDDwDt4Q9B+MEAcxP8pPeTYGh22
lvCOxxKEwDuJPAvTtYpfiqU1Ip//njnMgWIpFcpIcqabALPrkHW8eD37SBzQ6R5l
fr01xf7lBG3bGqNXZkdXb0txnoXSmPya+B4oGqZc+KWNrKTntY3sNKD6k4tdOeoX
83rxb/gnZR5v7WP7WQIDAQAB
-----END PUBLIC KEY-----',
                'channel_code' => null,
                'locale_code' => null,
            ],
            // Gateway Mobile
            [
                'code' => 'sales.payment_methods.koko.mobile',
                'value' => '765283630',
                'channel_code' => null,
                'locale_code' => null,
            ],
            // Gateway Password
            [
                'code' => 'sales.payment_methods.koko.password',
                'value' => 'Madhu@123',
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

        $this->command->info('Koko configuration values have been set successfully!');
    }
}

