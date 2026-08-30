@if(isset($setting) && $setting)
<footer>
    <div class="container">
        <div class="row align-items-center">
            <div class="col-md-6">
                <div class="footer-brand mb-2">
                    <i class="bi bi-controller me-2" style="color:var(--clr-cyan);"></i>
                    {{ $setting->business_name ?? 'Maxibox Playstation' }}
                </div>
                @if($setting->address)
                <p class="small mb-1"><i class="bi bi-geo-alt me-1" style="color:var(--clr-cyan);"></i>{{ $setting->address }}</p>
                @endif
                @if($setting->phone_number)
                <p class="small mb-0">
                    <i class="bi bi-whatsapp me-1" style="color:var(--clr-cyan);"></i>
                    <a href="https://wa.me/{{ preg_replace('/\D/', '', $setting->phone_number) }}" target="_blank">
                        {{ $setting->phone_number }}
                    </a>
                </p>
                @endif
            </div>
            <div class="col-md-6 text-md-end mt-3 mt-md-0">
                <p class="small mb-0" style="color:rgba(255,255,255,0.4);">
                    &copy; {{ date('Y') }} {{ $setting->business_name ?? 'Maxibox Playstation' }}. Hak cipta dilindungi.
                </p>
            </div>
        </div>
    </div>
</footer>
@else
<footer>
    <div class="container text-center">
        <p class="small mb-0">&copy; {{ date('Y') }} Maxibox Playstation. Hak cipta dilindungi.</p>
    </div>
</footer>
@endif
