<?php

namespace App\Models;

use CodeIgniter\Model;

class UserModel extends Model
{
    protected $table            = 'users';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $allowedFields    = [
        'username', 'email', 'password', 'nama_lengkap', 'nik', 'nip',
        'no_hp', 'npwp', 'foto', 'ttd', 'role', 'remember_token'
    ];
    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    public function findByEmailOrUsername(string $login)
    {
        return $this->where('email', $login)
                    ->orWhere('username', $login)
                    ->first();
    }
}
