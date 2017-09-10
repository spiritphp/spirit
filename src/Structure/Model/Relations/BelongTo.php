<?php

namespace Spirit\Structure\Model\Relations;

use Spirit\Structure\Model;

class BelongTo extends HasOne
{

    public function dissociate()
    {
        $this->parentClass->{$this->foreignKey} = null;
    }

    public function associate(Model $model)
    {
        $this->parentClass->{$this->foreignKey} = $model->{$this->localKey};
    }

    public function save(Model $model)
    {

    }
}