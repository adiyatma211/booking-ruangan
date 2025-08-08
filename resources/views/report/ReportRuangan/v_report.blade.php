@extends('layouts.base')

@section('content')
    <div class="page-heading">
        <h3>Report Pemakaian Ruangan</h3>
        <p class="text-subtitle text-muted">Ringkasan pemakaian per ruangan / PIC dalam rentang tanggal.</p>
    </div>

    {{-- Filter --}}
    <div class="card mb-3">
        <div class="card-body">
            <form id="filterForm" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Tanggal Mulai</label>
                    <input type="date" class="form-control" id="start" name="start" value="{{ $start }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Tanggal Selesai</label>
                    <input type="date" class="form-control" id="end" name="end" value="{{ $end }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Ruangan</label>
                    <select class="form-select" id="ruangan_ids" name="ruangan_ids[]" multiple>
                        @foreach ($ruangans as $r)
                            <option value="{{ $r->id }}">{{ $r->nama }}</option>
                        @endforeach
                    </select>
                    <small class="text-muted">Kosongkan untuk semua ruangan.</small>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Group by</label>
                    <select class="form-select" id="group_by" name="group_by">
                        <option value="room" selected>Ruangan</option>
                        <option value="pic">PIC</option>
                        <option value="room_pic">Ruangan + PIC</option>
                    </select>
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="consolidate" name="consolidate" value="1">
                        <label class="form-check-label" for="consolidate">Konsolidasi</label>
                    </div>
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <button class="btn btn-primary w-100" type="submit">Terapkan</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Tabel --}}
    <div class="card">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="mb-0">Ringkasan</h5>
                <a id="exportCsv" class="btn btn-sm btn-outline-secondary" href="#">Export CSV</a>
            </div>
            <div class="table-responsive">
                <table class="table table-sm table-striped align-middle" id="reportTable">
                    <thead>
                        <tr>
                            <th id="col-nama">Nama</th>
                            <th class="text-end">Total Booking</th>
                            <th class="text-end">Total Jam</th>
                            <th class="text-end">Rata-rata Jam</th>
                            <th class="text-end">Utilization %</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td colspan="5" class="text-center text-muted">Silakan terapkan filter…</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Select2 (CDN) --}}
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Init Select2 (butuh jQuery sudah ada di layout)
            $('#ruangan_ids').select2({
                placeholder: "Pilih Ruangan...",
                allowClear: true,
                width: '100%'
            });

            const form = document.getElementById('filterForm');
            const tableBody = document.querySelector('#reportTable tbody');
            const exportBtn = document.getElementById('exportCsv');
            const colNama = document.getElementById('col-nama');

            function buildParams() {
                const fd = new FormData(form);
                const params = new URLSearchParams();
                params.set('start', fd.get('start') || '');
                params.set('end', fd.get('end') || '');

                // multiple select
                const sel = document.getElementById('ruangan_ids');
                Array.from(sel.selectedOptions).forEach(o => params.append('ruangan_ids[]', o.value));

                // group_by & consolidate
                params.set('group_by', document.getElementById('group_by').value || 'room');
                if (document.getElementById('consolidate').checked) params.set('consolidate', '1');

                return params;
            }

            function setHeaderByGroupBy(groupBy) {
                // Ubah header kolom pertama sesuai pilihan
                if (groupBy === 'pic') colNama.textContent = 'PIC';
                else if (groupBy === 'room_pic') colNama.textContent = 'Ruangan — PIC';
                else colNama.textContent = 'Ruangan';
            }

            async function loadData(e) {
                if (e) e.preventDefault();

                const start = document.getElementById('start').value;
                const end = document.getElementById('end').value;
                if (!start || !end) return;

                tableBody.innerHTML = `<tr><td colspan="5" class="text-center text-muted">Loading…</td></tr>`;

                const params = buildParams();
                const url = `{{ route('reports.ruangan.data') }}?` + params.toString();

                try {
                    const res = await fetch(url, {
                        headers: {
                            'Accept': 'application/json'
                        }
                    });
                    if (!res.ok) throw new Error('HTTP ' + res.status);
                    const json = await res.json();

                    // set header kolom pertama berdasar meta.group_by
                    setHeaderByGroupBy(json?.meta?.group_by || 'room');

                    const rows = json.data || [];
                    if (!rows.length) {
                        tableBody.innerHTML =
                            `<tr><td colspan="5" class="text-center text-muted">Tidak ada data pada rentang ini.</td></tr>`;
                        return;
                    }

                    tableBody.innerHTML = rows.map(r => `
          <tr>
            <td>${r.nama ?? r.ruangan ?? '-'}</td>
            <td class="text-end">${r.total_booking}</td>
            <td class="text-end">${r.total_jam}</td>
            <td class="text-end">${r.rata2_jam}</td>
            <td class="text-end">${r.utilization_pct}%</td>
          </tr>
        `).join('');

                } catch (err) {
                    tableBody.innerHTML =
                        `<tr><td colspan="5" class="text-center text-danger">Gagal memuat data.</td></tr>`;
                    console.error(err);
                }

                // set export link
                exportBtn.href = `{{ route('reports.ruangan.export') }}?` + buildParams().toString();
            }

            form.addEventListener('submit', loadData);
            // auto-load pertama
            loadData();
        });
    </script>
@endsection
