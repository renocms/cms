<?php

namespace Reno\Cms\Interfaces\Services;

use Reno\Cms\DTO\Resources\ResourceForCreate;
use Reno\Cms\DTO\Resources\ResourceForEdit;
use Reno\Cms\Models\Resource;

interface ResourceServiceInterface
{
    public function create(ResourceForCreate $dto): Resource;
    
    public function makeDraft(?int $parentId, ?int $contextId = null): Resource;
    
    public function update(int $id, ResourceForEdit $dto): Resource;
    
    public function delete(int $id): bool;
    
    public function findById(int $id): ?Resource;
    
    public function move(int $id, ?int $parentId, int $sortOrder): Resource;
}

