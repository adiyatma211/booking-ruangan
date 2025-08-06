@extends('layouts.base')
@section('content')
    <div class="page-heading">
        <div class="page-title">
            <div class="row">
                <div class="col-12 col-md-6 order-md-1 order-last">
                    <h3>Management Roles</h3>
                    <p class="text-subtitle text-muted">Menu Management Roles </p>
                </div>
                <div class="col-12 col-md-6 order-md-2 order-first">
                    <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="index.html">Management Parameter</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Management Roles</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>


        <section class="section">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Table Roles</h5>
                    <button type="button" class="btn btn-outline-primary block" data-bs-toggle="modal"
                        data-bs-target="#roleModal">
                        Tambah Roles
                    </button>
                </div>

                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table" id="table1">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Nama</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($GetRoles as $a)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $a->name }}</td>
                                        <td>
                                            <a href="javascript:void(0)" class="btn btn-sm btn-warning btn-edit-role"
                                                data-id="{{ $a->id }}" data-name="{{ $a->name }}">
                                                <i class="bi bi-pencil-square"></i> Edit
                                            </a>

                                            <button type="button" class="btn btn-sm btn-danger btn-delete-role"
                                                data-id="{{ $a->id }}">
                                                <i class="bi bi-trash"></i> Delete
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </section>

        <!-- Modal -->
        <!-- Modal Tambah/Edit Role -->
        <div class="modal fade" id="roleModal" tabindex="-1" role="dialog" aria-labelledby="roleModalTitle"
            aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="roleModalTitle">Tambah Role</h5>
                        <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                            <i data-feather="x"></i>
                        </button>
                    </div>
                    <div class="modal-body">
                        <form id="formRole" class="form form-horizontal">
                            @csrf
                            <input type="hidden" name="id" id="role_id">
                            <div class="form-body">
                                <div class="row">
                                    <div class="col-md-4">
                                        <label for="role_name">Nama Role</label>
                                    </div>
                                    <div class="col-md-8 form-group">
                                        <input type="text" id="role_name" name="name" class="form-control" required>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light-secondary" data-bs-dismiss="modal">Tutup</button>
                        <button type="button" class="btn btn-primary ms-1" id="btnSaveRole">Simpan</button>
                    </div>
                </div>
            </div>
        </div>


        <!-- Script -->
        <script>
            $(document).ready(function() {

                // === Handle klik tombol Tambah Role ===
                $('button[data-bs-target="#roleModal"]').on('click', function() {
                    $('#roleModalTitle').text('Tambah Role');
                    $('#formRole').trigger("reset");
                    $('#role_id').val('');
                    $('#btnSaveRole').data('mode', 'create');
                    $('#roleModal').modal('show');
                });

                // === Handle klik tombol Edit Role ===
                $('.btn-edit-role').on('click', function() {
                    const id = $(this).data('id');
                    const name = $(this).data('name');

                    $('#roleModalTitle').text('Edit Role');
                    $('#role_name').val(name);
                    $('#role_id').val(id);
                    $('#btnSaveRole').data('mode', 'edit');
                    $('#roleModal').modal('show');
                });

                // === Simpan Role (Tambah atau Edit) ===
                $('#btnSaveRole').on('click', function() {
                    const mode = $(this).data('mode');
                    const id = $('#role_id').val();
                    const url = (mode === 'edit') ? `/roles/update/${id}` :
                        `{{ route('parameter.tambah.roles') }}`;
                    const method = (mode === 'edit') ? 'PUT' : 'POST';

                    const formData = {
                        name: $('#role_name').val(),
                        _token: $('input[name="_token"]').val()
                    };

                    if (mode === 'edit') {
                        formData._method = 'PUT';
                    }

                    $.ajax({
                        url: url,
                        method: 'POST', // Tetap POST, gunakan _method untuk spoofing PUT
                        data: formData,
                        dataType: 'json',
                        success: function(response) {
                            if (response.status === 'success') {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Berhasil!',
                                    text: response.message
                                }).then(() => {
                                    location.reload();
                                });
                            } else {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Gagal!',
                                    text: response.message
                                });
                            }
                        },
                        error: function(xhr) {
                            let message = 'Terjadi kesalahan';
                            if (xhr.responseJSON && xhr.responseJSON.errors) {
                                message = Object.values(xhr.responseJSON.errors).join('\n');
                            } else if (xhr.responseJSON && xhr.responseJSON.message) {
                                message = xhr.responseJSON.message;
                            }

                            Swal.fire({
                                icon: 'error',
                                title: 'Gagal!',
                                text: message
                            });
                        }
                    });
                });

            });
            // === Handle Delete Role ===
            $(document).on('click', '.btn-delete-role', function() {
                const id = $(this).data('id');

                Swal.fire({
                    title: 'Yakin ingin menghapus?',
                    text: "Data tidak akan dihapus permanen.",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Ya, Hapus',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: `/roles/delete/${id}`,
                            method: 'DELETE',
                            data: {
                                _token: $('input[name="_token"]').val()
                            },
                            success: function(response) {
                                if (response.status === 'success') {
                                    Swal.fire('Berhasil!', response.message, 'success').then(() => {
                                        location.reload();
                                    });
                                } else {
                                    Swal.fire('Gagal!', response.message, 'error');
                                }
                            },
                            error: function(xhr) {
                                Swal.fire('Error!', 'Terjadi kesalahan saat menghapus.', 'error');
                            }
                        });
                    }
                });
            });
        </script>
    @endsection
