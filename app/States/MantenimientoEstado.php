<?php
// namespace App\States;

// use Spatie\ModelStates\State;
// use Spatie\ModelStates\StateConfig;

// abstract class MantenimientoEstado extends State
// {
//     abstract public function label(): string;

//     public static function config(): StateConfig
//     {
//         return parent::config()
//             // Define el estado por defecto (opcional)
//             ->default(Pendiente::class)
//             // Define las transiciones permitidas
//             ->allowTransition(Pendiente::class, EnProgreso::class)
//             ->allowTransition(EnProgreso::class, Completado::class)
//             ->allowTransition(EnProgreso::class, Cancelado::class);
//     }
// }
