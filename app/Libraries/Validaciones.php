<?php

namespace App\Libraries;

class Validaciones {

    public static function nombre(string $valor): string {
        $valor = trim($valor);
        $valor = ucwords(strtolower($valor));
        return ucfirst($valor);
    }

    public static function ci(string $valor): string {
        return preg_replace('/[^0-9]/', '', trim($valor));
    }

    public static function email(string $valor): string {
        return strtolower(trim($valor));
    }

    public static function telefono(string $valor): string {
        return preg_replace('/[^0-9+]/', '', trim($valor));
    }

    public static function texto(string $valor): string {
        return trim(preg_replace('/\s+/', ' ', $valor));
    }

    public static function fecha($valor): ?string {
        if(empty($valor)) return null;
        $fecha = \Carbon\Carbon::parse($valor);
        return $fecha ? $fecha->format('Y-m-d') : null;
    }

    public static function hora($valor): ?string {
        if(empty($valor)) return null;
        $fecha = \Carbon\Carbon::parse($valor);
        return $fecha ? $fecha->format('H:i') : null;
    }

    public static function precio($valor): float {
        return (float) number_format((float) str_replace([',','$','Bs'], '', $valor), 2, '.', '');
    }

    public static function validarCi(string $ci): array {
        $ci = self::ci($ci);
        $valido = preg_match('/^[0-9]{5,10}$/', $ci) === 1;
        return [
            'valido' => $valido,
            'valor' => $ci,
            'mensaje' => $valido ? '' : 'El CI debe tener entre 5 y 10 digitos numericos'
        ];
    }

    public static function validarEmail(string $email): array {
        $email = self::email($email);
        $valido = filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
        return [
            'valido' => $valido,
            'valor' => $email,
            'mensaje' => $valido ? '' : 'El email no tiene un formato valido'
        ];
    }

    public static function validarEdad($edad): array {
        $edad = (int) $edad;
        $valido = $edad >= 0 && $edad <= 120;
        return [
            'valido' => $valido,
            'valor' => $edad,
            'mensaje' => $valido ? '' : 'La edad debe estar entre 0 y 120 anos'
        ];
    }

    public static function validarTelefono(string $telefono): array {
        $telefono = self::telefono($telefono);
        $digitos = preg_replace('/[^0-9]/', '', $telefono);
        $valido = strlen($digitos) >= 7;
        return [
            'valido' => $valido,
            'valor' => $telefono,
            'mensaje' => $valido ? '' : 'El telefono debe tener al menos 7 digitos'
        ];
    }

    public static function validarPrecio($precio): array {
        $precio = self::precio($precio);
        $valido = $precio >= 0;
        return [
            'valido' => $valido,
            'valor' => $precio,
            'mensaje' => $valido ? '' : 'El precio no puede ser negativo'
        ];
    }

    public static function validarFechaNoFutura($fecha, string $campo = 'Fecha'): array {
        $fecha = self::fecha($fecha);
        if(!$fecha) {
            return ['valido' => false, 'valor' => null, 'mensaje' => $campo . ' no es una fecha valida'];
        }
        $hoy = \Carbon\Carbon::today()->format('Y-m-d');
        $valido = $fecha <= $hoy;
        return [
            'valido' => $valido,
            'valor' => $fecha,
            'mensaje' => $valido ? '' : $campo . ' no puede ser una fecha futura'
        ];
    }

    public static function validarFechaCita($fecha): array {
        $fecha = self::fecha($fecha);
        if(!$fecha) {
            return ['valido' => false, 'valor' => null, 'mensaje' => 'Fecha de cita no valida'];
        }
        $hoy = \Carbon\Carbon::today()->format('Y-m-d');
        $valido = $fecha >= $hoy;
        return [
            'valido' => $valido,
            'valor' => $fecha,
            'mensaje' => $valido ? '' : 'La fecha de cita no puede ser anterior a hoy'
        ];
    }

    public static function validarHora($hora): array {
        $hora = self::hora($hora);
        if(!$hora) {
            return ['valido' => false, 'valor' => null, 'mensaje' => 'Hora no valida'];
        }
        $valido = preg_match('/^([0-1]?[0-9]|2[0-3]):[0-5][0-9]$/', $hora) === 1;
        return [
            'valido' => $valido,
            'valor' => $hora,
            'mensaje' => $valido ? '' : 'Hora no valida (formato HH:MM)'
        ];
    }

    public static function validarEstado(string $estado, array $estadosPermitidos): array {
        $valido = in_array($estado, $estadosPermitidos);
        return [
            'valido' => $valido,
            'valor' => $estado,
            'mensaje' => $valido ? '' : 'Estado no valido. Debe ser: ' . implode(', ', $estadosPermitidos)
        ];
    }

    public static function validarTipoMuestra(string $tipo): array {
        $permitidos = ['Sangre','Orina','Heces','Saliva','Tejido','Otro'];
        return self::validarEstado($tipo, $permitidos);
    }

    public static function validarPacienteCompleto(array $datos): array {
        $errores = [];
        $limpiados = [];

        if(!isset($datos['nombre_completo']) || trim($datos['nombre_completo']) === '') {
            $errores[] = 'El nombre completo es obligatorio';
        } else {
            $limpiados['nombre_completo'] = self::nombre($datos['nombre_completo']);
        }

        $ciVal = self::validarCi($datos['ci'] ?? '');
        if(!$ciVal['valido']) $errores[] = $ciVal['mensaje'];
        $limpiados['ci'] = $ciVal['valor'];

        $edadVal = self::validarEdad($datos['edad'] ?? 0);
        if(!$edadVal['valido']) $errores[] = $edadVal['mensaje'];
        $limpiados['edad'] = $edadVal['valor'];

        $sexoVal = self::validarEstado($datos['sexo'] ?? '', ['Masculino','Femenino']);
        if(!$sexoVal['valido']) $errores[] = $sexoVal['mensaje'];
        $limpiados['sexo'] = $sexoVal['valor'];

        if(!empty($datos['email'] ?? '')) {
            $emailVal = self::validarEmail($datos['email']);
            if(!$emailVal['valido']) $errores[] = $emailVal['mensaje'];
            $limpiados['email'] = $emailVal['valor'];
        } else {
            $limpiados['email'] = null;
        }

        if(!empty($datos['telefono'] ?? '')) {
            $telVal = self::validarTelefono($datos['telefono']);
            if(!$telVal['valido']) $errores[] = $telVal['mensaje'];
            $limpiados['telefono'] = $telVal['valor'];
        } else {
            $limpiados['telefono'] = null;
        }

        $limpiados['direccion'] = !empty($datos['direccion']) ? self::texto($datos['direccion']) : null;

        return [
            'valido' => empty($errores),
            'datos' => $limpiados,
            'errores' => $errores
        ];
    }

    public static function validarOrden(array $datos): array {
        $errores = [];
        $limpiados = [];

        $limpiados['medico_solicitante'] = !empty($datos['medico_solicitante']) ? self::texto($datos['medico_solicitante']) : 'Particular';
        $limpiados['diagnostico_presuntivo'] = !empty($datos['diagnostico_presuntivo']) ? self::texto($datos['diagnostico_presuntivo']) : null;
        $limpiados['examenes'] = $datos['examenes'] ?? [];

        if(empty($limpiados['examenes']) || !is_array($limpiados['examenes']) || count($limpiados['examenes']) < 1) {
            $errores[] = 'Debe seleccionar al menos un examen';
        }

        return [
            'valido' => empty($errores),
            'datos' => $limpiados,
            'errores' => $errores
        ];
    }

    public static function validarCita(array $datos): array {
        $errores = [];
        $limpiados = [];

        $fechaVal = self::validarFechaCita($datos['fecha'] ?? '');
        if(!$fechaVal['valido']) $errores[] = $fechaVal['mensaje'];
        $limpiados['fecha'] = $fechaVal['valor'];

        $horaVal = self::validarHora($datos['hora'] ?? '');
        if(!$horaVal['valido']) $errores[] = $horaVal['mensaje'];
        $limpiados['hora'] = $horaVal['valor'];

        $limpiados['tipo'] = !empty($datos['tipo']) ? self::texto($datos['tipo']) : 'Toma de muestra';
        $limpiados['motivo'] = !empty($datos['motivo']) ? self::texto($datos['motivo']) : null;

        return [
            'valido' => empty($errores),
            'datos' => $limpiados,
            'errores' => $errores
        ];
    }

    public static function validarMuestra(array $datos): array {
        $errores = [];
        $limpiados = [];

        $tipoVal = self::validarTipoMuestra($datos['tipo_muestra'] ?? 'Sangre');
        if(!$tipoVal['valido']) $errores[] = $tipoVal['mensaje'];
        $limpiados['tipo_muestra'] = $tipoVal['valor'];

        $limpiados['estado'] = !empty($datos['estado']) ? $datos['estado'] : 'Recolectado';
        $limpiados['observaciones'] = !empty($datos['observaciones']) ? self::texto($datos['observaciones']) : null;
        $limpiados['tecnico_asignado_id'] = !empty($datos['tecnico_asignado_id']) ? (int) $datos['tecnico_asignado_id'] : null;

        return [
            'valido' => empty($errores),
            'datos' => $limpiados,
            'errores' => $errores
        ];
    }

    public static function validarResultado(array $datos): array {
        $errores = [];
        $limpiados = [];

        $limpiados['resultado'] = !empty($datos['resultado']) ? self::texto($datos['resultado']) : '';
        $limpiados['valores_referencia'] = !empty($datos['valores_referencia']) ? self::texto($datos['valores_referencia']) : null;
        $limpiados['interpretacion'] = !empty($datos['interpretacion']) ? self::texto($datos['interpretacion']) : null;
        $limpiados['estado'] = !empty($datos['estado']) ? $datos['estado'] : 'Pendiente';
        $limpiados['observaciones_validacion'] = !empty($datos['observaciones_validacion']) ? self::texto($datos['observaciones_validacion']) : null;

        return [
            'valido' => empty($errores),
            'datos' => $limpiados,
            'errores' => $errores
        ];
    }

    public static function validarUsuario(array $datos): array {
        $errores = [];
        $limpiados = [];

        $limpiados['name'] = !empty($datos['name']) ? self::nombre($datos['name']) : '';
        if(empty($limpiados['name'])) $errores[] = 'El nombre es obligatorio';

        $emailVal = self::validarEmail($datos['email'] ?? '');
        if(!$emailVal['valido']) $errores[] = $emailVal['mensaje'];
        $limpiados['email'] = $emailVal['valor'];

        $limpiados['rol'] = !empty($datos['rol']) ? $datos['rol'] : 'Recepcionista';
        $limpiados['activo'] = isset($datos['activo']) ? (bool) $datos['activo'] : true;
        $limpiados['password'] = $datos['password'] ?? null;

        return [
            'valido' => empty($errores),
            'datos' => $limpiados,
            'errores' => $errores
        ];
    }
}
