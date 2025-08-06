@extends('layouts.base')
@section('content')
    <div class="page-heading">
        <div class="page-title">
            <div class="row">
                <div class="col-12 col-md-6 order-md-1 order-last">
                    <h3>Manajemen Parameter Ruangan</h3>
                    <p class="text-subtitle text-muted">Atur daftar ruangan dan maksimal durasi pemakaian</p>
                </div>
            </div>
        </div>

        <section class="section">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Daftar Ruangan</h5>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#ruanganModal">Tambah
                        Ruangan</button>
                </div>

                <div class="card-body">
                    <table class="table table-striped" id="tableRuangan">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Nama Ruangan</th>
                                <th>Maksimal Waktu (jam)</th>
                                <th>Warna Label</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($ruangans as $ruangan)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $ruangan->nama }}</td>
                                    <td>{{ $ruangan->max_jam }} Jam</td>
                                    <td>{{ $ruangan->warna }}</td>
                                    <td>
                                        <button class="btn btn-warning btn-sm btn-edit" data-id="{{ $ruangan->id }}"
                                            data-nama="{{ $ruangan->nama }}" data-max="{{ $ruangan->max_jam }}">
                                            Edit
                                        </button>
                                        <button class="btn btn-danger btn-sm btn-delete" data-id="{{ $ruangan->id }}">
                                            Hapus
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </section>

        <!-- Modal Tambah/Edit -->
        <div class="modal fade" id="ruanganModal" tabindex="-1" role="dialog" aria-labelledby="ruanganModalLabel"
            aria-hidden="true">
            <div class="modal-dialog" role="document">
                <form id="formRuangan">
                    @csrf
                    <input type="hidden" name="id" id="ruangan_id">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="ruanganModalLabel">Tambah Ruangan</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-3">
                                <label for="nama" class="form-label">Nama Ruangan</label>
                                <input type="text" class="form-control" name="nama" id="nama" required>
                            </div>
                            <div class="mb-3">
                                <label for="max_jam" class="form-label">Maksimal Waktu (jam)</label>
                                <input type="number" class="form-control" name="max_jam" id="max_jam" min="1"
                                    max="24" required>
                            </div>
                            <div class="mb-3">
                                <label for="warna" class="form-label">Warna Label</label>
                                
                                <!-- Tempat Pickr muncul -->
                                <div id="warnaPicker"></div>
                            
                                <!-- Hidden input untuk simpan HEX ke database -->
                                <input type="hidden" name="warna" id="warna" value="#198754">
                            </div>
                            
                            
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                            <button type="submit" class="btn btn-primary">Simpan</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Script -->
    <script>
        const pickr = Pickr.create({
            el: '#warnaPicker',
            theme: 'classic', // classic / nano / monolith
            default: '#198754',
            components: {
                preview: true,
                opacity: true,
                hue: true,
                interaction: {
                    hex: true,
                    rgba: true,
                    input: true,
                    clear: true,
                    save: true
                }
            }
        });
        
        pickr.on('save', (color, instance) => {
            document.getElementById('warna').value = color.toHEXA().toString();
            pickr.hide();
        });
        </script>
    <script>
        // Buka modal untuk edit
        $(document).on('click', '.btn-edit', function() {
            $('#ruanganModalLabel').text('Edit Ruangan');
            $('#ruangan_id').val($(this).data('id'));
            $('#nama').val($(this).data('nama'));
            $('#max_jam').val($(this).data('max'));
            $('#ruanganModal').modal('show');
            $('#warna').val($(this).data('warna'));

        });

        // Submit form (Tambah/Edit)
        $('#formRuangan').on('submit', function(e) {
            e.preventDefault();

            const id = $('#ruangan_id').val();
            const url = id ? `/ruangan/update/${id}` : `{{ route('ruangan.store') }}`;
            const method = id ? 'PUT' : 'POST';

            $.ajax({
                url: url,
                method: 'POST',
                data: {
                    _token: $('input[name="_token"]').val(),
                    _method: method,
                    nama: $('#nama').val(),
                    max_jam: $('#max_jam').val(),
                    warna: $('#warna').val()
                    
                },
                success: function(response) {
                    Swal.fire('Berhasil!', response.message, 'success').then(() => {
                        location.reload();
                    });
                },
                error: function(xhr) {
                    const msg = xhr.responseJSON?.message || 'Terjadi kesalahan.';
                    Swal.fire('Error!', msg, 'error');
                }
            });
        });

        // Hapus data
        $(document).on('click', '.btn-delete', function() {
            const id = $(this).data('id');

            Swal.fire({
                title: 'Yakin ingin menghapus?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Ya, hapus',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: `/ruangan/delete/${id}`,
                        method: 'DELETE',
                        data: {
                            _token: '{{ csrf_token() }}'
                        },
                        success: function(response) {
                            Swal.fire('Berhasil!', response.message, 'success').then(() => {
                                location.reload();
                            });
                        },
                        error: function(xhr) {
                            Swal.fire('Error!', 'Gagal menghapus ruangan.', 'error');
                        }
                    });
                }
            });
        });
    </script>
@endsection
