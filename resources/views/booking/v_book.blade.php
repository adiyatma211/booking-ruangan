@extends('layouts.base')

@section('content')
    <div class="page-heading">
        <h3>Daftar Ruangan</h3>
        <p class="text-subtitle text-muted">Klik ruangan untuk melihat jadwal pemesanan</p>
    </div>

    <!-- Card Pencarian -->
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

    <!-- Card Daftar Ruangan -->
    <div class="row">
        @foreach ($ruangans as $ruangan)
            @php
                $penuh = $ruangan->is_full ?? false;
                $route = route('kalender', $ruangan->id);
            @endphp
            <div class="col-md-4 room-col" data-name="{{ strtolower($ruangan->nama) }}" data-max-jam="{{ $ruangan->max_jam }}" data-status="{{ $penuh ? 'penuh' : 'tersedia' }}">
                <div class="card shadow-sm mb-4 room-card {{ $penuh ? 'bg-light border-danger' : '' }}"
                    style="cursor:pointer; transition: transform .12s ease, box-shadow .12s ease;" onclick="window.location.href='{{ $route }}'">
                    <div class="card-body">
                        <h5 class="card-title d-flex justify-content-between align-items-center">
                            {{ $ruangan->nama }}
                            <span class="badge {{ $penuh ? 'bg-danger' : 'bg-success' }}">
                                {{ $penuh ? 'Penuh' : 'Tersedia' }}
                            </span>
                        </h5>
                        <p class="card-text">Maksimal: {{ $ruangan->max_jam }} jam</p>
                        <a href="{{ $route }}" onclick="event.stopPropagation()" class="btn btn-primary mt-2">Lihat Jadwal</a>
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
