<?php

namespace App\Constants;

final class UserRole
{
    public const ADMIN = 'admin';
    public const PROFESSOR = 'professor';
    public const STUDENT = 'student';

    public const ALL_ROLES = [
        self::ADMIN,
        self::PROFESSOR,
        self::STUDENT
    ];

    public const CAN_CREATE = [
        self::ADMIN,
        self::PROFESSOR
    ];
}
