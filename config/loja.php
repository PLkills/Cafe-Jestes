<?php
// Regras de negocio configuraveis (Cafe Jester)
return [
    'taxa_entrega'        => 6.00,   // R$ cobrado por pedido
    'frete_gratis_acima'  => 80.00,  // subtotal a partir do qual a taxa e zero (0 = desativa)
    'max_por_item'        => 10,     // quantidade maxima de cada linha
    'categorias_sem_personalizacao' => ['Graos'],
];
