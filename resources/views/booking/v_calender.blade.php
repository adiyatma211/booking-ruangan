@extends('layouts.base')

@section('content')
    <div class="page-heading">
        <h3>Jadwal Pemesanan Ruangan</h3>
        <p class="text-subtitle text-muted">Klik pada tanggal untuk melakukan pemesanan</p>
    </div>

    <div class="card mb-3">
        <div class="card-body d-flex align-items-center justify-content-between flex-wrap">
            <div class="d-flex align-items-center gap-3">
                <div style="width:14px;height:14px;border-radius:3px;background: {{ $ruangan->warna }};"></div>
                <div>
                    <div class="fw-bold">{{ $ruangan->nama }}</div>
                    <div class="text-muted small">Durasi maksimal: {{ $ruangan->max_jam }} jam</div>
                </div>
            </div>

            <div class="btn-group mt-2 mt-sm-0" role="group" aria-label="Tampilan Kalender">
                <button class="btn btn-outline-primary btn-sm" id="btnMonth">Bulan</button>
                <button class="btn btn-outline-primary btn-sm" id="btnWeek">Minggu</button>
                <button class="btn btn-outline-primary btn-sm" id="btnList">Daftar</button>
            </div>
        </div>
    </div>

    <div class="card position-relative">
        <div class="card-body">
            <div id="calendar"></div>
            <div id="calendarLoading" class="position-absolute top-0 start-0 w-100 h-100 d-none"
                 style="background: rgba(255,255,255,0.6);">
                <div class="d-flex w-100 h-100 justify-content-center align-items-center">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Form Pemesanan -->
    <div class="modal fade" id="pemesananModal" tabindex="-1" aria-labelledby="pemesananModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <form id="formPemesanan">
                @csrf
                <input type="hidden" name="tanggal" id="tanggal">
                <input type="hidden" name="ruangan_id" id="ruangan_id" value="{{ $ruangan->id }}">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Form Pemesanan</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">

                        <div class="mb-3">
                            <label for="tanggal_display" class="form-label">Tanggal</label>
                            <input type="text" class="form-control" id="tanggal_display" disabled>
                        </div>

                        <div class="mb-3">
                            <label for="keperluan" class="form-label">Keperluan</label>
                            <textarea class="form-control" id="keperluan" name="keperluan" rows="2" required placeholder="Contoh: Rapat koordinasi, pelatihan, dsb."></textarea>

                        </div>
                        

                        <div class="mb-3">
                            <label for="jam_mulai" class="form-label">Jam Mulai</label>
                            <input type="time" class="form-control" id="jam_mulai" name="jam_mulai" required>
                        </div>

                        <div class="mb-3">
                            <label for="jam_selesai" class="form-label">Jam Selesai</label>
                            <input type="time" class="form-control" id="jam_selesai" name="jam_selesai" required>
                        </div>

                        <div class="mb-3">
                            <label for="durasi" class="form-label">Durasi (jam)</label>
                            <input type="text" class="form-control" id="durasi" name="durasi"
                                   readonly>
                            <small class="text-muted">Maksimal {{ $ruangan->max_jam }} jam</small>
                        </div>

                        <div class="mb-3">
                            <label for="keterangan" class="form-label">Keterangan Tambahan</label>
                            <textarea class="form-control" id="keterangan" name="keterangan" rows="3"
                                placeholder="Tambahkan catatan jika diperlukan..."></textarea>
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

    <!-- Calendar JS & Durasi Calculation -->
    <script>
        function getTextColor(bgColor) {
            if (!bgColor) return '#ffffff';
            const color = bgColor.replace('#', '');
            const r = parseInt(color.substr(0, 2), 16);
            const g = parseInt(color.substr(2, 2), 16);
            const b = parseInt(color.substr(4, 2), 16);
            const luminance = (0.299 * r + 0.587 * g + 0.114 * b) / 255;
            return luminance > 0.5 ? 'black' : 'white';
        }

        document.addEventListener('DOMContentLoaded', function () {
            const calendarEl = document.getElementById('calendar');
            const maxJam = {{ $ruangan->max_jam }};
    
            const calendar = new FullCalendar.Calendar(calendarEl, {
    initialView: 'dayGridMonth',
    selectable: true,
    height: 650,
    locale: 'id',
    navLinks: true,
    headerToolbar: {
        left: 'prev,next today',
        center: 'title',
        right: 'dayGridMonth,timeGridWeek,listWeek'
    },

    displayEventTime: false,
    buttonText: {
        today: 'Hari ini',
        month: 'Bulan',
        week: 'Minggu',
        day: 'Hari',
        list: 'Daftar'
    },
    eventTimeFormat: {
        hour: '2-digit',
        minute: '2-digit',
        hour12: false
    },

    dateClick: function (info) {
        const selectedDate = new Date(info.dateStr);
        const today = new Date();
        today.setHours(0, 0, 0, 0);

        if (selectedDate < today) {
            Swal.fire({
                icon: 'warning',
                title: 'Tanggal Tidak Valid',
                text: 'Tanggal yang dipilih sudah lewat. Silakan pilih hari ini atau setelahnya.',
                confirmButtonColor: '#3085d6',
                confirmButtonText: 'OK'
            });
            return;
        }

        $('#formPemesanan')[0].reset();
        $('#tanggal').val(info.dateStr);
        $('#tanggal_display').val(info.dateStr);
        $('#durasi').val('');
        $('#jam_selesai').val('');
        $('#pemesananModal').modal('show');
    },

    events: "{{ route('pemesanan.events') }}?ruangan_id={{ $ruangan->id }}",

    loading: function(isLoading) {
        const overlay = document.getElementById('calendarLoading');
        if (isLoading) {
            overlay.classList.remove('d-none');
        } else {
            overlay.classList.add('d-none');
        }
    },

    eventClick: function(info) {
        const { keperluan, jam, pic, ruangan } = info.event.extendedProps;

        Swal.fire({
            title: 'Agenda: ' + keperluan,
            html: `
                <b>Jam:</b> ${jam}<br>
                <b>PIC:</b> ${pic}<br>
                <b>RUANGAN:</b> ${ruangan}<br>
            `,
            icon: 'info',
            confirmButtonText: 'Tutup'
        });
    },

    eventDidMount: function(info) {
        const { keperluan, jam, ruangan, pic } = info.event.extendedProps;

        $(info.el).tooltip({
            title: `Jam: ${jam}\nRuangan: ${ruangan}\nAgenda: ${keperluan}\nPIC: ${pic}`,
            placement: 'top',
            trigger: 'hover',
            container: 'body'
        });
    },

    eventContent: function(arg) {
        const jam = arg.event.extendedProps.jam;
        const keperluan = arg.event.extendedProps.keperluan;
        const pic = arg.event.extendedProps.pic;
        const ruangan = arg.event.extendedProps.ruangan;

        // Ambil warna dari event
        const color = 
            arg.backgroundColor || 
            arg.event.backgroundColor || 
            arg.event._def?.ui?.backgroundColor || 
            arg.event.color || 
            '#3788d8';

        const textColor = getTextColor(color);

        return {
            html: `
                <div style="
                    background-color: ${color};
                    color: ${textColor};
                    border-radius: 8px;
                    padding: 6px;
                    font-size: 12px;
                    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
                    margin-bottom: 4px;
                    line-height: 1.4;
                ">
                    <strong>${jam}</strong><br>
                    ${keperluan}<br>
                    <em>${pic}</em><br>
                    <small><b>Ruangan:</b> ${ruangan}</small>
                </div>
            `
        };
    }
});
    
            calendar.render();
            // View switching buttons
            document.getElementById('btnMonth').addEventListener('click', () => calendar.changeView('dayGridMonth'));
            document.getElementById('btnWeek').addEventListener('click', () => calendar.changeView('timeGridWeek'));
            document.getElementById('btnList').addEventListener('click', () => calendar.changeView('listWeek'));
    
            // Hitung durasi otomatis
            function hitungDurasi() {
                const jamMulai = document.getElementById('jam_mulai').value;
                const jamSelesai = document.getElementById('jam_selesai').value;
    
                if (jamMulai && jamSelesai) {
                    const [jam1, menit1] = jamMulai.split(':').map(Number);
                    const [jam2, menit2] = jamSelesai.split(':').map(Number);
    
                    const mulai = new Date();
                    mulai.setHours(jam1, menit1);
    
                    const selesai = new Date();
                    selesai.setHours(jam2, menit2);
    
                    const diffMs = selesai - mulai;
                    const diffJam = diffMs / (1000 * 60 * 60);
    
                    if (diffJam <= 0) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Durasi Tidak Valid',
                            text: 'Jam selesai harus lebih besar dari jam mulai.',
                        });
                        document.getElementById('durasi').value = '';
                        return;
                    }
    
                    if (diffJam > maxJam) {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Durasi Melebihi Batas',
                            text: `Durasi maksimal adalah ${maxJam} jam.`,
                        });
                        document.getElementById('durasi').value = '';
                        return;
                    }
    
                    document.getElementById('durasi').value = diffJam.toFixed(1);
                } else {
                    document.getElementById('durasi').value = '';
                }
            }
    
            document.getElementById('jam_mulai').addEventListener('change', function() {
                // Auto set default 1 jam setelah jam mulai (maksimal maxJam)
                const val = this.value;
                if (val) {
                    const [h, m] = val.split(':').map(Number);
                    const end = new Date();
                    end.setHours(h + Math.min(1, maxJam), m);
                    const hh = String(end.getHours()).padStart(2, '0');
                    const mm = String(end.getMinutes()).padStart(2, '0');
                    document.getElementById('jam_selesai').value = `${hh}:${mm}`;
                }
                hitungDurasi();
            });
            document.getElementById('jam_selesai').addEventListener('change', hitungDurasi);
    
            // Submit form pemesanan
            $('#formPemesanan').on('submit', function (e) {
                e.preventDefault();
    
                Swal.fire({
                    title: 'Konfirmasi Pemesanan',
                    text: "Apakah data sudah benar?",
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Ya, Simpan!',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        const formData = $(this).serialize();
    
                        $.ajax({
                            url: "{{ route('pemesanan.store') }}",
                            method: "POST",
                            data: formData,
                            success: function (res) {
                                Swal.fire('Sukses', res.message, 'success').then(() => {
                                    $('#pemesananModal').modal('hide');
                                    calendar.refetchEvents();
                                });
                            },
                            error: function (xhr) {
                                let errorMsg = xhr.responseJSON?.message || 'Terjadi kesalahan.';
                                Swal.fire('Gagal', errorMsg, 'error');
                            }
                        });
                    }
                });
            });
        });
    </script>   
    
@endsection
