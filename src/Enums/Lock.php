<?php

namespace Reno\Cms\Enums;

enum Lock : string
{
    case ResourceLayouts = 'resource-layouts';
    case ResourceTypes = 'resource-types';
    case Contexts = 'contexts';
}
