<?php

namespace App\Http\Controllers\Parameter;

use App\Models\Role;
use App\Models\User;
use App\Models\ruangan;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;


class ParameterController extends Controller
{



    // *****
    // Users
    // *****

      // Menyimpan user baru
    public function TambahUsers(Request $request)
    {
        $request->validate([
            'name'     => 'required|string|max:255',
            'nik'      => 'required|integer',
            'email'    => 'required|email',
            'role_id'  => 'required|exists:roles,id'
        ]);
    
        // Cek apakah user dengan nik atau email sudah ada
        if (User::where('nik', $request->nik)->exists()) {
            return response()->json([
                'status'  => 'error',
                'message' => 'NIK sudah terdaftar!'
            ], 409);
        }
    
        if (User::where('email', $request->email)->exists()) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Email sudah terdaftar!'
            ], 409);
        }
    
        try {
            $user = User::create([
                'name'     => $request->name,
                'nik'      => $request->nik,
                'email'    => $request->email,
                'password' => Hash::make('12345678'),
            ]);
        
            // Ambil nama role berdasarkan role_id
            $roleName = Role::find($request->role_id)->name;
        
            // Assign role ke user
            $user->assignRole($roleName);
        
            return response()->json([
                'status'  => 'success',
                'message' => 'User berhasil ditambahkan!',
                'data'    => $user
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Gagal menambahkan user: ' . $e->getMessage()
            ], 500);
        }
    }
    
    
      public function updateUsers(Request $request, $id)
      {
          $request->validate([
              'name'  => 'required|string|max:255',
              'nik'   => 'required|string|max:20|unique:users,nik,' . $id,
              'email' => 'required|email|unique:users,email,' . $id,
              'role_id'  => 'required|exists:roles,id'
          ]);
  
          try {
              $user = User::findOrFail($id);
              $user->update([
                  'name'  => $request->name,
                  'nik'   => $request->nik,
                  'email' => $request->email,
              ]);
  
              $user->assignRole([$request->role]);
  
              return response()->json([
                  'status'  => 'success',
                  'message' => 'User berhasil diperbarui!',
                  'data'    => $user
              ]);
          } catch (\Exception $e) {
              return response()->json([
                  'status'  => 'error',
                  'message' => 'Gagal memperbarui user: ' . $e->getMessage()
              ], 500);
          }
      }

      public function destroy($id)
      {
          try {
              $user = User::findOrFail($id);
              $user->delete(); // pastikan model pakai SoftDeletes
              return response()->json([
                  'status'  => 'success',
                  'message' => 'User berhasil dihapus (soft delete).'
              ]);
          } catch (\Exception $e) {
              return response()->json([
                  'status'  => 'error',
                  'message' => 'Gagal menghapus user: ' . $e->getMessage()
              ], 500);
          }
      }
    // *****
    // Roles
    // *****
    public function TambahRoles(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);
    
        try {
            // Cek apakah role dengan nama yang sama sudah ada
            if (Role::where('name', $request->input('name'))->exists()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Role dengan nama tersebut sudah ada.'
                ], 409); // 409 Conflict
            }
        
            // Jika belum ada, buat role baru
            $role = Role::create([
                'name' => $request->input('name'),
                'guard_name' => 'web',
            ]);
        
            return response()->json([
                'status' => 'success',
                'message' => 'Role berhasil ditambahkan!',
                'data' => $role
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal menambahkan role: ' . $e->getMessage()
            ], 500);
        }
    }

    public function UpdateRoles(Request $request, $id)
    {
        try {
            $request->validate([
                'name' => 'required|string|max:255'
            ]);
    
            $role = Role::findOrFail($id);
            $role->update([
                'name' => $request->name
            ]);
    
            return response()->json([
                'status' => 'success',
                'message' => 'Role berhasil diperbarui!',
                'data' => $role
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validasi gagal',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }    
    public function DeleteRole($id)
    {
        try {
            $role = Role::findOrFail($id);
            $role->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Role berhasil dihapus !'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal menghapus role: ' . $e->getMessage()
            ], 500);
        }
    }

    // ***
    // Ruangan
    // ***

    public function simpanRuangan(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nama'     => 'required|string|max:255',
            'max_jam'  => 'required|integer|min:1|max:24',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validasi gagal!',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            ruangan::create([
                'nama'     => $request->nama,
                'max_jam'  => $request->max_jam,
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Ruangan berhasil ditambahkan.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal menambahkan ruangan: ' . $e->getMessage()
            ], 500);
        }
    }

    public function updateRuangan(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'nama'     => 'required|string|max:255',
            'max_jam'  => 'required|integer|min:1|max:24',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validasi gagal!',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $ruangan = ruangan::findOrFail($id);
            $ruangan->update([
                'nama'     => $request->nama,
                'max_jam'  => $request->max_jam,
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Ruangan berhasil diperbarui.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal memperbarui ruangan: ' . $e->getMessage()
            ], 500);
        }
    }

    public function hapusRuangan($id)
    {
        try {
            $ruangan = ruangan::findOrFail($id);
            $ruangan->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Ruangan berhasil dihapus.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal menghapus ruangan: ' . $e->getMessage()
            ], 500);
        }
    }

}
