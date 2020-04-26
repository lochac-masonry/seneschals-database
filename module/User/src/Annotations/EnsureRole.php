<?php

declare(strict_types=1);

namespace User\Annotations;

/**
 * @Annotation
 * @Target({"CLASS", "METHOD"})
 */
class EnsureRole
{
    /** @var array<string> */
    public $permittedRoles = ['seneschal', 'admin'];
}
