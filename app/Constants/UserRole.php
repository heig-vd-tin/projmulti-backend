<?php

namespace App\Constants;

final class UserRole
{
    public const ADMIN = 'admin';
    public const PROFESSOR = 'professor';
    public const ASSISTANT = 'assistant';
    public const STUDENT = 'student';
    public const TRAINEE = 'trainee';
    public const AllRoles = [
        self::ADMIN,
        self::PROFESSOR,
        self::ASSISTANT,
        self::STUDENT,
        self::TRAINEE
    ];
}
