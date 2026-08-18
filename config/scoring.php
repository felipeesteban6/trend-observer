<?php

return [
    // Deben sumar 1.0. Ajustá según qué tan confiable te resulte cada fuente.
    'trend_weight' => (float) env('SCORING_TREND_WEIGHT', 0.6),
    'supplier_weight' => (float) env('SCORING_SUPPLIER_WEIGHT', 0.4),
];
