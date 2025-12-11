{!! view_render_event('bagisto.shop.layout.footer.before') !!}

<!--
    The category repository is injected directly here because there is no way
    to retrieve it from the view composer, as this is an anonymous component.
-->
@inject('themeCustomizationRepository', 'Webkul\Theme\Repositories\ThemeCustomizationRepository')

<!--
    This code needs to be refactored to reduce the amount of PHP in the Blade
    template as much as possible.
-->
@php
    $customization = $themeCustomizationRepository->findOneWhere([
        'type'       => 'footer_links',
        'status'     => 1,
        'channel_id' => core()->getCurrentChannel()->id,
    ]);
@endphp

<footer class="mt-9 bg-lightOrange max-sm:mt-10">
    <div class="flex justify-center gap-x-6 gap-y-8 p-[60px] max-1060:flex-col-reverse max-md:gap-5 max-md:p-8 max-sm:px-4 max-sm:py-5">
        <!-- For Desktop View -->
        <div class="flex flex-wrap items-start gap-24 max-1180:gap-6 max-1060">
            <div class="footer-logo-container">
            <img class="footer-logo" src="{{ Storage::url('channel/1/hpQPXawsQfYx7cN0eG2RlIMZvvIdAsD76wee8zeQ.png') }}" />
            <h1 style="font-size: 12px;">Discover Crown Gallery - curated luxury perfumes and niche fragrances in Sri Lanka. Secure checkout, fast delivery, and expert fragrance advice to help you choose the perfect scent.</h1>
            </div>
            <div class="footer-links">
            <b>Useful Links</b>
            @if ($customization?->options)
                    <ul class="grid gap-5 text-sm">
                @foreach ($customization->options as $footerLinkSection)
                        @php
                            usort($footerLinkSection, function ($a, $b) {
                                return $a['sort_order'] - $b['sort_order'];
                            });
                        @endphp

                        @foreach ($footerLinkSection as $link)
                            <li>
                                <a href="{{ $link['url'] }}">
                                    {{ $link['title'] }}
                                </a>
                            </li>
                        @endforeach
                @endforeach
                    </ul>
            @endif
            </div>
            <div class="social-media">
                <ul class="social-media-links">
                    <li><a href="https://web.facebook.com/profile.php?id=100063858581742"><img class="social-media-logo" src="/storage/theme/1/fb-icon.png" /></a></li>
                    <li><a href="https://www.instagram.com/crowngallerypvtltd?"><img class="social-media-logo" src="/storage/theme/1/ig-icon.png" /></a></li>
                    <li><a href="https://www.tiktok.com/@crowngallery0"><img class="social-media-logo" src="/storage/theme/1/tt-icons.png" /></a></li>
                </ul>
            </div>
            <div class="call">
                <h3>Inquiries? Call Us!</h3>
                <p><a href="tel:0777604941">077 760 4941</a></p>
            </div>
        </div>
    </div>

    <div class="flex justify-between bg-[#F1EADF] px-[60px] py-3.5 max-md:justify-center max-sm:px-5">
        {!! view_render_event('bagisto.shop.layout.footer.footer_text.before') !!}

        <p class="text-sm text-zinc-600 max-md:text-center">
            © 2025 Crown Gallery. All Rights Reserved.
        </p>

        {!! view_render_event('bagisto.shop.layout.footer.footer_text.after') !!}
    </div>
</footer>

{!! view_render_event('bagisto.shop.layout.footer.after') !!}
