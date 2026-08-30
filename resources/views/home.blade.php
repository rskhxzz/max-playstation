@extends('layouts.public')
@section('title', 'Maxibox Playstation – Sewa PS4 Antar ke Rumah')
@section('content')

{{-- Hero --}}
<section class="hero-section">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-7">
                <h1 class="hero-title">Sewa <span>PlayStation</span><br>Antar Ke Rumahmu</h1>
                <p class="hero-subtitle">Nikmati gaming di rumah tanpa repot. Kami antarkan PlayStation langsung ke lokasi Anda.</p>
                <div class="d-flex flex-wrap gap-3">
                    <a href="{{ route('booking.create') }}" class="btn btn-primary-custom btn-lg">
                        <i class="bi bi-cart3 me-2"></i>Pesan Sekarang
                    </a>
                    <a href="#paket" class="btn btn-cyan-outline btn-lg">
                        <i class="bi bi-info-circle me-2"></i>Lihat Paket
                    </a>
                </div>
                <div class="d-flex gap-4 mt-4">
                    <div class="text-center">
                        <div style="font-size:1.6rem;font-weight:800;color:var(--clr-cyan);">2</div>
                        <div style="font-size:0.78rem;color:rgba(255,255,255,0.6);">Unit PS4</div>
                    </div>
                    <div class="text-center">
                        <div style="font-size:1.6rem;font-weight:800;color:var(--clr-cyan);">3</div>
                        <div style="font-size:0.78rem;color:rgba(255,255,255,0.6);">Paket Sewa</div>
                    </div>
                    <div class="text-center">
                        <div style="font-size:1.6rem;font-weight:800;color:var(--clr-cyan);">QRIS</div>
                        <div style="font-size:0.78rem;color:rgba(255,255,255,0.6);">Pembayaran</div>
                    </div>
                </div>
            </div>
            <div class="col-lg-5 text-center mt-5 mt-lg-0">
                <i class="bi bi-controller hero-ps-icon"></i>
            </div>
        </div>
    </div>
</section>

{{-- Packages --}}
<section id="paket" class="py-5">
    <div class="container">
        <div class="text-center mb-5">
            <div class="section-title">Paket Sewa Kami</div>
            <div class="accent-line mx-auto"></div>
            <p class="section-subtitle mt-2">Pilih paket yang sesuai kebutuhanmu</p>
        </div>

        <div class="row justify-content-center g-4">
            @forelse($packages as $pkg)
            <div class="col-md-4">
                <div class="card package-card h-100">
                    <div class="card-body p-4 d-flex flex-column">
                        <div class="mb-2">
                            <span class="badge text-bg-dark" style="background:var(--clr-dark)!important;">
                                <i class="bi bi-clock me-1" style="color:var(--clr-cyan);"></i>
                                <span class="duration">{{ $pkg->duration_hours }} Jam</span>
                            </span>
                        </div>
                        <h5 class="fw-700 mb-1">{{ $pkg->name }}</h5>
                        <div class="price mb-2">Rp {{ number_format($pkg->price, 0, ',', '.') }}</div>
                        @if($pkg->description)
                        <p class="text-muted small mb-3">{{ $pkg->description }}</p>
                        @endif
                        @if($pkg->blocked_start_time && $pkg->blocked_end_time)
                        <div class="alert alert-warning py-2 px-3 mt-auto mb-0" style="font-size:0.8rem;">
                            <i class="bi bi-exclamation-circle me-1"></i>
                            Tidak tersedia pukul {{ substr($pkg->blocked_start_time,0,5) }}–{{ $pkg->blocked_end_time === '00:00:00' ? '24:00' : substr($pkg->blocked_end_time,0,5) }}
                        </div>
                        @else
                        <div class="text-success small mt-auto">
                            <i class="bi bi-check-circle me-1"></i>Tersedia kapan saja
                        </div>
                        @endif
                        <a href="{{ route('booking.create') }}?package={{ $pkg->id }}" class="btn btn-primary-custom w-100 mt-3">
                            Pilih Paket Ini
                        </a>
                    </div>
                </div>
            </div>
            @empty
            <div class="col-12 text-center text-muted">
                <i class="bi bi-box-seam" style="font-size:3rem;opacity:0.3;"></i>
                <p>Paket belum tersedia.</p>
            </div>
            @endforelse
        </div>
    </div>
</section>

{{-- FAQ --}}
@if($faqs->count() > 0)
<section class="py-5" style="background:#fff;">
    <div class="container">
        <div class="text-center mb-4">
            <div class="section-title">Pertanyaan Umum</div>
            <div class="accent-line mx-auto"></div>
        </div>

        <div class="d-flex align-items-center gap-3 justify-content-center mb-3">
            <button class="slider-btn" id="faqPrev"><i class="bi bi-chevron-left"></i></button>
            <span class="text-muted small">Geser untuk melihat lebih banyak</span>
            <button class="slider-btn" id="faqNext"><i class="bi bi-chevron-right"></i></button>
        </div>

        <div class="faq-slider-container">
            <div class="faq-slider-track" id="faqTrack">
                @foreach($faqs as $faq)
                <div class="faq-card">
                    <div class="faq-q">
                        <i class="bi bi-question-circle me-2" style="color:var(--clr-magenta);"></i>
                        {{ $faq->question }}
                    </div>
                    <div class="faq-a">{{ $faq->answer }}</div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</section>
@endif

@endsection

@push('scripts')
<script>
const track  = document.getElementById('faqTrack');
const btnPrev = document.getElementById('faqPrev');
const btnNext = document.getElementById('faqNext');

if (track) {
    let pos = 0;
    const cardWidth = 320 + 20; // card + gap
    const cards = track.children.length;
    const visible = Math.floor(track.parentElement.offsetWidth / cardWidth);

    btnNext?.addEventListener('click', () => {
        const maxPos = (cards - visible) * cardWidth;
        pos = Math.min(pos + cardWidth, maxPos);
        track.style.transform = `translateX(-${pos}px)`;
    });
    btnPrev?.addEventListener('click', () => {
        pos = Math.max(pos - cardWidth, 0);
        track.style.transform = `translateX(-${pos}px)`;
    });
}
</script>
@endpush
