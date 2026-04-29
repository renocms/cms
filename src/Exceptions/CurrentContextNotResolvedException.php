<?php

namespace Reno\Cms\Exceptions;

use RuntimeException;

class CurrentContextNotResolvedException extends RuntimeException
{
    protected $message = 'Current context not resolved';
}
