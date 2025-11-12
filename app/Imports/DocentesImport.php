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
use Maatwebsite\Excel\Concerns\WithBatchInserts;

class DocentesImport implements ToCollection, WithHeadingRow, SkipsOnError, WithBatchInserts
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

                // Convertir todos los valores a string para evitar problemas con números
                $codigo = (string) ($row['codigo'] ?? '');
                $nombre = (string) ($row['nombre'] ?? '');
                $apellidos = (string) ($row['apellidos'] ?? '');
                $ci = (string) ($row['ci'] ?? '');
                $telefono = !empty($row['telefono']) ? (string) $row['telefono'] : null;
                $emailRaw = !empty($row['email']) ? (string) $row['email'] : null;

                // Validar campos obligatorios
                if (empty($codigo) || empty($nombre) || empty($apellidos) || empty($ci)) {
                    $this->resultados[] = [
                        'fila' => $index + 2,
                        'codigo' => $codigo ?: 'N/A',
                        'estado' => 'error',
                        'mensaje' => 'Faltan campos obligatorios (CODIGO, NOMBRE, APELLIDOS, CI)'
                    ];
                    DB::rollBack();
                    continue;
                }

                // Generar username único
                $username = $this->generarUsername($nombre, $apellidos);
                
                // Generar email (si no viene en el archivo)
                $email = $emailRaw ?: $this->generarEmail($nombre, $apellidos);
                
                // Generar contraseña temporal: Primera letra del nombre + CI
                $password = $this->generarPasswordTemporal($nombre, $ci);

                // Verificar si el código ya existe
                $docenteExistente = Docente::where('codigo', $codigo)->first();
                
                if ($docenteExistente) {
                    $this->resultados[] = [
                        'fila' => $index + 2, // +2 porque Excel empieza en 1 y hay header
                        'codigo' => $codigo,
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
                    'codigo' => $codigo,
                    'nombre' => trim($nombre),
                    'apellidos' => trim($apellidos),
                    'ci' => $ci,
                    'telefono' => $telefono,
                    'email' => $email,
                    'id_usuario' => $usuario->id,
                ]);

                $this->resultados[] = [
                    'fila' => $index + 2,
                    'codigo' => $codigo,
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
                    'codigo' => $codigo ?? 'N/A',
                    'estado' => 'error',
                    'mensaje' => $e->getMessage()
                ];
            }
        }
    }

    /**
     * Tamaño del lote para inserciones
     */
    public function batchSize(): int
    {
        return 100;
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
