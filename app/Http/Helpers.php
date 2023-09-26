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

if (!function_exists('get_normalized_string')) {
    /**
     * Returns a normalized version of the string by removing special characters.
     *
     * @param string $string The string to be normalized.
     * @return string The normalized string.
     */
    function get_normalized_string(string $string): string
    {
        return str_replace(['-', '.', '_', ' ', '(', ')'], '', $string);
    }
}

if (!function_exists('get_six_digits_cpf')) {
    /**
     * Returns the first six digits of the CPF.
     *
     * @param string $cpf The complete CPF.
     * @return string The first six digits of the CPF.
     */
    function get_six_digits_cpf(string $cpf): string
    {
        return substr($cpf, 0, 6);
    }
}

if (!function_exists('generate_image_urls')) {
    /**
     * Generates URLs for a given image and base URLs.
     *
     * @param string|null $image The image path or null if there is no image.
     * @param string $baseUrlHttp The base HTTP URL to be used in generation.
     * @param string $baseUrlHttps The base HTTPS URL to be used in generation.
     *
     * @return array An associative array containing URLs for HTTP and HTTPS.
     */
    function generate_image_urls(?string $image, string $baseUrlHttp, string $baseUrlHttps): array
    {
        return [
            'http' => $image !== null ? $baseUrlHttp . 'public/' . $image : '',
            'https' => $image !== null ? $baseUrlHttps . 'public/' . $image : ''
        ];
    }
}
