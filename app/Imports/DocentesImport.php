<?php

namespace App\Imports;

use App\Models\Docente;
use App\Models\Usuario;
use App\Models\Rol;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\SkipsErrors;

class DocentesImport implements ToCollection, WithHeadingRow, WithValidation, SkipsOnError
{
    use SkipsErrors;

    protected $resultados = [];
    protected $rolDocenteId;

    public function __construct()
    {
        // Obtener el rol de docente (puedes ajustar el nombre según tu sistema)
        $this->rolDocenteId = Rol::where('nombre', 'Docente')->first()?->id 
            ?? Rol::first()?->id; // Usar el primer rol si no existe "Docente"
    }

    /**
     * Procesar la colección de datos
     */
    public function collection(Collection $rows)
    {
        foreach ($rows as $index => $row) {
            try {
                DB::beginTransaction();

                // Generar username único
                $username = $this->generarUsername($row['nombre'], $row['apellidos']);
                
                // Generar email (si no viene en el archivo)
                $email = !empty($row['email']) 
                    ? $row['email'] 
                    : $this->generarEmail($row['nombre'], $row['apellidos']);
                
                // Generar contraseña temporal: Primera letra del nombre + CI
                $password = $this->generarPasswordTemporal($row['nombre'], $row['ci']);

                // Verificar si el código ya existe
                $docenteExistente = Docente::where('codigo', $row['codigo'])->first();
                
                if ($docenteExistente) {
                    $this->resultados[] = [
                        'fila' => $index + 2, // +2 porque Excel empieza en 1 y hay header
                        'codigo' => $row['codigo'],
                        'estado' => 'omitido',
                        'mensaje' => 'El código ya existe'
                    ];
                    DB::rollBack();
                    continue;
                }

                // Crear usuario
                $usuario = Usuario::create([
                    'username' => $username,
                    'email' => $email,
                    'password' => Hash::make($password),
                    'id_rol' => $this->rolDocenteId,
                ]);

                // Crear docente
                $docente = Docente::create([
                    'codigo' => $row['codigo'],
                    'nombre' => trim($row['nombre']),
                    'apellidos' => trim($row['apellidos']),
                    'ci' => $row['ci'] ?? null,
                    'telefono' => $row['telefono'] ?? null,
                    'email' => $email,
                    'id_usuario' => $usuario->id,
                ]);

                $this->resultados[] = [
                    'fila' => $index + 2,
                    'codigo' => $row['codigo'],
                    'nombre' => $docente->nombre . ' ' . $docente->apellidos,
                    'username' => $username,
                    'email' => $email,
                    'password_temporal' => $password,
                    'estado' => 'creado',
                    'mensaje' => 'Creado exitosamente'
                ];

                DB::commit();

            } catch (\Exception $e) {
                DB::rollBack();
                $this->resultados[] = [
                    'fila' => $index + 2,
                    'codigo' => $row['codigo'] ?? 'N/A',
                    'estado' => 'error',
                    'mensaje' => $e->getMessage()
                ];
            }
        }
    }

    /**
     * Reglas de validación
     */
    public function rules(): array
    {
        return [
            'codigo' => 'required|string|max:20',
            'nombre' => 'required|string|max:100',
            'apellidos' => 'required|string|max:100',
            'ci' => 'required|string|max:20',
            'telefono' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
        ];
    }

    /**
     * Mensajes de error personalizados
     */
    public function customValidationMessages()
    {
        return [
            'codigo.required' => 'El código es obligatorio',
            'nombre.required' => 'El nombre es obligatorio',
            'apellidos.required' => 'Los apellidos son obligatorios',
            'ci.required' => 'El CI es obligatorio (se usa para generar la contraseña)',
            'email.email' => 'El formato del email no es válido',
        ];
    }

    /**
     * Generar username único
     */
    private function generarUsername($nombre, $apellidos)
    {
        // Formato: primera letra del nombre + apellidos (sin espacios, minúsculas)
        $base = strtolower(substr($nombre, 0, 1) . str_replace(' ', '', $apellidos));
        
        // Remover caracteres especiales
        $base = $this->removerAcentos($base);
        
        // Verificar si existe y agregar número si es necesario
        $username = $base;
        $contador = 1;
        
        while (Usuario::where('username', $username)->exists()) {
            $username = $base . $contador;
            $contador++;
        }
        
        return $username;
    }

    /**
     * Generar email único
     */
    private function generarEmail($nombre, $apellidos)
    {
        // Formato: nombre.apellidos@ficct.uagrm.edu.bo (ejemplo)
        $base = strtolower($nombre . '.' . str_replace(' ', '.', $apellidos));
        $base = $this->removerAcentos($base);
        $email = $base . '@ficct.uagrm.edu.bo';
        
        // Verificar si existe y agregar número si es necesario
        $contador = 1;
        while (Usuario::where('email', $email)->exists()) {
            $email = $base . $contador . '@ficct.uagrm.edu.bo';
            $contador++;
        }
        
        return $email;
    }

    /**
     * Generar contraseña temporal
     * Formato: Primera letra del nombre + CI
     */
    private function generarPasswordTemporal($nombre, $ci)
    {
        if (empty($ci)) {
            // Si no hay CI, usar un formato alternativo
            return 'FICCT' . rand(1000, 9999);
        }
        
        // Primera letra del nombre + CI
        $primeraLetra = strtoupper(substr(trim($nombre), 0, 1));
        return $primeraLetra . $ci;
    }

    /**
     * Remover acentos y caracteres especiales
     */
    private function removerAcentos($string)
    {
        $acentos = [
            'á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u',
            'Á' => 'A', 'É' => 'E', 'Í' => 'I', 'Ó' => 'O', 'Ú' => 'U',
            'ñ' => 'n', 'Ñ' => 'N'
        ];
        
        return strtr($string, $acentos);
    }

    /**
     * Obtener resultados de la importación
     */
    public function getResultados()
    {
        return $this->resultados;
    }

    /**
     * Manejar errores de validación
     */
    public function onError(\Throwable $e)
    {
        // Los errores se manejan en la colección
    }
}
