# Plan de Integración: Ejercicio 2 (Auditoría de Inicio de Sesión)

Este documento detalla el plan técnico exacto para cumplir con el **Ejercicio 2** de la actividad, interceptando el inicio de sesión para guardar un registro de auditoría.

## 1. Estructura de Base de Datos
Se debe crear una tabla para almacenar el usuario, la dirección IP y la fecha/hora de acceso.

**Comando en terminal:**
```bash
php artisan make:model LoginAudit -m
```

**Archivo resultante (Migración `database/migrations/xxxx_xx_xx_create_login_audits_table.php`):**
```php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('login_audits', function (Blueprint $table) {
            $table->id();
            // Identificador del usuario que inicia sesión
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            // Dirección IP desde la que se conecta
            $table->string('ip_address', 45);
            // Fecha y hora exactas
            $table->timestamp('login_at');
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('login_audits');
    }
};
```

## 2. Modelo (Eloquent)
Se configura el modelo para permitir el guardado de los datos y se establece la relación con la tabla de Usuarios.

**Archivo: `app/Models/LoginAudit.php`**
```php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LoginAudit extends Model
{
    protected $fillable = ['user_id', 'ip_address', 'login_at'];
    
    protected $casts = [
        'login_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
```

## 3. Integración en el Controlador de Login
Modificaremos el controlador actual para registrar la auditoría inmediatamente después de que las credenciales son validadas con éxito.

**Archivo a modificar: `app/Http/Controllers/Auth/AuthController.php`**

**Paso A:** Añadir estas importaciones en la parte superior del archivo (debajo del `namespace`):
```php
use App\Models\LoginAudit;
use Illuminate\Support\Carbon;
```

**Paso B:** Modificar el método `login()` inyectando la creación del registro (justo antes del `return match`):
```php
    public function login(LoginRequest $request): RedirectResponse
    {
        $credentials = $request->only('username', 'password');
        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            return back()
                ->withInput($request->except('password'))
                ->withErrors([
                    'username' => 'Username atau password tidak valid.',
                ]);
        }

        $request->session()->regenerate();
        $user = Auth::user();
        
        // --- INTEGRACIÓN DE AUDITORÍA ---
        LoginAudit::create([
            'user_id' => $user->id,
            'ip_address' => $request->ip(),
            'login_at' => Carbon::now(),
        ]);
        // --------------------------------

        return match ($user->role) {
            'admin'    => redirect()->route('admin.dashboard'),
            'hr'       => redirect()->route('hr.dashboard'),
            'employee' => redirect()->route('employee.dashboard'),
            default    => redirect()->route('login'),
        };
    }
```
