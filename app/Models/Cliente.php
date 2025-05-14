<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
class Cliente extends Model{

    use HasFactory;
    protected $table = 'clientes';
    protected $fillable = [
        'dni', 'nombre', 'apellidos', 'telefono', 'direccion', 'correo', 'centro_trabajo', 'foto'
    ];
    public function prestamos(){
        return $this->hasMany(Prestamos::class, 'cliente_id');
    }
}
