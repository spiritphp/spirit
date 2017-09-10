<?php

namespace Spirit\Structure\Model;

use Spirit\DB;
use Spirit\DB\Builder;
use Spirit\Structure\Model;

/**
 * Class SoftRemoveTrait
 * @package Spirit\Structure\Model
 *
 * @mixin Model
 * @mixin Builder
 */
trait SoftRemoveTrait
{

    /**
     * Мягкое удаление
     * @var bool
     */
    protected $isSoftRemove = true;
    protected $softRemovedDate = 'removed_at';

    protected function initSoftRemove()
    {
        if (!in_array('soft_remove', $this->scopes)) {
            $this->scopes[] = 'soft_remove';
        }
    }

    public function restore()
    {
        $this->getQueryBuilder()->update([
            $this->getRemoveAtColumn() => null
        ]);
    }

    public function softRemove()
    {
        $this->getQueryBuilder()->update([
            $this->getRemoveAtColumn() => DB::raw('now()')
        ]);
        $this->queryBuilder = null;
    }

    public function forceRemove()
    {
        $this->isSoftRemove = false;

        $this->remove();

        $this->isSoftRemove = true;
    }

    public function scopeSoftRemove()
    {
        $this->whereNull($this->getRemoveAtColumn());
    }

    public function scopeWithTrashed()
    {
        $this->withoutScopes[] = 'soft_remove';
    }

    public function scopeOnlyTrashed()
    {
        $this->withoutScopes[] = 'soft_remove';
        $this->whereNotNull($this->getRemoveAtColumn());
    }

    public function getRemoveAtColumn()
    {
        return defined('static::REMOVED_AT') ? static::REMOVED_AT : 'removed_at';
    }
}