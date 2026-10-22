<?php
// filtro de estado del listado de garantias (lo usan mostrar.php y datos.php)
const FILTROS_GARANTIA = ['proceso' => 'En proceso', 'todas' => 'Todas'] + GARANTIA_ESTADOS;

// valor valido del filtro; por defecto "En proceso"
function filtro_garantias($valor)
{
    return array_key_exists((string)$valor, FILTROS_GARANTIA) ? (string)$valor : 'proceso';
}

// condicion sql y parametros del filtro
function where_garantias($filtro)
{
    if ($filtro === 'proceso') {
        return ["g.estado <> 'Entregado'", []];
    }
    if ($filtro === 'todas') {
        return ['1 = 1', []];
    }
    return ['g.estado = ?', [$filtro]];
}
