<?php

namespace App\Providers;

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Schema::defaultStringLength(191);

        Validator::extend('latitude', function ($attribute, $value, $parameters, $validator) {
            return preg_match('/^[-]?(([0-8]?[0-9])\.(\d+))|(90(\.0+)?)$/', $value);
        });

        Validator::extend('longitude', function ($attribute, $value, $parameters, $validator) {
            return preg_match('/^[-]?((1[0-7][0-9])\.(\d+))|([0-9]?[0-9])\.(\d+)|(180(\.0+)?)$/', $value);
        });

        Validator::extend('cpf', function ($attribute, $value, $parameters, $validator) {
            $cpf = preg_replace('/[^0-9]/', '', $value);

            if (strlen($cpf) != 11) {
                return false;
            }

            if (preg_match('/(\d)\1{10}/', $cpf)) {
                return false;
            }

            $sum = 0;
            for ($i = 0; $i < 9; $i++) {
                $sum += $cpf[$i] * (10 - $i);
            }
            $remainder = $sum % 11;
            $digit1 = ($remainder < 2) ? 0 : 11 - $remainder;

            $sum = 0;
            for ($i = 0; $i < 10; $i++) {
                $sum += $cpf[$i] * (11 - $i);
            }
            $remainder = $sum % 11;
            $digit2 = ($remainder < 2) ? 0 : 11 - $remainder;

            if ($cpf[9] != $digit1 || $cpf[10] != $digit2) {
                return false;
            }

            return true;
        });
    }
}
