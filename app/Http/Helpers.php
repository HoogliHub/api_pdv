<?php

if (!function_exists('get_delivery_status')) {
    /**
     * Get the corresponding delivery status text for the given status code.
     *
     * @param string $status The delivery status code.
     * @return string The corresponding delivery status text.
     */
    function get_delivery_status(string $status): string
    {
        $delivery_status = [
            'cancelled' => 'CANCELADO',
            'pending' => 'A ENVIAR',
            'on_the_way' => 'A CAMINHO',
            'delivered' => 'ENTREGUE',
        ];

        return $delivery_status[$status] ?? 'Status Desconhecido';
    }
}

if (!function_exists('get_payment_status')) {
    /**
     * Get the corresponding payment status text for the given status code.
     *
     * @param string $status The payment status code.
     * @return string The corresponding payment status text.
     */
    function get_payment_status(string $status): string
    {
        $payment_status = [
            'paid' => 'PAGO',
            'unpaid' => 'PENDENTE DE PAGAMENTO',
        ];

        return $payment_status[$status] ?? 'Status Desconhecido';
    }
}

if (!function_exists('get_card_type')) {
    /**
     * Get the corresponding type card text for the given type code.
     *
     * @param string $type The type card code.
     * @return string The corresponding type card text.
     */
    function get_card_type(string $type): string
    {
        $card_type = [
            'credit_card' => 'Cartão de Crédito',
            'debit_card' => 'Cartão de débito'
        ];

        return $card_type[$type] ?? 'Desconhecido';
    }
}
