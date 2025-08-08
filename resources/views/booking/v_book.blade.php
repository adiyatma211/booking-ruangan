@extends('layouts.base')

@section('content')
    <div class="page-heading">
        <h3>Daftar Ruangan</h3>
        <p class="text-subtitle text-muted">Klik ruangan untuk melihat jadwal pemesanan</p>
    </div>

    <!-- Card Pencarian -->
    <div class="card mb-4 shadow-sm">
        <div class="card-body">
            <form class="row g-3">
                <div class="col-md-6">
                    <label for="search" class="form-label">Cari Ruangan</label>
                    <input type="text" class="form-control" id="search" placeholder="Masukkan nama ruangan...">
                </div>
                <div class="col-md-3">
                    <label for="durasi" class="form-label">Durasi Maksimal</label>
                    <select id="durasi" class="form-select">
                        <option selected>Semua</option>
                        <option value="1">1 jam</option>
                        <option value="2">2 jam</option>
                        <option value="4">4 jam</option>
                    </select>
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">Cari</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Card Daftar Ruangan -->
    <div class="row">
        @foreach ($ruangans as $ruangan)
            @php
                $penuh = $ruangan->is_full ?? false;
                $gaps = $ruangan->available_gaps ?? [];
                $merged = $ruangan->merged_bookings ?? []; // isi di controller: [['from'=>'12:50','to'=>'13:50'], ...]
                $route = route('kalender', $ruangan->id);

                $badgeClass = $penuh ? 'bg-danger' : 'bg-success';
                $badgeText = $penuh ? 'Penuh' : 'Tersedia';

                // Ubah data ke string simpel utk data-attr (hindari HTML/quote issues)
                $bookLines = collect($merged)
                    ->map(fn($b) => ($b['from'] ?? '') . '–' . ($b['to'] ?? ''))
                    ->filter()
                    ->implode('|');
                $gapLines = collect($gaps)
                    ->map(fn($g) => ($g['from'] ?? '') . '–' . ($g['to'] ?? ''))
                    ->filter()
                    ->implode('|');
            @endphp

            <div class="col-md-4">
                <div class="card shadow-sm mb-4 {{ $penuh ? 'bg-light border-danger' : '' }}" style="cursor:pointer;"
                    onclick="window.location.href='{{ $route }}'">
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
                            <div class="small text-muted">Tidak ada slot di jam operasional.</div>
                        @endif

                        <a href="{{ $route }}" onclick="event.stopPropagation()" class="btn btn-primary mt-3">Lihat
                            Jadwal</a>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <style>
        .tooltip-wide .tooltip-inner {
            max-width: 300px;
            text-align: left;
            white-space: nowrap;
            /* tiap rentang satu baris */
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Pastikan Bootstrap 5 bundle sudah terload (harus include bootstrap.bundle.min.js)
            document.querySelectorAll('[data-has-tooltip="1"]').forEach(function(el) {
                const books = (el.getAttribute('data-bookings') || '').split('|').filter(Boolean);
                const gaps = (el.getAttribute('data-gaps') || '').split('|').filter(Boolean);

                const bookHtml = books.length ? books.join('<br>') : '—';
                const gapHtml = gaps.length ? gaps.join('<br>') : '—';

                const html =
                    '<div class="text-start" style="min-width:220px">' +
                    '<div class="fw-semibold mb-1">Booking</div>' +
                    '<div class="small ' + (books.length ? '' : 'text-muted') + '">' + bookHtml + '</div>' +
                    '<hr class="my-2">' +
                    '<div class="fw-semibold mb-1">Slot tersedia</div>' +
                    '<div class="small ' + (gaps.length ? '' : 'text-muted') + '">' + gapHtml + '</div>' +
                    '</div>';

                new bootstrap.Tooltip(el, {
                    title: html,
                    html: true,
                    sanitize: false,
                    customClass: 'tooltip-wide',
                    placement: 'top',
                    trigger: 'hover focus'
                });
            });
        });
    </script>
@endsection
