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
                            <textarea class="form-control" id="keperluan" name="keperluan" rows="2" required
                                placeholder="Contoh: Rapat koordinasi, pelatihan, dsb."></textarea>

                        </div>


                        <div class="mb-3">
                            <label for="jam_mulai" class="form-label">Jam Mulai</label>
                            <input type="text" class="form-control" id="jam_mulai" name="jam_mulai" required>
                        </div>

                        <div class="mb-3">
                            <label for="jam_selesai" class="form-label">Jam Selesai</label>
                            <input type="text" class="form-control" id="jam_selesai" name="jam_selesai" required>
                        </div>

                        <div class="mb-3">
                            <label for="durasi" class="form-label">Durasi (jam)</label>
                            <input type="text" class="form-control" id="durasi" name="durasi" readonly>
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
                            <textarea class="form-control" id="keperluan" name="keperluan" rows="2" required
                                placeholder="Contoh: Rapat koordinasi, pelatihan, dsb."></textarea>
                        </div>

                        <div class="mb-3">
                            <label for="jam_mulai" class="form-label">Jam Mulai</label>
                            <input type="text" class="form-control" id="jam_mulai" name="jam_mulai"
                                inputmode="numeric" required placeholder="HH:MM">
                        </div>

                        <div class="mb-3">
                            <label for="jam_selesai" class="form-label">Jam Selesai</label>
                            <input type="text" class="form-control" id="jam_selesai" name="jam_selesai"
                                inputmode="numeric" required placeholder="HH:MM">
                        </div>

                        <div class="mb-3">
                            <label for="durasi" class="form-label">Durasi (jam)</label>
                            <input type="text" class="form-control" id="durasi" name="durasi" readonly>
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

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const maxJam = {{ $ruangan->max_jam }};
            const calendarEl = document.getElementById('calendar');
            let lastValidEnd = '';

            // ---- Helpers
            function parseHM(hm) {
                const [h, m] = hm.split(':').map(Number);
                return {
                    h,
                    m
                };
            }

            function addHours(hm, add) {
                const base = new Date();
                const {
                    h,
                    m
                } = parseHM(hm);
                base.setHours(h, m, 0, 0);
                base.setTime(base.getTime() + add * 3600 * 1000);
                return `${String(base.getHours()).padStart(2,'0')}:${String(base.getMinutes()).padStart(2,'0')}`;
            }

            function getTextColor(bg) {
                if (!bg) return '#fff';
                const c = bg.replace('#', '');
                const r = parseInt(c.substr(0, 2), 16),
                    g = parseInt(c.substr(2, 2), 16),
                    b = parseInt(c.substr(4, 2), 16);
                return ((0.299 * r + 0.587 * g + 0.114 * b) / 255) > 0.5 ? 'black' : 'white';
            }

            function isValidHHMM(v) {
                if (!/^\d{2}:\d{2}$/.test(v)) return false;
                const {
                    h,
                    m
                } = parseHM(v);
                return h >= 0 && h <= 23 && m >= 0 && m <= 59;
            }

            // ---- Masking HH:MM (tanpa lib)
            function attachTimeMask(el, onUpdate) {
                // Ketik: format ke HH:MM
                el.addEventListener('input', () => {
                    let digits = el.value.replace(/\D/g, '').slice(0, 4); // max 4 digit
                    if (digits.length >= 3) {
                        el.value = digits.slice(0, 2) + ':' + digits.slice(2);
                    } else if (digits.length >= 1) {
                        el.value = digits; // biarin dulu (1-2 digit) sebelum “:”
                    } else {
                        el.value = '';
                    }
                    if (typeof onUpdate === 'function') onUpdate();
                });

                // Batasi karakter yang boleh ditekan (angka, control, colon)
                el.addEventListener('keydown', (e) => {
                    const allowedKeys = ['Backspace', 'Delete', 'Tab', 'ArrowLeft', 'ArrowRight', 'Home',
                        'End', 'Enter'
                    ];
                    if (allowedKeys.includes(e.key)) return;
                    if (e.ctrlKey || e.metaKey) return; // copy/paste/select all
                    if (e.key === ':') return;
                    if (!/^\d$/.test(e.key)) {
                        e.preventDefault();
                    }
                });

                // Paste: sanitasi
                el.addEventListener('paste', (e) => {
                    e.preventDefault();
                    const text = (e.clipboardData || window.clipboardData).getData('text') || '';
                    let digits = text.replace(/\D/g, '').slice(0, 4);
                    if (digits.length >= 3) el.value = digits.slice(0, 2) + ':' + digits.slice(2);
                    else if (digits.length >= 1) el.value = digits;
                    else el.value = '';
                    if (typeof onUpdate === 'function') onUpdate();
                });

                // Blur: validasi range; kalau invalid, kosongkan & kasih info
                el.addEventListener('blur', () => {
                    if (!el.value) return;
                    // Auto-pad misal "9:5" jadi "09:05"
                    let v = el.value;
                    if (/^\d{1,2}:\d{1,2}$/.test(v)) {
                        const [a, b] = v.split(':');
                        v = String(a).padStart(2, '0') + ':' + String(b).padStart(2, '0');
                        el.value = v;
                    }
                    if (!isValidHHMM(el.value)) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Format Waktu Salah',
                            text: 'Gunakan format HH:MM (00–23 : 00–59).'
                        });
                        el.value = '';
                    }
                    if (typeof onUpdate === 'function') onUpdate();
                });
            }

            // ---- Init Flatpickr (24 jam, allowInput)
            const elMulai = document.getElementById('jam_mulai');
            const elSelesai = document.getElementById('jam_selesai');

            const fpMulai = elMulai._flatpickr ?? flatpickr(elMulai, {
                enableTime: true,
                noCalendar: true,
                dateFormat: "H:i",
                time_24hr: true,
                minuteIncrement: 5,
                allowInput: true,
                onChange: syncAfterStartChange,
                onValueUpdate: syncAfterStartChange
            });

            const fpSelesai = elSelesai._flatpickr ?? flatpickr(elSelesai, {
                enableTime: true,
                noCalendar: true,
                dateFormat: "H:i",
                time_24hr: true,
                minuteIncrement: 5,
                allowInput: true,
                onChange: hitungDurasi,
                onValueUpdate: hitungDurasi
            });

            // Pasang masking ke kedua input
            attachTimeMask(elMulai, syncAfterStartChange);
            attachTimeMask(elSelesai, hitungDurasi);

            function syncAfterStartChange() {
                const start = elMulai.value.trim();
                // Jangan set maxTime agar tidak auto-klamp; hanya set minTime
                fpSelesai.set('minTime', null);
                if (isValidHHMM(start)) fpSelesai.set('minTime', start);
                hitungDurasi();
            }

            function hitungDurasi() {
                const start = elMulai.value.trim();
                const end = elSelesai.value.trim();
                const durasiEl = document.getElementById('durasi');

                if (!isValidHHMM(start) || !isValidHHMM(end)) {
                    durasiEl.value = '';
                    return;
                }

                const s = parseHM(start),
                    e = parseHM(end);
                const d1 = new Date(),
                    d2 = new Date();
                d1.setHours(s.h, s.m, 0, 0);
                d2.setHours(e.h, e.m, 0, 0);
                const diff = (d2 - d1) / 3600000;

                if (diff <= 0) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Durasi Tidak Valid',
                        text: 'Jam selesai harus > jam mulai.'
                    });
                    if (lastValidEnd) {
                        fpSelesai.setDate(lastValidEnd, true);
                    } else {
                        fpSelesai.clear();
                    }
                    durasiEl.value = '';
                    return;
                }
                if (diff > maxJam) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Durasi Melebihi Batas',
                        text: `Maksimal ${maxJam} jam.`
                    });
                    if (lastValidEnd) {
                        fpSelesai.setDate(lastValidEnd, true);
                    } else {
                        fpSelesai.clear();
                    }
                    durasiEl.value = '';
                    return;
                }

                lastValidEnd = end;
                durasiEl.value = diff.toFixed(1);
            }

            // ---- FullCalendar (tetap seperti sebelumnya)
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

                dateClick(info) {
                    const selectedDate = new Date(info.dateStr);
                    const today = new Date();
                    today.setHours(0, 0, 0, 0);
                    if (selectedDate < today) {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Tanggal Tidak Valid',
                            text: 'Pilih hari ini atau setelahnya.'
                        });
                        return;
                    }

                    document.getElementById('formPemesanan').reset();
                    const insMulai = elMulai._flatpickr,
                        insSelesai = elSelesai._flatpickr;
                    if (insMulai) insMulai.clear();
                    if (insSelesai) insSelesai.clear();
                    fpSelesai.set('minTime', null);
                    lastValidEnd = '';

                    document.getElementById('tanggal').value = info.dateStr;
                    document.getElementById('tanggal_display').value = info.dateStr;
                    document.getElementById('durasi').value = '';

                    bootstrap.Modal.getOrCreateInstance(document.getElementById('pemesananModal')).show();
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

                eventClick(info) {
                    const {
                        keperluan,
                        jam,
                        pic,
                        ruangan
                    } = info.event.extendedProps;
                    Swal.fire({
                        title: 'Agenda: ' + keperluan,
                        html: `<b>Jam:</b> ${jam}<br><b>PIC:</b> ${pic}<br><b>RUANGAN:</b> ${ruangan}<br>`,
                        icon: 'info',
                        confirmButtonText: 'Tutup'
                    });
                },

                eventDidMount(info) {
                    const {
                        keperluan,
                        jam,
                        ruangan,
                        pic
                    } = info.event.extendedProps;
                    const title = `Jam: ${jam}\nRuangan: ${ruangan}\nAgenda: ${keperluan}\nPIC: ${pic}`;
                    new bootstrap.Tooltip(info.el, {
                        title,
                        placement: 'top',
                        trigger: 'hover',
                        container: 'body'
                    });
                },

                eventContent(arg) {
                    const {
                        jam,
                        keperluan,
                        pic,
                        ruangan
                    } = arg.event.extendedProps;
                    const color = arg.backgroundColor || arg.event.backgroundColor || arg.event._def?.ui
                        ?.backgroundColor || arg.event.color || '#3788d8';
                    const textColor = getTextColor(color);
                    return {
                        html: `
          <div style="background-color:${color};color:${textColor};border-radius:8px;padding:6px;font-size:12px;box-shadow:0 2px 4px rgba(0,0,0,0.1);margin-bottom:4px;line-height:1.4;">
            <strong>${jam}</strong><br>${keperluan}<br><em>${pic}</em><br>
            <small><b>Ruangan:</b> ${ruangan}</small>
          </div>`
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
                e.stopPropagation();

                setTimeout(() => {
                    Swal.fire({
                        title: 'Konfirmasi Pemesanan',
                        text: "Apakah data sudah benar?",
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonColor: '#3085d6',
                        cancelButtonColor: '#d33',
                        confirmButtonText: 'Ya, Simpan!',
                        cancelButtonText: 'Batal',
                        allowEnterKey: false,
                        stopKeydownPropagation: true,
                        allowOutsideClick: false,
                        allowEscapeKey: false
                    }).then((result) => {
                        if (!result.isConfirmed) return;

                        $.ajax({
                            url: "{{ route('pemesanan.store') }}",
                            method: "POST",
                            data: $('#formPemesanan').serialize(),
                            success: function(res) {
                                Swal.fire('Sukses', res.message, 'success')
                                    .then(() => {
                                        bootstrap.Modal.getInstance(document
                                                .getElementById(
                                                    'pemesananModal'))
                                            .hide();
                                        calendar.refetchEvents();
                                    });
                            },
                            error: function(xhr) {
                                let errorMsg = xhr.responseJSON?.message ||
                                    'Terjadi kesalahan.';
                                Swal.fire('Gagal', errorMsg, 'error');
                            }
                        });
                    });
                }, 0);
            });
        });
    </script>
@endsection
