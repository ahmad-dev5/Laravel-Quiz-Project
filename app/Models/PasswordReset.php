<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PasswordReset extends Model
{
    // Disable timestamps for this model as it's not needed in our case
    public $timestamps = false;

    // Define the table for the model
    protected $table = 'password_resets';

    // Define the fillable fields
    protected $fillable = ['email', 'token', 'created_at'];

    // Let Laravel treat created_at as a Carbon date instance for easy comparison
    protected $dates = ['created_at'];
}
