<?php

const PATRON_NOMBRE = '/^[A-Za-zÁÉÍÓÚáéíóúÑñÜü\s\'-]{2,60}$/u';
const PATRON_TELEFONO = '/^(\+52[\s-]?)?[0-9]{10}$/';
const PATRON_EMAIL = '/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/';
const PATRON_MARCA_MODELO = '/^[A-Za-z0-9ÁÉÍÓÚáéíóúÑñÜü\s\'-]{1,40}$/u';
const PATRON_ANIO = '/^(19[8-9][0-9]|20[0-2][0-9]|203[0-5])$/';
const PATRON_PLACA = '/^[A-Z0-9]{3,4}[-\s]?[0-9]{2,3}[-\s]?[A-Z0-9]{0,3}$/i';
const PATRON_VIN = '/^[A-HJ-NPR-Z0-9]{17}$/i';

function validar_registro_cliente(array $datos): array
{
    $errores = [];

    $camposNombre = [
        'nombres' => 'Nombres',
        'apPat' => 'Apellido paterno',
        'apMat' => 'Apellido materno',
    ];

    foreach ($camposNombre as $campo => $etiqueta) {
        $valor = trim($datos[$campo] ?? '');
        if ($valor === '' || !preg_match(PATRON_NOMBRE, $valor)) {
            $errores[] = "{$etiqueta}: solo letras, de 2 a 60 caracteres.";
        }
    }

    $telefono = trim($datos['tel'] ?? '');
    if ($telefono === '' || !preg_match(PATRON_TELEFONO, $telefono)) {
        $errores[] = 'Teléfono: ingrese 10 dígitos (opcional +52 al inicio).';
    }

    $email = trim($datos['email'] ?? '');
    if ($email === '' || !preg_match(PATRON_EMAIL, $email)) {
        $errores[] = 'Email: formato no válido.';
    }

    $marca = trim($datos['marca'] ?? '');
    if ($marca === '' || !preg_match(PATRON_MARCA_MODELO, $marca)) {
        $errores[] = 'Marca: solo letras y números, hasta 40 caracteres.';
    }

    $modelo = trim($datos['modelo'] ?? '');
    if ($modelo === '' || !preg_match(PATRON_MARCA_MODELO, $modelo)) {
        $errores[] = 'Modelo: solo letras y números, hasta 40 caracteres.';
    }

    $anio = trim((string) ($datos['anio'] ?? ''));
    if ($anio === '' || !preg_match(PATRON_ANIO, $anio)) {
        $errores[] = 'Año: debe estar entre 1980 y 2035.';
    }

    $placa = strtoupper(trim($datos['placa'] ?? ''));
    if ($placa === '' || !preg_match(PATRON_PLACA, $placa)) {
        $errores[] = 'Placa: formato no válido (ej. ABC1234 o ABC-12-34).';
    }

    $vin = strtoupper(trim($datos['vin'] ?? ''));
    if ($vin === '' || !preg_match(PATRON_VIN, $vin)) {
        $errores[] = 'VIN: debe tener exactamente 17 caracteres alfanuméricos.';
    }

    return $errores;
}

function datos_cliente_sanitizados(array $datos): array
{
    return [
        'nombres' => trim($datos['nombres'] ?? ''),
        'apPat' => trim($datos['apPat'] ?? ''),
        'apMat' => trim($datos['apMat'] ?? ''),
        'tel' => preg_replace('/\s+/', '', trim($datos['tel'] ?? '')),
        'email' => trim($datos['email'] ?? ''),
        'marca' => trim($datos['marca'] ?? ''),
        'modelo' => trim($datos['modelo'] ?? ''),
        'anio' => trim((string) ($datos['anio'] ?? $datos['año'] ?? '')),
        'placa' => strtoupper(trim($datos['placa'] ?? '')),
        'vin' => strtoupper(trim($datos['vin'] ?? $datos['VIN'] ?? '')),
    ];
}
