<?php

namespace Reno\Cms\Listeners;

use Reno\Cms\Events\FieldTypesRegistering;
use Reno\Cms\FieldTypes\BooleanFieldType;
use Reno\Cms\FieldTypes\CheckboxFieldType;
use Reno\Cms\FieldTypes\CheckboxGroupFieldType;
use Reno\Cms\FieldTypes\ColorFieldType;
use Reno\Cms\FieldTypes\DateFieldType;
use Reno\Cms\FieldTypes\FileFieldType;
use Reno\Cms\FieldTypes\GalleryFieldType;
use Reno\Cms\FieldTypes\MediaFieldType;
use Reno\Cms\FieldTypes\RepeaterFieldType;
use Reno\Cms\FieldTypes\NumberFieldType;
use Reno\Cms\FieldTypes\RadioFieldType;
use Reno\Cms\FieldTypes\RichContentFieldType;
use Reno\Cms\FieldTypes\SelectFieldType;
use Reno\Cms\FieldTypes\StringFieldType;
use Reno\Cms\FieldTypes\TextareaFieldType;

class AddDefaultFieldTypes
{
    public function handle(FieldTypesRegistering $event): void
    {
        $event->addFieldType(new StringFieldType());
        $event->addFieldType(new TextareaFieldType());
        $event->addFieldType(new NumberFieldType());
        $event->addFieldType(new BooleanFieldType());
        $event->addFieldType(new ColorFieldType());
        $event->addFieldType(new DateFieldType());
        $event->addFieldType(new MediaFieldType());
        $event->addFieldType(new RichContentFieldType());
        $event->addFieldType(new SelectFieldType());
        $event->addFieldType(new CheckboxFieldType());
        $event->addFieldType(new CheckboxGroupFieldType());
        $event->addFieldType(new RadioFieldType());
        $event->addFieldType(new FileFieldType());
        $event->addFieldType(new RepeaterFieldType());
        $event->addFieldType(new GalleryFieldType());
    }
}

