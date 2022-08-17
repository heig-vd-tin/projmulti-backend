<?php

namespace App\Constants;

final class UserRole
{
    public const ADMIN = 'admin';
    public const PROFESSOR = 'professor';
    public const STUDENT = 'student';

    public const ALL = [
        self::ADMIN,
        self::PROFESSOR,
        self::STUDENT
    ];

    public const TEACHERS = [
        self::ADMIN,
        self::PROFESSOR
    ];
}
