@extends('layouts.base')
@section('content')
    <div class="page-content">
        <section class="row">
            <div class="col-12 col-lg-9">
                @hasanyrole('SuperAdmin|Admin')
                    <div class="row">
                        <div class="col-6 col-lg-3 col-md-6">
                            <div class="card">
                                <div class="card-body px-4 py-4-5">
                                    <div class="row">
                                        <div class="col-md-4 col-lg-12 col-xl-12 col-xxl-5 d-flex justify-content-start ">
                                            <div class="stats-icon purple mb-2">
                                                <i class="iconly-boldShow"></i>
                                            </div>
                                        </div>
                                        <div class="col-md-8 col-lg-12 col-xl-12 col-xxl-7">
                                            <h6 class="text-muted font-semibold">Total Ruangan</h6>
                                            <h6 class="font-extrabold mb-0">{{ $ShowTotal }}</h6>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-6 col-lg-3 col-md-6">
                            <div class="card">
                                <div class="card-body px-4 py-4-5">
                                    <div class="row">
                                        <div class="col-md-4 col-lg-12 col-xl-12 col-xxl-5 d-flex justify-content-start ">
                                            <div class="stats-icon blue mb-2">
                                                <i class="iconly-boldProfile"></i>
                                            </div>
                                        </div>
                                        <div class="col-md-8 col-lg-12 col-xl-12 col-xxl-7">
                                            <h6 class="text-muted font-semibold">Booking Ruangan</h6>
                                            <h6 class="font-extrabold mb-0">{{ $ShowTotalPemesanan }}</h6>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-6 col-lg-3 col-md-6">
                            <div class="card">
                                <div class="card-body px-4 py-4-5">
                                    <div class="row">
                                        <div class="col-md-4 col-lg-12 col-xl-12 col-xxl-5 d-flex justify-content-start ">
                                            <div class="stats-icon green mb-2">
                                                <i class="iconly-boldAdd-User"></i>
                                            </div>
                                        </div>
                                        <div class="col-md-8 col-lg-12 col-xl-12 col-xxl-7">
                                            <h6 class="text-muted font-semibold">Total Pengguna</h6>
                                            <h6 class="font-extrabold mb-0">{{ $ShowTotalUser }}</h6>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12">
                            <div class="card mb-3">
                                <div class="card-header">
                                    <h4>Chart Booking</h4>
                                </div>
                                <div class="card-body">
                                    <form id="chartFilter" class="row g-3">
                                        <div class="col-md-3">
                                            <label class="form-label">Tanggal Mulai</label>
                                            <input type="date" class="form-control" id="start" name="start">
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label">Tanggal Selesai</label>
                                            <input type="date" class="form-control" id="end" name="end">
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label">Tipe Chart</label>
                                            <select class="form-select" id="chartType">
                                                <option value="area" selected>Area</option>
                                                <option value="line">Line</option>
                                                <option value="bar">Bar</option>
                                            </select>
                                        </div>
                                        <div class="col-md-3 d-flex align-items-end">
                                            <button class="btn btn-primary w-100" type="submit">Terapkan</button>
                                        </div>
                                    </form>

                                    <div class="mt-3" id="chart-booking"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endhasanyrole
                <div class="row">
                    <div class="col-12">
                        <div class="card mb-3">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h4 class="mb-0">Pesanan Ruangan Saya</h4>
                                <a href="/booking" class="btn btn-primary">
                                    + Tambah Booking
                                </a>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table id="myBookingsTable" class="table table-sm table-striped align-middle nowrap"
                                        style="width:100%">
                                        <thead>
                                            <tr>
                                                <th>Tanggal</th>
                                                <th>Jam</th>
                                                <th>Ruangan</th>
                                                <th>Keperluan</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($myBookings as $b)
                                                <tr>
                                                    <td>{{ $b->tanggal ? \Carbon\Carbon::parse($b->tanggal)->format('d M Y') : '-' }}
                                                    </td>
                                                    <td>
                                                        @php
                                                            $start = $b->jam_mulai
                                                                ? \Carbon\Carbon::createFromFormat(
                                                                    'H:i:s',
                                                                    $b->jam_mulai,
                                                                )->format('H:i')
                                                                : '-';
                                                            $end = $b->jam_selesai
                                                                ? \Carbon\Carbon::createFromFormat(
                                                                    'H:i:s',
                                                                    $b->jam_selesai,
                                                                )->format('H:i')
                                                                : '-';
                                                        @endphp
                                                        {{ $start }} – {{ $end }}
                                                    </td>
                                                    <td>{{ $b->ruangan->nama ?? '-' }}</td>
                                                    <td class="text-truncate" style="max-width:240px">
                                                        {{ $b->keperluan ?? '-' }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-lg-3">
                <div class="card">
                    <div class="card-body py-4 px-4">
                        <div class="d-flex align-items-center">
                            <div class="avatar avatar-xl">
                                <img src="{{ asset('dist/assets/compiled/jpg/1.jpg') }}" alt="Face 1">
                            </div>
                            <div class="ms-3 name">
                                <h5 class="font-bold">{{ Auth::user()->name }}</h5>
                                <h6 class="text-muted mb-0">
                                    {{ Auth::user()->roles->pluck('name')->implode(', ') ?: 'Tanpa role' }}
                                </h6>

                            </div>
                        </div>
                    </div>
                </div>
                <div class="card">
                    <div class="card-header">
                        <h4>
                            @if ($isAdmin)
                                Booking Baru
                            @else
                                Bookingan Saya
                            @endif
                        </h4>
                    </div>

                    <div class="card-content pb-4">
                        @forelse($recentBookings as $b)
                            <div class="recent-message d-flex px-4 py-3 border-bottom">
                                <div class="avatar avatar-lg">
                                    {{-- pakai avatar statis / inisial --}}
                                    <img src="{{ asset('dist/assets/compiled/jpg/4.jpg') }}" alt="avatar">
                                </div>
                                <div class="name ms-4">
                                    <h5 class="mb-1">{{ $b->keperluan }}</h5>
                                    <h6 class="text-muted mb-1">
                                        {{ $b->tanggal }} •
                                        {{ \Carbon\Carbon::createFromFormat('H:i:s', $b->jam_mulai)->format('H:i') }}
                                        – {{ \Carbon\Carbon::createFromFormat('H:i:s', $b->jam_selesai)->format('H:i') }}
                                    </h6>
                                    <div class="small text-muted">
                                        Ruangan: <strong>{{ $b->ruangan->nama ?? '-' }}</strong>
                                        @if ($isAdmin)
                                            • PIC: <strong>{{ $b->pemesan->name ?? '-' }}</strong>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="px-4 py-3 text-center text-muted">Belum ada booking.</div>
                        @endforelse

                        <div class="px-4">
                            <a href="{{ route('reports.ruangan.index') }}"
                                class="btn btn-block btn-xl btn-outline-primary font-bold mt-3">
                                Lihat Selengkapnya
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const elChart = document.getElementById('chart-booking');
            const form = document.getElementById('chartFilter');
            const inputStart = document.getElementById('start');
            const inputEnd = document.getElementById('end');
            const selType = document.getElementById('chartType');

            // Inisialisasi chart kosong
            const isDark = document.documentElement.classList.contains('dark');
            let chart = new ApexCharts(elChart, {
                chart: {
                    type: 'area',
                    height: 320,
                    toolbar: {
                        show: false
                    },
                    fontFamily: 'inherit'
                },
                series: [{
                    name: 'Booking',
                    data: []
                }],
                xaxis: {
                    categories: []
                },
                dataLabels: {
                    enabled: false
                },
                stroke: {
                    curve: 'smooth',
                    width: 2
                },
                markers: {
                    size: 3
                },
                fill: {
                    type: 'gradient',
                    gradient: {
                        shadeIntensity: 1,
                        opacityFrom: 0.35,
                        opacityTo: 0.05
                    }
                },
                grid: {
                    strokeDashArray: 4
                },
                theme: {
                    mode: isDark ? 'dark' : 'light'
                },
                tooltip: {
                    x: {
                        formatter: (val) => {
                            if (!val) return '';
                            const [y, m, d] = val.split('-');
                            const dt = new Date(`${y}-${m}-${d}T00:00:00`);
                            return dt.toLocaleDateString('id-ID', {
                                weekday: 'long',
                                day: '2-digit',
                                month: 'long',
                                year: 'numeric'
                            });
                        }
                    },
                    y: {
                        formatter: (v) => `${v} booking`
                    }
                },
                xaxis: {
                    categories: [],
                    labels: {
                        rotate: -15,
                        formatter: (val) => {
                            if (!val) return '';
                            const [y, m, d] = val.split('-');
                            const dt = new Date(`${y}-${m}-${d}T00:00:00`);
                            return dt.toLocaleDateString('id-ID', {
                                day: '2-digit',
                                month: 'short'
                            });
                        }
                    },
                    axisBorder: {
                        show: false
                    },
                    axisTicks: {
                        show: false
                    }
                },
                yaxis: {
                    labels: {
                        formatter: (v) => Math.round(v)
                    }
                },
            });
            chart.render();

            function buildUrl() {
                const params = new URLSearchParams();
                if (inputStart.value) params.set('start', inputStart.value);
                if (inputEnd.value) params.set('end', inputEnd.value);
                return `{{ route('dashboard.chart') }}?` + params.toString();
            }

            async function loadChart(e) {
                if (e) e.preventDefault();
                try {
                    const res = await fetch(buildUrl(), {
                        headers: {
                            'Accept': 'application/json'
                        }
                    });
                    if (!res.ok) throw new Error('HTTP ' + res.status);
                    const json = await res.json();

                    // Update data
                    await chart.updateOptions({
                        chart: {
                            type: selType.value || 'area'
                        },
                        xaxis: {
                            categories: json.labels || []
                        }
                    });
                    await chart.updateSeries([{
                        name: 'Booking',
                        data: json.series || []
                    }]);
                } catch (err) {
                    console.error(err);
                }
            }

            // Apply filter
            form.addEventListener('submit', loadChart);
            // Ubah tipe chart on-change tanpa submit
            selType.addEventListener('change', () => chart.updateOptions({
                chart: {
                    type: selType.value
                }
            }));

            // Load awal (default 14 hari)
            loadChart();
        });
    </script>

    <script>
        $(function() {
            $('#myBookingsTable').DataTable({
                responsive: true,
                pageLength: 10,
                lengthMenu: [5, 10, 25, 50],
                order: [
                    [0, 'desc']
                ], // sort Tanggal terbaru ke atas
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.13.8/i18n/id.json'
                },
                columnDefs: [{
                    targets: 3,
                    render: function(data, type) {
                        // biar pas search full text, tapi tampilan tetap ter-truncate oleh CSS
                        return type === 'display' ? data : $('<div>' + data + '</div>').text();
                    }
                }]
            });
        });
    </script>
@endsection
