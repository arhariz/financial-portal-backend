<?php

declare(strict_types=1);

/**
 * Default deduction types locked by D-030 / COM-07. New types require a Decision (GOV-01).
 * "Mandatory note" from D-030 is encoded on admin_adjustment.
 */
return [
    'defaults' => [
        [
            'name' => 'loss_adjustment',
            'rules' => ['type' => 'loss_adjustment', 'enabled' => true],
        ],
        [
            'name' => 'reversal',
            'rules' => ['type' => 'reversal', 'enabled' => true],
        ],
        [
            'name' => 'admin_adjustment',
            'rules' => ['type' => 'admin_adjustment', 'enabled' => true, 'requires_note' => true],
        ],
    ],
];
