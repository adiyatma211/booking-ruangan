@extends('layouts.base')
@section('content')
    <div class="page-heading">
        <div class="page-title">
            <div class="row">
                <div class="col-12 col-md-6 order-md-1 order-last">
                    <h3>Management Pengguna</h3>
                    <p class="text-subtitle text-muted">Menu Management Pengguna </p>
                </div>
                <div class="col-12 col-md-6 order-md-2 order-first">
                    <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="index.html">Management Parameter</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Management Pengguna</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>


        <section class="section">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Table Pengguna</h5>
                    <button type="button" class="btn btn-outline-primary block" data-bs-toggle="modal"
                        data-bs-target="#userModal">
                        Tambah Pengguna
                    </button>
                </div>

                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table" id="table1">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Nama</th>
                                    <th>NIK</th>
                                    <th>Email</th>
                                    <th>Role</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($GetUsers as $a)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $a->name }}</td>
                                        <td>{{ $a->nik }}</td>
                                        <td>{{ $a->email }}</td>
                                        <td>
                                            {{ $a->roles->pluck('name')->join(', ') ?: '-' }}
                                        </td>
                                        <td>
                                            <button class="btn btn-sm btn-warning btn-edit-user"
                                                data-id="{{ $a->id }}" data-name="{{ $a->name }}"
                                                data-nik="{{ $a->nik }}" data-email="{{ $a->email }}"
                                                data-role="{{ $a->role_id }}">
                                                <i class="bi bi-pencil-square"></i> Edit
                                            </button>

                                            <button class="btn btn-sm btn-danger btn-delete-user"
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

        <!-- Modal Tambah/Edit User -->
        <div class="modal fade" id="userModal" tabindex="-1" role="dialog" aria-labelledby="userModalTitle"
            aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="userModalTitle">Tambah Pengguna</h5>
                        <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                            <i data-feather="x"></i>
                        </button>
                    </div>
                    <div class="modal-body">
                        <form id="formUser" class="form form-horizontal">
                            @csrf
                            <input type="hidden" name="id" id="user_id">
                            <div class="form-group">
                                <label for="user_name">Nama</label>
                                <input type="text" id="user_name" name="name" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label for="user_nik">NIK</label>
                                <input type="text" id="user_nik" name="nik" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label for="user_email">Email</label>
                                <input type="email" id="user_email" name="email" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label for="user_role">Role</label>
                                <select id="user_role" name="role_id" class="form-control" required>
                                    <option value="">-- Pilih Role --</option>
                                    @foreach ($GetRoles as $role)
                                        <option value="{{ $role->id }}">{{ $role->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light-secondary" data-bs-dismiss="modal">Tutup</button>
                        <button type="button" class="btn btn-primary ms-1" id="btnSaveUser">Simpan</button>
                    </div>
                </div>
            </div>
        </div>


        <!-- Script -->
        <script>
            $(document).ready(function() {
                // Tambah user
                $('button[data-bs-target="#userModal"]').on('click', function() {
                    $('#userModalTitle').text('Tambah Pengguna');
                    $('#formUser').trigger("reset");
                    $('#user_id').val('');
                    $('#btnSaveUser').data('mode', 'create');
                    $('#userModal').modal('show');
                });

                // Edit user
                $('.btn-edit-user').on('click', function() {
                    const id = $(this).data('id');
                    const name = $(this).data('name');
                    const email = $(this).data('email');
                    const nik = $(this).data('nik');
                    const role_id = $(this).data('role');

                    $('#userModalTitle').text('Edit Pengguna');
                    $('#user_id').val(id);
                    $('#user_name').val(name);
                    $('#user_nik').val(nik);
                    $('#user_email').val(email);
                    $('#user_role').val(role_id);
                    $('#btnSaveUser').data('mode', 'edit');
                    $('#userModal').modal('show');
                });

                // Simpan User
                $('#btnSaveUser').on('click', function() {
                    const mode = $(this).data('mode');
                    const id = $('#user_id').val();
                    const url = (mode === 'edit') ? `/users/update/${id}` : `{{ route('users.store') }}`;
                    const method = (mode === 'edit') ? 'PUT' : 'POST';

                    const formData = {
                        name: $('#user_name').val(),
                        nik: $('#user_nik').val(),
                        email: $('#user_email').val(),
                        role_id: $('#user_role').val(),
                        _token: $('input[name="_token"]').val()
                    };

                    if (mode === 'edit') {
                        formData._method = 'PUT';
                    }

                    $.ajax({
                        url: url,
                        method: 'POST',
                        data: formData,
                        dataType: 'json',
                        success: function(response) {
                            Swal.fire('Berhasil!', response.message, 'success').then(() => location
                                .reload());
                        },
                        error: function(xhr) {
                            let message = 'Terjadi kesalahan';
                            if (xhr.responseJSON?.errors) {
                                message = Object.values(xhr.responseJSON.errors).join('\n');
                            }
                            Swal.fire('Gagal!', message, 'error');
                        }
                    });
                });

                // Hapus user
                $(document).on('click', '.btn-delete-user', function() {
                    const id = $(this).data('id');
                    Swal.fire({
                        title: 'Yakin ingin menghapus?',
                        text: "User tidak akan dihapus permanen.",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: 'Ya, Hapus'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            $.ajax({
                                url: `/users/delete/${id}`,
                                method: 'DELETE',
                                data: {
                                    _token: $('input[name="_token"]').val()
                                },
                                success: function(res) {
                                    Swal.fire('Berhasil!', res.message, 'success').then(
                                        () => location.reload());
                                },
                                error: function() {
                                    Swal.fire('Error!', 'Gagal menghapus user.', 'error');
                                }
                            });
                        }
                    });
                });
            });
        </script>
    @endsection
