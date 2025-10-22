@extends('layouts.base')

@section('content')
    <div class="page-heading">
        <h3>Daftar Ruangan</h3>
        <p class="text-subtitle text-muted">Klik ruangan untuk melihat jadwal pemesanan</p>
    </div>

    @php
        // Ambil opsi durasi unik dari DB
        $durasiOptions = collect($ruangans)
            ->pluck('max_jam')
            ->filter(fn($v) => is_numeric($v) && (int) $v > 0)
            ->map(fn($v) => (int) $v)
            ->unique()
            ->sort()
            ->values();
    @endphp

    <!-- Filter Pencarian -->
    <div class="card mb-4 shadow-sm">
        <div class="card-body">
            <div class="row g-3 align-items-end">
                <div class="col-md-6">
                    <label for="search" class="form-label">Cari Ruangan</label>
                    <input type="text" class="form-control" id="search" placeholder="Masukkan nama ruangan...">
                </div>
                <div class="col-md-3">
                    <label for="durasi" class="form-label">Durasi Maksimal</label>
                    <select id="durasi" class="form-select">
                        <option value="" selected>Semua</option>
                        <option value="1">1 jam</option>
                        <option value="2">2 jam</option>
                        <option value="4">4 jam</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <div class="form-label">Status</div>
                    <div class="d-flex gap-2 flex-wrap">
                        <button type="button" class="btn btn-outline-success btn-sm" data-filter-status="tersedia">Tersedia</button>
                        <button type="button" class="btn btn-outline-danger btn-sm" data-filter-status="penuh">Penuh</button>
                        <button type="button" class="btn btn-outline-secondary btn-sm" data-filter-status="reset">Reset</button>
                    </div>
                </div>
            </div>
            <div class="mt-3 small text-muted" id="resultCount"></div>
        </div>
    </div>

    <!-- Daftar Ruangan -->
    <div class="row">
        @foreach ($ruangans as $ruangan)
            @php
                $penuh = $ruangan->is_full ?? false;
                $gaps = $ruangan->available_gaps ?? [];
                $merged = $ruangan->merged_bookings ?? [];
                $route = route('kalender', $ruangan->id);

                $badgeClass = $penuh ? 'bg-danger' : 'bg-success';
                $badgeText = $penuh ? 'Penuh' : 'Tersedia';

                $bookLines = collect($merged)
                    ->map(fn($b) => ($b['from'] ?? '') . '–' . ($b['to'] ?? ''))
                    ->filter()
                    ->implode('|');
                $gapLines = collect($gaps)
                    ->map(fn($g) => ($g['from'] ?? '') . '–' . ($g['to'] ?? ''))
                    ->filter()
                    ->implode('|');
            @endphp
            <div class="col-md-4 room-col" data-name="{{ strtolower($ruangan->nama) }}" data-max-jam="{{ $ruangan->max_jam }}" data-status="{{ $penuh ? 'penuh' : 'tersedia' }}">
                <div class="card shadow-sm mb-4 room-card {{ $penuh ? 'bg-light border-danger' : '' }}"
                    style="cursor:pointer; transition: transform .12s ease, box-shadow .12s ease;" onclick="window.location.href='{{ $route }}'">
                    <div class="card-body">
                        <h5 class="card-title d-flex justify-content-between align-items-center">
                            <span>{{ $ruangan->nama }}</span>
                            <span class="badge {{ $badgeClass }}" data-has-tooltip="1" data-bookings="{{ $bookLines }}"
                                data-gaps="{{ $gapLines }}">
                                {{ $badgeText }}
                            </span>
                        </h5>

                        <p class="card-text mb-2">Maksimal: {{ $ruangan->max_jam }} jam</p>

                        @if (!$penuh && !empty($gaps))
                            <div class="small text-muted mb-1">Jam Tersedia Hari ini:</div>
                            <div class="d-flex flex-wrap gap-1">
                                @foreach ($gaps as $g)
                                    <span
                                        class="badge bg-light text-dark border">{{ $g['from'] }}–{{ $g['to'] }}</span>
                                @endforeach
                            </div>
                        @elseif($penuh)
                            <div class="small text-muted">Tidak ada kuota di jam operasional.</div>
                        @endif

                        <a href="{{ $route }}" onclick="event.stopPropagation()" class="btn btn-primary mt-3">
                            Lihat Jadwal
                        </a>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const search = document.getElementById('search');
            const durasi = document.getElementById('durasi');
            const statusBtns = document.querySelectorAll('[data-filter-status]');
            const cols = Array.from(document.querySelectorAll('.room-col'));
            const resultCount = document.getElementById('resultCount');

            let statusFilter = '';

            function applyFilters() {
                const q = (search.value || '').toLowerCase().trim();
                const d = durasi.value;
                let shown = 0;

                cols.forEach(col => {
                    const name = col.getAttribute('data-name');
                    const max = Number(col.getAttribute('data-max-jam'));
                    const st = col.getAttribute('data-status');

                    let visible = true;
                    if (q && !name.includes(q)) visible = false;
                    if (d && !(max >= Number(d))) visible = false;
                    if (statusFilter && st !== statusFilter) visible = false;

                    col.style.display = visible ? '' : 'none';
                    if (visible) shown++;
                });

                resultCount.textContent = `${shown} ruangan ditemukan`;
            }

            search.addEventListener('input', applyFilters);
            durasi.addEventListener('change', applyFilters);
            statusBtns.forEach(btn => {
                btn.addEventListener('click', () => {
                    const val = btn.getAttribute('data-filter-status');
                    statusFilter = (val === 'reset') ? '' : val;
                    statusBtns.forEach(b => b.classList.remove('active'));
                    if (statusFilter) btn.classList.add('active');
                    applyFilters();
                });
            });

            // Subtle hover lift for cards
            document.querySelectorAll('.room-card').forEach(card => {
                card.addEventListener('mouseenter', () => {
                    card.style.transform = 'translateY(-2px)';
                    card.style.boxShadow = '0 6px 18px rgba(0,0,0,0.08)';
                });
                card.addEventListener('mouseleave', () => {
                    card.style.transform = '';
                    card.style.boxShadow = '';
                });
            });

            applyFilters();
        });
    </script>
@endsection
