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
                // Simulasi kondisi ruangan penuh (nanti dari controller)
                $penuh = $ruangan->is_full ?? false;
            @endphp
            <div class="col-md-4">
                <div class="card shadow-sm mb-4 {{ $penuh ? 'bg-light border-danger' : '' }}" style="cursor:pointer;"
                    onclick="window.location.href='{{ route('kalender', $ruangan->id) }}'">
                    <div class="card-body">
                        <h5 class="card-title d-flex justify-content-between">
                            {{ $ruangan->nama }}
                            <span class="badge {{ $penuh ? 'bg-danger' : 'bg-success' }}">
                                {{ $penuh ? 'Penuh' : 'Tersedia' }}
                            </span>
                        </h5>
                        <p class="card-text">Maksimal: {{ $ruangan->max_jam }} jam</p>
                        <a href="{{ route('kalender', $ruangan->id) }}" class="btn btn-primary mt-2">Lihat
                            Jadwal</a>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
@endsection
