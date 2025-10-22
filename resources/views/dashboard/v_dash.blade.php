@extends('layouts.base')
@section('content')
    <div class="page-content">
        <section class="row">
            <div class="col-12 col-lg-9">
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
                                        <h6 class="font-extrabold mb-0">{{ $roomsCount ?? 0 }}</h6>
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
                                        <h6 class="text-muted font-semibold">Booking Hari Ini</h6>
                                        <h6 class="font-extrabold mb-0">{{ $bookingsTodayCount ?? 0 }}</h6>
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
                                        <h6 class="text-muted font-semibold">Jam Terbooking (hari ini)</h6>
                                        <h6 class="font-extrabold mb-0">{{ number_format($bookedHoursToday ?? 0, 1) }}<span class="text-muted" style="font-size:.8rem"> jam</span></h6>
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
                                        <div class="stats-icon red mb-2">
                                            <i class="iconly-boldBookmark"></i>
                                        </div>
                                    </div>
                                    <div class="col-md-8 col-lg-12 col-xl-12 col-xxl-7">
                                        <h6 class="text-muted font-semibold">Occupancy Hari Ini</h6>
                                        <h6 class="font-extrabold mb-0">{{ $occupancy ?? 0 }}<span class="text-muted" style="font-size:.8rem">%</span></h6>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body d-flex flex-wrap gap-2">
                                <a class="btn btn-primary" href="/booking">Buat Booking</a>
                                <a class="btn btn-outline-primary" href="/ruangan">Kelola Ruangan</a>
                                <a class="btn btn-outline-secondary" href="/reportRuangan">Report Penggunaan</a>
                                @if(!empty($nextBooking))
                                <span class="ms-auto text-muted small">Jadwal terdekat: {{ substr($nextBooking->jam_mulai,0,5) }} - {{ substr($nextBooking->jam_selesai,0,5) }}, {{ $nextBooking->ruangan->nama ?? 'Ruangan' }}</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h4>Penggunaan 7 Hari Terakhir</h4>
                            </div>
                            <div class="card-body">
                                <div id="chart-weekly-usage"></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12 col-xl-4">
                        <div class="card">
                            <div class="card-header">
                                <h4>Jadwal Terdekat</h4>
                            </div>
                            <div class="card-body">
                                <ul class="list-group">
                                    @forelse($upcoming as $item)
                                        <li class="list-group-item d-flex justify-content-between align-items-center">
                                            <div>
                                                <div class="fw-bold">{{ \Illuminate\Support\Carbon::parse($item->tanggal)->format('d M Y') }} • {{ substr($item->jam_mulai,0,5) }}-{{ substr($item->jam_selesai,0,5) }}</div>
                                                <div class="text-muted small">{{ $item->keperluan }} — {{ $item->ruangan->nama ?? 'Ruangan' }}</div>
                                            </div>
                                            <a class="btn btn-sm btn-outline-primary" href="{{ route('kalender', $item->ruangan_id) }}">Lihat</a>
                                        </li>
                                    @empty
                                        <li class="list-group-item text-muted">Tidak ada jadwal.</li>
                                    @endforelse
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-xl-8">
                        <div class="card">
                            <div class="card-header">
                                <h4>Ringkasan</h4>
                            </div>
                            <div class="card-body">
                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <div class="p-3 rounded border bg-light h-100">
                                            <div class="text-muted small">Ruangan</div>
                                            <div class="fw-bold fs-4">{{ $roomsCount ?? 0 }}</div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="p-3 rounded border bg-light h-100">
                                            <div class="text-muted small">Booking Hari Ini</div>
                                            <div class="fw-bold fs-4">{{ $bookingsTodayCount ?? 0 }}</div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="p-3 rounded border bg-light h-100">
                                            <div class="text-muted small">Occupancy</div>
                                            <div class="fw-bold fs-4">{{ $occupancy ?? 0 }}%</div>
                                        </div>
                                    </div>
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
                                <img src="{{ asset('dist/assets/compiled/jpg/1.jpg') }}" alt="User">
                            </div>
                            <div class="ms-3 name">
                                <h5 class="font-bold">{{ auth()->user()->name ?? 'User' }}</h5>
                                @php
                                    $email = auth()->user()->email ?? null;
                                    $handle = $email ? '@'.\Illuminate\Support\Str::before($email, '@') : '';
                                @endphp
                                <h6 class="text-muted mb-0">{{ $handle }}</h6>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card">
                    <div class="card-header">
                        <h4>Recent Booking User</h4>
                    </div>

                    <div class="card-content pb-4">
                        @forelse($recentBookings ?? [] as $b)
                            <div class="recent-message d-flex px-4 py-3">
                                <div class="avatar avatar-lg">
                                    <img src="{{ asset('dist/assets/compiled/jpg/1.jpg') }}" alt="user">
                                </div>
                                <div class="name ms-4">
                                    <h5 class="mb-1">{{ $b->user->name ?? 'User' }}</h5>
                                    <h6 class="text-muted mb-0 small">{{ $b->keperluan }} — {{ $b->ruangan->nama ?? 'Ruangan' }}</h6>
                                    <div class="text-muted small">{{ \Illuminate\Support\Carbon::parse($b->tanggal)->format('d M Y') }} • {{ substr($b->jam_mulai,0,5) }}-{{ substr($b->jam_selesai,0,5) }}</div>
                                </div>
                            </div>
                        @empty
                            <div class="px-4 py-3 text-muted">Belum ada booking terbaru.</div>
                        @endforelse
                        <div class="px-4">
                            <a href="/booking" class='btn btn-block btn-xl btn-outline-primary font-bold mt-3'>Lihat Kalender</a>
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

<script>
    document.addEventListener('DOMContentLoaded', function() {
        if (window.ApexCharts && document.querySelector('#chart-weekly-usage')) {
            const labels = @json($weeklyLabels ?? []);
            const seriesData = @json($weeklySeries ?? []);
            const options = {
                chart: { type: 'bar', height: 320, toolbar: { show: false } },
                series: [{ name: 'Jam Terpakai', data: seriesData }],
                xaxis: { categories: labels },
                colors: ['#435ebe'],
                dataLabels: { enabled: false },
                tooltip: { y: { formatter: (val) => `${val} jam` } }
            };
            const chart = new ApexCharts(document.querySelector('#chart-weekly-usage'), options);
            chart.render();
        }

        if (window.ApexCharts && document.querySelector('#chart-visitors-profile')) {
            const vLabels = @json($visitorLabels ?? []);
            const vSeries = @json($visitorSeries ?? []);
            const donut = new ApexCharts(document.querySelector('#chart-visitors-profile'), {
                chart: { type: 'donut', height: 300 },
                series: vSeries,
                labels: vLabels,
                legend: { position: 'bottom' },
                dataLabels: { enabled: true },
                tooltip: { y: { formatter: (val) => `${val} jam` } }
            });
            donut.render();
        }
    });
    </script>
