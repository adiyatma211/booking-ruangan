@extends('layouts.base')

@section('content')
    <div class="page-heading">
        <h3>Jadwal Pemesanan Ruangan</h3>
        <p class="text-subtitle text-muted">Klik pada tanggal untuk melakukan pemesanan</p>
    </div>

    <div class="card">
        <div class="card-body">
            <div id="calendar"></div>
        </div>
    </div>

    <!-- Modal Form Pemesanan -->
    <div class="modal fade" id="pemesananModal" tabindex="-1" aria-labelledby="pemesananModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <form id="formPemesanan">
                @csrf
                <input type="hidden" name="tanggal" id="tanggal">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Form Pemesanan</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="keperluan" class="form-label">Keperluan</label>
                            <input type="text" class="form-control" id="keperluan" name="keperluan" required>
                        </div>
                        <div class="mb-3">
                            <label for="jam_mulai" class="form-label">Jam Mulai</label>
                            <input type="time" class="form-control" id="jam_mulai" name="jam_mulai" required>
                        </div>
                        <div class="mb-3">
                            <label for="durasi" class="form-label">Durasi (jam)</label>
                            <input type="number" class="form-control" id="durasi" name="durasi" min="1"
                                max="4" required>
                            <small class="text-muted">Maksimal 4 jam</small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">Simpan</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var calendarEl = document.getElementById('calendar');
            var calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                selectable: true,
                height: 600,
                dateClick: function(info) {
                    $('#tanggal').val(info.dateStr);
                    $('#formPemesanan')[0].reset();
                    $('#pemesananModal').modal('show');
                },
                events: [{
                        title: 'Meeting Staff',
                        start: '{{ now()->format('Y-m') }}-10T09:00:00',
                        end: '{{ now()->format('Y-m') }}-10T11:00:00',
                        color: '#0d6efd'
                    },
                    {
                        title: 'Presentasi Client',
                        start: '{{ now()->format('Y-m') }}-15T13:00:00',
                        end: '{{ now()->format('Y-m') }}-15T15:00:00',
                        color: '#dc3545'
                    }
                ]
            });
            calendar.render();
        });
    </script>
@endsection
