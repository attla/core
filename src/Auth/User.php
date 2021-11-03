<?php

namespace Attla\Auth;

use Attla\Database\Eloquent;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
use Attla\Auth\Traits\{
    Authenticatable,
    CanResetPassword,
    MustVerifyEmail,
};

class User extends Eloquent implements
    AuthenticatableContract,
    CanResetPasswordContract
{
    use Authenticatable;
    use CanResetPassword;
    use MustVerifyEmail;
}
